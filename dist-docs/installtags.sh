#!/usr/bin/env bash

distDocDir=$(cd $(dirname "$0"); pwd)
seedComposer="${distDocDir}/composer-seed/composer-"
baseVersion="16"
versions="8.1,8.2,8.3,8.4,8.5"
originalPath=$(dirname "${distDocDir}")
cd "${originalPath}"

IFS=',' read -ra versionArray <<< "$versions"
for ver in "${versionArray[@]}"; do
    echo "================"
    echo "Processing ${baseVersion}-${ver}"
    echo "================"
    git checkout master
    rm composer.json composer.lock
    cp -f "${seedComposer}${ver}.json" "${originalPath}/composer.json"
    cp -f "${seedComposer}${ver}.lock" "${originalPath}/composer.lock"
    git branch -D "Ver.${baseVersion}-PHP${ver}"
    git branch "Ver.${baseVersion}-PHP${ver}"
    git checkout "Ver.${baseVersion}-PHP${ver}"
    git add composer.json composer.lock
    git pull origin "Ver.${baseVersion}-PHP${ver}"
    git commit -m "Update composer.json and composer.lock for PHP ${ver}"
    git push origin "Ver.${baseVersion}-PHP${ver}"
    git tag -d "${baseVersion}-${ver}"
    git tag "${baseVersion}-${ver}"
    git push origin --delete "Ver.${baseVersion}-PHP${ver}"
    git push origin "Ver.${baseVersion}-PHP${ver}"
    git push origin --delete "${baseVersion}-${ver}"
    git push origin "${baseVersion}-${ver}"
done
git push origin --tags
git checkout master

exit
