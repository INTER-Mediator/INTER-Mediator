/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */

/*==================================================
 Database Access Object for Server-based Database
 ==================================================*/

//"use strict"

var INTERMediator_DBAdapter;

INTERMediator_DBAdapter = {

    generate_authParams: function () {
        var authParams = '', shaObj, hmacValue;
        if (INTERMediatorOnPage.authUser.length > 0) {
            authParams = "&clientid=" + encodeURIComponent(INTERMediatorOnPage.clientId);
            authParams += "&authuser=" + encodeURIComponent(INTERMediatorOnPage.authUser);
            if (INTERMediatorOnPage.isNativeAuth) {
                authParams += "&response=" + encodeURIComponent(
                    INTERMediatorOnPage.publickey.biEncryptedString(INTERMediatorOnPage.authHashedPassword
                    + "\n" + INTERMediatorOnPage.authChallenge));
            } else {
                if (INTERMediatorOnPage.authHashedPassword && INTERMediatorOnPage.authChallenge) {
                    shaObj = new jsSHA(INTERMediatorOnPage.authHashedPassword, "ASCII");
                    hmacValue = shaObj.getHMAC(INTERMediatorOnPage.authChallenge, "ASCII", "SHA-256", "HEX");
                    authParams += "&response=" + encodeURIComponent(hmacValue);
                } else {
                    authParams += "&response=dummy";
                }
            }
        }

        authParams += "&notifyid=";
        authParams += encodeURIComponent(INTERMediatorOnPage.clientNotificationIdentifier());
        authParams += ("&pusher=" + (INTERMediator.pusherAvailable ? "yes" : ""));
        return authParams;
    },

    store_challenge: function (challenge) {
        if (challenge !== null) {
            INTERMediatorOnPage.authChallenge = challenge.substr(0, 24);
            INTERMediatorOnPage.authUserHexSalt = challenge.substr(24, 32);
            INTERMediatorOnPage.authUserSalt = String.fromCharCode(
                parseInt(challenge.substr(24, 2), 16),
                parseInt(challenge.substr(26, 2), 16),
                parseInt(challenge.substr(28, 2), 16),
                parseInt(challenge.substr(30, 2), 16));
        }
    },

    logging_comAction: function (debugMessageNumber, appPath, accessURL, authParams) {
        INTERMediator.setDebugMessage(
            INTERMediatorOnPage.getMessages()[debugMessageNumber]
            + "Accessing:" + decodeURI(appPath) + ", Parameters:" + decodeURI(accessURL + authParams));
    },

    logging_comResult: function (myRequest, resultCount, dbresult, requireAuth, challenge, clientid, newRecordKeyValue, changePasswordResult, mediatoken) {
        var responseTextTrancated;
        if (INTERMediator.debugMode > 1) {
            if (myRequest.responseText.length > 1000) {
                responseTextTrancated = myRequest.responseText.substr(0, 1000) + " ...[trancated]";
            } else {
                responseTextTrancated = myRequest.responseText;
            }
            INTERMediator.setDebugMessage("myRequest.responseText=" + responseTextTrancated);
            INTERMediator.setDebugMessage("Return: resultCount=" + resultCount
                + ", dbresult=" + INTERMediatorLib.objectToString(dbresult) + "\n"
                + "Return: requireAuth=" + requireAuth
                + ", challenge=" + challenge + ", clientid=" + clientid + "\n"
                + "Return: newRecordKeyValue=" + newRecordKeyValue
                + ", changePasswordResult=" + changePasswordResult + ", mediatoken=" + mediatoken
            );
        }
    },
    server_access: function (accessURL, debugMessageNumber, errorMessageNumber) {
        var newRecordKeyValue = '', dbresult = '', resultCount = 0, totalCount = null, challenge = null,
            clientid = null, requireAuth = false, myRequest = null, changePasswordResult = null,
            mediatoken = null, appPath, authParams, jsonObject, i, notifySupport = false, useNull = false,
            registeredID = "";
        appPath = INTERMediatorOnPage.getEntryPath();
        authParams = INTERMediator_DBAdapter.generate_authParams();
        INTERMediator_DBAdapter.logging_comAction(debugMessageNumber, appPath, accessURL, authParams);
        INTERMediatorOnPage.notifySupport = notifySupport;
        try {
            myRequest = new XMLHttpRequest();
            myRequest.open('POST', appPath, false, INTERMediatorOnPage.httpuser, INTERMediatorOnPage.httppasswd);
            myRequest.setRequestHeader("charset", "utf-8");
            myRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            myRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            myRequest.send(accessURL + authParams);
            jsonObject = JSON.parse(myRequest.responseText);
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
            for (i = 0; i < jsonObject.errorMessages.length; i++) {
                INTERMediator.setErrorMessage(jsonObject.errorMessages[i]);
            }
            for (i = 0; i < jsonObject.debugMessages.length; i++) {
                INTERMediator.setDebugMessage(jsonObject.debugMessages[i]);
            }
            useNull = jsonObject.usenull;
            registeredID = jsonObject.hasOwnProperty('registeredid') ? jsonObject.registeredid : "";

            INTERMediator_DBAdapter.logging_comResult(myRequest, resultCount, dbresult, requireAuth,
                challenge, clientid, newRecordKeyValue, changePasswordResult, mediatoken);
            INTERMediator_DBAdapter.store_challenge(challenge);
            if (clientid !== null) {
                INTERMediatorOnPage.clientId = clientid;
            }
            if (mediatoken !== null) {
                INTERMediatorOnPage.mediaToken = mediatoken;
            }
        } catch (e) {

            INTERMediator.setErrorMessage(e,
                INTERMediatorLib.getInsertedString(
                    INTERMediatorOnPage.getMessages()[errorMessageNumber], [e, myRequest.responseText]));

        }
        if (requireAuth) {
            INTERMediator.setDebugMessage("Authentication Required, user/password panel should be show.");
            INTERMediatorOnPage.authHashedPassword = null;
            throw "_im_requath_request_";
        }
        if (!accessURL.match(/access=challenge/)) {
            INTERMediatorOnPage.authCount = 0;
        }
        INTERMediatorOnPage.storeCredencialsToCookie();
        INTERMediatorOnPage.notifySupport = notifySupport;
        return {
            dbresult: dbresult,
            resultCount: resultCount,
            totalCount: totalCount,
            newRecordKeyValue: newRecordKeyValue,
            newPasswordResult: changePasswordResult,
            registeredId: registeredID,
            nullAcceptable: useNull
        };
    },

    changePassowrd: function (username, oldpassword, newpassword) {
        var challengeResult, params, result;

        if (username && oldpassword) {
            INTERMediatorOnPage.authUser = username;
            if (username != ''    // No usename and no challenge, get a challenge.
                && (INTERMediatorOnPage.authChallenge === null || INTERMediatorOnPage.authChallenge.length < 24 )) {
                INTERMediatorOnPage.authHashedPassword = "need-hash-pls";   // Dummy Hash for getting a challenge
                challengeResult = INTERMediator_DBAdapter.getChallenge();
                if (!challengeResult) {
                    INTERMediator.flushMessage();
                    return false; // If it's failed to get a challenge, finish everything.
                }
            }
            INTERMediatorOnPage.authHashedPassword
                = SHA1(oldpassword + INTERMediatorOnPage.authUserSalt)
            + INTERMediatorOnPage.authUserHexSalt;
        } else {
            INTERMediatorOnPage.retrieveAuthInfo();
        }
        params = "access=changepassword&newpass=" + INTERMediatorLib.generatePasswordHash(newpassword);
        try {
            result = INTERMediator_DBAdapter.server_access(params, 1029, 1030);
            if (result.newPasswordResult && result.newPasswordResult === true) {
                if (INTERMediatorOnPage.isNativeAuth) {
                    INTERMediatorOnPage.authHashedPassword = INTERMediatorOnPage.publickey.biEncryptedString(newpassword);
                } else {
                    INTERMediatorOnPage.authHashedPassword
                        = SHA1(newpassword + INTERMediatorOnPage.authUserSalt)
                    + INTERMediatorOnPage.authUserHexSalt;
                }
                INTERMediatorOnPage.storeCredencialsToCookie();
            }
        } catch (e) {
            return false;
        }
        return (result.newPasswordResult && result.newPasswordResult === true);
    },

    getChallenge: function () {
        try {
            this.server_access("access=challenge", 1027, 1028);
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                throw ex;
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-19");
            }
        }
        if (INTERMediatorOnPage.authChallenge === null) {
            return false;
        }
        return true;
    },

    uploadFile: function (parameters, uploadingFile, doItOnFinish) {
        var newRecordKeyValue = '', dbresult = '', resultCount = 0, challenge = null,
            clientid = null, requireAuth = false, myRequest = null, changePasswordResult = null,
            mediatoken = null, appPath, authParams, accessURL, jsonObject, i;
        //           var result = this.server_access("access=uploadfile" + parameters, 1031, 1032, uploadingFile);
        appPath = INTERMediatorOnPage.getEntryPath();
        authParams = INTERMediator_DBAdapter.generate_authParams();
        accessURL = "access=uploadfile" + parameters;
        INTERMediator_DBAdapter.logging_comAction(1031, appPath, accessURL, authParams);
        try {
            myRequest = new XMLHttpRequest();
            myRequest.open('POST', appPath, true, INTERMediatorOnPage.httpuser, INTERMediatorOnPage.httppasswd);
            myRequest.setRequestHeader("charset", "utf-8");
            var params = (accessURL + authParams).split('&');
            var fd = new FormData();
            for (var i = 0; i < params.length; i++) {
                var valueset = params[i].split('=');
                fd.append(valueset[0], decodeURIComponent(valueset[1]));
            }
            fd.append("_im_uploadfile", uploadingFile['content']);
            myRequest.onreadystatechange = function () {
                switch (myRequest.readyState) {
                    case 3:
                        break;
                    case 4:
                        jsonObject = JSON.parse(myRequest.responseText);
                        resultCount = jsonObject.resultCount ? jsonObject.resultCount : 0;
                        dbresult = jsonObject.dbresult ? jsonObject.dbresult : null;
                        requireAuth = jsonObject.requireAuth ? jsonObject.requireAuth : false;
                        challenge = jsonObject.challenge ? jsonObject.challenge : null;
                        clientid = jsonObject.clientid ? jsonObject.clientid : null;
                        newRecordKeyValue = jsonObject.newRecordKeyValue ? jsonObject.newRecordKeyValue : '';
                        changePasswordResult = jsonObject.changePasswordResult ? jsonObject.changePasswordResult : null;
                        mediatoken = jsonObject.mediatoken ? jsonObject.mediatoken : null;
                        for (i = 0; i < jsonObject.errorMessages.length; i++) {
                            INTERMediator.setErrorMessage(jsonObject.errorMessages[i]);
                        }
                        for (i = 0; i < jsonObject.debugMessages.length; i++) {
                            INTERMediator.setDebugMessage(jsonObject.debugMessages[i]);
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
                            INTERMediator.setDebugMessage("Authentication Required, user/password panel should be show.");
                            INTERMediatorOnPage.authHashedPassword = null;
                            throw "_im_requath_request_";
                        }
                        INTERMediatorOnPage.authCount = 0;
                        INTERMediatorOnPage.storeCredencialsToCookie();
                        doItOnFinish(dbresult);
                        break;
                }
            };
            myRequest.send(fd);
        } catch (e) {
            INTERMediator.setErrorMessage(e,
                INTERMediatorLib.getInsertedString(
                    INTERMediatorOnPage.getMessages()[1032], [e, myRequest.responseText]));
        }
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
        var noError = true, i, index, params, counter, extCount, criteriaObject, sortkeyObject,
            returnValue, result, ix, extCountSort, recordLimit = 10000000;

        if (args.name === null || args.name === "") {
            INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1005));
            noError = false;
        }
        if (!noError) {
            return;
        }

        if (args['records'] == null) {
            params = "access=select&name=" + encodeURIComponent(args['name']);
        } else {
            if (Number(args.records) === 0) {
                params = "access=describe&name=" + encodeURIComponent(args['name']);
            } else {
                params = "access=select&name=" + encodeURIComponent(args['name']);
            }
            if (args['uselimit'] === true
                && Number(args.records) >= INTERMediator.pagedSize
                && Number(INTERMediator.pagedSize) > 0) {
                recordLimit = INTERMediator.pagedSize;
            } else {
                recordLimit = args['records'];
            }
        }

        if (args['primaryKeyOnly']) {
            params += "&pkeyonly=true";
        }

        if (args['fields']) {
            for (i = 0; i < args['fields'].length; i++) {
                params += "&field_" + i + "=" + encodeURIComponent(args['fields'][i]);
            }
        }
        counter = 0;
        if (args['parentkeyvalue']) {
            //noinspection JSDuplicatedDeclaration
            for (index in args['parentkeyvalue']) {
                if (args['parentkeyvalue'].hasOwnProperty(index)) {
                    params += "&foreign" + counter
                    + "field=" + encodeURIComponent(index);
                    params += "&foreign" + counter
                    + "value=" + encodeURIComponent(args['parentkeyvalue'][index]);
                    counter++;
                }
            }
        }
        if (args['useoffset'] && INTERMediator.startFrom != null) {
            params += "&start=" + encodeURIComponent(INTERMediator.startFrom);
        }
        extCount = 0;
        while (args['conditions'] && args['conditions'][extCount]) {
            params += "&condition" + extCount;
            params += "field=" + encodeURIComponent(args['conditions'][extCount]['field']);
            params += "&condition" + extCount;
            params += "operator=" + encodeURIComponent(args['conditions'][extCount]['operator']);
            params += "&condition" + extCount;
            params += "value=" + encodeURIComponent(args['conditions'][extCount]['value']);
            extCount++;
        }
        criteriaObject = INTERMediator.additionalCondition[args['name']];
        if (criteriaObject) {
            if (criteriaObject["field"]) {
                criteriaObject = [criteriaObject];
            }
            for (index = 0; index < criteriaObject.length; index++) {
                if (criteriaObject[index] && criteriaObject[index]["field"]) {
                    if (criteriaObject[index]["value"] || criteriaObject[index]["field"] == "__operation__") {
                        params += "&condition" + extCount;
                        params += "field=" + encodeURIComponent(criteriaObject[index]["field"]);
                        if (criteriaObject[index]["operator"] !== undefined) {
                            params += "&condition" + extCount;
                            params += "operator=" + encodeURIComponent(criteriaObject[index]["operator"]);
                        }
                        if (criteriaObject[index]["value"] !== undefined) {
                            params += "&condition" + extCount;
                            params += "value=" + encodeURIComponent(criteriaObject[index]["value"]);
                        }
                        extCount++;
                    }
                }

            }
        }

        extCountSort = 0;
        sortkeyObject = INTERMediator.additionalSortKey[args['name']];
        if (sortkeyObject) {
            if (sortkeyObject["field"]) {
                sortkeyObject = [sortkeyObject];
            }
            for (index = 0; index < sortkeyObject.length; index++) {
                params += "&sortkey" + extCountSort;
                params += "field=" + encodeURIComponent(sortkeyObject[index]["field"]);
                params += "&sortkey" + extCountSort;
                params += "direction=" + encodeURIComponent(sortkeyObject[index]["direction"]);
                extCountSort++;
            }

        }

        var orderFields = {};
        for (var key in IMLibLocalContext.store) {
            var value = new String(IMLibLocalContext.store[key]);
            var keyParams = key.split(":");
            if (keyParams && keyParams.length > 1 && keyParams[1].trim() == args['name'] && value.length > 0) {
                if (keyParams[0].trim() == "condition" && keyParams.length >= 4) {
                    var fields = keyParams[2].split(",");
                    var operator = keyParams[3].trim();
                    if (fields.length > 1) {
                        params += "&condition" + extCount + "field=__operation__";
                        params += "&condition" + extCount + "operator=ex";
                        extCount++;
                    }
                    for (var index = 0; index < fields.length; index++) {
                        params += "&condition" + extCount + "field=" + encodeURIComponent(fields[index].trim());
                        params += "&condition" + extCount + "operator=" + encodeURIComponent(operator);
                        params += "&condition" + extCount + "value=" + encodeURIComponent(value);
                        extCount++;
                    }
                } else if (keyParams[0].trim() == "valueofaddorder" && keyParams.length >= 4) {
                    orderFields[parseInt(value)] = [keyParams[2].trim(), keyParams[3].trim()];
                } else if (keyParams[0].trim() == "limitnumber" && keyParams.length >= 4) {
                    recordLimit = parseInt(value);
                }
            }
        }
        params += "&records=" + encodeURIComponent(recordLimit);
        var orderedKeys = Object.keys(orderFields);
        for (var i = 0; i < orderedKeys.length; i++) {
            params += "&sortkey" + extCountSort + "field=" + encodeURIComponent(orderFields[orderedKeys[i]][0]);
            params += "&sortkey" + extCountSort + "direction=" + encodeURIComponent(orderFields[orderedKeys[i]][1]);
            extCountSort++;
        }
