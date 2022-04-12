# SAML Test

by INTER-Mediator Directive Committee (https://inter-mediator.org)

INTER-Mediator supports SAML for authentication, but it can't test within the github action's ci environment. So the
SAML feature has to be tested manually. We have a test environment within the demo server with the simplesamlphp's IdP
which just have test accounts. After someone tests the SAML features, the result has to be recorded here.

## Latest Test Record

The format of below is: [commit code from git log], [Version from composer.json], [Checker name], [Result]

- commit ce1b3167d04a8767480c40da99d3cdb90a4a9f76 (Tue Apr 12 11:07:50 2022 +0900), Ver.10(2022-04-08),
  by Masayuki Nii(2022-04-12 15:30), OK

- commit 1146c962dd1d42115cf9537b09fae951681e0fbc(Sat Jan 29 21:22:06 2022 +0900), Ver.9(2022-01-29),
  by Masayuki Nii(2022-01-30 9:00), OK

## Test Procedure

If you test as following, you need some urls. We are going to tell them personally, so please contact the
committee.

Set the SAML is active ($isSAML = true;), but the built-in auth is inactive ($samlWithBuiltInAuth = false;).

- Open the web app page, and check to show the IdP's login page not the built-in login page.
- Try to the wrong account, and check not to log in and repeatedly show the login panel.
- Try to the valid built-in account, and check not to log in.
- Try to the valid SAML account, and check to log in correctly.

Set the SAML is active ($isSAML = true;), but the built-in auth is also active ($samlWithBuiltInAuth = true;).

- Open the web app page, and check to show the the built-in login page with the SAML Auth button.
- Try to the wrong account on built-in login panel, and check not to log in and repeatedly show the login panel.
- Try to the valid built-in account on built-in login panel, and check to log in correctly.
- Try to the valid SAML account on built-in login panel, and check not to log in.
- Push the SAML Auth button, and check to show the IdP's login panel.
- After that try to the valid SAML account, and check to log in correctly.
- Try to the valid built-in account on the IdP's login panel, and check not to log in.

Set the SAML is inactive ($isSAML = false;), and the built-in auth is inactive ($samlWithBuiltInAuth = false;).

- Open the web app page, and check to show the built-in login page not to IdP's login page.
- Try to the wrong account, and check not to log in and repeatedly show the login panel.
- Try to the valid built-in account, and check to log in correctly.
- Try to the valid SAML account, and check not to log in.
