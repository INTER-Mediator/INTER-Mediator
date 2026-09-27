const AuthPageUser = require('../pageobjects/AuthPage/auth_page_credential_email_sqlite.page');
const fileTest = require('./ui_tests/file_updown')

describe('File Upload and Download with jQuery-File-Upload and SQLite', () => {
  fileTest(AuthPageUser,true)
})


