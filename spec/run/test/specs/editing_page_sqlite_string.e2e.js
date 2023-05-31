const EditingPage = require('../pageobjects/editing_page_sqlite.page');

const waiting = 2000
describe('Editing Page', () => {
  it('can open with the valid title.', async () => {
    await EditingPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Editing/SQLite"/*'INTER-Mediator - サンプル - フォーム形式/MySQL'*/)
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
    await EditingPage.navigatorInsertButton.click()
    await EditingPage.navigatorInsertButton.waitForClickable()
  })
  it('can edit the text field of varchar field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldVc1Textfield).toExist()
    await expect(EditingPage.fieldVc1Textfield).toHaveValue("") // Checking initial value
    const value = Math.trunc(Math.random() * 10000000)
    await EditingPage.fieldVc1Textfield.setValue(value) // Set a value to the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc1Textfield).toHaveValue(String(value))
    await EditingPage.fieldVc1Textfield.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc1Textfield).toHaveValue("")
  })
  it('can edit the text field of nullable varchar field.', async () => {
    await expect(EditingPage.fieldVc2Textfield).toExist()
    await expect(EditingPage.fieldVc2Textfield).toHaveValue("") // Checking initial value
    const value = Math.trunc(Math.random() * 10000000)
    await EditingPage.fieldVc2Textfield.setValue(value) // Set a value to the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc2Textfield).toHaveValue(String(value))
    await EditingPage.fieldVc2Textfield.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc2Textfield).toHaveValue("")
  })
  // Checkbox for non-integer type field is out of scope, ok?
  it('can edit the checkbox of varchar field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldVc1Checkbox).toExist()
    await expect(EditingPage.fieldVc1Checkbox).not.toBeSelected() // Checking initial value
    await EditingPage.fieldVc1Checkbox.click() // ON
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await EditingPage.fieldVc1Checkbox.waitForExist()
    await expect(EditingPage.fieldVc1Checkbox).toBeSelected()
    await expect(EditingPage.fieldVc1Textfield).toHaveValue("ON")
    await EditingPage.fieldVc1Checkbox.click() // OFF
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc1Checkbox).not.toBeSelected()
    await expect(EditingPage.fieldVc1Textfield).toHaveValue("")
  })
  // Checkbox for non-integer type field is out of scope, ok?
  it('can edit the checkbox of nullable varchar field.', async () => {
    await expect(EditingPage.fieldVc2Checkbox).toExist()
    await expect(EditingPage.fieldVc2Checkbox).not.toBeSelected() // Checking initial value
    await EditingPage.fieldVc2Checkbox.click() // ON
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc2Checkbox).toBeSelected()
    await expect(EditingPage.fieldVc2Textfield).toHaveValue("ON")
    await EditingPage.fieldVc2Checkbox.click() // OFF
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc2Checkbox).not.toBeSelected()
    await expect(EditingPage.fieldVc2Textfield).toHaveValue("")
  })
  // Radio Buttons for non-integer type field is out of scope, ok?
  it('can edit the radio buttons of varchar field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldVc1Radio[0]).toExist()
    await expect(EditingPage.fieldVc1Radio[1]).toExist()
    await expect(EditingPage.fieldVc1Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldVc1Radio[1]).not.toBeSelected() // Checking initial value
    await EditingPage.fieldVc1Radio[0].click() // First button
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc1Radio[0]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldVc1Radio[1]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldVc1Textfield).toHaveValue("select1")
    await EditingPage.fieldVc1Radio[1].click() // Second button
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc1Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldVc1Radio[1]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldVc1Textfield).toHaveValue("select2")
  })
  // Radio Buttons for non-integer type field is out of scope, ok?
