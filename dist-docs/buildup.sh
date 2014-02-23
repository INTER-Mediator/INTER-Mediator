#!/bin/sh

# INTER-Mediator Deployment File Set Builder
# by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.

# This project started at the end of 2009.
# INTER-Mediator is supplied under MIT License.

version="4.1.6"

# The jar file of YUI can be donwloaded from below.
# http://grepcode.com/snapshot/repo1.maven.org/maven2/com.yahoo.platform.yui/yuicompressor/2.4.7
#
YUICOMP="yuicompressor-2.4.7.jar"
YUICOMPLOG="yuicomp.log"
buildRootName="im_build"
imRootName="INTER-Mediator"
receipt="receipt.txt"

echo "================================================="
echo " Start to build the INTER-Mediator Ver.${version}"
echo "-------------------------------------------------"

dt=$(date "+%Y-%m-%d")

sedrule="/tmp/sedrule"
cat << EOF > "${sedrule}"
s/@@@@1@@@@/${dt}/
s/@@@@2@@@@/${version}/
EOF

distDocDir=$(cd $(dirname "$0"); pwd)
originalPath=$(dirname "${distDocDir}")

topOfDir=$(dirname "${originalPath}")
buildDir="${topOfDir}/${buildRootName}"
buildPath="${buildDir}/${imRootName}"

echo " Original: ${originalPath}"
echo " Build to: ${buildPath}"

echo "-------------------------------------------------"
echo "Choose the build result from these:"
echo " (1) Complete (everything contains)"
echo " (2) Core only (the least set to work wep applications)"
echo " (3) Core + Support (add Auth_Support and INTER-Mediator-Support)"
/bin/echo -n "Type 1, 2 or 3, and then type return----> "
read choice
echo ""

if [ -d "${buildDir}" ]; then
    rm -r "${buildDir}"
fi
mkdir -p "${buildPath}"

echo "PROCESSING: Copying php files in root"
cd "${originalPath}"
for aFile in $(ls *.php)
do
    filename=$(basename "${aFile}")
#    echo "SED: ${aFile}"
    sed -f "${sedrule}" "${aFile}" > "${buildPath}/${filename}"
done

#### Merge js files
echo "PROCESSING: Merging JS files"
sed -f "${sedrule}" "${originalPath}/Adapter_DBServer.js" > "${buildPath}/temp.js"
cat ${originalPath}/INTER-Mediator-Lib.js   >> "${buildPath}/temp.js"
cat ${originalPath}/INTER-Mediator-Page.js  >> "${buildPath}/temp.js"
cat ${originalPath}/INTER-Mediator-Parts.js >> "${buildPath}/temp.js"
sed -f "${sedrule}" ${originalPath}/INTER-Mediator.js >> "${buildPath}/temp.js"
cat ${originalPath}/lib/js_lib/sha1.js          >> "${buildPath}/temp.js"
cat ${originalPath}/lib/js_lib/sha256.js        >> "${buildPath}/temp.js"
cat ${originalPath}/lib/bi2php/biBigInt.js      >> "${buildPath}/temp.js"
cat ${originalPath}/lib/bi2php/biMontgomery.js  >> "${buildPath}/temp.js"
cat ${originalPath}/lib/bi2php/biRSA.js         >> "${buildPath}/temp.js"

sed -f "${sedrule}" "${buildPath}/temp.js" > "${buildPath}/INTER-Mediator.js"

#### Compress INTER-Mediator.js
if [ -f "${topOfDir}/${YUICOMP}" ]; then
    sed '1s/*/*!/' "${buildPath}/temp.js" > "${buildPath}/temp2.js"
    java -jar "${topOfDir}/${YUICOMP}" "${buildPath}/temp2.js" -v --charset UTF-8 -o "${buildPath}/temp3.js" 2> "${buildDir}/${YUICOMPLOG}"
    sed '1s/*!/*/' "${buildPath}/temp3.js" > "${buildPath}/INTER-Mediator.js"
    rm  "${buildPath}/temp.js" "${buildPath}/temp2.js" "${buildPath}/temp3.js"
fi

# Copy "lib" path php contents.
echo "PROCESSING: ${originalPath}/lib"
mkdir -p "${buildPath}/lib/bi2php"
cp "${originalPath}/lib/bi2php/biRSA.php" "${buildPath}/lib/bi2php"
cp -rf "${originalPath}/lib/FX" "${buildPath}/lib"
cp -rf "${originalPath}/lib/phpseclib" "${buildPath}/lib"
cp -rf "${originalPath}/lib/mailsend" "${buildPath}/lib"

