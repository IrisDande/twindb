<?php

include_once("includes.php");

function getTimezoneList(){
    global $m;

    $q = "SELECT `Name` FROM `time_zone_name` ORDER BY `Name`";
    $r = mysql_ro_execute($q, "www");
    $timezones = array();
    while($row = $r->fetch_row()){
        $node["time_zone"] = $row[0];
        array_push($timezones, $node);
        }
    $r->free();
    return $timezones;
}

function main(){
    global $m;
    read_config();
    $m["ro"] = get_ro_connection("www");
    $response = array();
    $response["success"] = TRUE;
    $response["data"] = getTimezoneList();
    echo json_encode($response);
    exit(0);
    }

main();
?>