it('can edit the radio buttons of nullable varchar field.', async () => {
    await expect(EditingPage.fieldVc2Radio[0]).toExist()
    await expect(EditingPage.fieldVc2Radio[1]).toExist()
    await expect(EditingPage.fieldVc2Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldVc2Radio[1]).not.toBeSelected() // Checking initial value
    await EditingPage.fieldVc2Radio[0].click() // First button
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc2Radio[0]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldVc2Radio[1]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldVc2Textfield).toHaveValue("select1")
    await EditingPage.fieldVc2Radio[1].click() // Second button
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc2Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldVc2Radio[1]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldVc2Textfield).toHaveValue("select2")
  })
  it('can edit the popup menu of varchar field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldVc1Popup).toExist()
    await expect(EditingPage.fieldVc1Popup).toHaveValue("select2") // Checking initial value
    await expect(EditingPage.fieldVc1Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
    await EditingPage.fieldVc1Popup.selectByVisibleText("select1") // Select second item
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc1Popup).toHaveValue("select1")
    await expect(EditingPage.fieldVc1Textfield).toHaveValue("select1")
    await EditingPage.fieldVc1Popup.selectByIndex(2) // Select third item
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc1Popup).toHaveValue("select2")
    await expect(EditingPage.fieldVc1Textfield).toHaveValue("select2")
    await EditingPage.fieldVc1Popup.selectByIndex(0) // Select first item
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc1Popup).toHaveValue("")
    await expect(EditingPage.fieldVc1Textfield).toHaveValue("")
  })
  it('can edit the popup menu of nullable varchar field.', async () => {
    await expect(EditingPage.fieldVc2Popup).toExist()
    await expect(EditingPage.fieldVc2Popup).toHaveValue("select2") // Checking initial value
    await expect(EditingPage.fieldVc2Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
    await EditingPage.fieldVc2Popup.selectByVisibleText("select1") // Select second item
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc2Popup).toHaveValue("select1")
    await expect(EditingPage.fieldVc2Textfield).toHaveValue("select1")
    await EditingPage.fieldVc2Popup.selectByIndex(2) // Select third item
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc2Popup).toHaveValue("select2")
    await expect(EditingPage.fieldVc2Textfield).toHaveValue("select2")
    await EditingPage.fieldVc2Popup.selectByIndex(0) // Select first item
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc2Popup).toHaveValue("")
    await expect(EditingPage.fieldVc2Textfield).toHaveValue("")
  })
  it('can edit the textarea of varchar field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldVc1Textarea).toExist()
    await expect(EditingPage.fieldVc1Textarea).toHaveValue("") // Checking initial value
    const value = "AAAA\n3333333\nイエスマンに未来はない\n#$#$#$#$"
    await EditingPage.fieldVc1Textarea.setValue(value) // Set a value to the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc1Textarea).toHaveValue(String(value))
    await EditingPage.fieldVc1Textarea.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc1Textarea).toHaveValue("")
  })
  it('can edit the textarea of varchar field which is nullable text field.', async () => {
    await expect(EditingPage.fieldVc2Textarea).toExist()
    await expect(EditingPage.fieldVc2Textarea).toHaveValue("") // Checking initial value
    const value = "AAAA\n3333333\nイエスマンに未来はない\n#$#$#$#$"
    await EditingPage.fieldVc2Textarea.setValue(value) // Set a value to the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc2Textarea).toHaveValue(String(value))
    await EditingPage.fieldVc2Textarea.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldVc2Textarea).toHaveValue("")
  })
  it('can edit the text field of text field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldText1Textfield).toExist()
    await expect(EditingPage.fieldText1Textfield).toHaveValue("") // Checking initial value
    const value = Math.trunc(Math.random() * 10000000)
    await EditingPage.fieldText1Textfield.setValue(value) // Set a value to the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText1Textfield).toHaveValue(String(value))
    await EditingPage.fieldText1Textfield.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText1Textfield).toHaveValue("")
  })
  it('can edit the text field of nullable text field.', async () => {
    await expect(EditingPage.fieldText2Textfield).toExist()
    await expect(EditingPage.fieldText2Textfield).toHaveValue("") // Checking initial value
    const value = Math.trunc(Math.random() * 10000000)
    await EditingPage.fieldText2Textfield.setValue(value) // Set a value to the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText2Textfield).toHaveValue(String(value))
    await EditingPage.fieldText2Textfield.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText2Textfield).toHaveValue("")
  })
  // Checkbox for non-integer type field is out of scope, ok?
  it('can edit the checkbox of text field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldText1Checkbox).toExist()
    await expect(EditingPage.fieldText1Checkbox).not.toBeSelected() // Checking initial value
    await EditingPage.fieldText1Checkbox.click() // ON
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText1Checkbox).toBeSelected()
    await expect(EditingPage.fieldText1Textfield).toHaveValue("ON")
    await EditingPage.fieldText1Checkbox.click() // OFF
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText1Checkbox).not.toBeSelected()
    await expect(EditingPage.fieldText1Textfield).toHaveValue("")
  })
  // Checkbox for non-integer type field is out of scope, ok?
