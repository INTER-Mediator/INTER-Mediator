const AuthPage = require('./auth.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class AuthPagePostgreSQL extends AuthPage {

  open() {
    return super.open('samples/E2E-Test/AuthPage/Auth_credential_email_PostgreSQL.html');
  }
}

module.exports = new AuthPagePostgreSQL();
