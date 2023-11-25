module.exports = (AuthPage) => {
  describe('Login required page', () => {
    const waiting = 500
    const alwaysEnglish = true
    const alwaysJapaese = false
    let isJapanese = false
    if (alwaysEnglish && !alwaysJapaese) {
      isJapanese = false
    } else if (!alwaysEnglish && alwaysJapaese) {
      isJapanese = true
    } else if (process.platform === 'darwin') {
      isJapanese = true
    }

    let noInputMsg, failMsg, errorMsg, cantChangePWMsg, changePWMsg
    if (isJapanese) {
      noInputMsg = "ユーザー名ないしはパスワードが入力されていません"
      failMsg = "ユーザー名とパスワードを確認して、もう一度ログインをしてください"
      errorMsg = "認証エラー!"
      cantChangePWMsg = "パスワードの変更に失敗しました。旧パスワードが違うなどが考えられます"
      changePWMsg = "パスワードの変更に成功しました。新しいパスワードでログインをしてください"
    } else {
      noInputMsg = "You should input user and/or password."
      failMsg = "Retry to login. You should clarify the user and the password."
      errorMsg = "Authentication Error!"
      cantChangePWMsg = "Failure to change your password. Maybe the old password is not correct."
      changePWMsg = "Succeed to change your password. Login with the new password."
    }

    it('1-can open with the valid title.', async () => {
      await AuthPage.open()
      // browser.pause(waiting)
      await expect(AuthPage.navigator).not.toExist()
    })
    it('2-shows the login panel.', async () => {
      await expect(AuthPage.authPanel).toExist()
    })
    it('3-declines wrong account.', async () => {
      await AuthPage.authUsername.setValue("")
      await AuthPage.authPassword.setValue("")
      await AuthPage.authLoginButton.click()
      await expect(AuthPage.authPanel).toExist()
      await expect(AuthPage.authLoginMessage).toHaveText(noInputMsg)

      await AuthPage.authUsername.setValue("dsakjjljl")
      await AuthPage.authPassword.setValue("dsakjjljl")
      await AuthPage.authLoginButton.click()
      await expect(AuthPage.authPanel).toExist()
      await expect(AuthPage.authLoginMessage).toHaveText(failMsg)

      await AuthPage.authUsername.setValue("dsakjjljl")
      await AuthPage.authPassword.setValue("dsakjjljl")
      await AuthPage.authLoginButton.click()
      await expect(AuthPage.authPanel).toExist()
      await AuthPage.authUsername.setValue("dsakjjljl")
      await AuthPage.authPassword.setValue("dsakjjljl")
      await AuthPage.authLoginButton.click()
      await expect(AuthPage.authPanel).toExist()
      await AuthPage.authUsername.setValue("dsakjjljl")
      await AuthPage.authPassword.setValue("dsakjjljl")
      await AuthPage.authLoginButton.click()
      await expect(AuthPage.authPanel).toExist() // Fail to login with wrong account 5 times.
      await AuthPage.authUsername.setValue("dsakjjljl")
      await AuthPage.authPassword.setValue("dsakjjljl")
      await AuthPage.authLoginButton.click() // This is 6th try, and show the message.
      await expect(AuthPage.authPanel).not.toExist()
      await expect(AuthPage.authErrorMessage).toExist()
      await expect(AuthPage.authErrorMessage).toHaveText(errorMsg)
    })

    it('4-succeed login after 1 mistake.', async () => {
      await browser.refresh()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
      await AuthPage.authUsername.setValue("dsakjjljl")
      await AuthPage.authPassword.setValue("dsakjjljl")
      await AuthPage.authLoginButton.click() // One mistake to login
      await expect(AuthPage.authPanel).toExist()
      await expect(AuthPage.authLoginMessage).toHaveText(failMsg)

      await AuthPage.authUsername.setValue("user1")
      await AuthPage.authPassword.setValue("user1")
      await AuthPage.authLoginButton.click() // Finally login succeed.
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).not.toExist()

      await expect(AuthPage.logoutLink).toHaveText("Logout")
      await AuthPage.logoutLink.waitForClickable()
      await AuthPage.logoutLink.click()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
    })

    it('5-succeed login after 2 mistake.', async () => {
      await browser.refresh()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
      await AuthPage.authUsername.setValue("dsakjjljl")
      await AuthPage.authPassword.setValue("dsakjjljl")
      await AuthPage.authLoginButton.click() // One mistake to login
      await expect(AuthPage.authPanel).toExist()
      await expect(AuthPage.authLoginMessage).toHaveText(failMsg)

      await AuthPage.authUsername.setValue("dsakjjljl")
      await AuthPage.authPassword.setValue("dsakjjljl")
      await AuthPage.authLoginButton.click() // One more mistake to login
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
      await expect(AuthPage.authLoginMessage).toHaveText(failMsg)

      await AuthPage.authUsername.setValue("user1")
      await AuthPage.authPassword.setValue("user1")
      await AuthPage.authLoginButton.click() // Finally login succeed.
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).not.toExist()

      await expect(AuthPage.logoutLink).toHaveText("Logout")
      await AuthPage.logoutLink.waitForClickable()
      await AuthPage.logoutLink.click()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
    })

    it('6-succeed login without mistake and continue to logging in.', async () => {
      await browser.refresh()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
      await AuthPage.authUsername.setValue("user1")
      await AuthPage.authPassword.setValue("user1")
      await AuthPage.authLoginButton.click() // Finally login succeed.
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).not.toExist()

      await browser.refresh()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).not.toExist() // Still logging in

      await browser.refresh()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).not.toExist() // Still logging in

      await expect(AuthPage.logoutLink).toHaveText("Logout")
      await AuthPage.logoutLink.waitForClickable()
      await AuthPage.logoutLink.click()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist() // logged out
    })

    it('7-succeed login with sha-256 hashed users.', async () => {
      await browser.refresh()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
      await AuthPage.authUsername.setValue("mig2m")
      await AuthPage.authPassword.setValue("mig2m")
      await AuthPage.authLoginButton.click() // login succeed.
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).not.toExist()

      await browser.refresh()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).not.toExist() // Still logging in

      await expect(AuthPage.logoutLink).toHaveText("Logout")
      await AuthPage.logoutLink.waitForClickable()
      await AuthPage.logoutLink.click()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist() // logged out

      await browser.refresh()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
      await AuthPage.authUsername.setValue("mig2")
      await AuthPage.authPassword.setValue("mig2")
      await AuthPage.authLoginButton.click() // login succeed.
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).not.toExist()

      await expect(AuthPage.logoutLink).toHaveText("Logout")
      await AuthPage.logoutLink.waitForClickable()
      await AuthPage.logoutLink.click()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist() // logged out
    })

    it('8-works timeout to login.', async () => {
      await browser.refresh()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
      await AuthPage.authUsername.setValue("user1")
      await AuthPage.authPassword.setValue("user1")
      await AuthPage.authLoginButton.click() // Finally login succeed.
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).not.toExist()

      await browser.pause(10000) // Wait for timeout

      await browser.refresh()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist() // logged out
    })

    it('9-can change the password.', async () => {
      await browser.refresh()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
      await AuthPage.authUsername.setValue("user1")
      await AuthPage.authPassword.setValue("dfjdjfadsklfjdksa")
      await AuthPage.authNewPassword.setValue("testtest")
      await AuthPage.authChangePWButton.click() // Change the password with wrong login info.
      await expect(AuthPage.authPanel).toExist()
      await expect(AuthPage.authNewPasswordMessage).toHaveText(cantChangePWMsg) // Succeed to change by this message

      await browser.refresh()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
      await AuthPage.authUsername.setValue("user1")
      await AuthPage.authPassword.setValue("user1")
      await AuthPage.authNewPassword.setValue("testtest")
      await AuthPage.authChangePWButton.click() // Change the password
      await expect(AuthPage.authPanel).toExist()
      await expect(AuthPage.authNewPasswordMessage).toHaveText(changePWMsg) // Succeed to change by this message

      await AuthPage.authUsername.setValue("user1")
      await AuthPage.authPassword.setValue("user1")
      await AuthPage.authLoginButton.click() // Fail to login with previous password
      await expect(AuthPage.authPanel).toExist()
      await expect(AuthPage.authLoginMessage).toHaveText(failMsg)

      await AuthPage.authUsername.setValue("user1")
      await AuthPage.authPassword.setValue("testtest")
      await AuthPage.authLoginButton.click() // can login with new password
      await expect(AuthPage.authPanel).not.toExist()

      await AuthPage.logoutLink.waitForClickable()
      await AuthPage.logoutLink.click()
      await expect(AuthPage.authPanel).toExist()
      await AuthPage.authUsername.setValue("user1")
      await AuthPage.authPassword.setValue("testtest")
      await AuthPage.authNewPassword.setValue("user1")
      await AuthPage.authChangePWButton.click() // Back the password to previous one.
      await expect(AuthPage.authPanel).toExist()
    })
  })
}
