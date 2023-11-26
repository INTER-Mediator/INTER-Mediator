const FormPage = require('../pageobjects/form_mysql.page')
const naviTest = require('./form_page_tests/navigation')
const queryTest = require('./form_page_tests/simplequery')
const formTest = require('./form_page_tests/form')

const mdPage = require('../pageobjects/md_mysql.page')
const mdTest = require('./master_detail_page_tests/navigation')

const dualPage = require('../pageobjects/dualpanes_mysql.page')
const dualTest = require('./master_detail_page_tests/dualpanes')

// describe('Form Page with MySQL', () => {
//   it('can open with the valid title.', async () => {
//     await FormPage.open()
//     await expect(browser).toHaveTitle("INTER-Mediator - Sample - Form Style/MySQL")
//   })
//   naviTest(FormPage)
//   queryTest(FormPage)
//   formTest(FormPage)
// });

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
