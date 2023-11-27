const MasterDetailPage = require('./md.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class MasterDetailPagePostgreSQL extends MasterDetailPage {

  open() {
    return super.open('samples/E2E-Test/master_detail_PostgreSQL.html');
  }
}

module.exports = new MasterDetailPagePostgreSQL();
