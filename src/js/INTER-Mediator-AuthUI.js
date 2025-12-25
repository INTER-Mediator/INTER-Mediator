/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

// JSHint support
/* global IMLibContextPool, INTERMediator, IMLibMouseEventDispatch, IMLibLocalContext,
 IMLibChangeEventDispatch, INTERMediatorLib, INTERMediator_DBAdapter, IMLibQueue, IMLibCalc, IMLibUI,
 IMLibEventResponder, INTERMediatorLog, IMLib, JSEncrypt, IMLibAuthentication, INTERMediatorOnPage */
/* jshint -W083 */ // Function within a loop
/**
 * @fileoverview IMLibAuthenticationUI class is defined here.
 */
let IMLibAuthenticationUI = {
  /**
   * Class handling page-level functionality for INTER-Mediator
   * @type {Object}
   */
  /** Maximum number of authentication attempts allowed */
  authCountLimit: 4,
  /** Whether the last credential check succeeded */
  succeedCredential: false,
  /** Treat username as an email address format on UI */
  isEmailAsUsername: false,
  /** Password policy description or rules string */
  passwordPolicy: null,
  /** Custom HTML to use for the login panel instead of generated DOM */
  loginPanelHTML: null,
  /** Show change-password inputs on a login panel */
  isShowChangePassword: true,
  /** Use built-in default styles for panels */
  isSetDefaultStyle: false,
  /** Title on login panel */
  authPanelTitle: null,
  /** Title on 2FA panel */
  authPanelTitle2FA: null,
  /** Extra HTML appended on login panel */
  authPanelExp: null,
  /** Extra HTML appended on the 2FA panel */
  authPanelExp2FA: null,
  /** Whether OAuth buttons are available on the panel */
  isOAuthAvailable: false, // @Private
  /** SAML logout URL provided by the server */
  logoutURL: null,
  /** SAML login URL provided by server */
  loginURL: null,
  /** Callback executed after showing a login panel */
  doAfterLoginPanel: null,
  /** CSS class for copy button on generated UI */
  buttonClassCopy: null,
  /** CSS class for delete button on generated UI */
  buttonClassDelete: null,
  /** CSS class for insert button on generated UI */
  buttonClassInsert: null,
  /** CSS class for master navigation button on generated UI */
  buttonClassMaster: null,
  /** CSS class for back navigation button on generated UI */
  buttonClassBackNavi: null,
  /** Whether to enable client service-related features */
  activateClientService: false,
  /** Additional labeled buttons to render on login panel */
  extraButtons: null,
  /** Number of digits required for 2FA code */
  digitsOf2FACode: null,
  /** Whether 2FA is required */
  isRequired2FA: false,
  /** Authenticated username (if any) */
  authedUser: null,
  /** Force ASCII-only username */
  userNameJustASCII: true,
  /** Force ASCII-only uppercase username */
  userNameJustASCIIUpper: false,
  /** Force ASCII-only lowercase username */
  userNameJustASCIILower: false,
  /** Whether passkey (WebAuthn) registration is available */
  isPasskey: false,
  /** Callback executed after authentication succeeds */
  doAfterAuth: null,

  /** URL of enrollment page (optional) */
  enrollPageURL: null,
  /** URL of password reset page (optional) */
  resetPageURL: null,
  /** Use SAML with a built-in authentication flow */
  samlWithBuiltInAuth: false,

  /** Whether current page is for passkey registration UI */
  isPasskeyRegistrationPage: false,
  /** If true, skip username/password UI and start passkey authentication immediately */
  isPasskeyOnlyOnAuth: false,
  /** If true, add additional WebAuthn-related attributes/classes to the login form */
  isAddClassAuthn: false,
  /** If true, omit confirmation UI for passkey (if supported by the flow) */
  isOmitPasskeyConfirm: false,
  /** Serialized passkey options JSON returned from server */
  passkeyOption: null,

  /**
   * Show the login panel and drive the authentication flow.
   * @param {Function} doAfterAuth Callback after successful authentication
   * @param {boolean} [doTest] If true, only test rather than execute
   */
  authenticating: function (doAfterAuth, doTest) {
    'use strict'
    if (doTest) {
      return
    }
    if (!IMLibAuthenticationUI.authedUser) {
      IMLibAuthentication.authCount = 0
    }
    if (IMLibAuthentication.authCount > IMLibAuthenticationUI.authCountLimit) {
      IMLibAuthenticationUI.authenticationError()
      IMLibAuthentication.logout(false, true)
      INTERMediatorLog.flushMessage()
      return
    }
    if (IMLibAuthenticationUI.isPasskey && IMLibAuthenticationUI.isPasskeyOnlyOnAuth) {
      IMLibAuthenticationUI.passkeyAuthentication(doAfterAuth)
      return
    }

    let userBox, passwordBox, authButton, oAuthButton = [], chgpwButton
    let samlButton, passkeyButton, extButtons = {}
    IMLibAuthenticationUI.doAfterAuth = doAfterAuth
    const bodyNode = document.getElementsByTagName('BODY')[0]
    const backBox = document.createElement('div')
    backBox.id = '_im_authpback'
    bodyNode.insertBefore(backBox, bodyNode.childNodes[0])
    if (IMLibAuthenticationUI.isSetDefaultStyle) {
      backBox.style.height = '100%'
      backBox.style.width = '100%'
      let url = INTERMediatorOnPage.getEntryPath()
      url = INTERMediatorLib.mergeURLParameter(url, 'theme', IMLibAuthentication.getTheme())
      url = INTERMediatorLib.mergeURLParameter(url, 'type', 'images')
      url = INTERMediatorLib.mergeURLParameter(url, 'name', 'background.gif')
      backBox.style.backgroundImage = `url(${url})`
      backBox.style.position = 'absolute'
      backBox.style.padding = ' 50px 0 0 0'
      backBox.style.top = '0'
      backBox.style.left = '0'
      backBox.style.zIndex = '777777'
    }

    if (IMLibAuthenticationUI.loginPanelHTML) {
      backBox.innerHTML = IMLibAuthenticationUI.loginPanelHTML
      passwordBox = document.getElementById('_im_password')
      userBox = document.getElementById('_im_username')
      authButton = document.getElementById('_im_authbutton')
      chgpwButton = document.getElementById('_im_changebutton')
      for (let provider in IMLibAuthentication.oAuthParams) {
        if (IMLibAuthentication.oAuthParams[provider] && IMLibAuthentication.oAuthParams[provider].Behavior !== 'not-show-on-login-panel') {
          oAuthButton[provider] = document.getElementById(`_im_oauthbutton-${provider}`)
        }
      }
      samlButton = document.getElementById('_im_samlbutton')
    } else {
      const frontPanel = document.createElement('div')
      if (IMLibAuthenticationUI.isSetDefaultStyle) {
        frontPanel.style.width = '450px'
        frontPanel.style.backgroundColor = '#333333'
        frontPanel.style.color = '#DDDDAA'
        frontPanel.style.margin = '50px auto 0 auto'
        frontPanel.style.padding = '20px'
        frontPanel.style.borderRadius = '10px'
        frontPanel.style.position = 'relative'
      }
      frontPanel.id = '_im_authpanel'
      backBox.appendChild(frontPanel)

      const panelTitle = IMLibAuthenticationUI.authPanelTitle ? IMLibAuthenticationUI.authPanelTitle : (IMLibAuthentication.realm ? IMLibAuthentication.realm : '')
      if (panelTitle && panelTitle.length > 0) {
        const realmBox = document.createElement('DIV')
        realmBox.appendChild(document.createTextNode(panelTitle))
        // realmBox.style.textAlign = 'left'
        realmBox.id = '_im_authrealm'
        frontPanel.appendChild(realmBox)
        const breakLine = document.createElement('HR')
        frontPanel.appendChild(breakLine)
      }

      const labelWidth = '100px'
      const userLabel = document.createElement('LABEL')
      frontPanel.appendChild(userLabel)
      const userSpan = document.createElement('span')
      if (IMLibAuthenticationUI.isSetDefaultStyle) {
        userSpan.style.minWidth = labelWidth
        userSpan.style.textAlign = 'right'
        userSpan.style.cssFloat = 'left'
      }
      userSpan.setAttribute('class', '_im_authlabel')
      userLabel.appendChild(userSpan)
      const msgNumber = IMLibAuthenticationUI.isEmailAsUsername ? 2011 : 2002
      userSpan.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(msgNumber)))
      userBox = document.createElement('INPUT')
      userBox.type = 'text'
      userBox.id = '_im_username'
      userBox.size = '20'
      userBox.setAttribute('autocapitalize', 'off')
      userLabel.appendChild(userBox)

      const breakLine = document.createElement('BR')
      breakLine.clear = 'all'
      frontPanel.appendChild(breakLine)

      const passwordLabel = document.createElement('LABEL')
      frontPanel.appendChild(passwordLabel)
      const passwordSpan = document.createElement('SPAN')
      if (IMLibAuthenticationUI.isSetDefaultStyle) {
        passwordSpan.style.minWidth = labelWidth
        passwordSpan.style.textAlign = 'right'
        passwordSpan.style.cssFloat = 'left'
      }
      passwordSpan.setAttribute('class', '_im_authlabel')
      passwordLabel.appendChild(passwordSpan)
      passwordSpan.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2003)))
      passwordBox = document.createElement('INPUT')
      passwordBox.type = 'password'
      passwordBox.id = '_im_password'
      passwordBox.size = '20'
      passwordLabel.appendChild(passwordBox)

      authButton = document.createElement('BUTTON')
      authButton.id = '_im_authbutton'
      authButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2004)))
      frontPanel.appendChild(authButton)

      {
        const breakLine = document.createElement('BR')
        breakLine.clear = 'all'
        frontPanel.appendChild(breakLine)
      }

      let newPasswordMessage = document.createElement('DIV')
      if (IMLibAuthenticationUI.isSetDefaultStyle) {
        newPasswordMessage.style.textAlign = 'center'
        newPasswordMessage.style.textSize = '10pt'
        newPasswordMessage.style.color = '#994433'
      }
      newPasswordMessage.id = '_im_login_message'
      frontPanel.appendChild(newPasswordMessage)

      if (IMLibAuthenticationUI.isShowChangePassword) {
        const breakLine = document.createElement('HR')
        frontPanel.appendChild(breakLine)

        const newPasswordLabel = document.createElement('LABEL')
        frontPanel.appendChild(newPasswordLabel)
        const newPasswordSpan = document.createElement('SPAN')
        if (IMLibAuthenticationUI.isSetDefaultStyle) {
          newPasswordSpan.style.minWidth = labelWidth
          newPasswordSpan.style.textAlign = 'right'
          newPasswordSpan.style.cssFloat = 'left'
          newPasswordSpan.style.fontSize = '0.7em'
          newPasswordSpan.style.paddingTop = '4px'
        }
        newPasswordSpan.setAttribute('class', '_im_authlabel_pwchange')
        newPasswordLabel.appendChild(newPasswordSpan)
        newPasswordSpan.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2006)))
        const newPasswordBox = document.createElement('INPUT')
        newPasswordBox.type = 'password'
        newPasswordBox.id = '_im_newpassword'
        newPasswordBox.size = '12'
        newPasswordLabel.appendChild(newPasswordBox)
        chgpwButton = document.createElement('BUTTON')
        chgpwButton.id = '_im_changebutton'
        chgpwButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2005)))
        frontPanel.appendChild(chgpwButton)

        newPasswordMessage = document.createElement('DIV')
        if (IMLibAuthenticationUI.isSetDefaultStyle) {
          newPasswordMessage.style.textAlign = 'center'
          newPasswordMessage.style.textSize = '10pt'
          newPasswordMessage.style.color = '#994433'
        }
        newPasswordMessage.id = '_im_newpass_message'
        frontPanel.appendChild(newPasswordMessage)
      }
      if ((IMLibAuthenticationUI.extraButtons && Object.keys(IMLibAuthenticationUI.extraButtons).length > 0)
        || IMLibAuthenticationUI.isOAuthAvailable
        || (IMLibAuthenticationUI.isPasskey && !IMLibAuthenticationUI.isPasskeyRegistrationPage)
        || (IMLibAuthentication.isSAML && IMLibAuthenticationUI.samlWithBuiltInAuth)) {
        const breakLine = document.createElement('HR')
        frontPanel.appendChild(breakLine)
      }
      if (IMLibAuthenticationUI.isOAuthAvailable) {
        for (let provider in IMLibAuthentication.oAuthParams) {
          if (IMLibAuthentication.oAuthParams[provider] && IMLibAuthentication.oAuthParams[provider].Behavior !== 'no-show-on-login-panel') {
            oAuthButton[provider] = document.createElement('BUTTON')
            const classOfProvider = (provider.indexOf("_") > -1) ? provider.substring(0, provider.indexOf("_")) : provider
            oAuthButton[provider].id = '_im_oauthbutton_' + classOfProvider.toLowerCase()
            oAuthButton[provider].disabled = false
            const buttonLabel = document.createTextNode(IMLibAuthentication.oAuthParams[provider].AuthButton)
            oAuthButton[provider].appendChild(buttonLabel)
            frontPanel.appendChild(oAuthButton[provider])
          }
        }
      }
      if (IMLibAuthentication.isSAML && IMLibAuthenticationUI.samlWithBuiltInAuth) {
        samlButton = document.createElement('BUTTON')
        samlButton.id = '_im_samlbutton'
        samlButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2026)))
        frontPanel.appendChild(samlButton)
      }
      for (const key in IMLibAuthenticationUI.extraButtons) {
        extButtons[key] = document.createElement('BUTTON')
        const key4id = [...key.matchAll(/(\w)/g)].reduce((acc, cur) => {
          return acc + cur[0]
        }, "")
        extButtons[key].id = `_im_extbutton_${key4id}`
        extButtons[key].appendChild(document.createTextNode(key))
        const moveURL = IMLibAuthenticationUI.extraButtons[key]
        extButtons[key].onclick = function () {
          location.href = moveURL
        }
        frontPanel.appendChild(extButtons[key])
      }
      if (IMLibAuthentication.enrollPageURL) {
        const breakLine = document.createElement('HR')
        frontPanel.appendChild(breakLine)
        const addingButton = document.createElement('BUTTON')
        addingButton.id = '_im_enrollbutton'
        addingButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2022)))
        addingButton.onclick = function () {
          location.href = IMLibAuthentication.enrollPageURL
        }
        frontPanel.appendChild(addingButton)
        if (IMLibAuthenticationUI.authedUser && IMLibAuthentication.authStoring === 'session-storage') {
          const messageNode = document.getElementById('_im_login_message')
          INTERMediatorLib.removeChildNodesAppendText(messageNode, 2012)
        }
      }
      if (IMLibAuthenticationUI.resetPageURL) {
        const breakLine = document.createElement('HR')
        frontPanel.appendChild(breakLine)
        const addingButton = document.createElement('BUTTON')
        addingButton.id = '_im_resetbutton'
        addingButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2023)))
        addingButton.onclick = function () {
          location.href = IMLibAuthenticationUI.resetPageURL
        }
        frontPanel.appendChild(addingButton)
        const resetMessage = document.createElement('div')
        resetMessage.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2024)))
        frontPanel.appendChild(resetMessage)
      }
      if (IMLibAuthenticationUI.authPanelExp) {
        const breakLine = document.createElement('HR')
        frontPanel.appendChild(breakLine)
        const addingNode = document.createElement('DIV')
        addingNode.className = '_im_auth_exp'
        addingNode.innerHTML = IMLibAuthenticationUI.authPanelExp
        frontPanel.appendChild(addingNode)
      }
      if (IMLibAuthenticationUI.isPasskey && !IMLibAuthenticationUI.isPasskeyRegistrationPage) {
        passkeyButton = document.createElement('BUTTON')
        passkeyButton.id = '_im_passkey'
        passkeyButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2034)))
        frontPanel.appendChild(passkeyButton)
        if (IMLibAuthenticationUI.isAddClassAuthn) {
          userBox.autocomplete = "webauthn"
        }
      }
    }
    // Setting event handlers to the buttons on the login panel.
    passwordBox.onkeydown = async function (event) {
      if (event.code === 'Enter') {
        await IMLibAuthenticationUI.authButtonClick()
      }
    }
    userBox.value = IMLibAuthentication.authUser()
    userBox.onkeydown = function (event) {
      if ((event.code === 'Enter' || event.code === 'NumpadEnter') && !event.isComposing) {
        passwordBox.focus()
      }
    }
    authButton.onclick = IMLibAuthenticationUI.authButtonClick
    if (chgpwButton) {
      chgpwButton.onclick = IMLibAuthenticationUI.changePWButtonClick
    }
    if (IMLibAuthenticationUI.isOAuthAvailable && oAuthButton) {
      for (let provider in IMLibAuthentication.oAuthParams) {
        if (IMLibAuthentication.oAuthParams[provider]
          && IMLibAuthentication.oAuthParams[provider].Behavior !== 'no-show-on-login-panel') {
          oAuthButton[provider].onclick = function (event) {
            if (!IMLibAuthentication.checkUIEventDT()) { // Prevent multiple click
              return
            }
            if (!IMLibAuthentication.oAuthParams[provider].AuthURL) {
              const messageNode = document.getElementById('_im_login_message')
              messageNode.appendChild(document.createTextNode(
                INTERMediatorLib.getInsertedStringFromErrorNumber(1059)))
              return
            }
            location.href = IMLibAuthentication.oAuthParams[provider].AuthURL
          }
        }
      }
    }
    if (IMLibAuthentication.isSAML && samlButton) {
      samlButton.onclick = function () {
        location.href = IMLibAuthenticationUI.loginURL
      }
    }
    if (IMLibAuthenticationUI.isPasskey && passkeyButton) {
      passkeyButton.onclick = function (event) {
        IMLibAuthenticationUI.passkeyButtonClick(event)
      }
    }

    if (IMLibAuthentication.authCount > 0) {
      const messageNode = document.getElementById('_im_login_message')
      INTERMediatorLib.removeChildNodesAppendText(messageNode, 2012)
    }

    window.scrollTo(0, 0)
    userBox.focus()
    IMLibAuthentication.authCount++
    if (IMLibAuthenticationUI.doAfterLoginPanel) {
      IMLibAuthenticationUI.doAfterLoginPanel(false)
    }
  },

  /**
   * Show authentication error overlay panel.
   */
  authenticationError: function () {
    'use strict'
    INTERMediatorOnPage.hideProgress()
    const bodyNode = document.getElementsByTagName('BODY')[0]
    const backBox = document.createElement('div')
    backBox.id = '_im_autherrorback'
    bodyNode.insertBefore(backBox, bodyNode.childNodes[0])
    if (IMLibAuthenticationUI.isSetDefaultStyle) {
      backBox.style.height = '100%'
      backBox.style.width = '100%'
      // backBox.style.backgroundColor = '#BBBBBB'
      if (IMLibAuthenticationUI.isSetDefaultStyle) {
        let url = INTERMediatorOnPage.getEntryPath()
        url = INTERMediatorLib.mergeURLParameter(url, 'theme', IMLibAuthentication.getTheme())
        url = INTERMediatorLib.mergeURLParameter(url, 'type', 'images')
        url = INTERMediatorLib.mergeURLParameter(url, 'name', 'background-error.gif')
        backBox.style.backgroundImage = `url(${url})`
      }
      backBox.style.position = 'absolute'
      backBox.style.padding = ' 50px 0 0 0'
      backBox.style.top = '0'
      backBox.style.left = '0'
      backBox.style.zIndex = '555555'
    }
    const frontPanel = document.createElement('div')
    frontPanel.id = '_im_autherrormessage'
    if (IMLibAuthenticationUI.isSetDefaultStyle) {
      frontPanel.style.width = '240px'
      frontPanel.style.backgroundColor = '#333333'
      frontPanel.style.color = '#DD6666'
      frontPanel.style.fontSize = '16pt'
      frontPanel.style.margin = '50px auto 0 auto'
      frontPanel.style.padding = '20px 4px 20px 4px'
      frontPanel.style.borderRadius = '10px'
      frontPanel.style.position = 'relative'
      frontPanel.style.textAlign = 'Center'
    }
    frontPanel.onclick = function () {
      bodyNode.removeChild(backBox)
    }
    backBox.appendChild(frontPanel)
    frontPanel.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2001)))
  },

  /**
   * Handler for the login/authenticate button.
   * Validates inputs, fetches a challenge if necessary, and requests credentials.
   * @returns {Promise<void>}
   */
  authButtonClick: async function () {
    await INTERMediatorOnPage.hideProgress()
    let messageNode = document.getElementById('_im_newpass_message')
    if (messageNode) {
      INTERMediatorLib.removeChildNodes(messageNode)
    }

    let inputUsername = document.getElementById('_im_username').value
    const inputPassword = document.getElementById('_im_password').value
    if (IMLibAuthenticationUI.userNameJustASCII) {
      inputUsername = INTERMediatorLib.justfyUsername(inputUsername)
    } else if (IMLibAuthenticationUI.userNameJustASCIIUpper) {
      inputUsername = INTERMediatorLib.justfyUsername(inputUsername, true)
    } else if (IMLibAuthenticationUI.userNameJustASCIILower) {
      inputUsername = INTERMediatorLib.justfyUsername(inputUsername, false, true)
    }

    if (inputUsername === '' || inputPassword === '') {
      messageNode = document.getElementById('_im_login_message')
      INTERMediatorLib.removeChildNodes(messageNode)
      messageNode.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2013)))
      return
    }
    IMLibAuthentication.authUser(inputUsername)
    const bodyNode = document.getElementsByTagName('BODY')[0]
    const backBox = document.getElementById('_im_authpback')
    if (backBox && bodyNode) {
      bodyNode.removeChild(backBox)
    }
    if (inputUsername !== '' && // No usename and no challenge, get a challenge.
      (IMLibAuthentication.authChallenge === null || IMLibAuthentication.authChallenge.length < 48)) {
      IMLibAuthentication.storedHashedPasswordAllClear('need-hash-pls')
      await INTERMediator_DBAdapter.getChallenge()
    }
    if (IMLibAuthentication.passwordHash < 1.1) {
      IMLibAuthentication.authHashedPassword(INTERMediatorLib.generatePasswrdHashV1(inputPassword, IMLibAuthentication.authUserSalt))
    }
    if (IMLibAuthentication.passwordHash < 1.6) {
      IMLibAuthentication.authHashedPassword2m(INTERMediatorLib.generatePasswrdHashV2m(inputPassword, IMLibAuthentication.authUserSalt))
    }
    if (IMLibAuthentication.passwordHash < 2.1) {
      IMLibAuthentication.authHashedPassword2(INTERMediatorLib.generatePasswrdHashV2(inputPassword, IMLibAuthentication.authUserSalt))
    }
    IMLibAuthenticationUI.succeedCredential = false
    if (IMLibAuthentication.authStoring === 'credential') {
      await INTERMediator_DBAdapter.getCredential()
      if (IMLibAuthenticationUI.succeedCredential) { // Succeed to log in.
        if (IMLibAuthenticationUI.isRequired2FA) { // Moving to the 2FA panel.
          IMLibAuthenticationUI.show2FAPanel(IMLibAuthenticationUI.doAfterAuth)
        } else {
          IMLibAuthenticationUI.doAfterAuth() // Logging-in successfully.
        }
      }
    } else if (IMLibAuthentication.authStoring === 'session-storage') {
      IMLibAuthenticationUI.doAfterAuth() // Retry.
    }
    INTERMediatorLog.flushMessage()
    await INTERMediatorOnPage.hideProgress(true)
  },

  /**
   * Start WebAuthn registration (passkey) for the current user.
   * @returns {Promise<void>}
   */
  getPasskey: async function () {
    const challengeHex = IMLibAuthentication.passkeyChallenge || '';
    const challengeBytes = new Uint8Array((challengeHex.match(/.{1,2}/g) || []).map(h => parseInt(h, 16)));

    const options = {
      publicKey: {
        challenge: challengeBytes,
        rp: {name: "Example CORP", id: location.hostname},
      }
    }
    navigator.credentials.get(options)
      .then((credentialInfoAssertion) => {
        const response = credentialInfoAssertion.response;
        const clientDataStr = new TextDecoder('utf-8').decode(response.clientDataJSON)
        const result = JSON.parse(clientDataStr)
        console.log(result)
        const clientExtensionsResults = credentialInfoAssertion.getClientExtensionResults();
        console.log(clientExtensionsResults)
      })
      .catch((err) => {
        console.error(err);
      });
  },

  /**
   * Handler for the change-password button.
   */
  changePWButtonClick: function () {
    INTERMediatorLib.removeChildNodes(document.getElementById('_im_login_message'))
    INTERMediatorLib.removeChildNodes(document.getElementById('_im_newpass_message'))

    const inputUsername = document.getElementById('_im_username').value
    const inputPassword = document.getElementById('_im_password').value
    const inputNewPassword = document.getElementById('_im_newpassword').value
    if (inputUsername === '' || inputPassword === '' || inputNewPassword === '') {
      INTERMediatorLib.removeChildNodesAppendText(document.getElementById('_im_newpass_message'))
      return
    }
    // hecking the password policy if specified.
    const message = IMLibAuthenticationUI.checkPasswordPolicy(inputNewPassword, inputUsername, IMLibAuthenticationUI.passwordPolicy)
    if (message.length > 0) { // Policy violated.
      document.getElementById('_im_newpass_message').appendChild(document.createTextNode(message.join(', ')))
    }
    INTERMediator_DBAdapter.changePassword(inputUsername, inputPassword, inputNewPassword, () => {
      document.getElementById('_im_newpass_message').appendChild(
        document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2009)))
      INTERMediatorLog.flushMessage()
    }, () => {
      document.getElementById('_im_newpass_message').appendChild(
        document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2010)))
      INTERMediatorLog.flushMessage()
    })
  },

  checkPasswordPolicy: function (pw, userName, policy) {
    // Checking the password policy if specified.
    let message = []
    if (policy) {
      const terms = policy.split(/[\s,]/)
      for (let i = 0; i < terms.length; i++) {
        switch (terms[i].toUpperCase()) {
          case 'USEALPHABET':
            if (!pw.match(/[A-Za-z]/)) {
              message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2015))
            }
            break
          case 'USENUMBER':
            if (!pw.match(/[0-9]/)) {
              message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2016))
            }
            break
          case 'USEUPPER':
            if (!pw.match(/[A-Z]/)) {
              message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2017))
            }
            break
          case 'USELOWER':
            if (!pw.match(/[a-z]/)) {
              message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2018))
            }
            break
          case 'USEPUNCTUATION':
            if (!pw.match(/[^A-Za-z0-9]/)) {
              message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2019))
            }
            break
          case 'NOTUSERNAME':
            if (pw === userName) {
              message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2020))
            }
            break
          default:
            if (terms[i].toUpperCase().indexOf('LENGTH') === 0) {
              const minLen = parseInt(terms[i].match(/[0-9]+/)[0])
              if (pw.length < minLen) {
                message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2021, [minLen]))
              }
            }
        }
      }
    }

    return message
  },

  /**
   * Show the 2FA input panel.
   * @param {Function} doAfterAuth Callback after successful 2FA
   */
  show2FAPanel: (doAfterAuth) => {
    let authButton
    IMLibAuthenticationUI.doAfterAuth = doAfterAuth
    const bodyNode = document.getElementsByTagName('BODY')[0]
    const backBox = document.createElement('div')
    backBox.id = '_im_authpback_2FA'
    bodyNode.insertBefore(backBox, bodyNode.childNodes[0])
    const frontPanel = document.createElement('div')
    frontPanel.id = '_im_authpanel_2FA'
    backBox.appendChild(frontPanel)

    if (IMLibAuthenticationUI.authPanelTitle2FA || IMLibAuthentication.realm) {
      const realmBox = document.createElement('DIV')
      realmBox.appendChild(document.createTextNode(IMLibAuthenticationUI.authPanelTitle ? IMLibAuthenticationUI.authPanelTitle : (IMLibAuthentication.realm ? IMLibAuthentication.realm : '')))
      realmBox.id = '_im_authrealm_2FA'
      frontPanel.appendChild(realmBox)
      const breakLine = document.createElement('HR')
      frontPanel.appendChild(breakLine)
    }
    const userLabel = document.createElement('LABEL')
    frontPanel.appendChild(userLabel)

    const codeSpan = document.createElement('span')
    codeSpan.setAttribute('class', '_im_authlabel_2FA')
    codeSpan.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2028)))
    userLabel.appendChild(codeSpan)

    const codeBox = document.createElement('INPUT')
    codeBox.type = 'text'
    codeBox.id = '_im_code_2FA'
    codeBox.size = '10'
    codeBox.setAttribute('autocapitalize', 'off')
    userLabel.appendChild(codeBox)

    authButton = document.createElement('BUTTON')
    authButton.id = '_im_authbutton_2FA'
    authButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2029)))
    frontPanel.appendChild(authButton)

    const explain = document.createElement('div')
    explain.setAttribute('id', '_im_explain_2FA')
    explain.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2030)))
    frontPanel.appendChild(explain)

    if (IMLibAuthenticationUI.authPanelExp2FA) {
      const breakLine = document.createElement('HR')
      frontPanel.appendChild(breakLine)
      const addingNode = document.createElement('DIV')
      addingNode.className = '_im_auth_exp_2fa'
      addingNode.innerHTML = IMLibAuthenticationUI.authPanelExp2FA
      frontPanel.appendChild(addingNode)
    }

    window.scrollTo(0, 0)
    codeBox.focus()
    IMLibAuthentication.authCount++
    if (IMLibAuthenticationUI.doAfterLoginPanel) {
      IMLibAuthenticationUI.doAfterLoginPanel(true)
    }

    codeBox.onkeydown = function (event) {
      if (event.code === 'Enter') {
        authButton.onclick()
      }
    }
    authButton.onclick = IMLibAuthenticationUI.click2FAuthButton
  },

  /**
   * Handler for 2FA authentication confirm button.
   * @returns {Promise<void>}
   */
  click2FAuthButton: async function () {
    await INTERMediatorOnPage.hideProgress()
    const inputCode = document.getElementById('_im_code_2FA').value
    if (inputCode === '' || inputCode.length !== IMLibAuthenticationUI.digitsOf2FACode) {
      INTERMediatorLib.removeChildNodesAppendText(document.getElementById('_im_explain_2FA'), 2031)
      return
    }
    IMLibAuthentication.authHashedPassword2(inputCode)
    await INTERMediator_DBAdapter.getCredential2FA()
    if (IMLibAuthenticationUI.succeedCredential) {
      const bodyNode = document.getElementsByTagName('BODY')[0]
      const backBox = document.getElementById('_im_authpback_2FA')
      if (bodyNode && backBox) {
        bodyNode.removeChild(backBox)
      }
      IMLibAuthenticationUI.doAfterAuth()
    } else {
      INTERMediatorLib.removeChildNodesAppendText(document.getElementById('_im_explain_2FA'), 2032)
    }
    INTERMediatorLog.flushMessage()
    await INTERMediatorOnPage.hideProgress(true)
  },

  /**
   * Click handler for the passkey button on login panel.
   * @param {Event} event The click event
   * @returns {Promise<void>}
   */
  passkeyButtonClick: async (event) => {
    await IMLibAuthenticationUI.passkeyAuthentication(null)
  },

  /**
   * Start passkey authentication (WebAuthn assertion) flow.
   * @param {?Function} [doAfterAuth=null] Callback invoked after successful authentication
   * @returns {Promise<void>}
   */
  passkeyAuthentication: async (doAfterAuth = null) => {
    await INTERMediator_DBAdapter.getChallengePasskeyCredential()
    if (IMLibAuthenticationUI.passkeyOption) {
      const obj = JSON.parse(IMLibAuthenticationUI.passkeyOption)
      obj.challenge = Uint8Array.fromBase64(obj.challenge, {alphabet: "base64url"});
      navigator.credentials.get({publicKey: obj})
        .then(async (info) => {
          await INTERMediator_DBAdapter.authPasskey(info)
          if (doAfterAuth) {
            doAfterAuth()
          } else {
            location.reload()
          }
        })
        .catch((err) => {
          window.alert(err)
        });
    }
  },

  /**
   * Start passkey registration (WebAuthn attestation) flow.
   * @returns {Promise<void>}
   */
  passkeyRegistration: async () => {
    await INTERMediator_DBAdapter.getChallengePasskeyRegistration()
    if (IMLibAuthenticationUI.passkeyOption) {
      const obj = JSON.parse(IMLibAuthenticationUI.passkeyOption)
      obj.challenge = Uint8Array.fromBase64(obj.challenge, {alphabet: "base64url"});
      obj.user.id = Uint8Array.fromBase64(obj.user.id, {alphabet: "base64url"})
      navigator.credentials.create({publicKey: obj})
        .then(async (info) => {
          await INTERMediator_DBAdapter.registerPasskey(info)
          location.reload()
        })
        .catch((err) => {
          window.alert(err)
        });
    }
  },

  /**
   * Unregister passkey for current user.
   * @returns {Promise<void>}
   */
  passkeyUnregistration: async () => {
    await INTERMediator_DBAdapter.unregisterPasskey()
    location.reload()
  }
}

// @@IM@@IgnoringRestOfFile
module.exports = IMLibAuthenticationUI
const INTERMediatorLib = require('../../src/js/INTER-Mediator-Lib')
const IMLibAuthentication = require('../../src/js/INTER-Mediator-Auth')
const INTERMediator_DBAdapter = require('../../src/js/Adapter_DBServer')
