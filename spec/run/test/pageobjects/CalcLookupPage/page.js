const IMPage = require('../im.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
module.exports = class CalcLookupPage extends IMPage {
  get fieldInvoiceId() {
    return $("._im_test-invoice-id")
  }

  get fieldIssued() {
    return $("._im_test-invoice-issued")
  }

  get fieldIssuedFormatted() {
    return $("._im_test-invoice-issued-formatted")
  }

  get fieldTitle() {
    return $("._im_test-invoice-title")
  }

  get fieldsItemProductId() {
    return $$("._im_test-item-product_id")
  }

  get fieldsProductName() {
    return $$("._im_test-product-name")
  }

  get popupProductId() {
    return $$("._im_test-product-id")
  }

  get fieldsQty() {
    return $$("._im_test-item-qty")
  }

  get fieldsItemProductName() {
    return $$("._im_test-item-puroduct_name")
  }

  get fieldsItemUnitprice() {
    return $$("._im_test-item-unitprice")
  }

  get fieldsItemNetPrice() {
    return $$("._im_test-item-net_price")
  }

  get fieldsItemTaxPrice() {
    return $$("._im_test-item-tax_price")
  }

  get fieldsItemAmountCalc() {
    return $$("._im_test-item-amount_calc")
  }

  get fieldTaxRate() {
    return $("._im_test-taxRate")
  }

  get fieldTotalCalc() {
    return $("._im_test-invoice-total_calc")
  }

  get itemInsertButton() {
    return $('.IM_Button_Insert')
  }

  get itemDeleteButton() {
    return $$('.IM_Button_Delete')
  }

  get itemCopyButton() {
    return $$('.IM_Button_Copy')
  }

}

// module.exports = new FormPage();
