const Key = require('webdriverio')

module.exports = (page) => {
  describe('Calculation Test', () => {
    const waiting = 500
    let isJapanese = false
    if (process.platform === 'darwin') {
      isJapanese = true
    }
    it('1-can open and has the navigator.', async () => {
      await page.open()
      await expect(page.navigator).toExist()
    })
    it('2-can create one item.', async () => {
      await expect(page.navigatorInsertButton).toExist()
      await page.navigatorInsertButton.click()
      await browser.pause(waiting)
      await expect(page.fieldsItemProductId).toBeElementsArrayOfSize(0)

      await page.itemInsertButton.click()
      await expect(page.fieldsItemProductId).toBeElementsArrayOfSize(1)

      await page.fieldsItemProductId[0].setValue(1)
      await browser.keys("\ue004") //Tab https://stackoverflow.com/questions/58621349/webdriverio-how-to-do-a-tab-key-action
      await browser.pause(waiting)
      await expect(page.fieldsProductName[0]).toHaveText("Apple")
      await expect(page.popupProductId[0]).toHaveValue("1")
      await expect(page.popupProductId[0]).toHaveText("Apple\nOrange\nMelon\nTomato\nOnion")
      await expect(page.fieldsQty[0]).toHaveValue("")
      await expect(page.fieldsItemProductName[0]).toHaveValue("Apple")
      await expect(page.fieldsItemUnitprice[0]).toHaveValue("340")
      await expect(page.fieldsItemNetPrice[0]).toHaveText("0")
      await expect(page.fieldsItemTaxPrice[0]).toHaveText("0")
      await expect(page.fieldsItemAmountCalc[0]).toHaveText("0")

      await page.fieldsQty[0].setValue("4")
      await browser.keys("\ue004") //Tab https://stackoverflow.com/questions/58621349/webdriverio-how-to-do-a-tab-key-action
      await browser.pause(waiting)
      await expect(page.fieldsItemNetPrice[0]).toHaveText("1,360")
      await expect(page.fieldsItemTaxPrice[0]).toHaveText("0")
      await expect(page.fieldsItemAmountCalc[0]).toHaveText("1,360")

      await page.fieldTaxRate.setValue("0.1")
      await browser.keys("\ue004") //Tab https://stackoverflow.com/questions/58621349/webdriverio-how-to-do-a-tab-key-action
      await browser.pause(waiting)
      await expect(page.fieldsItemNetPrice[0]).toHaveText("1,360")
      await expect(page.fieldsItemTaxPrice[0]).toHaveText("136")
      await expect(page.fieldsItemAmountCalc[0]).toHaveText("1,496")

      await page.fieldsItemUnitprice[0].setValue("100")
      await browser.keys("\ue004") //Tab https://stackoverflow.com/questions/58621349/webdriverio-how-to-do-a-tab-key-action
      await browser.pause(waiting)
      await expect(page.fieldsItemNetPrice[0]).toHaveText("400")
      await expect(page.fieldsItemTaxPrice[0]).toHaveText("40")
      await expect(page.fieldsItemAmountCalc[0]).toHaveText("440")

    })
    it('3-can create more item.', async () => {
      await page.itemInsertButton.click()
      await expect(page.fieldsItemProductId).toBeElementsArrayOfSize(2)
      await page.fieldsItemProductId[1].setValue(2)
      await browser.keys("\ue004") //Tab https://stackoverflow.com/questions/58621349/webdriverio-how-to-do-a-tab-key-action
      await browser.pause(waiting)
      await expect(page.fieldsProductName[1]).toHaveText("Orange")
      await expect(page.popupProductId[1]).toHaveValue("2")
      await expect(page.popupProductId[1]).toHaveText("Apple\nOrange\nMelon\nTomato\nOnion")
      await expect(page.fieldsQty[1]).toHaveValue("")
      await expect(page.fieldsItemProductName[1]).toHaveValue("Orange")
      await expect(page.fieldsItemUnitprice[1]).toHaveValue("1,540")
      await expect(page.fieldsItemNetPrice[1]).toHaveText("0")
      await expect(page.fieldsItemTaxPrice[1]).toHaveText("0")
      await expect(page.fieldsItemAmountCalc[1]).toHaveText("0")

      await page.fieldsQty[1].setValue("10")
      await browser.keys("\ue004") //Tab https://stackoverflow.com/questions/58621349/webdriverio-how-to-do-a-tab-key-action
      await browser.pause(waiting)
      await expect(page.fieldsItemNetPrice[1]).toHaveText("15,400")
      await expect(page.fieldsItemTaxPrice[1]).toHaveText("1,540")
      await expect(page.fieldsItemAmountCalc[1]).toHaveText("16,940")

    })
    it('4-can create one more item.', async () => {
      await page.itemInsertButton.click()
      await expect(page.fieldsItemProductId).toBeElementsArrayOfSize(3)
      await page.fieldsItemProductId[2].setValue(4)
      await browser.keys("\ue004") //Tab https://stackoverflow.com/questions/58621349/webdriverio-how-to-do-a-tab-key-action
      await browser.pause(waiting)
      await expect(page.fieldsProductName[2]).toHaveText("Tomato")
      await expect(page.popupProductId[2]).toHaveValue("4")
      await expect(page.popupProductId[2]).toHaveText("Apple\nOrange\nMelon\nTomato\nOnion")
      await expect(page.fieldsQty[2]).toHaveValue("")
      await expect(page.fieldsItemProductName[2]).toHaveValue("Tomato")
      await expect(page.fieldsItemUnitprice[2]).toHaveValue("2,440")
      await expect(page.fieldsItemNetPrice[2]).toHaveText("0")
      await expect(page.fieldsItemTaxPrice[2]).toHaveText("0")
      await expect(page.fieldsItemAmountCalc[2]).toHaveText("0")

      await page.fieldsQty[2].setValue("10")
      await browser.keys("\ue004") //Tab https://stackoverflow.com/questions/58621349/webdriverio-how-to-do-a-tab-key-action
      await browser.pause(waiting)
      await expect(page.fieldsItemNetPrice[2]).toHaveText("24,400")
      await expect(page.fieldsItemTaxPrice[2]).toHaveText("2,440")
      await expect(page.fieldsItemAmountCalc[2]).toHaveText("26,840")

    })
    it('5-can calcurate total value', async () => {
      await expect(page.fieldTotalCalc).toHaveText(isJapanese ? "¥44,220.00" : "44,220.00¥")

      await page.fieldTaxRate.setValue("0")
      await browser.keys("\ue004") //Tab https://stackoverflow.com/questions/58621349/webdriverio-how-to-do-a-tab-key-action
      await browser.pause(waiting * 4)
      await expect(page.fieldTotalCalc).toHaveText(isJapanese ? "¥40,200.00" : "40,200.00¥")

      await page.fieldsItemUnitprice[0].setValue("2500")
      await browser.keys("\ue004") //Tab https://stackoverflow.com/questions/58621349/webdriverio-how-to-do-a-tab-key-action
      await browser.pause(waiting)
      await expect(page.fieldTotalCalc).toHaveText(isJapanese ? "¥49,800.00" : "49,800.00¥")

      await page.popupProductId[2].selectByVisibleText("Onion")
      await browser.pause(waiting)
      await expect(page.fieldTotalCalc).toHaveText(isJapanese ? "¥238,800.00" : "238,800.00¥")
    })
  })
}
