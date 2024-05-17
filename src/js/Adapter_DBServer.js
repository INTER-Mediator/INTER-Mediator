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
 * Usually you don't have to instantiate this class with new operator.
 * @constructor
 */
const INTERMediator_DBAdapter = {

  eliminateDuplicatedConditions: false, /*
   If this property is set to true, the dupilicate conditions in query is going to eliminate before
   submitting to the server. This behavior is required in some case of FileMaker Server, but it can resolve
   by using the id=>-recid in a context. 2015-4-19 Masayuki Nii.
   */
  debugMessage: false,

  generate_authParams: function () {
    'use strict'
    let authParams = ''

    const user = !!INTERMediatorOnPage.authUser() ? INTERMediatorOnPage.authUser() : (INTERMediatorOnPage.authedUser ?? null)
    const clientId = !!INTERMediatorOnPage.clientId() ? INTERMediatorOnPage.clientId() : INTERMediatorOnPage.authedClientId

    if (user) {
      authParams = '&clientid=' + encodeURIComponent(clientId)
      authParams += '&authuser=' + encodeURIComponent(user)
      if ((INTERMediatorOnPage.authHashedPassword() || INTERMediatorOnPage.authHashedPassword2m()
        || INTERMediatorOnPage.authHashedPassword2()) && INTERMediatorOnPage.authChallenge) {
        if (INTERMediatorOnPage.passwordHash < 1.1 && INTERMediatorOnPage.authHashedPassword()) {
          authParams += '&response=' + encodeURIComponent(
            INTERMediatorLib.generateHexHash(INTERMediatorOnPage.authHashedPassword(), INTERMediatorOnPage.authChallenge))
        }
        if (INTERMediatorOnPage.passwordHash < 1.6 && INTERMediatorOnPage.authHashedPassword2m()) {
          authParams += '&response2m=' + encodeURIComponent(
            INTERMediatorLib.generateHexHash(INTERMediatorOnPage.authHashedPassword2m(), INTERMediatorOnPage.authChallenge))
        }
        if (INTERMediatorOnPage.passwordHash < 2.1 && INTERMediatorOnPage.authHashedPassword2()) {
          authParams += '&response2=' + encodeURIComponent(
            INTERMediatorLib.generateHexHash(INTERMediatorOnPage.authHashedPassword2(), INTERMediatorOnPage.authChallenge))
        }
        if (INTERMediator_DBAdapter.debugMessage) {
          INTERMediatorLog.setDebugMessage('generate_authParams/authHashedPassword=' + INTERMediatorOnPage.authHashedPassword())
          INTERMediatorLog.setDebugMessage('generate_authParams/authChallenge=' + INTERMediatorOnPage.authChallenge)
        }
      } else {
        authParams += '&response=dummy'
      }
    }

    authParams += '&notifyid='
    authParams += encodeURIComponent(INTERMediatorOnPage.clientNotificationIdentifier())
    authParams += "&tzoffset=" + (new Date()).getTimezoneOffset()
    return authParams
  },

  store_challenge: function (challenge, isChallenge) {
    'use strict'
    if (challenge) {
      const len = 48
      INTERMediatorOnPage.authChallenge = challenge.substr(0, len)
      INTERMediatorOnPage.authUserHexSalt = challenge.substr(len, len + 8)
      INTERMediatorOnPage.authUserSalt = String.fromCharCode(parseInt(challenge.substr(len, 2), 16), parseInt(challenge.substr(len + 2, 2), 16), parseInt(challenge.substr(len + 4, 2), 16), parseInt(challenge.substr(len + 6, 2), 16))
      if (INTERMediator_DBAdapter.debugMessage) {
        INTERMediatorLog.setDebugMessage('store_challenge/authChallenge=' + INTERMediatorOnPage.authChallenge)
        INTERMediatorLog.setDebugMessage('store_challenge/authUserHexSalt=' + INTERMediatorOnPage.authUserHexSalt)
        INTERMediatorLog.setDebugMessage('store_challenge/authUserSalt=' + INTERMediatorOnPage.authUserSalt)
      }
    }
    if (!isChallenge && INTERMediatorOnPage.authStoring === 'credential') {
      INTERMediatorOnPage.authChallenge = ''
    }
  },

  logging_comAction: function (debugMessageNumber, appPath, accessURL, authParams) {
    'use strict'
    INTERMediatorLog.setDebugMessage(INTERMediatorOnPage.getMessages()[debugMessageNumber] + 'Accessing:' + decodeURI(appPath) + ', Parameters:' + decodeURI(accessURL + authParams))
  },

  logging_comResult: function (responseText) {
    let responseTextTrancated
    if (INTERMediatorLog.debugMode > 1) {
      const jsonObject = JSON.parse(responseText)
      const resultCount = jsonObject.resultCount ?? 0
      const dbresult = jsonObject.dbresult ?? null
      const requireAuth = jsonObject.requireAuth ?? false
      const challenge = jsonObject.challenge ?? null
      const clientid = jsonObject.clientid ?? null
      const newRecordKeyValue = jsonObject.newRecordKeyValue ?? ''
      const changePasswordResult = jsonObject.changePasswordResult ?? null
      const authUser = jsonObject.authUser ?? null

      if (responseText.length > 1000) {
        responseTextTrancated = responseText.substring(0, 1000) + ' ...[trancated]'
      } else {
        responseTextTrancated = responseText
      }
      INTERMediatorLog.setDebugMessage('responseText=' + responseTextTrancated)
      INTERMediatorLog.setDebugMessage('Return: resultCount=' + resultCount
        + ', dbresult=' + INTERMediatorLib.objectToString(dbresult) + IMLib.nl_char
        + 'Return: requireAuth=' + requireAuth + ', challenge=' + challenge + ', clientid=' + clientid + IMLib.nl_char
        + 'Return: newRecordKeyValue=' + newRecordKeyValue + ', changePasswordResult=' + changePasswordResult
        + ', authUser=' + authUser)
    }
  },

  /* No return values */
  server_access_async: async function (accessURL, debugMessageNumber, errorMessageNumber,
                                       successProc = null, failedProc = null, authAgainProc = null,
                                       fData = null) {
    const appPath = INTERMediatorOnPage.getEntryPath()
    const authParams = INTERMediator_DBAdapter.generate_authParams()
    INTERMediator_DBAdapter.logging_comAction(debugMessageNumber, appPath, accessURL, authParams)
    const headers = new Headers()
    headers.append('X-Requested-With', 'fetch')
    headers.append('X-From', location.href)
    if (INTERMediatorOnPage.httpuser && INTERMediatorOnPage.httppasswd) {
      headers.append('Authorization', btoa(`${INTERMediatorOnPage.httpuser}:${INTERMediatorOnPage.httppasswd}`))
    }
    if (fData) {
      for (const param of authParams.split('&')) {
        const comp = param.split('=')
        if (comp.length === 2 && comp[0].length > 0) {
          fData.append(comp[0], decodeURIComponent(comp[1]))
        }
      }
    } else {
      headers.append("Content-Type", 'application/x-www-form-urlencoded; charset=UTF-8')
    }
    const initParam = {
      method: "POST",
      headers: headers,
      mode: "same-origin",
      credentials: "include",
      cache: "no-cache",
      body: fData ? fData : (accessURL + authParams)
    }
    await fetch(new Request(appPath, initParam)).then((response) => {
      if (!response.ok) {
        throw 'Communication Error'
      }
      return response.text()
    }).then((responseText) => {
      const jsonObject = JSON.parse(responseText)
      INTERMediatorLog.setErrorMessages(jsonObject.errorMessages, true)
      INTERMediatorLog.setDebugMessages(jsonObject.debugMessages)
      INTERMediatorLog.setWarningMessages(jsonObject.warningMessages)
      if (jsonObject.errorMessages && jsonObject.errorMessages.length > 0) {
        throw 'Communication Error'
      }

      INTERMediator_DBAdapter.logging_comResult(responseText)
      INTERMediator_DBAdapter.store_challenge(jsonObject.challenge ?? null,
        accessURL.match(/access=challenge/)
        || (INTERMediatorOnPage.isRequired2FA && (accessURL.match(/access=credential/)
          || accessURL.match(/access=authenticated/))))
      if (jsonObject.clientid) {
        INTERMediatorOnPage.clientId(jsonObject.clientid)
        INTERMediatorOnPage.authedClientId = jsonObject.clientid ?? null
      }
      if (INTERMediatorOnPage.isSAML) {
        if (jsonObject.samluser) {
          INTERMediatorOnPage.authUser(jsonObject.samluser)
          if (INTERMediatorOnPage.authStoring !== 'credential') {
            INTERMediatorOnPage.authHashedPassword(jsonObject.temppw)
            INTERMediatorOnPage.authHashedPassword2m(jsonObject.temppw)
            INTERMediatorOnPage.authHashedPassword2(jsonObject.temppw)
          }
        }
        if (jsonObject.samlloginurl) {
          INTERMediatorOnPage.loginURL = jsonObject.samlloginurl
        }
        if (jsonObject.samllogouturl) {
          INTERMediatorOnPage.logoutURL = jsonObject.samllogouturl
        }
        if (jsonObject.samladditionalfail) {
          IMLibQueue.setTask((complete) => {
            complete()
            if (confirm(INTERMediatorLib.getInsertedStringFromErrorNumber(2027))) {
              location.href = jsonObject.samladditionalfail
            }
          }, false, true)
        }
      }
      if (accessURL.indexOf('access=changepassword&newpass=') !== 0
        && accessURL.indexOf('access=authenticated') !== 0
        && accessURL.indexOf('access=challenge') !== 0) {
        if (jsonObject.requireAuth) {
          INTERMediatorLog.setDebugMessage('Authentication Required, user/password panel should be show.')
          INTERMediatorOnPage.clearCredentials()
          if (INTERMediatorOnPage.isSAML && !INTERMediatorOnPage.samlWithBuiltInAuth) {
            if (!jsonObject.samladditionalfail) {
              location.href = INTERMediatorOnPage.loginURL
            }
          }
          if (INTERMediatorOnPage.updatingWithSynchronize > 0 || INTERMediator.partialConstructing) {
            location.reload() // It might stop here.
          }
          if (authAgainProc) {
            authAgainProc()
          } else if (!accessURL.match(/access=challenge/)) {
            INTERMediator.constructMain()
          }
          return
        }
        if (!accessURL.match(/access=challenge/)) {
          INTERMediatorOnPage.authCount = 0
        }
      }
      INTERMediatorOnPage.authedUser = jsonObject.authUser ?? null
      INTERMediatorOnPage.succeedCredential = !jsonObject.requireAuth ?? false
      if (successProc) {
        successProc({
          dbresult: jsonObject.dbresult ?? null,
          resultCount: jsonObject.resultCount ?? 0,
          totalCount: jsonObject.totalCount ?? null,
          newRecordKeyValue: jsonObject.newRecordKeyValue ?? '',
          newPasswordResult: jsonObject.changePasswordResult ?? null,
          registeredId: jsonObject.registeredid ?? '',
          nullAcceptable: jsonObject.usenull,
          succeed_2FA: jsonObject.succeed_2FA
        })
      }
      INTERMediatorLog.flushMessage()
    }).catch(reason => {
      INTERMediatorLog.setErrorMessage('Communication Error: ' + reason)
      if (failedProc) {
        failedProc(new Error('_im_communication_error_'))
      }
      INTERMediatorLog.flushMessage()
    })
  },

  changePassword: async function (username, oldpassword, newpassword, doSucceed, doFail) {
    'use strict'

    if (!username || !oldpassword) {
      throw new Error('_im_changepw_noparams')
    }
    INTERMediatorOnPage.authUser(username)
    if (username !== '' && // No usename and no challenge, get a challenge.
      (INTERMediatorOnPage.authChallenge === null || INTERMediatorOnPage.authChallenge.length < 48)) {
      INTERMediatorOnPage.storedHashedPasswordAllClear('need-hash-pls') // Dummy Hash for getting a challenge
      await INTERMediator_DBAdapter.getChallenge()
    }
    INTERMediatorOnPage.storedHashedPasswordAllClear('')
    if (INTERMediatorOnPage.passwordHash < 1.1) {
      INTERMediatorOnPage.authHashedPassword(
        INTERMediatorLib.generatePasswrdHashV1(oldpassword, INTERMediatorOnPage.authUserSalt))
    }
    if (INTERMediatorOnPage.passwordHash < 1.6) {
      INTERMediatorOnPage.authHashedPassword2m(
        INTERMediatorLib.generatePasswrdHashV2m(oldpassword, INTERMediatorOnPage.authUserSalt))
    }
    if (INTERMediatorOnPage.passwordHash < 2.1) {
      INTERMediatorOnPage.authHashedPassword2(
        INTERMediatorLib.generatePasswrdHashV2(oldpassword, INTERMediatorOnPage.authUserSalt))
    }
    const params = 'access=changepassword&newpass=' + INTERMediatorLib.generatePasswordHash(newpassword)
    return INTERMediator_DBAdapter.server_access_async(params, 1029, 1030, (result) => {
      if (result.newPasswordResult) {
        if (INTERMediatorOnPage.passwordHash < 1.1) {
          INTERMediatorOnPage.authHashedPassword(
            INTERMediatorLib.generatePasswrdHashV1(newpassword, INTERMediatorOnPage.authUserSalt))
        }
        if (INTERMediatorOnPage.passwordHash < 2.1) {
          INTERMediatorOnPage.authHashedPassword2(
            INTERMediatorLib.generatePasswrdHashV2(newpassword, INTERMediatorOnPage.authUserSalt))
        }
        if (doSucceed) {
          doSucceed()
        }
      } else {
        if (doFail) {
          doFail()
        }
      }
    }, (er) => {
      if (doFail) {
        doFail()
      }
    })
  },

  getChallenge: async function () {
    'use strict'
    return INTERMediator_DBAdapter.server_access_async('access=challenge', 1027, 1028,
      null, null, null)
  },

  getCredential: async function () {
    'use strict'
    INTERMediatorOnPage.succeedCredential = false
    return INTERMediator_DBAdapter.server_access_async('access=credential', 1048, 1049,
      function (result) {
        //INTERMediatorOnPage.succeedCredential = !result.requireAuth
        if (!INTERMediatorOnPage.isRequired2FA) {
          INTERMediatorOnPage.clearCredentials()
        }
      }, function () {
        INTERMediatorOnPage.clearCredentials()
      }, INTERMediator_DBAdapter.createExceptionFunc(1016, function () {
        INTERMediator.constructMain()
      }))
  },

  getCredential2FA: async function () {
    'use strict'
    INTERMediatorOnPage.succeedCredential = false
    return INTERMediator_DBAdapter.server_access_async('access=authenticated', 1057, 1058,
      function (result) {
        INTERMediatorOnPage.succeedCredential = result.succeed_2FA
        if (result.succeed_2FA) {
          INTERMediatorOnPage.clearCredentials()
        }
      }, function () {
        INTERMediatorOnPage.clearCredentials()
      }, INTERMediator_DBAdapter.createExceptionFunc(1016, function () {
        INTERMediator.constructMain()
      }))
  },

  uploadFile: function (parameters, uploadingFile, doItOnFinish, exceptionProc) {
    'use strict'
    let myRequest = null
    // let result = this.server_access('access=uploadfile' + parameters, 1031, 1032, uploadingFile)
    const appPath = INTERMediatorOnPage.getEntryPath()
    const authParams = INTERMediator_DBAdapter.generate_authParams()
    const accessURL = 'access=uploadfile' + parameters
    INTERMediator_DBAdapter.logging_comAction(1031, appPath, accessURL, authParams)

    const headers = new Headers()
    headers.append('X-Requested-With', 'fetch')
    headers.append('X-From', location.href)
    if (INTERMediatorOnPage.httpuser && INTERMediatorOnPage.httppasswd) {
      headers.append('Authorization', btoa(`${INTERMediatorOnPage.httpuser}:${INTERMediatorOnPage.httppasswd}`))
    }
    if (fData) {
      for (const param of authParams.split('&')) {
        const comp = param.split('=')
        if (comp.length === 2 && comp[0].length > 0) {
          fData.append(comp[0], decodeURIComponent(comp[1]))
        }
      }
    } else {
      headers.append("Content-Type", 'application/x-www-form-urlencoded; charset=UTF-8')
    }
    let params = (accessURL + authParams).split('&')
    let fd = new FormData()
    for (let i = 0; i < params.length; i++) {
      let valueset = params[i].split('=')
      fd.append(valueset[0], decodeURIComponent(valueset[1]))
    }
    fd.append('_im_uploadfile', uploadingFile.content)
    const initParam = {
      method: "POST",
      headers: headers,
      mode: "same-origin",
      credentials: "include",
      cache: "no-cache",
      body: fd
    }

    fetch(new Request(appPath, initParam)).then((response) => {
      if (!response.ok) {
        throw 'Communication Error'
      }
      return response.text()
    }).then((responseText) => {
      INTERMediator_DBAdapter.uploadFileAfterSucceed(responseText, doItOnFinish, exceptionProc, false)
    })
  },

  uploadFileAfterSucceed: function (responseText, doItOnFinish, exceptionProc, isErrorDialog) {
    'use strict'
    let jsonObject
    try {
      jsonObject = JSON.parse(responseText)
    } catch (ex) {
      INTERMediatorLog.setErrorMessage(ex, INTERMediatorLib.getInsertedString(
        INTERMediatorOnPage.getMessages()[1032], ['']))
      INTERMediatorLog.flushMessage()
      if (exceptionProc) {
        exceptionProc()
      }
      return false
    }
    INTERMediator_DBAdapter.logging_comResult(responseText)
    INTERMediatorLog.setErrorMessages(jsonObject.errorMessages, !isErrorDialog)
    INTERMediatorLog.setDebugMessages(jsonObject.debugMessages)
    INTERMediatorLog.setWarningMessages(jsonObject.warningMessages)
    INTERMediator_DBAdapter.store_challenge(jsonObject.challenge ?? null, false)
    INTERMediatorOnPage.clientId(jsonObject.clientid ?? '')
    if (jsonObject.requireAuth) {
      INTERMediatorLog.setDebugMessage('Authentication Required, user/password panel should be show.')
      INTERMediatorOnPage.clearCredentials()
      if (exceptionProc) {
        exceptionProc()
      }
      return false
    }
    INTERMediatorLog.flushMessage()
    INTERMediatorOnPage.authCount = 0
    if (doItOnFinish) {
      doItOnFinish(jsonObject.dbresult ?? null)
    }
    return true
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
      this.server_access_async(params, 1012, 1004, (() => {
        let contextDef
        let contextName = args.name
        let recordsNumber = Number(args.records)
        let resolveCapt = resolve
        return (result) => {
          result.count = result.dbresult ? Object.keys(result.dbresult).length : 0
          contextDef = IMLibContextPool.getContextDef(contextName)
          if (!contextDef.relation && args.paging && Boolean(args.paging) === true) {
            INTERMediator.pagedAllCount = parseInt(result.resultCount, 10)
            if (result.totalCount) {
              INTERMediator.totalRecordCount = parseInt(result.totalCount, 10)
            }
          }
          if ((args.paging !== null) && (Boolean(args.paging) === true)) {
            INTERMediator.pagination = true
            if (!(recordsNumber >= Number(INTERMediator.pagedSize) && Number(INTERMediator.pagedSize) > 0)) {
              INTERMediator.pagedSize = parseInt(recordsNumber, 10)
            }
          }
          successProc ? successProc(result) : false
          resolveCapt(result)
        }
      })(), failedProc, INTERMediator_DBAdapter.createExceptionFunc(1016, (function () {
        return function () {
          if (INTERMediator.currentContext === true) {
            location.reload()
          } else {
            INTERMediator.constructMain(INTERMediator.currentContext, INTERMediator.currentRecordset)
          }
        }
      })()))
    }).catch((err) => {
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
    let index, params, counter, extCount, extCountSort, conditions, conditionSign
    let recordLimit = 10000000
    if (args.records === null) {
      params = 'access=read&name=' + encodeURIComponent(args.name)
    } else {
      if (parseInt(args.records, 10) === 0 && (INTERMediatorOnPage.dbClassName.match(/FileMaker_FX/) || INTERMediatorOnPage.dbClassName.match(/FileMaker_DataAPI/))) {
        params = 'access=describe&name=' + encodeURIComponent(args.name)
      } else {
        params = 'access=read&name=' + encodeURIComponent(args.name)
      }
      if (Boolean(args.uselimit) === true && parseInt(args.records, 10) >= INTERMediator.pagedSize && parseInt(INTERMediator.pagedSize, 10) > 0) {
        recordLimit = INTERMediator.pagedSize
      } else {
        recordLimit = args.records
      }
      if (INTERMediator.recordLimit && INTERMediator.recordLimit[args.name]) {
        recordLimit = parseInt(INTERMediator.recordLimit[args.name])
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
          params += '&foreign' + counter + 'field=' + encodeURIComponent(index)
          params += '&foreign' + counter + 'value=' + encodeURIComponent(args.parentkeyvalue[index])
          counter++
        }
      }
    }
    if (args.parentcontext) {
      params += '&parent=' + encodeURIComponent(args.parentcontext)
    }
    if (args.useoffset && INTERMediator.startFrom !== null) {
      params += '&start=' + parseInt(INTERMediator.startFrom)
    } else if (INTERMediator.recordStart && INTERMediator.recordStart[args.name]) {
      params += '&start=' + parseInt(INTERMediator.recordStart[args.name])
    }

    extCount = 0
    extCountSort = 0;
    conditions = []
    while (args.conditions && args.conditions[extCount]) {
      conditionSign = args.conditions[extCount].field + '#' + args.conditions[extCount].operator + '#' + args.conditions[extCount].value
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
    params += '&records=' + encodeURIComponent(recordLimit);

    [params, conditions, extCount] = INTERMediator_DBAdapter.parseAdditionalCriteria(params, INTERMediator.additionalCondition[args.name], conditions, extCount);
    [params, extCountSort] = INTERMediator_DBAdapter.parseAdditionalSortParameter(params, INTERMediator.additionalSortKey[args.name], extCountSort);
    params = INTERMediator_DBAdapter.parseLocalContext(args, params, extCount, extCountSort)[0]

    return params
  },

  // Private method for the db_queryParameters method
  parseAdditionalCriteria: function (params, criteriaObject, conditions, extCount) {
    const removeIndices = []
    if (criteriaObject) {
      if (criteriaObject.field) {
        criteriaObject = [criteriaObject]
      }
      for (let index = 0; index < criteriaObject.length; index++) {
        if (criteriaObject[index] && criteriaObject[index].field) {
          const conditionSign = criteriaObject[index].field + '#' + ((typeof (criteriaObject[index].operator) !== 'undefined') ? criteriaObject[index].operator : '') + '#' + ((typeof (criteriaObject[index].value) !== 'undefined') ? criteriaObject[index].value : '')
          if (!INTERMediator_DBAdapter.eliminateDuplicatedConditions || conditions.indexOf(conditionSign) < 0) {
            params += '&condition' + extCount
            params += 'field=' + encodeURIComponent(criteriaObject[index].field)
            if (typeof (criteriaObject[index].operator) !== 'undefined') {
              params += '&condition' + extCount
              params += 'operator=' + encodeURIComponent(criteriaObject[index].operator)
            }
            if (typeof (criteriaObject[index].value) !== 'undefined') {
              params += '&condition' + extCount
              let value = criteriaObject[index].value
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
        if (criteriaObject[index] && criteriaObject[index].onetime) {
          removeIndices.push = index
        }
      }
      if (removeIndices.length > 0) {
        const modifyConditions = []
        for (let index = 0; index < criteriaObject.length; index++) {
          if (!(index in removeIndices)) {
            modifyConditions.push(criteriaObject[index])
          }
        }
        INTERMediator.additionalCondition[args.name] = modifyConditions
        IMLibLocalContext.archive()
      }
    }
    return [params, conditions, extCount]
  },

  // Private method for the db_queryParameters method
  parseAdditionalSortParameter: function (params, sortkeyObject, extCountSort) {
    if (sortkeyObject) {
      if (sortkeyObject.field) {
        sortkeyObject = [sortkeyObject]
      }
      for (let index = 0; index < sortkeyObject.length; index++) {
        params += '&sortkey' + extCountSort
        params += 'field=' + encodeURIComponent(sortkeyObject[index].field)
        params += '&sortkey' + extCountSort
        params += 'direction=' + encodeURIComponent(sortkeyObject[index].direction)
        extCountSort++
      }
    }
    return [params, extCountSort]
  },

  // Private method for the db_queryParameters method
  parseLocalContext: function (args, params, extCount, extCountSort) {
    const orderFields = {}
    if (INTERMediator.alwaysAddOperationExchange) {
      INTERMediator.lcConditionsOP1AND = false
      INTERMediator.lcConditionsOP2AND = true
    }
    let isFirstItem = true
    for (const key in IMLibLocalContext.store) {
      const value = String(IMLibLocalContext.store[key])
      const keyParams = key.split(':')
      if (keyParams && keyParams.length > 1 && keyParams[1].trim() === args.name && value.length > 0) {
        if (keyParams[0].trim() === 'condition' && keyParams.length >= 4) {
          if (isFirstItem) {
            params += '&condition' + extCount + 'field=__operation__'
            params += '&condition' + extCount + 'operator=block/' + (INTERMediator.lcConditionsOP1AND ? 'T' : 'F') + '/' + (INTERMediator.lcConditionsOP2AND ? 'T' : 'F') + '/' + ((INTERMediator.lcConditionsOP3AND && INTERMediator.lcConditionsOP3AND.toString().toUpperCase() === 'AND') ? 'AND' : (INTERMediator.lcConditionsOP3AND ? 'T' : 'F'))
            extCount++
            isFirstItem = false
          }
          params += '&condition' + extCount + 'field=' + encodeURIComponent(keyParams[2].trim().replace(';;', '::').trim())
          params += '&condition' + extCount + 'operator=' + encodeURIComponent(keyParams[3].trim())
          params += '&condition' + extCount + 'value=' + encodeURIComponent(value)
          extCount++
        } else if (keyParams[0].trim() === 'valueofaddorder' && keyParams.length >= 4) {
          orderFields[parseInt(value)] = [keyParams[2].trim(), keyParams[3].trim()]
        }
      }
    }
    const orderedKeys = Object.keys(orderFields)
    for (let i = 0; i < orderedKeys.length; i++) {
      params += '&sortkey' + extCountSort + 'field=' + encodeURIComponent(orderFields[orderedKeys[i]][0])
      params += '&sortkey' + extCountSort + 'direction=' + encodeURIComponent(orderFields[orderedKeys[i]][1])
      extCountSort++
    }
    return [params, extCount, extCountSort];
  }, /*
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
      INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1045, [args.name]))
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
    if (INTERMediator.additionalFieldValueOnUpdate && INTERMediator.additionalFieldValueOnUpdate[args.name]) {
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
      INTERMediator_DBAdapter.server_access_async(params, 1013, 1014, successProc, failedProc, INTERMediator_DBAdapter.createExceptionFunc(1016, (function () {
        return function () {
          if (INTERMediator.currentContext === true) {
            location.reload()
          } else {
            INTERMediator.constructMain(INTERMediator.currentContext, INTERMediator.currentRecordset)
          }
        }
      })()))
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
      INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1045, [args.name]))
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
    if (INTERMediator.additionalFieldValueOnDelete && INTERMediator.additionalFieldValueOnDelete[args.name]) {
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
      INTERMediator_DBAdapter.server_access_async(params, 1017, 1015, successProc, failedProc, INTERMediator_DBAdapter.createExceptionFunc(1016, (function () {
        return function () {
          if (INTERMediator.currentContext === true) {
            location.reload()
          } else {
            INTERMediator.constructMain(INTERMediator.currentContext, INTERMediator.currentRecordset)
          }
        }
      })()))
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
      if (def.value && def.value.file && def.value.kind && def.value.kind === 'attached') {
        isFormData = true
      }
    }
    paramsStr = INTERMediator_DBAdapter.db_createParameters(args)
    if (isFormData) {
      paramsFD = INTERMediator_DBAdapter.db_createParametersAsForm(args)
    }
    if (paramsStr) {
      await INTERMediatorOnPage.retrieveAuthInfo()
      INTERMediator_DBAdapter.server_access_async(paramsStr, 1018, 1016, successProc, failedProc, INTERMediator_DBAdapter.createExceptionFunc(1016, (function () {
        return function () {
          if (INTERMediator.currentContext === true) {
            location.reload()
          } else {
            INTERMediator.constructMain(INTERMediator.currentContext, INTERMediator.currentRecordset)
          }
        }
      })()), paramsFD)
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
      INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1045, [args.name]))
      return false
    }
    params = 'access=create&name=' + encodeURIComponent(args.name)
    counter = 0
    if (INTERMediator.additionalFieldValueOnNewRecord && INTERMediator.additionalFieldValueOnNewRecord[args.name]) {
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
    if (INTERMediator.additionalFieldValueOnNewRecord && INTERMediator.additionalFieldValueOnNewRecord[args.name]) {
      addedObject = INTERMediator.additionalFieldValueOnNewRecord[args.name]
      if (addedObject.field) {
        addedObject = [addedObject]
      }
      for (index in addedObject) {
        if (addedObject.hasOwnProperty(index)) {
          let oneDefinition = addedObject[index]
          if (oneDefinition.value && oneDefinition.value.file && oneDefinition.value.kind && oneDefinition.value.kind === 'attached') {
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
      if (args.dataset[i].value && args.dataset[i].value.file && args.dataset[i].value.kind && args.dataset[i].value.kind === 'attached') {
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
      INTERMediator_DBAdapter.server_access_async(params, 1017, 1015, successProc, failedProc, INTERMediator_DBAdapter.createExceptionFunc(1016, (function () {
        return function () {
          if (INTERMediator.currentContext === true) {
            location.reload()
          } else {
            INTERMediator.constructMain(INTERMediator.currentContext, INTERMediator.currentRecordset)
          }
        }
      })()))
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
        INTERMediatorLog.setErrorMessage('Communication Error', INTERMediatorLib.getInsertedString(INTERMediatorOnPage.getMessages()[errorNumCapt], ['Communication Error', myRequest.responseText]))
      }
    }
  },

  unregister: async function (entityPkInfo = null) {
    if (INTERMediatorOnPage.activateClientService) {
      let params = 'access=unregister'
      if (entityPkInfo) {
        params += '&pks=' + encodeURIComponent(JSON.stringify(entityPkInfo))
      }
      await INTERMediatorOnPage.retrieveAuthInfo()
      await INTERMediator_DBAdapter.server_access_async(params, 1053, 1054, null, null, null)
    }
  },

  mentenance: async function () {
    let params = 'access=maintenance'
    await INTERMediatorOnPage.retrieveAuthInfo()
    await INTERMediator_DBAdapter.server_access_async(params, 1056, 1054, null, null, null)
  }
}

// @@IM@@IgnoringRestOfFile
module.exports = INTERMediator_DBAdapter
const INTERMediator = require('../../src/js/INTER-Mediator')
const IMLibLocalContext = require('../../src/js/INTER-Mediator-LocalContext')
const INTERMediatorLib = require("../../src/js/INTER-Mediator-Lib")
