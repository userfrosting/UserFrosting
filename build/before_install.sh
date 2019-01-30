#!/bin/bash
#
# Inspired by ownCloud´s before_install.sh script <3
#

WORKDIR=$PWD
DB=$1

echo "Work directory: $WORKDIR"
echo "Database: $DB"

#
# set up mysql
#
if [ "$DB" == "mysql" ] ; then
    echo "Setting up mysql ..."
    mysql -u root -e "CREATE DATABASE userfrosting;"
    mysql -u root -e "GRANT ALL ON userfrosting.* TO 'travis'@'localhost';"
    printf "UF_MODE=\"debug\"\nDB_DRIVER=\"mysql\"\nDB_HOST=\"localhost\"\nDB_PORT=\"3306\"\nDB_NAME=\"userfrosting\"\nDB_USER=\"travis\"\nDB_PASSWORD=\"\"\nTEST_DB=\"default\"\n" > app/.env
fi

#
# set up pgsql
#
if [ "$DB" == "pgsql" ] ; then
    echo "Setting up pgsql ..."
    psql -c "CREATE DATABASE userfrosting;" -U postgres
    psql -c "GRANT ALL PRIVILEGES ON DATABASE userfrosting TO postgres;" -U postgres
    printf "UF_MODE=\"debug\"\nDB_DRIVER=\"pgsql\"\nDB_HOST=\"localhost\"\nDB_PORT=\"5432\"\nDB_NAME=\"userfrosting\"\nDB_USER=\"postgres\"\nDB_PASSWORD=\"\"\nTEST_DB=\"default\"\n" > app/.env
fi

#
# set up sqlite
#
if [ "$DB" == "sqlite" ] ; then
    echo "Setting up sqlite ..."
    touch userfrosting.db
    printf "UF_MODE=\"debug\"\nDB_DRIVER=\"sqlite\"\nDB_NAME=\"userfrosting.db\"\nTEST_DB=\"default\"\n" > app/.env
fi
