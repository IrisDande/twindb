<?php

function response($data, $server_id, $error_msg = NULL, $debug_msg = NULL) {
    global $api_debug;
    $msg["data"] = $data;
    $msg["error"] = $error_msg;
    #debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."adding debug message to response on clients request - '".$debug_msg."'");
    if ($api_debug) {
        $msg["debug"] = $debug_msg;
    } else {
        $msg["debug"] = NULL;
    }
    return encrypt(json_encode($msg), $server_id);
}

function encrypt($msg, $recipient) {
    global $gpg_homedir;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."clear text message - '$msg'");
    $gpg_cmd = "gpg --homedir='$gpg_homedir'";
    $gpg_cmd .= " --batch";
    $gpg_cmd .= " --trust-model always";
    $gpg_cmd .= " --encrypt";
    $gpg_cmd .= " --sign";
    $gpg_cmd .= " --local-user api@twindb.com";
    $gpg_cmd .= " --recipient $recipient";
    $ds = array(
        0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
        1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
        2 => array("pipe", "w")   // stderr is a pipe that the child will write to
    );
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."GPG command: '$gpg_cmd'");
    $process = proc_open($gpg_cmd, $ds, $pipes);
    if (is_resource($process)) {
        fwrite($pipes[0], $msg);
        fclose($pipes[0]);
        $cout = stream_get_contents($pipes[1]);
        $cerr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $return_value = proc_close($process);
        if ($return_value == 0) {
            $ct = base64_encode($cout);
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."GPG message for $recipient is successfully encrypted");
       } else {
            vlog("Could not encrypt GPG message. STDOUT: $cout STDERR: $cerr");
            return FALSE;
        }
    } else {
        vlog("Could not start gpg procees '$gpg_cmd'");
        return FALSE;
    }
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."encrypted message - '$ct'");
    return $ct;
}

function decrypt($ct_64, $verify=TRUE) {
    global $gpg_homedir;
    global $server_id;

    $ct = base64_decode($ct_64);
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."encrypted message - '$ct'");
    $msg = "";
    $gpg_cmd = "gpg --homedir='$gpg_homedir'";
    $gpg_cmd .= " --decrypt";
    $gpg_cmd .= " --batch";
    $gpg_cmd .= " --yes";
    $gpg_cmd .= " --no-tty";
    $gpg_cmd .= " --passphrase-file /dev/null";
    if (!$verify) $gpg_cmd .= " --skip-verify";
    $ds = array(
        0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
        1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
        2 => array("pipe", "w")   // stderr is a pipe that the child will write to
    );
    $process = proc_open($gpg_cmd, $ds, $pipes);
    if (is_resource($process)) {
        fwrite($pipes[0], $ct);
        fclose($pipes[0]);
        $cout = stream_get_contents($pipes[1]);
        $cerr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $return_value = proc_close($process);
        if ($return_value == 0) {
            $msg = $cout;
            foreach(split("\n", $cerr) as $line) {
                if (strstr($line, "gpg: Good signature from") !== FALSE) {
                    $end = strpos($line, "@twindb.com");
                    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."server_id ends at $end");
                    $server_id = substr($line, $end - 36, 36); # 36 is length of UUID
                }
            }
            if ($server_id == "" && $verify) {
                vlog("Could not find server id in output: STDERR: $cerr");
                vlog("STDOUT: $cout");
                return FALSE;
            }
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."GPG message form $server_id is successfully decrypted");
       } else {
            vlog("Could not decrypt GPG message. STDOUT: $cout STDERR: $cerr");
            return FALSE;
        }
    } else {
        vlog("Could not start gpg procees '$gpg_cmd'");
        return FALSE;
    }
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."clear text message - '$msg'");
    return $msg;
}

# Gets request
# Input:
#   $c_srt    Encrypted string that contains the request
# Returns:
#   $request  Object that contains the request or FALSE if request cannnot be decrypted or verified 
function get_request($c_str) {
    $request = FALSE;
    
    # Decrypt the request, check signature
    $request_json = decrypt($c_str);

    if ($request_json != FALSE) {
        # $c_str was encrypted and signed request
        return json_decode($request_json);
    } else {
        # Check, maybe it's a plaintext request
        $request = json_decode($_POST["data"]);
        if ( $request != NULL && ( $request->type == "register_storage" || $request->type == "register_storage_confirm" ) ) {
            # Ok, this is a valid plain-text request
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Request type ".$request->type." can be plain text");
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Returning:");
            debug($request);
            return $request;
        }
        # try again, but don't check signature, maybe this is an unsigned request
        $request = json_decode(decrypt($c_str, FALSE));
        if ($request != NULL && $request->type == "register") {
            # Ok, this is an encrypted, but not signed request
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Request type ".$request->type." doesn't have to be signed");
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Returning:");
            debug($request);
            return $request;
        }
        # Ignoring unknown request
        return FALSE;
    } 
    return $request;
}


# Registers a server
# Input:
#   $request    Associative array with request parameters

function register_server($request) {

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."checking incoming request");
    debug($request);
    # Check that mandatory parameters are present
    if (!isset($request->params->reg_code)) {
        critical_api_error("There is no registration code in the incoming request");
    }
    if (!isset($request->params->name)) {
        critical_api_error("There is no server name in the incoming request");
    }
    if (!isset($request->params->server_id)) {
        critical_api_error("There is no server id in the incoming request");
    }
    if (!isset($request->params->enc_public_key)) {
        critical_api_error("There is no GPG public key in the incoming request");
    }
    if (!isset($request->params->ssh_public_key)) {
        critical_api_error("There is no SSH public key in the incoming request");
    }

    $reg_code = $request->params->reg_code;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Incoming registration request is OK");
    $user_id = get_user_id_by_code($reg_code);
    if ($user_id == -1) {
        critical_api_error("ERROR: Could not find user with reg_code $reg_code");
    }

    start_transaction("api");
    $server_id = $request->params->server_id;
    $name = $request->params->name;

    if (is_server_registered($server_id)) {
        critical_api_error("ERROR: Server $name ($server_id) is already registered");
    }

    $mysql_server_id = $request->params->mysql_server_id;
    if (mysql_server_id_exists($user_id, $mysql_server_id)) {
        critical_api_error("ERROR: Server with server_id $mysql_server_id already registered in TwinDB.".
            " server_id must be unique in your environment." .
            " Change variable server_id in my.cnf, restart MySQL server and try to register the agent again.");
    }

    if (!add_server($request, $user_id)) {
        critical_api_error("ERROR: Failed to register server $name ($server_id)");
    }
    commit_transaction("api");

    schedule_backup_job($server_id);
    
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Registered!");
    $response["success"] = TRUE;
    $response["response"] = NULL;
    echo json_encode($response);
    
    return TRUE;
}

# Unregisters a server
# Input:
#   $request    Associative array with request parameters

function unregister_server($request) {
    global $m;
    global $server_id;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."checking incoming request");
    debug($request);
    $delete_backups = FALSE;
    if ( isset($request->params->delete_backups) && $request->params->delete_backups == TRUE) {
        $delete_backups = TRUE;
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "." Will delete existing backups");
    } 
    $user_id = get_user_id_by_server_id($server_id);
    if ($user_id == -1) {
        critical_api_error("ERROR: Could not find user that owns server_id $server_id");
    }

    start_transaction();
    delete_server($request, $user_id, $delete_backups);
    commit_transaction();
    
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Unregistered!");
    $response["success"] = TRUE;
    $response["response"] = NULL;
    echo json_encode($response);
    
    return TRUE;
}

