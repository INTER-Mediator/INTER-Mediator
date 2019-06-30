#!/bin/ash

cd /var/www/html/INTER-Mediator
./node_modules/.bin/forever start \
    -a -l /tmp/im-forerver.log --minUptime 5000 --spinSleepTime 5000 \
    ./dist-docs/vm-for-trial/forever.json 11478