// params += "&randkey" + Math.random();    // For ie...
// IE uses caches as the result in spite of several headers. So URL should be randomly.
//
// This is not requred because the Notification feature adds the client Identifier for each communication.
// msyk June 1, 2014
        returnValue = {};
        try {
            result = this.server_access(params, 1012, 1004);
            returnValue.recordset = result.dbresult;
            returnValue.totalCount = result.resultCount;
            returnValue.count = 0;
            returnValue.registeredId = result.registeredId;
            returnValue.nullAcceptable = result.nullAcceptable;
            for (ix in result.dbresult) {
                returnValue.count++;
            }
            if (( args['paging'] != null) && ( args['paging'] == true )) {
                if (!(Number(args['records']) >= Number(INTERMediator.pagedSize)
                    && Number(INTERMediator.pagedSize) > 0)) {
                    INTERMediator.pagedSize = Number(args['records']);
                }
                INTERMediator.pagedAllCount = Number(result.resultCount);
                if (result.totalCount) {
                    INTERMediator.totalRecordCount = parseInt(result.totalCount, 10);
                }
            }
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                throw ex;
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-17");
            }
            returnValue.recordset = null;
            returnValue.totalCount = 0;
            returnValue.count = 0;
            returnValue.registeredid = null;
            returnValue.nullAcceptable = null;
        }
        return returnValue;
    },

    db_queryWithAuth: function (args, completion) {
        var returnValue = false;
        INTERMediatorOnPage.retrieveAuthInfo();
        try {
            returnValue = INTERMediator_DBAdapter.db_query(args);
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                if (INTERMediatorOnPage.requireAuthentication) {
                    if (!INTERMediatorOnPage.isComplementAuthData()) {
                        INTERMediatorOnPage.authChallenge = null;
                        INTERMediatorOnPage.authHashedPassword = null;
                        INTERMediatorOnPage.authenticating(
                            function () {
                                returnValue = INTERMediator_DBAdapter.db_queryWithAuth(arg, completion);
                            });
                        return;
                    }
                }
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-16");
            }
        }
        completion(returnValue);
    },

    /*
     db_update
     Update the database. The parameter of this function should be the object as below:

     {   name:<Name of the Context>
     conditions:<the array of the object {field:xx,operator:xx,value:xx} to search records>
     dataset:<the array of the object {field:xx,value:xx}. each value will be set to the field.> }
     */
    db_update: function (args) {
        var noError = true, params, extCount, result, counter, index, addedObject;

        if (args['name'] === null) {
            INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1007));
            noError = false;
        }
