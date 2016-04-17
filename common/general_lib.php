<?php
# Functions
function vlog($msg, $debug=FALSE) {
    $prefix = "";
    if (php_sapi_name() === "cli") {
        $prefix = date("c").":";
    }
    if ($debug) $prefix = $prefix." DEBUG:";
    if (is_array($msg) || is_object($msg)) {
        error_log(print_r($msg, TRUE));
    } else {
        error_log("$prefix $msg");
    }
    return;
}

function debug($msg) {
    global $api_debug;

    if ($api_debug) vlog($msg, TRUE);
}

function critical_error($msg, $msg_debug = NULL, $caller = NULL) {
    vlog($msg);
    switch($caller){
        case "api":
            critical_api_error($msg, $msg_debug);
            break;
        case "www":
            critical_www_error($msg, $msg_debug);
            break;
    }
    exit();
}

function critical_www_error($msg, $msg_debug = NULL) {
    $msg = str_replace("\n", "<p>", $msg);
    $msg_debug = str_replace("\n", "<p>", $msg_debug);
    $response["success"] = FALSE;
    $response["errors"]["msg"] = $msg;
    $response["errors"]["msg_debug"] = $msg_debug;
    echo json_encode($response);
    exit();
}

function critical_api_error($msg, $msg_debug = NULL) {
    global $server_id;

    vlog($msg);
    debug($msg_debug);
    if (isset($server_id)) {
        $response["success"] = FALSE;
        $response["response"] = response(NULL, $server_id, $msg, $msg_debug);
        echo json_encode($response);
    } else {
        vlog("server_id is not set. Error message won't be sent to an agent");
    }
    exit();
}

# Reads config and sets config variables
function read_config() {
    global $config;
    $variables = "variables.php";
    $file = file_get_contents($variables, TRUE);
    if ($file === FALSE) {
        vlog("Failed to load list of variables from file $variables");
        vlog("Current include path: ".get_include_path());
        return FALSE;
    } else {
        preg_match_all('/(\$)([A-Za-z0-9-_]+)/', $file, $vars);
        include($config);
        foreach($vars[2] as $var){
            if(isset($$var)) {
                if(!is_array(${$var})) {
                    $value = ${$var};
                } else {
                    $value = print_r(${$var}, TRUE);
                }
                debug(basename(__FILE__) . ": " . __LINE__ . ": " . __FUNCTION__ . "(): " . "Adding $var with value $value");
                $GLOBALS["$var"] = $$var;
            }
        }
        return TRUE;
    }
}

# Authenticates user
# returns user_id

function get_user() {
    global $demo;
    $user_id = 1; // demo user
    if(session_start() == FALSE){
        critical_www_error("get_user(): failed to create a session");
    }
    if (isset($_SESSION["auth"])) {
        debug(__FUNCTION__."(): enable normal(not demo) mode");
        $demo = false;
        $user_id = $_SESSION["user_id"];
    } else {
        debug(__FUNCTION__."(): enable demo mode");
        $demo = true;
    }
    debug(__FUNCTION__."(): returning user $user_id");
    return (int)$user_id;
}

function h_size($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = (int)floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}

function get_salt($email){
    return crypt($email, "$6$".md5($email));
}

function schedule_backup_job($server_id, $caller = NULL) {

    // Get parameters to schedule a job (storage, etc)
    $params = get_params($server_id);
    debug("Get schedules for server_id $server_id");
    $schedule = get_schedule_by_server_id($server_id);
    set_timezone($schedule["time_zone"]);
    date_default_timezone_set($schedule["time_zone"]);
    $timezone = new DateTimeZone($schedule["time_zone"]);
    $now = new DateTime('now', $timezone);
    $last_backup_job_time = get_last_successfull_backup_job_time($server_id, $caller);
    switch ($schedule["frequency_unit"]){
        case "Hour":
            $backup_period = $schedule["ntimes"] * 3600;
            break;
        case "Day":
            $backup_period = $schedule["ntimes"] * 3600 * 24;
            break;
        case "Week":
            $backup_period = $schedule["ntimes"] * 3600 * 24 * 7;
            break;
        default:
            $backup_period = 1; // every second. It should never happen, but to be on safe side let's define it
    }
    if ($last_backup_job_time != 0) {
        $next_backup = new DateTime();
        $next_backup->setTimestamp($last_backup_job_time + $backup_period);
    } else {
        $date = new DateTime('now', $timezone);
        $start_time = explode(":", $schedule["start_time"]);
        $date->setTime($start_time[0], $start_time[1], $start_time[2]);
        $next_backup = $date;
    }
    add_job($next_backup, $server_id, $params, $caller);
}

