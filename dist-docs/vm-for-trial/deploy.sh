#!/bin/bash
#
# setup shell script for CentOS 7.8, Ubuntu Server 18.04 and Alpine Linux 3.10
#
# This file can get from the URL below.
# https://raw.githubusercontent.com/INTER-Mediator/INTER-Mediator/master/dist-docs/vm-for-trial/deploy.sh
#
# How to test using Serverspec 2 after running this file on the guest of VM:
#
# - Install Ruby on the host of VM (You don't need installing Ruby on macOS usually)
# - Install Serverspec 2 on the host of VM (ex. "sudo gem install serverspec" on macOS)
#   See detail: https://serverspec.org/
# - Change directory to "vm-for-trial" directory on the host of VM
# - Run "rake spec" on the host of VM
#

OS=`cat /etc/os-release | grep ^ID | head -n 1 | cut -d'=' -f2 | cut -d'"' -f2`

if [ $OS = 'centos' ] ; then
    WEBROOT="/var/www/html"
    WWWUSERNAME="apache"
elif [ $OS = 'alpine' ] ; then
    WEBROOT="/var/www/localhost/htdocs"
    OLDWEBROOT="/var/www/html"
    WWWUSERNAME="apache"
else
    WEBROOT="/var/www/html"
    WWWUSERNAME="www-data"
fi

IMROOT="${WEBROOT}/INTER-Mediator"
IMSUPPORT="${IMROOT}/src/php/DB/Support"
IMSAMPLE="${IMROOT}/samples"
IMUNITTEST="${IMROOT}/spec/INTER-Mediator-UnitTest"
IMDISTDOC="${IMROOT}/dist-docs"
IMVMROOT="${IMROOT}/dist-docs/vm-for-trial"
IMSELINUX="${IMROOT}/dist-docs/selinux"
APACHEOPTCONF="/etc/apache2/sites-enabled/inter-mediator-server.conf"
SMBCONF="/etc/samba/smb.conf"

IMREPOSITORY="https://github.com/INTER-Mediator/INTER-Mediator.git"
#IMREPOSITORY="https://github.com/msyk/INTER-Mediator.git"
IMBRANCH="master"

RESULT=`id developer 2>/dev/null`
if [ $RESULT = '' ] ; then
    adduser developer
    yes im4135dev | passwd developer
    echo "developer ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/developer
    chmod 440 /etc/sudoers.d/developer
    touch /home/developer/.viminfo
    chown developer:developer /home/developer/.viminfo
fi

if [ $OS = 'centos' ] ; then
    echo "127.0.0.1 localhost inter-mediator-server" > /etc/hosts
    nmcli c mod "有線接続 1" ipv4.addresses 192.168.56.101/24
    nmcli c mod "有線接続 1" connection.id enp0s8
    mv /etc/sysconfig/network-scripts/ifcfg-有線接続_1 /etc/sysconfig/network-scripts/ifcfg-enp0s8
    yum install -y httpd
    yum install -y mariadb-server
    yum install -y postgresql-server
    yum install -y epel-release
    yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm
    yum install -y --enablerepo=epel,remi,remi-php73 php php-mbstring php-mysqlnd php-pdo php-pgsql php-xml php-bcmath php-process
    yum install -y mariadb-devel
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    yum install -y wget
    wget https://phar.phpunit.de/phpunit-8.phar -P /tmp
    mv /tmp/phpunit-8.phar /usr/local/bin/phpunit
    chmod +x /usr/local/bin/phpunit
    yum install -y git
    yum install -y nodejs
    yum install -y npm
    yum install -y samba
    yum install -y bzip2
    yum install -y fontconfig-devel
    npm install -g buster --unsafe-perm
    npm install -g phantomjs-prebuilt --unsafe-perm
    systemctl enable httpd.service
    systemctl enable mariadb.service
    systemctl enable postgresql.service
    postgresql-setup initdb
    systemctl enable smb.service
