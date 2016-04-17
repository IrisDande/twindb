<?php

include_once("includes.php");

function get_user_id($job_id) {
    $user_id = 0;

    $q = "SELECT `user_id` FROM `job` JOIN `server` USING(`server_id`) WHERE `job_id` = $job_id";
    $r = mysql_ro_execute($q, "www");
    if ( $r->num_rows > 0 ) {
        $row = $r->fetch_row();
        $user_id = (int)$row[0];
    } 
    return $user_id;
}

function cancelJob($params, $user_id){
    global $m;
 
    $user_id = (int)($user_id);
    $job_id = (int)$params["job_id"];
    if ($user_id != get_user_id($job_id)) {
        critical_www_error("Wrong job_id $job_id");
    }

    $q = "DELETE FROM `job` WHERE `job_id` = $job_id";
    mysql_rw_execute($q);
    return;
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

    cancelJob($params, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
