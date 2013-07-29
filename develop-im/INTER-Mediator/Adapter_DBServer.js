/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
/*==================================================
 Database Access Object for Server-based Database
 ==================================================*/
var INTERMediator_DBAdapter;

INTERMediator_DBAdapter = {

    generate_authParams: function () {
        var authParams = '', shaObj, hmacValue;
        if (INTERMediatorOnPage.authUser.length > 0) {
            authParams
                = "&clientid=" + encodeURIComponent(INTERMediatorOnPage.clientId)
                + "&authuser=" + encodeURIComponent(INTERMediatorOnPage.authUser);
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
//                authParams += "&response=" + encodeURIComponent(
//                    SHA1(INTERMediatorOnPage.authChallenge + INTERMediatorOnPage.authHashedPassword));
        }
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

    logging_comResult: function (myRequest, resultCount, dbresult, requireAuth,
                                 challenge, clientid, newRecordKeyValue, changePasswordResult, mediatoken) {
        var responseTextTrancated;
        if (INTERMediator.debugMode > 1) {
            if (myRequest.responseText.length > 1000)   {
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
        var newRecordKeyValue = '', dbresult = '', resultCount = 0, challenge = null,
            clientid = null, requireAuth = false, myRequest = null, changePasswordResult = null,
            mediatoken = null, appPath, authParams;
        appPath = INTERMediatorOnPage.getEntryPath();
        authParams = INTERMediator_DBAdapter.generate_authParams();
        INTERMediator_DBAdapter.logging_comAction(debugMessageNumber, appPath, accessURL, authParams)
        try {
            myRequest = new XMLHttpRequest();
            myRequest.open('POST', appPath, false, INTERMediatorOnPage.httpuser, INTERMediatorOnPage.httppasswd);
            myRequest.setRequestHeader("charset", "utf-8");
            myRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            myRequest.send(accessURL + authParams);
            eval(myRequest.responseText);
            INTERMediator_DBAdapter.logging_comResult(myRequest, resultCount, dbresult, requireAuth,
                challenge, clientid, newRecordKeyValue, changePasswordResult, mediatoken);
            INTERMediator_DBAdapter.store_challenge(challenge);
            if (clientid !== null) {
                INTERMediatorOnPage.clientId = clientid;
            }
            if (mediatoken !== null) {
                INTERMediatorOnPage.mediaToken = mediatoken
            }
        } catch (e) {

            INTERMediator.setErrorMessage(e,
                INTERMediatorLib.getInsertedString(
                    INTERMediatorOnPage.getMessages()[errorMessageNumber], [e, myRequest.responseText]));

        }
        if (requireAuth) {
            INTERMediator.setDebugMessage("Authentication Required, user/password panel should be show.");
            INTERMediatorOnPage.authHashedPassword = null;
            throw "_im_requath_request_"
        }
        if (!accessURL.match(/access=challenge/)) {
            INTERMediatorOnPage.authCount = 0;
        }
        INTERMediatorOnPage.storeCredencialsToCookie();
        return {dbresult: dbresult,
            resultCount: resultCount,
            newRecordKeyValue: newRecordKeyValue,
            newPasswordResult: changePasswordResult};
    },

    changePassowrd: function (username, oldpassword, newpassword) {
        INTERMediatorOnPage.authUser = username;
        if (username != ''    // No usename and no challenge, get a challenge.
            && (INTERMediatorOnPage.authChallenge == null || INTERMediatorOnPage.authChallenge.length < 24 )) {
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
        params = "access=changepassword&newpass=" + INTERMediatorLib.generatePasswordHash(newpassword);
        try {
            result = INTERMediator_DBAdapter.server_access(params, 1029, 1030);
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
        if (INTERMediatorOnPage.authChallenge == null) {
            return false;
        }
        return true;
    },

    uploadFile: function (parameters, uploadingFile, doItOnFinish) {
        var newRecordKeyValue = '', dbresult = '', resultCount = 0, challenge = null,
            clientid = null, requireAuth = false, myRequest = null, changePasswordResult = null,
            mediatoken = null, appPath, authParams, accessURL;
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
                        eval(myRequest.responseText);
                        INTERMediator_DBAdapter.logging_comResult(myRequest, resultCount, dbresult, requireAuth,
                            challenge, clientid, newRecordKeyValue, changePasswordResult, mediatoken);
                        INTERMediator_DBAdapter.store_challenge(challenge);
                        if (clientid !== null) {
                            INTERMediatorOnPage.clientId = clientid;
                        }
                        if (mediatoken !== null) {
                            INTERMediatorOnPage.mediaToken = mediatoken
                        }
                        if (requireAuth) {
                            INTERMediator.setDebugMessage("Authentication Required, user/password panel should be show.");
                            INTERMediatorOnPage.authHashedPassword = null;
                            throw "_im_requath_request_"
                        }
                        INTERMediatorOnPage.authCount = 0;
                        INTERMediatorOnPage.storeCredencialsToCookie();
                        doItOnFinish();
                        break;
                }
            }
            myRequest.send(fd);
        } catch (e) {
            INTERMediator.setErrorMessage( e,
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
     primaryKeyOnly: true/false
     }

     This function returns recordset of retrieved.
     */
    db_query: function (args) {
        var noError = true, i, index, params, counter, extCount, criteriaObject, sortkeyObject,
            returnValue, result, ix;

        if (args['name'] == null) {
            INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1005));
            noError = false;
        }
        if (!noError) {
            return;
        }

        params = "access=select&name=" + encodeURIComponent(args['name']);
        params += "&records=" + encodeURIComponent(args['records'] ? args['records'] : 10000000);

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
        if (args['conditions']) {
            params += "&condition" + extCount + "field=" + encodeURIComponent(args['conditions'][extCount]['field']);
            params += "&condition" + extCount + "operator=" + encodeURIComponent(args['conditions'][extCount]['operator']);
            params += "&condition" + extCount + "value=" + encodeURIComponent(args['conditions'][extCount]['value']);
            extCount++;
        }
        criteriaObject = INTERMediator.additionalCondition[args['name']];
        if (criteriaObject) {
            if (criteriaObject["field"]) {
                criteriaObject = [criteriaObject];
            }
            for (index in criteriaObject) {
                if (criteriaObject.hasOwnProperty(index)) {
                    params += "&condition" + extCount + "field=" + encodeURIComponent(criteriaObject[index]["field"]);
                    if (criteriaObject[index]["operator"] !== undefined) {
                        params += "&condition" + extCount + "operator=" + encodeURIComponent(criteriaObject[index]["operator"]);
                    }
                    if (criteriaObject[index]["value"] !== undefined) {
                        params += "&condition" + extCount + "value=" + encodeURIComponent(criteriaObject[index]["value"]);
                    }
                    extCount++;
                }
            }
        }

        extCount = 0;
        sortkeyObject = INTERMediator.additionalSortKey[args['name']];
        if (sortkeyObject) {
            if (sortkeyObject["field"]) {
                sortkeyObject = [sortkeyObject];
            }
            for (index in sortkeyObject) {
                params += "&sortkey" + extCount + "field=" + encodeURIComponent(sortkeyObject[index]["field"]);
                params += "&sortkey" + extCount + "direction=" + encodeURIComponent(sortkeyObject[index]["direction"]);
                extCount++;
            }
        }

        params += "&randkey" + Math.random();    // For ie...
        // IE uses caches as the result in spite of several headers. So URL should be randomly.
        returnValue = {};
        try {
            result = this.server_access(params, 1012, 1004);
            returnValue.recordset = result.dbresult;
            returnValue.totalCount = result.resultCount;
            returnValue.count = 0;
            for (ix in result.dbresult) {
                returnValue.count++;
            }
            if (( args['paging'] != null) && ( args['paging'] == true )) {
                INTERMediator.pagedSize = args['records'];
                INTERMediator.pagedAllCount = result.resultCount;
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
        var noError = true, params, extCount, result;

        if (args['name'] == null) {
            INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1007));
            noError = false;
        }
//        if (args['conditions'] == null) {
//            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1008));
//            noError = false;
//        }
        if (args['dataset'] == null) {
            INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1011));
            noError = false;
        }
        if (!noError) {
            return;
        }

        params = "access=update&name=" + encodeURIComponent(args['name']);
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
            params += "&field_" + extCount + "=" + encodeURIComponent(args['dataset'][extCount]['field']);
            params += "&value_" + extCount + "=" + encodeURIComponent(args['dataset'][extCount]['value']);
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
        var noError = true, params, i, result;

        if (args['name'] == null) {
            INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1019));
            noError = false;
        }
        if (args['conditions'] == null) {
            INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1020));
            noError = false;
        }
        if (!noError) {
            return;
        }

        params = "access=delete&name=" + encodeURIComponent(args['name']);
        for (i = 0; i < args['conditions'].length; i++) {
            params += "&condition" + i + "field=" + encodeURIComponent(args['conditions'][i]['field']);
            params += "&condition" + i + "operator=" + encodeURIComponent(args['conditions'][i]['operator']);
            params += "&condition" + i + "value=" + encodeURIComponent(args['conditions'][i]['value']);
        }
        result = this.server_access(params, 1017, 1015);
        return result;
