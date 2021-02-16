FROM php:8.0-apache
RUN apt-get update && apt install -y mariadb-client postgresql-client libpq-dev sqlite3 libsqlite3-dev git unzip libzip-dev sudo iputils-ping vim libpng-dev libldap2-dev
COPY Adapter_DBServer.js /var/www/html/Adapter_DBServer.js
COPY Adapter_LocalDB.js /var/www/html/Adapter_LocalDB.js
COPY DB_FileMaker_DataAPI.php /var/www/html/DB_FileMaker_DataAPI.php
COPY DB_FileMaker_FX.php /var/www/html/DB_FileMaker_FX.php
COPY DB_Formatters.php /var/www/html/DB_Formatters.php
COPY DB_Interfaces.php /var/www/html/DB_Interfaces.php
COPY DB_Logger.php /var/www/html/DB_Logger.php
COPY DB_Null.php /var/www/html/DB_Null.php
COPY DB_PDO.php /var/www/html/DB_PDO.php
COPY DB_Proxy.php /var/www/html/DB_Proxy.php
COPY DB_Settings.php /var/www/html/DB_Settings.php
COPY DB_Support /var/www/html/DB_Support
COPY DB_TextFile.php /var/www/html/DB_TextFile.php
COPY DB_UseSharedObjects.php /var/www/html/DB_UseSharedObjects.php
COPY Data_Converter /var/www/html/Data_Converter
COPY DefinitionChecker.php /var/www/html/DefinitionChecker.php
COPY FieldDivider.php /var/www/html/FieldDivider.php
COPY FileUploader.php /var/www/html/FileUploader.php
COPY GenerateJSCode.php /var/www/html/GenerateJSCode.php
COPY IMLocale.php /var/www/html/IMLocale.php
COPY IMLocaleCurrencyTable.php /var/www/html/IMLocaleCurrencyTable.php
COPY IMLocaleFormatTable.php /var/www/html/IMLocaleFormatTable.php
COPY IMLocaleStringTable.php /var/www/html/IMLocaleStringTable.php
COPY IMNumberFormatter.php /var/www/html/IMNumberFormatter.php
COPY IMUtil.php /var/www/html/IMUtil.php
COPY INTER-Mediator-Calc.js /var/www/html/INTER-Mediator-Calc.js
COPY INTER-Mediator-Context.js /var/www/html/INTER-Mediator-Context.js
COPY INTER-Mediator-DoOnStart.js /var/www/html/INTER-Mediator-DoOnStart.js
COPY INTER-Mediator-Element.js /var/www/html/INTER-Mediator-Element.js
COPY INTER-Mediator-Events.js /var/www/html/INTER-Mediator-Events.js
COPY INTER-Mediator-Format.js /var/www/html/INTER-Mediator-Format.js
COPY INTER-Mediator-Lib.js /var/www/html/INTER-Mediator-Lib.js
COPY INTER-Mediator-Log.js /var/www/html/INTER-Mediator-Log.js
COPY INTER-Mediator-Navi.js /var/www/html/INTER-Mediator-Navi.js
COPY INTER-Mediator-Page.js /var/www/html/INTER-Mediator-Page.js
COPY INTER-Mediator-Parts.js /var/www/html/INTER-Mediator-Parts.js
COPY INTER-Mediator-Queuing.js /var/www/html/INTER-Mediator-Queuing.js
COPY INTER-Mediator-Support /var/www/html/INTER-Mediator-Support
COPY INTER-Mediator-UI.js /var/www/html/INTER-Mediator-UI.js
COPY INTER-Mediator.js /var/www/html/INTER-Mediator.js
COPY INTER-Mediator.php /var/www/html/INTER-Mediator.php
COPY LDAPAuth.php /var/www/html/LDAPAuth.php
COPY LineDivider.php /var/www/html/LineDivider.php
COPY MediaAccess.php /var/www/html/MediaAccess.php
COPY MessageStrings.php /var/www/html/MessageStrings.php
COPY MessageStrings_ja.php /var/www/html/MessageStrings_ja.php
COPY NotifyServer.php /var/www/html/NotifyServer.php
COPY OAuthAuth.php /var/www/html/OAuthAuth.php
COPY SendMail.php /var/www/html/SendMail.php
COPY Theme.php /var/www/html/Theme.php
COPY dist-docs /var/www/html/dist-docs
COPY lib /var/www/html/lib
COPY metadata.json /var/www/html/metadata.json
COPY params.php /var/www/html/params.php
COPY samples /var/www/html/samples
COPY themes /var/www/html/themes
RUN docker-php-ext-install bcmath zip pdo pdo_mysql pdo_pgsql pdo_sqlite exif gd ldap
RUN chown www-data /var/www
RUN sed -i -e "s/mysql:host=localhost;dbname=test_db;charset=utf8/mysql:host=mariadb;dbname=test_db;charset=utf8mb4/g" /var/www/html/params.php
RUN find /var/www/html/samples/Hands-on/Session1/ -type f -print0 | xargs -0 sed -i -e "s/mysql:host=localhost;dbname=test_db;charset=utf8mb4/mysql:host=mariadb;dbname=test_db;charset=utf8mb4/g"
RUN find /var/www/html/samples/ -type f -print0 | xargs -0 sed -i -e "s/pgsql:host=localhost;port=5432;dbname=test_db/pgsql:host=postgresql;port=5432;dbname=test_db/g"

COPY INTER-Mediator-UnitTest /var/www/html/INTER-Mediator-UnitTest
RUN curl -sS https://getcomposer.org/installer | php; mv composer.phar /usr/local/bin/composer; chmod +x /usr/local/bin/composer
RUN find /var/www/html/INTER-Mediator-UnitTest/ -type f -print0 | xargs -0 sed -i -e "s/mysql:dbname=test_db;host=127.0.0.1;charset=utf8/mysql:host=mariadb;dbname=test_db;charset=utf8mb4/g"
RUN find /var/www/html/INTER-Mediator-UnitTest/*.php -type f -print0 | xargs -0 sed -i -e "s/assertContains/assertStringContainsStringIgnoringCase/g"
RUN find /var/www/html/INTER-Mediator-UnitTest/*.php -type f -print0 | xargs -0 sed -i -e "s/assertNotContains/assertStringNotContainsStringIgnoringCase/g"
RUN cd /var/www/html && composer require 'phpunit/phpunit=9.5.x'; composer install
ENV DOCKER true

RUN mkdir /var/db
RUN mkdir /var/db/im
COPY dist-docs/sample_schema_sqlite.txt /tmp/sample_schema_sqlite.txt
RUN sqlite3 /var/db/im/sample.sq3 < /tmp/sample_schema_sqlite.txt

#RUN cd /var/www/html && vendor/bin/phpunit --globals-backup ./INTER-Mediator-UnitTest/INTERMediator_AllTests.php