elif [ $OS = 'alpine' ] ; then
    echo "127.0.0.1 localhost inter-mediator-server" > /etc/hosts
    ip addr add 192.168.56.101/24 dev eth1
    echo "auto lo" > /etc/network/interfaces
    echo "iface lo inet loopback" >> /etc/network/interfaces
    echo "" >> /etc/network/interfaces
    echo "auto eth0" >> /etc/network/interfaces
    echo "iface eth0 inet dhcp" >> /etc/network/interfaces
    echo "	hostname inter-mediator-server" >> /etc/network/interfaces
    echo "" >> /etc/network/interfaces
    echo "auto eth1" >> /etc/network/interfaces
    echo "iface eth1 inet static" >> /etc/network/interfaces
    echo "	address 192.168.56.101" >> /etc/network/interfaces
    echo "	netmask 255.255.255.0" >> /etc/network/interfaces

    echo "#/media/cdrom/apks" > /etc/apk/repositories
    echo "http://dl-cdn.alpinelinux.org/alpine/v3.10/main" >> /etc/apk/repositories
    echo "http://dl-cdn.alpinelinux.org/alpine/v3.10/community" >> /etc/apk/repositories
    echo "#http://dl-cdn.alpinelinux.org/alpine/edge/main" >> /etc/apk/repositories
    echo "#http://dl-cdn.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories
    echo "#http://dl-cdn.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories

    apk update
    apk upgrade
    apk add --no-cache curl
    apk add --no-cache apache2
    apk add --no-cache apache2-proxy
    apk add --no-cache mariadb-client
    apk add --no-cache mariadb
    apk add --no-cache postgresql
    apk add --no-cache sqlite
    apk add --no-cache acl
    apk add --no-cache php7
    apk add --no-cache php7-apache2
    apk add --no-cache php7-curl
    apk add --no-cache php7-pdo
    apk add --no-cache php7-pdo_mysql
    apk add --no-cache php7-pdo_pgsql
    apk add --no-cache php7-pdo_sqlite
    apk add --no-cache php7-openssl
    apk add --no-cache php7-dom
    apk add --no-cache php7-json
    apk add --no-cache php7-bcmath
    apk add --no-cache php7-phar
    apk add --no-cache php7-mbstring
    apk add --no-cache php7-xml
    apk add --no-cache php7-xmlwriter
    apk add --no-cache php7-tokenizer
    apk add --no-cache php7-simplexml
    apk add --no-cache php7-session
    apk add --no-cache php7-mysqli
    apk add --no-cache composer
    apk add --no-cache libbsd=0.8.6-r2
    apk add --no-cache python
    apk add --no-cache git
    #apk add --no-cache nodejs
    #apk add --no-cache nodejs-npm
    apk add --no-cache samba
    apk add --no-cache dbus
    #apk add --no-cache firefox
    apk add --no-cache chromium libgudev
    apk add --no-cache xvfb
    apk add --no-cache fontconfig-dev

    #apk add --no-cache virtualbox-additions-grsec
    apk add --no-cache virtualbox-guest-additions
    apk add --no-cache virtualbox-guest-modules-grsec

    apk add --no-cache ca-certificates
    apk add --no-cache wget
    update-ca-certificates
    #wget https://phar.phpunit.de/phpunit-6.phar -P /tmp
    #mv /tmp/phpunit-6.phar /usr/local/bin/phpunit
    #chmod +x /usr/local/bin/phpunit

    rc-service apache2 start
    rc-update add apache2
    /etc/init.d/postgresql setup
    rc-service postgresql start
    rc-update add postgresql
    rc-service dbus start
    rc-update add dbus
    rc-service samba start
    rc-update add samba
