/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

var INTERMediatorLib = {

    ignoreEnclosureRepeaterClassName:"_im_ignore_enc_rep",
    rollingRepeaterClassName:"_im_repeater",
    rollingEnclocureClassName:"_im_enclosure",

    generatePasswordHash: function(password)    {
        var numToHex,salt, saltHex, code, lowCode, highCode;
        numToHex = ['0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F'];
        salt = "";
        saltHex = "";
        for( i = 0 ; i < 4 ; i++ )  {
            code = Math.floor(Math.random()*(128-32)+32);
            lowCode = code & 0xF;
            highCode = (code >> 4) & 0xF;
            salt += String.fromCharCode(code);
            saltHex += numToHex[highCode] + numToHex[lowCode];
        }
        return encodeURIComponent(SHA1(password+salt)+saltHex);

    },
    getParentRepeater:function (node) {
        var currentNode = node;
        while (currentNode != null) {
            if (INTERMediatorLib.isRepeater(currentNode, true)) {
                return currentNode;
            }
            currentNode = currentNode.parentNode;
        }
        return null;
    },

    getParentEnclosure:function (node) {
        var currentNode = node;
        while (currentNode != null) {
            if (INTERMediatorLib.isEnclosure(currentNode, true)) {
                return currentNode;
            }
            currentNode = currentNode.parentNode;
        }
        return null;
    },

    isEnclosure:function (node, nodeOnly) {
        var tagName, className, children, k;

        if (!node || node.nodeType !== 1) {
            return false;
        }
        tagName = node.tagName;
        className = INTERMediatorLib.getClassAttributeFromNode(node);
        if (className && className.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterClassName) >= 0) {
            return false;
        }
        if ((tagName === 'TBODY')
            || (tagName === 'UL')
            || (tagName === 'OL')
            || (tagName === 'SELECT')
            || ((tagName === 'DIV' || tagName === 'SPAN' )
            && className
            && className.indexOf(INTERMediatorLib.rollingEnclocureClassName) >= 0)) {
            if (nodeOnly) {
                return true;
            } else {
                children = node.childNodes;
                for ( k = 0; k < children.length; k++) {
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

    isRepeater:function (node, nodeOnly) {
        var tagName, className, children, k;

        if (!node || node.nodeType !== 1) {
            return false;
        }
        tagName = node.tagName;
        className = INTERMediatorLib.getClassAttributeFromNode(node);
        if (className && className.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterClassName) >= 0) {
            return false;
        }
        if ((tagName === 'TR')
            || (tagName === 'LI')
            || (tagName === 'OPTION')
            || ((tagName === 'DIV' || tagName === 'SPAN' )
            && className
            && className.indexOf(INTERMediatorLib.rollingRepeaterClassName) >= 0)) {
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
            for ( k = 0; k < children.length; k++) {
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

    isLinkedElement:function (node) {
        var classInfo, matched;

        if (node != null) {
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

    getEnclosureSimple:function (node) {
        if (INTERMediatorLib.isEnclosure(node, true)) {
            return node;
        }
        return INTERMediatorLib.getEnclosureSimple(node.parentNode);
    },

    getEnclosure:function (node) {
        var currentNode, detectedRepeater;

        currentNode = node;
        while (currentNode != null) {
            if (INTERMediatorLib.isRepeater(currentNode)) {
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
            var repeaterTag, enclosureTag, enclosureClass, repeaterClass, repeaterType;
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
                if (enclosureClass && enclosureClass.indexOf('_im_enclosure') >= 0) {
                    repeaterClass = INTERMediatorLib.getClassAttributeFromNode(repeater);
                    if ((repeaterTag === 'DIV' || repeaterTag === 'SPAN') && repeaterClass != null && repeaterClass.indexOf('_im_repeater') >= 0) {
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

    getLinkedElementInfo:function (node) {
        var defs = [], eachDefs, i, classAttr, matched;
        if (INTERMediatorLib.isLinkedElement(node)) {
            if (INTERMediator.titleAsLinkInfo) {
                if (node.getAttribute('TITLE') != null) {
                    eachDefs = node.getAttribute('TITLE').split(INTERMediator.defDivider);
                    for ( i = 0; i < eachDefs.length; i++) {
                        defs.push(resolveAlias(eachDefs[i]));
                    }
                }
            }
            if (INTERMediator.classAsLinkInfo) {
                classAttr = INTERMediatorLib.getClassAttributeFromNode(node);
                if (classAttr !== null && classAttr.length > 0) {
                    matched = classAttr.match(/IM\[([^\]]*)\]/);
                    eachDefs = matched[1].split(INTERMediator.defDivider);
                    for ( i = 0; i < eachDefs.length; i++) {
                        defs.push(resolveAlias(eachDefs[i]));
                    }
                }
            }
            return defs;
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

    /**
     * Get the repeater tag from the enclosure tag.
     */

    repeaterTagFromEncTag:function (tag) {
        if (tag == 'TBODY') return 'TR';
        else if (tag == 'SELECT') return 'OPTION';
        else if (tag == 'UL') return 'LI';
        else if (tag == 'OL') return 'LI';
        else if (tag == 'DIV') return 'DIV';
        else if (tag == 'SPAN') return 'SPAN';
        return null;
    },

    getNodeInfoArray:function (nodeInfo) {
        var comps, tableName;

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
            'table':tableName,
            'field':fieldName,
            'target':targetName
        };
    },

    /* As for IE7, DOM element can't have any prototype. */

    getClassAttributeFromNode:function (node) {
        var str = '';
        if (node == null) return '';
        if (INTERMediator.isIE && INTERMediator.ieVersion < 8) {
            str = node.getAttribute('className');
        } else {
            str = node.getAttribute('class');
        }
        return str;
    },

    setClassAttributeToNode:function (node, className) {
        if (node == null) return;
        if (INTERMediator.isIE && INTERMediator.ieVersion < 8) {
            node.setAttribute('className', className);
        } else {
            node.setAttribute('class', className);
        }
    },

    addEvent:function (node, evt, func) {
        if (node.addEventListener) {
            node.addEventListener(evt, func, false);
        } else if (node.attachEvent) {
            node.attachEvent('on' + evt, func);
        }
    },

    toNumber:function (str) {
        var s = '', i, c;
        str = (new String(str)).toString();
        for ( i = 0; i < str.length; i++) {
            c = str.charAt(i);
            if ((c >= '0' && c <= '9') || c == '-' || c == '.') {
                s += c;
            }
        }
        return parseFloat(s);
    },

    RoundHalfToEven: function(value, digit) {
        return value;
    },

    numberFormat:function (str, digit) {
        var s, n, sign, f, underDot, underNumStr;
        s = new Array();
        n = INTERMediatorLib.toNumber(str);
        sign = '';
        if (n < 0) {
            sign = '-';
            n = -n;
        }
        f = n - Math.floor(n);
        //    if (f == 0) f = '';
        for (n = Math.floor(n); n > 0; n = Math.floor(n / 1000)) {
            if (n >= 1000) {
                s.push(('000' + (n % 1000).toString()).substr(-3));
            } else {
                s.push(n);
            }
        }
        underDot = (digit == null) ? 0 : INTERMediatorLib.toNumber(digit);
        underNumStr = (underDot == 0) ? '' : new String(Math.floor(f * Math.pow(10, underDot)));
        while (underNumStr.length < underDot) {
            underNumStr = "0" + underNumStr;
        }
        return sign + s.reverse().join(',') + (underNumStr == '' ? '' : '.' + underNumStr);
    },

    objectToString:function (obj) {
        var str, i, key;

        if ( obj === null ) {
            return "null";
        }
        if (typeof obj == 'object') {
            str = '';
            if (obj.constractor === Array) {
                for ( i = 0; i < obj.length; i++) {
                    str += INTERMediatorLib.objectToString(obj[i]) + ", ";
                }
                return "[" + str + "]";
            } else {
                for ( key in obj) {
                    str += "'" + key + "':" + INTERMediatorLib.objectToString(obj[key]) + ", ";
                }
                return "{" + str + "}"
            }
        } else {
            return "'" + obj + "'";
        }
    },

    getTargetTableForRetrieve:function (element) {
        if (element['view'] != null) {
            return element['view'];
        }
        return element['name'];
    },

    getTargetTableForUpdate:function (element) {
        if (element['table'] != null) {
            return element['table'];
        }
        return element['name'];
    },

    getInsertedString:function (tmpStr, dataArray) {
        var resultStr, counter;

        resultStr = tmpStr;
        if (dataArray != null) {
            for ( counter = 1; counter <= dataArray.length; counter++) {
                resultStr = resultStr.replace("@" + counter + "@", dataArray[counter - 1]);
            }
        }
        return resultStr;
    },

    getInsertedStringFromErrorNumber:function (errNum, dataArray) {
        var resultStr, counter;

        resultStr = INTERMediatorOnPage.getMessages()[errNum];
        if (dataArray != null) {
            for ( counter = 1; counter <= dataArray.length; counter++) {
                resultStr = resultStr.replace("@" + counter + "@", dataArray[counter - 1]);
            }
        }
        return resultStr;
    },

    getNamedObject:function (obj, key, named) {
        var index;
        for ( index in obj) {
            if (obj[index][key] == named) {
                return obj[index];
            }
        }
        return null;
    },

    getNamedObjectInObjectArray:function (ar, key, named) {
        var i;
        for ( i = 0; i < ar.length; i++) {
            if (ar[i][key] == named) {
                return ar[i];
            }
        }
        return null;
    },

    getNamedValueInObject:function (ar, key, named, retrieveKey) {
        var result = [],index;
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

    is_array:function (target) {
        return target
            && typeof target === 'object'
            && typeof target.length === 'number'
            && typeof target.splice === 'function'
            && !(target.propertyIsEnumerable('length'));
    },

    getNamedValuesInObject:function (ar, key1, named1, key2, named2, retrieveKey) {
        var result = [],index;
        for ( index in ar) {
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

    getRecordsetFromFieldValueObject:function (obj) {
        var recordset = {}, index;
        for ( index in obj) {
            recordset[ obj[index]['field'] ] = obj[index]['value'];
        }
        return recordset;
    },

    getNodePath:function (node) {
        var path = '';
        if (node.tagName == null) {
            return '';
        } else {
            return INTERMediatorLib.getNodePath(node.parentNode) + "/" + node.tagName;
        }
    }
};
