/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
// Cleaning-up by http://jsbeautifier.org/ or Eclipse's Formatting


function INTERMediatorCheckBrowser(deleteNode) {
    var positiveList = IM_browserCompatibility();
    var matchAgent = false;
    var matchOS = false;
    var versionStr;
    for (var agent in  positiveList) {
        if (navigator.userAgent.toUpperCase().indexOf(agent.toUpperCase()) > -1) {
            matchAgent = true;
            if (positiveList[agent] instanceof Object) {
                for (var os in positiveList[agent]) {
                    if (navigator.platform.toUpperCase().indexOf(os.toUpperCase()) > -1) {
                        matchOS = true;
                        versionStr = positiveList[agent][os];
                        break;
                    }
                }
            } else {
                matchOS = true;
                versionStr = positiveList[agent];
                break;
            }
        }
    }
    var judge = false;
    if (matchAgent == true && matchOS == true) {
        var specifiedVersion = parseInt(versionStr);
        var versionNum;
        if (navigator.appVersion.indexOf('MSIE') > -1) {
            var msieMark = navigator.appVersion.indexOf('MSIE');
            var dotPos = navigator.appVersion.indexOf('.', msieMark);
            versionNum = parseInt(navigator.appVersion.substring(msieMark + 4, dotPos));
            /*
             As for the appVersion property of IE, refer http://msdn.microsoft.com/en-us/library/aa478988.aspx
             */
        } else {
            var dotPos = navigator.appVersion.indexOf('.');
            versionNum = parseInt(navigator.appVersion.substring(0, dotPos));
        }
        if (versionStr.indexOf('-') > -1) {
            judge = (specifiedVersion >= versionNum);
        } else if (versionStr.indexOf('+') > -1) {
            judge = (specifiedVersion <= versionNum);
        } else {
            judge = (specifiedVersion == versionNum);
        }
    }
    if (judge == true) {
        if (deleteNode != null) {
            deleteNode.parentNode.removeChild(deleteNode);
        }
    } else {
        var bodyNode = document.getElementsByTagName('BODY')[0];
        bodyNode.innerHTML = '<div align="center"><font color="gray"><font size="+2">'
            + IM_getMessages()[1022] + '</font><br>'
            + IM_getMessages()[1023] + '<br>' + navigator.userAgent + '</font></div>';
    }
    return judge;
}

