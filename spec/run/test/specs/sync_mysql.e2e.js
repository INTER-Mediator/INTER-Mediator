const FormPage = require('../pageobjects/FormPage/form_mysql.page')
const syncTest = require('./form_page_tests/sync')

const waiting = 500

describe('Form Page with MySQL', () => {
  it('can open four tabs for the same url.', async () => {
    await FormPage.open()
    await browser.pause(waiting * 20) // Extra waiting
    await FormPage.setTitle('page1')
    await FormPage.open(true)
    await FormPage.setTitle('page2')
    await FormPage.open(true)
    await FormPage.setTitle('page3')
    await FormPage.open(true)
    await FormPage.setTitle('page2-2')
  })

  syncTest(FormPage,['page1','page2','page3','page2-2'])
});
