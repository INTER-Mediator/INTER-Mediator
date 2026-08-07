#!/usr/bin/env bash

# This script is for creating branches for each PHP version.
# 1. Start installfiles.sh
# 2. Comit and push the "master" branch
# 3. Start installbranches.sh
# 4. Create Pull Request for every branch (Ver.8.1-PHP8.1, Ver.8.1-PHP8.2, Ver.8.1-PHP8.3, Ver.8.1-PHP8.4)
# 5. Start installtags.sh

distDocDir=$(cd $(dirname "$0"); pwd)
seedComposer="${distDocDir}/composer-seed/composer-"
versions="8.1,8.2,8.3,8.4,8.5"
composerVers="2.2.28,2.2.28,2.7.9,2.10.2,2.10.2"
originalPath=$(dirname "${distDocDir}")
cd "${originalPath}"


# rm -rf vendor node_modules

echo "******************************************************"
echo "Target Directory: $(pwd)"
echo "******************************************************"
mv '__Did_you_run_composer_update.txt' spec/tempfile
#composer update
pnpm clean --lockfile
npm_config_prefer_online=true pnpm install --lockfile-only
pnpm install --frozen-lockfile
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
export PATH="$SCRIPT_DIR/../vendor/bin:$PATH"
mv spec/tempfile '__Did_you_run_composer_update.txt'
rm package-lock.json

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

IFS=',' read -ra cvArray <<< "$composerVers"
IFS=',' read -ra versionArray <<< "$versions"
COUNT=0
for ver in "${versionArray[@]}"; do
    brew unlink php
    brew link "php@${ver}"

    rm composer.json composer.lock

    echo "Setup composer ver.${cvArray[${COUNT}]}"
    php composer-setup.php --version=${cvArray[${COUNT}]}

    cp -f "${seedComposer}${ver}.json" "${originalPath}/composer.json"
    php composer.phar update --no-scripts --no-plugins --no-interaction
    cp -f "composer.lock" "${seedComposer}${ver}.lock"
    ((COUNT++))
done
rm composer.phar composer-setup.php

cd spec/run
echo "******************************************************"
echo "Target Directory: $(pwd)"
echo "******************************************************"
#../../node_modules/.bin/pnpm install --frozen-lockfile
pnpm clean --lockfile
npm_config_prefer_online=true pnpm install --lockfile-only

cd ../run_v8
echo "******************************************************"
echo "Target Directory: $(pwd)"
echo "******************************************************"
#../../node_modules/.bin/pnpm install --frozen-lockfile
pnpm clean --lockfile
npm_config_prefer_online=true pnpm install --lockfile-only

cd ../run-safari
echo "******************************************************"
echo "Target Directory: $(pwd)"
echo "******************************************************"
#../../node_modules/.bin/pnpm install --frozen-lockfile
pnpm clean --lockfile
npm_config_prefer_online=true pnpm install --lockfile-only

# brew unlink php@7.4
# brew link php
