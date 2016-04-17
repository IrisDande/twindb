<?php

include_once("includes.php");

function saveData($params, $user_id){
    global $m;
   
    $user_id = (int)($user_id);
    $server_id = $m["rw"]->escape_string($params["server_id"]);
    $name = $m["rw"]->escape_string($params["name"]);
    $time_zone = $m["rw"]->escape_string($params["time_zone"]);
    $config_id = (int)$params["config_id"];

    start_transaction();
    $q = "UPDATE `server`";
    $q .= " SET ";
    $q .= " `name` = '$name',";
    $q .= " `time_zone` = '$time_zone',";
    $q .= " `config_id` = $config_id";
    $q .= " WHERE `user_id` = ".$user_id;
    $q .= " AND `server_id` = '$server_id'";
    mysql_rw_execute($q, "www");
    commit_transaction();
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

    $response["data"] = saveData($params, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
