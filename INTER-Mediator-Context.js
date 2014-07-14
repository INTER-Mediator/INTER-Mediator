/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

IMLibContextPool = {
    poolingContexts: null,

    clearAll: function () {
        this.poolingContexts = null;
    },

    registerContext: function (context) {
        if (this.poolingContexts == null) {
            this.poolingContexts = [context];
        } else {
            this.poolingContexts.push(context);
        }
    },

    excludingNode: null,

    synchronize: function (context, recKey, key, value, target, portal) {
//        console.log("SYNC:"+context+"/"+recKey+"/"+key+"/"+value);
        var i, j, viewName, refNode, targetNodes, result = false;
        viewName = context.viewName;
        if (this.poolingContexts == null) {
            return null;
        }
        if (portal) {
            for (i = 0; i < this.poolingContexts.length; i++) {
                if (this.poolingContexts[i].viewName === viewName
                    && this.poolingContexts[i].binding[recKey] !== undefined
                    && this.poolingContexts[i].binding[recKey][key] !== undefined
                    && this.poolingContexts[i].binding[recKey][key][portal] !== undefined
                    && this.poolingContexts[i].store[recKey] !== undefined
                    && this.poolingContexts[i].store[recKey][key] !== undefined
                    && this.poolingContexts[i].store[recKey][key][portal] !== undefined) {

                    this.poolingContexts[i].store[recKey][key][portal] = value;
                    targetNodes = this.poolingContexts[i].binding[recKey][key][portal];
                    for (j = 0; j < targetNodes.length; j++) {
                        refNode = document.getElementById(targetNodes[j].id);
                        if (refNode) {
                            IMLibElement.setValueToIMNode(refNode, targetNodes[j].target, value, true);
                        }
                    }
                }
            }
        } else {
            for (i = 0; i < this.poolingContexts.length; i++) {
                if (this.poolingContexts[i].viewName === viewName
                    && this.poolingContexts[i].binding[recKey] !== undefined
                    && this.poolingContexts[i].binding[recKey][key] !== undefined
                    && this.poolingContexts[i].store[recKey] !== undefined
                    && this.poolingContexts[i].store[recKey][key] !== undefined) {

                    this.poolingContexts[i].store[recKey][key] = value;
                    targetNodes = this.poolingContexts[i].binding[recKey][key];
                    for (j = 0; j < targetNodes.length; j++) {
                        refNode = document.getElementById(targetNodes[j].id);
                        if (refNode) {
                            IMLibElement.setValueToIMNode(refNode, targetNodes[j].target, value, true);
                        }
                    }
                }
            }
        }
        return result;
    },

    getContextInfoFromId: function (idValue, target) {
        var i, targetContext, element, linkInfo, nodeInfo, targetName, result = null;
        if (!idValue) {
            return result;
        }

        element = document.getElementById(idValue);
        linkInfo = INTERMediatorLib.getLinkedElementInfo(element);
        if (!linkInfo && INTERMediatorLib.isWidgetElement(element.parentNode)) {
            linkInfo = INTERMediatorLib.getLinkedElementInfo(element.parentNode);
        }
        nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfo[0]);

        targetName = target === "" ? "_im_no_target" : target;
        for (i = 0; i < this.poolingContexts.length; i++) {
            targetContext = this.poolingContexts[i];
            if (targetContext.contextInfo[idValue] &&
                targetContext.contextInfo[idValue][targetName] &&
                targetContext.contextInfo[idValue][targetName].context.contextName == nodeInfo.table) {
                result = targetContext.contextInfo[idValue][targetName];
            }
        }
        return result;
    },

    getKeyFieldValueFromId: function (idValue, target) {
        var contextInfo = this.getContextInfoFromId(idValue, target);
        if (!contextInfo) {
            return null;
        }
        var contextName = contextInfo.context.contextName;
        var contextDef = IMLibContextPool.getContextDef(contextName);
        if (!contextDef) {
            return null;
        }
        var keyField = contextDef.key ? contextDef.key : "id";
        return contextInfo.record.substr(keyField.length + 1);
    },

    getContextDef: function (contextName) {
        return INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', contextName);
    },

    updateContext: function (idValue, target) {
        var contextInfo, value;
        contextInfo = IMLibContextPool.getContextInfoFromId(idValue, target);
        value = IMLibElement.getValueFromIMNode(document.getElementById(idValue));
        if (contextInfo) {
            contextInfo.context.setValue(
                contextInfo['record'], contextInfo.field, value, false, target, contextInfo.portal);
        }
    },

    contextFromEnclosureId: function (idValue) {
        var i, enclosure;
        if (!idValue) {
            return false;
        }
        for (i = 0; i < this.poolingContexts.length; i++) {
            enclosure = this.poolingContexts[i].enclosureNode;
            if (enclosure.getAttribute('id') == idValue) {
                return this.poolingContexts[i];
            }
        }
        return null;
    },

    contextFromName: function (cName) {
        var i;
        if (!cName) {
            return false;
        }
        for (i = 0; i < this.poolingContexts.length; i++) {
            if (this.poolingContexts[i].contextName == cName) {
                return this.poolingContexts[i];
            }
        }
        return null;
    },

    getContextFromName: function (cName) {
        var i, result = [];
        if (!cName) {
            return false;
        }
        for (i = 0; i < this.poolingContexts.length; i++) {
            if (this.poolingContexts[i].contextName == cName) {
                result.push(this.poolingContexts[i]);
            }
        }
        return result;
    },

    getContextsFromNameAndForeignValue: function (cName, fValue) {
        var i, j, result = [], parentKeyField;
        if (!cName) {
            return false;
        }
        parentKeyField = "id";
        for (i = 0; i < this.poolingContexts.length; i++) {
            if (this.poolingContexts[i].contextName == cName
                && this.poolingContexts[i].foreignValue[parentKeyField] == fValue) {
                result.push(this.poolingContexts[i]);
            }
        }
        return result;
    },

    dependingObjects: function (idValue) {
        var i, j, result = [];
        if (!idValue) {
            return false;
        }
        for (i = 0; i < this.poolingContexts.length; i++) {
            for (j = 0; j < this.poolingContexts[i].dependingObject.length; j++) {
                if (this.poolingContexts[i].dependingObject[j] == idValue) {
                    result.push(this.poolingContexts[i]);
                }
            }
        }
        return result.length == 0 ? false : result;
    },

    updateOnAnotherClient: function (eventName, info) {
        var keying, i, entityName = info.entity, contextDef, contextView, keyField, bindingInfo, fieldName, cIndex,
            targetNode;
        console.error("eventName=" + eventName + "\nentity=" + info.entity + "\npk-value="
            + info.pkvalue + "\nfield=" + info.field + "\nvalue=" + info.value);

        if (eventName == 'update') {
            for (i = 0; i < this.poolingContexts.length; i++) {
                contextDef = this.getContextDef(this.poolingContexts[i].contextName);
                contextView = contextDef.view ? contextDef.view : contextDef.name;
                if (contextView == entityName) {
                    keyField = contextDef.key;
                    this.poolingContexts[i].setValue(keyField + "=" + info.pkvalue, info.field[0], info.value[0]);
                }
            }
            IMLibCalc.recalculation();
        } else if (eventName == 'create') {
        } else if (eventName == 'delete') {
            for (i = 0; i < this.poolingContexts.length; i++) {
                contextDef = this.getContextDef(this.poolingContexts[i].contextName);
                contextView = contextDef.view ? contextDef.view : contextDef.name;
                if (contextView == entityName) {
                    keyField = contextDef.key;
                    keying = keyField + "=" + info.pkvalue;
                    bindingInfo = this.poolingContexts[i].binding[keying];
                    for (fieldName in bindingInfo) {
                        for (cIndex = 0; cIndex < bindingInfo[fieldName].length; cIndex++) {
                            targetNode = document.getElementById(bindingInfo[fieldName][cIndex].id);
                            if (targetNode) {
                                targetNode = INTERMediatorLib.getParentRepeater(targetNode);
                                if (targetNode) {
                                    targetNode.parentNode.removeChild(targetNode);
                                }
                            }
                        }
                    }
                    delete this.poolingContexts[i].binding[keying];
                }
            }
            IMLibCalc.recalculation();
        }
    }
}

