module.exports = (AuthPage) => {
  describe('Login required page', () => {
    const waiting = 1500
    let isJapanese = false
    if (process.platform === 'darwin') {
      isJapanese = true
    }

    let noInputMsg, failMsg, errorMsg, cantChangePWMsg, changePWMsg, successMsg2FA, failMsg2FA, wrongMsg2FA
    if (isJapanese) {
      noInputMsg = "ユーザー名ないしはパスワードが入力されていません"
      failMsg = "ユーザー名とパスワードを確認して、もう一度ログインをしてください"
      errorMsg = "認証エラー!"
      cantChangePWMsg = "パスワードの変更に失敗しました。旧パスワードが違うなどが考えられます"
      changePWMsg = "パスワードの変更に成功しました。新しいパスワードでログインをしてください"
      successMsg2FA = "登録してあるメールアドレスにコードを送りました。そのコードを入力してください。"
      failMsg2FA = "コードを入力してください。もしくはコードの桁数が違います。"
      wrongMsg2FA = "入力したコードが違います。"
    } else {
      noInputMsg = "You should input user and/or password."
      failMsg = "Retry to login. You should clarify the user and the password."
      errorMsg = "Authentication Error!"
      cantChangePWMsg = "Failure to change your password. Maybe the old password is not correct."
      changePWMsg = "Succeed to change your password. Login with the new password."
      successMsg2FA = "Any code was sent to the registered mail address now, so it should be entered here."
      failMsg2FA = "The code has to be entered, or the digit of the code is invalid."
      wrongMsg2FA = "The code doesn't match."
    }

    it('1-can open with the valid title.', async () => {
      await AuthPage.open()
      // browser.pause(waiting)
      await expect(AuthPage.navigator).not.toExist()
    })

    it('2-shows the login panel.', async () => {
      await expect(AuthPage.authPanel).toExist()
    })

    it('3-succeed login with authenticated user.', async () => {
      await browser.refresh()
//      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
      await AuthPage.authUsername.setValue("user1")
      await AuthPage.authPassword.setValue("user1")
      await AuthPage.authLoginButton.click() // login succeed.
//      await browser.pause(waiting)
      await expect(AuthPage.authPanel).not.toExist()
      await expect(AuthPage.auth2FAPanel).not.toExist()
      await expect(AuthPage.logoutLink).toHaveText("Logout")
      await AuthPage.logoutLink.waitForClickable()
      await AuthPage.logoutLink.click()
//      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
    })

    it('4-succeed login with authenticated group.', async () => {
      await browser.refresh()
//      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
      await AuthPage.authUsername.setValue("user4")
      await AuthPage.authPassword.setValue("user4") // user4 belongs to group2.
      await AuthPage.authLoginButton.click() // login succeed.
//      await browser.pause(waiting)
      await expect(AuthPage.authPanel).not.toExist()
      await expect(AuthPage.auth2FAPanel).not.toExist()
      await expect(AuthPage.logoutLink).toHaveText("Logout")
      await AuthPage.logoutLink.waitForClickable()
      await AuthPage.logoutLink.click()
//      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
    })

    it('5-failed login without authenticated group.', async () => {
      await browser.refresh()
//      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
      await AuthPage.authUsername.setValue("user2") // not in authentication/user also group
      await AuthPage.authPassword.setValue("user2")
      await AuthPage.authLoginButton.click()
      await browser.pause(waiting)
      await expect(AuthPage.authPanel).toExist()
      await expect(AuthPage.authLoginMessage).toHaveText(failMsg)
    })
  })
}
