<?php

include_once("includes.php");

function deleteRetentionPolicy($params, $user_id){
    global $m;
 
    $user_id = (int)($user_id);
    foreach($params as $key => &$value){
        $value = $m["rw"]->escape_string($value);
    }

    start_transaction("www");
    $q = "DELETE FROM `retention_policy`";
    $q .= " WHERE";
    $q .= " `user_id` = $user_id";
    $q .= " AND `retention_policy_id` = ".$params["retention_policy_id"];
    $q .= " LIMIT 1";
    mysql_rw_execute($q, "www");
    $retention_policy_id = $m["insert_id"];
    commit_transaction("www");
    return $retention_policy_id;
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

    $response["data"]["retention_policy_id"] = deleteRetentionPolicy($params, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