# Registers a storage server
# Input:
#   $request    Associative array with request parameters

function register_storage($request) {
    global $m;
    global $dispatcher_ssh_key_public;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."checking incoming request");
    debug($request);
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Registration code: ".$request->params->reg_code);
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Size: ".$request->params->size);
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Name: ".$request->params->name);
    $reg_code = $request->params->reg_code;
    $user_id = (int)get_user_id_by_code($reg_code);
    if ($user_id == -1) {
        $response["success"] = FALSE;
        $response["response"] = NULL;
        $response["error"] = "ERROR: Could not find user with reg_code $reg_code";
        echo json_encode($response);
        return TRUE;
    }
    $size = (int)$request->params->size;
    $ip = $_SERVER["REMOTE_ADDR"];
    $name = $m["rw"]->escape_string($request->params->name);
    $params["ip"] = $ip;
    $params_json = json_encode($params);
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."request from $ip");

    start_transaction("api");
    # Delete previous unsuccessful registration attempts
    $q = "DELETE `storage`, `volume`";
    $q .= " FROM `storage` JOIN `volume` USING(`storage_id`)";
    $q .= " WHERE `storage`.`ip` = '$ip' AND `storage`.`registered` = 'no'";
    mysql_rw_execute($q, "api");
    $q = "INSERT INTO `storage`";
    $q .= "(`user_id`, `name`, `params`, `size`, `ip`)";
    $q .= " VALUES($user_id, '$name', '$params_json', $size, '$ip')";
    mysql_rw_execute($q, "api");
    $storage_id = $m["insert_id"];
    if ( $user_id != 0 ) {
        # Make a volume from the new storage
        $q = "INSERT INTO `volume` (`storage_id`, `user_id`, `username`, `size`, `name`)";
        $q .= " VALUES($storage_id, $user_id, 'user_id_$user_id', $size, '$name')";
        mysql_rw_execute($q, "api");
        $volume_id = $m["insert_id"];
        $q = "UPDATE `config` SET `volume_id` = $volume_id WHERE `user_id` = $user_id AND `volume_id` IS NULL";
        mysql_rw_execute($q, "api");
        
        $response["user_id"] = $user_id;
        $response["storage_id"] = $storage_id;
        $response["ssh_keys"] = array();
        $q = "SELECT `ssh_public_key` FROM `server` WHERE `user_id` = $user_id";
        $r = mysql_rw_execute($q, "api");
        while ( $row = $r->fetch_row() ) {
            array_push($response["ssh_keys"], $row[0]);
        }
    }
    commit_transaction("api");
    
    $response["success"] = TRUE;
    $response["response"] = $response;
    echo json_encode($response);

    return TRUE;
}
# When storage registers itself dispatcher requests to create a user and volume on the new storage
# The storage server calls handler_register_storage_confirm to confirm that user is created and 
# the storage is to confirm that user is created and 
# the storage is good to go.
# Input:
#   $request    Associative array with request parameters

function handler_register_storage_confirm($request) {
    global $m;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."checking incoming request");
    debug($request);
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Registration code: ".$request->params->reg_code);
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Size: ".$request->params->size);
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Name: ".$request->params->name);
    $reg_code = $request->params->reg_code;
    $user_id = (int)get_user_id_by_code($reg_code);
    if ($user_id == -1) {
        $response["success"] = FALSE;
        $response["response"] = NULL;
        $response["error"] = "ERROR: Could not find user with reg_code $reg_code";
        echo json_encode($response);
        return TRUE;
    }
    $storage_id = (int)$request->params->storage_id;
    $ip = $_SERVER["REMOTE_ADDR"];
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."request from $ip");

    mysql_rw_execute("BEGIN");
    $q = "UPDATE `storage` SET `registered` = 'yes' WHERE `storage_id` = $storage_id";
    mysql_rw_execute($q);

    mysql_rw_execute("COMMIT");
    $response["success"] = TRUE;
    $response["response"] = NULL;
    echo json_encode($response);

    return TRUE;
}
# Finds user by reg_code
# Input:
#   $reg_code   Registration code assigned to a user
# Returns:
#   user_id or -1 if no user is found
function get_user_id_by_code($reg_code) {
    global $m;
    $user_id = -1;

    $reg_code = $m["ro"]->escape_string($reg_code);
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."looking for user with reg_code $reg_code");

    $q = "SELECT user_id ";
    $q .= " FROM user";
    $q .= " WHERE reg_code = '$reg_code'";
    $q .= " AND reg_code IS NOT NULL";
    $r = mysql_rw_execute($q, "api");

    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        $user_id = (int)$row[0];
    }
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."returning user_id = $user_id");
    return (int)$user_id;
}

# Finds user by server_id
# Input:
#   $server_id   Server_id
# Returns:
#   user_id or 0 if no user is found
function get_user_id_by_server_id($server_id, $caller = "api") {
    global $m;
    $user_id = -1;

    $server_id = $m["ro"]->escape_string($server_id);
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."looking for user with server_id = '$server_id'");

    $q = "SELECT `user_id`";
    $q .= " FROM `server`";
    $q .= " WHERE `server_id` = '$server_id'";
    $q .= " AND `server`.`registered` = 'Yes'";
    $r = mysql_rw_execute($q, $caller);

    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        $user_id = $row[0];
    }
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."returning user_id = $user_id");
    return (int)$user_id;
}

# Finds user by job_id
# Input:
#   $job_id   job_id
# Returns:
#   user_id or 0 if no user is found
function get_user_id_by_job_id($job_id, $caller = NULL) {
    global $m;
    global $server_id;
    $user_id = 0;

    $server_id = $m["ro"]->escape_string($server_id);
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."looking for user that owns job_id = '$job_id'");

    $q = "SELECT `server`.`user_id`";
    $q .= " FROM `job`";
    $q .= " JOIN `server` USING(`server_id`)";
    $q .= " WHERE `job`.`job_id` = '$job_id'";
    $r = mysql_rw_execute($q, $caller);

    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        $user_id = $row[0];
    }
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."returning user_id = $user_id");
    return (int)$user_id;
}


# Finds job type by job_id
# Input:
#   $job_id   job_id
# Returns:
#   job type or NULL if not found
function get_type_by_job_id($job_id, $caller = NULL) {

    $type = NULL;

    $job_id = (int)$job_id;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): looking for type of job with  job_id = $job_id");

    $q = "SELECT `type`";
    $q .= " FROM `job`";
    $q .= " WHERE `job_id` = $job_id";
    $r = mysql_rw_execute($q, $caller);

    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        $type = $row[0];
    }
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): job_id = $job_id, type = $type");
    return $type;
}



# Finds user by volume_id
# Input:
#   $volume_id   volume_id
# Returns:
#   user_id or -1 if no user is found
function get_user_id_by_volume_id($volume_id) {
    global $m;
    global $server_id;
    $user_id = -1;

    $server_id = $m["ro"]->escape_string($server_id);
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."looking for user that owns volume_id = '$volume_id'");

    $volume_id = (int)$volume_id;
    $q = "SELECT `user_id`";
    $q .= " FROM `volume`";
    $q .= " WHERE `volume_id` = $volume_id";
    $r = mysql_rw_execute($q, "api");

    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        $user_id = $row[0];
    }
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."returning user_id = $user_id");
    return (int)$user_id;
}

