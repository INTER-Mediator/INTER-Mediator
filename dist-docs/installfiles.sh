#!/usr/bin/env bash

distDocDir=$(cd $(dirname "$0"); pwd)
originalPath=$(dirname "${distDocDir}")
cd "${originalPath}"

param=""

if [ $# -gt 1 ]; then
    echo "*** No parameter of just 1 parameter is allowed. ***" 1>&2
    exit 1
fi

for opt in "$@"
do
	case "$opt" in
		-[0-9])
			param=${opt}
			;;
		* )
			echo "invalid option -- $opt"
			exit 1
	esac
done


choice=${param:1}
if [ ${#param} = 0 ]; then
    /bin/echo "-------------------------------------------------"
    /bin/echo "Choose the task for composer/package json files:"
    /bin/echo ' (1) Clear lock files on root'
    /bin/echo ' (2) spec/<files for PHP7> to root'
    /bin/echo ' (3) root/<files for PHP7> to spec/'
    /bin/echo ' (4) Update lock files'
    /bin/echo -n "Type 1, 2, 3 or 4, and then type return----> "
    read choice
    /bin/echo ""
else
    /bin/echo "Choice by command line parameter: ${choice}"
fi

case ${choice} in
1 )
    /bin/echo "Remove lock files from root"
    rm composer.lock package-lock.json
    ;;
2 )
    /bin/echo "From spec as for PHP 7 to root"
    cp spec/composer7.json composer.json
    cp spec/composer7.lock composer.lock
    cp spec/package7.json package.json
    cp spec/package-lock7.json package-lock.json
    ;;
3 )
    /bin/echo "From root to spec as for PHP 7"
    cp composer.json spec/composer7.json
    cp composer.lock spec/composer7.lock
    cp package.json spec/package7.json
    cp package-lock.json spec/package-lock7.json
    ;;
4 )
    mv '__Did_you_run_composer_update.txt' spec/tempfile
    /bin/echo "## From spec as for PHP 7 to root"
    cp spec/composer7.json composer.json
    rm composer.lock
    cp spec/package7.json package.json
    rm package-lock.json

    /bin/echo "## composer update"
    composer update --with-all-dependencies

    /bin/echo "## From root to spec as for PHP 7"
    cp composer.json spec/composer7.json
    cp composer.lock spec/composer7.lock
    cp package.json spec/package7.json
    cp package-lock.json spec/package-lock7.json

    mv spec/tempfile '__Did_you_run_composer_update.txt'
    ;;
esac
