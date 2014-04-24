/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

//"use strict"

var INTERMediatorLib = new INTERMediatorLib();

function INTERMediatorLib() {
    var instance = {

        ignoreEnclosureRepeaterClassName: "_im_ignore_enc_rep",
        ignoreEnclosureRepeaterControlName: "ignore_enc_rep",
        rollingRepeaterClassName: "_im_repeater",
        rollingEnclocureClassName: "_im_enclosure",
        rollingRepeaterDataControlName: "repeater",
        rollingEnclocureDataControlName: "enclosure",
        
        initialize: function () {
            INTERMediator.startFrom = 0;
            INTERMediator.additionalCondition = {};
            INTERMediator.additionalSortKey = {};
            
            return null;
        },
        
        setup: function () {
            if (window.addEventListener) {
                window.addEventListener("load", this.initialize, false);
            } else if (window.attachEvent) { // for IE
                window.attachEvent("onload", this.initialize);
            } else  {
                window.onload = this.initialize;
            }
            
            return null;
        },

        generatePasswordHash: function (password) {
            var numToHex, salt, saltHex, code, lowCode, highCode;
            numToHex = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F'];
            salt = "";
            saltHex = "";
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
            while (currentNode != null) {
                if (INTERMediatorLib.isRepeater(currentNode, true)) {
                    return currentNode;
                }
                currentNode = currentNode.parentNode;
            }
            return null;
        },

        getParentEnclosure: function (node) {
            var currentNode = node;
            while (currentNode != null) {
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
            controlAttr = node.getAttribute("data-im-control");
            if (controlAttr && controlAttr.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterControlName) >= 0) {
                return false;
            }
            tagName = node.tagName;
            if ((tagName === 'TBODY')
                || (tagName === 'UL')
                || (tagName === 'OL')
                || (tagName === 'SELECT')
                || ((tagName === 'DIV' || tagName === 'SPAN' )
                && className
                && className.indexOf(INTERMediatorLib.rollingEnclocureClassName) >= 0)
                || ((tagName === 'DIV' || tagName === 'SPAN' )
                && controlAttr
                && controlAttr.indexOf(INTERMediatorLib.rollingEnclocureDataControlName) >= 0)) {
                if (nodeOnly) {
                    return true;
                } else {
                    children = node.childNodes;
                    for (k = 0; k < children.length; k++) {
//                    if (INTERMediatorLib.isEnclosure(children[k], true)) {
//
//                    } else
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
            controlAttr = node.getAttribute("data-im-control");
            if (controlAttr && controlAttr.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterControlName) >= 0) {
                return false;
            }
            tagName = node.tagName;
            if ((tagName === 'TR')
                || (tagName === 'LI')
                || (tagName === 'OPTION')
                || ((tagName === 'DIV' || tagName === 'SPAN' )
                && className
                && className.indexOf(INTERMediatorLib.rollingRepeaterClassName) >= 0)
                || ((tagName === 'DIV' || tagName === 'SPAN' )
                && controlAttr
                && controlAttr.indexOf(INTERMediatorLib.rollingRepeaterDataControlName) >= 0)) {
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

            if (node != null) {
                attr = node.getAttribute("data-im")
                if (attr) {
                    return true;
                }
                if (INTERMediator.titleAsLinkInfo) {
                    if (node.getAttribute('TITLE') != null && node.getAttribute('TITLE').length > 0) {
                        // IE: If the node doesn't have a title attribute, getAttribute
                        // doesn't return null.
                        // So it requrired check if it's empty string.
                        return true;
                    }
                }
                if (INTERMediator.classAsLinkInfo) {
                    classInfo = INTERMediatorLib.getClassAttributeFromNode(node);
                    if (classInfo != null) {
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
            var classInfo, matched;

            if (node != null) {
                attr = node.getAttribute("data-im-widget")
                if (attr) {
                    return true;
                }
                classInfo = INTERMediatorLib.getClassAttributeFromNode(node);
                if (classInfo != null) {
                    matched = classInfo.match(/IM_WIDGET\[.*\]/);
                    if (matched) {
                        return true;
                    }
                }

            }
            return false;
        },

        isNamedElement: function (node) {
            var nameInfo, matched;

            if (node != null) {
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
            while (currentNode != null) {
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
                if ((repeaterTag === 'TR' && enclosureTag === 'TBODY')
                    || (repeaterTag === 'OPTION' && enclosureTag === 'SELECT')
                    || (repeaterTag === 'LI' && enclosureTag === 'OL')
                    || (repeaterTag === 'LI' && enclosureTag === 'UL')) {
                    return true;
                }
                if ((enclosureTag === 'DIV' || enclosureTag === 'SPAN' )) {
                    enclosureClass = INTERMediatorLib.getClassAttributeFromNode(enclosure);
                    enclosureDataAttr = enclosure.getAttribute("data-im-control");
                    if ((enclosureClass && enclosureClass.indexOf('_im_enclosure') >= 0)
                        || (enclosureDataAttr && enclosureDataAttr == "enclosure")) {
                        repeaterClass = INTERMediatorLib.getClassAttributeFromNode(repeater);
                        repeaterDataAttr = repeater.getAttribute("data-im-control");
                        if ((repeaterTag === 'DIV' || repeaterTag === 'SPAN')
                            && ((repeaterClass && repeaterClass.indexOf('_im_repeater') >= 0)
                            || (repeaterDataAttr && repeaterDataAttr == "repeater"))) {
                            return true;
                        } else if (repeaterTag === 'INPUT') {
                            repeaterType = repeater.getAttribute('type');
                            if (repeaterType
                                && ((repeaterType.indexOf('radio') >= 0 || repeaterType.indexOf('check') >= 0))) {
                                return true;
                            }
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
            var defs = [], eachDefs, reg, i, attr, matched;
            if (INTERMediatorLib.isLinkedElement(node)) {
                attr = node.getAttribute("data-im");
                if (attr !== null && attr.length > 0) {
                    reg = new RegExp("[\\s" + INTERMediator.defDivider + "]+");
                    eachDefs = attr.split(reg);
                    for (i = 0; i < eachDefs.length; i++) {
                        if (eachDefs[i] && eachDefs[i].length > 0) {
                            defs.push(resolveAlias(eachDefs[i]));
                        }
                    }
                    return defs;
                }
                if (INTERMediator.titleAsLinkInfo && node.getAttribute('TITLE') != null) {
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
                classAttr = node.getAttribute("data-im-widget");
                if (classAttr && classAttr.length > 0) {
                    reg = new RegExp("[\\s" + INTERMediator.defDivider + "]+");
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
                    reg = new RegExp("[\\s" + INTERMediator.defDivider + "]+");
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
            if (tag == 'TBODY') return 'TR';
            else if (tag == 'SELECT') return 'OPTION';
            else if (tag == 'UL') return 'LI';
            else if (tag == 'OL') return 'LI';
            else if (tag == 'DIV') return 'DIV';
            else if (tag == 'SPAN') return 'SPAN';
            return null;
        },

        getNodeInfoArray: function (nodeInfo) {
            var comps, tableName, fieldName, targetName;

            if(! nodeInfo.split)  {
                return {
                    'table': null,
                    'field': null,
                    'target': null,
                    'tableindex': null
                };
            }
            comps = nodeInfo.split(INTERMediator.separator);
            tableName = '', fieldName = '', targetName = '';
            if (comps.length == 3) {
                tableName = comps[0];
                fieldName = comps[1];
                targetName = comps[2];
            } else if (comps.length == 2) {
                tableName = comps[0];
                fieldName = comps[1];
            } else {
                fieldName = nodeInfo;
            }
            return {
                'table': tableName,
                'field': fieldName,
                'target': targetName,
                'tableindex': "_im_index_" + tableName
            };
        },

        getCalcNodeInfoArray: function (nodeInfo) {
            var comps, tableName, fieldName, targetName;

            comps = nodeInfo.split(INTERMediator.separator);
            tableName = '', fieldName = '', targetName = '';
            if (comps.length == 3) {
                tableName = comps[0];
                fieldName = comps[1];
                targetName = comps[2];
            } else if (comps.length == 2) {
                fieldName = comps[0];
                targetName = comps[1];
            } else {
                fieldName = nodeInfo;
            }
            return {
                'table': tableName,
                'field': fieldName,
                'target': targetName,
                'tableindex': "_im_index_" + tableName
            };
        },

        /* As for IE7, DOM element can't have any prototype. */

        getClassAttributeFromNode: function (node) {
            var str = '';
            if (node === null) return '';
            if (INTERMediator.isIE && INTERMediator.ieVersion < 8) {
                str = node.getAttribute('className');
            } else {
                str = node.getAttribute('class');
            }
            return str;
        },

        setClassAttributeToNode: function (node, className) {
            if (node === null) return;
            if (INTERMediator.isIE && INTERMediator.ieVersion < 8) {
                node.setAttribute('className', className);
            } else {
                node.setAttribute('class', className);
            }
        },

        addEvent: function (node, evt, func) {
            if (node.addEventListener) {
                node.addEventListener(evt, func, false);
            } else if (node.attachEvent) {
                node.attachEvent('on' + evt, func);
            }
        },

        toNumber: function (str) {
            var s = '', i, c;
            str = (new String(str)).toString();
            for (i = 0; i < str.length; i++) {
                c = str.charAt(i);
                if ((c >= '0' && c <= '9') || c == '.' || c == '-' || c == this.cachedDiditSeparator[0]) {
                    s += c;
                }
            }
            return parseFloat(s);
        },

        RoundHalfToEven: function (value, digit) {
            return value;
        },

        Round: function (value, digit) {
            var powers = Math.pow(10, digit);
            return Math.round(value * powers) / powers;
        },

        /*
         digit should be a positive value. negative value doesn't support so far.
         */
        numberFormat: function (str, digit) {
            var s, n, sign, power, underDot, underNumStr, pstr, roundedNum, underDecimalNum, integerNum;

            n = this.toNumber(str);
            sign = '';
            if (n < 0) {
                sign = '-';
                n = -n;
            }
            underDot = (digit === undefined) ? 0 : this.toNumber(digit);
            power = Math.pow(10, underDot);
            roundedNum = Math.round(n * power);
            underDecimalNum = (underDot > 0) ? roundedNum % power : 0;
            integerNum = (roundedNum - underDecimalNum) / power;
            underNumStr = (underDot > 0) ? new String(underDecimalNum) : '';
            while (underNumStr.length < underDot) {
                underNumStr = "0" + underNumStr;
            }
            n = integerNum;
            s = [];
            for (n = Math.floor(n); n > 0; n = Math.floor(n / 1000)) {
                if (n >= 1000) {
                    pstr = '000' + (n % 1000).toString();
                    s.push(pstr.substr(pstr.length - 3));
                } else {
                    s.push(n);
                }
            }
            return sign + s.reverse().join(this.cachedDiditSeparator[1])
                + (underNumStr == '' ? '' : this.cachedDiditSeparator[0] + underNumStr);
        },

        cachedDiditSeparator: null,

        digitSeparator: function () {
            return this.cachedDiditSeparator;
        },
        /* The initialization of the cachedDiditSeparator property is at the end of this object. */

        objectToString: function (obj) {
            var str, i, key;

            if (obj === null) {
                return "null";
            }
            if (typeof obj == 'object') {
                str = '';
                if (obj.constractor === Array) {
                    for (i = 0; i < obj.length; i++) {
                        str += INTERMediatorLib.objectToString(obj[i]) + ", ";
                    }
                    return "[" + str + "]";
                } else {
                    for (key in obj) {
                        str += "'" + key + "':" + INTERMediatorLib.objectToString(obj[key]) + ", ";
                    }
                    return "{" + str + "}"
                }
            } else {
                return "'" + obj + "'";
            }
        },

        getTargetTableForRetrieve: function (element) {
            if (element['view'] != null) {
                return element['view'];
            }
            return element['name'];
        },

        getTargetTableForUpdate: function (element) {
            if (element['table'] != null) {
                return element['table'];
            }
            return element['name'];
        },

        getInsertedString: function (tmpStr, dataArray) {
            var resultStr, counter;

            resultStr = tmpStr;
            if (dataArray != null) {
                for (counter = 1; counter <= dataArray.length; counter++) {
                    resultStr = resultStr.replace("@" + counter + "@", dataArray[counter - 1]);
                }
            }
            return resultStr;
        },

        getInsertedStringFromErrorNumber: function (errNum, dataArray) {
            var resultStr, counter;

            resultStr = INTERMediatorOnPage.getMessages()[errNum];
            if (dataArray != null) {
                for (counter = 1; counter <= dataArray.length; counter++) {
                    resultStr = resultStr.replace("@" + counter + "@", dataArray[counter - 1]);
                }
            }
            return resultStr;
        },

        getNamedObject: function (obj, key, named) {
            var index;
            for (index in obj) {
                if (obj[index][key] == named) {
                    return obj[index];
                }
            }
            return null;
        },

        getNamedObjectInObjectArray: function (ar, key, named) {
            var i;
            for (i = 0; i < ar.length; i++) {
                if (ar[i][key] == named) {
                    return ar[i];
                }
            }
            return null;
        },

        getNamedValueInObject: function (ar, key, named, retrieveKey) {
            var result = [], index;
            for (index in ar) {
                if (ar[index][key] == named) {
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
                if (ar[index][key1] == named1 && ar[index][key2] == named2) {
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
                recordset[ obj[index]['field'] ] = obj[index]['value'];
            }
            return recordset;
        },

        getNodePath: function (node) {
            var path = '';
            if (node.tagName === null) {
                return '';
            } else {
                return INTERMediatorLib.getNodePath(node.parentNode) + "/" + node.tagName;
            }
        },

        /*
         If the cNode parameter is like '_im_post', this function will search data-im-control="post" elements.
         */
        getElementsByClassNameOrDataAttr: function (node, cName) {
            var nodes = [];
            var attrValue = (cName.length > 5) ? cName.substr(4) : null;
            var reg = new RegExp(cName);
            checkNode(node);
            return nodes;

            function checkNode(target) {
                var className, attr;
                if (target.nodeType != 1) {
                    return;
                }
                className = INTERMediatorLib.getClassAttributeFromNode(target);
                attr = target.getAttribute("data-im-control");
                if ((className && className.match(reg)) || (attr && attrValue && attr == attrValue)) {
                    nodes.push(target);
                }
                for (var i = 0; i < target.children.length; i++) {
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
                if (target.nodeType != 1) {
                    return;
                }
                className = INTERMediatorLib.getClassAttributeFromNode(target);
                if (className && className.match(reg)) {
                    nodes.push(target);
                }
                for (var i = 0; i < target.children.length; i++) {
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
                if (target.nodeType != 1) {
                    return;
                }
                nodeId = target.getAttribute("id");
                if (nodeId && nodeId.match(reg)) {
                    nodes.push(target);
                }
                for (var i = 0; i < target.children.length; i++) {
                    checkNode(target.children[i]);
                }
            }
        }
    };

    // Initialize the cachedDiditSeparator property.
    var num, str, decimal, separator, digits;
    try {
        num = new Number(1000.1);
        str = num.toLocaleString();
        decimal = str.substr(-2, 1);
        str = str.substring(0, str.length - 2);
        separator = str.match(/[^0-9]/)[0];
        digits = str.length - str.indexOf(separator) - 1;
        instance.cachedDiditSeparator = [decimal, separator, digits];
    } catch (ex) {
        instance.cachedDiditSeparator = [".", ",", 3];
    }
    return instance;
}


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
 IMLibNodeGraph.addEdge("a","b");
 IMLibNodeGraph.addEdge("b","c");
 IMLibNodeGraph.addEdge("c","d");
 IMLibNodeGraph.addEdge("a","e");
 IMLibNodeGraph.addEdge("b","f");
 IMLibNodeGraph.addEdge("a","f");
 IMLibNodeGraph.addEdge("i","j");
 IMLibNodeGraph.addNode("x");

 The first calling of the getLeafNodesWithRemoving method returns "d", "f", "e", "j", "x".
 The second calling does "c", "i". The third one does "b", the forth one does "a".
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
    applyToAllNodes: function (f) {
        var i;
        for (i = 0; i < this.nodes.length; i++) {
            f(this.nodes[i]);
        }

    }
};

var IMLibElement = {
    setValueToIMNode: function (element, curTarget, curVal, clearField) {
        var styleName, statement, currentValue, scriptNode, typeAttr, valueAttr, textNode,
            needPostValueSet = false, nodeTag, curValues, i;
        // IE should \r for textNode and <br> for innerHTML, Others is not required to convert

        if (curVal === undefined) {
            return false;   // Or should be an error?
        }
        if (!element) {
            return false;   // Or should be an error?
        }
        if (curVal == null) {
            curVal = '';
        }

        nodeTag = element.tagName;

        if (clearField === true && curTarget == "") {
            switch (nodeTag) {
                case "INPUT":
                    element.value = "";
                    break;
                default:
                    while (element.childNodes.length > 0) {
                        element.removeChild(element.childNodes[0]);
                    }
                    break;
            }
        }

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
                    element.style[styleName] = curVal;
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
                    element.style[styleName] = curVal;
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
                    element.style[styleName] = curVal;
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
        return needPostValueSet;
    },
    getValueFromIMNode: function (element) {
        var nodeTag, typeAttr;
        
        if (element) {
            nodeTag = element.tagName;
        } else {
            return "";
        }
        if (INTERMediatorLib.isWidgetElement(element)) {
            return element._im_getValue();
        } else if (nodeTag == "INPUT") {
            typeAttr = element.getAttribute('type');
            if (typeAttr == 'checkbox' || typeAttr == 'radio') { // set the value
                return element.value;
            } else { // this node must be text field
                return element.value;
            }
        } else if (nodeTag == "SELECT") {
            return element.value;
        } else if (nodeTag == "TEXTAREA") {
            return element.value;
        } else {
            return element.innerHTML;
        }
    }
}
