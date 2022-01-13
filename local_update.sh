#!/bin/bash

export PATH=/bin:/usr/bin:/usr/local/bin
TODAY=`LC_ALL=en_EN.utf8 date --date="yesterday" +"%d%b%Y"`

BACKUP_FILE="/backup/${TODAY}/dabba-${TODAY}.sql.gz"
echo "UPDATE lOCAL unsing ${BACKUP_FILE}"

scp dabba:$BACKUP_FILE /tmp/dabba-${TODAY}.sql.gz
gunzip /tmp/dabba-${TODAY}.sql.gz
php7.4 bin/console doctrine:schema:drop --force
php7.4 bin/console doctrine:database:import "/tmp/dabba-${TODAY}.sql"

rm "/tmp/dabba-${TODAY}.sql"

echo "SYNC UPLOADS FOLDER"
rsync -rcv dabba:/var/www/dabba_API/public/uploads/ ./public/uploads/

echo "CLEAR CACHE"
php7.4 bin/console cache:clear

echo "RESET SUCCESSFULL"