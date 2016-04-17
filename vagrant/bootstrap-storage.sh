#!/usr/bin/env bash

set -e

yum -y install https://dev.mysql.com/get/mysql-community-release-el6-5.noarch.rpm
yum -y install https://twindb.com/twindb-release-latest.noarch.rpm
rpm -Uvh http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm

packages="pwgen
strace"

yum -y install ${packages}

echo "192.168.50.100    dispatcher.twindb.com" >> /etc/hosts
echo "192.168.50.100    console.dev.twindb.com" >> /etc/hosts
