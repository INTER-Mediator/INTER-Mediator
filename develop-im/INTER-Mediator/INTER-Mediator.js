/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

var INTERMediator = {
    /*
     Properties
     */
    debugMode: false,
    // Show the debug messages at the top of the page.
    separator: '@',
    // This must be referred as 'INTERMediator.separator'. Don't use 'this.separator'
    defDivider: '|',
    // Same as the "separator".
    additionalCondition: [],
    // This array should be [{tableName: [{field:xxx,operator:xxx,value:xxxx}]}, ... ]
    additionalSortKey: [],
    // This array should be [{tableName: [{field:xxx,direction:xxx}]}, ... ]
    defaultTargetInnerHTML: false,
    // For general elements, if target isn't specified, the value will be set to innerHTML.
    // Otherwise, set as the text node.
    navigationLabel: null,
    // Navigation is controlled by this parameter.
    startFrom: 0,
    // Start from this number of record for "skipping" records.
    widgetElementIds: [],
    radioNameMode: false,
    dontSelectRadioCheck: false,
    ignoreOptimisticLocking: false,
    supressDebugMessageOnPage: false,
    supressErrorMessageOnPage: false,
    additionalFieldValueOnNewRecord: [],
    waitSecondsAfterPostMessage: 4,
    pagedSize: 0,
    pagedAllCount: 0,
    currentEncNumber: 0,
    isIE: false,
    ieVersion: -1,
    titleAsLinkInfo: true,
    classAsLinkInfo: true,
    noRecordClassName: "_im_for_noresult_",

    // Remembering Objects
    updateRequiredObject: null,
    /*
     {id-value:{               // For the node of this id attribute.
     targetattribute:,      // about target
     initialvalue:,          // The value from database.
     name:
     field:id,               // about target field
     keying:id=1,            // The key field specifier to identify this record.
     foreignfield:,          // foreign field name
     foreignvalue:,},        // foreign field value
     ...}
     */
    keyFieldObject: null,
    /* inside of keyFieldObject
     {node:xxx,         // The node information
     original:xxx       // Copy of childs
     name:xxx,             // name of context
     foreign-value:Recordset as {f1:v1, f2:v2, ..} ,not [{field:xx, value:xx},..]
     f1, f2 is "join-field"'s field name, v1, v2 are their values.
     target:xxxx}       // Related (depending) node's id attribute value.
     */
    rootEnclosure: null,
    // Storing to retrieve the page to initial condition.
    // {node:xxx, parent:xxx, currentRoot:xxx, currentAfter:xxxx}
    errorMessages: [],
    debugMessages: [],


    //=================================
    // Message for Programmers
    //=================================

    setDebugMessage: function (message, level) {
        if (level === undefined) {
            level = 1;
        }
        if (INTERMediator.debugMode >= level) {
            INTERMediator.debugMessages.push(message);
            console.log("INTER-Mediator[DEBUG:%s]: %s", new Date(), message);
        }
    },

    setErrorMessage: function (ex, moreMessage) {
        moreMessage = moreMessage === undefined ? "" : (" - " + moreMessage);
        if ((typeof ex == 'string' || ex instanceof String)) {
            INTERMediator.errorMessages.push(ex + moreMessage);
            console.error("INTER-Mediator[ERROR]: %s", ex + moreMessage);
        } else {
            if (ex.message) {
                INTERMediator.errorMessages.push(ex.message + moreMessage);
                console.error("INTER-Mediator[ERROR]: %s", ex.message + moreMessage);
            }
            if (ex.stack) {
                console.error(ex.stack);
            }
        }
    },

    flushMessage: function () {
        var debugNode, title, body, i, j, lines, clearButton, tNode, target;

        if (!INTERMediator.supressErrorMessageOnPage
            && INTERMediator.errorMessages.length > 0) {
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
            debugNode.appendChild(document.createTextNode(
                "============ERROR MESSAGE on " + new Date() + "============"));
            debugNode.appendChild(document.createElement('hr'));
            for (i = 0; i < INTERMediator.errorMessages.length; i++) {
                lines = INTERMediator.errorMessages[i].split("\n");
                for (j = 0; j < lines.length; j++) {
                    if (j > 0) {
                        debugNode.appendChild(document.createElement('br'));
                    }
                    debugNode.appendChild(document.createTextNode(lines[j]));
                }
                debugNode.appendChild(document.createElement('hr'));
            }
        }
        if (!INTERMediator.supressDebugMessageOnPage
            && INTERMediator.debugMode
            && INTERMediator.debugMessages.length > 0) {
            debugNode = document.getElementById('_im_debug_panel_4873643897897');
            if (debugNode == null) {
                debugNode = document.createElement('div');
                debugNode.setAttribute('id', '_im_debug_panel_4873643897897');
                debugNode.style.backgroundColor = '#DDDDDD';
                clearButton = document.createElement('button');
                clearButton.setAttribute('title', 'clear');
                INTERMediatorLib.addEvent(clearButton, 'click', function () {
                    target = document.getElementById('_im_debug_panel_4873643897897');
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
                if (body) {
                    if (body.firstChild) {
                        body.insertBefore(debugNode, body.firstChild);
                    } else {
                        body.appendChild(debugNode);
                    }
                }
            }
            debugNode.appendChild(document.createTextNode(
                "============DEBUG INFO on " + new Date() + "============ "));
            var aLink = document.createElement('a');
            aLink.setAttribute('href', INTERMediatorOnPage.getEditorPath());
            aLink.appendChild(document.createTextNode('Definition File Editor'));
            debugNode.appendChild(aLink);
            debugNode.appendChild(document.createElement('hr'));
            for (i = 0; i < INTERMediator.debugMessages.length; i++) {
                lines = INTERMediator.debugMessages[i].split("\n");
                for (j = 0; j < lines.length; j++) {
                    if (j > 0) {
                        debugNode.appendChild(document.createElement('br'));
                    }
                    debugNode.appendChild(document.createTextNode(lines[j]));
                }
                debugNode.appendChild(document.createElement('hr'));
            }
        }
        INTERMediator.errorMessages = [];
        INTERMediator.debugMessages = [];
    },

    //=================================
    // User interactions
    //=================================

    isShiftKeyDown: false,
    isControlKeyDown: false,

    keyDown: function (evt) {
        var keyCode = (window.event) ? evt.which : evt.keyCode;
        if (keyCode == 16) {
            INTERMediator.isShiftKeyDown = true;
        }
        if (keyCode == 17) {
            INTERMediator.isControlKeyDown = true;
        }
    },

    keyUp: function (evt) {
        var keyCode = (window.event) ? evt.which : evt.keyCode;
        if (keyCode == 16) {
            INTERMediator.isShiftKeyDown = false;
        }
        if (keyCode == 17) {
            INTERMediator.isControlKeyDown = false;
        }
    },
    /*
     valueChange
     Parameters:
     */
    valueChange: function (idValue) {
        var changedObj, linkInfo, matched, context, i, index, checkFunction, target, value, result;

        if (INTERMediator.isShiftKeyDown && INTERMediator.isControlKeyDown) {
            INTERMediator.setDebugMessage("Canceled to update the value with shift+control keys.");
            INTERMediator.flushMessage();
            INTERMediator.isShiftKeyDown = false;
            INTERMediator.isControlKeyDown = false;
            return;
        }
        INTERMediator.isShiftKeyDown = false;
        INTERMediator.isControlKeyDown = false;

        changedObj = document.getElementById(idValue);

        linkInfo = INTERMediatorLib.getLinkedElementInfo(changedObj);
        if (linkInfo.length > 0) {
            matched = linkInfo[0].match(/([^@]+)/);
            context = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', matched[1]);
            if (context["validation"] != null) {
                for (i = 0; i < linkInfo.length; i++) {
                    matched = linkInfo[i].match(/([^@]+)@([^@]+)/);
                    for (index in context["validation"]) {
                        if (context["validation"][index]["field"] == matched[2]) {
                            checkFunction = function () {
                                target = changedObj;
                                value = changedObj.value;
                                result = false;
                                eval("result = " + context["validation"][index]["rule"]);
                                if (!result) {
                                    alert(context["validation"][index]["message"]);
                                    changedObj.value = INTERMediator.updateRequiredObject[idValue]["initialvalue"];
                                    changedObj.focus();
                                    if (INTERMediatorOnPage.doAfterValidationFailure != null) {
                                        INTERMediatorOnPage.doAfterValidationFailure(target, linkInfo[i]);
                                    }
                                } else {
                                    if (INTERMediatorOnPage.doAfterValidationSucceed != null) {
                                        INTERMediatorOnPage.doAfterValidationSucceed(target, linkInfo[i]);
                                    }
                                }
                                return result;
                            }
                            if (!checkFunction()) {
                                return;
                            }
                        }
                    }
                }
            }
        }
        if (changedObj != null) {
            if (INTERMediatorOnPage.getOptionsTransaction() == 'none') {
                INTERMediator.updateRequiredObject[idValue]['edit'] = true;
            } else {
                INTERMediator.updateDB(idValue);
                INTERMediator.flushMessage();
            }
        }
    },

    updateDB: function (idValue) {
        var newValue = null, changedObj, objType, objectSpec, keyingComp, keyingField, keyingValue, currentVal,
            response, isDiffrentOnDB, valueAttr, criteria, updateNodeId, needUpdate, i, j, k, checkQueryParameter,
            dbspec, mergedValues, targetNodes;

        changedObj = document.getElementById(idValue);
        if (changedObj != null) {
            INTERMediatorOnPage.showProgress();
            INTERMediatorOnPage.retrieveAuthInfo();
            objType = changedObj.getAttribute('type');
            if (objType == 'radio' && !changedObj.checked) {
                return;
            }
            objectSpec = INTERMediator.updateRequiredObject[idValue];
            if (!INTERMediator.ignoreOptimisticLocking) {
                keyingComp = objectSpec['keying'].split('=');
                keyingField = keyingComp[0];
                keyingComp.shift();
                keyingValue = keyingComp.join('=');
                checkQueryParameter = {
                    name: objectSpec['name'],
                    records: 1,
                    paging: objectSpec['paging'],
                    fields: [objectSpec['field']],
                    parentkeyvalue: null,
                    conditions: [
                        {field: keyingField, operator: '=', value: keyingValue}
                    ],
                    useoffset: false,
                    primaryKeyOnly: true
                };
                try {
                    currentVal = INTERMediator_DBAdapter.db_query(checkQueryParameter);
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        if (INTERMediatorOnPage.requireAuthentication && !INTERMediatorOnPage.isComplementAuthData()) {
                            INTERMediatorOnPage.authChallenge = null;
                            INTERMediatorOnPage.authHashedPassword = null;
                            INTERMediatorOnPage.authenticating(
                                function () {
                                    INTERMediator.db_query(checkQueryParameter);
                                }
                            );
                            return;
                        }
                    } else {
                        INTERMediator.setErrorMessage(ex, "EXCEPTION-1");
                    }
                }

                if (currentVal.recordset == null
                    || currentVal.recordset[0] == null
                    || currentVal.recordset[0][objectSpec['field']] == null) {
                    alert(INTERMediatorLib.getInsertedString(
                        INTERMediatorOnPage.getMessages()[1003], [objectSpec['field']]));
                    return;
                }
                if (currentVal.count > 1) {
                    response = confirm(INTERMediatorOnPage.getMessages()[1024]);
                    if (!response) {
                        return;
                    }
                }
                currentVal = currentVal.recordset[0][objectSpec['field']];
                isDiffrentOnDB = (objectSpec['initialvalue'] != currentVal);
            }

            if (INTERMediator.widgetElementIds.indexOf(changedObj.getAttribute('id')) > -1) {
                newValue = changedObj._im_getValue();
            } else if (changedObj.tagName == 'TEXTAREA') {
                newValue = changedObj.value;
            } else if (changedObj.tagName == 'SELECT') {
                newValue = changedObj.value;
                if (changedObj.firstChild.value == "") {
                    // for compatibility with Firefox when the value of select tag is empty.
                    changedObj.removeChild(changedObj.firstChild);
                }
            } else if (changedObj.tagName == 'INPUT') {

                if (objType != null) {
                    if (objType == 'checkbox') {
                        dbspec = INTERMediatorOnPage.getDBSpecification();
                        if (dbspec['db-class'] != null && dbspec['db-class'] == 'FileMaker_FX') {
                            mergedValues = [];
                            targetNodes = changedObj.parentNode.getElementsByTagName('INPUT');
                            for (k = 0; k < targetNodes.length; k++) {
                                if (targetNodes[k].checked) {
                                    mergedValues.push(targetNodes[k].getAttribute('value'));
                                }
                            }
                            newValue = mergedValues.join("\n");
                            isDiffrentOnDB = (newValue == currentVal);
                        } else {
                            valueAttr = changedObj.getAttribute('value');
                            if (changedObj.checked) {
                                newValue = valueAttr;
                                isDiffrentOnDB = (valueAttr == currentVal);
                            } else {
                                newValue = '';
                                isDiffrentOnDB = (valueAttr != currentVal);
                            }
                        }
                    } else if (objType == 'radio') {
                        newValue = changedObj.value;
                    } else { //text, password
                        newValue = changedObj.value;
                    }
                }
            }
        }

        if (isDiffrentOnDB && !INTERMediator.ignoreOptimisticLocking) {
            // The value of database and the field is diffrent. Others must be changed this field.
            if (!confirm(INTERMediatorLib.getInsertedString(
                INTERMediatorOnPage.getMessages()[1001],
                [objectSpec['initialvalue'], newValue, currentVal]))) {
                return;
            }
            INTERMediatorOnPage.retrieveAuthInfo(); // This is required. Why?
        }

        if (newValue != null) {
            criteria = objectSpec['keying'].split('=');
            try {
                INTERMediator_DBAdapter.db_update({
                    name: objectSpec['name'],
                    conditions: [
                        {field: criteria[0], operator: '=', value: criteria[1]}
                    ],
                    dataset: [
                        {field: objectSpec['field'], value: newValue}
                    ]
                });
            } catch (ex) {
                if (ex == "_im_requath_request_") {
                    if (ex == "_im_requath_request_") {
                        if (INTERMediatorOnPage.requireAuthentication
                            && !INTERMediatorOnPage.isComplementAuthData()) {
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
                } else {
                    INTERMediator.setErrorMessage(ex, "EXCEPTION-2");
                }
            }

            if (changedObj.tagName == 'INPUT' && objType == 'radio') {
                for (i in INTERMediator.updateRequiredObject) {
                    if (INTERMediator.updateRequiredObject[i]['field'] == objectSpec['field']) {
                        INTERMediator.updateRequiredObject[i]['initialvalue'] = newValue;
                    }
                }
            } else {
                objectSpec['initialvalue'] = newValue;
            }
            updateNodeId = objectSpec['updatenodeid'];
            needUpdate = false;
            for (i = 0; i < INTERMediator.keyFieldObject.length; i++) {
                for (j = 0; j < INTERMediator.keyFieldObject[i]['target'].length; j++) {
                    if (INTERMediator.keyFieldObject[i]['target'][j] == idValue) {
                        needUpdate = true;
                    }
                }
            }
            if (needUpdate) {
                for (i = 0; i < INTERMediator.keyFieldObject.length; i++) {
                    if (INTERMediator.keyFieldObject[i]['node'].getAttribute('id') == updateNodeId) {
                        INTERMediator.constructMain(i);
                        break;
                    }
                }
            }
        }
        INTERMediatorOnPage.hideProgress();
    },


    deleteButton: function (targetName, keyField, keyValue, removeNodes, isConfirm) {
        var key, removeNode;
        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1025])) {
                return;
            }
        }
        INTERMediatorOnPage.showProgress();
        try {
            INTERMediatorOnPage.retrieveAuthInfo();
            INTERMediator_DBAdapter.db_delete({
                name: targetName,
                conditions: [
                    {field: keyField, operator: '=', value: keyValue}
                ]
            });
        } catch (ex) {
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
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-3");
            }
        }

        for (key in removeNodes) {
            removeNode = document.getElementById(removeNodes[key]);
            removeNode.parentNode.removeChild(removeNode);
        }
        INTERMediatorOnPage.hideProgress();
        INTERMediator.flushMessage();
    },

    insertButton: function (targetName, foreignValues, updateNodes, removeNodes, isConfirm) {
        var currentContext, recordSet, index, key, removeNode, i;
        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1026])) {
                return;
            }
        }
        INTERMediatorOnPage.showProgress();
        currentContext = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', targetName);
        recordSet = [];
        if (foreignValues != null) {
            for (index in currentContext['relation']) {
                recordSet.push({
                    field: currentContext['relation'][index]["foreign-key"],
                    value: foreignValues[currentContext['relation'][index]["join-field"]]
                });
            }
        }
        try {
            INTERMediatorOnPage.retrieveAuthInfo();
            INTERMediator_DBAdapter.db_createRecord({name: targetName, dataset: recordSet});
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
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-4");
            }
        }

        for (key in removeNodes) {
            removeNode = document.getElementById(removeNodes[key]);
            removeNode.parentNode.removeChild(removeNode);
        }
        for (i = 0; i < INTERMediator.keyFieldObject.length; i++) {
            if (INTERMediator.keyFieldObject[i]['node'].getAttribute('id') == updateNodes) {
                INTERMediator.keyFieldObject[i]['foreign-value'] = foreignValues;
                INTERMediator.constructMain(i);
                break;
            }
        }
        INTERMediatorOnPage.hideProgress();
        INTERMediator.flushMessage();
    },

    insertRecordFromNavi: function (targetName, keyField, isConfirm) {
        var key, ds, targetKey, newId, restore, fieldObj;

        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1026])) {
                return;
            }
        }
        INTERMediatorOnPage.showProgress();
        ds = INTERMediatorOnPage.getDataSources(); // Get DataSource parameters
        targetKey = null;
        for (key in ds) { // Search this table from DataSource
            if (ds[key]['name'] == targetName) {
                targetKey = key;
                break;
            }
        }
        if (targetKey === null) {
            alert("no targetname :" + targetName);
            return;
        }

        try {
            INTERMediatorOnPage.retrieveAuthInfo();
            newId = INTERMediator_DBAdapter.db_createRecord({name: targetName, dataset: []});
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
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-5");
            }
        }

        if (newId > -1) {
            restore = INTERMediator.additionalCondition;
            INTERMediator.startFrom = 0;
            fieldObj = {
                field: keyField,
                value: newId
            };
            if (ds[targetKey]['records'] <= 1) {
                INTERMediator.additionalCondition = {};
                INTERMediator.additionalCondition[targetName] = fieldObj;
            }
            INTERMediator.constructMain(true);
            INTERMediator.additionalCondition = restore;
        }
        INTERMediatorOnPage.hideProgress();
        INTERMediator.flushMessage();
    },

    deleteRecordFromNavi: function (targetName, keyField, keyValue, isConfirm) {
        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1026])) {
                return;
            }
        }
        INTERMediatorOnPage.showProgress();
        try {
            INTERMediatorOnPage.retrieveAuthInfo();
            INTERMediator_DBAdapter.db_delete({
                name: targetName,
                conditions: [
                    {field: keyField, operator: '=', value: keyValue}
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
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-6");
            }
        }

        if (INTERMediator.pagedAllCount - INTERMediator.startFrom < 2) {
            INTERMediator.startFrom--;
            if (INTERMediator.startFrom < 0) {
                INTERMediator.startFrom = 0;
            }
        }
        INTERMediator.constructMain(true);
        INTERMediatorOnPage.hideProgress();
        INTERMediator.flushMessage();
    },

    saveRecordFromNavi: function () {
        var idValue;

        for (idValue in INTERMediator.updateRequiredObject) {
            if (INTERMediator.updateRequiredObject[idValue]['edit']) {
                INTERMediator.updateDB(idValue);
            }
        }
        INTERMediator.flushMessage();
    },

    partialConstructing: false,
    objectReference: {},

    linkedElmCounter: 0,

    clickPostOnlyButton: function (node) {
        var i, j, fieldData, elementInfo, comp, contextCount, selectedContext, contextInfo;
        var mergedValues, inputNodes, typeAttr, k;
        var linkedNodes, namedNodes;
        var target = node.parentNode;
        while (!INTERMediatorLib.isEnclosure(target, true)) {
            target = target.parentNode;
            if (!target) {
                return;
            }
        }
        linkedNodes = []; // Collecting linked elements to this array.
        namedNodes = [];
        for (i = 0; i < target.childNodes.length; i++) {
            seekLinkedElement(target.childNodes[i]);
        }
        contextCount = {};
        for (i = 0; i < linkedNodes.length; i++) {
            elementInfo = INTERMediatorLib.getLinkedElementInfo(linkedNodes[i]);
            for (j = 0; j < elementInfo.length; j++) {
                comp = elementInfo[j].split(INTERMediator.separator);
                if (!contextCount[comp[j]]) {
                    contextCount[comp[j]] = 0;
                }
                contextCount[comp[j]]++;
            }
        }
        if (contextCount.length < 1) {
            return;
        }
        var maxCount = -100;
        for (var contextName in contextCount) {
            if (maxCount < contextCount[contextName]) {
                maxCount = contextCount[contextName];
                selectedContext = contextName;
            }
        }

        fieldData = [];
        for (i = 0; i < linkedNodes.length; i++) {
            elementInfo = INTERMediatorLib.getLinkedElementInfo(linkedNodes[i]);
            for (j = 0; j < elementInfo.length; j++) {
                comp = elementInfo[j].split(INTERMediator.separator);
                if (comp[0] == selectedContext) {
                    if (INTERMediatorLib.isWidgetElement(linkedNodes[i])) {
                        fieldData.push({field: comp[1], value: linkedNodes[i]._im_getValue()});
                    } else if (linkedNodes[i].tagName == 'SELECT') {
                        fieldData.push({field: comp[1], value: linkedNodes[i].value});
                    } else if (linkedNodes[i].tagName == 'TEXTAREA') {
                        fieldData.push({field: comp[1], value: linkedNodes[i].value});
                    } else if (linkedNodes[i].tagName == 'INPUT') {
                        if (( linkedNodes[i].getAttribute('type') == 'radio' )
                            || ( linkedNodes[i].getAttribute('type') == 'checkbox' )) {
                            if (linkedNodes[i].checked) {
                                fieldData.push({field: comp[1], value: linkedNodes[i].value});
                            }
                        } else {
                            fieldData.push({field: comp[1], value: linkedNodes[i].value});
                        }
                    }
                }
            }
        }
        for (i = 0; i < namedNodes.length; i++) {
            elementInfo = INTERMediatorLib.getNamedInfo(namedNodes[i]);
            for (j = 0; j < elementInfo.length; j++) {
                comp = elementInfo[j].split(INTERMediator.separator);
                if (comp[0] == selectedContext) {
                    mergedValues = [];
                    inputNodes = namedNodes[i].getElementsByTagName('INPUT');
                    for (k = 0; k < inputNodes.length; k++) {
                        typeAttr = inputNodes[k].getAttribute('type');
                        if (typeAttr == 'radio' || typeAttr == 'checkbox') {
                            if (inputNodes[k].checked) {
                                mergedValues.push(inputNodes[k].value);
                            }
                        } else {
                            mergedValues.push(inputNodes[k].value);
                        }
                    }
                    fieldData.push({field: comp[1],
                        value: mergedValues.join("\n") + "\n"});
                }
            }
        }

        contextInfo = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', selectedContext);
        INTERMediator_DBAdapter.db_createRecordWithAuth(
            {name: selectedContext, dataset: fieldData},
            function (returnValue) {
                var newNode, parentOfTarget, targetNode = node, thisContext = contextInfo, isSetMsg = false;
                INTERMediator.flushMessage();
                if (INTERMediatorOnPage.processingAfterPostOnlyContext) {
                    INTERMediatorOnPage.processingAfterPostOnlyContext(targetNode);
                }
                if (thisContext['post-dismiss-message']) {
                    parentOfTarget = targetNode.parentNode;
                    parentOfTarget.removeChild(targetNode);
                    newNode = document.createElement('SPAN');
                    INTERMediatorLib.setClassAttributeToNode(newNode, 'IM_POSTMESSAGE');
                    newNode.appendChild(document.createTextNode(thisContext['post-dismiss-message']));
                    parentOfTarget.appendChild(newNode);
                    isSetMsg = true;
                }
                if (thisContext['post-reconstruct']) {
                    setTimeout(function () {
                        INTERMediator.construct(true);
                    }, isSetMsg ? INTERMediator.waitSecondsAfterPostMessage * 1000 : 0);
                }
                if (thisContext['post-move-url']) {
                    setTimeout(function () {
                        location.href = thisContext['post-move-url'];
                    }, isSetMsg ? INTERMediator.waitSecondsAfterPostMessage * 1000 : 0);
                }
            });

        function seekLinkedElement(node) {
            var children, i;
            if (node.nodeType === 1) {
                if (INTERMediatorLib.isLinkedElement(node)) {
                    linkedNodes.push(node);
                } else if (INTERMediatorLib.isWidgetElement(node)) {
                    linkedNodes.push(node);
                } else if (INTERMediatorLib.isNamedElement(node)) {
                    namedNodes.push(node);
                } else {
                    children = node.childNodes;
                    for (i = 0; i < children.length; i++) {
                        seekLinkedElement(children[i]);
                    }
                }
            }
        }


    },

//=================================
// Construct Page
//=================================
    /**
     * Construct the Web Page with DB Data
     * You should call here when you show the page.
     *
     * parameter: true=construct page, others=construct partially
     */
    construct: function (indexOfKeyFieldObject) {
        var timerTask;
        INTERMediatorOnPage.showProgress();
        if (indexOfKeyFieldObject === true || indexOfKeyFieldObject === undefined) {
            timerTask = 'INTERMediator.constructMain(true)';
        } else {
            timerTask = 'INTERMediator.constructMain(' + indexOfKeyFieldObject + ')';
        }
        setTimeout(timerTask, 0);
    },


    constructMain: function (indexOfKeyFieldObject) {
        var i, theNode, currentLevel = 0, postSetFields = [], buttonIdNum = 1,
            deleteInsertOnNavi = [], eventListenerPostAdding = [], isInsidePostOnly, nameAttrCounter = 1;

        INTERMediatorOnPage.retrieveAuthInfo();
        try {
            if (indexOfKeyFieldObject === true || indexOfKeyFieldObject === undefined) {
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
                                INTERMediator.constructMain(indexOfKeyFieldObject);
                            }
                        );
                        return;
                    }
                }
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-7");
            }
        }
        INTERMediatorOnPage.hideProgress();

        // Event listener should add after adding node to document.
        for (i = 0; i < eventListenerPostAdding.length; i++) {
            theNode = document.getElementById(eventListenerPostAdding[i].id);
            if (theNode) {
                INTERMediatorLib.addEvent(
                    theNode, eventListenerPostAdding[i].event, eventListenerPostAdding[i].todo);
            }
        }

        if (INTERMediatorOnPage.doAfterConstruct) {
            INTERMediatorOnPage.doAfterConstruct();
        }

        INTERMediator.flushMessage(); // Show messages


        function partialConstruct(indexOfKeyFieldObject) {
            var updateNode, originalNodes, i, beforeKeyFieldObjectCount, currentNode, currentID,
                enclosure, field, targetNode;

            isInsidePostOnly = false;

            updateNode = INTERMediator.keyFieldObject[indexOfKeyFieldObject]['node'];
            while (updateNode.firstChild) {
                updateNode.removeChild(updateNode.firstChild);
            }
            originalNodes = INTERMediator.keyFieldObject[indexOfKeyFieldObject]['original'];
            for (i = 0; i < originalNodes.length; i++) {
                updateNode.appendChild(originalNodes[i]);
            }
            beforeKeyFieldObjectCount = INTERMediator.keyFieldObject.length;
            postSetFields = [];
            try {
                seekEnclosureNode(
                    updateNode,
                    INTERMediator.keyFieldObject[indexOfKeyFieldObject]['foreign-value'],
                    INTERMediator.keyFieldObject[indexOfKeyFieldObject]['name'],
                    INTERMediatorLib.getEnclosureSimple(updateNode),
                    null
                );
            } catch (ex) {
                if (ex == "_im_requath_request_") {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex, "EXCEPTION-8");
                }
            }

            for (i = 0; i < postSetFields.length; i++) {
                document.getElementById(postSetFields[i]['id']).value = postSetFields[i]['value'];
            }
            for (i = beforeKeyFieldObjectCount + 1; i < INTERMediator.keyFieldObject.length; i++) {
                currentNode = INTERMediator.keyFieldObject[i];
                currentID = currentNode['node'].getAttribute('id');
                if (currentNode['target'] == null) {

                    if (currentID != null && currentID.match(/IM[0-9]+-[0-9]+/)) {
                        enclosure = INTERMediatorLib.getParentRepeater(currentNode['node']);
                    } else {
                        enclosure = INTERMediatorLib.getParentRepeater(
                            INTERMediatorLib.getParentEnclosure(currentNode['node']));
                    }
                    if (enclosure != null) {
                        for (field in currentNode['foreign-value']) {
                            targetNode = getEnclosedNode(enclosure, currentNode['name'], field);
                            if (targetNode) {
                                currentNode['target'] = targetNode.getAttribute('id');
                            }
                        }
                    }
                }
            }

        }

        function pageConstruct() {
            var ua, msiePos, i, c, bodyNode, currentNode, currentID, enclosure, targetNode, emptyElement;

            INTERMediator.keyFieldObject = [];
            INTERMediator.updateRequiredObject = {};
            INTERMediator.currentEncNumber = 1;
            INTERMediator.widgetElementIds = [];
            isInsidePostOnly = false;

            // Detect Internet Explorer and its version.
            ua = navigator.userAgent;
            msiePos = ua.toLocaleUpperCase().indexOf('MSIE');
            if (msiePos >= 0) {
                INTERMediator.isIE = true;
                for (i = msiePos + 4; i < ua.length; i++) {
                    c = ua.charAt(i);
                    if (c != ' ' && c != '.' && (c < '0' || c > '9')) {
                        INTERMediator.ieVersion = INTERMediatorLib.toNumber(ua.substring(msiePos + 4, i));
                        break;
                    }
                }
            }
            // Restoring original HTML Document from backup data.
            bodyNode = document.getElementsByTagName('BODY')[0];
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
                } else {
                    INTERMediator.setErrorMessage(ex, "EXCEPTION-9");
                }
            }


            // After work to set up popup menus.
            for (i = 0; i < postSetFields.length; i++) {
                if (postSetFields[i]['value'] == "" && document.getElementById(postSetFields[i]['id']).tagName == "SELECT") {
                    // for compatibility with Firefox when the value of select tag is empty.
                    emptyElement = document.createElement('option');
                    emptyElement.setAttribute("value", "");
                    document.getElementById(postSetFields[i]['id']).insertBefore(emptyElement, document.getElementById(postSetFields[i]['id']).firstChild);
                }
                document.getElementById(postSetFields[i]['id']).value = postSetFields[i]['value'];
            }
            for (i = 0; i < INTERMediator.keyFieldObject.length; i++) {
                currentNode = INTERMediator.keyFieldObject[i];
                currentID = currentNode['node'].getAttribute('id');
                if (currentNode['target'] == null) {
                    if (currentID != null && currentID.match(/IM[0-9]+-[0-9]+/)) {
                        enclosure = INTERMediatorLib.getParentRepeater(currentNode['node']);
                    } else {
                        enclosure = INTERMediatorLib.getParentRepeater(
                            INTERMediatorLib.getParentEnclosure(currentNode['node']));
                    }
                    if (enclosure != null) {
                        targetNode = getEnclosedNode(enclosure, currentNode['name'], currentNode['field']);
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
            var children, className, i;
            if (node.nodeType === 1) { // Work for an element
                try {
                    if (INTERMediatorLib.isEnclosure(node, false)) { // Linked element and an enclosure
                        className = INTERMediatorLib.getClassAttributeFromNode(node);
                        if (className && className.match(/_im_post/)) {
                            setupPostOnlyEnclosure(node);
                        } else {
                            if (INTERMediator.isIE) {
                                try {
                                    expandEnclosure(node, currentRecord, currentTable, parentEnclosure, objectReference);
                                } catch (ex) {
                                    if (ex == "_im_requath_request_") {
                                        throw ex;
                                    }
                                }
                            } else {
                                expandEnclosure(node, currentRecord, currentTable, parentEnclosure, objectReference);
                            }
                        }
                    } else {
                        children = node.childNodes; // Check all child nodes.
                        if (children) {
                            for (i = 0; i < children.length; i++) {
                                if (children[i].nodeType === 1) {
                                    seekEnclosureNode(
                                        children[i],
                                        currentRecord,
                                        currentTable,
                                        parentEnclosure,
                                        objectReference
                                    );
                                }
                            }
                        }
                    }
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex, "EXCEPTION-10");
                    }
                }

            }
        }

        function setupPostOnlyEnclosure(node) {
            var nodes;
            var postNodes = INTERMediatorLib.getElementsByClassName(node, '_im_post');
            for (var i = 1; i < postNodes.length; i++) {
                INTERMediatorLib.addEvent(
                    postNodes[i],
                    'click',
                    (function () {
                        var targetNode = postNodes[i];
                        return function () {
                            INTERMediator.clickPostOnlyButton(targetNode);
                        }
                    })());
            }
            nodes = node.childNodes;
            isInsidePostOnly = true;
            for (i = 0; i < nodes.length; i++) {
                seekEnclosureInPostOnly(nodes[i]);
            }
            isInsidePostOnly = false;
            // -------------------------------------------
            function seekEnclosureInPostOnly(node) {
                var children, i;
                if (node.nodeType === 1) { // Work for an element
                    try {
                        if (INTERMediatorLib.isEnclosure(node, false)) { // Linked element and an enclosure
                            expandEnclosure(node, null, null, null, null);
                        } else {
                            children = node.childNodes; // Check all child nodes.
                            for (i = 0; i < children.length; i++) {
                                seekEnclosureInPostOnly(children[i]);
                            }
                        }
                    } catch (ex) {
                        if (ex == "_im_requath_request_") {
                            throw ex;
                        } else {
                            INTERMediator.setErrorMessage(ex, "EXCEPTION-11");
                        }
                    }
                }
            }
        }

        /**
         * Expanding an enclosure.
         */

        function expandEnclosure(node, currentRecord, currentTable, parentEnclosure, parentObjectInfo) {
            var objectReference = {}, linkedNodes, encNodeTag, parentNodeId, repeatersOriginal, repeaters,
                linkDefs, voteResult, currentContext, fieldList, repNodeTag, relationValue, dependObject,
                relationDef, index, fieldName, thisKeyFieldObject, i, j, k, ix, targetRecords, newNode,
                nodeClass, repeatersOneRec, currentLinkedNodes, shouldDeleteNodes, keyField, keyValue, counter,
                nodeTag, typeAttr, linkInfoArray, RecordCounter, valueChangeFunction, nInfo, curVal,
                curTarget, postCallFunc, newlyAddedNodes, keyingValue, oneRecord, isMatch, pagingValue,
                recordsValue, currentWidgetNodes, widgetSupport, nodeId, nameAttr, nameNumber, nameTable;

            currentLevel++;
            INTERMediator.currentEncNumber++;

            widgetSupport = {};

            if (!node.getAttribute('id')) {
                node.setAttribute('id', nextIdValue());
            }

            encNodeTag = node.tagName;
            parentNodeId = (parentEnclosure == null ? null : parentEnclosure.getAttribute('id'));
            repNodeTag = INTERMediatorLib.repeaterTagFromEncTag(encNodeTag);
            repeatersOriginal = collectRepeatersOriginal(node, repNodeTag); // Collecting repeaters to this array.
            repeaters = collectRepeaters(repeatersOriginal);  // Collecting repeaters to this array.
            linkedNodes = collectLinkedElement(repeaters).linkedNode;
            linkDefs = collectLinkDefinitions(linkedNodes);
            voteResult = tableVoting(linkDefs);
            currentContext = voteResult.targettable;
            fieldList = voteResult.fieldlist; // Create field list for database fetch.

            if (currentContext) {
                try {
                    relationValue = null;
                    dependObject = [];
                    relationDef = currentContext['relation'];
                    if (relationDef) {
                        relationValue = {};
                        for (index in relationDef) {
                            relationValue[ relationDef[index]['join-field'] ]
                                = currentRecord[relationDef[index]['join-field']];
                            for (fieldName in parentObjectInfo) {
                                if (fieldName == relationDef[index]['join-field']) {
                                    dependObject.push(parentObjectInfo[fieldName]);
                                }
                            }
                        }
                    }

                    thisKeyFieldObject = {
                        'node': node,
                        'name': currentContext['name'] /*currentTable */,
                        'foreign-value': relationValue,
                        'parent': node.parentNode,
                        'original': [],
                        'target': dependObject
                    };
                    for (i = 0; i < repeatersOriginal.length; i++) {
                        thisKeyFieldObject.original.push(repeatersOriginal[i].cloneNode(true));
                    }
                    INTERMediator.keyFieldObject.push(thisKeyFieldObject);

                    // Access database and get records
                    pagingValue = false;
                    if (currentContext['paging']) {
                        pagingValue = currentContext['paging'];
                    }
                    recordsValue = 10000000000;
                    if (currentContext['records']) {
                        recordsValue = currentContext['records'];
                    }
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex, "EXCEPTION-25");
                    }
                }

                if (currentContext['cache'] == true) {
                    try {
                        if (!INTERMediatorOnPage.dbCache[currentContext['name']]) {
                            INTERMediatorOnPage.dbCache[currentContext['name']] = INTERMediator_DBAdapter.db_query({
                                name: currentContext['name'],
                                records: null,
                                paging: null,
                                fields: fieldList,
                                parentkeyvalue: null,
                                conditions: null,
                                useoffset: false});
                        }
                        if (relationValue == null) {
                            targetRecords = INTERMediatorOnPage.dbCache[currentContext['name']];
                        } else {
                            targetRecords = {recordset: [], count: 0};
                            counter = 0;
                            for (ix in INTERMediatorOnPage.dbCache[currentContext['name']].recordset) {
                                oneRecord = INTERMediatorOnPage.dbCache[currentContext['name']].recordset[ix];
                                isMatch = true;
                                index = 0;
                                for (keyField in relationValue) {
                                    fieldName = currentContext['relation'][index]['foreign-key'];
                                    if (oneRecord[fieldName] != relationValue[keyField]) {
                                        isMatch = false;
                                        break;
                                    }
                                    index++;
                                }
                                if (isMatch) {
                                    if (!pagingValue || (pagingValue && ( counter >= INTERMediator.startFrom ))) {
                                        targetRecords.recordset.push(oneRecord);
                                        targetRecords.count++;
                                        if (recordsValue <= targetRecords.count) {
                                            break;
                                        }
                                    }
                                    counter++;
                                }
                            }
                        }
                    } catch (ex) {
                        if (ex == "_im_requath_request_") {
                            throw ex;
                        } else {
                            INTERMediator.setErrorMessage(ex, "EXCEPTION-24");
                        }
                    }

                } else {   // cache is not active.
                    try {
                        targetRecords = INTERMediator_DBAdapter.db_query({
                            name: currentContext['name'],
                            records: currentContext['records'],
                            paging: currentContext['paging'],
                            fields: fieldList,
                            parentkeyvalue: relationValue,
                            conditions: null,
                            useoffset: true});
                    } catch (ex) {
                        if (ex == "_im_requath_request_") {
                            throw ex;
                        } else {
                            INTERMediator.setErrorMessage(ex, "EXCEPTION-12");
                        }
                    }
                }

                if (targetRecords.count == 0) {
                    for (i = 0; i < repeaters.length; i++) {
                        newNode = repeaters[i].cloneNode(true);
                        nodeClass = INTERMediatorLib.getClassAttributeFromNode(newNode);
                        if (nodeClass == INTERMediator.noRecordClassName) {
                            node.appendChild(newNode);
                            if (newNode.getAttribute('id') == null) {
//                                idValue = 'IM' + INTERMediator.currentEncNumber + '-' + INTERMediator.linkedElmCounter;
                                newNode.setAttribute('id', nextIdValue());
//                                INTERMediator.linkedElmCounter++;
                            }
                        }
                    }
                }

                RecordCounter = 0;
                for (ix in targetRecords.recordset) { // for each record
                    try {
                        RecordCounter++;
                        repeatersOneRec = cloneEveryNodes(repeatersOriginal);
                        currentWidgetNodes = collectLinkedElement(repeatersOneRec).widgetNode;
                        currentLinkedNodes = collectLinkedElement(repeatersOneRec).linkedNode;
                        shouldDeleteNodes = shouldDeleteNodeIds(repeatersOneRec);
                        keyField = currentContext['key'] ? currentContext['key'] : 'id';
                        keyValue = targetRecords.recordset[ix][keyField];
                        keyingValue = keyField + "=" + keyValue;

                        for (k = 0; k < currentLinkedNodes.length; k++) {
                            // for each linked element
                            if (currentLinkedNodes[k].getAttribute('id') == null) {
                                currentLinkedNodes[k].setAttribute('id', nextIdValue());
                            }
                        }
                        for (k = 0; k < currentWidgetNodes.length; k++) {
                            var wInfo = INTERMediatorLib.getWidgetInfo(currentWidgetNodes[k]);
                            if (wInfo[0]) {
                                if (!widgetSupport[wInfo[0]]) {
                                    var targetName = "IMParts_" + wInfo[0];
                                    widgetSupport[wInfo[0]] = {
                                        plugin: eval(targetName),
                                        instanciate: eval(targetName + ".instanciate"),
                                        finish: eval(targetName + ".finish")};
                                }
                                (widgetSupport[wInfo[0]].instanciate).apply(
                                    (widgetSupport[wInfo[0]].plugin), [currentWidgetNodes[k]]);
                            }
                        }
                    } catch (ex) {
                        if (ex == "_im_requath_request_") {
                            throw ex;
                        } else {
                            INTERMediator.setErrorMessage(ex, "EXCEPTION-26");
                        }
                    }

                    nameTable = {};
                    for (k = 0; k < currentLinkedNodes.length; k++) {
                        try {
                            nodeTag = currentLinkedNodes[k].tagName;
                            nodeId = currentLinkedNodes[k].getAttribute('id');
                            if (INTERMediatorLib.isWidgetElement(currentLinkedNodes[k])) {
                                nodeId = currentLinkedNodes[k]._im_getComponentId();
                                INTERMediator.widgetElementIds.push(nodeId);
                            }
                            // get the tag name of the element
                            typeAttr = currentLinkedNodes[k].getAttribute('type');
                            // type attribute
                            linkInfoArray = INTERMediatorLib.getLinkedElementInfo(currentLinkedNodes[k]);
                            // info array for it  set the name attribute of radio button
                            // should be different for each group
                            if (typeAttr == 'radio') { // set the value to radio button
                                nameTableKey = linkInfoArray.join('|');
                                if (!nameTable[nameTableKey]) {
                                    nameTable[nameTableKey] = nameAttrCounter;
                                    nameAttrCounter++
                                }
                                nameNumber = nameTable[nameTableKey];
//                            nameNumber = INTERMediator.radioNameMode ? currentLevel : RecordCounter;nameAttrCounter
                                nameAttr = currentLinkedNodes[k].getAttribute('name');
                                if (nameAttr) {
                                    currentLinkedNodes[k].setAttribute('name', nameAttr + '-' + nameNumber);
                                } else {
                                    currentLinkedNodes[k].setAttribute('name', 'IM-R-' + nameNumber);
                                }
                            }

                            if (!isInsidePostOnly
                                && (nodeTag == 'INPUT' || nodeTag == 'SELECT' || nodeTag == 'TEXTAREA')) {
                                valueChangeFunction = function (targetId) {
                                    var theId = targetId;
                                    return function (evt) {
                                        INTERMediator.valueChange(theId);
                                    }
                                };
                                eventListenerPostAdding.push({
                                    'id': nodeId,
                                    'event': 'change',
                                    'todo': valueChangeFunction(nodeId)
                                });
                                if (nodeTag != 'SELECT') {
                                    eventListenerPostAdding.push({
                                        'id': nodeId,
                                        'event': 'keydown',
                                        'todo': INTERMediator.keyDown
                                    });
                                    eventListenerPostAdding.push({
                                        'id': nodeId,
                                        'event': 'keyup',
                                        'todo': INTERMediator.keyUp
                                    });
                                }
                            }

                            for (j = 0; j < linkInfoArray.length; j++) {
                                // for each info Multiple replacement definitions
                                // for one node is prohibited.
                                nInfo = INTERMediatorLib.getNodeInfoArray(linkInfoArray[j]);
                                curVal = targetRecords.recordset[ix][nInfo['field']];
                                if (curVal == null) {
                                    curVal = '';
                                }
                                curTarget = nInfo['target'];
                                // Store the key field value and current value for update

                                if (nodeTag == 'INPUT' || nodeTag == 'SELECT' || nodeTag == 'TEXTAREA'
                                    || INTERMediatorLib.isWidgetElement(currentLinkedNodes[k])) {
                                    INTERMediator.updateRequiredObject[nodeId] = {
                                        targetattribute: curTarget,
                                        initialvalue: curVal,
                                        name: currentContext['name'],
                                        field: nInfo['field'],
                                        'parent-enclosure': node.getAttribute('id'),
                                        keying: keyingValue,
                                        'foreign-value': relationValue,
                                        updatenodeid: parentNodeId};
                                }

                                objectReference[nInfo['field']] = nodeId;

                                // Set data to the element.
                                if (setDataToElement(currentLinkedNodes[k], curTarget, curVal)) {
                                    postSetFields.push({'id': nodeId, 'value': curVal});
                                }
                            }
                        } catch (ex) {
                            if (ex == "_im_requath_request_") {
                                throw ex;
                            } else {
                                INTERMediator.setErrorMessage(ex, "EXCEPTION-26");
                            }
                        }

                    }
                    setupDeleteButton(encNodeTag, repNodeTag, repeatersOneRec[repeatersOneRec.length - 1],
                        currentContext, keyField, keyValue, shouldDeleteNodes);

                    newlyAddedNodes = [];
                    for (i = 0; i < repeatersOneRec.length; i++) {
                        newNode = repeatersOneRec[i].cloneNode(true);
                        nodeClass = INTERMediatorLib.getClassAttributeFromNode(newNode);
                        if (nodeClass != INTERMediator.noRecordClassName) {
                            node.appendChild(newNode);
                            newlyAddedNodes.push(newNode);
                            if (newNode.getAttribute('id') == null) {
                                newNode.setAttribute('id', nextIdValue());
                            }
                            seekEnclosureNode(newNode, targetRecords.recordset[ix],
                                currentContext['name'], node, objectReference);
                        }
                    }
                    try {
                        if (INTERMediatorOnPage.expandingRecordFinish != null) {
                            INTERMediatorOnPage.expandingRecordFinish(currentContext['name'], newlyAddedNodes);
                            INTERMediator.setDebugMessage(
                                "Call INTERMediatorOnPage.expandingRecordFinish with the context: "
                                    + currentContext['name'], 2);
                        }

                        if (currentContext['post-repeater']) {
                            postCallFunc = new Function("arg",
                                "INTERMediatorOnPage." + currentContext['post-repeater'] + "(arg)");
                            postCallFunc(newlyAddedNodes);
                            INTERMediator.setDebugMessage("Call the post repeater method 'INTERMediatorOnPage."
                                + currentContext['post-repeater'] + "' with the context: " + currentContext['name'], 2);
                        }
                    } catch (ex) {
                        if (ex == "_im_requath_request_") {
                            throw ex;
                        } else {
                            INTERMediator.setErrorMessage(ex, "EXCEPTION-23");
                        }
                    }

                }
                setupInsertButton(currentContext, encNodeTag, repNodeTag, node, relationValue);

                for (var pName in widgetSupport) {
//                    (widgetSupport[pName].finish).apply(
//                        (widgetSupport[pName].plugin), null );
                    widgetSupport[pName].plugin.finish();
                }
                try {
                    if (INTERMediatorOnPage.expandingEnclosureFinish != null) {
                        INTERMediatorOnPage.expandingEnclosureFinish(currentContext['name'], node);
                        INTERMediator.setDebugMessage(
                            "Call INTERMediatorOnPage.expandingEnclosureFinish with the context: "
                                + currentContext['name'], 2);
                    }
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex, "EXCEPTION-21");
                    }
                }

                try {
                    if (currentContext['post-enclosure']) {
                        postCallFunc = new Function("arg",
                            "INTERMediatorOnPage." + currentContext['post-enclosure'] + "(arg)");
                        postCallFunc(node);
                        INTERMediator.setDebugMessage(
                            "Call the post enclosure method 'INTERMediatorOnPage." + currentContext['post-enclosure']
                                + "' with the context: " + currentContext['name'], 2);
                    }
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex, "EXCEPTION-22: hint: post-enclosure of " + currentContext.name);
                    }
                }

            } else {
                repeaters = [];
                for (i = 0; i < repeatersOriginal.length; i++) {
                    newNode = node.appendChild(repeatersOriginal[i]);
                    seekEnclosureNode(newNode, null, null, node, null);
                }
            }
            currentLevel--;
        }

        function nextIdValue() {
            INTERMediator.linkedElmCounter++;
            return currentIdValue();
        }

        function currentIdValue() {
            return 'IM' + INTERMediator.currentEncNumber + '-' + INTERMediator.linkedElmCounter;
        }

        function collectRepeatersOriginal(node, repNodeTag) {
            var i, repeatersOriginal = [], children;

            children = node.childNodes; // Check all child node of the enclosure.
            for (i = 0; i < children.length; i++) {
                if (children[i].nodeType === 1 && children[i].tagName == repNodeTag) {
                    // If the element is a repeater.
                    repeatersOriginal.push(children[i]); // Record it to the array.
                }
            }
            return repeatersOriginal;
        }

        function collectRepeaters(repeatersOriginal) {
            var i, repeaters = [], inDocNode, parentOfRep, cloneNode;
            for (i = 0; i < repeatersOriginal.length; i++) {
                inDocNode = repeatersOriginal[i];
                parentOfRep = repeatersOriginal[i].parentNode;
                cloneNode = repeatersOriginal[i].cloneNode(true);
                repeaters.push(cloneNode);
                cloneNode.setAttribute('id', nextIdValue());
                parentOfRep.removeChild(inDocNode);
            }
            return repeaters;
        }

        var linkedNodesCollection;
        var widgetNodesCollection;

        function collectLinkedElement(repeaters) {
            var i;
            linkedNodesCollection = []; // Collecting linked elements to this array.
            widgetNodesCollection = [];
            for (i = 0; i < repeaters.length; i++) {
                seekLinkedElement(repeaters[i]);
            }
            return {linkedNode: linkedNodesCollection, widgetNode: widgetNodesCollection};
        }

        function seekLinkedElement(node) {
            var nType, currentEnclosure, children, detectedEnclosure, i;
            nType = node.nodeType;
            if (nType === 1) {
                if (INTERMediatorLib.isLinkedElement(node)) {
                    currentEnclosure = INTERMediatorLib.getEnclosure(node);
                    if (currentEnclosure === null) {
                        linkedNodesCollection.push(node);
                    } else {
                        return currentEnclosure;
                    }
                }
                if (INTERMediatorLib.isWidgetElement(node)) {
                    currentEnclosure = INTERMediatorLib.getEnclosure(node);
                    if (currentEnclosure === null) {
                        widgetNodesCollection.push(node);
                    } else {
                        return currentEnclosure;
                    }
                }
                children = node.childNodes;
                for (i = 0; i < children.length; i++) {
                    detectedEnclosure = seekLinkedElement(children[i]);
//                    if (detectedEnclosure !== null) {
//                        if (detectedEnclosure == children[i]) {
//                            return null;
//                        } else {
//                            return detectedEnclosure;
//                        }
//                    }
                }
            }
            return null;
        }

        function collectLinkDefinitions(linkedNodes) {
            var linkDefs = [], nodeDefs, j, k;
            for (j = 0; j < linkedNodes.length; j++) {
                nodeDefs = INTERMediatorLib.getLinkedElementInfo(linkedNodes[j]);
                if (nodeDefs !== null) {
                    for (k = 0; k < nodeDefs.length; k++) {
                        linkDefs.push(nodeDefs[k]);
                    }
                }
            }
            return linkDefs;
        }

        function tableVoting(linkDefs) {
            var j, nodeInfoArray, nodeInfoField, nodeInfoTable, maxVoted, maxTableName, tableName,
                nodeInfoTableIndex, context,
                tableVote = [],    // Containing editable elements or not.
                fieldList = []; // Create field list for database fetch.

            for (j = 0; j < linkDefs.length; j++) {
                nodeInfoArray = INTERMediatorLib.getNodeInfoArray(linkDefs[j]);
                nodeInfoField = nodeInfoArray['field'];
                nodeInfoTable = nodeInfoArray['table'];
                nodeInfoTableIndex = nodeInfoArray['tableindex'];   // Table name added "_im_index_" as the prefix.
                if (nodeInfoField != null && nodeInfoTable != null &&
                    nodeInfoField.length != 0 && nodeInfoTable.length != 0) {
                    if (fieldList[nodeInfoTableIndex] == null) {
                        fieldList[nodeInfoTableIndex] = [];
                    }
                    fieldList[nodeInfoTableIndex].push(nodeInfoField);
                    if (tableVote[nodeInfoTableIndex] == null) {
                        tableVote[nodeInfoTableIndex] = 1;
                    } else {
                        ++tableVote[nodeInfoTableIndex];
                    }
                } else {
                    INTERMediator.setErrorMessage(
                        INTERMediatorLib.getInsertedStringFromErrorNumber(1006, [linkDefs[j]]));
                    //   return null;
                }
            }
            maxVoted = -1;
            maxTableName = ''; // Which is the maximum voted table name.
            for (tableName in tableVote) {
                if (maxVoted < tableVote[tableName]) {
                    maxVoted = tableVote[tableName];
                    maxTableName = tableName.substring(10);
                }
            }
            context = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', maxTableName);
            return {targettable: context, fieldlist: fieldList["_im_index_" + maxTableName]};
        }

        function cloneEveryNodes(originalNodes) {
            var i, clonedNodes = [];
            for (i = 0; i < originalNodes.length; i++) {
                clonedNodes.push(originalNodes[i].cloneNode(true));
            }
            return clonedNodes;
        }

        function shouldDeleteNodeIds(repeatersOneRec) {
            var shouldDeleteNodes = [], i;
            for (i = 0; i < repeatersOneRec.length; i++) {
                if (repeatersOneRec[i].getAttribute('id') == null) {
                    repeatersOneRec[i].setAttribute('id', nextIdValue());
                }
                shouldDeleteNodes.push(repeatersOneRec[i].getAttribute('id'));
            }
            return shouldDeleteNodes;
        }

        function setDataToElement(element, curTarget, curVal) {
            var styleName, statement, currentValue, scriptNode, typeAttr, valueAttr, textNode,
                needPostValueSet = false, nodeTag, curValues, i;
            // IE should \r for textNode and <br> for innerHTML, Others is not required to convert
            nodeTag = element.tagName;

            if (curTarget != null && curTarget.length > 0) { //target is specified
                if (curTarget.charAt(0) == '#') { // Appending
                    curTarget = curTarget.substring(1);
                    if (curTarget == 'innerHTML') {
                        if (INTERMediator.isIE && nodeTag == "TEXTAREA") {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br>");
                        }
                        element.innerHTML += curVal;
                    } else if (curTarget == 'textNode' || curTarget == 'script') {
                        textNode = document.createTextNode(curVal);
                        if (nodeTag == "TEXTAREA") {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r");
                        }
                        element.appendChild(textNode);
                    } else if (curTarget.indexOf('style.') == 0) {
                        styleName = curTarget.substring(6, curTarget.length);
                        statement = "element.style." + styleName + "='" + curVal + "';";
                        eval(statement);
                    } else {
                        currentValue = element.getAttribute(curTarget);
                        element.setAttribute(curTarget, currentValue + curVal);
                    }
                }
                else if (curTarget.charAt(0) == '$') { // Replacing
                    curTarget = curTarget.substring(1);
                    if (curTarget == 'innerHTML') {
                        if (INTERMediator.isIE && nodeTag == "TEXTAREA") {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br>");
                        }
                        element.innerHTML = element.innerHTML.replace("$", curVal);
                    } else if (curTarget == 'textNode' || curTarget == 'script') {
                        if (nodeTag == "TEXTAREA") {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r");
                        }
                        element.innerHTML = element.innerHTML.replace("$", curVal);
                    } else if (curTarget.indexOf('style.') == 0) {
                        styleName = curTarget.substring(6, curTarget.length);
                        statement = "element.style." + styleName + "='" + curVal + "';";
                        eval(statement);
                    } else {
                        currentValue = element.getAttribute(curTarget);
                        element.setAttribute(curTarget, currentValue.replace("$", curVal));
                    }
                } else { // Setting
                    if (INTERMediatorLib.isWidgetElement(element)) {
                        element._im_setValue(curVal);
                    } else if (curTarget == 'innerHTML') { // Setting
                        if (INTERMediator.isIE && nodeTag == "TEXTAREA") {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br>");
                        }
                        element.innerHTML = curVal;
                    } else if (curTarget == 'textNode') {
                        if (nodeTag == "TEXTAREA") {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r");
                        }
                        textNode = document.createTextNode(curVal);
                        element.appendChild(textNode);
                    } else if (curTarget == 'script') {
                        textNode = document.createTextNode(curVal);
                        if (nodeTag == "SCRIPT") {
                            element.appendChild(textNode);
                        } else {
                            scriptNode = document.createElement("script");
                            scriptNode.type = "text/javascript";
                            scriptNode.appendChild(textNode);
                            element.appendChild(scriptNode);
                        }
                    } else if (curTarget.indexOf('style.') == 0) {
                        styleName = curTarget.substring(6, curTarget.length);
                        statement = "element.style." + styleName + "='" + curVal + "';";
                        eval(statement);
                    } else {
                        element.setAttribute(curTarget, curVal);
                    }
                }
            } else { // if the 'target' is not specified.
                if (INTERMediatorLib.isWidgetElement(element)) {
                    element._im_setValue(curVal);
                } else if (nodeTag == "INPUT") {
                    typeAttr = element.getAttribute('type');
                    if (typeAttr == 'checkbox' || typeAttr == 'radio') { // set the value
                        valueAttr = element.value;
                        curValues = curVal.split("\n");
                        if (typeAttr == 'checkbox' && curValues.length > 1) {
                            element.checked = false;
                            for (i = 0; i < curValues.length; i++) {
                                if (valueAttr == curValues[i] && !INTERMediator.dontSelectRadioCheck) {
                                    if (INTERMediator.isIE) {
                                        element.setAttribute('checked', 'checked');
                                    } else {
                                        element.checked = true;
                                    }
                                }
                            }
                        } else {
                            if (valueAttr == curVal && !INTERMediator.dontSelectRadioCheck) {
                                if (INTERMediator.isIE) {
                                    element.setAttribute('checked', 'checked');
                                } else {
                                    element.checked = true;
                                }
                            } else {
                                element.checked = false;
                            }
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
                        textNode = document.createTextNode(curVal);
                        element.appendChild(textNode);
                    }
                }
            }
            return needPostValueSet;
        }

        function setupDeleteButton(encNodeTag, repNodeTag, endOfRepeaters, currentContext, keyField, keyValue, shouldDeleteNodes) {
            // Handling Delete buttons
            var buttonNode, thisId, deleteJSFunction, tdNodes, tdNode;

            if (currentContext['repeat-control'] && currentContext['repeat-control'].match(/delete/i)) {
                if (currentContext['relation'] || currentContext['records'] === undefined || currentContext['records'] > 1) {
                    buttonNode = document.createElement('BUTTON');
                    buttonNode.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[6]));
                    thisId = 'IM_Button_' + buttonIdNum;
                    buttonNode.setAttribute('id', thisId);
                    buttonIdNum++;
                    deleteJSFunction = function (a, b, c, d, e) {
                        var contextName = a, keyField = b, keyValue = c, removeNodes = d, confirming = e;

                        return function () {
                            INTERMediator.deleteButton(
                                contextName, keyField, keyValue, removeNodes, confirming);
                        };
                    };
                    eventListenerPostAdding.push({
                        'id': thisId,
                        'event': 'click',
                        'todo': deleteJSFunction(
                            currentContext['name'],
                            keyField,
                            keyValue,
                            shouldDeleteNodes,
                            currentContext['repeat-control'].match(/confirm-delete/i))
                    });
//                    endOfRepeaters = repeatersOneRec[repeatersOneRec.length - 1];
                    switch (encNodeTag) {
                        case 'TBODY':
                            tdNodes = endOfRepeaters.getElementsByTagName('TD');
                            tdNode = tdNodes[tdNodes.length - 1];
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
                        kind: 'DELETE',
                        name: currentContext['name'],
                        key: keyField,
                        value: keyValue,
                        confirm: currentContext['repeat-control'].match(/confirm-delete/i)
                    });
                }
            }
        }

        function setupInsertButton(currentContext, encNodeTag, repNodeTag, node, relationValue) {
            var buttonNode, shouldRemove, enclosedNode, footNode, trNode, tdNode, liNode, divNode, insertJSFunction, i,
                firstLevelNodes, targetNodeTag, existingButtons;
            if (currentContext['repeat-control'] && currentContext['repeat-control'].match(/insert/i)) {
                if (relationValue || !currentContext['paging'] || currentContext['paging'] === false) {
                    buttonNode = document.createElement('BUTTON');
                    buttonNode.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[5]));
                    INTERMediatorLib.setClassAttributeToNode(buttonNode, '_im_insert_button');
                    shouldRemove = [];
                    switch (encNodeTag) {
                        case 'TBODY':
                            targetNodeTag = "TFOOT";
                            if (currentContext['repeat-control'].match(/top/i)) {
                                targetNodeTag = "THEAD";
                            }
                            enclosedNode = node.parentNode;
                            firstLevelNodes = enclosedNode.childNodes;
                            footNode = null;
                            for (i = 0; i < firstLevelNodes.length; i++) {
                                if (firstLevelNodes[i].tagName === targetNodeTag) {
                                    footNode = firstLevelNodes[i];
                                    break;
                                }
                            }
                            if (footNode == null) {
                                footNode = document.createElement(targetNodeTag);
                                enclosedNode.appendChild(footNode);
                            }
                            existingButtons = INTERMediatorLib.getElementsByClassName(footNode, '_im_insert_button');
                            if (existingButtons.length == 0) {
                                trNode = document.createElement('TR');
                                tdNode = document.createElement('TD');
                                if (trNode.getAttribute('id') == null) {
                                    trNode.setAttribute('id', nextIdValue());
                                }
                                footNode.appendChild(trNode);
                                trNode.appendChild(tdNode);
                                tdNode.appendChild(buttonNode);
                                shouldRemove = [trNode.getAttribute('id')];
                            }
                            break;
                        case 'UL':
                        case 'OL':
                            liNode = document.createElement('LI');
                            existingButtons = INTERMediatorLib.getElementsByClassName(liNode, '_im_insert_button');
                            if (existingButtons.length == 0) {
                                liNode.appendChild(buttonNode);
                                if (currentContext['repeat-control'].match(/top/i)) {
                                    node.insertBefore(liNode, node.firstChild);
                                } else {
                                    node.appendChild(liNode);
                                }
                            }
                            break;
                        case 'DIV':
                        case 'SPAN':
                            if (repNodeTag == "DIV" || repNodeTag == "SPAN") {
                                divNode = document.createElement(repNodeTag);
                                existingButtons = INTERMediatorLib.getElementsByClassName(divNode, '_im_insert_button');
                                if (existingButtons.length == 0) {
                                    divNode.appendChild(buttonNode);
                                    if (currentContext['repeat-control'].match(/top/i)) {
                                        node.insertBefore(divNode, node.firstChild);
                                    } else {
                                        node.appendChild(divNode);
                                    }
                                }
                            }
                            break;
                    }
                    insertJSFunction = function (a, b, c, d, e) {
                        var contextName = a, relationValue = b, nodeId = c, removeNodes = d, confirming = e;
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
                            currentContext['repeat-control'].match(/confirm-insert/i))
                    );
                } else {
                    deleteInsertOnNavi.push({
                        kind: 'INSERT',
                        name: currentContext['name'],
                        key: currentContext['key'] ? currentContext['key'] : 'id',
                        confirm: currentContext['repeat-control'].match(/confirm-insert/i)
                    });
                }
            }
        }

        /**
         * Create Navigation Bar to move previous/next page
         */

        function navigationSetup() {
            var navigation, i, insideNav, navLabel, node, start, pageSize, allCount, disableClass,
                prevPageCount, nextPageCount, endPageCount, onNaviInsertFunction, onNaviDeleteFunction;

            navigation = document.getElementById('IM_NAVIGATOR');
            if (navigation != null) {
                insideNav = navigation.childNodes;
                for (i = 0; i < insideNav.length; i++) {
                    navigation.removeChild(insideNav[i]);
                }
                navigation.innerHTML = '';
                navigation.setAttribute('class', 'IM_NAV_panel');
                navLabel = INTERMediator.navigationLabel;

                if (navLabel == null || navLabel[8] !== false) {
                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        ((navLabel == null || navLabel[8] == null) ? INTERMediatorOnPage.getMessages()[2] : navLabel[8])));
                    node.setAttribute('class', 'IM_NAV_button');
                    INTERMediatorLib.addEvent(node, 'click', function () {
                        location.reload();
                    });
                }

                if (navLabel == null || navLabel[4] !== false) {
                    start = Number(INTERMediator.startFrom);
                    pageSize = Number(INTERMediator.pagedSize);
                    allCount = Number(INTERMediator.pagedAllCount);
                    disableClass = " IM_NAV_disabled";
                    node = document.createElement('SPAN');
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

                if (navLabel == null || navLabel[0] !== false) {
                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        (navLabel == null || navLabel[0] == null) ? '<<' : navLabel[0]));
                    node.setAttribute('class', 'IM_NAV_button' + (start == 0 ? disableClass : ""));
                    INTERMediatorLib.addEvent(node, 'click', function () {
                        INTERMediator.startFrom = 0;
                        INTERMediator.constructMain(true);
                    });

                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        (navLabel == null || navLabel[1] == null) ? '<' : navLabel[1]));
                    node.setAttribute('class', 'IM_NAV_button' + (start == 0 ? disableClass : ""));
                    prevPageCount = (start - pageSize > 0) ? start - pageSize : 0;
                    INTERMediatorLib.addEvent(node, 'click', function () {
                        INTERMediator.startFrom = prevPageCount;
                        INTERMediator.constructMain(true);
                    });

                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        (navLabel == null || navLabel[2] == null) ? '>' : navLabel[2]));
                    node.setAttribute('class', 'IM_NAV_button' + (start + pageSize >= allCount ? disableClass : ""));
                    nextPageCount
                        = (start + pageSize < allCount) ? start + pageSize : ((allCount - pageSize > 0) ? start : 0);
                    INTERMediatorLib.addEvent(node, 'click', function () {
                        INTERMediator.startFrom = nextPageCount;
                        INTERMediator.constructMain(true);
                    });

                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        (navLabel == null || navLabel[3] == null) ? '>>' : navLabel[3]));
                    node.setAttribute('class', 'IM_NAV_button' + (start + pageSize >= allCount ? disableClass : ""));
                    endPageCount = allCount - pageSize;
                    INTERMediatorLib.addEvent(node, 'click', function () {
                        INTERMediator.startFrom = (endPageCount > 0) ? endPageCount : 0;
                        INTERMediator.constructMain(true);
                    });
                }

                for (i = 0; i < deleteInsertOnNavi.length; i++) {
                    switch (deleteInsertOnNavi[i]['kind']) {
                        case 'INSERT':
                            node = document.createElement('SPAN');
                            navigation.appendChild(node);
                            node.appendChild(
                                document.createTextNode(INTERMediatorOnPage.getMessages()[3] + ': ' + deleteInsertOnNavi[i]['name']));
                            node.setAttribute('class', 'IM_NAV_button');
                            onNaviInsertFunction = function (a, b, c) {
                                var contextName = a, keyValue = b, confirming = c;
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
                            onNaviDeleteFunction = function (a, b, c, d) {
                                var contextName = a, keyName = b, keyValue = c, confirming = d;
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
            var i, j, nodeInfo, nInfo, children, r;

            if (rootNode.nodeType == 1) {
                nodeInfo = INTERMediatorLib.getLinkedElementInfo(rootNode);
                for (j = 0; j < nodeInfo.length; j++) {
                    nInfo = INTERMediatorLib.getNodeInfoArray(nodeInfo[j]);
                    if (nInfo['table'] == tableName && nInfo['field'] == fieldName) {
                        return rootNode;
                    }
                }
            }
            children = rootNode.childNodes; // Check all child node of the enclosure.
            for (i = 0; i < children.length; i++) {
                r = getEnclosedNode(children[i], tableName, fieldName);
                if (r !== null) {
                    return r;
                }
            }
            return null;
        }

        function appendCredit() {
            var bodyNode, creditNode, cNode, spNode, aNode;

            if (document.getElementById('IM_CREDIT') == null) {
                bodyNode = document.getElementsByTagName('BODY')[0];
                creditNode = document.createElement('div');
                bodyNode.appendChild(creditNode);
                creditNode.setAttribute('id', 'IM_CREDIT');
                creditNode.setAttribute('class', 'IM_CREDIT');

                cNode = document.createElement('div');
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
                spNode = document.createElement('span');
                cNode.appendChild(spNode);
                cNode.style.color = '#666666';
                cNode.style.fontSize = '7pt';
                aNode = document.createElement('a');
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