# INTER-Mediator End-To-End Test with WebdriverIO

Installation and starging tests are below. The last command can the end-to-end test with WebdriverIO.
```
cd spec/run
npm update
npm run wdio
```
After setup with the command ```npm update```, you can test with this command on the root of this repository:
```
composer wdio-test
```

Also refer to the GitHub Action at /.github/workflows/php.yml.

The samples/E2E-Test directory has the target pages for these tests.

## Other commands

Just run the test with Google Chrome

```
npx wdio wdio-chrome.conf.js 
```

Just run the test with Microsoft Edge

```
npx wdio wdio-edge.conf.js 
```

Just run the test with Firefox

```
npx wdio wdio-firefox.conf.js 
```

Just run the test with Safari. This test works on the /spec/run-safari directory.

```
cd /spec/run-safari
npx wdio wdio-safari.conf.js 
```