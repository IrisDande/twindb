<?php

include_once("includes.php");

function getJobs($user_id, $params){
    global $m;
    
    $user_id = (int)($user_id);
    $start = (int)$params["start"];
    $limit = (int)$params["limit"];
    $page = (int)$params["page"];
    $server_id_clause = "";
    if ( isset($params["server_id"]) ) {
        $server_id = $m["ro"]->escape_string( $params["server_id"] );
        $cluster_id = get_cluster_id($server_id, "www");
        if ($cluster_id === NULL){
            $server_id_clause = " AND `server`.`cluster_id` IS NULL";
        } else {
            $cluster_id = (int)$cluster_id;
            $server_id_clause = " AND `server`.`cluster_id` = $cluster_id";
        }
    }
    $q = "SELECT";
    $q .= " `job`.`job_id`,";
    $q .= " `server`.`name`,";
    $q .= " `job`.`type`,";
    $q .= " `job`.`status`,";
    $q .= " `job`.`start_scheduled`,";
    $q .= " `job`.`start_actual`,";
    $q .= " `job`.`finish_actual`";
    $q .= " FROM `job`";
    $q .= " JOIN `server` USING (`server_id`)";
    $q .= " WHERE `server`.`user_id` = $user_id";
    $q .= " $server_id_clause ";
    if ( isset($params["filter"]) ) {
        $q .= getFilterClause( $params["filter"] );
    }
    $q .= " ORDER BY `job`.`job_id` DESC";
    $q .= " LIMIT $limit OFFSET $start";
    $r = mysql_ro_execute($q, "www");
    $result = array();
    while($row = $r->fetch_assoc()){
        array_push($result, $row);
        }
    $r->free();
    return $result;
}

function getJobsTotal($user_id, $params) {
    global $m;

    $user_id = (int)($user_id);
    $server_id_clause = "";
    if ( isset($params["server_id"]) ) {
        $server_id = $m["ro"]->escape_string( $params["server_id"] );
        $cluster_id = get_cluster_id($server_id, "www");
        $server_id_clause = " AND `server`.`cluster_id` = $cluster_id";
    }

    $q = "SELECT COUNT(*) FROM `job` JOIN `server` USING (`server_id`) WHERE `server`.`user_id` = $user_id $server_id_clause";
    if ( isset($params["filter"]) ) {
        $q .= getFilterClause( $params["filter"] );
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
    $response["data"] = getJobs($user_id, $params);
    $response["total"] = getJobsTotal($user_id, $params);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
