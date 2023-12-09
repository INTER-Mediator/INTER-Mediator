const EditingPage = require('../pageobjects/EditingPage/editing_page_postgresql.page');

const integerTest = require('./editing_page_tests/integer')
const realTest = require('./editing_page_tests/real')
const booleanTest = require('./editing_page_tests/boolean')
const stringTest = require('./editing_page_tests/string')
const datetimeTest = require('./editing_page_tests/datetime')

describe('Editing Page with PostgreSQL', () => {
  /*
  Summary: Open the editing page
  Condition: None.
  Operation: Open the editing test page.
  Check-with: The page title is valid.
   */
  it('1.can open with the valid title.', async () => {
    await EditingPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Editing/PostgreSQL")
  })
  /*
  Summary: Pagination Control
  Condition: The editing test page opens.
  Check-with: Exist the INTER-Mediator's pagination bar.
  Check-with: Exist the "Update" button on the bar.
  Check-with: Exist the "Update" button on the bar.
  Check-with: Exist 4 navigating buttons on the bar.
  Check-with: Exist << buttons on the bar.
  Check-with: Exist < buttons on the bar.
  Check-with: Exist > buttons on the bar.
  Check-with: Exist >> buttons on the bar.
  Check-with: Exist the "Insert" button on the bar.
  Operation: Click the "Insert" button on the bar, and reopen the page with new record.
   */
  it('2.has the INTER-Mediator\'s navigation.', async () => {
    await expect(EditingPage.navigator).toExist()
    await expect(EditingPage.navigatorUpdateButton).toExist()
    await expect(EditingPage.navigatorInfo).toExist()
    await expect(EditingPage.navigatorMoveButtons).toBeElementsArrayOfSize(4)
    await expect(EditingPage.navigatorMoveButtonFirst).toExist()
    await expect(EditingPage.navigatorMoveButtonFirst).toHaveText('<<')
    await expect(EditingPage.navigatorMoveButtonPrevious).toExist()
    await expect(EditingPage.navigatorMoveButtonPrevious).toHaveText('<')
    await expect(EditingPage.navigatorMoveButtonNext).toExist()
    await expect(EditingPage.navigatorMoveButtonNext).toHaveText('>')
    await expect(EditingPage.navigatorMoveButtonLast).toExist()
    await expect(EditingPage.navigatorMoveButtonLast).toHaveText('>>')
    await expect(EditingPage.navigatorInsertButton).toExist()
    await EditingPage.navigatorInsertButton.waitForClickable()
    await EditingPage.navigatorInsertButton.click()
    await EditingPage.navigatorUpdateButton.waitForClickable()
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(500)
    await EditingPage.reopen()
  })
  integerTest(EditingPage)
  realTest(EditingPage)
  booleanTest(EditingPage)
  stringTest(EditingPage)
  datetimeTest(EditingPage)
})

