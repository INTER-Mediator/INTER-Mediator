const AuthPageUser = require('../pageobjects/AuthPage/auth_page_credential_email_sqlite.page');
const fileTest = require('./ui_tests/file_updown')

describe('File Upload and Download with SQLite', () => {
  fileTest(AuthPageUser,true)
})


