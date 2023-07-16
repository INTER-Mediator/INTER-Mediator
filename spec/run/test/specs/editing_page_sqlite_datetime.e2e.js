const EditingPage = require('../pageobjects/editing_page_sqlite.page');

const waiting = 500

let pageTitle = "INTER-Mediator - Sample - Editing/SQLite"

let initDateTime, initTime, zeroDateTime
if (process.platform === 'darwin') {
  initDateTime = "2000-12-31 15:00:00" // For Asia/Tokyo server
  initTime = "15:00:00" // For Asia/Tokyo server
  zeroDateTime = "1969-12-31 15:00:00"
} else {
  initDateTime = "2001-01-01 00:00:00" // For UCT server
  initTime = "00:00:00" // For UCT server
  zeroDateTime = "1970-01-01 00:00:00"
}

describe('Editing Page Date/Time Fields', () => {
  it('can open with the valid title.', async () => {
    await EditingPage.open()
    await expect(browser).toHaveTitle(pageTitle)
  })
  it('has the INTER-Mediator\'s navigation.', async () => {
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
    await browser.pause(waiting)
    await EditingPage.navigatorInsertButton.click()
    await EditingPage.navigatorInsertButton.waitForClickable()
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await EditingPage.reopen()
  })
  it('can edit the text field of datetime field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldDt1Textfield).toExist()
    await expect(EditingPage.fieldDt1Textfield).toHaveValue(initDateTime) // Checking initial value

    const value = new Date().toISOString().substring(0, 19).replace("T", " ")
    await EditingPage.fieldDt1Textfield.setValue(value) // Set a value to the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDt1Textfield).toHaveValue(String(value))

    await EditingPage.fieldDt1Textfield.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDt1Textfield).toHaveValue(zeroDateTime)
  })
  it('can edit the text field of nullable datetime field.', async () => {
    await expect(EditingPage.fieldDt2Textfield).toExist()
    await expect(EditingPage.fieldDt2Textfield).toHaveValue("") // Checking initial value

    const value = new Date().toISOString().substring(0, 19).replace("T", " ")
    await EditingPage.fieldDt2Textfield.setValue(value) // Set a value to the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDt2Textfield).toHaveValue(String(value))

    await EditingPage.fieldDt2Textfield.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDt2Textfield).toHaveValue("")
  })
  it('can edit the text field of date field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldDate1Textfield).toExist()
    await expect(EditingPage.fieldDate1Textfield).toHaveValue("2001-01-01") // Checking initial value

    const value = new Date().toISOString().substring(0, 10)
    await EditingPage.fieldDate1Textfield.setValue(value) // Set a value to the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDate1Textfield).toHaveValue(String(value))

    await EditingPage.fieldDate1Textfield.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDate1Textfield).toHaveValue("1970-01-01")
  })
  it('can edit the text field of nullable date field.', async () => {
    await expect(EditingPage.fieldDate2Textfield).toExist()
    await expect(EditingPage.fieldDate2Textfield).toHaveValue("") // Checking initial value

    const value = new Date().toISOString().substring(0, 10)
    await EditingPage.fieldDate2Textfield.setValue(value) // Set a value to the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDate2Textfield).toHaveValue(String(value))

    await EditingPage.fieldDate2Textfield.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDate2Textfield).toHaveValue("")
  })
  it('can edit the text field of time field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldTime1Textfield).toExist()
    await expect(EditingPage.fieldTime1Textfield).toHaveValue(initTime) // Checking initial value

    const value = new Date().toISOString().substring(11, 19)
    await EditingPage.fieldTime1Textfield.setValue(value) // Set a value to the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldTime1Textfield).toHaveValue(String(value))

    await EditingPage.fieldTime1Textfield.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldTime1Textfield).toHaveValue(initTime)
  })
  it('can edit the text field of nullable time field.', async () => {
    await expect(EditingPage.fieldTime2Textfield).toExist()
    await expect(EditingPage.fieldTime2Textfield).toHaveValue("") // Checking initial value

    const value = new Date().toISOString().substring(11, 19)
    await EditingPage.fieldTime2Textfield.setValue(value) // Set a value to the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldTime2Textfield).toHaveValue(String(value))

    await EditingPage.fieldTime2Textfield.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldTime2Textfield).toHaveValue("")
  })
  it('can edit the text field of timestamp field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldTs1Textfield).toExist()
    await expect(EditingPage.fieldTs1Textfield).toHaveValue(initDateTime) // Checking initial value

    const value = new Date().toISOString().substring(0, 19).replace("T", " ")
    await EditingPage.fieldTs1Textfield.setValue(value) // Set a value to the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldTs1Textfield).toHaveValue(String(value))

    await EditingPage.fieldTs1Textfield.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldTs1Textfield).toHaveValue(zeroDateTime)
  })
  it('can edit the text field of nullable timestamp field.', async () => {
    await expect(EditingPage.fieldTs2Textfield).toExist()
    await expect(EditingPage.fieldTs2Textfield).toHaveValue("") // Checking initial value

    const value = new Date().toISOString().substring(0, 19).replace("T", " ")
    await EditingPage.fieldTs2Textfield.setValue(value) // Set a value to the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldTs2Textfield).toHaveValue(String(value))

    await EditingPage.fieldTs2Textfield.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldTs2Textfield).toHaveValue("")
  })
})


