const EditingPage = require("../../pageobjects/EditingPage/editing_page_mysql.page");
module.exports = (EditingPage) => {
  describe("Date/Time Field", function () {
    const waiting = 500

    let initDateTime, initTime, zeroDateTime
    if (process.platform === 'darwin') {
      initDateTime = "2000-12-31 15:00:00" // For Asia/Tokyo server
      initTime = "15:00:00" // For Asia/Tokyo server
      zeroDateTime = "0999-12-31 14:41:01"
    } else {
      initDateTime = "2001-01-01 00:00:00" // For UCT server
      initTime = "00:00:00" // For UCT server
      zeroDateTime = "1000-01-01 00:00:00"
    }

    /*
    Summary: Text field with non-null datetime field
    Condition: The editing test page with new created record opens.
    Check-with: Exist the text field of the field dt1(datetime, not null).
    Check-with: The dt1 text field has the value with the initial value.
    Operation: Set the current date and time value to dt1 text field, and reload the page with "Update" button.
    Check-with: The dt1 text field has the value by set on the previous operation.
     */
    it('1-can edit the text field of datetime field which is NOT NULL.', async () => {
      await EditingPage.reopen()

      await expect(EditingPage.fieldDt1Textfield).toExist()
      await expect(EditingPage.fieldDt1Textfield).toHaveValue(initDateTime) // Checking initial value
      const value = new Date().toISOString().substring(0, 19).replace("T", " ")
      await EditingPage.fieldDt1Textfield.setValue(value) // Set a value to the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDt1Textfield).toHaveValue(String(value))
      // This field can't clear
    })
    /*
    Summary: Text field with nullable datetime field
    Condition: The editing test page with new created record opens.
    Check-with: Exist the text field of the field dt2(datetime, nullable).
    Check-with: The dt2 text field has the value "".
    Operation: Set the current date and time value to dt2 text field, and reload the page with "Update" button.
    Check-with: The dt2 text field has the value by set on the previous operation.
    Operation: Set the value "" to dt2 text field (i.e. clear it), and reload the page with "Update" button.
    Check-with: The dt2 text field has the value "".
     */
    it('2-can edit the text field of nullable datetime field.', async () => {
      await EditingPage.fieldDt2Textfield.waitForExist()
      await expect(EditingPage.fieldDt2Textfield).toExist()
      await expect(EditingPage.fieldDt2Textfield).toHaveValue("") // Checking initial value
      const value = new Date().toISOString().substring(0, 19).replace("T", " ")
      await EditingPage.fieldDt2Textfield.setValue(value) // Set a value to the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDt2Textfield).toHaveValue(String(value))
      await EditingPage.fieldDt2Textfield.setValue("") // clear the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDt2Textfield).toHaveValue("")
    })
    /*
    Summary: Text field with non-null date field
    Condition: The editing test page with new created record opens.
    Check-with: Exist the text field of the field date1(date, not null).
    Check-with: The date1 text field has the value with the initial value.
    Operation: Set the current date value to date1 text field, and reload the page with "Update" button.
    Check-with: The date1 text field has the value by set on the previous operation.
     */
    it('3-can edit the text field of date field which is NOT NULL.', async () => {
      await expect(EditingPage.fieldDate1Textfield).toExist()
      await expect(EditingPage.fieldDate1Textfield).toHaveValue("2001-01-01") // Checking initial value
      const value = new Date().toISOString().substring(0, 10)
      // await EditingPage.fieldDate1Textfield.clearValue()
      // await browser.pause(waiting)
      await EditingPage.fieldDate1Textfield.setValue(value) // Set a value to the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDate1Textfield).toHaveValue(String(value))
      // This field can't clear
    })
    /*
    Summary: Text field with nullable date field
    Condition: The editing test page with new created record opens.
    Check-with: Exist the text field of the field date2(date, nullable).
    Check-with: The date2 text field has the value "".
    Operation: Set the current date value to date2 text field, and reload the page with "Update" button.
    Check-with: The date2 text field has the value by set on the previous operation.
    Operation: Set the value "" to date2 text field (i.e. clear it), and reload the page with "Update" button.
    Check-with: The date2 text field has the value "".
     */
    it('4-can edit the text field of nullable date field.', async () => {
      await expect(EditingPage.fieldDate2Textfield).toExist()
      await expect(EditingPage.fieldDate2Textfield).toHaveValue("") // Checking initial value
      const value = new Date().toISOString().substring(0, 10)
      await EditingPage.fieldDate2Textfield.setValue(value) // Set a value to the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDate2Textfield).toHaveValue(String(value))
      await EditingPage.fieldDate2Textfield.setValue("") // Clear the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDate2Textfield).toHaveValue("")
    })
    /*
    Summary: Text field with non-null time field
    Condition: The editing test page with new created record opens.
    Check-with: Exist the text field of the field time1(time, not null).
    Check-with: The time1 text field has the value with the initial value.
    Operation: Set the current time value to time1 text field, and reload the page with "Update" button.
    Check-with: The time1 text field has the value by set on the previous operation.
     */
    it('5-can edit the text field of time field which is NOT NULL.', async () => {
      await expect(EditingPage.fieldTime1Textfield).toExist()
      await expect(EditingPage.fieldTime1Textfield).toHaveValue(initTime) // Checking initial value
      const value = new Date().toISOString().substring(11, 19)
      await EditingPage.fieldTime1Textfield.setValue(value) // Set a value to the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldTime1Textfield).toHaveValue(String(value))
      // This field can't clear
    })
    /*
    Summary: Text field with nullable time field
    Condition: The editing test page with new created record opens.
    Check-with: Exist the text field of the field time2(time, nullable).
    Check-with: The time2 text field has the value "".
    Operation: Set the current time value to time2 text field, and reload the page with "Update" button.
    Check-with: The time2 text field has the value by set on the previous operation.
    Operation: Set the value "" to time2 text field (i.e. clear it), and reload the page with "Update" button.
    Check-with: The time2 text field has the value "".
     */
    it('6-can edit the text field of nullable time field.', async () => {
      await expect(EditingPage.fieldTime2Textfield).toExist()
      await expect(EditingPage.fieldTime2Textfield).toHaveValue("") // Checking initial value
      const value = new Date().toISOString().substring(11, 19)
      await EditingPage.fieldTime2Textfield.setValue(value) // Set a value to the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldTime2Textfield).toHaveValue(String(value))
      await EditingPage.fieldTime2Textfield.setValue("") // Clear the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldTime2Textfield).toHaveValue("")
    })

    // The ts1, ts2 fields are timestamp type and it's same as dt1, dt2.

  })

}
