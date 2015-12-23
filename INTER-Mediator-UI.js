/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
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
     Parameters: It the validationOnly parameter is set to true, this method should return the boolean value
     if validation is succeed or not.
     */
    valueChange: function (idValue, validationOnly) {
        var changedObj, objType, i, newValue, criteria, linkInfo, nodeInfo, contextInfo,
            keyingComp, keyingField, keyingValue, targetField, targetContext, completeFunction;

        if (IMLibUI.isShiftKeyDown && IMLibUI.isControlKeyDown) {
            INTERMediator.setDebugMessage("Canceled to update the value with shift+control keys.");
            INTERMediator.flushMessage();
            IMLibUI.isShiftKeyDown = false;
            IMLibUI.isControlKeyDown = false;
            return true;
        }
        IMLibUI.isShiftKeyDown = false;
        IMLibUI.isControlKeyDown = false;

        changedObj = document.getElementById(idValue);
        if (!changedObj) {
            return false;
        }
        if (changedObj.readOnly) {  // for Internet Explorer
            return true;
        }

        linkInfo = INTERMediatorLib.getLinkedElementInfo(changedObj);
        // for js-widget support
        if (!linkInfo && INTERMediatorLib.isWidgetElement(changedObj.parentNode)) {
            linkInfo = INTERMediatorLib.getLinkedElementInfo(changedObj.parentNode);
        }
        nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfo[0]);  // Suppose to be the first definition.
        contextInfo = IMLibContextPool.getContextInfoFromId(idValue, nodeInfo.target);

        if (!IMLibUI.validation(changedObj)) {  // Validation error.
            changedObj.focus();
            window.setTimeout((function () {
                var originalObj = changedObj;
                var originalContextInfo = contextInfo;
                return function () {
                    if (originalContextInfo) {
                        originalObj.value = originalContextInfo.context.getValue(
                            originalContextInfo.record, originalContextInfo.field);
                    }
                    originalObj.removeAttribute("data-im-validation-notification");
                }
            })(), 0);
            return false;
        }
        if (validationOnly === true) {
            return true;
        }

        objType = changedObj.getAttribute("type");
        if (objType === "radio" && !changedObj.checked) {
            INTERMediatorOnPage.hideProgress();
            return true;
        }

        if (!contextInfo) {
            return false;
        }
        newValue = IMLibElement.getValueFromIMNode(changedObj);
        if (contextInfo.context.isValueUndefined(contextInfo.record, contextInfo.field, contextInfo.portal)) {
            INTERMediator.setErrorMessage("Error in updating.",
                INTERMediatorLib.getInsertedString(
                    INTERMediatorOnPage.getMessages()[1040],
                    [contextInfo.context.contextName, contextInfo.field]));
            return false;
        }
        if (INTERMediatorOnPage.getOptionsTransaction() == 'none') {
            // Just supporting NON-target info.
            // contextInfo.context.setValue(
            // contextInfo.record, contextInfo.field, newValue);
            contextInfo.context.setModified(contextInfo.record, contextInfo.field, newValue);
            return false;
        }
        INTERMediatorOnPage.showProgress();
        try {
            if (!changedObj) {
                throw "false_exit";
            }
            linkInfo = INTERMediatorLib.getLinkedElementInfo(changedObj);
            nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfo[0]);
            if (nodeInfo.table == IMLibLocalContext.contextName) {
                throw "false_exit";
            }
            idValue = changedObj.getAttribute('id');
            contextInfo = IMLibContextPool.getContextInfoFromId(idValue, nodeInfo.target);   // suppose to target = ""

            if (INTERMediator.ignoreOptimisticLocking) {
                IMLibContextPool.updateContext(idValue, nodeInfo.target);
                newValue = IMLibElement.getValueFromIMNode(changedObj);
                if (newValue != null) {
                    criteria = contextInfo.record.split('=');
                    INTERMediatorOnPage.retrieveAuthInfo();
                    INTERMediator_DBAdapter.db_update_async({
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
                        },
                        (function () {
                            var idValueCapt2 = idValue;
                            var contextInfoCapt = contextInfo;
                            var newValueCapt = newValue;
                            return function (result) {
                                var updateRequiredContext, currentValue, associatedNode;
                                INTERMediatorOnPage.hideProgress();
                                updateRequiredContext = IMLibContextPool.dependingObjects(idValueCapt2);
                                for (i = 0; i < updateRequiredContext.length; i++) {
                                    updateRequiredContext[i].foreignValue = {};
                                    updateRequiredContext[i].foreignValue[contextInfoCapt.field] = newValueCapt;
                                    if (updateRequiredContext[i]) {
                                        INTERMediator.constructMain(updateRequiredContext[i]);
                                        associatedNode = updateRequiredContext[i].enclosureNode;
                                        if (INTERMediatorLib.isPopupMenu(associatedNode)) {
                                            currentValue = contextInfo.context.getContextValue(associatedNode.id, "");
                                            IMLibElement.setValueToIMNode(associatedNode, "", currentValue, false);
                                        }
                                    }
                                }
                                IMLibCalc.recalculation();//IMLibCalc.recalculation(idValueCapt2); // Optimization Required
                                INTERMediator.flushMessage();
                            }
                        })(),
                        function () {
                            INTERMediatorOnPage.hideProgress();
                            INTERMediator.setErrorMessage("Error in valueChange method.", "EXCEPTION-2");
                        }
                    );
                }
            } else {
                targetContext = contextInfo['context'];
                targetField = contextInfo['field'];
                keyingComp = contextInfo['record'].split('=');
                keyingField = keyingComp[0];
                keyingComp.shift();
                keyingValue = keyingComp.join('=');
                INTERMediator_DBAdapter.eliminateDuplicatedConditions = true;
                INTERMediator_DBAdapter.db_query_async(
                    {
                        name: contextInfo['context'].contextName,
                        records: 1,
                        paging: false,
                        fields: [targetField],
                        parentkeyvalue: null,
                        conditions: [
                            {field: keyingField, operator: '=', value: keyingValue}
                        ],
                        useoffset: false,
                        primaryKeyOnly: true
                    },
                    (function () {
                        var targetFieldCapt = targetField;
                        var contextInfoCapt = contextInfo;
                        var targetContextCapt = targetContext;
                        var changedObjeCapt = changedObj;
                        var nodeInfoCapt = nodeInfo;
                        var idValueCapt = idValue;
                        return function (result) {
                            var response, initialvalue, newValue, isOthersModified,
                                isCheckResult, portalKey, portalIndex, currentFieldVal;
                            if (contextInfoCapt.portal) {
                                isCheckResult = false;
                                portalKey = contextInfo.context.contextName + "::-recid";
                                if (result.dbresult && result.dbresult[0]) {
                                    for (portalIndex in result.dbresult[0]) {
                                        var portalRecord = result.dbresult[0][portalIndex];
                                        if (portalRecord[portalKey]
                                            && portalRecord[targetFieldCapt] !== undefined
                                            && portalRecord[portalKey] == contextInfo.portal) {
                                            currentFieldVal = portalRecord[targetFieldCapt];
                                            isCheckResult = true;
                                        }
                                    }
                                }
                                if (!isCheckResult) {
                                    alert(INTERMediatorLib.getInsertedString(
                                        INTERMediatorOnPage.getMessages()[1003], [targetFieldCapt]));
                                    INTERMediatorOnPage.hideProgress();
                                    return;
                                }
                            } else {
                                if (!result.dbresult
                                    || !result.dbresult[0]    // This value could be null or undefined
                                    || result.dbresult[0][targetFieldCapt] === undefined) {
                                    alert(INTERMediatorLib.getInsertedString(
                                        INTERMediatorOnPage.getMessages()[1003], [targetFieldCapt]));
                                    INTERMediatorOnPage.hideProgress();
                                    return;
                                }
                                if (result.resultCount > 1) {
                                    response = confirm(INTERMediatorOnPage.getMessages()[1024]);
                                    if (!response) {
                                        INTERMediatorOnPage.hideProgress();
                                        return;
                                    }
                                }
                                currentFieldVal = result.dbresult[0][targetFieldCapt];
                            }
                            initialvalue = targetContextCapt.getValue(
                                contextInfoCapt.record, targetFieldCapt, contextInfoCapt.portal);

                            switch (changedObjeCapt.tagName) {
                                case "INPUT":
                                    switch (changedObjeCapt.getAttribute("type")) {
                                        case "checkbox":
                                            if (initialvalue == changedObjeCapt.value) {
                                                isOthersModified = false;
                                            } else if (!parseInt(currentFieldVal)) {
                                                isOthersModified = false;
                                            } else {
                                                isOthersModified = true;
                                            }
                                            break;
                                        default:
                                            isOthersModified = (initialvalue != currentFieldVal);
                                            break;
                                    }
                                    break;
                                default:
                                    isOthersModified = (initialvalue != currentFieldVal);
                                    break;
                            }
                            if (isOthersModified) {
                                // The value of database and the field is different. Others must be changed this field.
                                newValue = IMLibElement.getValueFromIMNode(changedObjeCapt);
                                if (!confirm(INTERMediatorLib.getInsertedString(
                                        INTERMediatorOnPage.getMessages()[1001],
                                        [initialvalue, newValue, currentFieldVal]))) {
                                    window.setTimeout(function () {
                                        changedObjeCapt.focus();
                                    }, 0);

                                    INTERMediatorOnPage.hideProgress();
                                    return false;
                                }
                                INTERMediatorOnPage.retrieveAuthInfo(); // This is required. Why?
                            }
                            IMLibContextPool.updateContext(idValueCapt, nodeInfoCapt.target);
                            newValue = IMLibElement.getValueFromIMNode(changedObjeCapt);
                            if (newValue != null) {
                                criteria = contextInfo.record.split('=');
                                INTERMediatorOnPage.retrieveAuthInfo();
                                INTERMediator_DBAdapter.db_update_async({
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
                                    },
                                    (function () {
                                        var idValueCapt2 = idValueCapt;
                                        var contextInfoCapt = contextInfo;
                                        var newValueCapt = newValue;
                                        return function (result) {
                                            var updateRequiredContext, currentValue, associatedNode;
                                            INTERMediatorOnPage.hideProgress();
                                            updateRequiredContext = IMLibContextPool.dependingObjects(idValueCapt2);
                                            for (i = 0; i < updateRequiredContext.length; i++) {
                                                updateRequiredContext[i].foreignValue = {};
                                                updateRequiredContext[i].foreignValue[contextInfoCapt.field] = newValueCapt;
                                                if (updateRequiredContext[i]) {
                                                    INTERMediator.constructMain(updateRequiredContext[i]);
                                                    associatedNode = updateRequiredContext[i].enclosureNode;
                                                    if (INTERMediatorLib.isPopupMenu(associatedNode)) {
                                                        currentValue = contextInfo.context.getContextValue(associatedNode.id, "");
                                                        IMLibElement.setValueToIMNode(associatedNode, "", currentValue, false);
                                                    }
                                                }
                                            }
                                            IMLibCalc.recalculation();//IMLibCalc.recalculation(idValueCapt2); // Optimization Required
                                            INTERMediator.flushMessage();
                                        }
                                    })(),
                                    function () {
                                        INTERMediatorOnPage.hideProgress();
                                        INTERMediator.setErrorMessage("Error in valueChange method.", "EXCEPTION-2");
                                    }
                                );
                            }
                        }
                    })(),
                    function () {
                        INTERMediatorOnPage.hideProgress();
                        INTERMediator.setErrorMessage("Error in valueChange method.", "EXCEPTION-1");
                    }
                );
            }
        } catch (ex) {
            INTERMediatorOnPage.hideProgress();
        }
        return true;
    },

    validation: function (changedObj) {
        var linkInfo, matched, context, i, index, didValidate, contextInfo,
            result, messageNodes = [], messageNode;
        if (messageNodes) {
            while (messageNodes.length > 0) {
                messageNodes[0].parentNode.removeChild(messageNodes[0]);
                delete messageNodes[0];
            }
        }
        if (!messageNodes) {
            messageNodes = [];
        }
        try {
            linkInfo = INTERMediatorLib.getLinkedElementInfo(changedObj);
            didValidate = false;
            result = true;
            if (linkInfo.length > 0) {
                matched = linkInfo[0].match(/([^@]+)/);
                if (matched[1] !== IMLibLocalContext.contextName) {
                    context = INTERMediatorLib.getNamedObject(
                        INTERMediatorOnPage.getDataSources(), "name", matched[1]);
                    if (context != null && context.validation != null) {
                        for (i = 0; i < linkInfo.length; i++) {
                            matched = linkInfo[i].match(/([^@]+)@([^@]+)/);
                            for (index in context.validation) {
                                if (context.validation[index].field === matched[2]) {
                                    didValidate = true;
                                    result = Parser.evaluate(
                                        context.validation[index].rule,
                                        {"value": changedObj.value, "target": changedObj});
                                    if (!result) {
                                        switch (context.validation[index].notify) {
                                            case "inline":
                                                INTERMediatorLib.clearErrorMessage(changedObj);
                                                messageNode = INTERMediatorLib.createErrorMessageNode(
                                                    "SPAN", context.validation[index].message);
                                                changedObj.parentNode.insertBefore(
                                                    messageNode, changedObj.nextSibling);
                                                messageNodes.push(messageNode);
                                                break;
                                            case "end-of-sibling":
                                                INTERMediatorLib.clearErrorMessage(changedObj);
                                                messageNode = INTERMediatorLib.createErrorMessageNode(
                                                    "DIV", context.validation[index].message);
                                                changedObj.parentNode.appendChild(messageNode);
                                                messageNodes.push(messageNode);
                                                break;
                                            default:
                                                if (changedObj.getAttribute("data-im-validation-notification") !== "alert") {
                                                    alert(context.validation[index].message);
                                                    changedObj.setAttribute("data-im-validation-notification", "alert");
                                                }
                                                break;
                                        }
                                        contextInfo = IMLibContextPool.getContextInfoFromId(changedObj, "");
                                        if (contextInfo) {                                        // Just supporting NON-target info.
                                            changedObj.value = contextInfo.context.getValue(
                                                contextInfo.record, contextInfo.field);
                                            window.setTimeout(function () {
                                                changedObj.focus();
                                            }, 0);
                                            if (INTERMediatorOnPage.doAfterValidationFailure !== null) {
                                                INTERMediatorOnPage.doAfterValidationFailure(changedObj, linkInfo[i]);
                                            }
                                        }
                                        return result;
                                    } else {
                                        switch (context.validation[index].notify) {
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
                        result = INTERMediatorOnPage.doAfterValidationSucceed(changedObj, linkInfo[i]);
                    }
                }
            }
            return result;
        } catch (ex) {
            if (ex === "_im_requath_request_") {
                throw ex;
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-32: on the validation process.");
            }
            return false;
        }
    },

    copyButton: function (contextObj, keyValue) {
        var contextDef, assocDef, i, def, assocContexts, pStart, copyTerm;

        INTERMediatorOnPage.showProgress();
        contextDef = contextObj.getContextDef();
        if (contextDef['repeat-control'].match(/confirm-copy/)) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1041])) {
                return;
            }
        }
        try {
            if (contextDef["relation"]) {
                for (index in contextDef["relation"]) {
                    if (contextDef["relation"][index]["portal"] == true) {
                        contextDef["portal"] = true;
                    }
                }
            }

            assocDef = [];
            if (contextDef['repeat-control'].match(/copy-/)) {
                pStart = contextDef['repeat-control'].indexOf('copy-');
                copyTerm = contextDef['repeat-control'].substr(pStart + 5);
                if ((pStart = copyTerm.search(/\s/)) > -1) {
                    copyTerm = copyTerm.substr(0, pStart)
                }
                assocContexts = copyTerm.split(",");
                for (i = 0; i < assocContexts.length; i++) {
                    def = IMLibContextPool.getContextDef(assocContexts[i]);
                    if (def['relation'][0]['foreign-key']) {
                        assocDef.push({
                            name: def['name'],
                            field: def['relation'][0]['foreign-key'],
                            value: keyValue
                        });
                    }
                }
            }

            INTERMediatorOnPage.retrieveAuthInfo();
            INTERMediator_DBAdapter.db_copy_async({
                    name: contextDef["name"],
                    conditions: [{field: contextDef["key"], operator: "=", value: keyValue}],
                    associated: assocDef.length > 0 ? assocDef : null
                },
                (function () {
                    var contextDefCapt = contextDef;
                    var contextObjCapt = contextObj;
                    return function (result) {
                        var restore, conditions;
                        var newId = result.newRecordKeyValue;
                        if (newId > -1) {
                            restore = INTERMediator.additionalCondition;
                            INTERMediator.startFrom = 0;
                            if (contextDefCapt.records <= 1) {
                                conditions = INTERMediator.additionalCondition;
                                conditions[contextDefCapt.name] = {field: contextDefCapt.key, value: newId};
                                INTERMediator.additionalCondition = conditions;
                                IMLibLocalContext.archive();
                            }
                            INTERMediator_DBAdapter.unregister();
                            INTERMediator.constructMain(contextObjCapt);
                            INTERMediator.additionalCondition = restore;
                        }
                        IMLibCalc.recalculation();
                        INTERMediatorOnPage.hideProgress();
                        INTERMediator.flushMessage();
                    }
                })(),
                null
            );
        } catch (ex) {
            INTERMediator.setErrorMessage(ex, "EXCEPTION-43");
        }
    },

    deleteButton: function (targetName, keyField, keyValue, foreignField, foreignValue, removeNodes, isConfirm) {
        var i, index, currentContext, relationDef, dialogMessage, successProc;

        if (isConfirm) {
            dialogMessage = INTERMediatorOnPage.getMessages()[1025];
            if (!window.confirm(dialogMessage)) {
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
                    if (relationDef.hasOwnProperty(index) && relationDef[index]["portal"] == true) {
                        currentContext["portal"] = true;
                    }
                }
            }
            var successProc = function () {
                if (currentContext["relation"] == true) {
                    INTERMediator.pagedAllCount--;
                    if (INTERMediator.pagedAllCount - INTERMediator.startFrom < 1) {
                        INTERMediator.startFrom = INTERMediator.startFrom - INTERMediator.pagedSize;
                        if (INTERMediator.startFrom < 0) {
                            INTERMediator.startFrom = 0;
                        }
                    }
                    if (INTERMediator.pagedAllCount >= INTERMediator.pagedSize) {
                        INTERMediator.construct();
                    }
                    IMLibPageNavigation.navigationSetup();
                }
            };
            if (foreignField != "" && currentContext["portal"] == true) {
                INTERMediator_DBAdapter.db_update_async({
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
                }, successProc, null);
            } else {
                INTERMediator_DBAdapter.db_delete_async({
                    name: targetName,
                    conditions: [
                        {field: keyField, operator: '=', value: keyValue}
                    ]
                }, successProc, null);
            }

        } catch (ex) {
            if (ex == "_im_requath_request_") {
                if (INTERMediatorOnPage.requireAuthentication && !INTERMediatorOnPage.isComplementAuthData()) {
                    INTERMediatorOnPage.clearCredentials();
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
        IMLibCalc.recalculation(undefined, true);
        INTERMediatorOnPage.hideProgress();
        INTERMediator.flushMessage();
    },

    insertButton: function (targetName, keyValue, foreignValues, updateNodes, removeNodes, isConfirm) {
        var currentContext, recordSet, index, key, relationDef, targetRecord, portalField,
            targetPortalField, targetPortalValue, existRelated = false, relatedRecordSet,
            newRecord, portalRowNum, recId, maxRecId;

        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1026])) {
                return;
            }
        }
        INTERMediatorOnPage.showProgress();
        currentContext = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', targetName);
        recordSet = [];
        relatedRecordSet = [];
        if (foreignValues != null) {
            for (index in currentContext['relation']) {
                if (currentContext['relation'].hasOwnProperty(index)) {
                    recordSet.push({
                        field: currentContext['relation'][index]["foreign-key"],
                        value: foreignValues[currentContext['relation'][index]["join-field"]]
                    });
                }
            }
        }
        //try {
        INTERMediatorOnPage.retrieveAuthInfo();

        relationDef = currentContext["relation"];
        if (relationDef) {
            for (index in relationDef) {
                if (relationDef.hasOwnProperty(index) && relationDef[index]["portal"] == true) {
                    currentContext["portal"] = true;
                }
            }
        }
        if (currentContext["portal"] == true) {
            relatedRecordSet = [];
            for (index in currentContext["default-values"]) {
                if (currentContext["default-values"].hasOwnProperty(index)) {
                    relatedRecordSet.push({
                        field: targetName + "::" + currentContext["default-values"][index]["field"] + ".0",
                        value: currentContext["default-values"][index]["value"]
                    });
                }
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
            INTERMediator_DBAdapter.db_createRecord_async(
                {name: targetName, dataset: recordSet},
                (function () {
                    var currentContextCapt = currentContext;
                    var updateNodesCapt = updateNodes;
                    var foreignValuesCapt = foreignValues;
                    var existRelatedCapt = existRelated;
                    var keyValueCapt = keyValue;
                    return function (result) {
                        var keyField, newRecordId, associatedContext, conditions, createdRecord;
                        newRecordId = result.newRecordKeyValue;
                        keyField = currentContextCapt["key"] ? currentContextCapt["key"] : "-recid";
                        associatedContext = IMLibContextPool.contextFromEnclosureId(updateNodesCapt);
                        if (associatedContext) {
                            associatedContext.foreignValue = foreignValuesCapt;
                            if (currentContextCapt["portal"] == true && existRelatedCapt == false) {
                                conditions = INTERMediator.additionalCondition;
                                conditions[targetName] = {
                                    field: keyField,
                                    operator: "=",
                                    value: keyValueCapt
                                };
                                INTERMediator.additionalCondition = conditions;
                            }
                            createdRecord = [{}];
                            createdRecord[0][keyField] = newRecordId;
                            INTERMediator.constructMain(associatedContext, result.dbresult);
                        }
                        IMLibCalc.recalculation();
                        INTERMediatorOnPage.hideProgress();
                        INTERMediator.flushMessage();
                    }
                })(),
                function () {
                    INTERMediator.setErrorMessage("Insert Error", "EXCEPTION-4");
                }
            );

        }
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

        if (INTERMediatorOnPage.processingBeforePostOnlyContext) {
            if (!INTERMediatorOnPage.processingBeforePostOnlyContext(targetNode)) {
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

        contextInfo = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', selectedContext);
        INTERMediator_DBAdapter.db_createRecord_async(
            {name: selectedContext, dataset: fieldData},
            function (result) {
                var newNode, parentOfTarget, targetNode = node, thisContext = contextInfo,
                    isSetMsg = false;
                INTERMediator.flushMessage();
                if (INTERMediatorOnPage.processingAfterPostOnlyContext) {
                    INTERMediatorOnPage.processingAfterPostOnlyContext(targetNode, result.newRecordKeyValue);
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
            }, null);

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
};
