const searchPage = require('../pageobjects/SearchPage/searching_mysql.page')
const searchTest = require('./search_page_tests/simplesearch')
const complexTest = require('./search_page_tests/complexsearch')

describe('Searching Page with MySQL', () => {
  it('can open with the valid title.', async () => {
    await searchPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Search Page/MySQL")
  })
  searchTest(searchPage)
  it('can reopen with the valid title.', async () => {
    await searchPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Search Page/MySQL")
  })
  complexTest(searchPage)
})
