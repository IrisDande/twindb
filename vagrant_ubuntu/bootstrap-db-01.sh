#!/usr/bin/env bash

set -e


hostname="db01"

dd if=/dev/zero of=/swapfile1 bs=1024 count=524288
chown root:root /swapfile1
chmod 0600 /swapfile1
mkswap /swapfile1
swapon /swapfile1
echo "/swapfile1 none swap sw 0 0" >> /etc/fstab

wget -qO /tmp/mysql-apt-config_0.3.5-1ubuntu14.04_all.deb \
    https://dev.mysql.com/get/mysql-apt-config_0.3.5-1ubuntu14.04_all.deb

export DEBIAN_FRONTEND=noninteractive

debconf-set-selections <<< 'mysql-apt-config mysql-apt-config/select-server string mysql-5.6'
debconf-set-selections <<< 'mysql-apt-config mysql-apt-config/select-workbench string workbench-6.3'
debconf-set-selections <<< 'mysql-apt-config mysql-apt-config/select-utilities string mysql-utilities-1.5'
debconf-set-selections <<< 'mysql-apt-config mysql-apt-config/select-connector-python string connector-python-2.0'
debconf-set-selections <<< 'mysql-server mysql-server/root_password password MySuperPassword'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password MySuperPassword'
apt-get update

dpkg -i /tmp/mysql-apt-config_0.3.5-1ubuntu14.04_all.deb

wget -qO /etc/apt/sources.list.d/twindb.list http://repo.twindb.com/twindb.`lsb_release -cs`.list

apt-key adv --keyserver pgp.mit.edu --recv-keys 2A9C65370E199794 || \
    apt-key adv --keyserver keys.gnupg.net --recv-keys 2A9C65370E199794

packages="
mysql-server-5.6
mysql-client-5.6
mysql-connector-python
percona-xtrabackup
haveged
"
apt-get update
for p in $packages
do
    # apt-get install -o Dpkg::Options::="--force-confold" --force-yes -y $p
    apt-get -y install $p
done

echo "127.0.0.1         $hostname" >> /etc/hosts
echo "192.168.50.100    dispatcher.twindb.com" >> /etc/hosts
echo "192.168.50.100    console.dev.twindb.com" >> /etc/hosts



cat <<EOF > /etc/mysql/conf.d/twindb.cnf
[mysqld]
server_id=101
log_bin
bind-address    = 0.0.0.0
EOF

cat <<EOF > /root/.my.cnf
[client]
user=root
password=MySuperPassword
EOF

cp /root/.my.cnf /home/vagrant/.my.cnf
chmod 644 /home/vagrant/.my.cnf

function wait_for_mysql() {
    # wait till mysql starts
    timeout=300
    mysql_started="NO"
    while [ $timeout -gt 0 ]
    do
        if ! [ "`mysql -e 'SELECT 1'`" = "1" ]
        then
            echo "SUCCESS"
            break
        fi
        sleep 1
        let timeout=$timeout-1
    done
}

service mysql restart

if [ "`wait_for_mysql`" = "SUCCESS" ]
then
    mysql -u root -e "CREATE USER 'replication'@'%' IDENTIFIED BY 'bigs3cret'"
    mysql -u root -e "GRANT REPLICATION SLAVE ON *.* TO 'replication'@'%'"
else
    echo "MySQL failed to start"
    exit -1
fi

echo $hostname > /etc/hostname
hostname $hostname
