<?php

include_once("includes.php");

function add_user($params){
    global $m;
    global $default_volume_type;

    $email = $m["rw"]->escape_string(trim($params["email"]));
    $password = $m["rw"]->escape_string($params["password"]);
    $enc_password = $m["rw"]->escape_string(crypt($password, get_salt($email)));
    start_transaction("www");

    $q = "INSERT INTO `user` (`email`, `password`, `reg_code`) VALUES('$email', '$enc_password', MD5(RAND()))";
    mysql_rw_execute($q, "www");
    $user_id = $m["insert_id"];

    $q = "INSERT INTO `retention_policy` (`user_id`) VALUES('$user_id')";
    mysql_rw_execute($q, "www");
    $retention_policy_id = $m["insert_id"];

    $q = "INSERT INTO `schedule` (`user_id`)  VALUES('$user_id')";
    mysql_rw_execute($q, "www");
    $schedule_id = $m["insert_id"];

    if ($default_volume_type == "s3") {
        $volume_id = create_s3_volume($user_id);
    } else {
        $volume_id = create_ebs_volume($user_id);
    }

    if ($volume_id == 0) {

        return 0;
    }

    $q = "INSERT INTO `config`".
        " (`user_id`, `schedule_id`, `retention_policy_id`, `volume_id`, `mysql_user`, `mysql_password`) ".
        " VALUES($user_id, $schedule_id, $retention_policy_id, $volume_id, 'twindb_agent', MD5(RAND()))";
    mysql_rw_execute($q, "www");
    commit_transaction("www");

    return $user_id;
}


function create_s3_volume($user_id) {
    
    global $m;
    global $AWS_ACCESS_KEY_ID;
    global $AWS_SECRET_ACCESS_KEY;

    putenv("AWS_ACCESS_KEY_ID=$AWS_ACCESS_KEY_ID");
    putenv("AWS_SECRET_ACCESS_KEY=$AWS_SECRET_ACCESS_KEY");

    $user_id = (int)$user_id;
    $Bucket = "twindb_user_id_$user_id";
    aws_createBucket($Bucket);

    $UserName = "twindb_user_id_$user_id";

    aws_createUser($UserName);
    $result = json_decode(aws_createAccessKey($UserName), TRUE);

    $AccessKeyId = $result["AccessKey"]["AccessKeyId"];
    $SecretAccessKey = $result["AccessKey"]["SecretAccessKey"];

    $statements = array();
    $statement = array(
        "Sid" => "Stmt".date("Uu"),
        "Effect" => "Allow",
        "Action" => array("s3:GetObject", "s3:PutObject"),
        "Resource" => array("arn:aws:s3:::twindb_user_id_$user_id/*")
    );
    array_push($statements, $statement);
    $policy = array(
        "Version" => "2012-10-17",
        "Statement" => $statements
    );
    $policyDocument = tempnam(sys_get_temp_dir(), "twindb");
    $policyName = "twindb_user_id_" . $user_id . "_s3_policy";

    $f = fopen($policyDocument, "w");
    fwrite($f, json_encode($policy));
    fclose($f);

    $policy["Version"] = date("Y-m-d");
    $policy["Statement"] = array();

    $result = json_decode(aws_createPolicy($policyName, $policyDocument), TRUE);
    $policyArn = $result["Policy"]["Arn"];
    unlink($policyDocument);

    aws_attachUserPolicy($policyArn, $UserName);

    $params = array(
        "Bucket" => $Bucket,
        "AccessKeyId" => $AccessKeyId,
        "SecretAccessKey" => $SecretAccessKey
    );

    $params_json = $m["rw"]->escape_string(json_encode($params));
    $q = "INSERT INTO `storage` (`user_id`, `name`, `params`, `type`, `registered`, `used_size`)".
        " VALUES($user_id, '$Bucket', '$params_json', 's3', 'yes', 0)";
    mysql_rw_execute($q, "www");
    $storage_id = (int)$m["insert_id"];

    $q = "INSERT INTO `volume` (`storage_id`, `user_id`) VALUES ($storage_id, $user_id)";
    mysql_rw_execute($q, "www");
    $volume_id = $m["insert_id"];

    return (int)$volume_id;
}

function create_ebs_volume($user_id) {

    global $dispatcher_ssh_key_private;
    global $free_space;
    global $m;

    $user_id = (int)$user_id;
    $storage_id = NULL;

    $q = "SELECT ";
    $q .= " `storage`.`storage_id`,";
    $q .= " `storage`.`size`,";
    $q .= " IFNULL(SUM(`volume`.`size`), 0) AS allocated_size";
    $q .= " FROM `storage`";
    $q .= " LEFT JOIN `volume`";
    $q .= " USING (`storage_id`)";
    $q .= " WHERE `storage`.`user_id` = 0"; // allocate volume from TwinDB storage
    $q .= " GROUP BY 1";
    $q .= " HAVING allocated_size + $free_space < storage.size";
    $q .= " LIMIT 1";
    $r = mysql_rw_execute($q, "www");

    if($r->num_rows > 0) {
        $row = $r->fetch_row();
        $storage_id = (int)$row[0];
    } else {
        critical_www_error("Failed to create storage");
    }

    $ip = NULL;
    $q = "SELECT `ip` FROM `storage` WHERE `storage_id` = $storage_id";
    $r = mysql_ro_execute($q, "www");
    if ($r->num_rows > 0 ) {
        $row = $r->fetch_row();
        $ip = $row[0];
    } else {
        critical_www_error("Can not create volume for user_id $user_id. Could not find storage with id $storage_id");
    }

    $volume_id = NULL;
    $password = md5(rand());

    $cmd = "ssh -i ".escapeshellarg($dispatcher_ssh_key_private).
        " -oStrictHostKeyChecking=no -p 4194".
        " -l root ".escapeshellarg($ip).
        " 'twindb-add_chroot_user user_id_$user_id $password'";

    $ds = array(
        0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
        1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
        2 => array("pipe", "w")   // stderr is a pipe that the child will write to
    );

    $process = proc_open($cmd, $ds, $pipes);

    if (is_resource($process)) {

        $cout = stream_get_contents($pipes[1]);
        $cerr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $return_code = proc_close($process);

        if ($return_code != 0) {
            critical_www_error("Can not create volume for user_id $user_id.\nError while creating volume: \nSTDOUT:\n$cout\nSTDERR:\n$cerr");
        }

        $q = "INSERT INTO `volume`".
            " (`user_id`, `storage_id`, `username`) ".
            " VALUES('$user_id', '$storage_id', 'user_id_$user_id')";
        mysql_rw_execute($q, "www");
        $volume_id = (int)$m["rw"]->insert_id;

    } else {
        critical_www_error("Can not create volume for user_id $user_id. Could not start command $cmd");
    }
    return $volume_id;
}


function main($params){
    global $m;

    read_config();
    $m["rw"] = get_rw_connection("www");
    $m["ro"] = get_ro_connection("www");
    $user_id = add_user($params);
    if($user_id != 0){
        session_start();
        $_SESSION["user_id"] = (int)$user_id;
        $_SESSION["auth"] = true;
        $result['success'] = true;
        $result['errors']['msg'] = 'User added and authenticated!';
        setcookie("authenticated", true, 0, "/");
        setcookie("email", $params["email"], 0, "/");
    } else {
        $result['success'] = false;
        $result['errors']['msg'] = 'Could not register new user';
        setcookie('authenticated', false, time()-42000, '/');
    }
    echo json_encode($result);
}

main($_POST);
