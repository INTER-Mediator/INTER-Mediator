/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

// Cleaning-up by http://jsbeautifier.org/
// Next Generation gets start
var INTERMediator = {
	separator: '@',
	// This must be refered as 'INTERMediator.separator'. Don't use 'this.separator'
	defDevider: '|',
	// Same as the "separator".
	updateRequredObject: null,
	keyFieldObject: null,
	messages: null,

	valueChange: function (idValue) {
		var newValue = null;
		var changedObj = document.getElementById(idValue);
		if (changedObj != null) {
			var objectSpec = INTERMediator.updateRequredObject[idValue];
			var currentVal = INTERMediator.db_query({
				records: 1,
				name: objectSpec['table']
			}, [objectSpec['field']], null, objectSpec['keying']);
			if (currentVal[0] == null || currentVal[0][objectSpec['field']] == null) {
				// ERROR
				alert("No information to update:");
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
	//	INTERMediator.flushMessage();
	},

	flushMessage: function () {
		for (var i = 0; i < INTERMediator.messages.length; i++) {
			debugOut(INTERMediator.messages[i]);
		}
		
		INTERMediator.messages = [];
	},
	/**
	 * Construct the Web Page with DB Data
	 * You should call here when you show the page.
	 */
	construct: function () {

		INTERMediator.updateRequredObject = {};
		INTERMediator.keyFieldObject = [];
		INTERMediator.messages = [];

		var titleAsLinkInfo = true;
		var classAsLinkInfo = true;
		var currentLevel = 0;
		var linkedNodes;
		var currentEncNumber = 1;
		var postSetFields = new Array();
		var previousId = '';

		// Root node is BODY tag.
		seekEnclosureNode(document.getElementsByTagName('BODY')[0], '');
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
		// Show messages
		//INTERMediator.flushMessage();
		
		/**
		 * Seeking nodes and if a node is an enclosure, proceed repeating.
		 */
		function seekEnclosureNode(node, currentRecord, currentTable) {
			var enclosure = null;
			var nType = node.nodeType;
			if (nType == 1) { // Work for an element
				if (isEnclosure(node, false)) { // Linked element and an
					// enclosure
					if (enclosure = expandEnclosure(node, currentRecord, currentTable))	{ // Expand the enclosure
						// expanded with foreign-value
					//	INTERMediator.messages.push("expanded with foreign-value =" + node.tagName
					//			+"/currentRecord="+objectToString(currentRecord));
					}
				} else {
					var childs = node.childNodes; // Check all child nodes.
					for (var i = 0; i < childs.length; i++) {
						if ( childs[i].nodeType == 1 )	{
						var checkingEncl = seekEnclosureNode(childs[i], currentRecord, currentTable);
					/*	INTERMediator.messages.push("seekEnclosureNode returned =" + childs[i].tagName
								+"/chekingEncl="+checkingEncl
								+"/node type="+childs[i].nodeType
								+"/currentRecord="+objectToString(currentRecord)
								);*/
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
		//			+"/currentRecord="+objectToString(currentRecord));

			currentLevel++;
			currentEncNumber++;
			var thisEncNumber = currentEncNumber;

			var encNodeTag = node.tagName;
			var repNodeTag = repeaterTagFromEncTag(encNodeTag);
			var repeaters = new Array(); // Collecting repeaters to this array.
			var childs = node.childNodes; // Check all child node of the enclosure.
			for (var i = 0; i < childs.length; i++) {
				if (childs[i].nodeType == 1 && childs[i].tagName == repNodeTag) {
					// If the element is a repeater.
					repeaters.push(childs[i]); // Record it to the array.
				}
			}
			linkedNodes = new Array(); // Collecting linked elements to this array.
			for (var i = 0; i < repeaters.length; i++) {
				var inDocNode = repeaters[i];
				repeaters[i] = repeaters[i].cloneNode(true);
				inDocNode.parentNode.removeChild(inDocNode);
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
				//	ds[targetKey]['repeat-control'];
				var foreignValue = '';
				var foreignField = '';
				if  (currentRecord[ds[targetKey]['join-field']] != null) {
					foreignValue = currentRecord[ds[targetKey]['join-field']];
					foreignField = ds[targetKey]['join-field'];
					INTERMediator.keyFieldObject.push({node:node,table:currentTable,field:foreignField});
				/*	INTERMediator.messages.push("seekEnclosureNode: " + objectToString(currentRecord)
							+ "/foreignValue=" + foreignValue
							+ "/foreignField=" + foreignField
							+ "/targetTable=" + currentTable
							+ "/node=" + node.tagName
							);*/
				}
				var targetRecords = INTERMediator.db_query(ds[targetKey], fieldList, foreignValue);
				// Access database and get records
				var linkedElmCounter = 1;
				var RecordCounter = 0;
				var eventLisnerPostAdding = new Array();
				// var currentEncNumber = currentLevel;
				for (var ix in targetRecords) { // for each record
					RecordCounter++;
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
								foreignvalue: foreignValue,
							};

							if (curTarget != null && curTarget.length > 0) {
								currentLinkedNodes[k].setAttribute(curTarget, curVal);
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
									currentLinkedNodes[k].innerHTML = curVal;
								}
							}
							//  }
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
						if (theNode.addEventListener) {
							theNode.addEventListener(eventLisnerPostAdding[i]['event'], eventLisnerPostAdding[i]['todo']);
						} else if (currentLinkedNodes[k].attachEvent) {
							theNode.attachEvent('on' + eventLisnerPostAdding[i]['evnet'], eventLisnerPostAdding[i]['todo']);
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
			if ((repeaterTag == 'TR' && enclosureTag == 'TBODY') || (repeaterTag == 'OPTION' && enclosureTag == 'SELECT') || (repeaterTag == 'LI' && enclosureTag == 'OL') || (repeaterTag == 'LI' && enclosureTag == 'UL')) return true;
			else if (enclosureTag == 'DIV') {
				var enclosureClass = getClassAttributeFromNode(enclosure);
				if (enclosureClass != null && enclosureClass.indexOf('_im_enclosure') >= 0) {
					var repeaterClass = getClassAttributeFromNode(repeater);
					if (repeaterTag == 'DIV' && repeaterClass != null && repeaterClass.indexOf('_im_repeater') >= 0) {
						return true;
					} else if (repeaterTag == 'INPUT') {
						var repeaterType = repeater.getAttribute('type');
						if (repeaterType != null && (repeaterType.indexOf('radio') >= 0 || repeaterType.indexOf('check') >= 0)) {
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
						var matched = classAttr.match(/IM\[(.*)\]/);
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
			if ((tagName == 'TBODY') || (tagName == 'UL') || (tagName == 'OL') || (tagName == 'SELECT') || (tagName == 'DIV' && className == '_im_repeater')) {
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
			if ((tagName == 'TR') || (tagName == 'LI') || (tagName == 'OPTION') || (tagName == 'DIV' && className == '_im_enclosure')) {
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
					} else {
						if (searchLinkedElement(node.childNodes[k])) {
							return true;
						}
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

/*
 * if repeater is linked node too, it could be missed 5/21 2010 msyk
 */

		function seekLinkedElement(node) {
			var enclosure = null;
			var nType = node.nodeType;
			if (nType == 1) {
				if (isLinkedElement(node)) {
					var currentEnclosure = getEnclosure(node);
					if (currentEnclosure == null) {
						linkedNodes.push(node);
						return null;
					} else {
						return currentEnclosure;
					}
				} else {
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
		// End of the prototype of INTERMediator class.
	},

	db_query: function (detaSource, fields, parentKeyVal, extraCondition) {
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
		if (extraCondition != null) {
			params += "&ext_cond=" + encodeURI(extraCondition);
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
			var dbresult = '';
			eval(myRequest.responseText);
		} catch (e) {
			INTERMediator.messages.push("ERROR in db_query=" + e + "/" + myRequest.responseText);
		}
		return dbresult;
	},

	db_update: function (objectSpec, newValue) {
		var params = "?access=update&table=" + encodeURI(objectSpec['table']);
		params += "&ext_cond=" + encodeURI(objectSpec['keying']);
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
			INTERMediator.messages.push("ERROR in db_query=" + e + "/" + myRequest.responseText);
		}
		return dbresult;
	}


}
// /////////////////////
var isDebug = false;
var isEdited = false;
var fieldIdList = new Array();
var tableTemplates = new Array();
var deleteRecords = new Array();
var insertRecords = new Array();
var modifiedIds = new Array();
var addedRowIds = new Array();
var serial = 987001;
var myRequest = null;

function saveRecord() {
	if (myRequest != null) {
		alert(getMessageString(109));
		return;
	}

	var valids = getvalidationParams();
	var elmStr = '';
	var postData = '';
	var tags = ['input', 'select', 'textarea'];
	var isValid = true;
	for (var j = 0; j < tags.length; j++) {
		var elements = document.getElementsByTagName(tags[j]);
		for (var i = 0; i < elements.length; i++) {
			var nameAttr = elements[i].getAttribute('name');
			if (nameAttr != null) {
				var validChkName = nameAttr;
				var splitNameAttr = nameAttr.split(INTERMediator.separator);
				if (splitNameAttr.length > 2) {
					validChkName = splitNameAttr[0] + INTERMediator.separator + splitNameAttr[1];
				}

				for (var k in valids) {
					if (valids[k]['field'] == validChkName) {
						if (valids[k]['rule'] == 'require') {
							if (elements[i].value == '') {
								var fName = (valids[k]['option']) ? valids[k]['option'] : validChkName;
								errorOut(getMessageString(110), fName);
								isValid = false;
							}
						} else if (valids[k]['rule'] == 'mail') {
							if (!elements[i].value.match(/[\w\d_-]+@[\w\d_-]+\.[\w\d._-]+/)) {
								var fName = (valids[k]['option']) ? valids[k]['option'] : validChkName;
								errorOut(getMessageString(111), fName);
								isValid = false;
							}
						}
					}
				}

				var isInclude = true;
				if (j == 0 && elements[i].getAttribute('type') == 'checkbox') {
					elmStr = elements[i].checked ? elements[i].value : '';
				} else if (j == 0 && elements[i].getAttribute('type') == 'radio') {
					isInclude = elements[i].checked;
					elmStr = elements[i].checked ? elements[i].getAttribute('value') : '';
				} else {
					elmStr = elements[i].value;
				}
				if (isInclude) {
					postData += '&' + encodeURIComponent(nameAttr) + '=' + encodeURIComponent(elmStr);
				}
			}
		}
	}
	if (!isValid) return;

	if (typeof beforeSave == 'function') {
		if (beforeSave() == false) return;
	}

	var seq = 0;
	for (var aTable in deleteRecords) {
		for (var i = 0; i < deleteRecords[aTable].length; i++) {
			postData += '&' + "__easypage__delete_table_" + seq + "=" + encodeURIComponent(aTable);
			postData += '&' + "__easypage__delete_key_" + seq + "=" + encodeURIComponent(deleteRecords[aTable][i]);
			seq++;
			if (isDebug) {
				debugOut('Delete Table:', aTable, deleteRecords[aTable][i]);
			}
		}
	}

	var seq = 0;
	for (var aTable in insertRecords) {
		for (var i = 0; i < insertRecords[aTable].length; i++) {
			postData += '&' + "__easypage__insert_table_" + seq + "=" + encodeURIComponent(aTable);
			postData += '&' + "__easypage__insert_id_" + seq + "=" + encodeURIComponent(insertRecords[aTable][i]);
			seq++;
			if (isDebug) {
				debugOut('Insert Table:', aTable, insertRecords[aTable][i]);
			}
		}
	}
	if (postData == '') {
		document.getElementById('__easypage_navigation_message').innerHTML = getMessageString(106);
		return;
	}
	postData = getDataSourceParams() + getOptionParams() + getDatabaseParams() + postData;
	myRequest = new XMLHttpRequest();
	myRequest.open("post", getSaveURL(), true, getAccessUser(), getAccessPassword());
	myRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
	myRequest.onreadystatechange = finishXMLHttpRequest;
	myRequest.send(postData.substr(1));
}

function finishXMLHttpRequest() {
	if (myRequest.readyState == 4) {
		var res = myRequest.responseXML;
		var str = childNodeValueNoError(res, 'message');
		if (str.length > 0) document.getElementById('__easypage_navigation_message').innerHTML = getMessageString(str);
		if (res != null && res.getElementsByTagName('error') != null) {
			var nodes = res.getElementsByTagName('error');
			for (var i = 0; i < nodes.length; i++) {
				var errorMsg = nodeValueNoError(nodes[i]);
				if (errorMsg != '') errorOut(errorMsg);
			}
		}
		if (res != null && res.getElementsByTagName('debug-message') != null) {
			var nodes = res.getElementsByTagName('debug-message');
			for (var i = 0; i < nodes.length; i++) {
				var errorMsg = nodeValueNoError(nodes[i]);
				if (errorMsg != '') debugOut(errorMsg);
			}
		}
		if (res != null && res.getElementsByTagName('generated') != null) {
			var nodes = res.getElementsByTagName('generated');
			for (var i = 0; i < nodes.length; i++) {
				var targetId = childNodeValueNoError(nodes[i], 'element-id');
				var targetVal = childNodeValueNoError(nodes[i], 'value');
				var target = document.getElementById(targetId);
				if (target.tagName == 'DIV') {
					target.innerHTML = targetVal;
				} else {
					target.value = targetVal;
				}
				if (isDebug) debugOut('Set the new generated id:', targetId, targetVal);
			}
		}
		deleteRecords = new Array();
		insertRecords = new Array();
		modifiedIds = new Array();
		myRequest = null;

		if (typeof afterSaveComplete == 'function') {
			if (afterSaveComplete() == false) return;
		}

	} else {
		document.getElementById('__easypage_navigation_message').innerHTML = getMessageString(104) + 'readyState=' + myRequest.readyState + "/" + myRequest.responseText;
	}
}

function childNodeValueNoError(node, tag) {
	if (node == null) return '';
	var str = '';
	var cNode = node.getElementsByTagName(tag);
	if (!navigator.appName.match(/Explorer/)) {
		for (var i = 0; i < cNode.length; i++) {
			str += cNode[i].textContent;
		}
	} else {
		for (var i = 0; i < cNode.length; i++) {
			for (var j = 0; j < cNode[i].childNodes.length; j++) {
				if (cNode[i].childNodes[j].nodeValue != null) str += cNode[i].childNodes[j].nodeValue;
			}
		}
	}
	return str;
}

function nodeValueNoError(node) {
	if (node == null) return '';
	var str = '';
	if (!navigator.appName.match(/Explorer/)) {
		str = node.textContent;
	} else {
		for (var i = 0; i < node.childNodes.length; i++) {
			if (node.childNodes[i].nodeValue != null) str += node.childNodes[i].nodeValue;
		}
	}
	return str;
}

function debugMode(bool) {
	isDebug = bool;
}

function modifiedField(id) {
	for (var i = 0; i < modifiedIds.length; i++) {
		if (modifiedIds[i] == id) {
			return;
		}
	}
	modifiedIds.push(id);

	if (typeof afterFieldModified == 'function') {
		if (afterFieldModified() == false) return;
	}
}

function doAtTheFinishing() {
	var modRecords = modifiedIds.length;
	for (var i in deleteRecords) modRecords += deleteRecords[i].length;
	for (var i in insertRecords) modRecords += insertRecords[i].length;
	if (modRecords != 0) return getMessageString(105);
}


function doAtTheStarting() {
	fieldIdList = new Array();
	var idAttr;
	var tags = ['input', 'select', 'textarea', 'div', 'a', 'img'];
	for (var j = 0; j < tags.length; j++) {
		var elements = document.getElementsByTagName(tags[j]);
		for (var i = 0; i < elements.length; i++) {
			var nameAttr = (j > 2) ? elements[i].getAttribute('title') : elements[i].getAttribute('name');
			if (nameAttr) {
				if (elements[i].getAttribute('id') != null && elements[i].getAttribute('id') != '') {
					idAttr = elements[i].getAttribute('id');
				} else {
					idAttr = new String(++serial);
					elements[i].setAttribute('id', idAttr);
				}
				fieldIdList[nameAttr] = idAttr;
				addEvent(elements[i], 'change', new Function('modifiedField(' + idAttr + ')'));
				addEvent(elements[i], 'keydown', new Function('modifiedField(' + idAttr + ')'));

				var sp = nameAttr.indexOf(this.separator);
				if (sp > 0) {
					var tbName = nameAttr.substr(0, sp);
					if (!tableTemplates[tbName]) {
						for (var target = elements[i]; target.tagName != 'TR'; target = target.parentNode);
						tableTemplates[tbName] = {
							'parent': target.parentNode,
							'template': target.cloneNode(true),
							'editable': false
						};
						addedRowIds[tbName] = new Array();
						target.parentNode.removeChild(target);
						if (isDebug) debugOut("Recognized Repeat Table", tbName);
					}
					if (j < 3) {
						tableTemplates[tbName]['editable'] = true;
					}
				}
			}
		}
	}
	if (isDebug) {
		var str = 'fieldIdList = ';
		for (var i in fieldIdList) str += '[' + i + ':' + fieldIdList[i] + '] ';
		debugOut(str);
	}
	initializeWithDBValues();
}

function deleteRecord() {

	if (typeof beforeDeleteRecord == 'function') {
		if (beforeDeleteRecord() == false) return;
	}

	if (typeof afterDeleteRecord == 'function') {
		if (afterDeleteRecord() == false) return;
	}
}

function newRecord() {
	if (modifiedIds.length != 0) if (!confirm(getMessageString(105))) return;

	if (typeof beforeNewRecord == 'function') {
		if (beforeNewRecord() == false) return;
	}

	for (var attrName in fieldIdList) {
		var target = document.getElementById(fieldIdList[attrName]);
		if (target) {
			if (target.tagName == 'DIV') {
				target.innerHTML = '';
			} else {
				target.value = '';
			}
		}
	}
	for (var tbName in addedRowIds) {
		for (var i = 0; i < addedRowIds[tbName].length; i++) {
			var target = document.getElementById(addedRowIds[tbName][i]);
			if (target) {
				target.parentNode.removeChild(target);
			}
		}
	}
	mainTableName = getMainTableName();
	insertRecords = new Array();
	insertRecords[mainTableName] = new Array(fieldIdList[getKeyFieldName(mainTableName)]);

	if (typeof afterNewRecord == 'function') {
		if (afterNewRecord() == false) return;
	}
}

function checkKeyFieldMainTable(key) {
	if (!fieldIdList[key] || document.getElementById(fieldIdList[key]).tagName == 'DIV') {
		var target = null;
		for (var i in fieldIdList) {
			if (i.indexOf(this.separator) < 0) {
				target = document.getElementById(fieldIdList[i]);
				if (target != null) break;
			}
		}
		if (target == null) target = document.getElementsByTagName('BODY')[0];
		var elm = document.createElement('input');
		elm.setAttribute('type', 'hidden');
		elm.setAttribute('name', key);
		elm.setAttribute('id', 'easypage_main_table_key_field');
		target.parentNode.appendChild(elm);
		fieldIdList[key] = 'easypage_main_table_key_field';
		if (isDebug) debugOut("Add the key field:" + key + " to the main table.");
	}
}

function deleteLineFromRepeatTable(tableName, trId, keyId) {
	if (idValue(keyId) == '') {
		errorOut(getMessageString(108));
	}
	debugOut('deleteLineFromRepeatTable', tableName, trId, keyId, idValue(keyId));

	if (typeof beforeTableRowDelete == 'function') {
		if (beforeTableRowDelete() == false) return;
	}

	if (!deleteRecords[tableName]) deleteRecords[tableName] = new Array();
	deleteRecords[tableName].push(idValue(keyId));
	var tr = document.getElementById(trId);
	tr.parentNode.removeChild(tr);

	if (typeof afterTableRowDelete == 'function') {
		if (afterTableRowDelete() == false) return;
	}
}

function addLineToRepeatTable(tableName) {
	if (typeof beforeTableRowInsert == 'function') {
		if (beforeTableRowInsert() == false) return;
	}

	var data = new Array();
	data[tableName + separator + getForeignKeyFieldName(tableName)] = fieldValue(getKeyFieldName(getMainTableName()));
	var keyFieldId = addToRepeat(tableName, data);
	if (!insertRecords[tableName]) insertRecords[tableName] = new Array();
	insertRecords[tableName].push(keyFieldId);

	if (typeof afterTableRowInsert == 'function') {
		if (afterTableRowInsert() == false) return;
	}

	debugOut('Called addLineToRepeatTable:', getForeignKeyFieldName(tableName), fieldValue(getKeyFieldName(getMainTableName())), keyFieldId);
}

function fieldValue(fName) {
	var target = document.getElementById(fieldIdList[fName]);
	if (!target) return '';
	if (target.tagName == 'DIV') return target.innerHTML;
	return target.value;
}

function idValue(id) {
	var target = document.getElementById(id);
	if (!target) return '';
	if (target.tagName == 'DIV') return target.innerHTML;
	return target.value;
}

function addRepeatTableControl(tableName, setting) {
	if (tableTemplates[tableName]) {
		var tbody = tableTemplates[tableName]['parent'];
		var trLine = tableTemplates[tableName]['template'];

		if (setting.match(/delete/)) {
			var td = document.createElement('TD');
			setClassAttributeToNode(td, 'easypage_table_control');
			trLine.appendChild(td);
			var aElm = document.createElement('span');
			setClassAttributeToNode(aElm, 'easypage_table_control_delete');
			aElm.appendChild(document.createTextNode(getMessageString(5)));
			td.appendChild(aElm);
		}

		if (setting.match(/insert/)) {
			var tfoot = tbody.parentNode.createTFoot();
			tbody.parentNode.insertBefore(tfoot, tbody);
			var tr = document.createElement('TR');
			tfoot.appendChild(tr);
			td = document.createElement('TD');
			td.setAttribute('colSpan', trLine.getElementsByTagName('TD').length + 1);
			td.setAttribute('align', 'right');
			setClassAttributeToNode(td, 'easypage_table_control');
			tr.appendChild(td);
			aElm = document.createElement('span');
			setClassAttributeToNode(aElm, 'easypage_table_control_insert');
			addEvent(aElm, 'click', new Function("addLineToRepeatTable('" + tableName + "');"));
			aElm.appendChild(document.createTextNode(getMessageString(4)));
			td.appendChild(aElm);
		}
	} else {
		errorOut('The table-control option has irrelevant table name');
	}
	if (isDebug) debugOut("Call function addRepeatTableControl: table=" + tableName);
}

function checkKeyFieldRepeatTable(tableName, key, fkey) {
	msg = '';
	if (!tableTemplates[tableName]['editable']) return;
	var tdTemplate = tableTemplates[tableName]['template'];
	if (tdTemplate == null) return;

	var keyFullName = tableName + separator + key;
	var fKeyFullName = tableName + separator + fkey;
	var divNodes = document.getElementsByTagName('DIV');
	var isDivKey = false,
		isDivFKey = false;
	for (var i = 0; i < divNodes.length; i++) {
		var nameAttr = divNodes[i].getAttribute('title');
		if (nameAttr) {
			if (nameAttr == keyFullName) isDivKey = true;
			if (nameAttr == fKeyFullName) isDivFKey = true;
		}
	}
	if (key != '' && (!fieldIdList[keyFullName] || isDivKey)) {
		var newIdAttr = 'easypage_repeat_table_key_field_' + tableName;
		var target = tableTemplates[tableName]['template'].getElementsByTagName('TD')[0];
		var elm = document.createElement('input');
		elm.setAttribute('type', 'hidden');
		elm.setAttribute('name', keyFullName);
		elm.setAttribute('id', newIdAttr);
		target.appendChild(elm);
		fieldIdList[keyFullName] = newIdAttr;
		msg += "/ Add the key field:" + key + " to table:" + tableName;
	}
	if (fkey != '' && (!fieldIdList[fKeyFullName] || isDivFKey)) {
		var newIdAttr = 'easypage_repeat_table_foreign_key_field_' + tableName;
		var target = tableTemplates[tableName]['template'].getElementsByTagName('TD')[0];
		var elm = document.createElement('input');
		elm.setAttribute('type', 'hidden');
		elm.setAttribute('name', fKeyFullName);
		elm.setAttribute('id', newIdAttr);
		target.appendChild(elm);
		fieldIdList[keyFullName] = newIdAttr;
		msg += "/ Add the forreign key field:" + key + " to table:" + tableName;
	}
	if (isDebug) debugOut("Call function checkKeyFieldRepeatTable: table=" + tableName + ", key=" + key + ", foreign key=" + fkey + msg);
}

var n = 0;

function addToRepeat(table, data) {
	var triggers = getTrrigerParams();
	var keyFieldName = table + separator + getKeyFieldName(table);
	if (data[keyFieldName] == '') {
		errorOut(getMessageString(107));
	}
	var keyFieldId;
	var cloned = tableTemplates[table]['template'].cloneNode(true);
	cloned.setAttribute('id', (++serial));
	var trId = serial;
	var tags = ['input', 'select', 'textarea', 'div', 'a', 'img'];
	var postCheck = new Array();
	var checkValues = new Array();
	for (var j = 0; j < tags.length; j++) {
		var elements = cloned.getElementsByTagName(tags[j]);
		for (var i = 0; i < elements.length; i++) {
			var nameAttr = (j > 2) ? elements[i].getAttribute('title') : elements[i].getAttribute('name');
			if (nameAttr) {
				elements[i].setAttribute((j > 2) ? 'title' : 'name', nameAttr + separator + n);
				elements[i].setAttribute('id', (++serial));
				if (nameAttr == keyFieldName && elements[i].tagName != 'DIV') keyFieldId = serial;
				if (data[nameAttr]) {
					if (elements[i].tagName == 'DIV') {
						elements[i].innerHTML = data[nameAttr];
					} else if (elements[i].tagName == 'SELECT') {
						elements[i].value = data[nameAttr];
					} else if (elements[i].tagName == 'INPUT' && elements[i].getAttribute('type') == 'checkbox') {
						elements[i].checked = (data[nameAttr] != '');
					} else if (elements[i].tagName == 'INPUT' && elements[i].getAttribute('type') == 'radio') {
						checkValues[serial] = elements[i].value;
						if (elements[i].value == data[nameAttr]) {
							postCheck[serial] = true;
						} else {}
					} else if (elements[i].tagName == 'TEXTAREA') {
						elements[i].value = data[nameAttr];
					} else if (elements[i].tagName == 'A') {
						elements[i].setAttribute('href', data[nameAttr]);
					} else if (elements[i].tagName == 'IMG') {
						elements[i].setAttribute('src', data[nameAttr]);
					} else {
						elements[i].value = data[nameAttr];
					}
				}
				addEvent(elements[i], 'change', new Function('modifiedField(' + serial + ');'));
				addEvent(elements[i], 'keydown', new Function('modifiedField(' + serial + ');'));
				for (var ix in triggers) {
					if (triggers[ix]['field'] == nameAttr) {
						addEvent(elements[i], triggers[ix]['event'], new Function(triggers[ix]['function'] + '(this);'));
						// debugOut( 'addEvent', nameAttr, triggers[ix]['function']);
					}
				}
				// debugOut( 'addToRepeat', nameAttr, data[nameAttr], keyFieldName);
			}
		}
	}
	tableTemplates[table]['parent'].appendChild(cloned);
	addedRowIds[table].push(trId);

	elements = cloned.getElementsByTagName('SPAN');
	for (var i = 0; i < elements.length; i++) {
		if (getClassAttributeFromNode(elements[i]).indexOf('easypage_table_control_delete') >= 0) {
			addEvent(elements[i], 'click', new Function("deleteLineFromRepeatTable('" + table + "','" + trId + "','" + keyFieldId + "')"));
		}
	}

	for (var e in postCheck) {
		document.getElementById(e).checked = true;
	}
	for (var e in checkValues) {
		document.getElementById(e).value = checkValues[e];
	}
	n++;
	return keyFieldId;
}

function setValue(field, value) {
	var triggers = getTrrigerParams();
	var elmId = fieldIdList[field];
	var target = document.getElementById(elmId);
	if (target == null) return;
	if (target.tagName == 'DIV') target.innerHTML = value;
	else if (target.tagName == 'SELECT') {
		target.value = value;
	} else if (target.tagName == 'INPUT' && target.getAttribute('type') == 'checkbox') {
		target.checked = (value != '');
	} else if (target.tagName == 'INPUT' && target.getAttribute('type') == 'radio') {
		for (var i = elmId; i > 987000; i--) {
			target = document.getElementById(i);
			if (target.tagName != 'INPUT' || target.getAttribute('type') != 'radio') break;
			if (target.value == value) {
				target.checked = true;
				break;
			}
		}
	} else if (target.tagName == 'TEXTAREA') {
		target.value = value;
	} else if (target.tagName == 'A') {
		target.setAttribute('href', data[nameAttr]);
	} else if (target.tagName == 'IMG') {
		target.setAttribute('src', data[nameAttr]);
	} else {
		target.value = value;
	}
	addEvent(target, 'change', new Function('modifiedField(' + serial + ');'));
	addEvent(target, 'keydown', new Function('modifiedField(' + serial + ');'));
	for (var ix in triggers) {
		if (triggers[ix]['field'] == field) {
			addEvent(target, triggers[ix]['event'], new Function(triggers[ix]['function'] + '(this);'));
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

function getNameAttributeForAllTags(node) {
	var tag = node.tagName;
	if (tag == 'INPUT') {
		return node.getAttribute('name');
	} else if (tag == 'TEXTAREA') {
		return node.getAttribute('name');
	} else if (tag == 'SELECT') {
		return node.getAttribute('name');
	} else if (tag == 'DIV') {
		return node.getAttribute('title');
	} else if (tag == 'A') {
		return node.getAttribute('title');
	} else if (tag == 'IMG') {
		return node.getAttribute('title');
	}
	return null;
}

function showNoRecordMessage() {
	errorOut(getMessageString(101));
}

function getElementNodeByName(nameAttr, originNode) {
	var field = getNameAttributeForAllTags(originNode);
	var comps = field.split(separator);
	var targetName = nameAttr;
	if (comps.length > 2) {
		targetName = nameAttr + separator + comps[2];
	}
	var tags = ['input', 'select', 'textarea', 'div', 'a', 'img'];
	for (var j = 0; j < tags.length; j++) {
		var elements = document.getElementsByTagName(tags[j]);
		for (var i = 0; i < elements.length; i++) {
			var nAttr = (j > 2) ? elements[i].getAttribute('title') : elements[i].getAttribute('name');
			if (nAttr) {
				if (targetName == nAttr) {
					return elements[i];
				}
			}
		}
	}
	return null;
}

function getElementNodesByName(nameAttr) {
	var nodes = new Array();
	var tags = ['input', 'select', 'textarea', 'div', 'a', 'img'];
	for (var j = 0; j < tags.length; j++) {
		var elements = document.getElementsByTagName(tags[j]);
		for (var i = 0; i < elements.length; i++) {
			var nAttr = (j > 2) ? elements[i].getAttribute('title') : elements[i].getAttribute('name');
			if (nAttr) {
				if (nAttr.indexOf(nameAttr) == 0) {
					nodes.push(elements[i]);
				}
			}
		}
	}
	return nodes;
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

function appendCredit() {
	var body = document.getElementsByTagName('body')[0];
	var cNode = document.createElement('div');
	body.appendChild(cNode);
	cNode.style.backgroundColor = '#F6F7FF';
	cNode.style.height = '2px';

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
function errorOut(str, msg1, msg2, msg3) {
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