function get_last_successfull_backup_job_time($server_id, $caller=NULL) {
    global $m;
    $server_id = $m["rw"]->escape_string($server_id);
    $q = "SELECT UNIX_TIMESTAMP(`start_scheduled`)
          FROM `job`
          WHERE `server_id` = '$server_id'
          AND `type` = 'backup'
          AND `status` = 'Finished'
          ORDER BY `start_scheduled` DESC
          LIMIT 1";
    $r = mysql_rw_execute($q, $caller);
    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        $last_backup = (int)$row[0];
        debug("Last time backup from server $server_id was scheduled on ".date("r", $last_backup));
        return $last_backup;
    }
    debug("Backup from server $server_id was never scheduled");
    return 0;
}

function get_params($server_id, $caller = NULL) {

    global $m;

    $params = NULL;

    $server_id = $m["ro"]->escape_string($server_id);

    $q = "SELECT ";
    $q .= " `storage`.`type`,";
    $q .= " `storage`.`params`,";
    $q .= " `volume`.`volume_id`";
    $q .= " FROM `server`";
    $q .= " JOIN `config` USING(`config_id`)";
    $q .= " JOIN `volume` USING(`volume_id`)";
    $q .= " JOIN `storage` USING(`storage_id`)";
    $q .= " WHERE `server_id` = '$server_id'";

    $r = mysql_rw_execute($q, $caller);
    if ($r->num_rows > 0) {
        $row = $r->fetch_assoc();
        $params = json_decode($row["params"]);
        $params->type = $row["type"];
        $params->volume_id = $row["volume_id"];
        $next_backup_params = get_next_backup_type($server_id);
        $params->backup_type = $next_backup_params["next_backup_type"]; // 'full' or 'incremental'
        $params->lsn = $next_backup_params["lsn"]; // LSN of the last backup copy
        $params->ancestor = $next_backup_params["ancestor"]; // backup_copy_id of the provious backup (if incremental)
        $params = json_encode($params);
    } else {
        $params = NULL;
    }
    $r->free();
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."returning job params $params");

    return $params;
}

function get_schedule_by_server_id($server_id, $caller = NULL) {

    global $m;
    $schedule = NULL;

    $server_id = $m["ro"]->escape_string($server_id);
    $q = "SELECT";
    $q .= " `schedule`.`start_time`,";
    $q .= " `schedule`.`ntimes`,";
    $q .= " `schedule`.`frequency_unit`,";
    $q .= " `schedule`.`full_copy`,";
    $q .= " `schedule`.`time_zone`";
    $q .= " FROM `server`";
    $q .= " JOIN `config` USING(`config_id`)";
    $q .= " JOIN `schedule` USING(`schedule_id`)";
    $q .= " WHERE `server`.`server_id` = '$server_id'";
    $schedules = mysql_rw_execute($q, $caller);

    debug("Found ". $schedules->num_rows." schedule(s)");
    if ($schedules->num_rows > 0) {
        $schedule = $schedules->fetch_assoc();
    } else {
        critical_error("Couldn't find schedule for server id $server_id");
    }
    $schedules->free();

    return $schedule;
}

function get_servers($cluster_id, $caller = NULL) {
    $cluster_id = (int)$cluster_id;

    $q = "SELECT ";
    $q .= " `server`.`server_id`,";
    $q .= " `server`.`mysql_server_id`,";
    $q .= " `server`.`mysql_master_server_id`";
    $q .= " FROM `server` ";
    $q .= " WHERE `cluster_id` = $cluster_id";

    $r = mysql_rw_execute($q, $caller);
    debug("Found ".$r->num_rows." servers");
    
    $servers = array();
    while($row = $r->fetch_assoc()) {
        $row["visited"] = FALSE;
        $servers[$row["mysql_server_id"]] = $row;
    }
    $r->free();

    return $servers;
}