IMLibContext = function (contextName) {
    this.contextName = contextName;
    this.tableName = null;
    this.viewName = null;
    this.store = {};
    this.binding = {};
    this.contextInfo = {};
    this.modified = {};
    IMLibContextPool.registerContext(this);

    this.foreignValue = {};
    this.enclosureNode = null;
    this.repeaterNodes = null;
    this.dependingObject = [];
    this.original = null;
    this.nullAcceptable = true;

    this.clearAll = function () {
        this.store = {};
        this.binding = {};
    };

    this.setContextName = function (name) {
        this.contextName = name;
    };

    this.setTableName = function (name) {
        this.tableName = name;
    };

    this.setViewName = function (name) {
        this.viewName = name;
    };

    this.addDependingObject = function (idNumber) {
        this.dependingObject.push(idNumber);
    };

    this.addForeignValue = function (field, value) {
        this.foreignValue[field] = value;
    };

    this.setOriginal = function (repeaters) {
        this.original = [];
        for (i = 0; i < repeaters.length; i++) {
            this.original.push(repeaters[i].cloneNode(true));
        }
    };

    this.setTable = function (context) {
        // console.error(context);
        var contextDef;
        if (!context || !INTERMediatorOnPage.getDataSources) {
            this.tableName = this.contextName;
            this.viewName = this.contextName;
            // This is not a valid case, it just prevent the error in the unit test.
            return;
        }
        contextDef = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), "name", context.contextName)
        if (contextDef) {
            this.viewName = contextDef['view'] ? contextDef['view'] : contextDef['name'];
            this.tableName = contextDef['table'] ? contextDef['table'] : contextDef['name'];
        }
    };

    this.setTable(this);

    this.setModified = function (recKey, key, value) {
        if (this.modified[recKey] === undefined) {
            this.modified[recKey] = {};
        }
        this.modified[recKey][key] = value;
    };

    this.getModified = function () {
        return this.modified;
    };

    this.clearModified = function () {
        this.modified = {};
    };

    this.setValue = function (recKey, key, value, nodeId, target, portal) {
        //console.error(this.contextName, this.tableName, recKey, key, value, nodeId);
        if (recKey != undefined && recKey != null) {
            if (this.store[recKey] === undefined) {
                this.store[recKey] = {};
            }
            if (portal && this.store[recKey][key] === undefined) {
                this.store[recKey][key] = {};
            }
            if (this.binding[recKey] === undefined) {
                this.binding[recKey] = {};
            }
            if (this.binding[recKey][key] === undefined) {
                this.binding[recKey][key] = [];
            }
            if (portal && this.binding[recKey][key][portal] === undefined) {
                if (this.binding[recKey][key].length < 1) {
                    this.binding[recKey][key] = {};
                }
                this.binding[recKey][key][portal] = [];
            }
            if (key != undefined && key != null) {
                if (portal) {
                    this.store[recKey][key][portal] = value;
                } else {
                    this.store[recKey][key] = value;
                }
                if (nodeId) {
                    if (portal) {
                        this.binding[recKey][key][portal].push({id: nodeId, target: target});
                    } else {
                        this.binding[recKey][key].push({id: nodeId, target: target});
                    }
                    if (this.contextInfo[nodeId] === undefined) {
                        this.contextInfo[nodeId] = {};
                    }
                    this.contextInfo[nodeId][target == "" ? "_im_no_target" : target]
                        = {context: this, record: recKey, field: key};
                    if (portal) {
                        this.contextInfo[nodeId][target == "" ? "_im_no_target" : target].portal = portal;
                    }
                } else {
                    IMLibContextPool.synchronize(this, recKey, key, value, target, portal);
                }
            }
        }
    };

    this.getValue = function (recKey, key, portal) {
        var value;
        try {
            if (portal) {
                value = this.store[recKey][key][portal];
            } else {
                value = this.store[recKey][key];
            }
            return value === undefined ? null : value;
        } catch (ex) {
            return null;
        }
    };

    this.getContextInfo = function (nodeId, target) {
        try {
            var info = this.contextInfo[nodeId][target == "" ? "_im_no_target" : target];
            return info === undefined ? null : info;
        } catch (ex) {
            return null;
        }
    };

    this.getContextValue = function (nodeId, target) {
        try {
            var info = this.contextInfo[nodeId][target == "" ? "_im_no_target" : target];
            var value = info.context.getValue(info.record, info.field);
            return value === undefined ? null : value;
        } catch (ex) {
            return null;
        }
    }
};

