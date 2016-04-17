<?php

include_once("includes.php");

function getList($params){
    global $m;

    $user_id = get_user();
    if(isset($params["attribute_id"])){
        $attribute_id = (int)$params["attribute_id"];    
    } else {
        critical_www_error("Can not get attribute values because attribute is not given",
            basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): \$params[\"attribute_id\"] is not set");
    }
    $q = "SELECT DISTINCT `server_attribute`.`value` AS `attribute_value`";
    $q .= " FROM `server_attribute`";
    $q .= " LEFT JOIN `server` USING (`server_id`)";
    $q .= " WHERE `server`.`user_id` = $user_id ";
    $q .= " AND `server_attribute`.`attribute_id` = $attribute_id";
    $q .= " ORDER BY `attribute_value`";
    $r = mysql_ro_execute($q, "www");
    $result = array();
    if($params["include_empty"] === "true"){
        $node["attribute_value"] = "<i>(empty)</i>";
        $node["attribute_id"] = $attribute_id;
        array_push($result, $node);
    }
    while($row = $r->fetch_row()){
        $node["attribute_id"] = $attribute_id;
        $node["attribute_value"] = $row[0];
        array_push($result, $node);
    }
    $r->free();
    return $result;
}

function main($params){
    global $m;
    read_config();
    $m["ro"] = get_ro_connection("www");
    $response = array();
    $response["success"] = TRUE;
    $response["data"] = getList($params);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
