const FormPage = require('../pageobjects/form_sqlite.page');
const naviTest = require('./form_page_tests/navigation')
const queryTest = require('./form_page_tests/simplequery')
const formTest = require('./form_page_tests/form')

describe('Form Page with SQLite', () => {
  it('can open with the valid title.', async () => {
    await FormPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Form Style/SQLite")
  });
  naviTest(FormPage)
  queryTest(FormPage)
  formTest(FormPage)
});
