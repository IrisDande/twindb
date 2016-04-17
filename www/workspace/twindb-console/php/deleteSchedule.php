<?php

include_once("includes.php");

function deleteSchedule($params, $user_id){
    global $m;
 
    $user_id = (int)($user_id);
    foreach($params as $key => &$value){
        $value = $m["rw"]->escape_string($value);
    }
    $schedule_id = $params["schedule_id"];

    start_transaction("www");
    $q = "DELETE FROM `schedule`";
    $q .= " WHERE";
    $q .= " `user_id` = $user_id";
    $q .= " AND `schedule_id` = $schedule_id";
    $q .= " LIMIT 1";
    mysql_rw_execute($q, "www");
    $schedule_id = $m["insert_id"];
    commit_transaction("www");
    return $schedule_id;
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

    $response["data"]["schedule_id"] = deleteSchedule($params, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
