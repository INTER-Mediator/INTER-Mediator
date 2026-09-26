const FormPage = require('../pageobjects/AuthPage/auth_page_credential_basic_uppy_sqlite.page');
const fileTest = require('./ui_tests/file_updown')
describe('File Upload and Download with Uppy and SQLite', () => {
  fileTest(FormPage, false, true)
})

