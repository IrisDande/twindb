<?php

include_once("includes.php");

function ackNotification($params, $user_id){
    global $m;
   
    $user_id = (int)($user_id);
    $notification_id = (int)$params["notification_id"];

    start_transaction("www");
    $q = "UPDATE `notification`";
    $q .= " SET ";
    $q .= " `acknowledged` = 'Yes'";
    $q .= " WHERE `user_id` = $user_id";
    $q .= " AND `notification_id` = $notification_id";
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

    $response["data"] = ackNotification($params, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
