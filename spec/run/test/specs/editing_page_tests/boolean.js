module.exports = (EditingPage) => {
  describe("Boolean Field", function () {
    const waiting = 500
    let isPGSQL = false
    /*
    Summary: Text field with non-null bool field
    Condition: The editing test page with new created record opens.
    Check-with: Exist the text field of the field bool1(bool, not null).
    Check-with: The bool1 text field has the value "0".
    Operation: Set a random number to bool1 text field, and reload the page with "Update" button.
    Check-with: The bool1 text field has the value by set on the previous operation.
    Operation: Set the value "" to bool1 text field (i.e. clear it), and reload the page with "Update" button.
    Check-with: The bool1 text field has the value "0" by set on the previous operation.
     */
    it('1-can edit the text field of boolean field which is NOT NULL.', async () => {
      const url = await browser.getUrl()
      if (url.indexOf("PostgreSQL") > -1) {
        isPGSQL = true
      }
      await EditingPage.reopen()

      await expect(EditingPage.fieldBool1Textfield).toExist()
      await expect(EditingPage.fieldBool1Textfield).toHaveValue(isPGSQL ? "" : "0") // Checking initial value
      const value = 1
      await EditingPage.fieldBool1Textfield.setValue(value) // Set a value to the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldBool1Textfield).toHaveValue(isPGSQL ? "true" : "1")
      await EditingPage.fieldBool1Textfield.setValue("") // Clear the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldBool1Textfield).toHaveValue(isPGSQL ? "" : "0")
    })
    /*
    Summary: Text field with nullable boolean field
    Condition: The editing test page with new created record opens.
    Check-with: Exist the text field of the field bool2(boolean, nullable).
    Check-with: The bool2 text field has the value "".
    Operation: Set a random number to bool2 text field, and reload the page with "Update" button.
    Check-with: The bool2 text field has the value by set on the previous operation.
    Operation: Set the value "" to bool2 text field (i.e. clear it), and reload the page with "Update" button.
    Check-with: The bool2 text field has the value "" by set on the previous operation.
     */
    it('2-can edit the text field of nullable boolean field.', async () => {
      await expect(EditingPage.fieldBool2Textfield).toExist()
      await expect(EditingPage.fieldBool2Textfield).toHaveValue("") // Checking initial value
      const value = 1
      await EditingPage.fieldBool2Textfield.setValue(value) // Set a value to the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldBool2Textfield).toHaveValue(isPGSQL? "true" : "1")
      await EditingPage.fieldBool2Textfield.setValue("") // Clear the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldBool2Textfield).toHaveValue("")
    })
    /*
    Summary: Checkbox with non-null boolean field
    Condition: The editing test page with new created record opens.
    Check-with: The checkbox of the field bool1(boolean, not null) is not selected.
    Operation: Click the bool1 checkbox and set to checked, and reload the page with "Update" button.
    Check-with: The checkbox of the field bool1 is selected.
    Check-with: The bool1 text field has the value 1 which the value attribute of the checkbox.
    Operation: Click the bool1 checkbox and set not to checked, and reload the page with "Update" button.
    Check-with: The checkbox of the field bool1 is not selected.
    Check-with: The bool1 text field has the value 0.
     */
    it('3-can edit the checkbox of boolean field which is NOT NULL.', async () => {
      await EditingPage.fieldBool1Textfield.setValue("") // Clear the field
      await expect(EditingPage.fieldBool1Checkbox).toExist()
      await expect(EditingPage.fieldBool1Checkbox).not.toBeSelected() // Checking initial value
      await EditingPage.fieldBool1Checkbox.waitForClickable() // For stability
      await EditingPage.fieldBool1Checkbox.click() // ON
      await browser.pause(waiting)
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await EditingPage.fieldBool1Checkbox.waitForClickable() // For stability
      await expect(EditingPage.fieldBool1Checkbox).toBeSelected()
      await expect(EditingPage.fieldBool1Textfield).toHaveValue(isPGSQL? "true" : "1")
      await EditingPage.fieldBool1Checkbox.waitForClickable() // OFF
      await EditingPage.fieldBool1Checkbox.click() // OFF
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldBool1Checkbox).not.toBeSelected()
      await expect(EditingPage.fieldBool1Textfield).toHaveValue(isPGSQL ? "" : "0")
    })
    /*
    Summary: Checkbox with nullable boolean field
    Condition: The editing test page with new created record opens.
    Check-with: The checkbox of the field bool2(boolean, nullable) is not selected.
    Operation: Click the bool2 checkbox and set to checked, and reload the page with "Update" button.
    Check-with: The checkbox of the field bool2 is selected.
    Check-with: The bool2 text field has the value 1 which the value attribute of the checkbox.
    Operation: Click the bool2 checkbox and set not to checked, and reload the page with "Update" button.
    Check-with: The checkbox of the field bool2 is not selected.
    Check-with: The bool2 text field has the value "".
     */
    it('4-can edit the checkbox of nullable boolean field.', async () => {
      await EditingPage.fieldBool2Textfield.setValue("") // Clear the field
      await expect(EditingPage.fieldBool2Checkbox).toExist()
      await expect(EditingPage.fieldBool2Checkbox).not.toBeSelected() // Checking initial value
      await EditingPage.fieldBool2Checkbox.waitForClickable() // ON
      await EditingPage.fieldBool2Checkbox.click() // ON
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldBool2Checkbox).toBeSelected()
      await expect(EditingPage.fieldBool2Textfield).toHaveValue(isPGSQL ? "true" : "1")
      await EditingPage.fieldBool2Checkbox.waitForClickable() // OFF
      await EditingPage.fieldBool2Checkbox.click() // OFF
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldBool2Checkbox).not.toBeSelected()
      await expect(EditingPage.fieldBool2Textfield).toHaveValue("")
    })
    /*
    Summary: Radio buttons with non-null boolean field
    Condition: The editing test page with new created record opens.
    Check-with: The first button of the field bool1(boolean, not null) is selected.
    Check-with: The second button of the field bool1 is not selected.
    Operation: Click the first button and select it.
    Check-with: The first button of the field bool1 is selected.
    Check-with: The second button of the field bool1 is not selected.
    Check-with: The bool1 text field has the value "" which the value attribute of the button.
    Operation: Click the second button and select it.
    Check-with: The first button of the field bool1 is not selected.
    Check-with: The second button of the field bool1 is selected.
    Check-with: The bool1 text field has the value "true" which the value attribute of the button.
     */
    it('5-can edit the radio buttons of boolean field which is NOT NULL.', async () => {
      await EditingPage.fieldBool1Textfield.setValue("") // Clear the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldBool1Radio[0]).toExist()
      await expect(EditingPage.fieldBool1Radio[1]).toExist()
      await expect(EditingPage.fieldBool1Radio[0]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldBool1Radio[1]).not.toBeSelected() // Checking initial value
      await EditingPage.fieldBool1Radio[0].waitForClickable() // First button
      await EditingPage.fieldBool1Radio[0].click() // First button
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldBool1Radio[0]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldBool1Radio[1]).not.toBeSelected() // Checking initial value
      await expect(EditingPage.fieldBool1Textfield).toHaveValue(isPGSQL ? "" : "0")
      await EditingPage.fieldBool1Radio[1].waitForClickable() // Second button
      await EditingPage.fieldBool1Radio[1].click() // Second button
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldBool1Radio[0]).not.toBeSelected() // Checking initial value
      await expect(EditingPage.fieldBool1Radio[1]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldBool1Textfield).toHaveValue(isPGSQL ? "true" : "1")
    })
    /*
    Summary: Radio buttons with nullable boolean field
    Condition: The editing test page with new created record opens.
    Check-with: The first button of the field bool2(boolean, nullable) is selected.
    Check-with: The second button of the field bool2 is not selected.
    Operation: Click the first button and select it.
    Check-with: The first button of the field bool2 is selected.
    Check-with: The second button of the field bool2 is not selected.
    Check-with: The bool2 text field has the value "" which the value attribute of the button.
    Operation: Click the second button and select it.
    Check-with: The first button of the field bool2 is not selected.
    Check-with: The second button of the field bool2 is selected.
    Check-with: The bool2 text field has the value "true" which the value attribute of the button.
     */
    it('6-can edit the radio buttons of nullable boolean field.', async () => {
      await EditingPage.fieldBool2Textfield.setValue("") // Clear the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldBool2Radio[0]).toExist()
      await expect(EditingPage.fieldBool2Radio[1]).toExist()
      if (isPGSQL) {
        await expect(EditingPage.fieldBool2Radio[0]).toBeSelected() // Checking initial value
        await expect(EditingPage.fieldBool2Radio[1]).not.toBeSelected() // Checking initial value
      } else {
        await expect(EditingPage.fieldBool2Radio[0]).not.toBeSelected() // Checking initial value
        await expect(EditingPage.fieldBool2Radio[1]).not.toBeSelected() // Checking initial value
      }
      await EditingPage.fieldBool2Radio[0].waitForClickable() // First button
      await EditingPage.fieldBool2Radio[0].click() // First button
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await EditingPage.fieldBool2Radio[0].waitForClickable() // For stability
      await expect(EditingPage.fieldBool2Radio[0]).toBeSelected()
      await expect(EditingPage.fieldBool2Radio[1]).not.toBeSelected()
      if (isPGSQL) {
        await expect(EditingPage.fieldBool2Textfield).toHaveValue("")
      } else {
        await expect(EditingPage.fieldBool2Textfield).toHaveValue("0")
      }
      await EditingPage.fieldBool2Radio[1].waitForClickable() // Second button
      await EditingPage.fieldBool2Radio[1].click() // Second button
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldBool2Radio[0]).not.toBeSelected() // Checking initial value
      await expect(EditingPage.fieldBool2Radio[1]).toBeSelected() // Checking initial value
      if (isPGSQL) {
        await expect(EditingPage.fieldBool2Textfield).toHaveValue("true")
      } else {
        await expect(EditingPage.fieldBool2Textfield).toHaveValue("1")
      }
    })
    /*
    Summary: popup menu with non-null boolean field
    Condition: The editing test page with new created record opens.
    Check-with: The popup menu of the field bool1(boolean, not null) exist.
    Check-with: The options of the popup menu is as expected.
    Operation: Select the option item "select1", and click "Update" button.
    Check-with: The popup menu has the value "".
    Check-with: The bool1 text field also has the value "true".
    Operation: Select the option item 2, and click "Update" button.
    Check-with: The popup menu has the value "".
    Check-with: The bool1 text field also has the value "true".
    Operation: Select the first option item, and click "Update" button.
    Check-with: The popup menu has the value "".
    Check-with: The bool1 text field also has the value "".
     */
    it('7-can edit the popup menu of boolean field which is NOT NULL.', async () => {
      await expect(EditingPage.fieldBool1Popup).toExist()
      await expect(EditingPage.fieldBool1Popup).toHaveValue("") // Checking initial value
      await expect(EditingPage.fieldBool1Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
      await EditingPage.fieldBool1Popup.waitForClickable()
      await EditingPage.fieldBool1Popup.selectByVisibleText("select1") // Select second item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      if (isPGSQL) {
        await expect(EditingPage.fieldBool1Popup).toHaveValue("")
        await expect(EditingPage.fieldBool1Textfield).toHaveValue("true")
      } else {
        await expect(EditingPage.fieldBool1Popup).toHaveValue("10")
        await expect(EditingPage.fieldBool1Textfield).toHaveValue("10")
      }
      await EditingPage.fieldBool1Popup.waitForClickable()
      await EditingPage.fieldBool1Popup.selectByIndex(2) // Select third item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      if (isPGSQL) {
        await expect(EditingPage.fieldBool1Popup).toHaveValue("")
        await expect(EditingPage.fieldBool1Textfield).toHaveValue("true")
      } else {
        await expect(EditingPage.fieldBool1Popup).toHaveValue("20")
        await expect(EditingPage.fieldBool1Textfield).toHaveValue("20")
      }
      await EditingPage.fieldBool1Popup.waitForClickable()
      await EditingPage.fieldBool1Popup.selectByIndex(0) // Select first item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldBool1Popup).toHaveValue("")
      if (isPGSQL) {
        await expect(EditingPage.fieldBool1Textfield).toHaveValue("")
      } else {
        await expect(EditingPage.fieldBool1Textfield).toHaveValue("0")
      }
    })
    /*
    Summary: popup menu with non-null boolean field
    Condition: The editing test page with new created record opens.
    Check-with: The popup menu of the field bool1(boolean, not null) exist.
    Check-with: The options of the popup menu is as expected.
    Operation: Select the option item "select1", and click "Update" button.
    Check-with: The popup menu has the value "".
    Check-with: The bool1 text field also has the value "true".
    Operation: Select the option item 2, and click "Update" button.
    Check-with: The popup menu has the value "".
    Check-with: The bool1 text field also has the value "true".
    Operation: Select the first option item, and click "Update" button.
    Check-with: The popup menu has the value "".
    Check-with: The bool1 text field also has the value "".
     */
    it('8-can edit the popup menu of nullable boolean field.', async () => {
      await expect(EditingPage.fieldBool2Popup).toExist()
      await expect(EditingPage.fieldBool2Popup).toHaveValue("") // Checking initial value
      await expect(EditingPage.fieldBool2Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
      await EditingPage.fieldBool2Popup.waitForClickable()
      await EditingPage.fieldBool2Popup.selectByVisibleText("select1") // Select second item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      if (isPGSQL) {
        await expect(EditingPage.fieldBool2Popup).toHaveValue("")
        await expect(EditingPage.fieldBool2Textfield).toHaveValue("true")
      } else {
        await expect(EditingPage.fieldBool2Popup).toHaveValue("10")
        await expect(EditingPage.fieldBool2Textfield).toHaveValue("10")
      }
      await EditingPage.fieldBool2Popup.waitForClickable()
      await EditingPage.fieldBool2Popup.selectByIndex(2) // Select third item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      if (isPGSQL) {
        await expect(EditingPage.fieldBool2Popup).toHaveValue("")
        await expect(EditingPage.fieldBool2Textfield).toHaveValue("true")
      } else {
        await expect(EditingPage.fieldBool2Popup).toHaveValue("20")
        await expect(EditingPage.fieldBool2Textfield).toHaveValue("20")
      }
      await EditingPage.fieldBool2Popup.waitForClickable()
      await EditingPage.fieldBool2Popup.selectByIndex(0) // Select first item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldBool2Popup).toHaveValue("")
      await expect(EditingPage.fieldBool2Textfield).toHaveValue("")
    })
  })
}