const CalcLookupPage = require('./page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class CalcLookupPageSQLite extends CalcLookupPage {

  open() {
    return super.open('samples/E2E-Test/CalcLookupPage/Calc_Lookup_SQLite.html');
  }
}

module.exports = new CalcLookupPageSQLite();