function get_user_id_by_attribute_id($attribute_id, $caller = NULL){
    global $m;
    $user_id = 0;

    $attribute_id = (int)$attribute_id;

    $q = "SELECT `user_id`";
    $q .= " FROM `attribute`";
    $q .= " WHERE `attribute_id` = '$attribute_id'";
    $r = mysql_rw_execute($q, $caller);
    if($r->num_rows > 0){
        $row = $r->fetch_row();
        $user_id = $row[0];
    }
    debug("Attribute $attribute_id belongs to user $user_id");
    return (int)$user_id;
}

function get_user_id_by_config_id($config_id, $caller = NULL){
    global $m;
    $user_id = 0;

    $config_id = (int)$config_id;

    $q = "SELECT `user_id`";
    $q .= " FROM `config`";
    $q .= " WHERE `config_id` = $config_id";
    $r = mysql_rw_execute($q, $caller);
    if($r->num_rows > 0){
        $row = $r->fetch_row();
        $user_id = $row[0];
    }
    debug("Config $config_id belongs to user $user_id");
    return (int)$user_id;
}

function get_user_id_by_backup_copy_id($backup_copy_id, $caller = NULL){
    global $m;
    $user_id = 0;

    $backup_copy_id = (int)$backup_copy_id;

    $q = "SELECT `server`.`user_id`";
    $q .= " FROM `backup_copy`";
    $q .= " JOIN `job` USING(`job_id`)";
    $q .= " JOIN `server` USING (`server_id`)";
    $q .= " WHERE `backup_copy`.`backup_copy_id` = $backup_copy_id";
    $r = mysql_rw_execute($q, $caller);
    if($r->num_rows > 0){
        $row = $r->fetch_row();
        $user_id = $row[0];
    }
    debug("Backup copy id $backup_copy_id belongs to user $user_id");
    return (int)$user_id;
}

function add_server($request, $user_id) {
    global $m;

    $user_id                     = (int)($user_id);
    $server_id                   = $m["rw"]->escape_string($request->params->server_id);
    $name                        = $m["rw"]->escape_string($request->params->name);
    $ssh_public_key              = $m["rw"]->escape_string($request->params->ssh_public_key);
    $enc_public_key              = $m["rw"]->escape_string($request->params->enc_public_key);
    if ($request->params->mysql_server_id != "") {
        $mysql_server_id = (int)$request->params->mysql_server_id;
    } else {
        $mysql_server_id = "NULL";   
    }
    if ($request->params->mysql_master_host != "") {
        $mysql_master_host = "'".$m["rw"]->escape_string($request->params->mysql_master_host)."'";
    } else {
        $mysql_master_host = "NULL";
    }
    if ($request->params->mysql_master_server_id != "") {
        $mysql_master_server_id = (int)$request->params->mysql_master_server_id;
    } else {
        $mysql_master_server_id = "NULL";   
    }
    if ( $mysql_master_server_id == 0 ) {
        $mysql_master_server_id = "NULL";
    }
    if ($request->params->mysql_seconds_behind_master != "") {
        $mysql_seconds_behind_master = (int)$request->params->mysql_seconds_behind_master;
    } else {
        $mysql_seconds_behind_master = "NULL";   
    }
    if ($request->params->mysql_slave_io_running != "") {
        $mysql_slave_io_running = "'".$m["rw"]->escape_string($request->params->mysql_slave_io_running)."'";
    } else {
        $mysql_slave_io_running = "NULL";
    }
    if ($request->params->mysql_slave_sql_running != "") {
        $mysql_slave_sql_running = "'".$m["rw"]->escape_string($request->params->mysql_slave_sql_running)."'";
    } else {
        $mysql_slave_sql_running = "NULL";
    }
    $config_id                   = (int)get_config_id($user_id);
    
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."adding server $server_id");

    $q = "INSERT INTO `server`";
    $q .= "(`server_id`, `user_id`, `name`, `ssh_public_key`, `enc_public_key`, `config_id`, `last_seen_at`, `mysql_server_id`, `mysql_master_host`, `mysql_master_server_id`, `mysql_seconds_behind_master`, `mysql_slave_io_running`, `mysql_slave_sql_running`)";
    $q .= " VALUES('$server_id', $user_id, '$name', '$ssh_public_key', '$enc_public_key', $config_id, NOW(), $mysql_server_id, $mysql_master_host, $mysql_master_server_id, $mysql_seconds_behind_master, $mysql_slave_io_running, $mysql_slave_sql_running)";
    $q .= " ON DUPLICATE KEY UPDATE";
    $q .= " `registered` = 'Yes',";
    $q .= " `user_id` = '$user_id',";
    $q .= " `name` = '$name',";
    $q .= " `ssh_public_key` = '$ssh_public_key',";
    $q .= " `enc_public_key` = '$enc_public_key',";
    $q .= " `config_id` = $config_id,";
    $q .= " `last_seen_at` = NOW(),";
    $q .= " `mysql_server_id` = $mysql_server_id,";
    $q .= " `mysql_master_host` = $mysql_master_host,";
    $q .= " `mysql_master_server_id` = $mysql_master_server_id,";
    $q .= " `mysql_seconds_behind_master` = $mysql_seconds_behind_master,";
    $q .= " `mysql_slave_io_running` = $mysql_slave_io_running,";
    $q .= " `mysql_slave_sql_running` = $mysql_slave_sql_running";

    $r = mysql_rw_execute($q, "api");
    if ($r === TRUE) {
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."created new server with id $server_id");
        add_gpg_key($request->params->enc_public_key);
        add_ssh_key($request->params->ssh_public_key);
    } 
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."server id $server_id is successfully added");
    return TRUE;
}

function delete_server($request, $user_id, $delete_backups) {
    global $m;
    global $server_id;
    global $dispatcher_ssh_key_private;
    
    $server_id = $m["rw"]->escape_string($server_id);
    if ( $delete_backups ) {
        $user_id = (int)$user_id;
        $q = "SELECT `backup_copy`.`backup_copy_id`,";
        $q .= " `backup_copy`.`name`,";
        $q .= " `storage`.`ip`";
        $q .= " FROM `backup_copy`";
        $q .= " JOIN `job` USING(`job_id`)";
        $q .= " JOIN `volume` USING (`volume_id`)";
        $q .= " JOIN `storage` USING(`storage_id`) ";
        $q .= "WHERE `server_id` = '$server_id'";
        $r = mysql_rw_execute($q, "api");
        while ( $row = $r->fetch_row() ) {
            $backup_copy_id = (int)$row[0];
            $backup_copy_file = $m["rw"]->escape_string( $row[1] );
            $ip = $m["rw"]->escape_string( $row[2] );
            $ssh_arg = "-oStrictHostKeyChecking=no";
            $ssh_arg .= " -i ". escapeshellarg($dispatcher_ssh_key_private);
            $ssh_arg .= " -p 4194";
            $ssh_arg .= " ".escapeshellarg("root@$ip");
            $ssh_arg .= " ".escapeshellarg("rm -f /var/twindb-sftp/user_id_$user_id/home/$backup_copy_file");
            $cmd = escapeshellcmd("ssh $ssh_arg");
            exec($cmd, $cout, $retcode);
            if ( $retcode == 0 ) {
                $q = "DELETE FROM `backup_copy` WHERE `backup_copy_id` = $backup_copy_id";
                mysql_rw_execute($q, "api");
            } else {
                critical_api_error("Failed to delete backup copies of server $server_id from storage $ip");
            }
        }
        $q = "DELETE FROM `server` WHERE `server_id` = '$server_id'";
        mysql_rw_execute($q, "api");
    } else {
        $q = "UPDATE `server` SET `registered` = 'No' WHERE `server_id` = '$server_id'";
        mysql_rw_execute($q, "api");
    }
}

