const FormPage = require('../pageobjects/form_mysql.page');

let pageTitle
if (/*process.platform === 'darwin'*/ false) {
  pageTitle = "NTER-Mediator - サンプル - フォーム形式/MySQL"
} else {
  pageTitle = "INTER-Mediator - Sample - Form Style/MySQL"
}

describe('Form Page with MySQL', () => {
  it('can open with the valid title.', async () => {
    await FormPage.open()
    await expect(browser).toHaveTitle(pageTitle)
  });
  const naviTest = require('./form_page_tests/navigation')
  naviTest(FormPage)
  const queryTest = require('./form_page_tests/simplequery')
  queryTest(FormPage)
  const formTest = require('./form_page_tests/form')
  formTest(FormPage)
});
