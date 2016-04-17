#!/usr/bin/env php
<?php
date_default_timezone_set("UTC");
set_include_path(get_include_path().":/usr/share/twindb/inc");
set_include_path(get_include_path().":/etc/twindb/");

include_once("variables.php");
include_once("general_lib.php");
include_once("mysql_lib.php");

include_once("config.php");

main();

function main(){
    global $m;
    global $argv;
    global $api_debug;

    $options = getopt("g", array("type:"));
    if ($options == FALSE && count($argv) > 1) {
        vlog("Error: Can't parse command line options");
        usage();
        exit(1);
    }

    if (isset($options["g"])) {
        $api_debug=TRUE;
        debug("Debug mode is ON");
    }

    $m["rw"] = get_rw_connection();
    $m["ro"] = get_ro_connection();
    set_timezone("UTC");
    
    debug("Taking snapshot of volume usage");
    $q = "REPLACE INTO `volume_usage_history` (`volume_id`, `date`, `used`)";
    $q .= " SELECT `volume_id`, NOW(), `used_size` FROM `volume`";
    mysql_rw_execute($q);
}

function usage() {
    global $argv;

    echo "Usage:\n";
    echo "    ".basename($argv[0])." [-g]\n";
    echo "    -g print debug information\n";
}
?>
