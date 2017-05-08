/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

/**
 * @fileoverview IMLibElement class is defined here.
 */
/**
 *
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
var IMLibElement = {
    patterns: [
        /^number\(([0-9]+)\)/,
        /^number[\(\)]*/,
        /^currency\(([0-9]+)\)/,
        /^currency[\(\)]*/,
        /^boolean\([\"|']([\S]+)[\"|'],[\s]*[\"|']([\S]+)[\"|']\)/,
        /^percent\(([0-9]+)\)/,
        /^percent[\(\)]*/
    ],

    setValueToIMNode: function (element, curTarget, curVal, clearField) {
<<<<<<< HEAD
        "use strict";
        var styleName, currentValue, scriptNode, typeAttr, valueAttr, textNode,
            needPostValueSet = false, nodeTag, curValues, i, formattedValue = null,
            formatSpec, flags = {}, formatOption, negativeColor, negativeStyle, charStyle,
            kanjiSeparator, param1, negativeSign, negativeTailSign;
=======
        var styleName, currentValue, scriptNode, typeAttr, valueAttr, textNode,
            needPostValueSet = false, nodeTag, curValues, i, isReplaceOrAppned = false, imControl;
>>>>>>> INTER-Mediator/master
        // IE should \r for textNode and <br> for innerHTML, Others is not required to convert

        if (curVal === undefined) {
            return false;   // Or should be an error?
        }
        if (!element) {
            return false;   // Or should be an error?
        }
        if (curVal === null || curVal === false) {
            curVal = "";
        }

        nodeTag = element.tagName;
        imControl = element.getAttribute('data-im-control');

<<<<<<< HEAD
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
=======
        if (clearField === true && curTarget == '') {
            switch (nodeTag) {
            case 'INPUT':
                switch (element.getAttribute('type')) {
                case 'text':
                    element.value = '';
>>>>>>> INTER-Mediator/master
                    break;
                default:
                    break;
<<<<<<< HEAD
            }
        }

        formatSpec = element.getAttribute("data-im-format");
        if (formatSpec) {
            flags = {
                useSeparator: false,
                blankIfZero: false,
                negativeStyle: 0,
                charStyle: 0,
                kanjiSeparator: 0
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
            charStyle = element.getAttribute("data-im-format-numeral-type");
            if (charStyle) {
                if (charStyle.toLowerCase() === "half-width") {
                    flags.charStyle = 0;
                } else if (charStyle.toLowerCase() === "full-width") {
                    flags.charStyle = 1;
                } else if (charStyle.toLowerCase() === "kanji-numeral-modern") {
                    flags.charStyle = 2;
                } else if (charStyle.toLowerCase() === "kanji-numeral") {
                    flags.charStyle = 3;
                }
            }
            kanjiSeparator = element.getAttribute("data-im-format-kanji-separator");
            if (kanjiSeparator) {
                if (kanjiSeparator.toLowerCase() === "every-4th-place") {
                    flags.kanjiSeparator = 1;
                } else if (kanjiSeparator.toLowerCase() === "full-notation") {
                    flags.kanjiSeparator = 2;
                }
                if (flags.kanjiSeparator > 0) {
                    flags.useSeparator = true;
                }
            }
            for (i = 0; i < IMLibElement.patterns.length; i++) {
                param1 = formatSpec.match(IMLibElement.patterns[i]);
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
=======
                }
                break;
            case 'SELECT':
                break;
            default:
                while (element.childNodes.length > 0) {
                    if (element.parentNode.getAttribute('data-im-element') === 'processed' ||
                        INTERMediatorLib.isWidgetElement(element.parentNode)) {
                        // for data-im-widget
                        return false;
                    }
                    element.removeChild(element.childNodes[0]);
                }
                break;
>>>>>>> INTER-Mediator/master
            }
        }

<<<<<<< HEAD
        if (curTarget !== null && curTarget.length > 0) { //target is specified
            if (curTarget.charAt(0) === "#") { // Appending
                curTarget = curTarget.substring(1);
                if (curTarget === "innerHTML") {
                    if (INTERMediator.isIE && nodeTag === "TEXTAREA") {
=======
        if (curTarget != null && curTarget.length > 0) { //target is specified
            if (curTarget.charAt(0) == '#') { // Appending
                //if (element.getAttribute('data-im-element') !== 'processed') {
                curTarget = curTarget.substring(1);
                if (curTarget == 'innerHTML') {
                    if (INTERMediator.isIE && nodeTag == 'TEXTAREA') {
>>>>>>> INTER-Mediator/master
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br>");
                    }
                    element.innerHTML += curVal;
                } else if (curTarget === "textNode" || curTarget === "script") {
                    textNode = document.createTextNode(curVal);
<<<<<<< HEAD
                    if (nodeTag === "TEXTAREA") {
=======
                    if (nodeTag == 'TEXTAREA') {
>>>>>>> INTER-Mediator/master
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
                    if (curVal.indexOf('/fmi/xml/cnt/') === 0 && currentValue.indexOf('?media=') === -1) {
                        curVal = INTERMediatorOnPage.getEntryPath() + '?media=' + curVal;
                    }
                    element.setAttribute(curTarget, currentValue + curVal);
                }
<<<<<<< HEAD
            }
            else if (curTarget.charAt(0) === "$") { // Replacing
                curTarget = curTarget.substring(1);
                if (curTarget === "innerHTML") {
                    if (INTERMediator.isIE && nodeTag === "TEXTAREA") {
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br>");
                    }
                    element.innerHTML = element.innerHTML.replace("$", curVal);
                } else if (curTarget === "textNode" || curTarget === "script") {
                    if (nodeTag === "TEXTAREA") {
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r");
                    }
                    element.innerHTML = element.innerHTML.replace("$", curVal);
                } else if (curTarget.indexOf("style.") === 0) {
=======
                isReplaceOrAppned = true;
                //}
            } else if (curTarget.charAt(0) == '$') { // Replacing
                curTarget = curTarget.substring(1);
                if (curTarget == 'innerHTML') {
                    if (INTERMediator.isIE && nodeTag == 'TEXTAREA') {
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br>");
                    }
                    element.innerHTML = element.innerHTML.replace('$', curVal);
                } else if (curTarget == 'textNode' || curTarget == 'script') {
                    if (nodeTag == 'TEXTAREA') {
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r");
                    }
                    element.innerHTML = element.innerHTML.replace('$', curVal);
                } else if (curTarget.indexOf('style.') == 0) {
>>>>>>> INTER-Mediator/master
                    styleName = curTarget.substring(6, curTarget.length);
                    if (curTarget !== "style.color" ||
                        (curTarget === "style.color" && !negativeColor)) {
                        element.style[styleName] = curVal;
                    }
                } else {
                    currentValue = element.getAttribute(curTarget);
                    curVal = String(curVal);
                    if (curVal.indexOf('/fmi/xml/cnt/') === 0 && currentValue.indexOf('?media=') === -1) {
                        curVal = INTERMediatorOnPage.getEntryPath() + '?media=' + curVal;
                    }
                    element.setAttribute(curTarget, currentValue.replace('$', curVal));
                }
                isReplaceOrAppned = true;
            } else { // Setting
                if (INTERMediatorLib.isWidgetElement(element)) {
                    element._im_setValue(curVal);
<<<<<<< HEAD
                } else if (curTarget === "innerHTML") { // Setting
                    if (INTERMediator.isIE && nodeTag === "TEXTAREA") {
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br>");
                    }
                    element.innerHTML = curVal;
                } else if (curTarget === "textNode") {
                    if (nodeTag === "TEXTAREA") {
=======
                } else if (curTarget == 'innerHTML') { // Setting
                    if (INTERMediator.isIE && nodeTag == 'TEXTAREA') {
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br>");
                    }
                    element.innerHTML = curVal;
                } else if (curTarget == 'textNode') {
                    if (nodeTag == 'TEXTAREA') {
>>>>>>> INTER-Mediator/master
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r");
                    }
                    textNode = document.createTextNode(curVal);
                    element.appendChild(textNode);
                } else if (curTarget === "script") {
                    textNode = document.createTextNode(curVal);
<<<<<<< HEAD
                    if (nodeTag === "SCRIPT") {
=======
                    if (nodeTag == 'SCRIPT') {
>>>>>>> INTER-Mediator/master
                        element.appendChild(textNode);
                    } else {
                        scriptNode = document.createElement('script');
                        scriptNode.type = 'text/javascript';
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
<<<<<<< HEAD
            } else if (nodeTag === "INPUT") {
                typeAttr = element.getAttribute("type");
                if (typeAttr === "checkbox" || typeAttr === "radio") { // set the value
                    valueAttr = element.value;
                    curValues = curVal.toString().split("\n");
                    if (typeAttr === "checkbox" && curValues.length > 1) {
=======
            } else if (nodeTag == 'INPUT') {
                typeAttr = element.getAttribute('type');
                if (typeAttr == 'checkbox' || typeAttr == 'radio') { // set the value
                    valueAttr = element.value;
                    curValues = curVal.toString().split(IMLib.nl_char);
                    if (typeAttr == 'checkbox' && curValues.length > 1) {
>>>>>>> INTER-Mediator/master
                        for (i = 0; i < curValues.length; i++) {
                            if (valueAttr === curValues[i] && !INTERMediator.dontSelectRadioCheck) {
                                if (INTERMediator.isIE) {
                                    element.setAttribute("checked", "checked");
                                } else {
                                    element.checked = true;
                                }
                            }
                        }
                    } else {
                        if (valueAttr === curVal && !INTERMediator.dontSelectRadioCheck) {
                            if (INTERMediator.isIE) {
                                element.setAttribute("checked", "checked");
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
<<<<<<< HEAD
            } else if (nodeTag === "SELECT") {
=======
            } else if (nodeTag == 'SELECT') {
>>>>>>> INTER-Mediator/master
                needPostValueSet = true;
                element.value = curVal;
            } else if (nodeTag == 'TEXTAREA') {
                if (INTERMediator.defaultTargetInnerHTML) {
<<<<<<< HEAD
                    if (INTERMediator.isIE && nodeTag === "TEXTAREA") {
=======
                    if (INTERMediator.isIE) { // for IE
>>>>>>> INTER-Mediator/master
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br/>");
                    }
                    element.innerHTML = curVal;
                } else {
<<<<<<< HEAD
                    if (nodeTag === "TEXTAREA") {
                        if (INTERMediator.isTrident && INTERMediator.ieVersion >= 11) {
                            // for IE11
                            curVal = curVal.replace(/\r\n/g, "\n").replace(/\r/g, "\n");
                        } else {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r");
                        }
=======
                    if (curVal.length > 0 && INTERMediator.isTrident && INTERMediator.ieVersion >= 11) { // for IE11
                        curVal = curVal.replace(/\r\n/g, IMLib.nl_char).replace(/\r/g, IMLib.nl_char);
>>>>>>> INTER-Mediator/master
                    }
                    element.value = curVal;
                }
            } else { // include option tag node
                if (INTERMediator.defaultTargetInnerHTML) {
                    element.innerHTML = curVal;
                } else {
                    element.appendChild(document.createTextNode(curVal));
                }
            }
        }
<<<<<<< HEAD

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

=======
        if ((nodeTag === 'INPUT' || nodeTag === 'SELECT' || nodeTag === 'TEXTAREA')
            && !isReplaceOrAppned
            && (!imControl || imControl.indexOf('unbind') > 0 )) {
            var idValue = element.id;
            var elementCapt = element;
            INTERMediatorLib.addEvent(element, 'blur', function (event) {
                if (!IMLibUI.valueChange(idValue, true) && this.id === idValue) {
                    elementCapt.focus();
                }
            });
        }
        element.setAttribute('data-im-element', 'processed');
>>>>>>> INTER-Mediator/master
        return needPostValueSet;
    },

    getValueFromIMNode: function (element) {
        var nodeTag, typeAttr, newValue, mergedValues, targetNodes, k, valueAttr;

        if (element) {
            nodeTag = element.tagName;
            typeAttr = element.getAttribute('type');
        } else {
            return '';
        }
        if (INTERMediatorLib.isWidgetElement(element) ||
            (INTERMediatorLib.isWidgetElement(element.parentNode))) {
            newValue = element._im_getValue();
        } else if (nodeTag == 'INPUT') {
            if (typeAttr == 'checkbox') {
<<<<<<< HEAD
                dbspec = INTERMediatorOnPage.getDBSpecification();
                if (dbspec["db-class"] !== null && dbspec["db-class"] == "FileMaker_FX") {
=======
                if (INTERMediatorOnPage.dbClassName === 'DB_FileMaker_FX') {
>>>>>>> INTER-Mediator/master
                    mergedValues = [];
                    targetNodes = element.parentNode.getElementsByTagName('INPUT');
                    for (k = 0; k < targetNodes.length; k++) {
                        if (targetNodes[k].checked) {
                            mergedValues.push(targetNodes[k].getAttribute('value'));
                        }
                    }
                    newValue = mergedValues.join(IMLib.nl_char);
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
        } else if (nodeTag == 'SELECT') {
            newValue = element.value;
        } else if (nodeTag == 'TEXTAREA') {
            newValue = element.value;
        } else {
            newValue = element.innerHTML;
        }
        return newValue;
    },

<<<<<<< HEAD
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
                    if (currentVal.recordset[0].hasOwnProperty(portalIndex)) {
                        var portalRecord = currentVal.recordset[0][portalIndex];
                        if (portalRecord[portalKey] &&
                            portalRecord[targetField] !== undefined &&
                            portalRecord[portalKey] == contextInfo.portal) {
                            currentFieldVal = portalRecord[targetField];
                            isCheckResult = true;
                        }
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
                        } else {
                            isOthersModified = !!parseInt(currentFieldVal);
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
            if (removeNodes.hasOwnProperty(key)) {
                removeNode = document.getElementById(removeNodes[key]);
=======
    deleteNodes: function (removeNodes) {
        var removeNode, removingNodes, i, j, k, removeNodeId, nodeId, calcObject, referes, values, key;

        for (key = 0; key < removeNodes.length; key++) {
            removeNode = document.getElementById(removeNodes[key]);
            if (removeNode) {
>>>>>>> INTER-Mediator/master
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
                            if (IMLibCalc.calculateRequiredObject.hasOwnProperty(nodeId)) {
                                calcObject = IMLibCalc.calculateRequiredObject[nodeId];
                                referes = {};
                                values = {};
                                for (j in calcObject.referes) {
                                    if (calcObject.referes.hasOwnProperty(j)) {
                                        referes[j] = [];
                                        values[j] = [];
                                        for (k = 0; k < calcObject.referes[j].length; k++) {
<<<<<<< HEAD
                                            if (calcObject.referes.hasOwnProperty(j)) {
                                                if (removeNodeId != calcObject.referes[j][k]) {
                                                    referes[j].push(calcObject.referes[j][k]);
                                                    values[j].push(calcObject.values[j][k]);
                                                }
=======
                                            if (removeNodeId != calcObject.referes[j][k]) {
                                                referes[j].push(calcObject.referes[j][k]);
                                                values[j].push(calcObject.values[j][k]);
>>>>>>> INTER-Mediator/master
                                            }
                                        }
                                    }
                                }
                                calcObject.referes = referes;
                                calcObject.values = values;
                            }
                        }
                    }
                }
                try {
                    removeNode.parentNode.removeChild(removeNode);
<<<<<<< HEAD
                } catch
                    (ex) {
=======
                } catch (ex) {
>>>>>>> INTER-Mediator/master
                    // Avoid an error for Safari
                }
            }
        }
    }
};
