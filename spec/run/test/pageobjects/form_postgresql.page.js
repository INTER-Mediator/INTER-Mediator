const FormPage = require('./form.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class FormPagePostgreSQL extends FormPage {
  open() {
    return super.open('samples/E2E-Test/form_PostgreSQL.html');
  }
}

module.exports = new FormPagePostgreSQL();