function add_gpg_key($enc_public_key) {
    global $gpg_homedir;

    if (!file_exists($gpg_homedir)) {
        if (false == mkdir($gpg_homedir, 0700, true)) {
            critical_api_error("Could not init GPG environment", "Could not create GPG homedir $gpg_homedir");
        }
    }
    $gpg_cmd = "gpg --homedir='$gpg_homedir' --import --ignore-time-conflict";
    $ds = array(
        0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
        1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
        2 => array("pipe", "w")   // stderr is a pipe that the child will write to
    );
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."executing $gpg_cmd");
    $process = proc_open($gpg_cmd, $ds, $pipes);
    if (is_resource($process)) {
        fwrite($pipes[0], $enc_public_key);
        fclose($pipes[0]);
        $cout = stream_get_contents($pipes[1]);
        $cerr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $return_value = proc_close($process);
        if ($return_value != 0) {
            critical_api_error("Could not add GPG public key", "STDOUT: $cout\nSTDERR: $cerr");
        }
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."GPG key is successfully added");
    } else {
        critical_api_error(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Could not start gpg procees '$gpg_cmd'");
    }
    return true;
}
function add_ssh_key($ssh_public_key) {
    return true;
}
# Gets a config with the highest priority
function get_config_id($user_id) {
    global $m;
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."getting config for user_id $user_id");
    $user_id = (int)$user_id;

    $q = "SELECT config_id";
    $q .= " FROM config";
    $q .= " WHERE user_id = $user_id";
    $q .= " ORDER BY priority";
    $q .= " LIMIT 1";
    $r = mysql_rw_execute($q, "api");

    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        $config_id = $row[0];
    } else {
        $config_id = add_config($user_id);
    }
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."returning config id $config_id");
    return $config_id;
 }   

# Adds a default config
function add_config($user_id) {
    global $m;

    $user_id = (int)$user_id;
    $schedule_id = get_schedule($user_id);
    $retention_policy_id = get_retention_policy($user_id);
    $volume_id = get_volume($user_id);
    $mysql_user = "twindb_agent";
    $mysql_password = substr(md5(rand()), 0, 16);

    $q = "INSERT INTO config";
    $q .= " (user_id, schedule_id, retention_policy_id, volume_id, mysql_user, mysql_password)";
    $q .= " VALUES($user_id, $schedule_id, $retention_policy_id, $volume_id, '$mysql_user', '$mysql_password')";
    $r = mysql_rw_execute($q, "api");

    if ($r === TRUE) {
        $config_id = $m["insert_id"];
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."added config id $config_id user_id $user_id");
        return $config_id;
    }
}

# Gets a schedule with the highest priority
function get_schedule($user_id) {
    global $m;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."getting schedule for user_id $user_id");
    $user_id = (int)$user_id;

    $q = "SELECT schedule_id";
    $q .= " FROM schedule";
    $q .= " WHERE user_id = $user_id";
    $q .= " ORDER BY priority";
    $q .= " LIMIT 1";
    $r = mysql_rw_execute($q, "api");

    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        $schedule_id = $row[0];
    } else {
        $schedule_id = add_schedule($user_id);
    }
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."returning schedule id $schedule_id");
    return $schedule_id;
}

# Adds a default schedule
function add_schedule($user_id) {
    global $m;

    $user_id = (int)$user_id;

    $q = "INSERT INTO schedule";
    $q .= " (user_id)";
    $q .= " VALUES($user_id)";
    $r = mysql_rw_execute($q, "api");

    if ($r === TRUE) {
        $schedule_id = $m["insert_id"];
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."added schedule id $schedule_id user_id $user_id");
        return $schedule_id;
    }
}

# Gets a retention policy with the highest priority
function get_retention_policy($user_id) {
    global $m;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."getting retention policy for user_id $user_id");
    $user_id = (int)$user_id;

    $q = "SELECT retention_policy_id";
    $q .= " FROM retention_policy";
    $q .= " WHERE user_id = $user_id";
    $q .= " ORDER BY priority";
    $q .= " LIMIT 1";
    $r = mysql_rw_execute($q, "api");

    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        $retention_policy_id = $row[0];
    } else {
        $retention_policy_id = add_retention_policy($user_id);
    }
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."returning retention policy id $retention_policy_id");
    return $retention_policy_id;
    
}
# Adds a default retention policy
function add_retention_policy($user_id) {
    global $m;

    $user_id = (int)$user_id;

    $q = "INSERT INTO retention_policy";
    $q .= " (user_id)";
    $q .= " VALUES($user_id)";
    $r = mysql_rw_execute($q, "api");

    if ($r === TRUE) {
        $retention_policy_id = $m["insert_id"];
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."added retention policy id $retention_policy_id user_id $user_id");
        return $retention_policy_id;
    }
}
# Gets a volume with the highest priority
function get_volume($user_id) {
    global $m;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."getting volume for user_id $user_id");
    $user_id = (int)$user_id;

    $q = "SELECT volume_id";
    $q .= " FROM volume";
    $q .= " WHERE user_id = $user_id";
    $q .= " ORDER BY priority";
    $q .= " LIMIT 1";
    $r = mysql_rw_execute($q, "api");

    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        $volume_id = $row[0];
    } else {
        $volume_id = add_volume($user_id);
    }
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."returning volume id $volume_id");
    return $volume_id;
}

# Adds a default volume
function add_volume($user_id) {
    global $m;

    $user_id = (int)$user_id;
    $storage_id = (int)get_storage($user_id);

    $q = "INSERT INTO volume";
    $q .= " (user_id, storage_id, username)";
    $q .= " VALUES($user_id, $storage_id, 'user_id_$user_id')";
    $r = mysql_rw_execute($q, "api");

    if ($r === FALSE) {
        $volume_id = $m["insert_id"];
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."added volume id $volume_id user_id $user_id");
        return $volume_id;
    }
}

# Gets a storage
function get_storage($user_id) {
    global $m;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."getting storage for user_id $user_id");
    $user_id = (int)$user_id;
    # Return TwinDB's free or user's storage

    $q = "SELECT storage_id";
    $q .= " FROM storage";
    $q .= " WHERE user_id IN(0, $user_id)";
    $q .= " AND (size - used_size) > 2147483648"; # Make sure the storage has enough space for new volume
    $q .= " ORDER BY user_id";
    $q .= " LIMIT 1";
    $r = mysql_rw_execute($q, "api");

    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        $storage_id = $row[0];
    } else {
        critical_api_error("Could not find free storage for user volume. Please add more storage");
    }
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."returning storage id $volume_id");
    return $storage_id;
}

