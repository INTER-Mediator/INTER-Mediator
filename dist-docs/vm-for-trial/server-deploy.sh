#!/bin/bash
#
# setup shell script for VM on the server
#
# Start this script on setting current dir as root dir of INTER-Mediator
# In the middle of script, you have to enter the root's password of MySQL
#

IMROOT=$(pwd)
WEBROOT=$(dirname "${IMROOT}")
IMSUPPORT="${IMROOT}/INTER-Mediator-Support"
IMDISTDOC="${IMROOT}/dist-docs"
IMVMROOT="${IMROOT}/dist-docs/vm-for-trial"
IMSAMPLE="${IMROOT}/Samples"

cd "${WEBROOT}"
rm -f "${WEBROOT}/index.html"

cd "${IMSUPPORT}"
git clone https://github.com/codemirror/CodeMirror.git

cd "${WEBROOT}"
ln -s "${IMVMROOT}/index.php" index.php

echo 'AddType "text/html; charset=UTF-8" .html' > "${WEBROOT}/.htaccess"

echo '<?php' > "${WEBROOT}/params.php"
echo "\$dbUser = 'web_${USER}';" >> "${WEBROOT}/params.php"
echo "\$dbPassword = 'password';" >> "${WEBROOT}/params.php"
echo "\$dbDSN = 'mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=test_db_${USER};charset=utf8mb4';" \
        >> "${WEBROOT}/params.php"
echo "\$dbOption = array();" >> "${WEBROOT}/params.php"
echo "\$browserCompatibility = array(" >> "${WEBROOT}/params.php"
echo "'Chrome' => '1+','FireFox' => '2+','msie' => '9+','Opera' => '1+'," >> "${WEBROOT}/params.php"
echo "'Safari' => '4+','Trident' => '5+',);" >> "${WEBROOT}/params.php"
echo "\$dbServer = '192.168.56.1';" >> "${WEBROOT}/params.php"
echo "\$dbPort = '80';" >> "${WEBROOT}/params.php"
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

sed -E -e "s/'web'/'web\_${USER}'/g" "${IMDISTDOC}/sample_schema_mysql.sql" > "${IMDISTDOC}/tmp1"
sed -E -e "s/test\_db/test\_db\_${USER}/g" "${IMDISTDOC}/tmp1" > "${IMDISTDOC}/tmp2"
mysql -u root -p < "${IMDISTDOC}/tmp2"
