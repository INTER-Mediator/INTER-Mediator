/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

/* ==================================================
 Database Access Object for Server-based Database
 ================================================== */

// JSHint support
/* global IMLibContextPool, INTERMediator, INTERMediatorOnPage, IMLibMouseEventDispatch, IMLibLocalContext,
 IMLibChangeEventDispatch, INTERMediatorLib, IMLibQueue, IMLibCalc, IMLibPageNavigation, INTERMediatorLog,
 IMLibEventResponder, IMLibElement, Parser, IMLib, jsSHA, JSEncrypt */

/**
 * @fileoverview INTERMediator_DBAdapter class is defined here.
 */
/**
 *
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
const INTERMediator_DBAdapter = {

  eliminateDuplicatedConditions: false,
  /*
   If this property is set to true, the dupilicate conditions in query is going to eliminate before
   submitting to the server. This behavior is required in some case of FileMaker Server, but it can resolve
   by using the id=>-recid in a context. 2015-4-19 Masayuki Nii.
   */
  debugMessage: false,

  generate_authParams: function () {
    'use strict'
    let authParams = ''
    if (INTERMediatorOnPage.authUser.length > 0) {
      authParams = '&clientid=' + encodeURIComponent(INTERMediatorOnPage.clientId)
      authParams += '&authuser=' + encodeURIComponent(INTERMediatorOnPage.authUser)
      if (INTERMediatorOnPage.isNativeAuth || INTERMediatorOnPage.isLDAP) {
        if (INTERMediatorOnPage.authCryptedPassword && INTERMediatorOnPage.authChallenge) {
          const encrypt = new JSEncrypt()
          // require 2048-bit key length at least
          encrypt.setPublicKey(INTERMediatorOnPage.publickey)
          const encrypted = encrypt.encrypt(
            INTERMediatorOnPage.authCryptedPassword.substr(0, 220) +
            IMLib.nl_char + INTERMediatorOnPage.authChallenge
          )
          authParams += '&cresponse=' + encodeURIComponent(encrypted +
            IMLib.nl_char + INTERMediatorOnPage.authCryptedPassword.substr(220))
          if (INTERMediator_DBAdapter.debugMessage) {
            INTERMediatorLog.setDebugMessage('generate_authParams/authCryptedPassword=' +
              INTERMediatorOnPage.authCryptedPassword)
            INTERMediatorLog.setDebugMessage('generate_authParams/authChallenge=' +
              INTERMediatorOnPage.authChallenge)
          }
        } else {
          authParams += '&cresponse=dummy'
        }
      }
      if ((INTERMediatorOnPage.authHashedPassword
        || INTERMediatorOnPage.authHashedPassword2m
        || INTERMediatorOnPage.authHashedPassword2)
        && INTERMediatorOnPage.authChallenge) {
        if (INTERMediatorOnPage.passwordHash < 1.1 && INTERMediatorOnPage.authHashedPassword) {
          const shaObj = new jsSHA('SHA-256', 'TEXT')
          shaObj.setHMACKey(INTERMediatorOnPage.authChallenge, 'TEXT')
          shaObj.update(INTERMediatorOnPage.authHashedPassword)
          const hmacValue = shaObj.getHMAC('HEX')
          authParams += '&response=' + encodeURIComponent(hmacValue)
        }
        if (INTERMediatorOnPage.passwordHash < 1.6 && INTERMediatorOnPage.authHashedPassword2m) {
          const shaObj = new jsSHA('SHA-256', 'TEXT')
          shaObj.setHMACKey(INTERMediatorOnPage.authChallenge, 'TEXT')
          shaObj.update(INTERMediatorOnPage.authHashedPassword2m)
          const hmacValue = shaObj.getHMAC('HEX')
          authParams += '&response2m=' + encodeURIComponent(hmacValue)
        }
        if (INTERMediatorOnPage.passwordHash < 2.1 && INTERMediatorOnPage.authHashedPassword2) {
          const shaObj = new jsSHA('SHA-256', 'TEXT')
          shaObj.setHMACKey(INTERMediatorOnPage.authChallenge, 'TEXT')
          shaObj.update(INTERMediatorOnPage.authHashedPassword2)
          const hmacValue = shaObj.getHMAC('HEX')
          authParams += '&response2=' + encodeURIComponent(hmacValue)
        }
        if (INTERMediator_DBAdapter.debugMessage) {
          INTERMediatorLog.setDebugMessage('generate_authParams/authHashedPassword=' +
            INTERMediatorOnPage.authHashedPassword)
          INTERMediatorLog.setDebugMessage('generate_authParams/authChallenge=' +
            INTERMediatorOnPage.authChallenge)
        }
      } else {
        authParams += '&response=dummy'
      }
    }

    authParams += '&notifyid='
    authParams += encodeURIComponent(INTERMediatorOnPage.clientNotificationIdentifier())
    // authParams += ('&pusher=' + (INTERMediator.pusherAvailable ? 'yes' : ''))
    return authParams
  },

  store_challenge: function (challenge) {
    'use strict'
    if (challenge !== null) {
      INTERMediatorOnPage.authChallenge = challenge.substr(0, 24)
      INTERMediatorOnPage.authUserHexSalt = challenge.substr(24, 32)
      INTERMediatorOnPage.authUserSalt = String.fromCharCode(
        parseInt(challenge.substr(24, 2), 16),
        parseInt(challenge.substr(26, 2), 16),
        parseInt(challenge.substr(28, 2), 16),
        parseInt(challenge.substr(30, 2), 16))
      if (INTERMediator_DBAdapter.debugMessage) {
        INTERMediatorLog.setDebugMessage('store_challenge/authChallenge=' + INTERMediatorOnPage.authChallenge)
        INTERMediatorLog.setDebugMessage('store_challenge/authUserHexSalt=' + INTERMediatorOnPage.authUserHexSalt)
        INTERMediatorLog.setDebugMessage('store_challenge/authUserSalt=' + INTERMediatorOnPage.authUserSalt)
      }
    }
  },

  logging_comAction: function (debugMessageNumber, appPath, accessURL, authParams) {
    'use strict'
    INTERMediatorLog.setDebugMessage(
      INTERMediatorOnPage.getMessages()[debugMessageNumber] +
      'Accessing:' + decodeURI(appPath) + ', Parameters:' + decodeURI(accessURL + authParams))
  },

  logging_comResult: function (myRequest, resultCount, dbresult, requireAuth, challenge, clientid, newRecordKeyValue, changePasswordResult, mediatoken) {
    'use strict'
    let responseTextTrancated
    if (INTERMediatorLog.debugMode > 1) {
      if (myRequest.responseText.length > 1000) {
        responseTextTrancated = myRequest.responseText.substr(0, 1000) + ' ...[trancated]'
      } else {
        responseTextTrancated = myRequest.responseText
      }
      INTERMediatorLog.setDebugMessage('myRequest.responseText=' + responseTextTrancated)
      INTERMediatorLog.setDebugMessage('Return: resultCount=' + resultCount +
        ', dbresult=' + INTERMediatorLib.objectToString(dbresult) + IMLib.nl_char +
        'Return: requireAuth=' + requireAuth +
        ', challenge=' + challenge + ', clientid=' + clientid + IMLib.nl_char +
        'Return: newRecordKeyValue=' + newRecordKeyValue +
        ', changePasswordResult=' + changePasswordResult + ', mediatoken=' + mediatoken
      )
    }
  },

  /* No return values */
  server_access_async: async function (accessURL, debugMessageNumber, errorMessageNumber,
                                       successProc = null, failedProc = null, authAgainProc = null,
                                       fData = null) {
    // 'use strict'
    let newRecordKeyValue = '', dbresult = '', resultCount = 0, totalCount = null, challenge = null, clientid = null,
      requireAuth = false, myRequest = null, changePasswordResult = null, mediatoken = null, appPath, authParams,
      jsonObject, i, /*notifySupport = false, */useNull = false, registeredID = '', alertBackup
    appPath = INTERMediatorOnPage.getEntryPath()
    authParams = INTERMediator_DBAdapter.generate_authParams()
    INTERMediator_DBAdapter.logging_comAction(debugMessageNumber, appPath, accessURL, authParams)
    // INTERMediatorOnPage.notifySupport = notifySupport
    const promise = new Promise((resolve, reject) => {
      try {
        myRequest = new XMLHttpRequest()
        myRequest.open('POST', appPath, true, INTERMediatorOnPage.httpuser, INTERMediatorOnPage.httppasswd)
        myRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
        myRequest.setRequestHeader('X-From', location.href)
        myRequest.setRequestHeader('charset', 'utf-8')
        myRequest.onreadystatechange = () => {
          switch (myRequest.readyState) {
            case 0: // Unsent
              break
            case 1: // Opened
              break
            case 2: // Headers Received
              break
            case 3: // Loading
              break
            case 4:
              try {
                jsonObject = JSON.parse(myRequest.responseText)
              } catch (ex) {
                INTERMediatorLog.setErrorMessage('Communication Error: ' + myRequest.responseText)
                if (failedProc) {
                  failedProc(new Error('_im_communication_error_'))
                }
                INTERMediatorLog.flushMessage()
                return
              }
              resultCount = jsonObject.resultCount ? jsonObject.resultCount : 0
              totalCount = jsonObject.totalCount ? jsonObject.totalCount : null
              dbresult = jsonObject.dbresult ? jsonObject.dbresult : null
              requireAuth = jsonObject.requireAuth ? jsonObject.requireAuth : false
              challenge = jsonObject.challenge ? jsonObject.challenge : null
              clientid = jsonObject.clientid ? jsonObject.clientid : null
              newRecordKeyValue = jsonObject.newRecordKeyValue ? jsonObject.newRecordKeyValue : ''
              changePasswordResult = jsonObject.changePasswordResult ? jsonObject.changePasswordResult : null
              mediatoken = jsonObject.mediatoken ? jsonObject.mediatoken : null
              //notifySupport = jsonObject.notifySupport
              alertBackup = INTERMediatorLog.errorMessageByAlert
              INTERMediatorLog.errorMessageByAlert = false
              for (i = 0; i < jsonObject.errorMessages.length; i++) {
                INTERMediatorLog.setErrorMessage(jsonObject.errorMessages[i])
              }
              INTERMediatorLog.errorMessageByAlert = alertBackup
              for (i = 0; i < jsonObject.debugMessages.length; i++) {
                INTERMediatorLog.setDebugMessage(jsonObject.debugMessages[i])
              }
              useNull = jsonObject.usenull
              registeredID = jsonObject.hasOwnProperty('registeredid') ? jsonObject.registeredid : ''

              if (jsonObject.errorMessages.length > 0) {
                INTERMediatorLog.setErrorMessage('Communication Error: ' + jsonObject.errorMessages)
                if (failedProc) {
                  failedProc()
                }
                throw 'Communication Error'
              }

              INTERMediator_DBAdapter.logging_comResult(myRequest, resultCount, dbresult, requireAuth,
                challenge, clientid, newRecordKeyValue, changePasswordResult, mediatoken)
              INTERMediator_DBAdapter.store_challenge(challenge)
              if (clientid !== null) {
                INTERMediatorOnPage.clientId = clientid
              }
              if (mediatoken !== null) {
                INTERMediatorOnPage.mediaToken = mediatoken
                INTERMediatorOnPage.storeMediaCredentialsToCookie()
              }
              // This is forced fail-over for the password was changed in LDAP auth.
              if (INTERMediatorOnPage.isLDAP === true &&
                INTERMediatorOnPage.authUserHexSalt !== INTERMediatorOnPage.authHashedPassword.substr(-8, 8)) {
                if (accessURL !== 'access=challenge') {
                  requireAuth = true
                }
              }
              if (INTERMediatorOnPage.isSAML && jsonObject.samluser) {
                INTERMediatorOnPage.authUser =jsonObject.samluser
                INTERMediatorOnPage.authHashedPassword = jsonObject.temppw
                INTERMediatorOnPage.authHashedPassword2m = jsonObject.temppw
                INTERMediatorOnPage.authHashedPassword2 = jsonObject.temppw
                INTERMediatorOnPage.logoutScript = function () {
                  location.href = jsonObject.samllogouturl
                }
              }
              if (accessURL.indexOf('access=changepassword&newpass=') === 0) {
                if (successProc) {
                  successProc({
                    dbresult: dbresult,
                    resultCount: resultCount,
                    totalCount: totalCount,
                    newRecordKeyValue: newRecordKeyValue,
                    newPasswordResult: changePasswordResult,
                    registeredId: registeredID,
                    nullAcceptable: useNull
                  })
                }
                return
              }
              if (requireAuth) {
                INTERMediatorLog.setDebugMessage('Authentication Required, user/password panel should be show.')
                INTERMediatorOnPage.clearCredentials()
                if (authAgainProc) {
                  authAgainProc(myRequest)
                }
                return
              }
              if (!accessURL.match(/access=challenge/)) {
                INTERMediatorOnPage.authCount = 0
              }
              INTERMediatorOnPage.storeCredentialsToCookieOrStorage()
              //INTERMediatorOnPage.notifySupport = notifySupport
              if (successProc) {
                successProc({
                  dbresult: dbresult,
                  resultCount: resultCount,
                  totalCount: totalCount,
                  newRecordKeyValue: newRecordKeyValue,
                  newPasswordResult: changePasswordResult,
                  registeredId: registeredID,
                  nullAcceptable: useNull
                })
              }
              resolve()
              break
          }
        }
        if (fData) {
          for (const param of authParams.split('&')) {
            const comp = param.split('=')
            if (comp.length == 2 && comp[0].length > 0) {
              fData.append(comp[0], decodeURIComponent(comp[1]))
            }
          }
          //myRequest.setRequestHeader('Content-Type', 'multipart/form-data')
          myRequest.send(fData)
        } else {
          myRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
          myRequest.send(accessURL + authParams)
        }
      } catch (e) {
        INTERMediatorLog.setErrorMessage(e,
          INTERMediatorLib.getInsertedString(
            INTERMediatorOnPage.getMessages()[errorMessageNumber], [e, myRequest.responseText]))
        if (failedProc) {
          failedProc()
        }
        reject()
      }
    })
    return promise
  },

  changePassword: async function (username, oldpassword, newpassword, doSucceed, doFail) {
    'use strict'
    let params

    return new Promise(async (resolve, reject) => {
      if (!username || !oldpassword) {
        reject(new Error('_im_changepw_noparams'))
        return
      }
      INTERMediatorOnPage.authUser = username
      if (username !== '' && // No usename and no challenge, get a challenge.
        (INTERMediatorOnPage.authChallenge === null || INTERMediatorOnPage.authChallenge.length < 24)) {
        INTERMediatorOnPage.authHashedPassword = 'need-hash-pls' // Dummy Hash for getting a challenge
        INTERMediatorOnPage.authHashedPassword2m = 'need-hash-pls' // Dummy Hash for getting a challenge
        INTERMediatorOnPage.authHashedPassword2 = 'need-hash-pls' // Dummy Hash for getting a challenge
        try {
          await INTERMediator_DBAdapter.getChallenge()
        } catch (er) {
          reject(er)
          return
        }
      }
      INTERMediatorOnPage.authHashedPassword = null
      INTERMediatorOnPage.authHashedPassword2m = null
      INTERMediatorOnPage.authHashedPassword2 = null
      if (INTERMediatorOnPage.passwordHash < 1.1) {
        let shaObj = new jsSHA('SHA-1', 'TEXT')
        shaObj.update(oldpassword + INTERMediatorOnPage.authUserSalt)
        let hash = shaObj.getHash('HEX')
        INTERMediatorOnPage.authHashedPassword = hash + INTERMediatorOnPage.authUserHexSalt
      }
      if (INTERMediatorOnPage.passwordHash < 1.6) {
        let shaObj = new jsSHA('SHA-1', 'TEXT')
        shaObj.update(oldpassword + INTERMediatorOnPage.authUserSalt)
        let hash = shaObj.getHash('HEX')
        shaObj = new jsSHA('SHA-256', 'TEXT', {"numRounds": 5000})
        shaObj.update(hash + INTERMediatorOnPage.authUserSalt)
        let hashNext = shaObj.getHash('HEX')
        INTERMediatorOnPage.authHashedPassword2m = hashNext + INTERMediatorOnPage.authUserHexSalt
      }
      if (INTERMediatorOnPage.passwordHash < 2.1) {
        let shaObj = new jsSHA('SHA-256', 'TEXT', {"numRounds": 5000})
        shaObj.update(oldpassword + INTERMediatorOnPage.authUserSalt)
        let hash = shaObj.getHash('HEX')
        INTERMediatorOnPage.authHashedPassword2 = hash + INTERMediatorOnPage.authUserHexSalt
      }
      params = 'access=changepassword&newpass=' + INTERMediatorLib.generatePasswordHash(newpassword)
      this.server_access_async(params, 1029, 1030,
        (result) => {
          if (result.newPasswordResult) {
            if (INTERMediatorOnPage.isNativeAuth || INTERMediatorOnPage.isLDAP) {
              const encrypt = new JSEncrypt()
              encrypt.setPublicKey(INTERMediatorOnPage.publickey)
              INTERMediatorOnPage.authCryptedPassword = encrypt.encrypt(newpassword)
            } else {
              INTERMediatorOnPage.authCryptedPassword = ''
            }
            if (INTERMediatorOnPage.passwordHash < 1.1) {
              let shaObj = new jsSHA('SHA-1', 'TEXT')
              shaObj.update(newpassword + INTERMediatorOnPage.authUserSalt)
              let hash = shaObj.getHash('HEX')
              INTERMediatorOnPage.authHashedPassword = hash + INTERMediatorOnPage.authUserHexSalt
            }
            // if (INTERMediatorOnPage.passwordHash < 1.6) {
            //   let shaObj = new jsSHA('SHA-1', 'TEXT')
            //   shaObj.update(newpassword + INTERMediatorOnPage.authUserSalt)
            //   let hash = shaObj.getHash('HEX')
            //   shaObj = new jsSHA('SHA-256', 'TEXT')
            //   shaObj.update(hash + INTERMediatorOnPage.authUserSalt)
            //   let hashNext = shaObj.getHash('HEX')
            //   INTERMediatorOnPage.authHashedPassword2m = hashNext + INTERMediatorOnPage.authUserHexSalt
            // }
            if (INTERMediatorOnPage.passwordHash < 2.1) {
              let shaObj = new jsSHA('SHA-256', 'TEXT', {"numRounds": 5000})
              shaObj.update(newpassword + INTERMediatorOnPage.authUserSalt)
              let hash = shaObj.getHash('HEX')
              INTERMediatorOnPage.authHashedPassword2 = hash + INTERMediatorOnPage.authUserHexSalt
            }
            INTERMediatorOnPage.storeCredentialsToCookieOrStorage()
            doSucceed()
          } else {
            doFail()
          }
        },
        (er) => {
          doFail()
        }
      )
    }).catch((er) => {
      throw er
    })
  },

  getChallenge: async function () {
    'use strict'
    await INTERMediator_DBAdapter.server_access_async('access=challenge', 1027, 1028, null, null, null)
  },

  uploadFile: function (parameters, uploadingFile, doItOnFinish, exceptionProc) {
    'use strict'
    let myRequest = null
    let appPath, authParams, accessURL, i
    // let result = this.server_access('access=uploadfile' + parameters, 1031, 1032, uploadingFile)
    appPath = INTERMediatorOnPage.getEntryPath()
    authParams = INTERMediator_DBAdapter.generate_authParams()
    accessURL = 'access=uploadfile' + parameters
    INTERMediator_DBAdapter.logging_comAction(1031, appPath, accessURL, authParams)
    try {
      myRequest = new XMLHttpRequest()
      myRequest.open('POST', appPath, true, INTERMediatorOnPage.httpuser, INTERMediatorOnPage.httppasswd)
      myRequest.setRequestHeader('charset', 'utf-8')
      let params = (accessURL + authParams).split('&')
      let fd = new FormData()
      for (i = 0; i < params.length; i++) {
        let valueset = params[i].split('=')
        fd.append(valueset[0], decodeURIComponent(valueset[1]))
      }
      fd.append('_im_uploadfile', uploadingFile.content)
      myRequest.onreadystatechange = function () {
        switch (myRequest.readyState) {
          case 3:
            break
          case 4:
            INTERMediator_DBAdapter.uploadFileAfterSucceed(myRequest, doItOnFinish, exceptionProc, false)
            break
        }
      }
      myRequest.send(fd)
    } catch (e) {
      INTERMediatorLog.setErrorMessage(e,
        INTERMediatorLib.getInsertedString(
          INTERMediatorOnPage.getMessages()[1032], [e, myRequest.responseText]))
      exceptionProc()
    }
  },

  uploadFileAfterSucceed: function (myRequest, doItOnFinish, exceptionProc, isErrorDialog) {
    'use strict'
    let newRecordKeyValue = ''
    let dbresult = ''
    let resultCount = 0
    let challenge = null
    let clientid = null
    let requireAuth = false
    let changePasswordResult = null
    let mediatoken = null
    let jsonObject, i
    let returnValue = true
    try {
      jsonObject = JSON.parse(myRequest.responseText)
    } catch (ex) {
      INTERMediatorLog.setErrorMessage(ex,
        INTERMediatorLib.getInsertedString(
          INTERMediatorOnPage.getMessages()[1032], ['', '']))
      INTERMediatorLog.flushMessage()
      exceptionProc()
      return false
    }
    resultCount = jsonObject.resultCount ? jsonObject.resultCount : 0
    dbresult = jsonObject.dbresult ? jsonObject.dbresult : null
    requireAuth = jsonObject.requireAuth ? jsonObject.requireAuth : false
    challenge = jsonObject.challenge ? jsonObject.challenge : null
    clientid = jsonObject.clientid ? jsonObject.clientid : null
    newRecordKeyValue = jsonObject.newRecordKeyValue ? jsonObject.newRecordKeyValue : ''
    changePasswordResult = jsonObject.changePasswordResult ? jsonObject.changePasswordResult : null
    mediatoken = jsonObject.mediatoken ? jsonObject.mediatoken : null
    for (i = 0; i < jsonObject.errorMessages.length; i++) {
      if (isErrorDialog) {
        window.alert(jsonObject.errorMessages[i])
      } else {
        INTERMediatorLog.setErrorMessage(jsonObject.errorMessages[i])
      }
      returnValue = false
    }
    for (i = 0; i < jsonObject.debugMessages.length; i++) {
      INTERMediatorLog.setDebugMessage(jsonObject.debugMessages[i])
    }

    INTERMediator_DBAdapter.logging_comResult(myRequest, resultCount, dbresult, requireAuth,
      challenge, clientid, newRecordKeyValue, changePasswordResult, mediatoken)
    INTERMediator_DBAdapter.store_challenge(challenge)
    if (clientid !== null) {
      INTERMediatorOnPage.clientId = clientid
    }
    if (mediatoken !== null) {
      INTERMediatorOnPage.mediaToken = mediatoken
    }
    if (requireAuth) {
      INTERMediatorLog.setDebugMessage('Authentication Required, user/password panel should be show.')
      INTERMediatorOnPage.clearCredentials()
      // throw new Error('_im_requath_request_')
      exceptionProc()
    }
    INTERMediatorOnPage.authCount = 0
    INTERMediatorOnPage.storeCredentialsToCookieOrStorage()
    doItOnFinish(dbresult)
    return returnValue
  },

  /*
   db_query
   Querying from database. The parameter of this function should be the object as below:

   {
   name:<name of the context>
   records:<the number of retrieving records, could be null>
   fields:<the array of fields to retrieve, but this parameter is ignored so far.
   parentkeyvalue:<the value of foreign key field, could be null>
   conditions:<the array of the object {field:xx,operator:xx,value:xx} to search records, could be null>
   useoffset:<true/false whether the offset parameter is set on the query.>
   uselimit:<true/false whether the limit parameter is set on the query.>
   primaryKeyOnly: true/false
   }
   */
  db_query_async: async function (args, successProc, failedProc) {
    'use strict'
    let params
    if (!INTERMediator_DBAdapter.db_queryChecking(args)) {
      return
    }
    params = INTERMediator_DBAdapter.db_queryParameters(args)
    return new Promise((resolve, reject) => {
        this.server_access_async(params, 1012, 1004,
          (() => {
            let contextDef
            let contextName = args.name
            let recordsNumber = Number(args.records)
            let resolveCapt = resolve
            return (result) => {
              result.count = result.dbresult ? Object.keys(result.dbresult).length : 0
              contextDef = IMLibContextPool.getContextDef(contextName)
              if (!contextDef.relation &&
                args.paging && Boolean(args.paging) === true) {
                INTERMediator.pagedAllCount = parseInt(result.resultCount, 10)
                if (result.totalCount) {
                  INTERMediator.totalRecordCount = parseInt(result.totalCount, 10)
                }
              }
              if ((args.paging !== null) && (Boolean(args.paging) === true)) {
                INTERMediator.pagination = true
                if (!(recordsNumber >= Number(INTERMediator.pagedSize) &&
                  Number(INTERMediator.pagedSize) > 0)) {
                  INTERMediator.pagedSize = parseInt(recordsNumber, 10)
                }
              }
              successProc ? successProc(result) : false
              resolveCapt(result)
            }
          })(),
          failedProc,
          INTERMediator_DBAdapter.createExceptionFunc(
            1016,
            (function () {
              const argsCapt = args
              const succesProcCapt = successProc
              const failedProcCapt = failedProc
              return function () {
                INTERMediator.constructMain(INTERMediator.currentContext, INTERMediator.currentRecordset)
              }
            })()
          )
        )
      }
    ).catch((err) => {
      throw err
    })
  },

  db_queryChecking: function (args) {
    'use strict'
    let noError = true
    if (args.name === null || args.name === '') {
      INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1005))
      noError = false
    }
    return noError
  },

  db_queryParameters: function (args) {
    'use strict'
    let i, index, params, counter, extCount, criteriaObject, sortkeyObject, extCountSort
    let recordLimit = 10000000
    let conditions, conditionSign, modifyConditions, orderFields, key,
      keyParams, value, fields, operator, orderedKeys
    let addExLimit = 1
    let removeIndice = []
    if (args.records === null) {
      params = 'access=read&name=' + encodeURIComponent(args.name)
    } else {
      if (parseInt(args.records, 10) === 0 &&
        (INTERMediatorOnPage.dbClassName.match(/FileMaker_FX/) ||
          INTERMediatorOnPage.dbClassName.match(/FileMaker_DataAPI/))) {
        params = 'access=describe&name=' + encodeURIComponent(args.name)
      } else {
        params = 'access=read&name=' + encodeURIComponent(args.name)
      }
      if (Boolean(args.uselimit) === true &&
        parseInt(args.records, 10) >= INTERMediator.pagedSize &&
        parseInt(INTERMediator.pagedSize, 10) > 0) {
        recordLimit = INTERMediator.pagedSize
      } else {
        recordLimit = args.records
      }
    }

    if (args.primaryKeyOnly) {
      params += '&pkeyonly=true'
    }

    counter = 0
    if (INTERMediatorLib.isArray(args.fields)) {
      for (const field of args.fields) {
        params += '&field_' + counter + '=' + encodeURIComponent(field)
        counter += 1
      }
    }
    counter = 0
    if (args.parentkeyvalue) {
      // noinspection JSDuplicatedDeclaration
      for (index in args.parentkeyvalue) {
        if (args.parentkeyvalue.hasOwnProperty(index)) {
          params += '&foreign' + counter +
            'field=' + encodeURIComponent(index)
          params += '&foreign' + counter +
            'value=' + encodeURIComponent(args.parentkeyvalue[index])
          counter++
        }
      }
    }
    if (args.useoffset && INTERMediator.startFrom !== null) {
      params += '&start=' + encodeURIComponent(INTERMediator.startFrom)
    }
    extCount = 0
    conditions = []
    while (args.conditions && args.conditions[extCount]) {
      conditionSign = args.conditions[extCount].field + '#' +
        args.conditions[extCount].operator + '#' +
        args.conditions[extCount].value
      if (!INTERMediator_DBAdapter.eliminateDuplicatedConditions || conditions.indexOf(conditionSign) < 0) {
        params += '&condition' + extCount
        params += 'field=' + encodeURIComponent(args.conditions[extCount].field)
        params += '&condition' + extCount
        params += 'operator=' + encodeURIComponent(args.conditions[extCount].operator)
        params += '&condition' + extCount
        params += 'value=' + encodeURIComponent(args.conditions[extCount].value)
        conditions.push(conditionSign)
      }
      extCount++
    }
    criteriaObject = INTERMediator.additionalCondition[args.name]
    if (criteriaObject) {
      if (criteriaObject.field) {
        criteriaObject = [criteriaObject]
      }
      for (index = 0; index < criteriaObject.length; index++) {
        if (criteriaObject[index] && criteriaObject[index].field) {
          if (criteriaObject[index].value || criteriaObject[index].field === '__operation__') {
            conditionSign =
              criteriaObject[index].field + '#' +
              ((criteriaObject[index].operator !== undefined) ? criteriaObject[index].operator : '') + '#' +
              ((criteriaObject[index].value !== undefined) ? criteriaObject[index].value : '')
            if (!INTERMediator_DBAdapter.eliminateDuplicatedConditions || conditions.indexOf(conditionSign) < 0) {
              params += '&condition' + extCount
              params += 'field=' + encodeURIComponent(criteriaObject[index].field)
              if (criteriaObject[index].operator !== undefined) {
                params += '&condition' + extCount
                params += 'operator=' + encodeURIComponent(criteriaObject[index].operator)
              }
              if (criteriaObject[index].value !== undefined) {
                params += '&condition' + extCount
                value = criteriaObject[index].value
                if (Array.isArray(value)) {
                  value = JSON.stringify(value)
                }
                params += 'value=' + encodeURIComponent(value)
              }
              if (criteriaObject[index].field !== '__operation__') {
                conditions.push(conditionSign)
              }
            }
            extCount++
          }
        }
        if (criteriaObject[index] && criteriaObject[index].onetime) {
          removeIndice.push = index
        }
      }
      if (removeIndice.length > 0) {
        modifyConditions = []
        for (index = 0; index < criteriaObject.length; index++) {
          if (!(index in removeIndice)) {
            modifyConditions.push(criteriaObject[index])
          }
        }
        INTERMediator.additionalCondition[args.name] = modifyConditions
        IMLibLocalContext.archive()
      }
    }

    extCountSort = 0
    sortkeyObject = INTERMediator.additionalSortKey[args.name]
    if (sortkeyObject) {
      if (sortkeyObject.field) {
        sortkeyObject = [sortkeyObject]
      }
      for (index = 0; index < sortkeyObject.length; index++) {
        params += '&sortkey' + extCountSort
        params += 'field=' + encodeURIComponent(sortkeyObject[index].field)
        params += '&sortkey' + extCountSort
        params += 'direction=' + encodeURIComponent(sortkeyObject[index].direction)
        extCountSort++
      }
    }

    orderFields = {}
    addExLimit = INTERMediator.alwaysAddOperationExchange ? 0 : addExLimit
    for (key in IMLibLocalContext.store) {
      if (IMLibLocalContext.store.hasOwnProperty(key)) {
        value = String(IMLibLocalContext.store[key])
        keyParams = key.split(':')
        if (keyParams && keyParams.length > 1 && keyParams[1].trim() === args.name && value.length > 0) {
          if (keyParams[0].trim() === 'condition' && keyParams.length >= 4) {
            fields = keyParams[2].split(',')
            operator = keyParams[3].trim()
            if (fields.length > addExLimit) {
              params += '&condition' + extCount + 'field=__operation__'
              params += '&condition' + extCount + 'operator=ex'
              extCount++
              // conditions = []
            }
            for (index = 0; index < fields.length; index++) {
              conditionSign = fields[index].trim() + '#' + operator + '#' + value
              if (!INTERMediator_DBAdapter.eliminateDuplicatedConditions || conditions.indexOf(conditionSign) < 0) {
                params += '&condition' + extCount +
                  'field=' + encodeURIComponent(fields[index].replace(';;', '::').trim())
                params += '&condition' + extCount + 'operator=' + encodeURIComponent(operator)
                params += '&condition' + extCount + 'value=' + encodeURIComponent(value)
                conditions.push(conditionSign)
              }
              extCount++
            }
          } else if (keyParams[0].trim() === 'valueofaddorder' && keyParams.length >= 4) {
            orderFields[parseInt(value)] = [keyParams[2].trim(), keyParams[3].trim()]
          }
        }
      }
    }
    params += '&records=' + encodeURIComponent(recordLimit)
    orderedKeys = Object.keys(orderFields)
    for (i = 0; i < orderedKeys.length; i++) {
      params += '&sortkey' + extCountSort + 'field=' + encodeURIComponent(orderFields[orderedKeys[i]][0])
      params += '&sortkey' + extCountSort + 'direction=' + encodeURIComponent(orderFields[orderedKeys[i]][1])
      extCountSort++
    }
    return params
  },

  /*
   db_update
   Update the database. The parameter of this function should be the object as below:

   {   name:<Name of the Context>
   conditions:<the array of the object {field:xx,operator:xx,value:xx} to search records>
   dataset:<the array of the object {field:xx,value:xx}. each value will be set to the field.> }
   */
  db_updateChecking: function (args) {
    'use strict'
    let noError = true
    let contextDef

    if (args.name === null) {
      INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1007))
      noError = false
    }
    contextDef = IMLibContextPool.getContextDef(args.name)
    if (!contextDef.key) {
      INTERMediatorLog.setErrorMessage(
        INTERMediatorLib.getInsertedStringFromErrorNumber(1045, [args.name]))
      noError = false
    }
    if (args.dataset === null) {
      INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1011))
      noError = false
    }
    return noError
  },

  db_updateParameters: function (args) {
    'use strict'
    let params, extCount, counter, index, addedObject
    params = 'access=update&name=' + encodeURIComponent(args.name)
    counter = 0
    if (INTERMediator.additionalFieldValueOnUpdate &&
      INTERMediator.additionalFieldValueOnUpdate[args.name]) {
      addedObject = INTERMediator.additionalFieldValueOnUpdate[args.name]
      if (addedObject.field) {
        addedObject = [addedObject]
      }
      for (index in addedObject) {
        if (addedObject.hasOwnProperty(index)) {
          let oneDefinition = addedObject[index]
          params += '&field_' + counter + '=' + encodeURIComponent(oneDefinition.field)
          params += '&value_' + counter + '=' + encodeURIComponent(oneDefinition.value)
          counter++
        }
      }
    }

    if (args.conditions) {
      for (extCount = 0; extCount < args.conditions.length; extCount++) {
        params += '&condition' + extCount + 'field='
        params += encodeURIComponent(args.conditions[extCount].field)
        params += '&condition' + extCount + 'operator='
        params += encodeURIComponent(args.conditions[extCount].operator)
        if (args.conditions[extCount].value) {
          params += '&condition' + extCount + 'value='
          params += encodeURIComponent(args.conditions[extCount].value)
        }
      }
    }
    for (extCount = 0; extCount < args.dataset.length; extCount++) {
      params += '&field_' + (counter + extCount) + '=' + encodeURIComponent(args.dataset[extCount].field)
      params += '&value_' + (counter + extCount) + '=' + encodeURIComponent(args.dataset[extCount].value)
    }
    return params
  },

  db_update_async: async function (args, successProc, failedProc) {
    'use strict'
    let params
    if (!INTERMediator_DBAdapter.db_updateChecking(args)) {
      return
    }
    params = INTERMediator_DBAdapter.db_updateParameters(args)
    if (params) {
      await INTERMediatorOnPage.retrieveAuthInfo()
      INTERMediator_DBAdapter.server_access_async(
        params,
        1013,
        1014,
        successProc,
        failedProc,
        INTERMediator_DBAdapter.createExceptionFunc(
          1016,
          (function () {
            let argsCapt = args
            let succesProcCapt = successProc
            let failedProcCapt = failedProc
            return function () {
              INTERMediator.constructMain(INTERMediator.currentContext, INTERMediator.currentRecordset)
            }
          })()
        )
      )
    }
  },

  /*
   db_delete
   Delete the record. The parameter of this function should be the object as below:

   {   name:<Name of the Context>
   conditions:<the array of the object {field:xx,operator:xx,value:xx} to search records, could be null>}
   */
  db_deleteChecking: function (args) {
    'use strict'
    let noError = true
    let contextDef

    if (args.name === null) {
      INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1019))
      noError = false
    }
    contextDef = IMLibContextPool.getContextDef(args.name)
    if (!contextDef.key) {
      INTERMediatorLog.setErrorMessage(
        INTERMediatorLib.getInsertedStringFromErrorNumber(1045, [args.name]))
      noError = false
    }
    if (args.conditions === null) {
      INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1020))
      noError = false
    }
    return noError
  },

  db_deleteParameters: function (args) {
    'use strict'
    let params, i, counter, index, addedObject
    params = 'access=delete&name=' + encodeURIComponent(args.name)
    counter = 0
    if (INTERMediator.additionalFieldValueOnDelete &&
      INTERMediator.additionalFieldValueOnDelete[args.name]) {
      addedObject = INTERMediator.additionalFieldValueOnDelete[args.name]
      if (addedObject.field) {
        addedObject = [addedObject]
      }
      for (index in addedObject) {
        if (addedObject.hasOwnProperty(index)) {
          let oneDefinition = addedObject[index]
          params += '&field_' + counter + '=' + encodeURIComponent(oneDefinition.field)
          params += '&value_' + counter + '=' + encodeURIComponent(oneDefinition.value)
          counter++
        }
      }
    }

    for (i = 0; i < args.conditions.length; i++) {
      params += '&condition' + i + 'field=' + encodeURIComponent(args.conditions[i].field)
      params += '&condition' + i + 'operator=' + encodeURIComponent(args.conditions[i].operator)
      params += '&condition' + i + 'value=' + encodeURIComponent(args.conditions[i].value)
    }
    return params
  },

  db_delete_async: async function (args, successProc, failedProc) {
    'use strict'
    let params
    if (!INTERMediator_DBAdapter.db_deleteChecking(args)) {
      return
    }
    params = INTERMediator_DBAdapter.db_deleteParameters(args)
    if (params) {
      await INTERMediatorOnPage.retrieveAuthInfo()
      INTERMediator_DBAdapter.server_access_async(
        params,
        1017,
        1015,
        successProc,
        failedProc,
        INTERMediator_DBAdapter.createExceptionFunc(
          1016,
          (function () {
            let argsCapt = args
            let succesProcCapt = successProc
            let failedProcCapt = failedProc
            return function () {
              INTERMediator.constructMain(INTERMediator.currentContext, INTERMediator.currentRecordset)
            }
          })()
        )
      )
    }
  },

  /*
   db_createRecord
   Create a record. The parameter of this function should be the object as below:

   {   name:<Name of the Context>
   dataset:<the array of the object {field:xx,value:xx}. Initial value for each field> }

   This function returns the value of the key field of the new record.
   */
  db_createRecord_async: async function (args, successProc, failedProc) {
    'use strict'
    let isFormData = false, paramsStr = '', paramsFD = null
    for (const def of args.dataset) { // Checking the multi parted form data is required.
      if (def.value && def.value.file && def.value.kind && def.value.kind == 'attached') {
        isFormData = true
      }
    }
    paramsStr = INTERMediator_DBAdapter.db_createParameters(args)
    if (isFormData) {
      paramsFD = INTERMediator_DBAdapter.db_createParametersAsForm(args)
    }
    if (paramsStr) {
      await INTERMediatorOnPage.retrieveAuthInfo()
      INTERMediator_DBAdapter.server_access_async(
        paramsStr,
        1018,
        1016,
        successProc,
        failedProc,
        INTERMediator_DBAdapter.createExceptionFunc(1016, (function () {
          let argsCapt = args
          let succesProcCapt = successProc
          let failedProcCapt = failedProc
          return function () {
            INTERMediator.constructMain(INTERMediator.currentContext, INTERMediator.currentRecordset)
          }
        })()),
        paramsFD
      )
    }
  },

  db_createParameters: function (args) {
    'use strict'
    let params, i, index, addedObject, counter, targetKey, ds, key, contextDef

    if (args.name === null) {
      INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1021))
      return false
    }
    contextDef = IMLibContextPool.getContextDef(args.name)
    if (!contextDef.key) {
      INTERMediatorLog.setErrorMessage(
        INTERMediatorLib.getInsertedStringFromErrorNumber(1045, [args.name]))
      return false
    }
    params = 'access=create&name=' + encodeURIComponent(args.name)
    counter = 0
    if (INTERMediator.additionalFieldValueOnNewRecord &&
      INTERMediator.additionalFieldValueOnNewRecord[args.name]) {
      addedObject = INTERMediator.additionalFieldValueOnNewRecord[args.name]
      if (addedObject.field) {
        addedObject = [addedObject]
      }
      for (const def of addedObject) {
        params += '&field_' + counter + '=' + encodeURIComponent(def.field)
        params += '&value_' + counter + '=' + encodeURIComponent(def.value)
        counter++
      }
    }
    for (i = 0; i < args.dataset.length; i++) {
      params += '&field_' + counter + '=' + encodeURIComponent(args.dataset[i].field)
      params += '&value_' + counter + '=' + encodeURIComponent(args.dataset[i].value)
      counter++
    }
    return params
  },

  db_createParametersAsForm: function (args) {
    let params, i, index, addedObject, counter, counterAttach, fields
    params = new FormData()
    params.append('access', 'create')
    params.append('name', encodeURIComponent(args.name))
    fields = ''
    counter = 0
    counterAttach = 0
    if (INTERMediator.additionalFieldValueOnNewRecord &&
      INTERMediator.additionalFieldValueOnNewRecord[args.name]) {
      addedObject = INTERMediator.additionalFieldValueOnNewRecord[args.name]
      if (addedObject.field) {
        addedObject = [addedObject]
      }
      for (index in addedObject) {
        if (addedObject.hasOwnProperty(index)) {
          let oneDefinition = addedObject[index]
          if (oneDefinition.value && oneDefinition.value.file
            && oneDefinition.value.kind && oneDefinition.value.kind == 'attached') {
            params.append('value_' + counterAttach, oneDefinition.value.name)
            fields += (fields.length === 0 ? '' : ',') + oneDefinition.field
            counterAttach++
          } else {
            params.append('field_' + counter, oneDefinition.field)
            params.append('value_' + counter, oneDefinition.value ? oneDefinition.value : '')
            counter++
          }
        }
      }
    }
    for (i = 0; i < args.dataset.length; i++) {
      if (args.dataset[i].value && args.dataset[i].value.file
        && args.dataset[i].value.kind && args.dataset[i].value.kind == 'attached') {
        params.append('attach_' + counterAttach, args.dataset[i].value.file, args.dataset[i].value.file.name)
        fields += (fields.length === 0 ? '' : ',') + args.dataset[i].field
        counterAttach++
      } else {
        params.append('field_' + counter, args.dataset[i].field)
        params.append('value_' + counter, args.dataset[i].value ? args.dataset[i].value : '')
        counter++
      }
    }
    params.append('_im_filesfields', fields)
    return params
  },

  /*
   db_copy
   Copy the record. The parameter of this function should be the object as below:
   {
   name: The name of context,
   conditions: [ {
   field: Field name, operator: '=', value: Field Value : of the source record
   }],
   associated: Associated Record info.
   [{name: assocDef.name, field: fKey, value: fValue}]
   }
   {   name:<Name of the Context>
   conditions:<the array of the object {field:xx,operator:xx,value:xx} to search records, could be null>}
   */
  db_copy_async: async function (args, successProc, failedProc) {
    'use strict'
    let params = INTERMediator_DBAdapter.db_copyParameters(args)
    if (params) {
      await INTERMediatorOnPage.retrieveAuthInfo()
      INTERMediator_DBAdapter.server_access_async(
        params,
        1017,
        1015,
        successProc,
        failedProc,
        INTERMediator_DBAdapter.createExceptionFunc(
          1016,
          (function () {
            let argsCapt = args
            let succesProcCapt = successProc
            let failedProcCapt = failedProc
            return function () {
              INTERMediator.constructMain(INTERMediator.currentContext, INTERMediator.currentRecordset)
            }
          })()
        )
      )
    }
  },

  db_copyParameters: function (args) {
    'use strict'
    let noError = true
    let params, i

    if (args.name === null) {
      INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1019))
      noError = false
    }
    if (args.conditions === null) {
      INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1020))
      noError = false
    }
    if (!noError) {
      return false
    }

    params = 'access=copy&name=' + encodeURIComponent(args.name)
    for (i = 0; i < args.conditions.length; i++) {
      params += '&condition' + i + 'field=' + encodeURIComponent(args.conditions[i].field)
      params += '&condition' + i + 'operator=' + encodeURIComponent(args.conditions[i].operator)
      params += '&condition' + i + 'value=' + encodeURIComponent(args.conditions[i].value)
    }
    if (args.associated) {
      for (i = 0; i < args.associated.length; i++) {
        params += '&assoc' + i + '=' + encodeURIComponent(args.associated[i].name)
        params += '&asfield' + i + '=' + encodeURIComponent(args.associated[i].field)
        params += '&asvalue' + i + '=' + encodeURIComponent(args.associated[i].value)
      }
    }
    return params
  },

  createExceptionFunc: function (errMessageNumber, AuthProc) {
    'use strict'
    let errorNumCapt = errMessageNumber
    return function (myRequest) {
      if (INTERMediatorOnPage.requireAuthentication) {
        if (!INTERMediatorOnPage.isComplementAuthData()) {
          INTERMediatorOnPage.clearCredentials()
          INTERMediatorOnPage.authenticating(AuthProc)
        }
      } else {
        INTERMediatorLog.setErrorMessage('Communication Error',
          INTERMediatorLib.getInsertedString(
            INTERMediatorOnPage.getMessages()[errorNumCapt],
            ['Communication Error', myRequest.responseText]))
      }
    }
  },

  unregister: async function (entityPkInfo) {
    'use strict'
    let params, p = null
    if (INTERMediatorOnPage.activateClientService) {
      params = 'access=unregister'
      if (entityPkInfo) {
        params += '&pks=' + encodeURIComponent(JSON.stringify(entityPkInfo))
      }
      p = await INTERMediator_DBAdapter.server_access_async(params, 1018, 1016, null, null, null)
    }
    return p
  }
}

// @@IM@@IgnoringRestOfFile
module.exports = INTERMediator_DBAdapter
