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

    formatters: {
        number: INTERMediatorLib.decimalFormat,
        currency: INTERMediatorLib.currencyFormat,
        boolean: INTERMediatorLib.booleanFormat,
        percent: INTERMediatorLib.percentFormat,
        date: INTERMediatorLib.dateFormat,
        datetime: INTERMediatorLib.datetimeFormat,
        time: INTERMediatorLib.timeFormat
    },

    unformatters: {
        number: INTERMediatorLib.convertNumeric,
        currency: INTERMediatorLib.convertNumeric,
        boolean: INTERMediatorLib.convertBoolean,
        percent: INTERMediatorLib.convertPercent,
        date: INTERMediatorLib.convertDate,
        datetime: INTERMediatorLib.convertDateTime,
        time: INTERMediatorLib.convertTime
    },

    formatOptions: {
        "useseparator": {useSeparator: true},
        "blankifzero": {blankIfZero: true}
    },
    formatNegativeStyle: {
        leadingminus: {negativeStyle: 0},
        "leading-minus": {negativeStyle: 0},
        trailingminus: {negativeStyle: 1},
        "trailing-minus": {negativeStyle: 1},
        parenthesis: {negativeStyle: 2},
        angle: {negativeStyle: 3},
        credit: {negativeStyle: 4},
        triangle: {negativeStyle: 5}
    },
    formatNumeralType: {
        "half-width": {charStyle: 0},
        "full-width": {charStyle: 1},
        "kanji-numeral-modern": {charStyle: 2},
        "kanji-numeral": {charStyle: 3}
    },
    formatKanjiSeparator: {
        "every-4th-place": {kanjiSeparator: 1, useSeparator: true},
        "full-notation": {kanjiSeparator: 2, useSeparator: true}
    },

    appendObject: function (obj, adding) {
        var result = obj;
        if (adding) {
            for (var key in adding) {
                if (adding.hasOwnProperty(key)) {
                    result[key] = adding[key];
                }
            }
        }
        return result;
    },

