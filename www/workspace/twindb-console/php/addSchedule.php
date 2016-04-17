<?php

include_once("includes.php");

function addSchedule($params, $user_id){
    global $m;
   
    $user_id = (int)($user_id);
    foreach($params as $key => &$value){
        $value = $m["rw"]->escape_string($value);
    }
    $name = $params["name"];

    start_transaction("www");
    $q = "INSERT INTO `schedule`";
    $q .= " (`user_id`, `name`)";
    $q .= " VALUES($user_id, '$name')";
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

    $response["data"]["schedule_id"] = addSchedule($params, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
