#!/bin/bash
#
# setup shell script for Alpine Linux 3.4.6 and Ubuntu Server 14.04.5
#
# This file can get from the URL below.
# https://raw.githubusercontent.com/INTER-Mediator/INTER-Mediator/master/dist-docs/vm-for-trial/deploy.sh
#
# How to test using Serverspec 2 after running this file on the guest of VM:
#
# - Install Ruby on the host of VM (You don't need installing Ruby on OS X usually)
# - Install Serverspec 2 on the host of VM (ex. "sudo gem install serverspec" on OS X)
#   See detail: http://serverspec.org/
# - Change directory to "vm-for-trial" directory on the host of VM
# - Run "rake spec" on the host of VM
#

OS=`cat /etc/os-release | grep ^ID | cut -d'=' -f2`

if [ $OS = 'alpine' ] ; then
    WEBROOT="/var/www/localhost/htdocs"
else
    WEBROOT="/var/www/html"
fi

IMROOT="${WEBROOT}/INTER-Mediator"
IMSUPPORT="${IMROOT}/INTER-Mediator-Support"
IMSAMPLE="${IMROOT}/Samples"
IMUNITTEST="${IMROOT}/INTER-Mediator-UnitTest"
IMDISTDOC="${IMROOT}/dist-docs"
IMVMROOT="${IMROOT}/dist-docs/vm-for-trial"
APACHEOPTCONF="/etc/apache2/sites-enabled/inter-mediator-server.conf"
SMBCONF="/etc/samba/smb.conf"

RESULT=`id developer 2>/dev/null`
if [ $RESULT = '' ] ; then
    adduser developer
    yes im4135dev | passwd developer
    echo "developer ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/developer
    chmod 440 /etc/sudoers.d/developer
    touch /home/developer/.viminfo
    chown developer:developer /home/developer/.viminfo
fi

if [ $OS = 'alpine' ] ; then
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

    apk update
    apk upgrade
    apk add --no-cache apache2
    apk add --no-cache mariadb-client
    apk add --no-cache mariadb
    apk add --no-cache postgresql
    apk add --no-cache sqlite
    apk add --no-cache acl
    apk add --no-cache php5
    apk add --no-cache php5-apache2
    apk add --no-cache php5-curl
    apk add --no-cache php5-pdo
    apk add --no-cache php5-pdo_mysql
    apk add --no-cache php5-pdo_pgsql
    apk add --no-cache php5-pdo_sqlite
    apk add --no-cache php5-openssl
    apk add --no-cache php5-dom
    apk add --no-cache php5-json
    apk add --no-cache php5-bcmath
    apk add --no-cache php5-phar
    apk add --no-cache git
    apk add --no-cache nodejs
    apk add --no-cache samba
    apk add --no-cache dbus
    apk add --no-cache firefox
    apk add --no-cache chromium libgudev
    apk add --no-cache xvfb
    apk add --no-cache fontconfig-dev

    apk add --no-cache ca-certificates
    apk add --no-cache wget
    update-ca-certificates
    wget https://phar.phpunit.de/phpunit-5.6.2.phar -P /tmp
    mv /tmp/phpunit-5.6.2.phar /usr/local/bin/phpunit
    chmod +x /usr/local/bin/phpunit

    rc-service apache2 start
    rc-update add apache2
    /etc/init.d/mariadb setup
    rc-service mariadb start
    rc-update add mariadb
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
    apt-get install php5-mysql --assume-yes
    apt-get install php5-pgsql --assume-yes
    apt-get install php5-sqlite --assume-yes
    apt-get install php5-curl --assume-yes
    apt-get install php5-gd --assume-yes
    apt-get install php5-xmlrpc --assume-yes
    apt-get install php5-intl --assume-yes
    apt-get install git --assume-yes
    apt-get install nodejs --assume-yes && update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10
    apt-get install nodejs-legacy --assume-yes
    apt-get install npm --assume-yes
    apt-get install libfontconfig1 --assume-yes
    apt-get install samba --assume-yes
    apt-get install phpunit --assume-yes
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
    addgroup apache im-developer
