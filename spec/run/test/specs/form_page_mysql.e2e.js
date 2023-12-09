const FormPage = require('../pageobjects/FormPage/form_mysql.page')
const naviTest = require('./form_page_tests/navigation')
const queryTest = require('./form_page_tests/simplequery')
const formTest = require('./form_page_tests/form')

describe('Form Page with MySQL', () => {
  it('can open with the valid title.', async () => {
    await FormPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Form Style/MySQL")
  });
  naviTest(FormPage)
  queryTest(FormPage)
  formTest(FormPage)
});