# Logs a message from an agent.
# server_id must be globally defined. 
# Input:
#   $request    Associative array with request parameters

function rlog($request) {
    global $m;
    global $server_id;

    if (strlen($server_id) == 36) {
        $server_id = $m["rw"]->escape_string($server_id);
        $msg = $m["rw"]->escape_string($request->params->msg);
        if ( isset($request->params->job_id) ) {
            $job_id = (int)$request->params->job_id;
        } else {
            $job_id = "NULL";
        }
        $msg = $m["rw"]->escape_string($request->params->msg);
        $user_id = (int)get_user_id_by_server_id($server_id);
        if ($user_id == -1) return FALSE;
        $q = "INSERT INTO log";
        $q .= " (user_id, job_id, server_id, msg)";
        $q .= " VALUES($user_id, $job_id, '$server_id', '$msg')";
        $r = mysql_rw_execute($q, "api");
    }
    $response["success"] = TRUE;
    $response["response"] = NULL;
    echo json_encode($response);
    return TRUE;
}

# Gets config for server and returns it to client
# Input:
#   $request    Associative array with request parameters

function get_config($request) {
    global $m;
    global $server_id;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."checking incoming request");
    debug($request);
    if(!isset($server_id)){
        critical_api_error("server_id is not set");
    }

    $server_id = $m["ro"]->escape_string($server_id);

    $q = "SELECT ";
    $q .= " `config`.`config_id`,";
    $q .= " `config`.`user_id`,";
    $q .= " `config`.`schedule_id`,";
    $q .= " `config`.`retention_policy_id`,";
    $q .= " `config`.`volume_id`,";
    $q .= " `config`.`mysql_user`,";
    $q .= " `config`.`mysql_password`";
    $q .= " FROM `server`";
    $q .= " JOIN `config` USING(`config_id`)";
    $q .= " WHERE `server_id` = '$server_id'";
    $q .= " AND `server`.`registered` = 'Yes'";
    $r = mysql_rw_execute($q, "api");

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."found ".$r->num_rows." config(s)for $server_id");
    $config = $r->fetch_assoc();
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."returning config:");
    debug($config);

    $response["success"] = TRUE;
    $response["response"] = response($config, $server_id);
    echo json_encode($response);
    return TRUE;
}

# Gets job for server and returns it to client
# Input:
#   $request    Associative array with request parameters

function get_job($request) {
    global $m;
    global $server_id;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."checking incoming request");
    debug($request);
    if(!isset($server_id)){
        critical_api_error("server_id is not set");
    }

    $server_id = $m["ro"]->escape_string($server_id);

    $q = "SELECT ";
    $q .= " `job_id`,";
    $q .= " `type`,";
    $q .= " UNIX_TIMESTAMP(`start_scheduled`) AS `start_scheduled`,";
    $q .= " `params`";
    $q .= " FROM `job`";
    $q .= " JOIN `server` USING (`server_id`)";
    $q .= " WHERE `job`.`server_id` = '$server_id'";
    $q .= " AND `server`.`registered` = 'Yes'";
    $q .= " AND `job`.`status` = 'Scheduled'";
    $q .= " ORDER BY `job`.`start_scheduled`";
    $q .= " LIMIT 1";
    $r = mysql_rw_execute($q, "api");

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."found ".$r->num_rows." job(s)for $server_id");
    $job = $r->fetch_assoc();
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."returning job:");
    debug($job);

    $response["success"] = TRUE;
    $response["response"] = response($job, $server_id);
    echo json_encode($response);
    return TRUE;
}

# Handles notify event from agent
# server_id must be globally defined. 
# Input:
#   $request    Associative array with request parameters

function handler_notify($request) {
    global $m;
    global $server_id;

    if (strlen($server_id) != 36) {
        critical_api_error("Wrong or undefined server_id = $server_id");
    }
    $server_id = $m["rw"]->escape_string($server_id);
    $user_id = (int)get_user_id_by_server_id($server_id);
    $job_id = (int)$request->params->job_id;
    # Exit if unknown server
    if ($user_id == -1) return FALSE;
    # Exits if given job_id doesn't belong to this server;
    if ($user_id != get_user_id_by_job_id($job_id, "api")) return FALSE;
    switch ($request->params->event) {
        case "start_job":
            start_transaction("api");

            # Set 'In progress' status
            $q = "UPDATE `job`";
            $q .= " SET `status` = 'In progress',";
            $q .= " `start_actual` = NOW()";
            $q .= " WHERE `job_id` = $job_id";
            mysql_rw_execute($q, "api");

            commit_transaction("api");

            break;
        case "stop_job":
            start_transaction("api");

            $status = $request->params->ret_code == 0 ? "Finished" : "Failed";
            if ($status == "Failed") {

                $q = "SELECT email FROM user WHERE user_id = $user_id";
                $result = mysql_ro_execute($q, "api");
                $email = NULL;
                if ($result->num_rows > 0) {
                    $row = $result->fetch_row();
                    $email = $row[0];
                }
                $result->free();

                $q = "SELECT name FROM server WHERE server_id = '$server_id'";
                $result = mysql_ro_execute($q, "api");
                $server_name = NULL;
                if ($result->num_rows > 0) {
                    $row = $result->fetch_row();
                    $server_name = $row[0];
                }
                $result->free();

                $q = "SELECT ts, msg FROM log WHERE job_id = $job_id";
                $result = mysql_ro_execute($q, "api");
                $msg = "Failed job_id $job_id log:\n";
                while ($row = $result->fetch_assoc()) {
                    $msg .= $row["ts"] . ": " . $row["msg"] . "\n";
                }
                $result->free();

                mail("nagios@twindb.com", "Failed job[$job_id]: User: $email: Server: $server_name", $msg);
            }
            $job_id = (int)$request->params->job_id;
            $q = "UPDATE `job`";
            $q .= " SET `status` = '$status',";
            $q .= " `finish_actual` = NOW()";
            $q .= " WHERE `job_id` = $job_id";
            mysql_rw_execute($q, "api");

            commit_transaction("api");
            break;
        default:
            $response["success"] = FALSE;
            $response["response"] = NULL;
            echo json_encode($response);
            return FALSE;
    }
    $response["success"] = TRUE;
    $response["response"] = NULL;
    echo json_encode($response);
    return TRUE;
}

# Handles report_sss event from agent
# server_id must be globally defined. 
# Input:
#   $request    Associative array with request parameters

