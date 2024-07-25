const AuthPage = require('../pageobjects/AuthPage/auth_page_credential_usergroup_postgresql.page');

const usergroupTest = require('./auth_page_tests/usergroup')

describe('Auth Page with PostgreSQL', () => {
  usergroupTest(AuthPage)
})


