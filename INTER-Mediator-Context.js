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
 * @fileoverview IMLibContextPool, IMLibContext and IMLibLocalContext classes are defined here.
 */
/**
 *
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
var IMLibContextPool = {
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
        var i, j, viewName, refNode, targetNodes, result = [], calcKey;
        viewName = context.viewName;
        if (this.poolingContexts == null) {
            return null;
        }
        if (portal) {
            for (i = 0; i < this.poolingContexts.length; i++) {
                if (this.poolingContexts[i].viewName === viewName &&
                    this.poolingContexts[i].binding[recKey] !== undefined &&
                    this.poolingContexts[i].binding[recKey][key] !== undefined &&
                    this.poolingContexts[i].binding[recKey][key][portal] !== undefined &&
                    this.poolingContexts[i].store[recKey] !== undefined &&
                    this.poolingContexts[i].store[recKey][key] !== undefined &&
                    this.poolingContexts[i].store[recKey][key][portal] !== undefined) {

                    this.poolingContexts[i].store[recKey][key][portal] = value;
                    targetNodes = this.poolingContexts[i].binding[recKey][key][portal];
                    for (j = 0; j < targetNodes.length; j++) {
                        refNode = document.getElementById(targetNodes[j].id);
                        if (refNode) {
                            IMLibElement.setValueToIMNode(refNode, targetNodes[j].target, value, true);
                            result.push(targetNodes[j].id);
                        }
                    }
                }
            }
        } else {
            for (i = 0; i < this.poolingContexts.length; i++) {
                if (this.poolingContexts[i].viewName === viewName &&
                    this.poolingContexts[i].binding[recKey] !== undefined &&
                    this.poolingContexts[i].binding[recKey][key] !== undefined &&
                    this.poolingContexts[i].store[recKey] !== undefined &&
                    this.poolingContexts[i].store[recKey][key] !== undefined) {

                    this.poolingContexts[i].store[recKey][key] = value;
                    targetNodes = this.poolingContexts[i].binding[recKey][key];
                    for (j = 0; j < targetNodes.length; j++) {
                        refNode = document.getElementById(targetNodes[j].id);
                        calcKey = targetNodes[j].id;
                        if (targetNodes[j].target && targetNodes[j].target.length > 0) {
                            calcKey += INTERMediator.separator + targetNodes[j].target;
                        }
                        if (refNode && !(calcKey in IMLibCalc.calculateRequiredObject)) {
                            IMLibElement.setValueToIMNode(refNode, targetNodes[j].target, value, true);
                            result.push(targetNodes[j].id);
                            //console.log(refNode, targetNodes[j].target, value);
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
        if (!element) {
            return result;
        }

        linkInfo = INTERMediatorLib.getLinkedElementInfo(element);
        if (!linkInfo && INTERMediatorLib.isWidgetElement(element.parentNode)) {
            linkInfo = INTERMediatorLib.getLinkedElementInfo(element.parentNode);
        }
        nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfo[0]);

        targetName = target === '' ? '_im_no_target' : target;
        if (this.poolingContexts === null) {
            return null;
        }
        for (i = 0; i < this.poolingContexts.length; i++) {
            targetContext = this.poolingContexts[i];
            if (targetContext.contextInfo[idValue] &&
                targetContext.contextInfo[idValue][targetName] &&
                targetContext.contextInfo[idValue][targetName].context.contextName == nodeInfo.table) {
                result = targetContext.contextInfo[idValue][targetName];
                return result;
            }
        }
        return null;
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
        var keyField = contextDef.key ? contextDef.key : 'id';
        return contextInfo.record.substr(keyField.length + 1);
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

    getContextsFromNameAndForeignValue: function (cName, fValue, parentKeyField) {
        var i, result = [];
        if (!cName) {
            return false;
        }
        //parentKeyField = 'id';
        for (i = 0; i < this.poolingContexts.length; i++) {
            if (this.poolingContexts[i].contextName == cName &&
                this.poolingContexts[i].foreignValue[parentKeyField] == fValue) {
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

    getChildContexts: function (parentContext) {
        var i, childContexts = [];
        for (i = 0; i < this.poolingContexts.length; i++) {
            if (this.poolingContexts[i].parentContext == parentContext) {
                childContexts.push(this.poolingContexts[i]);
            }
        }
        return childContexts;
    },

    childContexts: null,

    removeContextsFromPool: function (contexts) {
        var i, regIds = [], delIds = [];
        for (i = 0; i < this.poolingContexts.length; i++) {
            if (contexts.indexOf(this.poolingContexts[i]) > -1) {
                regIds.push(this.poolingContexts[i].registeredId);
                delIds.push(i);
            }
        }
        for (i = delIds.length - 1; i > -1; i--) {
            this.poolingContexts.splice(delIds[i], 1);
        }
        return regIds;
    },

    removeRecordFromPool: function (repeaterIdValue) {
        var i, j, field, nodeIds = [], targetKeying, targetKeyingObj, parentKeying, relatedId, idValue, delNodes,
            contextAndKey, sameOriginContexts, countDeleteNodes;

        contextAndKey = getContextAndKeyFromId(repeaterIdValue);
        if (contextAndKey == null) {
            return;
        }
        sameOriginContexts = this.getContextsWithSameOrigin(contextAndKey.context);
        //sameOriginContexts.push(contextAndKey.context);
        targetKeying = contextAndKey.key;
        //targetKeyingObj = contextAndKey.context.binding[targetKeying];

        for (i = 0; i < sameOriginContexts.length; i++) {
            targetKeyingObj = sameOriginContexts[i].binding[targetKeying];
            for (field in targetKeyingObj) {
                if (targetKeyingObj.hasOwnProperty(field)) {
                    for (j = 0; j < targetKeyingObj[field].length; j++) {
                        if (nodeIds.indexOf(targetKeyingObj[field][j].id) < 0) {
                            nodeIds.push(targetKeyingObj[field][j].id);
                        }
                    }
                }
            }

            if (INTERMediatorOnPage.dbClassName === 'DB_FileMaker_FX') {
                // for FileMaker portal access mode
                parentKeying = Object.keys(contextAndKey.context.binding)[0];
                relatedId = targetKeying.split('=')[1];
                if (sameOriginContexts[i].binding[parentKeying] &&
                    sameOriginContexts[i].binding[parentKeying]['_im_repeater'] &&
                    sameOriginContexts[i].binding[parentKeying]['_im_repeater'][relatedId] &&
                    sameOriginContexts[i].binding[parentKeying]['_im_repeater'][relatedId][0]) {
                    nodeIds.push(sameOriginContexts[i].binding[parentKeying]['_im_repeater'][relatedId][0].id);
                }
            }
        }
        delNodes = [];
        for (i = 0; i < sameOriginContexts.length; i++) {
            for (idValue in sameOriginContexts[i].contextInfo) {
                if (sameOriginContexts[i].contextInfo.hasOwnProperty(idValue)) {
                    if (nodeIds.indexOf(idValue) >= 0) {
                        delete contextAndKey.context.contextInfo[idValue];
                        delNodes.push(idValue);
                    }
                }
            }
            delete sameOriginContexts[i].binding[targetKeying];
            delete sameOriginContexts[i].store[targetKeying];
        }
        countDeleteNodes = delNodes.length;
        IMLibElement.deleteNodes(delNodes);

        this.poolingContexts = this.poolingContexts.filter(function (context) {
            return nodeIds.indexOf(context.enclosureNode.id) < 0;
        });

        return countDeleteNodes;

        // Private functions
        function getContextAndKeyFromId(repeaterIdValue) {
            var i, field, j, keying, foreignKey;

            for (i = 0; i < IMLibContextPool.poolingContexts.length; i++) {
                for (keying in IMLibContextPool.poolingContexts[i].binding) {
                    if (IMLibContextPool.poolingContexts[i].binding.hasOwnProperty(keying)) {
                        for (field in IMLibContextPool.poolingContexts[i].binding[keying]) {
                            if (IMLibContextPool.poolingContexts[i].binding[keying].hasOwnProperty(field)
                                && field == '_im_repeater') {
                                for (j = 0; j < IMLibContextPool.poolingContexts[i].binding[keying][field].length; j++) {
                                    if (repeaterIdValue == IMLibContextPool.poolingContexts[i].binding[keying][field][j].id) {
                                        return ({context: IMLibContextPool.poolingContexts[i], key: keying});
                                    }
                                }

                                if (INTERMediatorOnPage.dbClassName === 'DB_FileMaker_FX') {
                                    // for FileMaker portal access mode
                                    for (foreignKey in IMLibContextPool.poolingContexts[i].binding[keying][field]) {
                                        for (j = 0; j < IMLibContextPool.poolingContexts[i].binding[keying][field][foreignKey].length; j++) {
                                            if (repeaterIdValue == IMLibContextPool.poolingContexts[i].binding[keying][field][foreignKey][j].id) {
                                                return ({
                                                    context: IMLibContextPool.poolingContexts[i],
                                                    key: '-recid=' + foreignKey
                                                });
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return null;
        }
    },

    getContextsWithSameOrigin: function (originalContext) {
        var i, contexts = [], contextDef, isPortal = false;

        contextDef = IMLibContextPool.getContextDef(originalContext.contextName);
        if (contextDef && contextDef['relation']) {
            for (i in contextDef['relation']) {
                if (contextDef['relation'].hasOwnProperty(i) && contextDef['relation'][i]['portal']) {
                    isPortal = true;
                    break;
                }
            }
        }
        for (i = 0; i < IMLibContextPool.poolingContexts.length; i++) {
            if (IMLibContextPool.poolingContexts[i].sourceName == originalContext.sourceName) {
                if (!isPortal || originalContext.parentContext != IMLibContextPool.poolingContexts[i]) {
                    contexts.push(IMLibContextPool.poolingContexts[i]);
                }
            }
        }
        return contexts;
    },

    updateOnAnotherClient: function (eventName, info) {
        var i, j, k, entityName = info.entity, contextDef, contextView, keyField, recKey;

        if (eventName == 'update') {
            for (i = 0; i < this.poolingContexts.length; i++) {
                contextDef = this.getContextDef(this.poolingContexts[i].contextName);
                contextView = contextDef.view ? contextDef.view : contextDef.name;
                if (contextView == entityName) {
                    keyField = contextDef.key;
                    recKey = keyField + '=' + info.pkvalue;
                    this.poolingContexts[i].setValue(recKey, info.field[0], info.value[0]);

                    var bindingInfo = this.poolingContexts[i].binding[recKey][info.field[0]];
                    for (j = 0; j < bindingInfo.length; j++) {
                        var updateRequiredContext = IMLibContextPool.dependingObjects(bindingInfo[j].id);
                        for (k = 0; k < updateRequiredContext.length; k++) {
                            updateRequiredContext[k].foreignValue = {};
                            updateRequiredContext[k].foreignValue[info.field[0]] = info.value[0];
                            if (updateRequiredContext[k]) {
                                INTERMediator.constructMain(updateRequiredContext[k]);
                            }
                        }
                    }
                }
            }
            IMLibCalc.recalculation();
        } else if (eventName == 'create') {
            for (i = 0; i < this.poolingContexts.length; i++) {
                contextDef = this.getContextDef(this.poolingContexts[i].contextName);
                contextView = contextDef.view ? contextDef.view : contextDef.name;
                if (contextView == entityName) {
                    if (this.poolingContexts[i].isContaining(info.value[0])) {
                        INTERMediator.constructMain(this.poolingContexts[i], info.value);
                    }
                }
            }
            IMLibCalc.recalculation();
        }
        else if (eventName == 'delete') {
            for (i = 0; i < this.poolingContexts.length; i++) {
                contextDef = this.getContextDef(this.poolingContexts[i].contextName);
                contextView = contextDef.view ? contextDef.view : contextDef.name;
                if (contextView == entityName) {
                    this.poolingContexts[i].removeEntry(info.pkvalue);
                }
            }
            IMLibCalc.recalculation();
        }
    },

    getMasterContext: function () {
        var i, contextDef;
        if (!this.poolingContexts) {
            return null;
        }
        for (i = 0; i < this.poolingContexts.length; i++) {
            contextDef = this.poolingContexts[i].getContextDef();
            if (contextDef['navi-control'] && contextDef['navi-control'].match(/master/)) {
                return this.poolingContexts[i];
            }
        }
        return null;
    },

    getDetailContext: function () {
        var i, contextDef;
        if (!this.poolingContexts) {
            return null;
        }
        for (i = 0; i < this.poolingContexts.length; i++) {
            contextDef = this.poolingContexts[i].getContextDef();
            if (contextDef['navi-control'] && contextDef['navi-control'].match(/detail/)) {
                return this.poolingContexts[i];
            }
        }
        return null;
    },

    getContextDef: function (contextName) {
        return INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', contextName);
    },

    getContextFromNodeId: function (nodeId) {
        var i, context, contextDef, rKey, fKey, pKey, isPortal, bindInfo;
        if (!this.poolingContexts) {
            return null;
        }
        for (i = 0; i < this.poolingContexts.length; i++) {
            context = this.poolingContexts[i];
            contextDef = context.getContextDef();
            isPortal = false;
            if (contextDef['relation']) {
                for (rKey in contextDef['relation']) {
                    if (contextDef['relation'][rKey][portal]) {
                        isPortal = true;
                    }
                }
            }
            for (rKey in context.binding) {
                for (fKey in context.binding[rKey]) {
                    if (isPortal) {
                        for (pKey in context.binding[rKey][fKey]) {
                            bindInfo = context.binding[rKey][fKey][pKey];
                            if (bindInfo.nodeId == nodeId) {
                                return context;
                            }
                        }
                    } else {
                        bindInfo = context.binding[rKey][fKey];
                        if (bindInfo.nodeId == nodeId) {
                            return context;
                        }
                    }
                }
            }
        }
        return null;
    },

    getContextFromEnclosureNode: function (enclosureNode) {
        var i, context;
        if (!this.poolingContexts) {
            return null;
        }
        for (i = 0; i < this.poolingContexts.length; i++) {
            context = this.poolingContexts[i];
            if (context.enclosureNode == enclosureNode) {
                return context;
            }
        }
        return null;
    },

    generateContextObject: function (contextDef, enclosure, repeaters, repeatersOriginal) {
        var contextObj = new IMLibContext(contextDef['name']);
        contextObj.contextDefinition = contextDef;
        contextObj.enclosureNode = enclosure;
        contextObj.repeaterNodes = repeaters;
        contextObj.original = repeatersOriginal;
        contextObj.sequencing = true;
        return contextObj;
    }
};

/**
 *
 * @constructor
 */
