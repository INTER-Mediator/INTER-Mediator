const SearchPagePage = require('./searching.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class SearchPagePostgreSQL extends SearchPagePage {

  open() {
    return super.open('samples/E2E-Test/SearchPage/searching_PostgreSQL.html');
  }
}

module.exports = new SearchPagePostgreSQL();