IMLibLocalContext = {
    contextName: "_",
    store: {},
    binding: {},

    clearAll: function () {
        this.store = {};
    },

    setValue: function (key, value) {
        var i, hasUpdated, refIds, node;

        hasUpdated = false;
        if (key != undefined && key != null) {
            if (value === undefined || value === null) {
                delete this.store[key];
            } else {
                this.store[key] = value;
                hasUpdated = true;
                refIds = this.binding[key];
                if (refIds) {
                    for (i = 0; i < refIds.length; i++) {
                        node = document.getElementById(refIds[i]);
                        IMLibElement.setValueToIMNode(node, "", value, true);
                    }
                }
            }
        }
        if (hasUpdated) {
            this.archive();
        }
    },

    getValue: function (key) {
        var value = this.store[key];
        return value === undefined ? null : value;
    },

    archive: function () {
        var jsonString;
        INTERMediatorOnPage.removeCookie('_im_localcontext');
        if (INTERMediator.isIE && INTERMediator.ieVersion < 9) {
            this.store._im_additionalCondition = INTERMediator.additionalCondition;
            this.store._im_additionalSortKey = INTERMediator.additionalSortKey;
            this.store._im_startFrom = INTERMediator.startFrom;
            this.store._im_pagedSize = INTERMediator.pagedSize;
            /*
             IE8 issue: "" string is modified as "null" on JSON stringify.
             http://blogs.msdn.com/b/jscript/archive/2009/06/23/serializing-the-value-of-empty-dom-elements-using-native-json-in-ie8.aspx
             */
            jsonString = JSON.stringify(this.store, function (k, v) {
                return v === "" ? "" : v;
            });
        } else {
            jsonString = JSON.stringify(this.store);
        }
        if (INTERMediator.useSessionStorage === true && typeof sessionStorage !== 'undefined' && sessionStorage !== null) {
            sessionStorage.setItem("_im_localcontext", jsonString);
        } else {
            INTERMediatorOnPage.setCookieWorker('_im_localcontext', jsonString, false, 0);
        }
    },

    unarchive: function () {
        var localContext = "";
        if (INTERMediator.useSessionStorage === true && typeof sessionStorage !== 'undefined' && sessionStorage !== null) {
            localContext = sessionStorage.getItem("_im_localcontext");
        } else {
            localContext = INTERMediatorOnPage.getCookie('_im_localcontext');
        }
        if (localContext && localContext.length > 0) {
            this.store = JSON.parse(localContext);
            if (INTERMediator.isIE && INTERMediator.ieVersion < 9) {
                if (this.store._im_additionalCondition) {
                    INTERMediator.additionalCondition = this.store._im_additionalCondition;
                }
                if (this.store._im_additionalSortKey) {
                    INTERMediator.additionalSortKey = this.store._im_additionalSortKey;
                }
                if (this.store._im_startFrom) {
                    INTERMediator.startFrom = this.store._im_startFrom;
                }
                if (this.store._im_pagedSize) {
                    INTERMediator.pagedSize = this.store._im_pagedSize;
                }
            }
        }
    },

    binding: function (node) {
        var linkInfos, nodeInfo, idValue, i, value;
        if (node.nodeType != 1) {
            return;
        }
        linkInfos = INTERMediatorLib.getLinkedElementInfo(node);
        for (i = 0; i < linkInfos.length; i++) {
            nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfos[i]);
            if (nodeInfo.table == this.contextName) {
                if (!node.id) {
                    node.id = nextIdValue();
                }
                idValue = node.id;
                if (!this.binding[nodeInfo.field]) {
                    this.binding[nodeInfo.field] = [];
                }
                this.binding[nodeInfo.field].push(idValue);
                INTERMediatorLib.addEvent(node, 'change', (function () {
                    var nodeId = idValue;
                    var self = this;
                    return function () {
                        IMLibLocalContext.update(nodeId);
                    };
                })());
                IMLibChangeEventDispatch.setExecute(idValue, IMLibUI.valueChange);

                value = this.store[nodeInfo.field];
                IMLibElement.setValueToIMNode(node, nodeInfo.target, value, true);
            }
        }

        function nextIdValue() {
            INTERMediator.linkedElmCounter++;
            return currentIdValue();
        }

        function currentIdValue() {
            return 'IM' + INTERMediator.currentEncNumber + '-' + INTERMediator.linkedElmCounter;
        }

    },

    update: function (idValue) {
        var node, nodeValue, linkInfos, nodeInfo, i;
        node = document.getElementById(idValue);
        nodeValue = IMLibElement.getValueFromIMNode(node);
        linkInfos = INTERMediatorLib.getLinkedElementInfo(node);
        for (i = 0; i < linkInfos.length; i++) {
            nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfos[i]);
            if (nodeInfo.table == this.contextName) {
                this.setValue(nodeInfo.field, nodeValue);
            }
        }
    },

    bindingDescendant: function (rootNode) {
        var self = this;
        seek(rootNode);

        function seek(node) {
            var children, i;
            if (node.nodeType === 1) { // Work for an element
                try {
                    self.binding(node);
                    children = node.childNodes; // Check all child nodes.
                    if (children) {
                        for (i = 0; i < children.length; i++) {
                            seek(children[i]);
                        }
                    }
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex, "EXCEPTION-31");
                    }
                }
            }
        }
    }
};

