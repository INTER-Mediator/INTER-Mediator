const AuthPage = require('./auth.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class AuthPageSQLite extends AuthPage {

  open() {
    return super.open('samples/E2E-Test/AuthPage/Auth_credential_usergroup_SQLite.html');
  }
}

module.exports = new AuthPageSQLite();
