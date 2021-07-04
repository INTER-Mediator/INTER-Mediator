#!/bin/bash
#
# This file can update the sample database of MySQL, MariaDB, PostgreSQL and SQLite in VM.
# https://raw.githubusercontent.com/INTER-Mediator/INTER-Mediator/master/dist-docs/vm-for-trial/dbupdate.sh
#

OS=`cat /etc/os-release | grep ^ID | head -n 1 | cut -d'=' -f2 | cut -d'"' -f2`

WEBROOT="/var/www/html"
WWWUSERNAME="www-data"
if [ -e "/etc/alpine-release" ]; then
    WEBROOT="/var/www/localhost/htdocs"
    WWWUSERNAME="apache"
fi
if [ $OS = 'centos' ] ; then
    WWWUSERNAME="apache"
fi
IMROOT="${WEBROOT}/INTER-Mediator"
IMVMFORTRIAL="${IMROOT}/dist-docs/vm-for-trial"
if [ $IMVMFORTRIAL = '.' ] ; then
    IMDISTDOC=".."
else
    IMDISTDOC=`dirname ${IMVMFORTRIAL}`
fi
SQLITEDIR="/var/db/im"
SQLITEDB="${SQLITEDIR}/sample.sq3"

VMPASSWORD="im4135dev"

read -p "Do you initialize the test databases? [y/n]: " INPUT

if [ "$INPUT" = "y" -o "$INPUT" = "Y" ]; then
    echo "Initializing databases..."

    if [ -e "/etc/redhat-release" ]; then
        mysql -u root --password="${VMPASSWORD}" test_db -e "DROP USER 'web'@'localhost';"
    fi
    if [ -e "/etc/alpine-release" ]; then
        mysql -u root --password="${VMPASSWORD}" test_db -e "DROP USER IF EXISTS 'web'@'localhost';"
    fi
    if [ $OS = 'ubuntu' ] ; then
        mysql -u root --password="${VMPASSWORD}" -e "set global validate_password_policy=LOW;"
    fi
    mysql -u root --password="${VMPASSWORD}" < "${IMDISTDOC}/sample_schema_mysql.txt"
    # mysql -u root --password="${VMPASSWORD}" < "${IMDISTDOC}/sample_schema_mysql.txt" > /dev/null 2>&1
    if [ $OS = 'ubuntu' ] ; then
        mysql -u root --password="${VMPASSWORD}" -e "set global validate_password_policy=MEDIUM;"
    fi
    if [ $? -gt 0 ]; then
        if [ -e '/.dockerenv' ]; then
            mysql -h db -u root --password="${VMPASSWORD}" < "${IMDISTDOC}/sample_schema_mysql.txt"
        fi
    fi
    if [ ! -e '/.dockerenv' ]; then
        if [ -e "/etc/alpine-release" ]; then
            mysql -u root --password="${VMPASSWORD}" test_db -e "update information set lastupdated='`date -d "\`git --git-dir=/${IMROOT}/.git log -1 -- -p dist-docs/sample_schema_mysql.txt | grep Date: | awk '{print $3,$4,$5,$6}'\`" +%Y-%m-%d`' where id = 1;"
        else
            mysql -u root --password="${VMPASSWORD}" test_db -e "update information set lastupdated='`date -d "\`git --git-dir=/${IMROOT}/.git log -1 -- -p dist-docs/sample_schema_mysql.txt | grep Date: | awk '{print $2,$3,$4,$5,$6}'\`" +%Y-%m-%d`' where id = 1;"
        fi
    fi

    echo "${VMPASSWORD}" | sudo -u postgres -S psql -c 'drop database if exists test_db;'
    echo "${VMPASSWORD}" | sudo -u postgres -S psql -c 'create database test_db;'
    echo "${VMPASSWORD}" | sudo -u postgres -S psql -f "${IMDISTDOC}/sample_schema_pgsql.txt" test_db

    if [ ! -e "${SQLITEDIR}" ]; then
        echo "${VMPASSWORD}" | sudo -S mkdir -p "${SQLITEDIR}"
    fi
    if [ -f "${SQLITEDB}" ]; then
        echo "${VMPASSWORD}" | sudo -S rm "${SQLITEDB}"
    fi
    echo "${VMPASSWORD}" | sudo -S sqlite3 "${SQLITEDB}" < "${IMDISTDOC}/sample_schema_sqlite.txt"
    echo "${VMPASSWORD}" | sudo -S chown -R "${WWWUSERNAME}":im-developer "${SQLITEDIR}"
    echo "${VMPASSWORD}" | sudo -S chmod 775 "${SQLITEDIR}"
    echo "${VMPASSWORD}" | sudo -S chmod 664 "${SQLITEDB}"

    echo "Finished initializing databases."
fi
