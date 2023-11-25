const AuthPage = require('../pageobjects/auth_page_mysql.page');

describe('Auth Page with MySQL', () => {
  const basicTest = require('./auth_page_tests/basic')
  basicTest(AuthPage)

})


