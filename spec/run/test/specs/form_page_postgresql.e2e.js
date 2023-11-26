const FormPage = require('../pageobjects/form_postgresql.page');
const naviTest = require('./form_page_tests/navigation')
const queryTest = require('./form_page_tests/simplequery')
const formTest = require('./form_page_tests/form')
const mdPage = require("../pageobjects/md_mysql.page");
const mdTest = require("./master_detail_page_tests/navigation");
const dualPage = require("../pageobjects/dualpanes_postgresql.page");
const dualTest = require("./master_detail_page_tests/dualpanes");

describe('Form Page with PostgreSQL', () => {
  it('can open with the valid title.', async () => {
    await FormPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Form Style/PostgreSQL")
  });
  naviTest(FormPage)
  queryTest(FormPage)
  formTest(FormPage)
});
describe('Master-Detail Page with PostgreSQL', () => {
  it('can open with the valid title.', async () => {
    await mdPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Master-Detail/PostgreSQL")
  })
  mdTest(mdPage)
})

describe('Dual Panes Master-Detail Page with PostgreSQL', () => {
  it('can open with the valid title.', async () => {
    await dualPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Dual-Panes/PostgreSQL")
  })
  dualTest(mdPage)
})
