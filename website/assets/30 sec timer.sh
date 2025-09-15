#!/bin/bash
echo "Running sleep"

sleep 30

DB_NAME='CyberCity'
DB_USER='CyberCity'
DB_PASS='CyberCity'
DB_HOST='10.177.202.196'

NEW_VALUE='0'

SQL="UPDATE Challenges SET moduleValue = 'NEW_VALUE';"
echo changed

mysql -u "$DB_USER" -p "$DB_PASS" -h "$DB_HOST" "$DB_NAME" -e "$SQL"
