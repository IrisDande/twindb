<?php

include_once("includes.php");

function deleteAttribute($params, $user_id){
    global $m;
 
    $user_id = (int)($user_id);
    foreach($params as $key => &$value){
        $value = $m["rw"]->escape_string($value);
    }

    start_transaction("www");
    $attribute_id = (int)$params["attribute_id"];
    $q = "DELETE FROM `attribute`";
    $q .= " WHERE";
    $q .= " `user_id` = $user_id";
    $q .= " AND `attribute_id` = $attribute_id";
    $q .= " LIMIT 1";
    mysql_rw_execute($q, "www");
    commit_transaction("www");
    return $attribute_id;
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

    $response["data"]["attribute_id"] = deleteAttribute($params, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
