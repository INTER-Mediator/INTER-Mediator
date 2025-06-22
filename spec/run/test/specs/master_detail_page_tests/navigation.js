module.exports = (mdPage) => {
  const waiting = 500

  describe('Master-Detail Page', () => {
    it('1-can show the Master area, and disappear the Detail area.', async () => {
      await expect(mdPage.navigator).toExist()
      expect(await mdPage.getNavigatorStyleDisplay()).not.toBe('none')
      await expect(mdPage.masterTable).toExist()
      expect(await mdPage.getMasterTableStyleDisplay()).not.toBe('none')
      await expect(mdPage.detailTable).toExist()
      expect(await mdPage.getDetailTableStyleDisplay()).toBe('none')
    });
    it('2-can show the data from database.', async () => {
      const masterCodes = await mdPage.masterFieldPostalCode
      const masterPrefs = await mdPage.masterFieldPref
      const masterCities = await mdPage.masterFieldCity
      const masterTowns = await mdPage.masterFieldTown
      expect(masterCodes.length).toBe(100)
      expect(masterPrefs.length).toBe(100)
      expect(masterCities.length).toBe(100)
      expect(masterTowns.length).toBe(100)
      expect(masterCodes[0]).toHaveValue('1000000')
      expect(masterPrefs[0]).toHaveValue('東京都')
      expect(masterCities[0]).toHaveValue('千代田区')
      expect(masterTowns[0]).toHaveValue('以下に掲載がない場合')
      expect(masterCodes[1]).toHaveValue('1000001')
      expect(masterPrefs[1]).toHaveValue('東京都')
      expect(masterCities[1]).toHaveValue('千代田区')
      expect(masterTowns[1]).toHaveValue('千代田')
      expect(masterCodes[2]).toHaveValue('1000002')
      expect(masterPrefs[2]).toHaveValue('東京都')
      expect(masterCities[2]).toHaveValue('千代田区')
      expect(masterTowns[2]).toHaveValue('皇居外苑')
      expect(masterCodes[3]).toHaveValue('1000003')
      expect(masterPrefs[3]).toHaveValue('東京都')
      expect(masterCities[3]).toHaveValue('千代田区')
      expect(masterTowns[3]).toHaveValue('一ツ橋（１丁目）')
    });
    it('3-can move to the detail page', async () => {
      const buttons = await mdPage.masterButtonMoveToDetail
      buttons[3].click()
      browser.pause(waiting)

      expect(mdPage.detailFieldPostalCode).toHaveValue('1000003')
      expect(mdPage.detailFieldPref).toHaveValue('東京都')
      expect(mdPage.detailFieldCity).toHaveValue('千代田区')
      expect(mdPage.detailFieldTown).toHaveValue('一ツ橋（１丁目）')

      await expect(mdPage.navigator).toExist()
      expect(await mdPage.getNavigatorStyleDisplay()).toBe('none')
      await expect(mdPage.masterTable).toExist()
      expect(await mdPage.getMasterTableStyleDisplay()).toBe('none')
      await expect(mdPage.detailTable).toExist()
      expect(await mdPage.getDetailTableStyleDisplay()).not.toBe('none')

    })
    it('4-can back to the master page', async () => {
      const button = await mdPage.detailButtonMoveToMaster
      button.click()
      browser.pause(waiting)

      await expect(mdPage.navigator).toExist()
      expect(await mdPage.getNavigatorStyleDisplay()).not.toBe('none')
      await expect(mdPage.masterTable).toExist()
      expect(await mdPage.getMasterTableStyleDisplay()).not.toBe('none')
      await expect(mdPage.detailTable).toExist()
      expect(await mdPage.getDetailTableStyleDisplay()).toBe('none')

    })
    it('5-can edit on the detail page and affect to the result of the master page', async () => {
      const buttons = await mdPage.masterButtonMoveToDetail
      buttons[4].click()
      browser.pause(waiting)

      expect(mdPage.detailFieldPostalCode).toHaveValue('1000004')
      expect(mdPage.detailFieldPref).toHaveValue('東京都')
      expect(mdPage.detailFieldCity).toHaveValue('千代田区')
      expect(mdPage.detailFieldTown).toHaveValue('大手町（次のビルを除く）')

      await expect(mdPage.navigator).toExist()
      expect(await mdPage.getNavigatorStyleDisplay()).toBe('none')
      await expect(mdPage.masterTable).toExist()
      expect(await mdPage.getMasterTableStyleDisplay()).toBe('none')
      await expect(mdPage.detailTable).toExist()
      expect(await mdPage.getDetailTableStyleDisplay()).not.toBe('none')

      await mdPage.detailFieldTown.waitForEnabled()
      const value = "######"
      await mdPage.detailFieldTown.setValue(value) // Set a value to the field
      browser.pause(waiting)

      const button = await mdPage.detailButtonMoveToMaster
      button.click()
      browser.pause(waiting)

      await expect(mdPage.navigator).toExist()
      expect(await mdPage.getNavigatorStyleDisplay()).not.toBe('none')
      await expect(mdPage.masterTable).toExist()
      expect(await mdPage.getMasterTableStyleDisplay()).not.toBe('none')
      await expect(mdPage.detailTable).toExist()
      expect(await mdPage.getDetailTableStyleDisplay()).toBe('none')

      const masterTowns = await mdPage.masterFieldTown
      expect(masterTowns[4]).toHaveValue(value)
    })
    it('6-can move to next page of navigator, and checking', async () => {
      await mdPage.navigatorMoveButtonNext.click()
      browser.pause(waiting)
      await mdPage.firstMasterButtonMoveToDetail.waitForExist()
      browser.pause(waiting)

      let masterCodes = await mdPage.masterFieldPostalCode
      let masterPrefs = await mdPage.masterFieldPref
      let masterCities = await mdPage.masterFieldCity
      let masterTowns = await mdPage.masterFieldTown
      expect(masterCodes.length).toBe(100)
      expect(masterPrefs.length).toBe(100)
      expect(masterCities.length).toBe(100)
      expect(masterTowns.length).toBe(100)
      expect(masterCodes[0]).toHaveValue('1006122')
      expect(masterPrefs[0]).toHaveValue('東京都')
      expect(masterCities[0]).toHaveValue('千代田区')
      expect(masterTowns[0]).toHaveValue('永田町山王パークタワー（２２階）')

      const buttons = await mdPage.masterButtonMoveToDetail
      buttons[1].click()
      browser.pause(waiting)
      await mdPage.detailButtonMoveToMaster.waitForExist()
      browser.pause(waiting)

      expect(mdPage.detailFieldPostalCode).toHaveValue('1006123')
      expect(mdPage.detailFieldPref).toHaveValue('東京都')
      expect(mdPage.detailFieldCity).toHaveValue('千代田区')
      expect(mdPage.detailFieldTown).toHaveValue('永田町山王パークタワー（２３階）')

      await expect(mdPage.navigator).toExist()
      expect(await mdPage.getNavigatorStyleDisplay()).toBe('none')
      await expect(mdPage.masterTable).toExist()
      expect(await mdPage.getMasterTableStyleDisplay()).toBe('none')
      await expect(mdPage.detailTable).toExist()
      expect(await mdPage.getDetailTableStyleDisplay()).not.toBe('none')

      const value = "######"
      await mdPage.detailFieldTown.setValue(value) // Set a value to the field
      browser.pause(waiting)

      const button = await mdPage.detailButtonMoveToMaster
      button.click()
      browser.pause(waiting)
      await mdPage.firstMasterButtonMoveToDetail.waitForExist()
      browser.pause(waiting)


      await expect(mdPage.navigator).toExist()
      expect(await mdPage.getNavigatorStyleDisplay()).not.toBe('none')
      await expect(mdPage.masterTable).toExist()
      expect(await mdPage.getMasterTableStyleDisplay()).not.toBe('none')
      await expect(mdPage.detailTable).toExist()
      expect(await mdPage.getDetailTableStyleDisplay()).toBe('none')

      masterCodes = await mdPage.masterFieldPostalCode
      masterPrefs = await mdPage.masterFieldPref
      masterCities = await mdPage.masterFieldCity
      masterTowns = await mdPage.masterFieldTown
      expect(masterCodes.length).toBe(100)
      expect(masterPrefs.length).toBe(100)
      expect(masterCities.length).toBe(100)
      expect(masterTowns.length).toBe(100)
      expect(masterCodes[2]).toHaveValue('1006123')
      expect(masterPrefs[2]).toHaveValue('東京都')
      expect(masterCities[2]).toHaveValue('千代田区')
      expect(masterTowns[2]).toHaveValue(value)
    })
    it('7-can back to scrolled position', async () => {
      await mdPage.navigatorMoveButtonNext.click()
      browser.pause(waiting)
      await mdPage.firstMasterButtonMoveToDetail.waitForExist()
      browser.pause(waiting)

      let masterCodes = await mdPage.masterFieldPostalCode
      let masterPrefs = await mdPage.masterFieldPref
      let masterCities = await mdPage.masterFieldCity
      let masterTowns = await mdPage.masterFieldTown
      expect(masterCodes.length).toBe(100)
      expect(masterPrefs.length).toBe(100)
      expect(masterCities.length).toBe(100)
      expect(masterTowns.length).toBe(100)
      expect(masterCodes[0]).toHaveValue('1006407')
      expect(masterPrefs[0]).toHaveValue('東京都')
      expect(masterCities[0]).toHaveValue('千代田区')
      expect(masterTowns[0]).toHaveValue('丸の内東京ビルディング（７階）')

      const buttons = await mdPage.masterButtonMoveToDetail
      buttons[99].click()
      browser.pause(waiting)
      await mdPage.detailButtonMoveToMaster.waitForExist()
      browser.pause(waiting)

      expect(mdPage.detailFieldPostalCode).toHaveValue('1006633')
      expect(mdPage.detailFieldPref).toHaveValue('東京都')
      expect(mdPage.detailFieldCity).toHaveValue('千代田区')
      expect(mdPage.detailFieldTown).toHaveValue('丸の内グラントウキョウサウスタワー（３３階）')

      await expect(mdPage.navigator).toExist()
      expect(await mdPage.getNavigatorStyleDisplay()).toBe('none')
      await expect(mdPage.masterTable).toExist()
      expect(await mdPage.getMasterTableStyleDisplay()).toBe('none')
      await expect(mdPage.detailTable).toExist()
      expect(await mdPage.getDetailTableStyleDisplay()).not.toBe('none')

      const button = await mdPage.detailButtonMoveToMaster
      button.click()
      browser.pause(waiting)
      await mdPage.firstMasterButtonMoveToDetail.waitForExist()
      browser.pause(waiting)

      await expect(mdPage.navigator).toExist()
      expect(await mdPage.getNavigatorStyleDisplay()).not.toBe('none')
      await expect(mdPage.masterTable).toExist()
      expect(await mdPage.getMasterTableStyleDisplay()).not.toBe('none')
      await expect(mdPage.detailTable).toExist()
      expect(await mdPage.getDetailTableStyleDisplay()).toBe('none')

      masterCodes = await mdPage.masterFieldPostalCode
      masterPrefs = await mdPage.masterFieldPref
      masterCities = await mdPage.masterFieldCity
      masterTowns = await mdPage.masterFieldTown

      expect(await masterCodes[0].isDisplayedInViewport()).toBe(false)
      expect(await masterCodes[99].isDisplayedInViewport()).toBe(true)
    })
  })
}


