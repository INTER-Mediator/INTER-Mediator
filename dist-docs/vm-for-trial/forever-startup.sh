#!/bin/ash

cd /var/www/html/INTER-Mediator
./node_modules/.bin/forever start ./dist-docs/vm-for-trial/forever.json
