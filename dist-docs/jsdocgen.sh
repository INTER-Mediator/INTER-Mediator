#!/bin/sh

# INTER-Mediator Deployment File Set Builder
# Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
# This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
#
# INTER-Mediator is supplied under MIT License.
# Please see the full license for details:
# https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt

# How to install the JSDoc (Node and npm are already installed)
# 1. Set your current directory to the parent of INTER-Mediator directory.
# 2. Do the "npm install jsdoc" command.

jsDocRelativePath="./node_modules/.bin/jsdoc"
jsDocOutput="./jsdoc-out/"
jsDocIndex="jsdoc-out/index.html"
distDocDir=$(cd $(dirname "$0"); pwd)
originalPath=$(dirname "${distDocDir}")
parentPath=$(dirname "${originalPath}")

"${parentPath}/${jsDocRelativePath}" -d "${parentPath}/${jsDocOutput}" "${originalPath}"

# As you understand, this script works on OS X only.
open "${parentPath}/${jsDocIndex}"
