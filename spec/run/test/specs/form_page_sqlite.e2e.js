const FormPage = require('../pageobjects/form_sqlite.page');

describe('Form Page', () => {
  it('can open with the valid title', async () => {
    await FormPage.open()
    await expect(browser).toHaveTitle('INTER-Mediator - Sample - Form Style/SQLite')
  });
  it('has the INTER-Mediator\'s navigatio.n', async () => {
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
    await expect(FormPage.navigatorInfoInsertButton).toExist()
    await expect(FormPage.navigatorInfoCopy).toExist()
  });
  it('can move current record with the navigation.', async () => {
    await expect(FormPage.fieldPersonId).toExist()
    await expect(FormPage.fieldPersonId).toHaveText("1")
    await FormPage.navigatorMoveButtonNext.click()
    await expect(FormPage.fieldPersonId).toExist()
    await expect(FormPage.fieldPersonId).toHaveText("2")
    await FormPage.navigatorMoveButtonNext.click()
    await expect(FormPage.fieldPersonId).toExist()
    await expect(FormPage.fieldPersonId).toHaveText("3")
    await FormPage.navigatorMoveButtonPrevious.click()
    await expect(FormPage.fieldPersonId).toExist()
    await expect(FormPage.fieldPersonId).toHaveText("2")
    await FormPage.navigatorMoveButtonLast.click()
    await expect(FormPage.fieldPersonId).toExist()
    await expect(FormPage.fieldPersonId).toHaveText("3")
    await FormPage.navigatorMoveButtonFirst.click()
    await expect(FormPage.fieldPersonId).toExist()
    await expect(FormPage.fieldPersonId).toHaveText("1")
  });
  it('can retrieve the first record from database.', async () => {
    await FormPage.navigatorMoveButtonFirst.click()
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
  it('can retrieve the second record from database.', async () => {
    await FormPage.navigatorMoveButtonNext.click()
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
  it('can retrieve the third record from database.', async () => {
    await FormPage.navigatorMoveButtonNext.click()
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
  it('can edit the first record.', async () => {
    await FormPage.navigatorUpdateButton.click();
    await expect(FormPage.fieldPersonId).toHaveText("1")
    await FormPage.fieldPersonCategory.selectByVisibleText('Family')
    await FormPage.fieldPersonCheck.click()
    await FormPage.fieldPersonName.setValue("edit1")
    await FormPage.fieldPersonLocations[0].click()
    await FormPage.fieldPersonMemo.setValue("first\nsecond\nthird")
  });
  it('can store the edited data on the first record.', async () => {
    await FormPage.navigatorUpdateButton.click();
    await FormPage.navigatorMoveButtonNext.click();
    await FormPage.navigatorMoveButtonPrevious.click();
    await expect(FormPage.fieldPersonId).toHaveText("1")
    await expect(FormPage.fieldPersonCategory).toHaveValue("101")
    await expect(FormPage.fieldPersonCategory).toHaveText("Family\nClassMate\nCollegue")
    await expect(FormPage.fieldPersonCheck).toBeSelected()
    await expect(FormPage.fieldPersonName).toHaveValue("edit1")
    await expect(FormPage.fieldPersonMail).toHaveValue("msyk@msyk.net")
    await expect(FormPage.fieldPersonLocations[0]).toBeSelected()
    await expect(FormPage.fieldPersonLocations[1]).not.toBeSelected()
    await expect(FormPage.fieldPersonLocations[2]).not.toBeSelected()
    await expect(FormPage.fieldPersonLocations[3]).not.toBeSelected()
    await expect(FormPage.fieldPersonMemo).toHaveValue("first\nsecond\nthird")
  });
  // it('can NOT store the edited data within 5 seconds.', async () => {
  //   await FormPage.navigatorUpdateButton.click();
  //   await FormPage.fieldPersonName.setValue("edit2")
  //   browser.closeWindow()
  // });
  // it('can NOT store the edited data within 5 seconds as the result.', async () => {
  //   const FormPage2 = require('../pageobjects/form.page');
  //   await FormPage2.open()
  //   await expect(FormPage2.fieldPersonName).toHaveValue("edit1")
  // });
});


