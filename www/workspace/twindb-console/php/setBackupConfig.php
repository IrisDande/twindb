<?php

include_once("includes.php");

function setBackupConfig($params, $user_id){
    global $m;
   
    $user_id = (int)($user_id);
    foreach($params as $key => &$value){
        $value = $m["rw"]->escape_string($value);
    }
    start_transaction("www");
    $q = "UPDATE `config`";
    $q .= " SET `name` = '".$params["name"]."',";
    $q .= " `schedule_id` = ".$params["schedule_id"].",";
    $q .= " `retention_policy_id` = ".$params["retention_policy_id"].",";
    $q .= " `volume_id` = ".$params["volume_id"].",";
    $q .= " `mysql_user` = '".$params["mysql_user"]."',";
    $q .= " `mysql_password` = '".$params["mysql_password"]."'";
    $q .= " WHERE `user_id` = '$user_id' AND `config_id` = ".$params["config_id"];
    $r = mysql_rw_execute($q, "www");
    commit_transaction("www");
    return null;
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
    $response = array();
    $response["success"] = TRUE;

    $params["config_id"] = $_POST["config_id"];
    
    $response["data"] = setBackupConfig($params, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
