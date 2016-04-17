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
exit(0);

function main() {
    global $m;
    global $argv;
    global $api_debug;

    $options = getopt("g", array("type:"));
    if ( $options == FALSE && count($argv) > 1 ) {
        vlog("Error: Can't parse command line options");
        usage();
        exit(1);
    }

    if ( isset($options["g"]) ) {
        $api_debug = TRUE;
        debug("Debug mode is ON");
    }

    $m["rw"] = get_rw_connection();
    $m["ro"] = get_ro_connection();

    debug("Getting clusters list");
    $q = "SELECT DISTINCT `cluster_id` FROM `server`";
    $result = mysql_rw_execute($q);
    
    while ($row = $result->fetch_assoc()) {
        $cluster_id = (int)$row["cluster_id"];
        start_transaction();
        // Pick a random config of one of the servers in a cluster
        $q = "SELECT `retention_policy`.`keep_full_sets`
              FROM `server`
              JOIN `config` USING (`config_id`)
              JOIN `retention_policy` USING (`retention_policy_id`)
              WHERE `server`.`cluster_id` = $cluster_id
              LIMIT 1";
        $r = mysql_rw_execute($q);
        if ($r->num_rows == 1){
            $retention_policy = $r->fetch_assoc();
            $keep_full_sets = $retention_policy["keep_full_sets"];
            # Get full backup copies
            $q = "SELECT `backup_copy`.`backup_copy_id`
                  FROM `backup_copy`
                  JOIN `job` USING (`job_id`)
                  JOIN `server` USING (`server_id`)
                  WHERE `backup_copy`.`full_backup` = 'Y'
                  AND `server`.`cluster_id` = $cluster_id
                  ORDER BY `job`.`finish_actual` DESC
                  LIMIT 1000 OFFSET $keep_full_sets
                  FOR UPDATE";
            $full_backups = mysql_rw_execute($q);
            debug("Found ".$full_backups->num_rows." full sets to delete");
            while($full_backup = $full_backups->fetch_assoc()) {
                $backup_copy_id = (int)$full_backup["backup_copy_id"];
                delete_full_set($backup_copy_id);
            }
        }
        commit_transaction();
    }
}

function delete_full_set($backup_copy_id) {
    global $dispatcher_ssh_key_private;
    global $m;

    $backup_copy_id = (int)$backup_copy_id;

    $q = "SELECT";
    $q .= " `backup_copy_tree`.`descendant`,";
    $q .= " `backup_copy`.`name`,";
    $q .= " `storage`.`ip`,";
    $q .= " `server`.`user_id`,";
    $q .= " `server`.`server_id`";
    $q .= " FROM `backup_copy_tree`";
    $q .= " JOIN `backup_copy`";
    $q .= " ON `backup_copy`.`backup_copy_id` = `backup_copy_tree`.`descendant`";
    $q .= " JOIN `job` USING (`job_id`)";
    $q .= " JOIN `server` USING (`server_id`)";
    $q .= " JOIN `volume` USING(`volume_id`)";
    $q .= " JOIN `storage` USING(`storage_id`)";
    $q .= " WHERE `backup_copy_tree`.`ancestor` = $backup_copy_id";
    $q .= " FOR UPDATE";
    $r = mysql_rw_execute($q);
    while ( $backup_row = $r->fetch_assoc() ) {
        $user_id = (int)$backup_row["user_id"];
        $descendant = (int)$backup_row["descendant"];
        $backup_copy_file = $backup_row["name"];
        $server_id = $m["rw"]->escape_string($backup_row["server_id"]);
        $ip = $m["rw"]->escape_string($backup_row["ip"]);;
        $ssh_arg = "-oStrictHostKeyChecking=no";
        $ssh_arg .= " -i ". escapeshellarg($dispatcher_ssh_key_private);
        $ssh_arg .= " -p 4194";
        $ssh_arg .= " " . escapeshellarg("root@$ip");
        $ssh_arg .= " rm " . escapeshellarg("/var/twindb-sftp/user_id_$user_id/home/$backup_copy_file");
        $cmd = escapeshellcmd("ssh $ssh_arg");
        debug("Removing backup:\nssh $ssh_arg");
        exec($cmd, $cout, $retcode);
        if ( $retcode == 0 ) {
            $q = "UPDATE `volume`";
            $q .= " JOIN `backup_copy` USING(`volume_id`)";
            $q .= " SET `volume`.`used_size` = `volume`.`used_size` - `backup_copy`.`size`";
            $q .= " WHERE `backup_copy`.`backup_copy_id` = $descendant";
            mysql_rw_execute($q);
            $q = "UPDATE `storage`";
            $q .= " JOIN `volume` USING(`storage_id`)";
            $q .= " JOIN `backup_copy` USING(`volume_id`)";
            $q .= " SET `storage`.`used_size` = `storage`.`used_size` - `backup_copy`.`size`";
            $q .= " WHERE `backup_copy`.`backup_copy_id` = $descendant";
            mysql_rw_execute($q);
            $q = "DELETE FROM `backup_copy` WHERE `backup_copy_id` = $descendant";
            mysql_rw_execute($q);
            # Delete all scheduled incremental backup jobs where this backup copy
            # is a parent
            $q = "DELETE FROM `job` WHERE `params` LIKE '%\"ancestor\":\"$descendant\"%'";
            $q .= " AND `type` = 'backup' AND `status` = 'Scheduled' AND `server_id` = '$server_id'";
            mysql_rw_execute($q);
        } else {
            vlog("Failed to delete backup copy $backup_copy_file from storage $ip");
        }
    }
}
function usage() {
    global $argv;

    echo "Usage:\n";
    echo "    ".basename($argv[0])." [-g]\n";
    echo "    -g print debug information\n";
}
?>
