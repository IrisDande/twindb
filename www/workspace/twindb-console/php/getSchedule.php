<?php

include_once("includes.php");

function getSchedule($user_id, $schedule_id = 0){
    global $m;
    
    $schedule_id = (int)($schedule_id);
    $user_id = (int)($user_id);
    $q = "SELECT";
    $q .= " `schedule_id`,";
    $q .= " `name`,";
    $q .= " `start_time`,";
    $q .= " `ntimes`,";
    $q .= " `full_copy`,";
    $q .= " `time_zone`,";
    $q .= " `frequency_unit`";
    $q .= " FROM `schedule`";
    $q .= " WHERE `user_id` = $user_id";
    if ( $schedule_id != 0 ) {
        $q .= " AND `schedule_id` = $schedule_id";
    }
    $r = mysql_ro_execute($q, "www");
    $result = array();
    while($schedule = $r->fetch_assoc()){
        array_push($result, $schedule);
        ;
        }
    return $result;
}

function main(){
    global $m;

    read_config();
    $user_id = get_user();
    $m["ro"] = get_ro_connection("www");
    $response = array();
    $response["success"] = TRUE;
    $schedule_id = 0;
    if (isset($_POST["schedule_id"])) {
        $schedule_id = $_POST["schedule_id"];
    }
    $response["data"] = getSchedule($user_id, $schedule_id);
    echo json_encode($response);
    exit(0);
    }

main();
?>
