#!/bin/ash

cd /var/www/html/INTER-Mediator
./node_modules/.bin/forever start \
    -a -l /tmp/im-forerver.log --minUptime 5000 --spinSleepTime 5000 \
    ./src/js/Service_Server.js 11478
