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
var INTERMediaotr_DBAdapter = {

    server_access: function( accessURL, debugMessageNumber, errorMessageNumber )   {

        var appPath = INTERMediatorOnPage.getEntryPath();
        var authParams = '';
        if ( INTERMediatorOnPage.authUser.length > 0 )  {
            authParams
                = "&authuser=" + encodeURIComponent( INTERMediatorOnPage.authUser )
                + "&response=" + encodeURIComponent( SHA1(INTERMediatorOnPage.authChallenge
                + INTERMediatorOnPage.authHashedPassword ));
        }

        INTERMediator.debugMessages.push(
            INTERMediatorOnPage.getMessages()[debugMessageNumber] + decodeURI(appPath + accessURL + authParams));

        var newRecordKeyValue = '';
        var dbresult = '';
        var resultCount = 0;
        var challenge = null;
        var requireAuth = false;
        try {
            myRequest = new XMLHttpRequest();
            myRequest.open('GET', appPath + accessURL + authParams, false);
            myRequest.send(null);
            INTERMediator.debugMessages.push("myRequest.responseText="+myRequest.responseText);

            eval( myRequest.responseText );
            if ( challenge != null )    {
                INTERMediatorOnPage.authChallenge = challenge.substr(0, 24);
                INTERMediatorOnPage.authUserHexSalt = challenge.substr(24, 32);
                INTERMediatorOnPage.authUserSalt =String.fromCharCode(
                    parseInt(challenge.substr(24, 2),16),
                    parseInt(challenge.substr(26, 2),16),
                    parseInt(challenge.substr(28, 2),16),
                    parseInt(challenge.substr(30, 2),16));
            }
        } catch (e) {

            INTERMediator.errorMessages.push(
                INTERMediatorLib.getInsertedString(
                    INTERMediatorOnPage.getMessages()[errorMessageNumber], [e, myRequest.responseText]));

        }
        if ( requireAuth )  {
            //INTERMediator.debugMessages.push("requiredAuth == true");
            INTERMediatorOnPage.authHashedPassword = null;
            throw "_im_requath_request_"
        }
        if ( ! accessURL.match(/access=challenge/) )  {
            INTERMediatorOnPage.authCount = 0;
        }
        INTERMediatorOnPage.storeCredencialsToCookie();
        return {dbresult: dbresult, resultCount: resultCount, newRecordKeyValue: newRecordKeyValue};
    },

    getChallenge: function( )  {
        try {
            this.server_access( "?access=challenge", 0, 0 );
        } catch(ex)  {
            if ( ex == "_im_requath_request_" ) {
                throw ex;
            }
        }
        if ( INTERMediatorOnPage.authChallenge == null )    {
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

        var params = "?access=select&name=" + encodeURI(args['name']);
        params += "&records=" + encodeURI((args['records'] != null) ? args['records'] : 10000000);
        for (var i = 0; i < args['fields'].length; i++) {
            params += "&field_" + i + "=" + encodeURI(args['fields'][i]);
        }
        var counter = 0;
        if (args['parentkeyvalue'] != null) {
            for (var index in  args['parentkeyvalue']) {
                params += "&foreign" + counter + "field=" + encodeURI(index);
                params += "&foreign" + counter + "value=" + encodeURI(args['parentkeyvalue'][index]);
                counter++;
            }
        }
        if (args['useoffset'] && INTERMediator.startFrom != null) {
            params += "&start=" + encodeURI(INTERMediator.startFrom);
        }
        var extCount = 0;
        if (args['conditions'] != null) {
            params += "&condition" + extCount + "field=" + encodeURI(args['conditions'][extCount]['field']);
            params += "&condition" + extCount + "operator=" + encodeURI(args['conditions'][extCount]['operator']);
            params += "&condition" + extCount + "value=" + encodeURI(args['conditions'][extCount]['value']);
            extCount++;
        }
        var criteraObject = INTERMediator.additionalCondition[args['name']];
        if (criteraObject != null && criteraObject["field"] != null) {
            criteraObject = [criteraObject];
        }
        for (var index in criteraObject) {
            params += "&condition" + extCount + "field=" + encodeURI(criteraObject[index]["field"]);
            if (criteraObject[index]["operator"] != null) {
                params += "&condition" + extCount + "operator=" + encodeURI(criteraObject[index]["operator"]);
            }
            params += "&condition" + extCount + "value=" + encodeURI(criteraObject[index]["value"]);
            extCount++;
        }

        extCount = 0;
        var sortkeyObject = INTERMediator.additionalSortKey[args['name']];
        if (sortkeyObject != null && sortkeyObject["field"] != null) {
            sortkeyObject = [sortkeyObject];
        }
        for (var index in sortkeyObject) {
            params += "&sortkey" + extCount + "field=" + encodeURI(sortkeyObject[index]["field"]);
            params += "&sortkey" + extCount + "direction=" + encodeURI(sortkeyObject[index]["direction"]);
            extCount++;
        }

        params += "&randkey" + Math.random();    // For ie...
        // IE uses caches as the result in spite of several headers. So URL should be randomly.
        var returnValue = {};
        try {
            var result = this.server_access( params, 1012, 1004 );
            returnValue.recordset = result.dbresult;
            returnValue.totalCount = result.resultCount;
            returnValue.count = 0;
            for( var ix in result.dbresult )   {
                returnValue.count++;
            }
            if (( args['paging'] != null) && ( args['paging'] == true )) {
                INTERMediator.pagedSize = args['records'];
                INTERMediator.pagedAllCount = result.resultCount;
            }
        } catch(ex)  {
            if ( ex == "_im_requath_request_" ) {
                throw ex;
            }
            returnValue.recordset = null;
            returnValue.totalCount = 0;
            returnValue.count = 0;
        }
        return returnValue;
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

        var params = "?access=update&name=" + encodeURI(args['name']);
        var extCount = 0;
        if (args['conditions'] != null) {
            params += "&condition" + extCount + "field=" + encodeURI(args['conditions'][extCount]['field']);
            params += "&condition" + extCount + "operator=" + encodeURI(args['conditions'][extCount]['operator']);
            params += "&condition" + extCount + "value=" + encodeURI(args['conditions'][extCount]['value']);
            extCount++;
        }
        for (var extCount = 0; extCount < args['dataset'].length; extCount++) {
            params += "&field_" + extCount + "=" + encodeURI(args['dataset'][extCount]['field']);
            params += "&value_" + extCount + "=" + encodeURI(args['dataset'][extCount]['value']);
        }
        var result = this.server_access( params, 1013, 1014 );
        return result.dbresult;
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

        var params = "?access=delete&name=" + encodeURI(args['name']);
        for (var i = 0; i < args['conditions'].length; i++) {
            params += "&condition" + i + "field=" + encodeURI(args['conditions'][i]['field']);
            params += "&condition" + i + "operator=" + encodeURI(args['conditions'][i]['operator']);
            params += "&condition" + i + "value=" + encodeURI(args['conditions'][i]['value']);
        }
        var result = this.server_access( params, 1017, 1015 );
//        INTERMediator.flushMessage();
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
        var params = "?access=insert&name=" + encodeURI(args['name']);
        for (var i = 0; i < args['dataset'].length; i++) {
            params += "&field_" + i + "=" + encodeURI(args['dataset'][i]['field']);
            params += "&value_" + i + "=" + encodeURI(args['dataset'][i]['value']);
        }
        var result = this.server_access( params, 1018, 1016 );
//        INTERMediator.flushMessage();
        return result.newRecordKeyValue;
    }
};
