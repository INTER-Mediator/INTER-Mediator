# INTER-Mediator End-To-End Test with WebdriverIO

Installation and starting tests are below. The last command can the end-to-end test with WebdriverIO.
```
cd spec/run
pnpm ci
pnpm run seed-drivers
npx wdio run wdio.conf.js
```
After setup with the command ```pnpm install --frozen-lockfile```, you can test with this command on the root of this repository:
```
composer wdio-test
```

Also refer to the GitHub Action at /.github/workflows/php.yml.

The samples/E2E-Test directory has the target pages for these tests.

## macOS with ESET / endpoint protection

ESET Cyber Security's real-time and web protection interrupts WebdriverIO's
automatic driver downloads (the wdio download process is killed right before the
driver binary is written, leaving a truncated cache without the executable).
A plain `curl` download is not blocked, so run the seeding helper to fetch the
driver binaries and place them in the cache paths wdio checks. wdio then skips
its own (blocked) downloads:

```
pnpm run seed-drivers            # chromedriver + geckodriver + edgedriver (installed browsers)
pnpm run seed-drivers chrome     # only a specific driver (chrome | firefox | edge)
```

It seeds only the drivers for browsers that are actually installed, and skips any
that are already present. `pnpm run wdio` and `composer wdio-test` run it
automatically (the default `wdio.conf.js` uses Chrome, Firefox and Edge). For the
direct `npx wdio <config>` commands below, run `pnpm run seed-drivers` once first.

## Other commands

Just run the test with Google Chrome

```
npx wdio wdio-auth-chrome.conf.js 
```

Just run the test with Microsoft Edge

```
npx wdio wdio-form-edge.conf.js 
```

Just run the test with Firefox

```
npx wdio wdio-auth-firefox.conf.js 
```

Just run the test with Safari. This test works on the /spec/run-safari directory.

```
cd /spec/run-safari
npx wdio wdio-safari.conf.js 
```

Another syncing between clients test.

```
npx wdio wdio-sync-chrome.conf.jp
```

