<?php
include_once("includes.php");

read_config();
$result["success"] = TRUE;
$result["registration_open"] = $registration_open;

echo json_encode($result);