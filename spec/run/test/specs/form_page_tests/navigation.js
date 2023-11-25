module.exports = (FormPage) => {
  const waiting = 500

  describe('Navigation', () => {
    it('1-has the INTER-Mediator\'s navigation.', async () => {
      await expect(FormPage.navigator).toExist()
      await expect(FormPage.navigatorUpdateButton).toExist()
      await expect(FormPage.navigatorInfo).toExist()
      await expect(FormPage.navigatorMoveButtons).toBeElementsArrayOfSize(4)
      await expect(FormPage.navigatorMoveButtonFirst).toExist()
      await expect(FormPage.navigatorMoveButtonFirst).toHaveText('<<')
      await expect(FormPage.navigatorMoveButtonPrevious).toExist()
      await expect(FormPage.navigatorMoveButtonPrevious).toHaveText('<')
      await expect(FormPage.navigatorMoveButtonNext).toExist()
      await expect(FormPage.navigatorMoveButtonNext).toHaveText('>')
      await expect(FormPage.navigatorMoveButtonLast).toExist()
      await expect(FormPage.navigatorMoveButtonLast).toHaveText('>>')
      await expect(FormPage.navigatorDeleteButton).toExist()
      await expect(FormPage.navigatorInsertButton).toExist()
      await expect(FormPage.navigatorCopyButton).toExist()
    });
    it('2-can move current record with the navigation.', async () => {
      await expect(FormPage.fieldPersonId).toExist()
      await expect(FormPage.fieldPersonId).toHaveText("1")
      await FormPage.navigatorMoveButtonNext.click()
      await browser.pause(waiting)
      await expect(FormPage.fieldPersonId).toExist()
      await expect(FormPage.fieldPersonId).toHaveText("2")
      await FormPage.navigatorMoveButtonNext.click()
      await browser.pause(waiting)
      await expect(FormPage.fieldPersonId).toExist()
      await expect(FormPage.fieldPersonId).toHaveText("3")
      await FormPage.navigatorMoveButtonPrevious.click()
      await browser.pause(waiting)
      await expect(FormPage.fieldPersonId).toExist()
      await expect(FormPage.fieldPersonId).toHaveText("2")
      await FormPage.navigatorMoveButtonLast.click()
      await browser.pause(waiting)
      await expect(FormPage.fieldPersonId).toExist()
      await expect(FormPage.fieldPersonId).toHaveText("3")
      await FormPage.navigatorMoveButtonFirst.click()
      await browser.pause(waiting)
      await expect(FormPage.fieldPersonId).toExist()
      await expect(FormPage.fieldPersonId).toHaveText("1")
    });
  })
}

