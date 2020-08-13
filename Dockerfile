FROM php:7.4-apache
RUN apt-get update && apt-get install -y mariadb-client git unzip libzip-dev sudo iputils-ping vim
COPY composer.json /var/www/html/composer.json
COPY package.json /var/www/html/package.json
COPY samples /var/www/html/samples
COPY themes /var/www/html/themes
COPY src /var/www/html/src
COPY INTER-Mediator.php /var/www/html/INTER-Mediator.php
COPY params.php /var/www/html/params.php
COPY dist-docs /var/www/html/dist-docs
RUN docker-php-ext-install bcmath zip pdo pdo_mysql
RUN curl -sS https://getcomposer.org/installer | php; mv composer.phar /usr/local/bin/composer; chmod +x /usr/local/bin/composer && composer install
RUN chown www-data /var/www
RUN sed -i -e "s/mysql:host=127.0.0.1;/mysql:host=db;/g" /var/www/html/params.php