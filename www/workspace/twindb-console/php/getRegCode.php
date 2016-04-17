<?php

include_once("includes.php");

function getRegCode($user_id){
    global $m;
    
    $user_id = (int)($user_id);
    $q = "SELECT";
    $q .= " `reg_code`";
    $q .= " FROM `user`";
    $q .= " WHERE `user_id` = $user_id";
    $r = mysql_ro_execute($q, "www");
    $result = array();
    while($row = $r->fetch_assoc()){
        array_push($result, $row);
        }
    return $result;
}

function main(){
    global $m;
    global $demo;

    read_config();
    $user_id = get_user();
    $response = array();
    $response["success"] = TRUE;
    if ($demo) {
        $response["data"]["reg_code"] = "hexadecimal_registation_code";
    } else {
        $m["ro"] = get_ro_connection("www");
        $response["data"] = getRegCode($user_id);
    }
    echo json_encode($response);
    exit(0);
    }

main();
?>
