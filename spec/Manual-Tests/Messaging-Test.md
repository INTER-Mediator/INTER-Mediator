# Messaging Test

by INTER-Mediator Directive Committee (https://inter-mediator.org)

INTER-Mediator can send email after database operations.
We have a test application to sending emails for smtp server, AWS SES SMTP, AWS SES API, Google API.

## Latest Test Record

The format of below is: [commit code from git log], [Version from composer.json], [Checker name], [Result]

- commit dbad739174b5ac9e38224532e47858582096d9e8 (Sat Jun 14 14:07:27 2025 +0900)
  INTER-Mediator Ver.14 (2025-05-18),
  PHP 8.4.7+MySQL Ver 8.4.5, by Masayuki Nii <nii@msyk.net>, OK

## Test Procedure

The test application(https://github.com/INTER-Mediator/IMTesting_Messaging) is deployed on the my own mac. 

- Open the web app menu page.
- Checking to send a mail on the testpage1.html with SMTP way.
- Checking to send a mail on the testpage2.html with AWS SES SMTP.
- Checking to send a mail on the testpage3.html with AWS SES API
- Checking to send a mail on the testpage4.html with Gmail API.

## Past Test Record

The format of below is: [commit code from git log], [Version from composer.json], [Checker name], [Result]

### Previous Test Records

TBD

