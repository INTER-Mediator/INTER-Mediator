const CalcLookupPage = require('./page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class CalcLookupPageMySQL extends CalcLookupPage {

  open() {
    return super.open('samples/E2E-Test/CalcLookupPage/Calc_Lookup_MySQL.html');
  }
}

module.exports = new CalcLookupPageMySQL();
