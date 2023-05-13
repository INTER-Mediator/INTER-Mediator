const IMPage = require('./im.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class FormPagePostgreSQL extends IMPage {
  get fieldPersonId() {
    return $('._im_test-person-id');
  }

  get fieldPersonCategory() {
    return $('._im_test-person-category');
  }

  get fieldPersonCheck() {
    return $('._im_test-person-checking');
  }

  get fieldPersonName() {
    return $('._im_test-person-name');
  }

  get fieldPersonMail() {
    return $('._im_test-person-mail');
  }

  get fieldPersonLocations() {
    return $$('._im_test-person-location');
  }

  get fieldPersonMemo() {
    return $('._im_test-person-memo');
  }

  open() {
    return super.open('samples/Sample_form/form_PostgreSQL.html');
  }

}

module.exports = new FormPagePostgreSQL();
