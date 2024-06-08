const IMPage = require('../im.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
module.exports = class AuthPage extends IMPage {
  get logoutLink() {
    return $("#logout_link")
  }

  get itemInsertButton() {
    return $('.IM_Button_Insert')
  }

  get itemDeleteButton() {
    return $$('.IM_Button_Delete')
  }

  get fieldsItemWidget() {
    return $$('[data-im-widget="jquery_fileupload"]')
  }

  get fieldsItemUploading() {
    return $$("._im_test-file_comp")
  }

  get fieldsItemPic() {
    return $$("._im_test-file_show")
  }

  get fieldsUsername() {
    return $$("._im_test-username")
  }

}

// module.exports = new FormPage();