function add_job($date, $server_id, $params, $caller = NULL) {
 
    global $m;

    $server_id = $m["rw"]->escape_string($server_id);
    $params = $m["rw"]->escape_string($params);

    $q = "INSERT INTO `job` (`server_id`, `start_scheduled`, `params`)
    VALUES ('$server_id', FROM_UNIXTIME(".$date->format("U")."), '$params')";
    mysql_rw_execute($q, $caller);
    return;
}

function discover_replication_topology() {
    discover_new_clusters();
    return TRUE; 
}

function discover_new_clusters($caller = NULL) {

    $q = "SELECT DISTINCT `user_id` FROM `server` WHERE `cluster_id` IS NULL or `role` IS NULL";
    $r = mysql_rw_execute($q, $caller);
    while ($user = $r->fetch_assoc()) {
        $user_id = $user["user_id"];
        discover_new_clusters_for_user($user_id);
    }
    return TRUE;
}

function discover_new_clusters_for_user($user_id, $caller = NULL){
    $user_id = (int)$user_id;

    # Reset cluster id for all servers
    reset_cluster_id($user_id, $caller);

    # Delete orphaned clusters
    purge_unused_clusters($caller);

    while (TRUE) {
        $q = "SELECT";
        $q .= " `cluster_id`,";
        $q .= " `server_id`,";
        $q .= " `mysql_server_id`,";
        $q .= " `mysql_master_server_id`";
        $q .= " FROM `server`";
        $q .= " WHERE `user_id` = $user_id";
        $q .= " AND `cluster_id` IS NULL";
        $q .= " AND ( `mysql_slave_io_running` = 'Yes'";
        $q .= "     OR `mysql_slave_io_running` IS NULL )";
        $q .= " FOR UPDATE";
        $servers = mysql_rw_execute($q, $caller);
        if ($servers->num_rows > 0) {
            $server = $servers->fetch_assoc();
            $cluster_id = create_new_cluster();
            // Traverses through a replication cluster and sets cluster_id
            $params["cluster_id"] = $cluster_id;
            traverse_graph($server, "assign_cluster_id", $params);
        }
        if ($servers->num_rows == 0) break;
        $servers->free();
    }
    return;
}

function traverse_graph($server, $callback, $callback_params) {
    # Call function on a graph node
    call_user_func($callback, $server, $callback_params);

    foreach (get_adjacent_servers($server) as $v) {
        if (!visited_node($v)) {
            traverse_graph($v, $callback, $callback_params);
        }
    }
    return;   
}

function assign_cluster_id($server, $params, $caller = NULL) {

    global $m;

    $cluster_id = (int)$params["cluster_id"];    
    $server_id = $m["rw"]->escape_string($server["server_id"]);
    $mysql_server_id = (int)$server["mysql_server_id"];

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "." traversing graph starting from $server_id ($mysql_server_id)");

    $q = "UPDATE `server`";
    $q .= " SET `cluster_id` = $cluster_id";
    $q .= " WHERE `server_id` = '$server_id'";
    mysql_rw_execute($q, $caller);
    
    return;
}

function get_adjacent_servers($server, $caller = NULL) {

    global $m;

    $result = Array();
    $server_id = $m["rw"]->escape_string($server["server_id"]);
    $mysql_server_id = (int)$server["mysql_server_id"];
    $mysql_master_server_id = (int)$server["mysql_master_server_id"];
    
    $q = "SELECT";
    $q .= " `cluster_id`,";
    $q .= " `server_id`,";
    $q .= " `mysql_server_id`,";
    $q .= " `mysql_master_server_id`";
    $q .= " FROM `server`";
    $q .= " WHERE (`mysql_server_id` = $mysql_master_server_id AND `mysql_server_id` <> 0)"; # master of $server
    $q .= " OR `mysql_master_server_id` = $mysql_server_id";   # all slaves of $server
    $q .= " AND `mysql_slave_io_running` = 'YES'"; # mysql_master_server_id is reliable only if IO thread is running
    $servers = mysql_rw_execute($q, $caller);

    while($row = $servers->fetch_assoc()){
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."found adjacent server to $server_id");
        $row["visited"] = ($row["cluster_id"] == "") ? FALSE : TRUE;
        debug($row);
        array_push($result, $row);
    }
    return $result;
}

