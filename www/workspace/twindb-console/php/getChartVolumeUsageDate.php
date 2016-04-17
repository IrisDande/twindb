<?php

include_once("includes.php");

function getChartVolumeUsageDate($user_id, $params){
    global $m;
    
    $user_id = (int)($user_id);
    $q = "SELECT";
    $q .= " `volume_usage_history`.`date`,";
    $q .= " ROUND(SUM(`volume_usage_history`.`used`) * 100 / SUM(`volume`.`size`), 4) AS `usage`";
    $q .= " FROM `volume_usage_history`";
    $q .= " JOIN `volume` USING (`volume_id`)";
    $q .= " WHERE user_id = $user_id";
    $q .= " GROUP BY `date`";
    $q .= " ORDER BY `volume_usage_history`.`date`";

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
    $response["data"] = getChartVolumeUsageDate($user_id, $params);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
