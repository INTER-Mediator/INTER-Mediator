/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

/*==================================================
 Database Access Object for Server-based Database
 ==================================================*/

// JSHint support
/* global IMLibContextPool, INTERMediator, INTERMediatorOnPage, IMLibMouseEventDispatch, IMLibLocalContext,
 IMLibChangeEventDispatch, INTERMediatorLib, IMLibQueue, IMLibCalc, IMLibPageNavigation, INTERMediatorLog,
 IMLibEventResponder, IMLibElement, Parser, IMLib, jsSHA, SHA1 */

/**
 * @fileoverview INTERMediator_DBAdapter class is defined here.
 */
/**
 *
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
var INTERMediator_DBAdapter = {

    eliminateDuplicatedConditions: false,
    /*
     If this property is set to true, the dupilicate conditions in query is going to eliminate before
     submitting to the server. This behavior is required in some case of FileMaker Server, but it can resolve
     by using the id=>-recid in a context. 2015-4-19 Masayuki Nii.
     */
    debugMessage: false,

    generate_authParams: function () {
        'use strict';
        var authParams = '', shaObj, hmacValue;
        if (INTERMediatorOnPage.authUser.length > 0) {
            authParams = '&clientid=' + encodeURIComponent(INTERMediatorOnPage.clientId);
            authParams += '&authuser=' + encodeURIComponent(INTERMediatorOnPage.authUser);
            if (INTERMediatorOnPage.isNativeAuth || INTERMediatorOnPage.isLDAP) {
                if (INTERMediatorOnPage.authCryptedPassword && INTERMediatorOnPage.authChallenge) {
                    authParams += '&cresponse=' + encodeURIComponent(
                            INTERMediatorOnPage.publickey.biEncryptedString(INTERMediatorOnPage.authCryptedPassword +
                                IMLib.nl_char + INTERMediatorOnPage.authChallenge));
                    if (INTERMediator_DBAdapter.debugMessage) {
                        INTERMediatorLog.setDebugMessage('generate_authParams/authCryptedPassword=' +
                            INTERMediatorOnPage.authCryptedPassword);
                        INTERMediatorLog.setDebugMessage('generate_authParams/authChallenge=' +
                            INTERMediatorOnPage.authChallenge);
                    }
                } else {
                    authParams += '&cresponse=dummy';
                }
            }
            if (INTERMediatorOnPage.authHashedPassword && INTERMediatorOnPage.authChallenge) {
                shaObj = new jsSHA(INTERMediatorOnPage.authHashedPassword, 'ASCII');
                hmacValue = shaObj.getHMAC(INTERMediatorOnPage.authChallenge, 'ASCII', 'SHA-256', 'HEX');
                authParams += '&response=' + encodeURIComponent(hmacValue);
                if (INTERMediator_DBAdapter.debugMessage) {
                    INTERMediatorLog.setDebugMessage('generate_authParams/authHashedPassword=' +
                        INTERMediatorOnPage.authHashedPassword);
                    INTERMediatorLog.setDebugMessage('generate_authParams/authChallenge=' +
                        INTERMediatorOnPage.authChallenge);
                }
            } else {
                authParams += '&response=dummy';
            }
        }

        authParams += '&notifyid=';
        authParams += encodeURIComponent(INTERMediatorOnPage.clientNotificationIdentifier());
        authParams += ('&pusher=' + (INTERMediator.pusherAvailable ? 'yes' : ''));
        return authParams;
    },

    store_challenge: function (challenge) {
        'use strict';
        if (challenge !== null) {
            INTERMediatorOnPage.authChallenge = challenge.substr(0, 24);
            INTERMediatorOnPage.authUserHexSalt = challenge.substr(24, 32);
            INTERMediatorOnPage.authUserSalt = String.fromCharCode(
                parseInt(challenge.substr(24, 2), 16),
                parseInt(challenge.substr(26, 2), 16),
                parseInt(challenge.substr(28, 2), 16),
                parseInt(challenge.substr(30, 2), 16));
            if (INTERMediator_DBAdapter.debugMessage) {
                INTERMediatorLog.setDebugMessage('store_challenge/authChallenge=' + INTERMediatorOnPage.authChallenge);
                INTERMediatorLog.setDebugMessage('store_challenge/authUserHexSalt=' + INTERMediatorOnPage.authUserHexSalt);
                INTERMediatorLog.setDebugMessage('store_challenge/authUserSalt=' + INTERMediatorOnPage.authUserSalt);
            }
        }
    },

    logging_comAction: function (debugMessageNumber, appPath, accessURL, authParams) {
        'use strict';
        INTERMediatorLog.setDebugMessage(
            INTERMediatorOnPage.getMessages()[debugMessageNumber] +
            'Accessing:' + decodeURI(appPath) + ', Parameters:' + decodeURI(accessURL + authParams));
    },

    logging_comResult: function (myRequest, resultCount, dbresult, requireAuth, challenge, clientid, newRecordKeyValue, changePasswordResult, mediatoken) {
        'use strict';
        var responseTextTrancated;
        if (INTERMediatorLog.debugMode > 1) {
            if (myRequest.responseText.length > 1000) {
                responseTextTrancated = myRequest.responseText.substr(0, 1000) + ' ...[trancated]';
            } else {
                responseTextTrancated = myRequest.responseText;
            }
            INTERMediatorLog.setDebugMessage('myRequest.responseText=' + responseTextTrancated);
            INTERMediatorLog.setDebugMessage('Return: resultCount=' + resultCount +
                ', dbresult=' + INTERMediatorLib.objectToString(dbresult) + IMLib.nl_char +
                'Return: requireAuth=' + requireAuth +
                ', challenge=' + challenge + ', clientid=' + clientid + IMLib.nl_char +
                'Return: newRecordKeyValue=' + newRecordKeyValue +
                ', changePasswordResult=' + changePasswordResult + ', mediatoken=' + mediatoken
            );
        }
    },

    server_access: function (accessURL, debugMessageNumber, errorMessageNumber) {
        INTERMediatorLog.setErrorMessage('INTERMediator_DBAdapter.server_access method was discontinued in Ver.6');
        //     'use strict';
        //     var newRecordKeyValue = '', dbresult = '', resultCount = 0, totalCount = null, challenge = null,
        //         clientid = null, requireAuth = false, myRequest = null, changePasswordResult = null,
        //         mediatoken = null, appPath, authParams, jsonObject, i, notifySupport = false, useNull = false,
        //         registeredID = '';
        //     appPath = INTERMediatorOnPage.getEntryPath();
        //     authParams = INTERMediator_DBAdapter.generate_authParams();
        //     INTERMediator_DBAdapter.logging_comAction(debugMessageNumber, appPath, accessURL, authParams);
        //     INTERMediatorOnPage.notifySupport = notifySupport;
        //     try {
        //         myRequest = new XMLHttpRequest();
        //         myRequest.open('POST', appPath, false, INTERMediatorOnPage.httpuser, INTERMediatorOnPage.httppasswd);
        //         myRequest.setRequestHeader('charset', 'utf-8');
        //         myRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        //         myRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        //         myRequest.setRequestHeader('X-From', location.href);
        //         myRequest.send(accessURL + authParams);
        //         jsonObject = JSON.parse(myRequest.responseText);
        //         resultCount = jsonObject.resultCount ? jsonObject.resultCount : 0;
        //         totalCount = jsonObject.totalCount ? jsonObject.totalCount : null;
        //         dbresult = jsonObject.dbresult ? jsonObject.dbresult : null;
        //         requireAuth = jsonObject.requireAuth ? jsonObject.requireAuth : false;
        //         challenge = jsonObject.challenge ? jsonObject.challenge : null;
        //         clientid = jsonObject.clientid ? jsonObject.clientid : null;
        //         newRecordKeyValue = jsonObject.newRecordKeyValue ? jsonObject.newRecordKeyValue : '';
        //         changePasswordResult = jsonObject.changePasswordResult ? jsonObject.changePasswordResult : null;
        //         mediatoken = jsonObject.mediatoken ? jsonObject.mediatoken : null;
        //         notifySupport = jsonObject.notifySupport;
        //         for (i = 0; i < jsonObject.errorMessages.length; i += 1) {
        //             INTERMediatorLog.setErrorMessage(jsonObject.errorMessages[i]);
        //         }
        //         for (i = 0; i < jsonObject.debugMessages.length; i += 1) {
        //             INTERMediatorLog.setDebugMessage(jsonObject.debugMessages[i]);
        //         }
        //         useNull = jsonObject.usenull;
        //         registeredID = jsonObject.hasOwnProperty('registeredid') ? jsonObject.registeredid : '';
        //         INTERMediator_DBAdapter.logging_comResult(myRequest, resultCount, dbresult, requireAuth,
        //             challenge, clientid, newRecordKeyValue, changePasswordResult, mediatoken);
        //         INTERMediator_DBAdapter.store_challenge(challenge);
        //         if (clientid !== null) {
        //             INTERMediatorOnPage.clientId = clientid;
        //         }
        //         if (mediatoken !== null) {
        //             INTERMediatorOnPage.mediaToken = mediatoken;
        //         }
        //         // This is forced fail-over for the password was changed in LDAP auth.
        //         if (INTERMediatorOnPage.isLDAP === true &&
        //             INTERMediatorOnPage.authUserHexSalt !== INTERMediatorOnPage.authHashedPassword.substr(-8, 8)) {
        //             if (accessURL !== 'access=challenge') {
        //                 requireAuth = true;
        //             }
        //         }
        //     } catch (e) {
        //         INTERMediatorLog.setErrorMessage(e,
        //             INTERMediatorLib.getInsertedString(
        //                 INTERMediatorOnPage.getMessages()[errorMessageNumber], [e, myRequest.responseText]));
        //     }
        //     if (accessURL.indexOf('access=changepassword&newpass=') === 0) {
        //         return changePasswordResult;
        //     }
        //     if (requireAuth) {
        //         INTERMediatorLog.setDebugMessage('Authentication Required, user/password panel should be show.');
        //         INTERMediatorOnPage.clearCredentials();
        //         throw new Error('_im_auth_required_');
        //     }
        //     if (!accessURL.match(/access=challenge/)) {
        //         INTERMediatorOnPage.authCount = 0;
        //     }
        //     INTERMediatorOnPage.storeCredentialsToCookieOrStorage();
        //     INTERMediatorOnPage.notifySupport = notifySupport;
        //     return {
        //         dbresult: dbresult,
        //         resultCount: resultCount,
        //         totalCount: totalCount,
        //         newRecordKeyValue: newRecordKeyValue,
        //         newPasswordResult: changePasswordResult,
        //         registeredId: registeredID,
        //         nullAcceptable: useNull
        //     };
    },

    /* No return values */
    server_access_async: function (accessURL, debugMessageNumber, errorMessageNumber, successProc, failedProc, authAgainProc) {
        'use strict';
        var newRecordKeyValue = '', dbresult = '', resultCount = 0, totalCount = null,
            challenge = null, clientid = null, requireAuth = false, myRequest = null,
            changePasswordResult = null, mediatoken = null, appPath,
            authParams, jsonObject, i, notifySupport = false, useNull = false, registeredID = '';
        appPath = INTERMediatorOnPage.getEntryPath();
        authParams = INTERMediator_DBAdapter.generate_authParams();
        INTERMediator_DBAdapter.logging_comAction(debugMessageNumber, appPath, accessURL, authParams);
        INTERMediatorOnPage.notifySupport = notifySupport;
        try {
            myRequest = new XMLHttpRequest();
            myRequest.open('POST', appPath, true,
                INTERMediatorOnPage.httpuser, INTERMediatorOnPage.httppasswd);
            myRequest.setRequestHeader('charset', 'utf-8');
            myRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            myRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            myRequest.setRequestHeader('X-From', location.href);
            myRequest.onreadystatechange = function () {
                switch (myRequest.readyState) {
                case 0: // Unsent
                    break;
                case 1: // Opened
                    break;
                case 2: // Headers Received
                    break;
                case 3: // Loading
                    break;
                case 4:
                    try {
                        jsonObject = JSON.parse(myRequest.responseText);
                    } catch (ex) {
                        INTERMediatorLog.setErrorMessage('Communication Error: ' + myRequest.responseText);
                        failedProc ? failedProc(new Error('_im_communication_error_')) : false;
                        return;
                    }
                    resultCount = jsonObject.resultCount ? jsonObject.resultCount : 0;
                    totalCount = jsonObject.totalCount ? jsonObject.totalCount : null;
                    dbresult = jsonObject.dbresult ? jsonObject.dbresult : null;
                    requireAuth = jsonObject.requireAuth ? jsonObject.requireAuth : false;
                    challenge = jsonObject.challenge ? jsonObject.challenge : null;
                    clientid = jsonObject.clientid ? jsonObject.clientid : null;
                    newRecordKeyValue = jsonObject.newRecordKeyValue ? jsonObject.newRecordKeyValue : '';
                    changePasswordResult = jsonObject.changePasswordResult ? jsonObject.changePasswordResult : null;
                    mediatoken = jsonObject.mediatoken ? jsonObject.mediatoken : null;
                    notifySupport = jsonObject.notifySupport;
                    useNull = jsonObject.usenull;
                    registeredID = jsonObject.hasOwnProperty('registeredid') ? jsonObject.registeredid : '';
                    INTERMediator_DBAdapter.store_challenge(challenge);
                    INTERMediatorOnPage.clientId = clientid ? clientid : null;
                    INTERMediatorOnPage.mediaToken = mediatoken ? mediatoken : null;
                    for (i = 0; i < jsonObject.errorMessages.length; i += 1) {
                        INTERMediatorLog.setErrorMessage(jsonObject.errorMessages[i]);
                    }
                    for (i = 0; i < jsonObject.debugMessages.length; i += 1) {
                        INTERMediatorLog.setDebugMessage(jsonObject.debugMessages[i]);
                    }
                    if (jsonObject.errorMessages.length > 0) {
                        INTERMediatorLog.setErrorMessage('Communication Error: ' + jsonObject.errorMessages);
                        failedProc ? failedProc(new Error('_im_communication_error_')) : false;
                        return;
                    }
                    INTERMediator_DBAdapter.logging_comResult(myRequest, resultCount, dbresult, requireAuth,
                        challenge, clientid, newRecordKeyValue, changePasswordResult, mediatoken);
                    // This is forced fail-over for the password was changed in LDAP auth.
                    if (INTERMediatorOnPage.isLDAP === true &&
                        INTERMediatorOnPage.authUserHexSalt !== INTERMediatorOnPage.authHashedPassword.substr(-8, 8)) {
                        if (accessURL !== 'access=challenge') {
                            requireAuth = true;
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
                            });
                        }
                        return;
                    }
                    if (requireAuth) {
                        INTERMediatorLog.setDebugMessage('Authentication Required, user/password panel should be show.');
                        INTERMediatorOnPage.clearCredentials();
                        if (authAgainProc) {
                            authAgainProc(myRequest);
                        }
                        failedProc ? failedProc(new Error('_im_auth_required_')) : false;
                        return;
                    }
                    if (!accessURL.match(/access=challenge/)) {
                        INTERMediatorOnPage.authCount = 0;
                    }
                    INTERMediatorOnPage.storeCredentialsToCookieOrStorage();
                    INTERMediatorOnPage.notifySupport = notifySupport;
                    if (successProc) {
                        successProc({
                            dbresult: dbresult,
                            resultCount: resultCount,
                            totalCount: totalCount,
                            newRecordKeyValue: newRecordKeyValue,
                            newPasswordResult: changePasswordResult,
                            registeredId: registeredID,
                            nullAcceptable: useNull
                        });
                    }
                    break;
                }
            };
            myRequest.send(accessURL + authParams);
        } catch (e) {
            INTERMediatorLog.setErrorMessage(e,
                INTERMediatorLib.getInsertedString(
                    INTERMediatorOnPage.getMessages()[errorMessageNumber], [e, myRequest.responseText]));
            if (failedProc) {
                failedProc();
            }
        }
    },

    /**
     * Change the password of specified user.
     * @param username The user name.
     * @param oldpassword The current password.
     * @param newpassword The new password
     * @returns {Promise.<*>}
     *
     * This method has to be called with await.
     *
     */
    changePassword: async function (username, oldpassword, newpassword) {
        'use strict';
        var challengeResult, params, messageNode;

        return new Promise(async (resolve, reject) => {
            if (!username || !oldpassword) {
                reject(new Error('_im_changepw_noparams'));
                return;
            }
            INTERMediatorOnPage.authUser = username;
            if (username !== '' &&  // No usename and no challenge, get a challenge.
                (INTERMediatorOnPage.authChallenge === null || INTERMediatorOnPage.authChallenge.length < 24 )) {
                INTERMediatorOnPage.authHashedPassword = 'need-hash-pls';   // Dummy Hash for getting a challenge
                try {
                    challengeResult = await INTERMediator_DBAdapter.getChallenge();
                } catch (er) {
                    reject(er);
                    return;
                }
                // if (!challengeResult) {
                //     messageNode = document.getElementById('_im_newpass_message');
                //     if (messageNode) {
                //         INTERMediatorLib.removeChildNodes(messageNode);
                //         messageNode.appendChild(
                //             document.createTextNode(
                //                 INTERMediatorLib.getInsertedStringFromErrorNumber(2008)));
                //     } else {
                //         window.alert(INTERMediatorLib.getInsertedStringFromErrorNumber(2008));
                //     }
                //     INTERMediatorLog.flushMessage();
                //     return false; // If it's failed to get a challenge, finish everything.
                // }
            }
            INTERMediatorOnPage.authHashedPassword =
                SHA1(oldpassword + INTERMediatorOnPage.authUserSalt) +
                INTERMediatorOnPage.authUserHexSalt;
            params = 'access=changepassword&newpass=' + INTERMediatorLib.generatePasswordHash(newpassword);

            this.server_access_async(params, 1029, 1030,
                (result) => {
                    if (result.newPasswordResult) {
                        INTERMediatorOnPage.authCryptedPassword =
                            INTERMediatorOnPage.publickey.biEncryptedString(newpassword);
                        INTERMediatorOnPage.authHashedPassword =
                            SHA1(newpassword + INTERMediatorOnPage.authUserSalt) + INTERMediatorOnPage.authUserHexSalt;
                        INTERMediatorOnPage.storeCredentialsToCookieOrStorage();
                        resolve(true);
                    } else {
                        reject(new Error('_im_changepw_notchange'));
                    }
                },
                (er) => {
                    reject(er);
                });
        }).catch((er) => {
            throw er;
        });
    },

    /**
     *
     * @returns {Promise.<T>}
     */
    getChallenge: function () {
        'use strict';
        return new Promise((resolve, reject) => {
            this.server_access_async('access=challenge', 1027, 1028,
                (result) => {
                    resolve(result);
                },
                (er) => {
                    reject(er);
                });
        }).catch((er) => {
            throw er;
        });
    },

    uploadFile: function (parameters, uploadingFile, doItOnFinish, exceptionProc) {
        'use strict';
        var myRequest = null, appPath, authParams, accessURL, i;
        //           var result = this.server_access('access=uploadfile' + parameters, 1031, 1032, uploadingFile);
        appPath = INTERMediatorOnPage.getEntryPath();
        authParams = INTERMediator_DBAdapter.generate_authParams();
        accessURL = 'access=uploadfile' + parameters;
        INTERMediator_DBAdapter.logging_comAction(1031, appPath, accessURL, authParams);
        try {
            myRequest = new XMLHttpRequest();
            myRequest.open('POST', appPath, true, INTERMediatorOnPage.httpuser, INTERMediatorOnPage.httppasswd);
            myRequest.setRequestHeader('charset', 'utf-8');
            var params = (accessURL + authParams).split('&');
            var fd = new FormData();
            for (i = 0; i < params.length; i += 1) {
                var valueset = params[i].split('=');
                fd.append(valueset[0], decodeURIComponent(valueset[1]));
            }
            fd.append('_im_uploadfile', uploadingFile.content);
            myRequest.onreadystatechange = function () {
                switch (myRequest.readyState) {
                case 3:
                    break;
                case 4:
                    INTERMediator_DBAdapter.uploadFileAfterSucceed(myRequest, doItOnFinish, exceptionProc, false);
                    break;
                }
            };
            myRequest.send(fd);
        } catch (e) {
            INTERMediatorLog.setErrorMessage(e,
                INTERMediatorLib.getInsertedString(
                    INTERMediatorOnPage.getMessages()[1032], [e, myRequest.responseText]));
            exceptionProc();
        }
    },

    uploadFileAfterSucceed: function (myRequest, doItOnFinish, exceptionProc, isErrorDialog) {
        'use strict';
        var newRecordKeyValue = '', dbresult = '', resultCount = 0, challenge = null,
            clientid = null, requireAuth = false, changePasswordResult = null,
            mediatoken = null, jsonObject, i, returnValue = true;
        try {
            jsonObject = JSON.parse(myRequest.responseText);
        } catch (ex) {
            INTERMediatorLog.setErrorMessage(ex,
                INTERMediatorLib.getInsertedString(
                    INTERMediatorOnPage.getMessages()[1032], ['', '']));
            INTERMediatorLog.flushMessage();
            exceptionProc();
            return false;
        }
        resultCount = jsonObject.resultCount ? jsonObject.resultCount : 0;
        dbresult = jsonObject.dbresult ? jsonObject.dbresult : null;
        requireAuth = jsonObject.requireAuth ? jsonObject.requireAuth : false;
        challenge = jsonObject.challenge ? jsonObject.challenge : null;
        clientid = jsonObject.clientid ? jsonObject.clientid : null;
        newRecordKeyValue = jsonObject.newRecordKeyValue ? jsonObject.newRecordKeyValue : '';
        changePasswordResult = jsonObject.changePasswordResult ? jsonObject.changePasswordResult : null;
        mediatoken = jsonObject.mediatoken ? jsonObject.mediatoken : null;
        for (i = 0; i < jsonObject.errorMessages.length; i += 1) {
            if (isErrorDialog) {
                window.alert(jsonObject.errorMessages[i]);
            } else {
                INTERMediatorLog.setErrorMessage(jsonObject.errorMessages[i]);
            }
            returnValue = false;
        }
        for (i = 0; i < jsonObject.debugMessages.length; i += 1) {
            INTERMediatorLog.setDebugMessage(jsonObject.debugMessages[i]);
        }

        INTERMediator_DBAdapter.logging_comResult(myRequest, resultCount, dbresult, requireAuth,
            challenge, clientid, newRecordKeyValue, changePasswordResult, mediatoken);
        INTERMediator_DBAdapter.store_challenge(challenge);
        if (clientid !== null) {
            INTERMediatorOnPage.clientId = clientid;
        }
        if (mediatoken !== null) {
            INTERMediatorOnPage.mediaToken = mediatoken;
        }
        if (requireAuth) {
            INTERMediatorLog.setDebugMessage('Authentication Required, user/password panel should be show.');
            INTERMediatorOnPage.clearCredentials();
            //throw new Error('_im_auth_required_');
            exceptionProc();
        }
        INTERMediatorOnPage.authCount = 0;
        INTERMediatorOnPage.storeCredentialsToCookieOrStorage();
        doItOnFinish(dbresult);
        return returnValue;
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

     This function returns recordset of retrieved.
     */
    db_query: function (args) {
        INTERMediatorLog.setErrorMessage('INTERMediator_DBAdapter.db_query method was discontinued in Ver.6');
        //     'use strict';
        //     var params, returnValue, result, contextDef;
        //
        //     if (!INTERMediator_DBAdapter.db_queryChecking(args)) {
        //         return;
        //     }
        //     params = INTERMediator_DBAdapter.db_queryParameters(args);
        //     // INTERMediator_DBAdapter.eliminateDuplicatedConditions = false;
        //     // params += '&randkey' + Math.random();    // For ie...
        //     // IE uses caches as the result in spite of several headers. So URL should be randomly.
        //     //
        //     // This is not requred because the Notification feature adds the client Identifier for each communication.
        //     // msyk June 1, 2014
        //     returnValue = {};
        //     try {
        //         result = this.server_access(params, 1012, 1004);
        //         returnValue.dbresult = result.dbresult;
        //         returnValue.totalCount = result.resultCount;
        //         returnValue.count = 0;
        //         returnValue.registeredId = result.registeredId;
        //         returnValue.nullAcceptable = result.nullAcceptable;
        //         returnValue.count = result.dbresult ? Object.keys(result.dbresult).length : 0;
        //
        //         contextDef = INTERMediatorLib.getNamedObject(
        //             INTERMediatorOnPage.getDataSources(), 'name', args.name);
        //         if (!contextDef.relation &&
        //             args.paging && Boolean(args.paging) === true) {
        //             INTERMediator.pagedAllCount = parseInt(result.resultCount, 10);
        //             if (result.totalCount) {
        //                 INTERMediator.totalRecordCount = parseInt(result.totalCount, 10);
        //             }
        //         }
        //         if ((args.paging !== null) && (Boolean(args.paging) === true)) {
        //             INTERMediator.pagination = true;
        //             if (!(Number(args.records) >= Number(INTERMediator.pagedSize) &&
        //                 Number(INTERMediator.pagedSize) > 0)) {
        //                 INTERMediator.pagedSize = parseInt(args.records, 10);
        //             }
        //         }
        //     } catch (ex) {
        //         if (ex.message === '_im_auth_required_') {
        //             throw ex;
        //         } else {
        //             INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-17');
        //         }
        //         returnValue.dbresult = null;
        //         returnValue.totalCount = 0;
        //         returnValue.count = 0;
        //         returnValue.registeredid = null;
        //         returnValue.nullAcceptable = null;
        //     }
        //     return returnValue;
    },
    //
    db_queryWithAuth: function (args, completion) {
        INTERMediatorLog.setErrorMessage('INTERMediator_DBAdapter.db_queryWithAuth method was discontinued in Ver.6');
        //     'use strict';
        //     var returnValue = false;
        //     INTERMediatorOnPage.retrieveAuthInfo();
        //     try {
        //         returnValue = INTERMediator_DBAdapter.db_query(args);
        //     } catch (ex) {
        //         if (ex.message === '_im_auth_required_') {
        //             if (INTERMediatorOnPage.requireAuthentication) {
        //                 if (!INTERMediatorOnPage.isComplementAuthData()) {
        //                     INTERMediatorOnPage.clearCredentials();
        //                     INTERMediatorOnPage.authenticating(
        //                         function () {
        //                             returnValue = INTERMediator_DBAdapter.db_queryWithAuth(args, completion);
        //                         });
        //                     return;
        //                 }
        //             }
        //         } else {
        //             INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-16');
        //         }
        //     }
        //     completion(returnValue);
    },

    db_query_async: function (args, successProc, failedProc) {
        'use strict';
        var params;
        if (!INTERMediator_DBAdapter.db_queryChecking(args)) {
            return;
        }
        params = INTERMediator_DBAdapter.db_queryParameters(args);
        return new Promise((resolve, reject) => {
                this.server_access_async(params, 1012, 1004,
                    (() => {
                        var contextDef;
                        var contextName = args.name;
                        var recordsNumber = Number(args.records);
                        var resolveCapt = resolve;
                        return (result) => {
                            result.count = result.dbresult ? Object.keys(result.dbresult).length : 0;
                            contextDef = IMLibContextPool.getContextDef(contextName);
                            if (!contextDef.relation &&
                                args.paging && Boolean(args.paging) === true) {
                                INTERMediator.pagedAllCount = parseInt(result.resultCount, 10);
                                if (result.totalCount) {
                                    INTERMediator.totalRecordCount = parseInt(result.totalCount, 10);
                                }
                            }
                            if ((args.paging !== null) && (Boolean(args.paging) === true)) {
                                INTERMediator.pagination = true;
                                if (!(recordsNumber >= Number(INTERMediator.pagedSize) &&
                                    Number(INTERMediator.pagedSize) > 0)) {
                                    INTERMediator.pagedSize = parseInt(recordsNumber, 10);
                                }
                            }
                            successProc ? successProc(result) : false;
                            resolveCapt(result);
                        };
                    })(),
                    (er) => {
                        failedProc ? failedProc(param) : false;
                        reject(er);
                    }
                );
            }
        ).catch((err) => {
            throw err;
        });
    },

    db_queryChecking: function (args) {
        'use strict';
        var noError = true;
        if (args.name === null || args.name === '') {
            INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1005));
            noError = false;
        }
        return noError;
    },

    db_queryParameters: function (args) {
        'use strict';
        var i, index, params, counter, extCount, criteriaObject, sortkeyObject,
            extCountSort, recordLimit = 10000000, conditions, conditionSign, modifyConditions,
            orderFields, key, keyParams, value, fields, operator, orderedKeys, removeIndice = [];
        if (args.records === null) {
            params = 'access=read&name=' + encodeURIComponent(args.name);
        } else {
            if (parseInt(args.records, 10) === 0 &&
                (INTERMediatorOnPage.dbClassName === 'DB_FileMaker_FX' ||
                INTERMediatorOnPage.dbClassName === 'DB_FileMaker_DataAPI')) {
                params = 'access=describe&name=' + encodeURIComponent(args.name);
            } else {
                params = 'access=read&name=' + encodeURIComponent(args.name);
            }
            if (Boolean(args.uselimit) === true &&
                parseInt(args.records, 10) >= INTERMediator.pagedSize &&
                parseInt(INTERMediator.pagedSize, 10) > 0) {
                recordLimit = INTERMediator.pagedSize;
            } else {
                recordLimit = args.records;
            }
        }

        if (args.primaryKeyOnly) {
            params += '&pkeyonly=true';
        }

        if (args.fields) {
            for (i = 0; i < args.fields.length; i += 1) {
                params += '&field_' + i + '=' + encodeURIComponent(args.fields[i]);
            }
        }
        counter = 0;
        if (args.parentkeyvalue) {
            //noinspection JSDuplicatedDeclaration
            for (index in args.parentkeyvalue) {
                if (args.parentkeyvalue.hasOwnProperty(index)) {
                    params += '&foreign' + counter +
                        'field=' + encodeURIComponent(index);
                    params += '&foreign' + counter +
                        'value=' + encodeURIComponent(args.parentkeyvalue[index]);
                    counter++;
                }
            }
        }
        if (args.useoffset && INTERMediator.startFrom !== null) {
            params += '&start=' + encodeURIComponent(INTERMediator.startFrom);
        }
        extCount = 0;
        conditions = [];
        while (args.conditions && args.conditions[extCount]) {
            conditionSign = args.conditions[extCount].field + '#' +
                args.conditions[extCount].operator + '#' +
                args.conditions[extCount].value;
            if (!INTERMediator_DBAdapter.eliminateDuplicatedConditions || conditions.indexOf(conditionSign) < 0) {
                params += '&condition' + extCount;
                params += 'field=' + encodeURIComponent(args.conditions[extCount].field);
                params += '&condition' + extCount;
                params += 'operator=' + encodeURIComponent(args.conditions[extCount].operator);
                params += '&condition' + extCount;
                params += 'value=' + encodeURIComponent(args.conditions[extCount].value);
                conditions.push(conditionSign);
            }
            extCount++;
        }
        criteriaObject = INTERMediator.additionalCondition[args.name];
        if (criteriaObject) {
            if (criteriaObject.field) {
                criteriaObject = [criteriaObject];
            }
            for (index = 0; index < criteriaObject.length; index++) {
                if (criteriaObject[index] && criteriaObject[index].field) {
                    if (criteriaObject[index].value || criteriaObject[index].field === '__operation__') {
                        conditionSign =
                            criteriaObject[index].field + '#' +
                            ((criteriaObject[index].operator !== undefined) ? criteriaObject[index].operator : '') + '#' +
                            ((criteriaObject[index].value !== undefined) ? criteriaObject[index].value : '' );
                        if (!INTERMediator_DBAdapter.eliminateDuplicatedConditions || conditions.indexOf(conditionSign) < 0) {
                            params += '&condition' + extCount;
                            params += 'field=' + encodeURIComponent(criteriaObject[index].field);
                            if (criteriaObject[index].operator !== undefined) {
                                params += '&condition' + extCount;
                                params += 'operator=' + encodeURIComponent(criteriaObject[index].operator);
                            }
                            if (criteriaObject[index].value !== undefined) {
                                params += '&condition' + extCount;
                                value = criteriaObject[index].value;
                                if (Array.isArray(value)) {
                                    value = JSON.stringify(value);
                                }
                                params += 'value=' + encodeURIComponent(value);
                            }
                            if (criteriaObject[index].field !== '__operation__') {
                                conditions.push(conditionSign);
                            }
                        }
                        extCount++;
                    }
                }
                if (criteriaObject[index] && criteriaObject[index].onetime) {
                    removeIndice.push = index;
                }
            }
            if (removeIndice.length > 0) {
                modifyConditions = [];
                for (index = 0; index < criteriaObject.length; index++) {
                    if (!(index in removeIndice)) {
                        modifyConditions.push(criteriaObject[index]);
                    }
                }
                INTERMediator.additionalCondition[args.name] = modifyConditions;
                IMLibLocalContext.archive();
            }
        }

        extCountSort = 0;
        sortkeyObject = INTERMediator.additionalSortKey[args.name];
        if (sortkeyObject) {
            if (sortkeyObject.field) {
                sortkeyObject = [sortkeyObject];
            }
            for (index = 0; index < sortkeyObject.length; index++) {
                params += '&sortkey' + extCountSort;
                params += 'field=' + encodeURIComponent(sortkeyObject[index].field);
                params += '&sortkey' + extCountSort;
                params += 'direction=' + encodeURIComponent(sortkeyObject[index].direction);
                extCountSort++;
            }
        }

        orderFields = {};
        for (key in IMLibLocalContext.store) {
            if (IMLibLocalContext.store.hasOwnProperty(key)) {
                value = String(IMLibLocalContext.store[key]);
                keyParams = key.split(':');
                if (keyParams && keyParams.length > 1 && keyParams[1].trim() === args.name && value.length > 0) {
                    if (keyParams[0].trim() === 'condition' && keyParams.length >= 4) {
                        fields = keyParams[2].split(',');
                        operator = keyParams[3].trim();
                        if (fields.length > 1) {
                            params += '&condition' + extCount + 'field=__operation__';
                            params += '&condition' + extCount + 'operator=ex';
                            extCount++;
                            //conditions = [];
                        }
                        for (index = 0; index < fields.length; index++) {
                            conditionSign = fields[index].trim() + '#' + operator + '#' + value;
                            if (!INTERMediator_DBAdapter.eliminateDuplicatedConditions || conditions.indexOf(conditionSign) < 0) {
                                params += '&condition' + extCount + 'field=' + encodeURIComponent(fields[index].trim());
                                params += '&condition' + extCount + 'operator=' + encodeURIComponent(operator);
                                params += '&condition' + extCount + 'value=' + encodeURIComponent(value);
                                conditions.push(conditionSign);
                            }
                            extCount++;
                        }
                    } else if (keyParams[0].trim() === 'valueofaddorder' && keyParams.length >= 4) {
                        orderFields[parseInt(value)] = [keyParams[2].trim(), keyParams[3].trim()];
                    } else if (keyParams[0].trim() === 'limitnumber' && keyParams.length >= 4) {
                        recordLimit = parseInt(value);
                    }
                }
            }
        }
        params += '&records=' + encodeURIComponent(recordLimit);
        orderedKeys = Object.keys(orderFields);
        for (i = 0; i < orderedKeys.length; i += 1) {
            params += '&sortkey' + extCountSort + 'field=' + encodeURIComponent(orderFields[orderedKeys[i]][0]);
            params += '&sortkey' + extCountSort + 'direction=' + encodeURIComponent(orderFields[orderedKeys[i]][1]);
            extCountSort++;
        }
        return params;
    },

    /*
     db_update
     Update the database. The parameter of this function should be the object as below:

     {   name:<Name of the Context>
     conditions:<the array of the object {field:xx,operator:xx,value:xx} to search records>
     dataset:<the array of the object {field:xx,value:xx}. each value will be set to the field.> }
     */
    db_update: function (args) {
        INTERMediatorLog.setErrorMessage('INTERMediator_DBAdapter.db_update method was discontinued in Ver.6');
        //     'use strict';
        //     var params, result;
        //     if (!INTERMediator_DBAdapter.db_updateChecking(args)) {
        //         return;
        //     }
        //     params = INTERMediator_DBAdapter.db_updateParameters(args);
        //     result = this.server_access(params, 1013, 1014);
        //     return result.dbresult;
    },

    db_updateWithAuth: function (args, completion) {
        INTERMediatorLog.setErrorMessage('INTERMediator_DBAdapter.db_updateWithAuth method was discontinued in Ver.6');
        //     'use strict';
        //     var returnValue = false;
        //     INTERMediatorOnPage.retrieveAuthInfo();
        //     try {
        //         returnValue = INTERMediator_DBAdapter.db_update(args);
        //     } catch (ex) {
        //         if (ex.message === '_im_auth_required_') {
        //             if (INTERMediatorOnPage.requireAuthentication) {
        //                 if (!INTERMediatorOnPage.isComplementAuthData()) {
        //                     INTERMediatorOnPage.clearCredentials();
        //                     INTERMediatorOnPage.authenticating(
        //                         function () {
        //                             returnValue = INTERMediator_DBAdapter.db_updateWithAuth(args, completion);
        //                         });
        //                     return;
        //                 }
        //             }
        //         } else {
        //             INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-15');
        //         }
        //     }
        //     completion(returnValue);
    },

    db_updateChecking: function (args) {
        'use strict';
        var noError = true, contextDef;

        if (args.name === null) {
            INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1007));
            noError = false;
        }
        contextDef = IMLibContextPool.getContextDef(args.name);
        if (!contextDef.key) {
            INTERMediatorLog.setErrorMessage(
                INTERMediatorLib.getInsertedStringFromErrorNumber(1045, [args.name]));
            noError = false;
        }
        if (args.dataset === null) {
            INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1011));
            noError = false;
        }
        return noError;
    },

    db_updateParameters: function (args) {
        'use strict';
        var params, extCount, counter, index, addedObject;
        params = 'access=update&name=' + encodeURIComponent(args.name);
        counter = 0;
        if (INTERMediator.additionalFieldValueOnUpdate &&
            INTERMediator.additionalFieldValueOnUpdate[args.name]) {
            addedObject = INTERMediator.additionalFieldValueOnUpdate[args.name];
            if (addedObject.field) {
                addedObject = [addedObject];
            }
            for (index in addedObject) {
                if (addedObject.hasOwnProperty(index)) {
                    var oneDefinition = addedObject[index];
                    params += '&field_' + counter + '=' + encodeURIComponent(oneDefinition.field);
                    params += '&value_' + counter + '=' + encodeURIComponent(oneDefinition.value);
                    counter++;
                }
            }
        }

        if (args.conditions) {
            for (extCount = 0; extCount < args.conditions.length; extCount++) {
                params += '&condition' + extCount + 'field=';
                params += encodeURIComponent(args.conditions[extCount].field);
                params += '&condition' + extCount + 'operator=';
                params += encodeURIComponent(args.conditions[extCount].operator);
                if (args.conditions[extCount].value) {
                    params += '&condition' + extCount + 'value=';
                    params += encodeURIComponent(args.conditions[extCount].value);
                }
            }
        }
        for (extCount = 0; extCount < args.dataset.length; extCount++) {
            params += '&field_' + (counter + extCount) + '=' + encodeURIComponent(args.dataset[extCount].field);
            if (INTERMediator.isTrident && INTERMediator.ieVersion === 8) {
                params += '&value_' + (counter + extCount) + '=' + encodeURIComponent(args.dataset[extCount].value.replace(/\n/g, ''));
            } else {
                params += '&value_' + (counter + extCount) + '=' + encodeURIComponent(args.dataset[extCount].value);
            }
        }
        return params;
    },

    db_update_async: function (args, successProc, failedProc) {
        'use strict';
        var params;
        if (!INTERMediator_DBAdapter.db_updateChecking(args)) {
            return;
        }
        params = INTERMediator_DBAdapter.db_updateParameters(args);
        if (params) {
            //INTERMediatorOnPage.retrieveAuthInfo();
            INTERMediator_DBAdapter.server_access_async(
                params,
                1013,
                1014,
                successProc,
                failedProc,
                INTERMediator_DBAdapter.createExceptionFunc(
                    1016,
                    (function () {
                        var argsCapt = args;
                        var succesProcCapt = successProc;
                        var failedProcCapt = failedProc;
                        return function () {
                            INTERMediator_DBAdapter.db_update_async(
                                argsCapt, succesProcCapt, failedProcCapt);
                        };
                    })()
                )
            );
        }
    },

    /*
     db_delete
     Delete the record. The parameter of this function should be the object as below:

     {   name:<Name of the Context>
     conditions:<the array of the object {field:xx,operator:xx,value:xx} to search records, could be null>}
     */
    db_delete: function (args) {
        INTERMediatorLog.setErrorMessage('INTERMediator_DBAdapter.db_delete method was discontinued in Ver.6');
//     'use strict';
        //     var params, result;
        //     if (!INTERMediator_DBAdapter.db_deleteChecking(args)) {
        //         return;
        //     }
        //     params = INTERMediator_DBAdapter.db_deleteParameters(args);
        //     result = this.server_access(params, 1017, 1015);
        //     INTERMediatorLog.flushMessage();
        //     return result;
    },

    db_deleteWithAuth: function (args, completion) {
        INTERMediatorLog.setErrorMessage('INTERMediator_DBAdapter.db_deleteWithAuth method was discontinued in Ver.6');
        //     'use strict';
        //     var returnValue = false;
        //     INTERMediatorOnPage.retrieveAuthInfo();
        //     try {
        //         returnValue = INTERMediator_DBAdapter.db_delete(args);
        //     } catch (ex) {
        //         if (ex.message === '_im_auth_required_') {
        //             if (INTERMediatorOnPage.requireAuthentication) {
        //                 if (!INTERMediatorOnPage.isComplementAuthData()) {
        //                     INTERMediatorOnPage.clearCredentials();
        //                     INTERMediatorOnPage.authenticating(
        //                         function () {
        //                             returnValue = INTERMediator_DBAdapter.db_deleteWithAuth(args, completion);
        //                         });
        //                     return;
        //                 }
        //             }
        //         } else {
        //             INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-14');
        //         }
        //     }
        //     completion(returnValue);
    },

    db_deleteChecking: function (args) {
        'use strict';
        var noError = true, contextDef;

        if (args.name === null) {
            INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1019));
            noError = false;
        }
        contextDef = IMLibContextPool.getContextDef(args.name);
        if (!contextDef.key) {
            INTERMediatorLog.setErrorMessage(
                INTERMediatorLib.getInsertedStringFromErrorNumber(1045, [args.name]));
            noError = false;
        }
        if (args.conditions === null) {
            INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1020));
            noError = false;
        }
        return noError;
    },

    db_deleteParameters: function (args) {
        'use strict';
        var params, i, counter, index, addedObject;
        params = 'access=delete&name=' + encodeURIComponent(args.name);
        counter = 0;
        if (INTERMediator.additionalFieldValueOnDelete &&
            INTERMediator.additionalFieldValueOnDelete[args.name]) {
            addedObject = INTERMediator.additionalFieldValueOnDelete[args.name];
            if (addedObject.field) {
                addedObject = [addedObject];
            }
            for (index in addedObject) {
                if (addedObject.hasOwnProperty(index)) {
                    var oneDefinition = addedObject[index];
                    params += '&field_' + counter + '=' + encodeURIComponent(oneDefinition.field);
                    params += '&value_' + counter + '=' + encodeURIComponent(oneDefinition.value);
                    counter++;
                }
            }
        }

        for (i = 0; i < args.conditions.length; i += 1) {
            params += '&condition' + i + 'field=' + encodeURIComponent(args.conditions[i].field);
            params += '&condition' + i + 'operator=' + encodeURIComponent(args.conditions[i].operator);
            params += '&condition' + i + 'value=' + encodeURIComponent(args.conditions[i].value);
        }
        return params;
    },

    db_delete_async: function (args, successProc, failedProc) {
        'use strict';
        var params;
        if (!INTERMediator_DBAdapter.db_deleteChecking(args)) {
            return;
        }
        params = INTERMediator_DBAdapter.db_deleteParameters(args);
        if (params) {
            //INTERMediatorOnPage.retrieveAuthInfo();
            INTERMediator_DBAdapter.server_access_async(
                params,
                1017,
                1015,
                successProc,
                failedProc,
                INTERMediator_DBAdapter.createExceptionFunc(
                    1016,
                    (function () {
                        var argsCapt = args;
                        var succesProcCapt = successProc;
                        var failedProcCapt = failedProc;
                        return function () {
                            INTERMediator_DBAdapter.db_delete_async(
                                argsCapt, succesProcCapt, failedProcCapt);
                        };
                    })()
                )
            );
        }
    },

    /*
     db_createRecord
     Create a record. The parameter of this function should be the object as below:

     {   name:<Name of the Context>
     dataset:<the array of the object {field:xx,value:xx}. Initial value for each field> }

     This function returns the value of the key field of the new record.
     */
    db_createRecord: function (args) {
        INTERMediatorLog.setErrorMessage('INTERMediator_DBAdapter.db_createRecord method was discontinued in Ver.6');
        //     'use strict';
        //     var params, result;
        //     params = INTERMediator_DBAdapter.db_createParameters(args);
        //     if (params) {
        //         result = INTERMediator_DBAdapter.server_access(params, 1018, 1016);
        //         INTERMediatorLog.flushMessage();
        //         return {
        //             newKeyValue: result.newRecordKeyValue,
        //             recordset: result.dbresult
        //         };
        //     }
        //     return false;
    },

    db_createRecordWithAuth: function (args, completion) {
        INTERMediatorLog.setErrorMessage('INTERMediator_DBAdapter.db_createRecordWithAuth method was discontinued in Ver.6');
        //     'use strict';
        //     var returnValue = false;
        //     INTERMediatorOnPage.retrieveAuthInfo();
        //     try {
        //         returnValue = INTERMediator_DBAdapter.db_createRecord(args);
        //     } catch (ex) {
        //         if (ex.message === '_im_auth_required_') {
        //             if (INTERMediatorOnPage.requireAuthentication) {
        //                 if (!INTERMediatorOnPage.isComplementAuthData()) {
        //                     INTERMediatorOnPage.clearCredentials();
        //                     INTERMediatorOnPage.authenticating(
        //                         function () {
        //                             returnValue = INTERMediator_DBAdapter.db_createRecordWithAuth(args, completion);
        //                         });
        //                     return;
        //                 }
        //             }
        //         } else {
        //             INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-13');
        //         }
        //     }
        //     if (completion) {
        //         completion(returnValue.newKeyValue);
        //     }
    },

    db_createRecord_async: function (args, successProc, failedProc) {
        'use strict';
        var params = INTERMediator_DBAdapter.db_createParameters(args);
        if (params) {
            //INTERMediatorOnPage.retrieveAuthInfo();
            INTERMediator_DBAdapter.server_access_async(
                params,
                1018,
                1016,
                successProc,
                failedProc,
                INTERMediator_DBAdapter.createExceptionFunc(
                    1016,
                    (function () {
                        var argsCapt = args;
                        var succesProcCapt = successProc;
                        var failedProcCapt = failedProc;
                        return function () {
                            INTERMediator_DBAdapter.db_createRecord_async(
                                argsCapt, succesProcCapt, failedProcCapt);
                        };
                    })()
                )
            );
        }
    },

    db_createParameters: function (args) {
        'use strict';
        var params, i, index, addedObject, counter, targetKey, ds, key, contextDef;

        if (args.name === null) {
            INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1021));
            return false;
        }
        contextDef = IMLibContextPool.getContextDef(args.name);
        if (!contextDef.key) {
            INTERMediatorLog.setErrorMessage(
                INTERMediatorLib.getInsertedStringFromErrorNumber(1045, [args.name]));
            return false;
        }
        ds = INTERMediatorOnPage.getDataSources(); // Get DataSource parameters
        targetKey = null;
        for (key in ds) { // Search this table from DataSource
            if (ds.hasOwnProperty(key) && ds[key].name === args.name) {
                targetKey = key;
                break;
            }
        }
        if (targetKey === null) {
            INTERMediatorLog.setErrorMessage('no targetname :' + args.name);
            return false;
        }
        params = 'access=create&name=' + encodeURIComponent(args.name);
        counter = 0;
        if (INTERMediator.additionalFieldValueOnNewRecord &&
            INTERMediator.additionalFieldValueOnNewRecord[args.name]) {
            addedObject = INTERMediator.additionalFieldValueOnNewRecord[args.name];
            if (addedObject.field) {
                addedObject = [addedObject];
            }
            for (index in addedObject) {
                if (addedObject.hasOwnProperty(index)) {
                    const oneDefinition = addedObject[index];
                    params += '&field_' + counter + '=' + encodeURIComponent(oneDefinition.field);
                    params += '&value_' + counter + '=' + encodeURIComponent(oneDefinition.value);
                    counter++;
                }
            }
        }

        for (i = 0; i < args.dataset.length; i += 1) {
            params += '&field_' + counter + '=' + encodeURIComponent(args.dataset[i].field);
            params += '&value_' + counter + '=' + encodeURIComponent(args.dataset[i].value);
            counter++;
        }
        return params;
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
    db_copy: function (args) {
        INTERMediatorLog.setErrorMessage('INTERMediator_DBAdapter.db_copy method was discontinued in Ver.6');
//     'use strict';
        //     var params, result;
        //     params = INTERMediator_DBAdapter.db_copyParameters(args);
        //     if (params) {
        //         result = INTERMediator_DBAdapter.server_access(params, 1017, 1015);
        //         INTERMediatorLog.flushMessage();
        //         return {
        //             newKeyValue: result.newRecordKeyValue,
        //             recordset: result.dbresult
        //         };
        //     }
        //     return false;
    },

    db_copyWithAuth: function (args, completion) {
        INTERMediatorLog.setErrorMessage('INTERMediator_DBAdapter.db_copyWithAuth method was discontinued in Ver.6');
//     'use strict';
        //     var returnValue = false;
        //     INTERMediatorOnPage.retrieveAuthInfo();
        //     try {
        //         returnValue = INTERMediator_DBAdapter.db_copy(args);
        //     } catch (ex) {
        //         if (ex.message === '_im_auth_required_') {
        //             if (INTERMediatorOnPage.requireAuthentication) {
        //                 if (!INTERMediatorOnPage.isComplementAuthData()) {
        //                     INTERMediatorOnPage.clearCredentials();
        //                     INTERMediatorOnPage.authenticating(
        //                         function () {
        //                             returnValue = INTERMediator_DBAdapter.db_copyWithAuth(args, completion);
        //                         });
        //                     return;
        //                 }
        //             }
        //         } else {
        //             INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-14');
        //         }
        //     }
        //     completion(returnValue);
    },

    db_copy_async: function (args, successProc, failedProc) {
        'use strict';
        var params = INTERMediator_DBAdapter.db_copyParameters(args);
        if (params) {
            //INTERMediatorOnPage.retrieveAuthInfo();
            INTERMediator_DBAdapter.server_access_async(
                params,
                1017,
                1015,
                successProc,
                failedProc,
                INTERMediator_DBAdapter.createExceptionFunc(
                    1016,
                    (function () {
                        var argsCapt = args;
                        var succesProcCapt = successProc;
                        var failedProcCapt = failedProc;
                        return function () {
                            INTERMediator_DBAdapter.db_copy_async(
                                argsCapt, succesProcCapt, failedProcCapt);
                        };
                    })()
                )
            );
        }
    },

    db_copyParameters: function (args) {
        'use strict';
        var noError = true, params, i;

        if (args.name === null) {
            INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1019));
            noError = false;
        }
        if (args.conditions === null) {
            INTERMediatorLog.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1020));
            noError = false;
        }
        if (!noError) {
            return false;
        }

        params = 'access=copy&name=' + encodeURIComponent(args.name);
        for (i = 0; i < args.conditions.length; i += 1) {
            params += '&condition' + i + 'field=' + encodeURIComponent(args.conditions[i].field);
            params += '&condition' + i + 'operator=' + encodeURIComponent(args.conditions[i].operator);
            params += '&condition' + i + 'value=' + encodeURIComponent(args.conditions[i].value);
        }
        if (args.associated) {
            for (i = 0; i < args.associated.length; i += 1) {
                params += '&assoc' + i + '=' + encodeURIComponent(args.associated[i].name);
                params += '&asfield' + i + '=' + encodeURIComponent(args.associated[i].field);
                params += '&asvalue' + i + '=' + encodeURIComponent(args.associated[i].value);
            }
        }
        return params;
    },

    createExceptionFunc: function (errMessageNumber, AuthProc) {
        'use strict';
        var errorNumCapt = errMessageNumber;
        return function (myRequest) {
            if (INTERMediatorOnPage.requireAuthentication) {
                if (!INTERMediatorOnPage.isComplementAuthData()) {
                    INTERMediatorOnPage.clearCredentials();
                    INTERMediatorOnPage.authenticating(AuthProc);
                }
            } else {
                INTERMediatorLog.setErrorMessage('Communication Error',
                    INTERMediatorLib.getInsertedString(
                        INTERMediatorOnPage.getMessages()[errorNumCapt],
                        ['Communication Error', myRequest.responseText]));
            }
        };
    },

    unregister: function (entityPkInfo) {
        'use strict';
        var result = null, params;
        if (INTERMediatorOnPage.clientNotificationKey) {
            var appKey = INTERMediatorOnPage.clientNotificationKey();
            if (appKey && appKey !== '_im_key_isnt_supplied') {
                params = 'access=unregister';
                if (entityPkInfo) {
                    params += '&pks=' + encodeURIComponent(JSON.stringify(entityPkInfo));
                }
                result = this.server_access(params, 1018, 1016);
                return result;
            }
        }
    }
};