it('can edit the checkbox of nullable text field.', async () => {
    await expect(EditingPage.fieldText2Checkbox).toExist()
    await expect(EditingPage.fieldText2Checkbox).not.toBeSelected() // Checking initial value
    await EditingPage.fieldText2Checkbox.click() // ON
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText2Checkbox).toBeSelected()
    await expect(EditingPage.fieldText2Textfield).toHaveValue("ON")
    await EditingPage.fieldText2Checkbox.click() // OFF
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText2Checkbox).not.toBeSelected()
    await expect(EditingPage.fieldText2Textfield).toHaveValue("")
  })
  // Radio Buttons for non-integer type field is out of scope, ok?
  it('can edit the radio buttons of text field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldText1Radio[0]).toExist()
    await expect(EditingPage.fieldText1Radio[1]).toExist()
    await expect(EditingPage.fieldText1Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldText1Radio[1]).not.toBeSelected() // Checking initial value
    await EditingPage.fieldText1Radio[0].click() // First button
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText1Radio[0]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldText1Radio[1]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldText1Textfield).toHaveValue("select1")
    await EditingPage.fieldText1Radio[1].click() // Second button
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText1Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldText1Radio[1]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldText1Textfield).toHaveValue("select2")
  })
  // Radio Buttons for non-integer type field is out of scope, ok?
  it('can edit the radio buttons of nullable text field.', async () => {
    await expect(EditingPage.fieldText2Radio[0]).toExist()
    await expect(EditingPage.fieldText2Radio[1]).toExist()
    await expect(EditingPage.fieldText2Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldText2Radio[1]).not.toBeSelected() // Checking initial value
    await EditingPage.fieldText2Radio[0].click() // First button
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText2Radio[0]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldText2Radio[1]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldText2Textfield).toHaveValue("select1")
    await EditingPage.fieldText2Radio[1].click() // Second button
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText2Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldText2Radio[1]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldText2Textfield).toHaveValue("select2")
  })
  it('can edit the popup menu of text field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldText1Popup).toExist()
    await expect(EditingPage.fieldText1Popup).toHaveValue("select2") // Checking initial value
    await expect(EditingPage.fieldText1Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
    await EditingPage.fieldText1Popup.selectByVisibleText("select1") // Select second item
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText1Popup).toHaveValue("select1")
    await expect(EditingPage.fieldText1Textfield).toHaveValue("select1")
    await EditingPage.fieldText1Popup.selectByIndex(2) // Select third item
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText1Popup).toHaveValue("select2")
    await expect(EditingPage.fieldText1Textfield).toHaveValue("select2")
    await EditingPage.fieldText1Popup.selectByIndex(0) // Select first item
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText1Popup).toHaveValue("")
    await expect(EditingPage.fieldText1Textfield).toHaveValue("")
  })
  it('can edit the popup menu of nullable text field.', async () => {
    await expect(EditingPage.fieldText2Popup).toExist()
    await expect(EditingPage.fieldText2Popup).toHaveValue("select2") // Checking initial value
    await expect(EditingPage.fieldText2Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
    await EditingPage.fieldText2Popup.selectByVisibleText("select1") // Select second item
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText2Popup).toHaveValue("select1")
    await expect(EditingPage.fieldText2Textfield).toHaveValue("select1")
    await EditingPage.fieldText2Popup.selectByIndex(2) // Select third item
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText2Popup).toHaveValue("select2")
    await expect(EditingPage.fieldText2Textfield).toHaveValue("select2")
    await EditingPage.fieldText2Popup.selectByIndex(0) // Select first item
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText2Popup).toHaveValue("")
    await expect(EditingPage.fieldText2Textfield).toHaveValue("")
  })
  it('can edit the textarea of text field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldText1Textarea).toExist()
    await expect(EditingPage.fieldText1Textarea).toHaveValue("") // Checking initial value
    const value = "AAAA\n3333333\nイエスマンに未来はない\n#$#$#$#$"
    await EditingPage.fieldText1Textarea.setValue(value) // Set a value to the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText1Textarea).toHaveValue(String(value))
    await EditingPage.fieldText1Textarea.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText1Textarea).toHaveValue("")
  })
  it('can edit the textarea of text field which is nullable text field.', async () => {
    await expect(EditingPage.fieldText2Textarea).toExist()
    await expect(EditingPage.fieldText2Textarea).toHaveValue("") // Checking initial value
    const value = "AAAA\n3333333\nイエスマンに未来はない\n#$#$#$#$"
    await EditingPage.fieldText2Textarea.setValue(value) // Set a value to the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText2Textarea).toHaveValue(String(value))
    await EditingPage.fieldText2Textarea.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldText2Textarea).toHaveValue("")
  })
})


