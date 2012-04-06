/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
// Cleaning-up by PHPStrom's formatting feature.

var INTERMediator = {
    /*
     Properties
     */
    debugMode:false,
    // Show the debug messages at the top of the page.
    separator:'@',
    // This must be referred as 'INTERMediator.separator'. Don't use 'this.separator'
    defDivider:'|',
    // Same as the "separator".
    additionalCondition:[],
    // This array should be [{tableName: [{field:xxx,operator:xxx,value:xxxx}]}, ... ]
    additionalSortKey:[],
    // This array should be [{tableName: [{field:xxx,direction:xxx}]}, ... ]
    defaultTargetInnerHTML:false,
    // For general elements, if target isn't specified, the value will be set to innerHTML.
    // Otherwise, set as the text node.
    navigationLabel:null,
    // Navigation is controlled by this parameter.
    startFrom:0,
    // Start from this number of record for "skipping" records.
    pagedSize:0,
    pagedAllCount:0,
    currentEncNumber:0,
    isIE:false,
    ieVersion:-1,
    titleAsLinkInfo:true,
    classAsLinkInfo:true,
    noRecordClassName:"_im_for_noresult_",

    // Remembering Objects
    updateRequiredObject:null,
    /*
     {   id-value:               // For the node of this id attribute.
     {targetattribute:,      // about target
     initialvalue:,          // The value from database.
     name:
     field:id,               // about target field
     keying:id=1,            // The key field specifier to identify this record.
     foreignfield:,          // foreign field name
     foreignvalue:, }        // foreign field value
     */
    keyFieldObject:null,
    /* inside of keyFieldObject
     {node:xxx,         // The node information
     original:xxx       // Copy of childs
     name:xxx,             // name of context
     foreign-value:Recordset as {f1:v1, f2:v2, ..} ,not [{field:xx, value:xx},..]
     target:xxxx}       // Related (depending) node's id attribute value.
     */
    rootEnclosure:null,
    // Storing to retrieve the page to initial condtion.
    // {node:xxx, parent:xxx, currentRoot:xxx, currentAfter:xxxx}
    errorMessages:[],
    debugMessages:[],


    //=================================
    // Message for Programmers
    //=================================
    flushMessage:function () {
        var debugNode, title, body, i, clearButton, tNode ;
        if (INTERMediator.errorMessages.length > 0) {
            debugNode = document.getElementById('_im_error_panel_4873643897897');
            if (debugNode == null) {
                debugNode = document.createElement('div');
                debugNode.setAttribute('id', '_im_error_panel_4873643897897');
                debugNode.style.backgroundColor = '#FFDDDD';
                title = document.createElement('h3');
                title.appendChild(document.createTextNode('Error Info from INTER-Mediator'));
                title.appendChild(document.createElement('hr'));
                debugNode.appendChild(title);
                body = document.getElementsByTagName('body')[0];
                body.insertBefore(debugNode, body.firstChild);
            }
            debugNode.appendChild(document.createTextNode("============ERROR MESSAGE on "+new Date()+"============"));
            debugNode.appendChild(document.createElement('hr'));
            for (i = 0; i < INTERMediator.errorMessages.length; i++) {
                debugNode.appendChild(document.createTextNode(INTERMediator.errorMessages[i]));
                debugNode.appendChild(document.createElement('hr'));
            }
        }
        if (INTERMediator.debugMode && INTERMediator.debugMessages.length > 0) {
            debugNode = document.getElementById('_im_debug_panel_4873643897897');
            if (debugNode == null) {
                debugNode = document.createElement('div');
                debugNode.setAttribute('id', '_im_debug_panel_4873643897897');
                debugNode.style.backgroundColor = '#DDDDDD';
                clearButton = document.createElement('button');
                clearButton.setAttribute('title', 'clear');
                INTERMediatorLib.addEvent(clearButton, 'click', function () {
                    var target = document.getElementById('_im_debug_panel_4873643897897');
                    target.parentNode.removeChild(target);
                });
                tNode = document.createTextNode('clear');
                clearButton.appendChild(tNode);
                title = document.createElement('h3');
                title.appendChild(document.createTextNode('Debug Info from INTER-Mediator'));
                title.appendChild(clearButton);
                title.appendChild(document.createElement('hr'));
                debugNode.appendChild(title);
                body = document.getElementsByTagName('body')[0];
                body.insertBefore(debugNode, body.firstChild);
            }
            debugNode.appendChild(document.createTextNode("============DEBUG INFO on "+new Date()+"============"));
            debugNode.appendChild(document.createElement('hr'));
            for ( i = 0; i < INTERMediator.debugMessages.length; i++) {
                debugNode.appendChild(document.createTextNode(INTERMediator.debugMessages[i]));
                debugNode.appendChild(document.createElement('hr'));
            }
        }
        INTERMediator.errorMessages = [];
        INTERMediator.debugMessages = [];
    },

    //=================================
    // User interactions
    //=================================
    /*
     valueChange
     Parameters:
     */
    valueChange:function (idValue) {
        var changedObj = document.getElementById(idValue);
        if (changedObj != null) {
            if (INTERMediatorOnPage.getOptionsTransaction() == 'none') {
                INTERMediator.updateRequiredObject[idValue]['edit'] = true;
            } else {
                INTERMediator.updateDB(idValue);
                INTERMediator.flushMessage();
            }
        }
    },

    updateDB:function (idValue) {
        var newValue = null, changedObj;
        changedObj = document.getElementById(idValue);
        if (changedObj != null) {
            INTERMediatorOnPage.retrieveAuthInfo();
            var objType = changedObj.getAttribute('type');
            if ( objType == 'radio' && ! changedObj.checked )  {
                return;
            }
            var objectSpec = INTERMediator.updateRequiredObject[idValue];
            var keyingComp = objectSpec['keying'].split('=');
            var keyingField = keyingComp[0];
            keyingComp.shift();
            var keyingValue = keyingComp.join('=');
            try {
                var currentVal = INTERMediaotr_DBAdapter.db_query({
                    name:objectSpec['name'],
                    records:1,
                    paging:objectSpec['paging'],
                    fields:[objectSpec['field']],
                    parentkeyvalue:null,
                    conditions:[
                        {field:keyingField, operator:'=', value:keyingValue}
                    ],
                    useoffset:false});
            } catch (ex) {
                if (ex == "_im_requath_request_") {
                    if (INTERMediatorOnPage.requireAuthentication && !INTERMediatorOnPage.isComplementAuthData()) {
                        INTERMediatorOnPage.authChallenge = null;
                        INTERMediatorOnPage.authHashedPassword = null;
                        INTERMediatorOnPage.authenticating(
                            function () {
                                INTERMediator.updateDB(idValue);
                            }
                        );
                        return;
                    }
                }
            }

            if (currentVal.recordset == null || currentVal.recordset[0] == null
                || currentVal.recordset[0][objectSpec['field']] == null) {
                alert(INTERMediatorLib.getInsertedString(INTERMediatorOnPage.getMessages()[1003], [objectSpec['field']]));
                INTERMediator.flushMessage();
                return;
            }
            if (currentVal.count > 1) {
                var response = confirm(INTERMediatorOnPage.getMessages()[1024]);
                if (!response) {
                    INTERMediator.flushMessage();
                    return;
                }
            }
            currentVal = currentVal.recordset[0][objectSpec['field']];
            var isDiffrentOnDB = (objectSpec['initialvalue'] != currentVal);

            if (changedObj.tagName == 'TEXTAREA') {
                newValue = changedObj.value;
            } else if (changedObj.tagName == 'SELECT') {
                newValue = changedObj.value;
            } else if (changedObj.tagName == 'INPUT') {

                if (objType != null) {
                    if (objType == 'checkbox') {
                        var valueAttr = changedObj.getAttribute('value');
                        if (changedObj.checked) {
                            newValue = valueAttr;
                            isDiffrentOnDB = (valueAttr == currentVal);
                        } else {
                            newValue = '';
                            isDiffrentOnDB = (valueAttr != currentVal);
                        }
                    } else if (objType == 'radio') {
                        newValue = changedObj.value;
                    } else { //text, password
                        newValue = changedObj.value;
                    }
                }
            }

            if ( isDiffrentOnDB ) {
                // The value of database and the field is diffrent. Others must be changed this field.
                if ( ! confirm(INTERMediatorLib.getInsertedString(
                    INTERMediatorOnPage.getMessages()[1001],
                    [objectSpec['initialvalue'], newValue, currentVal]))) {
                    INTERMediator.flushMessage();
                    return;
                }
                INTERMediatorOnPage.retrieveAuthInfo(); // This is required. Why?
            }

            if (newValue != null) {
                var criteria = objectSpec['keying'].split('=');
                try {
                    INTERMediaotr_DBAdapter.db_update({
                        name:objectSpec['name'],
                        conditions:[{field:criteria[0], operator:'=', value:criteria[1]}],
                        dataset:[{field:objectSpec['field'], value:newValue}]});
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        if (ex == "_im_requath_request_") {
                            if (INTERMediatorOnPage.requireAuthentication && !INTERMediatorOnPage.isComplementAuthData()) {
                                INTERMediatorOnPage.authChallenge = null;
                                INTERMediatorOnPage.authHashedPassword = null;
                                INTERMediatorOnPage.authenticating(
                                    function () {
                                        INTERMediator.updateDB(idValue);
                                    }
                                );
                                return;
                            }
                        }
                    }
                }

                objectSpec['initialvalue'] = newValue;
                var updateNodeId = objectSpec['updatenodeid'];
                var needUpdate = false;
                for (var i = 0; i < INTERMediator.keyFieldObject.length; i++) {
                    for (var j = 0; j < INTERMediator.keyFieldObject[i]['target'].length; j++) {
                        if (INTERMediator.keyFieldObject[i]['target'][j] == idValue) {
                            needUpdate = true;
                        }
                    }
                }
                if (needUpdate) {
                    for (var i = 0; i < INTERMediator.keyFieldObject.length; i++) {
                        if (INTERMediator.keyFieldObject[i]['node'].getAttribute('id') == updateNodeId) {
                            INTERMediator.construct(false, i);
                            break;
                        }
                    }
                }
            }
        }
    },

    deleteButton:function (targetName, keyField, keyValue, removeNodes, isConfirm) {
        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1025])) {
                return;
            }
        }
        try {
            INTERMediatorOnPage.retrieveAuthInfo();
            INTERMediaotr_DBAdapter.db_delete({
                name:targetName,
                conditions:[
                    {field:keyField, operator:'=', value:keyValue}
                ]
            });
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                if (ex == "_im_requath_request_") {
                    if (INTERMediatorOnPage.requireAuthentication && !INTERMediatorOnPage.isComplementAuthData()) {
                        INTERMediatorOnPage.authChallenge = null;
                        INTERMediatorOnPage.authHashedPassword = null;
                        INTERMediatorOnPage.authenticating(
                            function () {
                                INTERMediator.deleteButton(
                                    targetName, keyField, keyValue, removeNodes, false);
                            }
                        );
                        return;
                    }
                }
            }
        }

        for (var key in removeNodes) {
            var removeNode = document.getElementById(removeNodes[key]);
            removeNode.parentNode.removeChild(removeNode);
        }
        INTERMediator.flushMessage();
    },

    insertButton:function (targetName, foreignValues, updateNodes, removeNodes, isConfirm) {
        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1026])) {
                return;
            }
        }
        var currentContext = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', targetName);
        var recordSet = [];
        if (foreignValues != null) {
            for (var fieldName in foreignValues) {
                recordSet.push({field:fieldName, value:foreignValues[fieldName]});
            }
        }
        if (currentContext['default-values'] != null) {
            for (var index in currentContext['default-values']) {
                recordSet.push({
                    field:currentContext['default-values'][index]['field'],
                    value:currentContext['default-values'][index]['value']});
            }
        }
        try {
            INTERMediatorOnPage.retrieveAuthInfo();
            INTERMediaotr_DBAdapter.db_createRecord({name:targetName, dataset:recordSet});
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                INTERMediatorOnPage.authChallenge = null;
                INTERMediatorOnPage.authHashedPassword = null;
                INTERMediatorOnPage.authenticating(
                    function () {
                        INTERMediator.insertButton(
                            targetName, foreignValues, updateNodes, removeNodes, false);
                    }
                );
                INTERMediator.flushMessage();
                return;
            }
        }

        for (var key in removeNodes) {
            var removeNode = document.getElementById(removeNodes[key]);
            removeNode.parentNode.removeChild(removeNode);
        }
        for (var i = 0; i < INTERMediator.keyFieldObject.length; i++) {
            if (INTERMediator.keyFieldObject[i]['node'].getAttribute('id') == updateNodes) {
                INTERMediator.keyFieldObject[i]['foreign-value'] = foreignValues;
                INTERMediator.construct(false, i);
                break;
            }
        }
        INTERMediator.flushMessage();
    },

    insertRecordFromNavi:function (targetName, keyField, isConfirm) {
        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1025])) {
                return;
            }
        }
        var ds = INTERMediatorOnPage.getDataSources(); // Get DataSource parameters
        var targetKey = '';
        for (var key in ds) { // Search this table from DataSource
            if (INTERMediatorLib.getTargetTableForUpdate(ds[key]) == targetName) {
                targetKey = key;
                break;
            }
        }

        var recordSet = [];
        for (var index in ds[key]['default-values']) {
            recordSet.push({
                field:ds[key]['default-values'][index]['field'],
                value:ds[key]['default-values'][index]['value']});
        }
        try {
            INTERMediatorOnPage.retrieveAuthInfo();
            var newId = INTERMediaotr_DBAdapter.db_createRecord({name:targetName, dataset:recordSet});
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                if (INTERMediatorOnPage.requireAuthentication) {
                    if (!INTERMediatorOnPage.isComplementAuthData()) {
                        INTERMediatorOnPage.authChallenge = null;
                        INTERMediatorOnPage.authHashedPassword = null;
                        INTERMediatorOnPage.authenticating(function () {
                            INTERMediator.insertRecordFromNavi(targetName, keyField, isConfirm);
                        });
                        INTERMediator.flushMessage();
                        return;
                    }
                }
            }
        }

        if (newId > -1) {
            var restore = INTERMediator.additionalCondition;
            INTERMediator.startFrom = 0;
            var fieldObj = {
                field:keyField,
                value:newId
            };
            INTERMediator.additionalCondition = {};
            INTERMediator.additionalCondition[targetName] = fieldObj;
            INTERMediator.construct(true);
            INTERMediator.additionalCondition = restore;
        }
        INTERMediator.flushMessage();
    },

    deleteRecordFromNavi:function (targetName, keyField, keyValue, isConfirm) {
        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1026])) {
                return;
            }
        }
        try {
            INTERMediatorOnPage.retrieveAuthInfo();
            INTERMediaotr_DBAdapter.db_delete({
                name:targetName,
                conditions:[
                    {field:keyField, operator:'=', value:keyValue}
                ]
            });
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                INTERMediatorOnPage.authChallenge = null;
                INTERMediatorOnPage.authHashedPassword = null;
                INTERMediatorOnPage.authenticating(
                    function () {
                        INTERMediator.deleteRecordFromNavi(targetName, keyField, keyValue, isConfirm);
                    }
                );
                INTERMediator.flushMessage();
                return;
            }
        }

        if (INTERMediator.pagedAllCount - INTERMediator.startFrom < 2) {
            INTERMediator.startFrom--;
            if (INTERMediator.startFrom < 0) {
                INTERMediator.startFrom = 0;
            }
        }
        INTERMediator.construct(true);
        INTERMediator.flushMessage();
    },

    saveRecordFromNavi:function () {
        for (var idValue in INTERMediator.updateRequiredObject) {
            if (INTERMediator.updateRequiredObject[idValue]['edit']) {
                INTERMediator.updateDB(idValue);
            }
        }
        INTERMediator.flushMessage();
    },
    /*
     updateNodeId: function (nodeId) {
     for (var i = 0; i < INTERMediator.keyFieldObject.length; i++) {
     if (INTERMediator.keyFieldObject[i]['target'] == nodeId) {
     INTERMediator.construct(false, i);
     }
     }
     },
     */
    partialConstructing:false,
    objectReference:{},

