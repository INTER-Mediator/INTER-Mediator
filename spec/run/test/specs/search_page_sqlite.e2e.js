const searchPage = require('../pageobjects/SearchPage/searching_sqlite.page')
const searchTest = require('./search_page_tests/simplesearch')

describe('Searching Page with MySQL', () => {
  it('can open with the valid title.', async () => {
    await searchPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Search Page/SQLite")
  })
  searchTest(searchPage)
})
