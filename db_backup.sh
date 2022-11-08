#!/bin/bash
mysqldump -u root -psly monitoring > /home/lzds/monitoring.sql

#drive upload --file monitoring.sql
mpack -s "LZDS - Kiosk DB Backup" /home/lzds/monitoring.sql erick.tria@gmail.com

rm /home/lzds/monitoring.sql

