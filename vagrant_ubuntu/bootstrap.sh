#!/usr/bin/env bash

set -eux

hostname="app"

wget -qO /etc/apt/sources.list.d/twindb.list http://repo.twindb.com/twindb.`lsb_release -cs`.list

apt-key adv --keyserver pgp.mit.edu --recv-keys 2A9C65370E199794 || \
    apt-key adv --keyserver keys.gnupg.net --recv-keys 2A9C65370E199794
apt-get update

apt-get -y install build-essential devscripts debhelper
apt-get -y install ntp

wget -q -O /tmp/SenchaCmd-5.1.2.52-linux-x64.run.zip http://cdn.sencha.com/cmd/5.1.2.52/SenchaCmd-5.1.2.52-linux-x64.run.zip

apt-get -y install unzip openjdk-7-jre-headless
cd /tmp 
unzip SenchaCmd-5.1.2.52-linux-x64.run.zip 
chmod +x ./SenchaCmd-5.1.2.52-linux-x64.run
sudo -H -u vagrant ./SenchaCmd-5.1.2.52-linux-x64.run --mode unattended
cd -

echo "192.168.50.100    $hostname" >> /etc/hosts
echo "192.168.50.100    dispatcher.twindb.com" >> /etc/hosts
echo "192.168.50.100    console.twindb.com" >> /etc/hosts
echo "192.168.50.100    console.dev.twindb.com" >> /etc/hosts

cat <<EOF > /root/.vimrc
syntax on
set tabstop=4 shiftwidth=4 expandtab
set backspace=start,eol,indent
EOF

mkdir /etc/twindb/
cat <<EOF > /etc/twindb/config.php
<?php

\$mysql_db="twindb";
\$mysql_rw_host="192.168.50.101";
\$mysql_rw_user="twindb_rw";
\$mysql_rw_password="twindb_rw";
\$mysql_ro_host_pool[0]="192.168.50.102";
\$mysql_ro_user="twindb_ro";
\$mysql_ro_password="twindb_ro";

\$mysql_ro_host_pool[1]="192.168.50.103";
\$mysql_ro_user="twindb_ro";
\$mysql_ro_password="twindb_ro";

\$project_short_name = "twindb";
\$project_long_name  = "TwinDB";
\$project_domain     = "\$project_short_name.com";

\$from_name = "\$project_long_name Automailer";
\$from_email = "no-reply@\$project_domain";

\$gpg_homedir="/etc/\$project_short_name/gnupg";
\$tmpdir = sys_get_temp_dir();

date_default_timezone_set("UTC");
?>
EOF

hostname $hostname
echo $hostname > /etc/hostname

dd if=/dev/zero of=/swapfile1 bs=1024 count=524288
chown root:root /swapfile1
chmod 0600 /swapfile1
mkswap /swapfile1
swapon /swapfile1
echo "/swapfile1 none swap sw 0 0" >> /etc/fstab

debconf-set-selections <<< 'mysql-server mysql-server/root_password password MySuperPassword'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password MySuperPassword'
apt-get update
apt-get -y install apache2 php5 php5-mysql bash sed cron logrotate unzip openssh-client openssh-server mysql-server mysql-client
exit 0