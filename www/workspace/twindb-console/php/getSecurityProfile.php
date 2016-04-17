<?php

include_once("includes.php");

function getSecurityProfile($user_id){
    global $m;
    
    $user_id = (int)($user_id);
    $q = "SELECT";
    $q .= " `user_id`,";
    $q .= " `gpg_pub_key`";
    $q .= " FROM `user`";
    $q .= " WHERE `user_id` = $user_id";
    $r = mysql_ro_execute($q, "www");
    if($r->num_rows == 1){
        $row = $r->fetch_row();
        $profile["user_id"] = $row[0];
        $profile["gpg_pub_key"] = $row[1];
        return $profile;
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
    $response["data"] = getSecurityProfile($user_id);
    echo json_encode($response);
    exit(0);
    }

main();
?>
