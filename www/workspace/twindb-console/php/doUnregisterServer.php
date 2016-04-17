<?php

include_once("includes.php");

function unregisterServer($params, $user_id){
    global $m;
    global $server_id;

    debug("Unregistering server with parameters:");
    debug($params);
    $user_id = (int)($user_id);
    $server_id = $m["rw"]->escape_string($params["server_id"]);
    $delete_backups = (bool)$params["delete_backups"];
    if ( $params["delete_backups"] == "true" ) {
        $delete_backups = TRUE;
    } else {
        $delete_backups = FALSE;
    }
    debug("It will delete backups: $delete_backups");
    $user_id = get_user_id_by_server_id($server_id);
    if ($user_id != get_user_id_by_server_id($server_id)) {
        critical_www_error("ERROR: Could not find user that owns server_id $server_id");
    }
    start_transaction();
    delete_server(NULL, $user_id, $delete_backups, "www");
    commit_transaction();
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
    $m["ro"] = get_rw_connection("www");
    $response = array();
    $response["success"] = TRUE;

    unregisterServer($params, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
