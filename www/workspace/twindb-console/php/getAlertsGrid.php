<?php

include_once("includes.php");

function getAlerts($user_id, $params){
    global $m;
    
    $user_id = (int)($user_id);
    $q = "SELECT";
    $q .= " `notification_id`,";
    $q .= " `check_id`,";
    $q .= " `message`,";
    $q .= " `acknowledged`";
    $q .= " FROM `notification`";
    $q .= " WHERE `user_id` = $user_id";
    $q .= " AND `resolved` = 'No'";
    $r = mysql_ro_execute($q, "www");
    $result = array();
    while($row = $r->fetch_assoc()){
        $row["acknowledged"] = ($row["acknowledged"] == "Yes") ? TRUE : FALSE;
        array_push($result, $row);
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
    $response["data"] = getAlerts($user_id, $params);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
