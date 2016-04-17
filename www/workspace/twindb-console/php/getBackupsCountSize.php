<?php

include_once("includes.php");

function getData($user_id, $params){
    global $m;

    $user_id = (int)($user_id);
    $server_id = $m["ro"]->escape_string($params["server_id"]);
    $q = "SELECT";
    $q .= " COUNT(*),";
    $q .= " SUM(`size`)";
    $q .= " FROM `backup_copy`";
    $q .= " JOIN `job` USING (`job_id`)";
    $q .= " JOIN `server` USING (`server_id`)";
    $q .= " WHERE `job`.`server_id` = '$server_id'";
    $q .= " AND `server`.`user_id` = $user_id";
    $r = mysql_ro_execute($q, "www");
    $row = $r->fetch_row();
    $result["count"] = $row[0];
    $result["size"] = h_size($row[1]);
    return $result;
}

function main($params){
    global $m;

    read_config();
    $user_id = get_user();
    $m["ro"] = get_ro_connection("www");
    $response = array();
    $response["success"] = TRUE;
    $response["data"] = getData($user_id, $params);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