function handler_report_sss($request) {
    global $m;
    global $server_id;

    if (strlen($server_id) != 36) {
        critical_api_error("Wrong or undefined server_id = $server_id");
    }
    $server_id = $m["rw"]->escape_string($server_id);
    $user_id = (int)get_user_id_by_server_id($server_id);
    
    # Exit if unknown server
    if ($user_id == -1) return FALSE;

    $mysql_server_id = (int)$request->params->mysql_server_id;
    if ($request->params->mysql_master_server_id != "") {
        $mysql_master_server_id = (int)$request->params->mysql_master_server_id;
    } else {
        $mysql_master_server_id = "NULL";
    }
    if ($mysql_master_server_id == 0) {
        $mysql_master_server_id = "NULL";
    }
    if ($request->params->mysql_master_host != "") {
        $mysql_master_host = "'".$m["rw"]->escape_string($request->params->mysql_master_host)."'";
    } else {
        $mysql_master_host = "NULL";
    }
    if ($request->params->mysql_seconds_behind_master === NULL) {
        $mysql_seconds_behind_master = "NULL";
    } else {
        $mysql_seconds_behind_master =  (int)$request->params->mysql_seconds_behind_master;
    }
    if ($request->params->mysql_slave_io_running == "Yes") {
        $mysql_slave_io_running = "'Yes'";
    } elseif ($request->params->mysql_slave_io_running == "No") {
        $mysql_slave_io_running = "'No'";
    } else {
        $mysql_slave_io_running = "NULL";
    }
    if ($request->params->mysql_slave_sql_running == "Yes") {
        $mysql_slave_sql_running = "'Yes'";
    } elseif ($request->params->mysql_slave_sql_running == "No") {
        $mysql_slave_sql_running = "'No'";
    } else {
        $mysql_slave_sql_running = "NULL";
    }
    start_transaction("api");
    # Don't update SHOW SLAVE STATUS if it hasn't changed since last report
    if (sss_changed($request)) {
        // Reset cluster_id
        $q = "UPDATE `server` ";
        $q .= " SET `cluster_id` = NULL";
        $q .= " WHERE `user_id` = $user_id";
        mysql_rw_execute($q, "api");
    } 
    $q = "UPDATE `server`";
    $q .= " SET `last_seen_at` = NOW(),";
    $q .= " `mysql_server_id` = $mysql_server_id,";
    $q .= " `mysql_master_server_id` = $mysql_master_server_id,";
    $q .= " `mysql_master_host` = $mysql_master_host,";
    $q .= " `mysql_seconds_behind_master` = $mysql_seconds_behind_master,";
    $q .= " `mysql_slave_io_running` = $mysql_slave_io_running,";
    $q .= " `mysql_slave_sql_running` = $mysql_slave_sql_running";
    $q .= " WHERE `server_id` = '$server_id'";
    mysql_rw_execute($q, "api");
    commit_transaction("api");

    $response["success"] = TRUE;
    $response["response"] = NULL;
    echo json_encode($response);
    return TRUE;
}

# Returns TRUE if replication topology might have changed
 
function sss_changed($request) {
    global $m;
    global $server_id;

    if (strlen($server_id) != 36) {
        critical_api_error("Wrong or undefined server_id = $server_id");
    }
    $server_id = $m["rw"]->escape_string($server_id);
    $user_id = (int)get_user_id_by_server_id($server_id);
    
    # Exit if unknown server
    if ($user_id == -1) return FALSE;
    
    $q = "SELECT";
    $q .= " `mysql_server_id`,";
    $q .= " `mysql_master_server_id`";
    $q .= " FROM `server`";
    $q .= " WHERE `server_id` = '$server_id'";
    $r = mysql_rw_execute($q, "api");
    if ($r->num_rows > 0) {
        $row = $r->fetch_assoc();
        if ($request->params->mysql_server_id != $row["mysql_server_id"]) {
            return TRUE;
        }
        if ($request->params->mysql_master_server_id != $row["mysql_master_server_id"]) {
            return TRUE;
        }
    }
    return FALSE;

}
# Handles update_backup_data event from agent
# server_id must be globally defined. 
# Input:
#   $request    Associative array with request parameters

function handler_update_backup_data($request) {
    global $m;
    global $server_id;

    if (strlen($server_id) != 36) {
        critical_api_error("Wrong or undefined server_id = $server_id");
    }
    $server_id = $m["rw"]->escape_string($server_id);
    $user_id = (int)get_user_id_by_server_id($server_id);
    
    # Exit if unknown server
    if ($user_id == -1) return FALSE;
    
    $job_id = (int)$request->params->job_id;
    if( $user_id != get_user_id_by_job_id($job_id, "api")) {
        return FALSE;
    }
    $volume_id = (int)$request->params->volume_id;
    if( $user_id != get_user_id_by_volume_id($volume_id)) {
        return FALSE;
    }
    
    $name = $m["rw"]->escape_string($request->params->name);
    $size = (int)$request->params->size;
    $lsn = $m["rw"]->escape_string($request->params->lsn);
    if ( $lsn == "" ) {
        $response["success"] = FALSE;
        $response["response"] = response(NULL, $server_id, "ERROR: Empty LSN");
        echo json_encode($response);
        return FALSE;
    }
    $ancestor = (int)$request->params->ancestor;
    $volume_id = (int)$request->params->volume_id;
    $full_backup = ( get_backup_type($job_id) == "full" ) ? "Y" : "N";
    
    start_transaction("api");
    $q = "INSERT INTO `backup_copy`";
    $q .= " (`job_id`, `name`, `volume_id`, `size`, `lsn`, `ancestor`, `full_backup`)";
    $q .= " VALUES($job_id, '$name', $volume_id, $size, '$lsn', $ancestor, '$full_backup')";
    mysql_rw_execute($q, "api");
    $backup_copy_id = $m["insert_id"];
    $q = "INSERT INTO`backup_copy_tree` (`ancestor`, `descendant`, `length`)";
    $q .= " SELECT `ancestor`, $backup_copy_id, `length` + 1";
    $q .= " FROM `backup_copy_tree`";
    $q .= " WHERE `descendant` = $ancestor";
    $q .= " UNION ALL";
    $q .= " SELECT $backup_copy_id, $backup_copy_id, 0";
    mysql_rw_execute($q, "api");
    $q = "UPDATE `volume`";
    $q .= " SET `used_size` = `used_size` + $size";
    $q .= " WHERE `volume_id` = $volume_id";
    mysql_rw_execute($q, "api");
    $q = "UPDATE `storage`";
    $q .= " JOIN `volume` USING(`storage_id`)";
    $q .= " SET `storage`.`used_size` = `storage`.`used_size` + $size";
    $q .= " WHERE `volume`.`volume_id` = $volume_id";
    mysql_rw_execute($q, "api");
    commit_transaction("api");
    $response["success"] = TRUE;
    $response["response"] = NULL;
    echo json_encode($response);
    return TRUE;
}

# Handles schedule_backup event from agent
# server_id must be globally defined. 
# Input:
#   $request    Associative array with request parameters

function handler_schedule_backup($request) {
    global $m;
    global $server_id;

    if (strlen($server_id) != 36) {
        critical_api_error("Wrong or undefined server_id = $server_id");
    }
    $server_id = $m["rw"]->escape_string($server_id);
    $user_id = (int)get_user_id_by_server_id($server_id);
    
    # Exit if unknown server
    if ($user_id == -1) return FALSE;

    start_transaction();
    debug("Get schedules for server_id $server_id");
    $schedule = get_schedule_by_server_id($server_id);
    set_timezone($schedule["time_zone"], "api");
    date_default_timezone_set($schedule["time_zone"]);
    $timezone = new DateTimeZone($schedule["time_zone"]);
    $now = new DateTime('now', $timezone);
    $params = get_params($server_id, "api");
    add_job($now, $server_id, $params, "api"); 
    commit_transaction();
    $response["success"] = TRUE;
    $response["response"] = NULL;
    echo json_encode($response);
    return TRUE;
}

# Returns backup type as defined by job parameters
# Input:
#   $job_id   job_id
# Returns:
# "full" or "incremental"

