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
            table:<table to access>
            records:<the number of retrieving records, could be null>
            fields:<the array of fields to retrieve, but this parameter is ignored so far.
            parentkeyvalue:<the value of foreign key field, could be null>
            extracondition:<the array of the object {field:xx,operator:xx,value:xx}, could be null>
            useoffset:<true/false whether the offset parameter is set on the query.>    }
     */
	db_query: function (args) {
        var noError = true;
        if (args['name'] == null && args['extracondition'] != null)   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1005));
            noError = false;
        }
        if (args['table'] == null)   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1006));
            noError = false;
        }
        if ( ! noError )    {
            return;
        }

		var params = "?access=select&table=" + encodeURI(args['table']);
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
		if (args['extracondition'] != null) {
            var compOfCond = args['extracondition'].split("=");
            params += "&ext_cond" + extCount + "field=" + encodeURI(compOfCond[0]);
            params += "&ext_cond" + extCount + "operator=" + encodeURI("=");
            compOfCond.shift();
            params += "&ext_cond" + extCount + "value=" + encodeURI(compOfCond.join("="));
            extCount++;
		}
        for ( var oneItem in INTERMediator.additionalCondition ) {
            if ( args['name'] == oneItem )    {
                var criteraObject = INTERMediator.additionalCondition[oneItem];
                if ( criteraObject["field"] != null )   {
                    criteraObject = [criteraObject];
                }
                for ( var index in criteraObject )  {
                    params += "&ext_cond" + extCount + "field=" + encodeURI(criteraObject[index]["field"]);
                    if ( criteraObject[index]["operator"] != null )    {
                        params += "&ext_cond" + extCount + "operator=" + encodeURI(criteraObject[index]["operator"]);
                    }
                    params += "&ext_cond" + extCount + "value=" + encodeURI(criteraObject[index]["value"]);
                    extCount++;
                }
            }
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

        {   table:<table to access>
            key:<search criteria's field name>
            operator:<search criteria's operator>
            value:<search criteria's value>
            dataset:<the array of the object {field:xx,value:xx}. each value will be set to the field.> }
     */
	db_update: function ( args ) {
        var noError = true;
        if (args['table'] == null )   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1007));
            noError = false;
        }
        if (args['key'] == null)   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1008));
            noError = false;
        }
        if (args['operator'] == null)   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1009));
            noError = false;
        }
        if (args['value'] == null)   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1010));
            noError = false;
        }
        if (args['dataset'] == null)   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1011));
            noError = false;
        }
        if ( ! noError )    {
            return;
        }
        
		var params = "?access=update&table=" + encodeURI(args['table']);
        params += "&ext_cond0field=" + encodeURI(args['key']);
        params += "&ext_cond0operator=" + encodeURI(args['operator']);
        params += "&ext_cond0value=" + encodeURI(args['value']);

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

        {   table:<table to access>
            dataset:<the array of the object {field:xx,value:xx}. The criteria of the deleting records> }
     */
    db_delete: function( args )   {
        var noError = true;
        if (args['table'] == null )   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1019));
            noError = false;
        }
        if (args['dataset'] == null)   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1020));
            noError = false;
        }
        if ( ! noError )    {
            return;
        }

        var params = "?access=delete&table=" + encodeURI(args['table']);
        for ( var i = 0 ; i < args['dataset'].length ; i++ )    {
            params += "&field_" + i + "=" + encodeURI(args['dataset'][i]['field']);
            params += "&value_" + i + "=" + encodeURI(args['dataset'][i]['value']);
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

        {   table:<table to access>
            dataset:<the array of the object {field:xx,value:xx}. Initial value for each field> }

    This function returns the value of the key field of the new record.
     */
    db_createRecord: function( args ) {
        if (args['table'] == null )   {
            INTERMediator.errorMessages.push(INTERMediatorLib.getInsertedStringFromErrorNumber(1021));
            return;
        }
        var params = "?access=insert&table=" + encodeURI(args['table']);
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
