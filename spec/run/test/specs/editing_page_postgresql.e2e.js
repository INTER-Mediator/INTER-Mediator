const EditingPage = require('../pageobjects/editing_page_postgresql.page');
const waiting = 500
let pageTitle = "INTER-Mediator - Sample - Editing/PostgreSQL"

describe('Editing Page with PostgreSQL', () => {
  /*
  Summary: Open the editing page
  Condition: None.
  Operation: Open the editing test page.
  Check-with: The page title is valid.
   */
  it('1.can open with the valid title.', async () => {
    await EditingPage.open()
    await expect(browser).toHaveTitle(pageTitle)
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
    await browser.pause(waiting)
    await EditingPage.reopen()
  })
  // /*
  // Summary: Text field with non-null integer field
  // Condition: The editing test page with new created record opens.
  // Check-with: Exist the text field of the field num1(integer, not null).
  // Check-with: The num1 text field has the value "0".
  // Operation: Set a random number to num1 text field, and reload the page with "Update" button.
  // Check-with: The num1 text field has the value by set on the previous operation.
  // Operation: Set the value "" to num1 text field (i.e. clear it), and reload the page with "Update" button.
  // Check-with: The num1 text field has the value "0" by set on the previous operation.
  //  */
  // it('3.can edit the text field of integer field which is NOT NULL.', async () => {
  //   await expect(EditingPage.fieldNum1Textfield).toExist()
  //   await expect(EditingPage.fieldNum1Textfield).toHaveValue("0") // Checking initial value
  //   const value = Math.trunc(Math.random() * 10000000)
  //   await EditingPage.fieldNum1Textfield.setValue(String(value)) // Set a value to the field
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldNum1Textfield).toHaveValue(String(value))
  //   await EditingPage.fieldNum1Textfield.setValue("") // Clear the field
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldNum1Textfield).toHaveValue("0")
  // })
  // /*
  // Summary: Text field with nullable integer field
  // Condition: The editing test page with new created record opens.
  // Check-with: Exist the text field of the field num2(integer, nullable).
  // Check-with: The num2 text field has the value "".
  // Operation: Set a random number to num2 text field, and reload the page with "Update" button.
  // Check-with: The num2 text field has the value by set on the previous operation.
  // Operation: Set the value "" to num2 text field (i.e. clear it), and reload the page with "Update" button.
  // Check-with: The num2 text field has the value "" by set on the previous operation.
  //  */
  // it('4.can edit the text field of nullable integer field.', async () => {
  //   await expect(EditingPage.fieldNum2Textfield).toExist()
  //   await expect(EditingPage.fieldNum2Textfield).toHaveValue("") // Checking initial value
  //   const value = Math.trunc(Math.random() * 10000000)
  //   await EditingPage.fieldNum2Textfield.setValue(value) // Set a value to the field
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldNum2Textfield).toHaveValue(String(value))
  //   await EditingPage.fieldNum2Textfield.setValue("") // Clear the field
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldNum2Textfield).toHaveValue("")
  // })
  // /*
  // Summary: Checkbox with non-null integer field
  // Condition: The editing test page with new created record opens.
  // Check-with: The checkbox of the field num1(integer, not null) is not selected.
  // Operation: Click the num1 checkbox and set to checked, and reload the page with "Update" button.
  // Check-with: The checkbox of the field num1 is selected.
  // Check-with: The num1 text field has the value 1 which the value attribute of the checkbox.
  // Operation: Click the num1 checkbox and set not to checked, and reload the page with "Update" button.
  // Check-with: The checkbox of the field num1 is not selected.
  // Check-with: The num1 text field has the value 0.
  //  */
  // it('5.can edit the checkbox of integer field which is NOT NULL.', async () => {
  //   await expect(EditingPage.fieldNum1Checkbox).toExist()
  //   await expect(EditingPage.fieldNum1Checkbox).not.toBeSelected() // Checking initial value
  //   await EditingPage.fieldNum1Checkbox.click() // ON
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldNum1Checkbox).toBeSelected()
  //   await expect(EditingPage.fieldNum1Textfield).toHaveValue("1")
  //   await EditingPage.fieldNum1Checkbox.click() // OFF
  //   await browser.pause(waiting)
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldNum1Checkbox).not.toBeSelected()
  //   await expect(EditingPage.fieldNum1Textfield).toHaveValue("0")
  // })
  // /*
  // Summary: Checkbox with nullable integer field
  // Condition: The editing test page with new created record opens.
  // Check-with: The checkbox of the field num2(integer, nullable) is not selected.
  // Operation: Click the num2 checkbox and set to checked, and reload the page with "Update" button.
  // Check-with: The checkbox of the field num2 is selected.
  // Check-with: The num2 text field has the value 1 which the value attribute of the checkbox.
  // Operation: Click the num2 checkbox and set not to checked, and reload the page with "Update" button.
  // Check-with: The checkbox of the field num2 is not selected.
  // Check-with: The num2 text field has the value "".
  //  */
  // it('6.can edit the checkbox of nullable integer field.', async () => {
  //   await expect(EditingPage.fieldNum2Checkbox).toExist()
  //   await expect(EditingPage.fieldNum2Checkbox).not.toBeSelected() // Checking initial value
  //   await EditingPage.fieldNum2Checkbox.click() // ON
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldNum2Checkbox).toBeSelected()
  //   await expect(EditingPage.fieldNum2Textfield).toHaveValue("1")
  //   await EditingPage.fieldNum2Checkbox.click() // OFF
  //   await browser.pause(waiting)
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldNum2Checkbox).not.toBeSelected()
  //   await expect(EditingPage.fieldNum2Textfield).toHaveValue("")
  // })
  // /*
  // Summary: Radio buttons with non-null integer field
  // Condition: The editing test page with new created record opens.
  // Check-with: The first button of the field num1(integer, not null) is not selected.
  // Check-with: The second button of the field num1 is not selected.
  // Operation: Click the first button and select it.
  // Check-with: The first button of the field num1 is selected.
  // Check-with: The second button of the field num1 is not selected.
  // Check-with: The num1 text field has the value 1 which the value attribute of the button.
  // Operation: Click the second button and select it.
  // Check-with: The first button of the field num1 is selected.
  // Check-with: The second button of the field num1 is not selected.
  // Check-with: The num1 text field has the value 2 which the value attribute of the button.
  // Operation: Reload the page with "Update" button.
  // Check-with: The first button of the field num1 is selected (same as before reload).
  // Check-with: The second button of the field num1 is not selected (same as before reload).
  // Check-with: The num1 text field has the value 2 which the value attribute of the button (same as before reload).
  //  */
  // it('7.can edit the radio buttons of integer field which is NOT NULL.', async () => {
  //   const buttons = await EditingPage.fieldNum1Radio
  //   await expect(buttons[0]).toExist()
  //   await expect(buttons[1]).toExist()
  //   await expect(buttons[0]).not.toBeSelected() // Checking initial value
  //   await expect(buttons[1]).not.toBeSelected() // Checking initial value
  //
  //   await buttons[0].click() // First button
  //   await expect(buttons[0]).toBeSelected() // Checking initial value
  //   await expect(buttons[1]).not.toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldNum1Textfield).toHaveValue("1")
  //
  //   await buttons[1].click() // Second button
  //   await expect(buttons[0]).not.toBeSelected() // Checking initial value
  //   await expect(buttons[1]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldNum1Textfield).toHaveValue("2")
  //
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(buttons[0]).not.toBeSelected() // Checking initial value
  //   await expect(buttons[1]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldNum1Textfield).toHaveValue("2")
  // })
  // /*
  // Summary: Radio buttons with nullable integer field
  // Condition: The editing test page with new created record opens.
  // Check-with: The first button of the field num2(integer, nullable) is not selected.
  // Check-with: The second button of the field num2 is not selected.
  // Operation: Click the first button and select it.
  // Check-with: The first button of the field num2 is selected.
  // Check-with: The second button of the field num2 is not selected.
  // Check-with: The num2 text field has the value 1 which the value attribute of the button.
  // Operation: Click the second button and select it.
  // Check-with: The first button of the field num2 is selected.
  // Check-with: The second button of the field num2 is not selected.
  // Check-with: The num2 text field has the value 2 which the value attribute of the button.
  // Operation: Reload the page with "Update" button.
  // Check-with: The first button of the field num2 is selected (same as before reload).
  // Check-with: The second button of the field num2 is not selected (same as before reload).
  // Check-with: The num2 text field has the value 2 which the value attribute of the button (same as before reload).
  //  */
  // it('8.can edit the radio buttons of nullable integer field.', async () => {
  //   const buttons = await EditingPage.fieldNum2Radio
  //   await expect(buttons[0]).toExist()
  //   await expect(buttons[1]).toExist()
  //   await expect(buttons[0]).not.toBeSelected() // Checking initial value
  //   await expect(buttons[1]).not.toBeSelected() // Checking initial value
  //
  //   await buttons[0].click() // First button
  //   await expect(buttons[0]).toBeSelected() // Checking initial value
  //   await expect(buttons[1]).not.toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldNum2Textfield).toHaveValue("1")
  //
  //   await buttons[1].click() // Second button
  //   await expect(buttons[0]).not.toBeSelected() // Checking initial value
  //   await expect(buttons[1]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldNum2Textfield).toHaveValue("2")
  //
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(buttons[0]).not.toBeSelected() // Checking initial value
  //   await expect(buttons[1]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldNum2Textfield).toHaveValue("2")
  // })
  // /*
  // Summary: popup menu with non-null integer field
  // Condition: The editing test page with new created record opens.
  // Check-with: The popup menu of the field num1(integer, not null) exist.
  // Check-with: The options of the popup menu is as expected.
  // Operation: Select the option item "select1".
  // Check-with: The popup menu has the value 10 which the value attribute of the option.
  // Check-with: The num1 text field also has the value 10.
  // Operation: Select the option item 2.
  // Check-with: The popup menu has the value 20 which the value attribute of the option.
  // Check-with: The num1 text field also has the value 20.
  // Operation: Select the first option item.
  // Check-with: The popup menu has the value 0 (the value attribute of the option is "").
  // Check-with: The num1 text field also has the value 0.
  // Operation: Reload the page with "Update" button.
  // Check-with: The popup menu has the value 0 (same as before reload).
  // Check-with: The num1 text field also has the value 0 (same as before reload).
  //  */
  // it('9.can edit the popup menu of integer field which is NOT NULL.', async () => {
  //   await expect(EditingPage.fieldNum1Popup).toExist()
  //   await expect(EditingPage.fieldNum1Popup).toHaveValue("") // Checking initial value
  //   await expect(EditingPage.fieldNum1Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
  //
  //   await EditingPage.fieldNum1Popup.waitForClickable()
  //   await EditingPage.fieldNum1Popup.selectByVisibleText("select1") // Select second item
  //   await expect(EditingPage.fieldNum1Popup).toHaveValue("10")
  //   await expect(EditingPage.fieldNum1Textfield).toHaveValue("10")
  //
  //   await EditingPage.fieldNum1Popup.waitForClickable()
  //   await EditingPage.fieldNum1Popup.selectByIndex(2) // Select third item
  //   await expect(EditingPage.fieldNum1Popup).toHaveValue("20")
  //   await expect(EditingPage.fieldNum1Textfield).toHaveValue("20")
  //
  //   await EditingPage.fieldNum1Popup.waitForClickable()
  //   await EditingPage.fieldNum1Popup.selectByIndex(0) // Select first item
  //   await expect(EditingPage.fieldNum1Popup).toHaveValue("0")
  //   await expect(EditingPage.fieldNum1Textfield).toHaveValue("0")
  //
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldNum1Popup).toHaveValue("0")
  //   await expect(EditingPage.fieldNum1Textfield).toHaveValue("0")
  // })
  // /*
  // Summary: popup menu with nullable integer field
  // Condition: The editing test page with new created record opens.
  // Check-with: The popup menu of the field num2(integer, nullable) exist.
  // Check-with: The options of the popup menu is as expected.
  // Operation: Select the option item "select1".
  // Check-with: The popup menu has the value 10 which the value attribute of the option.
  // Check-with: The num2 text field also has the value 10.
  // Operation: Select the option item 2.
  // Check-with: The popup menu has the value 20 which the value attribute of the option.
  // Check-with: The num2 text field also has the value 20.
  // Operation: Select the first option item.
  // Check-with: The popup menu has the value "" which same as the value attribute of the option.
  // Check-with: The num2 text field also has the value "".
  // Operation: Reload the page with "Update" button.
  // Check-with: The popup menu has the value "".
  // Check-with: The num2 text field also has the value "".
  //  */
  // it('10.can edit the popup menu of integer field which is nullable.', async () => {
  //   await expect(EditingPage.fieldNum2Popup).toExist()
  //   await expect(EditingPage.fieldNum2Popup).toHaveValue("") // Checking initial value
  //   await expect(EditingPage.fieldNum2Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
  //
  //   await EditingPage.fieldNum2Popup.waitForClickable()
  //   await EditingPage.fieldNum2Popup.selectByVisibleText("select1") // Select second item
  //   await expect(EditingPage.fieldNum2Popup).toHaveValue("10")
  //   await expect(EditingPage.fieldNum2Textfield).toHaveValue("10")
  //
  //   await EditingPage.fieldNum2Popup.waitForClickable()
  //   await EditingPage.fieldNum2Popup.selectByIndex(2) // Select third item
  //   await expect(EditingPage.fieldNum2Popup).toHaveValue("20")
  //   await expect(EditingPage.fieldNum2Textfield).toHaveValue("20")
  //
  //   await EditingPage.fieldNum2Popup.waitForClickable()
  //   await EditingPage.fieldNum2Popup.selectByIndex(0) // Select first item
  //   await expect(EditingPage.fieldNum2Popup).toHaveValue("")
  //   await expect(EditingPage.fieldNum2Textfield).toHaveValue("")
  //
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldNum2Popup).toHaveValue("")
  //   await expect(EditingPage.fieldNum2Textfield).toHaveValue("")
  // })
  // /*
  // Summary: Text field with non-null float field
  // Condition: The editing test page with new created record opens.
  // Check-with: Exist the text field of the field float1(integer, not null).
  // Check-with: The float1 text field has the value "0".
  // Operation: Set a random number to float1 text field, and reload the page with "Update" button.
  // Check-with: The float1 text field has the value by set on the previous operation.
  // Operation: Set the value "" to float1 text field (i.e. clear it), and reload the page with "Update" button.
  // Check-with: The float1 text field has the value "0" by set on the previous operation.
  //  */
  // it('11.can edit the text field of float field which is NOT NULL.', async () => {
  //   await expect(EditingPage.fieldFloat1Textfield).toExist()
  //   await expect(EditingPage.fieldFloat1Textfield).toHaveValue("0") // Checking initial value
  //   const value = Math.trunc(Math.random() * 100000)/1000
  //   await EditingPage.fieldFloat1Textfield.setValue(value) // Set a value to the field
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat1Textfield).toHaveValue(String(value))
  //   await EditingPage.fieldFloat1Textfield.setValue("") // Clear the field
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat1Textfield).toHaveValue("0")
  // })
  // /*
  // Summary: Text field with nullable float field
  // Condition: The editing test page with new created record opens.
  // Check-with: Exist the text field of the field float2(float, nullable).
  // Check-with: The float2 text field has the value "".
  // Operation: Set a random number to float2 text field, and reload the page with "Update" button.
  // Check-with: The float2 text field has the value by set on the previous operation.
  // Operation: Set the value "" to float2 text field (i.e. clear it), and reload the page with "Update" button.
  // Check-with: The float2 text field has the value "" by set on the previous operation.
  //  */
  // it('12.can edit the text field of nullable float field.', async () => {
  //   await expect(EditingPage.fieldFloat2Textfield).toExist()
  //   await expect(EditingPage.fieldFloat2Textfield).toHaveValue("") // Checking initial value
  //   const value = Math.trunc(Math.random() * 100000)/1000
  //   await EditingPage.fieldFloat2Textfield.setValue(value) // Set a value to the field
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat2Textfield).toHaveValue(String(value))
  //   await EditingPage.fieldFloat2Textfield.setValue("") // Clear the field
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat2Textfield).toHaveValue("")
  // })
  // /*
  // Summary: Checkbox with non-null float field
  // Condition: The editing test page with new created record opens.
  // Check-with: The checkbox of the field float1(float, not null) is not selected.
  // Operation: Click the float1 checkbox and set to checked, and reload the page with "Update" button.
  // Check-with: The checkbox of the field float1 is selected.
  // Check-with: The float1 text field has the value 1 which the value attribute of the checkbox.
  // Operation: Click the float1 checkbox and set not to checked, and reload the page with "Update" button.
  // Check-with: The checkbox of the field float1 is not selected.
  // Check-with: The float1 text field has the value 0.
  //  */
  // it('13.can edit the checkbox of float field which is NOT NULL.', async () => {
  //   await expect(EditingPage.fieldFloat1Checkbox).toExist()
  //   await expect(EditingPage.fieldFloat1Checkbox).not.toBeSelected() // Checking initial value
  //   await EditingPage.fieldFloat1Checkbox.click() // ON
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat1Checkbox).toBeSelected()
  //   await expect(EditingPage.fieldFloat1Textfield).toHaveValue("1")
  //   await EditingPage.fieldFloat1Checkbox.click() // OFF
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat1Checkbox).not.toBeSelected()
  //   await expect(EditingPage.fieldFloat1Textfield).toHaveValue("0")
  // })
  // /*
  // Summary: Checkbox with nullable float field
  // Condition: The editing test page with new created record opens.
  // Check-with: The checkbox of the field float2(float, nullable) is not selected.
  // Operation: Click the float2 checkbox and set to checked, and reload the page with "Update" button.
  // Check-with: The checkbox of the field float2 is selected.
  // Check-with: The float2 text field has the value 1 which the value attribute of the checkbox.
  // Operation: Click the float2 checkbox and set not to checked, and reload the page with "Update" button.
  // Check-with: The checkbox of the field float2 is not selected.
  // Check-with: The float2 text field has the value "".
  //  */
  // it('14.can edit the checkbox of nullable float field.', async () => {
  //   await expect(EditingPage.fieldFloat2Checkbox).toExist()
  //   await expect(EditingPage.fieldFloat2Checkbox).not.toBeSelected() // Checking initial value
  //   await EditingPage.fieldFloat2Checkbox.click() // ON
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat2Checkbox).toBeSelected()
  //   await expect(EditingPage.fieldFloat2Textfield).toHaveValue("1")
  //   await EditingPage.fieldFloat2Checkbox.click() // OFF
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat2Checkbox).not.toBeSelected()
  //   await expect(EditingPage.fieldFloat2Textfield).toHaveValue("")
  // })
  // // Radio Buttons for non-integer type field is out of scope, ok?
  // /*
  // Summary: Radio buttons with non-null float field
  // Condition: The editing test page with new created record opens.
  // Check-with: The first button of the field float1(float, not null) is selected.
  // Check-with: The second button of the field float1 is not selected.
  // Operation: Click the first button and select it.
  // Check-with: The first button of the field float1 is selected.
  // Check-with: The second button of the field float1 is not selected.
  // Check-with: The float1 text field has the value 0 which the value attribute of the button.
  // Operation: Click the second button and select it.
  // Check-with: The first button of the field float1 is not selected.
  // Check-with: The second button of the field float1 is selected.
  // Check-with: The float1 text field has the value 1 which the value attribute of the button.
  //  */
  // it('15.can edit the radio buttons of float field which is NOT NULL.', async () => {
  //   await expect(EditingPage.fieldFloat1Radio[0]).toExist()
  //   await expect(EditingPage.fieldFloat1Radio[1]).toExist()
  //   await expect(EditingPage.fieldFloat1Radio[0]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldFloat1Radio[1]).not.toBeSelected() // Checking initial value
  //   await EditingPage.fieldFloat1Radio[0].click() // First button
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat1Radio[0]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldFloat1Radio[1]).not.toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldFloat1Textfield).toHaveValue("0")
  //   await EditingPage.fieldFloat1Radio[1].click() // Second button
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat1Radio[0]).not.toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldFloat1Radio[1]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldFloat1Textfield).toHaveValue("1")
  // })
  // // Radio Buttons for non-integer type field is out of scope, ok?
  // /*
  // Summary: Radio buttons with nullable float field
  // Condition: The editing test page with new created record opens.
  // Check-with: The first button of the field float2(float, nullable) is not selected.
  // Check-with: The second button of the field float2 is not selected.
  // Operation: Click the first button and select it.
  // Check-with: The first button of the field float2 is selected.
  // Check-with: The second button of the field float2 is not selected.
  // Check-with: The float2 text field has the value 0 which the value attribute of the button.
  // Operation: Click the second button and select it.
  // Check-with: The first button of the field float2 is not selected.
  // Check-with: The second button of the field float2 is selected.
  // Check-with: The float2 text field has the value 1 which the value attribute of the button.
  //  */
  // it('16.can edit the radio buttons of nullable float field.', async () => {
  //   await expect(EditingPage.fieldFloat2Radio[0]).toExist()
  //   await expect(EditingPage.fieldFloat2Radio[1]).toExist()
  //   await expect(EditingPage.fieldFloat2Radio[0]).not.toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldFloat2Radio[1]).not.toBeSelected() // Checking initial value
  //   await EditingPage.fieldFloat2Radio[0].click() // First button
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat2Radio[0]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldFloat2Radio[1]).not.toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldFloat2Textfield).toHaveValue("0")
  //   await EditingPage.fieldFloat2Radio[1].click() // Second button
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat2Radio[0]).not.toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldFloat2Radio[1]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldFloat2Textfield).toHaveValue("1")
  // })
  // /*
  // Summary: popup menu with non-null float field
  // Condition: The editing test page with new created record opens.
  // Check-with: The popup menu of the field float1(float, not null) exist.
  // Check-with: The options of the popup menu is as expected.
  // Operation: Select the option item "select1".
  // Check-with: The popup menu has the value 10 which the value attribute of the option.
  // Check-with: The float1 text field also has the value 10.
  // Operation: Select the option item 2.
  // Check-with: The popup menu has the value 20 which the value attribute of the option.
  // Check-with: The float1 text field also has the value 20.
  // Operation: Select the first option item.
  // Check-with: The popup menu has the value "" (the value attribute of the option is "").
  // Check-with: The float1 text field also has the value 0.
  //  */
  // it('17.can edit the popup menu of float field which is NOT NULL.', async () => {
  //   await expect(EditingPage.fieldFloat1Popup).toExist()
  //   await expect(EditingPage.fieldFloat1Popup).toHaveValue("") // Checking initial value
  //   await expect(EditingPage.fieldFloat1Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
  //   await EditingPage.fieldFloat1Popup.waitForClickable()
  //   await EditingPage.fieldFloat1Popup.selectByVisibleText("select1") // Select second item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat1Popup).toHaveValue("10")
  //   await expect(EditingPage.fieldFloat1Textfield).toHaveValue("10")
  //   await EditingPage.fieldFloat1Popup.waitForClickable()
  //   await EditingPage.fieldFloat1Popup.selectByIndex(2) // Select third item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat1Popup).toHaveValue("20")
  //   await expect(EditingPage.fieldFloat1Textfield).toHaveValue("20")
  //   await EditingPage.fieldFloat1Popup.waitForClickable()
  //   await EditingPage.fieldFloat1Popup.selectByIndex(0) // Select first item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat1Popup).toHaveValue("")
  //   await expect(EditingPage.fieldFloat1Textfield).toHaveValue("0")
  // })
  // /*
  // Summary: popup menu with nullable float field
  // Condition: The editing test page with new created record opens.
  // Check-with: The popup menu of the field float2(float, nullable) exist.
  // Check-with: The options of the popup menu is as expected.
  // Operation: Select the option item "select1".
  // Check-with: The popup menu has the value 10 which the value attribute of the option.
  // Check-with: The float2 text field also has the value 10.
  // Operation: Select the option item 2.
  // Check-with: The popup menu has the value 20 which the value attribute of the option.
  // Check-with: The float2 text field also has the value 20.
  // Operation: Select the first option item.
  // Check-with: The popup menu has the value "" (the value attribute of the option is "").
  // Check-with: The float2 text field also has the value "".
  //  */
  // it('18.can edit the popup menu of nullable float field.', async () => {
  //   await expect(EditingPage.fieldFloat2Popup).toExist()
  //   await expect(EditingPage.fieldFloat2Popup).toHaveValue("") // Checking initial value
  //   await expect(EditingPage.fieldFloat2Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
  //   await EditingPage.fieldFloat2Popup.waitForClickable()
  //   await EditingPage.fieldFloat2Popup.selectByVisibleText("select1") // Select second item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat2Popup).toHaveValue("10")
  //   await expect(EditingPage.fieldFloat2Textfield).toHaveValue("10")
  //   await EditingPage.fieldFloat2Popup.waitForClickable()
  //   await EditingPage.fieldFloat2Popup.selectByIndex(2) // Select third item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat2Popup).toHaveValue("20")
  //   await expect(EditingPage.fieldFloat2Textfield).toHaveValue("20")
  //   await EditingPage.fieldFloat2Popup.waitForClickable()
  //   await EditingPage.fieldFloat2Popup.selectByIndex(0) // Select first item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldFloat2Popup).toHaveValue("")
  //   await expect(EditingPage.fieldFloat2Textfield).toHaveValue("")
  // })
  // /*
  // Summary: Text field with non-null double field
  // Condition: The editing test page with new created record opens.
  // Check-with: Exist the text field of the field double1(double, not null).
  // Check-with: The double1 text field has the value "0".
  // Operation: Set a random number to double1 text field, and reload the page with "Update" button.
  // Check-with: The double1 text field has the value by set on the previous operation.
  // Operation: Set the value "" to double1 text field (i.e. clear it), and reload the page with "Update" button.
  // Check-with: The double1 text field has the value "0" by set on the previous operation.
  //  */
  // it('19.can edit the text field of double field which is NOT NULL.', async () => {
  //   await expect(EditingPage.fieldDouble1Textfield).toExist()
  //   await expect(EditingPage.fieldDouble1Textfield).toHaveValue("0") // Checking initial value
  //   const value = Math.random() * 10000000
  //   await EditingPage.fieldDouble1Textfield.setValue(value) // Set a value to the field
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble1Textfield).toHaveValue(String(value))
  //   await EditingPage.fieldDouble1Textfield.setValue("") // Clear the field
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble1Textfield).toHaveValue("0")
  // })
  // /*
  // Summary: Text field with nullable double field
  // Condition: The editing test page with new created record opens.
  // Check-with: Exist the text field of the field double2(double, nullable).
  // Check-with: The double2 text field has the value "".
  // Operation: Set a random number to double2 text field, and reload the page with "Update" button.
  // Check-with: The double2 text field has the value by set on the previous operation.
  // Operation: Set the value "" to double2 text field (i.e. clear it), and reload the page with "Update" button.
  // Check-with: The double2 text field has the value "" by set on the previous operation.
  //  */
  // it('20.can edit the text field of nullable double field.', async () => {
  //   await expect(EditingPage.fieldDouble2Textfield).toExist()
  //   await expect(EditingPage.fieldDouble2Textfield).toHaveValue("") // Checking initial value
  //   const value = Math.random() * 10000000
  //   await EditingPage.fieldDouble2Textfield.setValue(value) // Set a value to the field
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble2Textfield).toHaveValue(String(value))
  //   await EditingPage.fieldDouble2Textfield.setValue("") // Clear the field
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble2Textfield).toHaveValue("")
  // })
  // // Checkbox for non-integer type field is out of scope, ok?
  // /*
  // Summary: Checkbox with non-null double field
  // Condition: The editing test page with new created record opens.
  // Check-with: The checkbox of the field double1(double, not null) is not selected.
  // Operation: Click the double1 checkbox and set to checked, and reload the page with "Update" button.
  // Check-with: The checkbox of the field double1 is selected.
  // Check-with: The double1 text field has the value 1 which the value attribute of the checkbox.
  // Operation: Click the double1 checkbox and set not to checked, and reload the page with "Update" button.
  // Check-with: The checkbox of the field double1 is not selected.
  // Check-with: The double1 text field has the value 0.
  //  */
  // it('21.can edit the checkbox of double field which is NOT NULL.', async () => {
  //   await expect(EditingPage.fieldDouble1Checkbox).toExist()
  //   await expect(EditingPage.fieldDouble1Checkbox).not.toBeSelected() // Checking initial value
  //   await EditingPage.fieldDouble1Checkbox.waitForClickable() // ON
  //   await EditingPage.fieldDouble1Checkbox.click() // ON
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble1Checkbox).toBeSelected()
  //   await expect(EditingPage.fieldDouble1Textfield).toHaveValue("1")
  //   await EditingPage.fieldDouble1Checkbox.waitForClickable() // OFF
  //   await EditingPage.fieldDouble1Checkbox.click() // OFF
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble1Checkbox).not.toBeSelected()
  //   await expect(EditingPage.fieldDouble1Textfield).toHaveValue("0")
  // })
  // // Checkbox for non-integer type field is out of scope, ok?
  // /*
  // Summary: Checkbox with nullable double field
  // Condition: The editing test page with new created record opens.
  // Check-with: The checkbox of the field double2(double, nullable) is not selected.
  // Operation: Click the double2 checkbox and set to checked, and reload the page with "Update" button.
  // Check-with: The checkbox of the field double2 is selected.
  // Check-with: The double2 text field has the value 1 which the value attribute of the checkbox.
  // Operation: Click the double2 checkbox and set not to checked, and reload the page with "Update" button.
  // Check-with: The checkbox of the field double2 is not selected.
  // Check-with: The double2 text field has the value "".
  //  */
  // it('22.can edit the checkbox of nullable double field.', async () => {
  //   await expect(EditingPage.fieldDouble2Checkbox).toExist()
  //   await expect(EditingPage.fieldDouble2Checkbox).not.toBeSelected() // Checking initial value
  //   await EditingPage.fieldDouble2Checkbox.waitForClickable() // ON
  //   await EditingPage.fieldDouble2Checkbox.click() // ON
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble2Checkbox).toBeSelected()
  //   await expect(EditingPage.fieldDouble2Textfield).toHaveValue("1")
  //   await EditingPage.fieldDouble2Checkbox.waitForClickable() // OFF
  //   await EditingPage.fieldDouble2Checkbox.click() // OFF
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble2Checkbox).not.toBeSelected()
  //   await expect(EditingPage.fieldDouble2Textfield).toHaveValue("")
  // })
  // // Radio Buttons for non-integer type field is out of scope, ok?
  // /*
  // Summary: Radio buttons with non-null double field
  // Condition: The editing test page with new created record opens.
  // Check-with: The first button of the field double1(double, not null) is not selected.
  // Check-with: The second button of the field double1 is not selected.
  // Operation: Click the first button and select it.
  // Check-with: The first button of the field double1 is selected.
  // Check-with: The second button of the field double1 is not selected.
  // Check-with: The double1 text field has the value 0 which the value attribute of the button.
  // Operation: Click the second button and select it.
  // Check-with: The first button of the field double1 is selected.
  // Check-with: The second button of the field double1 is not selected.
  // Check-with: The double1 text field has the value 1 which the value attribute of the button.
  //  */
  // it('23.can edit the radio buttons of double field which is NOT NULL.', async () => {
  //   await expect(EditingPage.fieldDouble1Radio[0]).toExist()
  //   await expect(EditingPage.fieldDouble1Radio[1]).toExist()
  //   await expect(EditingPage.fieldDouble1Radio[0]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldDouble1Radio[1]).not.toBeSelected() // Checking initial value
  //   await EditingPage.fieldDouble1Radio[0].waitForClickable() // First button
  //   await EditingPage.fieldDouble1Radio[0].click() // First button
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble1Radio[0]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldDouble1Radio[1]).not.toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldDouble1Textfield).toHaveValue("0")
  //   await EditingPage.fieldDouble1Radio[1].waitForClickable() // Second button
  //   await EditingPage.fieldDouble1Radio[1].click() // Second button
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble1Radio[0]).not.toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldDouble1Radio[1]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldDouble1Textfield).toHaveValue("1")
  // })
  // /*
  // Summary: Radio buttons with nullable double field
  // Condition: The editing test page with new created record opens.
  // Check-with: The first button of the field double2(double, nullable) is not selected.
  // Check-with: The second button of the field double2 is not selected.
  // Operation: Click the first button and select it.
  // Check-with: The first button of the field double2 is selected.
  // Check-with: The second button of the field double2 is not selected.
  // Check-with: The double2 text field has the value 0 which the value attribute of the button.
  // Operation: Click the second button and select it.
  // Check-with: The first button of the field double2 is selected.
  // Check-with: The second button of the field double2 is not selected.
  // Check-with: The double2 text field has the value 1 which the value attribute of the button.
  //  */
  // it('24.can edit the radio buttons of nullable double field.', async () => {
  //   await expect(EditingPage.fieldDouble2Radio[0]).toExist()
  //   await expect(EditingPage.fieldDouble2Radio[1]).toExist()
  //   await expect(EditingPage.fieldDouble2Radio[0]).not.toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldDouble2Radio[1]).not.toBeSelected() // Checking initial value
  //   await EditingPage.fieldDouble2Radio[0].waitForClickable()
  //   await EditingPage.fieldDouble2Radio[0].click() // First button
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble2Radio[0]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldDouble2Radio[1]).not.toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldDouble2Textfield).toHaveValue("0")
  //   await EditingPage.fieldDouble2Radio[1].waitForClickable()
  //   await EditingPage.fieldDouble2Radio[1].click() // Second button
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble2Radio[0]).not.toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldDouble2Radio[1]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldDouble2Textfield).toHaveValue("1")
  // })
  // /*
  // Summary: popup menu with non-null double field
  // Condition: The editing test page with new created record opens.
  // Check-with: The popup menu of the field double1(double, not null) exist.
  // Check-with: The options of the popup menu is as expected.
  // Operation: Select the option item "select1".
  // Check-with: The popup menu has the value 10 which the value attribute of the option.
  // Check-with: The double1 text field also has the value 10.
  // Operation: Select the option item 2.
  // Check-with: The popup menu has the value 20 which the value attribute of the option.
  // Check-with: The double1 text field also has the value 20.
  // Operation: Select the first option item.
  // Check-with: The popup menu has the value "" (the value attribute of the option is "").
  // Check-with: The double1 text field also has the value 0.
  //  */
  // it('25.can edit the popup menu of double field which is NOT NULL.', async () => {
  //   await expect(EditingPage.fieldDouble1Popup).toExist()
  //   await expect(EditingPage.fieldDouble1Popup).toHaveValue("") // Checking initial value
  //   await expect(EditingPage.fieldDouble1Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
  //   await EditingPage.fieldDouble1Popup.waitForClickable()
  //   await EditingPage.fieldDouble1Popup.selectByVisibleText("select1") // Select second item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble1Popup).toHaveValue("10")
  //   await expect(EditingPage.fieldDouble1Textfield).toHaveValue("10")
  //   await EditingPage.fieldDouble1Popup.waitForClickable()
  //   await EditingPage.fieldDouble1Popup.selectByIndex(2) // Select third item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble1Popup).toHaveValue("20")
  //   await expect(EditingPage.fieldDouble1Textfield).toHaveValue("20")
  //   await EditingPage.fieldDouble1Popup.waitForClickable()
  //   await EditingPage.fieldDouble1Popup.selectByIndex(0) // Select first item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble1Popup).toHaveValue("")
  //   await expect(EditingPage.fieldDouble1Textfield).toHaveValue("0")
  // })
  // /*
  // Summary: popup menu with non-null double field
  // Condition: The editing test page with new created record opens.
  // Check-with: The popup menu of the field double2(double, not null) exist.
  // Check-with: The options of the popup menu is as expected.
  // Operation: Select the option item "select1".
  // Check-with: The popup menu has the value 10 which the value attribute of the option.
  // Check-with: The double2 text field also has the value 10.
  // Operation: Select the option item 2.
  // Check-with: The popup menu has the value 20 which the value attribute of the option.
  // Check-with: The double2 text field also has the value 20.
  // Operation: Select the first option item.
  // Check-with: The popup menu has the value "" (the value attribute of the option is "").
  // Check-with: The double2 text field also has the value "".
  //  */
  // it('26.can edit the popup menu of nullable double field.', async () => {
  //   await expect(EditingPage.fieldDouble2Popup).toExist()
  //   await expect(EditingPage.fieldDouble2Popup).toHaveValue("") // Checking initial value
  //   await expect(EditingPage.fieldDouble2Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
  //   await EditingPage.fieldDouble2Popup.waitForClickable()
  //   await EditingPage.fieldDouble2Popup.selectByVisibleText("select1") // Select second item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble2Popup).toHaveValue("10")
  //   await expect(EditingPage.fieldDouble2Textfield).toHaveValue("10")
  //   await EditingPage.fieldDouble2Popup.waitForClickable()
  //   await EditingPage.fieldDouble2Popup.selectByIndex(2) // Select third item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble2Popup).toHaveValue("20")
  //   await expect(EditingPage.fieldDouble2Textfield).toHaveValue("20")
  //   await EditingPage.fieldDouble2Popup.waitForClickable()
  //   await EditingPage.fieldDouble2Popup.selectByIndex(0) // Select first item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldDouble2Popup).toHaveValue("")
  //   await expect(EditingPage.fieldDouble2Textfield).toHaveValue("")
  // })
  // /*
  // Summary: Text field with non-null bool field
  // Condition: The editing test page with new created record opens.
  // Check-with: Exist the text field of the field bool1(bool, not null).
  // Check-with: The bool1 text field has the value "0".
  // Operation: Set a random number to bool1 text field, and reload the page with "Update" button.
  // Check-with: The bool1 text field has the value by set on the previous operation.
  // Operation: Set the value "" to bool1 text field (i.e. clear it), and reload the page with "Update" button.
  // Check-with: The bool1 text field has the value "0" by set on the previous operation.
  //  */
  // it('27.can edit the text field of boolean field which is NOT NULL.', async () => {
  //   await expect(EditingPage.fieldBool1Textfield).toExist()
  //   await expect(EditingPage.fieldBool1Textfield).toHaveValue("") // Checking initial value
  //   const value = 1
  //   await EditingPage.fieldBool1Textfield.setValue(value) // Set a value to the field
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool1Textfield).toHaveValue("true")
  //   await EditingPage.fieldBool1Textfield.setValue("") // Clear the field
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool1Textfield).toHaveValue("")
  // })
  // /*
  // Summary: Text field with nullable boolean field
  // Condition: The editing test page with new created record opens.
  // Check-with: Exist the text field of the field bool2(boolean, nullable).
  // Check-with: The bool2 text field has the value "".
  // Operation: Set a random number to bool2 text field, and reload the page with "Update" button.
  // Check-with: The bool2 text field has the value by set on the previous operation.
  // Operation: Set the value "" to bool2 text field (i.e. clear it), and reload the page with "Update" button.
  // Check-with: The bool2 text field has the value "" by set on the previous operation.
  //  */
  // it('28.can edit the text field of nullable boolean field.', async () => {
  //   await expect(EditingPage.fieldBool2Textfield).toExist()
  //   await expect(EditingPage.fieldBool2Textfield).toHaveValue("") // Checking initial value
  //   const value = 1
  //   await EditingPage.fieldBool2Textfield.setValue(value) // Set a value to the field
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool2Textfield).toHaveValue("true")
  //   await EditingPage.fieldBool2Textfield.setValue("") // Clear the field
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool2Textfield).toHaveValue("")
  // })
  // /*
  // Summary: Checkbox with non-null boolean field
  // Condition: The editing test page with new created record opens.
  // Check-with: The checkbox of the field bool1(boolean, not null) is not selected.
  // Operation: Click the bool1 checkbox and set to checked, and reload the page with "Update" button.
  // Check-with: The checkbox of the field bool1 is selected.
  // Check-with: The bool1 text field has the value 1 which the value attribute of the checkbox.
  // Operation: Click the bool1 checkbox and set not to checked, and reload the page with "Update" button.
  // Check-with: The checkbox of the field bool1 is not selected.
  // Check-with: The bool1 text field has the value 0.
  //  */
  // it('29.can edit the checkbox of boolean field which is NOT NULL.', async () => {
  //   await EditingPage.fieldBool1Textfield.setValue("") // Clear the field
  //   await expect(EditingPage.fieldBool1Checkbox).toExist()
  //   await expect(EditingPage.fieldBool1Checkbox).not.toBeSelected() // Checking initial value
  //   await EditingPage.fieldBool1Checkbox.click() // ON
  //   await browser.pause(waiting)
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool1Checkbox).toBeSelected()
  //   await expect(EditingPage.fieldBool1Textfield).toHaveValue("true")
  //   await EditingPage.fieldBool1Checkbox.waitForClickable() // OFF
  //   await EditingPage.fieldBool1Checkbox.click() // OFF
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool1Checkbox).not.toBeSelected()
  //   await expect(EditingPage.fieldBool1Textfield).toHaveValue("")
  // })
  // /*
  // Summary: Checkbox with nullable boolean field
  // Condition: The editing test page with new created record opens.
  // Check-with: The checkbox of the field bool2(boolean, nullable) is not selected.
  // Operation: Click the bool2 checkbox and set to checked, and reload the page with "Update" button.
  // Check-with: The checkbox of the field bool2 is selected.
  // Check-with: The bool2 text field has the value 1 which the value attribute of the checkbox.
  // Operation: Click the bool2 checkbox and set not to checked, and reload the page with "Update" button.
  // Check-with: The checkbox of the field bool2 is not selected.
  // Check-with: The bool2 text field has the value "".
  //  */
  // it('30.can edit the checkbox of nullable boolean field.', async () => {
  //   await EditingPage.fieldBool2Textfield.setValue("") // Clear the field
  //   await expect(EditingPage.fieldBool2Checkbox).toExist()
  //   await expect(EditingPage.fieldBool2Checkbox).not.toBeSelected() // Checking initial value
  //   await EditingPage.fieldBool2Checkbox.waitForClickable() // ON
  //   await EditingPage.fieldBool2Checkbox.click() // ON
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool2Checkbox).toBeSelected()
  //   await expect(EditingPage.fieldBool2Textfield).toHaveValue("true")
  //   await EditingPage.fieldBool2Checkbox.waitForClickable() // OFF
  //   await EditingPage.fieldBool2Checkbox.click() // OFF
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool2Checkbox).not.toBeSelected()
  //   await expect(EditingPage.fieldBool2Textfield).toHaveValue("")
  // })
  // /*
  // Summary: Radio buttons with non-null boolean field
  // Condition: The editing test page with new created record opens.
  // Check-with: The first button of the field bool1(boolean, not null) is selected.
  // Check-with: The second button of the field bool1 is not selected.
  // Operation: Click the first button and select it.
  // Check-with: The first button of the field bool1 is selected.
  // Check-with: The second button of the field bool1 is not selected.
  // Check-with: The bool1 text field has the value "" which the value attribute of the button.
  // Operation: Click the second button and select it.
  // Check-with: The first button of the field bool1 is not selected.
  // Check-with: The second button of the field bool1 is selected.
  // Check-with: The bool1 text field has the value "true" which the value attribute of the button.
  //  */
  // it('31.can edit the radio buttons of boolean field which is NOT NULL.', async () => {
  //   await EditingPage.fieldBool1Textfield.setValue("") // Clear the field
  //   await expect(EditingPage.fieldBool1Radio[0]).toExist()
  //   await expect(EditingPage.fieldBool1Radio[1]).toExist()
  //   await expect(EditingPage.fieldBool1Radio[0]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldBool1Radio[1]).not.toBeSelected() // Checking initial value
  //   await EditingPage.fieldBool1Radio[0].waitForClickable() // First button
  //   await EditingPage.fieldBool1Radio[0].click() // First button
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool1Radio[0]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldBool1Radio[1]).not.toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldBool1Textfield).toHaveValue("")
  //   await EditingPage.fieldBool1Radio[1].waitForClickable() // Second button
  //   await EditingPage.fieldBool1Radio[1].click() // Second button
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool1Radio[0]).not.toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldBool1Radio[1]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldBool1Textfield).toHaveValue("true")
  // })
  // /*
  // Summary: Radio buttons with nullable boolean field
  // Condition: The editing test page with new created record opens.
  // Check-with: The first button of the field bool2(boolean, nullable) is selected.
  // Check-with: The second button of the field bool2 is not selected.
  // Operation: Click the first button and select it.
  // Check-with: The first button of the field bool2 is selected.
  // Check-with: The second button of the field bool2 is not selected.
  // Check-with: The bool2 text field has the value "" which the value attribute of the button.
  // Operation: Click the second button and select it.
  // Check-with: The first button of the field bool2 is not selected.
  // Check-with: The second button of the field bool2 is selected.
  // Check-with: The bool2 text field has the value "true" which the value attribute of the button.
  //  */
  // it('32.can edit the radio buttons of nullable boolean field.', async () => {
  //   await EditingPage.fieldBool2Textfield.setValue("") // Clear the field
  //   await expect(EditingPage.fieldBool2Radio[0]).toExist()
  //   await expect(EditingPage.fieldBool2Radio[1]).toExist()
  //   await expect(EditingPage.fieldBool2Radio[0]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldBool2Radio[1]).not.toBeSelected() // Checking initial value
  //   await EditingPage.fieldBool2Radio[0].waitForClickable() // First button
  //   await EditingPage.fieldBool2Radio[0].click() // First button
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool2Radio[0]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldBool2Radio[1]).not.toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldBool2Textfield).toHaveValue("")
  //   await EditingPage.fieldBool2Radio[1].waitForClickable() // Second button
  //   await EditingPage.fieldBool2Radio[1].click() // Second button
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool2Radio[0]).not.toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldBool2Radio[1]).toBeSelected() // Checking initial value
  //   await expect(EditingPage.fieldBool2Textfield).toHaveValue("true")
  // })
  // /*
  // Summary: popup menu with non-null boolean field
  // Condition: The editing test page with new created record opens.
  // Check-with: The popup menu of the field bool1(boolean, not null) exist.
  // Check-with: The options of the popup menu is as expected.
  // Operation: Select the option item "select1", and click "Update" button.
  // Check-with: The popup menu has the value "".
  // Check-with: The bool1 text field also has the value "true".
  // Operation: Select the option item 2, and click "Update" button.
  // Check-with: The popup menu has the value "".
  // Check-with: The bool1 text field also has the value "true".
  // Operation: Select the first option item, and click "Update" button.
  // Check-with: The popup menu has the value "".
  // Check-with: The bool1 text field also has the value "".
  //  */
  // it('33.can edit the popup menu of boolean field which is NOT NULL.', async () => {
  //   await expect(EditingPage.fieldBool1Popup).toExist()
  //   await expect(EditingPage.fieldBool1Popup).toHaveValue("") // Checking initial value
  //   await expect(EditingPage.fieldBool1Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
  //   await EditingPage.fieldBool1Popup.waitForClickable()
  //   await EditingPage.fieldBool1Popup.selectByVisibleText("select1") // Select second item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool1Popup).toHaveValue("")
  //   await expect(EditingPage.fieldBool1Textfield).toHaveValue("true")
  //   await EditingPage.fieldBool1Popup.waitForClickable()
  //   await EditingPage.fieldBool1Popup.selectByIndex(2) // Select third item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool1Popup).toHaveValue("")
  //   await expect(EditingPage.fieldBool1Textfield).toHaveValue("true")
  //   await EditingPage.fieldBool1Popup.waitForClickable()
  //   await EditingPage.fieldBool1Popup.selectByIndex(0) // Select first item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool1Popup).toHaveValue("")
  //   await expect(EditingPage.fieldBool1Textfield).toHaveValue("")
  // })
  // /*
  // Summary: popup menu with non-null boolean field
  // Condition: The editing test page with new created record opens.
  // Check-with: The popup menu of the field bool1(boolean, not null) exist.
  // Check-with: The options of the popup menu is as expected.
  // Operation: Select the option item "select1", and click "Update" button.
  // Check-with: The popup menu has the value "".
  // Check-with: The bool1 text field also has the value "true".
  // Operation: Select the option item 2, and click "Update" button.
  // Check-with: The popup menu has the value "".
  // Check-with: The bool1 text field also has the value "true".
  // Operation: Select the first option item, and click "Update" button.
  // Check-with: The popup menu has the value "".
  // Check-with: The bool1 text field also has the value "".
  //  */
  // it('34.can edit the popup menu of nullable boolean field.', async () => {
  //   await expect(EditingPage.fieldBool2Popup).toExist()
  //   await expect(EditingPage.fieldBool2Popup).toHaveValue("") // Checking initial value
  //   await expect(EditingPage.fieldBool2Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
  //   await EditingPage.fieldBool2Popup.waitForClickable()
  //   await EditingPage.fieldBool2Popup.selectByVisibleText("select1") // Select second item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool2Popup).toHaveValue("")
  //   await expect(EditingPage.fieldBool2Textfield).toHaveValue("true")
  //   await EditingPage.fieldBool2Popup.waitForClickable()
  //   await EditingPage.fieldBool2Popup.selectByIndex(2) // Select third item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool2Popup).toHaveValue("")
  //   await expect(EditingPage.fieldBool2Textfield).toHaveValue("true")
  //   await EditingPage.fieldBool2Popup.waitForClickable()
  //   await EditingPage.fieldBool2Popup.selectByIndex(0) // Select first item
  //   await EditingPage.navigatorUpdateButton.waitForClickable()
  //   await EditingPage.navigatorUpdateButton.click()
  //   await browser.pause(waiting)
  //   await expect(EditingPage.fieldBool2Popup).toHaveValue("")
  //   await expect(EditingPage.fieldBool2Textfield).toHaveValue("")
  // })

  const integerTest = require('./editing_page_tests/integer')
  integerTest(EditingPage)
  const realTest = require('./editing_page_tests/real')
  realTest(EditingPage)
  const booleanTest = require('./editing_page_tests/boolean')
  booleanTest(EditingPage)
  const stringTest = require('./editing_page_tests/string')
  stringTest(EditingPage)
  const datetimeTest = require('./editing_page_tests/datetime')
  datetimeTest(EditingPage)


})

