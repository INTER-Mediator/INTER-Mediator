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
 * Time: 17:30
 * To change this template use File | Settings | File Templates.
 */
var IM_DBAdapter = {
    //=================================
    // Database Access
    //=================================
    /*
    db_query
    Parameters:
        dataSource[name]:
        dataSource[records]:
        fields:
        parentKeyVal:
        extraCondition: "field,operator,value"
     */
	db_query: function (detaSource, fields, parentKeyVal, extraCondition, useOffset) {

		// Create string for the parameter.
		var params = "?access=select&table=" + encodeURI(detaSource['name']);
		params += "&records=" + encodeURI((detaSource['records'] != null) ? detaSource['records'] : 10000000);
		var arCount = fields.length;
		for (var i = 0; i < arCount; i++) {
			params += "&field_" + i + "=" + encodeURI(fields[i]);
		}
        if (parentKeyVal != null) {
            params += "&parent_keyval=" + encodeURI(parentKeyVal);
        }
        if ( useOffset && INTERMediator.startFrom != null ) {
            params += "&start=" + encodeURI(INTERMediator.startFrom);
        }
        var extCount = 0;
		if (extraCondition != null) {
            var compOfCond = extraCondition.split("=");
            params += "&ext_cond" + extCount + "field=" + encodeURI(compOfCond[0]);
            params += "&ext_cond" + extCount + "operator=" + encodeURI("=");
            compOfCond.shift();
            params += "&ext_cond" + extCount + "value=" + encodeURI(compOfCond.join("="));
            extCount++;
		}
        for ( var oneItem in INTERMediator.additionalCondition ) {
            if ( detaSource['name'] == oneItem )    {
                var criteraObject = INTERMediator.additionalCondition[oneItem];
                params += "&ext_cond" + extCount + "field=" + encodeURI(criteraObject["field"]);
                if ( criteraObject["operator"] != null )    {
                    params += "&ext_cond" + extCount + "operator=" + encodeURI(criteraObject["operator"]);
                }
                params += "&ext_cond" + extCount + "value=" + encodeURI(criteraObject["value"]);
                extCount++;
            }
        }
        params += "&randkey" + Math.random();    // For ie...
            // IE uses caches as the result in spite of several headers. So URL should be randomly.
		var appPath = IM_getEntryPath();

        INTERMediator.debugMessages.push( "Access: " + appPath + params );
        var dbresult = '';
		myRequest = new XMLHttpRequest();
		try {
			myRequest.open('GET', appPath + params, false);
			myRequest.send(null);
			eval(myRequest.responseText);
            if (( detaSource['paging'] != null) && ( detaSource['paging'] == true ))  {
                INTERMediator.pagedSize = detaSource['records'];
                INTERMediator.pagedAllCount = resultCount;
            }

		} catch (e) {
			INTERMediator.errorMessages.push("ERROR in db_query=" + e + "/" + myRequest.responseText);
		}
		return dbresult;
	},

    /*
    db_update
    Parameters:
        objectSpec[table]
        objectSpec[keying]
        newValue
     */
	db_update: function (objectSpec, newValue) {
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

    db_delete: function( tableName, fieldsValues )   {
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

    db_createRecord: function( tableName, fieldsValues ) {
        var params = "?access=insert&table=" + encodeURI(tableName);
        var count = 0;
        for ( var oneField in fieldsValues )    {
            params += "&field_" + count + "=" + encodeURI(oneField);
            params += "&value_" + count + "=" + encodeURI(fieldsValues[oneField]);
            count++;
        }
        var appPath = IM_getEntryPath();

        var newRecordKeyValue = '';
        INTERMediator.debugMessages.push("New Record Request=" + appPath + params);
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
