#!/bin/bash
#
# This file can update the sample database of MySQL, PostgreSQL and SQLite in VM.
# https://raw.githubusercontent.com/INTER-Mediator/INTER-Mediator/master/dist-docs/vm-for-trial/dbupdate.sh
#

WEBROOT="/var/www/html"

IMROOT="${WEBROOT}/INTER-Mediator"
IMDISTDOC="${IMROOT}/dist-docs"

mysql -u root --password=im4135dev < "${IMDISTDOC}/sample_schema_mysql.txt"

echo "im4135dev" | sudo -u postgres -S psql -c 'drop database if exists test_db;'
echo "im4135dev" | sudo -u postgres -S psql -c 'create database test_db;'
echo "im4135dev" | sudo -u postgres -S psql -f "${IMDISTDOC}/sample_schema_pgsql.txt" test_db

SQLITEDIR="/var/db/im"
SQLITEDB="${SQLITEDIR}/sample.sq3"
if [ -f "${SQLITEDB}" ]; then
    echo "im4135dev" | sudo -S rm "${SQLITEDB}"
fi
echo "im4135dev" | sudo -S sqlite3 "${SQLITEDB}" < "${IMDISTDOC}/sample_schema_sqlite.txt"
echo "im4135dev" | sudo -S chown -R www-data:im-developer "${SQLITEDIR}"
echo "im4135dev" | sudo -S chmod 775 "${SQLITEDIR}"
echo "im4135dev" | sudo -S chmod 664 "${SQLITEDB}"
