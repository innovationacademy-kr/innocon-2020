#!/bin/bash
tar -czpf /var/www/_backup/_backend.`date +%Y-%m-%d_%H:%M`.tgz /var/www/_backend 1>/dev/null 2>/dev/null
tar -czpf /var/www/_backup/html.`date +%Y-%m-%d_%H:%M`.tgz /home/makegood/html 1>/dev/null 2>/dev/null
mysqldump --extended-insert=FALSE -uroot -p1q2w3 templete > /var/www/_backup/templete.`date +%Y-%m-%d_%H:%M`.sql
find /var/www/_backup/ -type f -mtime +10 | sort | xargs rm -f