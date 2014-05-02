/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */


//"use strict"

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
    defaultTargetInnerHTML: false,
    // For general elements, if target isn't specified, the value will be set to innerHTML.
    // Otherwise, set as the text node.
    navigationLabel: null,
    // Navigation is controlled by this parameter.
    elementIds: [],
    widgetElementIds: [],
    radioNameMode: false,
    dontSelectRadioCheck: false,
    ignoreOptimisticLocking: false,
    supressDebugMessageOnPage: false,
    supressErrorMessageOnPage: false,
    additionalFieldValueOnNewRecord: {},
    additionalFieldValueOnUpdate: {},
    additionalFieldValueOnDelete: {},
    waitSecondsAfterPostMessage: 4,
    pagedAllCount: 0,
    currentEncNumber: 0,
    isIE: false,
    isTrident: false,
    ieVersion: -1,
    titleAsLinkInfo: true,
    classAsLinkInfo: true,
    isDBDataPreferable: false,
    noRecordClassName: "_im_for_noresult_",

    rootEnclosure: null,
    // Storing to retrieve the page to initial condition.
    // {node:xxx, parent:xxx, currentRoot:xxx, currentAfter:xxxx}
    calculateRequiredObject: null,
    /*
     key => {    // Key is the id attribute of the node which is defined as "calcuration"
     "field":
     "expression": exp.replace(/ /g, ""),   // expression
     "nodeInfo": nInfo,     // node if object i.e. {field:.., table:.., target:..., tableidnex:....}
     "values": {}   // key=target name in expression, value=real value.
     // if value=undefined, it shows the value is calculation field
     "refers": {}
     }
     */
    errorMessages: [],
    debugMessages: [],
    deleteInsertOnNavi: [],

    /* These following properties moved to the setter/getter archtecture, and defined out side of this object.*/
    //startFrom: 0,
    // Start from this number of record for "skipping" records.
    //pagedSize: 0,
    // 
    //additionalCondition: {},
    // This array should be [{tableName: [{field:xxx,operator:xxx,value:xxxx}]}, ... ]
    //additionalSortKey: {},
    // This array should be [{tableName: [{field:xxx,direction:xxx}]}, ... ]

    //=================================
    // Message for Programmers
    //=================================

    setDebugMessage: function (message, level) {
        if (level === undefined) {
            level = 1;
        }
        if (INTERMediator.debugMode >= level) {
            INTERMediator.debugMessages.push(message);
            if (typeof console != 'undefined') {
                console.log("INTER-Mediator[DEBUG:%s]: %s", new Date(), message);
            }
        }
    },

    setErrorMessage: function (ex, moreMessage) {
        moreMessage = moreMessage === undefined ? "" : (" - " + moreMessage);
        if ((typeof ex == 'string' || ex instanceof String)) {
            INTERMediator.errorMessages.push(ex + moreMessage);
            if (typeof console != 'undefined') {
                console.error("INTER-Mediator[ERROR]: %s", ex + moreMessage);
            }
        } else {
            if (ex.message) {
                INTERMediator.errorMessages.push(ex.message + moreMessage);
                if (typeof console != 'undefined') {
                    console.error("INTER-Mediator[ERROR]: %s", ex.message + moreMessage);
                }
            }
            if (ex.stack && typeof console != 'undefined') {
                console.error(ex.stack);
            }
        }
    },

    flushMessage: function () {
        var debugNode, title, body, i, j, lines, clearButton, tNode, target;

        if (!INTERMediator.supressErrorMessageOnPage
            && INTERMediator.errorMessages.length > 0) {
            debugNode = document.getElementById('_im_error_panel_4873643897897');
            if (debugNode === null) {
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
            if (debugNode === null) {
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
            if (INTERMediatorOnPage.getEditorPath()) {
                var aLink = document.createElement('a');
                aLink.setAttribute('href', INTERMediatorOnPage.getEditorPath());
                aLink.appendChild(document.createTextNode('Definition File Editor'));
                debugNode.appendChild(aLink);
            }
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

    // Detect Internet Explorer and its version.
    propertyIETridentSetup: function () {
        var ua, msiePos, c, i;
        ua = navigator.userAgent;
        msiePos = ua.toLocaleUpperCase().indexOf('MSIE');
        if (msiePos >= 0) {
            INTERMediator.isIE = true;
            for (i = msiePos + 4; i < ua.length; i++) {
                c = ua.charAt(i);
                if (!(c == ' ' || c == '.' || (c >= '0' && c <= '9'))) {
                    INTERMediator.ieVersion = INTERMediatorLib.toNumber(ua.substring(msiePos + 4, i));
                    break;
                }
            }
        }
        msiePos = ua.indexOf('; Trident/');
        if (msiePos >= 0) {
            INTERMediator.isTrident = true;
            for (i = msiePos + 10; i < ua.length; i++) {
                c = ua.charAt(i);
                if (!(c == ' ' || c == '.' || (c >= '0' && c <= '9'))) {
                    INTERMediator.ieVersion = INTERMediatorLib.toNumber(ua.substring(msiePos + 10, i)) + 4;
                    break;
                }
            }
        }
    },

    /*
     =================================
     User interactions
     =================================
     */
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
        var changedObj, objType, contextInfo, i, updateRequiredContext, associatedNode, currentValue, newValue,
            linkInfo, nodeInfo;

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
        if (changedObj != null) {
            validation(changedObj);
            objType = changedObj.getAttribute('type');
            if (objType == 'radio' && !changedObj.checked) {
                INTERMediatorOnPage.hideProgress();
                return;
            }
            linkInfo = INTERMediatorLib.getLinkedElementInfo(changedObj);
            nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfo[0]);  // Suppose to be the first definition.
            contextInfo = IMLibContextPool.getContextInfoFromId(idValue, nodeInfo.target);
            newValue = IMLibElement.getValueFromIMNode(changedObj);
            if (INTERMediatorOnPage.getOptionsTransaction() == 'none') {
                // Just supporting NON-target info.
//                contextInfo.context.setValue(
//                    contextInfo.record, contextInfo.field, newValue);
                contextInfo.context.setModified(contextInfo.record, contextInfo.field, newValue);
            } else {
                INTERMediatorOnPage.showProgress();
                if (!IMLibElement.checkOptimisticLock(changedObj, nodeInfo.target)) {
                    INTERMediatorOnPage.hideProgress();
                } else {
                    IMLibContextPool.updateContext(idValue, nodeInfo.target);
                    updateDB(changedObj, idValue, nodeInfo.target);

                    updateRequiredContext = IMLibContextPool.dependingObjects(idValue);
                    for (i = 0; i < updateRequiredContext.length; i++) {
                        updateRequiredContext[i].foreignValue = {};
                        updateRequiredContext[i].foreignValue[contextInfo.field] = newValue;
                        if (updateRequiredContext[i]) {
                            INTERMediator.constructMain(updateRequiredContext[i]);
                            associatedNode = updateRequiredContext[i].enclosureNode;
                            if (INTERMediatorLib.isPopupMenu(associatedNode)) {
                                currentValue = contextInfo.context.getContextValue(associatedNode.id, "");
                                IMLibElement.setValueToIMNode(associatedNode, "", currentValue, false);
                            }
                        }
                    }
                }
                INTERMediator.recalculation(idValue);
                INTERMediator.flushMessage();
            }
        }

        function validation(changedObj) {
            var linkInfo, matched, context, i, j, index, didValidate, contextInfo, result, messageNode, errorMsgs;
            linkInfo = INTERMediatorLib.getLinkedElementInfo(changedObj);
            didValidate = false;
            if (linkInfo.length > 0) {
                matched = linkInfo[0].match(/([^@]+)/);
                if (matched[1] != IMLibLocalContext.contextName) {
                    context = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', matched[1]);
                    if (context["validation"] != null) {
                        for (i = 0; i < linkInfo.length; i++) {
                            matched = linkInfo[i].match(/([^@]+)@([^@]+)/);
                            for (index in context["validation"]) {
                                if (context["validation"][index]["field"] == matched[2]) {
                                    didValidate = true;
                                    result = Parser.evaluate(
                                        context["validation"][index]["rule"],
                                        {"value": changedObj.value, "target": changedObj});
                                    if (!result) {
                                        switch (context["validation"][index]["notify"]) {
                                            case 'inline':
                                                messageNode = document.createElement('SPAN');
                                                INTERMediatorLib.setClassAttributeToNode(messageNode, '_im_alertmessage');
                                                messageNode.appendChild(
                                                    document.createTextNode(context["validation"][index]["message"]));
                                                changedObj.parentNode.insertBefore(messageNode, changedObj.nextSibling);
                                                break;
                                            case 'end-of-sibling':
                                                messageNode = document.createElement('DIV');
                                                INTERMediatorLib.setClassAttributeToNode(messageNode, '_im_alertmessage');
                                                messageNode.appendChild(
                                                    document.createTextNode(context["validation"][index]["message"]));
                                                changedObj.parentNode.appendChild(messageNode);
                                                break;
                                            default:
                                                alert(context["validation"][index]["message"]);
                                        }
                                        contextInfo = IMLibContextPool.getContextInfoFromId(idValue, "");
                                        // Just supporting NON-target info.
                                        changedObj.value = contextInfo.context.getValue(
                                            contextInfo.record, contextInfo.field);
                                        window.setTimeout(function () {
                                            changedObj.focus();
                                        }, 0);
                                        if (INTERMediatorOnPage.doAfterValidationFailure != null) {
                                            INTERMediatorOnPage.doAfterValidationFailure(changedObj, linkInfo[i]);
                                        }
                                        return result;
                                    } else {
                                        switch (context["validation"][index]["notify"]) {
                                            case 'inline':
                                            case 'end-of-sibling':
                                                errorMsgs = INTERMediatorLib.getElementsByClassName(
                                                    changedObj.parentNode, '_im_alertmessage');
                                                for (j = 0; j < errorMsgs.length; j++) {
                                                    errorMsgs[j].parentNode.removeChild(errorMsgs[j]);
                                                }
                                                break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if (didValidate) {
                    if (INTERMediatorOnPage.doAfterValidationSucceed != null) {
                        INTERMediatorOnPage.doAfterValidationSucceed(changedObj, linkInfo[i]);
                    }
                }
            }
        }

        function updateDB(changedObj, idValue, target) {
            var newValue, contextInfo, criteria;

            INTERMediatorOnPage.retrieveAuthInfo();
            contextInfo = IMLibContextPool.getContextInfoFromId(idValue, target);   // Just supporting NON-target info.
            newValue = IMLibElement.getValueFromIMNode(changedObj);

            if (newValue != null) {
                criteria = contextInfo.record.split('=');
                try {
                    INTERMediator_DBAdapter.db_update({
                        name: contextInfo.context.contextName,
                        conditions: [
                            {field: criteria[0], operator: '=', value: criteria[1]}
                        ],
                        dataset: [
                            {field: contextInfo.field, value: newValue}
                        ]
                    });
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        if (INTERMediatorOnPage.requireAuthentication
                            && !INTERMediatorOnPage.isComplementAuthData()) {
                            INTERMediatorOnPage.authChallenge = null;
                            INTERMediatorOnPage.authHashedPassword = null;
                            INTERMediatorOnPage.authenticating(function () {
                                updateDB(changedObj, idValue);
                            });
                            return;
                        }
                    } else {
                        INTERMediator.setErrorMessage(ex, "EXCEPTION-2");
                    }
                }
            }
            INTERMediatorOnPage.hideProgress();
        }
    },


    deleteButton: function (targetName, keyField, keyValue, foreignField, foreignValue, removeNodes, isConfirm) {
        var key, removeNode, removingNodes;
        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1025])) {
                return;
            }
        }
        INTERMediatorOnPage.showProgress();
        try {
            INTERMediatorOnPage.retrieveAuthInfo();
            if (foreignField != "") {
                INTERMediator_DBAdapter.db_update({
                    name: targetName,
                    conditions: [
                        {field: keyField, operator: "=", value: keyValue}
                    ],
                    dataset: [
                        {
                            field: "-delete.related",
                            operator: "=",
                            value: foreignField.replace("\:\:-recid", "") + "." + foreignValue
                        }
                    ]
                });
            } else {
                INTERMediator_DBAdapter.db_delete({
                    name: targetName,
                    conditions: [
                        {field: keyField, operator: '=', value: keyValue}
                    ]
                });
            }
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                if (INTERMediatorOnPage.requireAuthentication && !INTERMediatorOnPage.isComplementAuthData()) {
                    INTERMediatorOnPage.authChallenge = null;
                    INTERMediatorOnPage.authHashedPassword = null;
                    INTERMediatorOnPage.authenticating(
                        function () {
                            INTERMediator.deleteButton(
                                targetName, keyField, keyValue, foreignField, foreignValue, removeNodes, false);
                        }
                    );
                    return;
                }
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-3");
            }
        }

        var i, j, k, removeNodeId, nodeId, calcObject, referes, values;
        for (key in removeNodes) {
            removeNode = document.getElementById(removeNodes[key]);
            removingNodes = INTERMediatorLib.getElementsByIMManaged(removeNode);
            if (removingNodes) {
                for (i = 0; i < removingNodes.length; i++) {
                    removeNodeId = removingNodes[i].id;
                    if (removeNodeId in INTERMediator.calculateRequiredObject) {
                        delete INTERMediator.calculateRequiredObject[removeNodeId];
                    }
                }
                for (i = 0; i < removingNodes.length; i++) {
                    removeNodeId = removingNodes[i].id;
                    for (nodeId in INTERMediator.calculateRequiredObject) {
                        calcObject = INTERMediator.calculateRequiredObject[nodeId];
                        referes = {};
                        values = {};
                        for (j in calcObject.referes) {
                            referes[j] = [], values[j] = [];
                            for (k = 0; k < calcObject.referes[j].length; k++) {
                                if (removeNodeId != calcObject.referes[j][k]) {
                                    referes[j].push(calcObject.referes[j][k]);
                                    values[j].push(calcObject.values[j][k]);
                                }
                            }
                        }
                        calcObject.referes = referes;
                        calcObject.values = values;
                    }
                }
            }
            try {
                removeNode.parentNode.removeChild(removeNode);
            } catch
                (ex) {
                // Avoid an error for Safari
            }
        }
        INTERMediator.recalculation();
        INTERMediatorOnPage.hideProgress();
        INTERMediator.flushMessage();
    },

    insertButton: function (targetName, keyValue, foreignValues, updateNodes, removeNodes, isConfirm) {
        var currentContext, recordSet, index, key, removeNode, i, relationDef, targetRecord, portalField,
            targetPortalField, targetPortalValue, existRelated = false, relatedRecordSet;
        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1026])) {
                return;
            }
        }
        INTERMediatorOnPage.showProgress();
        currentContext = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', targetName);
        recordSet = [], relatedRecordSet = [];
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

            relationDef = currentContext["relation"];
            if (relationDef) {
                for (index in relationDef) {
                    if (relationDef[index]["portal"] == true) {
                        currentContext["portal"] = true;
                    }
                }
            }
            if (currentContext["portal"] == true) {
                relatedRecordSet = [];
                for (index in currentContext["default-values"]) {
                    relatedRecordSet.push({
                        field: targetName + "::" + currentContext["default-values"][index]["field"] + ".0",
                        value: currentContext["default-values"][index]["value"]
                    });
                }

                if (relatedRecordSet.length == 0) {
                    targetPortalValue = "";

                    targetRecord = INTERMediator_DBAdapter.db_query({
                            name: targetName,
                            records: 1,
                            conditions: [
                                {
                                    field: currentContext["key"] ? currentContext["key"] : "-recid",
                                    operator: "=",
                                    value: keyValue}
                            ]}
                    );
                    for (portalField in targetRecord["recordset"][0][0]) {
                        if (portalField.indexOf(targetName + "::") > -1) {
                            existRelated = true;
                            targetPortalField = portalField;
                            if (portalField == targetName + "::" + recordSet[0]['field']) {
                                targetPortalValue = recordSet[0]['value'];
                                break;
                            }
                            if (portalField != targetName + "::id"
                                && portalField != targetName + "::" + recordSet[0]['field']) {
                                break;
                            }
                        }
                    }

                    if (existRelated == false) {
                        targetRecord = INTERMediator_DBAdapter.db_query({
                                name: targetName,
                                records: 0,
                                conditions: [
                                    {
                                        field: currentContext["key"] ? currentContext["key"] : "-recid",
                                        operator: "=",
                                        value: keyValue}
                                ]}
                        );
                        for (portalField in targetRecord["recordset"]) {
                            if (portalField.indexOf(targetName + "::") > -1) {
                                targetPortalField = portalField;
                                if (portalField == targetName + "::" + recordSet[0]['field']) {
                                    targetPortalValue = recordSet[0]['value'];
                                    break;
                                }
                                if (portalField != targetName + "::id"
                                    && portalField != targetName + "::" + recordSet[0]['field']) {
                                    break;
                                }
                            }
                        }
                    }
                    relatedRecordSet.push({field: targetPortalField + ".0", value: targetPortalValue});
                }

                INTERMediator_DBAdapter.db_update({
                    name: targetName,
                    conditions: [
                        {
                            field: currentContext["key"] ? currentContext["key"] : "-recid",
                            operator: "=",
                            value: keyValue}
                    ],
                    dataset: relatedRecordSet
                });
            } else {
                INTERMediator_DBAdapter.db_createRecord({name: targetName, dataset: recordSet});
            }
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                INTERMediatorOnPage.authChallenge = null;
                INTERMediatorOnPage.authHashedPassword = null;
                INTERMediatorOnPage.authenticating(
                    function () {
                        INTERMediator.insertButton(
                            targetName, keyValue, foreignValues, updateNodes, removeNodes, false);
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
            try {
                removeNode.parentNode.removeChild(removeNode);
            } catch (ex) {
                // Avoid an error for Safari
            }
        }

        var associatedContext = IMLibContextPool.contextFromEnclosureId(updateNodes);
        if (associatedContext) {
            associatedContext.foreignValue = foreignValues;
            if (currentContext["portal"] == true && existRelated == false) {
                INTERMediator.additionalCondition[targetName] = {
                    field: currentContext["key"] ? currentContext["key"] : "-recid",
                    operator: "=",
                    value: keyValue
                };
            }
            INTERMediator.constructMain(associatedContext);
        }

        INTERMediator.recalculation();
        INTERMediatorOnPage.hideProgress();
        INTERMediator.flushMessage();
    },

    insertRecordFromNavi: function (targetName, keyField, isConfirm) {
        var newId, restore, fieldObj, contextDef;

        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1026])) {
                return;
            }
        }
        INTERMediatorOnPage.showProgress();
        contextDef = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), "name", targetName);
        if (contextDef === null) {
            alert("no targetname :" + targetName);
            INTERMediatorOnPage.hideProgress();
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
            if (contextDef.records <= 1) {
                INTERMediator.additionalCondition[targetName] = {field: keyField, value: newId};
                IMLibLocalContext.archive();
            }
            INTERMediator.constructMain(true);
            INTERMediator.additionalCondition = restore;
        }
        INTERMediator.recalculation();
        INTERMediatorOnPage.hideProgress();
        INTERMediator.flushMessage();
    },

    deleteRecordFromNavi: function (targetName, keyField, keyValue, isConfirm) {
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

    saveRecordFromNavi: function (dontUpdate) {
        var contextName, keying, field, keyingComp, keyingField, keyingValue, checkQueryParameter, i,initialValue,
            currentVal, fieldArray, valueArray, diffrence, needUpdate = true, context, updateData;

        INTERMediatorOnPage.showProgress();
        INTERMediatorOnPage.retrieveAuthInfo();
        for (i = 0 ; i < IMLibContextPool.poolingContexts.length ; i++) {
            context  = IMLibContextPool.poolingContexts[i];
            updateData = context.getModified();
            for (keying in updateData) {
                fieldArray = [];
                valueArray = [];
                for (field in updateData[keying]) {
                    fieldArray.push(field);
                    valueArray.push({field: field, value: updateData[keying][field]});
                }
                if (!INTERMediator.ignoreOptimisticLocking) {
                    keyingComp = keying.split('=');
                    keyingField = keyingComp[0];
                    keyingComp.shift();
                    keyingValue = keyingComp.join('=');
                    checkQueryParameter = {
                        name: context.contextName,
                        records: 1,
                        paging: false,
                        fields: fieldArray,
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
                            if (INTERMediatorOnPage.requireAuthentication
                                && !INTERMediatorOnPage.isComplementAuthData()) {
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
                            INTERMediator.setErrorMessage(ex, "EXCEPTION-28");
                        }
                    }

                    if (currentVal.recordset == null
                        || currentVal.recordset[0] === null) {
                        alert(INTERMediatorLib.getInsertedString(
                            INTERMediatorOnPage.getMessages()[1003], [fieldArray.join(',')]));
                        return;
                    }
                    if (currentVal.count > 1) {
                        response = confirm(INTERMediatorOnPage.getMessages()[1024]);
                        if (!response) {
                            return;
                        }
                    }

                    diffrence = false;
                    for (field in updateData[keying]) {
                        initialValue = context.getValue(keying, field);
                        if (initialValue != currentVal.recordset[0][field]) {
                            diffrence += INTERMediatorLib.getInsertedString(
                                INTERMediatorOnPage.getMessages()[1035], [
                                    field,
                                    currentVal.recordset[0][field],
                                    updateData[keying][field]
                                ]);
                        }
                    }
                    if (diffrence !== false) {
                        if (!confirm(INTERMediatorLib.getInsertedString(
                            INTERMediatorOnPage.getMessages()[1034], [diffrence]))) {
                            return;
                        }
                        INTERMediatorOnPage.retrieveAuthInfo(); // This is required. Why?
                    }
                }

                try {
                    INTERMediator_DBAdapter.db_update({
                        name: context.contextName,
                        conditions: [
                            {field: keyingField, operator: '=', value: keyingValue}
                        ],
                        dataset: valueArray
                    });

                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        if (INTERMediatorOnPage.requireAuthentication
                            && !INTERMediatorOnPage.isComplementAuthData()) {
                            INTERMediatorOnPage.authChallenge = null;
                            INTERMediatorOnPage.authHashedPassword = null;
                            INTERMediatorOnPage.authenticating(
                                function () {
                                    INTERMediator.deleteRecordFromNavi(targetName, keyField, keyValue, isConfirm);
                                }
                            );
                            return;
                        }
                    } else {
                        INTERMediator.setErrorMessage(ex, "EXCEPTION-29");
                    }
                }
                context.clearModified();
            }
        }
        if (needUpdate && (dontUpdate !== true)) {
            INTERMediator.constructMain(true);
        }
        INTERMediatorOnPage.hideProgress();
        INTERMediator.flushMessage();
    },

    partialConstructing: false,
    objectReference: {
    },

    linkedElmCounter: 0,

    clickPostOnlyButton: function (node) {
        var i, j, fieldData, elementInfo, comp, contextCount, selectedContext, contextInfo, validationInfo;
        var mergedValues, inputNodes, typeAttr, k, messageNode, result, alertmessage;
        var linkedNodes, namedNodes, index, hasInvalid;
        var targetNode = node.parentNode;
        while (!INTERMediatorLib.isEnclosure(targetNode, true)) {
            targetNode = targetNode.parentNode;
            if (!targetNode) {
                return;
            }
        }
        linkedNodes = []; // Collecting linked elements to this array.
        namedNodes = [];
        for (i = 0; i < targetNode.childNodes.length; i++) {
            seekLinkedElement(targetNode.childNodes[i]);
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
                contextInfo = INTERMediatorOnPage.getContextInfo(contextName);
            }
        }

        alertmessage = '';
        fieldData = [];
        hasInvalid = false;
        for (i = 0; i < linkedNodes.length; i++) {
            elementInfo = INTERMediatorLib.getLinkedElementInfo(linkedNodes[i]);
            for (j = 0; j < elementInfo.length; j++) {
                comp = elementInfo[j].split(INTERMediator.separator);
                if (comp[0] == selectedContext) {
                    if (contextInfo.validation) {
                        for (index in contextInfo.validation) {
                            validationInfo = contextInfo.validation[index];
                            if (validationInfo.field == comp[1]) {
                                if (validationInfo) {
                                    result = Parser.evaluate(
                                        validationInfo.rule,
                                        {"value": linkedNodes[i].value, "target": linkedNodes[i]}
                                    );
                                    if (!result) {
                                        hasInvalid = true;
                                        switch (validationInfo.notify) {
                                            case 'inline':
                                                clearErrorMessage(linkedNodes[i]);
                                                messageNode = createErrorMessageNode('SPAN', validationInfo.message);
                                                linkedNodes[i].parentNode.insertBefore(messageNode, linkedNodes[i].nextSibling);
                                                break;
                                            case 'end-of-sibling':
                                                clearErrorMessage(linkedNodes[i]);
                                                messageNode = createErrorMessageNode('DIV', validationInfo.message);
                                                linkedNodes[i].parentNode.appendChild(messageNode);
                                                break;
                                            default:
                                                alertmessage += validationInfo.message + "\n";
                                        }
                                        if (INTERMediatorOnPage.doAfterValidationFailure != null) {
                                            INTERMediatorOnPage.doAfterValidationFailure(linkedNodes[i], linkInfo[i]);
                                        }
                                    } else {
                                        switch (validationInfo.notify) {
                                            case 'inline':
                                            case 'end-of-sibling':
                                                clearErrorMessage(linkedNodes[i]);
                                                break;
                                        }
                                    }
                                }
                            }
                        }
                    }
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

        if (alertmessage.length > 0) {
            window.alert(alertmessage);
            return;
        }
        if (hasInvalid) {
            return;
        }

        if (INTERMediatorOnPage.processingBeforePostOnlyContext) {
            if (!INTERMediatorOnPage.processingBeforePostOnlyContext(targetNode)) {
                return;
            }
        }

        contextInfo = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', selectedContext);
        INTERMediator_DBAdapter.db_createRecordWithAuth(
            {name: selectedContext, dataset: fieldData},
            function (returnValue) {
                var newNode, parentOfTarget, targetNode = node, thisContext = contextInfo, isSetMsg = false;
                INTERMediator.flushMessage();
                if (INTERMediatorOnPage.processingAfterPostOnlyContext) {
                    INTERMediatorOnPage.processingAfterPostOnlyContext(targetNode, returnValue);
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

        function createErrorMessageNode(tag, message) {
            var messageNode;
            messageNode = document.createElement(tag);
            INTERMediatorLib.setClassAttributeToNode(messageNode, '_im_alertmessage');
            messageNode.appendChild(document.createTextNode(message));
            return messageNode;
        }

        function clearErrorMessage(node) {
            var errorMsgs, j;
            errorMsgs = INTERMediatorLib.getElementsByClassName(node.parentNode, '_im_alertmessage');
            for (j = 0; j < errorMsgs.length; j++) {
                errorMsgs[j].parentNode.removeChild(errorMsgs[j]);
            }
        }
    },

    /*
     On updating, the updatedNodeId should be set to the updating node id.
     On deleting, parameter doesn't required.
     */
    recalculation: function (updatedNodeId) {
        var nodeId, newValueAdded, leafNodes, calcObject, ix, calcFieldInfo, updatedValue, isRecalcAll = false;
        var targetNode, newValue, field, i, updatedNodeIds, updateNodeValues, cachedIndex;

        if (updatedNodeId === undefined) {
            isRecalcAll = true;
            updatedNodeIds = [];
            updateNodeValues = [];
        } else {
            newValue = IMLibElement.getValueFromIMNode(document.getElementById(updatedNodeId));
            updatedNodeIds = [updatedNodeId];
            updateNodeValues = [newValue];
        }

        IMLibNodeGraph.clear();
        for (nodeId in INTERMediator.calculateRequiredObject) {
            calcObject = INTERMediator.calculateRequiredObject[nodeId];
            calcFieldInfo = INTERMediatorLib.getCalcNodeInfoArray(nodeId);
            targetNode = document.getElementById(calcFieldInfo.field);
            for (field in calcObject.referes) {
                for (ix = 0; ix < calcObject.referes[field].length; ix++) {
                    IMLibNodeGraph.addEdge(nodeId, calcObject.referes[field][ix]);
                }
            }
        }
        do {
            leafNodes = IMLibNodeGraph.getLeafNodesWithRemoving();
            for (i = 0; i < leafNodes.length; i++) {
                calcObject = INTERMediator.calculateRequiredObject[leafNodes[i]];
                calcFieldInfo = INTERMediatorLib.getCalcNodeInfoArray(leafNodes[i]);
                if (calcObject) {
                    if (isRecalcAll) {
                        newValueAdded = true;
                    } else {
                        newValueAdded = false;
                        for (field in calcObject.referes) {
                            for (ix = 0; ix < calcObject.referes[field].length; ix++) {
                                cachedIndex = updatedNodeIds.indexOf(calcObject.referes[field][ix]);
                                if (cachedIndex >= 0) {
                                    calcObject.values[field][ix] = updateNodeValues[cachedIndex];
                                    newValueAdded = true;
                                }
                            }
                        }
                    }
                    if (newValueAdded) {
                        updatedValue = Parser.evaluate(
                            calcObject.expression,
                            calcObject.values
                        );
                        IMLibElement.setValueToIMNode(
                            document.getElementById(calcFieldInfo.field),
                            calcFieldInfo.target,
                            updatedValue,
                            true);
                        updatedNodeIds.push(calcFieldInfo.field);
                        updateNodeValues.push(updatedValue);
                    }
                }
                else {

                }
            }
        } while (leafNodes.length > 0);
        if (IMLibNodeGraph.nodes.length > 0) {
            // Spanning Tree Detected.
        }

    },

    /**
     * //=================================
     * // Construct Page
     * //=================================

     * Construct the Web Page with DB Data
     * You should call here when you show the page.
     *
     * parameter: true=construct page, others=construct partially
     */
    construct: function (indexOfKeyFieldObject) {
        var timerTask;
        INTERMediatorOnPage.showProgress();
        if (indexOfKeyFieldObject === true || indexOfKeyFieldObject === undefined) {
            timerTask = function () {
                INTERMediator.constructMain(true)
            };
        } else {
            timerTask = function () {
                INTERMediator.constructMain(indexOfKeyFieldObject)
            };
        }
        setTimeout(timerTask, 0);
    },


    constructMain: function (updateRequiredContext) {
        var i, theNode, currentLevel = 0, postSetFields = [], buttonIdNum = 1,
            eventListenerPostAdding = [], isInsidePostOnly, nameAttrCounter = 1;
        INTERMediator.deleteInsertOnNavi = [];
        INTERMediatorOnPage.retrieveAuthInfo();
        try {
            if (updateRequiredContext === true || updateRequiredContext === undefined) {
                this.partialConstructing = false;
                IMLibContextPool.clearAll();
                pageConstruct();
            } else {
                this.partialConstructing = true;
                partialConstruct(updateRequiredContext);
            }
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                if (INTERMediatorOnPage.requireAuthentication) {
                    if (!INTERMediatorOnPage.isComplementAuthData()) {
                        INTERMediatorOnPage.authChallenge = null;
                        INTERMediatorOnPage.authHashedPassword = null;
                        INTERMediatorOnPage.authenticating(
                            function () {
                                INTERMediator.constructMain(updateRequiredContext);
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

        /*

         */

        function partialConstruct(updateRequiredContext) {
            var updateNode, originalNodes, i;

            isInsidePostOnly = false;

            updateNode = updateRequiredContext.enclosureNode;
            while (updateNode.firstChild) {
                updateNode.removeChild(updateNode.firstChild);
            }
            originalNodes = updateRequiredContext.original;
            for (i = 0; i < originalNodes.length; i++) {
                updateNode.appendChild(originalNodes[i]);
            }
            postSetFields = [];
            try {
                seekEnclosureNode(
                    updateNode,
                    updateRequiredContext.foreignValue,
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
            updateCalculationFields();
        }

        function pageConstruct() {
            var i, bodyNode, currentNode, currentID, enclosure, targetNode, emptyElement;

//            INTERMediator.keyFieldObject = [];
            // INTERMediator.updateRequiredObject = {};
            INTERMediator.calculateRequiredObject = {};
            INTERMediator.currentEncNumber = 1;
            INTERMediator.elementIds = [];
            INTERMediator.widgetElementIds = [];
            isInsidePostOnly = false;

            IMLibLocalContext.unarchive();
            // Restoring original HTML Document from backup data.
            bodyNode = document.getElementsByTagName('BODY')[0];
            if (INTERMediator.rootEnclosure === null) {
                INTERMediator.rootEnclosure = bodyNode.innerHTML;
            } else {
                bodyNode.innerHTML = INTERMediator.rootEnclosure;
            }
            postSetFields = [];

            try {
                seekEnclosureNode(bodyNode, null, null, null);
            } catch (ex) {
                if (ex == "_im_requath_request_") {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex, "EXCEPTION-9");
                }
            }


            // After work to set up popup menus.
            for (i = 0; i < postSetFields.length; i++) {
                if (postSetFields[i]['value'] == ""
                    && document.getElementById(postSetFields[i]['id']).tagName == "SELECT") {
                    // for compatibility with Firefox when the value of select tag is empty.
                    emptyElement = document.createElement('option');
                    emptyElement.setAttribute("value", "");
                    document.getElementById(postSetFields[i]['id']).insertBefore(
                        emptyElement, document.getElementById(postSetFields[i]['id']).firstChild);
                }
                document.getElementById(postSetFields[i]['id']).value = postSetFields[i]['value'];
            }
            IMLibLocalContext.bindingDescendant(bodyNode);
            updateCalculationFields();
            INTERMediator.navigationSetup();
            appendCredit();
        }

        /**
         * Seeking nodes and if a node is an enclosure, proceed repeating.
         */

        function seekEnclosureNode(node, currentRecord, parentEnclosure, objectReference) {
            var children, className, i, attr;
            if (node.nodeType === 1) { // Work for an element
                try {
                    if (INTERMediatorLib.isEnclosure(node, false)) { // Linked element and an enclosure
                        className = INTERMediatorLib.getClassAttributeFromNode(node);
                        attr = node.getAttribute("data-im-control");
                        if ((className && className.match(/_im_post/))
                            || (attr && attr == "post")) {
                            setupPostOnlyEnclosure(node);
                        } else {
                            if (INTERMediator.isIE) {
                                try {
                                    expandEnclosure(node, currentRecord, parentEnclosure, objectReference);
                                } catch (ex) {
                                    if (ex == "_im_requath_request_") {
                                        throw ex;
                                    }
                                }
                            } else {
                                expandEnclosure(node, currentRecord, parentEnclosure, objectReference);
                            }
                        }
                    } else {
                        children = node.childNodes; // Check all child nodes.
                        if (children) {
                            for (i = 0; i < children.length; i++) {
                                if (children[i].nodeType === 1) {
                                    seekEnclosureNode(children[i], currentRecord, parentEnclosure, objectReference);
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
            var postNodes = INTERMediatorLib.getElementsByClassNameOrDataAttr(node, '_im_post');
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
                            expandEnclosure(node, null, null, null);
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

        function expandEnclosure(node, currentRecord, parentEnclosure, parentObjectInfo) {
            var objectReference = {}, linkedNodes, encNodeTag, repeatersOriginal, repeaters,
                linkDefs, voteResult, currentContext, fieldList, repNodeTag, relationValue, dependObject,
                relationDef, index, fieldName, i, j, k, ix, targetRecords, newNode,
                nodeClass, repeatersOneRec, currentLinkedNodes, shouldDeleteNodes, keyField, keyValue,
                nodeTag, typeAttr, linkInfoArray, RecordCounter, valueChangeFunction, nInfo, curVal,
                curTarget, newlyAddedNodes, keyingValue, pagingValue, widgetSupport,
                recordsValue, currentWidgetNodes, widgetSupport, nodeId, nameAttr, nameNumber, nameTable,
                selectedNode, foreignField, foreignValue, foreignFieldValue, dbspec, setupWidget,
                nameTableKey, replacedNode, children, dataAttr, calcDef, calcFields, contextObj;

            currentLevel++;
            INTERMediator.currentEncNumber++;

            widgetSupport = {};

            if (!node.getAttribute('id')) {
                node.setAttribute('id', nextIdValue());
            }

            encNodeTag = node.tagName;
            repNodeTag = INTERMediatorLib.repeaterTagFromEncTag(encNodeTag);
            repeatersOriginal = collectRepeatersOriginal(node, repNodeTag); // Collecting repeaters to this array.
            repeaters = collectRepeaters(repeatersOriginal);  // Collecting repeaters to this array.
            linkedNodes = collectLinkedElement(repeaters).linkedNode;
            linkDefs = collectLinkDefinitions(linkedNodes);
            voteResult = tableVoting(linkDefs);
            currentContext = voteResult.targettable;

            if (currentContext) {
                contextObj = new IMLibContext(currentContext['name']);
                contextObj.enclosureNode = node;
                contextObj.repeaterNodes = repeaters;

                setupWidget = false;
                fieldList = []; // Create field list for database fetch.
                calcDef = currentContext['calculation'];
                calcFields = [];
                for (ix in calcDef) {
                    calcFields.push(calcDef[ix]["field"]);
                }
                for (i = 0; i < voteResult.fieldlist.length; i++) {
                    if (!calcFields[voteResult.fieldlist[i]]) {
                        calcFields.push(voteResult.fieldlist[i]);
                    }
                }

                if (currentRecord) {
                    try {
                        relationValue = null;
                        dependObject = [];
                        relationDef = currentContext['relation'];
                        if (relationDef) {
                            relationValue = {};
                            for (index in relationDef) {
                                relationValue[ relationDef[index]['join-field'] ]
                                    = currentRecord[relationDef[index]['join-field']];
                                if (relationDef[index]['portal'] == true) {
                                    currentContext['portal'] = true;
                                }
                                for (fieldName in parentObjectInfo) {
                                    if (fieldName == relationDef[index]['join-field']) {
                                        dependObject.push(parentObjectInfo[fieldName]);
                                    }
                                }
                            }
                        }

                        contextObj.foreignValue = relationValue;
                        contextObj.dependingObject = dependObject;
                        contextObj.original = [];
                        for (i = 0; i < repeatersOriginal.length; i++) {
                            contextObj.original.push(repeatersOriginal[i].cloneNode(true));
                        }

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
                }

                targetRecords = retrieveDataForEnclosure(currentContext, fieldList, relationValue);

                if (targetRecords.count == 0) {
                    for (i = 0; i < repeaters.length; i++) {
                        newNode = repeaters[i].cloneNode(true);
                        nodeClass = INTERMediatorLib.getClassAttributeFromNode(newNode);
                        dataAttr = newNode.getAttribute("data-im-control");
                        if (nodeClass == INTERMediator.noRecordClassName || dataAttr == "noresult") {
                            node.appendChild(newNode);
                            setIdValue(newNode);
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
                        dbspec = INTERMediatorOnPage.getDBSpecification();
                        if (dbspec["db-class"] != null && dbspec["db-class"] == "FileMaker_FX") {
                            keyField = currentContext["key"] ? currentContext["key"] : "-recid";
                        } else {
                            keyField = currentContext["key"] ? currentContext["key"] : "id";
                        }
                        if (currentContext['portal'] == true) {
                            keyField = "-recid";
                            foreignField = currentContext['name'] + "::-recid";
                            foreignValue = targetRecords.recordset[ix][foreignField];
                            foreignFieldValue = foreignField + "=" + foreignValue;
                        } else {
                            foreignFieldValue = "=";
                        }
                        keyValue = targetRecords.recordset[ix][keyField];
                        keyingValue = keyField + "=" + keyValue;

                        for (k = 0; k < currentLinkedNodes.length; k++) {
                            // for each linked element
                            nodeId = currentLinkedNodes[k].getAttribute("id");
                            replacedNode = setIdValue(currentLinkedNodes[k]);

                            if (targetRecords.recordset.length > 1) {
                                if (replacedNode.getAttribute("type") == "checkbox") {
                                    children = replacedNode.parentNode.childNodes;
                                    for (i = 0; i < children.length; i++) {
                                        if (children[i].nodeType === 1 && children[i].tagName == "LABEL"
                                            && nodeId == children[i].getAttribute("for")) {
                                            children[i].setAttribute("for", replacedNode.getAttribute("id"));
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        for (k = 0; k < currentWidgetNodes.length; k++) {
                            var wInfo = INTERMediatorLib.getWidgetInfo(currentWidgetNodes[k]);
                            if (wInfo[0]) {
                                setupWidget = true;
                                IMParts_Catalog[wInfo[0]].instanciate.apply(
                                    IMParts_Catalog[wInfo[0]], [currentWidgetNodes[k]]);
                            }
                        }
                    } catch (ex) {
                        if (ex == "_im_requath_request_") {
                            throw ex;
                        } else {
                            INTERMediator.setErrorMessage(ex, "EXCEPTION-26");
                        }
                    }

//                        repeaterCalcItems = [];
                    if (currentContext['portal'] != true
                        || (currentContext['portal'] == true && targetRecords["totalCount"] > 0)) {
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
                                            var result = INTERMediator.valueChange(theId);
                                            if (!result) {
                                                evt.preventDefault();
                                            }
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
                                    if (!INTERMediator.isDBDataPreferable || curVal != null) {
                                        updateCalcurationInfo(currentContext, nodeId, nInfo, targetRecords.recordset[ix]);
                                    }
                                    curTarget = nInfo['target'];
                                    objectReference[nInfo['field']] = nodeId;

                                    // Set data to the element.
                                    if (curVal === null) {
                                        if (IMLibElement.setValueToIMNode(currentLinkedNodes[k], curTarget, '')) {
                                            postSetFields.push({'id': nodeId, 'value': curVal});
                                        }
                                    } else if ((typeof curVal == 'object' || curVal instanceof Object)) {
                                        if (curVal.length > 0) {
                                            if (IMLibElement.setValueToIMNode(
                                                currentLinkedNodes[k],
                                                curTarget,
                                                curVal[0])) {
                                                postSetFields.push({'id': nodeId, 'value': curVal[0]});
                                            }
                                        }
                                    } else {
                                        if (IMLibElement.setValueToIMNode(currentLinkedNodes[k], curTarget, curVal)) {
                                            postSetFields.push({'id': nodeId, 'value': curVal});
                                        }
                                    }
                                }
                                contextObj.setValue(keyingValue, nInfo['field'], curVal, nodeId, curTarget);

                            } catch (ex) {
                                if (ex == "_im_requath_request_") {
                                    throw ex;
                                } else {
                                    INTERMediator.setErrorMessage(ex, "EXCEPTION-27");
                                }
                            }
                        }
                    }

                    if (currentContext['portal'] == true) {
                        keyField = "-recid";
                        foreignField = currentContext['name'] + "::-recid";
                        foreignValue = targetRecords.recordset[ix][foreignField];
                        foreignFieldValue = foreignField + "=" + foreignValue;
                    } else {
                        foreignField = "";
                        foreignValue = "";
                        foreignFieldValue = "=";
                    }

                    setupDeleteButton(encNodeTag, repNodeTag, repeatersOneRec[repeatersOneRec.length - 1],
                        currentContext, keyField, keyValue, foreignField, foreignValue, shouldDeleteNodes);

                    if (currentContext['portal'] != true
                        || (currentContext['portal'] == true && targetRecords["totalCount"] > 0)) {
                        newlyAddedNodes = [];
                        for (i = 0; i < repeatersOneRec.length; i++) {
                            newNode = repeatersOneRec[i].cloneNode(true);
                            nodeClass = INTERMediatorLib.getClassAttributeFromNode(newNode);
                            dataAttr = newNode.getAttribute("data-im-control");
                            if ((nodeClass != INTERMediator.noRecordClassName) && (dataAttr != "noresult")) {
                                node.appendChild(newNode);
                                newlyAddedNodes.push(newNode);
                                setIdValue(newNode);
                                seekEnclosureNode(newNode, targetRecords.recordset[ix], node, objectReference);
                            }
                        }

                        try {
                            if (INTERMediatorOnPage.additionalExpandingRecordFinish[currentContext['name']]) {
                                INTERMediatorOnPage.additionalExpandingRecordFinish[currentContext['name']](node);
                                INTERMediator.setDebugMessage(
                                    "Call the post enclosure method 'INTERMediatorOnPage.additionalExpandingRecordFinish["
                                        + currentContext['name'] + "] with the context.", 2);
                            }
                        } catch (ex) {
                            if (ex == "_im_requath_request_") {
                                throw ex;
                            } else {
                                INTERMediator.setErrorMessage(ex,
                                    "EXCEPTION-33: hint: post-repeater of " + currentContext.name);
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
                                INTERMediatorOnPage[currentContext['post-repeater']](newlyAddedNodes);

                                INTERMediator.setDebugMessage("Call the post repeater method 'INTERMediatorOnPage."
                                    + currentContext['post-repeater'] + "' with the context: "
                                    + currentContext['name'], 2);
                            }
                        } catch (ex) {
                            if (ex == "_im_requath_request_") {
                                throw ex;
                            } else {
                                INTERMediator.setErrorMessage(ex, "EXCEPTION-23");
                            }
                        }
                    }

                }
                setupInsertButton(currentContext, keyValue, encNodeTag, repNodeTag, node, relationValue);

                if (setupWidget) {
                    for (var plugin in IMParts_Catalog) {
                        IMParts_Catalog[plugin].finish();
                    }
                }
                try {
                    if (INTERMediatorOnPage.additionalExpandingEnclosureFinish[currentContext['name']]) {
                        INTERMediatorOnPage.additionalExpandingEnclosureFinish[currentContext['name']](node);
                        INTERMediator.setDebugMessage(
                            "Call the post enclosure method 'INTERMediatorOnPage.additionalExpandingEnclosureFinish["
                                + currentContext['name'] + "] with the context.", 2);
                    }
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex,
                            "EXCEPTION-32: hint: post-enclosure of " + currentContext.name);
                    }
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
                        INTERMediatorOnPage[currentContext['post-enclosure']](node);
                        INTERMediator.setDebugMessage(
                            "Call the post enclosure method 'INTERMediatorOnPage." + currentContext['post-enclosure']
                                + "' with the context: " + currentContext['name'], 2);
                    }
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex,
                            "EXCEPTION-22: hint: post-enclosure of " + currentContext.name);
                    }
                }

            } else {
                repeaters = [];
                for (i = 0; i < repeatersOriginal.length; i++) {
                    newNode = node.appendChild(repeatersOriginal[i]);

                    // for compatibility with Firefox
                    if (repeatersOriginal[i].getAttribute("selected") != null) {
                        selectedNode = newNode;
                    }
                    if (selectedNode !== undefined) {
                        selectedNode.selected = true;
                    }

                    seekEnclosureNode(newNode, null, node, null);
                }
            }
            currentLevel--;
        }

        function updateCalcurationInfo(currentContext, nodeId, nInfo, currentRecord) {
            var calcDef, exp, field, elements, i, index, objectKey, calcFieldInfo, itemIndex, values, referes;

            calcDef = currentContext['calculation'];
            field = null;
            exp = null;
            for (index in calcDef) {
                if (calcDef[index]["field"].indexOf(nInfo["field"]) == 0) {
                    try {
                        exp = calcDef[index]["expression"];
                        field = calcDef[index]["field"];
                        elements = Parser.parse(exp).variables();
                        calcFieldInfo = INTERMediatorLib.getCalcNodeInfoArray(field);
                        objectKey = nodeId
                            + (calcFieldInfo.target.length > 0 ? (INTERMediator.separator + calcFieldInfo.target) : "");
                    } catch (ex) {
                        INTERMediator.setErrorMessage(ex,
                            INTERMediatorLib.getInsertedString(
                                INTERMediatorOnPage.getMessages()[1036], [field, exp]));
                    }
                    if (elements) {
                        values = {};
                        referes = {};
                        for (i = 0; i < elements.length; i++) {
                            itemIndex = elements[i];
                            if (itemIndex) {
                                values[itemIndex] = [currentRecord[itemIndex]];
                                referes[itemIndex] = [undefined];
                            }
                        }
                        INTERMediator.calculateRequiredObject[objectKey] = {
                            "field": field,
                            "expression": exp,
                            "nodeInfo": nInfo,
                            "values": values,
                            "referes": referes
                        };
                    }
                }
            }
//                console.error(INTERMediator.calculateRequiredObject);
        }


        function updateCalculationFields() {
            var nodeId, exp, nInfo, valuesArray, leafNodes, calcObject, ix, refersArray, calcFieldInfo;
            var targetNode, targetExp, field, valueSeries, targetElement, targetIds, field, counter;

            IMLibNodeGraph.clear();
            for (nodeId in INTERMediator.calculateRequiredObject) {
                calcObject = INTERMediator.calculateRequiredObject[nodeId];
                if (calcObject) {
                    calcFieldInfo = INTERMediatorLib.getCalcNodeInfoArray(nodeId);
                    targetNode = document.getElementById(calcFieldInfo.field);
                    for (field in calcObject.values) {
                        if (field.indexOf(INTERMediator.separator) > -1) {
                            targetExp = field;
                        } else {
                            targetExp = calcObject.nodeInfo.table + INTERMediator.separator + field;
                        }
                        do {
                            targetIds = INTERMediatorOnPage.getNodeIdsHavingTargetFromRepeater(targetNode, targetExp);
                            if (targetIds && targetIds.length > 0) {
                                break;
                            }
                            targetIds = INTERMediatorOnPage.getNodeIdsHavingTargetFromEnclosure(targetNode, targetExp);
                            if (targetIds && targetIds.length > 0) {
                                break;
                            }
                            targetNode = INTERMediatorLib.getParentRepeater(
                                INTERMediatorLib.getParentEnclosure(targetNode));
                        } while (targetNode);
                        if (INTERMediatorLib.is_array(targetIds)) {
                            INTERMediator.calculateRequiredObject[nodeId].referes[field] = targetIds;
                            if (targetIds.length != INTERMediator.calculateRequiredObject[nodeId].values[field].length) {
                                counter = targetIds.length;
                                valuesArray = [];
                                while (counter > 0) {
                                    counter--;
                                    valuesArray.push(undefined);
                                }
                                INTERMediator.calculateRequiredObject[nodeId].values[field] = valuesArray;
                            }
                        }
                    }

                    for (field in calcObject.referes) {
                        for (ix = 0; ix < calcObject.referes[field].length; ix++) {
                            IMLibNodeGraph.addEdge(nodeId, calcObject.referes[field][ix]);
                        }
                    }
                }
            }

            do {
                leafNodes = IMLibNodeGraph.getLeafNodesWithRemoving();
                for (i = 0; i < leafNodes.length; i++) {
                    calcObject = INTERMediator.calculateRequiredObject[leafNodes[i]];
                    calcFieldInfo = INTERMediatorLib.getCalcNodeInfoArray(leafNodes[i]);
                    if (calcObject) {
                        targetNode = document.getElementById(calcFieldInfo.field);
                        exp = calcObject.expression;
                        nInfo = calcObject.nodeInfo;
                        valuesArray = calcObject.values;
                        refersArray = calcObject.referes;
                        for (field in valuesArray) {
                            valueSeries = [];
                            for (ix = 0; ix < valuesArray[field].length; ix++) {
                                if (valuesArray[field][ix] == undefined) {
                                    targetElement = document.getElementById(refersArray[field][ix]);
                                    valueSeries.push(IMLibElement.getValueFromIMNode(targetElement));
                                } else {
                                    valueSeries.push(valuesArray[field][ix]);
                                }
                            }
                            calcObject.values[field] = valueSeries;
                        }
                        IMLibElement.setValueToIMNode(
                            targetNode,
                            calcFieldInfo.target,
                            Parser.evaluate(exp, valuesArray),
                            true);
                    } else {

                    }
                }
            } while (leafNodes.length > 0);
            if (IMLibNodeGraph.nodes.length > 0) {
                INTERMediator.setErrorMessage(new Exception(),
                    INTERMediatorLib.getInsertedString(
                        INTERMediatorOnPage.getMessages()[1037], []));
            }
        }

        function retrieveDataForEnclosure(currentContext, fieldList, relationValue) {
            var ix, keyField, targetRecords, counter, oneRecord, isMatch, index, fieldName, condition,
                recordNumber, useLimit, optionalCondition = [];

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
                    if (relationValue === null) {
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
                    if (currentContext["portal"] == true) {
                        for (condition in INTERMediator.additionalCondition) {
                            optionalCondition.push(INTERMediator.additionalCondition[condition]);
                            break;
                        }
                    }
                    useLimit = true;
                    if (currentContext["relation"] !== undefined) {
                        useLimit = false;
                    }
                    if (currentContext['maxrecords'] && useLimit && Number(INTERMediator.pagedSize) > 0
                        && Number(currentContext['maxrecords']) >= Number(INTERMediator.pagedSize)) {
                        recordNumber = Number(INTERMediator.pagedSize);
                    } else {
                        recordNumber = Number(currentContext['records']);
                    }
                    targetRecords = INTERMediator_DBAdapter.db_query({
                        "name": currentContext['name'],
                        "records": recordNumber,
                        "paging": currentContext['paging'],
                        "fields": fieldList,
                        "parentkeyvalue": relationValue,
                        "conditions": optionalCondition,
                        "useoffset": true,
                        "useLimit": useLimit});
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex, "EXCEPTION-12");
                    }
                }
            }
            return targetRecords;
        }

        function setIdValue(node) {
            var i, elementInfo, comp, overwrite = true;

            if (node.getAttribute('id') === null) {
                node.setAttribute('id', nextIdValue());
            } else {
                if (INTERMediator.elementIds.indexOf(node.getAttribute('id')) >= 0) {
                    elementInfo = INTERMediatorLib.getLinkedElementInfo(node);
                    for (i = 0; i < elementInfo.length; i++) {
                        comp = elementInfo[i].split(INTERMediator.separator);
                        if (comp[2] == "#id") {
                            overwrite = false;
                        }
                    }
                    if (overwrite) {
                        node.setAttribute('id', nextIdValue());
                    }
                }
                INTERMediator.elementIds.push(node.getAttribute('id'));
            }
            return node;
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
                if (nodeInfoTable != IMLibLocalContext.contextName) {
                    if (nodeInfoField != null
                        && nodeInfoField.length != 0
                        && nodeInfoTable.length != 0
                        && nodeInfoTable != null) {
                        if (!fieldList[nodeInfoTableIndex]) {
                            fieldList[nodeInfoTableIndex] = [];
                        }
                        fieldList[nodeInfoTableIndex].push(nodeInfoField);
                        if (!tableVote[nodeInfoTableIndex]) {
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
                setIdValue(repeatersOneRec[i]);
                shouldDeleteNodes.push(repeatersOneRec[i].getAttribute('id'));
            }
            return shouldDeleteNodes;
        }

        function setupDeleteButton(encNodeTag, repNodeTag, endOfRepeaters, currentContext, keyField, keyValue, foreignField, foreignValue, shouldDeleteNodes) {
            // Handling Delete buttons
            var buttonNode, thisId, deleteJSFunction, tdNodes, tdNode;

            if (!currentContext['repeat-control']
                || !currentContext['repeat-control'].match(/delete/i)) {
                return;
            }
            if (currentContext['relation']
                || currentContext['records'] === undefined
                || (currentContext['records'] > 1 && Number(INTERMediator.pagedSize) != 1)) {

                buttonNode = document.createElement('BUTTON');
                INTERMediatorLib.setClassAttributeToNode(buttonNode, "IM_Button_Delete");
                buttonNode.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[6]));
                thisId = 'IM_Button_' + buttonIdNum;
                buttonNode.setAttribute('id', thisId);
                buttonIdNum++;
                deleteJSFunction = function (a, b, c, d, e) {
                    var contextName = a, keyField = b, keyValue = c, removeNodes = d, confirming = e;

                    return function () {
                        INTERMediator.deleteButton(
                            contextName, keyField, keyValue, foreignField, foreignValue, removeNodes, confirming);
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
                // endOfRepeaters = repeatersOneRec[repeatersOneRec.length - 1];
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
                INTERMediator.deleteInsertOnNavi.push({
                    kind: 'DELETE',
                    name: currentContext['name'],
                    key: keyField,
                    value: keyValue,
                    confirm: currentContext['repeat-control'].match(/confirm-delete/i)
                });
            }
        }

        function setupInsertButton(currentContext, keyValue, encNodeTag, repNodeTag, node, relationValue) {
            var buttonNode, shouldRemove, enclosedNode, footNode, trNode, tdNode, liNode, divNode, insertJSFunction, i,
                firstLevelNodes, targetNodeTag, existingButtons, keyField, dbspec;
            if (currentContext['repeat-control'] && currentContext['repeat-control'].match(/insert/i)) {
                if (relationValue || !currentContext['paging'] || currentContext['paging'] === false) {
                    buttonNode = document.createElement('BUTTON');
                    INTERMediatorLib.setClassAttributeToNode(buttonNode, "IM_Button_Insert");
                    buttonNode.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[5]));
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
                            if (footNode === null) {
                                footNode = document.createElement(targetNodeTag);
                                enclosedNode.appendChild(footNode);
                            }
                            existingButtons = INTERMediatorLib.getElementsByClassName(footNode, 'IM_Button_Insert');
                            if (existingButtons.length == 0) {
                                trNode = document.createElement('TR');
                                tdNode = document.createElement('TD');
                                setIdValue(trNode);
                                footNode.appendChild(trNode);
                                trNode.appendChild(tdNode);
                                tdNode.appendChild(buttonNode);
                                shouldRemove = [trNode.getAttribute('id')];
                            }
                            break;
                        case 'UL':
                        case 'OL':
                            liNode = document.createElement('LI');
                            existingButtons = INTERMediatorLib.getElementsByClassName(liNode, 'IM_Button_Insert');
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
                                existingButtons = INTERMediatorLib.getElementsByClassName(divNode, 'IM_Button_Insert');
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
                            INTERMediator.insertButton(contextName, keyValue, relationValue, nodeId, removeNodes, confirming);
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
                    dbspec = INTERMediatorOnPage.getDBSpecification();
                    if (dbspec["db-class"] != null && dbspec["db-class"] == "FileMaker_FX") {
                        keyField = currentContext["key"] ? currentContext["key"] : "-recid";
                    } else {
                        keyField = currentContext["key"] ? currentContext["key"] : "id";
                    }
                    INTERMediator.deleteInsertOnNavi.push({
                        kind: 'INSERT',
                        name: currentContext['name'],
                        key: keyField,
                        confirm: currentContext['repeat-control'].match(/confirm-insert/i)
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

            if (document.getElementById('IM_CREDIT') === null) {
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
                aNode.setAttribute('href', 'http://inter-mediator.org/');
                aNode.setAttribute('target', '_href');
                spNode.appendChild(document.createTextNode('Generated by '));
                spNode.appendChild(aNode);
                spNode.appendChild(document.createTextNode(' Ver.@@@@2@@@@(@@@@1@@@@)'));
            }
        }
    },

    /**
     * Create Navigation Bar to move previous/next page
     */

    navigationSetup: function () {
        var navigation, i, insideNav, navLabel, node, start, pageSize, allCount, disableClass, c_node,
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

            if (navLabel === null || navLabel[8] !== false) {
                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    ((navLabel === null || navLabel[8] === null) ? INTERMediatorOnPage.getMessages()[2] : navLabel[8])));
                node.setAttribute('class', 'IM_NAV_button');
                INTERMediatorLib.addEvent(node, 'click', function () {
                    IM_Init();

                    location.reload();
                });
            }

            if (navLabel === null || navLabel[4] !== false) {
                start = Number(INTERMediator.startFrom);
                pageSize = Number(INTERMediator.pagedSize);
                allCount = Number(INTERMediator.pagedAllCount);
                disableClass = " IM_NAV_disabled";
                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    ((navLabel === null || navLabel[4] === null) ?
                        INTERMediatorOnPage.getMessages()[1] : navLabel[4]) + (start + 1)
                        + ((Math.min(start + pageSize, allCount) - start > 1) ?
                        (((navLabel === null || navLabel[5] === null) ? "-" : navLabel[5])
                            + Math.min(start + pageSize, allCount)) : '')
                        + ((navLabel === null || navLabel[6] === null) ? " / " : navLabel[6]) + (allCount)
                        + ((navLabel === null || navLabel[7] === null) ? "" : navLabel[7])));
                node.setAttribute('class', 'IM_NAV_info');
            }

            if (navLabel === null || navLabel[0] !== false) {
                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    (navLabel === null || navLabel[0] === null) ? '<<' : navLabel[0]));
                node.setAttribute('class', 'IM_NAV_button' + (start == 0 ? disableClass : ""));
                INTERMediatorLib.addEvent(node, 'click', function () {
                    INTERMediator.startFrom = 0;
                    INTERMediator.constructMain(true);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    (navLabel === null || navLabel[1] === null) ? '<' : navLabel[1]));
                node.setAttribute('class', 'IM_NAV_button' + (start == 0 ? disableClass : ""));
                prevPageCount = (start - pageSize > 0) ? start - pageSize : 0;
                INTERMediatorLib.addEvent(node, 'click', function () {
                    INTERMediator.startFrom = prevPageCount;
                    INTERMediator.constructMain(true);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    (navLabel === null || navLabel[2] === null) ? '>' : navLabel[2]));
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
                    (navLabel === null || navLabel[3] === null) ? '>>' : navLabel[3]));
                node.setAttribute('class', 'IM_NAV_button' + (start + pageSize >= allCount ? disableClass : ""));
                if (pageSize == 1) {
                    endPageCount = allCount - pageSize;
                } else {
                    endPageCount = allCount - (allCount % pageSize);
                }
                INTERMediatorLib.addEvent(node, 'click', function () {
                    INTERMediator.startFrom = (endPageCount > 0) ? endPageCount : 0;
                    INTERMediator.constructMain(true);
                });

                // Get from http://agilmente.com/blog/2013/08/04/inter-mediator_pagenation_1/
                node = document.createElement("SPAN");
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[10]));
                c_node = document.createElement("INPUT");
                c_node.setAttribute("class", 'IM_NAV_JUMP');
                c_node.setAttribute("type", 'text');
                c_node.setAttribute("value", Math.ceil(INTERMediator.startFrom / pageSize + 1));
                node.appendChild(c_node);
                node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[11]));
                INTERMediatorLib.addEvent(
                    c_node,
                    "change",
                    function () {
                        if (this.value < 1) {
                            this.value = 1;
                        }
                        var max_page = Math.ceil(allCount / pageSize);
                        if (max_page < this.value) {
                            this.value = max_page;
                        }
                        INTERMediator.startFrom = ( ~~this.value - 1 ) * pageSize;
                        INTERMediator.construct(true);
                    }
                )
                // ---------
            }

            if (navLabel === null || navLabel[9] !== false) {
                for (i = 0; i < INTERMediator.deleteInsertOnNavi.length; i++) {
                    switch (INTERMediator.deleteInsertOnNavi[i]['kind']) {
                        case 'INSERT':
                            node = document.createElement('SPAN');
                            navigation.appendChild(node);
                            node.appendChild(
                                document.createTextNode(
                                    INTERMediatorOnPage.getMessages()[3] + ': '
                                        + INTERMediator.deleteInsertOnNavi[i]['name']));
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
                                    INTERMediator.deleteInsertOnNavi[i]['name'],
                                    INTERMediator.deleteInsertOnNavi[i]['key'],
                                    INTERMediator.deleteInsertOnNavi[i]['confirm'] ? true : false)
                            );
                            break;
                        case 'DELETE':
                            node = document.createElement('SPAN');
                            navigation.appendChild(node);
                            node.appendChild(
                                document.createTextNode(
                                    INTERMediatorOnPage.getMessages()[4] + ': '
                                        + INTERMediator.deleteInsertOnNavi[i]['name']));
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
                                    INTERMediator.deleteInsertOnNavi[i]['name'],
                                    INTERMediator.deleteInsertOnNavi[i]['key'],
                                    INTERMediator.deleteInsertOnNavi[i]['value'],
                                    INTERMediator.deleteInsertOnNavi[i]['confirm'] ? true : false));
                            break;
                    }
                }
            }
            if (navLabel === null || navLabel[10] !== false) {
                if (INTERMediatorOnPage.getOptionsTransaction() == 'none') {
                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        (navLabel === null || navLabel[10] === null) ?
                            INTERMediatorOnPage.getMessages()[7] : navLabel[10]));
                    node.setAttribute('class', 'IM_NAV_button');
                    INTERMediatorLib.addEvent(node, 'click', INTERMediator.saveRecordFromNavi);
                }
            }
            if (navLabel === null || navLabel[11] !== false) {
                if (INTERMediatorOnPage.requireAuthentication) {
                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        INTERMediatorOnPage.getMessages()[8] + INTERMediatorOnPage.authUser));
                    node.setAttribute('class', 'IM_NAV_info');

                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        (navLabel === null || navLabel[11] === null) ?
                            INTERMediatorOnPage.getMessages()[9] : navLabel[11]));
                    node.setAttribute('class', 'IM_NAV_button');
                    INTERMediatorLib.addEvent(node, 'click',
                        function () {
                            INTERMediatorOnPage.logout();
                            location.reload();
                        });
                }
            }
        }
    },

    getLocalProperty: function (localKey, defaultValue) {
        var value;
        value = IMLibLocalContext.getValue(localKey);
        return value === null ? defaultValue : value;
    },

    setLocalProperty: function (localKey, value) {
        IMLibLocalContext.setValue(localKey, value);
    }
};