//        if (args['conditions'] == null) {
//            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1008));
//            noError = false;
//        }
        if (args['dataset'] === null) {
            INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1011));
            noError = false;
        }
        if (!noError) {
            return;
        }

        params = "access=update&name=" + encodeURIComponent(args['name']);

        counter = 0;
        if (INTERMediator.additionalFieldValueOnUpdate
            && INTERMediator.additionalFieldValueOnUpdate[args['name']]) {
            addedObject = INTERMediator.additionalFieldValueOnUpdate[args['name']];
            if (addedObject["field"]) {
                addedObject = [addedObject];
            }
            for (index in addedObject) {
                var oneDefinition = addedObject[index];
                params += "&field_" + counter + "=" + encodeURIComponent(oneDefinition['field']);
                params += "&value_" + counter + "=" + encodeURIComponent(oneDefinition['value']);
                counter++;
            }
        }

        if (args['conditions'] != null) {
            for (extCount = 0; extCount < args['conditions'].length; extCount++) {
                params += "&condition" + extCount + "field=";
                params += encodeURIComponent(args['conditions'][extCount]['field']);
                params += "&condition" + extCount + "operator=";
                params += encodeURIComponent(args['conditions'][extCount]['operator']);
                if (args['conditions'][extCount]['value']) {
                    params += "&condition" + extCount + "value=";
                    params += encodeURIComponent(args['conditions'][extCount]['value']);
                }
            }
        }
        for (extCount = 0; extCount < args['dataset'].length; extCount++) {
            params += "&field_" + (counter + extCount) + "=" + encodeURIComponent(args['dataset'][extCount]['field']);
            if (INTERMediator.isTrident && INTERMediator.ieVersion == 8) {
                params += "&value_" + (counter + extCount) + "=" + encodeURIComponent(args['dataset'][extCount]['value'].replace(/\n/g, ""));
            } else {
                params += "&value_" + (counter + extCount) + "=" + encodeURIComponent(args['dataset'][extCount]['value']);
            }
        }
        result = this.server_access(params, 1013, 1014);
        return result.dbresult;
    },

    db_updateWithAuth: function (args, completion) {
        var returnValue = false;
        INTERMediatorOnPage.retrieveAuthInfo();
        try {
            returnValue = INTERMediator_DBAdapter.db_update(args);
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                if (INTERMediatorOnPage.requireAuthentication) {
                    if (!INTERMediatorOnPage.isComplementAuthData()) {
                        INTERMediatorOnPage.authChallenge = null;
                        INTERMediatorOnPage.authHashedPassword = null;
                        INTERMediatorOnPage.authenticating(
                            function () {
                                returnValue = INTERMediator_DBAdapter.db_updateWithAuth(arg, completion);
                            });
                        return;
                    }
                }
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-15");
            }
        }
        completion(returnValue);
    },

    /*
     db_delete
     Delete the record. The parameter of this function should be the object as below:

     {   name:<Name of the Context>
     conditions:<the array of the object {field:xx,operator:xx,value:xx} to search records, could be null>}
     */
    db_delete: function (args) {
        var noError = true, params, i, result, counter, index, addedObject;

        if (args['name'] === null) {
            INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1019));
            noError = false;
        }
        if (args['conditions'] === null) {
            INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1020));
            noError = false;
        }
        if (!noError) {
            return;
        }

        params = "access=delete&name=" + encodeURIComponent(args['name']);
        counter = 0;
        if (INTERMediator.additionalFieldValueOnDelete
            && INTERMediator.additionalFieldValueOnDelete[args['name']]) {
            addedObject = INTERMediator.additionalFieldValueOnDelete[args['name']];
            if (addedObject["field"]) {
                addedObject = [addedObject];
            }
            for (index in addedObject) {
                var oneDefinition = addedObject[index];
                params += "&field_" + counter + "=" + encodeURIComponent(oneDefinition['field']);
                params += "&value_" + counter + "=" + encodeURIComponent(oneDefinition['value']);
                counter++;
            }
        }

        for (i = 0; i < args['conditions'].length; i++) {
            params += "&condition" + i + "field=" + encodeURIComponent(args['conditions'][i]['field']);
            params += "&condition" + i + "operator=" + encodeURIComponent(args['conditions'][i]['operator']);
            params += "&condition" + i + "value=" + encodeURIComponent(args['conditions'][i]['value']);
        }
        result = this.server_access(params, 1017, 1015);
        INTERMediator.flushMessage();
        return result;
    },

    db_deleteWithAuth: function (args, completion) {
        var returnValue = false;
        INTERMediatorOnPage.retrieveAuthInfo();
        try {
            returnValue = INTERMediator_DBAdapter.db_delete(args);
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                if (INTERMediatorOnPage.requireAuthentication) {
                    if (!INTERMediatorOnPage.isComplementAuthData()) {
                        INTERMediatorOnPage.authChallenge = null;
                        INTERMediatorOnPage.authHashedPassword = null;
                        INTERMediatorOnPage.authenticating(
                            function () {
                                returnValue = INTERMediator_DBAdapter.db_deleteWithAuth(arg, completion);
                            });
                        return;
                    }
                }
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-14");
            }
        }
        completion(returnValue);
    },
    /*
     db_createRecord
     Create a record. The parameter of this function should be the object as below:

     {   name:<Name of the Context>
     dataset:<the array of the object {field:xx,value:xx}. Initial value for each field> }

     This function returns the value of the key field of the new record.
     */
    db_createRecord: function (args) {
        var params, i, result, index, addedObject, counter, targetKey, ds, key;

        if (args['name'] === null) {
            INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1021));
            return;
        }

        ds = INTERMediatorOnPage.getDataSources(); // Get DataSource parameters
        targetKey = null;
        for (key in ds) { // Search this table from DataSource
            if (ds[key]['name'] == args['name']) {
                targetKey = key;
                break;
            }
        }
        if (targetKey === null) {
            alert("no targetname :" + args['name']);
            return;
        }

        params = "access=new&name=" + encodeURIComponent(args['name']);

        counter = 0;
        if (INTERMediator.additionalFieldValueOnNewRecord
            && INTERMediator.additionalFieldValueOnNewRecord[args['name']]) {
            addedObject = INTERMediator.additionalFieldValueOnNewRecord[args['name']];
            if (addedObject["field"]) {
                addedObject = [addedObject];
            }
            for (index in addedObject) {
                var oneDefinition = addedObject[index];
                params += "&field_" + counter + "=" + encodeURIComponent(oneDefinition['field']);
                params += "&value_" + counter + "=" + encodeURIComponent(oneDefinition['value']);
                counter++;
            }
        }

        for (i = 0; i < args['dataset'].length; i++) {
            params += "&field_" + counter + "=" + encodeURIComponent(args['dataset'][i]['field']);
            params += "&value_" + counter + "=" + encodeURIComponent(args['dataset'][i]['value']);
            counter++;
        }
        result = this.server_access(params, 1018, 1016);
        INTERMediator.flushMessage();
        return {
            newKeyValue: result.newRecordKeyValue,
            recordset: result.dbresult
        };
    },

    db_createRecordWithAuth: function (args, completion) {
        var returnValue = false;
        INTERMediatorOnPage.retrieveAuthInfo();
        try {
            returnValue = INTERMediator_DBAdapter.db_createRecord(args);
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                if (INTERMediatorOnPage.requireAuthentication) {
                    if (!INTERMediatorOnPage.isComplementAuthData()) {
                        INTERMediatorOnPage.authChallenge = null;
                        INTERMediatorOnPage.authHashedPassword = null;
                        INTERMediatorOnPage.authenticating(
                            function () {
                                returnValue = INTERMediator_DBAdapter.db_createRecordWithAuth(arg, completion);
                            });
                        return;
                    }
                }
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-13");
            }
        }
        if (completion) {
            completion(returnValue.newKeyValue);
        }
    },

    unregister: function (entityPkInfo) {
        //console.log(entityPkInfo);
        var result = null, params;
        if (INTERMediatorOnPage.clientNotificationKey) {
            var appKey = INTERMediatorOnPage.clientNotificationKey();
            if (appKey && appKey != "_im_key_isnt_supplied") {
                params = "access=unregister";
                if (entityPkInfo) {
                    params += "&pks=" + encodeURIComponent(JSON.stringify(entityPkInfo));
                }
                result = this.server_access(params, 1018, 1016);
                return result;
            }
        }
    }
}
;