var INTERMediatorLib = {
    getParentRepeater:function(node) {
        var currentNode = node;
        while (currentNode != null) {
            if (INTERMediatorLib.isRepeater(currentNode, true)) {
                return currentNode;
            }
            currentNode = currentNode.parentNode;
        }
        return null;
    },

    getParentEnclosure:function(node) {
        var currentNode = node;
        while (currentNode != null) {
            if (INTERMediatorLib.isEnclosure(currentNode, true)) {
                return currentNode;
            }
            currentNode = currentNode.parentNode;
        }
        return null;
    },

    isEnclosure:function(node, nodeOnly) {
        if (node == null || node.nodeType != 1) return false;
        var tagName = node.tagName;
        var className = INTERMediatorLib.getClassAttributeFromNode(node);
        if ((tagName == 'TBODY') || (tagName == 'UL') || (tagName == 'OL') || (tagName == 'SELECT')
            || ((tagName == 'DIV' || tagName == 'SPAN' ) && className != null && className.indexOf('_im_enclosure') >= 0)) {
            if (nodeOnly) {
                return true;
            } else {
                var countChild = node.childNodes.length;
                for (var k = 0; k < countChild; k++) {
                    if (INTERMediatorLib.isRepeater(node.childNodes[k], false)) {
                        return true;
                    }
                }
            }
        }
        return false;
    },

    isRepeater:function(node, nodeOnly) {
        if (node.nodeType != 1) return false;
        var tagName = node.tagName;
        var className = INTERMediatorLib.getClassAttributeFromNode(node);
        if ((tagName == 'TR') || (tagName == 'LI') || (tagName == 'OPTION')
            || ((tagName == 'DIV' || tagName == 'SPAN' ) && className != null && className.indexOf('_im_repeater') >= 0)) {
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
            var countChild = node.childNodes.length;
            for (var k = 0; k < countChild; k++) {
                var nType = node.childNodes[k].nodeType;
                if (nType == 1) { // Work for an element
                    if (INTERMediatorLib.isLinkedElement(node.childNodes[k])) {
                        return true;
                    } else if (searchLinkedElement(node.childNodes[k])) {
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

    isLinkedElement:function(node) {
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
                var classInfo = INTERMediatorLib.getClassAttributeFromNode(node);
                if (classInfo != null) {
                    var matched = classInfo.match(/IM\[.*\]/);
                    if (matched != null) {
                        return true;
                    }
                }
            }
        }
        return false;
    },

    detectedRepeater:null,

    getEnclosureSimple:function(node) {
        if (INTERMediatorLib.isEnclosure(node, true)) {
            return node;
        }
        return INTERMediatorLib.getEnclosureSimple(node.parentNode);
    },

    getEnclosure:function(node) {

        var currentNode = node;
        while (currentNode != null) {
            if (INTERMediatorLib.isRepeater(currentNode)) {
                INTERMediatorLib.detectedRepeater = currentNode;
            } else if (isRepeaterOfEnclosure(INTERMediatorLib.detectedRepeater, currentNode)) {
                INTERMediatorLib.detectedRepeater = null;
                return currentNode;
            }
            currentNode = currentNode.parentNode;
        }
        return null;

        /**
         * Check the pair of nodes in argument is valid for repater/enclosure.
         */

        function isRepeaterOfEnclosure(repeater, enclosure) {
            if (repeater == null || enclosure == null) return false;
            var repeaterTag = repeater.tagName;
            var enclosureTag = enclosure.tagName;
            if ((repeaterTag == 'TR' && enclosureTag == 'TBODY')
                || (repeaterTag == 'OPTION' && enclosureTag == 'SELECT')
                || (repeaterTag == 'LI' && enclosureTag == 'OL')
                || (repeaterTag == 'LI' && enclosureTag == 'UL')) {
                return true;
            }
            if ((enclosureTag == 'DIV' || enclosureTag == 'SPAN' )) {
                var enclosureClass = INTERMediatorLib.getClassAttributeFromNode(enclosure);
                if (enclosureClass != null && enclosureClass.indexOf('_im_enclosure') >= 0) {
                    var repeaterClass = INTERMediatorLib.getClassAttributeFromNode(repeater);
                    if ((repeaterTag == 'DIV' || repeaterTag == 'SPAN') && repeaterClass != null && repeaterClass.indexOf('_im_repeater') >= 0) {
                        return true;
                    } else if (repeaterTag == 'INPUT') {
                        var repeaterType = repeater.getAttribute('type');
                        if (repeaterType != null && ((repeaterType.indexOf('radio') >= 0
                            || repeaterType.indexOf('check') >= 0))) {
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

    getLinkedElementInfo:function(node) {
        if (INTERMediatorLib.isLinkedElement(node)) {
            var defs = new Array();
            if (INTERMediator.titleAsLinkInfo) {
                if (node.getAttribute('TITLE') != null) {
                    var eachDefs = node.getAttribute('TITLE').split(INTERMediator.defDivider);
                    for (var i = 0; i < eachDefs.length; i++) {
                        defs.push(resolveAlias(eachDefs[i]));
                    }
                }
            }
            if (INTERMediator.classAsLinkInfo) {
                var classAttr = INTERMediatorLib.getClassAttributeFromNode(node);
                if (classAttr != null && classAttr.length > 0) {
                    var matched = classAttr.match(/IM\[([^\]]*)\]/);
                    var eachDefs = matched[1].split(INTERMediator.defDivider);
                    for (var i = 0; i < eachDefs.length; i++) {
                        defs.push(resolveAlias(eachDefs[i]));
                    }
                }
            }
            return defs;
        }
        return false;

        function resolveAlias(def) {
            var options = IM_getOptions();
            var aliases = options['aliases'];
            if (aliases != null && aliases[def] != null) {
                return aliases[def];
            }
            return def;
        }
    },

    /**
     * Get the repeater tag from the enclosure tag.
     */

    repeaterTagFromEncTag:function(tag) {
        if (tag == 'TBODY') return 'TR';
        else if (tag == 'SELECT') return 'OPTION';
        else if (tag == 'UL') return 'LI';
        else if (tag == 'OL') return 'LI';
        else if (tag == 'DIV') return 'DIV';
        else if (tag == 'SPAN') return 'SPAN';
        return null;
    },

    getNodeInfoArray:function(nodeInfo) {
        var comps = nodeInfo.split(INTERMediator.separator);
        var tableName = '', fieldName = '', targetName = '';
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

    getClassAttributeFromNode:function(node) {
        if (node == null) return '';
        var str = '';
        if (INTERMediator.isIE && INTERMediator.ieVersion < 8) {
            str = node.getAttribute('className');
        } else {
            str = node.getAttribute('class');
        }
        return str;
    },

    setClassAttributeToNode:function(node, className) {
        if (node == null) return;
        if (INTERMediator.isIE && INTERMediator.ieVersion < 8) {
            node.setAttribute('className', className);
        } else {
            node.setAttribute('class', className);
        }
    },

    addEvent:function(node, evt, func) {
        if (node.addEventListener) {
            node.addEventListener(evt, func, false);
        } else if (node.attachEvent) {
            node.attachEvent('on' + evt, func);
        }
    },

    toNumber:function(str) {
        var s = '';
        for (var i = 0; i < str.length; i++) {
            var c = str.charAt(i);
            if ((c >= '0' && c <= '9') || c == '-' || c == '.') s += c;
        }
        return new Number(s);
    },

    numberFormat:function(str) {
        var s = new Array();
        var n = new Number(str);
        var sign = '';
        if (n < 0) {
            sign = '-';
            n = -n;
        }
        var f = n - Math.floor(n);
        if (f == 0) f = '';
        for (n = Math.floor(n); n > 0; n = Math.floor(n / 1000)) {
            if (n > 1000) {
                s.push(('000' + (n % 1000).toString()).substr(-3));
            } else {
                s.push(n);
            }
        }
        return sign + s.reverse().join(',') + f;
    },

    objectToString:function(obj) {
        if (obj == null) {
            return "null";
        }
        if (typeof obj == 'object') {
            var str = '';
            if (obj.constractor === Array) {
                for (var i = 0; i < obj.length; i++) {
                    str += INTERMediatorLib.objectToString(obj[i]) + ", ";
                }
                return "[" + str + "]";
            } else {
                for (var key in obj) {
                    str += "'" + key + "':" + INTERMediatorLib.objectToString(obj[key]) + ", ";
                }
                return "{" + str + "}"
            }
        } else {
            return "'" + obj + "'";
        }
    },

    getTargetTableForRetrieve:function(element) {
        if (element['view'] != null) {
            return element['view'];
        }
        return element['name'];
    },

    getTargetTableForUpdate:function(element) {
        if (element['table'] != null) {
            return element['table'];
        }
        return element['name'];
    },

    getInsertedString:function(tmpStr, dataArray) {
        var resultStr = tmpStr;
        if (dataArray != null) {
            for (var counter = 1; counter <= dataArray.length; counter++) {
                resultStr = resultStr.replace("@" + counter + "@", dataArray[counter - 1]);
            }
        }
        return resultStr;
    },

    getInsertedStringFromErrorNumber:function(errNum, dataArray) {
        var resultStr = IM_getMessages()[errNum];
        if (dataArray != null) {
            for (var counter = 1; counter <= dataArray.length; counter++) {
                resultStr = resultStr.replace("@" + counter + "@", dataArray[counter - 1]);
            }
        }
        return resultStr;
    },

    getNamedObject:function(obj, key, named) {
        for (var index in obj) {
            if (obj[index][key] == named) {
                return obj[index];
            }
        }
        return null;
    },

    getNamedObjectInObjectArray:function(ar, key, named) {
        for (var i = 0; i < ar.length; i++) {
            if (ar[i][key] == named) {
                return ar[i];
            }
        }
        return null;
    },

    getNamedValueInObject:function(ar, key, named, retrieveKey) {
        for (var index in ar) {
            if (ar[index][key] == named) {
                return ar[index][retrieveKey];
            }
        }
        return null;
    },

    getRecordsetFromFieldValueObject:function(obj) {
        var recordset = {};
        for (var index in obj) {
            recordset[ obj[index]['field'] ] = obj[index]['value'];
        }
        return recordset;
    },

    getNodePath:function(node) {
        var path = '';
        if (node.tagName == null) {
            return '';
        } else {
            return INTERMediatorLib.getNodePath(node.parentNode) + "/" + node.tagName;
        }
    }
}

var INTERMediatorOnPage = {
    /*
     Seek nodes from the repeater of "fromNode" parameter.
     */
    getNodeIdFromIMDefinition:function(imDefinition, fromNode) {
        var repeaterNode = INTERMediatorLib.getParentRepeater(fromNode);
        return seekNode(repeaterNode, imDefinition);

        function seekNode(node, imDefinition) {
            if (node.nodeType != 1) {
                return null;
            }
            var children = node.childNodes;
            if (children == null) {
                return null;
            } else {
                for (var i = 0; i < children.length; i++) {
                    if (children[i].getAttribute != null) {
                        var thisClass = children[i].getAttribute('class');
                        var thisTitle = children[i].getAttribute('title');
                        if ((thisClass != null && thisClass.indexOf(imDefinition) > -1)
                            || (thisTitle != null && thisTitle.indexOf(imDefinition) > -1)) {
                            return children[i].getAttribute('id');
                            break;
                        } else {
                            var returnValue = seekNode(children[i], imDefinition);
                            if (returnValue != null) {
                                return returnValue;
                            }
                        }
                    }
                }
            }
            return null;
        }
    },

    getNodeIdsFromIMDefinition:function(imDefinition, fromNode) {
        var enclosureNode = INTERMediatorLib.getParentEnclosure(fromNode);
        if (enclosureNode != null) {
            var nodeIds = [];
            seekNode(enclosureNode, imDefinition);
        }
        return nodeIds;

        function seekNode(node, imDefinition) {
            if (node.nodeType != 1) {
                return null;
            }
            var children = node.childNodes;
            if (children == null) {
                return null;
            } else {
                for (var i = 0; i < children.length; i++) {
                    if (children[i].getAttribute != null) {
                        var thisClass = children[i].getAttribute('class');
                        var thisTitle = children[i].getAttribute('title');
                        if ((thisClass != null && thisClass.indexOf(imDefinition) > -1)
                            || (thisTitle != null && thisTitle.indexOf(imDefinition) > -1)) {
                            nodeIds.push(children[i].getAttribute('id'));
                        }
                        seekNode(children[i], imDefinition);
                    }
                }
            }
            return null;
        }
    }
}

var INTERMediator = {
    /*
     Properties
     */
    debugMode:false,
    // Show the debug messages at the top of the page.
    separator:'@',
    // This must be referred as 'INTERMediator.separator'. Don't use 'this.separator'
    defDivider:'|',
    // Same as the "separator".
    additionalCondition:[],
    // This array should be [{tableName: {field:xxx,operator:xxx,value:xxxx}}, ... ]
    defaultTargetInnerHTML:false,
    // For general elements, if target isn't specified, the value will be set to innerHTML.
    // Otherwise, set as the text node.
    navigationLabel:null,
    // Navigation is controlled by this parameter.
    startFrom:0,
    // Start from this number of record for "skipping" records.
    pagedSize:0,
    pagedAllCount:0,
    currentEncNumber:0,
    isIE:false,
    ieVersion:-1,
    titleAsLinkInfo:true,
    classAsLinkInfo:true,

    // Remembering Objects
    updateRequiredObject:null,
    /*
     {   id-value:               // For the node of this id attribute.
     {targetattribute:,      // about target
     initialvalue:,          // The value from database.
     name:
     field:id,               // about target field
     keying:id=1,            // The key field specifier to identify this record.
     foreignfield:,          // foreign field name
     foreignvalue:, }        // foreign field value
     */
    keyFieldObject:null,
    /* inside of keyFieldObject
     {node:xxx,         // The node information
     original:xxx       // Copy of childs
     name:xxx,             // name of context
     foreign-value:Recordset as {f1:v1, f2:v2, ..} ,not [{field:xx, value:xx},..]
     target:xxxx}       // Related (depending) node's id attribute value.
     */
    rootEnclosure:null,
    // Storing to retrieve the page to initial condtion.
    // {node:xxx, parent:xxx, currentRoot:xxx, currentAfter:xxxx}
    errorMessages:[],
    debugMessages:[],


    //=================================
    // Message for Programmers
    //=================================
    flushMessage:function() {
        if (INTERMediator.errorMessages.length > 0) {
            var debugNode = document.getElementById('easypage_error_panel_4873643897897');
            if (debugNode == null) {
                debugNode = document.createElement('div');
                debugNode.setAttribute('id', 'easypage_error_panel_4873643897897');
                debugNode.style.backgroundColor = '#FFDDDD';
                var title = document.createElement('h3');
                title.appendChild(document.createTextNode('Error Info from INTER-Mediator'));
                title.appendChild(document.createElement('hr'));
                debugNode.appendChild(title);
                var body = document.getElementsByTagName('body')[0];
                body.insertBefore(debugNode, body.firstChild);
            }
            for (var i = 0; i < INTERMediator.errorMessages.length; i++) {
                debugNode.appendChild(document.createTextNode(INTERMediator.errorMessages[i]));
                debugNode.appendChild(document.createElement('hr'));
            }
        }
        if (INTERMediator.debugMode && INTERMediator.debugMessages.length > 0) {
            var debugNode = document.getElementById('easypage_debug_panel_4873643897897');
            if (debugNode == null) {
                debugNode = document.createElement('div');
                debugNode.setAttribute('id', 'easypage_debug_panel_4873643897897');
                debugNode.style.backgroundColor = '#DDDDDD';
                var clearButton = document.createElement('button');
                clearButton.setAttribute('title', 'clear');
                INTERMediatorLib.addEvent(clearButton, 'click', function() {
                    var target = document.getElementById('easypage_debug_panel_4873643897897');
                    target.parentNode.removeChild(target);
                });
                var tNode = document.createTextNode('clear');
                clearButton.appendChild(tNode);
                var title = document.createElement('h3');
                title.appendChild(document.createTextNode('Debug Info from INTER-Mediator'));
                title.appendChild(clearButton);
                title.appendChild(document.createElement('hr'));
                debugNode.appendChild(title);
                var body = document.getElementsByTagName('body')[0];
                body.insertBefore(debugNode, body.firstChild);
            }

            for (var i = 0; i < INTERMediator.debugMessages.length; i++) {
                debugNode.appendChild(document.createTextNode(INTERMediator.debugMessages[i]));
                debugNode.appendChild(document.createElement('hr'));
            }
        }
        INTERMediator.errorMessages = [];
        INTERMediator.debugMessages = [];
    },

    //=================================
    // User interactions
    //=================================
    /*
     valueChange
     Parameters:
     */
    valueChange:function(idValue) {
        var newValue = null;
        var changedObj = document.getElementById(idValue);
        if (changedObj != null) {
            // Check the current value of the field
            var objectSpec = INTERMediator.updateRequiredObject[idValue];
            var keyingComp = objectSpec['keying'].split('=');
            var keyingField = keyingComp[0];
            keyingComp.shift();
            var keyingValue = keyingComp.join('=');
            var currentVal = IM_DBAdapter.db_query({
                name:objectSpec['name'],
                records:1,
                paging:objectSpec['paging'],
                fields:[objectSpec['field']],
                parentkeyvalue:null,
                conditions:[
                    {field:keyingField, operator:'=', value:keyingValue}
                ],
                useoffset:false});
            if (currentVal == null || currentVal[0] == null || currentVal[0][objectSpec['field']] == null) {
                alert(INTERMediatorLib.getInsertedString(IM_getMessages()[1003], [objectSpec['field']]));
                INTERMediator.flushMessage();
                return;
            }
            currentVal = currentVal[0][objectSpec['field']];
            if (objectSpec['initialvalue'] != currentVal) {
                // The value of database and the field is diffrent. Others must be changed this field.
                if (!confirm(INTERMediatorLib.getInsertedString(
                    IM_getMessages()[1001], [objectSpec['initialvalue'], currentVal]))) {
                    INTERMediator.flushMessage();
                    return;
                }
            }

            if (changedObj.tagName == 'TEXTAREA') {
                newValue = changedObj.value;
            } else if (changedObj.tagName == 'SELECT') {
                newValue = changedObj.value;
            } else if (changedObj.tagName == 'INPUT') {
                var objType = changedObj.getAttribute('type');
                if (objType != null) {
                    if (objType == 'checkbox') {
                        var valueAttr = changedObj.getAttribute('value');
                        if (changedObj.checked) {
                            newValue = valueAttr;
                        } else {
                            newValue = '';
                        }
                    } else if (objType == 'radio') {
                        newValue = changedObj.value;
                    } else { //text, password
                        newValue = changedObj.value;
                    }
                }
            }
            if (newValue != null) {
                var criteria = objectSpec['keying'].split('=');
                IM_DBAdapter.db_update({
                    name:objectSpec['name'],
                    conditions:[
                        {field:criteria[0], operator:'=', value:criteria[1]}
                    ],
                    dataset:[
                        {field:objectSpec['field'], value:newValue}
                    ]});
                objectSpec['initialvalue'] = newValue;
                var updateNodeId = objectSpec['updatenodeid'];
                var needUpdate = false;
                for (var i = 0; i < INTERMediator.keyFieldObject.length; i++) {
                    for (var j = 0; j < INTERMediator.keyFieldObject[i]['target'].length; j++) {
                        if (INTERMediator.keyFieldObject[i]['target'][j] == idValue) {
                            needUpdate = true;
                        }
                    }
                }
                if (needUpdate) {
                    for (var i = 0; i < INTERMediator.keyFieldObject.length; i++) {
                        if (INTERMediator.keyFieldObject[i]['node'].getAttribute('id') == updateNodeId) {
                            INTERMediator.construct(false, i);
                            break;
                        }
                    }
                }
            }
        }
        INTERMediator.flushMessage();
    },

    deleteButton:function(targetName, keyField, keyValue, removeNodes) {
        IM_DBAdapter.db_delete({
            name:targetName,
            conditions:[
                {field:keyField, operator:'=', value:keyValue}
            ]
        });
        for (var key in removeNodes) {
            var removeNode = document.getElementById(removeNodes[key]);
            removeNode.parentNode.removeChild(removeNode);
        }
        INTERMediator.flushMessage();
    },

    insertButton:function(targetName, foreignValues, updateNodes, removeNodes) {
        var currentContext = INTERMediatorLib.getNamedObject(IM_getDataSources(), 'name', targetName);
        var recordSet = [];
        if (foreignValues != null) {
            for (var fieldName in foreignValues) {
                recordSet.push({field:fieldName, value:foreignValues[fieldName]});
            }
        }
        if (currentContext['default-values'] != null) {
            for (var index in currentContext['default-values']) {
                recordSet.push({
                    field:currentContext['default-values'][index]['field'],
                    value:currentContext['default-values'][index]['value']});
            }
        }
        IM_DBAdapter.db_createRecord({name:targetName, dataset:recordSet});
        for (var key in removeNodes) {
            var removeNode = document.getElementById(removeNodes[key]);
            removeNode.parentNode.removeChild(removeNode);
        }
        for (var i = 0; i < INTERMediator.keyFieldObject.length; i++) {
            if (INTERMediator.keyFieldObject[i]['node'].getAttribute('id') == updateNodes) {
                INTERMediator.keyFieldObject[i]['foreign-value'] = foreignValues;
                INTERMediator.construct(false, i);
                break;
            }
        }
        INTERMediator.flushMessage();
    },

    insertRecordFromNavi:function(targetName, keyField) {
        var ds = IM_getDataSources(); // Get DataSource parameters
        var targetKey = '';
        for (var key in ds) { // Search this table from DataSource
            if (INTERMediatorLib.getTargetTableForUpdate(ds[key]) == targetName) {
                targetKey = key;
                break;
            }
        }

        var recordSet = [];
        for (var index in ds[key]['default-values']) {
            recordSet.push({
                field:ds[key]['default-values'][index]['field'],
                value:ds[key]['default-values'][index]['value']});
        }
        var newId = IM_DBAdapter.db_createRecord({name:targetName, dataset:recordSet});
        if (newId > -1) {
            var restore = INTERMediator.additionalCondition;
            INTERMediator.startFrom = 0;
            var fieldObj = {
                field:keyField,
                value:newId
            };
            INTERMediator.additionalCondition = {};
            INTERMediator.additionalCondition[targetName] = fieldObj;
            INTERMediator.construct(true);
            INTERMediator.additionalCondition = restore;
        }
        INTERMediator.flushMessage();
    },

    deleteRecordFromNavi:function(targetName, keyField, keyValue) {
        IM_DBAdapter.db_delete({
            name:targetName,
            conditions:[
                {field:keyField, operator:'=', value:keyValue}
            ]
        });
        if (INTERMediator.pagedAllCount - INTERMediator.startFrom < 2) {
            INTERMediator.startFrom--;
            if (INTERMediator.startFrom < 0) {
                INTERMediator.startFrom = 0;
            }
        }
        INTERMediator.construct(true);
        INTERMediator.flushMessage();
    },
    /*
     updateNodeId: function (nodeId) {
     for (var i = 0; i < INTERMediator.keyFieldObject.length; i++) {
     if (INTERMediator.keyFieldObject[i]['target'] == nodeId) {
     INTERMediator.construct(false, i);
     }
     }
     },
     */
    partialConstructing:false,
    objectReference:{},

    //=================================
    // Construct Page
    //=================================
    /**
     * Construct the Web Page with DB Data
     * You should call here when you show the page.
     *
     * parameter: fromStart: true=construct page, false=construct partially
     */
    construct:function(fromStart, indexOfKeyFieldObject) {

        var currentLevel = 0;
        var linkedNodes;
        var postSetFields = [];
        var buttonIdNum = 1;
        var deleteInsertOnNavi = [];

        if (fromStart) {
            INTERMediator.partialConstructing = false;
            pageConstruct();
        } else {
            INTERMediator.partialConstructing = true;
            partialConstruct(indexOfKeyFieldObject);
        }
        INTERMediator.flushMessage(); // Show messages

        /*

         */
        function partialConstruct(indexOfKeyFieldObject) {
            // Create parent table essentials.
            var updateContext = INTERMediator.keyFieldObject[indexOfKeyFieldObject]['name'];
            var updateNode = INTERMediator.keyFieldObject[indexOfKeyFieldObject]['node'];
            while (updateNode.firstChild) {
                updateNode.removeChild(updateNode.firstChild);
            }
            var originalNodes = INTERMediator.keyFieldObject[indexOfKeyFieldObject]['original'];
            for (var i = 0; i < originalNodes.length; i++) {
                updateNode.appendChild(originalNodes[i]);
            }
            var beforeKeyFieldObjectCount = INTERMediator.keyFieldObject.length;
            var currentContext = INTERMediatorLib.getNamedObject(IM_getDataSources(), 'name', updateContext);
            var parentRecordset = {};
            for (var field in INTERMediator.keyFieldObject[indexOfKeyFieldObject]['foreign-value']) {
                var joinFled = INTERMediatorLib.getNamedValueInObject(
                    currentContext['relation'], 'foreign-key', field, 'join-field');
                parentRecordset[joinFled]
                    = INTERMediator.keyFieldObject[indexOfKeyFieldObject]['foreign-value'][field];
            }
            postSetFields = [];

            seekEnclosureNode(
                updateNode, parentRecordset, updateContext,
                INTERMediatorLib.getEnclosureSimple(updateNode), null);

            for (var i = 0; i < postSetFields.length; i++) {
                document.getElementById(postSetFields[i]['id']).value = postSetFields[i]['value'];
            }
            for (var i = beforeKeyFieldObjectCount + 1; i < INTERMediator.keyFieldObject.length; i++) {
                var currentNode = INTERMediator.keyFieldObject[i];
                var currentID = currentNode['node'].getAttribute('id');
                if (currentNode['target'] == null) {
                    var enclosure;
                    if (currentID != null && currentID.match(/IM[0-9]+-[0-9]+/)) {
                        enclosure = INTERMediatorLib.getParentRepeater(currentNode['node']);
                    } else {
                        enclosure = INTERMediatorLib.getParentRepeater(
                            INTERMediatorLib.getParentEnclosure(currentNode['node']));
                    }
                    if (enclosure != null) {
                        var targetNode = getEnclosedNode(enclosure,
                            currentNode['name'], currentNode['foreign-value'][0]['field']);
                        if (targetNode) {
                            currentNode['target'] = targetNode.getAttribute('id');
                        }
                    }
                }
            }

        }

        function pageConstruct() {
            INTERMediator.keyFieldObject = [];
            INTERMediator.updateRequiredObject = {};
            INTERMediator.currentEncNumber = 1;

            // Detect Internet Explorer and its version.
            var ua = navigator.userAgent;
            var msiePos = ua.toLocaleUpperCase().indexOf('MSIE');
            if (msiePos >= 0) {
                INTERMediator.isIE = true;
                for (var i = msiePos + 4; i < ua.length; i++) {
                    var c = ua.charAt(i);
                    if (c != ' ' && c != '.' && (c < '0' || c > '9')) {
                        INTERMediator.ieVersion = INTERMediatorLib.toNumber(ua.substring(msiePos + 4, i));
                        break;
                    }
                }
            }
            // Restoring original HTML Document from backup data.
            var bodyNode = document.getElementsByTagName('BODY')[0];
            if (INTERMediator.rootEnclosure == null) {
                INTERMediator.rootEnclosure = bodyNode.innerHTML;
            } else {
                bodyNode.innerHTML = INTERMediator.rootEnclosure;
            }
            postSetFields = [];

            seekEnclosureNode(bodyNode, null, null, null, null);

            // After work to set up popup menus.
            for (var i = 0; i < postSetFields.length; i++) {
                document.getElementById(postSetFields[i]['id']).value = postSetFields[i]['value'];
            }
            for (var i = 0; i < INTERMediator.keyFieldObject.length; i++) {
                var currentNode = INTERMediator.keyFieldObject[i];
                var currentID = currentNode['node'].getAttribute('id');
                if (currentNode['target'] == null) {
                    var enclosure;
                    if (currentID != null && currentID.match(/IM[0-9]+-[0-9]+/)) {
                        enclosure = INTERMediatorLib.getParentRepeater(currentNode['node']);
                    } else {
                        enclosure = INTERMediatorLib.getParentRepeater(
                            INTERMediatorLib.getParentEnclosure(currentNode['node']));
                    }
                    if (enclosure != null) {
                        var targetNode = getEnclosedNode(enclosure, currentNode['name'], currentNode['field']);
                        if (targetNode) {
                            currentNode['target'] = targetNode.getAttribute('id');
                        }
                    }
                }
            }
            navigationSetup();
            appendCredit();
        }

        /**
         * Seeking nodes and if a node is an enclosure, proceed repeating.
         */

        function seekEnclosureNode(node, currentRecord, currentTable, parentEnclosure, objectReference) {
            var nType = node.nodeType;
            if (nType == 1) { // Work for an element
                if (INTERMediatorLib.isEnclosure(node, false)) { // Linked element and an enclosure
                    expandEnclosure(node, currentRecord, currentTable, parentEnclosure, objectReference);
                } else {
                    var childs = node.childNodes; // Check all child nodes.
                    for (var i = 0; i < childs.length; i++) {
                        if (childs[i].nodeType == 1) {
                            seekEnclosureNode(childs[i], currentRecord, currentTable, parentEnclosure, objectReference);
                        }
                    }
                }
            }
        }

        /**
         * Expanding an enclosure.
         */

        function expandEnclosure(node, currentRecord, currentTable, parentEnclosure, parentObjectInfo) {
            /*    INTERMediator.debugMessages.push( "expandEnclosure("+INTERMediatorLib.getNodePath(node) + "#" +
             currentTable+"#"+INTERMediatorLib.objectToString(currentRecord)+"#"
             +(parentEnclosure==null?'null':parentEnclosure.getAttribute('id'))+"#"
             +INTERMediatorLib.objectToString(parentObjectInfo));*/
            currentLevel++;
            INTERMediator.currentEncNumber++;
            var objectReference = {};

            var linkedElmCounter = 0;
            if (node.getAttribute('id') == null) {
                var idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                node.setAttribute('id', idValue);
                linkedElmCounter++;
            }

            var encNodeTag = node.tagName;
            //    var parentEnclosure = INTERMediatorLib.getEnclosure(node);
            var parentNodeId = (parentEnclosure == null ? null : parentEnclosure.getAttribute('id'));
            var repNodeTag = INTERMediatorLib.repeaterTagFromEncTag(encNodeTag);
            var repeatersOriginal = []; // Collecting repeaters to this array.
            var repeaters = []; // Collecting repeaters to this array.
            var children = node.childNodes; // Check all child node of the enclosure.
            for (var i = 0; i < children.length; i++) {
                if (children[i].nodeType == 1 && children[i].tagName == repNodeTag) {
                    // If the element is a repeater.
                    repeatersOriginal.push(children[i]); // Record it to the array.
                }
            }

            for (var i = 0; i < repeatersOriginal.length; i++) {
                var inDocNode = repeatersOriginal[i];
                var parentOfRep = repeatersOriginal[i].parentNode;
                var cloneNode = repeatersOriginal[i].cloneNode(true);
                repeaters.push(cloneNode);
                idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                cloneNode.setAttribute('id', idValue);
                linkedElmCounter++;
                parentOfRep.removeChild(inDocNode);
            }
            linkedNodes = []; // Collecting linked elements to this array.
            for (var i = 0; i < repeaters.length; i++) {
                seekLinkedElement(repeaters[i]);
            }
            var currentLinkedNodes = linkedNodes; // Store in the local variable
            // Collecting linked elements in array
            var linkDefs = [];
            for (var j = 0; j < linkedNodes.length; j++) {
                var nodeDefs = INTERMediatorLib.getLinkedElementInfo(linkedNodes[j]);
                if (nodeDefs != null) {
                    for (var k = 0; k < nodeDefs.length; k++) {
                        linkDefs.push(nodeDefs[k]);
                    }
                }
            }

            var voteResult = tableVoting(linkDefs);
            var currentContext = voteResult.targettable;
            var fieldList = voteResult.fieldlist; // Create field list for database fetch.

            if (currentContext != null) {
                var retationValue = null;
                var dependObject = [];
                var relationDef = currentContext['relation'];
                if (relationDef != null) {
                    var retationValue = [];
                    for (var index in relationDef) {
                        retationValue[ relationDef[index]['foreign-key'] ]
                            = currentRecord[relationDef[index]['join-field']];

                        for (var fieldName in parentObjectInfo) {
                            if (fieldName == relationDef[index]['join-field']) {
                                dependObject.push(parentObjectInfo[fieldName]);
                            }
                        }
                    }
                }

                /*   INTERMediator.debugMessages.push(
                 "$$$"+node.getAttribute('id')+"#"+
                 INTERMediatorLib.objectToString(dependObject));*/
                var thisKeyFieldObject = {
                    'node':node,
                    'name':currentContext['name'] /*currentTable */,
                    'foreign-value':retationValue,
                    'parent':node.parentNode,
                    'original':[],
                    'target':dependObject
                };
                for (var i = 0; i < repeatersOriginal.length; i++) {
                    thisKeyFieldObject.original.push(repeatersOriginal[i].cloneNode(true));
                }
                INTERMediator.keyFieldObject.push(thisKeyFieldObject);

                // Access database and get records
                var targetRecords = IM_DBAdapter.db_query({
                    name:currentContext['name'],
                    records:currentContext['records'],
                    paging:currentContext['paging'],
                    fields:fieldList,
                    parentkeyvalue:retationValue,
                    conditions:null,
                    useoffset:true});

                var RecordCounter = 0;
                var eventListenerPostAdding = [];

                // var currentEncNumber = currentLevel;
                for (var ix in targetRecords) { // for each record
                    RecordCounter++;

                    var shouldDeleteNodes = [];
                    repeaters = [];
                    for (var i = 0; i < repeatersOriginal.length; i++) {
                        var clonedNode = repeatersOriginal[i].cloneNode(true);
                        repeaters.push(clonedNode);
                    }
                    linkedNodes = [];
                    for (var i = 0; i < repeaters.length; i++) {
                        seekLinkedElement(repeaters[i]);
                        if (repeaters[i].getAttribute('id') == null) {
                            idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                            repeaters[i].setAttribute('id', idValue);
                            linkedElmCounter++;
                        }
                        shouldDeleteNodes.push(repeaters[i].getAttribute('id'));
                    }
                    var currentLinkedNodes = linkedNodes; // Store in the local variable
                    var keyField = currentContext['key'];
                    var keyValue = targetRecords[ix][currentContext['key']];
                    var keyingValue = keyField + "=" + keyValue;

                    for (var k = 0; k < currentLinkedNodes.length; k++) {
                        // for each linked element
                        if (currentLinkedNodes[k].getAttribute('id') == null) {
                            idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                            currentLinkedNodes[k].setAttribute('id', idValue);
                            linkedElmCounter++;
                        }
                        var nodeTag = currentLinkedNodes[k].tagName;
                        // get the tag name of the element
                        var typeAttr = currentLinkedNodes[k].getAttribute('type');
                        // type attribute
                        var linkInfoArray = INTERMediatorLib.getLinkedElementInfo(currentLinkedNodes[k]);
                        // info array for it  set the name attribute of radio button
                        // should be different for each group
                        if (typeAttr == 'radio') { // set the value to radio button
                            var nameAttr = currentLinkedNodes[k].getAttribute('name');
                            if (nameAttr) {
                                currentLinkedNodes[k].setAttribute('name', nameAttr + '-' + RecordCounter);
                            } else {
                                currentLinkedNodes[k].setAttribute('name', 'IM-R-' + RecordCounter);
                            }
                        }

                        if (nodeTag == 'INPUT' || nodeTag == 'SELECT' || nodeTag == 'TEXTAREA') {
                            eventListenerPostAdding.push({
                                'id':idValue,
                                'event':'change',
                                'todo':new Function('INTERMediator.valueChange("' + idValue + '")')
                            });
                        }


                        for (var j = 0; j < linkInfoArray.length; j++) {
                            // for each info Multiple replacement definitions
                            // for one node is prohibited.
                            var nInfo = INTERMediatorLib.getNodeInfoArray(linkInfoArray[j]);
                            var curVal = targetRecords[ix][nInfo['field']];
                            if (curVal == null) {
                                curVal = '';
                            }
                            var curTarget = nInfo['target'];
                            // Store the key field value and current value for update
                            if (nodeTag == 'INPUT' || nodeTag == 'SELECT' || nodeTag == 'TEXTAREA') {
                                INTERMediator.updateRequiredObject[idValue] = {
                                    targetattribute:curTarget,
                                    initialvalue:curVal,
                                    name:currentContext['name'],
                                    field:nInfo['field'],
                                    'parent-enclosure':node.getAttribute('id'),
                                    keying:keyingValue,
                                    'foreign-value':retationValue,
                                    'updatenodeid':parentNodeId};
                            }

                            objectReference[nInfo['field']] = idValue;

                            // Set data to the element.
                            if (setDataToElement(currentLinkedNodes[k], curTarget, curVal)) {
                                postSetFields.push({'id':idValue, 'value':curVal});
                            }
                        }
                    }

                    if (currentContext['repeat-control'] != null && currentContext['repeat-control'].match(/delete/i)) {
                        if (currentContext['relation'] != null) {
                            var buttonNode = document.createElement('BUTTON');
                            buttonNode.appendChild(document.createTextNode(IM_getMessages()[6]));
                            var thisId = 'IM_Button_' + buttonIdNum;
                            buttonNode.setAttribute('id', thisId);
                            buttonIdNum++;
                            eventListenerPostAdding.push({
                                'id':thisId,
                                'event':'click',
                                'todo':new Function("INTERMediator.deleteButton(" + "'"
                                    + currentContext['name']
                                    + "'," + "'" + keyField + "'," + "'" + keyValue + "',"
                                    + INTERMediatorLib.objectToString(shouldDeleteNodes) + ");")
                            });
                            var endOfRepeaters = repeaters[repeaters.length - 1];
                            switch (encNodeTag) {
                                case 'TBODY':
                                    var tdNodes = endOfRepeaters.getElementsByTagName('TD');
                                    var tdNode = tdNodes[tdNodes.length - 1];
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
                            deleteInsertOnNavi.push({
                                kind:'DELETE',
                                name:currentContext['name'],
                                key:keyField,
                                value:keyValue
                            });
                        }
                    }

                    for (var i = 0; i < repeaters.length; i++) {
                        var newNode = repeaters[i].cloneNode(true);
                        node.appendChild(newNode);
                        if (newNode.getAttribute('id') == null) {
                            idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                            newNode.setAttribute('id', idValue);
                            linkedElmCounter++;
                        }
                        seekEnclosureNode(newNode, targetRecords[ix], currentContext['name'], node, objectReference);
                    }

                    // Event listener should add after adding node to document.
                    for (var i = 0; i < eventListenerPostAdding.length; i++) {
                        var theNode = document.getElementById(eventListenerPostAdding[i]['id']);
                        if (theNode != null) {
                            INTERMediatorLib.addEvent(
                                theNode, eventListenerPostAdding[i]['event'], eventListenerPostAdding[i]['todo']);
                        }
                    }
                }

                if (currentContext['repeat-control'] != null && currentContext['repeat-control'].match(/insert/i)) {
                    if (retationValue != null) {
                        var buttonNode = document.createElement('BUTTON');
                        buttonNode.appendChild(document.createTextNode(IM_getMessages()[5]));
                        var shouldRemove = [];
                        switch (encNodeTag) {
                            case 'TBODY':
                                var enclosedNode = node.parentNode;
                                var footNode = enclosedNode.getElementsByTagName('TFOOT')[0];
                                if (footNode == null) {
                                    footNode = document.createElement('TFOOT');
                                    enclosedNode.appendChild(footNode);
                                }
                                var trNode = document.createElement('TR');
                                var tdNode = document.createElement('TD');
                                if (trNode.getAttribute('id') == null) {
                                    idValue = 'IM' + INTERMediator.currentEncNumber + '-' + linkedElmCounter;
                                    trNode.setAttribute('id', idValue);
                                    linkedElmCounter++;
                                }
                                footNode.appendChild(trNode);
                                trNode.appendChild(tdNode);
                                tdNode.appendChild(buttonNode);
                                shouldRemove = [trNode.getAttribute('id')];
                                break;
                            case 'UL':
                            case 'OL':
                                var liNode = document.createElement('LI');
                                liNode.appendChild(buttonNode)
                                node.appendChild(liNode);
                                break;
                            case 'DIV':
                            case 'SPAN':
                                if (repNodeTag == "DIV" || repNodeTag == "SPAN") {
                                    var divNode = document.createElement(repNodeTag);
                                    divNode.appendChild(buttonNode)
                                    node.appendChild(liNode);
                                }
                                break;
                        }
                        var insertJSScript = "INTERMediator.insertButton("
                            + "'" + currentContext['name'] + "',"
                            + (retationValue == null ? 'null' : INTERMediatorLib.objectToString(retationValue)) + ","
                            + "'" + node.getAttribute('id') + "',"
                            + (shouldRemove == null ? 'null' : INTERMediatorLib.objectToString(shouldRemove)) + ");"
                        INTERMediatorLib.addEvent(buttonNode, 'click', new Function(insertJSScript));
                        //    INTERMediator.debugMessages.push(
                        //        "insertJSScript:"+INTERMediatorLib.objectToString(insertJSScript) );
                    } else {
                        deleteInsertOnNavi.push({
                            kind:'INSERT',
                            name:currentContext['name'],
                            key:currentContext['key']
                        });
                    }
                }

                if (INTERMediatorOnPage.expandingEnclosureFinish != null) {
                    INTERMediatorOnPage.expandingEnclosureFinish(currentContext['name'], node);
                }

            } else {
                INTERMediator.errorMessages.push(
                    INTERMediatorLib.getInsertedString(
                        IM_getMessages()[1002], [INTERMediatorLib.objectToString(fieldList)]));
            }
            currentLevel--;
            //    return foreignValue != '';
        }

        function seekLinkedElement(node) {
            var enclosure = null;
            var nType = node.nodeType;
            if (nType == 1) {
                if (INTERMediatorLib.isLinkedElement(node)) {
                    var currentEnclosure = INTERMediatorLib.getEnclosure(node);
                    if (currentEnclosure == null) {
                        linkedNodes.push(node);
                    } else {
                        return currentEnclosure;
                    }
                }
                var childs = node.childNodes;
                for (var i = 0; i < childs.length; i++) {
                    var detectedEnclosure = seekLinkedElement(childs[i]);
                    if (detectedEnclosure != null) {
                        if (detectedEnclosure == childs[i]) {
                            return null;
                        } else {
                            return detectedEnclosure;
                        }
                    }
                }
            }
            return null;
        }

        function tableVoting(linkDefs) {
            var tableVote = [];    // Containing editable elements or not.
            var fieldList = []; // Create field list for database fetch.
            for (var j = 0; j < linkDefs.length; j++) {
                var nodeInfoArray = INTERMediatorLib.getNodeInfoArray(linkDefs[j]);
                var nodeInfoField = nodeInfoArray['field'];
                var nodeInfoTable = nodeInfoArray['table'];
                if (nodeInfoField != null && nodeInfoTable != null &&
                    nodeInfoField.length != 0 && nodeInfoTable.length != 0) {
                    if (fieldList[nodeInfoTable] == null) {
                        fieldList[nodeInfoTable] = [];
                    }
                    fieldList[nodeInfoTable].push(nodeInfoField);
                    if (tableVote[nodeInfoTable] == null) {
                        tableVote[nodeInfoTable] = 1;
                    } else {
                        ++tableVote[nodeInfoTable];
                    }
                } else {
                    INTERMediator.errorMessages.push(
                        INTERMediatorLib.getInsertedStringFromErrorNumber(1006, [linkDefs[j]]));
                    //   return null;
                }
            }
            var maxVoted = -1;
            var maxTableName = ''; // Which is the maximum voted table name.
            for (var tableName in tableVote) {
                if (maxVoted < tableVote[tableName]) {
                    maxVoted = tableVote[tableName];
                    maxTableName = tableName;
                }
            }
            var context = INTERMediatorLib.getNamedObject(IM_getDataSources(), 'name', maxTableName);
            return {'targettable':context, 'fieldlist':fieldList[maxTableName]};
        }

        function setDataToElement(element, curTarget, curVal) {
            var needPostValueSet = false;
            // IE should \r for textNode and <br> for innerHTML, Others is not required to convert
            var nodeTag = element.tagName;

            if (curTarget != null && curTarget.length > 0) { //target is specified
                if (curTarget.charAt(0) == '#') { // Appending
                    curTarget = curTarget.substring(1);
                    if (curTarget == 'innerHTML') {
                        if (INTERMediator.isIE && nodeTag == "TEXTAREA") {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br/>");
                        }
                        element.innerHTML += curVal;
                    } else if (curTarget == 'textNode') {
                        var textNode = document.createTextNode(curVal);
                        if (nodeTag == "TEXTAREA") {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r");
                        }
                        element.appendChild(textNode);
                    } else if (curTarget.indexOf('style.') == 0) {
                        var styleName = curTarget.substring(6, curTarget.length);
                        var statement = "element.style." + styleName + "='" + curVal + "';";
                        eval(statement);
                    } else {
                        var currentValue = element.getAttribute(curTarget);
                        element.setAttribute(curTarget, currentValue + curVal);
                    }
                } else { // Setting
                    if (curTarget == 'innerHTML') { // Setting
                        if (INTERMediator.isIE && nodeTag == "TEXTAREA") {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br/>");
                        }
                        element.innerHTML = curVal;
                    } else if (curTarget == 'textNode') {
                        var textNode = document.createTextNode(curVal);
                        if (nodeTag == "TEXTAREA") {
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r");
                        }
                        element.appendChild(textNode);
                    } else if (curTarget.indexOf('style.') == 0) {
                        var styleName = curTarget.substring(6, curTarget.length);
                        var statement = "element.style." + styleName + "='" + curVal + "';";
                        eval(statement);
                    } else {
                        element.setAttribute(curTarget, curVal);
                    }
                }
            } else { // if the 'target' is not specified.
                if (nodeTag == "INPUT") {
                    var typeAttr = element.getAttribute('type');
                    if (typeAttr == 'checkbox' || typeAttr == 'radio') { // set the value
                        var valueAttr = element.value;
                        if (valueAttr == curVal) {
                            if (INTERMediator.isIE) {
                                element.setAttribute('checked', 'checked');
                            } else {
                                element.checked = true;
                            }
                        } else {
                            element.checked = false;
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
                            curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r");
                        }
                        var textNode = document.createTextNode(curVal);
                        element.appendChild(textNode);
                    }
                }
            }
            return needPostValueSet;
        }

        /**
         * Create Navigation Bar to move previous/next page
         * @param target
         */

        function navigationSetup() {
            var navigation = document.getElementById('IM_NAVIGATOR');
            if (navigation != null) {

                var insideNav = navigation.childNodes;
                for (var i = 0; i < insideNav.length; i++) {
                    navigation.removeChild(insideNav[i]);
                }
                navigation.innerHTML = '';
                navigation.setAttribute('class', 'IM_NAV_panel');
                var navLabel = INTERMediator.navigationLabel;

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(navLabel == null ? IM_getMessages()[2] : navLabel[1]));
                node.setAttribute('class', 'IM_NAV_button');
                INTERMediatorLib.addEvent(node, 'click', function() {
                    location.reload();
                });

                var start = Number(INTERMediator.startFrom);
                var pageSize = Number(INTERMediator.pagedSize);
                var allCount = Number(INTERMediator.pagedAllCount);
                var disableClass = " IM_NAV_disabled";
                var node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    (navLabel == null ? IM_getMessages()[1] : navLabel[4]) + (start + 1)
                        + ((Math.min(start + pageSize, allCount) - start > 2) ?
                        ((navLabel == null ? "-" : navLabel[5]) + Math.min(start + pageSize, allCount)) : '')
                        + (navLabel == null ? " / " : navLabel[6]) + (allCount) + (navLabel == null ? "" : navLabel[7])));
                node.setAttribute('class', 'IM_NAV_info');

                var node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(navLabel == null ? '<<' : navLabel[0]));
                node.setAttribute('class', 'IM_NAV_button' + (start == 0 ? disableClass : ""));
                INTERMediatorLib.addEvent(node, 'click', function() {
                    INTERMediator.startFrom = 0;
                    INTERMediator.construct(true);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(navLabel == null ? '<' : navLabel[1]));
                node.setAttribute('class', 'IM_NAV_button' + (start == 0 ? disableClass : ""));
                var prevPageCount = (start - pageSize > 0) ? start - pageSize : 0;
                INTERMediatorLib.addEvent(node, 'click', function() {
                    INTERMediator.startFrom = prevPageCount;
                    INTERMediator.construct(true);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(navLabel == null ? '>' : navLabel[2]));
                node.setAttribute('class', 'IM_NAV_button' + (start + pageSize >= allCount ? disableClass : ""));
                var nextPageCount
                    = (start + pageSize < allCount) ? start + pageSize : ((allCount - pageSize > 0) ? start : 0);
                INTERMediatorLib.addEvent(node, 'click', function() {
                    INTERMediator.startFrom = nextPageCount;
                    INTERMediator.construct(true);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(navLabel == null ? '>>' : navLabel[3]));
                node.setAttribute('class', 'IM_NAV_button' + (start + pageSize >= allCount ? disableClass : ""));
                var endPageCount = allCount - pageSize;
                INTERMediatorLib.addEvent(node, 'click', function() {
                    INTERMediator.startFrom = (endPageCount > 0) ? endPageCount : 0;
                    INTERMediator.construct(true);
                });

                for (var i = 0; i < deleteInsertOnNavi.length; i++) {
                    switch (deleteInsertOnNavi[i]['kind']) {
                        case 'INSERT':
                            node = document.createElement('SPAN');
                            navigation.appendChild(node);
                            node.appendChild(
                                document.createTextNode(IM_getMessages()[3] + ': ' + deleteInsertOnNavi[i]['name']));
                            node.setAttribute('class', 'IM_NAV_button');
                            INTERMediatorLib.addEvent(node, 'click',
                                new Function("INTERMediator.insertRecordFromNavi(" + "'" + deleteInsertOnNavi[i]['name']
                                    + "'," + "'" + deleteInsertOnNavi[i]['key'] + "'" + ");"));
                            break;
                        case 'DELETE':
                            node = document.createElement('SPAN');
                            navigation.appendChild(node);
                            node.appendChild(
                                document.createTextNode(IM_getMessages()[4] + ': ' + deleteInsertOnNavi[i]['name']));
                            node.setAttribute('class', 'IM_NAV_button');
                            INTERMediatorLib.addEvent(node, 'click',
                                new Function("INTERMediator.deleteRecordFromNavi(" + "'" + deleteInsertOnNavi[i]['name']
                                    + "'," + "'" + deleteInsertOnNavi[i]['key'] + "',"
                                    + "'" + deleteInsertOnNavi[i]['value'] + "'" + ");"));
                            break;
                    }
                }
            }
        }

        function getEnclosedNode(rootNode, tableName, fieldName) {
            if (rootNode.nodeType == 1) {
                var nodeInfo = INTERMediatorLib.getLinkedElementInfo(rootNode);
                for (var j = 0; j < nodeInfo.length; j++) {
                    var nInfo = INTERMediatorLib.getNodeInfoArray(nodeInfo[j]);
                    if (nInfo['table'] == tableName && nInfo['field'] == fieldName) {
                        return rootNode;
                    }
                }
            }
            var childs = rootNode.childNodes; // Check all child node of the enclosure.
            for (var i = 0; i < childs.length; i++) {
                var r = getEnclosedNode(childs[i], tableName, fieldName);
                if (r != null) {
                    return r;
                }
            }
            return null;
        }

        function appendCredit() {
            if (document.getElementById('IM_CREDIT') == null) {
                var bodyNode = document.getElementsByTagName('BODY')[0];
                var creditNode = document.createElement('div');
                bodyNode.appendChild(creditNode);
                creditNode.setAttribute('id', 'IM_CREDIT');
                creditNode.setAttribute('class', 'IM_CREDIT');

                var cNode = document.createElement('div');
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
                var spNode = document.createElement('span');
                cNode.appendChild(spNode);
                cNode.style.color = '#666666';
                cNode.style.fontSize = '7pt';
                var aNode = document.createElement('a');
                aNode.appendChild(document.createTextNode('INTER-Mediator'));
                aNode.setAttribute('href', 'http://msyk.net/im');
                aNode.setAttribute('target', '_href');
                spNode.appendChild(document.createTextNode('Generated by '));
                spNode.appendChild(aNode);
                spNode.appendChild(document.createTextNode(' Ver.0.7.6(2011-09-18)'));
            }
        }
    }
}