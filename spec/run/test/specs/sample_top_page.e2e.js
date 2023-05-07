const TopPage = require('../pageobjects/sample_top.page');

describe('Sample Top Page', () => {
  it('can open with the valid title', async () => {
    await TopPage.open();
    await expect(browser).toHaveTitle('INTER-Mediator - Samples')
  });
});
