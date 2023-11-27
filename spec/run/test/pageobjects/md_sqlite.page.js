const MasterDetailPage = require('./md.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class MasterDetailPageSQLite extends MasterDetailPage {

  open() {
    return super.open('samples/E2E-Test/master_detail_SQLite.html');
  }
}

module.exports = new MasterDetailPageSQLite();
