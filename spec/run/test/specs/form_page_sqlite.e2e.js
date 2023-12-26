const FormPage = require('../pageobjects/FormPage/form_sqlite.page');
const naviTest = require('./form_page_tests/navigation')
const queryTest = require('./form_page_tests/simplequery')
const formTest = require('./form_page_tests/form')

describe('Form Page with SQLite', () => {
  let windowTitle = "INTER-Mediator - Sample - Form Style/SQLite"
  if (process.platform === 'darwin') {
    windowTitle = "INTER-Mediator - サンプル - フォーム形式/SQLite"
  }
  it('can open with the valid title.', async () => {
    await FormPage.open()
    await expect(browser).toHaveTitle(windowTitle)
  });
  naviTest(FormPage)
  queryTest(FormPage)
  formTest(FormPage)
});
