/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

//'use strict';
/**
 * @fileoverview IMLib and INTERMediatorLib classes are defined here.
 */
/**
 *
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
var IMLib = {
    nl_char: '\n',
    cr_char: '\r',
    tab_char: '\t',
    singleQuote_char: '\'',
    doubleQuote_char: '"',
    backSlash_char: '\\',

    get zerolength_str() {
        return '';
    },
    set zerolength_str(value) {
        // do nothing
    },

    get crlf_str() {
        return '\r\n';
    },
    set crlf_str(value) {
        // do nothing
    }
};

/**
 *
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
var INTERMediatorLib = {

    ignoreEnclosureRepeaterClassName: '_im_ignore_enc_rep',
    ignoreEnclosureRepeaterControlName: 'ignore_enc_rep',
    roleAsRepeaterClassName: '_im_repeater',
    roleAsEnclosureClassName: '_im_enclosure',
    roleAsRepeaterDataControlName: 'repeater',
    roleAsEnclosureDataControlName: 'enclosure',
    roleAsSeparatorDataControlName: 'separator',
    roleAsHeaderDataControlName: 'header',
    roleAsFooterDataControlName: 'footer',
    roleAsNoResultDataControlName: 'noresult',

    initialize: function () {
        IMLibLocalContext.unarchive();
        return null;
    },

    setup: function () {
        if (window.addEventListener) {
            window.addEventListener('load', this.initialize, false);
        } else if (window.attachEvent) { // for IE
            window.attachEvent('onload', this.initialize);
        } else {
            window.onload = this.initialize;
        }

        return null;
    },

    generatePasswordHash: function (password) {
        var numToHex, salt, saltHex, code, lowCode, highCode, i;
        numToHex = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F'];
        salt = '';
        saltHex = '';
        for (i = 0; i < 4; i++) {
            code = Math.floor(Math.random() * (128 - 32) + 32);
            lowCode = code & 0xF;
            highCode = (code >> 4) & 0xF;
            salt += String.fromCharCode(code);
            saltHex += numToHex[highCode] + numToHex[lowCode];
        }
        return encodeURIComponent(SHA1(password + salt) + saltHex);
    },

    getParentRepeater: function (node) {
        var currentNode = node;
        while (currentNode !== null) {
            if (INTERMediatorLib.isRepeater(currentNode, true)) {
                return currentNode;
            }
            currentNode = currentNode.parentNode;
        }
        return null;
    },

    getParentEnclosure: function (node) {
        var currentNode = node;
        while (currentNode !== null) {
            if (INTERMediatorLib.isEnclosure(currentNode, true)) {
                return currentNode;
            }
            currentNode = currentNode.parentNode;
        }
        return null;
    },

    isEnclosure: function (node, nodeOnly) {
        var tagName, className, children, k, controlAttr;

        if (!node || node.nodeType !== 1) {
            return false;
        }
        className = INTERMediatorLib.getClassAttributeFromNode(node);
        if (className && className.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterClassName) >= 0) {
            return false;
        }
        controlAttr = node.getAttribute('data-im-control');
        if (controlAttr && controlAttr.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterControlName) >= 0) {
            return false;
        }
        tagName = node.tagName;
        if ((tagName === 'TBODY') ||
            (tagName === 'UL') ||
            (tagName === 'OL') ||
            (tagName === 'SELECT') ||
            ((tagName === 'DIV' || tagName === 'SPAN') &&
            className &&
            className.indexOf(INTERMediatorLib.roleAsEnclosureClassName) >= 0) ||
            (controlAttr &&
            controlAttr.indexOf(INTERMediatorLib.roleAsEnclosureDataControlName) >= 0)) {
            if (nodeOnly) {
                return true;
            } else {
                children = node.childNodes;
                for (k = 0; k < children.length; k++) {
                    if (INTERMediatorLib.isRepeater(children[k], true)) {
                        return true;
                    }
                }
            }
        }
        return false;
    },

    isRepeater: function (node, nodeOnly) {
        var tagName, className, children, k, controlAttr;

        if (!node || node.nodeType !== 1) {
            return false;
        }
        className = INTERMediatorLib.getClassAttributeFromNode(node);
        if (className && className.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterClassName) >= 0) {
            return false;
        }
        controlAttr = node.getAttribute('data-im-control');
        if (controlAttr && controlAttr.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterControlName) >= 0) {
            return false;
        }
        tagName = node.tagName;
        if ((tagName === 'TR') || (tagName === 'LI') || (tagName === 'OPTION') ||
            (className && className.indexOf(INTERMediatorLib.roleAsRepeaterClassName) >= 0) ||
            (controlAttr && controlAttr.indexOf(INTERMediatorLib.roleAsRepeaterDataControlName) >= 0) ||
            (controlAttr && controlAttr.indexOf(INTERMediatorLib.roleAsSeparatorDataControlName) >= 0) ||
            (controlAttr && controlAttr.indexOf(INTERMediatorLib.roleAsFooterDataControlName) >= 0) ||
            (controlAttr && controlAttr.indexOf(INTERMediatorLib.roleAsHeaderDataControlName) >= 0) ||
            (controlAttr && controlAttr.indexOf(INTERMediatorLib.roleAsNoResultDataControlName) >= 0)
        ) {
            if (nodeOnly) {
                return true;
            } else {
                return searchLinkedElement(node);
            }
        }
        return false;

        function searchLinkedElement(node) {
            if (INTERMediatorLib.isLinkedElement(node)) {
                return true;
            }
            children = node.childNodes;
            for (k = 0; k < children.length; k++) {
                if (children[k].nodeType === 1) { // Work for an element
                    if (INTERMediatorLib.isLinkedElement(children[k])) {
                        return true;
                    } else if (searchLinkedElement(children[k])) {
                        return true;
                    }
                }
            }
            return false;
        }
    },


    /**
     * Cheking the argument is the Linked Element or not.
     */

    isLinkedElement: function (node) {
        var classInfo, matched, attr;

        if (node !== null && node.getAttribute) {
            attr = node.getAttribute('data-im');
            if (attr) {
                return true;
            }
            if (INTERMediator.titleAsLinkInfo) {
                if (node.getAttribute('TITLE') !== null && node.getAttribute('TITLE').length > 0) {
                    // IE: If the node doesn't have a title attribute, getAttribute
                    // doesn't return null.
                    // So it requrired check if it's empty string.
                    return true;
                }
            }
            if (INTERMediator.classAsLinkInfo) {
                classInfo = INTERMediatorLib.getClassAttributeFromNode(node);
                if (classInfo !== null) {
                    matched = classInfo.match(/IM\[.*\]/);
                    if (matched) {
                        return true;
                    }
                }
            }
        }
        return false;
    },

    isWidgetElement: function (node) {
        var classInfo, matched, attr, parentNode;

        if (!node) {
            return false;
        }
        if (INTERMediatorLib.getLinkedElementInfo(node)) {
            attr = node.getAttribute('data-im-widget');
            if (attr) {
                return true;
            }
            classInfo = INTERMediatorLib.getClassAttributeFromNode(node);
            if (classInfo !== null) {
                matched = classInfo.match(/IM_WIDGET\[.*\]/);
                if (matched) {
                    return true;
                }
            }
        } else {
            parentNode = node.parentNode;
            if (!parentNode && INTERMediatorLib.getLinkedElementInfoImpl(parentNode)) {
                attr = parentNode.getAttribute('data-im-widget');
                if (attr) {
                    return true;
                }
                classInfo = INTERMediatorLib.getClassAttributeFromNode(parentNode);
                if (classInfo !== null) {
                    matched = classInfo.match(/IM_WIDGET\[.*\]/);
                    if (matched) {
                        return true;
                    }
                }
            }
        }
        return false;
    },

    isNamedElement: function (node) {
        var nameInfo, matched;

        if (node !== null) {
            nameInfo = node.getAttribute('data-im-group');
            if (nameInfo) {
                return true;
            }
            nameInfo = node.getAttribute('name');
            if (nameInfo) {
                matched = nameInfo.match(/IM\[.*\]/);
                if (matched) {
                    return true;
                }
            }
        }
        return false;
    },

    getEnclosureSimple: function (node) {
        if (INTERMediatorLib.isEnclosure(node, true)) {
            return node;
        }
        return INTERMediatorLib.getEnclosureSimple(node.parentNode);
    },

    getEnclosure: function (node) {
        var currentNode, detectedRepeater;

        currentNode = node;
        while (currentNode !== null) {
            if (INTERMediatorLib.isRepeater(currentNode, true)) {
                detectedRepeater = currentNode;
            } else if (isRepeaterOfEnclosure(detectedRepeater, currentNode)) {
                detectedRepeater = null;
                return currentNode;
            }
            currentNode = currentNode.parentNode;
        }
        return null;

        /**
         * Check the pair of nodes in argument is valid for repater/enclosure.
         */

        function isRepeaterOfEnclosure(repeater, enclosure) {
            var repeaterTag, enclosureTag, enclosureClass, repeaterClass, enclosureDataAttr,
                repeaterDataAttr, repeaterType;
            if (!repeater || !enclosure) {
                return false;
            }
            repeaterTag = repeater.tagName;
            enclosureTag = enclosure.tagName;
            if ((repeaterTag === 'TR' && enclosureTag === 'TBODY') ||
                (repeaterTag === 'OPTION' && enclosureTag === 'SELECT') ||
                (repeaterTag === 'LI' && enclosureTag === 'OL') ||
                (repeaterTag === 'LI' && enclosureTag === 'UL')) {
                return true;
            }
            enclosureClass = INTERMediatorLib.getClassAttributeFromNode(enclosure);
            enclosureDataAttr = enclosure.getAttribute('data-im-control');
            if ((enclosureClass && enclosureClass.indexOf(INTERMediatorLib.roleAsEnclosureClassName) >= 0) ||
                (enclosureDataAttr && enclosureDataAttr.indexOf('enclosure') >= 0)) {
                repeaterClass = INTERMediatorLib.getClassAttributeFromNode(repeater);
                repeaterDataAttr = repeater.getAttribute('data-im-control');
                if ((repeaterClass && repeaterClass.indexOf(INTERMediatorLib.roleAsRepeaterClassName) >= 0) ||
                    (repeaterDataAttr && repeaterDataAttr.indexOf(INTERMediatorLib.roleAsRepeaterDataControlName) >= 0 ) ||
                    (repeaterDataAttr && repeaterDataAttr.indexOf(INTERMediatorLib.roleAsSeparatorDataControlName) >= 0 ) ||
                    (repeaterDataAttr && repeaterDataAttr.indexOf(INTERMediatorLib.roleAsFooterDataControlName) >= 0 ) ||
                    (repeaterDataAttr && repeaterDataAttr.indexOf(INTERMediatorLib.roleAsHeaderDataControlName) >= 0 ) ||
                    (repeaterDataAttr && repeaterDataAttr.indexOf(INTERMediatorLib.roleAsNoResultDataControlName) >= 0 )
                ) {
                    return true;
                } else if (repeaterTag === 'INPUT') {
                    repeaterType = repeater.getAttribute('type');
                    if (repeaterType &&
                        ((repeaterType.indexOf('radio') >= 0 || repeaterType.indexOf('check') >= 0))) {
                        return true;
                    }
                }
            }
            return false;
        }
    },


    /**
     * Get the table name / field name information from node as the array of
     * definitions.
     */

    getLinkedElementInfo: function (node) {
        var result = INTERMediatorLib.getLinkedElementInfoImpl(node)
        if (result !== false) {
            return result;
        }
        if (INTERMediatorLib.isWidgetElement(node.parentNode)) {
            return INTERMediatorLib.getLinkedElementInfo(node.parentNode);
        }
        return false;
    },

    getLinkedElementInfoImpl: function (node) {
        var defs = [], eachDefs, reg, i, attr, matched;
        if (INTERMediatorLib.isLinkedElement(node)) {
            attr = node.getAttribute('data-im');
            if (attr !== null && attr.length > 0) {
                reg = new RegExp('[\\s' + INTERMediator.defDivider + ']+');
                eachDefs = attr.split(reg);
                for (i = 0; i < eachDefs.length; i++) {
                    if (eachDefs[i] && eachDefs[i].length > 0) {
                        defs.push(resolveAlias(eachDefs[i]));
                    }
                }
                return defs;
            }
            if (INTERMediator.titleAsLinkInfo && node.getAttribute('TITLE')) {
                eachDefs = node.getAttribute('TITLE').split(INTERMediator.defDivider);
                for (i = 0; i < eachDefs.length; i++) {
                    defs.push(resolveAlias(eachDefs[i]));
                }
                return defs;
            }
            if (INTERMediator.classAsLinkInfo) {
                attr = INTERMediatorLib.getClassAttributeFromNode(node);
                if (attr !== null && attr.length > 0) {
                    matched = attr.match(/IM\[([^\]]*)\]/);
                    eachDefs = matched[1].split(INTERMediator.defDivider);
                    for (i = 0; i < eachDefs.length; i++) {
                        defs.push(resolveAlias(eachDefs[i]));
                    }
                }
                return defs;
            }
        }
        return false;

        function resolveAlias(def) {
            var aliases = INTERMediatorOnPage.getOptionsAliases();
            if (aliases != null && aliases[def] != null) {
                return aliases[def];
            }
            return def;
        }
    },

    getWidgetInfo: function (node) {
        var defs = [], eachDefs, i, classAttr, matched, reg;
        if (INTERMediatorLib.isWidgetElement(node)) {
            classAttr = node.getAttribute('data-im-widget');
            if (classAttr && classAttr.length > 0) {
                reg = new RegExp('[\\s' + INTERMediator.defDivider + ']+');
                eachDefs = classAttr.split(reg);
                for (i = 0; i < eachDefs.length; i++) {
                    if (eachDefs[i] && eachDefs[i].length > 0) {
                        defs.push(eachDefs[i]);
                    }
                }
                return defs;
            }
            classAttr = INTERMediatorLib.getClassAttributeFromNode(node);
            if (classAttr && classAttr.length > 0) {
                matched = classAttr.match(/IM_WIDGET\[([^\]]*)\]/);
                eachDefs = matched[1].split(INTERMediator.defDivider);
                for (i = 0; i < eachDefs.length; i++) {
                    defs.push(eachDefs[i]);
                }
                return defs;
            }
        }
        return false;
    },

    getNamedInfo: function (node) {
        var defs = [], eachDefs, i, nameAttr, matched, reg;
        if (INTERMediatorLib.isNamedElement(node)) {
            nameAttr = node.getAttribute('data-im-group');
            if (nameAttr && nameAttr.length > 0) {
                reg = new RegExp('[\\s' + INTERMediator.defDivider + ']+');
                eachDefs = nameAttr.split(reg);
                for (i = 0; i < eachDefs.length; i++) {
                    if (eachDefs[i] && eachDefs[i].length > 0) {
                        defs.push(eachDefs[i]);
                    }
                }
                return defs;
            }
            nameAttr = node.getAttribute('name');
            if (nameAttr && nameAttr.length > 0) {
                matched = nameAttr.match(/IM\[([^\]]*)\]/);
                eachDefs = matched[1].split(INTERMediator.defDivider);
                for (i = 0; i < eachDefs.length; i++) {
                    defs.push(eachDefs[i]);
                }
                return defs;
            }
        }
        return false;
    },

    /**
     * Get the repeater tag from the enclosure tag.
     */

    repeaterTagFromEncTag: function (tag) {
        if (tag === 'TBODY') {
            return 'TR';
        }
        else if (tag === 'SELECT') {
            return 'OPTION';
        }
        else if (tag === 'UL') {
            return 'LI';
        }
        else if (tag === 'OL') {
            return 'LI';
        }
        //else if (tag == 'DIV') return 'DIV';
        //else if (tag == 'SPAN') return 'SPAN';
        return null;
    },

    getNodeInfoArray: function (nodeInfo) {
        var comps, tableName, fieldName, targetName;

        if (!nodeInfo || !nodeInfo.split) {
            return {
                'table': null,
                'field': null,
                'target': null,
                'tableindex': null,
                'crossTable': false
            };
        }
        comps = nodeInfo.split(INTERMediator.separator);
        tableName = '';
        fieldName = '';
        targetName = '';
        if (comps.length === 3) {
            tableName = comps[0];
            fieldName = comps[1];
            targetName = comps[2];
        } else if (comps.length === 2) {
            tableName = comps[0];
            fieldName = comps[1];
        } else {
            fieldName = nodeInfo;
        }
        return {
            'table': tableName,
            'field': fieldName,
            'target': targetName,
            'tableindex': '_im_index_' + tableName,
            'crossTable': INTERMediator.crossTableStage === 3
        };
    },

    /**
     * @typedef {Object} IMType_NodeInfo
     * @property {string} field The field name.
     * @property {string} table The context name defined in the relevant definition file.
     * @property {string} target The target information which specified in the 3rd component of target spec.
     * @property {string} tableidnex This is used for FileMaker database's portal expanding.
     */

    /**
     * This method returns the IMType_NodeInfo object of the node specified with the parameter.
     * @param idValue the id attribute of the linked node.
     * @returns {IMType_NodeInfo}
     */
    getCalcNodeInfoArray: function (idValue) {
        var comps, tableName, fieldName, targetName, node, attribute;

        if (!idValue) {
            return null;
        }
        node = document.getElementById(idValue);
        if (!node) {
            return null;
        }
        attribute = node.getAttribute('data-im');
        if (!attribute) {
            return null;
        }
        comps = attribute.split(INTERMediator.separator);
        tableName = '';
        fieldName = '';
        targetName = '';
        if (comps.length === 3) {
            tableName = comps[0];
            fieldName = comps[1];
            targetName = comps[2];
        } else if (comps.length === 2) {
            fieldName = comps[0];
            targetName = comps[1];
        } else {
            fieldName = attribute;
        }
        return {
            'table': tableName,
            'field': fieldName,
            'target': targetName,
            'tableindex': '_im_index_' + tableName
        };
    },

    /* As for IE7, DOM element can't have any prototype. */

    getClassAttributeFromNode: function (node) {
        var str = '';
        if (node === null) {
            return '';
        }
        if (INTERMediator.isIE && INTERMediator.ieVersion < 8) {
            str = node.getAttribute('className');
        } else {
            str = node.getAttribute('class');
        }
        return str;
    },

    setClassAttributeToNode: function (node, className) {
        if (node === null) {
            return;
        }
        if (INTERMediator.isIE && INTERMediator.ieVersion < 8) {
            node.setAttribute('className', className);
        } else {
            node.setAttribute('class', className);
        }
    },

    /*
     INTER-Mediator supporting browser is over Ver.9 for IE. So this method is already deprecated.
     The eventInfos property doesn't use other than below methods.
     */
    eventInfos: [],

    addEvent: function (node, evt, func) {
        if (node.addEventListener) {
            node.addEventListener(evt, func, false);
            this.eventInfos.push({'node': node, 'event': evt, 'function': func});
            return this.eventInfos.length - 1;
        } else if (node.attachEvent) {
            node.attachEvent('on' + evt, func);
            this.eventInfos.push({'node': node, 'event': evt, 'function': func});
            return this.eventInfos.length - 1;
        }
        return -1;
    },

    removeEvent: function (serialId) {
        if (this.eventInfos[serialId].node.removeEventListener) {
            this.eventInfos[serialId].node.removeEventListener(this.eventInfos[serialId].evt, this.eventInfos[serialId].func, false);
        } else if (this.eventInfos[serialId].node.detachEvent) {
            this.eventInfos[serialId].node.detachEvent('on' + this.eventInfos[serialId].evt, this.eventInfos[serialId].func);
        }
    },

    // - - - - -

    toNumber: function (str) {
        'use strict';
        var s = '', i, c;
        str = str.toString();
        for (i = 0; i < str.length; i++) {
            c = str.charAt(i);
            if ((c >= '0' && c <= '9') || c === '.' || c === '-' ||
                c === INTERMediatorOnPage.localeInfo['mon_decimal_point']) {
                s += c;
            } else if (c >= '０' && c <= '９') {
                s += String.fromCharCode(c.charCodeAt(0) - '０'.charCodeAt(0) + '0'.charCodeAt(0));
            }
        }
        return parseFloat(s);
    },

    RoundHalfToEven: function (value, digit) {
        throw 'RoundHalfToEven method is NOT implemented.';
    },

    /**
     * This method returns the rounded value of the 1st parameter to the 2nd parameter from decimal point.
     * @param {number} value The source value.
     * @param {integer} digit Positive number means after the decimal point, and negative menas before it.
     * @returns {number}
     */
    Round: function (value, digit) {
        var powers = Math.pow(10, digit);
        return Math.round(value * powers) / powers;
    },

    normalizeNumerics: function (value) {
        var i;
        for (i = 0; i < 10; i++) {
            value = String(value).split(String.fromCharCode(65296 + i)).join(String(i));
            // Full-width numeric characters start from 0xFF10(65296). This is convert to Full to ASCII char for numeric.
        }
        return value;
    },

    /**
     * This method returns the rounded value of the 1st parameter to the 2nd parameter from decimal point
     * with a thousands separator.
     * @param {number} str The source value.
     * @param {integer} digit Positive number means after the decimal point, and negative means before it.
     * @param {string} decimalPoint
     * @param {string} thousandsSep
     * @param {string} currencySymbol
     * @param {object} flags
     * @returns {string}
     */
    numberFormatImpl: function (str, digit, decimalPoint, thousandsSep, currencySymbol, flags) {
        'use strict';
        var s, n, prefix, i, sign, tailSign = '', power, underDot, underNumStr, pstr,
            roundedNum, underDecimalNum, integerNum, formatted, numStr, j, isMinusValue,
            numerals, numbers;
        if (str === '' || str === null || str === undefined) {
            return '';
        }
        prefix = (String(str).substring(0, 1) === '-') ? '-' : '';
        if (String(str).match(/[-]/)) {
            str = prefix + String(str).split('-').join('');
        }
        //str = INTERMediatorLib.normalizeNumerics(str);
        n = INTERMediatorLib.toNumber(str);
        if (isNaN(n)) {
            return '';
        }
        if (flags === undefined) {
            flags = {};
        }
        sign = INTERMediatorOnPage.localeInfo.positive_sign;
        isMinusValue = false;
        if (n < 0) {
            sign = INTERMediatorOnPage.localeInfo.negative_sign;
            if (flags.negativeStyle === 0 || flags.negativeStyle === 1) {
                sign = '-';
            } else if (flags.negativeStyle === 2) {
                sign = '(';
                tailSign = ')';
            } else if (flags.negativeStyle === 3) {
                sign = '<';
                tailSign = '>';
            } else if (flags.negativeStyle === 4) {
                sign = ' CR';
            } else if (flags.negativeStyle === 5) {
                sign = '▲';
            }
            n = -n;
            isMinusValue = true;
        }

        if (flags.blankIfZero === true && n === 0) {
            return '';
        }

        if (flags.usePercentNotation) {
            n = n * 100;
        }

        underDot = (digit === undefined) ? INTERMediatorOnPage.localeInfo.frac_digits : this.toNumber(digit);
        power = Math.pow(10, underDot);
        roundedNum = Math.round(n * power);
        underDecimalNum = (underDot > 0) ? roundedNum % power : 0;
        integerNum = (roundedNum - underDecimalNum) / power;
        underNumStr = (underDot > 0) ? String(underDecimalNum) : '';
        while (underNumStr.length < underDot) {
            underNumStr = '0' + underNumStr;
        }

        if (flags.useSeparator === true) {
            if (n === 0) {
                formatted = "0";
            } else {
                n = integerNum;
                s = [];
                if (flags.kanjiSeparator === 1 || flags.kanjiSeparator === 2) {
                    numerals = ['万', '億', '兆', '京', '垓', '𥝱', '穣', '溝',
                        '澗', '正', '載', '極', '恒河沙', '阿僧祇', '那由他',
                        '不可思議', '無量大数'];
                    i = 0;
                    formatted = '';
                    for (n = Math.floor(n); n > 0; n = Math.floor(n / 10000)) {
                        if (n >= 10000) {
                            pstr = '0000' + (n % 10000).toString();
                        } else {
                            pstr = (n % 10000).toString();
                        }
                        if (flags.kanjiSeparator === 1) {
                            if (n >= 10000) {
                                if (pstr.substr(pstr.length - 4) !== '0000') {
                                    formatted = numerals[i] +
                                        Number(pstr.substr(pstr.length - 4)) +
                                        formatted;
                                } else {
                                    if (numerals[i - 1] !== formatted.charAt(0)) {
                                        formatted = numerals[i] + formatted;
                                    } else {
                                        formatted = numerals[i] + formatted.slice(1);
                                    }
                                }
                            } else {
                                formatted = n + formatted;
                            }
                        } else if (flags.kanjiSeparator === 2) {
                            numStr = pstr.substr(pstr.length - 4);
                            pstr = '';
                            if (numStr === '0001') {
                                pstr = '1';
                            } else if (numStr !== '0000') {
                                for (j = 0; j < numStr.length; j++) {
                                    if (numStr.charAt(j) > 1) {
                                        pstr = pstr + numStr.charAt(j);
                                    }
                                    if (numStr.charAt(j) > 0) {
                                        if (numStr.length - j === 4) {
                                            pstr = pstr + '千';
                                        } else if (numStr.length - j === 3) {
                                            pstr = pstr + '百';
                                        } else if (numStr.length - j === 2) {
                                            pstr = pstr + '十';
                                        }
                                    }
                                }
                            }
                            if (n >= 10000) {
                                if (pstr.length > 0) {
                                    formatted = numerals[i] + pstr + formatted;
                                } else {
                                    if (numerals[i - 1] !== formatted.charAt(0)) {
                                        formatted = numerals[i] + formatted;
                                    } else {
                                        formatted = numerals[i] + formatted.slice(1);
                                    }
                                }
                            } else {
                                if (numStr.length === 1) {
                                    formatted = n + formatted;
                                } else {
                                    formatted = pstr + formatted;
                                }
                            }
                        }
                        i++;
                    }
                    formatted = formatted +
                        (underNumStr === '' ? '' : decimalPoint + underNumStr);
                } else {
                    for (n = Math.floor(n); n > 0; n = Math.floor(n / 1000)) {
                        if (n >= 1000) {
                            pstr = '000' + (n % 1000).toString();
                            s.push(pstr.substr(pstr.length - 3));
                        } else {
                            s.push(n);
                        }
                    }
                    formatted = s.reverse().join(thousandsSep) +
                        (underNumStr === '' ? '' : decimalPoint + underNumStr);
                }
                if (flags.negativeStyle === 0 || flags.negativeStyle === 5) {
                    formatted = sign + formatted;
                } else if (flags.negativeStyle === 1 || flags.negativeStyle === 4) {
                    formatted = formatted + sign;
                } else if (flags.negativeStyle === 2 || flags.negativeStyle === 3) {
                    formatted = sign + formatted + tailSign;
                } else {
                    formatted = sign + formatted;
                }
            }
        } else {
            formatted = integerNum + (underNumStr === '' ? '' : decimalPoint + underNumStr);
            if (flags.negativeStyle === 0 || flags.negativeStyle === 5) {
                formatted = sign + formatted;
            } else if (flags.negativeStyle === 1 || flags.negativeStyle === 4) {
                formatted = formatted + sign;
            } else if (flags.negativeStyle === 2 || flags.negativeStyle === 3) {
                formatted = sign + formatted + tailSign;
            } else {
                formatted = sign + formatted;
            }
        }

        if (currencySymbol) {
            if (!isMinusValue) {
                if (INTERMediatorOnPage.localeInfo.p_cs_precedes == 1) {    // Stay operator "=="
                    if (INTERMediatorOnPage.localeInfo.p_sep_by_space == 1) { // Stay operator "=="
                        formatted = currencySymbol + ' ' + formatted;
                    } else {
                        formatted = currencySymbol + formatted;
                    }
                } else {
                    if (INTERMediatorOnPage.localeInfo.p_sep_by_space == 1) { // Stay operator '=='
                        formatted = formatted + ' ' + currencySymbol;
                    } else {
                        formatted = formatted + currencySymbol;
                    }
                }
            } else {
                if (INTERMediatorOnPage.localeInfo.n_cs_precedes == 1) { // Stay operator "=="
                    if (INTERMediatorOnPage.localeInfo.n_sep_by_space == 1) { // Stay operator "=="
                        formatted = currencySymbol + ' ' + formatted;
                    } else {
                        formatted = currencySymbol + formatted;
                    }
                } else {
                    if (INTERMediatorOnPage.localeInfo.n_sep_by_space == 1) { // Stay operator '=='
                        formatted = formatted + ' ' + currencySymbol;
                    } else {
                        formatted = formatted + currencySymbol;
                    }
                }
            }
        }

        if (flags.charStyle) {
            if (flags.charStyle === 1) {
                for (i = 0; i < 10; i++) {
                    formatted = String(formatted).split(String(i)).join(String.fromCharCode(65296 + i));
                }
            } else if (flags.charStyle === 2) {
                numbers = {
                    0: '〇', 1: '一', 2: '二', 3: '三', 4: '四',
                    5: '五', 6: '六', 7: '七', 8: '八', 9: '九'
                };
                for (i = 0; i < 10; i++) {
                    formatted = String(formatted).split(String(i)).join(String(numbers[i]));
                }
            } else if (flags.charStyle === 3) {
                numbers = {
                    0: '〇', 1: '壱', 2: '弐', 3: '参', 4: '四',
                    5: '伍', 6: '六', 7: '七', 8: '八', 9: '九'
                };
                for (i = 0; i < 10; i++) {
                    formatted = String(formatted).split(String(i)).join(String(numbers[i]));
                }
            }
        }

        if (flags.usePercentNotation === true && formatted !== '') {
            formatted = formatted + '%';
        }

        return formatted;
    },

    getKanjiNumber: function (n) {
        var s = [], count = 0;
        String(n).split('').reverse().forEach(function (c) {
            s.push(INTERMediatorLib.kanjiDigit[count]);
            count++;
            s.push(INTERMediatorLib.kanjiNumbers[parseInt(c)]);
        });
        return s.reverse().join('');
    },

    numberFormat: function (str, digit, flags) {
        'use strict';
        if (flags === undefined) {
            flags = {};
        }
        flags.useSeparator = true;    // for compatibility
        return this.decimalFormat(str, digit, flags);
    },

    percentFormat: function (str, digit, flags) {
        'use strict';
        if (typeof flags !== 'object') {
            flags = {};
        }
        flags.usePercentNotation = true;
        return INTERMediatorLib.numberFormatImpl(str, digit,
            INTERMediatorOnPage.localeInfo.mon_decimal_point,
            INTERMediatorOnPage.localeInfo.mon_thousands_sep,
            false,
            flags
        );
    },

    decimalFormat: function (str, digit, flags) {
        'use strict';
        return INTERMediatorLib.numberFormatImpl(str, digit,
            INTERMediatorOnPage.localeInfo.mon_decimal_point,
            INTERMediatorOnPage.localeInfo.mon_thousands_sep,
            false,
            flags
        );
    },

    currencyFormat: function (str, digit, flags) {
        'use strict';
        return INTERMediatorLib.numberFormatImpl(str, digit,
            INTERMediatorOnPage.localeInfo.mon_decimal_point,
            INTERMediatorOnPage.localeInfo.mon_thousands_sep,
            INTERMediatorOnPage.localeInfo.currency_symbol,
            flags
        );
    },

    booleanFormat: function (str, forms) {
        'use strict';
        var trueString = 'true', falseString = 'false', fmtStr;
        var params = forms.split(',');
        if (params[0]) {
            fmtStr = params[0].trim();
            if (fmtStr.length > 0) {
                trueString = fmtStr;
            }
        }
        if (params[1]) {
            fmtStr = params[1].trim();
            if (fmtStr.length > 0) {
                falseString = fmtStr;
            }
        }
        if (str === '' || str === null) {
            return '';
        } else {
            if (parseInt(str, 10) !== 0) {
                return trueString;
            } else {
                return falseString;
            }
        }
    },

    datetimeFormat: function (str, params) {
        'use strict';
        return INTERMediatorLib.datetimeFormatImpl(str, params, 'datetime');
    },

    dateFormat: function (str, params) {
        'use strict';
        return INTERMediatorLib.datetimeFormatImpl(str, params, 'date');
    },

    timeFormat: function (str, params) {
        'use strict';
        return INTERMediatorLib.datetimeFormatImpl(str, params, 'time');
    },

    placeHolder: {
        '%Y': Date.prototype.getFullYear, //
        '%y': function () {
            return INTERMediatorLib.tweDigitsNumber(this.getFullYear());
        }, //	西暦2桁	17
        '%g': function () {
            return INTERMediatorLib.getLocalYear(this, 1);
        }, //	ロカールによる年数	平成29年
        '%G': function () {
            return INTERMediatorLib.getLocalYear(this, 2);
        }, //	ロカールによる年数	平成二十九年
        '%M': function () {
            return INTERMediatorLib.tweDigitsNumber(this.getMonth() + 1);
        }, //	月2桁	07
        '%m': function () {
            return this.getMonth() + 1;
        }, //	月数値	7
        '%b': function () {
            return INTERMediatorOnPage.localeInfo["ABMON"][this.getMonth()];
        }, //	短縮月名	Jul
        '%B': function () {
            return INTERMediatorOnPage.localeInfo["MON"][this.getMonth()];
        }, //	月名	July
        '%t': function () {
            return INTERMediatorLib.eMonAbbr[this.getMonth()];
        }, //	短縮月名	Jul
        '%T': function () {
            return INTERMediatorLib.eMonName[this.getMonth()];
        }, //	月名	July
        '%D': function () {
            return INTERMediatorLib.tweDigitsNumber(this.getDate());
        }, //	日2桁	12
        '%d': Date.prototype.getDate, //	日数値	12
        '%a': function () {
            return INTERMediatorLib.eDayAbbr[this.getDay()];
        }, //	英語短縮曜日名	Mon
        '%A': function () {
            return INTERMediatorLib.eDayName[this.getDay()];
        }, //	英語曜日名	Monday
        '%w': function () {
            return INTERMediatorOnPage.localeInfo["ABDAY"][this.getDay()];
        }, //	ロカールによる短縮曜日名	月
        '%W': function () {
            return INTERMediatorOnPage.localeInfo["DAY"][this.getDay()];
        }, //	ロカールによる曜日名	月曜日
        '%H': function () {
            return INTERMediatorLib.tweDigitsNumber(this.getHours());
        }, //	時2桁	09
        '%h': Date.prototype.getHours, //	時数値	9
        '%J': function () {
            return INTERMediatorLib.tweDigitsNumber(this.getHours() % 12);
        }, //	12時間制時2桁	09
        '%j': function () {
            return this.getHours() % 12;
        }, //	12時間制時数値	9
        '%K': function () {
            var n = this.getHours() % 12;
            return INTERMediatorLib.tweDigitsNumber(n === 0 ? 12 : n);
        }, //	12時間制時2桁	09
        '%k': function () {
            var n = this.getHours() % 12;
            return n === 0 ? 12 : n;
        }, //	12時間制時数値	9
        '%I': function () {
            return INTERMediatorLib.tweDigitsNumber(this.getMinutes());
        }, //	分2桁	05
        '%i': Date.prototype.getMinutes, //	分数値	5
        '%S': function () {
            return INTERMediatorLib.tweDigitsNumber(this.getSeconds());
        }, //	秒2桁	00
        '%s': Date.prototype.getSeconds, //	秒数値	0
        '%P': function () {
            return Math.floor(this.getHours() / 12) === 0 ? "AM" : "PM";
        }, //	AM/PM	AM
        '%p': function () {
            return Math.floor(this.getHours() / 12) === 0 ? "am" : "pm";
        }, //	am/pm	am
        '%N': function () {
            return Math.floor(this.getHours() / 12) === 0 ?
                INTERMediatorOnPage.localeInfo["AM_STR"] : INTERMediatorOnPage.localeInfo["PM_STR"];
        }, //	am/pm	am
        // '%Z': Date.prototype.getTimezoneOffset, //	タイムゾーン省略名	JST
        // '%z': Date.prototype.getTimezoneOffset, //	タイムゾーンオフセット	+0900
        '%%': function () {
            return '%';
        } //	パーセント	%
    },

    tweDigitsNumber: function (n) {
        var v = parseInt(n);
        return ('0' + v.toString()).substr(-2, 2);
    },

    jYearStartDate: {'1989/1/8': '平成', '1926/12/25': '昭和', '1912/7/30': '大正', '1868/1/25': '明治'},
    eDayName: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
    eDayAbbr: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
    eMonName: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    eMonAbbr: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    kanjiNumbers: ['〇', '一', '二', '三', '四', '五', '六', '七', '八', '九'],
    kanjiDigit: ['', '十', '百', '千', '万'],

    getLocalYear: function (dt, fmt) {
        var gengoName, gengoYear, startDateStr, dtStart;
        if (!dt) {
            return '';
        }
        gengoName = '';
        gengoYear = 0;
        for (startDateStr in INTERMediatorLib.jYearStartDate) {
            if (INTERMediatorLib.jYearStartDate.hasOwnProperty(startDateStr)) {
                dtStart = new Date(startDateStr);
                if (dt > dtStart) {
                    gengoName = INTERMediatorLib.jYearStartDate[startDateStr];
                    gengoYear = dt.getFullYear() - dtStart.getFullYear() + 1;
                    gengoYear = ((gengoYear === 1) ? '元' : (fmt === 2 ? INTERMediatorLib.getKanjiNumber(gengoYear) : gengoYear));
                    break;
                }
            }
        }
        return gengoName + gengoYear + '年';
    },

    datetimeFormatImpl: function (str, params, flags) {
        'use strict';
        var paramStr = params.trim().toUpperCase();
        var kind = flags.trim().toUpperCase();
        var key = kind.substr(0, 1) + '_FMT_' + paramStr;
        if (INTERMediatorOnPage.localeInfo[key]) {
            params = INTERMediatorOnPage.localeInfo[key];
            if (kind === 'DATETIME') {
                params += ' ' + INTERMediatorOnPage.localeInfo['T_FMT_' + paramStr];
            }
        }
        var dt = new Date(str.replace(/-/g, '/')), c, result = '', replaced;
        if (dt.toString() === 'Invalid Date') {
            return '';
        }
        for (c = 0; c < params.length; c++) {
            if ((c + 1) < params.length && INTERMediatorLib.placeHolder[params.substr(c, 2)]) {
                replaced = (INTERMediatorLib.placeHolder[params.substr(c, 2)]).apply(dt);
                result += replaced;
                c++;
            } else {
                result += params.substr(c, 1);
            }
        }
        return result;
    },

    convertNumeric: function (value) {
        value = value.replace(new RegExp(INTERMediatorOnPage.localeInfo.mon_thousands_sep, 'g'), '');
        value = INTERMediatorLib.normalizeNumerics(value);
        if (value !== '') {
            value = parseFloat(value);
        }
        return value;
    },

    convertBoolean: function (value, forms) {
        var trueString = 'true', falseString = 'false', fmtStr;
        value = value.trim();
        var params = forms.split(',');
        if (params[0]) {
            fmtStr = params[0].trim();
            if (fmtStr.length > 0) {
                trueString = fmtStr;
            }
        }
        if (params[1]) {
            fmtStr = params[1].trim();
            if (fmtStr.length > 0) {
                falseString = fmtStr;
            }
        }
        if (value === trueString) {
            return true;
        } else if (value === falseString) {
            return false;
        }
        return null;
    },

    convertPercent: function (value) {
        value = value.replace(new RegExp(INTERMediatorOnPage.localeInfo.mon_thousands_sep, 'g'), '');
        value = value.replace('%', '');
        value = INTERMediatorLib.normalizeNumerics(value);
        if (value !== '') {
            value = parseFloat(value) / 100;
        }
        return value;
    },

    convertDate: function (value, params) {
        return INTERMediatorLib.convertDateTimeImpl(value, params, 'date');
    },
    convertTime: function (value, params) {
        return INTERMediatorLib.convertDateTimeImpl(value, params, 'time');
    },
    convertDateTime: function (value, params) {
        return INTERMediatorLib.convertDateTimeImpl(value, params, 'datetime');
    },

    convertDateTimeImpl: function (value, params, flags) {
        var c, result, replacement = [], regexp = '';
        var r, matched, y, m, d, h, i, s, paramStr, kind, key, mon;

        paramStr = params.trim().toUpperCase();
        kind = flags.trim().toUpperCase();
        key = kind.substr(0, 1) + '_FMT_' + paramStr;
        if (INTERMediatorOnPage.localeInfo[key]) {
            params = INTERMediatorOnPage.localeInfo[key];
            if (kind === 'DATETIME') {
                params += ' ' + INTERMediatorOnPage.localeInfo['T_FMT_' + paramStr];
            }
        }
        params = params.replace(/([\(\)])/g, '\\$1');
        for (c = 0; c < params.length; c++) {
            if ((c + 1) < params.length && INTERMediatorLib.reverseRegExp[params.substr(c, 2)]) {
                regexp += INTERMediatorLib.reverseRegExp[params.substr(c, 2)];
                replacement.push(params.substr(c, 2));
                c++;
            } else {
                regexp += params.substr(c, 1);
            }
        }
        r = new RegExp(regexp);
        matched = r.exec(value);
        result = value;
        if (matched) {
            for (c = 0; c < replacement.length; c++) {
                switch (replacement[c]) {
                case '%Y':
                case '%y':
                    y = matched[c + 1];
                    break;
                case '%M':
                case '%m':
                    m = matched[c + 1];
                    break;
                case '%T':
                case '%t':
                    mon = matched[c + 1];
                    m = INTERMediatorLib.eMonAbbr.indexOf(mon.substr(0, 1).toUpperCase() + mon.substr(1, 2).toLowerCase());
                    m++;
                    break;
                case '%D':
                case '%d':
                    d = matched[c + 1];
                    break;
                case '%H':
                case '%h':
                    h = matched[c + 1];
                    break;
                case '%I':
                case '%i':
                    i = matched[c + 1];
                    break;
                case '%S':
                case '%s':
                    s = matched[c + 1];
                    break;
                }
            }
            if (y && m && d && h && i && s) {
                result = y + '-' + m + '-' + d + ' ' + h + ':' + i + ':' + s;
            } else if (y && m && d) {
                result = y + '-' + m + '-' + d;
            } else if (h && i && s) {
                result = h + ':' + i + ':' + s;
            }
        }
        return result;

    },

    reverseRegExp: {
        '%Y': '([\\d]{4})', //
        '%y': '([\\d]{2})', //	西暦2桁	17
        '%g': '(明治|大正|昭和|平成)(元|[\\d]{1,2})年', //	ロカールによる年数	平成29年
        '%G': '(明治|大正|昭和|平成)(.+)年', //	ロカールによる年数	平成二十九年
        '%M': '([\\d]{1,2})', //	月2桁	07
        '%m': '([\\d]{1,2})', //	月数値	7
        '%b': '(.+)', //	短縮月名	Jul
        '%B': '(.+)', //	月名	July
        '%t': '(.+)', //	短縮月名	Jul
        '%T': '(.+)', //	月名	July
        '%D': '([\\d]{1,2})', //	日2桁	12
        '%d': '([\\d]{1,2})', //	日数値	12
        '%a': '(.+)', //	英語短縮曜日名	Mon
        '%A': '(.+)', //	英語曜日名	Monday
        '%w': '(.+)', //	ロカールによる短縮曜日名	月
        '%W': '(.+)', //	ロカールによる曜日名	月曜日
        '%H': '([\\d]{1,2})', //	時2桁	09
        '%h': '([\\d]{1,2})', //	時数値	9
        '%J': '([\\d]{1,2})', //	12時間制時2桁	09
        '%j': '([\\d]{1,2})', //	12時間制時数値	9
        '%K': '([\\d]{1,2})', //	12時間制時2桁	09
        '%k': '([\\d]{1,2})', //	12時間制時数値	9
        '%I': '([\\d]{1,2})', //	分2桁	05
        '%i': '([\\d]{1,2})', //	分数値	5
        '%S': '([\\d]{1,2})', //	秒2桁	00
        '%s': '([\\d]{1,2})', //	秒数値	0
        '%P': '(AM|PM)', //	AM/PM	AM
        '%p': '(am|pm)', //	am/pm	am
        '%N': '(' + INTERMediatorOnPage.localeInfo['AM_STR'] + '|' + INTERMediatorOnPage.localeInfo['PM_STR'] + ')', //	am/pm	am
        '%%': '[\%]' //	パーセント	%
    },

    objectToString: function (obj) {
        var str, i, key, sq;

        if (obj === null) {
            return 'null';
        }
        if (typeof obj === 'object') {
            str = '';
            sq = String.fromCharCode(39);
            if (obj.constructor === Array) {
                for (i = 0; i < obj.length; i++) {
                    str += INTERMediatorLib.objectToString(obj[i]) + ', ';
                }
                return '[' + str + ']';
            } else {
                for (key in obj) {
                    str += sq + key + sq + ':' + INTERMediatorLib.objectToString(obj[key]) + ', ';
                }
                return '{' + str + '}';
            }
        } else {
            return sq + obj + sq;
        }
    },

    getTargetTableForRetrieve: function (element) {
        if (element['view'] !== null) {
            return element['view'];
        }
        return element['name'];
    },

    getTargetTableForUpdate: function (element) {
        if (element['table'] !== null) {
            return element['table'];
        }
        return element['name'];
    },

    getInsertedString: function (tmpStr, dataArray) {
        var resultStr, counter;

        resultStr = tmpStr;
        if (dataArray !== null) {
            for (counter = 1; counter <= dataArray.length; counter++) {
                resultStr = resultStr.replace('@' + counter + '@', dataArray[counter - 1]);
            }
        }
        return resultStr;
    },

    getInsertedStringFromErrorNumber: function (errNum, dataArray) {
        var resultStr, counter, messageArray;

        messageArray = INTERMediatorOnPage.getMessages();
        resultStr = messageArray ? messageArray[errNum] : 'Error:' + errNum;
        if (dataArray) {
            for (counter = 1; counter <= dataArray.length; counter++) {
                resultStr = resultStr.replace('@' + counter + '@', dataArray[counter - 1]);
            }
        }
        return resultStr;
    },

    getNamedObject: function (obj, key, named) {
        var index;
        for (index in obj) {
            if (obj[index][key] === named) {
                return obj[index];
            }
        }
        return null;
    },

    getNamedObjectInObjectArray: function (ar, key, named) {
        var i;
        for (i = 0; i < ar.length; i++) {
            if (ar[i][key] === named) {
                return ar[i];
            }
        }
        return null;
    },

    getNamedValueInObject: function (ar, key, named, retrieveKey) {
        var result = [], index;
        for (index in ar) {
            if (ar[index][key] === named) {
                result.push(ar[index][retrieveKey]);
            }
        }
        if (result.length === 0) {
            return null;
        } else if (result.length === 1) {
            return result[0];
        } else {
            return result;
        }
    },

    is_array: function (target) {
        return target
            && typeof target === 'object'
            && typeof target.length === 'number'
            && typeof target.splice === 'function'
            && !(target.propertyIsEnumerable('length'));
    },

    getNamedValuesInObject: function (ar, key1, named1, key2, named2, retrieveKey) {
        var result = [], index;
        for (index in ar) {
            if (ar.hasOwnProperty(index) && ar[index][key1] === named1 && ar[index][key2] === named2) {
                result.push(ar[index][retrieveKey]);
            }
        }
        if (result.length === 0) {
            return null;
        } else if (result.length === 1) {
            return result[0];
        } else {
            return result;
        }
    },

    getRecordsetFromFieldValueObject: function (obj) {
        var recordset = {}, index;
        for (index in obj) {
            if (obj.hasOwnProperty(index)) {
                recordset[obj[index]['field']] = obj[index]['value'];
            }
        }
        return recordset;
    },

    getNodePath: function (node) {
        if (node.tagName === null) {
            return '';
        } else {
            return INTERMediatorLib.getNodePath(node.parentNode) + '/' + node.tagName;
        }
    },

    isPopupMenu: function (element) {
        if (!element || !element.tagName) {
            return false;
        }
        if (element.tagName == 'SELECT') {
            return true;
        }
        return false;
    },

    /*
     If the cNode parameter is like '_im_post', this function will search data-im-control='post' elements.
     */
    getElementsByClassNameOrDataAttr: function (node, cName) {
        var nodes = [], attrValue;

        attrValue = (cName.match(/^_im_/)) ? cName.substr(4) : cName;
        if (attrValue) {
            checkNode(node);
        }
        return nodes;

        function checkNode(target) {
            var value, i, items;
            if (target === undefined || target.nodeType !== 1) {
                return;
            }
            value = INTERMediatorLib.getClassAttributeFromNode(target);
            if (value) {
                items = value.split('|');
                for (i = 0; i < items.length; i++) {
                    if (items[i] == attrValue) {
                        nodes.push(target);
                    }
                }
            }
            value = target.getAttribute('data-im-control');
            if (value) {
                items = value.split(/[| ]/);
                for (i = 0; i < items.length; i++) {
                    if (items[i] == attrValue) {
                        nodes.push(target);
                    }
                }
            }
            value = target.getAttribute('data-im');
            if (value) {
                items = value.split(/[| ]/);
                for (i = 0; i < items.length; i++) {
                    if (items[i] == attrValue) {
                        nodes.push(target);
                    }
                }
            }
            for (i = 0; i < target.children.length; i++) {
                checkNode(target.children[i]);
            }
        }
    },

    getElementsByAttributeValue: function (node, attribute, value) {
        var nodes = [];
        var reg = new RegExp(value);
        checkNode(node);
        return nodes;

        function checkNode(target) {
            var aValue, i;
            if (target === undefined || target.nodeType !== 1) {
                return;
            }
            aValue = target.getAttribute(attribute);
            if (aValue && aValue.match(reg)) {
                nodes.push(target);
            }
            for (i = 0; i < target.children.length; i++) {
                checkNode(target.children[i]);
            }
        }
    },

    getElementsByClassName: function (node, cName) {
        var nodes = [];
        var reg = new RegExp(cName);
        checkNode(node);
        return nodes;

        function checkNode(target) {
            var className, i;
            if (target === undefined || target.nodeType !== 1) {
                return;
            }
            className = INTERMediatorLib.getClassAttributeFromNode(target);
            if (className && className.match(reg)) {
                nodes.push(target);
            }
            for (i = 0; i < target.children.length; i++) {
                checkNode(target.children[i]);
            }
        }
    },

    getElementsByIMManaged: function (node) {
        var nodes = [];
        var reg = new RegExp(/^IM/);
        checkNode(node);
        return nodes;

        function checkNode(target) {
            var nodeId, i;
            if (target === undefined || target.nodeType !== 1) {
                return;
            }
            nodeId = target.getAttribute('id');
            if (nodeId && nodeId.match(reg)) {
                nodes.push(target);
            }
            for (i = 0; i < target.children.length; i++) {
                checkNode(target.children[i]);
            }
        }
    },

    seekLinkedAndWidgetNodes: function (nodes, ignoreEnclosureCheck) {
        var linkedNodesCollection = []; // Collecting linked elements to this array.;
        var widgetNodesCollection = [];
        var i, doEncCheck = ignoreEnclosureCheck;

        if (ignoreEnclosureCheck === undefined || ignoreEnclosureCheck === null) {
            doEncCheck = false;
        }

        for (i = 0; i < nodes.length; i++) {
            seekLinkedElement(nodes[i]);
        }
        return {linkedNode: linkedNodesCollection, widgetNode: widgetNodesCollection};

        function seekLinkedElement(node) {
            var nType, currentEnclosure, children, i;
            nType = node.nodeType;
            if (nType === 1) {
                if (INTERMediatorLib.isLinkedElement(node)) {
                    currentEnclosure = doEncCheck ? INTERMediatorLib.getEnclosure(node) : null;
                    if (currentEnclosure === null) {
                        linkedNodesCollection.push(node);
                    } else {
                        return currentEnclosure;
                    }
                }
                if (INTERMediatorLib.isWidgetElement(node)) {
                    currentEnclosure = doEncCheck ? INTERMediatorLib.getEnclosure(node) : null;
                    if (currentEnclosure === null) {
                        widgetNodesCollection.push(node);
                    } else {
                        return currentEnclosure;
                    }
                }
                children = node.childNodes;
                for (i = 0; i < children.length; i++) {
                    seekLinkedElement(children[i]);
                }
            }
            return null;
        }
    },

    createErrorMessageNode: function (tag, message) {
        var messageNode;
        messageNode = document.createElement(tag);
        INTERMediatorLib.setClassAttributeToNode(messageNode, '_im_alertmessage');
        messageNode.appendChild(document.createTextNode(message));
        return messageNode;
    },

    removeChildNodes: function (node) {
        if (node) {
            while (node.childNodes.length > 0) {
                node.removeChild(node.childNodes[0]);
            }
        }
    },

    clearErrorMessage: function (node) {
        var errorMsgs, j;
        if (node) {
            errorMsgs = INTERMediatorLib.getElementsByClassName(node.parentNode, '_im_alertmessage');
            for (j = 0; j < errorMsgs.length; j++) {
                errorMsgs[j].parentNode.removeChild(errorMsgs[j]);
            }
        }
    },

    dateTimeStringISO: function (dt) {
        dt = (!dt) ? new Date() : dt;
        return dt.getFullYear() + '-'
            + ('0' + (dt.getMonth() + 1)).substr(-2, 2) + '-'
            + ('0' + dt.getDate()).substr(-2, 2) + ' '
            + ('0' + dt.getHours()).substr(-2, 2) + ':'
            + ('0' + dt.getMinutes()).substr(-2, 2) + ':'
            + ('0' + dt.getSeconds()).substr(-2, 2);
    },

    dateTimeStringFileMaker: function (dt) {
        dt = (!dt) ? new Date() : dt;
        return ('0' + (dt.getMonth() + 1)).substr(-2, 2) + '/'
            + ('0' + dt.getDate()).substr(-2, 2) + '/'
            + dt.getFullYear() + ' '
            + ('0' + dt.getHours()).substr(-2, 2) + ':'
            + ('0' + dt.getMinutes()).substr(-2, 2) + ':'
            + ('0' + dt.getSeconds()).substr(-2, 2);
    },

    dateStringISO: function (dt) {
        dt = (!dt) ? new Date() : dt;
        return dt.getFullYear() + '-'
            + ('0' + (dt.getMonth() + 1)).substr(-2, 2) + '-'
            + ('0' + dt.getDate()).substr(-2, 2);
    },

    dateStringFileMaker: function (dt) {
        dt = (!dt) ? new Date() : dt;
        return ('0' + (dt.getMonth() + 1)).substr(-2, 2) + '/'
            + ('0' + dt.getDate()).substr(-2, 2) + '/'
            + dt.getFullYear();
    },

    timeString: function (dt) {
        dt = (!dt) ? new Date() : dt;
        return ('0' + dt.getHours()).substr(-2, 2) + ':'
            + ('0' + dt.getMinutes()).substr(-2, 2) + ':'
            + ('0' + dt.getSeconds()).substr(-2, 2);
    }
};

