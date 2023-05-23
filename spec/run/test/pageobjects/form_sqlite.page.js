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

  get contactTable() {
    return $('._im_test-contact-table');
  }

  get contactTableInsertButton() {
    return this.contactTable.$('.IM_Button_Insert')
  }

  get rowContact() {
    return $$('._im_test-contact-row');
  }

  get rowContactDeleteButton() {
    return this.contactTable.$$('.IM_Button_Delete')
  }

  get rowContactCopyButton() {
    return this.contactTable.$$('.IM_Button_Copy')
  }

  get rowContactDateTime() {
    return $$('._im_test-contact-datetime');
  }

  get rowContactSummary() {
    return $$('._im_test-contact-summary');
  }

  get rowContactImportant() {
    return $$('._im_test-contact-important');
  }

  get rowContactWay() {
    return $$('._im_test-contact-way');
  }

  get rowContactKind() {
    return $$('._im_test-contact-kind');
  }

  get rowContactDescription() {
    return $$('._im_test-contact-description');
  }

  open() {
    return super.open('samples/Sample_form/form_SQLite.html');
  }
}

module.exports = new FormPagePostgreSQL();