else
    echo "set grub-pc/install_devices /dev/sda" | debconf-communicate
    aptitude clean
    aptitude update
    aptitude full-upgrade --assume-yes
    apt-get install apache2 --assume-yes
    apt-get install openssh-server --assume-yes
    apt-get install mysql-server --assume-yes
    apt-get install postgresql --assume-yes
    apt-get install sqlite --assume-yes
    apt-get install acl --assume-yes
    apt-get install libmysqlclient-dev --assume-yes
    apt-get install php --assume-yes
    apt-get install php-cli --assume-yes
    apt-get install php-mysql --assume-yes
    apt-get install php-pgsql --assume-yes
    apt-get install php7.2-sqlite --assume-yes
    apt-get install php7.2-xml --assume-yes
    apt-get install php-bcmath --assume-yes
    apt-get install php-curl --assume-yes
    apt-get install php-gd --assume-yes
    apt-get install php-xmlrpc --assume-yes
    apt-get install php-intl --assume-yes
    apt-get install git --assume-yes
    #apt-get install nodejs --assume-yes && update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10
    #apt-get install nodejs-legacy --assume-yes
    #apt-get install npm --assume-yes
    apt-get install libfontconfig1 --assume-yes
    apt-get install samba --assume-yes
    #apt-get install phpunit --assume-yes
    #apt-get install firefox --assume-yes
    apt-get install chromium-browser --assume-yes
    apt-get install xvfb --assume-yes

    # for Japanese
    apt-get install language-pack-ja --assume-yes
    apt-get install fbterm --assume-yes
    apt-get install unifont --assume-yes

    # Switch to the current security-supported stack by running
    apt-get install --assume-yes linux-generic-lts-xenial linux-image-generic-lts-xenial

    aptitude clean
fi

if [ $OS = 'alpine' ] ; then
    addgroup im-developer
    addgroup developer im-developer
    addgroup ${WWWUSERNAME} im-developer
else
    groupadd im-developer
    usermod -a -G im-developer developer
    usermod -a -G im-developer ${WWWUSERNAME}
fi

yes im4135dev | passwd postgres
if [ $OS = 'centos' ] ; then
    sed -i -e "s/\ peer/\ trust/g" /var/lib/pgsql/data/pg_hba.conf
    sed -i -e "s/\ ident/\ trust/g" /var/lib/pgsql/data/pg_hba.conf
    systemctl restart postgresql.service
fi

mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' identified by 'im4135dev';" -u root
if [ $OS = 'centos' ] ; then
    echo "[mysqld]" > /etc/my.cnf.d/im.cnf
    echo "datadir=/var/lib/mysql" >> /etc/my.cnf.d/im.cnf
    echo "socket=/var/lib/mysql/mysql.sock" >> /etc/my.cnf.d/im.cnf
    echo "character-set-server=utf8mb4" >> /etc/my.cnf.d/im.cnf
    echo "skip-character-set-client-handshake" >> /etc/my.cnf.d/im.cnf
    echo "[client]" >> /etc/my.cnf.d/im.cnf
    echo "default-character-set=utf8mb4" >> /etc/my.cnf.d/im.cnf
    echo "[mysqldump]" >> /etc/my.cnf.d/im.cnf
    echo "default-character-set=utf8mb4" >> /etc/my.cnf.d/im.cnf
    echo "[mysql]" >> /etc/my.cnf.d/im.cnf
    echo "default-character-set=utf8mb4" >> /etc/my.cnf.d/im.cnf
    systemctl start mariadb
    mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' identified by 'im4135dev';" -u root
