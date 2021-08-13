FROM --platform=linux/amd64 php:8.0-apache
RUN apt-get update && apt-get install -y mariadb-client git unzip libzip-dev sudo iputils-ping vim libpng-dev libldap2-dev
COPY composer.json /var/www/html/composer.json
COPY package.json /var/www/html/package.json
COPY samples /var/www/html/samples
COPY themes /var/www/html/themes
COPY src /var/www/html/src
COPY INTER-Mediator.php /var/www/html/INTER-Mediator.php
COPY params.php /var/www/html/params.php
COPY dist-docs /var/www/html/dist-docs
COPY dist-docs/container-for-trial/index.php /var/www/html/index.php
COPY dist-docs/container-for-trial/info.php /var/www/html/info.php
RUN docker-php-ext-install bcmath zip pdo pdo_mysql exif gd ldap
RUN uname -m
RUN curl -sS https://getcomposer.org/installer | php; mv composer.phar /usr/local/bin/composer; chmod +x /usr/local/bin/composer && cd /var/www/html && composer install
RUN chown www-data /var/www
RUN sed -i -e "s/mysql:host=127.0.0.1;/mysql:host=db;/g" /var/www/html/params.php