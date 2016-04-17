<?php

# Returns a read-only connection to a pool of slaves
# If no slaves are avaiable(replication broken or a slave is behind) 
# then returns read-write connection to the master
function get_ro_connection($caller = NULL) {
    global $mysql_db;
    global $mysql_ro_host_pool;
    global $mysql_ro_user;
    global $mysql_ro_password;
    
    $mysqli = NULL;
    $timeout = 1;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."shuffling pool of read-only servers");
    # shufle RO hosts and pick up one
    shuffle($mysql_ro_host_pool);
    debug($mysql_ro_host_pool);
    foreach ($mysql_ro_host_pool as $host) {
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."trying host $host");
        $mysqli = mysqli_init();
        $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, $timeout);
        @$mysqli->real_connect($host, $mysql_ro_user, $mysql_ro_password, $mysql_db);
        if ($mysqli->connect_error) {
            vlog("WARNING: can not connect to server $host. MySQL replied: \"".$mysqli->connect_error."\"");
            continue;
        }
        # Check if slave is in good shape
        $q = "SHOW SLAVE STATUS";
        $r = $mysqli->query($q);
        if ($r == FALSE) {
            vlog("Can't use server $host as reader. Error on query '$q': ".$mysqli->error);
            continue;
        }
        if ($r->num_rows == 0) {
            # server seems to be master, use it
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."host $host is a master, will use it");
            set_sql_mode($mysqli, $caller);
            set_connection_timezone($mysqli, "UTC", $caller);
            return $mysqli;
        } else {
            # server is a slave, check if it's healthy
            $row = $r->fetch_assoc();
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."$host Slave_IO_Running: ". $row["Slave_IO_Running"]);
            if ($row["Slave_IO_Running"] != "Yes") {
                vlog("Can't use server $host as reader because replication IO thread is not running");
                continue;
            }
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."$host Slave_SQL_Running: ". $row["Slave_SQL_Running"]);
            if ($row["Slave_SQL_Running"] != "Yes") {
                vlog("Can't use server $host as reader because replication SQL thread is not running");
                continue;
            }
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."$host Seconds_Behind_Master: ". $row["Seconds_Behind_Master"]);
            if ($row["Seconds_Behind_Master"] != 0) {
                vlog("Can't use server $host as reader because it's ".$row["Seconds_Behind_Master"]." seconds behind the master");
                continue;
            }
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."host $host is a healthy slave, will use it");
            # if we got here, the slave is healthy
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."returning read-only connection");
            set_sql_mode($mysqli, $caller);
            set_connection_timezone($mysqli, "UTC", $caller);
            return $mysqli;
        }
    }
    # Could not find a slave, try to get a connection to master
    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."could not find a healthy slave, will try to use master instead");
    return get_rw_connection($caller);
}

# Returns a read-write connection to the master
function get_rw_connection($caller = NULL) {
    global $mysql_db;
    global $mysql_rw_host;
    global $mysql_rw_user;
    global $mysql_rw_password;

    # Wait a bit longer when connecting to the master
    $timeout = 3;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."trying host $mysql_rw_host");
    $mysqli = mysqli_init();
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, $timeout);
    @$mysqli->real_connect($mysql_rw_host, $mysql_rw_user, $mysql_rw_password, $mysql_db);
    if ($mysqli->connect_errno == 0) {
        save_binlog_coordinates($mysqli);
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."returning read/write connection");
    } else {
        $msg = "Error: ".$mysqli->connect_error; 
        critical_error($msg, $msg, $caller);
    }
    set_sql_mode($mysqli, $caller);
    set_connection_timezone($mysqli, "UTC", $caller);
    return $mysqli;
}

function set_sql_mode($mysqli, $caller = NULL) {
    $q = "SET sql_mode='STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'";
    $r = $mysqli->query($q);
    if ($r == FALSE) {
        $msg = "Error while sending query '$q'. MySQL replied: ".$mysqli->error;
        $msg_debug = basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Query: $q Error: ".$mysqli->error;
        critical_error($msg, $msg_debug, $caller);
    }
}
function mysql_rw_execute($q, $caller = NULL) {
    global $m;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."sending query '$q'");
    if (!isset($m["rw"])) {
        $m["rw"] = get_rw_connection($caller);
    }
    $r = $m["rw"]->query($q);
    if ($r == FALSE) {
        $msg = "Error while sending query '$q'. MySQL replied: ".$m["rw"]->error;
        $msg_debug = basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Query: $q Error: ".$m["rw"]->error;
        critical_error($msg, $msg_debug, $caller);
    }
    $m["insert_id"] = $m["rw"]->insert_id;
    save_binlog_coordinates($m["rw"]);
    return $r;
}

