#!/usr/bin/env bash

rootDir=$(cd $(dirname "$0"); pwd)
cd "${rootDir}"

UNSTABLE=true

######### Authentication Test #########
# wdio-auth-chrome.conf.js executes on GitHub Actions
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-auth-edge.conf.js
# wdio-auth-firefox.conf.js executes on GitHub Actions

######### Editing Test #########
# wdio-editing-chrome.conf.js executes on GitHub Actions
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-editing-edge.conf.js
if [[ "$UNSTABLE" != true ]]; then
  ../../dist-docs/sample_schema_all_apply.sh
  npx wdio run wdio-editing-firefox.conf.js
fi

######### Form and Others Test #########
# wdio-form-chrome.conf.js executes on GitHub Actions
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-form-edge.conf.js
if [[ "$UNSTABLE" != true ]]; then
  ../../dist-docs/sample_schema_all_apply.sh
  npx wdio run wdio-form-firefox.conf.js
fi

######### Master/Detail Style Form Test #########
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-form-md-chrome.conf.js
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-form-md-edge.conf.js
if [[ "$UNSTABLE" != true ]]; then
  ../../dist-docs/sample_schema_all_apply.sh
  npx wdio run wdio-form-md-firefox.conf.js
fi

######### Searching Test #########
cd ../run_v8
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-search-chrome.conf.js
if [[ "$UNSTABLE" != true ]]; then
  ../../dist-docs/sample_schema_all_apply.sh
  npx wdio run wdio-search-edge.conf.js
fi
if [[ "$UNSTABLE" != true ]]; then
  ../../dist-docs/sample_schema_all_apply.sh
  npx wdio run wdio-search-firefox.conf.js
fi

######### Synchronization Test #########
../../dist-docs/sample_schema_all_apply.sh
npx wdio run wdio-sync-chrome.conf.js
if [[ "$UNSTABLE" != true ]]; then
  ../../dist-docs/sample_schema_all_apply.sh
  npx wdio run wdio-sync-edge.conf.js
fi
if [[ "$UNSTABLE" != true ]]; then
  ../../dist-docs/sample_schema_all_apply.sh
  npx wdio run wdio-sync-firefox.conf.js
fi
