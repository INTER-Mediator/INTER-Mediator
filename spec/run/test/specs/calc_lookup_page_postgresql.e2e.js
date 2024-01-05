const CalcLookupPage = require('../pageobjects/CalcLookupPage/postgresql.page');

const calcTest = require('./calc_lookup_page_tests/calc')

describe('Calclation and Lookup Page with MySQL', () => {
  calcTest(CalcLookupPage)
})


