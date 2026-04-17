#!/usr/bin/env bash

distDocDir=$(cd $(dirname "$0"); pwd)

mysql -uroot < "${distDocDir}/sample_schema_mysql.sql"

# psql -c 'create database test_db;' -h localhost postgres
psql --quiet -f "${distDocDir}/sample_schema_pgsql.sql" -h localhost test_db

# sudo chown _www /var/db/im
# sudo mkdir /var/db/im
rm /var/db/im/sample.sq3
sqlite3 /var/db/im/sample.sq3 < "${distDocDir}/sample_schema_sqlite.sql"

#mysql -uroot < "${distDocDir}/../spec/run/additionals_mysql.sql"
#psql --quiet -f "${distDocDir}/../spec/run/additionals_postgresql.sql" -h localhost test_db
#sqlite3 /var/db/im/sample.sq3 < "${distDocDir}/../spec/run/additionals_sqlite.sql"

