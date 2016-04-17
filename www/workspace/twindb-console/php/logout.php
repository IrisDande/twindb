<?php
session_start();
session_destroy();
setcookie(session_name(), '', time()-42000, '/');
setcookie('authenticated', '', time()-42000, '/');

$result = array();
$result['success'] = true;
$result['errors']['msg'] = 'User logged out';
echo json_encode($result);
?>