// Formatting values
//
    getFormattedValue: function (element, curVal) {
        var flags, formatSpec, formatOption, negativeStyle, charStyle,
            kanjiSeparator, formattedValue = null, params, formatFunc;

        formatSpec = element.getAttribute("data-im-format");
        if (!formatSpec) {
            return null;
        }
        flags = {
            useSeparator: false,
            blankIfZero: false,
            negativeStyle: 0,
            charStyle: 0,
            kanjiSeparator: 0
        };
        formatOption = element.getAttribute("data-im-format-options");
        flags = IMLibElement.appendObject(flags, IMLibElement.formatOptions[formatOption]);
        negativeStyle = element.getAttribute("data-im-format-negative-style");
        flags = IMLibElement.appendObject(flags, IMLibElement.formatNegativeStyle[negativeStyle]);
        charStyle = element.getAttribute("data-im-format-numeral-type");
        flags = IMLibElement.appendObject(flags, IMLibElement.formatNumeralType[charStyle]);
        kanjiSeparator = element.getAttribute("data-im-format-kanji-separator");
        flags = IMLibElement.appendObject(flags, IMLibElement.formatKanjiSeparator[kanjiSeparator]);
        params = 0;
        formatFunc = IMLibElement.formatters[formatSpec.trim().toLocaleLowerCase()];  // in case of no parameters in attribute
        if (!formatFunc) {
            parsed = formatSpec.match(/[^a-zA-Z]*([a-zA-Z]+).*[\(]([^\(]*)[\)]/);
            formatFunc = IMLibElement.formatters[parsed[1].toLocaleLowerCase()];
            params = parsed[2];
            if (parsed[2].length === 0) { // in case of parameter is just ().
                params = 0
            }
        }
        if (formatFunc) {
            formattedValue = formatFunc(curVal, params, flags);
        }
        return formattedValue;
    },

    getUnformattedValue: function (element, value) {
        var formatSpec, unformatFunc, parsed, params, convertedValue,flags = undefined;
        formatSpec = element.getAttribute("data-im-format");
        if (!formatSpec) {
            return null;
        }
        unformatFunc = IMLibElement.unformatters[formatSpec.trim().toLocaleLowerCase()];  // in case of no parameters in attribute
        if (!unformatFunc) {
            parsed = formatSpec.match(/[^a-zA-Z]*([a-zA-Z]+).*[\(]([^\(]*)[\)]/);
            unformatFunc = IMLibElement.unformatters[parsed[1].toLocaleLowerCase()];
            params = parsed[2];
            if (parsed[2].length === 0) { // in case of parameter is just ().
                params = 0
            }
        }
        if (unformatFunc) {
            convertedValue = unformatFunc(value, params, flags);
        }
        return convertedValue;

    },

    setValueToIMNode: function (element, curTarget, curVal, clearField) {
        var styleName, currentValue, scriptNode, typeAttr, valueAttr, textNode, formatSpec, formattedValue,
            needPostValueSet = false, curValues, i, isReplaceOrAppend = false, imControl;

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
        if (typeof curVal === 'object' && curVal.constructor === Array && curVal.length > 0) {
            curVal = curVal[0];
        }

        imControl = element.getAttribute('data-im-control');

        if (clearField && curTarget === '') {
            switch (element.tagName) {
                case 'INPUT':
                    switch (element.getAttribute('type')) {
                        case 'text':
                            element.value = '';
                            break;
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
            }
        }
        formattedValue = IMLibElement.getFormattedValue(element, curVal);
        if (element.getAttribute("data-im-format")) {
            if (formattedValue === null) {
                INTERMediator.setErrorMessage(
                    "The 'data-im-format' attribute is not valid: " + formatSpec);
            } else {
                curVal = formattedValue;
            }
        }

        curVal = String(curVal);
        negativeColor = element.getAttribute("data-im-format-negative-color");
        if (curTarget !== null && curTarget.length > 0) { //target is specified
            if (curTarget.charAt(0) === '#') { // Appending
                curTarget = curTarget.substring(1);
                originalValue = element.getAttribute("data-im-original-" + curTarget);
                if (curTarget === 'innerHTML') {
                    currentValue = originalValue ? originalValue : element.innerHTML;
                    element.innerHTML = currentValue + curVal;
                } else if (curTarget === "textNode" || curTarget === "script") {
                    currentValue = originalValue ? originalValue : element.textContent;
                    element.textContent = currentValue + curVal;
                } else if (curTarget.indexOf("style.") === 0) {
                    styleName = curTarget.substring(6, curTarget.length);
                    currentValue = originalValue ? originalValue : element.style[styleName];
                    if (curTarget !== "style.color" ||
                        (curTarget === "style.color" && !negativeColor)) {
                        element.style[styleName] = currentValue + curVal;
                    }
                } else {
                    currentValue = originalValue ? originalValue : element.getAttribute(curTarget);
                    if (curVal.indexOf('/fmi/xml/cnt/') === 0 && currentValue.indexOf('?media=') === -1) {
                        curVal = INTERMediatorOnPage.getEntryPath() + '?media=' + curVal;
                    }
                    element.setAttribute(curTarget, currentValue + curVal);
                }
                isReplaceOrAppend = true;
                if (!originalValue) {
                    element.setAttribute("data-im-original-" + curTarget, currentValue);
                }
            } else if (curTarget.charAt(0) === '$') { // Replacing
                curTarget = curTarget.substring(1);
                originalValue = element.getAttribute("data-im-original-" + curTarget);
                if (curTarget === 'innerHTML') {
                    currentValue = originalValue ? originalValue : element.innerHTML;
                    curVal = currentValue.replace('$', curVal);
                    if (INTERMediator.isIE && INTERMediator.ieVersion < 10) { // for IE
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br/>");
                    }
                    element.innerHTML = curVal
                } else if (curTarget === 'textNode' || curTarget === 'script') {
                    currentValue = originalValue ? originalValue : element.textContent;
                    element.textContent = currentValue.replace('$', curVal);
                } else if (curTarget.indexOf('style.') === 0) {
                    styleName = curTarget.substring(6, curTarget.length);
                    currentValue = originalValue ? originalValue : element.style[styleName];
                    if (curTarget !== "style.color" ||
                        (curTarget === "style.color" && !negativeColor)) {
                        element.style[styleName] = currentValue.replace('$', curVal);
                    }
                } else {
                    currentValue = originalValue ? originalValue : element.getAttribute(curTarget);
                    if (curVal.indexOf('/fmi/xml/cnt/') === 0 && currentValue.indexOf('?media=') === -1) {
                        curVal = INTERMediatorOnPage.getEntryPath() + '?media=' + curVal;
                    }
                    element.setAttribute(curTarget, currentValue.replace('$', curVal));
                }
                isReplaceOrAppend = true;
                if (!originalValue) {
                    element.setAttribute("data-im-original-" + curTarget, currentValue);
                }
            } else { // Setting
                if (INTERMediatorLib.isWidgetElement(element)) {
                    element._im_setValue(curVal);
                } else if (curTarget === 'innerHTML') { // Setting
                    if (INTERMediator.isIE && INTERMediator.ieVersion < 10) { // for IE
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br/>");
                    }
                    element.innerHTML = curVal;
                } else if (curTarget === 'textNode') {
                    textNode = document.createTextNode(curVal);
                    element.appendChild(textNode);
                } else if (curTarget === "script") {
                    textNode = document.createTextNode(curVal);
                    if (element.tagName === 'SCRIPT') {
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
                    if (INTERMediator.isIE && INTERMediator.ieVersion < 10 && element.tagName === 'TEXTAREA') { // for IE
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br/>");
                    }
                    element.setAttribute(curTarget, curVal);
                }
            }
        } else { // if the 'target' is not specified.
            if (INTERMediatorLib.isWidgetElement(element)) {
                element._im_setValue(curVal);
            } else if (element.tagName === 'INPUT') {
                typeAttr = element.getAttribute('type');
                if (typeAttr === 'checkbox' || typeAttr === 'radio') { // set the value
                    valueAttr = element.value;
                    curValues = curVal.split(IMLib.nl_char);
                    if (typeAttr === 'checkbox' && curValues.length > 1) {
                        for (i = 0; i < curValues.length; i++) {
                            if (valueAttr == curValues[i] && !INTERMediator.dontSelectRadioCheck) {
                                // The above operator shuold be "==" not "==="
                                if (INTERMediator.isIE) {
                                    element.setAttribute("checked", "checked");
                                } else {
                                    element.checked = true;
                                }
                            }
                        }
                    } else {
                        if (valueAttr == curVal && !INTERMediator.dontSelectRadioCheck) {
                            // The above operator shuold be "==" not "==="
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
            } else if (element.tagName === 'SELECT') {
                needPostValueSet = true;
                element.value = curVal;
            } else if (element.tagName === 'TEXTAREA') {
                if (INTERMediator.defaultTargetInnerHTML) {
                    if (INTERMediator.isIE && INTERMediator.ieVersion < 10) { // for IE
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br/>");
                    }
                    element.innerHTML = curVal;
                } else {
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
        if ((element.tagName === 'INPUT' || element.tagName === 'SELECT' || element.tagName === 'TEXTAREA')
            && !isReplaceOrAppend
            && (!imControl || imControl.indexOf('unbind') > 0 )) {
            if (!element.getAttribute("data-imbluradded")) {
                IMLibBlurEventDispatch.setExecute(element.id, (function () {
                    var idValue = element.id;
                    var elementCapt = element;
                    return function (event) {
                        if (!IMLibUI.valueChange(idValue, true)) {
                            elementCapt.focus();
                        }
                    }
                })());
                element.setAttribute("data-imbluradded", "set");
            }
            if (!element.getAttribute("data-imchangeadded")) {
                IMLibChangeEventDispatch.setExecute(element.id, (function () {
                    var idValue = element.id;
                    var elementCapt = element;
                    return function (event) {
                        if (!IMLibUI.valueChange(idValue, false)) {
                            elementCapt.focus();
                        }
                    }
                })());
                element.setAttribute("data-imchangeadded", "set");
            }
            if ((INTERMediator.isTrident || INTERMediator.isEdge) && !element.getAttribute("data-iminputadded")) {
                IMLibInputEventDispatch.setExecute(element.id, (function () {
                    var idValue = element.id;
                    var elementCapt = element;
                    return function (event) {
                        if (document.getElementById(idValue).value === '') {
                            if (!IMLibUI.valueChange(idValue, false)) {
                                elementCapt.focus();
                            }
                        }
                    }
                })());
                element.setAttribute("data-iminputadded", "set");
            }
        }
        element.setAttribute('data-im-element', 'processed');
        return needPostValueSet;
    },

    getValueFromIMNode: function (element) {
        var nodeTag, typeAttr, newValue, mergedValues, targetNodes, k, valueAttr, formatSpec, convertedValue;

        if (element) {
            nodeTag = element.tagName;
            typeAttr = element.getAttribute('type');
        } else {
            return '';
        }

        if (INTERMediatorLib.isWidgetElement(element) ||
            (INTERMediatorLib.isWidgetElement(element.parentNode))) {
            newValue = element._im_getValue();
        } else if (nodeTag === 'INPUT') {
            if (typeAttr === 'checkbox') {
                if (INTERMediatorOnPage.dbClassName === 'DB_FileMaker_FX') {
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
            } else if (typeAttr === 'radio') {
                newValue = element.value;
            } else { //text, password
                newValue = element.value;
            }
        } else if (nodeTag === 'SELECT') {
            newValue = element.value;
        } else if (nodeTag === 'TEXTAREA') {
            if (INTERMediator.isIE && INTERMediator.ieVersion < 10) { // for IE
                newValue = element.innerHTML.replace(/<br[\/]{0,1}>/g, "\n");
            } else {
                newValue = element.value;
            }
        } else {
            newValue = element.innerHTML;
        }
        convertedValue = IMLibElement.getUnformattedValue(element, newValue);
        newValue = convertedValue ? convertedValue : newValue;
        // formatSpec = element.getAttribute("data-im-format");
        // if (formatSpec) {
        //     newValue = newValue.replace(new RegExp(INTERMediatorOnPage.localeInfo.mon_thousands_sep, "g"), "");
        //     newValue = INTERMediatorLib.normalizeNumerics(newValue);
        //     if (newValue !== "") {
        //         newValue = parseFloat(newValue);
        //     }
        // }
        return newValue;
    },

    /*
     <<Multiple lines in TEXTAREA before IE 10>> 2017-08-05, Masayuki Nii

     Most of modern browsers can handle the 'next line(\n)' character as the line separator.
     Otherwise IE 9 requires special handling for multiple line strings.

     - If such a strings sets to value property, it shows just a single line.
     - To prevent the above situation, it has to replace the line sparating characters to <br>,
     and set it to innerHTML property.
     - The value property of multi-line strings doesn't contain any line sparating characters.
     - The innerHTML property of multi-line strings contains <br> for line sparators.
     - If the value of TEXTAREA can be get with repaceing <br> to \n from the innerHTML property.

     */

    deleteNodes: function (removeNodes) {
        var removeNode, removingNodes, i, j, k, removeNodeId, nodeId, calcObject, referes, values, key;

        for (key = 0; key < removeNodes.length; key++) {
            removeNode = document.getElementById(removeNodes[key]);
            if (removeNode) {
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
                                            if (removeNodeId !== calcObject.referes[j][k]) {
                                                referes[j].push(calcObject.referes[j][k]);
                                                values[j].push(calcObject.values[j][k]);
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
                } catch (ex) {
                    // Avoid an error for Safari
                }
            }
        }
    }
};