INTERMediatorLib.initialize();


/*

 IMLibNodeGraph object can handle the directed acyclic graph.
 The nodes property stores every node, i.e. the id attribute of each node.
 The edges property stores ever edge represented by the objet {from: node1, to: node2}.
 If the node1 or node2 aren't stored in the nodes array, they are going to add as nodes too.

 The following is the example to store the directed acyclic graph.

 a -> b -> c -> d
 |    -> f
 ------>
 -> e
 i -> j
 x

 IMLibNodeGraph.clear();
 IMLibNodeGraph.addEdge('a','b');
 IMLibNodeGraph.addEdge('b','c');
 IMLibNodeGraph.addEdge('c','d');
 IMLibNodeGraph.addEdge('a','e');
 IMLibNodeGraph.addEdge('b','f');
 IMLibNodeGraph.addEdge('a','f');
 IMLibNodeGraph.addEdge('i','j');
 IMLibNodeGraph.addNode('x');

 The first calling of the getLeafNodesWithRemoving method returns 'd', 'f', 'e', 'j', 'x'.
 The second calling does 'c', 'i'. The third one does 'b', the forth one does 'a'.
 You can get the nodes from leaves to root as above.

 If the getLeafNodesWithRemoving method returns [] (no elements array), and the nodes property has any elements,
 it shows the graph has circular reference.

 */
