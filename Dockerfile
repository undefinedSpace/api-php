FROM ubuntu:16.04

#
# Maintainer
#
MAINTAINER paul@drteam.rocks

#
# Do not ask us
#
ENV DEBIAN_FRONTEND noninteractive

#
# Default MySQL root password
#
ENV MYSQL_ROOT_PASSWORD root

#
# Port available from external network
#
EXPOSE 22 80 3306

#
# Set the MySQL default params
#
RUN bash -c 'debconf-set-selections <<< "mysql-server mysql-server/root_password password $MYSQL_ROOT_PASSWORD"'
RUN bash -c 'debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $MYSQL_ROOT_PASSWORD"'
RUN locale-gen ru_RU.UTF-8 && dpkg-reconfigure locales

#
# Update apt
#
RUN apt-get update -q -q
RUN apt-get upgrade --yes --force-yes

#
# Install WebServer
#
RUN apt-get install nginx php-fpm php-mysql wget mc composer git zip unzip --yes --force-yes

#
# Install MySQL
#
RUN apt-get install mysql-client mysql-server --yes --force-yes

#
# Setup a root password; simple enough to remember, but hard enough that
# it won't be cracked immediately.  (ha!)
#
RUN echo "root:mypasswd" | chpasswd

#
# Create the project root dir
#
RUN mkdir -p /var/www/undefinedSpace/api-php/

#
# Create the workspace
#
WORKDIR /var/www/undefinedSpace/api-php/
ADD ./app ./app
ADD ./extra ./extra
ADD ./public ./public
ADD ./composer.json ./composer.json
ADD ./extra/nginx.conf /etc/nginx/sites-enabled/default

#
# Composer update
#
RUN composer update

#
# Install database
#
RUN ./extra/install.sh

#
# Run services
#
ENTRYPOINT ./extra/run.sh
