const EditingPage = require('../pageobjects/editing_page_mysql.page');

const waiting = 500

let pageTitle = "INTER-Mediator - Sample - Editing/MySQL"

describe('Editing Page Numeric Fields', () => {
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
  it('can edit the text field of integer field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldNum1Textfield).toExist()
    await expect(EditingPage.fieldNum1Textfield).toHaveValue("0") // Checking initial value
    const value = Math.trunc(Math.random() * 10000000)
    await EditingPage.fieldNum1Textfield.setValue(value) // Set a value to the field
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldNum1Textfield).toHaveValue(String(value))
    await EditingPage.fieldNum1Textfield.setValue("") // Clear the field
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldNum1Textfield).toHaveValue("0")
  })
  it('can edit the text field of nullable integer field.', async () => {
    await expect(EditingPage.fieldNum2Textfield).toExist()
    await expect(EditingPage.fieldNum2Textfield).toHaveValue("") // Checking initial value
    const value = Math.trunc(Math.random() * 10000000)
    await EditingPage.fieldNum2Textfield.setValue(value) // Set a value to the field
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldNum2Textfield).toHaveValue(String(value))
    await EditingPage.fieldNum2Textfield.setValue("") // Clear the field
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldNum2Textfield).toHaveValue("")
  })
  it('can edit the checkbox of integer field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldNum1Checkbox).toExist()
    await expect(EditingPage.fieldNum1Checkbox).not.toBeSelected() // Checking initial value
    await EditingPage.fieldNum1Checkbox.click() // ON
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldNum1Checkbox).toBeSelected()
    await expect(EditingPage.fieldNum1Textfield).toHaveValue("1")
    await EditingPage.fieldNum1Checkbox.click() // OFF
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldNum1Checkbox).not.toBeSelected()
    await expect(EditingPage.fieldNum1Textfield).toHaveValue("0")
  })
  it('can edit the checkbox of nullable integer field.', async () => {
    await expect(EditingPage.fieldNum2Checkbox).toExist()
    await expect(EditingPage.fieldNum2Checkbox).not.toBeSelected() // Checking initial value
    await EditingPage.fieldNum2Checkbox.click() // ON
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldNum2Checkbox).toBeSelected()
    await expect(EditingPage.fieldNum2Textfield).toHaveValue("1")
    await EditingPage.fieldNum2Checkbox.click() // OFF
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldNum2Checkbox).not.toBeSelected()
    await expect(EditingPage.fieldNum2Textfield).toHaveValue("")
  })
  it('can edit the radio buttons of integer field which is NOT NULL.', async () => {
    const buttons = await EditingPage.fieldNum1Radio
    await expect(buttons[0]).toExist()
    await expect(buttons[1]).toExist()
    await expect(buttons[0]).not.toBeSelected() // Checking initial value
    await expect(buttons[1]).not.toBeSelected() // Checking initial value
    await buttons[0].click() // First button
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(buttons[0]).toBeSelected() // Checking initial value
    await expect(buttons[1]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldNum1Textfield).toHaveValue("1")
    await buttons[1].click() // Second button
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(buttons[0]).not.toBeSelected() // Checking initial value
    await expect(buttons[1]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldNum1Textfield).toHaveValue("2")
  })
  it('can edit the radio buttons of nullable integer field.', async () => {
    const buttons = await EditingPage.fieldNum2Radio
    await expect(buttons[0]).toExist()
    await expect(buttons[1]).toExist()
    await expect(buttons[0]).not.toBeSelected() // Checking initial value
    await expect(buttons[1]).not.toBeSelected() // Checking initial value
    await buttons[0].click() // First button
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(buttons[0]).toBeSelected() // Checking initial value
    await expect(buttons[1]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldNum2Textfield).toHaveValue("1")
    await buttons[1].click() // Second button
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(buttons[0]).not.toBeSelected() // Checking initial value
    await expect(buttons[1]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldNum2Textfield).toHaveValue("2")
  })
  it('can edit the popup menu of integer field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldNum1Popup).toExist()
    await expect(EditingPage.fieldNum1Popup).toHaveValue("") // Checking initial value
    await expect(EditingPage.fieldNum1Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
    await EditingPage.fieldNum1Popup.selectByVisibleText("select1") // Select second item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldNum1Popup).toHaveValue("10")
    await expect(EditingPage.fieldNum1Textfield).toHaveValue("10")
    await EditingPage.fieldNum1Popup.selectByIndex(2) // Select third item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldNum1Popup).toHaveValue("20")
    await expect(EditingPage.fieldNum1Textfield).toHaveValue("20")
    await EditingPage.fieldNum1Popup.selectByIndex(0) // Select first item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldNum1Popup).toHaveValue("0")
    await expect(EditingPage.fieldNum1Textfield).toHaveValue("0")
  })
  it('can edit the popup menu of integer field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldNum2Popup).toExist()
    await expect(EditingPage.fieldNum2Popup).toHaveValue("") // Checking initial value
    await expect(EditingPage.fieldNum2Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
    await EditingPage.fieldNum2Popup.selectByVisibleText("select1") // Select second item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldNum2Popup).toHaveValue("10")
    await expect(EditingPage.fieldNum2Textfield).toHaveValue("10")
    await EditingPage.fieldNum2Popup.selectByIndex(2) // Select third item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldNum2Popup).toHaveValue("20")
    await expect(EditingPage.fieldNum2Textfield).toHaveValue("20")
    await EditingPage.fieldNum2Popup.selectByIndex(0) // Select first item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldNum2Popup).toHaveValue("")
    await expect(EditingPage.fieldNum2Textfield).toHaveValue("")
  })

  it('can edit the text field of float field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldFloat1Textfield).toExist()
    await expect(EditingPage.fieldFloat1Textfield).toHaveValue("0") // Checking initial value
    const value = Math.trunc(Math.random() * 100000) / 1000
    await EditingPage.fieldFloat1Textfield.setValue(value) // Set a value to the field
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat1Textfield).toHaveValue(String(value))
    await EditingPage.fieldFloat1Textfield.setValue("") // Clear the field
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat1Textfield).toHaveValue("0")
  })
  it('can edit the text field of nullable float field.', async () => {
    await expect(EditingPage.fieldFloat2Textfield).toExist()
    await expect(EditingPage.fieldFloat2Textfield).toHaveValue("") // Checking initial value
    const value = Math.trunc(Math.random() * 100000) / 1000
    await EditingPage.fieldFloat2Textfield.setValue(value) // Set a value to the field
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat2Textfield).toHaveValue(String(value))
    await EditingPage.fieldFloat2Textfield.setValue("") // Clear the field
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat2Textfield).toHaveValue("")
  })
  // Checkbox for non-integer type field is out of scope, ok?
  it('can edit the checkbox of float field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldFloat1Checkbox).toExist()
    await expect(EditingPage.fieldFloat1Checkbox).not.toBeSelected() // Checking initial value
    await EditingPage.fieldFloat1Checkbox.click() // ON
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat1Checkbox).toBeSelected()
    await expect(EditingPage.fieldFloat1Textfield).toHaveValue("1")
    await EditingPage.fieldFloat1Checkbox.click() // OFF
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat1Checkbox).not.toBeSelected()
    await expect(EditingPage.fieldFloat1Textfield).toHaveValue("0")
  })
  // Checkbox for non-integer type field is out of scope, ok?
  it('can edit the checkbox of nullable float field.', async () => {
    await expect(EditingPage.fieldFloat2Checkbox).toExist()
    await expect(EditingPage.fieldFloat2Checkbox).not.toBeSelected() // Checking initial value
    await EditingPage.fieldFloat2Checkbox.click() // ON
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat2Checkbox).toBeSelected()
    await expect(EditingPage.fieldFloat2Textfield).toHaveValue("1")
    await EditingPage.fieldFloat2Checkbox.click() // OFF
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat2Checkbox).not.toBeSelected()
    await expect(EditingPage.fieldFloat2Textfield).toHaveValue("")
  })
  // Radio Buttons for non-integer type field is out of scope, ok?
  it('can edit the radio buttons of float field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldFloat1Radio[0]).toExist()
    await expect(EditingPage.fieldFloat1Radio[1]).toExist()
    await expect(EditingPage.fieldFloat1Radio[0]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldFloat1Radio[1]).not.toBeSelected() // Checking initial value
    await EditingPage.fieldFloat1Radio[0].click() // First button
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat1Radio[0]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldFloat1Radio[1]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldFloat1Textfield).toHaveValue("0")
    await EditingPage.fieldFloat1Radio[1].click() // Second button
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat1Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldFloat1Radio[1]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldFloat1Textfield).toHaveValue("1")
  })
  // Radio Buttons for non-integer type field is out of scope, ok?
  it('can edit the radio buttons of nullable float field.', async () => {
    await expect(EditingPage.fieldFloat2Radio[0]).toExist()
    await expect(EditingPage.fieldFloat2Radio[1]).toExist()
    await expect(EditingPage.fieldFloat2Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldFloat2Radio[1]).not.toBeSelected() // Checking initial value
    await EditingPage.fieldFloat2Radio[0].click() // First button
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat2Radio[0]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldFloat2Radio[1]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldFloat2Textfield).toHaveValue("0")
    await EditingPage.fieldFloat2Radio[1].click() // Second button
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat2Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldFloat2Radio[1]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldFloat2Textfield).toHaveValue("1")
  })
  it('can edit the popup menu of float field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldFloat1Popup).toExist()
    await expect(EditingPage.fieldFloat1Popup).toHaveValue("") // Checking initial value
    await expect(EditingPage.fieldFloat1Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
    await EditingPage.fieldFloat1Popup.selectByVisibleText("select1") // Select second item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat1Popup).toHaveValue("10")
    await expect(EditingPage.fieldFloat1Textfield).toHaveValue("10")
    await EditingPage.fieldFloat1Popup.selectByIndex(2) // Select third item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat1Popup).toHaveValue("20")
    await expect(EditingPage.fieldFloat1Textfield).toHaveValue("20")
    await EditingPage.fieldFloat1Popup.selectByIndex(0) // Select first item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat1Popup).toHaveValue("")
    await expect(EditingPage.fieldFloat1Textfield).toHaveValue("0")
  })
  it('can edit the popup menu of nullable float field.', async () => {
    await expect(EditingPage.fieldFloat2Popup).toExist()
    await expect(EditingPage.fieldFloat2Popup).toHaveValue("") // Checking initial value
    await expect(EditingPage.fieldFloat2Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
    await EditingPage.fieldFloat2Popup.selectByVisibleText("select1") // Select second item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat2Popup).toHaveValue("10")
    await expect(EditingPage.fieldFloat2Textfield).toHaveValue("10")
    await EditingPage.fieldFloat2Popup.selectByIndex(2) // Select third item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat2Popup).toHaveValue("20")
    await expect(EditingPage.fieldFloat2Textfield).toHaveValue("20")
    await EditingPage.fieldFloat2Popup.selectByIndex(0) // Select first item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldFloat2Popup).toHaveValue("")
    await expect(EditingPage.fieldFloat2Textfield).toHaveValue("")
  })
  it('can edit the text field of double field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldDouble1Textfield).toExist()
    await expect(EditingPage.fieldDouble1Textfield).toHaveValue("0") // Checking initial value
    const value = Math.random() * 10000000
    await EditingPage.fieldDouble1Textfield.setValue(value) // Set a value to the field
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble1Textfield).toHaveValue(String(value))
    await EditingPage.fieldDouble1Textfield.setValue("") // Clear the field
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble1Textfield).toHaveValue("0")
  })
  it('can edit the text field of nullable double field.', async () => {
    await expect(EditingPage.fieldDouble2Textfield).toExist()
    await expect(EditingPage.fieldDouble2Textfield).toHaveValue("") // Checking initial value
    const value = Math.random() * 10000000
    await EditingPage.fieldDouble2Textfield.setValue(value) // Set a value to the field
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble2Textfield).toHaveValue(String(value))
    await EditingPage.fieldDouble2Textfield.setValue("") // Clear the field
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble2Textfield).toHaveValue("")
  })
  // Checkbox for non-integer type field is out of scope, ok?
  it('can edit the checkbox of double field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldDouble1Checkbox).toExist()
    await expect(EditingPage.fieldDouble1Checkbox).not.toBeSelected() // Checking initial value
    await EditingPage.fieldDouble1Checkbox.click() // ON
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble1Checkbox).toBeSelected()
    await expect(EditingPage.fieldDouble1Textfield).toHaveValue("1")
    await EditingPage.fieldDouble1Checkbox.click() // OFF
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble1Checkbox).not.toBeSelected()
    await expect(EditingPage.fieldDouble1Textfield).toHaveValue("0")
  })
  // Checkbox for non-integer type field is out of scope, ok?
  it('can edit the checkbox of nullable double field.', async () => {
    await expect(EditingPage.fieldDouble2Checkbox).toExist()
    await expect(EditingPage.fieldDouble2Checkbox).not.toBeSelected() // Checking initial value
    await EditingPage.fieldDouble2Checkbox.click() // ON
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble2Checkbox).toBeSelected()
    await expect(EditingPage.fieldDouble2Textfield).toHaveValue("1")
    await EditingPage.fieldDouble2Checkbox.click() // OFF
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble2Checkbox).not.toBeSelected()
    await expect(EditingPage.fieldDouble2Textfield).toHaveValue("")
  })
  // Radio Buttons for non-integer type field is out of scope, ok?
  it('can edit the radio buttons of double field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldDouble1Radio[0]).toExist()
    await expect(EditingPage.fieldDouble1Radio[1]).toExist()
    await expect(EditingPage.fieldDouble1Radio[0]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldDouble1Radio[1]).not.toBeSelected() // Checking initial value
    await EditingPage.fieldDouble1Radio[0].click() // First button
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble1Radio[0]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldDouble1Radio[1]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldDouble1Textfield).toHaveValue("0")
    await EditingPage.fieldDouble1Radio[1].click() // Second button
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble1Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldDouble1Radio[1]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldDouble1Textfield).toHaveValue("1")
  })
  // Radio Buttons for non-integer type field is out of scope, ok?
  it('can edit the radio buttons of double integer field.', async () => {
    await expect(EditingPage.fieldDouble2Radio[0]).toExist()
    await expect(EditingPage.fieldDouble2Radio[1]).toExist()
    await expect(EditingPage.fieldDouble2Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldDouble2Radio[1]).not.toBeSelected() // Checking initial value
    await EditingPage.fieldDouble2Radio[0].click() // First button
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble2Radio[0]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldDouble2Radio[1]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldDouble2Textfield).toHaveValue("0")
    await EditingPage.fieldDouble2Radio[1].click() // Second button
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble2Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldDouble2Radio[1]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldDouble2Textfield).toHaveValue("1")
  })
  it('can edit the popup menu of double field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldDouble1Popup).toExist()
    await expect(EditingPage.fieldDouble1Popup).toHaveValue("") // Checking initial value
    await expect(EditingPage.fieldDouble1Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
    await EditingPage.fieldDouble1Popup.selectByVisibleText("select1") // Select second item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble1Popup).toHaveValue("10")
    await expect(EditingPage.fieldDouble1Textfield).toHaveValue("10")
    await EditingPage.fieldDouble1Popup.selectByIndex(2) // Select third item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble1Popup).toHaveValue("20")
    await expect(EditingPage.fieldDouble1Textfield).toHaveValue("20")
    await EditingPage.fieldDouble1Popup.selectByIndex(0) // Select first item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble1Popup).toHaveValue("")
    await expect(EditingPage.fieldDouble1Textfield).toHaveValue("0")
  })
  it('can edit the popup menu of nullable double field.', async () => {
    await expect(EditingPage.fieldDouble2Popup).toExist()
    await expect(EditingPage.fieldDouble2Popup).toHaveValue("") // Checking initial value
    await expect(EditingPage.fieldDouble2Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
    await EditingPage.fieldDouble2Popup.selectByVisibleText("select1") // Select second item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble2Popup).toHaveValue("10")
    await expect(EditingPage.fieldDouble2Textfield).toHaveValue("10")
    await EditingPage.fieldDouble2Popup.selectByIndex(2) // Select third item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble2Popup).toHaveValue("20")
    await expect(EditingPage.fieldDouble2Textfield).toHaveValue("20")
    await EditingPage.fieldDouble2Popup.selectByIndex(0) // Select first item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldDouble2Popup).toHaveValue("")
    await expect(EditingPage.fieldDouble2Textfield).toHaveValue("")
  })
  it('can edit the text field of boolean field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldBool1Textfield).toExist()
    await expect(EditingPage.fieldBool1Textfield).toHaveValue("0") // Checking initial value
    const value = 1
    await EditingPage.fieldBool1Textfield.setValue(value) // Set a value to the field
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool1Textfield).toHaveValue(String(value))
    await EditingPage.fieldBool1Textfield.setValue("") // Clear the field
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool1Textfield).toHaveValue("0")
  })
  it('can edit the text field of nullable boolean field.', async () => {
    await expect(EditingPage.fieldBool2Textfield).toExist()
    await expect(EditingPage.fieldBool2Textfield).toHaveValue("") // Checking initial value
    const value = 1
    await EditingPage.fieldBool2Textfield.setValue(value) // Set a value to the field
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool2Textfield).toHaveValue(String(value))
    await EditingPage.fieldBool2Textfield.setValue("") // Clear the field
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool2Textfield).toHaveValue("")
  })
  it('can edit the checkbox of boolean field which is NOT NULL.', async () => {
    await EditingPage.fieldBool1Textfield.setValue("") // Clear the field
    await expect(EditingPage.fieldBool1Checkbox).toExist()
    await expect(EditingPage.fieldBool1Checkbox).not.toBeSelected() // Checking initial value
    await EditingPage.fieldBool1Checkbox.click() // ON
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool1Checkbox).toBeSelected()
    await expect(EditingPage.fieldBool1Textfield).toHaveValue("1")
    await EditingPage.fieldBool1Checkbox.click() // OFF
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool1Checkbox).not.toBeSelected()
    await expect(EditingPage.fieldBool1Textfield).toHaveValue("0")
  })
  it('can edit the checkbox of nullable boolean field.', async () => {
    await EditingPage.fieldBool2Textfield.setValue("") // Clear the field
    await expect(EditingPage.fieldBool2Checkbox).toExist()
    await expect(EditingPage.fieldBool2Checkbox).not.toBeSelected() // Checking initial value
    await EditingPage.fieldBool2Checkbox.click() // ON
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool2Checkbox).toBeSelected()
    await expect(EditingPage.fieldBool2Textfield).toHaveValue("1")
    await EditingPage.fieldBool2Checkbox.click() // OFF
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool2Checkbox).not.toBeSelected()
    await expect(EditingPage.fieldBool2Textfield).toHaveValue("")
  })
  it('can edit the radio buttons of boolean field which is NOT NULL.', async () => {
    await EditingPage.fieldBool1Textfield.setValue("") // Clear the field
    await expect(EditingPage.fieldBool1Radio[0]).toExist()
    await expect(EditingPage.fieldBool1Radio[1]).toExist()
    await expect(EditingPage.fieldBool1Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldBool1Radio[1]).not.toBeSelected() // Checking initial value
    await EditingPage.fieldBool1Radio[0].click() // First button
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool1Radio[0]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldBool1Radio[1]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldBool1Textfield).toHaveValue("0")
    await EditingPage.fieldBool1Radio[1].click() // Second button
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool1Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldBool1Radio[1]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldBool1Textfield).toHaveValue("1")
  })
  it('can edit the radio buttons of nullable boolean field.', async () => {
    await EditingPage.fieldBool2Textfield.setValue("") // Clear the field
    await expect(EditingPage.fieldBool2Radio[0]).toExist()
    await expect(EditingPage.fieldBool2Radio[1]).toExist()
    await expect(EditingPage.fieldBool2Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldBool2Radio[1]).not.toBeSelected() // Checking initial value
    await EditingPage.fieldBool2Radio[0].click() // First button
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool2Radio[0]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldBool2Radio[1]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldBool2Textfield).toHaveValue("0")
    await EditingPage.fieldBool2Radio[1].click() // Second button
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool2Radio[0]).not.toBeSelected() // Checking initial value
    await expect(EditingPage.fieldBool2Radio[1]).toBeSelected() // Checking initial value
    await expect(EditingPage.fieldBool2Textfield).toHaveValue("1")
  })
  it('can edit the popup menu of boolean field which is NOT NULL.', async () => {
    await expect(EditingPage.fieldBool1Popup).toExist()
    await expect(EditingPage.fieldBool1Popup).toHaveValue("") // Checking initial value
    await expect(EditingPage.fieldBool1Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
    await EditingPage.fieldBool1Popup.selectByVisibleText("select1") // Select second item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool1Popup).toHaveValue("10")
    await expect(EditingPage.fieldBool1Textfield).toHaveValue("10")
    await EditingPage.fieldBool1Popup.selectByIndex(2) // Select third item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool1Popup).toHaveValue("20")
    await expect(EditingPage.fieldBool1Textfield).toHaveValue("20")
    await EditingPage.fieldBool1Popup.selectByIndex(0) // Select first item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool1Popup).toHaveValue("")
    await expect(EditingPage.fieldBool1Textfield).toHaveValue("0")
  })
  it('can edit the popup menu of nullable boolean field.', async () => {
    await expect(EditingPage.fieldBool2Popup).toExist()
    await expect(EditingPage.fieldBool2Popup).toHaveValue("") // Checking initial value
    await expect(EditingPage.fieldBool2Popup).toHaveText("unselect\nselect1\nselect2\nselect3")
    await EditingPage.fieldBool2Popup.selectByVisibleText("select1") // Select second item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool2Popup).toHaveValue("10")
    await expect(EditingPage.fieldBool2Textfield).toHaveValue("10")
    await EditingPage.fieldBool2Popup.selectByIndex(2) // Select third item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool2Popup).toHaveValue("20")
    await expect(EditingPage.fieldBool2Textfield).toHaveValue("20")
    await EditingPage.fieldBool2Popup.selectByIndex(0) // Select first item
    await browser.pause(waiting)
    await EditingPage.navigatorUpdateButton.click()
    await browser.pause(waiting)
    await expect(EditingPage.fieldBool2Popup).toHaveValue("")
    await expect(EditingPage.fieldBool2Textfield).toHaveValue("")
  })
})
