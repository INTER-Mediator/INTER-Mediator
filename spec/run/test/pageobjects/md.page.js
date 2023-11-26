const IMPage = require('./im.page')

/**
 * sub page containing specific selectors and methods for a specific page
 */
module.exports = class MasterDetailPage extends IMPage {
  get masterButtonMoveToDetail() {
    return $$('.IM_Button_Master')
  }

  get firstMasterButtonMoveToDetail() {
    return $('.IM_Button_Master')
  }

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

  get detailButtonMoveToMaster() {
    return $('.IM_Button_BackNavi')
  }

  get detailFieldPostalCode() {
    return $('._im_test-detail_postal_code')
  }

  get detailFieldPref() {
    return $('._im_test-detail_pref')
  }

  get detailFieldCity() {
    return $('._im_test-detail_city')
  }

  get detailFieldTown() {
    return $('._im_test-detail_town')
  }

  get masterTable() {
    return $('#master-table')
  }

  get detailTable() {
    return $('#detail-table')
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

  async getDetailTableStyleDisplay() {
    const element = await this.detailTable
    const value = await element.getCSSProperty('display')
    return value.parsed ? value.parsed.string : ""
  }
}

// module.exports = new FormPage()