else
    groupadd im-developer
    usermod -a -G im-developer developer
    usermod -a -G im-developer www-data
fi
yes im4135dev | passwd postgres

mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' identified by 'im4135dev';" -u root
if [ $OS = 'alpine' ] ; then
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
git clone https://github.com/INTER-Mediator/INTER-Mediator.git && cd INTER-Mediator && git remote add upstream https://github.com/INTER-Mediator/INTER-Mediator.git

rm -f "${WEBROOT}/index.html"

cd "${IMSUPPORT}"
git clone https://github.com/codemirror/CodeMirror.git

cd "${WEBROOT}"
ln -s "${IMVMROOT}/index.php" index.php

echo 'AddType "text/html; charset=UTF-8" .html' > "${WEBROOT}/.htaccess"

echo '<?php' > "${WEBROOT}/params.php"
echo "\$dbUser = 'web';" >> "${WEBROOT}/params.php"
echo "\$dbPassword = 'password';" >> "${WEBROOT}/params.php"
echo "\$dbDSN = 'mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=test_db;charset=utf8mb4';" \
            >> "${WEBROOT}/params.php"
echo "\$dbOption = array();" >> "${WEBROOT}/params.php"
echo "\$browserCompatibility = array(" >> "${WEBROOT}/params.php"
echo "'Chrome' => '1+','FireFox' => '2+','msie' => '9+','Opera' => '1+'," >> "${WEBROOT}/params.php"
echo "'Safari' => '4+','Trident' => '5+',);" >> "${WEBROOT}/params.php"
echo "\$dbServer = '192.168.56.1';" >> "${WEBROOT}/params.php"
echo "\$dbPort = '80';" >> "${WEBROOT}/params.php"
echo "\$dbDataType = 'FMPro12';" >> "${WEBROOT}/params.php"
echo "\$dbDatabase = 'TestDB';" >> "${WEBROOT}/params.php"
echo "\$dbProtocol = 'HTTP';" >> "${WEBROOT}/params.php"
echo "\$passPhrase = '';" >> "${WEBROOT}/params.php"
echo "\$generatedPrivateKey = <<<EOL" >> "${WEBROOT}/params.php"
echo "-----BEGIN RSA PRIVATE KEY-----" >> "${WEBROOT}/params.php"
echo "MIIBOwIBAAJBAKihibtt92M6A/z49CqNcWugBd3sPrW3HF8TtKANZd1EWQ/agZ65" >> "${WEBROOT}/params.php"
echo "H2/NdL8H6zCgmKpYFTqFGwlYrnWrsbD1UxcCAwEAAQJAWX5pl1Q0D7Axf6csBg1M" >> "${WEBROOT}/params.php"
echo "3V5u3qlLWqsUXo0ZtjuGDRgk5FsJOA9bkxfpJspbr2CFkodpBuBCBYpOTQhLUc2H" >> "${WEBROOT}/params.php"
echo "MQIhAN1stwI2BIiSBNbDx2YiW5IVTEh/gTEXxOCazRDNWPQJAiEAwvZvqIQLexer" >> "${WEBROOT}/params.php"
echo "TnKj7q+Zcv4G2XgbkhtaLH/ELiA/Fh8CIQDGIC3M86qwzP85cCrub5XCK/567GQc" >> "${WEBROOT}/params.php"
echo "GmmWk80j2KpciQIhAI/ybFa7x85Gl5EAS9F7jYy9ykjeyVyDHX0liK+V1355AiAG" >> "${WEBROOT}/params.php"
echo "jU6zr1wG9awuXj8j5x37eFXnfD/p92GpteyHuIDpog==" >> "${WEBROOT}/params.php"
echo "-----END RSA PRIVATE KEY-----" >> "${WEBROOT}/params.php"
echo "EOL;" >> "${WEBROOT}/params.php"
echo "\$webServerName = array('');" >> "${WEBROOT}/params.php"

# Install npm packages

cd "${IMROOT}"
npm install -g buster

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

# Import schema

echo "y" | source "${IMVMROOT}/dbupdate.sh"

# Modify permissions

