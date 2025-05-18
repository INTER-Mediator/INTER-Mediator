# MyNumberCard Test

by INTER-Mediator Directive Committee (https://inter-mediator.org)

INTER-Mediator supports MyNumberCard for authentication, but it can't test within the github action's ci environment.
So the MyNumberCard feature has to be tested manually.
We have a test environment within the demo server to test authentication.
After someone tests the SAML features, the result has to be recorded here.

## Latest Test Record

The format of below is: [commit code from git log], [Version from composer.json], [Checker name], [Result]

- 

## Test Procedure

The test application(https://github.com/INTER-Mediator/IMTesting_MyNumberCard) is deployed to our server. 

- Open the web app menu page(https://demo.inter-mediator.com/IMTesting_MyNumberCard).
- Here is the starting point of following tests.

### Authentication with MyNumberCard.

- Click the "chat.html" link.
- Login panel is shown.
- Click "マイナンバーカードで認証" button.
- The simulator of MyNumberCard authentication is shown.
- Click any user which can succeed to authentication.
- Show the chat.html page with generated username.
- Check to be able to post any message.

## Past Test Record

The format of below is: [commit code from git log], [Version from composer.json], [Checker name], [Result]


### Before developing "IMTesting_SAML" app
