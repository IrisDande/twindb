<?php

include_once("includes.php");

function getOrders($user_id){
    global $m;
    
    $user_id = (int)($user_id);
    $q = "SELECT";
    $q .= " `order`.`order_id`,";
    $q .= " `package`.`name` AS package,";
    $q .= " `package`.`price` AS price,";
    $q .= " `order`.`start_date`,";
    $q .= " `order`.`stop_date`";
    $q .= " FROM `order`";
    $q .= " LEFT JOIN `package` ON `order`.`package_id` = `package`.`package_id`";
    $q .= " WHERE `user_id` = $user_id";
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
    $response["data"] = getOrders($user_id);
    echo json_encode($response);
    exit(0);
    }

main();
?>
