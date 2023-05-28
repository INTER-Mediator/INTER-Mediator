const EditingPage = require('./editing.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class FormPageMySQL extends EditingPage {

  open() {
    return super.open('samples/E2E-Test/Editing_MySQL.html');
  }
}

module.exports = new FormPageMySQL();
