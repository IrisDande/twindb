<?php

include_once("includes.php");

function setPassword($params, $user_id){
    global $m;
   
    $user_id = (int)($user_id);
    foreach($params as $key => &$value){
        $value = $m["rw"]->escape_string($value);
    }
    start_transaction("www");
    $q = "SELECT";
    $q .= " `email`";
    $q .= " FROM `user`";
    $q .= " WHERE `user_id` = $user_id";
    $r = mysql_rw_execute($q, "www");
    if($r->num_rows == 1){
        $row = $r->fetch_row();
        $email = $row[0];
    } else { 
        critical_www_error("Could not find email of user_id = $user_id");
    }
    $newpassword = $m["rw"]->escape_string(crypt($params["newpassword"], get_salt($email)));
    $q = "UPDATE `user`";
    $q .= " SET ";
    $q .= " `password` = '$newpassword'";
    $q .= " WHERE `user_id` = ".$user_id;
    mysql_rw_execute($q, "www");
    commit_transaction("www");
    return null;
}

function main($params){
    global $m;
    global $demo;

    read_config();
    $user_id = get_user();
    if($demo){
        critical_www_error("This is read-only demo mode");
    }
    $m["rw"] = get_rw_connection("www");
    $m["ro"] = get_ro_connection("www");
    $response = array();
    $response["success"] = TRUE;

    $response["data"] = setPassword($params, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
