const AuthPage = require('../pageobjects/auth_page_postgresql.page');

describe('Auth Page with PostgreSQL', () => {
  const basicTest = require('./auth_page_tests/basic')
  basicTest(AuthPage)
})