INTERMediator.propertyIETridentSetup();

if (INTERMediator.isIE && INTERMediator.ieVersion < 9) {
    INTERMediator.startFrom = 0;
    INTERMediator.pagedSize = 0;
    INTERMediator.additionalCondition = {};
    INTERMediator.additionalSortKey = {};
} else {
    Object.defineProperty(INTERMediator, 'startFrom', {
        get: function () {
            return INTERMediator.getLocalProperty("_im_startFrom", 0);
        },
        set: function (value) {
            INTERMediator.setLocalProperty("_im_startFrom", value);
        }
    });
    Object.defineProperty(INTERMediator, 'pagedSize', {
        get: function () {
            return INTERMediator.getLocalProperty("_im_pagedSize", 0);
        },
        set: function (value) {
            INTERMediator.setLocalProperty("_im_pagedSize", value);
        }
    });
    Object.defineProperty(INTERMediator, 'additionalCondition', {
        get: function () {
            return INTERMediator.getLocalProperty("_im_additionalCondition", {});
        },
        set: function (value) {
            INTERMediator.setLocalProperty("_im_additionalCondition", value);
        }
    });
    Object.defineProperty(INTERMediator, 'additionalSortKey', {
        get: function () {
            return INTERMediator.getLocalProperty("_im_additionalSortKey", {});
        },
        set: function (value) {
            INTERMediator.setLocalProperty("_im_additionalSortKey", value);
        }
    });
}
