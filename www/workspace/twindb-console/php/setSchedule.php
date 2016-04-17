<?php

include_once("includes.php");

function setSchedule($params, $user_id){
    global $m;
   
    $user_id = (int)($user_id);
    foreach($params as $key => &$value){
        $value = $m["rw"]->escape_string($value);
    }
    $ntimes = (int)$params["ntimes"];

    $schedule_id = (int)$params["schedule_id"];
    $name = $params["name"];
    $start_time = $params["start_time"];
    $time_zone = $params["time_zone"];
    $full_copy = $params["full_copy"];
    $frequency_unit = $params["frequency_unit"];

    start_transaction("www");
    $q = "UPDATE `schedule`";
    $q .= " SET `name` = '$name',";
    $q .= " `start_time` = '$start_time',";
    $q .= " `time_zone` = '$time_zone',";
    $q .= " `ntimes` = $ntimes,";
    $q .= " `frequency_unit` = '$frequency_unit',";
    $q .= " `full_copy` = '$full_copy'";
    $q .= " WHERE `user_id` = '$user_id' AND `schedule_id` = '$schedule_id'";
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

    $params["ntimes"] = (isset($params["ntimes"])) ? $params["ntimes"] : "NULL";

    $response["data"] = setSchedule($params, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
