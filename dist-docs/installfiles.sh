#!/usr/bin/env bash

distDocDir=$(cd $(dirname "$0"); pwd)
originalPath=$(dirname "${distDocDir}")
cd "${originalPath}"

mv '__Did_you_run_composer_update.txt' spec/tempfile
composer update --with-all-dependencies
mv spec/tempfile '__Did_you_run_composer_update.txt'

cd spec/run
npm update

cd ../run-safari
npm update
