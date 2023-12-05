const SearchPage = require('./searching.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class SearchPageMySQL extends SearchPage {

  open() {
    return super.open('samples/E2E-Test/SearchPage/searching_MySQL.html');
  }
}

module.exports = new SearchPageMySQL();