if [ $choice = 3 ]; then
    dirs=""
elif [ $choice = 2 ]; then
    dirs="Auth_Support INTER-Mediator-Support"
else
    dirs="Auth_Support INTER-Mediator-Support INTER-Mediator-UnitTest Samples"
fi

for TARGET in ${dirs}
do
    echo "PROCESSING: ${originalPath}/${TARGET}"
    mkdir -p "${buildPath}/${TARGET}"
    for DIR in $(ls "${originalPath}/${TARGET}/")
    do
#        echo "Processing: ${originalPath}/${TARGET}/${DIR}"
        if [ -f "${originalPath}/${TARGET}/${DIR}" ]; then
#            echo "SED: ${originalPath}/${TARGET}/${DIR}"
            sed -f "${sedrule}" "${originalPath}/${TARGET}/${DIR}" > "${buildPath}/${TARGET}/${DIR}"
        else
            mkdir -p "${buildPath}/${TARGET}/${DIR}"
            for FILE in $(ls "${originalPath}/${TARGET}/${DIR}")
            do
                if [ -f "${originalPath}/${TARGET}/${DIR}/${FILE}" ]; then
                    case "${FILE}" in
                        *\.html | *\.php | *\.js | *\.css | *\.txt)
#                          echo "SED: ${originalPath}/${TARGET}/${DIR}/${FILE}"
                            sed -f "${sedrule}" "${originalPath}/${TARGET}/${DIR}/${FILE}" > "${buildPath}/${TARGET}/${DIR}/${FILE}"
                            ;;
                        *)
#                            echo "CP: ${originalPath}/${TARGET}/${DIR}/${FILE}"
                            cp "${originalPath}/${TARGET}/${DIR}/${FILE}" "${buildPath}/${TARGET}/${DIR}/${FILE}"
                            ;;
                    esac
                fi
            done
        fi
    done
done

if [ $choice = 1 ]; then
    echo "PROCESSING: ${originalPath}/readme.md"
    cp     "${originalPath}/readme.md" "${buildPath}"

    echo "PROCESSING: ${originalPath}/dist-docs"
    cp -rf "${originalPath}/dist-docs" "${buildPath}"

    echo "PROCESSING: Rest of ${originalPath}/Samples"
    cp -r  "${originalPath}/Samples/Sample_products/images" "${buildPath}/Samples/Sample_products/"
    cp -r  "${originalPath}/Samples/WebSite/previous_rsrcs" "${buildPath}/Samples/WebSite/"

# Invalidate the definition file of the DefEditor.
    echo "PROCESSING: Invalidate the Definition File Editor for security reason."
    defeditdeffile="${buildPath}/INTER-Mediator-Support/defedit.php"
    sed 's|IM_Entry|/* IM_Entry|' "${defeditdeffile}" > /tmp/defedit.php
    cp /tmp/defedit.php "${defeditdeffile}"
fi

find "${buildPath}" -name "\.*" -exec rm -rf {} \;

rm "${sedrule}"

#
echo ""
echo "=================================================" >> "${buildDir}/${receipt}"
echo " INTER-Mediator Ver.${version} was successfully Build" >> "${buildDir}/${receipt}"
echo " Check out: ${buildDir}" >> "${buildDir}/${receipt}"
echo "=================================================" >> "${buildDir}/${receipt}"
echo "Date: $(date)" >> "${buildDir}/${receipt}"
echo "Original: ${originalPath}" >> "${buildDir}/${receipt}"
echo "Build to: ${buildPath}" >> "${buildDir}/${receipt}"
if [ $choice = 1 ]; then
    echo "Your Choice: (1) Complete (everything contains)" >> "${buildDir}/${receipt}"
elif [ $choice = 2 ]; then
    echo "Your Choice: (2) Core only (the least set to work wep applications)" >> "${buildDir}/${receipt}"
elif [ $choice = 3 ]; then
    echo "Your Choice: (3) Core + Support (add Auth_Support and INTER-Mediator-Support)" >> "${buildDir}/${receipt}"
else
    echo "Your Choice: (1) Complete (everything contains)" >> "${buildDir}/${receipt}"
fi
echo "" >> "${buildDir}/${receipt}"
echo "You can deploy the 'INTER-Mediator' folder into your web applications. Enjoy!!" >> "${buildDir}/${receipt}"
echo "" >> "${buildDir}/${receipt}"
echo "INTER-Mediator Web Site: http://inter-mediator.org" >> "${buildDir}/${receipt}"

echo "================================================="
echo " INTER-Mediator Ver.${version} was successfully Build"
echo " Check out: ${buildDir}"
echo "================================================="
