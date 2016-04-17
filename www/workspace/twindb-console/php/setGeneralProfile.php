<?php

include_once("includes.php");

function setGeneralProfile($params, $user_id){
    global $m;
   
    $user_id = (int)($user_id);
    foreach($params as $key => &$value){
        $value = $m["rw"]->escape_string($value);
    }

    start_transaction("www");
    $q = "UPDATE `user`";
    $q .= " SET ";
    $q .= " `first_name` = '".$params["first_name"]."',";
    $q .= " `last_name` = '".$params["last_name"]."',";
    $q .= " `phone` = '".$params["phone"]."',";
    $q .= " `skype` = '".$params["skype"]."'";
    $q .= " WHERE `user_id` = ".$user_id;
    mysql_rw_execute($q, "www");
    commit_transaction("www");
    return null;
}

function main($params){
    global $m;
    global $demo;

    read_config();
    $user_id = get_user($demo);
    if($demo){
        critical_www_error("This is read-only demo mode");
    }
    $m["rw"] = get_rw_connection("www");
    $response = array();
    $response["success"] = TRUE;

    $response["data"] = setGeneralProfile($params, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
