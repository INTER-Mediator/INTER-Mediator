const FormPage = require('./form.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class FormPageMySQL extends FormPage {

  open() {
    return super.open('samples/E2E-Test/form_MySQL.html');
  }
}

module.exports = new FormPageMySQL();
