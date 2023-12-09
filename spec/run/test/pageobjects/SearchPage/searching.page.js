const IMPage = require('../im.page')

/**
 * sub page containing specific selectors and methods for a specific page
 */
module.exports = class SearchPage extends IMPage {
  get masterFieldPostalCode() {
    return $$('._im_test-master_postal_code')
  }

  get masterFieldPref() {
    return $$('._im_test-master_pref')
  }

  get masterFieldCity() {
    return $$('._im_test-master_city')
  }

  get masterFieldTown() {
    return $$('._im_test-master_town')
  }

  get masterTable() {
    return $('#master-table')
  }

  get searchPostalCode() {
    return $('#_im_test_box_pcode')
  }

  get searchTown() {
    return $('#_im_test_box_town')
  }

  get searchCity() {
    return $('#_im_test_box_city')
  }

  get searchAll() {
    return $('#_im_test_box_all')
  }

  get button1() {
    return $('#_im_test_button1')
  }

  get button2() {
    return $('#_im_test_button2')
  }

  get button3() {
    return $('#_im_test_button3')
  }

  get button4() {
    return $('#_im_test_button4')
  }

  get button5() {
    return $('#_im_test_button5')
  }

  get limitPoupu() {
    return $('#_im_test_limit')
  }

  get searchButton() {
    return $('#_im_test_button_search')
  }

  get sortAsc() {
    return $('._im_test_asc_sort')
  }

  get sortDesc() {
    return $('._im_test_desc_sort')
  }

  async getNavigatorStyleDisplay() {
    const element = await this.navigator
    const value = await element.getCSSProperty('display')
    return value.parsed ? value.parsed.string : ""
  }

  async getMasterTableStyleDisplay() {
    const element = await this.masterTable
    const value = await element.getCSSProperty('display')
    return value.parsed ? value.parsed.string : ""
  }
}

// module.exports = new FormPage()
