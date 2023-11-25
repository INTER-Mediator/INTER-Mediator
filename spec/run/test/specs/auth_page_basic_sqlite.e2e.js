const AuthPage = require('../pageobjects/auth_page_sqlite.page');

describe('Auth Page with SQLite', () => {
  const basicTest = require('./auth_page_tests/basic')
  basicTest(AuthPage)
})

