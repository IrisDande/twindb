<?php

include_once("includes.php");

function getData($user_id, $server_id){
    global $m;
    
    $user_id = (int)($user_id);
    $server_id = $m["ro"]->escape_string($server_id);

    $q = "SELECT `server`.`server_id`,";
    $q .= " `server`.`name`,";
    $q .= " `server`.`time_zone`,";
    $q .= " `server`.`config_id`,";
    $q .= " UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`server`.`last_seen_at`) AS lastseen,";
    $q .= " `server`.`mysql_slave_io_running`,";
    $q .= " `server`.`mysql_slave_sql_running`,";
    $q .= " `server`.`mysql_seconds_behind_master`,";
    $q .= " `server`.`Reload_priv`,";
    $q .= " `server`.`Lock_tables_priv`,";
    $q .= " `server`.`Repl_client_priv`,";
    $q .= " `server`.`Super_priv`,";
    $q .= " `server`.`Create_tablespace_priv`";
    $q .= " FROM `server`";
    $q .= " WHERE `server`.`user_id` = $user_id";
    $q .= " AND `server`.`server_id` = '$server_id'";
    $r = mysql_ro_execute($q, "www");
    if ( $r->num_rows == 1 ) {
        $row = $r->fetch_assoc();
        $data["server_id"] = $row["server_id"];
        $data["name"] = $row["name"];
        $data["time_zone"] = $row["time_zone"];
        $data["config_id"] = $row["config_id"];
        $data["lastseen"] = get_str_lastseen( (int)$row["lastseen"] );
        $msg = "";
        if ( $row["mysql_slave_io_running"] == "NO" ) {
            $msg .= "<p>IO thread is stopped.</p>";
        }
        if ( $row["mysql_slave_sql_running"] == "NO" ) {
            $msg .= "<p>SQL thread is stopped.</p>";
        }
        if ( $row["mysql_seconds_behind_master"] != 0 ) {
            $msg .= "<p>Replication lag is ".$row["mysql_seconds_behind_master"]." seconds</p>";
        }
        $data["replication_status"] = ( $msg == "" ) ? "OK" : $msg;
        $msg = "";

        if ( $row["Reload_priv"] == "" ) {
            $msg .= "<p>RELOAD privilege isn't reported.</p>";
        } elseif ( $row["Reload_priv"] == "N" ) {
            $msg .= "<p>RELOAD privilege isn't granted.</p>";
        }
        
        if ( $row["Lock_tables_priv"] == "" ) {
            $msg .= "<p>LOCK TABLES privilege isn't reported.</p>";
        } elseif ( $row["Lock_tables_priv"] == "N" ) {
            $msg .= "<p>LOCK TABLES privilege isn't granted.<p>";
        }

        if ( $row["Repl_client_priv"] == "" ) {
            $msg .= "<p>REPLICATION CLIENT privilege isn't reported.</p>";
        } elseif ( $row["Repl_client_priv"] == "N" ) {
            $msg .= "<p>REPLICATION CLIENT privilege isn't granted.</p>";
        }
        if ( $row["Super_priv"] == "" ) {
            $msg .= "<p>SUPER privilege isn't reported.</p>";
        } elseif ( $row["Super_priv"] == "N" ) {
            $msg .= "<p>SUPER privilege isn't granted.</p>";
        }
        if ( $row["Create_tablespace_priv"] == "" ) {
            $msg .= "<p>CREATE TABLESPACE privilege isn't reported.</p>";
        } elseif ( $row["Create_tablespace_priv"] == "N" ) {
            $msg .= "<p>CREATE TABLESPACE privilege isn't granted.</p>";
        }
        $data["agent_permissions_status"] = ( $msg == "" ) ? "OK" : $msg;
        return $data;
    }
    return null;
}

function get_str_lastseen($sec) {
    if ( $sec < 60 ) {
        return "less than a minute ago";
    }
    if ( $sec < 3600 ) {
        $n = round($sec / 60);
        return "$n minutes ago";
    }
    if ( $sec < 3600*24 ) {
        $n = round($sec / 3600);
        return "$n hours ago";
    }
    $n = round($sec / (3600 * 24));
    return "$n days ago";
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
