<?php

include_once("includes.php");

function getSchedule($user_id){
    global $m;
    
    $user_id = (int)($user_id);
    $q = "SELECT";
    $q .= " `schedule_id`";
    $q .= " FROM `schedule`";
    $q .= " WHERE `user_id` = $user_id";
    $q .= " ORDER BY `schedule_id`";
    $q .= " LIMIT 1";
    $r = mysql_rw_execute($q, "www");
    $schedule_id = 0;
    if ($r->num_rows > 0){
        $row = $r->fetch_row();
        $schedule_id = $row[0];
        }
    return $schedule_id;
}

function getRetentionPolicy($user_id){
    global $m;
    
    $user_id = (int)($user_id);
    $q = "SELECT";
    $q .= " `retention_policy_id`";
    $q .= " FROM `retention_policy`";
    $q .= " WHERE `user_id` = $user_id";
    $q .= " ORDER BY `retention_policy_id`";
    $q .= " LIMIT 1";
    $r = mysql_rw_execute($q, "www");
    $retention_policy_id = 0;
    if ($r->num_rows > 0){
        $row = $r->fetch_row();
        $retention_policy_id = $row[0];
        }
    return $retention_policy_id;
}

function getVolume($user_id){
    global $m;
    
    $user_id = (int)($user_id);
    $q = "SELECT";
    $q .= " `volume_id`";
    $q .= " FROM `volume`";
    $q .= " WHERE `user_id` = $user_id";
    $q .= " ORDER BY `volume_id`";
    $q .= " LIMIT 1";
    $r = mysql_rw_execute($q, "www");
    $volume_id = 0;
    if ($r->num_rows > 0){
        $row = $r->fetch_row();
        $volume_id = $row[0];
        }
    return $volume_id;
}

function get_mysql_user($user_id){
    global $m;
    
    $user_id = (int)($user_id);
    $q = "SELECT";
    $q .= " `mysql_user`";
    $q .= " FROM `config`";
    $q .= " WHERE `user_id` = $user_id";
    $q .= " ORDER BY `config_id`";
    $q .= " LIMIT 1";
    $r = mysql_rw_execute($q, "www");
    $mysql_user = "root";
    if ($r->num_rows > 0){
        $row = $r->fetch_row();
        $mysql_user = $row[0];
        }
    return $mysql_user;
}

function get_mysql_password($user_id){
    global $m;
    
    $user_id = (int)($user_id);
    $q = "SELECT";
    $q .= " `mysql_password`";
    $q .= " FROM `config`";
    $q .= " WHERE `user_id` = $user_id";
    $q .= " ORDER BY `config_id`";
    $q .= " LIMIT 1";
    $r = mysql_rw_execute($q, "www");
    $mysql_password = "";
    if ($r->num_rows > 0){
        $row = $r->fetch_row();
        $mysql_password = $row[0];
        }
    return $mysql_password;
}

function addBackupConfig($params, $user_id){
    global $m;
   
    $user_id = (int)($user_id);
    foreach($params as $key => &$value){
        $value = $m["rw"]->escape_string($value);
    }
    $name = $params["name"];

    start_transaction("www");
    $schedule_id = getSchedule($user_id);
    if($schedule_id == 0) critical_www_error("Could not find schedule for new backup configuration");

    $retention_policy_id = getRetentionPolicy($user_id);
    if($retention_policy_id == 0) critical_www_error("Could not find retention policy for new backup configuration");

    $volume_id = getVolume($user_id);

    if($volume_id == 0) critical_www_error("Could not find storage for new backup configuration");
    $mysql_user = $m["rw"]->escape_string(get_mysql_user($user_id));
    $mysql_password = $m["rw"]->escape_string(get_mysql_password($user_id));

    $q = "INSERT INTO `config`";
    $q .= " (`user_id`, `name`, `schedule_id`, `retention_policy_id`, `volume_id`, `mysql_user`, `mysql_password`)";
    $q .= " VALUES($user_id, '$name', $schedule_id, $retention_policy_id, $volume_id, '$mysql_user', '$mysql_password')";
    $r = mysql_rw_execute($q, "www");
    $config_id = $m["insert_id"];
    commit_transaction("www");
    return $config_id;
}

function main($params){
    global $m;
    global $demo;

    read_config();
    $user_id = get_user($demo);
    if($demo){
        critical_www_error("This is read-only demo mode");
    }
    $m["rw"] = get_rw_connection("www");
    $m["ro"] = get_ro_connection("www");
    $response = array();
    $response["success"] = TRUE;

    $response["data"]["config_id"] = addBackupConfig($params, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
