const MasterDetailPage = require('./md.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class MasterDetailPageMySQL extends MasterDetailPage {

  open() {
    return super.open('samples/E2E-Test/MasterDetailPage/master_separate_SQLite.html');
  }
}

module.exports = new MasterDetailPageMySQL();
