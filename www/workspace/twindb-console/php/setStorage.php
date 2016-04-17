<?php

include_once("includes.php");

function setStorage($params, $user_id){
    global $m;
   
    $user_id = (int)($user_id);
    foreach($params as $key => &$value){
        $value = $m["rw"]->escape_string($value);
    }

    start_transaction("www");
    $q = "UPDATE `volume`";
    $q .= " SET `name` = '".$params["name"]."'";
    $q .= " WHERE `user_id` = '$user_id' AND `volume_id` = ".$params["volume_id"];
    mysql_rw_execute($q, "www");
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

    $params["volume_id"] = $_POST["volume_id"];
    
    $response["data"] = setStorage($params, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
