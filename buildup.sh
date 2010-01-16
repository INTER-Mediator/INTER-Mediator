#!/bin/sh

# INTER-Mediator Distribution File Builder by Masayuki Nii
#    Execute for current directory as the root of repository.

yuipath=../yuicompressor-2.4.2.jar

dt=`date "+%Y-%m-%d"`
curpath=`pwd`
mkdir ../temp
cp -r develop-im ../temp
cp dist-doc/readme.txt ../temp
cp dist-doc/change_log.txt ../temp
cp dist-doc/sample_schema.txt ../temp
cp dist-doc/TestDB.fp7 ../temp

cd ../temp

rm -rf develop-im/INTER-Mediator/FX
sed -e "s/@@@@@@@@/${dt}/" develop-im/INTER-Mediator/INTER-Mediator.js > develop-im/INTER-Mediator/__INTER-Mediator.js
java -jar "${curpath}/${yuipath}" -o develop-im/INTER-Mediator/INTER-Mediator.js develop-im/INTER-Mediator/__INTER-Mediator.js

rm develop-im/INTER-Mediator/__INTER-Mediator.js
rm INTER-Mediator-${dt}.zip
zip -r INTER-Mediator-${dt} readme.txt change_log.txt sample_schema.txt TestDB.fp7 develop-im

