#!/bin/bash

# install mysql
DEBIAN_FRONTEND=noninteractive && apt-get install -y mysql-server mysql-client
service mysql start

# create database
mysql -p$MYSQL_ROOT_PASSWORD < /var/www/undefinedSpace/api-php/backup/versions.sql
