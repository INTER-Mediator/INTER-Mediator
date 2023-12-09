const SearchPagePage = require('./searching.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class SearchPagePageSQLite extends SearchPagePage {

  open() {
    return super.open('samples/E2E-Test/SearchPage/searching_SQLite.html');
  }
}

module.exports = new SearchPagePageSQLite();
