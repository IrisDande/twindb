<?php

include_once("includes.php");

function getData($user_id, $server_id){
    global $m;
    
    $data["nodes"] = array();
    $data["edges"] = array();
    $user_id = (int)($user_id);
    $server_id = $m["ro"]->escape_string($server_id);

    $q = "SELECT `cluster_id`, `name` FROM `server` WHERE `server_id` = '$server_id' AND `user_id` = $user_id";
    $r = mysql_ro_execute($q, "www");
    if ( $r->num_rows == 1 ) {
        $row = $r->fetch_row();
        $cluster_id = (int)$row[0];
        $name = $row[1];
        if ( $cluster_id != NULL ) {
            $q = "SELECT";
            $q .= " `server_id`,";
            $q .= " `name`";
            $q .= " FROM `server`";
            $q .= " WHERE `cluster_id` = $cluster_id";
            $q .= " AND  `user_id` = $user_id";
            $r_nodes = mysql_ro_execute($q, "www");
            while ( $row = $r_nodes->fetch_assoc() ) {
                $node["server_id"] = $row["server_id"];
                $node["name"] = $row["name"];
                array_push($data["nodes"], $node);
            }
            $q = "SELECT s1.server_id AS `to`,";
            $q .= " s1.name,";
            $q .= " s1.mysql_server_id,";
            $q .= " s1.mysql_master_server_id,";
            $q .= " s2.server_id AS `from`";
            $q .= " FROM server s1";
            $q .= " JOIN server s2";
            $q .= " ON s1.mysql_master_server_id = s2.mysql_server_id";
            $q .= " AND s2.cluster_id = $cluster_id";
            $q .= " WHERE s1.cluster_id = $cluster_id";
            $q .= " AND s1.user_id = $user_id";
            $r_edges = mysql_ro_execute($q, "www");
            while ( $row = $r_edges->fetch_assoc() ) {
                $node["from"] = $row["from"];
                $node["to"] = $row["to"];
                array_push($data["edges"], $node);
            }
            return $data;
        } else {
            $node["server_id"] = $row["server_id"];
            $node["name"] = $row["name"];
            array_push($data["nodes"], $node);
        
        }
    } else {
        return NULL;
    }

    return $data;
}

function main($params){
    global $m;

    read_config();
    $user_id = get_user();
    $m["ro"] = get_ro_connection("www");
    $response = array();
    $response["success"] = TRUE;
    $server_id = $params["server_id"];
    $response["data"] = getData($user_id, $server_id);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
