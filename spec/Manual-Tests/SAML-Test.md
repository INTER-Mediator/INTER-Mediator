# SAML Test

by INTER-Mediator Directive Committee (https://inter-mediator.org)

INTER-Mediator supports SAML for authentication, but it can't test within the github action's ci environment. So the
SAML feature has to be tested manually. We have a test environment within the demo server with the simplesamlphp's IdP
which just have test accounts. After someone tests the SAML features, the result has to be recorded here.

## Latest Test Record

The format of below is: [commit code from git log], [Version from composer.json], [Checker name], [Result]

- commit d1f52043b2670c518c7bd5c81e9997d5007afbc8 (Fri May 3 09:20:41 2024 +0900), 
  INTER-Mediator Ver.13(2024-04-26) with SimpleSAMLphp Ver.2.2.2,
  by Masayuki Nii(2024-05-03 12:30), OK

- commit 7ff364c4b07b4a862534b4ccfbb25f8b2986baed (Sat Feb 24 12:21:26 2024 +0900),
  INTER-Mediator Ver.13(2024-02-15) with SimpleSAMLphp Ver.2.1.3,
  by Masayuki Nii(2024-02-24 12:30), OK

- commit 103f3886a530f1fbb39619d7be234ef1f0e6cedf (Mon Jul 10 07:35:31 2023 +0900), 
  INTER-Mediator Ver.11(2023-05-31) with SimpleSAMLphp Ver.2.0.4,
  by Masayuki Nii(2023-07-10 11:10), OK

- commit d2c2dae28fa2caf9a28662172de797a83c1dd0fe (Sun Jul 2 11:49:42 2023 +0900),
  INTER-Mediator Ver.11(2023-05-31) with SimpleSAMLphp Ver.2.0.4, 
  by Masayuki Nii(2023-07-05 11:30), OK

- commit 9e71362ba2ca277987e55ee7a517a027f2b1453a (Wed Oct 12 08:09:56 2022 +0900), 
  INTER-Mediator Ver.10(2022-10-01),
  by Masayuki Nii(2022-10-12 11:45), OK

- commit ce1b3167d04a8767480c40da99d3cdb90a4a9f76 (Tue Apr 12 11:07:50 2022 +0900), 
  INTER-Mediator Ver.10(2022-04-08),
  by Masayuki Nii(2022-04-12 15:30), OK

- commit 1146c962dd1d42115cf9537b09fae951681e0fbc(Sat Jan 29 21:22:06 2022 +0900), 
  INTER-Mediator Ver.9(2022-01-29),
  by Masayuki Nii(2022-01-30 9:00), OK

## Test Procedure

If you test as following, you need some urls and accounts. We are going to tell them personally, so please contact the
committee.

Set the SAML is active ($isSAML = true;), but the built-in auth is inactive ($samlWithBuiltInAuth = false;).

- Open the web app page(https://demo.inter-mediator.com/saml-trial/chat.html), 
  and check to show the IdP's login page not the built-in login page.
- Try to the wrong account, and check not to log in and repeatedly show the login panel.
- Try to the valid built-in account, and check not to log in.
- Try to the valid SAML account, and check to log in correctly.

Set the SAML is active ($isSAML = true;), but the built-in auth is also active ($samlWithBuiltInAuth = true;).

- Open the web app page, and check to show the built-in login page with the SAML Auth button.
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
