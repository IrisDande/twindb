<?php

include_once("includes.php");

function getBackupConfig($user_id, $config_id = 0){
    global $m;
    
    $config_id = (int)($config_id);
    $user_id = (int)($user_id);
    $q = "SELECT";
    $q .= " `config_id`,";
    $q .= " `name`,";
    $q .= " `schedule_id`,";
    $q .= " `retention_policy_id`,";
    $q .= " `volume_id`,";
    $q .= " `mysql_user`,";
    $q .= " `mysql_password`";
    $q .= " FROM `config`";
    $q .= " WHERE `user_id` = $user_id";
    if ( $config_id != 0 ) {
        $q .= " AND `config_id` = $config_id";
    }
    $r = mysql_ro_execute($q, "www");
    $result = array();
    while($row = $r->fetch_assoc()){
        array_push($result, $row);
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
    $config_id = 0;
    if (isset($_POST["config_id"])) {
        $config_id = $_POST["config_id"];
    }
    $response["data"] = getBackupConfig($user_id, $config_id);
    echo json_encode($response);
    exit(0);
    }

main();
?>
