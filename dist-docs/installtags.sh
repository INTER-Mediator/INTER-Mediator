#!/usr/bin/env bash

# This script is for creating branches for each PHP version.
# 1. Start installfiles.sh
# 2. Comit and push the "master" branch
# 3. Start installbranches.sh
# 4. Create Pull Request for every branch (Ver.8.1-PHP8.1, Ver.8.1-PHP8.2, Ver.8.1-PHP8.3, Ver.8.1-PHP8.4)
# 5. Start installtags.sh

baseVersion="16"
versions="8.1,8.2,8.3,8.4,8.5"
originalPath=$(dirname "${distDocDir}")
cd "${originalPath}"

IFS=',' read -ra versionArray <<< "$versions"
for ver in "${versionArray[@]}"; do
    echo "================"
    echo "Processing ${baseVersion}-${ver}"
    echo "================"
    git push upstream --delete "${baseVersion}-${ver}"
    git push upstream "${baseVersion}-${ver}"
done
# git push origin --tags
# git checkout master

exit