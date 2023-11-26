const FormPage = require('../pageobjects/form_postgresql.page');
const naviTest = require('./form_page_tests/navigation')
const queryTest = require('./form_page_tests/simplequery')
const formTest = require('./form_page_tests/form')

describe('Form Page with PostgreSQL', () => {
  it('can open with the valid title.', async () => {
    await FormPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Form Style/PostgreSQL")
  });
  naviTest(FormPage)
  queryTest(FormPage)
  formTest(FormPage)
});
