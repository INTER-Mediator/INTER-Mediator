const IMPage = require('./im.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
module.exports = class AuthPage extends IMPage {
  get logoutLink() {
    return $("#logout_link")
  }
}

// module.exports = new FormPage();
