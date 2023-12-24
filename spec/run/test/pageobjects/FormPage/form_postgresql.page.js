const FormPage = require('./form.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class FormPagePostgreSQL extends FormPage {
  open(isNewWindow = false) {
    return super.open('samples/E2E-Test/FormPage/form_PostgreSQL.html', isNewWindow);
  }
}

module.exports = new FormPagePostgreSQL();
