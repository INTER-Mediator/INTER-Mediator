module.exports = (EditingPage) => {
  describe("Float/Double Field", function () {
    const waiting = 500
    let isSQLITE = false
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
    it('1-can edit the text field of float field which is NOT NULL.', async () => {
      const url = await browser.getUrl()
      if (url.indexOf("SQLite") > -1) {
        isSQLITE = true
      }
      await EditingPage.reopen()

      await expect(EditingPage.fieldFloat1Textfield).toExist()
      await expect(EditingPage.fieldFloat1Textfield).toHaveValue(isSQLITE ? "0.00" : "0") // Checking initial value
//      const value = Math.trunc(Math.random() * 100000) / 1000
      let value = Math.trunc(Math.random() * 100000)
      value = (value % 10 === 0) ? (value + 1) : value
      value /= 100
      await EditingPage.fieldFloat1Textfield.setValue(value) // Set a value to the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldFloat1Textfield).toHaveValue(String(value))
      await EditingPage.fieldFloat1Textfield.setValue("") // Clear the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldFloat1Textfield).toHaveValue(isSQLITE ? "0.00" : "0")
    })
    /*
    Summary: Text field with nullable float field
    Condition: The editing test page with new created record opens.
    Check-with: Exist the text field of the field float2(float, nullable).
    Check-with: The float2 text field has the value "".
    Operation: Set a random number to float2 text field, and reload the page with "Update" button.
    Check-with: The float2 text field has the value by set on the previous operation.
    Operation: Set the value "" to float2 text field (i.e. clear it), and reload the page with "Update" button.
    Check-with: The float2 text field has the value "" by set on the previous operation.
     */
    it('2-can edit the text field of nullable float field.', async () => {
      await expect(EditingPage.fieldFloat2Textfield).toExist()
      await expect(EditingPage.fieldFloat2Textfield).toHaveValue("") // Checking initial value
      //const value = Math.trunc(Math.random() * 100000) / 1000
      let value = Math.trunc(Math.random() * 100000)
      value = (value % 10 === 0) ? (value + 1) : value
      value /= 100
      await EditingPage.fieldFloat2Textfield.setValue(value) // Set a value to the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldFloat2Textfield).toHaveValue(String(value))
      await EditingPage.fieldFloat2Textfield.setValue("") // Clear the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldFloat2Textfield).toHaveValue("")
    })
    /*
    Summary: Checkbox with non-null float field
    Condition: The editing test page with new created record opens.
    Check-with: The checkbox of the field float1(float, not null) is not selected.
    Operation: Click the float1 checkbox and set to checked, and reload the page with "Update" button.
    Check-with: The checkbox of the field float1 is selected.
    Check-with: The float1 text field has the value 1 which the value attribute of the checkbox.
    Operation: Click the float1 checkbox and set not to checked, and reload the page with "Update" button.
    Check-with: The checkbox of the field float1 is not selected.
    Check-with: The float1 text field has the value 0.
     */
    it('3-can edit the checkbox of float field which is NOT NULL.', async () => {
      await expect(EditingPage.fieldFloat1Checkbox).toExist()
      await expect(EditingPage.fieldFloat1Checkbox).not.toBeSelected() // Checking initial value
      await EditingPage.fieldFloat1Checkbox.click() // ON
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting * 3)
      await EditingPage.fieldFloat1Checkbox.waitForEnabled()
      await expect(EditingPage.fieldFloat1Checkbox).toBeSelected()
      await expect(EditingPage.fieldFloat1Textfield).toHaveValue(isSQLITE ? "1.00" : "1")
      await EditingPage.fieldFloat1Checkbox.click() // OFF
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting * 3)
      await EditingPage.fieldFloat1Checkbox.waitForEnabled()
      await expect(EditingPage.fieldFloat1Checkbox).not.toBeSelected()
      await expect(EditingPage.fieldFloat1Textfield).toHaveValue(isSQLITE ? "0.00" : "0")
    })
    /*
    Summary: Checkbox with nullable float field
    Condition: The editing test page with new created record opens.
    Check-with: The checkbox of the field float2(float, nullable) is not selected.
    Operation: Click the float2 checkbox and set to checked, and reload the page with "Update" button.
    Check-with: The checkbox of the field float2 is selected.
    Check-with: The float2 text field has the value 1 which the value attribute of the checkbox.
    Operation: Click the float2 checkbox and set not to checked, and reload the page with "Update" button.
    Check-with: The checkbox of the field float2 is not selected.
    Check-with: The float2 text field has the value "".
     */
    it('4-can edit the checkbox of nullable float field.', async () => {
      await expect(EditingPage.fieldFloat2Checkbox).toExist()
      await expect(EditingPage.fieldFloat2Checkbox).not.toBeSelected() // Checking initial value
      await EditingPage.fieldFloat2Checkbox.click() // ON
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting * 3)
      await EditingPage.fieldFloat2Checkbox.waitForEnabled()
      await expect(EditingPage.fieldFloat2Checkbox).toBeSelected()
      await expect(EditingPage.fieldFloat2Textfield).toHaveValue(isSQLITE ? "1.00" : "1")
      await EditingPage.fieldFloat2Checkbox.click() // OFF
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting * 3)
      await EditingPage.fieldFloat2Checkbox.waitForEnabled()
      await expect(EditingPage.fieldFloat2Checkbox).not.toBeSelected()
      await expect(EditingPage.fieldFloat2Textfield).toHaveValue("")
    })
    // Radio Buttons for non-integer type field is out of scope, ok?
    /*
    Summary: Radio buttons with non-null float field
    Condition: The editing test page with new created record opens.
    Check-with: The first button of the field float1(float, not null) is selected.
    Check-with: The second button of the field float1 is not selected.
    Operation: Click the first button and select it.
    Check-with: The first button of the field float1 is selected.
    Check-with: The second button of the field float1 is not selected.
    Check-with: The float1 text field has the value 0 which the value attribute of the button.
    Operation: Click the second button and select it.
    Check-with: The first button of the field float1 is not selected.
    Check-with: The second button of the field float1 is selected.
    Check-with: The float1 text field has the value 1 which the value attribute of the button.
     */
    it('5-can edit the radio buttons of float field which is NOT NULL.', async () => {
      await expect(EditingPage.fieldFloat1Radio[0]).toExist()
      await expect(EditingPage.fieldFloat1Radio[1]).toExist()
      await expect(EditingPage.fieldFloat1Radio[0]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldFloat1Radio[1]).not.toBeSelected() // Checking initial value
      await EditingPage.fieldFloat1Radio[0].click() // First button
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await EditingPage.fieldFloat1Radio[0].waitForEnabled()
      await expect(EditingPage.fieldFloat1Radio[0]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldFloat1Radio[1]).not.toBeSelected() // Checking initial value
      await expect(EditingPage.fieldFloat1Textfield).toHaveValue(isSQLITE ? "0.00" : "0")
      await EditingPage.fieldFloat1Radio[1].click() // Second button
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await EditingPage.fieldFloat1Radio[0].waitForEnabled()
      await expect(EditingPage.fieldFloat1Radio[0]).not.toBeSelected() // Checking initial value
      await expect(EditingPage.fieldFloat1Radio[1]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldFloat1Textfield).toHaveValue(isSQLITE ? "1.00" : "1")
    })
    // Radio Buttons for non-integer type field is out of scope, ok?
    /*
    Summary: Radio buttons with nullable float field
    Condition: The editing test page with new created record opens.
    Check-with: The first button of the field float2(float, nullable) is not selected.
    Check-with: The second button of the field float2 is not selected.
    Operation: Click the first button and select it.
    Check-with: The first button of the field float2 is selected.
    Check-with: The second button of the field float2 is not selected.
    Check-with: The float2 text field has the value 0 which the value attribute of the button.
    Operation: Click the second button and select it.
    Check-with: The first button of the field float2 is not selected.
    Check-with: The second button of the field float2 is selected.
    Check-with: The float2 text field has the value 1 which the value attribute of the button.
     */
    it('6-can edit the radio buttons of nullable float field.', async () => {
      await expect(EditingPage.fieldFloat2Radio[0]).toExist()
      await expect(EditingPage.fieldFloat2Radio[1]).toExist()
      await expect(EditingPage.fieldFloat2Radio[0]).not.toBeSelected() // Checking initial value
      await expect(EditingPage.fieldFloat2Radio[1]).not.toBeSelected() // Checking initial value
      await EditingPage.fieldFloat2Radio[0].click() // First button
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await EditingPage.fieldFloat2Radio[0].waitForEnabled()
      await expect(EditingPage.fieldFloat2Radio[0]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldFloat2Radio[1]).not.toBeSelected() // Checking initial value
      await expect(EditingPage.fieldFloat2Textfield).toHaveValue(isSQLITE ? "0.00" : "0")
      await EditingPage.fieldFloat2Radio[1].click() // Second button
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await EditingPage.fieldFloat2Radio[0].waitForEnabled()
      await expect(EditingPage.fieldFloat2Radio[0]).not.toBeSelected() // Checking initial value
      await expect(EditingPage.fieldFloat2Radio[1]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldFloat2Textfield).toHaveValue(isSQLITE ? "1.00" : "1")
    })
    /*
    Summary: popup menu with non-null float field
    Condition: The editing test page with new created record opens.
    Check-with: The popup menu of the field float1(float, not null) exist.
    Check-with: The options of the popup menu is as expected.
    Operation: Select the option item "select1".
    Check-with: The popup menu has the value 10 which the value attribute of the option.
    Check-with: The float1 text field also has the value 10.
    Operation: Select the option item 2.
    Check-with: The popup menu has the value 20 which the value attribute of the option.
    Check-with: The float1 text field also has the value 20.
    Operation: Select the first option item.
    Check-with: The popup menu has the value "" (the value attribute of the option is "").
    Check-with: The float1 text field also has the value 0.
     */
    it('7-can edit the popup menu of float field which is NOT NULL.', async () => {
      await expect(EditingPage.fieldFloat1Popup).toExist()
      await expect(EditingPage.fieldFloat1Popup).toHaveValue("") // Checking initial value
      await expect(EditingPage.fieldFloat1Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
      await EditingPage.fieldFloat1Popup.waitForClickable()
      await EditingPage.fieldFloat1Popup.selectByVisibleText("select1") // Select second item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldFloat1Popup).toHaveValue("10")
      await expect(EditingPage.fieldFloat1Textfield).toHaveValue(isSQLITE ? "10.00" : "10")
      await EditingPage.fieldFloat1Popup.waitForClickable()
      await EditingPage.fieldFloat1Popup.selectByIndex(2) // Select third item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldFloat1Popup).toHaveValue("20")
      await expect(EditingPage.fieldFloat1Textfield).toHaveValue(isSQLITE ? "20.00" : "20")
      await EditingPage.fieldFloat1Popup.waitForClickable()
      await EditingPage.fieldFloat1Popup.selectByIndex(0) // Select first item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldFloat1Popup).toHaveValue("")
      await expect(EditingPage.fieldFloat1Textfield).toHaveValue(isSQLITE ? "0.00" : "0")
    })
    /*
    Summary: popup menu with nullable float field
    Condition: The editing test page with new created record opens.
    Check-with: The popup menu of the field float2(float, nullable) exist.
    Check-with: The options of the popup menu is as expected.
    Operation: Select the option item "select1".
    Check-with: The popup menu has the value 10 which the value attribute of the option.
    Check-with: The float2 text field also has the value 10.
    Operation: Select the option item 2.
    Check-with: The popup menu has the value 20 which the value attribute of the option.
    Check-with: The float2 text field also has the value 20.
    Operation: Select the first option item.
    Check-with: The popup menu has the value "" (the value attribute of the option is "").
    Check-with: The float2 text field also has the value "".
     */
    it('8-can edit the popup menu of nullable float field.', async () => {
      await expect(EditingPage.fieldFloat2Popup).toExist()
      await expect(EditingPage.fieldFloat2Popup).toHaveValue("") // Checking initial value
      await expect(EditingPage.fieldFloat2Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
      await EditingPage.fieldFloat2Popup.waitForClickable()
      await EditingPage.fieldFloat2Popup.selectByVisibleText("select1") // Select second item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldFloat2Popup).toHaveValue("10")
      await expect(EditingPage.fieldFloat2Textfield).toHaveValue(isSQLITE ? "10.00" : "10")
      await EditingPage.fieldFloat2Popup.waitForClickable()
      await EditingPage.fieldFloat2Popup.selectByIndex(2) // Select third item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldFloat2Popup).toHaveValue("20")
      await expect(EditingPage.fieldFloat2Textfield).toHaveValue(isSQLITE ? "20.00" : "20")
      await EditingPage.fieldFloat2Popup.waitForClickable()
      await EditingPage.fieldFloat2Popup.selectByIndex(0) // Select first item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldFloat2Popup).toHaveValue("")
      await expect(EditingPage.fieldFloat2Textfield).toHaveValue("")
    })
    /*
    Summary: Text field with non-null double field
    Condition: The editing test page with new created record opens.
    Check-with: Exist the text field of the field double1(double, not null).
    Check-with: The double1 text field has the value "0".
    Operation: Set a random number to double1 text field, and reload the page with "Update" button.
    Check-with: The double1 text field has the value by set on the previous operation.
    Operation: Set the value "" to double1 text field (i.e. clear it), and reload the page with "Update" button.
    Check-with: The double1 text field has the value "0" by set on the previous operation.
     */
    it('9-can edit the text field of double field which is NOT NULL.', async () => {
      await expect(EditingPage.fieldDouble1Textfield).toExist()
      await expect(EditingPage.fieldDouble1Textfield).toHaveValue(isSQLITE ? "0.00" : "0") // Checking initial value
      //const value = Math.random() * 10000000
      let value = Math.trunc(Math.random() * 100000)
      value = (value % 10 === 0) ? (value + 1) : value
      value /= 100
      await EditingPage.fieldDouble1Textfield.setValue(value) // Set a value to the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble1Textfield).toHaveValue(String(value))
      await EditingPage.fieldDouble1Textfield.setValue("") // Clear the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble1Textfield).toHaveValue(isSQLITE ? "0.00" : "0")
    })
    /*
    Summary: Text field with nullable double field
    Condition: The editing test page with new created record opens.
    Check-with: Exist the text field of the field double2(double, nullable).
    Check-with: The double2 text field has the value "".
    Operation: Set a random number to double2 text field, and reload the page with "Update" button.
    Check-with: The double2 text field has the value by set on the previous operation.
    Operation: Set the value "" to double2 text field (i.e. clear it), and reload the page with "Update" button.
    Check-with: The double2 text field has the value "" by set on the previous operation.
     */
    it('10-can edit the text field of nullable double field.', async () => {
      await expect(EditingPage.fieldDouble2Textfield).toExist()
      await expect(EditingPage.fieldDouble2Textfield).toHaveValue("") // Checking initial value
      //const value = Math.random() * 10000000
      let value = Math.trunc(Math.random() * 100000)
      value = (value % 10 === 0) ? (value + 1) : value
      value /= 100
      await EditingPage.fieldDouble2Textfield.setValue(value) // Set a value to the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble2Textfield).toHaveValue(String(value))
      await EditingPage.fieldDouble2Textfield.setValue("") // Clear the field
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble2Textfield).toHaveValue("")
    })
    // Checkbox for non-integer type field is out of scope, ok?
    /*
    Summary: Checkbox with non-null double field
    Condition: The editing test page with new created record opens.
    Check-with: The checkbox of the field double1(double, not null) is not selected.
    Operation: Click the double1 checkbox and set to checked, and reload the page with "Update" button.
    Check-with: The checkbox of the field double1 is selected.
    Check-with: The double1 text field has the value 1 which the value attribute of the checkbox.
    Operation: Click the double1 checkbox and set not to checked, and reload the page with "Update" button.
    Check-with: The checkbox of the field double1 is not selected.
    Check-with: The double1 text field has the value 0.
     */
    it('11-can edit the checkbox of double field which is NOT NULL.', async () => {
      await expect(EditingPage.fieldDouble1Checkbox).toExist()
      await expect(EditingPage.fieldDouble1Checkbox).not.toBeSelected() // Checking initial value
      await EditingPage.fieldDouble1Checkbox.waitForClickable() // ON
      await EditingPage.fieldDouble1Checkbox.click() // ON
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble1Checkbox).toBeSelected()
      await expect(EditingPage.fieldDouble1Textfield).toHaveValue(isSQLITE ? "1.00" : "1")
      await EditingPage.fieldDouble1Checkbox.waitForClickable() // OFF
      await EditingPage.fieldDouble1Checkbox.click() // OFF
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble1Checkbox).not.toBeSelected()
      await expect(EditingPage.fieldDouble1Textfield).toHaveValue(isSQLITE ? "0.00" : "0")
    })
    // Checkbox for non-integer type field is out of scope, ok?
    /*
    Summary: Checkbox with nullable double field
    Condition: The editing test page with new created record opens.
    Check-with: The checkbox of the field double2(double, nullable) is not selected.
    Operation: Click the double2 checkbox and set to checked, and reload the page with "Update" button.
    Check-with: The checkbox of the field double2 is selected.
    Check-with: The double2 text field has the value 1 which the value attribute of the checkbox.
    Operation: Click the double2 checkbox and set not to checked, and reload the page with "Update" button.
    Check-with: The checkbox of the field double2 is not selected.
    Check-with: The double2 text field has the value "".
     */
    it('12-can edit the checkbox of nullable double field.', async () => {
      await expect(EditingPage.fieldDouble2Checkbox).toExist()
      await expect(EditingPage.fieldDouble2Checkbox).not.toBeSelected() // Checking initial value
      await EditingPage.fieldDouble2Checkbox.waitForClickable() // ON
      await EditingPage.fieldDouble2Checkbox.click() // ON
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble2Checkbox).toBeSelected()
      await expect(EditingPage.fieldDouble2Textfield).toHaveValue(isSQLITE ? "1.00" : "1")
      await EditingPage.fieldDouble2Checkbox.waitForClickable() // OFF
      await EditingPage.fieldDouble2Checkbox.click() // OFF
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble2Checkbox).not.toBeSelected()
      await expect(EditingPage.fieldDouble2Textfield).toHaveValue("")
    })
    // Radio Buttons for non-integer type field is out of scope, ok?
    /*
    Summary: Radio buttons with non-null double field
    Condition: The editing test page with new created record opens.
    Check-with: The first button of the field double1(double, not null) is not selected.
    Check-with: The second button of the field double1 is not selected.
    Operation: Click the first button and select it.
    Check-with: The first button of the field double1 is selected.
    Check-with: The second button of the field double1 is not selected.
    Check-with: The double1 text field has the value 0 which the value attribute of the button.
    Operation: Click the second button and select it.
    Check-with: The first button of the field double1 is selected.
    Check-with: The second button of the field double1 is not selected.
    Check-with: The double1 text field has the value 1 which the value attribute of the button.
     */
    it('13-can edit the radio buttons of double field which is NOT NULL.', async () => {
      await expect(EditingPage.fieldDouble1Radio[0]).toExist()
      await expect(EditingPage.fieldDouble1Radio[1]).toExist()
      await expect(EditingPage.fieldDouble1Radio[0]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldDouble1Radio[1]).not.toBeSelected() // Checking initial value
      await EditingPage.fieldDouble1Radio[0].waitForClickable() // First button
      await EditingPage.fieldDouble1Radio[0].click() // First button
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble1Radio[0]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldDouble1Radio[1]).not.toBeSelected() // Checking initial value
      await expect(EditingPage.fieldDouble1Textfield).toHaveValue(isSQLITE ? "0.00" : "0")
      await EditingPage.fieldDouble1Radio[1].waitForClickable() // Second button
      await EditingPage.fieldDouble1Radio[1].click() // Second button
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble1Radio[0]).not.toBeSelected() // Checking initial value
      await expect(EditingPage.fieldDouble1Radio[1]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldDouble1Textfield).toHaveValue(isSQLITE ? "1.00" : "1")
    })
    /*
    Summary: Radio buttons with nullable double field
    Condition: The editing test page with new created record opens.
    Check-with: The first button of the field double2(double, nullable) is not selected.
    Check-with: The second button of the field double2 is not selected.
    Operation: Click the first button and select it.
    Check-with: The first button of the field double2 is selected.
    Check-with: The second button of the field double2 is not selected.
    Check-with: The double2 text field has the value 0 which the value attribute of the button.
    Operation: Click the second button and select it.
    Check-with: The first button of the field double2 is selected.
    Check-with: The second button of the field double2 is not selected.
    Check-with: The double2 text field has the value 1 which the value attribute of the button.
     */
    it('14-can edit the radio buttons of nullable double field.', async () => {
      await expect(EditingPage.fieldDouble2Radio[0]).toExist()
      await expect(EditingPage.fieldDouble2Radio[1]).toExist()
      await expect(EditingPage.fieldDouble2Radio[0]).not.toBeSelected() // Checking initial value
      await expect(EditingPage.fieldDouble2Radio[1]).not.toBeSelected() // Checking initial value
      await EditingPage.fieldDouble2Radio[0].waitForClickable()
      await EditingPage.fieldDouble2Radio[0].click() // First button
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble2Radio[0]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldDouble2Radio[1]).not.toBeSelected() // Checking initial value
      await expect(EditingPage.fieldDouble2Textfield).toHaveValue(isSQLITE ? "0.00" : "0")
      await EditingPage.fieldDouble2Radio[1].waitForClickable()
      await EditingPage.fieldDouble2Radio[1].click() // Second button
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble2Radio[0]).not.toBeSelected() // Checking initial value
      await expect(EditingPage.fieldDouble2Radio[1]).toBeSelected() // Checking initial value
      await expect(EditingPage.fieldDouble2Textfield).toHaveValue(isSQLITE ? "1.00" : "1")
    })
    /*
    Summary: popup menu with non-null double field
    Condition: The editing test page with new created record opens.
    Check-with: The popup menu of the field double1(double, not null) exist.
    Check-with: The options of the popup menu is as expected.
    Operation: Select the option item "select1".
    Check-with: The popup menu has the value 10 which the value attribute of the option.
    Check-with: The double1 text field also has the value 10.
    Operation: Select the option item 2.
    Check-with: The popup menu has the value 20 which the value attribute of the option.
    Check-with: The double1 text field also has the value 20.
    Operation: Select the first option item.
    Check-with: The popup menu has the value "" (the value attribute of the option is "").
    Check-with: The double1 text field also has the value 0.
     */
    it('15-can edit the popup menu of double field which is NOT NULL.', async () => {
      await expect(EditingPage.fieldDouble1Popup).toExist()
      await expect(EditingPage.fieldDouble1Popup).toHaveValue("") // Checking initial value
      await expect(EditingPage.fieldDouble1Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
      await EditingPage.fieldDouble1Popup.waitForClickable()
      await EditingPage.fieldDouble1Popup.selectByVisibleText("select1") // Select second item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble1Popup).toHaveValue("10")
      await expect(EditingPage.fieldDouble1Textfield).toHaveValue(isSQLITE ? "10.00" : "10")
      await EditingPage.fieldDouble1Popup.waitForClickable()
      await EditingPage.fieldDouble1Popup.selectByIndex(2) // Select third item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble1Popup).toHaveValue("20")
      await expect(EditingPage.fieldDouble1Textfield).toHaveValue(isSQLITE ? "20.00" : "20")
      await EditingPage.fieldDouble1Popup.waitForClickable()
      await EditingPage.fieldDouble1Popup.selectByIndex(0) // Select first item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble1Popup).toHaveValue("")
      await expect(EditingPage.fieldDouble1Textfield).toHaveValue(isSQLITE ? "0.00" : "0")
    })
    /*
    Summary: popup menu with non-null double field
    Condition: The editing test page with new created record opens.
    Check-with: The popup menu of the field double2(double, not null) exist.
    Check-with: The options of the popup menu is as expected.
    Operation: Select the option item "select1".
    Check-with: The popup menu has the value 10 which the value attribute of the option.
    Check-with: The double2 text field also has the value 10.
    Operation: Select the option item 2.
    Check-with: The popup menu has the value 20 which the value attribute of the option.
    Check-with: The double2 text field also has the value 20.
    Operation: Select the first option item.
    Check-with: The popup menu has the value "" (the value attribute of the option is "").
    Check-with: The double2 text field also has the value "".
     */
    it('16-can edit the popup menu of nullable double field.', async () => {
      await expect(EditingPage.fieldDouble2Popup).toExist()
      await expect(EditingPage.fieldDouble2Popup).toHaveValue("") // Checking initial value
      await expect(EditingPage.fieldDouble2Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
      await EditingPage.fieldDouble2Popup.waitForClickable()
      await EditingPage.fieldDouble2Popup.selectByVisibleText("select1") // Select second item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble2Popup).toHaveValue("10")
      await expect(EditingPage.fieldDouble2Textfield).toHaveValue(isSQLITE ? "10.00" : "10")
      await EditingPage.fieldDouble2Popup.waitForClickable()
      await EditingPage.fieldDouble2Popup.selectByIndex(2) // Select third item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble2Popup).toHaveValue("20")
      await expect(EditingPage.fieldDouble2Textfield).toHaveValue(isSQLITE ? "20.00" : "20")
      await EditingPage.fieldDouble2Popup.waitForClickable()
      await EditingPage.fieldDouble2Popup.selectByIndex(0) // Select first item
      await EditingPage.navigatorUpdateButton.waitForClickable()
      await EditingPage.navigatorUpdateButton.click()
      await browser.pause(waiting)
      await expect(EditingPage.fieldDouble2Popup).toHaveValue("")
      await expect(EditingPage.fieldDouble2Textfield).toHaveValue("")
    })
  })
}

