<?php

set_include_path(get_include_path().":/usr/share/twindb/inc");

include_once("variables.php");
include_once("general_lib.php");
include_once("mysql_lib.php");
include_once("dispatcher_lib.php");

function process_post() {
    # MySQL connections array
    global $m;
    global $_POST;

    date_default_timezone_set("UTC");
    # Initalize response
    $response["success"] = FALSE;
    $response["response"] = NULL;

    # Parse arguments
    #
    # $_POST[“data”] is GPG encrypted & signed JSON string. 
    # It’s encrypted with public api@twindb.com key 
    # and signed with private server’s key:
    #
    #   {
    #   "type":"get_config",
    #   "params":{
    #       "param_1":1,
    #       "param_2":2
    #       }
    #   }
    if (!isset($_POST["data"])) {
        critical_error("There is no request", 
                basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."\$_POST = '".json_encode($_POST)."'");
    }
    $request = get_request($_POST["data"]);
    debug("Got request:");
    debug($request);

    $m["rw"] = get_rw_connection("api");
    $m["ro"] = get_ro_connection("api");

    if (!isset($request->type)) {
        vlog("Ignoring request without type:");
        vlog($request);
        exit(0);
        }
    
    switch ($request->type) {
        case "register":
            register_server($request);
            break;
        case "confirm_registration":
            handler_confirm_registration($request);
            break;
        case "unregister":
            unregister_server($request);
            break;
        case "register_storage":
            register_storage($request);
            break;
        case "register_storage_confirm":
            handler_register_storage_confirm($request);
            break;
        case "get_config":
            get_config($request);
            break;
        case "get_job":
            get_job($request);
            break;
        case "notify":
            handler_notify($request);
            break;
        case "report_sss":
            handler_report_sss($request);
            break;
        case "report_agent_privileges":
            handler_report_agent_privileges($request);
            break;
        case "update_backup_data":
            handler_update_backup_data($request);
            break;
        case "send_key":
            handler_send_key($request);
            break;
        case "get_backups_chain":
            handler_get_backups_chain($request);
            break;
        case "log":
            rlog($request);
            break;
        case "schedule_backup":
            handler_schedule_backup($request);
            break;
        case "is_registered":
            handler_is_registered($request);
            break;
        default:
            critical_api_error("Unknown request type ".$request->type,
                    basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "."\$_POST[\"data\"] = '".$_POST["data"]."'");
    }
    $m["rw"]->close();
    $m["ro"]->close();
}

function process_get() {
    # MySQL connections array
    global $m;
    global $_GET;
    global $dispatcher_ssh_key_public;

    date_default_timezone_set("UTC");

    $m["ro"] = get_ro_connection();

    if ( isset($_GET["get_keys"]) ) {
        $ip = $m["ro"]->escape_string($_SERVER["REMOTE_ADDR"]);
        # Check if the request is coming form a known server
        $q = "SELECT `user_id` FROM `storage` WHERE `ip`='$ip'";
        $r = mysql_ro_execute($q);
        if ( $r->num_rows > 0 ) {
            $r->free();
            if ($_GET["get_keys"] == "root" ) {
                readfile($dispatcher_ssh_key_public);
            } else {
                $user_id = (int)str_replace("user_id_", "", $_GET["get_keys"]);
                $q = "SELECT `ssh_public_key` FROM `server` WHERE `user_id` = $user_id";
                $r = mysql_ro_execute($q);
                while( $row = $r->fetch_row() ) {
                    echo $row[0];
                }
            }
        } else {
            # Exit if we don't know the server
            exit(0);
        }
    }

}
# EOF functions

read_config();

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
    process_post();
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
    process_get();
}

?>
