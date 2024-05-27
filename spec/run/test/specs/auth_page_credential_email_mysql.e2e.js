const AuthPage = require('../pageobjects/AuthPage/auth_page_credential_email_mysql.page');
const basicTest = require('./auth_page_tests/email')

describe('Auth Page with MySQL', () => {
  basicTest(AuthPage)
})


