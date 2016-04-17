<?php

include_once("includes.php");

function getList(){
    global $m;

    $user_id = get_user();
    // Only super-user (user_id == 1) may see list of users
    if ( $user_id != 1 ) {
        return NULL;
    }
    $q = "SELECT `email` FROM `user` ORDER BY `email`";
    $r = mysql_ro_execute($q, "www");
    
    $list = array();
    while($row = $r->fetch_row()){
        $node["email"] = $row[0];
        array_push($list, $node);
        }
    $r->free();
    return $list;
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
