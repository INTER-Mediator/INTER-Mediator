module.exports = (AuthPage, isUserAuth = false) => {
  describe('Login required page with images', () => {
    const waiting = 1000

    const filePath1 = '../../samples/Sample_products/images/tomatos.png'
    const filePath2 = '../../samples/Sample_products/images/galia-melon.png'
    const filePath3 = '../../samples/Sample_products/images/mela-verde.png'
    const filePath4 = '../../samples/Sample_products/images/orange_1.png'
    const filePath5 = '../../samples/Sample_products/images/onion2.png'

    it('1-can open and show the login panel.', async () => {
      await AuthPage.open()
      await expect(AuthPage.navigator).not.toExist()
      await expect(AuthPage.authPanel).toExist()
    })
    it('2-login successfully.', async () => {
      await AuthPage.authUsername.setValue("user1")
      await AuthPage.authPassword.setValue("zuks69#bAkc")
      await AuthPage.authLoginButton.waitForClickable()
      await AuthPage.authLoginButton.click() // Finally login succeed.
      await browser.pause(waiting)
      await expect(AuthPage.auth2FAPanel).not.toExist()
    })
    it('3-Send a picture file and checking to show.', async () => {
      await browser.refresh()
      await browser.pause(waiting)

      const currentRecords = await AuthPage.fieldsItemUploading.length
      await AuthPage.itemInsertButton.waitForClickable()
      await AuthPage.itemInsertButton.click()
      await browser.pause(waiting)
      await browser.refresh()
      await browser.pause(waiting)
      await expect(AuthPage.fieldsItemUploading).toBeElementsArrayOfSize(currentRecords + 1)
      // const lastPicture = await AuthPage.fieldsItemPic[currentRecords]
      expect(await AuthPage.fieldsItemPic[currentRecords]).toExist()
      const lastWidget = await AuthPage.fieldsItemWidget[currentRecords]
      expect(lastWidget).toExist()
      expect(await AuthPage.fieldsItemPic[currentRecords].getSize('width')).toBeLessThan(20)
      // console.log(await lastPicture.getSize('width')) // This returns the value "16".

      // let currentWorkingDirectory = process.cwd();
      // console.log(currentWorkingDirectory);
      // On my Mac: /Users/msyk/Code/INTER-Mediator/spec/run

      const remoteFilePath = await browser.uploadFile(filePath1)
      const targetId = await lastWidget.getAttribute('id')
      const fileElement = await $(`#${targetId}-fileupload`)
      await expect(fileElement).toExist()
      await fileElement.setValue(remoteFilePath)
      const sendButton = await lastWidget.$('.filesend-button')
      await sendButton.waitForClickable()
      await expect(sendButton).toExist()
      await sendButton.click()

      // await AuthPage.itemInsertButton.waitForClickable()
      // await AuthPage.itemInsertButton.click() // Create 2 records
      await browser.pause(waiting)
      await browser.refresh()
      await browser.pause(waiting)

      // const lastPicture2 = await AuthPage.fieldsItemPic[currentRecords]
      expect(await AuthPage.fieldsItemPic[currentRecords].getSize('width')).toBeGreaterThan(40)
      // console.log(await AuthPage.fieldsItemPic[currentRecords].getSize('width')) // This returns the value "50".

      // let accessStatus = 0;
      // let imageSize = 0;
      // const href = await browser.getUrl()
      // const src = await AuthPage.fieldsItemPic[currentRecords].getAttribute('src')
      // await fetch(href + src, {}).then((response) => {
      //   accessStatus = response.status
      //   return response.blob()
      // }).then((blob) => {
      //   imageSize = blob.size
      // })
      // console.log(accessStatus, imageSize)
    })
    it('4-logging in another user, and the count of record is keeping in table privilege.', async () => {
      if (!isUserAuth) {
        const currentRecords = await AuthPage.fieldsItemUploading.length
        await AuthPage.logoutLink.waitForClickable()
        await AuthPage.logoutLink.click()
        await expect(AuthPage.authPanel).toExist()
        await AuthPage.authUsername.setValue("user2")
        await AuthPage.authPassword.setValue("zuks69#bAkc")
        await AuthPage.authLoginButton.waitForClickable()
        await AuthPage.authLoginButton.click() // Finally login succeed.
        await browser.pause(waiting)
        await expect(AuthPage.auth2FAPanel).not.toExist()

        await expect(AuthPage.fieldsItemUploading).toBeElementsArrayOfSize(currentRecords)
        await expect(AuthPage.fieldsItemPic).toBeElementsArrayOfSize(currentRecords)
        // const lastPicture = await AuthPage.fieldsItemPic[currentRecords - 1]
        expect(await await AuthPage.fieldsItemPic[currentRecords - 1].getSize('width')).toBeGreaterThan(40)
        // console.log(await AuthPage.fieldsItemPic[currentRecords].getSize('width')) // This returns the value "50".

        await AuthPage.itemInsertButton.waitForClickable()
        await AuthPage.itemInsertButton.click()
        await browser.pause(waiting)
        {
          expect(await AuthPage.fieldsItemPic[currentRecords]).toExist()
          const lastWidget = await AuthPage.fieldsItemWidget[currentRecords]
          expect(lastWidget).toExist()
          expect(await AuthPage.fieldsItemPic[currentRecords].getSize('width')).toBeLessThan(20)
          const remoteFilePath = await browser.uploadFile(filePath2)
          const targetId = await lastWidget.getAttribute('id')
          const fileElement = await $(`#${targetId}-fileupload`)
          await expect(fileElement).toExist()
          await fileElement.setValue(remoteFilePath)
          const sendButton = await lastWidget.$('.filesend-button')
          await sendButton.waitForClickable()
          await expect(sendButton).toExist()
          await sendButton.click()
          await browser.pause(waiting)
        }
        await AuthPage.itemInsertButton.waitForClickable()
        await AuthPage.itemInsertButton.click()
        await browser.pause(waiting)
        {
          expect(await AuthPage.fieldsItemPic[currentRecords]).toExist()
          const lastWidget = await AuthPage.fieldsItemWidget[currentRecords]
          expect(lastWidget).toExist()
          expect(await AuthPage.fieldsItemPic[currentRecords].getSize('width')).toBeGreaterThan(20)
          const remoteFilePath = await browser.uploadFile(filePath3)
          const targetId = await lastWidget.getAttribute('id')
          const fileElement = await $(`#${targetId}-fileupload`)
          await expect(fileElement).toExist()
          await fileElement.setValue(remoteFilePath)
          const sendButton = await lastWidget.$('.filesend-button')
          await sendButton.waitForClickable()
          await expect(sendButton).toExist()
          await sendButton.click()
          await browser.pause(waiting)
        }
        await browser.refresh()
        await browser.pause(waiting)

        await expect(AuthPage.fieldsItemUploading).toBeElementsArrayOfSize(currentRecords + 2)
        await expect(AuthPage.fieldsItemPic).toBeElementsArrayOfSize(currentRecords + 2)

        await AuthPage.logoutLink.waitForClickable()
        await AuthPage.logoutLink.click()
        await expect(AuthPage.authPanel).toExist()
        await AuthPage.authUsername.setValue("user3")
        await AuthPage.authPassword.setValue("zuks69#bAkc")
        await AuthPage.authLoginButton.waitForClickable()
        await AuthPage.authLoginButton.click() // Finally login succeed.
        await browser.pause(waiting)
        await expect(AuthPage.auth2FAPanel).not.toExist()

        await expect(AuthPage.fieldsItemUploading).toBeElementsArrayOfSize(currentRecords + 2)
        await expect(AuthPage.fieldsItemPic).toBeElementsArrayOfSize(currentRecords + 2)
      }
    })
    it('5-logging in another user, and the count of record is keepingin record privilege.', async () => {
      if (isUserAuth) {
        const href = (await browser.getUrl()).split("/").slice(0, -1).join("/") + "/"

        await AuthPage.logoutLink.waitForClickable()
        await AuthPage.logoutLink.click()
        await expect(AuthPage.authPanel).toExist()
        await AuthPage.authUsername.setValue("user1")
        await AuthPage.authPassword.setValue("zuks69#bAkc")
        await AuthPage.authLoginButton.waitForClickable()
        await AuthPage.authLoginButton.click() // Finally login succeed.
        await browser.pause(waiting)
        await expect(AuthPage.auth2FAPanel).not.toExist()
        const user1RecCount = await AuthPage.fieldsItemUploading.length

        await AuthPage.itemInsertButton.waitForClickable()
        await AuthPage.itemInsertButton.click()
        await browser.pause(waiting)
        {
          expect(await AuthPage.fieldsItemPic[user1RecCount]).toExist()
          const lastWidget = await AuthPage.fieldsItemWidget[user1RecCount]
          expect(lastWidget).toExist()
          expect(await AuthPage.fieldsItemPic[user1RecCount].getSize('width')).toBeLessThan(20)
          const remoteFilePath = await browser.uploadFile(filePath4)
          const targetId = await lastWidget.getAttribute('id')
          const fileElement = await $(`#${targetId}-fileupload`)
          await expect(fileElement).toExist()
          await fileElement.setValue(remoteFilePath)
          const sendButton = await lastWidget.$('.filesend-button')
          await sendButton.waitForClickable()
          await expect(sendButton).toExist()
          await sendButton.click()
          await browser.pause(waiting)
        }
        await AuthPage.itemInsertButton.waitForClickable()
        await AuthPage.itemInsertButton.click()
        await browser.pause(waiting)
        {
          expect(await AuthPage.fieldsItemPic[user1RecCount + 1]).toExist()
          const lastWidget = await AuthPage.fieldsItemWidget[user1RecCount + 1]
          expect(lastWidget).toExist()
          expect(await AuthPage.fieldsItemPic[user1RecCount + 1].getSize('width')).toBeLessThan(20)
          const remoteFilePath = await browser.uploadFile(filePath5)
          const targetId = await lastWidget.getAttribute('id')
          const fileElement = await $(`#${targetId}-fileupload`)
          await expect(fileElement).toExist()
          await fileElement.setValue(remoteFilePath)
          const sendButton = await lastWidget.$('.filesend-button')
          await sendButton.waitForClickable()
          await expect(sendButton).toExist()
          await sendButton.click()
          await browser.pause(waiting)
        }
        await browser.refresh()
        await browser.pause(waiting)
        await expect(AuthPage.fieldsItemUploading).toBeElementsArrayOfSize(user1RecCount + 2)
        await expect(AuthPage.fieldsItemPic).toBeElementsArrayOfSize(user1RecCount + 2)
        for (let i = 0; i < (user1RecCount + 2); i += 1) {
          await expect(AuthPage.fieldsUsername[i]).toHaveText("user1")
        }

        const user1src = await AuthPage.fieldsItemPic[user1RecCount + 1].getAttribute('src')
        const user1pic = href + user1src

        await AuthPage.logoutLink.waitForClickable()
        await AuthPage.logoutLink.click()
        await expect(AuthPage.authPanel).toExist()
        await AuthPage.authUsername.setValue("user2")
        await AuthPage.authPassword.setValue("zuks69#bAkc")
        await AuthPage.authLoginButton.waitForClickable()
        await AuthPage.authLoginButton.click() // Finally login succeed.
        await browser.pause(waiting)
        await expect(AuthPage.auth2FAPanel).not.toExist()
        const user2RecCount = await AuthPage.fieldsItemUploading.length

        await AuthPage.itemInsertButton.waitForClickable()
        await AuthPage.itemInsertButton.click()
        await browser.pause(waiting)
        {
          expect(await AuthPage.fieldsItemPic[user2RecCount]).toExist()
          const lastWidget = await AuthPage.fieldsItemWidget[user2RecCount]
          expect(lastWidget).toExist()
          expect(await AuthPage.fieldsItemPic[user2RecCount].getSize('width')).toBeLessThan(20)
          const remoteFilePath = await browser.uploadFile(filePath2)
          const targetId = await lastWidget.getAttribute('id')
          const fileElement = await $(`#${targetId}-fileupload`)
          await expect(fileElement).toExist()
          await fileElement.setValue(remoteFilePath)
          const sendButton = await lastWidget.$('.filesend-button')
          await sendButton.waitForClickable()
          await expect(sendButton).toExist()
          await sendButton.click()
          await browser.pause(waiting)
        }
        await AuthPage.itemInsertButton.waitForClickable()
        await AuthPage.itemInsertButton.click()
        await browser.pause(waiting)
        {
          expect(await AuthPage.fieldsItemPic[user2RecCount + 1]).toExist()
          const lastWidget = await AuthPage.fieldsItemWidget[user2RecCount + 1]
          expect(lastWidget).toExist()
          expect(await AuthPage.fieldsItemPic[user2RecCount + 1].getSize('width')).toBeLessThan(20)
          const remoteFilePath = await browser.uploadFile(filePath3)
          const targetId = await lastWidget.getAttribute('id')
          const fileElement = await $(`#${targetId}-fileupload`)
          await expect(fileElement).toExist()
          await fileElement.setValue(remoteFilePath)
          const sendButton = await lastWidget.$('.filesend-button')
          await sendButton.waitForClickable()
          await expect(sendButton).toExist()
          await sendButton.click()
          await browser.pause(waiting)
        }
        await AuthPage.itemInsertButton.waitForClickable()
        await AuthPage.itemInsertButton.click()
        await browser.pause(waiting)
        {
          expect(await AuthPage.fieldsItemPic[user2RecCount + 2]).toExist()
          const lastWidget = await AuthPage.fieldsItemWidget[user2RecCount + 2]
          expect(lastWidget).toExist()
          expect(await AuthPage.fieldsItemPic[user2RecCount + 2].getSize('width')).toBeLessThan(20)
          const remoteFilePath = await browser.uploadFile(filePath4)
          const targetId = await lastWidget.getAttribute('id')
          const fileElement = await $(`#${targetId}-fileupload`)
          await expect(fileElement).toExist()
          await fileElement.setValue(remoteFilePath)
          const sendButton = await lastWidget.$('.filesend-button')
          await sendButton.waitForClickable()
          await expect(sendButton).toExist()
          await sendButton.click()
          await browser.pause(waiting)
        }
        await browser.refresh()
        await browser.pause(waiting)

        await expect(AuthPage.fieldsItemUploading).toBeElementsArrayOfSize(user2RecCount + 3)
        await expect(AuthPage.fieldsItemPic).toBeElementsArrayOfSize(user2RecCount + 3)
        for (let i = 0; i < user2RecCount + 3; i += 1) {
          await expect(AuthPage.fieldsUsername[i]).toHaveText("user2")
        }
        const user2src = await AuthPage.fieldsItemPic[user2RecCount + 2].getAttribute('src')
        const user2pic = href + user2src

        await AuthPage.logoutLink.waitForClickable()
        await AuthPage.logoutLink.click()
        await expect(AuthPage.authPanel).toExist()
        await AuthPage.authUsername.setValue("user1")
        await AuthPage.authPassword.setValue("zuks69#bAkc")
        await AuthPage.authLoginButton.waitForClickable()
        await AuthPage.authLoginButton.click() // Finally login succeed.
        await browser.pause(waiting)
        await expect(AuthPage.auth2FAPanel).not.toExist()
        await expect(AuthPage.fieldsItemUploading).toBeElementsArrayOfSize(user1RecCount + 2)
        await expect(AuthPage.fieldsItemPic).toBeElementsArrayOfSize(user1RecCount + 2)
        for (let i = 0; i < user1RecCount + 2; i += 1) {
          await expect(AuthPage.fieldsUsername[i]).toHaveText("user1")
        }

        await AuthPage.logoutLink.waitForClickable()
        await AuthPage.logoutLink.click()
        await expect(AuthPage.authPanel).toExist()
        await AuthPage.authUsername.setValue("user2")
        await AuthPage.authPassword.setValue("zuks69#bAkc")
        await AuthPage.authLoginButton.waitForClickable()
        await AuthPage.authLoginButton.click() // Finally login succeed.
        await browser.pause(waiting)
        await expect(AuthPage.auth2FAPanel).not.toExist()
        await expect(AuthPage.fieldsItemUploading).toBeElementsArrayOfSize(user2RecCount + 3)
        await expect(AuthPage.fieldsItemPic).toBeElementsArrayOfSize(user2RecCount + 3)
        for (let i = 0; i < user2RecCount + 3; i += 1) {
          await expect(AuthPage.fieldsUsername[i]).toHaveText("user2")
        }
      }
      await AuthPage.logoutLink.waitForClickable()
      await AuthPage.logoutLink.click()
    })
  })
}
