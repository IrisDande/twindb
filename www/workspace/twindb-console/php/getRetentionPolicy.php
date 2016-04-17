<?php

include_once("includes.php");

function getRetentionPolicy($user_id, $retention_policy_id = 0){
    global $m;
    
    $retention_policy_id = (int)($retention_policy_id);
    $user_id = (int)($user_id);
    $q = "SELECT";
    $q .= " `retention_policy_id`,";
    $q .= " `name`,";
    $q .= " `keep_full_sets`";
    $q .= " FROM `retention_policy`";
    $q .= " WHERE `user_id` = $user_id";
    if ($retention_policy_id != 0) {
        $q .= " AND `retention_policy_id` = $retention_policy_id";
    }
    $r = mysql_ro_execute($q, "www");
    $result = array();
    while($row = $r->fetch_assoc()){
        array_push($result, $row);
        ;
        }
    return $result;
}

function main(){
    global $m;

    read_config();
    $user_id = get_user();
    $m["ro"] = get_ro_connection("www");
    $response = array();
    $response["success"] = TRUE;
    $retention_policy_id = 0;
    if (isset($_POST["retention_policy_id"])) {
        $retention_policy_id = $_POST["retention_policy_id"];
    }
    $response["data"] = getRetentionPolicy($user_id, $retention_policy_id);
    echo json_encode($response);
    exit(0);
    }

main();
?>
