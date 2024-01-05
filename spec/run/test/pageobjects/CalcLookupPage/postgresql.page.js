const CalcLookupPage = require('./page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class CalcLookupPagePostgreSQL extends CalcLookupPage {

  open() {
    return super.open('samples/E2E-Test/CalcLookupPage/Calc_Lookup_PostgreSQL.html');
  }
}

module.exports = new CalcLookupPagePostgreSQL();
