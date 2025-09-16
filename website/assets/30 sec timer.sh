#!/bin/bash

echo "Running sleep"
sleep 3

DB_NAME='CyberCity'
DB_USER='CyberCity'
DB_PASS='CyberCity'
DB_HOST='10.177.202.196'

SQL="UPDATE Challenges SET moduleValue = 0;"
echo "Executing SQL update..."

mysql -u"$DB_USER" -p"$DB_PASS" -h"$DB_HOST" -D"$DB_NAME" -e "$SQL"

if [ $? -eq 0 ]; then
    echo "Update successful."
else
    echo "Update failed." >&2
fi