<?php

include_once("includes.php");

function getStorage($volume_id, $user_id){

    $volume_id = (int)($volume_id);
    $user_id = (int)($user_id);

    $q = "SELECT";
    $q .= " `volume_id`,";
    $q .= " `name`,";
    $q .= " `size`,";
    $q .= " `used_size`";
    $q .= " FROM `volume`";
    $q .= " WHERE `user_id` = $user_id";
    if ($volume_id != 0) {
        $q .= " AND `volume_id` = $volume_id";
    }
    $r = mysql_ro_execute($q, "www");

    $result = array();
    while($row = $r->fetch_assoc()) {

        $volume["volume_id"] = $row["volume_id"];
        $volume["name"] = $row["name"];
        $volume["size"] = h_size($row["size"]);
        $used = (float)$row["used_size"]/(float)$row["size"];
        $precision = ($used > 0.01) ? 2 : 4;
        $volume["used"] = round($used, $precision);

        array_push($result, $volume);
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
    $volume_id = $params["volume_id"];
    $response["data"] = getStorage($volume_id, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);