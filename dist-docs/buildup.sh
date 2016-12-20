#!/bin/sh

# INTER-Mediator Deployment File Set Builder
# Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
# This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
#
# INTER-Mediator is supplied under MIT License.
# Please see the full license for details:
# https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt

version="5.6-dev"

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
distDocDir=$(cd $(dirname "$0"); pwd)
originalPath=$(dirname "${distDocDir}")

printf '{"version":"%s","releasedate":"%s"}' "${version}" "${dt}" > "${originalPath}/metadata.json"

topOfDir=$(dirname "${originalPath}")
buildDir="${topOfDir}/${buildRootName}"
buildPath="${buildDir}/${imRootName}"

echo " Original: ${originalPath}"
echo " Build to: ${buildPath}"

echo "-------------------------------------------------"
echo "Choose the build result from these:"
echo ' (1) Complete (everything contains)'
echo ' (2) Core only (the least set to work wep applications)'
echo ' (3) Core + Support (add Auth_Support and INTER-Mediator-Support)'
echo ' (4) Write just version and release date to metadata.json'
/bin/echo -n "Type 1, 2, 3 or 4, and then type return----> "
read choice
echo ""

if [ $choice = 4 ]; then
    exit 0;
fi

if [ -d "${buildDir}" ]; then
    rm -r "${buildDir}"
fi
mkdir -p "${buildPath}"

echo "PROCESSING: Copying php files in root"
cd "${originalPath}"
for aFile in $(ls *.php)
do
    filename=$(basename "${aFile}")
    cp "${aFile}" "${buildPath}/${filename}"
done

cp  "${originalPath}/metadata.json" "${buildPath}/metadata.json"

#### Merge js files
echo "PROCESSING: Merging JS files"
cp  "${originalPath}/INTER-Mediator.js"                          "${buildPath}/temp.js"
cat "${originalPath}/INTER-Mediator-Element.js"               >> "${buildPath}/temp.js"
cat "${originalPath}/INTER-Mediator-Events.js"                >> "${buildPath}/temp.js"
cat "${originalPath}/INTER-Mediator-Context.js"               >> "${buildPath}/temp.js"
cat "${originalPath}/INTER-Mediator-Lib.js"                   >> "${buildPath}/temp.js"
cat "${originalPath}/INTER-Mediator-Calc.js"                  >> "${buildPath}/temp.js"
cat "${originalPath}/INTER-Mediator-Page.js"                  >> "${buildPath}/temp.js"
cat "${originalPath}/INTER-Mediator-Parts.js"                 >> "${buildPath}/temp.js"
cat "${originalPath}/INTER-Mediator-Navi.js"                  >> "${buildPath}/temp.js"
cat "${originalPath}/INTER-Mediator-UI.js"                    >> "${buildPath}/temp.js"
cat "${originalPath}/lib/js_lib/tinySHA1.js"                  >> "${buildPath}/temp.js"
cat "${originalPath}/lib/js_lib/sha256.js"                    >> "${buildPath}/temp.js"
cat "${originalPath}/lib/bi2php/biBigInt.js"                  >> "${buildPath}/temp.js"
cat "${originalPath}/lib/bi2php/biMontgomery.js"              >> "${buildPath}/temp.js"
cat "${originalPath}/lib/bi2php/biRSA.js"                     >> "${buildPath}/temp.js"
cat "${originalPath}/Adapter_DBServer.js"                     >> "${buildPath}/temp.js"
cat "${originalPath}/lib/js_lib/js-expression-eval-parser.js" >> "${buildPath}/temp.js"
cat "${originalPath}/INTER-Mediator-DoOnStart.js"             >> "${buildPath}/temp.js"

cp "${buildPath}/temp.js" "${buildPath}/INTER-Mediator.js"

#### Compress INTER-Mediator.js
if [ -f "${topOfDir}/${YUICOMP}" ]; then
    sed '1s/*/*!/' "${buildPath}/temp.js" > "${buildPath}/temp2.js"

    osName=$(uname -s)
    echo "Detected OS: ${osName}"
    if [[ "${osName}" == CYGWIN* ]];  then
    	jarPath=$(cygpath -w "${topOfDir}/${YUICOMP}")
    	temp2Path=$(cygpath -w "${buildPath}/temp2.js")
    	temp3Path=$(cygpath -w "${buildPath}/temp3.js")
    	yuiLogPath=$(cygpath -w "${buildDir}/${YUICOMPLOG}")
    else
    	jarPath="${topOfDir}/${YUICOMP}"
    	temp2Path="${buildPath}/temp2.js"
    	temp3Path="${buildPath}/temp3.js"
    	yuiLogPath="${buildDir}/${YUICOMPLOG}"
    fi
    java -jar "${jarPath}"  "${temp2Path}" -v --charset UTF-8 -o "${temp3Path}" 2> "${yuiLogPath}"
    sed '1s/*!/*/' "${temp3Path}" > "${buildPath}/INTER-Mediator.js"
    rm  "${buildPath}/temp.js" "${temp2Path}" "${temp3Path}"
else
    rm  "${buildPath}/temp.js"
fi

# Copy "DB_Support" directory.
echo "PROCESSING: ${originalPath}/DB_Support"
cp -prf "${originalPath}/DB_Support" "${buildPath}"

