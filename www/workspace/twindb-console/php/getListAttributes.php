<?php

include_once("includes.php");

function getList(){
    global $m;

    $user_id = get_user();
    $q = "SELECT `attribute_id`, `attribute` FROM `attribute` WHERE `user_id` = $user_id ORDER BY `attribute`";
    $r = mysql_ro_execute($q, "www");
    $result = array();
    while($row = $r->fetch_row()){
        $node["attribute_id"] = $row[0];
        $node["attribute"] = $row[1];
        array_push($result, $node);
        }
    $r->free();
    return $result;
}

function main(){
    global $m;
    read_config();
    $m["ro"] = get_ro_connection("www");
    $response = array();
    $response["success"] = TRUE;
    $response["data"] = getList();
    echo json_encode($response);
    exit(0);
    }

main();
?>