INTERMediatorLib.setup();

IMLibKeyEventDispatch = {
    dispatchTable: {},

    clearAll: function () {
        this.dispatchTable = {};
    },

    setExecute: function (idValue, charCode, exec) {
        if (idValue && charCode) {
            if (!this.dispatchTable[idValue]) {
                this.dispatchTable[idValue] = {};
            }
            this.dispatchTable[idValue][charCode] = exec;
        }
    }
};

INTERMediatorLib.addEvent(document, "keydown", function (e) {
    var event, charCode, target, idValue;
    event = e ? e : window.event;
    if (event.charCode) {
        charCode = event.charCode;
    } else {
        charCode = event.keyCode;
    }
    if (!event) {
        return;
    }
    target = event.target;
    if (!target) {
        target = event.srcElement;
        if (!target) {
            return;
        }
    }
    idValue = target.id;
    if (!idValue) {
        return;
    }
    if (!IMLibKeyEventDispatch.dispatchTable[idValue]) {
        return;
    }
    var executable = IMLibKeyEventDispatch.dispatchTable[idValue][charCode];
    if (!executable) {
        return;
    }
    executable(event);
});

IMLibMouseEventDispatch = {
    dispatchTable: {},
    dispatchTableTarget: {},

    clearAll: function () {
        this.dispatchTable = {};
        this.dispatchTableTarget = {};
    },

    setExecute: function (idValue, exec) {
        if (idValue && exec) {
            this.dispatchTable[idValue] = exec;
        }
    },

    setTargetExecute: function (targetValue, exec) {
        if (targetValue && exec) {
            this.dispatchTableTarget[targetValue] = exec;
        }
    }
};

