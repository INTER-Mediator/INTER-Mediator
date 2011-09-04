/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
/**
 * Created by JetBrains PhpStorm.
 * User: nii
 * Date: 11/06/09
 * Time: 17:40
 * To change this template use File | Settings | File Templates.
 */
var IM_DBAdapter = {
    /*
    db_query
    Parameters:
        dataSource[name]:
        dataSource[records]:
        fields:
        parentKeyVal:
        extraCondition: "field,operator,value"
     */
	db_query: function (args) {
		return dbresult;
	},

    /*
    db_update
    Parameters:
        objectSpec[table]
        objectSpec[keying]
        newValue
     */
	db_update: function (args) {
		var params = "?access=update&table=" + encodeURI(objectSpec['table']);
        var extCount = 0;
		if ( objectSpec['keying'] != null ) {
            var compOfCond = objectSpec['keying'].split("=");
            params += "&ext_cond" + extCount + "field=" + encodeURI(compOfCond[0]);
            params += "&ext_cond" + extCount + "operator=" + encodeURI("=");
            compOfCond.shift();
            params += "&ext_cond" + extCount + "value=" + encodeURI(compOfCond.join("="));
            extCount++;
        }
		params += "&field_0=" + encodeURI(objectSpec['field']);
		params += "&value_0=" + encodeURI(newValue);
		var appPath = IM_getEntryPath();

		INTERMediator.debugMessages.push("Update Request=" + appPath + params);

		myRequest = new XMLHttpRequest();
		try {
			myRequest.open('GET', appPath + params, false);
			// myRequest.setRequestHeader('Content-Type','application/x-www-form-urlencoded;
			// charset=UTF-8');
			myRequest.send(null);
			var dbresult = '';
			eval(myRequest.responseText);
		} catch (e) {
			INTERMediator.errorMessages.push("ERROR in db_update=" + e + "/" + myRequest.responseText);
		}
		return dbresult;
	},

    db_delete: function( args )   {
        var params = "?access=delete&table=" + encodeURI(tableName);
        var count = 0;
        for ( var oneField in fieldsValues )    {
            params += "&field_" + count + "=" + encodeURI(oneField);
            params += "&value_" + count + "=" + encodeURI(fieldsValues[oneField]);
            count++;
        }
        var appPath = IM_getEntryPath();
        INTERMediator.debugMessages.push( "Delete Request: " + appPath + params );
        myRequest = new XMLHttpRequest();
        try {
            myRequest.open('GET', appPath + params, false);
            myRequest.send(null);
            var dbresult = '';
            eval(myRequest.responseText);
        } catch (e) {
            INTERMediator.errorMessages.push("ERROR in db_deleteRecord=" + e + "/" + myRequest.responseText);
        }
        INTERMediator.flushMessage();
    },

    db_createRecord: function( args ) {
        var params = "?access=insert&table=" + encodeURI(tableName);
        var count = 0;
        for ( var oneField in fieldsValues )    {
            params += "&field_" + count + "=" + encodeURI(oneField);
            params += "&value_" + count + "=" + encodeURI(fieldsValues[oneField]);
            count++;
        }
        var appPath = IM_getEntryPath();

        var newRecordKeyValue = '';
        INTERMediator.debugMessages.push("Update Request=" + appPath + params);
        myRequest = new XMLHttpRequest();
        try {
            myRequest.open('GET', appPath + params, false);
            myRequest.send(null);
            eval(myRequest.responseText);
        } catch (e) {
            INTERMediator.errorMessages.push("ERROR in db_createRecord=" + e + "/" + myRequest.responseText);
        }
        INTERMediator.flushMessage();
        return newRecordKeyValue;
    }

}
