<?php

include_once("includes.php");

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

    $server_id = $params["server_id"];
    if ($user_id != get_user_id_by_server_id($server_id, "www")) {
        critical_www_error("Wrong server_id $server_id");
    }
    schedule_backup_job($server_id, "www");
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