elif [ $OS = 'alpine' ] ; then
    echo "[mysqld]" > /etc/mysql/my.cnf
    echo "datadir=/var/lib/mysql" >> /etc/mysql/my.cnf
    echo "socket=/run/mysqld/mysqld.sock" >> /etc/mysql/my.cnf
    echo "user=mysql" >> /etc/mysql/my.cnf
    echo "# Disabling symbolic-links is recommended to prevent assorted security risks" >> /etc/mysql/my.cnf
    echo "symbolic-links=0" >> /etc/mysql/my.cnf
    echo "character-set-server=utf8mb4" >> /etc/mysql/my.cnf
    echo "skip-character-set-client-handshake" >> /etc/mysql/my.cnf
    echo "" >> /etc/mysql/my.cnf
    echo "[mysqld_safe]" >> /etc/mysql/my.cnf
    echo "#log-error=/var/log/mysqld.log" >> /etc/mysql/my.cnf
    echo "pid-file=/var/run/mysqld/mysqld.pid" >> /etc/mysql/my.cnf
    echo "" >> /etc/mysql/my.cnf
    echo "[client]" >> /etc/mysql/my.cnf
    echo "default-character-set=utf8mb4" >> /etc/mysql/my.cnf
    echo "" >> /etc/mysql/my.cnf
    echo "[mysqldump]" >> /etc/mysql/my.cnf
    echo "default-character-set=utf8mb4" >> /etc/mysql/my.cnf
    echo "" >> /etc/mysql/my.cnf
    echo "[mysql]" >> /etc/mysql/my.cnf
    echo "default-character-set=utf8mb4" >> /etc/mysql/my.cnf

    sed -i "s/^skip-networking/#skip-networking/" /etc/my.cnf.d/mariadb-server.cnf

    /etc/init.d/mariadb setup
    rc-service mariadb start
    /usr/bin/mysqladmin -u root password 'im4135dev'
    rc-update add mariadb
else
    echo "[mysqld]" > /etc/mysql/conf.d/im.cnf
    echo "character-set-server=utf8mb4" >> /etc/mysql/conf.d/im.cnf
    echo "skip-character-set-client-handshake" >> /etc/mysql/conf.d/im.cnf
    echo "[client]" >> /etc/mysql/conf.d/im.cnf
    echo "default-character-set=utf8mb4" >> /etc/mysql/conf.d/im.cnf
    echo "[mysqldump]" >> /etc/mysql/conf.d/im.cnf
    echo "default-character-set=utf8mb4" >> /etc/mysql/conf.d/im.cnf
    echo "[mysql]" >> /etc/mysql/conf.d/im.cnf
    echo "default-character-set=utf8mb4" >> /etc/mysql/conf.d/im.cnf
fi

a2enmod headers
echo "#Header add Content-Security-Policy \"default-src 'self'\"" > "${APACHEOPTCONF}"

cd "${WEBROOT}"
git clone --branch ${IMBRANCH} ${IMREPOSITORY}
cd INTER-Mediator
git checkout ${IMBRANCH}
#git remote add upstream ${IMREPOSITORY} checkout ${IMBRANCH}
#result=`git diff master..release 2> /dev/null`
#if [ "$result" = '' ]; then
    #git checkout stable
#    git checkout master
#fi

rm -f "${WEBROOT}/index.html"
cd "${WEBROOT}"
ln -s "${IMVMROOT}/index.php" index.php

echo 'AddType "text/html; charset=UTF-8" .html' > "${WEBROOT}/.htaccess"

echo '<?php' > "${WEBROOT}/params.php"
echo "\$dbUser = 'web';" >> "${WEBROOT}/params.php"
echo "\$dbPassword = 'password';" >> "${WEBROOT}/params.php"
if [ $OS = 'centos' ] ; then
    echo "\$dbDSN = 'mysql:unix_socket=/var/lib/mysql/mysql.sock;dbname=test_db;charset=utf8mb4';" \
        >> "${WEBROOT}/params.php"
elif [ $OS = 'alpine' ] ; then
    echo "\$dbDSN = 'mysql:unix_socket=/run/mysqld/mysqld.sock;dbname=test_db;charset=utf8mb4';" \
        >> "${WEBROOT}/params.php"
else
    echo "\$dbDSN = 'mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=test_db;charset=utf8mb4';" \
        >> "${WEBROOT}/params.php"