function visited_node($node) {
    return $node["visited"];
}

function create_new_cluster($caller = NULL) {

    global $m;

    $q = "INSERT INTO `cluster` (`cluster_id`) VALUES(NULL)";
    mysql_rw_execute($q, $caller);
    return $m["insert_id"];
}

function reset_cluster_id($user_id, $caller = NULL) {
    $q = "UPDATE `server`";
    $q .= " SET `cluster_id` = NULL";
    $q .= " WHERE `user_id` = $user_id";
    mysql_rw_execute($q, $caller);
}

function purge_unused_clusters($caller = NULL) {
    $q = "DELETE `cluster`";
    $q .= " FROM `cluster`";
    $q .= " LEFT JOIN `server` USING(`cluster_id`)";
    $q .= " WHERE `server_id` IS NULL";
    mysql_rw_execute($q, $caller);
}
function pickup_server_for_backup($servers, $cluster_id, $caller = NULL) {
    global $m;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Need to pick up a server for a job from these:");
    debug($servers);

    if (!is_array($servers)) {
        vlog("$servers is not an array");
        return NULL;
    }
    if (count($servers) == 0) {
        vlog("Array $servers is empty");
        return NULL;
    }
    // Make sure $master is always non NULL
    $servers_values = array_values($servers);
    $master = $servers_values[0];

    $cluster_id = (int)$cluster_id;
    // get leaf slaves
    $q = "SELECT";
    $q .= " `A`.`server_id`,";
    $q .= " `A`.`mysql_server_id`,";
    $q .= " `A`.`mysql_master_server_id`";
    $q .= " FROM `server` `A`";
    $q .= " LEFT JOIN `server` `B` ON `A`.`mysql_server_id` = `B`.`mysql_master_server_id`";
    $q .= " WHERE `A`.`cluster_id` = $cluster_id AND `B`.`mysql_server_id` IS NULL";
    $q .= " LIMIT 1";
    $leaf_slaves = mysql_rw_execute($q, $caller);
    $s = $leaf_slaves->fetch_assoc();
    
    $slaves_path = array();
    $acylic = True;
    while ($s["mysql_master_server_id"] != 0) {
        if (!slave_in_path($slaves_path, $s)) {
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Visiting ".$s["mysql_server_id"]);
            debug($s);
            array_push($slaves_path, $s);
            // move one server up
            $s = $servers[$s["mysql_master_server_id"]];
        } else {
            // we found a cycle
            $acylic = False;
            break;
        }
    }
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Slaves path:");
    debug($slaves_path);
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Last slave");
    debug($s);
    $masters = array();
    if ($acylic) {
        // last server in replication chain is a master
        array_push($masters, $s);
    } else {
        // Last slave in the path is the master
        array_push($masters, $s);
        $x = array_pop($slaves_path);
        while ($x != $s) {
            array_push($masters, $x);
            $x = array_pop($slaves_path);
        }
    }
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Masters:");
    debug($masters);
    $master_list = "(";
    $master_list_empty = True;
    $comma = "";
    if (count($masters) == 0) {
        vlog("Could not find any masters in cluster id $cluster_id");
        return NULL;
    }
    // Set all servers as slaves
    $q = "UPDATE `server` SET `role` = 'slave' WHERE `cluster_id` = '$cluster_id'";
    mysql_rw_execute($q, $caller);
    foreach ($masters as $master) {
        if ($master["mysql_server_id"] != "") { 
            $master_list .= "$comma ".$master["mysql_server_id"];
            $comma = ",";
            $master_list_empty = False;
            $server_id = $m["rw"]->escape_string($master["server_id"]);
            $q = "UPDATE `server` SET `role` = 'master' WHERE `server_id` = '$server_id'";
            mysql_rw_execute($q, $caller);
        }
    }
    $master_list .= ")";

    // Now, when we have masters in our replication cluster we can find their immediate slaves.
    $q = "SELECT";
    $q .= " `server_id`,";
    $q .= " `mysql_server_id`,";
    $q .= " `mysql_master_server_id`,";
    $q .= " FALSE AS `visited`";
    $q .= " FROM `server`";
    $q .= " WHERE `cluster_id` = $cluster_id";
    if (!$master_list_empty) {
        $q .= " AND mysql_master_server_id IN $master_list";    // Select immediate slaves of the masters
        $q .= " AND mysql_server_id NOT IN $master_list";       // But skip masters in master<-> master topologies
    }
    $q .= " AND `mysql_slave_io_running` = 'Yes'";
    $q .= " AND `mysql_slave_sql_running` = 'Yes'";
    $q .= " AND `last_seen_at` > DATE_SUB(NOW(), INTERVAL 10 MINUTE)"; // Select only online servers
    $q .= " ORDER BY `mysql_seconds_behind_master`";
    $r = mysql_rw_execute($q, $caller);

    // Array of server_id
    $slaves = array();
    while($row = $r->fetch_row()) {
        array_push($slaves, $row[0]);
    }
    if (count($slaves) == 0) {
        // return the last known master if no appropriate slaves were found
        return $master["server_id"];
    }
    $last_backup_server_id = get_last_backup_server_id($cluster_id);

    // Prefer server if the last backup was taken from it
    foreach ($slaves as $slave) {
        if ($slave == $last_backup_server_id) {
            return $slave;
        }
    }
    
    // We could not find server we took the last backup from
    $slaves_left = count($slaves);
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): There are $slaves_left candiadate slaves");
    foreach ($slaves as $slave) {
        $slaves_left--;
        // if job hasn't started after it was scheduled, skip the server
        // For example, if the schedule tells to take backups every hour
        // and backup job hasn't started two hours after it's scheduled - skip this slave.
        if (missed_job($slave)) {
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): Slave $slave missed a job.");
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): Will skip this slave and fail all its jobs");
            fail_not_started_jobs($slave);
            continue;
        }
        // if it is not the last slave and its last job has failer - better skip it.
        // But if the last job for whole cluster was successful give the slave a chance to do it
        if (last_job_status($slave) == "Failed" && last_job_status_cluster($cluster_id) == "Failed") {
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): Slave $slave failed last job.");
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): Will skip it");
            continue;
        }
        return $slave;
    }

    // could not find any slaves, return the master
    return $master["server_id"];
}