var IMLibNodeGraph = {
    nodes: [],
    edges: [],
    clear: function () {
        this.nodes = [];
        this.edges = [];
    },
    addNode: function (node) {
        if (this.nodes.indexOf(node) < 0) {
            this.nodes.push(node);
        }
    },
    addEdge: function (fromNode, toNode) {
        if (this.nodes.indexOf(fromNode) < 0) {
            this.addNode(fromNode);
        }
        if (this.nodes.indexOf(toNode) < 0) {
            this.addNode(toNode);
        }
        this.edges.push({from: fromNode, to: toNode});
    },
    getAllNodesInEdge: function () {
        var i, nodes = [];
        for (i = 0; i < this.edges.length; i++) {
            if (nodes.indexOf(this.edges[i].from) < 0) {
                nodes.push(this.edges[i].from);
            }
            if (nodes.indexOf(this.edges[i].to) < 0) {
                nodes.push(this.edges[i].to);
            }
        }
        return nodes;
    },
    getLeafNodes: function () {
        var i, srcs = [], dests = [], srcAndDests = this.getAllNodesInEdge();
        for (i = 0; i < this.edges.length; i++) {
            srcs.push(this.edges[i].from);
        }
        for (i = 0; i < this.edges.length; i++) {
            if (srcs.indexOf(this.edges[i].to) < 0 && dests.indexOf(this.edges[i].to) < 0) {
                dests.push(this.edges[i].to);
            }
        }
        for (i = 0; i < this.nodes.length; i++) {
            if (srcAndDests.indexOf(this.nodes[i]) < 0) {
                dests.push(this.nodes[i]);
            }
        }
        return dests;
    },
    getLeafNodesWithRemoving: function () {
        var i, newEdges = [], dests = this.getLeafNodes();
        for (i = 0; i < this.edges.length; i++) {
            if (dests.indexOf(this.edges[i].to) < 0) {
                newEdges.push(this.edges[i]);
            }
        }
        this.edges = newEdges;
        for (i = 0; i < dests.length; i++) {
            this.nodes.splice(this.nodes.indexOf(dests[i]), 1);
        }
        return dests;
    },
    removeNode: function (node) {
        var i, newEdges = [];
        for (i = 0; i < this.edges.length; i++) {
            if (this.edges[i].to != node) {
                newEdges.push(this.edges[i]);
            }
        }
        this.edges = newEdges;
        this.nodes.splice(this.nodes.indexOf(node), 1);
    },
    applyToAllNodes: function (f) {
        var i;
        for (i = 0; i < this.nodes.length; i++) {
            f(this.nodes[i]);
        }

    },
    //
    // decodeOpenIDToken: function ($token) {
    //     var header, payload, cert, components = $token.split('.');
    //     if (components.length != 3) {
    //         return false;
    //     }
    //     header = Base64.decode(components[0]);
    //     payload = Base64.decode(components[1]);
    //     cert = Base64.decode(components[2]);
    // }
};
