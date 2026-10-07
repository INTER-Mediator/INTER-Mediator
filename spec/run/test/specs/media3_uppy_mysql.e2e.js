const FormPage = require('../pageobjects/AuthPage/auth_page_credential_basic_uppy_mysql.page');
const fileTest = require('./ui_tests/file_updown')
describe('File Upload and Download with Uppy and MySQL', () => {
  fileTest(FormPage, false, true)
})