function missed_job($server_id, $caller = NULL) {
    global $m;

    $result = FALSE;
    $server_id = $m["ro"]->escape_string($server_id);
    $q = "SELECT";
    $q .= " UNIX_TIMESTAMP(NOW()) AS `now`,";
    $q .= " UNIX_TIMESTAMP(`job`.`start_scheduled`) AS `start_scheduled`,";
    $q .= " `schedule`.`ntimes`,";
    $q .= " `schedule`.`frequency_unit`";
    $q .= " FROM `job`";
    $q .= " JOIN `server` USING(`server_id`)";
    $q .= " JOIN `config` USING(`config_id`)";
    $q .= " JOIN `schedule` USING(`schedule_id`)";
    $q .= " WHERE `server`.`server_id` = '$server_id'";
    $q .= " AND `job`.`type` = 'backup'";
    $q .= " AND `job`.`status` = 'Scheduled'";
    $r = mysql_rw_execute($q, $caller);
    if ($r->num_rows > 0) {
        $row = $r->fetch_assoc();
        switch ($row["frequency_unit"]) {
            case "Hour": $window = 3600; break;
            case "Day": $window = 3600 * 24; break;
            case "Week": $window = 3600 * 24 * 7; break;
        }
        $job_period = (int)$row["ntimes"] * $window;
        if (((int)$row["now"] - (int)$row["start_scheduled"]) > $job_period * 2) {
            $msg = "Server id $server_id missed backup job.";
            $msg .= " It was scheduled at ".date(DATE_RFC1036, $row["start_scheduled"]).",";
            $msg .= " but now ".date(DATE_RFC1036, $row["now"])." and it hasn't started yet.";
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): ".$msg); 
            return TRUE;
        } 
    }
    return $result;

}
function fail_not_started_jobs($server_id, $caller = NULL) {
    global $m;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): Mark all Scheduled jobs for server id $server_id as Failed");
    $server_id = $m["rw"]->escape_string($server_id);
    $q = "UPDATE `job`";
    $q .= " SET `finish_actual` = NOW(),";
    $q .= " `status` = 'Failed'";
    $q .= " WHERE `server_id` = '$server_id'";
    $q .= " AND `status` = 'Scheduled'";
    mysql_rw_execute($q, $caller);
}

