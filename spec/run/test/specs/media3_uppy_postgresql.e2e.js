const FormPage
  = require('../pageobjects/AuthPage/auth_page_credential_basic_uppy_postgresql.page');

const fileTest = require('./ui_tests/file_updown')
const FormPage = require("../pageobjects/AuthPage/auth_page_credential_basic_uppy_mysql.page");
describe('File Upload and Download with MySQL', () => {
  fileTest(FormPage, false, true)
})

