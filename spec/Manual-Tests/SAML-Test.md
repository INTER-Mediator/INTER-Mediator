# SAML Test

by INTER-Mediator Directive Committee (https://inter-mediator.org)

INTER-Mediator supports SAML for authentication, but it can't test within the github action's ci environment. So the
SAML feature has to be tested manually. We have a test environment within the demo server with the simplesamlphp's IdP
which just have test accounts. After someone tests the SAML features, the result has to be recorded here.

## Latest Test Record

The format of below is: [commit code from git log], [Version from composer.json], [Checker name], [Result]

- commit 51b60d401f775cfdab28a2b28dade90292a28cdf (Sun May 18 16:45:01 2025 +0900)
  INTER-Mediator Ver.14 (2025-05-18) with SimpleSAMLphp Ver.2.4.1,
  PHP 8.1.2-1ubuntu2.19+MySQL 8.0.40-0ubuntu0.22.04.1+Chrome (136.0.7103.114) on mac,
  by Masayuki Nii <nii@msyk.net>, OK

## Test Procedure

The test application(https://github.com/INTER-Mediator/IMTesting_SAML) is deployed to our server. 

- Open the web app menu page(https://demo.inter-mediator.com/IMTesting_SAML).
- Here is the starting point of following tests.

Set the SAML is active ($isSAML = true;), but the built-in auth is inactive ($samlWithBuiltInAuth = false;).

- Open the "isSaml = true, samlWithBuiltInAuth = false" page. 
- Check to show the IdP's login page not the built-in login page.
- Try the wrong account, and check not to log in and repeatedly show the login panel.
- Try the valid built-in account, and check not to log in.
- Try the valid SAML account, and check to log in correctly.

Set the SAML is active ($isSAML = true;), but the built-in auth is also active ($samlWithBuiltInAuth = true;).

- Open the web app menu page
- Open the "isSaml = true, samlWithBuiltInAuth = true" page.
- Check to show the built-in login page with the SAML Auth button.
- Try the wrong account on built-in login panel, and check not to log in and repeatedly show the login panel.
- Try the valid built-in account on built-in login panel, and check to log in correctly.
- Try the valid SAML account on built-in login panel, and check not to log in.
- Push the SAML Auth button, and check to show the IdP's login panel.
- After that, try the valid SAML account, and check to log in correctly.
- Try the valid built-in account on the IdP's login panel, and check not to log in.

Set the SAML is active($isSAML = true;), and limited users can log in with adding "user=>['user1','user01']" to the definition file.

- Open the web app menu page
- Open the "isSaml = true, samlWithBuiltInAuth = true, user=user01 or mig2" page.
- Push the SAML Auth button, and check to show the IdP's login panel.
- After that, try the invalid SAML account (ex. user02), and check not to log in.
- Try the valid SAML account (ex. user01), and check to log in correctly.

Set the SAML is inactive ($isSAML = false;), and the built-in auth is inactive ($samlWithBuiltInAuth = false;).

- Open the web app menu page
- Open the "isSaml = false, samlWithBuiltInAuth = false" page.
- Check to show the built-in login page not to IdP's login page.
- Try to the wrong account, and check not to log in and repeatedly show the login panel.
- Try to the valid built-in account, and check to log in correctly.
- Try to the valid SAML account, and check not to log in.

## Past Test Record

The format of below is: [commit code from git log], [Version from composer.json], [Checker name], [Result]

### Before developing "IMTesting_SAML" app

- commit 0e0a34d2c7354369e17bcce7bfb8bebd4b7eaa7b (Fri Jul 26 14:14:15 2024 +0900),
  INTER-Mediator Ver.13(2024-07-26) with SimpleSAMLphp Ver.2.2.2,
  by Masayuki Nii(2024-07-26 15:00), OK
  (The test procedure for limited user was added from here testing.)

- commit 1f0eca34f4e4e859a5533cf3a7c9a3306f0903f0 (Wed Jun 19 12:19:35 2024 +0900),
  INTER-Mediator Ver.13(2024-04-26) with SimpleSAMLphp Ver.2.2.2,
  by Masayuki Nii(2024-06-22 11:00), OK

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

