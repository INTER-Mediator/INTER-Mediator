#!/bin/sh

# INTER-Mediator Distribution File Builder by Masayuki Nii
#    Execute for current directory as the root of repository.

YUICOMP="../yuicompressor-2.4.7.jar"

version="3.5"

dt=`date "+%Y-%m-%d"`
versionInFilename=`echo "${version}" | tr '.' '_'`;

curpath=$(cd $(dirname "$0"); pwd)
echo "Working Directory is: ${curpath}"
cd "${curpath}"

cat << EOF > "${curpath}"/sedrule
s/@@@@1@@@@/${dt}/
s/@@@@2@@@@/${version}/
EOF

rm -r ../temp
mkdir ../temp
cd ../temp
cp "${curpath}"/dist-docs/*.txt .
cp "${curpath}"/dist-docs/TestDB.fp7 .
cp "${curpath}"/dist-docs/TestDB.fmp12 .

mkdir develop-im
for DIR in `ls "${curpath}"/develop-im`
do
    if [ -f "${curpath}/develop-im/${DIR}" ]; then
        echo "${curpath}/develop-im/${DIR}"
        sed -f "${curpath}/sedrule" "${curpath}/develop-im/${DIR}" > "develop-im/${DIR}"
    else
        mkdir "develop-im/${DIR}"
        for FILE in `ls "${curpath}/develop-im/${DIR}"`
        do
            if [ -f "${curpath}/develop-im/${DIR}/${FILE}" ]; then
                case "${FILE}" in
                    *\.html | *\.php | *\.js | *\.css | *\.txt)
                        echo "SED:${curpath}/develop-im/${DIR}/${FILE}"
                        sed -f "${curpath}/sedrule" "${curpath}/develop-im/${DIR}/${FILE}" > "develop-im/${DIR}/${FILE}"
                        ;;
                    *)
                        echo "CP:${curpath}/develop-im/${DIR}/${FILE}"
                        cp "${curpath}/develop-im/${DIR}/${FILE}" "develop-im/${DIR}/${FILE}"
                        ;;
                esac
            fi
        done
    fi
done

cp -r "${curpath}"/develop-im/Sample_products/images develop-im/Sample_products/
cp -r "${curpath}"/develop-im/INTER-Mediator/FX      develop-im/INTER-Mediator/
cp -r "${curpath}"/develop-im/INTER-Mediator/js_lib  develop-im/INTER-Mediator/
cp -r "${curpath}"/develop-im/INTER-Mediator/bi2php  develop-im/INTER-Mediator/
cp -r "${curpath}"/develop-im/WebSite/previous_rsrcs develop-im/WebSite/

echo "######### Marge JavaScript program"
cat develop-im/INTER-Mediator/Adapter_DBServer.js      > temp.js
cat develop-im/INTER-Mediator/INTER-Mediator-Lib.js   >> temp.js
cat develop-im/INTER-Mediator/INTER-Mediator-Page.js  >> temp.js
cat develop-im/INTER-Mediator/INTER-Mediator-Parts.js >> temp.js
cat develop-im/INTER-Mediator/INTER-Mediator.js       >> temp.js
cat develop-im/INTER-Mediator/js_lib/sha1.js          >> temp.js
cat develop-im/INTER-Mediator/js_lib/sha256.js        >> temp.js
cat develop-im/INTER-Mediator/bi2php/biBigInt.js      >> temp.js
cat develop-im/INTER-Mediator/bi2php/biMontgomery.js  >> temp.js
cat develop-im/INTER-Mediator/bi2php/biRSA.js         >> temp.js
rm develop-im/INTER-Mediator/Adapter_DBServer.js
rm develop-im/INTER-Mediator/INTER-Mediator-Lib.js
rm develop-im/INTER-Mediator/INTER-Mediator-Page.js
rm develop-im/INTER-Mediator/INTER-Mediator-Parts.js
rm develop-im/INTER-Mediator/INTER-Mediator.js
rm -rf develop-im/INTER-Mediator/js_lib
rm -rf develop-im/INTER-Mediator/bi2php

echo "######### Compress INTER-Mediator.js"
java -jar ${YUICOMP} temp.js -v --charset UTF-8 -o develop-im/INTER-Mediator/INTER-Mediator.js

echo "######### Compress Adapter_LocalDB"
java -jar ${YUICOMP} develop-im/INTER-Mediator/Adapter_LocalDB.js --charset UTF-8 -o temp.js
mv -f temp.js develop-im/INTER-Mediator/Adapter_LocalDB.js

find . -name "\.*"

#rm -rf develop-im/INTER-Mediator/FX
zip -r INTER-Mediator-${versionInFilename}.zip *.txt TestDB.fp7 TestDB.fmp12 develop-im
rm "${curpath}"/sedrule

