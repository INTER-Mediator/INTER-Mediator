const mdPage = require('../pageobjects/MasterDetailPage/md_mysql.page')
const mdTest = require('./master_detail_page_tests/navigation')
const dualPage = require('../pageobjects/MasterDetailPage/dualpanes_mysql.page')
const dualTest = require('./master_detail_page_tests/dualpanes')
const separatePage = require('../pageobjects/MasterDetailPage/separate_mysql.page')
const separateTest = require('./master_detail_page_tests/separate')

describe('Master-Detail Page with MySQL', () => {
  it('can open with the valid title.', async () => {
    await mdPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Master-Detail/MySQL")
  })
  mdTest(mdPage)
})

describe('Dual Panes Master-Detail Page with MySQL', () => {
  it('can open with the valid title.', async () => {
    await dualPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Dual-Panes/MySQL")
  })
  dualTest(mdPage)
})

describe('Separated Master-Detail Page with MySQL', () => {
  it('can open with the valid title.', async () => {
    await separatePage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Separate Master Page/MySQL")
  })
  separateTest(separatePage)
})
