<?php

include_once("includes.php");

function getServers($user_id, $params, &$total){
    global $m;
    
    $user_id = (int)($user_id);
    $q0 = "SELECT";
    $q0 .= " IFNULL(FORMAT(`S0`.`cluster_id`, 0) , 'Orphaned slaves') AS `cluster_id`,";
    $q0 .= " `S0`.`server_id`,";
    $q0 .= " IF(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`S0`.`last_seen_at`) < 300, 'Y', 'N') AS `online`,";
    $q0 .= " `S0`.`name`,";
    $q0 .= " `S0`.`role`,";
    $q0 .= " `S0`.`config`,";
    $q0 .= " `S0`.`time_zone`,";
    $q0 .= " `S0`.`created_at`,";
    $q0 .= " `S0`.`updated_at`,";
    $q0 .= " `S0`.`last_seen_at`,";
    $q0 .= " `S0`.`mysql_server_id`,";
    $q0 .= " `S0`.`mysql_master_server_id`,";
    $q0 .= " `S0`.`mysql_master_host`,";
    $q0 .= " `S0`.`mysql_seconds_behind_master`,";
    $q0 .= " `S0`.`mysql_slave_io_running`,";
    $q0 .= " `S0`.`mysql_slave_sql_running`";
    $q1 = " FROM (";
    $q1 .= " SELECT `server`.`server_id`,";
    $q1 .= " `server`.`cluster_id`,";
    $q1 .= " `server`.`user_id`,";
    $q1 .= " `server`.`name`,";
    $q1 .= " `server`.`role`,";
    $q1 .= " IFNULL(`config`.`name`, '<i>(empty)</i>') AS `config`,";
    $q1 .= " `server`.`time_zone`,";
    $q1 .= " `server`.`created_at`,";
    $q1 .= " `server`.`updated_at`,";
    $q1 .= " `server`.`last_seen_at`,";
    $q1 .= " `server`.`mysql_server_id`,";
    $q1 .= " `server`.`mysql_master_server_id`,";
    $q1 .= " `server`.`mysql_master_host`,";
    $q1 .= " `server`.`mysql_seconds_behind_master`,";
    $q1 .= " `server`.`mysql_slave_io_running`,";
    $q1 .= " `server`.`mysql_slave_sql_running`";
    $q1 .= " FROM `server`";
    $q1 .= " LEFT JOIN `server_attribute` USING(`server_id`)";
    $q1 .= " LEFT JOIN `config` USING(`config_id`)";
    $q1 .= " LEFT JOIN `attribute` USING(`attribute_id`)";
    $q1 .= " WHERE `server`.`registered` = 'Yes'";
    $q1 .= " AND `server`.`registration_confirmed` = 'Yes'";
    $q1 .= " GROUP BY server.server_id";
    $q1 .= " ) S0";
    $set_n = 1;
    $qf = "";
    $qp = "";
    if (count($params["server_filter"]) > 0) {
        $server_filter = json_decode($params["server_filter"], TRUE);
        foreach($server_filter as $clause){
            if ($clause["checked"] 
                && 
                (count($clause["items"]) > 0 || $clause["include_null"]) ) {
                $attribute = $m["ro"]->escape_string($clause["attribute"]);
                $attribute_id = (int)($clause["attribute_id"]);
                $qf .= ", '$attribute' AS `attribute_$set_n`";
                $qf .= ", IFNULL(`S$set_n`.`value`, '<i>(empty)</i>') AS `attribute_value_$set_n`";
                $qp .= " JOIN(";
                $qp .= " SELECT server.server_id,";
                $qp .= " `attribute`.`attribute`,";
                $qp .= " `server_attribute`.`value`";
                $qp .= " FROM `server`";
                $qp .= " LEFT JOIN `server_attribute` ";
                $qp .= "     ON  `server`.`server_id` = `server_attribute`.`server_id`";
                $qp .= "          AND";
                $qp .= "         `server_attribute`.`attribute_id` = $attribute_id";
                $qp .= " LEFT JOIN `attribute` USING(`attribute_id`)";
                $qp .= " WHERE ";
                if (count($clause["items"]) > 0) {
                    $qp .= " (`attribute`.`attribute` = '$attribute'";
                    $qp .= " AND `server_attribute`.`value` IN(";
                    $comma = "";
                    foreach($clause["items"] as $item){
                        $value = $m["ro"]->escape_string($item);
                        $qp .= "$comma"."'$value'";
                        $comma = ",";
                    }
                    $qp .= " )";
                    $qp .= " )";
                }
                if($clause["include_null"]){
                    if (count($clause["items"]) > 0) $qp .= " OR";
                    $qp .= " `attribute`.`attribute` IS NULL";
                }
                $qp .= " ) S$set_n ON S0.server_id = S$set_n.server_id";
                $set_n++;
            }
        }
    }
    $q = $q0.$qf.$q1.$qp." WHERE `S0`.`user_id` = $user_id";
    if (isset($params["filter"])) {
        $params["filter"] = str_replace("\"field\":\"name\"", "\"field\":\"`S0`.`name`\"", $params["filter"]);
        $q .= getFilterClause($params["filter"]);
    }
    $q .= " ORDER BY `S0`.`cluster_id`, `S0`.`name`";
    if (isset($params["start"]) && isset($params["limit"])) {
        $start = (int)$params["start"];
        $limit = (int)$params["limit"];
        $q .= " LIMIT $limit OFFSET $start";
    }
    $r = mysql_ro_execute($q, "www");
    $result = array();
    $total = 0;
    while($row = $r->fetch_assoc()){
        $row["selected"] = True;
        array_push($result, $row);
        $total++;
        }
    return $result;
}
// probably need to deprecate this function as we can count total number of records in getServers()
function getServersTotal($user_id, $params) {

    $user_id = (int)($user_id);

    $q = "SELECT COUNT(*) FROM `server` WHERE `server`.`user_id` = $user_id ";
    if ( isset($params["filter"]) ) {
        $q .= getFilterClause($params["filter"]);
    }
    $r = mysql_ro_execute($q, "www");
    $row = $r->fetch_row();
    return $row[0];
}

function main($params){
    global $m;

    read_config();
    $user_id = get_user();
    $m["ro"] = get_ro_connection("www");
    $response = array();
    $response["success"] = TRUE;
    $response["data"] = getServers($user_id, $params, $total);
    $response["total"] = $total;
    echo json_encode($response);
    exit(0);
    }

main($_POST);