var IMLibContext = function (contextName) {
    this.contextName = contextName;  // Context Name, set on initialization.
    this.tableName = null;
    this.viewName = null;
    this.sourceName = null;
    this.contextDefinition = null;  // Context Definition object, set on initialization.
    this.store = {};
    this.binding = {};
    this.contextInfo = {};
    this.modified = {};
    this.recordOrder = [];
    this.pendingOrder = [];
    IMLibContextPool.registerContext(this);

    this.foreignValue = {};
    this.enclosureNode = null;    // Set on initialization.
    this.repeaterNodes = null;   // Set on initialization.
    this.dependingObject = [];
    this.original = null;      // Set on initialization.
    this.nullAcceptable = true;
    this.parentContext = null;
    this.registeredId = null;
    this.sequencing = false;       // Set true on initialization.
    this.dependingParentObjectInfo = null;
    this.isPortal = false;
    this.potalContainingRecordKV = null;

    this.updateFieldValue = function (idValue, succeedProc, errorProc, warnMultipleRecProc, warnOthersModifyProc) {
        var nodeInfo, contextInfo, linkInfo, changedObj, criteria, newValue;

        changedObj = document.getElementById(idValue);
        linkInfo = INTERMediatorLib.getLinkedElementInfo(changedObj);
        nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfo[0]);  // Suppose to be the first definition.
        contextInfo = IMLibContextPool.getContextInfoFromId(idValue, nodeInfo.target);   // suppose to target = ''

        if (INTERMediator.ignoreOptimisticLocking) {
            IMLibContextPool.updateContext(idValue, nodeInfo.target);
            newValue = IMLibElement.getValueFromIMNode(changedObj);
            if (newValue !== null) {
                criteria = contextInfo.record.split('=');
                INTERMediatorOnPage.retrieveAuthInfo();
                if (contextInfo.context.isPortal) {
                    criteria = contextInfo.context.potalContainingRecordKV.split('=');
                    INTERMediator_DBAdapter.db_update_async(
                        {
                            name: contextInfo.context.parentContext.contextName,
                            conditions: [{field: criteria[0], operator: '=', value: criteria[1]}],
                            dataset: [
                                {
                                    field: contextInfo.field + '.' + contextInfo.record.split('=')[1],
                                    value: newValue
                                }
                            ]
                        },
                        succeedProc,
                        errorProc
                    );
                } else {
                    criteria = contextInfo.record.split('=');
                    INTERMediator_DBAdapter.db_update_async(
                        {
                            name: contextInfo.context.contextName,
                            conditions: [{field: criteria[0], operator: '=', value: criteria[1]}],
                            dataset: [{field: contextInfo.field, value: newValue}]
                        },
                        succeedProc,
                        errorProc
                    );
                }

            }
        } else {
            var targetContext = contextInfo.context;
            var parentContext = targetContext.parentContext;
            var targetField = contextInfo['field'];
            var keyingComp
                = (targetContext.isPortal ? targetContext.potalContainingRecordKV : contextInfo['record']).split('=');
            var keyingField = keyingComp[0];
            keyingComp.shift();
            var keyingValue = keyingComp.join('=');
            INTERMediator_DBAdapter.db_query_async(
                {
                    name: targetContext.isPortal ? parentContext.contextName : targetContext.contextName,
                    records: 1,
                    paging: false,
                    fields: [contextInfo['field']],
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
                    var changedObjectCapt = changedObj;
                    var nodeInfoCapt = nodeInfo;
                    var idValueCapt = idValue;
                    return function (result) {
                        var initialvalue, newValue, isOthersModified, currentFieldVal, recordset = [],
                            portalRecords, index, keyField, keyingComp, criteria;
                        if (targetContextCapt.isPortal) {
                            portalRecords = targetContextCapt.getPortalRecordsetImpl(
                                result.dbresult[0],
                                targetContextCapt.contextName);
                            keyField = targetContextCapt.getKeyField();
                            keyingComp = contextInfoCapt.record.split('=');
                            for (index = 0; index < portalRecords.length; index++) {
                                if (portalRecords[index][keyField] == keyingComp[1]) {
                                    recordset.push(portalRecords[index]);
                                    break;
                                }
                            }
                        } else {
                            recordset = result.dbresult;
                        }
                        if (!recordset || !recordset[0] ||  // This value could be null or undefined
                            recordset[0][targetFieldCapt] === undefined) {
                            errorProc();
                            return;
                        }
                        if (result.resultCount > 1) {
                            if (!warnMultipleRecProc()) {
                                return;
                            }
                        }
                        if (targetContextCapt.isPortal) {
                            for (var i = 0; i < recordset.length; i++) {
                                if (recordset[i]['-recid'] === contextInfo['record'].split('=')[1]) {
                                    currentFieldVal = recordset[i][targetFieldCapt];
                                    break;
                                }
                            }
                            initialvalue = targetContextCapt.getValue(Object.keys(parentContext.store)[0], targetFieldCapt, '-recid=' + recordset[i]['-recid']);
                        } else {
                            currentFieldVal = recordset[0][targetFieldCapt];
                            initialvalue = targetContextCapt.getValue(contextInfoCapt.record, targetFieldCapt);
                        }
                        isOthersModified = checkSameValue(initialvalue, currentFieldVal);
                        if (changedObjectCapt.tagName == 'INPUT' &&
                            changedObjectCapt.getAttribute('type') == 'checkbox') {
                            if (initialvalue == changedObjectCapt.value) {
                                isOthersModified = false;
                            } else if (!parseInt(currentFieldVal)) {
                                isOthersModified = false;
                            } else {
                                isOthersModified = true;
                            }
                        }
                        if (isOthersModified) {
                            // The value of database and the field is different. Others must be changed this field.
                            newValue = IMLibElement.getValueFromIMNode(changedObjectCapt);
                            if (!warnOthersModifyProc(initialvalue, newValue, currentFieldVal)) {
                                return;
                            }
                            INTERMediatorOnPage.retrieveAuthInfo(); // This is required. Why?
                        }
                        IMLibContextPool.updateContext(idValueCapt, nodeInfoCapt.target);
                        newValue = IMLibElement.getValueFromIMNode(changedObjectCapt);
                        if (newValue != null) {
                            INTERMediatorOnPage.retrieveAuthInfo();
                            if (targetContextCapt.isPortal) {
                                criteria = targetContextCapt.potalContainingRecordKV.split('=');
                                INTERMediator_DBAdapter.db_update_async(
                                    {
                                        name: targetContextCapt.parentContext.contextName,
                                        conditions: [{field: criteria[0], operator: '=', value: criteria[1]}],
                                        dataset: [
                                            {
                                                field: contextInfoCapt.field + '.' + contextInfoCapt.record.split('=')[1],
                                                value: newValue
                                            }
                                        ]
                                    },
                                    succeedProc,
                                    errorProc
                                );
                            } else {
                                criteria = contextInfoCapt.record.split('=');
                                INTERMediator_DBAdapter.db_update_async(
                                    {
                                        name: targetContextCapt.contextName,
                                        conditions: [{field: criteria[0], operator: '=', value: criteria[1]}],
                                        dataset: [{field: contextInfo.field, value: newValue}]
                                    },
                                    succeedProc,
                                    errorProc
                                );
                            }
                        }
                    };
                })(),
                function
                    () {
                    INTERMediatorOnPage.hideProgress();
                    INTERMediator.setErrorMessage('Error in valueChange method.', 'EXCEPTION-1');
                    IMLibUI.clearLockInfo();
                }
            );
        }

        var handleAsNullValue = ["0000-00-00", "0000-00-00 00:00:00"];

        function checkSameValue(initialvalue, currentFieldVal) {
            if (handleAsNullValue.indexOf(initialvalue)) {
                initialvalue = "";
            }
            if (handleAsNullValue.indexOf(currentFieldVal)) {
                currentFieldVal = "";
            }
            return initialvalue != currentFieldVal;
        }
    };

    this.getKeyField = function () {
        var keyField;
        if (INTERMediatorOnPage.dbClassName === 'DB_FileMaker_FX') {
            if (this.isPortal) {
                keyField = '-recid';
            } else {
                keyField = this.contextDefinition['key'] ? this.contextDefinition['key'] : '-recid';
            }
        } else {
            keyField = this.contextDefinition['key'] ? this.contextDefinition['key'] : 'id';
        }
        return keyField;
    };

    this.getCalculationFields = function () {
        var calcDef = this.contextDefinition['calculation'];
        var calcFields = [], ix;
        for (ix in calcDef) {
            if (calcDef.hasOwnProperty(ix)) {
                calcFields.push(calcDef[ix]['field']);
            }
        }
        return calcFields;
    };

    this.isUseLimit = function () {
        var useLimit = false;
        if (this.contextDefinition['records'] && this.contextDefinition['paging']) {
            useLimit = true;
        }
        return useLimit;
    };

    this.getPortalRecords = function () {
        var targetRecords = {};
        if (!this.isPortal) {
            return null;
        }
        targetRecords.recordset = this.getPortalRecordsetImpl(
            this.parentContext.store[this.potalContainingRecordKV], this.contextName);
        return targetRecords;
    };

    this.getPortalRecordsetImpl = function (store, contextName) {
        var result, recId, recordset, key, contextDef;
        recordset = [];
        if (store[0]) {
            if (!store[0][contextName]) {
                for (key in store[0]) {
                    contextDef = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', key);
                    if (contextName === contextDef.view && !store[0][contextName]) {
                        contextName = key;
                        break;
                    }
                }
            }
            if (store[0][contextName]) {
                result = store[0][contextName];
                for (recId in result) {
                    if (result.hasOwnProperty(recId) && isFinite(recId)) {
                        recordset.push(result[recId]);
                    }
                }
            }
        }
        return recordset;
    };

    this.getRecordNumber = function () {
        var recordNumber;

        if (this.contextDefinition['navi-control'] &&
            this.contextDefinition['navi-control'] === 'detail') {
            recordNumber = 1;
        } else {
            if (this.contextDefinition.maxrecords) {
                if (parseInt(INTERMediator.pagedSize, 10) === 0) {
                    if (this.contextDefinition.records) {
                        recordNumber = parseInt(this.contextDefinition.records, 10);
                    } else {
                        recordNumber = parseInt(this.contextDefinition.maxrecords, 10);
                    }
                } else {
                    if (parseInt(this.contextDefinition.maxrecords, 10) < parseInt(INTERMediator.pagedSize, 10)) {
                        if (parseInt(this.contextDefinition.maxrecords, 10) < parseInt(this.contextDefinition.records, 10)) {
                            recordNumber = parseInt(this.contextDefinition.records, 10);
                        } else {
                            recordNumber = parseInt(this.contextDefinition.maxrecords, 10);
                        }
                    } else {
                        if (this.contextDefinition.relation) {
                            recordNumber = parseInt(this.contextDefinition.records, 10);
                        } else {
                            recordNumber = parseInt(INTERMediator.pagedSize, 10);
                        }
                    }
                }
            } else {
                if (parseInt(INTERMediator.pagedSize, 10) === 0 ||
                    (parseInt(this.contextDefinition.records, 10) < parseInt(INTERMediator.pagedSize, 10))) {
                    recordNumber = parseInt(this.contextDefinition.records, 10);
                } else {
                    if (this.contextDefinition.relation) {
                        recordNumber = parseInt(this.contextDefinition.records, 10);
                    } else {
                        recordNumber = parseInt(INTERMediator.pagedSize, 10);
                    }
                }
            }
            if (!this.contextDefinition.relation &&
                this.contextDefinition.paging && Boolean(this.contextDefinition.paging) === true) {
                INTERMediator.setLocalProperty('_im_pagedSize', recordNumber);
            }
        }
        return recordNumber;
    };

    this.setRelationWithParent = function (currentRecord, parentObjectInfo, parentContext) {
        var relationDef, index, joinField, fieldName, i;

        this.parentContext = parentContext;

        if (currentRecord) {
            try {
                relationDef = this.contextDefinition['relation'];
                if (relationDef) {
                    for (index in relationDef) {
                        if (Boolean(relationDef[index].portal) === true) {
                            this.isPortal = true;
                            this.potalContainingRecordKV = '-recid=' + currentRecord['-recid'];
                        }
                        joinField = relationDef[index]['join-field'];
                        this.addForeignValue(joinField, currentRecord[joinField]);
                        for (fieldName in parentObjectInfo) {
                            if (fieldName == relationDef[index]['join-field']) {
                                for (i = 0; i < parentObjectInfo[fieldName].length; i++) {
                                    this.addDependingObject(parentObjectInfo[fieldName][i]);
                                }
                                this.dependingParentObjectInfo =
                                    JSON.parse(JSON.stringify(parentObjectInfo));
                            }
                        }
                    }
                }
            } catch (ex) {
                if (ex == '_im_requath_request_') {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex, 'EXCEPTION-25');
                }
            }
        }

    };

    this.getInsertOrder = function (record) {
        var cName, sortKeys = [], contextDef, i, sortFields = [], sortDirections = [];
        for (cName in INTERMediator.additionalSortKey) {
            if (cName == this.contextName) {
                sortKeys.push(INTERMediator.additionalSortKey[cName]);
            }
        }
        contextDef = this.getContextDef();
        if (contextDef.sort) {
            sortKeys.push(contextDef.sort);
        }
        for (i = 0; i < sortKeys.length; i++) {
            if (sortFields.indexOf(sortKeys[i].field) < 0) {
                sortFields.push(sortKeys[i].field);
                sortDirections.push(sortKeys[i].direction);
            }
        }
    };

    this.indexingArray = function (keyField) {
        var ar = [], key, keyArray, counter = 0;
        for (key in this.store) {
            keyArray = key.split('=');
            ar[counter] = this.store[key][keyField];
            counter += 1;
        }
        return ar;
    };

    this.clearAll = function () {
        this.store = {};
        this.binding = {};
    };

    this.setContextName = function (name) {
        this.contextName = name;
    };

    this.getContextDef = function () {
        return INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', this.contextName);
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
        var i;
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
            this.sourceName = this.contextName;
            // This is not a valid case, it just prevent the error in the unit test.
            return;
        }
        contextDef = this.getContextDef();
        if (contextDef) {
            this.viewName = contextDef['view'] ? contextDef['view'] : contextDef['name'];
            this.tableName = contextDef['table'] ? contextDef['table'] : contextDef['name'];
            this.sourceName = (contextDef['source'] ? contextDef['source']
                : (contextDef['table'] ? contextDef['table']
                : (contextDef['view'] ? contextDef['view'] : contextDef['name'])));
        }
    };

    this.removeContext = function () {
        var regIds = [], childContexts = [];
        seekRemovingContext(this);
        regIds = IMLibContextPool.removeContextsFromPool(childContexts);
        while (this.enclosureNode.firstChild) {
            this.enclosureNode.removeChild(this.enclosureNode.firstChild);
        }
        INTERMediator_DBAdapter.unregister(regIds);

        function seekRemovingContext(context) {
            var i, myChildren;
            childContexts.push(context);
            regIds.push(context.registeredId);
            myChildren = IMLibContextPool.getChildContexts(context);
            for (i = 0; i < myChildren.length; i++) {
                seekRemovingContext(myChildren[i]);
            }
        }
    };

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

    this.getContextDef = function () {
        var contextDef;
        contextDef = INTERMediatorLib.getNamedObject(
            INTERMediatorOnPage.getDataSources(), 'name', this.contextName);
        return contextDef;
    };

    /*
     * The isDebug parameter is for debugging and testing. Usually you should not specify it.
     */
    this.checkOrder = function (oneRecord, isDebug) {
        var i, fields = [], directions = [], oneSortKey, condtextDef, lower, upper, index, targetRecord,
            contextValue, checkingValue, stop;
        if (isDebug !== true) {
            if (INTERMediator && INTERMediator.additionalSortKey[this.contextName]) {
                for (i = 0; i < INTERMediator.additionalSortKey[this.contextName].length; i++) {
                    oneSortKey = INTERMediator.additionalSortKey[this.contextName][i];
                    if (!(oneSortKey.field in fields)) {
                        fields.push(oneSortKey.field);
                        directions.push(oneSortKey.direction);
                    }
                }
            }
            condtextDef = this.getContextDef();
            if (condtextDef && condtextDef.sort) {
                for (i = 0; i < condtextDef.sort.length; i++) {
                    oneSortKey = condtextDef.sort[i];
                    if (!(oneSortKey.field in fields)) {
                        fields.push(oneSortKey.field);
                        directions.push(oneSortKey.direction);
                    }
                }
            }
        } else {
            fields = ['field1', 'field2'];
        }
        lower = 0;
        upper = this.recordOrder.length;
        for (i = 0; i < fields.length; i++) {
            if (oneRecord[fields[i]]) {
                index = parseInt((upper + lower) / 2);
                do {
                    targetRecord = this.store[this.recordOrder[index]];
                    contextValue = targetRecord[fields[i]];
                    checkingValue = oneRecord[fields[i]];
                    if (contextValue < checkingValue) {
                        lower = index;
                    } else if (contextValue > checkingValue) {
                        upper = index;
                    } else {
                        lower = upper = index;
                    }
                    index = parseInt((upper + lower) / 2);
                } while (upper - lower > 1);
                targetRecord = this.store[this.recordOrder[index]];
                contextValue = targetRecord[fields[i]];
                if (contextValue == checkingValue) {
                    lower = upper = index;
                    stop = false;
                    do {
                        targetRecord = this.store[this.recordOrder[lower - 1]];
                        if (targetRecord && targetRecord[fields[i]] && targetRecord[fields[i]] == checkingValue) {
                            lower--;
                        } else {
                            stop = true;
                        }
                    } while (!stop);
                    stop = false;
                    do {
                        targetRecord = this.store[this.recordOrder[upper + 1]];
                        if (targetRecord && targetRecord[fields[i]] && targetRecord[fields[i]] == checkingValue) {
                            upper++;
                        } else {
                            stop = true;
                        }
                    } while (!stop);
                    if (lower == upper) {
                        // index is the valid order number.
                        break;
                    }
                    upper++;
                } else if (contextValue < checkingValue) {
                    // index is the valid order number.
                    break;
                } else if (contextValue > checkingValue) {
                    index--;
                    break;
                }
            }
        }
        if (isDebug === true) {
            console.log('#lower=' + lower + ',upper=' + upper + ',index=' + index +
                ',contextValue=' + contextValue + ',checkingValue=' + checkingValue);
        }
        return index;
    };

    /*
     * The isDebug parameter is for debugging and testing. Usually you should not specify it.
     */
    this.rearrangePendingOrder = function (isDebug) {
        var i, index, targetRecord;
        for (i = 0; i < this.pendingOrder.length; i++) {
            targetRecord = this.store[this.pendingOrder[i]];
            index = this.checkOrder(targetRecord, isDebug);
            if (index >= -1) {
                this.recordOrder.splice(index + 1, 0, this.pendingOrder[i]);
            } else {
                // something wrong...
            }
        }
        this.pendingOrder = [];
    };

    this.getRepeaterEndNode = function (index) {
        var nodeId, field, repeaters = [], repeater, node, i, enclosure, children;

        var recKey = this.recordOrder[index];
        for (field in this.binding[recKey]) {
            nodeId = this.binding[recKey][field].nodeId;
            repeater = INTERMediatorLib.getParentRepeater(document.getElementById(nodeId));
            if (!(repeater in repeaters)) {
                repeaters.push(repeater);
            }
        }
        if (repeaters.length < 1) {
            return null;
        }
        node = repeaters[0];
        enclosure = INTERMediatorLib.getParentEnclosure(node);
        children = enclosure.childNodes;
        for (i = 0; i < children.length; i++) {
            if (children[i] in repeaters) {
                node = repeaters[i];
                break;
            }
        }
        return node;
    };

    this.storeRecords = function (records) {
        var ix, record, field, keyField, keyValue;
        var contextDef = contextDef = INTERMediatorLib.getNamedObject(
            INTERMediatorOnPage.getDataSources(), 'name', this.contextName);
        keyField = contextDef['key'] ? contextDef['key'] : 'id';
        if (records.recordset) {
            for (ix = 0; ix < records.recordset.length; ix++) {
                record = records.recordset[ix];
                for (field in record) {
                    keyValue = record[keyField] ? record[keyField] : ix;
                    this.setValue(keyField + '=' + keyValue, field, record[field]);
                }
            }
        }
    };

    this.getDataAtLastRecord = function (key) {
        var lastKey;
        var storekeys = Object.keys(this.store);
        if (storekeys.length > 0) {
            lastKey = storekeys[storekeys.length - 1];
            return this.getValue(lastKey, key);
        }
        return undefined;
    };

    // setData____ methods are for storing data both the model and the database.
