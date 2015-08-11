#!/bin/bash
#
# setup shell script for Ubuntu Server 14.04.3
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

WEBROOT="/var/www/html"

IMROOT="${WEBROOT}/INTER-Mediator"
IMSUPPORT="${IMROOT}/INTER-Mediator-Support"
IMSAMPLE="${IMROOT}/Samples"
IMUNITTEST="${IMROOT}/INTER-Mediator-UnitTest"
IMDISTDOC="${IMROOT}/dist-docs"
IMVMROOT="${IMROOT}/dist-docs/vm-for-trial"
APACHEOPTCONF="/etc/apache2/sites-enabled/inter-mediator-server.conf"
SMBCONF="/etc/samba/smb.conf"

RESULT=`id developer 2>/dev/null`
if [ $RESULT = ''] ; then
    adduser developer
    yes im4135dev | passwd developer
    echo "developer ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/developer
    chmod 440 /etc/sudoers.d/developer
    touch /home/developer/.viminfo
    chown developer:developer /home/developer/.viminfo
fi

groupadd im-developer
usermod -a -G im-developer developer
usermod -a -G im-developer www-data
yes im4135dev | passwd postgres

echo "set grub-pc/install_devices /dev/sda" | debconf-communicate
aptitude clean
aptitude update
aptitude full-upgrade --assume-yes
aptitude install apache2 --assume-yes
aptitude install openssh-server --assume-yes
aptitude install mysql-server --assume-yes
aptitude install postgresql --assume-yes
aptitude install sqlite --assume-yes
aptitude install acl --assume-yes
aptitude install libmysqlclient-dev --assume-yes
aptitude install php5-mysql --assume-yes
aptitude install php5-pgsql --assume-yes
aptitude install php5-sqlite --assume-yes
aptitude install php5-curl --assume-yes
aptitude install php5-gd --assume-yes
aptitude install php5-xmlrpc --assume-yes
aptitude install php5-intl --assume-yes
aptitude install git --assume-yes
aptitude install nodejs --assume-yes && update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10
aptitude install nodejs-legacy --assume-yes
aptitude install npm --assume-yes
aptitude install libfontconfig1 --assume-yes
aptitude install phpunit --assume-yes
aptitude install samba --assume-yes
aptitude clean

mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' identified by 'im4135dev';" -u root
echo "[mysqld]" > /etc/mysql/conf.d/im.cnf
echo "character-set-server=utf8mb4" >> /etc/mysql/conf.d/im.cnf
echo "skip-character-set-client-handshake" >> /etc/mysql/conf.d/im.cnf
echo "[client]" >> /etc/mysql/conf.d/im.cnf
echo "default-character-set=utf8mb4" >> /etc/mysql/conf.d/im.cnf
echo "[mysqldump]" >> /etc/mysql/conf.d/im.cnf
echo "default-character-set=utf8mb4" >> /etc/mysql/conf.d/im.cnf
echo "[mysql]" >> /etc/mysql/conf.d/im.cnf
echo "default-character-set=utf8mb4" >> /etc/mysql/conf.d/im.cnf

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

# Install npm packages

cd "${IMROOT}"
npm install -g buster
npm install -g phantomjs

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
chown developer:developer .*

# Share the Web Root Directory with SMB.

sed ':loop; N; $!b loop; ;s/#### Networking ####\n/#### Networking ####\n   hosts allow = 192.168.56. 127./g' "${SMBCONF}" > "${SMBCONF}".tmp
mv "${SMBCONF}".tmp "${SMBCONF}"
echo "" >> "${SMBCONF}"
echo "[webroot]" >> "${SMBCONF}"
echo "   comment = Apache Root Directory" >> "${SMBCONF}"
echo "   path = /var/www/html" >> "${SMBCONF}"
echo "   guest ok = no" >> "${SMBCONF}"
echo "   browseable = yes" >> "${SMBCONF}"
echo "   read only = no" >> "${SMBCONF}"
echo "   create mask = 0664" >> "${SMBCONF}"
echo "   directory mask = 0775" >> "${SMBCONF}"
echo "   force group = im-developer" >> "${SMBCONF}"
( echo im4135dev; echo im4135dev ) | sudo smbpasswd -s -a developer

# Launch buster-server for unit testing

echo -e '#!/bin/sh -e\n#\n# rc.local\n#\n# This script is executed at the end of each multiuser runlevel.\n# Make sure that the script will "exit 0" on success or any other\n# value on error.\n#\n# In order to enable or disable this script just change the execution\n# bits.\n#\n# By default this script does nothing.\n\n/usr/local/bin/buster-server &\n/bin/sleep 5\n/usr/local/bin/phantomjs /usr/local/lib/node_modules/buster/script/phantom.js http://localhost:1111/capture > /dev/null &\nexit 0' > /etc/rc.local

# The end of task.

/sbin/shutdown -h now
