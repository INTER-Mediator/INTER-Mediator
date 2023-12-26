module.exports = (searchPage) => {
  const waiting = 1000

  describe('Master-Detail Page', () => {
    /*
    f8 like '%中野%' OR f9 like '%中野%'
     */
    it('1-can OR search with multiple conditions.', async () => {
      // await browser.refresh()
      await expect(searchPage.navigator).toExist()
      await expect(searchPage.masterTable).toExist()
      await searchPage.button1.click() // all global variable are false. OR operation
      // await searchPage.searchPostalCode.setValue("")
      await searchPage.searchCity.setValue("中野")
      await searchPage.searchTown.setValue("中野")
      await searchPage.searchAll.setValue("")
      await searchPage.searchButton.click()
      // await searchPage.searchButton.click()
      // await searchPage.sortAsc.waitForClickable()
      // await searchPage.sortAsc.click()
      await expect(searchPage.navigator).toExist()
      await expect(searchPage.masterTable).toExist()
      await browser.pause(waiting)

      const masterCodes = await searchPage.masterFieldPostalCode
      const masterPrefs = await searchPage.masterFieldPref
      const masterCities = await searchPage.masterFieldCity
      const masterTowns = await searchPage.masterFieldTown
      expect(masterCodes.length).toBe(25)
      expect(masterPrefs.length).toBe(25)
      expect(masterCities.length).toBe(25)
      expect(masterTowns.length).toBe(25)
      expect(masterCodes[0]).toHaveValue('1640000')
      expect(masterPrefs[0]).toHaveValue('東京都')
      expect(masterCities[0]).toHaveValue('中野区')
      expect(masterTowns[0]).toHaveValue('以下に掲載がない場合')
      expect(masterCodes[24]).toHaveValue('1920351')
      expect(masterPrefs[24]).toHaveValue('東京都')
      expect(masterCities[24]).toHaveValue('八王子市')
      expect(masterTowns[24]).toHaveValue('東中野')

      await searchPage.searchCity.setValue("")
      await searchPage.searchTown.setValue("")
    });
    /*
    f8 like '%中野%' AND f9 like '%中野%'
     */
    it('2-can AND search with multiple conditions.', async () => {
      // await browser.refresh()
      await expect(searchPage.navigator).toExist()
      await expect(searchPage.masterTable).toExist()
      await searchPage.button3.click() // all global variable are false. AND operation
      // await searchPage.searchPostalCode.setValue("")
      await searchPage.searchCity.setValue("中野")
      await searchPage.searchTown.setValue("中野")
      // await searchPage.searchAll.setValue("")
      await searchPage.searchButton.click()
      await searchPage.sortAsc.click()
      await expect(searchPage.navigator).toExist()
      await expect(searchPage.masterTable).toExist()
      await browser.pause(waiting)

      const masterCodes = await searchPage.masterFieldPostalCode
      const masterPrefs = await searchPage.masterFieldPref
      const masterCities = await searchPage.masterFieldCity
      const masterTowns = await searchPage.masterFieldTown
      expect(masterCodes.length).toBe(2)
      expect(masterPrefs.length).toBe(2)
      expect(masterCities.length).toBe(2)
      expect(masterTowns.length).toBe(2)
      expect(masterCodes[0]).toHaveValue('1640001')
      expect(masterPrefs[0]).toHaveValue('東京都')
      expect(masterCities[0]).toHaveValue('中野区')
      expect(masterTowns[0]).toHaveValue('中野')
      expect(masterCodes[1]).toHaveValue('1640003')
      expect(masterPrefs[1]).toHaveValue('東京都')
      expect(masterCities[1]).toHaveValue('中野区')
      expect(masterTowns[1]).toHaveValue('東中野')

      await searchPage.searchCity.setValue("")
      await searchPage.searchTown.setValue("")
    });
    /*
    (f8 like '%北%' OR f8 like '%南%') OR (f9 like '%北%' OR f9 like '%南%')
     */
    it('3-can OR search with multiple conditions in one search field.', async () => {
      // await browser.refresh()
      await expect(searchPage.navigator).toExist()
      await expect(searchPage.masterTable).toExist()
      await searchPage.button4.click() // all global variable are false. AND operation
      // await searchPage.searchPostalCode.setValue("")
      await searchPage.searchCity.setValue("")
      await searchPage.searchTown.setValue("")
      await searchPage.searchAll.setValue("北 南")
      await searchPage.searchButton.click()
      // await searchPage.sortAsc.click()
      await expect(searchPage.navigator).toExist()
      await expect(searchPage.masterTable).toExist()
      await browser.pause(waiting)

      const masterCodes = await searchPage.masterFieldPostalCode
      const masterPrefs = await searchPage.masterFieldPref
      const masterCities = await searchPage.masterFieldCity
      const masterTowns = await searchPage.masterFieldTown
      expect(masterCodes.length).toBe(255)
      expect(masterPrefs.length).toBe(255)
      expect(masterCities.length).toBe(255)
      expect(masterTowns.length).toBe(255)
      expect(masterCodes[0]).toHaveValue('1010036')
      expect(masterPrefs[0]).toHaveValue('東京都')
      expect(masterCities[0]).toHaveValue('千代田区')
      expect(masterTowns[0]).toHaveValue('神田北乗物町')
      expect(masterCodes[254]).toHaveValue('2080013')
      expect(masterPrefs[254]).toHaveValue('東京都')
      expect(masterCities[254]).toHaveValue('武蔵村山市')
      expect(masterTowns[254]).toHaveValue('大南')
      await searchPage.searchAll.setValue("")
    });
    /*
    (f8 like '%北%' OR f8 like '%南%') AND (f9 like '%北%' OR f9 like '%南%')
     */
    it('4-can AND search with multiple conditions in one search field.', async () => {
      // await browser.refresh()
      await expect(searchPage.navigator).toExist()
      await expect(searchPage.masterTable).toExist()
      await searchPage.button5.click() // all global variable are false. AND operation
      // await searchPage.searchPostalCode.setValue("")
      await searchPage.searchCity.setValue("")
      await searchPage.searchTown.setValue("")
      await searchPage.searchAll.setValue("北 南")
      await searchPage.searchButton.click()
      // await searchPage.sortAsc.click()
      await expect(searchPage.navigator).toExist()
      await expect(searchPage.masterTable).toExist()
      await browser.pause(waiting)

      await expect(searchPage.masterFieldPostalCode).toBeElementsArrayOfSize(1)
      await expect(searchPage.masterFieldPostalCode).toBeElementsArrayOfSize(1)
      await expect(searchPage.masterFieldCity).toBeElementsArrayOfSize(1)
      await expect(searchPage.masterFieldTown).toBeElementsArrayOfSize(1)
      const masterCodes = await searchPage.masterFieldPostalCode
      const masterPrefs = await searchPage.masterFieldPref
      const masterCities = await searchPage.masterFieldCity
      const masterTowns = await searchPage.masterFieldTown
      expect(masterCodes[0]).toHaveValue('1150044')
      expect(masterPrefs[0]).toHaveValue('東京都')
      expect(masterCities[0]).toHaveValue('北区')
      expect(masterTowns[0]).toHaveValue('赤羽南')

      await searchPage.searchAll.setValue("")
    });
  })
}


