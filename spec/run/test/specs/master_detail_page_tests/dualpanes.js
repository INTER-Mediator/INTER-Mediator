module.exports = (dualPage) => {
  const waiting = 500

  describe('Master-Detail Page', () => {
    it('1-can show both master and detail area.', async () => {
      await expect(dualPage.navigator).toExist()
      expect(await dualPage.getNavigatorStyleDisplay()).not.toBe('none')
      await expect(dualPage.masterTable).toExist()
      expect(await dualPage.getMasterTableStyleDisplay()).not.toBe('none')
      await expect(dualPage.detailTable).toExist()
      expect(await dualPage.getDetailTableStyleDisplay()).not.toBe('none')
      expect(await dualPage.detailButtonMoveToMaster).not.toExist()
    });
    it('2-can show the data from database.', async () => {
      const masterCodes = await dualPage.masterFieldPostalCode
      const masterPrefs = await dualPage.masterFieldPref
      const masterCities = await dualPage.masterFieldCity
      const masterTowns = await dualPage.masterFieldTown
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
      expect(dualPage.detailFieldPostalCode).toHaveValue('1000000')
      expect(dualPage.detailFieldPref).toHaveValue('東京都')
      expect(dualPage.detailFieldCity).toHaveValue('千代田区')
      expect(dualPage.detailFieldTown).toHaveValue('以下に掲載がない場合')
    });
    it('3-can change the data on the detail area', async () => {
      const buttons = await dualPage.masterButtonMoveToDetail
      buttons[1].click()
      expect(dualPage.detailFieldPostalCode).toHaveValue('1000001')
      expect(dualPage.detailFieldPref).toHaveValue('東京都')
      expect(dualPage.detailFieldCity).toHaveValue('千代田区')
      expect(dualPage.detailFieldTown).toHaveValue('千代田')
      buttons[2].click()
      expect(dualPage.detailFieldPostalCode).toHaveValue('1000002')
      expect(dualPage.detailFieldPref).toHaveValue('東京都')
      expect(dualPage.detailFieldCity).toHaveValue('千代田区')
      expect(dualPage.detailFieldTown).toHaveValue('皇居外苑')
    })
    it('4-can edit on the detail page and affect to the result of the master page', async () => {
      const buttons = await dualPage.masterButtonMoveToDetail
      buttons[3].click()
      const value = "######"
      await dualPage.detailFieldTown.setValue(value) // Set a value to the field
      browser.pause(waiting)
      const masterTowns = await dualPage.masterFieldTown
      expect(masterTowns[3]).toHaveValue(value)
    })
    it('5-can move to next page of navigator, and checking', async () => {
      await dualPage.navigatorMoveButtonNext.click()
      browser.pause(waiting)
      expect(await dualPage.firstMasterButtonMoveToDetail).toExist()

      let masterCodes = await dualPage.masterFieldPostalCode
      let masterPrefs = await dualPage.masterFieldPref
      let masterCities = await dualPage.masterFieldCity
      let masterTowns = await dualPage.masterFieldTown
      expect(masterCodes.length).toBe(100)
      expect(masterPrefs.length).toBe(100)
      expect(masterCities.length).toBe(100)
      expect(masterTowns.length).toBe(100)
      expect(masterCodes[0]).toHaveValue('1006122')
      expect(masterPrefs[0]).toHaveValue('東京都')
      expect(masterCities[0]).toHaveValue('千代田区')
      expect(masterTowns[0]).toHaveValue('永田町山王パークタワー（２２階）')
      expect(dualPage.detailFieldPostalCode).toHaveValue('1006122')
      expect(dualPage.detailFieldPref).toHaveValue('東京都')
      expect(dualPage.detailFieldCity).toHaveValue('千代田区')
      expect(dualPage.detailFieldTown).toHaveValue('永田町山王パークタワー（２２階）')

      await dualPage.navigatorMoveButtonNext.click()
      browser.pause(waiting)
      expect(await dualPage.firstMasterButtonMoveToDetail).toExist()

      masterCodes = await dualPage.masterFieldPostalCode
      masterPrefs = await dualPage.masterFieldPref
      masterCities = await dualPage.masterFieldCity
      masterTowns = await dualPage.masterFieldTown
      expect(masterCodes.length).toBe(100)
      expect(masterPrefs.length).toBe(100)
      expect(masterCities.length).toBe(100)
      expect(masterTowns.length).toBe(100)
      expect(masterCodes[0]).toHaveValue('1006407')
      expect(masterPrefs[0]).toHaveValue('東京都')
      expect(masterCities[0]).toHaveValue('千代田区')
      expect(masterTowns[0]).toHaveValue('丸の内東京ビルディング（７階）')
      expect(dualPage.detailFieldPostalCode).toHaveValue('1006407')
      expect(dualPage.detailFieldPref).toHaveValue('東京都')
      expect(dualPage.detailFieldCity).toHaveValue('千代田区')
      expect(dualPage.detailFieldTown).toHaveValue('丸の内東京ビルディング（７階）')

    })
  })
}