function get_backup_type($job_id) {
    
    $job_id = (int)$job_id;

    $q = "SELECT `params` FROM `job`";
    $q .= " WHERE `job_id` = $job_id";

    $r = mysql_rw_execute($q, "api");
    if ( $r->num_rows > 0 ) {
        $row = $r->fetch_row();
        $params = json_decode($row[0]);
        switch ( $params->backup_type ) {
            case "full": return "full"; break;
            case "incremental": return "incremental"; break;
            default: critical_api_error("Unexpected backup_type '".$params->backup_type."' in job id $job_id");;
        }
    } else {
        critical_api_error("Can not find job id $job_id");
    }
}

# Handles report_agent_privileges event from agent
# server_id must be globally defined. 
# Input:
#   $request    Associative array with request parameters

function handler_report_agent_privileges($request) {
    global $m;
    global $server_id;

    if (strlen($server_id) != 36) {
        critical_api_error("Wrong or undefined server_id = $server_id");
    }
    $server_id = $m["rw"]->escape_string($server_id);
    $user_id = (int)get_user_id_by_server_id($server_id);
    
    # Exit if unknown server
    if ($user_id == -1) return FALSE;
    
    if ( isset($request->params->Reload_priv) ) {
        if ( $request->params->Reload_priv == "Y") {
            $Reload_priv = "Y";
        } else {
            $Reload_priv = "N";
        }
    } else {
        $Reload_priv = "NULL";
    }

    if ( isset($request->params->Lock_tables_priv) ) {
        if ( $request->params->Lock_tables_priv == "Y") {
            $Lock_tables_priv = "Y";
        } else {
            $Lock_tables_priv = "N";
        }
    } else {
        $Lock_tables_priv = "NULL";
    }

    if ( isset($request->params->Repl_client_priv) ) {
        if ( $request->params->Repl_client_priv == "Y") {
            $Repl_client_priv = "Y";
        } else {
            $Repl_client_priv = "N";
        }
    } else {
        $Repl_client_priv = "NULL";
    }

    if ( isset($request->params->Super_priv) ) {
        if ( $request->params->Super_priv == "Y") {
            $Super_priv = "Y";
        } else {
            $Super_priv = "N";
        }
    } else {
        $Super_priv = "NULL";
    }

    if ( isset($request->params->Create_tablespace_priv) ) {
        if ( $request->params->Create_tablespace_priv == "Y") {
            $Create_tablespace_priv = "Y";
        } else {
            $Create_tablespace_priv = "N";
        }
    } else {
        $Create_tablespace_priv = "NULL";
    }
    
    # Get existsing privileges for server_id
    $q = "SELECT Reload_priv, Lock_tables_priv, Repl_client_priv, Super_priv, Create_tablespace_priv FROM `server`";
    $q .= " WHERE `server_id` = '$server_id'";

    $r = mysql_rw_execute($q, "api");
    $priv_changed = FALSE;
    if ( $r->num_rows > 0 ) {
        $row = $r->fetch_assoc();
        if ( $row["Reload_priv"] != $Reload_priv ) $priv_changed = TRUE;
        if ( $row["Lock_tables_priv"] != $Lock_tables_priv ) $priv_changed = TRUE;
        if ( $row["Repl_client_priv"] != $Repl_client_priv ) $priv_changed = TRUE;
        if ( $row["Super_priv"] != $Super_priv ) $priv_changed = TRUE;
        if ( $row["Create_tablespace_priv"] != $Create_tablespace_priv ) $priv_changed = TRUE;
    }

    if ( $priv_changed ) {
        $q = "UPDATE `server` SET";
        $q .= " `Reload_priv` = ".( ($Reload_priv == "NULL") ? "NULL" : "'$Reload_priv'" ).",";
        $q .= " `Lock_tables_priv` = ".( ($Lock_tables_priv == "NULL") ? "NULL" : "'$Lock_tables_priv'" ).",";
        $q .= " `Repl_client_priv` = ".( ($Repl_client_priv == "NULL") ? "NULL" : "'$Repl_client_priv'" ).",";
        $q .= " `Super_priv` = ".( ($Super_priv == "NULL") ? "NULL" : "'$Super_priv'" ).",";
        $q .= " `Create_tablespace_priv` = ".( ($Create_tablespace_priv == "NULL") ? "NULL" : "'$Create_tablespace_priv'" );
        $q .= " WHERE `server_id` = '$server_id'";
        mysql_rw_execute($q, "api");
    }
    
    $response["success"] = TRUE;
    $response["response"] = NULL;
    echo json_encode($response);
    return TRUE;
}

# Handles is_registered event from agent
# server_id must be globally defined. 
# Input:
#   $request    Associative array with request parameters

function handler_is_registered($request) {
    global $m;
    global $server_id;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."checking incoming request");
    debug($request);
    if(!isset($server_id)){
        critical_api_error("server_id is not set");
    }
    if (!isset($request->params->enc_public_key)) {
        critical_api_error("is_registered request doesn't include GPG public key");
    }
    if (!add_gpg_key($request->params->enc_public_key)) {
        critical_api_error("Failed to import GPG public key from server $server_id");
    }
    $response["registered"] = is_server_registered( $server_id );

    $data["success"] = TRUE;
    $data["response"] = response($response, $server_id);
    echo json_encode($data);
    return TRUE;
}

function is_server_registered($server_id) {
    global $m;

    $server_id = $m["rw"]->escape_string($server_id);

    $q = "SELECT `server_id`";
    $q .= " FROM `server`";
    $q .= " WHERE `server_id` = '$server_id'";
    $q .= " AND `registered` = 'Yes'";
    $q .= " AND `registration_confirmed` = 'Yes'";
    $r = mysql_rw_execute($q, "api");

    return $r->num_rows > 0 ? TRUE : FALSE;
}

function mysql_server_id_exists($user_id, $mysql_server_id) {

    $user_id = (int)$user_id;
    $mysql_server_id = (int)$mysql_server_id;

    $q = "SELECT server_id FROM server " .
        " WHERE user_id = $user_id " .
        " AND mysql_server_id = $mysql_server_id" .
        " AND registered = 'Yes'";
    $r = mysql_rw_execute($q, "api");

    return $r->num_rows > 0 ? TRUE : FALSE;
}

# Handles send_key request from agent
# server_id must be globally defined. 
# Input:
#   $request    Associative array with request parameters

function handler_send_key($request) {
    global $m;
    global $server_id;

    if (strlen($server_id) != 36) {
        critical_api_error("Wrong or undefined server_id = $server_id");
    }
    $server_id = $m["rw"]->escape_string($server_id);
    $user_id = (int)get_user_id_by_server_id($server_id);
    
    # Exit if unknown server
    if ($user_id == -1) return FALSE;
    
    $job_id = (int)$request->params->job_id;
    if( $user_id != get_user_id_by_job_id($job_id, "api")) {
        return FALSE;
    }
    
    $enc_private_key = $m["rw"]->escape_string($request->params->enc_private_key);
    
    start_transaction("api");
    $q = "UPDATE `server`";
    $q .= " SET `enc_private_key` = '$enc_private_key'";
    $q .= " WHERE `server_id` = '$server_id'";
    mysql_rw_execute($q, "api");
    $q = "UPDATE `job`";
    $q .= " SET `start_actual` = NOW(), `finish_actual` = NOW(),";
    $q .= " `status` = 'Finished'";
    $q .= " WHERE `job_id` = $job_id";
    mysql_rw_execute($q, "api");
    commit_transaction("api");
    $response["success"] = TRUE;
    $response["response"] = NULL;
    echo json_encode($response);
    return TRUE;
}

