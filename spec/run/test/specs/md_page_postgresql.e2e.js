const mdPage = require("../pageobjects/MasterDetailPage/md_postgresql.page");
const mdTest = require("./master_detail_page_tests/navigation");
const dualPage = require("../pageobjects/MasterDetailPage/dualpanes_postgresql.page");
const dualTest = require("./master_detail_page_tests/dualpanes");
const separatePage = require("../pageobjects/MasterDetailPage/separate_postgresql.page");
const separateTest = require("./master_detail_page_tests/separate");

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

describe('Separated Master-Detail Page with MySQL', () => {
  it('can open with the valid title.', async () => {
    await separatePage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Separate Master Page/PostgreSQL")
  })
  separateTest(separatePage)
})
