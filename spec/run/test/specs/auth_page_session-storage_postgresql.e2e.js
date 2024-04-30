const AuthPage = require('../pageobjects/AuthPage/auth_page_session-storage_postgresql.page');

const basicTest = require('./auth_page_tests/basic')

describe('Auth Page with PostgreSQL', () => {
  basicTest(AuthPage)
})


