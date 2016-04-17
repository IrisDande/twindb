<?php

include_once("includes.php");

function check_server_id($server_id, $user_id){
    $user_id = (int)$user_id;
    if ( $user_id !== get_user_id_by_server_id($server_id, "www")){
        critical_www_error("Wrong server_id $server_id for user_id $user_id");
    }
}

function check_attribute_id($attribute_id, $user_id){
    $user_id = (int)$user_id;
    if ( $user_id !== get_user_id_by_attribute_id($attribute_id, "www")){
        critical_www_error("Wrong attribute_id $attribute_id for user_id $user_id");
    }
}

function check_config_id($config_id, $user_id){
    $user_id = (int)$user_id;
    if ( $user_id !== get_user_id_by_config_id($config_id, "www")){
        critical_www_error("Wrong config_id $config_id for user_id $user_id");
    }
}
function setAttributes($params, $user_id){
    global $m;
   
    $user_id = (int)($user_id);

    start_transaction("www");
    $data = json_decode($params["data"]);
    vlog($params["data"]);
    vlog(print_r($data, TRUE));
    foreach($data as $row){
        $selected = $row[0];
        $server_id = $m["ro"]->escape_string($row[1]);
        debug("Selected: $selected");
        debug("server_id: $server_id");
        if(!$selected) continue;
        // Make sure server belongs to the current user
        check_server_id($server_id, $user_id);
        if ($params["action"] == "set attribute") {
            $attribute_id = (int)$params["attribute_id"];
            check_attribute_id($attribute_id, $user_id);
            $attribute_value = $m["rw"]->escape_string($params["attribute_value"]);
            $q = "INSERT INTO `server_attribute`(`server_id`, `attribute_id`, `value`)";
            $q .= " VALUES('$server_id', $attribute_id, '$attribute_value')";
            $q .= " ON DUPLICATE KEY UPDATE `value` = '$attribute_value'";
        } elseif ($params["action"] == 'remove attribute') {
            $attribute_id = (int)$params["attribute_id"];
            check_attribute_id($attribute_id, $user_id);
            $q = "DELETE FROM `server_attribute`";
            $q .= " WHERE `server_id` = '$server_id' AND `attribute_id` = $attribute_id";
        } elseif ($params["action"] == 'set backup configuration') {
            $config_id = (int)$params["config_id"];
            check_config_id($config_id, $user_id);
            $q = "UPDATE `server`";
            $q .= " SET `config_id` = $config_id";
            $q .= " WHERE `server_id` = '$server_id'";
        } else {
            critical_www_error("Unknown action ".$params["action"]);
        }
        mysql_rw_execute($q, "www");
    }
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
    $m["ro"] = get_ro_connection("www");
    $response = array();
    $response["success"] = TRUE;

    $response["data"] = setAttributes($params, $user_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
