#!/usr/bin/env bash

set -e
project_short_name="twindb"
# check if it can connect to MySQL

set +e
mysql -e "SELECT 1" > /dev/null 2>&1
ret_code=$?
set -e

MYSQL_ARGS=""
if [ $ret_code -ne 0 ]
then
    echo "We need MySQL user to install or upgrade MySQL schema"
    read -p "Enter user[root]: " mysql_user
    test -z "$mysql_user" && mysql_user="root"
    read -p "Enter password[]: " -s mysql_password
    echo ""
    MYSQL_ARGS="-u $mysql_user"
    if ! test -z "$mysql_password"
    then
        MYSQL_ARGS="$MYSQL_ARGS -p$mysql_password"
    fi
fi

if ! test -z "$1"
then
    mysql_db="$1"
else
    read -p "Enter database[$project_short_name]: " mysql_db
    test -z "$mysql_db" && mysql_db="$project_short_name"
fi

set -u

if [ "$mysql_db" = "information_schema" ] || [ "$mysql_db" = "performance_schema" ] 
then
    echo "Database name can't be $mysql_db"
    exit 1
fi

# make sure `mysql`.`time_zone_name` is populated

tz_count=`mysql $MYSQL_ARGS -NBe "select count(*) from mysql.time_zone_name"`
if [ "$tz_count" -eq 0 ]
then
    zoneinfo_dir='/usr/share/zoneinfo'
    echo "There are no records in mysql.time_zone_name"
    cmd="mysql_tzinfo_to_sql $zoneinfo_dir | mysql $MYSQL_ARGS -f mysql"
    echo "Running $cmd"
    cmd1="mysql_tzinfo_to_sql $zoneinfo_dir"
    cmd2="mysql $MYSQL_ARGS -f mysql"
    echo "Running $cmd1 | $cmd2"
    if test -d "$zoneinfo_dir"
    then
        $cmd1 | $cmd2
    else
        echo "There is no directory $zoneinfo_dir"
        echo "We need to populate mysql.time_zone_name with time zones"
        echo "Please do it manually. For more details consult with with http://dev.mysql.com/doc/refman/5.6/en/time-zone-support.html"
        exit 1
    fi
fi
# check if $mysql_db is already created
if [ "`mysql $MYSQL_ARGS -NBe \"SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '$mysql_db'\"`" = "0" ]
then
    echo "Database $mysql_db doesn't exist. Creating it"
    mysql $MYSQL_ARGS -e "CREATE DATABASE \`$mysql_db\`"
    echo "Creating tables in database $mysql_db"
    mysql $MYSQL_ARGS "$mysql_db" < /usr/share/$project_short_name/db/$project_short_name-schema.sql
    echo "Populating tables with initial data"
    mysql $MYSQL_ARGS "$mysql_db" < /usr/share/$project_short_name/db/$project_short_name-data.sql

    # Now let's create a user for the application
    echo "Now we need to create two MySQL accounts for the application: read-write and read-only."
    
    echo    "======== READ/WRITE Account ========"
    read -p "Enter username for READ/WRITE account[${project_short_name}_rw]: " app_user_rw
    test -z "$app_user_rw" && app_user_rw="${project_short_name}_rw"
    read -p "Enter password for READ/WRITE account[]: " -s app_password_rw
    echo ""
    echo    "======== READ-ONLY Account ========"
    read -p "Enter username for READ-ONLY account[${project_short_name}_ro]: " app_user_ro
    test -z "$app_user_ro" && app_user_ro="${project_short_name}_ro"
    read -p "Enter password for READ-ONLY account[]: " -s app_password_ro
    echo ""
    read -p "Enter hostname $app_user_rw and $app_user_ro will be connecting from [localhost]: " app_hostname
    test -z "$app_hostname" && app_hostname="localhost"

    mysql $MYSQL_ARGS -e "GRANT REPLICATION CLIENT ON *.* TO '$app_user_rw'@'$app_hostname' IDENTIFIED BY '$app_password_rw';"
    mysql $MYSQL_ARGS -e "GRANT ALL PRIVILEGES ON \`$mysql_db\`.* TO '$app_user_rw'@'$app_hostname';"
    
    mysql $MYSQL_ARGS -e "GRANT REPLICATION CLIENT ON *.* TO '$app_user_ro'@'$app_hostname' IDENTIFIED BY '$app_password_ro';"
    mysql $MYSQL_ARGS -e "GRANT SELECT ON \`$mysql_db\`.* TO '$app_user_ro'@'$app_hostname';"
    echo "Database is successfully created"
fi

echo "Upgrading MySQL schema"
# database $mysql_db already exists
db_version=`mysql $MYSQL_ARGS -NBe "SELECT version FROM db_version ORDER BY id DESC LIMIT 1" "$mysql_db"`
if test -d "/usr/share/$project_short_name/db/updates"
then
    current_version=`cd /usr/share/$project_short_name/db/updates && ls | sed -e 's/^update-//' -e 's/.sql$//' | sort -n| tail -1`
    if ! test -z "$current_version"
    then
        for u in `seq $(($db_version + 1)) $current_version`
        do
            echo "Applying update $u"
            mysql $MYSQL_ARGS "$mysql_db" < /usr/share/$project_short_name/db/updates/update-${u}.sql
        done
    fi
fi
echo "Schema update is successful"

# Make sure $mysql_db.time_zone_name is not empty too
tz_count=`mysql $MYSQL_ARGS -NBe "select count(*) from ${mysql_db}.time_zone_name"`
if [ "$tz_count" -eq 0 ]
then
    mysql $MYSQL_ARGS -e "INSERT INTO ${mysql_db}.time_zone_name (Name, Time_zone_id) SELECT Name, Time_zone_id from mysql.time_zone_name"
fi