fi
echo "\$dbOption = [];" >> "${WEBROOT}/params.php"
echo "\$browserCompatibility = [" >> "${WEBROOT}/params.php"
echo "'Chrome' => '1+','FireFox' => '2+','msie' => '9+','Opera' => '1+'," >> "${WEBROOT}/params.php"
echo "'Safari' => '4+','Trident' => '5+',];" >> "${WEBROOT}/params.php"
echo "\$dbServer = '192.168.56.1';" >> "${WEBROOT}/params.php"
echo "\$dbPort = '80';" >> "${WEBROOT}/params.php"
echo "\$dbDatabase = 'TestDB';" >> "${WEBROOT}/params.php"
echo "\$dbProtocol = 'HTTP';" >> "${WEBROOT}/params.php"
echo "\$passPhrase = '';" >> "${WEBROOT}/params.php"
echo "\$generatedPrivateKey = <<<EOL" >> "${WEBROOT}/params.php"
echo "-----BEGIN RSA PRIVATE KEY-----" >> "${WEBROOT}/params.php"
echo "MIIEpAIBAAKCAQEAp5xZdpzUZcbG8+MDgHIsHnBC6DGbJZ769/dfGKdFvE5+LGkw" >> "${WEBROOT}/params.php"
echo "p7nsRqcZ6ETZAHG9ghgn7scR6lmfQdwWHeFxDnl2OZ0CP4J0ZnJ36noTLHyycmlU" >> "${WEBROOT}/params.php"
echo "02vQII83DcPfi7+4FCuGwyJOTwLHENvNGDajQm1mMJgZ1A0O7JxPaDUpT+u4uDx3" >> "${WEBROOT}/params.php"
echo "Bjv9dF3m6ZigB1fJ+El1WI++YAlYSWIzEzwdeGP87bfHBB0G07YAZhFvBEen6l53" >> "${WEBROOT}/params.php"
echo "x0WKkt/p4GP5G1JOZZgaqURDD8XZTKAO8t9TzeOROCI832bEsmZ7S/U91MlVbmOI" >> "${WEBROOT}/params.php"
echo "qJlKyhaQVy6H05sfV/Okfsn08EJp4oanZbqAFwIDAQABAoIBAHzWKpv5ewjC4HPN" >> "${WEBROOT}/params.php"
echo "5VHJt6qEGpEuQUvn+SyvBhkqnPn/zGHvhtml1KFa3CTvAmEeVfOLYlKp2mIdlkxL" >> "${WEBROOT}/params.php"
echo "S/29Z6NMPA31LzN2SpPzNfViLt23koE8in1dk4psoKiT9u/zP3tmX9z+tCyM+Q9J" >> "${WEBROOT}/params.php"
echo "ZpxeNYLIUJBo+PPDNhZs5YfL8JUg28cJ5ekraBUlNQP0TaawmZolQlTfgjQYzt89" >> "${WEBROOT}/params.php"
echo "5fFk/CcAyUKe4dnJu39IF75wUe2HYLkrECzD/hiDatdYJ9FK5bMj0TxG4AIZNhx6" >> "${WEBROOT}/params.php"
echo "QbCgQ0Ojjchxxi2goKJRiRzE8H0BpVezXvJ+fTmXb6aOtnMrxMD+8VJoykSI/sCx" >> "${WEBROOT}/params.php"
echo "bOQ2JOECgYEAz4n6qcggtV+3f/Aw4WtgU38vkwzVqcUr464nCSHS7H+AZu8/DP3+" >> "${WEBROOT}/params.php"
echo "YbFCNJob89Uoge1qIuSHmf2flZnooGZLhK0CQKthgjb125pBaCeERu0KDrqpmThu" >> "${WEBROOT}/params.php"
echo "Ea7d91JTEH2U4LpAi/QXQs8MfOzsastrDXn/6J0quQjdB8rYZ3ImEwUCgYEAzr+W" >> "${WEBROOT}/params.php"
echo "QYfxnJ70Q04GgEQ+c8tdE0+uoY+q8d5Q7unShBbB5KU1IbIlKaEPNaQD/xDdKMvd" >> "${WEBROOT}/params.php"
echo "sFTn4bTmWyRKzokVWjBdRUvHNQU9CS8gawhZysv2GIyJPY2iwnr+gFVyBmT5/2KW" >> "${WEBROOT}/params.php"
echo "9oxz2elZ3xA6MwHsr/pA7CVJk1BrAlaH+hBN6WsCgYATqL5V1t9CTw7Sz63RrJoz" >> "${WEBROOT}/params.php"
echo "TpjzFQQoUMUXjCemdc3FGU7QcVlHoce2+VOMKAz9y/NKW3LyWzN9Isk7IpkmmIoO" >> "${WEBROOT}/params.php"
echo "x1SvS4yxCQPBCZuoghXFoi6RtpzaJr5GbooYI3Q626p+nyX+G+EYMwS70LWUaDB7" >> "${WEBROOT}/params.php"
echo "1lKndjvVy0Eku9JD+kwhAQKBgQDODrkM2xb9yJcetZdZI6sy8Y70fkhIkc4IflEf" >> "${WEBROOT}/params.php"
echo "rT+5kozw+4924/yR/woPpkatYvtpe7aZ1iW+GPQ2BnfgXVRArU9oj1weBfiNPMEM" >> "${WEBROOT}/params.php"
echo "rCgCLUI7uWXXSWDcgIVDFuYsZVudI3/efqHAoAiIf73htJtX0Q3/zjIEdvQQQnoH" >> "${WEBROOT}/params.php"
echo "y1Q7vQKBgQC635mth5vpv96xzxHlHHuGJUMECyJRrpxRkWQkmJIY7rBrHHADP3NU" >> "${WEBROOT}/params.php"
echo "L4glO+uLW/ffp8RtbDcPDEWsGK5fzKm69qCsBaguU/IIrXFcZxhFAO7MqJLrfEhP" >> "${WEBROOT}/params.php"
echo "H1mRfJ9Twh2tPyssPqNYhweL2loa8xpef/HQCtTKrzQR0x3HaNmKaA==" >> "${WEBROOT}/params.php"
echo "-----END RSA PRIVATE KEY-----" >> "${WEBROOT}/params.php"
echo "EOL;" >> "${WEBROOT}/params.php"
echo "\$webServerName = [''];" >> "${WEBROOT}/params.php"
echo "\$serviceServerPort = '11478';" >> "${WEBROOT}/params.php"
echo "\$serviceServerHost = 'localhost';" >> "${WEBROOT}/params.php"
echo "\$serviceServerConnect = 'localhost';" >> "${WEBROOT}/params.php"
echo "\$stopSSEveryQuit = false;" >> "${WEBROOT}/params.php"
echo "\$preventSSAutoBoot = false;" >> "${WEBROOT}/params.php"
echo "\$notUseServiceServer = false;" >> "${WEBROOT}/params.php"
echo "\$messages['default'][1022] = \"We don't support Internet Explorer. We'd like you to access by Edge or any other major browsers.\";" >> "${WEBROOT}/params.php"
echo "\$messages['ja'][1022] = \"Internet Explorerは使用できません。Edgeあるいは他の一般的なブラウザをご利用ください。\";" >> "${WEBROOT}/params.php"


