const mdPage = require("../pageobjects/MasterDetailPage/md_sqlite.page");
const mdTest = require("./master_detail_page_tests/navigation");
const dualPage = require("../pageobjects/MasterDetailPage/dualpanes_sqlite.page");
const dualTest = require("./master_detail_page_tests/dualpanes");
const separatePage = require("../pageobjects/MasterDetailPage/separate_sqlite.page");
const separateTest = require("./master_detail_page_tests/separate");

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

describe('Separated Master-Detail Page with MySQL', () => {
  it('can open with the valid title.', async () => {
    await separatePage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Separate Master Page/SQLite")
  })
  separateTest(separatePage)
})
