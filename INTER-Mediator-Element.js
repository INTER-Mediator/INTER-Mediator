/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */

var IMLibElement = {
    setValueToIMNode: function (element, curTarget, curVal, clearField) {
        var styleName, statement, currentValue, scriptNode, typeAttr, valueAttr, textNode,
            needPostValueSet = false, nodeTag, curValues, i, patterns, formattedValue = null,
            formatSpec, flags = {}, formatOption, negativeColor, negativeStyle, param1, mark, 
            negativeSign, negativeTailSign;
        // IE should \r for textNode and <br> for innerHTML, Others is not required to convert

        if (curVal === undefined) {
            return false;   // Or should be an error?
        }
        if (!element) {
            return false;   // Or should be an error?
        }
        if (curVal === null || curVal === false) {
            curVal = '';
        }

        nodeTag = element.tagName;

        if (clearField === true && curTarget === "") {
            switch (nodeTag) {
                case "INPUT":
                    switch (element.getAttribute("type")) {
                        case "text":
                            element.value = "";
                            break;
                        default:
                            break;
                    }
                    break;
                case "SELECT":
                    break;
                default:
                    while (element.childNodes.length > 0) {
                        element.removeChild(element.childNodes[0]);
                    }
                    break;
            }
        }

        formatSpec = element.getAttribute("data-im-format");
        if (formatSpec) {
            flags = {
                useSeparator: false,
                blankIfZero: false,
                negativeStyle: 0
            };
            formatOption = element.getAttribute("data-im-format-options");
            if (formatOption) {
                if (formatOption.toLowerCase().split(" ").indexOf("useseparator") > -1) {
                    flags.useSeparator = true;
                }
                if (formatOption.toLowerCase().split(" ").indexOf("blankifzero") > -1) {
                    flags.blankIfZero = true;
                }
            }
            negativeColor = element.getAttribute("data-im-format-negative-color");
            negativeStyle = element.getAttribute("data-im-format-negative-style");
            if (negativeStyle) {
                if (negativeStyle.toLowerCase() === "leadingminus" || 
                    negativeStyle.toLowerCase() === "leading-minus") {
                    flags.negativeStyle = 0;
                } else if (negativeStyle.toLowerCase() === "trailingminus" ||
                    negativeStyle.toLowerCase() === "trailing-minus") {
                    flags.negativeStyle = 1;
                } else if (negativeStyle.toLowerCase() === "parenthesis") {
                    flags.negativeStyle = 2;
                } else if (negativeStyle.toLowerCase() === "angle") {
                    flags.negativeStyle = 3;
                } else if (negativeStyle.toLowerCase() === "credit") {
                    flags.negativeStyle = 4;
                } else if (negativeStyle.toLowerCase() === "triangle") {
                    flags.negativeStyle = 5;
                }
            }
            
            patterns = [
                /^number\(([0-9]+)\)/,
                /^number[\(\)]*/,
                /^currency\(([0-9]+)\)/,
                /^currency[\(\)]*/,
                /^boolean\([\"|']([\S]+)[\"|'],[\s]*[\"|']([\S]+)[\"|']\)/,
                /^percent\(([0-9]+)\)/,
                /^percent[\(\)]*/
            ];
            for (i = 0; i < patterns.length; i++) {
                param1 = formatSpec.match(patterns[i]);
                if (param1) {
                    switch (param1.length) {
                        case 3:
                            if (param1[0].indexOf("boolean") > -1) {
                                formattedValue = INTERMediatorLib.booleanFormat(curVal, param1[1], param1[2]);
                            }
                            break;
                        case 2:
                            if (param1[0].indexOf("number") > -1) {
                                formattedValue = INTERMediatorLib.decimalFormat(curVal, param1[1], flags);
                            } else if (param1[0].indexOf("currency") > -1) {
                                formattedValue = INTERMediatorLib.currencyFormat(curVal, param1[1], flags);
                            } else if (param1[0].indexOf("percent") > -1) {
                                formattedValue = INTERMediatorLib.percentFormat(curVal, param1[1], flags);
                            }
                            break;
                        default:
                            if (param1[0].indexOf("number") > -1) {
                                formattedValue = INTERMediatorLib.decimalFormat(curVal, 0, flags);
                            } else if (param1[0].indexOf("currency") > -1) {
                                formattedValue = INTERMediatorLib.currencyFormat(curVal, 0, flags);
                            } else if (param1[0].indexOf("percent") > -1) {
                                formattedValue = INTERMediatorLib.percentFormat(curVal, 0, flags);
                            }
                            break;
                    }
                    break;
                }
            }
            if (formattedValue === null) {
                formattedValue = curVal;
                INTERMediator.setErrorMessage("The 'data-im-format' attribute is not valid: " + formatSpec);
            }
            curVal = formattedValue;
        }

        if (curTarget !== null && curTarget.length > 0) { //target is specified
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
                } else if (curTarget.indexOf("style.") === 0) {
                    styleName = curTarget.substring(6, curTarget.length);
                    if (curTarget !== "style.color" || 
                        (curTarget === "style.color" && !negativeColor)) {
                        element.style[styleName] = curVal;
                    }
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
                } else if (curTarget.indexOf("style.") === 0) {
                    styleName = curTarget.substring(6, curTarget.length);
                    if (curTarget !== "style.color" || 
                        (curTarget === "style.color" && !negativeColor)) {
                        element.style[styleName] = curVal;
                    }
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
                } else if (curTarget.indexOf("style.") === 0) {
                    styleName = curTarget.substring(6, curTarget.length);
                    if (curTarget !== "style.color" || 
                        (curTarget === "style.color" && !negativeColor)) {
                        element.style[styleName] = curVal;
                    }
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
                    curValues = curVal.toString().split("\n");
                    if (typeAttr == 'checkbox' && curValues.length > 1) {
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
                element.value = curVal;
            } else { // include option tag node
                if (INTERMediator.defaultTargetInnerHTML) {
                    if (INTERMediator.isIE && nodeTag == "TEXTAREA") {
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br/>");
                    }
                    element.innerHTML = curVal;
                } else {
                    if (nodeTag == "TEXTAREA") {
                        if (INTERMediator.isTrident && INTERMediator.ieVersion >= 11) {
                            // for IE11
                            curVal = curVal.replace(/\r\n/g, "\n").replace(/\r/g, "\n");
                        } else {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r");
                        }
                    }
                    textNode = document.createTextNode(curVal);
                    element.appendChild(textNode);
                }
            }
        }
        
        if (formatSpec && negativeColor) {
            negativeSign = INTERMediatorOnPage.localeInfo.negative_sign;
            negativeTailSign = "";
            if (flags.negativeStyle === 0 || flags.negativeStyle === 1) {
                negativeSign = "-";
            } else if (flags.negativeStyle === 2) {
                negativeSign = "(";
                negativeTailSign = ")";
            } else if (flags.negativeStyle === 3) {
                negativeSign = "<";
                negativeTailSign = ">";
            } else if (flags.negativeStyle === 4) {
                negativeSign = " CR";
            } else if (flags.negativeStyle === 5) {
                negativeSign = "▲";
            }
            
            if (flags.negativeStyle === 0 || flags.negativeStyle === 5) {
                if (curVal.indexOf(negativeSign) === 0) {
                    element.style.color = negativeColor;
                }
            } else if (flags.negativeStyle === 1 || flags.negativeStyle === 4) {
                if (curVal.indexOf(negativeSign) > -1 && 
                    curVal.indexOf(negativeSign) === curVal.length - negativeSign.length) {
                    element.style.color = negativeColor;
                }
            } else if (flags.negativeStyle === 2 || flags.negativeStyle === 3) {
                if (curVal.indexOf(negativeSign) === 0) {
                    if (curVal.indexOf(negativeTailSign) > -1 && 
                        curVal.indexOf(negativeTailSign) === curVal.length - 1) {
                        element.style.color = negativeColor;
                    }
                }
            }
        }

        return needPostValueSet;
    },

    getValueFromIMNode: function (element) {
        var nodeTag, typeAttr, newValue, dbspec, mergedValues, targetNodes, k, valueAttr;

        if (element) {
            nodeTag = element.tagName;
            typeAttr = element.getAttribute('type');
        } else {
            return "";
        }
        if (INTERMediatorLib.isWidgetElement(element) || 
            (INTERMediatorLib.isWidgetElement(element.parentNode))) {
            newValue = element._im_getValue();
        } else if (nodeTag == "INPUT") {
            if (typeAttr == 'checkbox') {
                dbspec = INTERMediatorOnPage.getDBSpecification();
                if (dbspec["db-class"] !== null && dbspec["db-class"] == "FileMaker_FX") {
                    mergedValues = [];
                    targetNodes = element.parentNode.getElementsByTagName('INPUT');
                    for (k = 0; k < targetNodes.length; k++) {
                        if (targetNodes[k].checked) {
                            mergedValues.push(targetNodes[k].getAttribute('value'));
                        }
                    }
                    newValue = mergedValues.join("\n");
                } else {
                    valueAttr = element.getAttribute('value');
                    if (element.checked) {
                        newValue = valueAttr;
                    } else {
                        newValue = '';
                    }
                }
            } else if (typeAttr == 'radio') {
                newValue = element.value;
            } else { //text, password
                newValue = element.value;
            }
        } else if (nodeTag == "SELECT") {
            newValue = element.value;
        } else if (nodeTag == "TEXTAREA") {
            newValue = element.value;
        } else {
            newValue = element.innerHTML;
        }
        return newValue;
    },

    checkOptimisticLock: function (element, target) {
        var linkInfo, nodeInfo, idValue, contextInfo, keyingComp, keyingField, keyingValue, checkQueryParameter,
            currentVal, response, targetField, targetContext, initialvalue, newValue, isOthersModified,
            isCheckResult, portalKey, portalIndex, currentFieldVal;

        if (!element) {
            return false;
        }
        linkInfo = INTERMediatorLib.getLinkedElementInfo(element);
        nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfo[0]);
        if (nodeInfo.table == IMLibLocalContext.contextName) {
            return false;
        }
        idValue = element.getAttribute('id');
        contextInfo = IMLibContextPool.getContextInfoFromId(idValue, target);   // suppose to target = ""
        if (INTERMediator.ignoreOptimisticLocking) {
            return true;
        }
        targetContext = contextInfo.context;
        targetField = contextInfo.field;
        keyingComp = contextInfo.record.split('=');
        keyingField = keyingComp[0];
        keyingComp.shift();
        keyingValue = keyingComp.join('=');
        checkQueryParameter = {
            name: contextInfo.context.contextName,
            records: 1,
            paging: false,
            fields: [targetField],
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
        if (contextInfo.portal) {
            isCheckResult = false;
            portalKey = contextInfo.context.contextName + "::-recid";
            if (currentVal.recordset && currentVal.recordset[0]) {
                for (portalIndex in currentVal.recordset[0]) {
                    var portalRecord = currentVal.recordset[0][portalIndex];
                    if (portalRecord[portalKey] && 
                        portalRecord[targetField] !== undefined && 
                        portalRecord[portalKey] == contextInfo.portal) {
                        currentFieldVal = portalRecord[targetField];
                        isCheckResult = true;
                    }
                }
            }
            if (!isCheckResult) {
                alert(INTERMediatorLib.getInsertedString(
                    INTERMediatorOnPage.getMessages()[1003], [targetField]));
                return false;
            }
        } else {
            if (currentVal.recordset === null || 
                currentVal.recordset[0] === null || 
                currentVal.recordset[0][targetField] === undefined) {
                alert(INTERMediatorLib.getInsertedString(
                    INTERMediatorOnPage.getMessages()[1003], [targetField]));
                return false;
            }
            if (currentVal.totalCount > 1) {
                response = confirm(INTERMediatorOnPage.getMessages()[1024]);
                if (!response) {
                    return false;
                }
            }
            currentFieldVal = currentVal.recordset[0][targetField];
        }
        initialvalue = targetContext.getValue(contextInfo.record, targetField, contextInfo.portal);

        switch (element.tagName) {
            case "INPUT":
                switch (element.getAttribute("type")) {
                    case "checkbox":
                        if (initialvalue == element.value) {
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

//        console.error(isOthersModified, initialvalue, newValue, currentFieldVal);

        if (isOthersModified) {
            // The value of database and the field is different. Others must be changed this field.
            newValue = IMLibElement.getValueFromIMNode(element);
            if (!confirm(INTERMediatorLib.getInsertedString(
                    INTERMediatorOnPage.getMessages()[1001], [initialvalue, newValue, currentFieldVal]))) {
                window.setTimeout(function () {
                    element.focus();
                }, 0);

                return false;
            }
            INTERMediatorOnPage.retrieveAuthInfo(); // This is required. Why?
        }
        return true;
    },

    deleteNodes: function (removeNodes) {
        var removeNode, removingNodes, i, j, k, removeNodeId, nodeId, calcObject, referes, values, key;

        for (key in removeNodes) {
            removeNode = document.getElementById(removeNodes[key]);
            removingNodes = INTERMediatorLib.getElementsByIMManaged(removeNode);
            if (removingNodes) {
                for (i = 0; i < removingNodes.length; i++) {
                    removeNodeId = removingNodes[i].id;
                    if (removeNodeId in IMLibCalc.calculateRequiredObject) {
                        delete IMLibCalc.calculateRequiredObject[removeNodeId];
                    }
                }
                for (i = 0; i < removingNodes.length; i++) {
                    removeNodeId = removingNodes[i].id;
                    for (nodeId in IMLibCalc.calculateRequiredObject) {
                        calcObject = IMLibCalc.calculateRequiredObject[nodeId];
                        referes = {};
                        values = {};
                        for (j in calcObject.referes) {
                            referes[j] = [];
                            values[j] = [];
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
    }
};