function last_job_status($server_id, $caller = NULL) {
    global $m;

    $server_id = $m["ro"]->escape_string($server_id);
    $q = "SELECT `status` FROM `job`";
    $q .= " WHERE `server_id` = '$server_id'";
    $q .= " AND `finish_actual` IS NOT NULL";
    $q .= " ORDER BY `finish_actual` DESC";
    $q .= " LIMIT 1";
    $r = mysql_rw_execute($q, $caller);
    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        return $row[0];
    }
    return NULL;
}

function last_job_status_cluster($cluster_id, $caller = NULL) {
    global $m;

    $cluster_id = (int)$cluster_id;
    $q = "SELECT `status`"
        . " FROM `job`"
        . " JOIN `server` USING(`server_id`) "
        . " WHERE `server`.`cluster_id` = $cluster_id "
        . " AND `finish_actual` IS NOT NULL "
        . " ORDER BY job_id DESC LIMIT 1";
    $r = mysql_rw_execute($q, $caller);
    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        return $row[0];
    }
    return NULL;
}

function slave_in_path($slaves_path, $slave) {
    
    foreach ($slaves_path as $s) {
        if ($s["server_id"] == $slave["server_id"]) {
            return True;
        }
    }
    return False;
}

function get_clusters($caller = NULL) {

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Getting list of clusters with at least one server"); 
    $q = "SELECT";
    $q .= " `cluster_id`,";   
    $q .= " COUNT(`server_id`) AS `c`";   
    $q .= " FROM `cluster`";   
    $q .= " JOIN `server` USING (`cluster_id`)";
    $q .= " GROUP BY `cluster_id`";
    $q .= " HAVING `c` > 0";
    $r = mysql_rw_execute($q, $caller);
    $clusters = array();
    while($row = $r->fetch_assoc()) {
        array_push($clusters, $row);
    }
    $r->free();
    
    return $clusters;
}

# returns true if cluster has a scheduled backup job
# Input:
#   $server_id  -   Any server from the cluster in question
# Returns:
#   TRUE or FALSE depending on whether there is a scheduled job for this cluster

function job_scheduled($server_id, $caller = NULL) {

    $q = "SELECT `cluster_id` FROM `server` WHERE `server_id` = '$server_id'";
    $r = mysql_rw_execute($q, $caller);
    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        $cluster_id = (int)$row[0];
    } else {
        return False;
    }
    $q = "SELECT COUNT(*) ";
    $q .= " FROM `job`";
    $q .= " JOIN `server` USING (`server_id`)";
    $q .= " WHERE `server`.`cluster_id` = $cluster_id";
    $q .= " AND `job`.`type` = 'backup'";
    $q .= " AND `job`.`status` = 'Scheduled'";
    $r = mysql_rw_execute($q, $caller);
    $row = $r->fetch_row();
    if ($row[0] > 0) {
        return True;
    }
    return False;
}

