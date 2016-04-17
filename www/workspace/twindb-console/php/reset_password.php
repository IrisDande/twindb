<?php

include_once("includes.php");

function check_reset_code($params){
    global $m;
    $code = $m["rw"]->escape_string(trim($params["code"]));
    $user_id = 0;

    start_transaction("www");
    $q = "SELECT `user_id` FROM `password_reset_code` WHERE `code` = '$code' AND `expires` > NOW()";
    $r = mysql_rw_execute($q, "www");
    if($r->num_rows > 0){
        $row = $r->fetch_row();
        $user_id = $row[0];
    }
    $q = "DELETE FROM `password_reset_code` WHERE `code` = '$code'";
    mysql_rw_execute($q, "www");
    commit_transaction("www");
    return $user_id;
}

function main($params){
    global $m;

    read_config();
    $m["rw"] = get_rw_connection("www");
    $user_id = check_reset_code($params);
    if($user_id != 0){
        session_start();
        $_SESSION["user_id"] = (int)$user_id;
        $_SESSION["auth"] = true;
        setcookie("authenticated", "1", 0, "/");
        $uri = str_replace("php/reset_password.php", "", $_SERVER["REQUEST_URI"]);
        header("Location: $uri");
    } else {
        echo "<p>Reset link is wrong or expired.<p>Try to generate it again on <a href='$uri'>TwinDB</a>";
    }
    exit(0);
}

main($_GET);
?>
