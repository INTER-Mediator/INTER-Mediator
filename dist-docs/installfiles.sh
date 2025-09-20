#!/usr/bin/env bash

distDocDir=$(cd $(dirname "$0"); pwd)
originalPath=$(dirname "${distDocDir}")
cd "${originalPath}"

# brew unlink php
# brew link php@7.4

# rm -rf vendor node_modules

mv '__Did_you_run_composer_update.txt' spec/tempfile
composer update --with-all-dependencies
mv spec/tempfile '__Did_you_run_composer_update.txt'

cd spec/run
npm install --before 2025-09-14

cd ../run-safari
npm install --before 2025-09-14

# brew unlink php@7.4
# brew link php