function save_binlog_coordinates($connection) {
    global $m;
    $q = "SHOW MASTER STATUS";
    $binlog_result = $connection->query($q);
    if ($binlog_result != FALSE) {
        if ($binlog_result->num_rows > 0) {
            $row = $binlog_result->fetch_row();
            $m["File"] = $row[0];
            $m["Position"] = $row[1];
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."current binlog coordinates('".$m["File"]."', ".$m["Position"].")");
        }
        $binlog_result->free();
    }
    return;
}

function slave_caught_up(){
    global $m;
    // If binlog coordinates aren't set, return FALSE
    if (!isset($m["File"])) return FALSE;
    if (!isset($m["Position"])) return FALSE;
    $q = "SHOW SLAVE STATUS";
    $r = $m["ro"]->query($q);
    if ($r == FALSE) {
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Query: $q Error: ".$m["ro"]->error);
        return FALSE;
    } else {
        if ($r->num_rows > 0) {
            $row = $r->fetch_assoc();
            if ($row["Slave_IO_Running"] == "No") {
                debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."broken slave: Slave_IO_Running is NO.");
                return FALSE;
            }
            if ($row["Slave_SQL_Running"] == "No") {
                debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."broken slave: Slave_SQL_Running is NO.");
                return FALSE;
            }
            if ($row["Relay_Master_Log_File"] >= $m["File"] 
                && (int)$row["Exec_Master_Log_Pos"] >= (int)$m["Position"]) {
                // return TRUE only if the slave executed event from the master
                debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."slave caught up");
                return TRUE;
            } else {
                debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): Lagging slave"); 
                debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): Master ('".$m["File"]."', ".$m["Position"].")");
                debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): Slave ('".$row["Relay_Master_Log_File"]."', ".$row["Exec_Master_Log_Pos"].")");
                return FALSE;
            }
        } else {
            // No slave configured
            debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): No slaves configured");
            return FALSE;
        }
    }
}

function mysql_ro_execute($q, $caller = NULL) {
    global $m;

    debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."sending query '$q'");
    if (slave_caught_up()) {
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."using a slave");
        $connection = $m["ro"];
    } else {
        debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."using the master");
        if (isset($m["rw"])) {
            $connection = $m["rw"];
        } else {
            $connection = get_rw_connection($caller);
        }
    }
    $r = $connection->query($q);
    if ($r == FALSE) {
        $msg = "Error while sending query '$q'. MySQL replied: ".$connection->error;
        $msg_debug = basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Query: $q Error: ".$connection->error;
        critical_error($msg, $msg_debug, $caller);
    }
    return $r;
}

function start_transaction( $caller = NULL ) {

    mysql_rw_execute("BEGIN", $caller);

    return;
}

function commit_transaction( $caller = NULL ) {

    mysql_rw_execute("COMMIT", $caller);

    return;
}

function rollback_transaction( $caller = NULL ) {

    mysql_rw_execute("ROLLBACK", $caller);

    return;
}

# Sets time zone in the given MySQL connection
function set_connection_timezone($connection, $timezone, $caller = NULL) {
    $q = "SET time_zone = '$timezone' ";
    $r = $connection->query($q);
    if ($r == FALSE) {
        $msg = "Error while sending query '$q'. MySQL replied: ".$connection->error;
        $msg_debug = basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."Query: $q Error: ".$connection->error;
        critical_error($msg, $msg_debug, $caller);
    }
    return;
}

# Sets time zone in all MySQL sessions
function set_timezone($timezone, $caller = NULL) {
    global $m;

    if (isset($m["rw"])) {
        set_connection_timezone($m["rw"], $timezone, $caller);
    }
    if (isset($m["ro"])) {
        set_connection_timezone($m["ro"], $timezone, $caller);
    }
    return;
 }