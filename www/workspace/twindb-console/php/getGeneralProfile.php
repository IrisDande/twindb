<?php

include_once("includes.php");

function getGeneralProfile($user_id){
    global $m;
    
    $user_id = (int)($user_id);
    $q = "SELECT";
    $q .= " `user_id`,";
    $q .= " `email`,";
    $q .= " `first_name`,";
    $q .= " `last_name`,";
    $q .= " `phone`,";
    $q .= " `skype`";
    $q .= " FROM `user`";
    $q .= " WHERE `user_id` = $user_id";
    $r = mysql_ro_execute($q, "www");
    if($r->num_rows == 1){
        $row = $r->fetch_assoc();
        return $row;
        }
    return null;
}

function main(){
    global $m;

    read_config();
    $user_id = get_user();
    $m["ro"] = get_ro_connection("www");
    $response = array();
    $response["success"] = TRUE;
    //$retention_policy_id = 10;
    $response["data"] = getGeneralProfile($user_id);
    echo json_encode($response);
    exit(0);
    }

main();
?>