//
    this.setDataAtLastRecord = function (key, value) {
        var lastKey, keyAndValue;
        var storekeys = Object.keys(this.store);
        if (storekeys.length > 0) {
            lastKey = storekeys[storekeys.length - 1];
            this.setValue(lastKey, key, value);
            keyAndValue = lastKey.split('=');
            INTERMediator_DBAdapter.db_update({
                name: this.contextName,
                conditions: [{field: keyAndValue[0], operator: '=', value: keyAndValue[1]}],
                dataset: [{field: key, value: value}]
            });
            IMLibCalc.recalculation();
            INTERMediator.flushMessage();
        }
    };

    this.setDataWithKey = function (pkValue, key, value) {
        var targetKey, contextDef, storeElements;
        contextDef = this.getContextDef();
        if (!contextDef) {
            return;
        }
        targetKey = contextDef.key + '=' + pkValue;
        storeElements = this.store[targetKey];
        if (storeElements) {
            this.setValue(targetKey, key, value);
            INTERMediator_DBAdapter.db_update({
                name: this.contextName,
                conditions: [{field: contextDef.key, operator: '=', value: pkValue}],
                dataset: [{field: key, value: value}]
            });
            INTERMediator.flushMessage();
        }
    };

    this.setValue = function (recKey, key, value, nodeId, target, portal) {
        //console.error(this.contextName, this.tableName, recKey, key, value, nodeId);
        var updatedNodeIds = null;
        if (portal) {
            /* eslint no-console: ["error", {allow: ["error"]}] */
            console.error('Using the portal parameter in IMLibContext.setValue');
        }
        if (recKey != undefined && recKey != null) {
            if (this.store[recKey] === undefined) {
                this.store[recKey] = {};
            }
            if (portal && this.store[recKey][key] === undefined) {
                this.store[recKey][key] = {};
            }
            if (this.binding[recKey] === undefined) {
                this.binding[recKey] = {};
                if (this.sequencing) {
                    this.recordOrder.push(recKey);
                } else {
                    this.pendingOrder.push(recKey);
                }
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
                    //this.store[recKey][key][portal] = value;
                    this.store[recKey][key] = value;
                } else {
                    this.store[recKey][key] = value;
                }
                if (nodeId) {
                    if (portal) {
                        //this.binding[recKey][key][portal].push({id: nodeId, target: target});
                        this.binding[recKey][key].push({id: nodeId, target: target});
                    } else {
                        this.binding[recKey][key].push({id: nodeId, target: target});
                    }
                    if (this.contextInfo[nodeId] === undefined) {
                        this.contextInfo[nodeId] = {};
                    }
                    this.contextInfo[nodeId][target == '' ? '_im_no_target' : target] =
                    {context: this, record: recKey, field: key};
                    if (portal) {
                        this.contextInfo[nodeId][target == '' ? '_im_no_target' : target].portal = portal;
                    }
                } else {
                    if (INTERMediator.partialConstructing) {
                        updatedNodeIds = IMLibContextPool.synchronize(this, recKey, key, value, target, portal);
                    }
                }
            }
        }
        return updatedNodeIds;
    };

    this.getValue = function (recKey, key, portal) {
        var value;
        try {
            if (portal) {
                value = this.store[portal][key];
            } else {
                value = this.store[recKey][key];
            }
            if (Array.isArray(value)) {
                value = value.join();
            }
            return value === undefined ? null : value;
        } catch (ex) {
            return null;
        }
    };

    this.isValueUndefined = function (recKey, key, portal) {
        var value, tableOccurence, relatedRecId;
        try {
            if (portal) {
                tableOccurence = key.split('::')[0];
                relatedRecId = portal.split('=')[1];
                value = this.store[recKey][0][tableOccurence][relatedRecId][key];
            } else {
                value = this.store[recKey][key];
            }
            return value === undefined ? true : false;
        } catch (ex) {
            return null;
        }
    };

    this.getContextInfo = function (nodeId, target) {
        try {
            var info = this.contextInfo[nodeId][target == '' ? '_im_no_target' : target];
            return info === undefined ? null : info;
        } catch (ex) {
            return null;
        }
    };

    this.getContextValue = function (nodeId, target) {
        try {
            var info = this.contextInfo[nodeId][target == '' ? '_im_no_target' : target];
            var value = info.context.getValue(info.record, info.field);
            return value === undefined ? null : value;
        } catch (ex) {
            return null;
        }
    };

    this.getContextRecord = function (nodeId) {
        var infos, keys, i;
        try {
            infos = this.contextInfo[nodeId];
            keys = Object.keys(infos);
            for (i = 0; i < keys.length; i++) {
                if (infos[keys[i]]) {
                    return this.store[infos[keys[i]].record];
                }
            }
            return null;
        } catch (ex) {
            return null;
        }
    };

    this.removeEntry = function (pkvalue) {
        var keyField, keying, bindingInfo, contextDef, targetNode, repeaterNodes, i, parentNode,
            removingNodeIds = [];
        contextDef = this.getContextDef();
        keyField = contextDef.key;
        keying = keyField + '=' + pkvalue;
        bindingInfo = this.binding[keying];
        if (bindingInfo) {
            repeaterNodes = bindingInfo['_im_repeater'];
            if (repeaterNodes) {
                for (i = 0; i < repeaterNodes.length; i++) {
                    removingNodeIds.push(repeaterNodes[i].id);
                }
            }
        }
        if (removingNodeIds.length > 0) {
            for (i = 0; i < removingNodeIds.length; i++) {
                IMLibContextPool.removeRecordFromPool(removingNodeIds[i]);
            }
            for (i = 0; i < removingNodeIds.length; i++) {
                targetNode = document.getElementById(removingNodeIds[i]);
                if (targetNode) {
                    parentNode = INTERMediatorLib.getParentRepeater(targetNode);
                    if (parentNode) {
                        parentNode.parentNode.removeChild(targetNode);
                    }
                }
            }
        }
    };

    this.isContaining = function (value) {
        var contextDef, contextName, checkResult = [], i, fieldName, result, opePosition, leftHand, rightHand,
            leftResult, rightResult;

        contextDef = this.getContextDef();
        contextName = contextDef.name;
        if (contextDef.query) {
            for (i in contextDef.query) {
                checkResult.push(checkCondition(contextDef.query[i], value));
            }
        }
        if (INTERMediator.additionalCondition[contextName]) {
            for (i = 0; i < INTERMediator.additionalCondition[contextName].length; i++) {
                checkResult.push(checkCondition(INTERMediator.additionalCondition[contextName][i], value));
            }
        }

        result = true;
        if (checkResult.length != 0) {
            opePosition = checkResult.indexOf('D');
            if (opePosition > -1) {
                leftHand = checkResult.slice(0, opePosition);
                rightHand = opePosition.slice(opePosition + 1);
                if (rightHand.length == 0) {
                    result = (leftHand.indexOf(false) < 0);
                } else {
                    leftResult = (leftHand.indexOf(false) < 0);
                    rightResult = (rightHand.indexOf(false) < 0);
                    result = leftResult || rightResult;
                }
            } else {
                opePosition = checkResult.indexOf('EX');
                if (opePosition > -1) {
                    leftHand = checkResult.slice(0, opePosition);
                    rightHand = opePosition.slice(opePosition + 1);
                    if (rightHand.length == 0) {
                        result = (leftHand.indexOf(true) > -1);
                    } else {
                        leftResult = (leftHand.indexOf(true) > -1);
                        rightResult = (rightHand.indexOf(true) > -1);
                        result = leftResult && rightResult;
                    }
                } else {
                    opePosition = checkResult.indexOf(false);
                    if (opePosition > -1) {
                        result = (checkResult.indexOf(false) < 0);
                    }
                }
            }

            if (result == false) {
                return false;
            }
        }

        if (this.foreignValue) {
            for (fieldName in this.foreignValue) {
                if (contextDef.relation) {
                    for (i in contextDef.relation) {
                        if (contextDef.relation[i]['join-field'] == fieldName) {
                            result &= (checkCondition({
                                field: contextDef.relation[i]['foreign-key'],
                                operator: '=',
                                value: this.foreignValue[fieldName]
                            }, value));
                        }
                    }
                }
            }
        }

        return result;

        function checkCondition(conditionDef, oneRecord) {
            var realValue;

            if (conditionDef.field == '__operation__') {
                return conditionDef.operator == 'ex' ? 'EX' : 'D';
            }

            realValue = oneRecord[conditionDef.field];
            if (!realValue) {
                return false;
            }
            switch (conditionDef.operator) {
            case '=':
            case 'eq':
                return realValue == conditionDef.value;
            case '>':
            case 'gt':
                return realValue > conditionDef.value;
            case '<':
            case 'lt':
                return realValue < conditionDef.value;
            case '>=':
            case 'gte':
                return realValue >= conditionDef.value;
            case '<=':
            case 'lte':
                return realValue <= conditionDef.value;
            case '!=':
            case 'neq':
                return realValue != conditionDef.value;
            default:
                return false;
            }
        }
    };

    this.insertEntry = function (pkvalue, fields, values) {
        var i, field, value;
        for (i = 0; i < fields.length; i++) {
            field = fields[i];
            value = values[i];
            this.setValue(pkvalue, field, value);
        }
    };

    /*
     * Initialize this object
     */
    this.setTable(this);
};

