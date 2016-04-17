<?php

include_once("includes.php");

function getData($user_id, $params){
    global $m;
    
    $user_id = (int)($user_id);
    $q = "SELECT";
    $q .= " config.config_id,";
    $q .= " config.name,";
    $q .= " schedule.ntimes,";
    $q .= " schedule.frequency_unit";
    $q .= " FROM `config`";
    $q .= " JOIN `schedule` USING(`schedule_id`)";
    $q .= " WHERE config.user_id = $user_id";
    $r = mysql_ro_execute($q, "www");
    $result = array();
    while($row = $r->fetch_assoc()){
        $item["config_id"] = $row["config_id"];
        $item["name"] = $row["name"];
        $item["max_data_loss"] = $row["ntimes"]." ".$row["frequency_unit"];
        if ($row["ntimes"] > 1) {
            $item["max_data_loss"] .= "s";
        }
        array_push($result, $item);
        }
    $r->free();
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
