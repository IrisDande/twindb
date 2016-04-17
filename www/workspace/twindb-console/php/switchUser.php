<?php

include_once("includes.php");

function auth($params){
    global $m;
    
    $email = $m["ro"]->escape_string(trim($params["email"]));
    $password = $m["ro"]->escape_string($params["password"]);
    $enc_password = $m["ro"]->escape_string(crypt($password, get_salt($email)));
    $q = "SELECT";
    $q .= " `user_id`";
    $q .= " FROM `user`";
    $q .= " WHERE `email` = '$email'";
    $q .= " AND `password` = '$enc_password'";
    mysql_ro_execute($q, "www");
    if ($r->num_rows == 0){
        // Auth failed. Wait random time before returning NOK
        sleep(rand(1,5));
        return 0;
    } else {
        $row = $r->fetch_row();
        return $row[0];
    }
    return 0;
}

function main($params){
    global $m;
    global $demo;

    read_config();
    $m["ro"] = get_ro_connection("www");
    $current_user = get_user();
    if ($demo) {
        $result['success'] = false;
        $result['errors']['msg'] = 'Will not switch user in demo mode';
        setcookie('authenticated', false, time()-42000, '/');
        setcookie('email', '', time()-42000, '/');
        echo json_encode($result);
        exit(0);
    }
    if ($current_user != 1) {
        $result['success'] = false;
        $result['errors']['msg'] = 'Only super user can switch users';
        setcookie('authenticated', false, time()-42000, '/');
        setcookie('email', '', time()-42000, '/');
    } else {
        $new_email = $m["ro"]->escape_string($params["new_email"]);
        $q = "SELECT `user_id` FROM `user` WHERE `email` = '$new_email'";
        $r = mysql_ro_execute($q, "www");
        if ( $r->num_rows > 0 ) {
            $row = $r->fetch_row();
            $user_id = $row[0];
            // Start and destroy current session
            session_start();
            session_destroy();
            session_start();
            $_SESSION["user_id"] = (int)$user_id;
            $_SESSION["auth"] = true;
            $result['success'] = true;
            $result['errors']['msg'] = 'User authenticated!';
            setcookie("authenticated", true, 0, "/");
            setcookie("email", $new_email, 0, "/");
        } else {
            $result['success'] = false;
            $result['errors']['msg'] = "Failed to switch to $new_email";
            setcookie('authenticated', false, time()-42000, '/');
            setcookie('email', '', time()-42000, '/');
        }
    }
    echo json_encode($result);
}

main($_POST);
?>
