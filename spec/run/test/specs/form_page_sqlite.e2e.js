const FormPage = require('../pageobjects/form_sqlite.page');
const naviTest = require('./form_page_tests/navigation')
const queryTest = require('./form_page_tests/simplequery')
const formTest = require('./form_page_tests/form')
const mdPage = require("../pageobjects/md_sqlite.page");
const mdTest = require("./master_detail_page_tests/navigation");
const dualPage = require("../pageobjects/dualpanes_sqlite.page");
const dualTest = require("./master_detail_page_tests/dualpanes");

describe('Form Page with SQLite', () => {
  it('can open with the valid title.', async () => {
    await FormPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Form Style/SQLite")
  });
  naviTest(FormPage)
  queryTest(FormPage)
  formTest(FormPage)
});
describe('Master-Detail Page with MySQL', () => {
  it('can open with the valid title.', async () => {
    await mdPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Master-Detail/SQLite")
  })
  mdTest(mdPage)
})

describe('Dual Panes Master-Detail Page with MySQL', () => {
  it('can open with the valid title.', async () => {
    await dualPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Dual-Panes/SQLite")
  })
  dualTest(mdPage)
})
