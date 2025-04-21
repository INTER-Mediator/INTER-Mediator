#!/bin/bash

# INTER-Mediator Deployment File Set Builder
# Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
# This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
#
# INTER-Mediator is supplied under MIT License.
# Please see the full license for details:
# https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt

# $1: file path, $2: appending file
function readFileUntilMark() {
  sed -ne '/@@IM@@IgnoringRestOfFile/q;P' <"$1" >"__temp1"
  sed -ne '/@@IM@@IgnoringNextLine/{n;d;};P' <"__temp1" >"__temp2"
  sed -ne '/@@IM@@IgnoringNextLine/d;P' <"__temp2" >>"$2"
  rm "__temp1" "__temp2"
}

version="14"

distDocDir=$(
  cd $(dirname "$0")
  pwd
)
imRoot=$(dirname "${distDocDir}")
if [ $(basename "${imRoot}") != "inter-mediator" ]; then
  /bin/echo "This command works on just composer installed inter-mediator."
  exit 1
fi
imRootOver=$(dirname "${imRoot}")
# In case of cloned repository, it might not be 'inter-mediator'
#if [ $(basename "${imRootOver}") != "inter-mediator" ]; then
#  /bin/echo "This command works on just composer installed inter-mediator."
#  exit 1
#fi
vendorDir=$(dirname "${imRootOver}")
if [ $(basename "${vendorDir}") != "vendor" ]; then
  /bin/echo "This command works on just composer installed inter-mediator."
  exit 1
fi

MINIFYJS="minify"
minifyjsDir="${vendorDir}/matthiasmullie/${MINIFYJS}"
minifyjsBin="${vendorDir}/bin/${MINIFYJS}js"
if [ -e "${minifyjsDir}" -a -e "${minifyjsBin}" ]; then
  /bin/echo " Path of minifyer (installed by composer): ${minifyjsDir}"
else
  /bin/echo "*** Minifyer isn't exist. ***"
  exit 1
fi

#### Merge js files
/bin/echo "PROCESSING: Merging JS files"
/bin/echo "/*! INTER-Mediator Ver.${version} https://inter-mediator.com/ */" >"${imRoot}/src/js/temp.js"
#  readFileUntilMark "${originalPath}/node_modules/jsencrypt/bin/jsencrypt.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/node_modules/socket.io-client/dist/socket.io.js" "${imRoot}/src/js/temp.js"
/bin/echo "" >>"${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/node_modules/jssha/dist/sha.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/node_modules/inter-mediator-formatter/index.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/node_modules/inter-mediator-nodegraph/index.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/node_modules/inter-mediator-queue/index.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/node_modules/inter-mediator-expressionparser/index.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/src/js/INTER-Mediator.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/src/js/INTER-Mediator-Page.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/src/js/INTER-Mediator-ContextPool.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/src/js/INTER-Mediator-Context.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/src/js/INTER-Mediator-LocalContext.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/src/js/INTER-Mediator-Lib.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/src/js/INTER-Mediator-Element.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/src/js/INTER-Mediator-Calc.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/src/js/Adapter_DBServer.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/src/js/INTER-Mediator-Navi.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/src/js/INTER-Mediator-UI.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/src/js/INTER-Mediator-Log.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/src/js/INTER-Mediator-Events.js" "${imRoot}/src/js/temp.js"
readFileUntilMark "${imRoot}/src/js/INTER-Mediator-DoOnStart.js" "${imRoot}/src/js/temp.js"

#### Compress INTER-Mediator.js
if [ -e "${minifyjsDir}" ]; then
  /bin/echo "MINIFYING."
  "${minifyjsBin}" "${imRoot}/src/js/temp.js" >"${imRoot}/src/js/INTER-Mediator.min.js"
  /bin/echo "" >>"${imRoot}/src/js/INTER-Mediator.min.js"
fi
rm "${imRoot}/src/js/temp.js"
