#!/usr/bin/env bash

rootDir=$(cd $(dirname "$0"); pwd)
cd "${rootDir}"

######### Authentication Test #########
# wdio-auth-chrome.conf.js executes on GitHub Actions
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-auth-edge.conf.js
# wdio-auth-firefox.conf.js executes on GitHub Actions

######### Editing Test #########
# wdio-editing-chrome.conf.js executes on GitHub Actions
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-editing-edge.conf.js
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-editing-firefox.conf.js

######### Form and Others Test #########
# wdio-form-chrome.conf.js executes on GitHub Actions
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-form-edge.conf.js
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-form-firefox.conf.js

######### Master/Detail Style Form Test #########
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-form-md-chrome.conf.js
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-form-md-edge.conf.js
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-form-md-firefox.conf.js

######### Searching Test #########
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-search-chrome.conf.js
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-search-edge.conf.js
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-search-firefox.conf.js

######### Synchronization Test #########
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-sync-chrome.conf.js
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-sync-edge.conf.js
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-sync-firefox.conf.js

