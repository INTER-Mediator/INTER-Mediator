const AuthPage = require('./auth.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class AuthPageMySQL extends AuthPage {

  open() {
    return super.open('samples/E2E-Test/AuthPage/Auth_session-storage_MySQL.html');
  }
}

module.exports = new AuthPageMySQL();
