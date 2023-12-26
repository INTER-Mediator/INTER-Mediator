# End-to-End Test

by INTER-Mediator Directive Committee (https://inter-mediator.org)

Although some of end-to-end tests with Webdriver.io are running on GitHUb Actions,
others don't work on GitHub Actions because unexpected errors.
In this document, we report the result of end-to-end tests rest of GitHUb Actions by running manually.

## Latest Test Record

The format of below is: [commit code from git log], [Version from composer.json], [Passed Test], [Environment], [Checker name], [Result].

The test is identified by .conf.js files in the /spec/run directory.
The test 'wdio-chrome.conf.js' and 'wdio-firefox.conf.js' are running on GitHub Actions.

- commit f529a39c53f8afc6baea956747bf11af455db226 (Sun Dec 24 13:56:23 2023 +0900),
  INTER-Mediator Ver.12 (2023-11-16),
  wdio-search-chrome.conf.js,
  PHP 8.3.0 (Homebrew based)+MySQL 8.2.0/PostgreSQL 14.10/SQLite 3.43.2+chrome (v120.0.6099.109) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

- commit f529a39c53f8afc6baea956747bf11af455db226 (Sun Dec 24 13:56:23 2023 +0900),
  INTER-Mediator Ver.12 (2023-11-16),
  wdio-sync-chrome.conf.js,
  PHP 8.3.0 (Homebrew based)+MySQL 8.2.0/PostgreSQL 14.10/SQLite 3.43.2+chrome (v120.0.6099.109) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

- commit f529a39c53f8afc6baea956747bf11af455db226 (Sun Dec 24 13:56:23 2023 +0900),
  INTER-Mediator Ver.12 (2023-11-16),
  wdio-edge.conf.js,
  PHP 8.3.0 (Homebrew based)+MySQL 8.2.0/PostgreSQL 14.10/SQLite 3.43.2+MicrosoftEdge (v120.0.2210.91) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

- commit f529a39c53f8afc6baea956747bf11af455db226 (Sun Dec 24 13:56:23 2023 +0900),
  INTER-Mediator Ver.12 (2023-11-16),
  wdio-search-edge.conf.js,
  PHP 8.3.0 (Homebrew based)+MySQL 8.2.0/PostgreSQL 14.10/SQLite 3.43.2+MicrosoftEdge (v120.0.2210.91) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

- commit f529a39c53f8afc6baea956747bf11af455db226 (Sun Dec 24 13:56:23 2023 +0900),
  INTER-Mediator Ver.12 (2023-11-16),
  wdio-sync-edge.conf.js,
  PHP 8.3.0 (Homebrew based)+MySQL 8.2.0/PostgreSQL 14.10/SQLite 3.43.2+MicrosoftEdge (v120.0.2210.91) on mac,
  by Masayuki Nii <nii@msyk.net>, OK