# tells whether a next backup should be 'full' or 'incremental'
function get_next_backup_type($server_id, $caller = NULL) {
    global $m;

    $result["next_backup_type"] = "full";
    $result["lsn"] = "0";
    $result["ancestor"] = 0;
    
    $server_id = $m["ro"]->escape_string($server_id);
    # Get time of the last successful full backup for the given cluster
    $q = "SELECT UNIX_TIMESTAMP(`job`.`finish_actual`),";
    $q .= " `job`.`finish_actual`,";
    $q .= " `backup_copy`.`lsn`,";
    $q .= " `backup_copy`.`backup_copy_id`";
    $q .= " FROM `backup_copy`";
    $q .= " JOIN `job` USING(`job_id`)";
    $q .= " JOIN `server` USING (`server_id`)";
    $q .= " WHERE `server`.`server_id` = '$server_id'";
    $q .= " AND `backup_copy`.`full_backup` = 'Y'";
    $q .= " AND `job`.`status` = 'Finished'";
    $q .= " ORDER BY `job`.`finish_actual` DESC";
    $q .= " LIMIT 1";
    $r = mysql_rw_execute($q, $caller);
    
    if ( $r->num_rows > 0 ) {
        $row = $r->fetch_row();
        $last_full = $row[0];
        $last_full_str = $row[1];
        $last_lsn = $row[2];
        $ancestor = $row[3];
        $now = time();
        $full_interval = get_full_interval($server_id);
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Full backup interval: $full_interval");
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Last full backup was taken on $last_full_str");
        switch ($full_interval) {
            case "Daily":
                if ( ($now - $last_full) < 24*3600 ) {
                    $result["next_backup_type"] = "incremental";
                    $result["lsn"] = $last_lsn;
                    $result["ancestor"] = $ancestor;
                }
                break;
            case "Weekly":
                if ( ($now - $last_full) < 7*24*3600 ) {
                    $result["next_backup_type"] = "incremental";
                    $result["lsn"] = $last_lsn;
                    $result["ancestor"] = $ancestor;
                }
                break;
            case "Monthly":
                if ( ($now - $last_full) < 30*7*24*3600 ) {
                    $result["next_backup_type"] = "incremental";
                    $result["lsn"] = $last_lsn;
                    $result["ancestor"] = $ancestor;
                }
                break;
            case "Quarterly":
                if ( ($now - $last_full) < 3*30*7*24*3600 ) {
                    $result["next_backup_type"] = "incremental";
                    $result["lsn"] = $last_lsn;
                    $result["ancestor"] = $ancestor;
                }
                break;
            case "Yearly":
                if ( ($now - $last_full) < 365*24*3600 ) {
                    $result["next_backup_type"] = "incremental";
                    $result["lsn"] = $last_lsn;
                    $result["ancestor"] = $ancestor;
                }
                break;
            default:
            $result["next_backup_type"] = "full";
            $result["lsn"] = 0;
            $result["ancestor"] = 0;
        }
    } 
    return $result;
}

function get_cluster_id($server_id, $caller = NULL) {
    global $m;
    $cluster_id = 0;    

    $server_id = $m["ro"]->escape_string($server_id);
    $q = "SELECT `cluster_id` FROM `server` WHERE `server_id` = '$server_id'";
    $r = mysql_rw_execute($q, $caller);
    if($r->num_rows > 0) {
        $row = $r->fetch_row();
        $cluster_id = $row[0];
    }
    return (int)$cluster_id;
}

function get_full_interval($server_id, $caller = NULL) {
    global $m;
    $full_interval = NULL;

    $server_id = $m["ro"]->escape_string($server_id);
    $q = "SELECT `schedule`.`full_copy`";
    $q .= " FROM `schedule`";
    $q .= " JOIN `config` USING(`schedule_id`)";
    $q .= " JOIN `server` USING (`config_id`)";
    $q .= " WHERE `server`.`server_id` = '$server_id'";
    $r = mysql_rw_execute($q, $caller);
    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        $full_interval = $row[0];
    }
    return $full_interval;
}

function get_last_backup_server_id($cluster_id, $caller = NULL) {
    $server_id = NULL;

    $cluster_id = (int)$cluster_id;
    $q = "SELECT `server`.`server_id`";
    $q .= " FROM `backup_copy`";
    $q .= " JOIN `job` USING (`job_id`)";
    $q .= " JOIN `server` USING (`server_id`)";
    $q .= " WHERE `server`.`cluster_id` = $cluster_id";
    $q .= " ORDER BY `backup_copy`.`backup_copy_id`";
    $q .= " DESC LIMIT 1";
    $r = mysql_rw_execute($q, $caller);
    
    if ( $r->num_rows > 0 ) {
        $row = $r->fetch_row();
        $server_id = $row[0];
    }
    return $server_id;
}

