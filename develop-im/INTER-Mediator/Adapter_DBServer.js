/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
var IM_DBAdapter = {
    //=================================
    // Database Access
    //=================================
    /*
    db_query
    Querying from database. The parameter of this function should be the object as below:

        {   name:<name of the definition, require when the extracondition is set>
          x  table:<table to access>
            records:<the number of retrieving records, could be null>
            fields:<the array of fields to retrieve, but this parameter is ignored so far.
            parentkeyvalue:<the value of foreign key field, could be null>
          x  conditions:<the array of the object {field:xx,operator:xx,value:xx}, could be null>
            useoffset:<true/false whether the offset parameter is set on the query.>    }
     */
	db_query: function (args) {
        var noError = true;
        if (args['name'] == null && args['conditions'] != null)   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1005));
            noError = false;
        }
        if ( ! noError )    {
            return;
        }

		var params = "?access=select&name=" + encodeURI(args['name']);
		params += "&records=" + encodeURI((args['records'] != null) ? args['records'] : 10000000);
		for (var i = 0; i < args['fields'].length; i++) {
			params += "&field_" + i + "=" + encodeURI(args['fields'][i]);
		}
        if ( args['parentkeyvalue'] != null) {
            params += "&parent_keyval=" + encodeURI(args['parentkeyvalue']);
        }
        if ( args['useoffset'] && INTERMediator.startFrom != null ) {
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
        if ( criteraObject != null && criteraObject["field"] != null )   {
            criteraObject = [criteraObject];
        }
        for ( var index in criteraObject )  {
            params += "&condition" + extCount + "field=" + encodeURI(criteraObject[index]["field"]);
            if ( criteraObject[index]["operator"] != null )    {
                params += "&condition" + extCount + "operator=" + encodeURI(criteraObject[index]["operator"]);
            }
            params += "&condition" + extCount + "value=" + encodeURI(criteraObject[index]["value"]);
            extCount++;
        }

        params += "&randkey" + Math.random();    // For ie...
            // IE uses caches as the result in spite of several headers. So URL should be randomly.
		var appPath = IM_getEntryPath();

        INTERMediator.debugMessages.push( IM_getMessages()[1012] + decodeURI(appPath + params) );
        var dbresult = '';
		myRequest = new XMLHttpRequest();
		try {
			myRequest.open('GET', appPath + params, false);
			myRequest.send(null);
			eval(myRequest.responseText);
            if (( args['paging'] != null) && ( args['paging'] == true ))  {
                INTERMediator.pagedSize = args['records'];
                INTERMediator.pagedAllCount = resultCount;
            }

		} catch (e) {
			INTERMediator.errorMessages.push(
                INTERMediatorLib.getInsertedString(IM_getMessages()[1004],[e,myRequest.responseText]));
		}
		return dbresult;
	},

    /*
    db_update
    Update the database. The parameter of this function should be the object as below:

        {   table:<table to access>->name
            key:<search criteria's field name>
            operator:<search criteria's operator>
            value:<search criteria's value>--> conditions
            dataset:<the array of the object {field:xx,value:xx}. each value will be set to the field.> }
     */
	db_update: function ( args ) {
        var noError = true;
        if (args['name'] == null )   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1007));
            noError = false;
        }
        if (args['conditions'] == null)   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1008));
            noError = false;
        }
        if (args['dataset'] == null)   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1011));
            noError = false;
        }
        if ( ! noError )    {
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
        for ( var extCount = 0; extCount < args['dataset'].length ; extCount++) {
		    params += "&field_" + extCount +"=" + encodeURI(args['dataset'][extCount]['field']);
		    params += "&value_" + extCount +"=" + encodeURI(args['dataset'][extCount]['value']);
        }
        var appPath = IM_getEntryPath();
		INTERMediator.debugMessages.push(IM_getMessages()[1013] + decodeURI(appPath + params));

		myRequest = new XMLHttpRequest();
		try {
			myRequest.open('GET', appPath + params, false);
			// myRequest.setRequestHeader('Content-Type','application/x-www-form-urlencoded;
			// charset=UTF-8');
			myRequest.send(null);
			var dbresult = '';
			eval(myRequest.responseText);
		} catch (e) {
            INTERMediator.errorMessages.push(
                INTERMediatorLib.getInsertedString(IM_getMessages()[1014],[e,myRequest.responseText]));
		}
		return dbresult;
	},

    /*
    db_delete
    Delete the record. The parameter of this function should be the object as below:

        {   table:<table to access>->name
            dataset:<the array of the object {field:xx,**operator:XX**,value:xx}. The criteria of the deleting records>
             ->conditions}
     */
    db_delete: function( args )   {
        var noError = true;
        if (args['name'] == null )   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1019));
            noError = false;
        }
        if (args['conditions'] == null)   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1020));
            noError = false;
        }
        if ( ! noError )    {
            return;
        }

        var params = "?access=delete&name=" + encodeURI(args['name']);
        for ( var i = 0 ; i < args['conditions'].length ; i++ )    {
            params += "&condition" + i + "field=" + encodeURI(args['conditions'][i]['field']);
            params += "&condition" + i + "operator=" + encodeURI(args['conditions'][i]['operator']);
            params += "&condition" + i + "value=" + encodeURI(args['conditions'][i]['value']);
        }
        var appPath = IM_getEntryPath();
        INTERMediator.debugMessages.push(IM_getMessages()[1017] + decodeURI(appPath + params));
        myRequest = new XMLHttpRequest();
        try {
            myRequest.open('GET', appPath + params, false);
            myRequest.send(null);
            var dbresult = '';
            eval(myRequest.responseText);
        } catch (e) {
            INTERMediator.errorMessages.push(
                INTERMediatorLib.getInsertedString(IM_getMessages()[1015],[e,myRequest.responseText]));
        }
        INTERMediator.flushMessage();
    },

    /*
    db_createRecord
    Create a record. The parameter of this function should be the object as below:

        {   table:<table to access>->name
            dataset:<the array of the object {field:xx,value:xx}. Initial value for each field> }

    This function returns the value of the key field of the new record.
     */
    db_createRecord: function( args ) {
        if (args['name'] == null )   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1021));
            return;
        }
        var params = "?access=insert&name=" + encodeURI(args['name']);
        for ( var i = 0 ; i < args['dataset'].length ; i++ )    {
            params += "&field_" + i + "=" + encodeURI(args['dataset'][i]['field']);
            params += "&value_" + i + "=" + encodeURI(args['dataset'][i]['value']);
        }
        var appPath = IM_getEntryPath();

        var newRecordKeyValue = '';
        INTERMediator.debugMessages.push(IM_getMessages()[1018] + decodeURI(appPath + params));
        myRequest = new XMLHttpRequest();
        try {
            myRequest.open('GET', appPath + params, false);
            myRequest.send(null);
            eval(myRequest.responseText);
        } catch (e) {
            INTERMediator.errorMessages.push(
                INTERMediatorLib.getInsertedString(IM_getMessages()[1016],[e,myRequest.responseText]));
        }
        INTERMediator.flushMessage();
        return newRecordKeyValue;
    }

}
