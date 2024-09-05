# End-to-End Test

by INTER-Mediator Directive Committee (https://inter-mediator.org)

Although some of end-to-end tests with Webdriver.io are running on GitHUb Actions,
others don't work on GitHub Actions because unexpected errors.
In this document, we report the result of end-to-end tests rest of GitHUb Actions by running manually.

## Latest Test Record

The format of below is: [commit code from git log], [Version from composer.json], [Passed Test], [Environment], [Checker name], [Result].

The test is identified by .conf.js files in the /spec/run directory.
The test 'wdio-auth-chrome.conf.js' and 'wdio-firefox.conf.js' are running on GitHub Actions.

- commit f9e4c32c37fcec902822b8297a2d61bd9c305060 (Fri Apr 26 09:35:19 2024 +0900)
  INTER-Mediator Ver.13 (2024-02-24),
  wdio-search-chrome.conf.js,
  PHP 8.3.6 (Homebrew based)+MySQL 8.3.0/PostgreSQL 14.10_1/SQLite 3.43.2+Chrome (124.0.6367.92) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

- commit f9e4c32c37fcec902822b8297a2d61bd9c305060 (Fri Apr 26 09:35:19 2024 +0900)
  INTER-Mediator Ver.13 (2024-02-24),
  wdio-sync-chrome.conf.js,
  PHP 8.3.6 (Homebrew based)+MySQL 8.3.0/PostgreSQL 14.10_1/SQLite 3.43.2+Chrome (124.0.6367.92) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

- commit f9e4c32c37fcec902822b8297a2d61bd9c305060 (Fri Apr 26 09:35:19 2024 +0900)
  INTER-Mediator Ver.13 (2024-02-24),
  wdio-sync-edge.conf.js,
  PHP 8.3.6 (Homebrew based)+MySQL 8.3.0/PostgreSQL 14.10_1/SQLite 3.43.2+MicrosoftEdge (124.0.2478.51 ) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

- commit 1f48bf9a70ef48c25633691b4ac12e8a0c0ca843 (Thu Apr 25 19:47:31 2024 +0900)
  INTER-Mediator Ver.13 (2024-02-24),
  wdio-edge.conf.js,
  PHP 8.3.6 (Homebrew based)+MySQL 8.3.0/PostgreSQL 14.10_1/SQLite 3.43.2+MicrosoftEdge (124.0.2478.51 ) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

- commit 38abab2ef6e372d4ae37376f732b1804cdb9ee05 (Sat Feb 24 18:09:51 2024 +0900),
  INTER-Mediator Ver.13 (2024-02-24),
  wdio-sync-edge.conf.js,
  PHP 8.3.2 (Homebrew based)+MySQL 8.3.0/PostgreSQL 14.10_1/SQLite 3.43.2+MicrosoftEdge (122.0.2365.52) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

- commit 7240f8bad63023f01d396e830139e178d3eebda7 (Sun Feb 18 15:09:22 2024 +0900),
  INTER-Mediator Ver.13 (2024-02-15),
  wdio-sync-chrome.conf.js,
  PHP 8.3.2 (Homebrew based)+MySQL 8.3.0/PostgreSQL 14.10_1/SQLite 3.43.2+Chrome (121.0.6167.184) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

- commit 3783f9cdfda23383f75593be1af890bc9d14ee27 (Thu Feb 15 13:13:02 2024 +0900),
  INTER-Mediator Ver.13 (2024-02-15),
  wdio-edge.conf.js,
  PHP 8.3.2 (Homebrew based)+MySQL 8.3.0/PostgreSQL 14.10_1/SQLite 3.43.2+MicrosoftEdge (121.0.2277.112) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

- commit 3783f9cdfda23383f75593be1af890bc9d14ee27 (Thu Feb 15 13:13:02 2024 +0900),
  INTER-Mediator Ver.13 (2024-02-15),
  wdio-sync-edge.conf.js,
  PHP 8.3.2 (Homebrew based)+MySQL 8.3.0/PostgreSQL 14.10_1/SQLite 3.43.2+MicrosoftEdge (121.0.2277.112) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

- commit 54e0853ffc0135451baf56c4ec8ffb99a1103473 (Sun Feb 4 14:25:34 2024 +0900),
  INTER-Mediator Ver.12 (2023-11-16),
  wdio-edge.conf.js,
  PHP 8.3.2 (Homebrew based)+MySQL 8.3.0/PostgreSQL 14.10_1/SQLite 3.43.2+MicrosoftEdge (v121.0.2277.98) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

- commit 54e0853ffc0135451baf56c4ec8ffb99a1103473 (Sun Feb 4 14:25:34 2024 +0900),
  INTER-Mediator Ver.12 (2023-11-16),
  wdio-sync-edge.conf.js,
  PHP 8.3.2 (Homebrew based)+MySQL 8.3.0/PostgreSQL 14.10_1/SQLite 3.43.2+MicrosoftEdge (v121.0.2277.98) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

- commit 54e0853ffc0135451baf56c4ec8ffb99a1103473 (Sun Feb 4 14:25:34 2024 +0900),
  INTER-Mediator Ver.12 (2023-11-16),
  wdio-sync-chrome.conf.js,
  PHP 8.3.2 (Homebrew based)+MySQL 8.3.0/PostgreSQL 14.10_1/SQLite 3.43.2+chrome (v120.0.6099.234) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

- commit 54e0853ffc0135451baf56c4ec8ffb99a1103473 (Sun Feb 4 14:25:34 2024 +0900),
  INTER-Mediator Ver.12 (2023-11-16),
  wdio-search-chrome.conf.js,
  PHP 8.3.2 (Homebrew based)+MySQL 8.3.0/PostgreSQL 14.10_1/SQLite 3.43.2+chrome (v120.0.6099.234) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

- Tests in wdio-search-edge.conf.js are integrated into wdio-edge.conf.js.

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
