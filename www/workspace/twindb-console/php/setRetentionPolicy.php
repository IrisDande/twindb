<?php

include_once("includes.php");

function setRetentionPolicy($params, $user_id){
    global $m;
   
    $user_id = (int)($user_id);
    foreach($params as $key => &$value){
        $value = $m["rw"]->escape_string($value);
    }

    start_transaction("www");
    $q = "UPDATE `retention_policy`";
    $q .= " SET `name` = '".$params["name"]."',";
    $q .= " `keep_full_sets` = ".$params["keep_full_sets"];
    $q .= " WHERE `user_id` = '$user_id' AND `retention_policy_id` = ".$params["retention_policy_id"];
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

    $params["retention_policy_id"] = $_POST["retention_policy_id"];
    
    $response["data"] = setRetentionPolicy($params, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
