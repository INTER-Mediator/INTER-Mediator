module.exports = (FormPage) => {
  const waiting = 500

  describe('Simple Query', () => {
    it('1-can retrieve the first record from database.', async () => {
      await FormPage.navigatorUpdateButton.waitForClickable();
      await FormPage.navigatorMoveButtonFirst.click()
      await browser.pause(waiting)

      await expect(FormPage.fieldPersonId).toHaveText("1")
      await expect(FormPage.fieldPersonCategory).toHaveValue("")
      await expect(FormPage.fieldPersonCategory).toHaveText("Family\nClassMate\nCollegue")
      await expect(FormPage.fieldPersonCheck).not.toBeSelected()
      await expect(FormPage.fieldPersonName).toHaveValue("Masayuki Nii")
      await expect(FormPage.fieldPersonMail).toHaveValue("msyk@msyk.net")
      await expect(FormPage.fieldPersonLocations[0]).not.toBeSelected()
      await expect(FormPage.fieldPersonLocations[1]).not.toBeSelected()
      await expect(FormPage.fieldPersonLocations[2]).not.toBeSelected()
      await expect(FormPage.fieldPersonLocations[3]).not.toBeSelected()
      await expect(FormPage.fieldPersonMemo).toHaveValue("")
    });
    it('2-can retrieve the second record from database.', async () => {
      await FormPage.navigatorUpdateButton.waitForClickable();
      await FormPage.navigatorMoveButtonNext.click()
      await browser.pause(waiting)

      await expect(FormPage.fieldPersonId).toHaveText("2")
      await expect(FormPage.fieldPersonCategory).toHaveValue("")
      await expect(FormPage.fieldPersonCategory).toHaveText("Family\nClassMate\nCollegue")
      await expect(FormPage.fieldPersonCheck).not.toBeSelected()
      await expect(FormPage.fieldPersonName).toHaveValue("Someone")
      await expect(FormPage.fieldPersonMail).toHaveValue("msyk@msyk.net")
      await expect(FormPage.fieldPersonLocations[0]).not.toBeSelected()
      await expect(FormPage.fieldPersonLocations[1]).not.toBeSelected()
      await expect(FormPage.fieldPersonLocations[2]).not.toBeSelected()
      await expect(FormPage.fieldPersonLocations[3]).not.toBeSelected()
      await expect(FormPage.fieldPersonMemo).toHaveValue("")
    });
    it('3-can retrieve the third record from database.', async () => {
      await FormPage.navigatorUpdateButton.waitForClickable();
      await FormPage.navigatorMoveButtonNext.click()
      await browser.pause(waiting)

      await expect(FormPage.fieldPersonId).toHaveText("3")
      await expect(FormPage.fieldPersonCategory).toHaveValue("")
      await expect(FormPage.fieldPersonCategory).toHaveText("Family\nClassMate\nCollegue")
      await expect(FormPage.fieldPersonCheck).not.toBeSelected()
      await expect(FormPage.fieldPersonName).toHaveValue("Anyone")
      await expect(FormPage.fieldPersonMail).toHaveValue("msyk@msyk.net")
      await expect(FormPage.fieldPersonLocations[0]).not.toBeSelected()
      await expect(FormPage.fieldPersonLocations[1]).not.toBeSelected()
      await expect(FormPage.fieldPersonLocations[2]).not.toBeSelected()
      await expect(FormPage.fieldPersonLocations[3]).not.toBeSelected()
      await expect(FormPage.fieldPersonMemo).toHaveValue("")
    });
  })
}

