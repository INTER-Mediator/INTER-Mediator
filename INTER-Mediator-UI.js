/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */

var IMLibUI = {

    isShiftKeyDown: false,
    isControlKeyDown: false,

    keyDown: function (evt) {
        var keyCode = (window.event) ? evt.which : evt.keyCode;
        if (keyCode == 16) {
            IMLibUI.isShiftKeyDown = true;
        }
        if (keyCode == 17) {
            IMLibUI.isControlKeyDown = true;
        }
    },

    keyUp: function (evt) {
        var keyCode = (window.event) ? evt.which : evt.keyCode;
        if (keyCode == 16) {
            IMLibUI.isShiftKeyDown = false;
        }
        if (keyCode == 17) {
            IMLibUI.isControlKeyDown = false;
        }
    },
    /*
     valueChange
     Parameters:
     */
    valueChange: function (idValue) {
        var changedObj, objType, contextInfo, i, updateRequiredContext, associatedNode, currentValue, newValue,
            linkInfo, nodeInfo;

        if (IMLibUI.isShiftKeyDown && IMLibUI.isControlKeyDown) {
            INTERMediator.setDebugMessage("Canceled to update the value with shift+control keys.");
            INTERMediator.flushMessage();
            IMLibUI.isShiftKeyDown = false;
            IMLibUI.isControlKeyDown = false;
            return;
        }
        IMLibUI.isShiftKeyDown = false;
        IMLibUI.isControlKeyDown = false;

        changedObj = document.getElementById(idValue);
        if (changedObj != null) {
            if (changedObj.readOnly) {  // for Internet Explorer
                return;
            }
            if (!validation(changedObj)) {   // Validation error.
                return;
            }
            objType = changedObj.getAttribute('type');
            if (objType == 'radio' && !changedObj.checked) {
                INTERMediatorOnPage.hideProgress();
                return;
            }
            linkInfo = INTERMediatorLib.getLinkedElementInfo(changedObj);
            // for js-widget support
            if (!linkInfo && INTERMediatorLib.isWidgetElement(changedObj.parentNode)) {
                linkInfo = INTERMediatorLib.getLinkedElementInfo(changedObj.parentNode);
            }

            nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfo[0]);  // Suppose to be the first definition.
            contextInfo = IMLibContextPool.getContextInfoFromId(idValue, nodeInfo.target);
            if (contextInfo) {
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
                }
            }
            IMLibCalc.recalculation(idValue);
            INTERMediator.flushMessage();
        }

        function validation(changedObj) {
            var linkInfo, matched, context, i, index, didValidate, contextInfo, result, messageNode, errorMsgs;
            try {
                linkInfo = INTERMediatorLib.getLinkedElementInfo(changedObj);
                didValidate = false;
                result = true;
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
                                                case "inline":
                                                    INTERMediatorLib.clearErrorMessage(changedObj);
                                                    messageNode = INTERMediatorLib.createErrorMessageNode(
                                                        "SPAN", context["validation"][index].message);
                                                    changedObj.parentNode.insertBefore(
                                                        messageNode, changedObj.nextSibling);
                                                    break;
                                                case "end-of-sibling":
                                                    INTERMediatorLib.clearErrorMessage(changedObj);
                                                    messageNode = INTERMediatorLib.createErrorMessageNode(
                                                        "DIV", context["validation"][index].message);
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
                                                case "inline":
                                                case "end-of-sibling":
                                                    INTERMediatorLib.clearErrorMessage(changedObj);
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
                return result;
            } catch (ex) {
                if (ex == "_im_requath_request_") {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex, "EXCEPTION-32: on the validation process.");
                }
                return false;
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
                            {
                                field: criteria[0],
                                operator: '=',
                                value: criteria[1]
                            }
                        ],
                        dataset: [
                            {
                                field: contextInfo.field + (contextInfo.portal ? ("." + contextInfo.portal) : ""),
                                value: newValue
                            }
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
        var i;

        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1025])) {
                return;
            }
        }
        INTERMediatorOnPage.showProgress();
        try {
            INTERMediatorOnPage.retrieveAuthInfo();

            currentContext = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', targetName);
            relationDef = currentContext["relation"];
            if (relationDef) {
                for (index in relationDef) {
                    if (relationDef[index]["portal"] == true) {
                        currentContext["portal"] = true;
                    }
                }
            }

            if (foreignField != "" && currentContext["portal"] == true) {
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
                            IMLibUI.deleteButton(
                                targetName, keyField, keyValue, foreignField, foreignValue, removeNodes, false);
                        }
                    );
                    return;
                }
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-3");
            }
        }
        for (i = 0; i < removeNodes.length; i++) {
            IMLibContextPool.removeRecordFromPool(removeNodes[i]);
        }
        IMLibElement.deleteNodes(removeNodes);
        IMLibCalc.recalculation();
        INTERMediatorOnPage.hideProgress();
        INTERMediator.flushMessage();
    },

    insertButton: function (targetName, keyValue, foreignValues, updateNodes, removeNodes, isConfirm) {
        var currentContext, recordSet, index, key, conditions, i, relationDef, targetRecord, portalField,
            targetPortalField, targetPortalValue, existRelated = false, relatedRecordSet, newRecordId,
            keyField, associatedContext, createdRecord, newRecord, portalRowNum, recId, maxRecId;

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
                                    value: keyValue
                                }
                            ]
                        }
                    );
                    for (portalField in targetRecord["recordset"][0][0]) {
                        if (portalField.indexOf(targetName + "::") > -1 && portalField !== targetName + "::-recid") {
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
                                        value: keyValue
                                    }
                                ]
                            }
                        );
                        for (portalField in targetRecord["recordset"]) {
                            if (portalField.indexOf(targetName + "::") > -1 && portalField !== targetName + "::-recid") {
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
                            value: keyValue
                        }
                    ],
                    dataset: relatedRecordSet
                });
                
                targetRecord = INTERMediator_DBAdapter.db_query(
                    {
                        name: targetName,
                        records: 1,
                        conditions: [
                            {
                                field: currentContext["key"] ? currentContext["key"] : "-recid",
                                operator: "=",
                                value: keyValue
                            }
                        ]
                    }
                );
                
                newRecord = {};
                maxRecId = -1;
                for (portalRowNum in targetRecord["recordset"][0]) {
                    if (portalRowNum == Number(portalRowNum)
                            && targetRecord["recordset"][0][portalRowNum][targetName + "::-recid"]) {
                        recId = parseInt(targetRecord["recordset"][0][portalRowNum][targetName + "::-recid"], 10);
                        if (recId > maxRecId) {
                            maxRecId = recId;
                            newRecord.recordset = [];
                            newRecord.recordset.push(targetRecord["recordset"][0][portalRowNum]);
                        }
                    }
                }
            } else {
                newRecord = INTERMediator_DBAdapter.db_createRecord({name: targetName, dataset: recordSet});
                newRecordId = newRecord.newKeyValue;
            }
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                INTERMediatorOnPage.authChallenge = null;
                INTERMediatorOnPage.authHashedPassword = null;
                INTERMediatorOnPage.authenticating(
                    function () {
                        IMLibUI.insertButton(
                            targetName, keyValue, foreignValues, updateNodes, removeNodes, false);
                    }
                );
                INTERMediator.flushMessage();
                return;
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-4");
            }
        }
        keyField = currentContext["key"] ? currentContext["key"] : "-recid";
        associatedContext = IMLibContextPool.contextFromEnclosureId(updateNodes);
        if (associatedContext) {
            associatedContext.foreignValue = foreignValues;
            if (currentContext["portal"] == true && existRelated == false) {
                conditions = INTERMediator.additionalCondition;
                conditions[targetName] = {
                    field: keyField,
                    operator: "=",
                    value: keyValue
                };
                INTERMediator.additionalCondition = conditions;
            }
            createdRecord = [{}];
            createdRecord[0][keyField] = newRecordId;
            INTERMediator.constructMain(associatedContext, newRecord.recordset);
        }

        IMLibCalc.recalculation();
        INTERMediatorOnPage.hideProgress();
        INTERMediator.flushMessage();
    },

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
                            if (validationInfo && validationInfo.field == comp[1]) {
                                switch (validationInfo.notify) {
                                    case "inline":
                                    case "end-of-sibling":
                                        INTERMediatorLib.clearErrorMessage(linkedNodes[i]);
                                        break;
                                }
                            }
                        }
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
                                            case "inline":
                                                INTERMediatorLib.clearErrorMessage(linkedNodes[i]);
                                                messageNode = INTERMediatorLib.createErrorMessageNode(
                                                    "SPAN", validationInfo.message);
                                                linkedNodes[i].parentNode.insertBefore(
                                                    messageNode, linkedNodes[i].nextSibling);
                                                break;
                                            case "end-of-sibling":
                                                INTERMediatorLib.clearErrorMessage(linkedNodes[i]);
                                                messageNode = INTERMediatorLib.createErrorMessageNode(
                                                    "DIV", validationInfo.message);
                                                linkedNodes[i].parentNode.appendChild(messageNode);
                                                break;
                                            default:
                                                alertmessage += validationInfo.message + "\n";
                                        }
                                        if (INTERMediatorOnPage.doAfterValidationFailure != null) {
                                            INTERMediatorOnPage.doAfterValidationFailure(linkedNodes[i]);
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
                    fieldData.push({
                        field: comp[1],
                        value: mergedValues.join("\n") + "\n"
                    });
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
    },

    eventUpdateHandler: function (contextName) {
        IMLibLocalContext.updateAll();
        var context = IMLibContextPool.getContextFromName(contextName);
        INTERMediator.constructMain(context[0]);
    },

    eventAddOrderHandler: function (e) {    // e is mouse event
        var targetKey, targetSplit, key, itemSplit, extValue;
        if (e.target) {
            targetKey = e.target.getAttribute("data-im");
        } else {
            targetKey = e.srcElement.getAttribute("data-im");
        }
        targetSplit = targetKey.split(":");
        if (targetSplit[0] != "_@addorder" || targetSplit.length < 3) {
            return;
        }
        for (key in IMLibLocalContext.store) {
            itemSplit = key.split(":");
            if (itemSplit.length > 3 && itemSplit[0] == "valueofaddorder" && itemSplit[1] == targetSplit[1]) {
                extValue = IMLibLocalContext.getValue(key);
                if (extValue) {
                    IMLibLocalContext.store[key]++;
                }
            }
        }
        IMLibLocalContext.setValue("valueof" + targetKey.substring(2), 1);
        IMLibUI.eventUpdateHandler(targetSplit[1]);
    }
}