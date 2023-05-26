const EditingPage = require('./editing.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class FormPagePostgreSQL extends EditingPage {

  open() {
    return super.open('samples/E2E-Test/Editing_SQLite.html');
  }
}

module.exports = new FormPagePostgreSQL();
