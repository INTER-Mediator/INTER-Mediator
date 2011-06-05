/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

// Cleaning-up by http://jsbeautifier.org/
// Next Generation gets start
var INTERMediator = {
    /*
        Properties
     */
    debugMode: false,
    // Show the debug messages at the top of the page.
	separator: '@',
	// This must be refered as 'INTERMediator.separator'. Don't use 'this.separator'
	defDevider: '|',
	// Same as the "separator".
    addtionalCondition: [],
    // This array should be [{tableName: {field:xxx,operator:xxx,value:xxxx}}, ... ]
    defalutTargetInnerHTML: false,
    // For general elements, if target isn't specified, the value will be set to innerHTML.
    // Otherwise, set as the text node.
    navigationLavel: null,
    // Navigation is controlled by this parameter.
    startFrom: 0,
    // Start from this number of record for "skipping" records.
    pagedSize: 0,
    pagedAllCount: 0,
    currentEncNumber: 0,
    // Rembering Objects
	updateRequredObject: null,
    /*
    {id-value:                  // For the node of this id attribute.
        {targetattribute:,      // about target
        initialvalue:,          // The value from database.
        table:person,           // about target table
        field:id,               // about target field
        keying:id=1,            // The key field specifier to identify this record.
        foreignfield:,          // foreign field name
        foreignvalue:, }        // foreign field value
     */
	keyFieldObject: null,
    /*
     {node:xxx,         // The node to depend another node
     original:xxx       // Copy of childs
     table:xxx,
     field:xxx,
     fieldvalue:xxxx,
     target:xxxx}       // Related (depending) node's id attribute value.
      */
    rootEnclosure: null,
    // Storing to retrieve the page to initial condtion.
    // {node:xxx, parent:xxx, currentRoot:xxx, currentAfter:xxxx}
    errorMessages: [],
    debugMessages: [],


    //=================================
    // Message for Programmers
    //=================================
    flushMessage: function () {
        for (var i = 0; i < INTERMediator.errorMessages.length; i++) {
            errorOut(INTERMediator.errorMessages[i]);
        }
        if (INTERMediator.debugMode) {
            for (var i = 0; i < INTERMediator.debugMessages.length; i++) {
                debugOut(INTERMediator.debugMessages[i]);
            }
        }
        INTERMediator.errorMessages = [];
        INTERMediator.debugMessages = [];

        function errorOut (str, msg1, msg2, msg3) {
            if (msg1 != null) str = str.replace('@1@', msg1);
            if (msg2 != null) str = str.replace('@2@', msg2);
            if (msg3 != null) str = str.replace('@3@', msg3);

            var debugNode = document.getElementById('easypage_error_panel_4873643897897');
            if (debugNode == null) {
                debugNode = document.createElement('div');
                debugNode.setAttribute('id', 'easypage_error_panel_4873643897897');
                debugNode.style.backgroundColor = '#FFDDDD';
                var title = document.createElement('h3');
                title.appendChild(document.createTextNode('Error Info from INTER-Mediator'));
                title.appendChild(document.createElement('hr'));
                debugNode.appendChild(title);
                var body = document.getElementsByTagName('body')[0];
                body.insertBefore(debugNode, body.firstChild);
            }
            debugNode.appendChild(document.createTextNode(str));
            debugNode.appendChild(document.createElement('hr'));
        }

        function debugOut(str) {
            var debugNode = document.getElementById('easypage_debug_panel_4873643897897');
            if (debugNode == null) {
                debugNode = document.createElement('div');
                debugNode.setAttribute('id', 'easypage_debug_panel_4873643897897');
                debugNode.style.backgroundColor = '#DDDDDD';
                var clearButton = document.createElement('button');
                clearButton.setAttribute('title', 'clear');
                INTERMediator.addEvent(clearButton, 'click', function () {
                    var target = document.getElementById('easypage_debug_panel_4873643897897');
                    target.parentNode.removeChild(target);
                });
                var tNode = document.createTextNode('clear');
                clearButton.appendChild(tNode);
                var title = document.createElement('h3');
                title.appendChild(document.createTextNode('Debug Info from INTER-Mediator'));
                title.appendChild(clearButton);
                title.appendChild(document.createElement('hr'));
                debugNode.appendChild(title);
                var body = document.getElementsByTagName('body')[0];
                body.insertBefore(debugNode, body.firstChild);
            }
            debugNode.appendChild(document.createTextNode(str));
            debugNode.appendChild(document.createElement('hr'));
        }
    },


    //=================================
    // Database Access
    //=================================
    /*
    valueChange
    Parameters:
     */
	valueChange: function (idValue) {
		var newValue = null;
		var changedObj = document.getElementById(idValue);
		if (changedObj != null) {
            // Check the current value of the field
			var objectSpec = INTERMediator.updateRequredObject[idValue];
			var currentVal = INTERMediator.db_query({
				records: 1,
				name: objectSpec['table']
			}, [objectSpec['field']], null, objectSpec['keying'], false);
			if (currentVal[0] == null || currentVal[0][objectSpec['field']] == null) {
				// ERROR
				alert("No information to update: field="+objectSpec['field']);
				INTERMediator.flushMessage();
				return;
			}
			currentVal = currentVal[0][objectSpec['field']];
			if (objectSpec['initialvalue'] != currentVal) {
				// The value of database and the field is diffrent. Others must be changed this field.
				answer = confirm("Other people might be updated. Initially="
                        +objectSpec['initialvalue']+"/Current="+currentVal
						+"You can overwrite with your data if you select OK.");
				if (!answer) {
					INTERMediator.flushMessage();
					return;
				}
			}

			if (changedObj.tagName == 'TEXTAREA') {
				newValue = changedObj.value;
			} else if (changedObj.tagName == 'SELECT') {
				newValue = changedObj.value;
			} else if (changedObj.tagName == 'INPUT') {
				var objType = changedObj.getAttribute('type');
				if (objType != null) {
					if (objType == 'checkbox') {
						var valueAttr = changedObj.getAttribute('value');
						if (changedObj.checked) {
							newValue = valueAttr;
						} else {
							newValue = '';
						}
					} else if (objType == 'radio') {
						newValue = changedObj.value;
					} else { //text, password
						newValue = changedObj.value;
					}
				}
			}
			if (newValue != null) {
				INTERMediator.db_update(objectSpec, newValue);
				objectSpec['initialvalue'] = newValue;
				for( var i=0 ; i< INTERMediator.keyFieldObject.length; i++)	{
					if (INTERMediator.keyFieldObject[i]['target'] == idValue)	{
                        INTERMediator.keyFieldObject[i]['fieldvalue'] = newValue;
                        INTERMediator.construct( false, null, i );
					}
				}
			}
		}
		INTERMediator.flushMessage();
	},

    deleteButton: function( tableName, keyField, keyValue, removeNodes )    {
        var recordSet = {};
        recordSet[keyField] = keyValue;
        INTERMediator.db_delete( tableName, recordSet );
        for( var key in removeNodes )   {
            var removeNode = document.getElementById(removeNodes[key]);
            removeNode.parentNode.removeChild(removeNode);
        }
        INTERMediator.flushMessage();
    },

    insertButton: function( tableName, keyField, keyValue, updateNodes, removeNodes )    {
        var recordSet = {};
        recordSet[keyField] = keyValue;
        INTERMediator.db_createRecord( tableName, recordSet );
        for( var key in removeNodes )   {
            var removeNode = document.getElementById(removeNodes[key]);
            removeNode.parentNode.removeChild(removeNode);
        }
        for( var i=0 ; i< INTERMediator.keyFieldObject.length; i++)	{
            if (INTERMediator.keyFieldObject[i]['node'].getAttribute('id') == updateNodes)	{
                INTERMediator.keyFieldObject[i]['fieldvalue'] = keyValue;
                INTERMediator.construct( false, null, i );
                break;
            }
        }
        INTERMediator.flushMessage();
    },

    insertRecordFromNavi: function(tableName, keyField) {
        var newId = INTERMediator.db_createRecord(tableName, null);
        if ( newId > -1 )   {
            var restore = INTERMediator.addtionalCondition;
            INTERMediator.startFrom = 0;
            var fieldObj = {field: keyField, value:newId};
            INTERMediator.addtionalCondition = {};
            INTERMediator.addtionalCondition[tableName] = fieldObj;
            INTERMediator.construct(true, null);
            INTERMediator.addtionalCondition = restore;
        }
        INTERMediator.flushMessage();
    },

    deleteRecordFromNavi: function(tableName, keyField, keyValue) {
        var fieldsValues = {};
        fieldsValues[keyField] = keyValue;
        INTERMediator.db_delete( tableName, fieldsValues );
        if ( INTERMediator.pagedAllCount - INTERMediator.startFrom < 2 )    {
            INTERMediator.startFrom--;
            if ( INTERMediator.startFrom < 0 )  {
                INTERMediator.startFrom = 0;
            }
        }
        INTERMediator.construct(true, null);
        INTERMediator.flushMessage();
    },

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
        for ( var oneItem in INTERMediator.addtionalCondition ) {
            if ( detaSource['name'] == oneItem )    {
                var criteraObject = INTERMediator.addtionalCondition[oneItem];
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
    },

    //=================================
    // Construct Page
    //=================================
    /**
     * Construct the Web Page with DB Data
     * You should call here when you show the page.
     *
     * parameter: fromStart: true=construct page, false=construct partially
     *            doAfterConstruct: as shown.
     */
    construct: function (fromStart, doAfterConstruct, numberOfKeyFieldObject) {

        var titleAsLinkInfo = true;
        var classAsLinkInfo = true;
        var currentLevel = 0;
        var linkedNodes;
        var postSetFields = new Array();
        var firstEnclosure = true;
        var isIE = false;
        var ieVersion = -1;
        var buttonIdNum = 1;
        var deleteInsertOnNavi = [];

        if ( fromStart )    {
            pageConstruct( doAfterConstruct );
        } else {
            partialConstruct( numberOfKeyFieldObject );
        }
        INTERMediator.flushMessage();   // Show messages

        /*

         */
        function partialConstruct( i )   {
            // Create parent table essentials.
            var updateTable = INTERMediator.keyFieldObject[i]['table'];
            var updateField = INTERMediator.keyFieldObject[i]['field'];
            var updateValue = INTERMediator.keyFieldObject[i]['fieldvalue'];
            var updateRecord = {};
            updateRecord[updateField] = updateValue;
            // Recreate nodes.
            var updateNode = INTERMediator.keyFieldObject[i]['node'];
            while (updateNode.firstChild) {
                updateNode.removeChild(updateNode.firstChild);
            }
            var originalNodes = INTERMediator.keyFieldObject[i]['original'];
            for ( var i = 0 ; i < originalNodes.length;  i++ )  {
                updateNode.appendChild(originalNodes[i]);
            }
            expandEnclosure(updateNode, updateRecord, updateTable);
        }

        /*
        
         */
        function pageConstruct( doAfterConstruct )    {
            INTERMediator.keyFieldObject = [];
            INTERMediator.currentEncNumber = 1;

            // Detect Internet Explorer and its version.
            var ua = navigator.userAgent;
            var msiePos = ua.indexOf('MSIE');
            if ( msiePos >= 0 ) {
                isIE = true;
                for( var i = msiePos+4 ; i < ua.length ; i++ )    {
                    var c = ua.charAt(i);
                    if ( c != ' ' && c != '.' && (c < '0' || c > '9') )   {
                        ieVersion = INTERMediator.toNumber( ua.substring( msiePos+4, i ));
                        break;
                    }
                }
            }

            // Initialize the page to the loaded one.
            firstEnclosure = true;
            // Restoring original HTML Document from backup data.
            if ( INTERMediator.rootEnclosure != null )  {
                var parentOfRoot = INTERMediator.rootEnclosure['parent'];
                parentOfRoot.removeChild( INTERMediator.rootEnclosure['currentRoot']);
                var newNode = INTERMediator.rootEnclosure['node'].cloneNode(true);
                INTERMediator.rootEnclosure['currentRoot'] = newNode;
                if ( INTERMediator.rootEnclosure['currentAfter'] == null )    {
                    parentOfRoot.appendChild(newNode);
                } else {
                    parentOfRoot.insertBefore( newNode, INTERMediator.rootEnclosure['currentAfter']);
                }
                firstEnclosure = false;
            }
            // Root node is BODY tag.
            var bodyNode = document.getElementsByTagName('BODY')[0];
            seekEnclosureNode(bodyNode, '');

            // After work to set up popup menus.
            for (var i = 0; i < postSetFields.length; i++) {
                document.getElementById(postSetFields[i]['id']).value = postSetFields[i]['value'];
            }

            for( var i=0 ; i< INTERMediator.keyFieldObject.length; i++)	{
                var currentNode = INTERMediator.keyFieldObject[i];
                var currentID = currentNode['node'].getAttribute('id');
                var enclosure;
                if ( currentID != null && currentID.match(/IM[0-9]+-[0-9]+/) )	{
                    enclosure = getParentRepeater( currentNode['node'] );
                } else {
                    enclosure = getParentRepeater(getParentEnclosure( currentNode['node'] ));
                }
                if ( targetNode )   {
                    var targetNode = getEnclosedNode( enclosure, currentNode['table'], currentNode['field'] );
                    if ( targetNode )	{
                        currentNode['target'] = targetNode.getAttribute('id');
                    }
                }
            }

            navigationSetup();
            appendCredit();

            if ( doAfterConstruct != null ) {
                doAfterConstruct();
            }
        }

        /**
         * Seeking nodes and if a node is an enclosure, proceed repeating.
         */
        function seekEnclosureNode(node, currentRecord, currentTable) {
        //    INTERMediator.messages.push("seekEnclosureNode =" + node.tagName
        //            +"/currentRecord="+objectToString(currentRecord));
            var enclosure = null;
            var nType = node.nodeType;
            if (nType == 1) { // Work for an element
                if (isEnclosure(node, false)) { // Linked element and an enclosure
                    if ( firstEnclosure && INTERMediator.rootEnclosure == null)   {
                        var targetNode =  ( node.tagName == "TBODY" ) ? node.parentNode : node;
                        INTERMediator.rootEnclosure = {
                            node:targetNode.cloneNode(true),
                            parent:targetNode.parentNode,
                            currentRoot:targetNode,
                            currentAfter:targetNode.nextSibling};
                        firstEnclosure = false;
                        INTERMediator.updateRequredObject = {};
                    }
                    enclosure = expandEnclosure(node, currentRecord, currentTable);
                } else {
                    var childs = node.childNodes; // Check all child nodes.
                    for (var i = 0; i < childs.length; i++) {
                        if ( childs[i].nodeType == 1 )	{
                            var checkingEncl = seekEnclosureNode(childs[i], currentRecord, currentTable);
                        }
                    }
                }
            }
            return enclosure;
        }

        /**
         * Expanding an enclosure.
         */

        function expandEnclosure(node, currentRecord, currentTable) {
            currentLevel++;
            INTERMediator.currentEncNumber++;

            var linkedElmCounter = 0;
            if ( node.getAttribute('id') == null )  {
                var idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                node.setAttribute('id', idValue);
                linkedElmCounter++;
            }

            var encNodeTag = node.tagName;
            var repNodeTag = repeaterTagFromEncTag(encNodeTag);
            var repeatersOriginal = []; // Collecting repeaters to this array.
            var repeaters = new Array(); // Collecting repeaters to this array.
            var children = node.childNodes; // Check all child node of the enclosure.

            for (var i = 0; i < children.length; i++) {
                if (children[i].nodeType == 1 && children[i].tagName == repNodeTag) {
                    // If the element is a repeater.
                    repeatersOriginal.push(children[i]); // Record it to the array.
                }
            }

            for (var i = 0; i < repeatersOriginal.length; i++) {
                var inDocNode = repeatersOriginal[i];
                var parentOfRep = repeatersOriginal[i].parentNode;
                var cloneNode = repeatersOriginal[i].cloneNode(true);
                repeaters.push(cloneNode);
                idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                cloneNode.setAttribute('id', idValue);
                linkedElmCounter++;
                parentOfRep.removeChild(inDocNode);
            }
            linkedNodes = new Array(); // Collecting linked elements to this array.
            for (var i = 0; i < repeaters.length; i++) {
                 seekLinkedElement(repeaters[i]);
            }
            var currentLinkedNodes = linkedNodes; // Store in the local variable
            // Collecting linked elements in array
            var linkDefs = new Array();
            for (var j = 0; j < linkedNodes.length; j++) {
                var nodeDefs = getLinkedElementInfo(linkedNodes[j]);
                if (nodeDefs != null) {
                    for (var k = 0; k < nodeDefs.length; k++) {
                        linkDefs.push(nodeDefs[k]);
                    }
                }
            }

            var linkDefsHash = new Array();
            // For collected linked elements with hash array.
            var tableVote = new Array();
            // counting each table name in linked elements.
            var hasEditable = false;
            // Containing editable elements or not.
            for (var j = 0; j < linkDefs.length; j++) {
                var tag = linkDefs[j].tagName;
                if (tag == 'INPUT' || tag == 'TEXTAREA' || tag == 'SELECT') {
                    hasEditable = true;
                }
                var nodeInfoArray = getNodeInfoArray(linkDefs[j]);
                linkDefsHash.push(nodeInfoArray);

                if (nodeInfoArray['table'] != '') {
                    if (tableVote[nodeInfoArray['table']] == null) {
                        tableVote[nodeInfoArray['table']] = 1;
                    } else {
                        ++tableVote[nodeInfoArray['table']];
                    }
                }
            }
            var maxVoted = -1;
            var maxTableName = ''; // Which is the maximum voted table name.
            for (var i in tableVote) {
                if (maxVoted < tableVote[i]) {
                    maxVoted < tableVote[i];
                    maxTableName = i;
                }
            }
        //    INTERMediator.debugMessages.push("maxTableName =" + maxTableName);
            var fieldList = new Array(); // Create field list for database fetch.
            for (var i = 0; i < linkDefsHash.length; i++) {
                if (linkDefsHash[i].table == maxTableName || linkDefsHash[i].table == '') {
                    fieldList.push(linkDefsHash[i].field);
                }
            }
            var ds = IM_getDataSources(); // Get DataSource parameters
            var targetKey = '';
            for (var key in ds) { // Search this table from DataSource
                if ((maxTableName == '') || (ds[key]['name'] == maxTableName)) {
                    targetKey = key;
                    break;
                }
            }
            if (targetKey != '') {
                var foreignValue = '';
                var foreignField = '';
                var keyFieldObjectRequired = (currentRecord[ds[targetKey]['join-field']] != null);
                keyFieldObjectRequired = true;
                if  ( keyFieldObjectRequired ) {
                    foreignValue = currentRecord[ds[targetKey]['join-field']];
                    foreignField = ds[targetKey]['join-field'];
                    var thisKeyFieldObject = {
                        node:node,
                        table:currentTable,
                        field:foreignField,
                        fieldvalue:foreignValue,
                        parent: node.parentNode,
                        original:[]
                    };
                    for (var i = 0; i < repeatersOriginal.length; i++) {
                        thisKeyFieldObject.original.push(repeatersOriginal[i].cloneNode(true));
                    }
                    INTERMediator.keyFieldObject.push( thisKeyFieldObject );
                }
                var targetRecords = INTERMediator.db_query(ds[targetKey], fieldList, foreignValue, null, true);
                // Access database and get records
                var RecordCounter = 0;
                var eventListenerPostAdding = new Array();

                // var currentEncNumber = currentLevel;
                for (var ix in targetRecords) { // for each record
                    RecordCounter++;

                    var shouldDeleteNodes = [];
                    repeaters = [];
                    for (var i = 0; i < repeatersOriginal.length; i++) {
                        var clonedNode = repeatersOriginal[i].cloneNode(true);
                        repeaters.push( clonedNode );
                    }
                    linkedNodes = [];
                    for (var i = 0; i < repeaters.length; i++) {
                        seekLinkedElement(repeaters[i]);
                        if ( repeaters[i].getAttribute('id') == null )    {
                            idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                            repeaters[i].setAttribute('id', idValue);
                            linkedElmCounter++;
                        }
                        shouldDeleteNodes.push(repeaters[i].getAttribute('id'));
                    }
                    var currentLinkedNodes = linkedNodes; // Store in the local variable

                    var keyField = ds[targetKey]['key'];
                    var keyValue = targetRecords[ix][ds[targetKey]['key']];
                    var keyingValue = keyField + "=" + keyValue;

                    for (var k = 0; k < currentLinkedNodes.length; k++) {
                        // for each linked element
                        if ( currentLinkedNodes[k].getAttribute('id') == null ) {
                            idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                            currentLinkedNodes[k].setAttribute('id', idValue);
                            linkedElmCounter++;
                        }
                        var nodeTag = currentLinkedNodes[k].tagName;
                        // get the tag name of the element
                        var typeAttr = currentLinkedNodes[k].getAttribute('type');
                        // type attribute
                        var linkInfoArray = getLinkedElementInfo(currentLinkedNodes[k]);
                        // info array for it  set the name attribute of radio button
                        // should be different for each group
                        if (typeAttr == 'radio') { // set the value to radio button
                            var nameAttr = currentLinkedNodes[k].getAttribute('name');
                            if (nameAttr) {
                                currentLinkedNodes[k].setAttribute('name', nameAttr + '-' + RecordCounter);
                            } else {
                                currentLinkedNodes[k].setAttribute('name', 'IM-R-' + RecordCounter);
                            }
                        }

                        if ( nodeTag == 'INPUT' || nodeTag == 'SELECT' ||nodeTag == 'TEXTAREA' )   {
                            eventListenerPostAdding.push({
                                'id': idValue,
                                'event': 'change',
                                'todo': new Function('INTERMediator.valueChange("' + idValue + '")')
                            });
                        }

                        for (var j = 0; j < linkInfoArray.length; j++) {
                            // for each info Multiple replacement definitions
                            // for one node is prohibited.
                            var nInfo = getNodeInfoArray(linkInfoArray[j]);
                            var curVal = targetRecords[ix][nInfo['field']];
                            var curTarget = nInfo['target'];
                            // INTERMediator.messages.push("curTarget ="+curTarget+"/curVal ="+curVal);
                            //    if ( curVal != null )	{
                            // Store the key field value and current value for update
                            if ( nodeTag == 'INPUT' || nodeTag == 'SELECT' ||nodeTag == 'TEXTAREA' )   {
                                INTERMediator.updateRequredObject[idValue] = {
                                    targetattribute: curTarget,
                                    initialvalue: curVal,
                                    table: ds[targetKey]['name'],
                                    field: nInfo['field'],
                                    keying: keyingValue,
                                    foreignfield: foreignField,
                                    foreignvalue: foreignValue
                                };
                            }

                            if (curTarget != null && curTarget.length > 0) {
                                if ( curTarget == 'innerHTML')  {
                                    currentLinkedNodes[k].innerHTML = curVal;
                                } else if ( curTarget == 'textNode')  {
                                    var textNode = document.createTextNode(curVal);
                                    currentLinkedNodes[k].appendChild(textNode);
                                } else if ( curTarget.indexOf('style.') == 0 )  {
                                    var styleName = curTarget.substring( 6, curTarget.length );
                                    var statement = "currentLinkedNodes[k].style."+styleName+"='"+curVal+"';";
                                    eval( statement );
                                } else {
                                    currentLinkedNodes[k].setAttribute(curTarget, curVal);
                                }
                            } else { // if the 'target' is not specified.
                                if (nodeTag == "INPUT") {
                                    if (typeAttr == 'checkbox' || typeAttr == 'radio') { // set the value
                                        var valueAttr = currentLinkedNodes[k].value;
                                        if (valueAttr == curVal) {
                                            if ( isIE ) {
                                                currentLinkedNodes[k].setAttribute('checked','checked');
                                            } else {
                                                currentLinkedNodes[k].checked = true;
                                            }
                                        } else {
                                            currentLinkedNodes[k].checked = false;
                                        }
                                    } else { // this node must be text field
                                        currentLinkedNodes[k].value = curVal;
                                    }
                                } else if (nodeTag == "SELECT") {
                                    postSetFields.push({
                                        'id': idValue,
                                        'value': curVal
                                    });
                                } else { // include option tag node
                                    if ( INTERMediator.defalutTargetInnerHTML ) {
                                        currentLinkedNodes[k].innerHTML = curVal;
                                    } else  {
                                        var textNode = document.createTextNode(curVal);
                                        currentLinkedNodes[k].appendChild(textNode);
                                    }
                                }
                            }
                        }
                    }

                    if (    ds[targetKey]['repeat-control'] != null
                         && ds[targetKey]['repeat-control'].match(/delete/i) )  {
                        if ( ds[targetKey]['foreign-key'] != null ) {
                            var buttonNode = document.createElement('BUTTON');
                            buttonNode.appendChild(document.createTextNode('Delete'));
                            var thisId = 'IM_Button_'+buttonIdNum;
                            buttonNode.setAttribute( 'id', thisId );
                            eventListenerPostAdding.push({
                                    'id': thisId,
                                    'event': 'click',
                                    'todo': new Function( "INTERMediator.deleteButton("
                                                + "'" + ds[targetKey]['name']+ "',"
                                                + "'" + keyField+ "',"
                                                + "'" + keyValue+ "',"
                                                + INTERMediator.objectToString( shouldDeleteNodes )
                                                + ");")});
                            buttonIdNum++;
                            var endOfRepeaters = repeaters[repeaters.length-1];
                            switch ( encNodeTag )   {
                                case 'TBODY':
                                        var tdNodes = endOfRepeaters.getElementsByTagName('TD');
                                        var tdNode = tdNodes[ tdNodes.length -1 ];
                                        tdNode.appendChild( buttonNode );
                                        break;
                                case 'UL':
                                case 'OL':
                                        endOfRepeaters.appendChild( buttonNode );
                                        break;
                                case 'DIV':
                                        if ( repNodeTag == "DIV" )  {
                                            endOfRepeaters.appendChild( buttonNode );
                                        }
                                        break;
                            }
                        } else {
                            deleteInsertOnNavi.push({
                                kind: 'DELETE',
                                table: ds[targetKey]['name'],
                                key : keyField,
                                value: keyValue
                            });
                        }
                    }

                    for (var i = 0; i < repeaters.length; i++) {
                        var newNode = repeaters[i].cloneNode(true);
                        node.appendChild(newNode);
                        if ( newNode.getAttribute('id') == null )  {
                            idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                            newNode.setAttribute('id', idValue);
                            linkedElmCounter++;
                        }
                        seekEnclosureNode(newNode, targetRecords[ix], ds[targetKey]['name']);
                    }

                    // Event listener should add after adding node to document.
                    for (var i = 0; i < eventListenerPostAdding.length; i++) {
                        var theNode = document.getElementById(eventListenerPostAdding[i]['id']);
                        if ( theNode != null )  {
                            INTERMediator.addEvent( theNode,
                                    eventListenerPostAdding[i]['event'],
                                    eventListenerPostAdding[i]['todo']);
                        }
                    }
                }

                if (   ds[targetKey]['repeat-control'] != null
                    && ds[targetKey]['repeat-control'].match(/insert/i) )  {
                    if ( foreignValue != null ) {
                        var buttonNode = document.createElement('BUTTON');
                        buttonNode.appendChild(document.createTextNode('Insert'));
                        var shouldRemove = [];
                        switch ( encNodeTag )   {
                            case 'TBODY':
                                    var enclosedNode = node.parentNode;
                                    var footNode = enclosedNode.getElementsByTagName('TFOOT')[0];
                                    if ( footNode == null )  {
                                        footNode = document.createElement('TFOOT');
                                        enclosedNode.appendChild(footNode);
                                    }
                                    var trNode = document.createElement('TR');
                                    var tdNode = document.createElement('TD');
                                    if ( trNode.getAttribute('id') == null )  {
                                        idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                                        trNode.setAttribute('id', idValue);
                                        linkedElmCounter++;
                                    }
                                    footNode.appendChild( trNode );
                                    trNode.appendChild( tdNode );
                                    tdNode.appendChild( buttonNode );
                                    shouldRemove = [trNode.getAttribute('id')];
                                    break;
                            case 'UL':
                            case 'OL':
                                var liNode = document.createElement('LI');
                                liNode.appendChild( buttonNode )
                                node.appendChild( liNode );
                                break;
                            case 'DIV':
                                    if ( repNodeTag == "DIV" )  {
                                        var divNode = document.createElement('DIV');
                                        divNode.appendChild( buttonNode )
                                        node.appendChild( liNode );
                                    }
                                    break;
                        }
                        INTERMediator.addEvent( buttonNode, 'click',
                                new Function( "INTERMediator.insertButton("
                                    + "'" + ds[targetKey]['name']+ "',"
                                    + "'" + ds[targetKey]['foreign-key']+ "',"
                                    + "'" + foreignValue+ "',"
                                    + "'" + node.getAttribute('id') + "',"
                                    + (shouldRemove==null ? 'null' : INTERMediator.objectToString( shouldRemove ))
                                    + ");"));
                    } else {
                        deleteInsertOnNavi.push({
                            kind: 'INSERT',
                            table: ds[targetKey]['name'],
                            key : ds[targetKey]['key']
                        });
                    }
                }

            } else {
                INTERMediator.errorMessages.push("Cant determine the Table Name: " +
                        INTERMediator.objectToString(linkDefsHash));
            }
            currentLevel--;
            return foreignValue != '';
        }

        /**
         * Detect the enclosure of the argument node.
         */

        function getEnclosure(node) {
            var detectedRepeater = null;
            var currentNode = node;
            while (currentNode != null) {
                if (isRepeater(currentNode)) {
                    detectedRepeater = currentNode;
                } else if (isRepeaterOfEnclosure(detectedRepeater, currentNode)) {
                    return currentNode;
                }
                currentNode = currentNode.parentNode;
            }
            return null;
        }

        function getParentRepeater(node) {
            var currentNode = node;
            while (currentNode != null) {
                if (isRepeater(currentNode,true)) {
                    return currentNode;
                }
                currentNode = currentNode.parentNode;
            }
            return null;
        }
        function getParentEnclosure(node) {
            var currentNode = node;
            while (currentNode != null) {
                if (isEnclosure(currentNode,true)) {
                    return currentNode;
                }
                currentNode = currentNode.parentNode;
            }
            return null;
        }

        function getEnclosedNode(rootNode, tableName, fieldName){
            if (rootNode.nodeType == 1) {
                var nodeInfo = getLinkedElementInfo(rootNode);
                for ( var j=0 ; j<nodeInfo.length ; j++){
                    var nInfo = getNodeInfoArray(nodeInfo[j]);
                    if (nInfo['table']==tableName && nInfo['field']==fieldName)	{
                        return rootNode;
                    }
                }
            }
            var childs = rootNode.childNodes; // Check all child node of the enclosure.
            for (var i = 0; i < childs.length; i++) {
                var r = getEnclosedNode(childs[i], tableName, fieldName);
                if ( r != null )	{
                    return r;
                }
            }
            return null;
        }

        /**
         * Check the pair of nodes in argument is valid for repater/enclosure.
         */

        function isRepeaterOfEnclosure(repeater, enclosure) {
            if (repeater == null || enclosure == null) return false;
            var repeaterTag = repeater.tagName;
            var enclosureTag = enclosure.tagName;
            if (       (repeaterTag == 'TR' && enclosureTag == 'TBODY')
                    || (repeaterTag == 'OPTION' && enclosureTag == 'SELECT')
                    || (repeaterTag == 'LI' && enclosureTag == 'OL')
                    || (repeaterTag == 'LI' && enclosureTag == 'UL')) {
                return true;
            }
            if (enclosureTag == 'DIV') {
                var enclosureClass = getClassAttributeFromNode(enclosure);
                if (enclosureClass != null && enclosureClass.indexOf('_im_enclosure') >= 0) {
                    var repeaterClass = getClassAttributeFromNode(repeater);
                    if (       repeaterTag == 'DIV'
                            && repeaterClass != null
                            && repeaterClass.indexOf('_im_repeater') >= 0) {
                        return true;
                    } else if (repeaterTag == 'INPUT') {
                        var repeaterType = repeater.getAttribute('type');
                        if (       repeaterType != null
                                && ((repeaterType.indexOf('radio') >= 0
                                    || repeaterType.indexOf('check') >= 0))) {
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        /**
         * Cheking the argument is the Linked Element or not.
         */

        function isLinkedElement(node) {
            if (node != null) {
                if (titleAsLinkInfo) {
                    if (node.getAttribute('TITLE') != null && node.getAttribute('TITLE').length > 0) {
                        // IE: If the node doesn't have a title attribute, getAttribute
                        // doesn't return null.
                        // So it requrired check if it's empty string.
                        return true;
                    }
                }
                if (classAsLinkInfo) {
                    var classInfo = getClassAttributeFromNode(node);
                    if (classInfo != null) {
                        var matched = classInfo.match(/IM\[.*\]/);
                        if (matched != null) {
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        /**
         * Get the table name / field name information from node as the array of
         * definitions.
         */

        function getLinkedElementInfo(node) {
            if (isLinkedElement(node)) {
                var defs = new Array();
                if (titleAsLinkInfo) {
                    if (node.getAttribute('TITLE') != null) {
                        var eachDefs = node.getAttribute('TITLE').split(INTERMediator.defDevider);
                        for (var i = 0; i < eachDefs.length; i++) {
                            defs.push(eachDefs[i]);
                        }
                    }
                }
                if (classAsLinkInfo) {
                    var classAttr = getClassAttributeFromNode(node);
                    if (classAttr != null && classAttr.length > 0) {
                        var matched = classAttr.match(/IM\[([^\]]*)\]/);
                        var eachDefs = matched[1].split(INTERMediator.defDevider);
                        for (var i = 0; i < eachDefs.length; i++) {
                            defs.push(eachDefs[i]);
                        }
                    }
                }
                return defs;
            } else {
                return false;
            }
        }

        /**
         * Get the first table name from the linked element.
         */

        function getFirstTableFromLinkedElement(node) {
            if (isLinkedElement(node)) {
                return getLinkedElementInfo(node)[0].split(INTERMediator.separator)[0];
            } else {
                return false;
            }
        }

        /**
         * Get the repeater tag from the enclosure tag.
         */

        function repeaterTagFromEncTag(tag) {
            if (tag == 'TBODY') return 'TR';
            else if (tag == 'SELECT') return 'OPTION';
            else if (tag == 'UL') return 'LI';
            else if (tag == 'OL') return 'LI';
            else if (tag == 'DIV') return 'DIV';
            return null;
        }

        /**
         * Check the argument node is an enclosure or not
         */

        function isEnclosure(node, nodeOnly) {

            if (node == null || node.nodeType != 1) return false;
            var tagName = node.tagName;
            var className = getClassAttributeFromNode(node);
            if (       (tagName == 'TBODY')
                    || (tagName == 'UL')
                    || (tagName == 'OL')
                    || (tagName == 'SELECT')
                    || (tagName == 'DIV'
                        && className != null
                        && className.indexOf('_im_enclosure') >= 0)) {
                if (nodeOnly) {
                    return true;
                } else {
                    var countChild = node.childNodes.length;
                    for (var k = 0; k < countChild; k++) {
                        if (isRepeater(node.childNodes[k], false)) {
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        /**
         * Check the argument node is an repeater or not
         */

        function isRepeater(node, nodeOnly) {
            if (node.nodeType != 1) return false;
            var tagName = node.tagName;
            var className = getClassAttributeFromNode(node);
            if (       (tagName == 'TR')
                    || (tagName == 'LI')
                    || (tagName == 'OPTION')
                    || (tagName == 'DIV'
                        && className != null
                        && className.indexOf('_im_repeater') >= 0)) {
                if (nodeOnly) {
                    return true;
                } else {
                    return searchLinkedElement(node);
                }
            } else {
                return false;
            }
        }

        function searchLinkedElement(node) {
            if (isLinkedElement(node)) {
                return true;
            }
            var countChild = node.childNodes.length;
            for (var k = 0; k < countChild; k++) {
                var nType = node.childNodes[k].nodeType;
                if (nType == 1) { // Work for an element
                    if (isLinkedElement(node.childNodes[k])) {
                        return true;
                    } else if (searchLinkedElement(node.childNodes[k])) {
                        return true;
                    }
                }
            }
            return false;
        }

        function getNodeInfoArray(nodeInfo) {
            var comps = nodeInfo.split(INTERMediator.separator);
            var tableName = '',
                fieldName = '',
                targetName = '';
            if (comps.length == 3) {
                tableName = comps[0];
                fieldName = comps[1];
                targetName = comps[2];
            } else if (comps.length == 2) {
                tableName = comps[0];
                fieldName = comps[1];
            } else {
                fieldName = nodeInfo;
            }
            return {
                'table': tableName,
                'field': fieldName,
                'target': targetName
            };
        }

        function seekLinkedElement(node) {
            var enclosure = null;
            var nType = node.nodeType;
            if (nType == 1) {
                if (isLinkedElement(node)) {
                    var currentEnclosure = getEnclosure(node);
                    if (currentEnclosure == null) {
                        linkedNodes.push(node);
                    } else {
                        return currentEnclosure;
                    }
                }
                var childs = node.childNodes;
                for (var i = 0; i < childs.length; i++) {
                    var detectedEnclosure = seekLinkedElement(childs[i]);
                    if (detectedEnclosure != null) {
                        if (detectedEnclosure == childs[i]) {
                            return null;
                        } else {
                            return detectedEnclosure;
                        }
                    }
                }
            }
            return null;
        }

        function setClassAttributeToNode(node, className) {
            if (node == null) return;
            if ( isIE && ieVersion < 8 ) {
                node.setAttribute('className', className);
            } else {
                node.setAttribute('class', className);
            }
        }

        function getClassAttributeFromNode(node) {
            if (node == null) return '';
            var str = '';
            if ( isIE && ieVersion < 8 ) {
                str = node.getAttribute('className');
            } else {
                str = node.getAttribute('class');
            }
            return str;
        }

        function arrayToString(ar) {

        }
        /**
         * Create Navigation Bar to move previous/next page
         * @param target
         */
        function navigationSetup() {
            var navigation = document.getElementById('IM_NAVIGATOR');
            if ( navigation != null )   {
                var insideNav = navigation.childNodes;
                for( var i=0 ; i<insideNav.length ; i++)    {
                    navigation.removeChild(insideNav[i]);
                }
                navigation.innerHTML = '';
                navigation.setAttribute('class', 'IM_NAV_panel');
                var navLabel = INTERMediator.navigationLavel;

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(navLabel==null?'Refresh':navLabel[1]));
                node.setAttribute('class', 'IM_NAV_button');
                INTERMediator.addEvent(node,'click',function(){
                    location.reload();
                });

                var start = Number(INTERMediator.startFrom);
                var pageSize = Number(INTERMediator.pagedSize);
                var allCount = Number(INTERMediator.pagedAllCount);
                var disableClass = " IM_NAV_disabled";
                var node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode( (navLabel==null?"Record #":navLabel[4])
                        + (start+1)
                        + ((Math.min(start+pageSize,allCount)-start > 2)
                            ? ((navLabel==null?"〜":navLabel[5]) + Math.min(start+pageSize,allCount)) : '' )
                        + (navLabel==null?" / ":navLabel[6])
                        + (allCount) + (navLabel==null?"":navLabel[7])));
                node.setAttribute('class', 'IM_NAV_info');

                var node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(navLabel==null?'<<':navLabel[0]));
                node.setAttribute('class', 'IM_NAV_button' + (start == 0 ? disableClass : "") );
                INTERMediator.addEvent(node,'click',function(){
                    INTERMediator.startFrom = 0;
                    INTERMediator.construct(true);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(navLabel==null?'<':navLabel[1]));
                node.setAttribute('class', 'IM_NAV_button' + (start == 0 ? disableClass : ""));
                var prevPageCount = ( start - pageSize > 0 ) ? start - pageSize : 0;
                INTERMediator.addEvent(node,'click',function(){
                    INTERMediator.startFrom = prevPageCount;
                    INTERMediator.construct(true);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(navLabel==null?'>':navLabel[2]));
                node.setAttribute('class', 'IM_NAV_button' + (start+pageSize >= allCount ? disableClass : ""));
                var nextPageCount = ( start + pageSize < allCount ) ? start + pageSize :
                        ((allCount - pageSize > 0) ? start : 0);
                INTERMediator.addEvent(node,'click',function(){
                    INTERMediator.startFrom = nextPageCount;
                    INTERMediator.construct(true);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(navLabel==null?'>>':navLabel[3]));
                node.setAttribute('class', 'IM_NAV_button' + (start+pageSize >= allCount ? disableClass : ""));
                var endPageCount = allCount - pageSize;
                INTERMediator.addEvent(node,'click',function(){
                    INTERMediator.startFrom = (endPageCount > 0) ? endPageCount : 0;
                    INTERMediator.construct(true);
                });

                for( var i = 0 ; i < deleteInsertOnNavi.length ; i++ )  {
                    switch ( deleteInsertOnNavi[i]['kind'] )    {
                        case 'INSERT':
                            node = document.createElement('SPAN');
                            navigation.appendChild(node);
                            node.appendChild(document.createTextNode('Insert: '+deleteInsertOnNavi[i]['table']));
                            node.setAttribute('class', 'IM_NAV_button');
                            INTERMediator.addEvent( node, 'click',
                                    new Function("INTERMediator.insertRecordFromNavi("
                                            + "'" + deleteInsertOnNavi[i]['table'] + "',"
                                            + "'" + deleteInsertOnNavi[i]['key'] + "'" + ");"));
                        break;
                        case 'DELETE':
                            node = document.createElement('SPAN');
                            navigation.appendChild(node);
                            node.appendChild(document.createTextNode('Delete: '+deleteInsertOnNavi[i]['table']));
                            node.setAttribute('class', 'IM_NAV_button');
                            INTERMediator.addEvent( node, 'click',
                                    new Function("INTERMediator.deleteRecordFromNavi("
                                            + "'" + deleteInsertOnNavi[i]['table'] + "',"
                                            + "'" + deleteInsertOnNavi[i]['key'] + "',"
                                            + "'" + deleteInsertOnNavi[i]['value'] + "'"
                                            + ");"));
                        break;
                    }
                }
            }
        }

        function appendCredit() {
            if ( document.getElementById('IM_CREDIT') == null ) {
                var body = document.getElementsByTagName('body')[0];
                var cNode = document.createElement('div');
                body.appendChild(cNode);
                cNode.style.backgroundColor = '#F6F7FF';
                cNode.style.height = '2px';
                cNode.setAttribute( 'id', 'IM_CREDIT')

                cNode = document.createElement('div');
                body.appendChild(cNode);
                cNode.style.backgroundColor = '#EBF1FF';
                cNode.style.height = '2px';

                cNode = document.createElement('div');
                body.appendChild(cNode);
                cNode.style.backgroundColor = '#E1EAFF';
                cNode.style.height = '2px';

                cNode = document.createElement('div');
                body.appendChild(cNode);
                cNode.setAttribute('align', 'right');
                cNode.style.backgroundColor = '#D7E4FF';
                cNode.style.padding = '2px';
                var spNode = document.createElement('span');
                cNode.appendChild(spNode);
                cNode.style.color = '#666666';
                cNode.style.fontSize = '7pt';
                var aNode = document.createElement('a');
                aNode.appendChild(document.createTextNode('INTER-Mediator'));
                aNode.setAttribute('href', 'http://msyk.net/im');
                aNode.setAttribute('target', '_href');
                spNode.appendChild(document.createTextNode('Generated by '));
                spNode.appendChild(aNode);
                spNode.appendChild(document.createTextNode(' Ver.@@@@2@@@@(@@@@1@@@@)'));
            }
        }
    },

    addEvent: function (node, evt, func) {
        if (node.addEventListener) {
            node.addEventListener(evt, func, false);
        } else if (node.attachEvent) {
            node.attachEvent('on' + evt, func);
        }
    },

    toNumber: function (str) {
        var s = '';
        for (var i = 0; i < str.length; i++) {
            var c = str.charAt(i);
            if ((c >= '0' && c <= '9') || c == '-' || c == '.') s += c;
        }
        return new Number(s);
    },

    numberFormat: function (str) {
        var s = new Array();
        var n = new Number(str);
        var sign = '';
        if (n < 0) {
            sign = '-';
            n = -n;
        }
        var f = n - Math.floor(n);
        if (f == 0) f = '';
        for (n = Math.floor(n); n > 0; n = Math.floor(n / 1000)) {
            if (n > 1000) {
                s.push(('000' + (n % 1000).toString()).substr(-3));
            } else {
                s.push(n);
            }
        }
        return sign + s.reverse().join(',') + f;
    },

    objectToString: function (obj)	{
        if ( obj == null ){
            return "'**NULL**'";
        }
        if ( typeof obj == 'object' )	{
            var str = '';
            if ( obj.constractor === Array )	{
                for ( var i =0 ; i < obj.length ; i++ )	{
                    str += INTERMediator.objectToString(obj[i])+", ";
                }
                return "["+str+"]";
            } else {
                for ( var key in obj )	{
                    str += "'" + key+"':"+INTERMediator.objectToString(obj[key])+", ";
                }
                return "{"+str+"}"
            }
        }
        else {
            return "'" + obj + "'";
        }
    }

}

