# undefinedSpace (Rest API on PHP)

undefinedSpace - It's a system for changes monitoring of files on the hard drive, helpful if you do not know which file was changed a couple of days ago.

undefinedSpace - complex of programs that consists of 3 main blocks: [Daemon](https://github.com/undefinedSpace/daemon), API ([PHP](https://github.com/undefinedSpace/api-php), [JS](https://github.com/undefinedSpace/nodejs-api)), Web UI (soon).

## How to install

Small installation guide for several combinations of platforms.

### Debian + NGINX

* Install packages `apt-get install nginx composer php php-fpm php-mysql`
* Copy the [nginx.conf](extra/nginx.conf) example to your `/etc/nginx/sites-enabled/` and don't forgot to change the **root_path** and **server_name** for example to **api**
* (optional) Add the line into your `/etc/hosts` like `127.0.0.1 api`

### After your web server is ready

* Download the [undefinedSpace Rest API on PHP](https://github.com/undefinedSpace/api-php/archive/master.zip) package

        wget -c https://github.com/undefinedSpace/api-php/archive/master.zip

* Extract the archive

        unzip master.zip

* Change directory to source folder

        cd api-php

* Now we need to create the database structure:

        mysql -u root -p < extra/versions.sql
        mysql -u root -p -e "CREATE USER 'us'@'localhost' IDENTIFIED BY 'us_password';"
        mysql -u root -p -e "GRANT ALL ON undefinedSpace.* TO 'us'@'localhost';"

**Attention! For security reasons password of your database must be different from the example!**

* Copy two configs from example:

        cp app/Configs/config.examples.php app/Configs/config.php
        cp app/Configs/database.examples.php app/Configs/database.php

* Change values inside configs to yours
* And finally we need download all php dependencies of this project:

        composer update

## Rest API

* [Sync](docs/Sync.md)
* [Projects](docs/Projects.md)
* [Servers](docs/Servers.md)
* [Events](docs/Events.md)
