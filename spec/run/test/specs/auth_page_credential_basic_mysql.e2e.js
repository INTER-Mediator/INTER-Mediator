const AuthPage = require('../pageobjects/AuthPage/auth_page_credential_basic_mysql.page');
const AuthPageUppy = require('../pageobjects/AuthPage/auth_page_credential_basic_uppy_mysql.page');
const basicTest = require('./auth_page_tests/basic')

describe('Auth Page with MySQL', () => {
  basicTest(AuthPage)
})

describe('Auth Page with Uppy and MySQL', () => {
  basicTest(AuthPageUppy)
})