setfacl --recursive --modify g:im-developer:rwx,d:g:im-developer:rwx "${WEBROOT}"
chown -R developer:im-developer "${WEBROOT}"
chmod -R a=rX,u+w,g+w "${WEBROOT}"
cd "${WEBROOT}" && cd INTER-Mediator && git checkout .
chmod 664 ${WEBROOT}/*.html
chmod 664 ${WEBROOT}/*.php
chmod 664 "${IMVMROOT}/dbupdate.sh"

# Home directory permissions modifying

cd ~developer
touch /home/developer/.bashrc
touch /home/developer/.viminfo
chown developer:developer .*

# Modify php.ini

cat /etc/php5/apache2/php.ini | sed -e 's/max_execution_time = 30/max_execution_time = 120/g' | sed -e 's/max_input_time = 60/max_input_time = 120/g' | sed -e 's/memory_limit = 128M/memory_limit = 256M/g' | sed -e 's/post_max_size = 8M/post_max_size = 100M/g' | sed -e 's/upload_max_filesize = 2M/upload_max_filesize = 100M/g' > /etc/php5/apache2/php.ini.tmp
mv /etc/php5/apache2/php.ini.tmp /etc/php5/apache2/php.ini

# Share the Web Root Directory with SMB.

if [ $OS = 'alpine' ] ; then
    echo "   hosts allow = 192.168.56. 127." >> "${SMBCONF}"
    echo "" >> "${SMBCONF}"
    echo "[webroot]" >> "${SMBCONF}"
    echo "   comment = Apache Root Directory" >> "${SMBCONF}"
    echo "   path = /var/www/localhost/htdocs" >> "${SMBCONF}"
else
    sed ':loop; N; $!b loop; ;s/#### Networking ####\n/#### Networking ####\n   hosts allow = 192.168.56. 127./g' "${SMBCONF}" > "${SMBCONF}".tmp
    mv "${SMBCONF}".tmp "${SMBCONF}"
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

# Modify /etc/default/keyboard, /etc/default/locale for Japanese

cat /etc/default/keyboard | sed -e 's/XKBLAYOUT="us"/XKBLAYOUT="jp"/g' > /etc/default/keyboard.tmp
mv /etc/default/keyboard.tmp /etc/default/keyboard
cat /etc/default/locale | sed -e 's/LANG="en_US.UTF-8"/LANG="ja_JP.UTF-8"/g' > /etc/default/locale.tmp
mv /etc/default/locale.tmp /etc/default/locale
chmod u+s /usr/bin/fbterm
dpkg-reconfigure -f noninteractive keyboard-configuration

# Launch buster-server for unit testing

if [ $OS = 'alpine' ] ; then
    echo -e '#!/bin/sh -e\n#\n# rc.local\n#\n# This script is executed at the end of each multiuser runlevel.\n# Make sure that the script will "exit 0" on success or any other\n# value on error.\n#\n# In order to enable or disable this script just change the execution\n# bits.\n#\n# By default this script does nothing.\n\nexport DISPLAY=:99.0\nXvfb :99 -screen 0 1024x768x24 &\n/bin/sleep 5\n/usr/bin/buster-server &\n/bin/sleep 5\nfirefox http://localhost:1111/capture > /dev/null &\n#chromium-browser --no-sandbox http://localhost:1111/capture > /dev/null &\n/bin/sleep 5\nexit 0' > /etc/local.d/buster-server.start
    chmod 755 /etc/local.d/buster-server.start
    rc-update add local default
else
    echo -e '#!/bin/sh -e\n#\n# rc.local\n#\n# This script is executed at the end of each multiuser runlevel.\n# Make sure that the script will "exit 0" on success or any other\n# value on error.\n#\n# In order to enable or disable this script just change the execution\n# bits.\n#\n# By default this script does nothing.\n\n/usr/local/bin/buster-server &\n/bin/sleep 5\n/usr/local/bin/phantomjs /usr/local/lib/node_modules/buster/script/phantom.js http://localhost:1111/capture > /dev/null &\nexit 0' > /etc/rc.local
fi

# The end of task.

if [ $OS = 'alpine' ] ; then
    poweroff
else
    /sbin/shutdown -h now
fi