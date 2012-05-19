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

    server_access:function (accessURL, debugMessageNumber, errorMessageNumber) {

        var appPath = INTERMediatorOnPage.getEntryPath();
        var authParams = '';
        if (INTERMediatorOnPage.authUser.length > 0) {
            authParams
                = "&clientid=" + encodeURIComponent(INTERMediatorOnPage.clientId)
                + "&authuser=" + encodeURIComponent(INTERMediatorOnPage.authUser);
            if (INTERMediatorOnPage.isNativeAuth) {
                authParams += "&response=" + encodeURIComponent(
                    INTERMediatorOnPage.publickey.biEncryptedString(INTERMediatorOnPage.authHashedPassword
                        + "\n" + INTERMediatorOnPage.authChallenge));
            } else {
                authParams += "&response=" + encodeURIComponent(
                    SHA1(INTERMediatorOnPage.authChallenge + INTERMediatorOnPage.authHashedPassword));
            }
        }

        INTERMediator.debugMessages.push(
            INTERMediatorOnPage.getMessages()[debugMessageNumber]
                + "Accessing:" + decodeURI(appPath) + ", Parameters:" + decodeURI(accessURL + authParams));

        var newRecordKeyValue = '';
        var dbresult = '';
        var resultCount = 0;
        var challenge = null;
        var clientid = null;
        var requireAuth = false;
        var myRequest = null;
        try {
            myRequest = new XMLHttpRequest();
            myRequest.open('POST', appPath, false);
            myRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            myRequest.send(accessURL + authParams);
            eval(myRequest.responseText);
            if (INTERMediator.debugMode > 1) {
                INTERMediator.debugMessages.push("myRequest.responseText=" + myRequest.responseText);
                INTERMediator.debugMessages.push("Return: resultCount=" + resultCount
                    + ", dbresult=" + INTERMediatorLib.objectToString(dbresult) + "\n"
                    + "Return: requireAuth=" + requireAuth
                    + ", challenge=" + challenge + ", clientid=" + clientid + "\n"
                    + "Return: newRecordKeyValue=" + newRecordKeyValue);
            }
            if (challenge !== null) {
                INTERMediatorOnPage.authChallenge = challenge.substr(0, 24);
                //    if ( ! INTERMediatorOnPage.isNativeAuth ) {
                INTERMediatorOnPage.authUserHexSalt = challenge.substr(24, 32);
                INTERMediatorOnPage.authUserSalt = String.fromCharCode(
                    parseInt(challenge.substr(24, 2), 16),
                    parseInt(challenge.substr(26, 2), 16),
                    parseInt(challenge.substr(28, 2), 16),
                    parseInt(challenge.substr(30, 2), 16));
                //    }
            }
            if (clientid !== null) {
                INTERMediatorOnPage.clientId = clientid;
            }

        } catch (e) {

            INTERMediator.errorMessages.push(
                INTERMediatorLib.getInsertedString(
                    INTERMediatorOnPage.getMessages()[errorMessageNumber], [e, myRequest.responseText]));

        }
        if (requireAuth) {
            INTERMediator.debugMessages.push("Authentication Required, user/password panel should be show.");
            INTERMediatorOnPage.authHashedPassword = null;
            throw "_im_requath_request_"
        }
        if (!accessURL.match(/access=challenge/)) {
            INTERMediatorOnPage.authCount = 0;
        }
        INTERMediatorOnPage.storeCredencialsToCookie();
        return {dbresult:dbresult, resultCount:resultCount, newRecordKeyValue:newRecordKeyValue};
    },

    getChallenge:function () {
        try {
            this.server_access("access=challenge", 1027, 1028);
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                throw ex;
            }
        }
        if (INTERMediatorOnPage.authChallenge == null) {
            return false;
        }
        return true;
    },

    /*
     db_query
     Querying from database. The parameter of this function should be the object as below:

     {   name:<name of the context>
     records:<the number of retrieving records, could be null>
     fields:<the array of fields to retrieve, but this parameter is ignored so far.
     parentkeyvalue:<the value of foreign key field, could be null>
     conditions:<the array of the object {field:xx,operator:xx,value:xx} to search records, could be null>
     useoffset:<true/false whether the offset parameter is set on the query.>    }

     This function returns recordset of retrieved.
     */
    db_query:function (args) {
        var noError = true;
        if (args['name'] == null) {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1005));
            noError = false;
        }
        if (!noError) {
            return;
        }

        var params = "access=select&name=" + encodeURIComponent(args['name']);
        params += "&records=" + encodeURIComponent((args['records'] != null) ? args['records'] : 10000000);
        for (var i = 0; i < args['fields'].length; i++) {
            params += "&field_" + i + "=" + encodeURIComponent(args['fields'][i]);
        }
        var counter = 0;
        if (args['parentkeyvalue'] != null) {
            //noinspection JSDuplicatedDeclaration
            for (var index in args['parentkeyvalue']) {
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
        var extCount = 0;
        if (args['conditions'] != null) {
            params += "&condition" + extCount + "field=" + encodeURIComponent(args['conditions'][extCount]['field']);
            params += "&condition" + extCount + "operator=" + encodeURIComponent(args['conditions'][extCount]['operator']);
            params += "&condition" + extCount + "value=" + encodeURIComponent(args['conditions'][extCount]['value']);
            extCount++;
        }
        var criteriaObject = INTERMediator.additionalCondition[args['name']];
        if (criteriaObject != null && criteriaObject["field"] != null) {
            criteriaObject = [criteriaObject];
        }
        for (var index in criteriaObject) {
            if (criteriaObject.hasOwnProperty(index)) {
                params += "&condition" + extCount + "field=" + encodeURIComponent(criteriaObject[index]["field"]);
                if (criteriaObject[index]["operator"] != null) {
                    params += "&condition" + extCount + "operator=" + encodeURIComponent(criteriaObject[index]["operator"]);
                }
                params += "&condition" + extCount + "value=" + encodeURIComponent(criteriaObject[index]["value"]);
                extCount++;
            }
        }

        extCount = 0;
        var sortkeyObject = INTERMediator.additionalSortKey[args['name']];
        if (sortkeyObject != null && sortkeyObject["field"] != null) {
            sortkeyObject = [sortkeyObject];
        }
        for (var index in sortkeyObject) {
            params += "&sortkey" + extCount + "field=" + encodeURIComponent(sortkeyObject[index]["field"]);
            params += "&sortkey" + extCount + "direction=" + encodeURIComponent(sortkeyObject[index]["direction"]);
            extCount++;
        }

        params += "&randkey" + Math.random();    // For ie...
        // IE uses caches as the result in spite of several headers. So URL should be randomly.
        var returnValue = {};
        try {
            var result = this.server_access(params, 1012, 1004);
            returnValue.recordset = result.dbresult;
            returnValue.totalCount = result.resultCount;
            returnValue.count = 0;
            for (var ix in result.dbresult) {
                returnValue.count++;
            }
            if (( args['paging'] != null) && ( args['paging'] == true )) {
                INTERMediator.pagedSize = args['records'];
                INTERMediator.pagedAllCount = result.resultCount;
            }
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                throw ex;
            }
            returnValue.recordset = null;
            returnValue.totalCount = 0;
            returnValue.count = 0;
        }
        return returnValue;
    },

    db_queryWithAuth:function (args, completion) {
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
            }
        }
        completion();
    },

    /*
     db_update
     Update the database. The parameter of this function should be the object as below:

     {   name:<Name of the Context>
     conditions:<the array of the object {field:xx,operator:xx,value:xx} to search records>
     dataset:<the array of the object {field:xx,value:xx}. each value will be set to the field.> }
     */
    db_update:function (args) {
        var noError = true;
        if (args['name'] == null) {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1007));
            noError = false;
        }
        if (args['conditions'] == null) {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1008));
            noError = false;
        }
        if (args['dataset'] == null) {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1011));
            noError = false;
        }
        if (!noError) {
            return;
        }

        var params = "access=update&name=" + encodeURIComponent(args['name']);
        var extCount = 0;
        if (args['conditions'] != null) {
            params += "&condition" + extCount + "field=" + encodeURIComponent(args['conditions'][extCount]['field']);
            params += "&condition" + extCount + "operator=" + encodeURIComponent(args['conditions'][extCount]['operator']);
            params += "&condition" + extCount + "value=" + encodeURIComponent(args['conditions'][extCount]['value']);
            extCount++;
        }
        for (var extCount = 0; extCount < args['dataset'].length; extCount++) {
            params += "&field_" + extCount + "=" + encodeURIComponent(args['dataset'][extCount]['field']);
            params += "&value_" + extCount + "=" + encodeURIComponent(args['dataset'][extCount]['value']);
        }
        var result = this.server_access(params, 1013, 1014);
        return result.dbresult;
    },

    db_updateWithAuth:function (args, completion) {
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
            }
        }
        completion();
    },

    /*
     db_delete
     Delete the record. The parameter of this function should be the object as below:

     {   name:<Name of the Context>
     conditions:<the array of the object {field:xx,operator:xx,value:xx} to search records, could be null>}
     */
    db_delete:function (args) {
        var noError = true;
        if (args['name'] == null) {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1019));
            noError = false;
        }
        if (args['conditions'] == null) {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1020));
            noError = false;
        }
        if (!noError) {
            return;
        }

        var params = "access=delete&name=" + encodeURIComponent(args['name']);
        for (var i = 0; i < args['conditions'].length; i++) {
            params += "&condition" + i + "field=" + encodeURIComponent(args['conditions'][i]['field']);
            params += "&condition" + i + "operator=" + encodeURIComponent(args['conditions'][i]['operator']);
            params += "&condition" + i + "value=" + encodeURIComponent(args['conditions'][i]['value']);
        }
        var result = this.server_access(params, 1017, 1015);
//        INTERMediator.flushMessage();
    },

    db_deleteWithAuth:function (args, completion) {
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
            }
        }
        completion();
    },
    /*
     db_createRecord
     Create a record. The parameter of this function should be the object as below:

     {   name:<Name of the Context>
     dataset:<the array of the object {field:xx,value:xx}. Initial value for each field> }

     This function returns the value of the key field of the new record.
     */
    db_createRecord:function (args) {
        if (args['name'] == null) {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1021));
            return;
        }
        var params = "access=new&name=" + encodeURIComponent(args['name']);
        for (var i = 0; i < args['dataset'].length; i++) {
            params += "&field_" + i + "=" + encodeURIComponent(args['dataset'][i]['field']);
            params += "&value_" + i + "=" + encodeURIComponent(args['dataset'][i]['value']);
        }
        var result = this.server_access(params, 1018, 1016);
//        INTERMediator.flushMessage();
        return result.newRecordKeyValue;
    },

    db_createRecordWithAuth:function (args, completion) {
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
            }
        }
        completion();
    }
};