//=================================
// Construct Page
//=================================
    /**
     * Construct the Web Page with DB Data
     * You should call here when you show the page.
     *
     * parameter: fromStart: true=construct page, false=construct partially
     */
    construct:function (fromStart, indexOfKeyFieldObject) {

        var currentLevel = 0;
        var linkedNodes;
        var postSetFields = [];
        var buttonIdNum = 1;
        var deleteInsertOnNavi = [];

        INTERMediatorOnPage.retrieveAuthInfo();
        try {
            if (fromStart) {
                this.partialConstructing = false;
                pageConstruct();
            } else {
                this.partialConstructing = true;
                partialConstruct(indexOfKeyFieldObject);
            }
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                if (INTERMediatorOnPage.requireAuthentication) {
                    if (!INTERMediatorOnPage.isComplementAuthData()) {
                        INTERMediatorOnPage.authChallenge = null;
                        INTERMediatorOnPage.authHashedPassword = null;
                        INTERMediatorOnPage.authenticating(
                            function () {
                                INTERMediator.construct(fromStart, indexOfKeyFieldObject);
                            }
                        );
                        return;
                    }
                }
            }
        }

        INTERMediator.flushMessage(); // Show messages

        /*

         */
        function partialConstruct(indexOfKeyFieldObject) {
            // Create parent table essentials.
            var updateContext = INTERMediator.keyFieldObject[indexOfKeyFieldObject]['name'];
            var updateNode = INTERMediator.keyFieldObject[indexOfKeyFieldObject]['node'];
            while (updateNode.firstChild) {
                updateNode.removeChild(updateNode.firstChild);
            }
            var originalNodes = INTERMediator.keyFieldObject[indexOfKeyFieldObject]['original'];
            for (var i = 0; i < originalNodes.length; i++) {
                updateNode.appendChild(originalNodes[i]);
            }
            var beforeKeyFieldObjectCount = INTERMediator.keyFieldObject.length;
            var currentContext = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', updateContext);
            var parentRecordset = {};
            for (var field in INTERMediator.keyFieldObject[indexOfKeyFieldObject]['foreign-value']) {
                var joinFled = INTERMediatorLib.getNamedValueInObject(
                    currentContext['relation'], 'foreign-key', field, 'join-field');
                parentRecordset[joinFled]
                    = INTERMediator.keyFieldObject[indexOfKeyFieldObject]['foreign-value'][field];
            }
            postSetFields = [];
            try {
                seekEnclosureNode(
                    updateNode, parentRecordset, updateContext,
                    INTERMediatorLib.getEnclosureSimple(updateNode), null);
            } catch (ex) {
                if (ex == "_im_requath_request_") {
                    throw ex;
                }
            }

            for (var i = 0; i < postSetFields.length; i++) {
                document.getElementById(postSetFields[i]['id']).value = postSetFields[i]['value'];
            }
            for (var i = beforeKeyFieldObjectCount + 1; i < INTERMediator.keyFieldObject.length; i++) {
                var currentNode = INTERMediator.keyFieldObject[i];
                var currentID = currentNode['node'].getAttribute('id');
                if (currentNode['target'] == null) {
                    var enclosure;
                    if (currentID != null && currentID.match(/IM[0-9]+-[0-9]+/)) {
                        enclosure = INTERMediatorLib.getParentRepeater(currentNode['node']);
                    } else {
                        enclosure = INTERMediatorLib.getParentRepeater(
                            INTERMediatorLib.getParentEnclosure(currentNode['node']));
                    }
                    if (enclosure != null) {
                        var targetNode = getEnclosedNode(enclosure,
                            currentNode['name'], currentNode['foreign-value'][0]['field']);
                        if (targetNode) {
                            currentNode['target'] = targetNode.getAttribute('id');
                        }
                    }
                }
            }

        }

        function pageConstruct() {
            INTERMediator.keyFieldObject = [];
            INTERMediator.updateRequiredObject = {};
            INTERMediator.currentEncNumber = 1;

            // Detect Internet Explorer and its version.
            var ua = navigator.userAgent;
            var msiePos = ua.toLocaleUpperCase().indexOf('MSIE');
            if (msiePos >= 0) {
                INTERMediator.isIE = true;
                for (var i = msiePos + 4; i < ua.length; i++) {
                    var c = ua.charAt(i);
                    if (c != ' ' && c != '.' && (c < '0' || c > '9')) {
                        INTERMediator.ieVersion = INTERMediatorLib.toNumber(ua.substring(msiePos + 4, i));
                        break;
                    }
                }
            }
            // Restoring original HTML Document from backup data.
            var bodyNode = document.getElementsByTagName('BODY')[0];
            if (INTERMediator.rootEnclosure == null) {
                INTERMediator.rootEnclosure = bodyNode.innerHTML;
            } else {
                bodyNode.innerHTML = INTERMediator.rootEnclosure;
            }
            postSetFields = [];

            try {
                seekEnclosureNode(bodyNode, null, null, null, null);
            } catch (ex) {
                if (ex == "_im_requath_request_") {
                    throw ex;
                }
            }


            // After work to set up popup menus.
            for (var i = 0; i < postSetFields.length; i++) {
                document.getElementById(postSetFields[i]['id']).value = postSetFields[i]['value'];
            }
            for (var i = 0; i < INTERMediator.keyFieldObject.length; i++) {
                var currentNode = INTERMediator.keyFieldObject[i];
                var currentID = currentNode['node'].getAttribute('id');
                if (currentNode['target'] == null) {
                    var enclosure;
                    if (currentID != null && currentID.match(/IM[0-9]+-[0-9]+/)) {
                        enclosure = INTERMediatorLib.getParentRepeater(currentNode['node']);
                    } else {
                        enclosure = INTERMediatorLib.getParentRepeater(
                            INTERMediatorLib.getParentEnclosure(currentNode['node']));
                    }
                    if (enclosure != null) {
                        var targetNode = getEnclosedNode(enclosure, currentNode['name'], currentNode['field']);
                        if (targetNode) {
                            currentNode['target'] = targetNode.getAttribute('id');
                        }
                    }
                }
            }
            navigationSetup();
            appendCredit();
        }

        /**
         * Seeking nodes and if a node is an enclosure, proceed repeating.
         */

        function seekEnclosureNode(node, currentRecord, currentTable, parentEnclosure, objectReference) {
            var nType = node.nodeType;
            if (nType == 1) { // Work for an element
                try {
                    if (INTERMediatorLib.isEnclosure(node, false)) { // Linked element and an enclosure
                        expandEnclosure(node, currentRecord, currentTable, parentEnclosure, objectReference);
                    } else {
                        var childs = node.childNodes; // Check all child nodes.
                        for (var i = 0; i < childs.length; i++) {
                            if (childs[i].nodeType == 1) {
                                seekEnclosureNode(childs[i], currentRecord, currentTable, parentEnclosure, objectReference);
                            }
                        }
                    }
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        throw ex;
                    }
                }

            }
        }

        /**
         * Expanding an enclosure.
         */

        function expandEnclosure(node, currentRecord, currentTable, parentEnclosure, parentObjectInfo) {
            currentLevel++;
            INTERMediator.currentEncNumber++;
            var objectReference = {};

            var linkedElmCounter = 0;
            if (node.getAttribute('id') == null) {
                var idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                node.setAttribute('id', idValue);
                linkedElmCounter++;
            }

            var encNodeTag = node.tagName;
            //    var parentEnclosure = INTERMediatorLib.getEnclosure(node);
            var parentNodeId = (parentEnclosure == null ? null : parentEnclosure.getAttribute('id'));
            var repNodeTag = INTERMediatorLib.repeaterTagFromEncTag(encNodeTag);
            var repeatersOriginal = []; // Collecting repeaters to this array.
            var repeaters = []; // Collecting repeaters to this array.
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
            linkedNodes = []; // Collecting linked elements to this array.
            for (var i = 0; i < repeaters.length; i++) {
                seekLinkedElement(repeaters[i]);
            }
            var currentLinkedNodes = linkedNodes; // Store in the local variable
            // Collecting linked elements in array
            var linkDefs = [];
            for (var j = 0; j < linkedNodes.length; j++) {
                var nodeDefs = INTERMediatorLib.getLinkedElementInfo(linkedNodes[j]);
                if (nodeDefs != null) {
                    for (var k = 0; k < nodeDefs.length; k++) {
                        linkDefs.push(nodeDefs[k]);
                    }
                }
            }

            var voteResult = tableVoting(linkDefs);
            var currentContext = voteResult.targettable;
            var fieldList = voteResult.fieldlist; // Create field list for database fetch.

            if (currentContext != null) {
                var relationValue = null;
                var dependObject = [];
                var relationDef = currentContext['relation'];
                if (relationDef != null) {
                    relationValue = [];
                    for (var index in relationDef) {
                        relationValue[ relationDef[index]['foreign-key'] ]
                            = currentRecord[relationDef[index]['join-field']];

                        for (var fieldName in parentObjectInfo) {
                            if (fieldName == relationDef[index]['join-field']) {
                                dependObject.push(parentObjectInfo[fieldName]);
                            }
                        }
                    }
                }

                var thisKeyFieldObject = {
                    'node':node,
                    'name':currentContext['name'] /*currentTable */,
                    'foreign-value':relationValue,
                    'parent':node.parentNode,
                    'original':[],
                    'target':dependObject
                };
                for (var i = 0; i < repeatersOriginal.length; i++) {
                    thisKeyFieldObject.original.push(repeatersOriginal[i].cloneNode(true));
                }
                INTERMediator.keyFieldObject.push(thisKeyFieldObject);

                // Access database and get records
                try {
                    var targetRecords = INTERMediaotr_DBAdapter.db_query({
                        name:currentContext['name'],
                        records:currentContext['records'],
                        paging:currentContext['paging'],
                        fields:fieldList,
                        parentkeyvalue:relationValue,
                        conditions:null,
                        useoffset:true});
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        throw ex;
                    }
                }

                if (targetRecords.count == 0) {
                    for (var i = 0; i < repeaters.length; i++) {
                        var newNode = repeaters[i].cloneNode(true);
                        var nodeClass = INTERMediatorLib.getClassAttributeFromNode(newNode);
                        if (nodeClass == INTERMediator.noRecordClassName) {
                            node.appendChild(newNode);
                            if (newNode.getAttribute('id') == null) {
                                idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                                newNode.setAttribute('id', idValue);
                                linkedElmCounter++;
                            }
                        }
                    }
                }

                var RecordCounter = 0;
                var eventListenerPostAdding = [];
                // var currentEncNumber = currentLevel;
                for (var ix in targetRecords.recordset) { // for each record
                    RecordCounter++;

                    var shouldDeleteNodes = [];
                    repeaters = [];
                    for (var i = 0; i < repeatersOriginal.length; i++) {
                        var clonedNode = repeatersOriginal[i].cloneNode(true);
                        repeaters.push(clonedNode);
                    }
                    linkedNodes = [];
                    for (var i = 0; i < repeaters.length; i++) {
                        seekLinkedElement(repeaters[i]);
                        if (repeaters[i].getAttribute('id') == null) {
                            idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                            repeaters[i].setAttribute('id', idValue);
                            linkedElmCounter++;
                        }
                        shouldDeleteNodes.push(repeaters[i].getAttribute('id'));
                    }
                    var currentLinkedNodes = linkedNodes; // Store in the local variable
                    var keyField = currentContext['key'];
                    var keyValue = targetRecords.recordset[ix][currentContext['key']];
                    var keyingValue = keyField + "=" + keyValue;

                    for (var k = 0; k < currentLinkedNodes.length; k++) {
                        // for each linked element
                        if (currentLinkedNodes[k].getAttribute('id') == null) {
                            idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                            currentLinkedNodes[k].setAttribute('id', idValue);
                            linkedElmCounter++;
                        }
                        var nodeTag = currentLinkedNodes[k].tagName;
                        // get the tag name of the element
                        var typeAttr = currentLinkedNodes[k].getAttribute('type');
                        // type attribute
                        var linkInfoArray = INTERMediatorLib.getLinkedElementInfo(currentLinkedNodes[k]);
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

                        if (nodeTag == 'INPUT' || nodeTag == 'SELECT' || nodeTag == 'TEXTAREA') {
                            var valueChangeFunction = function (a) {
                                var idValue = a;
                                return function () {
                                    INTERMediator.valueChange(idValue);
                                };
                            }
                            eventListenerPostAdding.push({
                                'id':idValue,
                                'event':'change',
                                'todo':valueChangeFunction(idValue)
                            });
                        }

                        for (var j = 0; j < linkInfoArray.length; j++) {
                            // for each info Multiple replacement definitions
                            // for one node is prohibited.
                            var nInfo = INTERMediatorLib.getNodeInfoArray(linkInfoArray[j]);
                            var curVal = targetRecords.recordset[ix][nInfo['field']];
                            if (curVal == null) {
                                curVal = '';
                            }
                            var curTarget = nInfo['target'];
                            // Store the key field value and current value for update
                            if (nodeTag == 'INPUT' || nodeTag == 'SELECT' || nodeTag == 'TEXTAREA') {
                                INTERMediator.updateRequiredObject[idValue] = {
                                    targetattribute:curTarget,
                                    initialvalue:curVal,
                                    name:currentContext['name'],
                                    field:nInfo['field'],
                                    'parent-enclosure':node.getAttribute('id'),
                                    keying:keyingValue,
                                    'foreign-value':relationValue,
                                    'updatenodeid':parentNodeId};
                            }

                            objectReference[nInfo['field']] = idValue;

                            // Set data to the element.
                            if (setDataToElement(currentLinkedNodes[k], curTarget, curVal)) {
                                postSetFields.push({'id':idValue, 'value':curVal});
                            }
                        }
                    }

                    // Handling Delete buttons
                    if (currentContext['repeat-control'] != null && currentContext['repeat-control'].match(/delete/i)) {
                        if (currentContext['relation'] != null || currentContext['records'] > 1) {
                            var buttonNode = document.createElement('BUTTON');
                            buttonNode.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[6]));
                            var thisId = 'IM_Button_' + buttonIdNum;
                            buttonNode.setAttribute('id', thisId);
                            buttonIdNum++;
                            var deleteJSFunction = function (a, b, c, d, e) {
                                var contextName = a;
                                var keyField = b;
                                var keyValue = c;
                                var removeNodes = d;
                                var confirming = e;
                                return function () {
                                    INTERMediator.deleteButton(
                                        contextName, keyField, keyValue, removeNodes, confirming);
                                };
                            };
                            eventListenerPostAdding.push({
                                'id':thisId,
                                'event':'click',
                                'todo':deleteJSFunction(currentContext['name'], keyField, keyValue, shouldDeleteNodes,
                                    currentContext['repeat-control'].match(/confirm-delete/i) ? true : false)
                            });
                            var endOfRepeaters = repeaters[repeaters.length - 1];
                            switch (encNodeTag) {
                                case 'TBODY':
                                    var tdNodes = endOfRepeaters.getElementsByTagName('TD');
                                    var tdNode = tdNodes[tdNodes.length - 1];
                                    tdNode.appendChild(buttonNode);
                                    break;
                                case 'UL':
                                case 'OL':
                                    endOfRepeaters.appendChild(buttonNode);
                                    break;
                                case 'DIV':
                                case 'SPAN':
                                    if (repNodeTag == "DIV" || repNodeTag == "SPAN") {
                                        endOfRepeaters.appendChild(buttonNode);
                                    }
                                    break;
                            }
                        } else {
                            deleteInsertOnNavi.push({
                                kind:'DELETE',
                                name:currentContext['name'],
                                key:keyField,
                                value:keyValue,
                                confirm:currentContext['repeat-control'].match(/confirm-delete/i) ? true : false
                            });
                        }
                    }

                    if (INTERMediatorOnPage.expandingRecordFinish != null) {
                        INTERMediatorOnPage.expandingRecordFinish(currentContext['name'], repeaters);
                    }

                    for (var i = 0; i < repeaters.length; i++) {
                        var newNode = repeaters[i].cloneNode(true);
                        var nodeClass = INTERMediatorLib.getClassAttributeFromNode(newNode);
                        if (nodeClass != INTERMediator.noRecordClassName) {
                            node.appendChild(newNode);
                            if (newNode.getAttribute('id') == null) {
                                idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                                newNode.setAttribute('id', idValue);
                                linkedElmCounter++;
                            }
                            seekEnclosureNode(newNode, targetRecords.recordset[ix], currentContext['name'], node, objectReference);
                        }
                    }

                    // Event listener should add after adding node to document.
                    for (var i = 0; i < eventListenerPostAdding.length; i++) {
                        var theNode = document.getElementById(eventListenerPostAdding[i]['id']);
                        if (theNode != null) {
                            INTERMediatorLib.addEvent(
                                theNode, eventListenerPostAdding[i]['event'], eventListenerPostAdding[i]['todo']);
                        }
                    }
                }

                if (currentContext['repeat-control'] != null && currentContext['repeat-control'].match(/insert/i)) {
                    if (relationValue != null) {
                        var buttonNode = document.createElement('BUTTON');
                        buttonNode.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[5]));
                        var shouldRemove = [];
                        switch (encNodeTag) {
                            case 'TBODY':
                                var enclosedNode = node.parentNode;
                                var footNode = enclosedNode.getElementsByTagName('TFOOT')[0];
                                if (footNode == null) {
                                    footNode = document.createElement('TFOOT');
                                    enclosedNode.appendChild(footNode);
                                }
                                var trNode = document.createElement('TR');
                                var tdNode = document.createElement('TD');
                                if (trNode.getAttribute('id') == null) {
                                    idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                                    trNode.setAttribute('id', idValue);
                                    linkedElmCounter++;
                                }
                                footNode.appendChild(trNode);
                                trNode.appendChild(tdNode);
                                tdNode.appendChild(buttonNode);
                                shouldRemove = [trNode.getAttribute('id')];
                                break;
                            case 'UL':
                            case 'OL':
                                var liNode = document.createElement('LI');
                                liNode.appendChild(buttonNode);
                                node.appendChild(liNode);
                                break;
                            case 'DIV':
                            case 'SPAN':
                                if (repNodeTag == "DIV" || repNodeTag == "SPAN") {
                                    var divNode = document.createElement(repNodeTag);
                                    divNode.appendChild(buttonNode);
                                    node.appendChild(liNode);
                                }
                                break;
                        }
                        var insertJSFunction = function (a, b, c, d, e) {
                            var contextName = a;
                            var relationValue = b;
                            var nodeId = c;
                            var removeNodes = d;
                            var confirming = e;
                            return function () {
                                INTERMediator.insertButton(contextName, relationValue, nodeId, removeNodes, confirming);
                            }
                        };

                        INTERMediatorLib.addEvent(
                            buttonNode,
                            'click',
                            insertJSFunction(
                                currentContext['name'],
                                relationValue,
                                node.getAttribute('id'),
                                shouldRemove,
                                currentContext['repeat-control'].match(/confirm-insert/i) ? true : false)
                        );
                    } else {
                        deleteInsertOnNavi.push({
                            kind:'INSERT',
                            name:currentContext['name'],
                            key:currentContext['key'],
                            confirm:currentContext['repeat-control'].match(/confirm-insert/i) ? true : false
                        });
                    }
                }

                if (INTERMediatorOnPage.expandingEnclosureFinish != null) {
                    INTERMediatorOnPage.expandingEnclosureFinish(currentContext['name'], node);
                }

            } else {
                INTERMediator.errorMessages.push(
                    INTERMediatorLib.getInsertedString(
                        INTERMediatorOnPage.getMessages()[1002], [INTERMediatorLib.objectToString(fieldList)]));
            }
            currentLevel--;
            //    return foreignValue != '';
        }

        function seekLinkedElement(node) {
            var enclosure = null;
            var nType = node.nodeType;
            if (nType == 1) {
                if (INTERMediatorLib.isLinkedElement(node)) {
                    var currentEnclosure = INTERMediatorLib.getEnclosure(node);
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

        function tableVoting(linkDefs) {
            var tableVote = [];    // Containing editable elements or not.
            var fieldList = []; // Create field list for database fetch.
            for (var j = 0; j < linkDefs.length; j++) {
                var nodeInfoArray = INTERMediatorLib.getNodeInfoArray(linkDefs[j]);
                var nodeInfoField = nodeInfoArray['field'];
                var nodeInfoTable = nodeInfoArray['table'];
                if (nodeInfoField != null && nodeInfoTable != null &&
                    nodeInfoField.length != 0 && nodeInfoTable.length != 0) {
                    if (fieldList[nodeInfoTable] == null) {
                        fieldList[nodeInfoTable] = [];
                    }
                    fieldList[nodeInfoTable].push(nodeInfoField);
                    if (tableVote[nodeInfoTable] == null) {
                        tableVote[nodeInfoTable] = 1;
                    } else {
                        ++tableVote[nodeInfoTable];
                    }
                } else {
                    INTERMediator.errorMessages.push(
                        INTERMediatorLib.getInsertedStringFromErrorNumber(1006, [linkDefs[j]]));
                    //   return null;
                }
            }
            var maxVoted = -1;
            var maxTableName = ''; // Which is the maximum voted table name.
            for (var tableName in tableVote) {
                if (maxVoted < tableVote[tableName]) {
                    maxVoted = tableVote[tableName];
                    maxTableName = tableName;
                }
            }
            var context = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', maxTableName);
            return {'targettable':context, 'fieldlist':fieldList[maxTableName]};
        }

        function setDataToElement(element, curTarget, curVal) {
            var needPostValueSet = false;
            // IE should \r for textNode and <br> for innerHTML, Others is not required to convert
            var nodeTag = element.tagName;

            if (curTarget != null && curTarget.length > 0) { //target is specified
                if (curTarget.charAt(0) == '#') { // Appending
                    curTarget = curTarget.substring(1);
                    if (curTarget == 'innerHTML') {
                        if (INTERMediator.isIE && nodeTag == "TEXTAREA") {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br/>");
                        }
                        element.innerHTML += curVal;
                    } else if (curTarget == 'textNode') {
                        var textNode = document.createTextNode(curVal);
                        if (nodeTag == "TEXTAREA") {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r");
                        }
                        element.appendChild(textNode);
                    } else if (curTarget.indexOf('style.') == 0) {
                        var styleName = curTarget.substring(6, curTarget.length);
                        var statement = "element.style." + styleName + "='" + curVal + "';";
                        eval(statement);
                    } else {
                        var currentValue = element.getAttribute(curTarget);
                        element.setAttribute(curTarget, currentValue + curVal);
                    }
                } else { // Setting
                    if (curTarget == 'innerHTML') { // Setting
                        if (INTERMediator.isIE && nodeTag == "TEXTAREA") {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br/>");
                        }
                        element.innerHTML = curVal;
                    } else if (curTarget == 'textNode') {
                        var textNode = document.createTextNode(curVal);
                        if (nodeTag == "TEXTAREA") {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r");
                        }
                        element.appendChild(textNode);
                    } else if (curTarget.indexOf('style.') == 0) {
                        var styleName = curTarget.substring(6, curTarget.length);
                        var statement = "element.style." + styleName + "='" + curVal + "';";
                        eval(statement);
                    } else {
                        element.setAttribute(curTarget, curVal);
                    }
                }
            } else { // if the 'target' is not specified.
                if (nodeTag == "INPUT") {
                    var typeAttr = element.getAttribute('type');
                    if (typeAttr == 'checkbox' || typeAttr == 'radio') { // set the value
                        var valueAttr = element.value;
                        if (valueAttr == curVal) {
                            if (INTERMediator.isIE) {
                                element.setAttribute('checked', 'checked');
                            } else {
                                element.checked = true;
                            }
                        } else {
                            element.checked = false;
                        }
                    } else { // this node must be text field
                        element.value = curVal;
                    }
                } else if (nodeTag == "SELECT") {
                    needPostValueSet = true;
                } else { // include option tag node
                    if (INTERMediator.defaultTargetInnerHTML) {
                        if (INTERMediator.isIE && nodeTag == "TEXTAREA") {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br/>");
                        }
                        element.innerHTML = curVal;
                    } else {
                        if (nodeTag == "TEXTAREA") {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r");
                        }
                        var textNode = document.createTextNode(curVal);
                        element.appendChild(textNode);
                    }
                }
            }
            return needPostValueSet;
        }

        /**
         * Create Navigation Bar to move previous/next page
         * @param target
         */

        function navigationSetup() {
            var navigation = document.getElementById('IM_NAVIGATOR');
            if (navigation != null) {
                var sq = "'", comma = ',';

                var insideNav = navigation.childNodes;
                for (var i = 0; i < insideNav.length; i++) {
                    navigation.removeChild(insideNav[i]);
                }
                navigation.innerHTML = '';
                navigation.setAttribute('class', 'IM_NAV_panel');
                var navLabel = INTERMediator.navigationLabel;

                if ( navLabel == null || navLabel[8] !== false )    {
                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        ((navLabel == null || navLabel[8] == null) ? INTERMediatorOnPage.getMessages()[2] : navLabel[8])));
                    node.setAttribute('class', 'IM_NAV_button');
                    INTERMediatorLib.addEvent(node, 'click', function () {
                        location.reload();
                    });
                }

                if ( navLabel == null || navLabel[4] !== false )    {
                    var start = Number(INTERMediator.startFrom);
                    var pageSize = Number(INTERMediator.pagedSize);
                    var allCount = Number(INTERMediator.pagedAllCount);
                    var disableClass = " IM_NAV_disabled";
                    var node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        ((navLabel == null || navLabel[4] == null) ? INTERMediatorOnPage.getMessages()[1] : navLabel[4]) + (start + 1)
                            + ((Math.min(start + pageSize, allCount) - start > 2) ?
                            (((navLabel == null || navLabel[5] == null) ? "-" : navLabel[5])
                                + Math.min(start + pageSize, allCount)) : '')
                            + ((navLabel == null || navLabel[6] == null) ? " / " : navLabel[6]) + (allCount)
                            + ((navLabel == null || navLabel[7] == null) ? "" : navLabel[7])));
                    node.setAttribute('class', 'IM_NAV_info');
                }

                if ( navLabel == null || navLabel[0] !== false )    {
                    var node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        (navLabel == null || navLabel[0] == null) ? '<<' : navLabel[0]));
                    node.setAttribute('class', 'IM_NAV_button' + (start == 0 ? disableClass : ""));
                    INTERMediatorLib.addEvent(node, 'click', function () {
                        INTERMediator.startFrom = 0;
                        INTERMediator.construct(true);
                    });

                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        (navLabel == null || navLabel[1] == null) ? '<' : navLabel[1]));
                    node.setAttribute('class', 'IM_NAV_button' + (start == 0 ? disableClass : ""));
                    var prevPageCount = (start - pageSize > 0) ? start - pageSize : 0;
                    INTERMediatorLib.addEvent(node, 'click', function () {
                        INTERMediator.startFrom = prevPageCount;
                        INTERMediator.construct(true);
                    });

                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        (navLabel == null || navLabel[2] == null) ? '>' : navLabel[2]));
                    node.setAttribute('class', 'IM_NAV_button' + (start + pageSize >= allCount ? disableClass : ""));
                    var nextPageCount
                        = (start + pageSize < allCount) ? start + pageSize : ((allCount - pageSize > 0) ? start : 0);
                    INTERMediatorLib.addEvent(node, 'click', function () {
                        INTERMediator.startFrom = nextPageCount;
                        INTERMediator.construct(true);
                    });

                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        (navLabel == null || navLabel[3] == null) ? '>>' : navLabel[3]));
                    node.setAttribute('class', 'IM_NAV_button' + (start + pageSize >= allCount ? disableClass : ""));
                    var endPageCount = allCount - pageSize;
                    INTERMediatorLib.addEvent(node, 'click', function () {
                        INTERMediator.startFrom = (endPageCount > 0) ? endPageCount : 0;
                        INTERMediator.construct(true);
                    });
                }

                for (var i = 0; i < deleteInsertOnNavi.length; i++) {
                    switch (deleteInsertOnNavi[i]['kind']) {
                        case 'INSERT':
                            node = document.createElement('SPAN');
                            navigation.appendChild(node);
                            node.appendChild(
                                document.createTextNode(INTERMediatorOnPage.getMessages()[3] + ': ' + deleteInsertOnNavi[i]['name']));
                            node.setAttribute('class', 'IM_NAV_button');
                            var onNaviInsertFunction = function (a, b, c) {
                                var contextName = a;
                                var keyValue = b;
                                var confirming = c;
                                return function () {
                                    INTERMediator.insertRecordFromNavi(contextName, keyValue, confirming);
                                };
                            };
                            INTERMediatorLib.addEvent(
                                node,
                                'click',
                                onNaviInsertFunction(
                                    deleteInsertOnNavi[i]['name'],
                                    deleteInsertOnNavi[i]['key'],
                                    deleteInsertOnNavi[i]['confirm'] ? true : false)
                            );
                            break;
                        case 'DELETE':
                            node = document.createElement('SPAN');
                            navigation.appendChild(node);
                            node.appendChild(
                                document.createTextNode(INTERMediatorOnPage.getMessages()[4] + ': ' + deleteInsertOnNavi[i]['name']));
                            node.setAttribute('class', 'IM_NAV_button');
                            var onNaviDeleteFunction = function (a, b, c, d) {
                                var contextName = a;
                                var keyName = b;
                                var keyValue = c;
                                var confirming = d;
                                return function () {
                                    INTERMediator.deleteRecordFromNavi(contextName, keyName, keyValue, confirming);
                                };
                            }
                            INTERMediatorLib.addEvent(
                                node,
                                'click',
                                onNaviDeleteFunction(
                                    deleteInsertOnNavi[i]['name'],
                                    deleteInsertOnNavi[i]['key'],
                                    deleteInsertOnNavi[i]['value'],
                                    deleteInsertOnNavi[i]['confirm'] ? true : false));
                            break;
                    }
                }
                if (INTERMediatorOnPage.getOptionsTransaction() == 'none') {
                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[7]));
                    node.setAttribute('class', 'IM_NAV_button');
                    INTERMediatorLib.addEvent(node, 'click', INTERMediator.saveRecordFromNavi);
                }
                if (INTERMediatorOnPage.requireAuthentication) {
                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        INTERMediatorOnPage.getMessages()[8] + INTERMediatorOnPage.authUser));
                    node.setAttribute('class', 'IM_NAV_info');

                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[9]));
                    node.setAttribute('class', 'IM_NAV_button');
                    INTERMediatorLib.addEvent(node, 'click',
                        function () {
                            INTERMediatorOnPage.logout();
                            location.reload();
                        });
                }
            }
        }

        function getEnclosedNode(rootNode, tableName, fieldName) {
            if (rootNode.nodeType == 1) {
                var nodeInfo = INTERMediatorLib.getLinkedElementInfo(rootNode);
                for (var j = 0; j < nodeInfo.length; j++) {
                    var nInfo = INTERMediatorLib.getNodeInfoArray(nodeInfo[j]);
                    if (nInfo['table'] == tableName && nInfo['field'] == fieldName) {
                        return rootNode;
                    }
                }
            }
            var childs = rootNode.childNodes; // Check all child node of the enclosure.
            for (var i = 0; i < childs.length; i++) {
                var r = getEnclosedNode(childs[i], tableName, fieldName);
                if (r != null) {
                    return r;
                }
            }
            return null;
        }

        function appendCredit() {
            if (document.getElementById('IM_CREDIT') == null) {
                var bodyNode = document.getElementsByTagName('BODY')[0];
                var creditNode = document.createElement('div');
                bodyNode.appendChild(creditNode);
                creditNode.setAttribute('id', 'IM_CREDIT');
                creditNode.setAttribute('class', 'IM_CREDIT');

                var cNode = document.createElement('div');
                creditNode.appendChild(cNode);
                cNode.style.backgroundColor = '#F6F7FF';
                cNode.style.height = '2px';

                cNode = document.createElement('div');
                creditNode.appendChild(cNode);
                cNode.style.backgroundColor = '#EBF1FF';
                cNode.style.height = '2px';

                cNode = document.createElement('div');
                creditNode.appendChild(cNode);
                cNode.style.backgroundColor = '#E1EAFF';
                cNode.style.height = '2px';

                cNode = document.createElement('div');
                creditNode.appendChild(cNode);
                cNode.setAttribute('align', 'right');
                cNode.style.backgroundColor = '#D7E4FF';
                cNode.style.padding = '2px';
                var spNode = document.createElement('span');
                cNode.appendChild(spNode);
                cNode.style.color = '#666666';
                cNode.style.fontSize = '7pt';
                var aNode = document.createElement('a');
                aNode.appendChild(document.createTextNode('INTER-Mediator'));
                aNode.setAttribute('href', 'http://inter-mediator.info/');
                aNode.setAttribute('target', '_href');
                spNode.appendChild(document.createTextNode('Generated by '));
                spNode.appendChild(aNode);
                spNode.appendChild(document.createTextNode(' Ver.@@@@2@@@@(@@@@1@@@@)'));
            }
        }
    }
};