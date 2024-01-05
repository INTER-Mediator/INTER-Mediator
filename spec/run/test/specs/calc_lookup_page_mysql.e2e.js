const CalcLookupPage = require('../pageobjects/CalcLookupPage/mysql.page');

const calcTest = require('./calc_lookup_page_tests/calc')

describe('Calclation and Lookup Page with MySQL', () => {
  calcTest(CalcLookupPage)
})


