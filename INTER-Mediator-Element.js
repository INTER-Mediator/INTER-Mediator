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
    setValueToIMNode: function (element, curTarget, curVal, clearField) {
        var styleName, currentValue, scriptNode, typeAttr, valueAttr, textNode,
            needPostValueSet = false, nodeTag, curValues, i, isReplaceOrAppned = false, imControl;
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
        imControl = element.getAttribute('data-im-control');

        if (clearField === true && curTarget == '') {
            switch (nodeTag) {
            case 'INPUT':
                switch (element.getAttribute('type')) {
                case 'text':
                    element.value = '';
                    break;
                default:
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

        if (curTarget != null && curTarget.length > 0) { //target is specified
            if (curTarget.charAt(0) == '#') { // Appending
                //if (element.getAttribute('data-im-element') !== 'processed') {
                curTarget = curTarget.substring(1);
                if (curTarget == 'innerHTML') {
                    if (INTERMediator.isIE && nodeTag == 'TEXTAREA') {
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br>");
                    }
                    element.innerHTML += curVal;
                } else if (curTarget == 'textNode' || curTarget == 'script') {
                    textNode = document.createTextNode(curVal);
                    if (nodeTag == 'TEXTAREA') {
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r");
                    }
                    element.appendChild(textNode);
                } else if (curTarget.indexOf('style.') == 0) {
                    styleName = curTarget.substring(6, curTarget.length);
                    element.style[styleName] = curVal;
                } else {
                    currentValue = element.getAttribute(curTarget);
                    if (curVal.indexOf('/fmi/xml/cnt/') === 0 && currentValue.indexOf('?media=') === -1) {
                        curVal = INTERMediatorOnPage.getEntryPath() + '?media=' + curVal;
                    }
                    element.setAttribute(curTarget, currentValue + curVal);
                }
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
                    styleName = curTarget.substring(6, curTarget.length);
                    element.style[styleName] = curVal;
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
                } else if (curTarget == 'innerHTML') { // Setting
                    if (INTERMediator.isIE && nodeTag == 'TEXTAREA') {
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br>");
                    }
                    element.innerHTML = curVal;
                } else if (curTarget == 'textNode') {
                    if (nodeTag == 'TEXTAREA') {
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r");
                    }
                    textNode = document.createTextNode(curVal);
                    element.appendChild(textNode);
                } else if (curTarget == 'script') {
                    textNode = document.createTextNode(curVal);
                    if (nodeTag == 'SCRIPT') {
                        element.appendChild(textNode);
                    } else {
                        scriptNode = document.createElement('script');
                        scriptNode.type = 'text/javascript';
                        scriptNode.appendChild(textNode);
                        element.appendChild(scriptNode);
                    }
                } else if (curTarget.indexOf('style.') == 0) {
                    styleName = curTarget.substring(6, curTarget.length);
                    element.style[styleName] = curVal;
                } else {
                    element.setAttribute(curTarget, curVal);
                }
            }
        } else { // if the 'target' is not specified.
            if (INTERMediatorLib.isWidgetElement(element)) {
                element._im_setValue(curVal);
            } else if (nodeTag == 'INPUT') {
                typeAttr = element.getAttribute('type');
                if (typeAttr == 'checkbox' || typeAttr == 'radio') { // set the value
                    valueAttr = element.value;
                    curValues = curVal.toString().split(IMLib.nl_char);
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
            } else if (nodeTag == 'SELECT') {
                needPostValueSet = true;
                element.value = curVal;
            } else if (nodeTag == 'TEXTAREA') {
                if (INTERMediator.defaultTargetInnerHTML) {
                    if (INTERMediator.isIE) { // for IE
                        curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br/>");
                    }
                    element.innerHTML = curVal;
                } else {
                    if (curVal.length > 0 && INTERMediator.isTrident && INTERMediator.ieVersion >= 11) { // for IE11
                        curVal = curVal.replace(/\r\n/g, IMLib.nl_char).replace(/\r/g, IMLib.nl_char);
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
        if (INTERMediatorLib.isWidgetElement(element)
            || (INTERMediatorLib.isWidgetElement(element.parentNode))) {
            newValue = element._im_getValue();
        } else if (nodeTag == 'INPUT') {
            if (typeAttr == 'checkbox') {
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
                                            if (removeNodeId != calcObject.referes[j][k]) {
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
