const EditingPage = require('./editing.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class FormPageSQLite extends EditingPage {

  open() {
    return super.open('samples/E2E-Test/EditingPage/Editing_SQLite.html');
  }

  async reopen() {
    const id = await this.fieldId.getText()
    return super.open(`samples/E2E-Test/EditingPage/Editing_SQLite.html?id=${id}`);
  }
}

module.exports = new FormPageSQLite();
