const AuthPage = require('../pageobjects/AuthPage/auth_page_credential_basic_sqlite.page');
const fileTest = require('./ui_tests/file_updown')

describe('File Upload and Download with SQLite', () => {
  fileTest(AuthPage,false)
})