if [ $OS = 'alpine' ] ; then
    ln -s ${WEBROOT} ${OLDWEBROOT}
fi

# Install php/js libraries

cd "${IMROOT}"
if [ $OS = 'centos' ] ; then
    /usr/local/bin/composer update
else
    composer update  # returns error for the script of nodejs-installer.
fi
if [ $OS = 'alpine' ] ; then
    sudo ln -s /var/www/html/INTER-Mediator/vendor/bin/phpunit /usr/local/bin/phpunit
    apk add --no-cache nodejs
    apk add --no-cache nodejs-npm
    npm install
    chown -R ${WWWUSERNAME}:im-developer "/var/www"
    chmod 775 "${IMROOT}/node_modules/forever/bin/forever"
fi

# Auto starting of Service Server

Crontab="/etc/crontabs/root"
echo "@reboot ${IMROOT}/dist-docs/vm-for-trial/forever-startup.sh" >> "${Crontab}"

# Copy Templates

for Num in $(seq 40)
do
    PadZero="00${Num}"
    DefFile="def${PadZero: -2}.php"
    PageFile="page${PadZero: -2}.html"
    sed -E -e "s|\('INTER-Mediator.php'\)|\('INTER-Mediator/INTER-Mediator.php'\)|" \
        "${IMSAMPLE}/templates/definition_file_simple.php" > "${WEBROOT}/${DefFile}"
    sed -E -e "s/definitin_file_simple.php/${DefFile}/" \
        "${IMSAMPLE}/templates/page_file_simple.html" > "${WEBROOT}/${PageFile}"
