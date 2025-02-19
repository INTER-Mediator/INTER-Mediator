FROM --platform=linux/x86_64 php:8.4-apache

# Refer to https://tt-computing.com/docker-php8_0-lamp, Thanks!

# Composer
ARG composer_ver=2.8.4
COPY --from=ghcr.io/mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync \
&& install-php-extensions @composer-${composer_ver}

# その他パッケージ、PHP拡張
RUN apt-get update
RUN apt-get install -y git unzip npm
# PHP intl
RUN apt-get install -y libicu-dev
RUN docker-php-ext-install intl
# PHP EXIF
RUN docker-php-ext-install exif
# PHP GD
RUN apt-get install -y zlib1g-dev libpng-dev libjpeg62-turbo-dev
RUN docker-php-ext-configure gd --with-jpeg
RUN docker-php-ext-install -j1 gd
# PHP PDO MySQL
RUN docker-php-ext-install pdo_mysql

RUN rm -rf /var/www/html/* && git clone https://github.com/INTER-Mediator/IMApp_Trial /var/www/html
RUN chown -R www-data /var/www
RUN cd /var/www/html; composer update