//        INTERMediator.flushMessage();
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
        var params, i, result, index;

        if (args['name'] == null) {
            INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1021));
            return;
        }

        ds = INTERMediatorOnPage.getDataSources(); // Get DataSource parameters
        var targetKey = null;
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
        var counter = 0;

//        for (index in ds[targetKey]['default-values']) {
//            params += "&field_" + counter + "="
//                + encodeURIComponent(ds[targetKey]['default-values'][index]['field']);
//            params += "&value_" + counter + "="
//                + encodeURIComponent(ds[targetKey]['default-values'][index]['value']);
//            counter++;
//        }
//
        for (i = 0; i < INTERMediator.additionalFieldValueOnNewRecord.length; i++) {
            var oneDefinition = INTERMediator.additionalFieldValueOnNewRecord[i];
            params += "&field_" + counter + "=" + encodeURIComponent(oneDefinition['field']);
            params += "&value_" + counter + "=" + encodeURIComponent(oneDefinition['value']);
            counter++;
        }

        for (i = 0; i < args['dataset'].length; i++) {
            params += "&field_" + counter + "=" + encodeURIComponent(args['dataset'][i]['field']);
            params += "&value_" + counter + "=" + encodeURIComponent(args['dataset'][i]['value']);
            counter++;
        }
        result = this.server_access(params, 1018, 1016);
//        INTERMediator.flushMessage();
        return result.newRecordKeyValue;
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
            completion(returnValue);
        }
    }
};
