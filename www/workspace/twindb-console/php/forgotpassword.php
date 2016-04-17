<?php

include_once("includes.php");

function send_reset_password_code($email, $code){
    global $from_name;
    global $from_email;
    $subject = "Reset password request";
    $header = "From: ". $from_name . " <" . $from_email . ">\r\n";
    $mail_body = "Hello,

Somebody on your behalf has just requested to reset your password.

To reset the password you may follow the link

https://".$_SERVER["HTTP_HOST"]."/php/reset_password.php?code=$code

If you haven't requested to reset your password simply ignore this e-mail.

With best regards,
TwinDB Team ";
    mail($email, $subject, $mail_body, $header, "-f$from_email");
}


function generate_reset_code($params){
    global $m;
    $email = $m["ro"]->escape_string(trim($params["email"]));
    
    $q = "SELECT";
    $q .= " `user_id`";
    $q .= " FROM `user`";
    $q .= " WHERE `email` = '$email'";
    $r = mysql_ro_execute($q, "www");
    if ($r->num_rows == 0){
        return 0;
    } else {
        $row = $r->fetch_row();
        $user_id = (int)$row[0];
        start_transaction("www");
        $code = md5($user_id * rand());
        $q = "DELETE FROM `password_reset_code` WHERE `user_id` = $user_id";
        mysql_rw_execute($q, "www");
        // Expire the reset code in 20 minutes
        $q = "INSERT INTO `password_reset_code`(`user_id`, `code`, `expires`)";
        $q .= " VALUES($user_id, '$code', FROM_UNIXTIME(UNIX_TIMESTAMP(NOW()) + 60*20))";
        mysql_rw_execute($q, "www");
        commit_transaction("www");
        send_reset_password_code($email, $code);
        return $user_id;
    }
    return 0;
}

function main($params){
    global $m;

    read_config();
    $m["rw"] = get_rw_connection("www");
    $m["ro"] = get_ro_connection("www");
    $user_id = generate_reset_code($params);
    
    $result['success'] = true;
    $result['errors']['msg'] = 'Reset link generated';
    echo json_encode($result);
}

main($_POST);
?>
