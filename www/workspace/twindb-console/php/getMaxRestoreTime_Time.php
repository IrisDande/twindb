<?php

include_once("includes.php");

function getChartData($user_id, $params){
    global $m;
    
    $user_id = (int)($user_id);
    $q = "SELECT DATE(`start_actual`) AS `date`,";
    $q .= " MAX(UNIX_TIMESTAMP(`finish_actual`) - UNIX_TIMESTAMP(`start_actual`)) AS `restore_time`";
    $q .= " FROM `job`";
    $q .= " JOIN `server` USING(`server_id`)";
    $q .= " WHERE `server`.`user_id` = $user_id";
    $q .= " AND `start_actual` IS NOT NULL";
    $q .= " AND `finish_actual` IS NOT NULL";
    $q .= " AND `job`.`type` = 'restore'";
    $q .= " AND `job`.`status` = 'Finished'";
    $q .= " GROUP BY 1";
    $q .= " ORDER BY 1;";

    $r = mysql_ro_execute($q, "www");
    $result = array();
    while($row = $r->fetch_assoc()){
        //$row["usage"] = (int)$row["usage"];
        array_push($result, $row);
        }
    return $result;
}

function main($params){
    global $m;

    read_config();
    $user_id = get_user();
    $m["ro"] = get_ro_connection("www");
    $response = array();
    $response["success"] = TRUE;
    $response["data"] = getChartData($user_id, $params);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
