<?php

include_once("includes.php");

function schedule_restore_job($server_id, $backup_copy_id, $restore_dir, $caller) {
    global $m;
    $server_id = $m["rw"]->escape_string($server_id);
    $restore_dir = $m["rw"]->escape_string($restore_dir);
    $backup_copy_id = (int)$backup_copy_id;
    $params["server_id"] = $server_id;
    $params["restore_dir"] = $restore_dir;
    $params["backup_copy_id"] = $backup_copy_id;

    $q = "INSERT INTO `job`(`server_id`, `type`, `start_scheduled`, `params`)";
    $q .= " VALUES('$server_id', 'restore', NOW(), '".json_encode($params)."')";
    mysql_rw_execute($q, "www");
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

    $server_id = $params["server_id"];
    $backup_copy_id = $params["backup_copy_id"];
    $restore_dir = $params["restore_dir"];

    if ($user_id != get_user_id_by_server_id($server_id, "www")) {
        critical_www_error("Wrong server_id $server_id");
    }
    if ($user_id != get_user_id_by_backup_copy_id($backup_copy_id, "www")) {
        critical_www_error("Wrong backup_copy_id $backup_copy_id");
    }
    schedule_restore_job($server_id, $backup_copy_id, $restore_dir, "www");
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
