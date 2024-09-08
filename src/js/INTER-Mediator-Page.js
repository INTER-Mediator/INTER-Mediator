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
 IMLibEventResponder, INTERMediatorLog, IMLib, JSEncrypt */
/* jshint -W083 */ // Function within a loop
/**
 * @fileoverview INTERMediatorOnPage class is defined here.
 */
/**
 *
 * Usually you don't have to instantiate this class with new operator.
 * @constructor
 */
let INTERMediatorOnPage = {
  authCountLimit: 4,
  _authCount: 0,
  authUserSalt: '',
  authUserHexSalt: '',
  authChallenge: '',
  requireAuthentication: false,
  authRequiredContext: null,
  authStoring: 'credential',
  authExpired: 3600,
  //publickey: null,
  httpuser: null,
  httppasswd: null,
  mediaToken: null,
  realm: '',
  succeedCredential: false,
  dbCache: {},
  isEmailAsUsername: false,
  passwordPolicy: null,
  creditIncluding: null,
  masterScrollPosition: null, // @Private
  nonSupportMessageId: 'nonsupportmessage',
  isFinishToConstruct: false,
  isAutoConstruct: true,
  loginPanelHTML: null,
  isShowChangePassword: true,
  isSetDefaultStyle: false,
  authPanelTitle: null,
  authPanelTitle2FA: null,
  authPanelExp: null,
  authPanelExp2FA: null,
  isOAuthAvailable: false, // @Private
  oAuthClientID: null, // @Private
  oAuthClientSecret: null, // @Private
  oAuthBaseURL: null, // @Private
  oAuthRedirect: null, // @Private
  oAuthScope: null, // @Private
  additionalExpandingEnclosureFinish: {},
  additionalExpandingRecordFinish: {},
  getEditorPath: null,
  getEntryPath: null,
  getDataSources: null,
  getOptionsAliases: null,
  getOptionsTransaction: null,
  dbClassName: null,
  defaultKeyName: null, // @Private
  browserCompatibility: null,
  clientNotificationIdentifier: null, // @Private
  metadata: null,
  appLocale: null,
  appCurrency: null,
  isShowProgress: true,
  notShowHeaderFooterOnNoResult: false,
  newRecordId: null,
  syncBeforeUpdate: null,
  syncAfterUpdate: null,
  syncBeforeCreate: null,
  syncAfterCreate: null,
  syncBeforeDelete: null,
  syncAfterDelete: null,
  logoutURL: null,
  loginURL: null,
  doAfterLoginPanel: null,
  buttonClassCopy: null,
  buttonClassDelete: null,
  buttonClassInsert: null,
  buttonClassMaster: null,
  buttonClassBackNavi: null,
  useServiceServer: false,
  activateClientService: false,
  credentialCookieDomain: null,
  updateProcessedNode: false,
  updatingWithSynchronize: 0,
  passwordHash: null,
  alwaysGenSHA2: false,
  isFollowingTimezone: false,
  isSAML: false,
  activateMaintenanceCall: false,
  extraButtons: null,
  digitsOf2FACode: null,
  isRequired2FA: false,
  authedUser: null,
  userNameJustASCII: true,

  get authCount() {
    this._authCount = IMLibLocalContext.getValue('_im_authcount')
    return this._authCount
  },
  set authCount(v) {
    this._authCount = v
    IMLibLocalContext.setValue('_im_authcount', v)
  },

  /*
  This method 'getMessages' is going to be replaced valid one with the browser's language.
  Here is defined to prevent the warning of static check.
  */
  getMessages: function () {
    'use strict'
    return null
  },

  getURLParametersAsArray: function () {
    'use strict'
    const result = {}
    const params = location.search.substring(1).split('&')
    for (let i = 0; i < params.length; i++) {
      const eqPos = params[i].indexOf('=')
      if (eqPos > 0) {
        const key = params[i].substring(0, eqPos)
        const value = params[i].substring(eqPos + 1)
        result[key] = decodeURIComponent(value)
      }
    }
    return result
  },

  getContextInfo: function (contextName) {
    'use strict'
    const dataSources = INTERMediatorOnPage.getDataSources()
    for (const index in dataSources) {
      if (dataSources.hasOwnProperty(index) && dataSources[index].name === contextName) {
        return dataSources[index]
      }
    }
    return null
  },

  authHashedPasswordWorker: function (key, value = false) {
    let returnVal = null
    if (INTERMediatorOnPage.requireAuthentication) {
      if (value === false) { // getter
        switch (INTERMediatorOnPage.authStoring) {
          case 'cookie':
          case 'cookie-domainwide':
          case 'credential':
            returnVal = INTERMediatorOnPage.getCookie(key)
            break
          case 'session-storage':
            returnVal = INTERMediatorOnPage.getSessionStorageWithFallDown(key)
            break
        }
      } else if (value === '') { // remover
        switch (INTERMediatorOnPage.authStoring) {
          case 'cookie':
          case 'cookie-domainwide':
          case 'credential':
            INTERMediatorOnPage.removeCookie(key)
            break
          case 'session-storage':
            INTERMediatorOnPage.removeFromSessionStorageWithFallDown(key)
            break
        }
      } else { // setter
        switch (INTERMediatorOnPage.authStoring) {
          case 'cookie':
            INTERMediatorOnPage.setCookie(key, value)
            break
          case 'cookie-domainwide':
            INTERMediatorOnPage.setCookieDomainWide(key, value)
            break
          case 'credential':
            INTERMediatorOnPage.setCookieDomainWide(key, value)
            break
          case 'session-storage':
            INTERMediatorOnPage.storeSessionStorageWithFallDown(key, value)
            break
        }
      }
    }
    return returnVal
  },

  clientId: function (value = false) {
    return INTERMediatorOnPage.authHashedPasswordWorker('_im_clientid', value)
  },

  authUser: function (value = false) {
    return INTERMediatorOnPage.authHashedPasswordWorker('_im_username', value)
  },

  authHashedPassword: function (value = false) {
    return INTERMediatorOnPage.authHashedPasswordWorker('_im_credential', value)
  },

  authHashedPassword2m: function (value = false) {
    return INTERMediatorOnPage.authHashedPasswordWorker('_im_credential2m', value)
  },

  authHashedPassword2: function (value = false) {
    return INTERMediatorOnPage.authHashedPasswordWorker('_im_credential2', value)
  },

  clearCredentials: function () {
    'use strict'
    INTERMediatorOnPage.authChallenge = ''
    INTERMediatorOnPage.authHashedPassword('')
    INTERMediatorOnPage.authHashedPassword2m('')
    INTERMediatorOnPage.authHashedPassword2('')
  },

  isComplementAuthData: function () {
    'use strict'
    return INTERMediatorOnPage.authUser() !== null && INTERMediatorOnPage.authUser().length > 0 && ((INTERMediatorOnPage.authHashedPassword() !== null && INTERMediatorOnPage.authHashedPassword().length > 0) || (INTERMediatorOnPage.authHashedPassword2m() !== null && INTERMediatorOnPage.authHashedPassword2m().length > 0) || (INTERMediatorOnPage.authHashedPassword2() !== null && INTERMediatorOnPage.authHashedPassword2().length > 0)) && INTERMediatorOnPage.authUserSalt !== null && INTERMediatorOnPage.authUserSalt.length > 0 && INTERMediatorOnPage.authChallenge !== null && INTERMediatorOnPage.authChallenge.length > 0
  },

  retrieveAuthInfo: async function () {
    'use strict'
    if (INTERMediatorOnPage.authUser() && INTERMediatorOnPage.authUser().length > 0) {
      if (INTERMediatorOnPage.authStoring !== 'credential') {
        await INTERMediator_DBAdapter.getChallenge()
        INTERMediatorLog.flushMessage()
      }
    }
  },

  logout: function (move = false, dontMove = false) {
    const logoutURL = INTERMediatorOnPage.logoutURL
    INTERMediatorOnPage.authUserSalt = ''
    INTERMediatorOnPage.authChallenge = ''
    INTERMediatorOnPage.loginURL = null
    INTERMediatorOnPage.logoutURL = null
    INTERMediatorOnPage.removeFromSessionStorageWithFallDown('_im_localcontext')
    switch (INTERMediatorOnPage.authStoring) {
      case 'cookie':
      case 'cookie-domainwide':
      case 'credential':
        INTERMediatorOnPage.removeCookie('_im_clientid')
        INTERMediatorOnPage.removeCookie('_im_session_exp')
        INTERMediatorOnPage.removeCookie('_im_username')
        INTERMediatorOnPage.removeCookie('_im_credential')
        INTERMediatorOnPage.removeCookie('_im_credential2m')
        INTERMediatorOnPage.removeCookie('_im_credential2')
        break
      case 'session-storage':
        INTERMediatorOnPage.removeFromSessionStorageWithFallDown('_im_clientid')
        INTERMediatorOnPage.removeFromSessionStorageWithFallDown('_im_session_exp')
        INTERMediatorOnPage.removeFromSessionStorageWithFallDown('_im_username')
        INTERMediatorOnPage.removeFromSessionStorageWithFallDown('_im_credential')
        INTERMediatorOnPage.removeFromSessionStorageWithFallDown('_im_credential2m')
        INTERMediatorOnPage.removeFromSessionStorageWithFallDown('_im_credential2')
        INTERMediatorOnPage.removeFromSessionStorageWithFallDown('_im_mediatoken')
        break
    }
    if (!dontMove) {
      if (logoutURL) { // For SAML auth.
        location.href = logoutURL
      } else if (move) { // built-in auth
        location.href = move
      } else {
        location.reload()
      }
    }
  },

  storedHashedPasswordAllClear: (value) => {
    INTERMediatorOnPage.authHashedPassword(value)
    INTERMediatorOnPage.authHashedPassword2m(value)
    INTERMediatorOnPage.authHashedPassword2(value)
  },

  storeSessionStorageWithFallDown: function (key, value) {
    'use strict'
    if (INTERMediator.useSessionStorage === true && typeof sessionStorage !== 'undefined' && sessionStorage !== null) {
      try {
        const expired = sessionStorage.getItem(INTERMediatorOnPage.getKeyWithRealm('_im_session_exp'))
        if (!expired || ((new Date()).toUTCString() >= (new Date(expired)).toUTCString())) {
          const d = new Date()
          d.setTime(d.getTime() + INTERMediatorOnPage.authExpired * 1000)
          sessionStorage.setItem(INTERMediatorOnPage.getKeyWithRealm('_im_session_exp'), d.toUTCString())
        }
        sessionStorage.setItem(INTERMediatorOnPage.getKeyWithRealm(key), value)
      } catch (ex) {
        INTERMediatorOnPage.setCookie(key, value)
      }
    } else {
      INTERMediatorOnPage.setCookie(key, value)
    }
  },

  getSessionStorageWithFallDown: function (key) {
    'use strict'
    let value = ''
    if (INTERMediator.useSessionStorage === true && typeof sessionStorage !== 'undefined' && sessionStorage !== null) {
      try {
        const expired = sessionStorage.getItem(INTERMediatorOnPage.getKeyWithRealm('_im_session_exp'))
        if ((new Date()).toUTCString() < (new Date(expired)).toUTCString()) {
          value = sessionStorage.getItem(INTERMediatorOnPage.getKeyWithRealm(key))
        } else {
          sessionStorage.removeItem(INTERMediatorOnPage.getKeyWithRealm('_im_session_exp'))
          sessionStorage.removeItem(INTERMediatorOnPage.getKeyWithRealm(key))
        }
        value = value ? value : ''
      } catch (ex) {
        value = INTERMediatorOnPage.getCookie(key)
      }
    } else {
      value = INTERMediatorOnPage.getCookie(key)
    }
    return value
  },

  removeFromSessionStorageWithFallDown: function (key) {
    'use strict'
    if (INTERMediator.useSessionStorage === true && typeof sessionStorage !== 'undefined' && sessionStorage !== null) {
      try {
        sessionStorage.removeItem(INTERMediatorOnPage.getKeyWithRealm(key))
      } catch (ex) {
        INTERMediatorOnPage.removeCookie(key)
      }
    } else {
      INTERMediatorOnPage.removeCookie(key)
    }
  },

  /* Cookies support */
  getKeyWithRealm: function (str) {
    'use strict'
    if (INTERMediatorOnPage.realm.length > 0) {
      return str + '_' + INTERMediatorOnPage.realm
    }
    return str
  },

  getCookie: function (key) {
    'use strict'
    let s = ''
    try {
      s = document.cookie.split('; ')
    } catch (e) {
    }
    const targetKey = this.getKeyWithRealm(key)
    for (let i = 0; i < s.length; i++) {
      if (s[i].indexOf(targetKey + '=') === 0) {
        return decodeURIComponent(s[i].substring(s[i].indexOf('=') + 1))
      }
    }
    return ''
  },

  removeCookie: function (key) {
    'use strict'
    if (document && document.cookie) {
      document.cookie = this.getKeyWithRealm(key) + '=; path=/; max-age=0; expires=Thu, 1-Jan-1900 00:00:00 GMT;'
      document.cookie = this.getKeyWithRealm(key) + '=; max-age=0;  expires=Thu, 1-Jan-1900 00:00:00 GMT;'
    }
  },

  setCookie: function (key, val) {
    'use strict'
    this.setCookieWorker(this.getKeyWithRealm(key), val, false, INTERMediatorOnPage.authExpired)
  },

  setCookieDomainWide: function (key, val, noRealm) {
    'use strict'
    const realKey = (noRealm === true) ? key : this.getKeyWithRealm(key)
    this.setCookieWorker(realKey, val, true, INTERMediatorOnPage.authExpired)
  },

  setCookieWorker: function (key, val, isDomain, expired) {
    'use strict'
    const d = new Date()
    d.setTime(d.getTime() + expired * 1000)
    let cookieString = key + '=' + encodeURIComponent(val)
    if (INTERMediatorOnPage.credentialCookieDomain) {
      cookieString += `;domain=.${INTERMediatorOnPage.credentialCookieDomain}`
      // The dot before domain name is for matching the PHP's setcookie function's behavior.
    }
    if (isDomain) {
      cookieString += `;path=/`
    }
    if (expired > 0) {
      cookieString += ';max-age=' + expired + ';expires=' + d.toUTCString()
    }
    if (document.URL.substring(0, 8) === 'https://') {
      cookieString += ';secure;'
    }
    document.cookie = cookieString
  },

  authenticating: function (doAfterAuth, doTest) {
    'use strict'
    if (doTest) {
      return
    }
    this.checkPasswordPolicy = function (newPassword, userName, policyString) {
      let message = []
      if (!policyString) {
        return message
      }
      const terms = policyString.split(/[\s,]/)
      for (let i = 0; i < terms.length; i++) {
        switch (terms[i].toUpperCase()) {
          case 'USEALPHABET':
            if (!newPassword.match(/[A-Za-z]/)) {
              message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2015))
            }
            break
          case 'USENUMBER':
            if (!newPassword.match(/[0-9]/)) {
              message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2016))
            }
            break
          case 'USEUPPER':
            if (!newPassword.match(/[A-Z]/)) {
              message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2017))
            }
            break
          case 'USELOWER':
            if (!newPassword.match(/[a-z]/)) {
              message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2018))
            }
            break
          case 'USEPUNCTUATION':
            if (!newPassword.match(/[^A-Za-z0-9]/)) {
              message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2019))
            }
            break
          case 'NOTUSERNAME':
            if (newPassword === userName) {
              message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2020))
            }
            break
          default:
            if (terms[i].toUpperCase().indexOf('LENGTH') === 0) {
              const minLen = terms[i].match(/[0-9]+/)[0]
              if (newPassword.length < minLen) {
                message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2021, [minLen]))
              }
            }
        }
      }
      return message
    }
    if (!INTERMediatorOnPage.authedUser) {
      INTERMediatorOnPage.authCount = 0
    }
    if (INTERMediatorOnPage.authCount > INTERMediatorOnPage.authCountLimit) {
      INTERMediatorOnPage.authenticationError()
      INTERMediatorOnPage.logout(false, true)
      INTERMediatorLog.flushMessage()
      return
    }

    let userBox, passwordBox, authButton, oAuthButton, chgpwButton, breakLine, samlButton, extButtons = {}
    const bodyNode = document.getElementsByTagName('BODY')[0]
    const backBox = document.createElement('div')
    backBox.id = '_im_authpback'
    bodyNode.insertBefore(backBox, bodyNode.childNodes[0])
    if (INTERMediatorOnPage.isSetDefaultStyle) {
      backBox.style.height = '100%'
      backBox.style.width = '100%'
      let url = INTERMediatorOnPage.getEntryPath()
      url = INTERMediatorLib.mergeURLParameter(url, 'theme', INTERMediatorOnPage.getTheme())
      url = INTERMediatorLib.mergeURLParameter(url, 'type', 'images')
      url = INTERMediatorLib.mergeURLParameter(url, 'name', 'background.gif')
      backBox.style.backgroundImage = `url(${url})`
      backBox.style.position = 'absolute'
      backBox.style.padding = ' 50px 0 0 0'
      backBox.style.top = '0'
      backBox.style.left = '0'
      backBox.style.zIndex = '777777'
    }

    if (INTERMediatorOnPage.loginPanelHTML) {
      backBox.innerHTML = INTERMediatorOnPage.loginPanelHTML
      passwordBox = document.getElementById('_im_password')
      userBox = document.getElementById('_im_username')
      authButton = document.getElementById('_im_authbutton')
      chgpwButton = document.getElementById('_im_changebutton')
      oAuthButton = document.getElementById('_im_oauthbutton')
      samlButton = document.getElementById('_im_samlbutton')
    } else {
      const frontPanel = document.createElement('div')
      if (INTERMediatorOnPage.isSetDefaultStyle) {
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

      const panelTitle = INTERMediatorOnPage.authPanelTitle ? INTERMediatorOnPage.authPanelTitle
        : (INTERMediatorOnPage.realm ? INTERMediatorOnPage.realm : '')
      if (panelTitle && panelTitle.length > 0) {
        const realmBox = document.createElement('DIV')
        realmBox.appendChild(document.createTextNode(panelTitle))
        // realmBox.style.textAlign = 'left'
        realmBox.id = '_im_authrealm'
        frontPanel.appendChild(realmBox)
        breakLine = document.createElement('HR')
        frontPanel.appendChild(breakLine)
      }

      const labelWidth = '100px'
      const userLabel = document.createElement('LABEL')
      frontPanel.appendChild(userLabel)
      const userSpan = document.createElement('span')
      if (INTERMediatorOnPage.isSetDefaultStyle) {
        userSpan.style.minWidth = labelWidth
        userSpan.style.textAlign = 'right'
        userSpan.style.cssFloat = 'left'
      }
      userSpan.setAttribute('class', '_im_authlabel')
      userLabel.appendChild(userSpan)
      const msgNumber = INTERMediatorOnPage.isEmailAsUsername ? 2011 : 2002
      userSpan.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(msgNumber)))
      userBox = document.createElement('INPUT')
      userBox.type = 'text'
      userBox.id = '_im_username'
      userBox.size = '20'
      userBox.setAttribute('autocapitalize', 'off')
      userLabel.appendChild(userBox)

      breakLine = document.createElement('BR')
      breakLine.clear = 'all'
      frontPanel.appendChild(breakLine)

      const passwordLabel = document.createElement('LABEL')
      frontPanel.appendChild(passwordLabel)
      const passwordSpan = document.createElement('SPAN')
      if (INTERMediatorOnPage.isSetDefaultStyle) {
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

      breakLine = document.createElement('BR')
      breakLine.clear = 'all'
      frontPanel.appendChild(breakLine)

      let newPasswordMessage = document.createElement('DIV')
      if (INTERMediatorOnPage.isSetDefaultStyle) {
        newPasswordMessage.style.textAlign = 'center'
        newPasswordMessage.style.textSize = '10pt'
        newPasswordMessage.style.color = '#994433'
      }
      newPasswordMessage.id = '_im_login_message'
      frontPanel.appendChild(newPasswordMessage)

      if (this.isShowChangePassword) {
        breakLine = document.createElement('HR')
        frontPanel.appendChild(breakLine)

        const newPasswordLabel = document.createElement('LABEL')
        frontPanel.appendChild(newPasswordLabel)
        const newPasswordSpan = document.createElement('SPAN')
        if (INTERMediatorOnPage.isSetDefaultStyle) {
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
        if (INTERMediatorOnPage.isSetDefaultStyle) {
          newPasswordMessage.style.textAlign = 'center'
          newPasswordMessage.style.textSize = '10pt'
          newPasswordMessage.style.color = '#994433'
        }
        newPasswordMessage.id = '_im_newpass_message'
        frontPanel.appendChild(newPasswordMessage)
      }
      if ((INTERMediatorOnPage.extraButtons && Object.keys(INTERMediatorOnPage.extraButtons).length > 0) || this.isOAuthAvailable || (INTERMediatorOnPage.isSAML && INTERMediatorOnPage.samlWithBuiltInAuth)) {
        breakLine = document.createElement('HR')
        frontPanel.appendChild(breakLine)
      }
      if (this.isOAuthAvailable) {
        oAuthButton = document.createElement('BUTTON')
        oAuthButton.id = '_im_oauthbutton'
        oAuthButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2014)))
        frontPanel.appendChild(oAuthButton)
      }
      if (INTERMediatorOnPage.isSAML && INTERMediatorOnPage.samlWithBuiltInAuth) {
        samlButton = document.createElement('BUTTON')
        samlButton.id = '_im_samlbutton'
        samlButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2026)))
        frontPanel.appendChild(samlButton)
      }
      for (const key in INTERMediatorOnPage.extraButtons) {
        extButtons[key] = document.createElement('BUTTON')
        const key4id = [...key.matchAll(/(\w)/g)].reduce((acc, cur) => {
          return acc + cur[0]
        }, "")
        extButtons[key].id = `_im_extbutton_${key4id}`
        extButtons[key].appendChild(document.createTextNode(key))
        const moveURL = INTERMediatorOnPage.extraButtons[key]
        extButtons[key].onclick = function () {
          location.href = moveURL
        }
        frontPanel.appendChild(extButtons[key])
      }
      if (INTERMediatorOnPage.enrollPageURL) {
        breakLine = document.createElement('HR')
        frontPanel.appendChild(breakLine)
        const addingButton = document.createElement('BUTTON')
        addingButton.id = '_im_enrollbutton'
        addingButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2022)))
        addingButton.onclick = function () {
          location.href = INTERMediatorOnPage.enrollPageURL
        }
        frontPanel.appendChild(addingButton)
        if (INTERMediatorOnPage.authedUser && INTERMediatorOnPage.authStoring === 'session-storage') {
          const messageNode = document.getElementById('_im_login_message')
          INTERMediatorLib.removeChildNodesAppendText(messageNode, 2012)
        }
      }
      if (INTERMediatorOnPage.resetPageURL) {
        breakLine = document.createElement('HR')
        frontPanel.appendChild(breakLine)
        const addingButton = document.createElement('BUTTON')
        addingButton.id = '_im_resetbutton'
        addingButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2023)))
        addingButton.onclick = function () {
          location.href = INTERMediatorOnPage.resetPageURL
        }
        frontPanel.appendChild(addingButton)
        const resetMessage = document.createElement('div')
        resetMessage.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2024)))
        frontPanel.appendChild(resetMessage)
      }
      if (INTERMediatorOnPage.authPanelExp) {
        breakLine = document.createElement('HR')
        frontPanel.appendChild(breakLine)
        const addingNode = document.createElement('DIV')
        addingNode.className = '_im_auth_exp'
        addingNode.innerHTML = INTERMediatorOnPage.authPanelExp
        frontPanel.appendChild(addingNode)
      }
    }
    passwordBox.onkeydown = function (event) {
      if (event.code === 'Enter') {
        authButton.onclick()
      }
    }
    userBox.value = INTERMediatorOnPage.authUser()
    userBox.onkeydown = function (event) {
      if ((event.code === 'Enter' || event.code === 'NumpadEnter') && !event.isComposing) {
        passwordBox.focus()
      }
    }
    authButton.onclick = async function () {
      await INTERMediatorOnPage.hideProgress()
      let messageNode = document.getElementById('_im_newpass_message')
      if (messageNode) {
        INTERMediatorLib.removeChildNodes(messageNode)
      }

      let inputUsername = document.getElementById('_im_username').value
      const inputPassword = document.getElementById('_im_password').value
      if (INTERMediatorOnPage.userNameJustASCII) {
        inputUsername = INTERMediatorLib.justfyUsername(inputUsername)
      }

      if (inputUsername === '' || inputPassword === '') {
        messageNode = document.getElementById('_im_login_message')
        INTERMediatorLib.removeChildNodes(messageNode)
        messageNode.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2013)))
        return
      }
      INTERMediatorOnPage.authUser(inputUsername)
      bodyNode.removeChild(backBox)
      if (inputUsername !== '' && // No usename and no challenge, get a challenge.
        (INTERMediatorOnPage.authChallenge === null || INTERMediatorOnPage.authChallenge.length < 48)) {
        INTERMediatorOnPage.storedHashedPasswordAllClear('need-hash-pls')
        await INTERMediator_DBAdapter.getChallenge()
      }
      if (INTERMediatorOnPage.passwordHash < 1.1) {
        INTERMediatorOnPage.authHashedPassword(INTERMediatorLib.generatePasswrdHashV1(inputPassword, INTERMediatorOnPage.authUserSalt))
      }
      if (INTERMediatorOnPage.passwordHash < 1.6) {
        INTERMediatorOnPage.authHashedPassword2m(INTERMediatorLib.generatePasswrdHashV2m(inputPassword, INTERMediatorOnPage.authUserSalt))
      }
      if (INTERMediatorOnPage.passwordHash < 2.1) {
        INTERMediatorOnPage.authHashedPassword2(INTERMediatorLib.generatePasswrdHashV2(inputPassword, INTERMediatorOnPage.authUserSalt))
      }
      INTERMediatorOnPage.succeedCredential = false
      if (INTERMediatorOnPage.authStoring === 'credential') {
        await INTERMediator_DBAdapter.getCredential()
        if (INTERMediatorOnPage.succeedCredential) {
          if (INTERMediatorOnPage.isRequired2FA) {
            INTERMediatorOnPage.show2FAPanel(doAfterAuth)
          } else {
            doAfterAuth() // Retry.
          }
        }
      } else if (INTERMediatorOnPage.authStoring === 'session-storage') {
        doAfterAuth() // Retry.
      }
      INTERMediatorLog.flushMessage()
      INTERMediatorOnPage.hideProgress(true)
    }
    if (chgpwButton) {
      const checkPolicyMethod = this.checkPasswordPolicy
      chgpwButton.onclick = function () {
        let messageNode = document.getElementById('_im_login_message')
        INTERMediatorLib.removeChildNodes(messageNode)
        messageNode = document.getElementById('_im_newpass_message')
        INTERMediatorLib.removeChildNodes(messageNode)

        const inputUsername = document.getElementById('_im_username').value
        const inputPassword = document.getElementById('_im_password').value
        const inputNewPassword = document.getElementById('_im_newpassword').value
        if (inputUsername === '' || inputPassword === '' || inputNewPassword === '') {
          messageNode = document.getElementById('_im_newpass_message')
          INTERMediatorLib.removeChildNodesAppendText(messageNode2007)
          return
        }

        const message = checkPolicyMethod(inputNewPassword, inputUsername, INTERMediatorOnPage.passwordPolicy)
        if (message.length > 0) { // Policy violated.
          messageNode.appendChild(document.createTextNode(message.join(', ')))
          return
        }

        INTERMediator_DBAdapter.changePassword(inputUsername, inputPassword, inputNewPassword, () => {
          messageNode.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2009)))
          INTERMediatorLog.flushMessage()
        }, () => {
          messageNode.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2010)))
          INTERMediatorLog.flushMessage()
        })
      }
    }
    if (this.isOAuthAvailable && oAuthButton) {
      oAuthButton.onclick = function () {
        INTERMediatorOnPage.setCookieDomainWide('_im_oauth_backurl', location.href, true)
        INTERMediatorOnPage.setCookieDomainWide('_im_oauth_realm', INTERMediatorOnPage.realm, true)
        INTERMediatorOnPage.setCookieDomainWide('_im_oauth_expired', INTERMediatorOnPage.authExpired, true)
        INTERMediatorOnPage.setCookieDomainWide('_im_oauth_storing', INTERMediatorOnPage.authStoring, true)
        location.href = INTERMediatorOnPage.oAuthBaseURL + '?scope=' + encodeURIComponent(INTERMediatorOnPage.oAuthScope) + '&redirect_uri=' + encodeURIComponent(INTERMediatorOnPage.oAuthRedirect) + '&response_type=code' + '&client_id=' + encodeURIComponent(INTERMediatorOnPage.oAuthClientID)
      }
    }
    if (INTERMediatorOnPage.isSAML && samlButton) {
      samlButton.onclick = function () {
        location.href = INTERMediatorOnPage.loginURL
      }
    }

    if (INTERMediatorOnPage.authCount > 0) {
      const messageNode = document.getElementById('_im_login_message')
      INTERMediatorLib.removeChildNodesAppendText(messageNode, 2012)
    }

    window.scrollTo(0, 0)
    userBox.focus()
    INTERMediatorOnPage.authCount++
    if (INTERMediatorOnPage.doAfterLoginPanel) {
      INTERMediatorOnPage.doAfterLoginPanel(false)
    }
  },

  authenticationError: function () {
    'use strict'
    INTERMediatorOnPage.hideProgress()
    const bodyNode = document.getElementsByTagName('BODY')[0]
    const backBox = document.createElement('div')
    backBox.id = '_im_autherrorback'
    bodyNode.insertBefore(backBox, bodyNode.childNodes[0])
    if (INTERMediatorOnPage.isSetDefaultStyle) {
      backBox.style.height = '100%'
      backBox.style.width = '100%'
      // backBox.style.backgroundColor = '#BBBBBB'
      if (INTERMediatorOnPage.isSetDefaultStyle) {
        let url = INTERMediatorOnPage.getEntryPath()
        url = INTERMediatorLib.mergeURLParameter(url, 'theme', INTERMediatorOnPage.getTheme())
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
    if (INTERMediatorOnPage.isSetDefaultStyle) {
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

  show2FAPanel: (doAfterAuth) => {
    let authButton, breakLine
    const bodyNode = document.getElementsByTagName('BODY')[0]
    const backBox = document.createElement('div')
    backBox.id = '_im_authpback_2FA'
    bodyNode.insertBefore(backBox, bodyNode.childNodes[0])
    const frontPanel = document.createElement('div')
    frontPanel.id = '_im_authpanel_2FA'
    backBox.appendChild(frontPanel)

    if (INTERMediatorOnPage.authPanelTitle2FA || INTERMediatorOnPage.realm) {
      const realmBox = document.createElement('DIV')
      realmBox.appendChild(document.createTextNode(
        INTERMediatorOnPage.authPanelTitle ? INTERMediatorOnPage.authPanelTitle
          : (INTERMediatorOnPage.realm ? INTERMediatorOnPage.realm : '')))
      realmBox.id = '_im_authrealm_2FA'
      frontPanel.appendChild(realmBox)
      breakLine = document.createElement('HR')
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

    if (INTERMediatorOnPage.authPanelExp2FA) {
      breakLine = document.createElement('HR')
      frontPanel.appendChild(breakLine)
      const addingNode = document.createElement('DIV')
      addingNode.className = '_im_auth_exp_2fa'
      addingNode.innerHTML = INTERMediatorOnPage.authPanelExp2FA
      frontPanel.appendChild(addingNode)
    }

    window.scrollTo(0, 0)
    codeBox.focus()
    INTERMediatorOnPage.authCount++
    if (INTERMediatorOnPage.doAfterLoginPanel) {
      INTERMediatorOnPage.doAfterLoginPanel(true)
    }

    codeBox.onkeydown = function (event) {
      if (event.code === 'Enter') {
        authButton.onclick()
      }
    }
    authButton.onclick = async function () {
      await INTERMediatorOnPage.hideProgress()
      const inputCode = document.getElementById('_im_code_2FA').value
      if (inputCode === '' || inputCode.length !== INTERMediatorOnPage.digitsOf2FACode) {
        messageNode = document.getElementById('_im_explain_2FA')
        INTERMediatorLib.removeChildNodesAppendText(messageNode, 2031)
        return
      }
      INTERMediatorOnPage.authHashedPassword2(inputCode)
      await INTERMediator_DBAdapter.getCredential2FA()
      if (INTERMediatorOnPage.succeedCredential) {
        bodyNode.removeChild(backBox)
        doAfterAuth()
      } else {
        messageNode = document.getElementById('_im_explain_2FA')
        INTERMediatorLib.removeChildNodesAppendText(messageNode, 2032)
      }
      INTERMediatorLog.flushMessage()
      INTERMediatorOnPage.hideProgress(true)
    }
  },
  /**
   *
   * @param deleteNode
   * @returns {boolean}
   */
  INTERMediatorCheckBrowser: function (deleteNode) {
    'use strict'
    let judge = false
    let positiveList = INTERMediatorOnPage.browserCompatibility()

    if (positiveList.edge && navigator.userAgent.indexOf('Edge/') > -1) {
      positiveList = {'edge': positiveList.edge}
    } else if (positiveList.trident && navigator.userAgent.indexOf('Trident/') > -1) {
      positiveList = {'trident': positiveList.trident}
    } else if (positiveList.msie && navigator.userAgent.indexOf('MSIE ') > -1) {
      positiveList = {'msie': positiveList.msie}
    } else if (positiveList.opera && (navigator.userAgent.indexOf('Opera/') > -1 || navigator.userAgent.indexOf('OPR/') > -1)) {
      positiveList = {'opera': positiveList.opera, 'opr': positiveList.opera}
    }

    let versionStr
    let matchAgent = false
    let matchOS = false
    for (const agent in positiveList) {
      if (positiveList.hasOwnProperty(agent)) {
        if (navigator.userAgent.toUpperCase().indexOf(agent.toUpperCase()) > -1) {
          matchAgent = true
          if (positiveList[agent] instanceof Object) {
            for (const os in positiveList[agent]) {
              if (positiveList[agent].hasOwnProperty(os) && navigator.platform.toUpperCase().indexOf(os.toUpperCase()) > -1) {
                matchOS = true
                versionStr = positiveList[agent][os]
                break
              }
            }
          } else {
            matchOS = true
            versionStr = positiveList[agent]
            break
          }
        }
      }
    }

    if (matchAgent && matchOS) {
      let specifiedVersion = parseInt(versionStr, 10)
      let agentPos = -1
      if (navigator.appVersion.indexOf('Edge/') > -1) {
        agentPos = navigator.appVersion.indexOf('Edge/') + 5
      } else if (navigator.appVersion.indexOf('Trident/') > -1) {
        agentPos = navigator.appVersion.indexOf('Trident/') + 8
      } else if (navigator.appVersion.indexOf('MSIE ') > -1) {
        agentPos = navigator.appVersion.indexOf('MSIE ') + 5
      } else if (navigator.appVersion.indexOf('OPR/') > -1) {
        agentPos = navigator.appVersion.indexOf('OPR/') + 4
      } else if (navigator.appVersion.indexOf('Opera/') > -1) {
        agentPos = navigator.appVersion.indexOf('Opera/') + 6
      } else if (navigator.appVersion.indexOf('Chrome/') > -1) {
        agentPos = navigator.appVersion.indexOf('Chrome/') + 7
      } else if (navigator.appVersion.indexOf('Safari/') > -1 && navigator.appVersion.indexOf('Version/') > -1) {
        agentPos = navigator.appVersion.indexOf('Version/') + 8
      } else if (navigator.userAgent.indexOf('Firefox/') > -1) {
        agentPos = navigator.userAgent.indexOf('Firefox/') + 8
      } else if (navigator.appVersion.indexOf('WebKit/') > -1) {
        agentPos = navigator.appVersion.indexOf('WebKit/') + 7
      }

      let dotPos, versionNum
      if (agentPos > -1) {
        if (navigator.userAgent.indexOf('Firefox/') > -1) {
          dotPos = navigator.userAgent.indexOf('.', agentPos)
          versionNum = parseInt(navigator.userAgent.substring(agentPos, dotPos), 10)
        } else {
          dotPos = navigator.appVersion.indexOf('.', agentPos)
          versionNum = parseInt(navigator.appVersion.substring(agentPos, dotPos), 10)
        }
        /*
         As for the appVersion property of IE, refer http://msdn.microsoft.com/en-us/library/aa478988.aspx
         */
      } else {
        dotPos = navigator.appVersion.indexOf('.')
        versionNum = parseInt(navigator.appVersion.substring(0, dotPos), 10)
      }
      if (INTERMediator.isTrident) {
        specifiedVersion = specifiedVersion + 4
      }
      if (versionStr.indexOf('-') > -1) {
        judge = (specifiedVersion >= versionNum)
        if (document.documentMode) {
          judge = (specifiedVersion >= document.documentMode)
        }
      } else if (versionStr.indexOf('+') > -1) {
        judge = (specifiedVersion <= versionNum)
        if (document.documentMode) {
          judge = (specifiedVersion <= document.documentMode)
        }
      } else {
        judge = (specifiedVersion === versionNum)
        if (document.documentMode) {
          judge = (specifiedVersion === document.documentMode)
        }
      }
    }
    if (judge === true) {
      if (deleteNode) {
        deleteNode.parentNode.removeChild(deleteNode)
      }
    } else {
      const bodyNode = document.getElementsByTagName('BODY')[0]
      const elm = document.createElement('div')
      elm.setAttribute('align', 'center')
      const childElm = document.createElement('font')
      childElm.setAttribute('color', 'gray')
      const grandChildElm = document.createElement('font')
      grandChildElm.setAttribute('size', '+2')
      grandChildElm.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[1022]))
      childElm.appendChild(grandChildElm)
      childElm.appendChild(document.createElement('br'))
      childElm.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[1023]))
      childElm.appendChild(document.createElement('br'))
      childElm.appendChild(document.createTextNode(navigator.userAgent))
      elm.appendChild(childElm)
      while (bodyNode.firstChild) {
        bodyNode.removeChild(bodyNode.firstChild)
      }
      // for (let i = bodyNode.childNodes.length - 1; i >= 0; i--) {
      //   bodyNode.removeChild(bodyNode.childNodes[i])
      // }
      bodyNode.appendChild(elm)
    }
    return judge
  },

  /*
   Seek nodes from the repeater of 'fromNode' parameter.
   */
  getNodeIdFromIMDefinition: function (imDefinition, fromNode, justFromNode) {
    'use strict'
    console.error('INTERMediatorOnPage.getNodeIdFromIMDefinition method in INTER-Mediator-Page.js will be removed in Ver.6.0. ' + 'The alternative method is getNodeIdsHavingTargetFromNode or getNodeIdsHavingTargetFromRepeater.')
    let repeaterNode
    if (justFromNode) {
      repeaterNode = fromNode
    } else {
      repeaterNode = INTERMediatorLib.getParentRepeater(fromNode)
    }
    return seekNode(repeaterNode, imDefinition)

    function seekNode(node, imDefinition) {
      if (node.nodeType !== 1) {
        return null
      }
      const children = node.childNodes
      if (children) {
        for (let i = 0; i < children.length; i++) {
          if (children[i].nodeType === 1) {
            if (INTERMediatorLib.isLinkedElement(children[i])) {
              const nodeDefs = INTERMediatorLib.getLinkedElementInfo(children[i])
              if (nodeDefs.indexOf(imDefinition) > -1) {
                return children[i].getAttribute('id')
              }
            }
            const returnValue = seekNode(children[i], imDefinition)
            if (returnValue !== null) {
              return returnValue
            }
          }
        }
      }
      return null
    }
  },

  getNodeIdFromIMDefinitionOnEnclosure: function (imDefinition, fromNode) {
    'use strict'
    console.error('INTERMediatorOnPage.getNodeIdFromIMDefinitionOnEnclosure method in INTER-Mediator-Page.js will be removed in Ver.6.0. ' + 'The alternative method is getNodeIdsHavingTargetFromEnclosure.')
    const repeaterNode = INTERMediatorLib.getParentEnclosure(fromNode)
    return seekNode(repeaterNode, imDefinition)

    function seekNode(node, imDefinition) {
      if (node.nodeType !== 1) {
        return null
      }
      const children = node.childNodes
      if (children) {
        for (let i = 0; i < children.length; i++) {
          if (children[i].nodeType === 1) {
            if (INTERMediatorLib.isLinkedElement(children[i])) {
              const nodeDefs = INTERMediatorLib.getLinkedElementInfo(children[i])
              if (nodeDefs.indexOf(imDefinition) > -1 && children[i].getAttribute) {
                return children[i].getAttribute('id')
              }
            }
            const returnValue = seekNode(children[i], imDefinition)
            if (returnValue !== null) {
              return returnValue
            }
          }
        }
      }
      return null
    }
  },

  getNodeIdsFromIMDefinition: function (imDefinition, fromNode, justFromNode) {
    'use strict'
    let nodeIds = []
    let enclosureNode
    if (justFromNode === true) {
      enclosureNode = [fromNode]
    } else if (justFromNode === false) {
      enclosureNode = [INTERMediatorLib.getParentEnclosure(fromNode)]
    } else {
      enclosureNode = INTERMediatorLib.getParentRepeaters(fromNode)
    }
    if (!enclosureNode) {
      return []
    }
    for (let i = 0; i < enclosureNode.length; i += 1) {
      if (enclosureNode[i] !== null) {
        if (Array.isArray(enclosureNode[i])) {
          for (let j = 0; j < enclosureNode[i].length; j++) {
            seekNode(enclosureNode[i][j], imDefinition)
          }
        } else {
          seekNode(enclosureNode[i], imDefinition)
        }
      }
    }
    return nodeIds

    function seekNode(node, imDefinition) {
      if (node.nodeType !== 1) {
        return
      }
      const children = node.childNodes
      if (children) {
        for (let i = 0; i < children.length; i++) {
          if (children[i].nodeType === 1) {
            const nodeDefs = INTERMediatorLib.getLinkedElementInfo(children[i])
            if (nodeDefs && nodeDefs.indexOf(imDefinition) > -1) {
              if (children[i].getAttribute('id')) {
                nodeIds.push(children[i].getAttribute('id'))
              } else {
                nodeIds.push(children[i])
              }
            }
          }
          seekNode(children[i], imDefinition)
        }
      }
    }
  },

  getNodeIdsHavingTargetFromNode: function (fromNode, imDefinition) {
    'use strict'
    return INTERMediatorOnPage.getNodeIdsFromIMDefinition(imDefinition, fromNode, true)
  },

  getNodeIdsHavingTargetFromRepeater: function (fromNode, imDefinition) {
    'use strict'
    return INTERMediatorOnPage.getNodeIdsFromIMDefinition(imDefinition, fromNode, 'others')
  },

  getNodeIdsHavingTargetFromEnclosure: function (fromNode, imDefinition) {
    'use strict'
    return INTERMediatorOnPage.getNodeIdsFromIMDefinition(imDefinition, fromNode, false)
  },

  /*
   * The hiding process is realized by _im_progress's div elements, but it's quite sensitive.
   * I've tried to set the CSS animations, but it seems to be a reason to stay the progress panel.
   * So far I gave up to use CSS animations. I think it's matter of handling transitionend event.
   * Now this method is going to be called multiple times in case of edit text field.
   * But it doesn't work by excluding to call by flag variable. I don't know why.
   * 2017-05-04 Masayuki Nii
   */
  hideProgress: async function (force = false) {
    if (!INTERMediatorOnPage.isShowProgress) {
      return
    }

    INTERMediatorOnPage.progressCounter -= 1;
    if (INTERMediatorOnPage.progressCounter <= 0 || force) {
      // Waiting for debug
      // const wait = async (ms) => new Promise(resolve => setTimeout(resolve, ms));
      // await wait(1000)

      const frontPanel = document.getElementById('_im_progress')
      if (frontPanel) {
        const themeName = INTERMediatorOnPage.getTheme().toLowerCase()
        if (themeName === 'least' || themeName === 'thosedays') {
          frontPanel.style.display = 'none'
        } else {
          // frontPanel.style.display = 'none'
          frontPanel.style.transitionDuration = '0.3s'
          frontPanel.style.opacity = 0
          frontPanel.style.zIndex = -9999
        }
      }
      INTERMediatorOnPage.progressShowing = false
      INTERMediatorOnPage.progressCounter = 0
    }
  },

  // Gear SVG was generated on http://loading.io/.
  showProgress: function (isDelay = true) {
    if (!INTERMediatorOnPage.isShowProgress) {
      return
    }
    INTERMediatorOnPage.progressCounter += 1;
    if (isDelay) {
      setTimeout(INTERMediatorOnPage.showProgressImpl, INTERMediatorOnPage.progressStartDelay)
    } else {
      INTERMediatorOnPage.showProgressImpl()
    }
  },

  progressCounter: 0,
  progressShowing: false,
  progressStartDelay: 300,

  showProgressImpl: function () {
    'use strict'
    // if (!INTERMediatorOnPage.isShowProgress) {
    //   return
    // }
    if (INTERMediatorOnPage.progressShowing) {
      return
    }
    if (INTERMediatorOnPage.progressCounter === 0) {
      return
    }
    const themeName = INTERMediatorOnPage.getTheme().toLowerCase()
    let frontPanel = document.getElementById('_im_progress')
    if (!frontPanel) {
      frontPanel = document.createElement('div')

      frontPanel.setAttribute('id', '_im_progress')
      const bodyNode = document.getElementsByTagName('BODY')[0]
      if (bodyNode.firstChild) {
        bodyNode.insertBefore(frontPanel, bodyNode.firstChild)
      } else {
        bodyNode.appendChild(frontPanel)
      }
      if (themeName === 'least' || themeName === 'thosedays') {
        const imageIM = document.createElement('img')
        imageIM.setAttribute('id', '_im_logo')
        let url = INTERMediatorOnPage.getEntryPath()
        url = INTERMediatorLib.mergeURLParameter(url, 'theme', INTERMediatorOnPage.getTheme())
        url = INTERMediatorLib.mergeURLParameter(url, 'type', 'images')
        url = INTERMediatorLib.mergeURLParameter(url, 'name', 'logo.gif')
        imageIM.setAttribute('src', url)
        frontPanel.appendChild(imageIM)
        const imageProgress = document.createElement('img')
        imageProgress.setAttribute('id', '_im_animatedimage')
        imageProgress.setAttribute('src', INTERMediatorOnPage.getEntryPath() + '?theme=' + INTERMediatorOnPage.getTheme() + '&type=images&name=inprogress.gif')
        frontPanel.appendChild(imageProgress)
        const brNode = document.createElement('BR')
        brNode.setAttribute('clear', 'all')
        frontPanel.appendChild(brNode)
        frontPanel.appendChild(document.createTextNode('INTER-Mediator working'))
      } else {
        const imageIM = document.createElement('img')
        let url = INTERMediatorOnPage.getEntryPath()
        url = INTERMediatorLib.mergeURLParameter(url, 'theme', INTERMediatorOnPage.getTheme())
        url = INTERMediatorLib.mergeURLParameter(url, 'type', 'images')
        url = INTERMediatorLib.mergeURLParameter(url, 'name', 'gears.svg')
        imageIM.setAttribute('src', url)
        frontPanel.appendChild(imageIM)
      }
    }
    if (themeName !== 'least' && themeName !== 'thosedays') {
      frontPanel.style.transitionDuration = '0'
      frontPanel.style.opacity = 1.0
      frontPanel.style.zIndex = 555555
    }
    INTERMediatorOnPage.progressShowing = true;
  },

  setReferenceToTheme: function () {
    'use strict'
    const headNode = document.getElementsByTagName('HEAD')[0]
    const linkElement = document.createElement('link')
    let url = INTERMediatorOnPage.getEntryPath()
    url = INTERMediatorLib.mergeURLParameter(url, 'theme', INTERMediatorOnPage.getTheme())
    url = INTERMediatorLib.mergeURLParameter(url, 'type', 'css')
    linkElement.setAttribute('href', url)
    linkElement.setAttribute('rel', 'stylesheet')
    linkElement.setAttribute('type', 'text/css')
    let styleIndex = -1
    for (let i = 0; i < headNode.childNodes.length; i++) {
      if (headNode.childNodes[i] && headNode.childNodes[i].nodeType === 1 && headNode.childNodes[i].tagName === 'LINK' && headNode.childNodes[i].rel === 'stylesheet') {
        styleIndex = i
        break
      }
    }
    if (styleIndex > -1) {
      headNode.insertBefore(linkElement, headNode.childNodes[styleIndex])
    } else {
      headNode.appendChild(linkElement)
    }
  }
}

// @@IM@@IgnoringRestOfFile
module.exports = INTERMediatorOnPage
// const JSEncrypt = require('../../node_modules/jsencrypt/bin/jsencrypt.js')
const INTERMediatorLib = require('../../src/js/INTER-Mediator-Lib')
const IMLibLocalContext = require('../../src/js/INTER-Mediator-LocalContext')
