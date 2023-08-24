const FormPage = require('./form.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class FormPageSQLite extends FormPage {

  open() {
    return super.open('samples/E2E-Test/form_SQLite.html');
  }
}

module.exports = new FormPageSQLite();
