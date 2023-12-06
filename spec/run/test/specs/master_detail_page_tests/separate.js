module.exports = (separatePage) => {
  const waiting = 500

  describe('Master-Detail Page', () => {
    it('1-can show the Master area, and disappear the Detail area.', async () => {
      await expect(separatePage.navigator).toExist()
      await expect(separatePage.masterTable).toExist()
      await expect(separatePage.detailTable).not.toExist()
    });
    it('2-can show the data from database.', async () => {
      expect(await separatePage.masterFieldPostalCode.length).toBe(100)
      expect(await separatePage.masterFieldPref.length).toBe(100)
      expect(await separatePage.masterFieldCity.length).toBe(100)
      expect(await separatePage.masterFieldTown.length).toBe(100)
      expect(await separatePage.masterFieldPostalCode[0]).toHaveValue('1000000')
      expect(await separatePage.masterFieldPref[0]).toHaveValue('東京都')
      expect(await separatePage.masterFieldCity[0]).toHaveValue('千代田区')
      expect(await separatePage.masterFieldTown[0]).toHaveValue('以下に掲載がない場合')
      expect(await separatePage.masterFieldPostalCode[1]).toHaveValue('1000001')
      expect(await separatePage.masterFieldPref[1]).toHaveValue('東京都')
      expect(await separatePage.masterFieldCity[1]).toHaveValue('千代田区')
      expect(await separatePage.masterFieldTown[1]).toHaveValue('千代田')
      expect(await separatePage.masterFieldPostalCode[2]).toHaveValue('1000002')
      expect(await separatePage.masterFieldPref[2]).toHaveValue('東京都')
      expect(await separatePage.masterFieldCity[2]).toHaveValue('千代田区')
      expect(await separatePage.masterFieldTown[2]).toHaveValue('皇居外苑')
      expect(await separatePage.masterFieldPostalCode[3]).toHaveValue('1000003')
      expect(await separatePage.masterFieldPref[3]).toHaveValue('東京都')
      expect(await separatePage.masterFieldCity[3]).toHaveValue('千代田区')
      expect(await separatePage.masterFieldTown[3]).toHaveValue('一ツ橋（１丁目）')
    });
    it('3-can move to the detail page', async () => {
      const buttons = await separatePage.masterButtonMoveToDetail
      buttons[3].click()
      browser.pause(waiting)

      await expect(separatePage.navigator).not.toExist()
      await expect(separatePage.masterTable).not.toExist()
      await expect(separatePage.detailTable).toExist()

      expect(separatePage.detailFieldPostalCode).toHaveValue('1000003')
      expect(separatePage.detailFieldPref).toHaveValue('東京都')
      expect(separatePage.detailFieldCity).toHaveValue('千代田区')
      expect(separatePage.detailFieldTown).toHaveValue('一ツ橋（１丁目）')
    })
    it('4-can back to the master page', async () => {
      const button = await separatePage.detailButtonMoveToMaster
      button.click()
      browser.pause(waiting)

      await separatePage.navigator.waitForExist()
      await expect(separatePage.navigator).toExist()
      await expect(separatePage.masterTable).toExist()
      await expect(separatePage.detailTable).not.toExist()

    })
    it('5-can edit on the detail page and affect to the result of the master page', async () => {
      const buttons = await separatePage.masterButtonMoveToDetail
      buttons[6].click()
      browser.pause(waiting)

      await separatePage.detailTable.waitForExist()
      await expect(separatePage.navigator).not.toExist()
      await expect(separatePage.masterTable).not.toExist()
      await expect(separatePage.detailTable).toExist()

      expect(separatePage.detailFieldPostalCode).toHaveValue('1000006')
      expect(separatePage.detailFieldPref).toHaveValue('東京都')
      expect(separatePage.detailFieldCity).toHaveValue('千代田区')
      expect(separatePage.detailFieldTown).toHaveValue('有楽町')

      const value = "######"
      await separatePage.detailFieldTown.setValue(value) // Set a value to the field
      browser.pause(waiting)

      const button = await separatePage.detailButtonMoveToMaster
      button.click()
      browser.pause(waiting)

      separatePage.navigator.waitForExist()
      await expect(separatePage.navigator).toExist()
      await expect(separatePage.masterTable).toExist()
      await expect(separatePage.detailTable).not.toExist()

      expect(await separatePage.masterFieldTown[6]).toHaveValue(value)
    })
    it('6-can move to next page of navigator, and checking', async () => {
      await separatePage.navigatorMoveButtonNext.click()
      browser.pause(waiting)

      await separatePage.masterFieldPostalCodeFirst.waitForExist()
      await separatePage.masterFieldPrefFirst.waitForExist()
      await separatePage.masterFieldCityFirst.waitForExist()
      await separatePage.masterFieldTownFirst.waitForExist()

      expect(await separatePage.masterFieldPostalCode.length).toBe(100)
      expect(await separatePage.masterFieldPref.length).toBe(100)
      expect(await separatePage.masterFieldCity.length).toBe(100)
      expect(await separatePage.masterFieldTown.length).toBe(100)
      expect(await separatePage.masterFieldPostalCode[0]).toHaveValue('1006122')
      expect(await separatePage.masterFieldPref[0]).toHaveValue('東京都')
      expect(await separatePage.masterFieldCity[0]).toHaveValue('千代田区')
      expect(await separatePage.masterFieldTown[0]).toHaveValue('永田町山王パークタワー（２２階）')

      const buttons = await separatePage.masterButtonMoveToDetail
      buttons[1].click()
      browser.pause(waiting)
      await separatePage.detailButtonMoveToMaster.waitForExist()
      browser.pause(waiting)

      await expect(separatePage.navigator).not.toExist()
      await expect(separatePage.masterTable).not.toExist()
      await expect(separatePage.detailTable).toExist()

      expect(separatePage.detailFieldPostalCode).toHaveValue('1006123')
      expect(separatePage.detailFieldPref).toHaveValue('東京都')
      expect(separatePage.detailFieldCity).toHaveValue('千代田区')
      expect(separatePage.detailFieldTown).toHaveValue('永田町山王パークタワー（２３階）')

      const value = "######"
      await separatePage.detailFieldTown.setValue(value) // Set a value to the field
      browser.pause(waiting)

      const button = await separatePage.detailButtonMoveToMaster
      button.click()
      browser.pause(waiting)

      await expect(separatePage.navigator).toExist()
      await expect(separatePage.masterTable).toExist()
      await expect(separatePage.detailTable).not.toExist()
      // await separatePage.firstMasterButtonMoveToDetail.waitForExist()

      expect(await separatePage.masterFieldPostalCode.length).toBe(100)
      expect(await separatePage.masterFieldPref.length).toBe(100)
      expect(await separatePage.masterFieldCity.length).toBe(100)
      expect(await separatePage.masterFieldTown.length).toBe(100)
      expect(await separatePage.masterFieldPostalCode[2]).toHaveValue('1006123')
      expect(await separatePage.masterFieldPref[2]).toHaveValue('東京都')
      expect(await separatePage.masterFieldCity[2]).toHaveValue('千代田区')
      expect(await separatePage.masterFieldTown[2]).toHaveValue(value)
    })
    it('7-can back to scrolled position', async () => {
      await separatePage.navigatorMoveButtonNext.click()
      browser.pause(waiting)

      await separatePage.masterFieldPostalCodeFirst.waitForExist()
      await separatePage.masterFieldPrefFirst.waitForExist()
      await separatePage.masterFieldCityFirst.waitForExist()
      await separatePage.masterFieldTownFirst.waitForExist()

      expect(await separatePage.masterFieldPostalCode.length).toBe(100)
      expect(await separatePage.masterFieldPref.length).toBe(100)
      expect(await separatePage.masterFieldCity.length).toBe(100)
      expect(await separatePage.masterFieldTown.length).toBe(100)
      expect(await separatePage.masterFieldPostalCode[0]).toHaveValue('1006407')
      expect(await separatePage.masterFieldPref[0]).toHaveValue('東京都')
      expect(await separatePage.masterFieldCity[0]).toHaveValue('千代田区')
      expect(await separatePage.masterFieldTown[0]).toHaveValue('丸の内東京ビルディング（７階）')

      const buttons = await separatePage.masterButtonMoveToDetail
      buttons[99].click()
      browser.pause(waiting)
      // await separatePage.detailButtonMoveToMaster.waitForExist()
      // browser.pause(waiting)

      await expect(separatePage.navigator).not.toExist()
      await expect(separatePage.masterTable).not.toExist()
      await expect(separatePage.detailTable).toExist()

      expect(separatePage.detailFieldPostalCode).toHaveValue('1006633')
      expect(separatePage.detailFieldPref).toHaveValue('東京都')
      expect(separatePage.detailFieldCity).toHaveValue('千代田区')
      expect(separatePage.detailFieldTown).toHaveValue('丸の内グラントウキョウサウスタワー（３３階）')

      const button = await separatePage.detailButtonMoveToMaster
      button.click()
      browser.pause(waiting)
      // await separatePage.firstMasterButtonMoveToDetail.waitForExist()
      // browser.pause(waiting)

      await expect(separatePage.navigator).toExist()
      await expect(separatePage.masterTable).toExist()
      await expect(separatePage.detailTable).not.toExist()

      expect(await await separatePage.masterFieldPostalCode[0].isDisplayedInViewport()).toBe(false)
      expect(await await separatePage.masterFieldPostalCode[99].isDisplayedInViewport()).toBe(true)
    })
  })
}