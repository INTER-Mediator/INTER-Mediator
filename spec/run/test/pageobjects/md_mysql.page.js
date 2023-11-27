const MasterDetailPage = require('./md.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class MasterDetailPageMySQL extends MasterDetailPage {

  open() {
    return super.open('samples/E2E-Test/master_detail_MySQL.html');
  }
}

module.exports = new MasterDetailPageMySQL();