# Handles get_backups_chain request from agent
# server_id must be globally defined. 
# Input:
#   $request    Associative array with request parameters
function handler_get_backups_chain($request) {
    global $m;
    global $server_id;

    if (strlen($server_id) != 36) {
        critical_api_error("Wrong or undefined server_id = $server_id");
    }
    $server_id = $m["rw"]->escape_string($server_id);
    $user_id = (int)get_user_id_by_server_id($server_id);
    
    # Exit if unknown server
    if ($user_id == -1) return FALSE;
    
    $backup_copy_id = (int)$request->params->backup_copy_id;
    if($user_id != get_user_id_by_backup_copy_id($backup_copy_id, "api")) {
        return FALSE;
    }
    $q = "SELECT";
    $q .= " `backup_copy`.`backup_copy_id`,";
    $q .= " `backup_copy`.`name`,";
    $q .= " `storage`.`ip`,";
    $q .= " IF(`backup_copy`.`full_backup` = 'Y', TRUE, FALSE) AS `full`";
    $q .= " FROM `backup_copy_tree`";
    $q .= " JOIN `backup_copy`";
    $q .= " ON `backup_copy`.`backup_copy_id` = `backup_copy_tree`.`ancestor`";
    $q .= " JOIN `volume` USING(`volume_id`)";
    $q .= " JOIN `storage` USING(`storage_id`)";
    $q .= " WHERE `backup_copy_tree`.`descendant` = $backup_copy_id";
    $q .= " ORDER BY `backup_copy_tree`.`length` DESC";
    $r = mysql_rw_execute($q, "api");
    $result = array();
    while ($row = $r->fetch_assoc()) {
        array_push($result, $row);
    }
    $response["success"] = TRUE;
    $response["response"] = response($result, $server_id);
    echo json_encode($response);
    return TRUE;
}

# Handles get_child_copy_params request from agent
# server_id must be globally defined. 
# Input:
#   $request    Associative array with request parameters
function handler_get_child_copy_params($request) {
    global $m;
    global $server_id;

    if (strlen($server_id) != 36) {
        critical_api_error("Wrong or undefined server_id = $server_id");
    }
    $server_id = $m["rw"]->escape_string($server_id);
    $user_id = (int)get_user_id_by_server_id($server_id);
    
    # Exit if unknown server
    if ($user_id == -1) return FALSE;
    
    $backup_copy_id = (int)$request->params->backup_copy_id;
    if($user_id != get_user_id_by_backup_copy_id($backup_copy_id, "api")) {
        return FALSE;
    }
    $q = "SELECT";
    $q .= " `backup_copy`.`backup_copy_id`,";
    $q .= " `backup_copy`.`name`,";
    $q .= " `storage`.`ip`";
    $q .= " FROM `backup_copy_tree`";
    $q .= " JOIN `backup_copy`";
    $q .= " ON `backup_copy`.`backup_copy_id` = `backup_copy_tree`.`descendant`";
    $q .= " JOIN `volume` USING(`volume_id`)";
    $q .= " JOIN `storage` USING(`storage_id`)";
    $q .= " WHERE `backup_copy_tree`.`ancestor` = $backup_copy_id";
    $q .= " AND `backup_copy_tree`.`length` = 1";
    $r = mysql_rw_execute($q, "api");
    $row = $r->fetch_assoc();
    $response["success"] = TRUE;
    $response["response"] = response($row, $server_id);
    echo json_encode($response);
    return TRUE;
}

# Finds servers that don't have private GPG key
# Returns:
#   Arrays of server_id
#   Example:
#   [ "42486b74-5eb0-45b5-a4da-6f3d6dabaa1e", "781e50c7-503d-4663-8393-c8165ce908db" ]
function get_servers_without_private_key() {

    $servers = Array();
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Getting servers without private GPG key");

    $q = "SELECT `server_id`";
    $q .= " FROM `server`";
    $q .= " WHERE `enc_private_key` IS NULL FOR UPDATE";
    # This query is a part of transaction, so must be sent to the master
    $r = mysql_rw_execute($q);

    while ($row = $r->fetch_row()) {
        $server_id = $row[0];
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Server $server_id doesn't have private GPG key");
        array_push($servers, $server_id);
    }
    $r->free();
    return $servers;
}

# Returns TRUE if there is a scheduled send_key job
function scheduled_send_key_job($server_id) {
    global $m;

    $server_id = $m["ro"]->escape_string($server_id);

    $q = "SELECT COUNT(*)";
    $q .= " FROM `job`";
    $q .= " WHERE `server_id` = '$server_id'";
    $q .= " AND `type` = 'send_key'";
    $q .= " AND `status` = 'Scheduled'";
    $r = mysql_rw_execute($q);
    
    $row = $r->fetch_row();
    if ($row[0] > 0) {
        return TRUE;
    }
    $r->free();
    return FALSE;
}
# Schedules send_key job
# Input:
#   $server_id  - server_id
# Returns nothing
function schedule_send_key_job($server_id) {
    global $m;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Scheduling send_key job for ");
    $server_id = $m["ro"]->escape_string($server_id);

    $q = "SELECT `user`.`gpg_pub_key`";
    $q .= " FROM `user`";
    $q .= " JOIN `server` USING(`user_id`)";
    $q .= " WHERE `server`.`server_id` = '$server_id'";
    $q .= " AND `user`.`gpg_pub_key` IS NOT NULL";
    $r = mysql_rw_execute($q);

    $params = new stdClass();

    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        $gpg_pub_key = $row[0];
        $params->gpg_pub_key = "$gpg_pub_key";
        if (strlen($gpg_pub_key) == 0) {
            vlog("Empty GPG public key for server_id $server_id");
            vlog("Won't schedule a send_key job for it");
            return;
        }
        $params_str = $m["rw"]->escape_string(json_encode($params));
        $q = "INSERT INTO `job`".
            " (`server_id`, `type`, `start_scheduled`, `params`)".
            " VALUES('$server_id', 'send_key', NOW(), '$params_str')";
        mysql_rw_execute($q);
    } else {
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."User of server $server_id doesn't have GPG public key");
    }
    $r->free();
    return;
}

# Handles handler_confirm_registration request from agent
# server_id must be globally defined.
# Input:
#   $request    Associative array with request parameters
function handler_confirm_registration($request) {
    global $m;
    global $server_id;

    if (strlen($server_id) != 36) {
        critical_api_error("Wrong or undefined server_id = $server_id");
    }
    $server_id = $m["rw"]->escape_string($server_id);
    $user_id = (int)get_user_id_by_server_id($server_id);

    # Exit if unknown server
    if ($user_id == -1) return FALSE;

    $q = "UPDATE `server` SET `registration_confirmed` = 'Yes' WHERE `server_id` = '$server_id'";
    $r = mysql_rw_execute($q, "api");
    $response["success"] = TRUE;
    $response["response"] = NULL;
    echo json_encode($response);
    return TRUE;
}


# EOF functions

?>
