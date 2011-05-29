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
    debugMode: true,
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
    // Start from this number of record for "skipping" table
    pagedSize: 0,
    pagedAllCount: 0,
	updateRequredObject: null,
	keyFieldObject: null,
	messages: null,
    rootEnclosure: null,
    // Storing to retrieve the page to initial condtion.
    // {node:xxx, parent:xxx, currentRoot:xxx, currentAfter:xxxx}

    //=================================
    // Message for Programmers
    //=================================
    flushMessage: function () {
        for (var i = 0; i < INTERMediator.messages.length; i++) {
            INTERMediator.debugOut(INTERMediator.messages[i]);
        }
        INTERMediator.messages = [];
    },

    errorOut: function (str, msg1, msg2, msg3) {
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
    },

    debugOut: function debugOut(str) {
        if (INTERMediator.debugMode)    {
            var debugNode = document.getElementById('easypage_debug_panel_4873643897897');
            if (debugNode == null) {
                debugNode = document.createElement('div');
                debugNode.setAttribute('id', 'easypage_debug_panel_4873643897897');
                debugNode.style.backgroundColor = '#DDDDDD';
                var clearButton = document.createElement('button');
                clearButton.setAttribute('title', 'clear');
                addEvent(clearButton, 'click', function () {
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
            var message = new Array();
            for (var i = 0; i < debugOut.arguments.length; i++) message.push(new String(debugOut.arguments[i]));
            debugNode.appendChild(document.createTextNode(message.join(', ')));
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
				// ERROR
				answer = confirm("Other people might be updated. Initially="+objectSpec['initialvalue']
						+"/Current="+currentVal
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
						location.reload();
					//	alert(INTERMediator.keyFieldObject[i]['table']+"/"+INTERMediator.keyFieldObject[i]['field']);
					}
				}
			}
		}
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
                params += "&ext_cond" + extCount + "operator=" + encodeURI(criteraObject["operator"]);
                params += "&ext_cond" + extCount + "value=" + encodeURI(criteraObject["value"]);
                extCount++;
            }
        }

		var appPath = IM_getEntryPath();

		INTERMediator.messages.push("Expand Table=" + appPath + params);

		myRequest = new XMLHttpRequest();
		try {
			myRequest.open('GET', appPath + params, false);
			// myRequest.setRequestHeader('Content-Type','application/x-www-form-urlencoded;
			// charset=UTF-8');
			myRequest.send(null);
			INTERMediator.messages.push("DB Response: " + myRequest.responseText);
			eval(myRequest.responseText);
            if (( detaSource['paging'] != null) && ( detaSource['paging'] == true ))  {
                INTERMediator.pagedSize = detaSource['records'];
                INTERMediator.pagedAllCount = resultCount;
            }

		} catch (e) {
			INTERMediator.messages.push("ERROR in db_query=" + e + "/" + myRequest.responseText);
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

		INTERMediator.messages.push("Update Request=" + appPath + params);

		myRequest = new XMLHttpRequest();
		try {
			myRequest.open('GET', appPath + params, false);
			// myRequest.setRequestHeader('Content-Type','application/x-www-form-urlencoded;
			// charset=UTF-8');
			myRequest.send(null);
			var dbresult = '';
			eval(myRequest.responseText);
		} catch (e) {
			INTERMediator.messages.push("ERROR in db_update=" + e + "/" + myRequest.responseText);
		}
		return dbresult;
	},

    db_delete: function()   {
        
    },

    db_createRecord: function() {

    },

    //=================================
    // Construct Page
    //=================================
    /**
     * Construct the Web Page with DB Data
     * You should call here when you show the page.
     */
    construct: function (fromStart, doAfterConstruct) {

        INTERMediator.keyFieldObject = [];
        INTERMediator.messages = [];

        var titleAsLinkInfo = true;
        var classAsLinkInfo = true;
        var currentLevel = 0;
        var linkedNodes;
        var currentEncNumber = 1;
        var postSetFields = new Array();
        var firstEnclosure = true;

        if ( fromStart )    {
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
        } else {
            firstEnclosure = false;
            INTERMediator.rootEnclosure = null;
            INTERMediator.startFrom = 0;
            INTERMediator.updateRequredObject = {};
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
            var targetNode = getEnclosedNode( enclosure, currentNode['table'], currentNode['field']);
            if ( targetNode )	{
                currentNode['target'] = targetNode.getAttribute('id');
            //	debugOut("####"+targetNode.getAttribute('id'));
            }
        }

        navigationSetup();
        appendCredit();

        if ( doAfterConstruct != null ) {
            doAfterConstruct();
        }

        // Show messages
    	INTERMediator.flushMessage();

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
                    if ( firstEnclosure )   {
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
        //	INTERMediator.messages.push("expandEnclosure =" + node.tagName
        //            +"/currentRecord="+objectToString(currentRecord));

            currentLevel++;
            currentEncNumber++;
            var thisEncNumber = currentEncNumber;

            var encNodeTag = node.tagName;
            var repNodeTag = repeaterTagFromEncTag(encNodeTag);
            var repeatersOriginal = new Array(); // Collecting repeaters to this array.
            var repeaters = new Array(); // Collecting repeaters to this array.
            var childs = node.childNodes; // Check all child node of the enclosure.
            for (var i = 0; i < childs.length; i++) {
                if (childs[i].nodeType == 1 && childs[i].tagName == repNodeTag) {
                    // If the element is a repeater.
                    repeatersOriginal.push(childs[i]); // Record it to the array.
                }
            }
            for (var i = 0; i < repeatersOriginal.length; i++) {
                var inDocNode = repeatersOriginal[i];
                var parentOfRep = repeatersOriginal[i].parentNode;
                var cloneNode = repeatersOriginal[i].cloneNode(true);
                repeaters.push(cloneNode);
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

                //     INTERMediator.messages.push("node table ="+nodeInfoArray['table']);
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
            INTERMediator.messages.push("maxTableName =" + maxTableName);
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
            // INTERMediator.messages.push("targetKey ="+targetKey);
            if (targetKey != '') {
                var foreignValue = '';
                var foreignField = '';
                if  (currentRecord[ds[targetKey]['join-field']] != null) {
                    foreignValue = currentRecord[ds[targetKey]['join-field']];
                    foreignField = ds[targetKey]['join-field'];
                    INTERMediator.keyFieldObject.push({node:node,table:currentTable,field:foreignField});
                }
                var targetRecords = INTERMediator.db_query(ds[targetKey], fieldList, foreignValue, null, true);
                // Access database and get records
                var linkedElmCounter = 1;
                var RecordCounter = 0;
                var eventLisnerPostAdding = new Array();

                // var currentEncNumber = currentLevel;
                for (var ix in targetRecords) { // for each record
                    RecordCounter++;

                    repeaters = new Array();
                    for (var i = 0; i < repeatersOriginal.length; i++) {
                        var cloneNode = repeatersOriginal[i].cloneNode(true);
                        repeaters.push(cloneNode);
                    }
                    linkedNodes = new Array(); // Collecting linked elements to this array.
                    for (var i = 0; i < repeaters.length; i++) {
                         seekLinkedElement(repeaters[i]);
                    }
                    var currentLinkedNodes = linkedNodes; // Store in the local variable

                    var keyingValue = ds[targetKey]['key'] + "=" + targetRecords[ix][ds[targetKey]['key']];
                    for (var k = 0; k < currentLinkedNodes.length; k++) {
                        // for each linked element
                        var idValue = 'IM' + thisEncNumber + '-' + linkedElmCounter;
                        currentLinkedNodes[k].setAttribute('id', idValue);
                        linkedElmCounter++;

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

                        eventLisnerPostAdding.push({
                            'id': idValue,
                            'event': 'change',
                            'todo': new Function('INTERMediator.valueChange("' + idValue + '")')
                        });

                        for (var j = 0; j < linkInfoArray.length; j++) {
                            // for each info Multiple replacement definitions
                            // for one node is prohibited.
                            var nInfo = getNodeInfoArray(linkInfoArray[j]);
                            var curVal = targetRecords[ix][nInfo['field']];
                            var curTarget = nInfo['target'];
                            // INTERMediator.messages.push("curTarget ="+curTarget+"/curVal ="+curVal);
                            //    if ( curVal != null )	{
                            // Store the key field value and current value for update
                            INTERMediator.updateRequredObject[idValue] = {
                                targetattribute: curTarget,
                                initialvalue: curVal,
                                table: ds[targetKey]['name'],
                                field: nInfo['field'],
                                keying: keyingValue,
                                foreignfield: foreignField,
                                foreignvalue: foreignValue
                            };

                        //    INTERMediator.messages.push("curTarget ="+curTarget+", curVal ="+curVal);
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
                                    if (typeAttr == 'checkbox') { // set the value
                                        // to checkbox
                                        var valueAttr = currentLinkedNodes[k].getAttribute('value');
                                        if (valueAttr == curVal) {
                                            currentLinkedNodes[k].checked = true;
                                        } else {
                                            currentLinkedNodes[k].checked = false;
                                        }
                                    } else if (typeAttr == 'radio') { // set the
                                        // value to
                                        // radio
                                        // button
                                        var valueAttr = currentLinkedNodes[k].getAttribute('value');
                                        if (valueAttr == curVal) {
                                            currentLinkedNodes[k].checked = true;
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
                    for (var i = 0; i < repeaters.length; i++) {
                        var newNode = repeaters[i].cloneNode(true);
                        node.appendChild(newNode);
                        seekEnclosureNode(newNode, targetRecords[ix], ds[targetKey]['name']);
                    }
                    // Event listener should add after adding node to document.
                    for (var i = 0; i < eventLisnerPostAdding.length; i++) {
                        var theNode = document.getElementById(eventLisnerPostAdding[i]['id']);
                        if ( theNode == null )  {
                            INTERMediator.messages.push("eventLisnerPostAdding null id=" + eventLisnerPostAdding[i]['id']);
                        } else {
                            if (theNode.addEventListener) {
                                theNode.addEventListener(eventLisnerPostAdding[i]['event'], eventLisnerPostAdding[i]['todo'],false);
                            } else if (currentLinkedNodes[k].attachEvent) {
                                theNode.attachEvent('on' + eventLisnerPostAdding[i]['evnet'], eventLisnerPostAdding[i]['todo']);
                            }
                        }
                    }
                }
            } else {
                INTERMediator.messages.push("Cant determine the Table Name: " + linkDefsHash.toString());
            }
            // currentLevel--;
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
            if (!navigator.appName.match(/Explorer/)) {
                node.setAttribute('class', className);
            } else {
                node.setAttribute('className', className);
            }
        }

        function getClassAttributeFromNode(node) {
            if (node == null) return '';
            var str = '';
            if (!navigator.appName.match(/Explorer/)) {
                str = node.getAttribute('class');
            } else {
                str = node.getAttribute('className');
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

                var start = Number(INTERMediator.startFrom);
                var pageSize = Number(INTERMediator.pagedSize);
                var allCount = Number(INTERMediator.pagedAllCount);
                var disableClass = " IM_NAV_disabled";
                var node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode( (navLabel==null?"Record #":navLabel[4])
                        + (start+1) + (navLabel==null?"〜":navLabel[5])
                        + Math.min(start+pageSize,allCount) + (navLabel==null?" / ":navLabel[6])
                        + (allCount) + (navLabel==null?"":navLabel[7])));
                node.setAttribute('class', 'IM_NAV_info');

                var node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(navLabel==null?'<<':navLabel[0]));
                node.setAttribute('class', 'IM_NAV_button' + (start == 0 ? disableClass : "") );
                addEvent(node,'click',function(){
                    INTERMediator.startFrom = 0;
                    INTERMediator.construct(true);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(navLabel==null?'<':navLabel[1]));
                node.setAttribute('class', 'IM_NAV_button' + (start == 0 ? disableClass : ""));
                var prevPageCount = ( start - pageSize > 0 ) ? start - pageSize : 0;
                addEvent(node,'click',function(){
                    INTERMediator.startFrom = prevPageCount;
                    INTERMediator.construct(true);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(navLabel==null?'>':navLabel[2]));
                node.setAttribute('class', 'IM_NAV_button' + (start+pageSize >= allCount ? disableClass : ""));
                var nextPageCount = ( start + pageSize < allCount ) ? start + pageSize :
                        ((allCount - pageSize > 0) ? start : 0);
                addEvent(node,'click',function(){
                    INTERMediator.startFrom = nextPageCount;
                    INTERMediator.construct(true);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(navLabel==null?'>>':navLabel[3]));
                node.setAttribute('class', 'IM_NAV_button' + (start+pageSize >= allCount ? disableClass : ""));
                var endPageCount = allCount - pageSize;
                addEvent(node,'click',function(){
                    INTERMediator.startFrom = (endPageCount > 0) ? endPageCount : 0;
                    INTERMediator.construct(true);
                });
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
    }
}

function addEvent(node, evt, func) {
	if (node.addEventListener) {
		node.addEventListener(evt, func, false);
	} else if (node.attachEvent) {
		node.attachEvent('on' + evt, func);
	}
}

function toNumber(str) {
	var s = '';
	for (var i = 0; i < str.length; i++) {
		var c = str.charAt(i);
		if ((c >= '0' && c <= '9') || c == '-' || c == '.') s += c;
	}
	return new Number(s);
}

function numberFormat(str) {
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
}

function objectToString(obj)	{
	if ( obj == null ){
		return "**NULL**";
	}
	if ( typeof obj == 'object' )	{
		var str = '';
		if ( obj.constractor === Array )	{
			for ( var i =0 ; i < obj.length ; i++ )	{
				str += objectToString(obj[i])+", ";
			}
			return "["+str+"]";
		} else {
			for ( var key in obj )	{
				str += key+":"+objectToString(obj[key])+", ";
			}
			return "{"+str+"}"
		}
	}
	else {
		return obj;
	}
}
