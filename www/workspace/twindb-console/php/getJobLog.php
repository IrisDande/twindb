<?php

include_once("includes.php");

function getJobLog($user_id, $params){
    global $m;
    
    $user_id = (int)($user_id);
    $job_id = (int)$params["job_id"];
    $q = "SELECT";
    $q .= " `log`.`ts`,";
    $q .= " `log`.`msg`";
    $q .= " FROM `log`";
    $q .= " WHERE `log`.`job_id` = $job_id";
    $q .= " AND `log`.`user_id` = $user_id";
    $r = mysql_ro_execute($q, "www");
    $msg = "";
    while($row = $r->fetch_assoc()){
        $msg .= $row["ts"].": ".$row["msg"]."\n";
        }
    $result = array();
    $row["job_id"] = $job_id;
    $row["msg"] = $msg;
    array_push($result, $row);
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
    $response["data"] = getJobLog($user_id, $params);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
