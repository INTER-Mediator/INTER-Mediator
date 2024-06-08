const AuthPage = require('../pageobjects/AuthPage/auth_page_credential_basic_mysql.page');
const AuthPageUser = require('../pageobjects/AuthPage/auth_page_credential_email_mysql.page');
const fileTest = require('./ui_tests/file_updown')

describe('File Upload and Download with MySQL', () => {
  fileTest(AuthPage, false)
  fileTest(AuthPageUser, true)
})


