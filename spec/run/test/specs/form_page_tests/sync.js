const Key = require('webdriverio')

const FormPage = require("../../pageobjects/FormPage/form_mysql.page");
module.exports = (FormPage, windowList) => {
  const waiting = 500

  describe('Form Page', () => {
    it('1-show on different pages to a record.', async () => {
      await browser.switchWindow(windowList[0])
      await expect(FormPage.fieldPersonId).toHaveText("1")
      await FormPage.navigator.waitForExist()
      // await FormPage.fieldPersonName.setValue("Data" + Math.trunc(Math.random() * 10000))
      // await browser.keys([Key.Tab])
      await FormPage.setTitle(windowList[0])

      await browser.switchWindow(windowList[1])
      await expect(FormPage.fieldPersonId).toHaveText("1")
      await FormPage.navigator.waitForExist()
      await FormPage.navigatorUpdateButton.waitForClickable();
      await FormPage.navigatorMoveButtonNext.click()
      await expect(FormPage.fieldPersonId).toHaveText("2")
      // await FormPage.fieldPersonName.setValue("Data" + Math.trunc(Math.random() * 10000))
      // await browser.keys([Key.Tab])
      await FormPage.setTitle(windowList[1])

      await browser.switchWindow(windowList[2])
      await expect(FormPage.fieldPersonId).toHaveText("1")
      await FormPage.navigator.waitForExist()
      await FormPage.navigatorUpdateButton.waitForClickable();
      await FormPage.navigatorMoveButtonNext.click()
      await FormPage.navigator.waitForExist()
      await FormPage.navigatorUpdateButton.waitForClickable();
      await FormPage.navigatorMoveButtonNext.click()
      await expect(FormPage.fieldPersonId).toHaveText("3")
      // await FormPage.fieldPersonName.setValue("Data" + Math.trunc(Math.random() * 10000))
      // await browser.keys([Key.Tab])
      await FormPage.setTitle(windowList[2])

      await browser.switchWindow(windowList[3])
      await expect(FormPage.fieldPersonId).toHaveText("1")
      await FormPage.navigator.waitForExist()
      await FormPage.navigatorUpdateButton.waitForClickable();
      await FormPage.navigatorMoveButtonNext.click()
      await expect(FormPage.fieldPersonId).toHaveText("2")
      // await FormPage.fieldPersonName.setValue("Data" + Math.trunc(Math.random() * 10000))
      // await browser.keys([Key.Tab])
      await FormPage.setTitle(windowList[3])
    })
    it('2-can synchronize with the field.', async () => {
      await browser.switchWindow(windowList[1])
      await expect(FormPage.fieldPersonId).toHaveText("2")
      const value = "Edit" + Math.trunc(Math.random() * 10000)
      await FormPage.fieldPersonName.setValue(value)
      // await browser.keys([Key.Tab])
      await FormPage.setTitle(windowList[1])

      await browser.switchWindow(windowList[3])
      await browser.pause(waiting * 4)
      await expect(FormPage.fieldPersonId).toHaveText("2")
      await expect(FormPage.fieldPersonName).toHaveValue(value)
    })
    it('3-dont synchronize for different records.', async () => {
      await browser.switchWindow(windowList[2])
      await expect(FormPage.fieldPersonId).toHaveText("3")
      const value = "Other" + Math.trunc(Math.random() * 10000)
      await FormPage.fieldPersonName.setValue(value)
      // await browser.keys([Key.Tab])
      await FormPage.setTitle(windowList[2])

      await browser.switchWindow(windowList[1])
      await FormPage.navigator.waitForExist()
      await FormPage.navigatorUpdateButton.waitForClickable();
      await expect(FormPage.fieldPersonId).toHaveText("2")
      await expect(FormPage.fieldPersonName).not.toHaveValue(value)
    })
    it('4-can synchronize with creating record.', async () => {
      await browser.switchWindow(windowList[1])
      await expect(FormPage.fieldPersonId).toHaveText("2")
      const rows = await FormPage.rowContact
      const lineCount = rows.length
      const fieldValue = await FormPage.rowContactSummary[0].getValue()
      await FormPage.rowContactCopyButton[0].waitForClickable()
      await FormPage.rowContactCopyButton[0].click()
      await expect(FormPage.rowContact).toBeElementsArrayOfSize(lineCount + 1)
      await FormPage.setTitle(windowList[1])

      await browser.switchWindow(windowList[3])
      await expect(FormPage.fieldPersonId).toHaveText("2")
      await expect(FormPage.rowContact).toBeElementsArrayOfSize(lineCount + 1)
      await expect(FormPage.rowContactSummary[0]).toHaveValue(fieldValue)
      await expect(FormPage.rowContactSummary[lineCount]).toHaveValue(fieldValue)
      await FormPage.setTitle(windowList[3])
    })
    it('5-dont synchronize with creating record for different record.', async () => {
      await browser.switchWindow(windowList[1])
      await expect(FormPage.fieldPersonId).toHaveText("2")
      const rows = await FormPage.rowContact
      const lineCount = rows.length
      await FormPage.setTitle(windowList[1])

      await browser.switchWindow(windowList[2])
      await expect(FormPage.fieldPersonId).toHaveText("3")
      await FormPage.navigator.waitForExist()
      await FormPage.rowContactCopyButton[0].waitForClickable()
      await FormPage.rowContactCopyButton[0].click()
      await FormPage.setTitle(windowList[2])

      await browser.switchWindow(windowList[3])
      await FormPage.navigator.waitForExist()
      await FormPage.navigatorUpdateButton.waitForClickable();
      await expect(FormPage.fieldPersonId).toHaveText("2")
      await expect(FormPage.rowContact).toBeElementsArrayOfSize(lineCount)
      await FormPage.setTitle(windowList[3])
    })
    it('6-can synchronize with deleting record.', async () => {
      await browser.switchWindow(windowList[1])
      await expect(FormPage.fieldPersonId).toHaveText("2")
      const rows = await FormPage.rowContact
      const lineCount = rows.length

      await FormPage.rowContactDeleteButton[0].waitForClickable()
      await FormPage.rowContactDeleteButton[0].click()
      await browser.acceptAlert()
      await expect(FormPage.rowContact).toBeElementsArrayOfSize(lineCount - 1)
      await FormPage.setTitle(windowList[1])

      await browser.switchWindow(windowList[3])
      await FormPage.navigator.waitForExist()
      await FormPage.navigatorUpdateButton.waitForClickable();
      await expect(FormPage.fieldPersonId).toHaveText("2")
      await expect(FormPage.rowContact).toBeElementsArrayOfSize(lineCount - 1)
      await FormPage.setTitle(windowList[3])
    })
    it('7-dont synchronize with deleting record for different record.', async () => {
      await browser.switchWindow(windowList[1])
      await expect(FormPage.fieldPersonId).toHaveText("2")
      const rows = await FormPage.rowContact
      const lineCount = rows.length
      await FormPage.setTitle(windowList[1])

      await browser.switchWindow(windowList[2])
      await expect(FormPage.fieldPersonId).toHaveText("3")
      await FormPage.navigator.waitForExist()
      await FormPage.rowContactDeleteButton[0].waitForClickable()
      await FormPage.rowContactDeleteButton[0].click()
      await FormPage.setTitle(windowList[2])

      await browser.switchWindow(windowList[3])
      await FormPage.navigator.waitForExist()
      await FormPage.navigatorUpdateButton.waitForClickable();
      await expect(FormPage.fieldPersonId).toHaveText("2")
      await expect(FormPage.rowContact).toBeElementsArrayOfSize(lineCount)
      await FormPage.setTitle(windowList[3])
    })
  })
}

