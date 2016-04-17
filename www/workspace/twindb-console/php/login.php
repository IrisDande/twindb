<?php

include_once("includes.php");

function auth($params){
    global $m;
    
    $email = $m["ro"]->escape_string(trim($params["email"]));
    $password = $m["ro"]->escape_string($params["password"]);
    $enc_password = $m["ro"]->escape_string(crypt($password, get_salt($email)));
    $q = "SELECT";
    $q .= " `user_id`,";
    $q .= " `first_name`,";
    $q .= " `last_name`";
    $q .= " FROM `user`";
    $q .= " WHERE `email` = '$email'";
    $q .= " AND (`password` = '$enc_password' OR `password` IS NULL)";
    $r = mysql_ro_execute($q, "www");
    if ($r->num_rows == 0){
        // Auth failed. Wait random time before returning NOK
        sleep(rand(1,5));
        return 0;
    } else {
        $row = $r->fetch_assoc();
        return $row;
    }
}

function main($params){
    global $m;

    read_config();
    $m["ro"] = get_ro_connection("www");
    $auth_response = auth($params);
    $user_id = $auth_response["user_id"];
    $first_name = $auth_response["first_name"];
    $last_name = $auth_response["last_name"];
    if($user_id != 0){
        session_start();
        $_SESSION["user_id"] = (int)$user_id;
        $_SESSION["auth"] = true;
        $result['success'] = true;
        $result['errors']['msg'] = 'User authenticated!';
        setcookie("authenticated", true, 0, "/");
        setcookie("email", $params["email"], 0, "/");
        if (strlen($first_name) > 0 || strlen($last_name)) {
            setcookie("user_name", "$first_name&nbsp;$last_name", 0, "/");
        }
    } else {
        $result['success'] = false;
        $result['errors']['msg'] = 'Incorrect user or password';
        setcookie('authenticated', false, time()-42000, '/');
        setcookie('email', '', time()-42000, '/');
        setcookie("user_name", '', time()-42000, "/");
    }
    echo json_encode($result);
}

main($_POST);
?>
