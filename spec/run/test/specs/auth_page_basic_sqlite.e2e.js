const AuthPage = require('../pageobjects/AuthPage/auth_page_sqlite.page');

const basicTest = require('./auth_page_tests/basic')

describe('Auth Page with SQLite', () => {
  basicTest(AuthPage)
})

