<?php

include_once("includes.php");

function getData($user_id, $server_id){
    global $m;
    
    $user_id = (int)($user_id);
    $server_id = $m["ro"]->escape_string($server_id);

    $q = "SELECT `server_id`,";
    $q .= " `ssh_public_key`,";
    $q .= " `enc_public_key`";
    $q .= " FROM `server`";
    $q .= " WHERE `user_id` = $user_id";
    $q .= " AND `server_id` = '$server_id'";
    $r = mysql_ro_execute($q, "www");
    if ( $r->num_rows == 1 ) {
        $row = $r->fetch_assoc();
        $data["server_id"] = $row["server_id"];
        $data["ssh_public_key"] = $row["ssh_public_key"];
        $data["enc_public_key"] = $row["enc_public_key"];
        return $data;
    }
    return null;
}

function main($params){
    global $m;

    read_config();
    $user_id = get_user();
    $m["ro"] = get_ro_connection("www");
    $response = array();
    $response["success"] = TRUE;
    $server_id = $params["server_id"];
    $response["data"] = getData($user_id, $server_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
