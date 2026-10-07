# OAuth Test

by INTER-Mediator Directive Committee (https://inter-mediator.org)

INTER-Mediator supports OAuth for authentication, but it can't test within the GitHub Action's ci environment.
So the OAuth feature has to be tested manually.
We have a test environment within the demo server to test provider's accounts.
After someone tests the OAuth features, the result has to be recorded here.

## Latest Test Record

The format of below is: [commit code from git log], [Version from composer.json], [Checker name], [Result]

- commit 3d33e5254f63416bb455c7bfe9a4ec99fbd79db9 (Thu Oct 1 09:21:00 2026 +0900)
  INTER-Mediator Ver.16 (2026-06-05),
  PHP 8.3.6+MySQL Ver 8.0.46-0ubuntu0.24.04.4+Brave 1.95.101 on mac,
  by Masayuki Nii (2026-10-01), OK


## Test Procedure

The test application(https://github.com/INTER-Mediator/IMTesting_OAuth) is deployed to our server. 

- Open the web app menu page(https://demo.inter-mediator.com/IMTesting_OAuth).
- Here is the starting point of following tests.

### Authentication with Google.

- Click the "chat.html" link.
- The login panel is shown.
- Click the "Sign in with Google" button.
- Follow the Google login process.
- Show the chat.html page with a generated username.
- Check to be able to post any message.
- logout and shows the login panel again.

### Authentication with Facebook.

- Click the "chat.html" link.
- The Login panel is shown.
- Click the "Facebook" button.
- Follow the Facebook login process.
- Show the chat.html page with a generated username.
- Check to be able to post any message.
- logout and shows the login panel again.

## Past Test Record

The format of below is: [commit code from git log], [Version from composer.json], [Checker name], [Result]

- commit 3d33e5254f63416bb455c7bfe9a4ec99fbd79db9 (Thu Oct 1 09:21:00 2026 +0900)
  INTER-Mediator Ver.16 (2026-06-05),
  PHP 8.3.6+MySQL Ver 8.0.46-0ubuntu0.24.04.4+Brave 1.95.101 on mac,
  by Masayuki Nii (2026-10-01), OK

- commit a7e10bcf5da603ffa0b174cd3f190f3aa8ed3d2c (Thu May 7 15:28:25 2026 +0900)
  INTER-Mediator Ver.15 (2026-05-07),
  by Masayuki Nii <nii@msyk.net>, OK

- commit 51b60d401f775cfdab28a2b28dade90292a28cdf (Sun May 18 16:45:01 2025 +0900)
  INTER-Mediator Ver.14 (2025-05-18) with SimpleSAMLphp Ver.2.4.1,
  PHP 8.1.2-1ubuntu2.19+MySQL 8.0.40-0ubuntu0.22.04.1+Chrome (136.0.7103.114) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

### Previous Test Records

TBD