# Copy "lib" path php contents.
echo "PROCESSING: ${originalPath}/lib"
mkdir -p "${buildPath}/lib/bi2php"
cp -p "${originalPath}/lib/bi2php/biRSA.php" "${buildPath}/lib/bi2php"
cp -prf "${originalPath}/lib/CWPKit" "${buildPath}/lib"
cp -prf "${originalPath}/lib/FX" "${buildPath}/lib"
cp -prf "${originalPath}/lib/ParagonIE" "${buildPath}/lib"
cp -prf "${originalPath}/lib/phpseclib_v1" "${buildPath}/lib"
cp -prf "${originalPath}/lib/phpseclib_v2" "${buildPath}/lib"
cp -prf "${originalPath}/lib/mailsend" "${buildPath}/lib"

if [ $choice = 3 ]; then
    dirs="Auth_Support INTER-Mediator-Support"
elif [ $choice = 2 ]; then
    dirs=""
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
            cp "${originalPath}/${TARGET}/${DIR}" "${buildPath}/${TARGET}/${DIR}"
        else
            mkdir -p "${buildPath}/${TARGET}/${DIR}"
            for FILE in $(ls "${originalPath}/${TARGET}/${DIR}")
            do
                if [ -f "${originalPath}/${TARGET}/${DIR}/${FILE}" ]; then
                    case "${FILE}" in
                        *\.html | *\.php | *\.js | *\.css | *\.txt)
#                          echo "SED: ${originalPath}/${TARGET}/${DIR}/${FILE}"
                            cp "${originalPath}/${TARGET}/${DIR}/${FILE}" "${buildPath}/${TARGET}/${DIR}/${FILE}"
                            ;;
                        *)
#                            echo "CP: ${originalPath}/${TARGET}/${DIR}/${FILE}"
                            cp -p "${originalPath}/${TARGET}/${DIR}/${FILE}" "${buildPath}/${TARGET}/${DIR}/${FILE}"
                            ;;
                    esac
                fi
            done
        fi
    done
done

if [ $choice = 1 ]; then
    echo "PROCESSING: ${originalPath}/README.md"
    cp -p   "${originalPath}/README.md" "${buildPath}"

    echo "PROCESSING: ${originalPath}/dist-docs"
    cp -prf "${originalPath}/dist-docs" "${buildPath}"

    echo "PROCESSING: Rest of ${originalPath}/Samples"
    cp -pr  "${originalPath}/Samples/Sample_products/images" "${buildPath}/Samples/Sample_products/"

# Invalidate the definition file of the DefEditor.
#    echo "PROCESSING: Invalidate the Definition File Editor for security reason."
#    defeditdeffile="${buildPath}/INTER-Mediator-Support/defedit.php"
#    sed 's|IM_Entry|/* IM_Entry|' "${defeditdeffile}" > /tmp/defedit.php
#    cp -p /tmp/defedit.php "${defeditdeffile}"
else
    echo "PROCESSING: ${originalPath}/dist-docs/License.txt"
    cp -p   "${originalPath}/dist-docs/License.txt" "${buildPath}"
	readmeLines=`wc -l "${originalPath}/dist-docs/readme.txt" | awk '{print $1}'`
	lines=`expr $readmeLines - 8`
    echo "PROCESSING: ${originalPath}/dist-docs/readme.txt"
    head -n `echo $lines` "${originalPath}/dist-docs/readme.txt" > "${buildPath}/readme.txt"
fi

echo "PROCESSING: ${originalPath}/themes"
cp -prf "${originalPath}/themes" "${buildPath}"

find "${buildPath}" -name "\.*" -exec rm -rf {} \;

#
echo ""
echo "=================================================" >> "${buildDir}/${receipt}"
echo " INTER-Mediator Ver.${version} was successfully Build" >> "${buildDir}/${receipt}"
echo " Check out: ${buildDir}" >> "${buildDir}/${receipt}"
echo "=================================================" >> "${buildDir}/${receipt}"
echo "Date: $(date)" >> "${buildDir}/${receipt}"
echo "OS Info: $(uname -a)" >> "${buildDir}/${receipt}"
echo "Original: ${originalPath}" >> "${buildDir}/${receipt}"
echo "Build to: ${buildPath}" >> "${buildDir}/${receipt}"
if [ $choice = 1 ]; then
    echo 'Your Choice: (1) Complete (everything contains)' >> "${buildDir}/${receipt}"
elif [ $choice = 2 ]; then
    echo 'Your Choice: (2) Core only (the least set to work wep applications)' >> "${buildDir}/${receipt}"
elif [ $choice = 3 ]; then
    echo 'Your Choice: (3) Core + Support (add Auth_Support and INTER-Mediator-Support)' >> "${buildDir}/${receipt}"
else
    echo 'Your Choice: (1) Complete (everything contains)' >> "${buildDir}/${receipt}"
fi
echo "" >> "${buildDir}/${receipt}"
echo "You can deploy the 'INTER-Mediator' folder into your web applications. Enjoy!!" >> "${buildDir}/${receipt}"
echo "" >> "${buildDir}/${receipt}"
echo "INTER-Mediator Web Site: http://inter-mediator.org" >> "${buildDir}/${receipt}"

echo "================================================="
echo " INTER-Mediator Ver.${version} was successfully Build"
echo " Check out: ${buildDir}"
echo "================================================="