// Converts grid filter from JSON format into SQL string
function getFilterClause($filter_json) {
    global $m;

    $filter_sql = "";
    $filter = json_decode($filter_json, TRUE);
    foreach ($filter as $filter_item) {
        $field = $m["ro"]->escape_string($filter_item["field"]);
        if (strstr($field, "`") == FALSE) {
            $field = "`$field`";
        }
        if ( $filter_item["type"] == "string" ) {
            $value = $m["ro"]->escape_string( $filter_item["value"] );
            $filter_sql .= " AND $field LIKE '%$value%'";
        } elseif ( $filter_item["type"] == "list" ) {
            $filter_sql .= " AND $field IN (";
            $comma = "";
            foreach ($filter_item["value"] as $value_item ) {
                $value_item = $m["ro"]->escape_string( $value_item );
                $filter_sql .= "$comma '$value_item'";
                $comma = ",";
            }
            $filter_sql .= ")";

        } elseif ( $filter_item["type"] == "datetime" ) {
            switch ( $filter_item["comparison"] ) {
                case "gt": $op = ">"; break;
                case "lt": $op = "<"; break;
                case "eq": $op = "="; break;
            }
            $value = $m["ro"]->escape_string( $filter_item["value"] );
            $filter_sql .= " AND $field $op '$value'";
        }
    }
    return $filter_sql;
}

function aws($args) {

    $args_str = "";

    foreach (explode(" ", $args) as $arg) {
        $args_str .= " ".escapeshellarg($arg);
    }
    $aws_cmd = "aws $args_str";

    $ds = array(
        0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
        1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
        2 => array("pipe", "w")   // stderr is a pipe that the child will write to
    );

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."command: '$aws_cmd'");

    $process = proc_open($aws_cmd, $ds, $pipes);

    if (is_resource($process)) {

        fclose($pipes[0]);

        $cout = stream_get_contents($pipes[1]);
        $cerr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $return_value = proc_close($process);

        if ($return_value == 0) {
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): Success");
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): STDOUT: $cout");
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): STDERR: $cerr");
            return $cout;
        } else {
            vlog("Command failed: $aws_cmd");
            vlog("STDOUT: $cout");
            vlog("STDERR: $cerr");
            return FALSE;
        }
    } else {
        vlog("Could not start aws procees $aws_cmd");
        return FALSE;
    }
}

function aws_createBucket($Bucket) {

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Creating bucket $Bucket");

    $result = aws("s3 mb s3://$Bucket");
    if($result === FALSE) {
        critical_www_error("Failed to create bucket $Bucket");
    }
    return $result;
}

function aws_createUser($UserName) {

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Creating user $UserName");

    $result = aws("iam create-user --user-name $UserName");
    if($result === FALSE) {
        critical_www_error("Failed to create user $UserName");
    }
    return $result;
}

function aws_createAccessKey($UserName) {

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Creating access key for user $UserName");

    $result = aws("iam create-access-key --user-name $UserName");
    if($result === FALSE) {
        critical_www_error("Failed to create access key for user $UserName");
    }
    return $result;
}

function aws_createPolicy($policy_name, $policy_document) {

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Creating policy $policy_name from file $policy_document");

    $result = aws("iam create-policy --policy-name $policy_name --policy-document file://$policy_document");
    if($result === FALSE) {
        critical_www_error("Failed to create policy $policy_name from file $policy_document");
    }
    return $result;
}

function aws_attachUserPolicy($PolicyArn, $UserName) {

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Attaching policy $PolicyArn to user $UserName");

    $result = aws("iam attach-user-policy --user-name $UserName --policy-arn $PolicyArn");
    if($result === FALSE) {
        critical_www_error("Failed to attaching policy $PolicyArn to user $UserName");
    }
    return $result;
}