/**
 *
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
var IMLibLocalContext = {
    contextName: '_',
    store: {},
    binding: {},

    clearAll: function () {
        this.store = {};
    },

    setValue: function (key, value, withoutArchive) {
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
                        IMLibElement.setValueToIMNode(node, '', value, true);
                    }
                }
            }
        }
        if (hasUpdated && !(withoutArchive === true)) {
            this.archive();
        }
    },

    getValue: function (key) {
        var value = this.store[key];
        return value === undefined ? null : value;
    },

    archive: function () {
        var jsonString, key, searchLen, hashLen, trailLen;
        INTERMediatorOnPage.removeCookie('_im_localcontext');
        if (INTERMediator.isIE && INTERMediator.ieVersion < 9) {
            this.store._im_additionalCondition = INTERMediator.additionalCondition;
            this.store._im_additionalSortKey = INTERMediator.additionalSortKey;
            this.store._im_startFrom = INTERMediator.startFrom;
            this.store._im_pagedSize = INTERMediator.pagedSize;
            /*
             IE8 issue: '' string is modified as 'null' on JSON stringify.
             http://blogs.msdn.com/b/jscript/archive/2009/06/23/serializing-the-value-of-empty-dom-elements-using-native-json-in-ie8.aspx
             */
            jsonString = JSON.stringify(this.store, function (k, v) {
                return v === '' ? '' : v;
            });
        } else {
            jsonString = JSON.stringify(this.store);
        }
        if (INTERMediator.useSessionStorage === true &&
            typeof sessionStorage !== 'undefined' &&
            sessionStorage !== null) {
            try {
                searchLen = location.search ? location.search.length : 0;
                hashLen = location.hash ? location.hash.length : 0;
                trailLen = searchLen + hashLen;
                key = '_im_localcontext' + document.URL.toString();
                key = (trailLen > 0) ? key.slice(0, -trailLen) : key;
                sessionStorage.setItem(key, jsonString);
            } catch (ex) {
                INTERMediatorOnPage.setCookieWorker('_im_localcontext', jsonString, false, 0);
            }
        } else {
            INTERMediatorOnPage.setCookieWorker('_im_localcontext', jsonString, false, 0);
        }
        //console.log('##Archive:', key);
        //console.log('##Archive:', this.store);
    },

    unarchive: function () {
        var localContext = '', searchLen, hashLen, key, trailLen;
        if (INTERMediator.useSessionStorage === true &&
            typeof sessionStorage !== 'undefined' &&
            sessionStorage !== null) {
            try {
                searchLen = location.search ? location.search.length : 0;
                hashLen = location.hash ? location.hash.length : 0;
                trailLen = searchLen + hashLen;
                key = '_im_localcontext' + document.URL.toString();
                key = (trailLen > 0) ? key.slice(0, -trailLen) : key;
                localContext = sessionStorage.getItem(key);
            } catch (ex) {
                localContext = INTERMediatorOnPage.getCookie('_im_localcontext');
            }
        } else {
            localContext = INTERMediatorOnPage.getCookie('_im_localcontext');
        }
        if (localContext && localContext.length > 0) {
            this.store = JSON.parse(localContext);
            //console.log('##Unarchive:', key);
            //console.log('##Unarchive:', this.store);
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
            this.updateAll(true);
        }
    },

    bindingNode: function (node) {
        var linkInfos, nodeInfo, idValue, i, j, value, params, unbinding, unexistId, dataImControl;
        if (node.nodeType != 1) {
            return;
        }
        linkInfos = INTERMediatorLib.getLinkedElementInfo(node);
        dataImControl = node.getAttribute("data-im-control");
        unbinding = (dataImControl && dataImControl == "unbind");
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
                if (this.binding[nodeInfo.field].indexOf(idValue) < 0 && ! unbinding) {
                    this.binding[nodeInfo.field].push(idValue);
                    //this.store[nodeInfo.field] = document.getElementById(idValue).value;
                }
                unexistId = -1;
                while (unexistId >= 0) {
                    for (j = 0; j < this.binding[nodeInfo.field].length; j++) {
                        if (!document.getElementById(this.binding[nodeInfo.field][j])) {
                            unexistId = j;
                        }
                    }
                    if (unexistId >= 0) {
                        delete this.binding[nodeInfo.field][unexistId];
                    }
                }

                params = nodeInfo.field.split(':');
                switch (params[0]) {
                case 'addorder':
                    IMLibMouseEventDispatch.setExecute(idValue, IMLibUI.eventAddOrderHandler);
                    break;
                case 'update':
                    IMLibMouseEventDispatch.setExecute(idValue, (function () {
                        var contextName = params[1];
                        return function () {
                            INTERMediator.startFrom = 0;
                            IMLibUI.eventUpdateHandler(contextName);
                            IMLibPageNavigation.navigationSetup();
                        };
                    })());
                    break;
                case 'condition':
                    var attrType = node.getAttribute("type");
                    if (attrType && attrType == "text") {
                        IMLibKeyEventDispatch.setExecuteByCode(idValue, 13, (function () {
                            var contextName = params[1];
                            return function () {
                                INTERMediator.startFrom = 0;
                                IMLibUI.eventUpdateHandler(contextName);
                                IMLibPageNavigation.navigationSetup();
                            };
                        })());
                    } else if (attrType && (attrType == "checkbox" ||attrType == "radio")) {
                        IMLibChangeEventDispatch.setExecute(idValue, (function () {
                            var contextName = params[1];
                            return function () {
                                INTERMediator.startFrom = 0;
                                IMLibUI.eventUpdateHandler(contextName);
                                IMLibPageNavigation.navigationSetup();
                            };
                        })());
                    }
                    break;
                case 'limitnumber':
                    IMLibChangeEventDispatch.setExecute(idValue, (function () {
                        var contextName = params[1];
                        return function () {
                            INTERMediator.pagedSize = document.getElementById(idValue).value;
                            IMLibUI.eventUpdateHandler(contextName);
                            IMLibPageNavigation.navigationSetup();
                        };
                    })());
                    break;
                default:
                    IMLibChangeEventDispatch.setExecute(idValue, IMLibLocalContext.update);
                    break;
                }

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
        IMLibLocalContext.updateFromNodeValue(idValue);
    },

    updateFromNodeValue: function (idValue) {
        var node, nodeValue, linkInfos, nodeInfo, i;
        node = document.getElementById(idValue);
        nodeValue = IMLibElement.getValueFromIMNode(node);
        linkInfos = INTERMediatorLib.getLinkedElementInfo(node);
        for (i = 0; i < linkInfos.length; i++) {
            IMLibLocalContext.store[linkInfos[i]] = nodeValue;
            nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfos[i]);
            if (nodeInfo.table == IMLibLocalContext.contextName) {
                IMLibLocalContext.setValue(nodeInfo.field, nodeValue);
            }
        }
    },

    updateFromStore: function (idValue) {
        var node, nodeValue, linkInfos, nodeInfo, i, target, comp;
        node = document.getElementById(idValue);
        target = node.getAttribute('data-im');
        comp = target.split(INTERMediator.separator);
        if (comp[1]) {
            nodeValue = IMLibLocalContext.store[comp[1]];
            linkInfos = INTERMediatorLib.getLinkedElementInfo(node);
            for (i = 0; i < linkInfos.length; i++) {
                IMLibLocalContext.store[linkInfos[i]] = nodeValue;
                nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfos[i]);
                if (nodeInfo.table == IMLibLocalContext.contextName) {
                    IMLibLocalContext.setValue(nodeInfo.field, nodeValue);
                }
            }
        }
    },

    updateAll: function (isStore) {
        var index, key, nodeIds, idValue, targetNode;
        for (key in IMLibLocalContext.binding) {
            nodeIds = IMLibLocalContext.binding[key];
            for (index = 0; index < nodeIds.length; index++) {
                idValue = nodeIds[index];
                targetNode = document.getElementById(idValue);
                if (targetNode &&
                    ( targetNode.tagName == 'INPUT' || targetNode.tagName == 'TEXTAREA' || targetNode.tagName == 'SELECT')) {
                    if (isStore === true) {
                        IMLibLocalContext.updateFromStore(idValue);
                    } else {
                        IMLibLocalContext.updateFromNodeValue(idValue);
                    }
                    break;
                }
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
                    self.bindingNode(node);
                    children = node.childNodes; // Check all child nodes.
                    if (children) {
                        for (i = 0; i < children.length; i++) {
                            seek(children[i]);
                        }
                    }
                } catch (ex) {
                    if (ex == '_im_requath_request_') {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex, 'EXCEPTION-31');
                    }
                }
            }
        }
    }
};
