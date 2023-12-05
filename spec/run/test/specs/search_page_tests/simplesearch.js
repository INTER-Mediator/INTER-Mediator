module.exports = (searchPage) => {
  const waiting = 1000

  describe('Master-Detail Page', () => {
    it('1-can show the Listing area, and showing all postalcode data.', async () => {
      await expect(searchPage.navigator).toExist()
      await expect(searchPage.masterTable).toExist()
      await browser.pause(waiting)
      await searchPage.button1.click() // all global variable are false. OR operation

      const masterCodes = await searchPage.masterFieldPostalCode
      const masterPrefs = await searchPage.masterFieldPref
      const masterCities = await searchPage.masterFieldCity
      const masterTowns = await searchPage.masterFieldTown
      expect(masterCodes.length).toBe(3654)
      expect(masterPrefs.length).toBe(3654)
      expect(masterCities.length).toBe(3654)
      expect(masterTowns.length).toBe(3654)
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
    /*
    f3 = "1710052"
     */
    it('2-can search from a postal code.', async () => {
      await browser.refresh()
      await searchPage.button1.click() // all global variable are false. OR operation
      await searchPage.searchPostalCode.setValue("1710052")
      await searchPage.searchButton.click()
      await expect(searchPage.navigator).toExist()
      await expect(searchPage.masterTable).toExist()

      const masterCodes = await searchPage.masterFieldPostalCode
      const masterPrefs = await searchPage.masterFieldPref
      const masterCities = await searchPage.masterFieldCity
      const masterTowns = await searchPage.masterFieldTown
      expect(masterCodes.length).toBe(1)
      expect(masterPrefs.length).toBe(1)
      expect(masterCities.length).toBe(1)
      expect(masterTowns.length).toBe(1)
      expect(masterCodes[0]).toHaveValue('1710052')
      expect(masterPrefs[0]).toHaveValue('東京都')
      expect(masterCities[0]).toHaveValue('豊島区')
      expect(masterTowns[0]).toHaveValue('南長崎')
      await searchPage.searchPostalCode.setValue("")
    });
    /*
    f8 like '%中%' OR f9 like '%中%'
     */
    it('3-can search from a part of string.', async () => {
      await browser.refresh()
      await searchPage.button1.click() // all global variable are false. OR operation
      // await searchPage.searchPostalCode.setValue("")
      // await searchPage.searchCity.setValue("")
      // await searchPage.searchTown.setValue("")
      await searchPage.searchAll.setValue("中")
      await searchPage.searchButton.click()
      await searchPage.sortAsc.click()
      await browser.pause(waiting)

      const masterCodes = await searchPage.masterFieldPostalCode
      const masterPrefs = await searchPage.masterFieldPref
      const masterCities = await searchPage.masterFieldCity
      const masterTowns = await searchPage.masterFieldTown
      expect(masterCodes.length).toBe(282)
      expect(masterPrefs.length).toBe(282)
      expect(masterCities.length).toBe(282)
      expect(masterTowns.length).toBe(282)
      expect(masterCodes[0]).toHaveValue('1001623')
      expect(masterPrefs[0]).toHaveValue('東京都')
      expect(masterCities[0]).toHaveValue('八丈島八丈町')
      expect(masterTowns[0]).toHaveValue('中之郷')
      expect(masterCodes[281]).toHaveValue('2080035')
      expect(masterPrefs[281]).toHaveValue('東京都')
      expect(masterCities[281]).toHaveValue('武蔵村山市')
      expect(masterTowns[281]).toHaveValue('中原')
    });
    it('4-can resort with sorting button.', async () => {
      await searchPage.sortDesc.click()
      // await browser.pause(waiting)
      const masterCodes = await searchPage.masterFieldPostalCode
      const masterPrefs = await searchPage.masterFieldPref
      const masterCities = await searchPage.masterFieldCity
      const masterTowns = await searchPage.masterFieldTown
      expect(masterCodes[281]).toHaveValue('1001623')
      expect(masterPrefs[281]).toHaveValue('東京都')
      expect(masterCities[281]).toHaveValue('八丈島八丈町')
      expect(masterTowns[281]).toHaveValue('中之郷')
      expect(masterCodes[0]).toHaveValue('2080035')
      expect(masterPrefs[0]).toHaveValue('東京都')
      expect(masterCities[0]).toHaveValue('武蔵村山市')
      expect(masterTowns[0]).toHaveValue('中原')

      await searchPage.searchAll.setValue("")
    });
    /*
   f8 like '%中%' AND f9 like '%中%'
    */
    it('5-can search from a part of string.', async () => {
      await browser.refresh()
      await expect(searchPage.navigator).toExist()
      await expect(searchPage.masterTable).toExist()
// await searchPage.button2.waitForClickable()
      await searchPage.button2.click() // all global variable are false. AND operation
      await searchPage.searchAll.setValue("中")
      await searchPage.searchButton.waitForClickable()
      await searchPage.searchButton.click()
      await searchPage.sortAsc.waitForClickable()
      await searchPage.sortAsc.click()
      await expect(searchPage.navigator).toExist()
      await expect(searchPage.masterTable).toExist()

      const masterCodes = await searchPage.masterFieldPostalCode
      const masterPrefs = await searchPage.masterFieldPref
      const masterCities = await searchPage.masterFieldCity
      const masterTowns = await searchPage.masterFieldTown
      expect(masterCodes.length).toBe(5)
      expect(masterPrefs.length).toBe(5)
      expect(masterCities.length).toBe(5)
      expect(masterTowns.length).toBe(5)
      expect(masterCodes[0]).toHaveValue('1030008')
      expect(masterPrefs[0]).toHaveValue('東京都')
      expect(masterCities[0]).toHaveValue('中央区')
      expect(masterTowns[0]).toHaveValue('日本橋中洲')
      expect(masterCodes[4]).toHaveValue('1830055')
      expect(masterPrefs[4]).toHaveValue('東京都')
      expect(masterCities[4]).toHaveValue('府中市')
      expect(masterTowns[4]).toHaveValue('府中町')
      await searchPage.searchAll.setValue("")
    });
    /*
    f8 like '%中野%' OR f9 like '%中野%'
     */
    it('6-can OR search with multiple conditions.', async () => {
      await browser.refresh()
      await expect(searchPage.navigator).toExist()
      await expect(searchPage.masterTable).toExist()
      await searchPage.button1.click() // all global variable are false. OR operation
      // await searchPage.searchPostalCode.setValue("")
      await searchPage.searchCity.setValue("中野")
      await searchPage.searchTown.setValue("中野")
      // await searchPage.searchAll.setValue("")
      await searchPage.searchButton.click()
      await searchPage.searchButton.click()
      await searchPage.sortAsc.waitForClickable()
      await searchPage.sortAsc.click()
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
    it('7-can AND search with multiple conditions.', async () => {
      await browser.refresh()
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
    it('8-can OR search with multiple conditions in one search field.', async () => {
      await browser.refresh()
      await expect(searchPage.navigator).toExist()
      await expect(searchPage.masterTable).toExist()
      await searchPage.button4.click() // all global variable are false. AND operation
      // await searchPage.searchPostalCode.setValue("")
      // await searchPage.searchCity.setValue("")
      // await searchPage.searchTown.setValue("")
      await searchPage.searchAll.setValue("北 南")
      await searchPage.searchButton.click()
      await searchPage.sortAsc.click()
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
    it('9-can AND search with multiple conditions in one search field.', async () => {
      await browser.refresh()
      await expect(searchPage.navigator).toExist()
      await expect(searchPage.masterTable).toExist()
      await searchPage.button5.click() // all global variable are false. AND operation
      // await searchPage.searchPostalCode.setValue("")
      // await searchPage.searchCity.setValue("")
      // await searchPage.searchTown.setValue("")
      await searchPage.searchAll.setValue("北 南")
      await searchPage.searchButton.click()
      await searchPage.sortAsc.click()
      await expect(searchPage.navigator).toExist()
      await expect(searchPage.masterTable).toExist()
      await browser.pause(waiting)

      const masterCodes = await searchPage.masterFieldPostalCode
      const masterPrefs = await searchPage.masterFieldPref
      const masterCities = await searchPage.masterFieldCity
      const masterTowns = await searchPage.masterFieldTown
      expect(masterCodes.length).toBe(1)
      expect(masterPrefs.length).toBe(1)
      expect(masterCities.length).toBe(1)
      expect(masterTowns.length).toBe(1)
      expect(masterCodes[0]).toHaveValue('1150044')
      expect(masterPrefs[0]).toHaveValue('東京都')
      expect(masterCities[0]).toHaveValue('北区')
      expect(masterTowns[0]).toHaveValue('赤羽南')

      await searchPage.searchAll.setValue("")
    });
  })
}


