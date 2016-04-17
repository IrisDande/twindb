<?php

# Set meta variables
$project_short_name = "twindb";
$project_long_name  = "TwinDB";
$config = "/etc/$project_short_name/config.php";

# Set global variables
# Set variables to their defaults
# Debug variables
$api_debug = FALSE;
# Email variables
$project_domain = "$project_short_name.com";
$from_name = "$project_long_name Automailer";
$from_email = "no-reply@$project_domain";
# GPG variables
$gpg_homedir="/etc/$project_short_name/gnupg";
# Other
$tmpdir = sys_get_temp_dir();
$registration_open = FALSE;
$free_space = 2*1024*1024*1024;
$dispatcher_ssh_key_private="/usr/share/$project_short_name/ssh/twindb-dispatcher-ssh.key";
$dispatcher_ssh_key_public="/usr/share/$project_short_name/ssh/twindb-dispatcher-ssh.key.pub";
$check_period = 60;

# MySQL variables
$mysql_db=$project_short_name;

$mysql_rw_host="localhost";
$mysql_rw_user="root";
$mysql_rw_password="";
$mysql_ro_host_pool[0]="localhost";
$mysql_ro_user="root";
$mysql_ro_password="";

# Amazon variables
$default_volume_type = "ebs"; # allowed values "ebs" or "s3"
$AWS_ACCESS_KEY_ID = NULL;
$AWS_SECRET_ACCESS_KEY = NULL;

# Non-configurable variables
$server_id = "";
date_default_timezone_set("UTC");
# EOF variables