done

# Firewall

if [ $OS = 'centos' ] ; then
    firewall-cmd --zone=public --add-service=http --permanent
    firewall-cmd --zone=public --add-service=samba --permanent
    firewall-cmd --reload
fi

# Modify permissions

setfacl --recursive --modify g:im-developer:rwx,d:g:im-developer:rwx "${WEBROOT}"
if [ $OS = 'centos' ] ; then
    chown -R apache:im-developer "${WEBROOT}"
else
    chown -R developer:im-developer "${WEBROOT}"
fi
chmod -R a=rX,u+w,g+w "${WEBROOT}"
cd "${WEBROOT}" && cd INTER-Mediator && git checkout .
chmod 664 ${WEBROOT}/*.html
chmod 664 ${WEBROOT}/*.php
chmod 775 "${IMVMROOT}/dbupdate.sh"
chmod 664 "${IMVMROOT}/index.php"
if [ $OS = 'centos' ] ; then
    chown -R apache:im-developer /usr/share/httpd
elif [ $OS = 'alpine' ] ; then
    chmod +x /var/www/html/INTER-Mediator/vendor/bin/phpunit
fi

# Home directory permissions modifying

cd ~developer
touch /home/developer/.bashrc
touch /home/developer/.viminfo
chown developer:developer .*

# Import schema

echo "y" | source "${IMVMROOT}/dbupdate.sh"

# Add a conf file for Apache HTTP Server

if [ $OS = 'alpine' ] ; then
    echo "LoadModule rewrite_module modules/mod_rewrite.so" > /etc/apache2/conf.d/im.conf
    echo "LoadModule slotmem_shm_module modules/mod_slotmem_shm.so" >> /etc/apache2/conf.d/im.conf
    echo "RewriteEngine on" >> /etc/apache2/conf.d/im.conf
    echo "RewriteRule ^/fmi/rest/(.*) http://192.168.56.1/fmi/rest/\$1 [P,L]" >> /etc/apache2/conf.d/im.conf
    echo "RewriteRule ^/fmi/xml/(.*)  http://192.168.56.1/fmi/xml/\$1 [P,L]" >> /etc/apache2/conf.d/im.conf
    sed -i 's/^LoadModule lbmethod_/#LoadModule lbmethod_/' /etc/apache2/conf.d/proxy.conf
fi

# Modify php.ini

if [ $OS = 'alpine' ] ; then
    cat /etc/php7/php.ini | sed -e 's/max_execution_time = 30/max_execution_time = 120/g' | sed -e 's/max_input_time = 60/max_input_time = 120/g' | sed -e 's/memory_limit = 128M/memory_limit = 256M/g' | sed -e 's/post_max_size = 8M/post_max_size = 100M/g' | sed -e 's/upload_max_filesize = 2M/upload_max_filesize = 100M/g' > /etc/php7/php.ini.tmp
    mv /etc/php7/php.ini.tmp /etc/php7/php.ini
else
    cat /etc/php/7.2/apache2/php.ini | sed -e 's/max_execution_time = 30/max_execution_time = 120/g' | sed -e 's/max_input_time = 60/max_input_time = 120/g' | sed -e 's/memory_limit = 128M/memory_limit = 256M/g' | sed -e 's/post_max_size = 8M/post_max_size = 100M/g' | sed -e 's/upload_max_filesize = 2M/upload_max_filesize = 100M/g' > /etc/php/7.2/apache2/php.ini.tmp
    mv /etc/php/7.2/apache2/php.ini.tmp /etc/php/7.2/apache2/php.ini
fi

# Share the Web Root Directory with SMB.

if [ $OS = 'centos' ] ; then
    echo "[global]" > "${SMBCONF}"
    echo "   security = user" >> "${SMBCONF}"
    echo "   passdb backend = tdbsam" >> "${SMBCONF}"
    echo "   max protocol = SMB3" >> "${SMBCONF}"
    echo "   min protocol = SMB2" >> "${SMBCONF}"
    echo "   ea support = yes" >> "${SMBCONF}"
    echo "   unix extensions = no" >> "${SMBCONF}"
    echo "   browseable = no" >> "${SMBCONF}"
    echo "   hosts allow = 192.168.56. 127." >> "${SMBCONF}"
    echo "" >> "${SMBCONF}"
    echo "[webroot]" >> "${SMBCONF}"
    echo "   comment = Apache Root Directory" >> "${SMBCONF}"
    echo "   path = /var/www/html" >> "${SMBCONF}"
elif [ $OS = 'alpine' ] ; then
    echo "   hosts allow = 192.168.56. 127." >> "${SMBCONF}"
    echo "" >> "${SMBCONF}"
    echo "[global]" >> "${SMBCONF}"
    echo "   browseable = no" >> "${SMBCONF}"
    echo "" >> "${SMBCONF}"
    echo "[webroot]" >> "${SMBCONF}"
    echo "   comment = Apache Root Directory" >> "${SMBCONF}"
    echo "   path = /var/www/localhost/htdocs" >> "${SMBCONF}"
else
    sed ':loop; N; $!b loop; ;s/#### Networking ####\n/#### Networking ####\n   hosts allow = 192.168.56. 127./g' "${SMBCONF}" > "${SMBCONF}".tmp
    mv "${SMBCONF}".tmp "${SMBCONF}"
    echo "" >> "${SMBCONF}"
    echo "[global]" >> "${SMBCONF}"
    echo "   browseable = no" >> "${SMBCONF}"
    echo "" >> "${SMBCONF}"
    echo "[webroot]" >> "${SMBCONF}"
    echo "   comment = Apache Root Directory" >> "${SMBCONF}"
    echo "   path = /var/www/html" >> "${SMBCONF}"
fi
echo "   guest ok = no" >> "${SMBCONF}"
echo "   browseable = yes" >> "${SMBCONF}"
echo "   read only = no" >> "${SMBCONF}"
echo "   create mask = 0664" >> "${SMBCONF}"
echo "   directory mask = 0775" >> "${SMBCONF}"
echo "   force group = im-developer" >> "${SMBCONF}"
( echo im4135dev; echo im4135dev ) | sudo smbpasswd -s -a developer

# SELinux

if [ $OS = 'centos' ] ; then
    setsebool -P samba_export_all_rw 1
    cd "${IMSELINUX}"
    semodule -i inter-mediator.pp
fi

# Modify /etc/default/keyboard, /etc/default/locale for Japanese

if [ $OS != 'alpine' ] ; then
    cat /etc/default/keyboard | sed -e 's/XKBLAYOUT="us"/XKBLAYOUT="jp"/g' > /etc/default/keyboard.tmp
    mv /etc/default/keyboard.tmp /etc/default/keyboard
    cat /etc/default/locale | sed -e 's/LANG="en_US.UTF-8"/LANG="ja_JP.UTF-8"/g' > /etc/default/locale.tmp
    mv /etc/default/locale.tmp /etc/default/locale
    chmod u+s /usr/bin/fbterm
    dpkg-reconfigure -f noninteractive keyboard-configuration
fi

# The end of task.

echo "Welcome to INTER-Mediator-Server VM!" > /etc/motd
if [ $OS = 'alpine' ] ; then
    chmod 755 "${WEBROOT}//INTER-Mediator/node_modules/jest/bin/jest.js"
    poweroff
else
    /sbin/shutdown -h now
fi
