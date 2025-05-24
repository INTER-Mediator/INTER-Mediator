# OAuth Test

by INTER-Mediator Directive Committee (https://inter-mediator.org)

INTER-Mediator supports OAuth for authentication, but it can't test within the github action's ci environment.
So the OAuthfeature has to be tested manually.
We have a test environment within the demo server to test provider's accounts.
After someone tests the OAuth features, the result has to be recorded here.

## Latest Test Record

The format of below is: [commit code from git log], [Version from composer.json], [Checker name], [Result]

- commit 51b60d401f775cfdab28a2b28dade90292a28cdf (Sun May 18 16:45:01 2025 +0900)
  INTER-Mediator Ver.14 (2025-05-18) with SimpleSAMLphp Ver.2.4.1,
  PHP 8.1.2-1ubuntu2.19+MySQL 8.0.40-0ubuntu0.22.04.1+Chrome (136.0.7103.114) on mac,
  by Masayuki Nii <nii@msyk.net>, OK


## Test Procedure

The test application(https://github.com/INTER-Mediator/IMTesting_OAuth) is deployed to our server. 

- Open the web app menu page(https://demo.inter-mediator.com/IMTesting_OAuth).
- Here is the starting point of following tests.

### Authentication with Google.

- Click the "chat.html" link.
- Login panel is shown.
- Click "Sign in with Google" button.
- Follow the Google login process.
- Show the chat.html page with generated username.
- Check to be able to post any message.
- logout, and shows the login panel again.

### Authentication with Facebook.

- Click the "chat.html" link.
- Login panel is shown.
- Click "Facebook" button.
- Follow the Facebook login process.
- Show the chat.html page with generated username.
- Check to be able to post any message.
- logout, and shows the login panel again.

## Past Test Record

The format of below is: [commit code from git log], [Version from composer.json], [Checker name], [Result]

### Previous Test Records

TBD

