#!/usr/bin/env bash

set -e

hostname="app"

yum -y install https://dev.mysql.com/get/mysql-community-release-el6-5.noarch.rpm
yum -y install https://twindb.com/twindb-release-latest.noarch.rpm
rpm -Uvh http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm
curl -s https://f072a9bde5afb5ba5349ff6bb227931a429226a32d63a628:@packagecloud.io/install/repositories/twindb/internal/script.rpm.sh | sudo bash

packages="
mysql-community-server
unzip
java
java-1.7.0-openjdk
rpm-build
ruby
rubygems
httpd
php
php-cli
php-mysql
php-process
mod_ssl
pwgen
strace
mysql-connector-python
lsof
percona-xtrabackup
redhat-lsb-core
vim
screen
ntpdate
haveged
python-pip
"
yum -y install ${packages}

wget -O /tmp/SenchaCmd-5.1.2.52-linux-x64.run.zip http://cdn.sencha.com/cmd/5.1.2.52/SenchaCmd-5.1.2.52-linux-x64.run.zip

cd /tmp
unzip SenchaCmd-5.1.2.52-linux-x64.run.zip
chmod +x SenchaCmd-5.1.2.52-linux-x64.run
./SenchaCmd-5.1.2.52-linux-x64.run --mode unattended
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

\$AWS_ACCESS_KEY_ID = "AKIAJOAEVHKELG45F56A";
\$AWS_SECRET_ACCESS_KEY = "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";

date_default_timezone_set("UTC");
?>
EOF


# Fix of Illegal chars in JS file
# https://forums.virtualbox.org/viewtopic.php?f=3&t=24905
echo "EnableSendfile Off" > /etc/httpd/conf.d/fix-illigal-char.conf
apachectl restart

ntpdate pool.ntp.org
chmod 711 /home/vagrant

pip install --upgrade pip
pip install awscli
