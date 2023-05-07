const IMPage = require('./im.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class SamplesPage extends IMPage {
  /**
   * overwrite specific options to adapt it to page object
   */
  open() {
    return super.open('samples/index.html');
  }
}

module.exports = new SamplesPage();
