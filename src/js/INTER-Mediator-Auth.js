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
 * @fileoverview IMLibAuthentication class is defined here.
 */
let IMLibAuthentication = {
  /**
   * Class handling page-level functionality for INTER-Mediator
   * @type {Object}
   */
  /** @type {number} */
  _authCount: 0,
  /** @type {string} */
  authUserSalt: '',
  /** @type {string} */
  authUserHexSalt: '',
  /** @type {string} */
  authChallenge: '',
  /** @type {boolean} */
  requireAuthentication: false,
  /** @type {('credential'|'cookie'|'cookie-domainwide'|'session-storage'|string)} */
  authStoring: 'credential',
  /** @type {number} Seconds until auth session expiration */
  authExpired: 3600, //publickey: null,
  /** @type {string|null} */
  httpuser: null,
  /** @type {string|null} */
  httppasswd: null,
  /** @type {string} */
  realm: '',
  /** @type {boolean} */
  activateClientService: false,
  /** @type {string|null} Domain for credential cookies */
  credentialCookieDomain: null,
  /** @type {number|null} Password hash version negotiated with server */
  passwordHash: null,
  /** @type {boolean} Force SHA2 generation even if not required */
  alwaysGenSHA2: false,
  /** @type {boolean} */
  isSAML: false,
  /** @type {Object<string, any>|null} OAuth parameters from server */
  oAuthParams: null,

  /**
   * Current authentication attempt count.
   * @returns {number}
   */
  get authCount() {
    IMLibAuthentication._authCount = IMLibLocalContext.getValue('_im_authcount')
    return IMLibAuthentication._authCount
  },
  /**
   * Set the authentication attempt count.
   * @param {number} v
   */
  set authCount(v) {
    IMLibAuthentication._authCount = v
    IMLibLocalContext.setValue('_im_authcount', v)
  },

  /**
   * Accessor for hashed password. It works as a getter, a setter, or a remover.
   * If the value is false, it works as a getter. If the value is a string, it works as a setter.
   * If the value is an empty string, it works as a remover.
   * @param {string} key - The key for the hashed password
   * @param {string|false} [value=false] - The value for the hashed password
   * @returns {string|null} The value of the hashed password if it exists
   */
  authHashedPasswordWorker: function (key, value = false) {
    let returnVal = null
    if (IMLibAuthentication.requireAuthentication) {
      if (value === false) { // getter
        switch (IMLibAuthentication.authStoring) {
          case 'cookie':
          case 'cookie-domainwide':
          case 'credential':
            returnVal = IMLibAuthentication.getCookie(key)
            break
          case 'session-storage':
            returnVal = IMLibAuthentication.getSessionStorageWithFallDown(key)
            break
        }
      } else if (value === '') { // remover
        switch (IMLibAuthentication.authStoring) {
          case 'cookie':
          case 'cookie-domainwide':
          case 'credential':
            IMLibAuthentication.removeCookie(key)
            break
          case 'session-storage':
            IMLibAuthentication.removeFromSessionStorageWithFallDown(key)
            break
        }
      } else { // setter
        switch (IMLibAuthentication.authStoring) {
          case 'cookie':
            IMLibAuthentication.setCookie(key, value)
            break
          case 'cookie-domainwide':
            IMLibAuthentication.setCookieDomainWide(key, value)
            break
          case 'credential':
            IMLibAuthentication.setCookieDomainWide(key, value)
            break
          case 'session-storage':
            IMLibAuthentication.storeSessionStorageWithFallDown(key, value)
            break
        }
      }
    }
    return returnVal
  },

  /**
   * Accessor for the client ID. It works as a getter, a setter, or a remover.
   * If the value is false, it works as a getter. If the value is a string, it works as a setter.
   * If the value is an empty string, it works as a remover.
   * @param {string|false} [value=false] - The client ID
   * @returns {string|null} The client ID if it exists
   */
  clientId: function (value = false) {
    return IMLibAuthentication.authHashedPasswordWorker('_im_clientid', value)
  },

  /**
   * Accessor for the user ID. It works as a getter, a setter, or a remover.
   * If the value is false, it works as a getter. If the value is a string, it works as a setter.
   * If the value is an empty string, it works as a remover.
   * @param {string|false} [value=false] - The user ID
   * @returns {string|null} The user ID if it exists
   */
  authUser: function (value = false) {
    return IMLibAuthentication.authHashedPasswordWorker('_im_username', value)
  },

  /**
   * Accessor for the hashed password. It works as a getter, a setter, or a remover.
   * If the value is false, it works as a getter. If the value is a string, it works as a setter.
   * If the value is an empty string, it works as a remover.
   * @param {string|false} [value=false] - The hashed password
   * @returns {string|null} The hashed password if it exists
   */
  authHashedPassword: function (value = false) {
    return IMLibAuthentication.authHashedPasswordWorker('_im_credential', value)
  },

  /**
   * Accessor for the hashed password (ver 2m, middle step).
   * @param {string|false} [value=false]
   * @returns {string|null}
   */
  authHashedPassword2m: function (value = false) {
    return IMLibAuthentication.authHashedPasswordWorker('_im_credential2m', value)
  },

  /**
   * Accessor for the hashed password (ver 2, current).
   * @param {string|false} [value=false]
   * @returns {string|null}
   */
  authHashedPassword2: function (value = false) {
    return IMLibAuthentication.authHashedPasswordWorker('_im_credential2', value)
  },

  /**
   * Clear all stored authentication credentials
   */
  clearCredentials: function () {
    'use strict'
    IMLibAuthentication.authChallenge = ''
    // IMLibAuthentication.passkeyChallenge = ''
    IMLibAuthentication.authHashedPassword('')
    IMLibAuthentication.authHashedPassword2m('')
    IMLibAuthentication.authHashedPassword2('')
  },

  /**
   * Check if authentication-related data are fully prepared.
   * @returns {boolean}
   */
  isComplementAuthData: function () {
    'use strict'
    return IMLibAuthentication.authUser() !== null
      && IMLibAuthentication.authUser().length > 0
      && ((IMLibAuthentication.authHashedPassword() !== null
        && IMLibAuthentication.authHashedPassword().length > 0) || (IMLibAuthentication.authHashedPassword2m() !== null
        && IMLibAuthentication.authHashedPassword2m().length > 0) || (IMLibAuthentication.authHashedPassword2() !== null
        && IMLibAuthentication.authHashedPassword2().length > 0))
      && IMLibAuthentication.authUserSalt !== null
      && IMLibAuthentication.authUserSalt.length > 0
      && IMLibAuthentication.authChallenge !== null
      && IMLibAuthentication.authChallenge.length > 0
  },

  /**
   * Retrieve authentication challenge and related info from the server if needed.
   * @returns {Promise<void>}
   */
  retrieveAuthInfo: async function () {
    'use strict'
    if (IMLibAuthentication.authUser() && IMLibAuthentication.authUser().length > 0) {
      if (IMLibAuthentication.authStoring !== 'credential') {
        await INTERMediator_DBAdapter.getChallenge()
        INTERMediatorLog.flushMessage()
      }
    }
  },

  /**
   * Store a value in sessionStorage with realm suffix, falling back to cookie.
   * @param {string} key
   * @param {string} value
   */
  storeSessionStorageWithFallDown: function (key, value) {
    'use strict'
    if (INTERMediator.useSessionStorage === true && typeof sessionStorage !== 'undefined' && sessionStorage !== null) {
      try {
        const expired = sessionStorage.getItem(IMLibAuthentication.getKeyWithRealm('_im_session_exp'))
        if (!expired || ((new Date()).toUTCString() >= (new Date(expired)).toUTCString())) {
          const d = new Date()
          d.setTime(d.getTime() + IMLibAuthentication.authExpired * 1000)
          sessionStorage.setItem(IMLibAuthentication.getKeyWithRealm('_im_session_exp'), d.toUTCString())
        }
        sessionStorage.setItem(IMLibAuthentication.getKeyWithRealm(key), value)
      } catch (ex) {
        IMLibAuthentication.setCookie(key, value)
      }
    } else {
      IMLibAuthentication.setCookie(key, value)
    }
  },

  /**
   * Get a value from sessionStorage with realm suffix, falling back to cookie.
   * @param {string} key
   * @returns {string}
   */
  getSessionStorageWithFallDown: function (key) {
    'use strict'
    let value = ''
    if (INTERMediator.useSessionStorage === true && typeof sessionStorage !== 'undefined' && sessionStorage !== null) {
      try {
        const expired = sessionStorage.getItem(IMLibAuthentication.getKeyWithRealm('_im_session_exp'))
        if ((new Date()).toUTCString() < (new Date(expired)).toUTCString()) {
          value = sessionStorage.getItem(IMLibAuthentication.getKeyWithRealm(key))
        } else {
          sessionStorage.removeItem(IMLibAuthentication.getKeyWithRealm('_im_session_exp'))
          sessionStorage.removeItem(IMLibAuthentication.getKeyWithRealm(key))
        }
        value = value ? value : ''
      } catch (ex) {
        value = IMLibAuthentication.getCookie(key)
      }
    } else {
      value = IMLibAuthentication.getCookie(key)
    }
    return value
  },

  /**
   * Remove a value from sessionStorage with realm suffix, falling back to cookie removal.
   * @param {string} key
   */
  removeFromSessionStorageWithFallDown: function (key) {
    'use strict'
    if (INTERMediator.useSessionStorage === true && typeof sessionStorage !== 'undefined' && sessionStorage !== null) {
      try {
        sessionStorage.removeItem(IMLibAuthentication.getKeyWithRealm(key))
      } catch (ex) {
        IMLibAuthentication.removeCookie(key)
      }
    } else {
      IMLibAuthentication.removeCookie(key)
    }
  },

  /* Cookie support */
  /**
   * Append realm suffix to a key if realm is set.
   * @param {string} str
   * @returns {string}
   */
  getKeyWithRealm: function (str) {
    'use strict'
    if (IMLibAuthentication.realm.length > 0) {
      return str + '_' + IMLibAuthentication.realm
    }
    return str
  },

  /**
   * Get a cookie value for a key (with realm suffix).
   * @param {string} key
   * @returns {string}
   */
  getCookie: function (key) {
    'use strict'
    let s = ''
    try {
      s = document.cookie.split('; ')
    } catch (e) {
    }
    const targetKey = IMLibAuthentication.getKeyWithRealm(key)
    for (let i = 0; i < s.length; i++) {
      if (s[i].indexOf(targetKey + '=') === 0) {
        return decodeURIComponent(s[i].substring(s[i].indexOf('=') + 1))
      }
    }
    return ''
  },

  /**
   * Remove a cookie for a key (with realm suffix).
   * @param {string} key
   */
  removeCookie: function (key) {
    'use strict'
    if (document && document.cookie) {
      document.cookie = IMLibAuthentication.getKeyWithRealm(key) + '=; path=/; max-age=0; expires=Thu, 1-Jan-1900 00:00:00 GMT;'
      document.cookie = IMLibAuthentication.getKeyWithRealm(key) + '=; max-age=0;  expires=Thu, 1-Jan-1900 00:00:00 GMT;'
    }
  },

  /**
   * Set a cookie for a key (with realm suffix).
   * @param {string} key
   * @param {string} val
   */
  setCookie: function (key, val) {
    'use strict'
    IMLibAuthentication.setCookieWorker(IMLibAuthentication.getKeyWithRealm(key), val, false, IMLibAuthentication.authExpired)
  },

  /**
   * Set a cookie for a key, domain-wide.
   * @param {string} key
   * @param {string} val
   * @param {boolean} [noRealm]
   */
  setCookieDomainWide: function (key, val, noRealm) {
    'use strict'
    const realKey = (noRealm === true) ? key : IMLibAuthentication.getKeyWithRealm(key)
    IMLibAuthentication.setCookieWorker(realKey, val, true, IMLibAuthentication.authExpired)
  },

  /**
   * Internal cookie setter.
   * @param {string} key
   * @param {string} val
   * @param {boolean} isDomain
   * @param {number} expired seconds
   */
  setCookieWorker: function (key, val, isDomain, expired) {
    'use strict'
    const d = new Date()
    d.setTime(d.getTime() + expired * 1000)
    let cookieString = key + '=' + encodeURIComponent(val)
    if (INTERMediatorOnPage.credentialCookieDomain) {
      cookieString += `;domain=.${INTERMediatorOnPage.credentialCookieDomain}`
      // The dot before the domain name is for matching the PHP's setcookie function's behavior.
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

  /**
   * Perform logout operation
   * @param {boolean|string} move URL to redirect to after logout, false for no redirect
   * @param {boolean} dontMove If true, prevents any redirection after logout
   */
  logout: function (move = false, dontMove = false) {
    const logoutURL = IMLibAuthenticationUI.logoutURL
    IMLibAuthentication.authUserSalt = ''
    IMLibAuthentication.authChallenge = ''
    IMLibAuthentication.passkeyChallenge = ''
    IMLibAuthenticationUI.loginURL = null
    IMLibAuthenticationUI.logoutURL = null
    IMLibAuthentication.removeFromSessionStorageWithFallDown('_im_localcontext')
    switch (IMLibAuthentication.authStoring) {
      case 'cookie':
      case 'cookie-domainwide':
      case 'credential':
        IMLibAuthentication.removeCookie('_im_clientid')
        IMLibAuthentication.removeCookie('_im_session_exp')
        IMLibAuthentication.removeCookie('_im_username')
        IMLibAuthentication.removeCookie('_im_credential')
        IMLibAuthentication.removeCookie('_im_credential2m')
        IMLibAuthentication.removeCookie('_im_credential2')
        break
      case 'session-storage':
        IMLibAuthentication.removeFromSessionStorageWithFallDown('_im_clientid')
        IMLibAuthentication.removeFromSessionStorageWithFallDown('_im_session_exp')
        IMLibAuthentication.removeFromSessionStorageWithFallDown('_im_username')
        IMLibAuthentication.removeFromSessionStorageWithFallDown('_im_credential')
        IMLibAuthentication.removeFromSessionStorageWithFallDown('_im_credential2m')
        IMLibAuthentication.removeFromSessionStorageWithFallDown('_im_credential2')
        IMLibAuthentication.removeFromSessionStorageWithFallDown('_im_mediatoken')
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

  /**
   * Helper to set all hashed password storages at once.
   * @param {string} value
   */
  storedHashedPasswordAllClear: (value) => {
    IMLibAuthentication.authHashedPassword(value)
    IMLibAuthentication.authHashedPassword2m(value)
    IMLibAuthentication.authHashedPassword2(value)
  },
}


// @@IM@@IgnoringRestOfFile
module.exports = IMLibAuthentication
const IMLibLocalContext = require('../../src/js/INTER-Mediator-LocalContext')
