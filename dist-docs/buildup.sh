#!/bin/sh

# INTER-Mediator Distribution File Builder by Masayuki Nii
#    Execute for current directory as the root of repository.

YUICOMP="../yuicompressor-2.4.7.jar"

version="4.0"

dt=`date "+%Y-%m-%d"`
versionInFilename=`echo "${version}" | tr '.' '_'`;
sedrule="/tmp/sedrule"

curpath=$(cd $(dirname "$0"); pwd)
curpath=$(dirname "${curpath}")
echo "Working Directory is: ${curpath}"
cd "${curpath}"

cat << EOF > "${sedrule}"
s/@@@@1@@@@/${dt}/
s/@@@@2@@@@/${version}/
EOF

rm -r ../temp
mkdir ../temp
cd ../temp

for aFile in $(ls "${curpath}"/*.php)
do
    filename=$(basename ${aFile})
    echo "SED:${aFile}"
    sed -f "${sedrule}" "${aFile}" > "./${filename}"
done
cp "${curpath}/index.html" .
cp "${curpath}/readme.md" .

#### Merge js files
cat ${curpath}/Adapter_DBServer.js      > temp.js
cat ${curpath}/INTER-Mediator-Lib.js   >> temp.js
cat ${curpath}/INTER-Mediator-Page.js  >> temp.js
cat ${curpath}/INTER-Mediator-Parts.js >> temp.js
cat ${curpath}/INTER-Mediator.js       >> temp.js
cat ${curpath}/lib/js_lib/sha1.js          >> temp.js
cat ${curpath}/lib/js_lib/sha256.js        >> temp.js
cat ${curpath}/lib/bi2php/biBigInt.js      >> temp.js
cat ${curpath}/lib/bi2php/biMontgomery.js  >> temp.js
cat ${curpath}/lib/bi2php/biRSA.js         >> temp.js

#### Compress INTER-Mediator.js
java -jar ${YUICOMP} temp.js -v --charset UTF-8 -o INTER-Mediator.js
rm temp.js

cp -rf "${curpath}/dist-docs" .
mkdir lib
mkdir lib/bi2php
cp "${curpath}/lib/bi2php/biRSA.php" ./lib/bi2php
cp -rf "${curpath}/lib/FX" ./lib
cp -rf "${curpath}/lib/phpseclib" ./lib

dirs="Auth_Support INTER-Mediator-Support INTER-Mediator-UnitTest Samples"

for TARGET in ${dirs}
do
mkdir ${TARGET}
for DIR in $(ls "${curpath}/${TARGET}")
do
    if [ -f "${curpath}/${TARGET}/${DIR}" ]; then
        echo "SED:${curpath}/${TARGET}/${DIR}"
        sed -f "${sedrule}" "${curpath}/${TARGET}/${DIR}" > "${TARGET}/${DIR}"
    else
        mkdir "Samples/${DIR}"
        for FILE in `ls "${curpath}/${TARGET}/${DIR}"`
        do
            if [ -f "${curpath}/${TARGET}/${DIR}/${FILE}" ]; then
                case "${FILE}" in
                    *\.html | *\.php | *\.js | *\.css | *\.txt)
                        echo "SED:${curpath}/${TARGET}/${DIR}/${FILE}"
                        sed -f "${sedrule}" "${curpath}/${TARGET}/${DIR}/${FILE}" > "${TARGET}/${DIR}/${FILE}"
                        ;;
                    *)
                        echo "CP:${curpath}/${TARGET}/${DIR}/${FILE}"
                        cp "${curpath}/${TARGET}/${DIR}/${FILE}" "${TARGET}/${DIR}/${FILE}"
                        ;;
                esac
            fi
        done
    fi
done
done

# Invalid the definition file of the DefEditor.
defeditdeffile="INTER-Mediator-Support/defedit.php"
sed 's|IM_Entry|/* IM_Entry|' "${defeditdeffile}" > /tmp/defedit.php
cp /tmp/defedit.php "${defeditdeffile}"

cp -r "${curpath}"/Samples/Sample_products/images   Samples/Sample_products/
cp -r "${curpath}"/Samples/WebSite/previous_rsrcs   Samples/WebSite/

find . -name "\.*" -exec rm -rf {} \;

zip -r ../INTER-Mediator-${versionInFilename}.zip *
rm "${sedrule}"

