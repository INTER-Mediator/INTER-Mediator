const AuthPage = require('../pageobjects/auth_page_mysql.page');

const waiting = 2000

const noInputMsg = "You should input user and/or password."
const failMsg = "Retry to login. You should clarify the user and the password."
const errorMsg = "Authentication Error!"
const cantChangePWMsg = "Failure to change your password. Maybe the old password is not correct."
const changePWMsg = "Succeed to change your password. Login with the new password."
// const noInputMsg = "ユーザー名ないしはパスワードが入力されていません"
// const failMsg = "ユーザー名とパスワードを確認して、もう一度ログインをしてください"
// const errorMsg = "認証エラー!"
// const cantChangePWMsg = "Failure to change your password. Maybe the old password is not correct."
// const changePWMsg = "Succeed to change your password. Login with the new password."

describe('Login required page', () => {
  it('can open with the valid title.', async () => {
    await AuthPage.open()
    await expect(browser).toHaveTitle("INTER-Mediator - Sample - Auth/MySQL"/*'INTER-Mediator - サンプル - フォーム形式/MySQL'*/)
    // browser.pause(waiting)
    await expect(AuthPage.navigator).not.toExist()
  })
  it('shows the login panel.', async () => {
    await expect(AuthPage.authPanel).toExist()
  })
  it('declines wrong account.', async () => {
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

  it('succeed login after 1 mistake.', async () => {
    await browser.refresh()
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

  it('succeed login after 2 mistake.', async () => {
    await browser.refresh()
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

  it('succeed login without mistake and continue to logging in.', async () => {
    await browser.refresh()
    await expect(AuthPage.authPanel).toExist()
    await AuthPage.authUsername.setValue("user1")
    await AuthPage.authPassword.setValue("user1")
    await AuthPage.authLoginButton.click() // Finally login succeed.
    await browser.pause(waiting)
    await expect(AuthPage.authPanel).not.toExist()

    await browser.refresh()
    await expect(AuthPage.authPanel).not.toExist() // Still logging in

    await browser.refresh()
    await expect(AuthPage.authPanel).not.toExist() // Still logging in

    await expect(AuthPage.logoutLink).toHaveText("Logout")
    await AuthPage.logoutLink.waitForClickable()
    await AuthPage.logoutLink.click()
    await browser.pause(waiting)
    await expect(AuthPage.authPanel).toExist() // logged out
  })

  it('works timeout to login.', async () => {
    await browser.refresh()
    await expect(AuthPage.authPanel).toExist()
    await AuthPage.authUsername.setValue("user1")
    await AuthPage.authPassword.setValue("user1")
    await AuthPage.authLoginButton.click() // Finally login succeed.
    await browser.pause(waiting)
    await expect(AuthPage.authPanel).not.toExist()

    await browser.pause(10000) // Wait for timeout

    await browser.refresh()
    await expect(AuthPage.authPanel).toExist() // logged out
  })

  it('can change the password.', async () => {
    await browser.refresh()
    await expect(AuthPage.authPanel).toExist()
    await AuthPage.authUsername.setValue("user1")
    await AuthPage.authPassword.setValue("dfjdjfadsklfjdksa")
    await AuthPage.authNewPassword.setValue("testtest")
    await AuthPage.authChangePWButton.click() // Change the password with wrong login info.
    await expect(AuthPage.authPanel).toExist()
    await expect(AuthPage.authNewPasswordMessage).toHaveText(cantChangePWMsg) // Succeed to change by this message

    await browser.refresh()
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

    await AuthPage.logoutLink.click()
    await expect(AuthPage.authPanel).toExist()
    await AuthPage.authUsername.setValue("user1")
    await AuthPage.authPassword.setValue("testtest")
    await AuthPage.authNewPassword.setValue("user1")
    await AuthPage.authChangePWButton.click() // Back the password to previous one.
    await expect(AuthPage.authPanel).toExist()
  })
})


