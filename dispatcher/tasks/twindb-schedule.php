#!/usr/bin/env php
<?php
date_default_timezone_set("UTC");
set_include_path(get_include_path().":/usr/share/twindb/inc");
set_include_path(get_include_path().":/etc/twindb/");

include_once("variables.php");
include_once("general_lib.php");
include_once("mysql_lib.php");
include_once("dispatcher_lib.php");

function main(){
    global $m;
    global $argv;
    global $api_debug;

    $options = getopt("g");
    if($options == FALSE && count($argv) > 1){
        vlog("Error: Can't parse command line options");
        usage();
        exit(1);
    }

    if(isset($options["g"])){
        $api_debug = TRUE;
        debug("Debug mode is ON");
    }

    $m["rw"] = get_rw_connection();
    $m["ro"] = get_ro_connection();

    debug("Discover changes in replication topology");

    start_transaction();
    discover_replication_topology();
    cancel_jobs_for_offline_agents("backup");
    schedule_backup_jobs();
    commit_transaction();

    start_transaction();
    schedule_send_key_jobs();
    commit_transaction();
}

function schedule_backup_jobs() {
    debug("Get list of clusters with servers");
    $clusters = get_clusters();
    foreach ($clusters as $cluster) {
        debug("Get list of servers in the cluster");
        $servers = get_servers($cluster["cluster_id"]);
        $server_id = pickup_server_for_backup($servers, $cluster["cluster_id"]);
        if ($server_id != NULL && !job_scheduled($server_id)) {
            schedule_backup_job($server_id);
        }
    }
    return;
}

function schedule_send_key_jobs() {
    debug("Scheduling send_key jobs");
    $servers_without_key = get_servers_without_private_key();
    foreach ($servers_without_key as $server_id) {
        if (!scheduled_send_key_job($server_id)) {
            schedule_send_key_job($server_id);
        }
    }
    return;
}

function cancel_jobs_for_offline_agents($job_type) {
    global $check_period;
    global $m;

    debug("Cancelling jobs scheduled to offline agents");

    $job_type = $m["rw"]->escape_string($job_type);

    $check_period = (int)$check_period;
    # Consider agent offline if it's not being seen for at least 60*60 seconds.
    # 60*60 because the max backup time in our client's environment is 56 minutes.
    # Ideally the agent should get check_period from the dispatcher, then we'll
    # consider the agent offline if there is no a ping since 2*$check_period ago.
    # And the agent reports should continue even if the agent is executing a job.
    $q = "UPDATE `job` JOIN `server` USING (`server_id`) SET `job`.`status` = 'Failed' ".
         "WHERE `job`.`status` = 'Scheduled' ".
         "AND `job`.`type` = '$job_type' ".
         "AND UNIX_TIMESTAMP(`server`.`last_seen_at`) < UNIX_TIMESTAMP(NOW()) - 60 * $check_period";
    mysql_rw_execute($q);
}

function usage(){
    global $argv;
    echo $argv[0]." [-g]\n";
    echo "\n-g print debug information\n";
    return;
}

read_config();
main();

?>
