<?php
include_once("includes.php");

$user_id = get_user();

function get_direct_children_of($i){
    $items = array();

    $i = (int)$i;
    $q = "SELECT ";
    $q .= " `menu_item`.`menuitem_id`,";
    $q .= " `menu_item`.`text`,";
    $q .= " `menu_item`.`iconCls`,";
    $q .= " `menu_item`.`className`";
    $q .= " FROM `menu_item_tree`";
    $q .= " LEFT JOIN `menu_item`";
    $q .= " ON `menu_item`.`menuitem_id` = `menu_item_tree`.`descendant`";
    $q .= " WHERE `menu_item_tree`.`ancestor` = $i";
    $q .= " AND `show` = 'Y'";
    $q .= " AND `menu_item_tree`.`depth` = 1";
    $q .= " ORDER BY `menu_item`.`order`";
    $r = mysql_ro_execute($q, "www");

    while ($row = $r->fetch_row()) {
        $item["id"] = $row[0];
        $item["text"] = $row[1];
        $item["iconCls"] = $row[2];
        $item["parent_id"] = $i;
        $item["className"] = $row[3];
        $item["leaf"] = true;
        $item["checked"] = NULL;
        $item["items"] = array();
        array_push($items, $item);
    }
    return $items;
}

function get_attributes(){
    global $user_id;
    
    $user_id = (int)$user_id;
    $items = array();
    $item["id"] = "attribute_id_0";
    $item["text"] = 'All servers';
    //$item["iconCls"] = 'chart_organisation';
    $item["iconCls"] = "tag_blue";
    $item["parent_id"] = 0;
    $item["className"] = "servergrid";
    $item["leaf"] = true;
    $item["checked"] = NULL;
    //$item["items"] = array();
    array_push($items, $item);

    $q = "SELECT ";
    $q .= " `attribute`.`attribute_id`,";
    $q .= " `attribute`.`attribute`";
    $q .= " FROM `attribute`";
    $q .= " WHERE `attribute`.`user_id` = $user_id";
    $q .= " ORDER BY `attribute`.`attribute`";
    $r = mysql_ro_execute($q, "www");
    
    while ($row = $r->fetch_row()) {
        $item["id"] = "attribute_id_".$row[0];
        $item["attribute_id"] = $row[0];
        $item["text"] = $row[1];
        $item["className"] = "servergrid";
        $item["leaf"] = false;
        $item["checked"] = false;
        $item["items"] = array();
        array_push($items, $item);
    }

    return $items;
}

function get_schedules($i){
    global $user_id;
    
    $items = array();

    $i = (int)$i;
    $q = "SELECT ";
    $q .= " `schedule`.`schedule_id`,";
    $q .= " `schedule`.`name`";
    $q .= " FROM `schedule`";
    $q .= " WHERE `schedule`.`user_id` = $user_id";
    $r = mysql_ro_execute($q, "www");

    while ($row = $r->fetch_row()) {
        $item["id"] = "schedule_id_".$row[0];
        $item["text"] = $row[1];
        $item["iconCls"] = "calendar_view_day";
        $item["parent_id"] = $i;
        $item["className"] = "schedule";
        $item["leaf"] = true;
        $item["checked"] = NULL;
        $item["params"] = json_encode(array('schedule_id' => $row[0]));
        $item["items"] = array();
        array_push($items, $item);
    }

    return $items;
}

function get_retention_policies($i){
    global $user_id;
    
    $items = array();

    $i = (int)$i;
    $q = "SELECT ";
    $q .= " `retention_policy`.`retention_policy_id`,";
    $q .= " `retention_policy`.`name`";
    $q .= " FROM `retention_policy`";
    $q .= " WHERE `retention_policy`.`user_id` = $user_id";
    $r = mysql_ro_execute($q, "www");

    while ($row = $r->fetch_row()) {
        $item["id"] = "retention_policy_id_".$row[0];
        $item["text"] = $row[1];
        $item["iconCls"] = "page_white_stack";
        $item["parent_id"] = $i;
        $item["className"] = "retentionpolicy";
        $item["leaf"] = true;
        $item["checked"] = NULL;
        $item["params"] = json_encode(array('retention_policy_id' => $row[0]));
        $item["items"] = array();
        array_push($items, $item);
    }

    return $items;
}

function get_storages($i){
    global $user_id;

    $items = array();

    $i = (int)$i;
    $user_id = (int)$user_id;

    $q = "SELECT ";
    $q .= " `volume`.`volume_id`,";
    $q .= " `volume`.`name`";
    $q .= " FROM `volume`";
    $q .= " WHERE `volume`.`user_id` = $user_id";
    $r = mysql_ro_execute($q, "www");

    while ($row = $r->fetch_row()) {
        $item["id"] = "storage_id_".$row[0];
        $item["text"] = $row[1];
        $item["iconCls"] = "NetworkDriveOnline";
        //$item["iconCls"] = "datacenter";
        $item["parent_id"] = $i;
        $item["className"] = "storage";
        $item["leaf"] = true;
        $item["checked"] = NULL;
        $item["params"] = json_encode(array('storage_id' => $row[0]));
        $item["items"] = array();
        array_push($items, $item);
    }
    
    return $items;
}

function get_storage_id($user_id) {
    global $free_space;

    $user_id = (int)$user_id;
    $q = "SELECT `storage_id`";
    $q .= " FROM `storage`";
    $q .= " WHERE `user_id` = $user_id";
    $q .= " AND (`size` - `used_size`) > $free_space";
    $q .= " ORDER BY (`size` - `used_size`)";
    $q .= " LIMIT 1";
    $r = mysql_ro_execute($q);
    
    if ($r->num_rows == 1) {
        $row = $r->fetch_row();
        return $row[0];
    } else {
        return 0;
    }
}
function get_configs($i){
    global $user_id;
    
    $items = array();

    $i = (int)$i;
    $q = "SELECT ";
    $q .= " `config`.`config_id`,";
    $q .= " `config`.`name`";
    $q .= " FROM `config`";
    $q .= " WHERE `config`.`user_id` = $user_id";
    $r = mysql_ro_execute($q, "www");

    while ($row = $r->fetch_row()) {
        $item["id"] = "config_id_".$row[0];
        $item["text"] = $row[1];
        $item["iconCls"] = "cog";
        $item["parent_id"] = $i;
        $item["className"] = "backupconfig";
        $item["leaf"] = true;
        $item["checked"] = NULL;
        $item["params"] = json_encode(array('config_id' => $row[0]));
        $item["items"] = array();
        array_push($items, $item);
    }
    
    return $items;
}

debug(basename(__FILE__).": ".__LINE__.": ".__FUNCTION__."(): "." reading menu items");
read_config();

$m["rw"] = get_rw_connection("www");
$m["ro"] = get_ro_connection("www");

$result = get_direct_children_of(0);
for ($i = 0; $i < count($result) ; $i++) {
    if ($result[$i]["text"] == "Server farm") {
        $result[$i]["items"] = get_attributes();
    } elseif ($result[$i]["text"] == "Schedule") {
        $result[$i]["items"] = get_schedules($result[$i]["id"]);
    } elseif ($result[$i]["text"] == "Retention policy") {
        $result[$i]["items"] = get_retention_policies($i);
    } elseif ($result[$i]["text"] == "Storage") {
        $result[$i]["items"] = get_storages($i);
    } elseif( $result[$i]["text"] == "Backup configuration") {
        $result[$i]["items"] = get_configs($i);
    } else {
        $result[$i]["items"] = get_direct_children_of($result[$i]["id"]);
    }
}

setcookie("registration_open", $registration_open, 0, "/");
echo json_encode($result);