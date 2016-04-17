<?php

include_once("includes.php");

function getGraphData($user_id, $params){
    global $m;
    
    $user_id = (int)($user_id);
    // Number of columns on the chart
    $n_cols = 5;
    // Get backup times range
    $q = "SELECT MIN(UNIX_TIMESTAMP(`finish_actual`) - UNIX_TIMESTAMP(`start_actual`)) AS min_bt,";
    $q .= " MAX(UNIX_TIMESTAMP(`finish_actual`) - UNIX_TIMESTAMP(`start_actual`)) AS max_bt";
    $q .= " FROM `job`";
    $q .= " JOIN `server` USING(`server_id`)";
    $q .= " WHERE `server`.`user_id` = $user_id";
    $q .= " AND `start_actual` IS NOT NULL";
    $q .= " AND `finish_actual` IS NOT NULL";
    $q .= " AND `job`.`type` = 'backup'";
    $q .= " AND `job`.`status` = 'Finished'";
    $q .= " HAVING min_bt IS NOT NULL AND max_bt IS NOT NULL";
    $r = mysql_ro_execute($q, "www");
    if ($r->num_rows > 0) {
        $row = $r->fetch_row();
        $min_bt = $row[0];
        $max_bt = $row[1];
        $interval_size_sec = ($max_bt - $min_bt) / $n_cols;
    } else {
        return NULL;
    }
    $unit = "sec";
    $unit_div = 1;
    if ($interval_size_sec > 60) {
        $unit = "mins";
        $unit_div = 60;
    }
    if ($interval_size_sec > 3600) {
        $unit = "hours";
        $unit_div = 3600;
    }
    if ($interval_size_sec > 3600 * 24) {
        $unit = "days";
        $unit_div = 3600 * 24;
    }
    $a = (int)$min_bt;
    $b = (int)($min_bt + $interval_size_sec);
    $result = array();
    while ($b < $max_bt) {
        $q = "SELECT COUNT(*) AS `n_jobs`";
        $q .= " FROM `job`";
        $q .= " JOIN `server` USING(`server_id`)";
        $q .= " WHERE `server`.`user_id` = $user_id";
        $q .= " AND `start_actual` IS NOT NULL";
        $q .= " AND `finish_actual` IS NOT NULL";
        $q .= " AND `job`.`type` = 'backup'";
        $q .= " AND UNIX_TIMESTAMP(`finish_actual`) - UNIX_TIMESTAMP(`start_actual`) >= $a";
        $q .= " AND UNIX_TIMESTAMP(`finish_actual`) - UNIX_TIMESTAMP(`start_actual`) < $b";
        $r = mysql_ro_execute($q, "www");
        $row = $r->fetch_row();
        $point["backup_time"] = "<".round($b/$unit_div, 0)." $unit";
        $point["n_jobs"] = $row[0];
        $r->free();
        $a = $b;
        $b = $a + $interval_size_sec;
        array_push($result, $point);
    }
        $q = "SELECT COUNT(*) AS `n_jobs`";
        $q .= " FROM `job`";
        $q .= " JOIN `server` USING(`server_id`)";
        $q .= " WHERE `server`.`user_id` = $user_id";
        $q .= " AND `start_actual` IS NOT NULL";
        $q .= " AND `finish_actual` IS NOT NULL";
        $q .= " AND `job`.`type` = 'backup'";
        $q .= " AND UNIX_TIMESTAMP(`finish_actual`) - UNIX_TIMESTAMP(`start_actual`) >= $a";
        $r = mysql_ro_execute($q, "www");
        $row = $r->fetch_row();
        $point["backup_time"] = ">=".round($a/$unit_div, 0)." $unit";
        $point["n_jobs"] = $row[0];
        $r->free();
        array_push($result, $point);
    return $result;
}

function main($params){
    global $m;

    read_config();
    $user_id = get_user();
    $m["ro"] = get_ro_connection("www");
    $response = array();
    $response["success"] = TRUE;
    $response["data"] = getGraphData($user_id, $params);
    echo json_encode($response);
    exit(0);
    }

main($_POST);
?>
