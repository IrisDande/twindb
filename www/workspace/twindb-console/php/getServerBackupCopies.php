<?php

include_once("includes.php");

function is_leaf($backup_copy_id) {
    $backup_copy_id = (int)$backup_copy_id;
    $q = "SELECT COUNT(*)";
    $q .= " FROM `backup_copy_tree`";
    $q .= " WHERE `ancestor` <> `descendant`";
    $q .= " AND `ancestor` = $backup_copy_id";
    $r = mysql_ro_execute($q, "www");
    $row = $r->fetch_row();
    $r->free();
    return ( $row[0] == 0 ) ? TRUE : FALSE;
}

function get_subtree($backup_copy_id) {
    $subtree = array();
    $backup_copy_id = (int)$backup_copy_id;
    
    // Get direct children
    $q = "SELECT";
    $q .= " `backup_copy_tree`.`descendant`,";
    $q .= " `backup_copy`.`name`,";
    $q .= " `backup_copy`.`size`,";
    $q .= " `job`.`finish_actual`,";
    $q .= " `backup_copy`.`backup_type`";
    $q .= " FROM `backup_copy_tree`";
    $q .= " JOIN `backup_copy`";
    $q .= " ON `backup_copy`.`backup_copy_id` = `backup_copy_tree`.`descendant`";
    $q .= " JOIN `job` USING (`job_id`)";
    $q .= " WHERE `backup_copy_tree`.`ancestor` <> `backup_copy_tree`.`descendant`";
    $q .= " AND `backup_copy_tree`.`length` = 1";
    $q .= " AND `backup_copy_tree`.`ancestor` = $backup_copy_id";
    $r = mysql_ro_execute($q, "www");

    while( $row = $r->fetch_row() ) {
        $copy["backup_copy_id"] = $row[0];
        $copy["name"] = $row[1];
        $copy["size"] = h_size( $row[2] );
        $copy["finish_actual"] = $row[3];
        $copy["backup_type"] = $row[4];
        $copy["iconCls"] = "application_double";
        $copy["leaf"] = is_leaf( $copy["backup_copy_id"] );
        if ( $copy["leaf"] == FALSE ) {
            $copy["expanded"] = TRUE;
            $copy["data"] = get_subtree( $copy["backup_copy_id"] );
        }
        array_push($subtree, $copy);
    }
    $r->free();
    return $subtree;
}

function getData($user_id, $params){
    global $m;
    
    $user_id = (int)($user_id);
    if (!isset($params["server_id"])) {
        vlog("Empty server_id variable in parameters received from agent");
        return NULL;
    }
    $server_id = $m["ro"]->escape_string($params["server_id"]);
    if ($user_id != get_user_id_by_server_id($server_id, "www")){
        vlog("server_id '$server_id' doesn't belong to user_id $user_id");
        return NULL;
    }
    $cluster_id = (int)get_cluster_id($server_id, "www");
    
    $q = "SELECT DISTINCT `t1`.`ancestor`,";
    $q .= " `backup_copy`.`name`,";
    $q .= " `backup_copy`.`size`,";
    $q .= " `job`.`finish_actual`,";
    $q .= " `backup_copy`.`backup_type`";
    $q .= " FROM `backup_copy_tree` `t1`";
    $q .= " LEFT JOIN `backup_copy_tree` `t2`";
    $q .= " ON `t1`.`ancestor` = `t2`.`descendant`";
    $q .= " AND `t2`.`length` > 0";
    $q .= " JOIN `backup_copy`";
    $q .= " ON `backup_copy`.`backup_copy_id` = `t1`.`ancestor`";
    $q .= " JOIN `job` USING (`job_id`)";
    $q .= " JOIN `server` USING (`server_id`)";
    $q .= " WHERE `t2`.`ancestor` IS NULL";
    $q .= " AND `server`.`cluster_id` = $cluster_id";
    $q .= " ORDER BY `job`.`finish_actual` DESC";
    $subtree = array();
    $r = mysql_ro_execute($q, "www");

    while( $row = $r->fetch_row() ) {
        $copy["backup_copy_id"] = $row[0];
        $copy["name"] = $row[1];
        $copy["size"] = h_size( $row[2] );
        $copy["finish_actual"] = $row[3];
        $copy["backup_type"] = $row[4];
        $copy["iconCls"] = "application_double";
        $copy["leaf"] = is_leaf( $copy["backup_copy_id"] );
        if ( $copy["leaf"] == FALSE ) {
            $copy["expanded"] = TRUE;
            $copy["data"] = get_subtree( $copy["backup_copy_id"] );
        }
        array_push($subtree, $copy);
    }
    $r->free();
    return $subtree;
}

function main($params){
    global $m;

    read_config();
    $user_id = get_user();
    $m["ro"] = get_ro_connection("www");
    $response = array();
    $response["success"] = TRUE;
    $response["data"] = getData($user_id, $params);
    echo json_encode($response);
    exit(0);
    }

main($_POST);