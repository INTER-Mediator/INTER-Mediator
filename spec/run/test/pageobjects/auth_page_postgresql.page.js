const AuthPage = require('./auth.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class AuthPagePostgreSQL extends AuthPage {

  open() {
    return super.open('samples/E2E-Test/Auth_Basic_PostgreSQL.html');
  }
}

module.exports = new AuthPagePostgreSQL();
