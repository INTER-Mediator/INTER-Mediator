const EditingPage = require("../../pageobjects/EditingPage/editing_page_mysql.page");
module.exports = (EditingPage) => {
  describe("Integer Field", function () {
    const waiting = 500
    /*
    Summary: Text field with non-null integer field
    Condition: The editing test page with new created record opens.
    Check-with: Exist the text field of the field num1(integer, not null).
    Check-with: The num1 text field has the value "0".
    Operation: Set a random number to num1 text field, and reload the page with "Update" button.
    Check-with: The num1 text field has the value by set on the previous operation.
    Operation: Set the value "" to num1 text field (i.e. clear it), and reload the page with "Update" button.
    Check-with: The num1 text field has the value "0" by set on the previous operation.
     */
    it('1-can edit the text field of integer field which is NOT NULL.', async () => {
      await EditingPage.reopen()

      await expect(EditingPage.fieldNum1Textfield).toExist()
      await expect(EditingPage.fieldNum1Textfield).toHaveValue("0") // Checking initial value
      const value = Math.trunc(Math.random() * 10000000)
      await EditingPage.fieldNum1Textfield.setValue(String(value)) // Set a value to the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldNum1Textfield).toHaveValue(String(value))
      await EditingPage.fieldNum1Textfield.setValue("") // Clear the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldNum1Textfield).toHaveValue("0")
    })
    /*
    Summary: Text field with nullable integer field
    Condition: The editing test page with new created record opens.
    Check-with: Exist the text field of the field num2(integer, nullable).
    Check-with: The num2 text field has the value "".
    Operation: Set a random number to num2 text field, and reload the page with "Update" button.
    Check-with: The num2 text field has the value by set on the previous operation.
    Operation: Set the value "" to num2 text field (i.e. clear it), and reload the page with "Update" button.
    Check-with: The num2 text field has the value "" by set on the previous operation.
     */
    it('2-can edit the text field of nullable integer field.', async () => {
      await expect(EditingPage.fieldNum2Textfield).toExist()
      await expect(EditingPage.fieldNum2Textfield).toHaveValue("") // Checking initial value
      const value = Math.trunc(Math.random() * 10000000)
      await EditingPage.fieldNum2Textfield.setValue(value) // Set a value to the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldNum2Textfield).toHaveValue(String(value))
      await EditingPage.fieldNum2Textfield.setValue("") // Clear the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldNum2Textfield).toHaveValue("")
    })
    /*
    Summary: Checkbox with non-null integer field
    Condition: The editing test page with new created record opens.
    Check-with: The checkbox of the field num1(integer, not null) is not selected.
    Operation: Click the num1 checkbox and set to checked, and reload the page with "Update" button.
    Check-with: The checkbox of the field num1 is selected.
    Check-with: The num1 text field has the value 1 which the value attribute of the checkbox.
    Operation: Click the num1 checkbox and set not to checked, and reload the page with "Update" button.
    Check-with: The checkbox of the field num1 is not selected.
    Check-with: The num1 text field has the value 0.
     */
    it('3-can edit the checkbox of integer field which is NOT NULL.', async () => {
      await expect(EditingPage.fieldNum1Checkbox).toExist()
      await expect(EditingPage.fieldNum1Checkbox).not.toBeSelected() // Checking initial value
      await EditingPage.fieldNum1Checkbox.click() // ON
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldNum1Checkbox).toBeSelected()
      await expect(EditingPage.fieldNum1Textfield).toHaveValue("1")
      await EditingPage.fieldNum1Checkbox.click() // OFF
      await browser.pause(waiting)
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldNum1Checkbox).not.toBeSelected()
      await expect(EditingPage.fieldNum1Textfield).toHaveValue("0")
    })
    /*
    Summary: Checkbox with nullable integer field
    Condition: The editing test page with new created record opens.
    Check-with: The checkbox of the field num2(integer, nullable) is not selected.
    Operation: Click the num2 checkbox and set to checked, and reload the page with "Update" button.
    Check-with: The checkbox of the field num2 is selected.
    Check-with: The num2 text field has the value 1 which the value attribute of the checkbox.
    Operation: Click the num2 checkbox and set not to checked, and reload the page with "Update" button.
    Check-with: The checkbox of the field num2 is not selected.
    Check-with: The num2 text field has the value "".
     */
    it('4-can edit the checkbox of nullable integer field.', async () => {
      await expect(EditingPage.fieldNum2Checkbox).toExist()
      await expect(EditingPage.fieldNum2Checkbox).not.toBeSelected() // Checking initial value
      await EditingPage.fieldNum2Checkbox.click() // ON
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldNum2Checkbox).toBeSelected()
      await expect(EditingPage.fieldNum2Textfield).toHaveValue("1")
      await EditingPage.fieldNum2Checkbox.click() // OFF
      await browser.pause(waiting)
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldNum2Checkbox).not.toBeSelected()
      await expect(EditingPage.fieldNum2Textfield).toHaveValue("")
    })
    /*
    Summary: Radio buttons with non-null integer field
    Condition: The editing test page with new created record opens.
    Check-with: The first button of the field num1(integer, not null) is not selected.
    Check-with: The second button of the field num1 is not selected.
    Operation: Click the first button and select it.
    Check-with: The first button of the field num1 is selected.
    Check-with: The second button of the field num1 is not selected.
    Check-with: The num1 text field has the value 1 which the value attribute of the button.
    Operation: Click the second button and select it.
    Check-with: The first button of the field num1 is selected.
    Check-with: The second button of the field num1 is not selected.
    Check-with: The num1 text field has the value 2 which the value attribute of the button.
    Operation: Reload the page with "Update" button.
    Check-with: The first button of the field num1 is selected (same as before reload).
    Check-with: The second button of the field num1 is not selected (same as before reload).
    Check-with: The num1 text field has the value 2 which the value attribute of the button (same as before reload).
     */
    it('5-can edit the radio buttons of integer field which is NOT NULL.', async () => {
      const buttons = await EditingPage.fieldNum1Radio
      await expect(buttons[0]).toExist()
      await expect(buttons[1]).toExist()
      await expect(buttons[0]).not.toBeSelected() // Checking initial value
      await expect(buttons[1]).not.toBeSelected() // Checking initial value

      await buttons[0].click() // First button
      await expect(buttons[0]).toBeSelected() // Checking initial value
      await expect(buttons[1]).not.toBeSelected() // Checking initial value
      await expect(EditingPage.fieldNum1Textfield).toHaveValue("1")

      await buttons[1].click() // Second button
      await expect(buttons[0]).not.toBeSelected() // Checking initial value
      await expect(buttons[1]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldNum1Textfield).toHaveValue("2")

      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(buttons[0]).not.toBeSelected() // Checking initial value
      await expect(buttons[1]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldNum1Textfield).toHaveValue("2")
    })
    /*
    Summary: Radio buttons with nullable integer field
    Condition: The editing test page with new created record opens.
    Check-with: The first button of the field num2(integer, nullable) is not selected.
    Check-with: The second button of the field num2 is not selected.
    Operation: Click the first button and select it.
    Check-with: The first button of the field num2 is selected.
    Check-with: The second button of the field num2 is not selected.
    Check-with: The num2 text field has the value 1 which the value attribute of the button.
    Operation: Click the second button and select it.
    Check-with: The first button of the field num2 is selected.
    Check-with: The second button of the field num2 is not selected.
    Check-with: The num2 text field has the value 2 which the value attribute of the button.
    Operation: Reload the page with "Update" button.
    Check-with: The first button of the field num2 is selected (same as before reload).
    Check-with: The second button of the field num2 is not selected (same as before reload).
    Check-with: The num2 text field has the value 2 which the value attribute of the button (same as before reload).
     */
    it('6-can edit the radio buttons of nullable integer field.', async () => {
      const buttons = await EditingPage.fieldNum2Radio
      await expect(buttons[0]).toExist()
      await expect(buttons[1]).toExist()
      await expect(buttons[0]).not.toBeSelected() // Checking initial value
      await expect(buttons[1]).not.toBeSelected() // Checking initial value

      await buttons[0].click() // First button
      await expect(buttons[0]).toBeSelected() // Checking initial value
      await expect(buttons[1]).not.toBeSelected() // Checking initial value
      await expect(EditingPage.fieldNum2Textfield).toHaveValue("1")

      await buttons[1].click() // Second button
      await expect(buttons[0]).not.toBeSelected() // Checking initial value
      await expect(buttons[1]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldNum2Textfield).toHaveValue("2")

      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(buttons[0]).not.toBeSelected() // Checking initial value
      await expect(buttons[1]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldNum2Textfield).toHaveValue("2")
    })
    /*
    Summary: popup menu with non-null integer field
    Condition: The editing test page with new created record opens.
    Check-with: The popup menu of the field num1(integer, not null) exist.
    Check-with: The options of the popup menu is as expected.
    Operation: Select the option item "select1".
    Check-with: The popup menu has the value 10 which the value attribute of the option.
    Check-with: The num1 text field also has the value 10.
    Operation: Select the option item 2.
    Check-with: The popup menu has the value 20 which the value attribute of the option.
    Check-with: The num1 text field also has the value 20.
    Operation: Select the first option item.
    Check-with: The popup menu has the value 0 (the value attribute of the option is "").
    Check-with: The num1 text field also has the value 0.
    Operation: Reload the page with "Update" button.
    Check-with: The popup menu has the value 0 (same as before reload).
    Check-with: The num1 text field also has the value 0 (same as before reload).
     */
    it('7-can edit the popup menu of integer field which is NOT NULL.', async () => {
      await expect(EditingPage.fieldNum1Popup).toExist()
      await expect(EditingPage.fieldNum1Popup).toHaveValue("") // Checking initial value
      await expect(EditingPage.fieldNum1Popup).toHaveText("unselect\nselect1\nselect2\nselect3")

      await EditingPage.fieldNum1Popup.waitForClickable()
      await EditingPage.fieldNum1Popup.selectByVisibleText("select1") // Select second item
      await expect(EditingPage.fieldNum1Popup).toHaveValue("10")
      await expect(EditingPage.fieldNum1Textfield).toHaveValue("10")

      await EditingPage.fieldNum1Popup.waitForClickable()
      await EditingPage.fieldNum1Popup.selectByIndex(2) // Select third item
      await expect(EditingPage.fieldNum1Popup).toHaveValue("20")
      await expect(EditingPage.fieldNum1Textfield).toHaveValue("20")

      await EditingPage.fieldNum1Popup.waitForClickable()
      await EditingPage.fieldNum1Popup.selectByIndex(0) // Select first item
      await expect(EditingPage.fieldNum1Popup).toHaveValue("0")
      await expect(EditingPage.fieldNum1Textfield).toHaveValue("0")

      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldNum1Popup).toHaveValue("0")
      await expect(EditingPage.fieldNum1Textfield).toHaveValue("0")
    })
    /*
    Summary: popup menu with nullable integer field
    Condition: The editing test page with new created record opens.
    Check-with: The popup menu of the field num2(integer, nullable) exist.
    Check-with: The options of the popup menu is as expected.
    Operation: Select the option item "select1".
    Check-with: The popup menu has the value 10 which the value attribute of the option.
    Check-with: The num2 text field also has the value 10.
    Operation: Select the option item 2.
    Check-with: The popup menu has the value 20 which the value attribute of the option.
    Check-with: The num2 text field also has the value 20.
    Operation: Select the first option item.
    Check-with: The popup menu has the value "" which same as the value attribute of the option.
    Check-with: The num2 text field also has the value "".
    Operation: Reload the page with "Update" button.
    Check-with: The popup menu has the value "".
    Check-with: The num2 text field also has the value "".
     */
    it('8-can edit the popup menu of integer field which is nullable.', async () => {
      await expect(EditingPage.fieldNum2Popup).toExist()
      await expect(EditingPage.fieldNum2Popup).toHaveValue("") // Checking initial value
      await expect(EditingPage.fieldNum2Popup).toHaveText("unselect\nselect1\nselect2\nselect3")

      await EditingPage.fieldNum2Popup.waitForClickable()
      await EditingPage.fieldNum2Popup.selectByVisibleText("select1") // Select second item
      await expect(EditingPage.fieldNum2Popup).toHaveValue("10")
      await expect(EditingPage.fieldNum2Textfield).toHaveValue("10")

      await EditingPage.fieldNum2Popup.waitForClickable()
      await EditingPage.fieldNum2Popup.selectByIndex(2) // Select third item
      await expect(EditingPage.fieldNum2Popup).toHaveValue("20")
      await expect(EditingPage.fieldNum2Textfield).toHaveValue("20")

      await EditingPage.fieldNum2Popup.waitForClickable()
      await EditingPage.fieldNum2Popup.selectByIndex(0) // Select first item
      await expect(EditingPage.fieldNum2Popup).toHaveValue("")
      await expect(EditingPage.fieldNum2Textfield).toHaveValue("")

      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldNum2Popup).toHaveValue("")
      await expect(EditingPage.fieldNum2Textfield).toHaveValue("")
    })
    /*
    Summary: Text field with non-null float field
    Condition: The editing test page with new created record opens.
    Check-with: Exist the text field of the field float1(integer, not null).
    Check-with: The float1 text field has the value "0".
    Operation: Set a random number to float1 text field, and reload the page with "Update" button.
    Check-with: The float1 text field has the value by set on the previous operation.
    Operation: Set the value "" to float1 text field (i.e. clear it), and reload the page with "Update" button.
    Check-with: The float1 text field has the value "0" by set on the previous operation.
     */
  })
}