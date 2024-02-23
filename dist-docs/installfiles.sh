#!/usr/bin/env bash

distDocDir=$(cd $(dirname "$0"); pwd)
originalPath=$(dirname "${distDocDir}")
cd "${originalPath}"

brew unlink php
brew link php@7.4

rm -rf vendor node_modules

mv '__Did_you_run_composer_update.txt' spec/tempfile
composer update --with-all-dependencies
mv spec/tempfile '__Did_you_run_composer_update.txt'

cd spec/run
npm update

cd ../run-safari
npm update

brew unlink php@7.4
brew link php