INTERMediatorLib.addEvent(document, "click", function (e) {
    var event, target, idValue, executable, targetDefs, i, nodeInfo, value;
    event = e ? e : window.event;
    if (!event) {
        return;
    }
    target = event.target;
    if (!target) {
        target = event.srcElement;
        if (!target) {
            return;
        }
    }
    idValue = target.id;
    if (!idValue) {
        return;
    }
    executable = IMLibMouseEventDispatch.dispatchTable[idValue];
    if (executable) {
        executable(event);
        return;
    }
    targetDefs = INTERMediatorLib.getLinkedElementInfo(target);
    for (i = 0; i < targetDefs.length; i++) {
        executable = IMLibMouseEventDispatch.dispatchTableTarget[targetDefs[i]];
        if (executable) {
            nodeInfo = INTERMediatorLib.getNodeInfoArray(targetDefs[i]);
            if (nodeInfo.target) {
                value = target.getAttribute(nodeInfo.target);
            } else {
                value = IMLibElement.getValueFromIMNode(target);
            }
            executable(value, target);
            return;
        }
    }
});

IMLibChangeEventDispatch = {
    dispatchTable: {},
    dispatchTableTarget: {},

    clearAll: function () {
        this.dispatchTable = {};
        this.dispatchTableTarget = {};
    },

    setExecute: function (idValue, exec) {
        if (idValue && exec) {
            this.dispatchTable[idValue] = exec;
        }
    },

    setTargetExecute: function (targetValue, exec) {
        if (targetValue && exec) {
            this.dispatchTableTarget[targetValue] = exec;
        }
    }
};

INTERMediatorLib.addEvent(document, "change", function (e) {
    var event = e ? e : window.event;
    if (!event) {
        return;
    }
    var target = event.target;
    if (!target) {
        target = event.srcElement;
        if (!target) {
            return;
        }
    }
    var idValue = target.id;
    if (!idValue) {
        return;
    }
    var executable = IMLibChangeEventDispatch.dispatchTable[idValue];
    if (!executable) {
        return;
    }
    executable(idValue);
});

INTERMediatorLib.addEvent(window, "beforeunload", function (e) {
    var confirmationMessage = "";

//    (e || window.event).returnValue = confirmationMessage;     //Gecko + IE
//    return confirmationMessage;                                //Webkit, Safari, Chrome etc.

});

INTERMediatorLib.addEvent(window, "unload", function (e) {
    INTERMediator_DBAdapter.unregister();
});
