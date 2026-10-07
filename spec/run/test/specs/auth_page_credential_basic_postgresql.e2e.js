const AuthPage = require('../pageobjects/AuthPage/auth_page_credential_basic_postgresql.page');
const AuthPageUppy = require('../pageobjects/AuthPage/auth_page_credential_basic_uppy_postgresql.page');

const basicTest = require('./auth_page_tests/basic')

describe('Auth Page with PostgreSQL', () => {
  basicTest(AuthPage)
})

describe('Auth Page with Uppy and PostgreSQL', () => {
  basicTest(AuthPageUppy)
})
