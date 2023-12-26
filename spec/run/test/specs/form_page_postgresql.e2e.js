const FormPage = require('../pageobjects/FormPage/form_postgresql.page');
const naviTest = require('./form_page_tests/navigation')
const queryTest = require('./form_page_tests/simplequery')
const formTest = require('./form_page_tests/form')

describe('Form Page with PostgreSQL', () => {
  let windowTitle = "INTER-Mediator - Sample - Form Style/PostgreSQL"
  if (process.platform === 'darwin') {
    windowTitle = "INTER-Mediator - サンプル - フォーム形式/PostgreSQL"
  }
  it('can open with the valid title.', async () => {
    await FormPage.open()
    await expect(browser).toHaveTitle(windowTitle)
  });
  naviTest(FormPage)
  queryTest(FormPage)
  formTest(FormPage)
});
