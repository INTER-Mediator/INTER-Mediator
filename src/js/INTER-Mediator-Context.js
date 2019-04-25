/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

// JSHint support
/* global INTERMediator, INTERMediatorOnPage, IMLibMouseEventDispatch, IMLibUI, IMLibKeyDownEventDispatch,
 IMLibChangeEventDispatch, INTERMediatorLib, INTERMediator_DBAdapter, IMLibQueue, IMLibCalc, IMLibPageNavigation,
 IMLibEventResponder, IMLibElement, Parser, IMLib, INTERMediatorLog */
/* jshint -W083 */ // Function within a loop

/**
 * @fileoverview IMLibContextPool, IMLibContext and IMLibLocalContext classes are defined here.
 */

/**
 *
 * @constructor
 */
var IMLibContext = function (contextName) {
    'use strict'
    this.contextName = contextName // Context Name, set on initialization.
    this.tableName = null
    this.viewName = null
    this.sourceName = null
    this.contextDefinition = null // Context Definition object, set on initialization.
    this.store = {}
    this.binding = {}
    this.contextInfo = {}
    this.modified = {}
    this.recordOrder = []
    this.pendingOrder = []
    IMLibContextPool.registerContext(this)

    this.foreignValue = {}
    this.enclosureNode = null // Set on initialization.
    this.repeaterNodes = null // Set on initialization.
    this.dependingObject = []
    this.original = null // Set on initialization.
    this.nullAcceptable = true
    this.parentContext = null
    this.registeredId = null
    this.sequencing = false // Set true on initialization.
    this.dependingParentObjectInfo = null
    this.isPortal = false
    this.potalContainingRecordKV = null

    /*
     * Initialize this object
     */
    this.setTable(this)
}

IMLibContext.prototype.updateFieldValue = async function (idValue, succeedProc, errorProc, warnMultipleRecProc, warnOthersModifyProc) {
    'use strict'
    var nodeInfo, contextInfo, linkInfo, changedObj, criteria, newValue

    changedObj = document.getElementById(idValue)
    linkInfo = INTERMediatorLib.getLinkedElementInfo(changedObj)
    nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfo[0]) // Suppose to be the first definition.
    contextInfo = IMLibContextPool.getContextInfoFromId(idValue, nodeInfo.target) // suppose to target = ''

    if (INTERMediator.ignoreOptimisticLocking) {
        IMLibContextPool.updateContext(idValue, nodeInfo.target)
        newValue = IMLibElement.getValueFromIMNode(changedObj)
        if (newValue !== null) {
            criteria = contextInfo.record.split('=')
            // await INTERMediatorOnPage.retrieveAuthInfo()
            if (contextInfo.context.isPortal) {
                criteria = contextInfo.context.potalContainingRecordKV.split('=')
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
                )
            } else {
                criteria = contextInfo.record.split('=')
                INTERMediator_DBAdapter.db_update_async(
                    {
                        name: contextInfo.context.contextName,
                        conditions: [{field: criteria[0], operator: '=', value: criteria[1]}],
                        dataset: [{field: contextInfo.field, value: newValue}]
                    },
                    succeedProc,
                    errorProc
                )
            }
        }
    } else {
        var targetContext = contextInfo.context
        var parentContext, keyingComp
        if (targetContext.isPortal === true) {
            parentContext = IMLibContextPool.getContextFromName(targetContext.sourceName)[0]
        } else {
            parentContext = targetContext.parentContext
        }
        var targetField = contextInfo.field
        if (targetContext.isPortal === true) {
            keyingComp = Object.keys(parentContext.store)[0].split('=')
        } else {
            keyingComp = (targetContext.isPortal ? targetContext.potalContainingRecordKV : contextInfo.record).split('=')
        }
        var keyingField = keyingComp[0]
        keyingComp.shift()
        var keyingValue = keyingComp.join('=')
        await INTERMediator_DBAdapter.db_query_async(
            {
                name: targetContext.isPortal ? parentContext.contextName : targetContext.contextName,
                records: 1,
                paging: false,
                fields: [contextInfo.field],
                parentkeyvalue: null,
                conditions: [
                    {field: keyingField, operator: '=', value: keyingValue}
                ],
                useoffset: false,
                primaryKeyOnly: true
            },
            (function () {
                var targetFieldCapt = targetField
                var contextInfoCapt = contextInfo
                var targetContextCapt = targetContext
                var changedObjectCapt = changedObj
                var nodeInfoCapt = nodeInfo
                var idValueCapt = idValue
                return function (result) {
                    var recordset = []
                    var initialvalue, newValue, isOthersModified, currentFieldVal,
                        portalRecords, index, keyField, keyingComp, criteria
                    if (targetContextCapt.isPortal) {
                        portalRecords = targetContextCapt.getPortalRecordsetImpl(
                            result.dbresult[0],
                            targetContextCapt.contextName)
                        keyField = targetContextCapt.getKeyField()
                        keyingComp = contextInfoCapt.record.split('=')
                        for (index = 0; index < portalRecords.length; index++) {
                            if (portalRecords[index][keyField] === keyingComp[1]) {
                                recordset.push(portalRecords[index])
                                break
                            }
                        }
                    } else {
                        recordset = result.dbresult
                    }
                    if (!recordset || !recordset[0] || // This value could be null or undefined
                        recordset[0][targetFieldCapt] === undefined) {
                        errorProc()
                        return
                    }
                    if (result.resultCount > 1) {
                        if (!warnMultipleRecProc()) {
                            return
                        }
                    }
                    if (targetContextCapt.isPortal) {
                        for (var i = 0; i < recordset.length; i += 1) {
                            if (recordset[i][INTERMediatorOnPage.defaultKeyName] === contextInfo.record.split('=')[1]) {
                                currentFieldVal = recordset[i][targetFieldCapt]
                                break
                            }
                        }
                        initialvalue = targetContextCapt.getValue(
                            Object.keys(parentContext.store)[0],
                            targetFieldCapt,
                            INTERMediatorOnPage.defaultKeyName + '=' + recordset[i][INTERMediatorOnPage.defaultKeyName]
                        )
                    } else {
                        currentFieldVal = recordset[0][targetFieldCapt]
                        initialvalue = targetContextCapt.getValue(contextInfoCapt.record, targetFieldCapt)
                    }
                    if (INTERMediatorOnPage.dbClassName === 'FileMaker_DataAPI') {
                        if (typeof (initialvalue) === 'number' && typeof (currentFieldVal) === 'string') {
                            initialvalue = initialvalue.toString()
                        }
                    }
                    isOthersModified = checkSameValue(initialvalue, currentFieldVal)
                    if (changedObjectCapt.tagName === 'INPUT' &&
                        changedObjectCapt.getAttribute('type') === 'checkbox') {
                        if (initialvalue === changedObjectCapt.value) {
                            isOthersModified = false
                        } else if (!parseInt(currentFieldVal)) {
                            isOthersModified = false
                        } else {
                            isOthersModified = true
                        }
                    }
                    if (isOthersModified) {
                        // The value of database and the field is different. Others must be changed this field.
                        newValue = IMLibElement.getValueFromIMNode(changedObjectCapt)
                        if (!warnOthersModifyProc(initialvalue, newValue, currentFieldVal)) {
                            return
                        }
                        // await INTERMediatorOnPage.retrieveAuthInfo() // This is required. Why?
                    }
                    IMLibContextPool.updateContext(idValueCapt, nodeInfoCapt.target)
                    newValue = IMLibElement.getValueFromIMNode(changedObjectCapt)
                    if (newValue !== null) {
                        if (targetContextCapt.isPortal) {
                            if (targetContextCapt.potalContainingRecordKV == null) {
                                criteria = Object.keys(targetContextCapt.foreignValue)
                                criteria[1] = targetContextCapt.foreignValue[criteria[0]]
                            } else {
                                criteria = targetContextCapt.potalContainingRecordKV.split('=')
                            }
                            INTERMediator_DBAdapter.db_update_async(
                                {
                                    name: targetContextCapt.isPortal ? targetContextCapt.sourceName : targetContextCapt.parentContext.contextName,
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
                            )
                        } else {
                            criteria = contextInfoCapt.record.split('=')
                            INTERMediator_DBAdapter.db_update_async(
                                {
                                    name: targetContextCapt.contextName,
                                    conditions: [{field: criteria[0], operator: '=', value: criteria[1]}],
                                    dataset: [{field: contextInfo.field, value: newValue}]
                                },
                                succeedProc,
                                errorProc
                            )
                        }
                    }
                }
            })(),
            function () {
                INTERMediatorOnPage.hideProgress()
                INTERMediatorLog.setErrorMessage('Error in valueChange method.', 'EXCEPTION-1')
            }
        )
    }

    function checkSameValue(initialValue, currentFieldVal) {
        var handleAsNullValue = ['0000-00-00', '0000-00-00 00:00:00']
        if (handleAsNullValue.indexOf(initialValue) >= 0) {
            initialValue = ''
        }
        if (handleAsNullValue.indexOf(currentFieldVal) >= 0) {
            currentFieldVal = ''
        }
        return initialValue !== currentFieldVal
    }
}

IMLibContext.prototype.getKeyField = function () {
    'use strict'
    var keyField
    if (INTERMediatorOnPage.dbClassName === 'FileMaker_FX' ||
        INTERMediatorOnPage.dbClassName === 'FileMaker_DataAPI') {
        if (this.isPortal) {
            keyField = INTERMediatorOnPage.defaultKeyName
        } else {
            keyField = this.contextDefinition.key ? this.contextDefinition.key : INTERMediatorOnPage.defaultKeyName
        }
    } else {
        keyField = this.contextDefinition.key ? this.contextDefinition.key : 'id'
    }
    return keyField
}

IMLibContext.prototype.getCalculationFields = function () {
    'use strict'
    var calcDef = this.contextDefinition.calculation
    var calcFields = []
    let ix
    for (ix in calcDef) {
        if (calcDef.hasOwnProperty(ix)) {
            calcFields.push(calcDef[ix].field)
        }
    }
    return calcFields
}

IMLibContext.prototype.isUseLimit = function () {
    'use strict'
    var useLimit = false
    if (this.contextDefinition.records && this.contextDefinition.paging) {
        useLimit = true
    }
    return useLimit
}

IMLibContext.prototype.getPortalRecords = function () {
    'use strict'
    var targetRecords = {}
    if (!this.isPortal) {
        return null
    }
    if (this.contextDefinition && this.contextDefinition.currentrecord) {
        targetRecords.recordset = this.getPortalRecordsetImpl(
            this.contextDefinition.currentrecord, this.contextName)
    } else {
        targetRecords.recordset = this.getPortalRecordsetImpl(
            this.parentContext.store[this.potalContainingRecordKV], this.contextName)
    }
    return targetRecords
}

IMLibContext.prototype.getPortalRecordsetImpl = function (store, contextName) {
    'use strict'
    var result, recId, recordset, key, contextDef
    recordset = []
    if (store[0]) {
        if (!store[0][contextName]) {
            for (key in store[0]) {
                if (store[0].hasOwnProperty(key)) {
                    contextDef = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', key)
                    if (contextName === contextDef.view && !store[0][contextName]) {
                        contextName = key
                        break
                    }
                }
            }
        }
        if (store[0][contextName]) {
            result = store[0][contextName]
            for (recId in result) {
                if (result.hasOwnProperty(recId) && isFinite(recId)) {
                    recordset.push(result[recId])
                }
            }
        }
    }
    return recordset
}

IMLibContext.prototype.getRecordNumber = function () {
    'use strict'
    var recordNumber, key, value, keyParams

    if (this.contextDefinition['navi-control'] &&
        this.contextDefinition['navi-control'] === 'detail') {
        recordNumber = 1
    } else {
        // The number of records is the records keyed value.
        if(this.contextDefinition.records) {
            recordNumber = parseInt(this.contextDefinition.records)
        }
        // From INTERMediator.recordLimit property
        // for (key in INTERMediator.recordLimit) {
        //     if (INTERMediator.recordLimit.hasOwnProperty(key)) {
        //         value = String(INTERMediator.recordLimit[key])
        //         if (key === this.contextDefinition.name &&
        //             value.length > 0) {
        //             recordNumber = parseInt(value)
        //             INTERMediator.setLocalProperty('_im_pagedSize', recordNumber)
        //         }
        //     }
        // }
        // From INTERMediator.pagedSize
        if (parseInt(INTERMediator.pagedSize) > 0 &&
          this.contextDefinition.paging &&
          Boolean(this.contextDefinition.paging) === true) {
            recordNumber = parseInt(INTERMediator.pagedSize)
            INTERMediator.setLocalProperty('_im_pagedSize', recordNumber)
        }
        // From Local context's limitnumber directive
        for (key in IMLibLocalContext.store) {
            if (IMLibLocalContext.store.hasOwnProperty(key)) {
                value = String(IMLibLocalContext.store[key])
                keyParams = key.split(':')
                if (keyParams &&
                    keyParams.length > 1 &&
                    keyParams[1].trim() === this.contextDefinition.name &&
                    value.length > 0 &&
                    keyParams[0].trim() === 'limitnumber') {
                    recordNumber = parseInt(value)
                    INTERMediator.setLocalProperty('_im_pagedSize', recordNumber)
                }
            }
        }
        // In case of paginating context, set INTERMediator.pagedSize property.
        if (!this.contextDefinition.relation &&
            this.contextDefinition.paging &&
            Boolean(this.contextDefinition.paging) === true) {
            INTERMediator.setLocalProperty('_im_pagedSize', recordNumber)
            INTERMediator.pagedSize = recordNumber
        }
    }
    return recordNumber
}

IMLibContext.prototype.setRelationWithParent = function (currentRecord, parentObjectInfo, parentContext) {
    'use strict'
    var relationDef, index, joinField, fieldName, i

    this.parentContext = parentContext

    if (currentRecord) {
        try {
            relationDef = this.contextDefinition.relation
            if (relationDef) {
                for (index in relationDef) {
                    if (relationDef.hasOwnProperty(index)) {
                        if (Boolean(relationDef[index].portal) === true) {
                            this.isPortal = true
                            this.potalContainingRecordKV = INTERMediatorOnPage.defaultKeyName + '=' +
                                currentRecord[INTERMediatorOnPage.defaultKeyName]
                        }
                        joinField = relationDef[index]['join-field']
                        this.addForeignValue(joinField, currentRecord[joinField])
                        for (fieldName in parentObjectInfo) {
                            if (fieldName === relationDef[index]['join-field']) {
                                for (i = 0; i < parentObjectInfo[fieldName].length; i += 1) {
                                    this.addDependingObject(parentObjectInfo[fieldName][i])
                                }
                                this.dependingParentObjectInfo =
                                    JSON.parse(JSON.stringify(parentObjectInfo))
                            }
                        }
                    }
                }
            }
        } catch (ex) {
            if (ex.message === '_im_auth_required_') {
                throw ex
            } else {
                INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-25')
            }
        }
    }
}

IMLibContext.prototype.getInsertOrder = function (/* record */) {
    'use strict'
    var cName
    let sortKeys = []
    let contextDef, i
    let sortFields = []
    let sortDirections = []
    for (cName in INTERMediator.additionalSortKey) {
        if (cName === this.contextName) {
            sortKeys.push(INTERMediator.additionalSortKey[cName])
        }
    }
    contextDef = this.getContextDef()
    if (contextDef.sort) {
        sortKeys.push(contextDef.sort)
    }
    for (i = 0; i < sortKeys.length; i += 1) {
        if (sortFields.indexOf(sortKeys[i].field) < 0) {
            sortFields.push(sortKeys[i].field)
            sortDirections.push(sortKeys[i].direction)
        }
    }
}

IMLibContext.prototype.indexingArray = function (keyField) {
    'use strict'
    var ar = []
    let key
    let counter = 0
    for (key in this.store) {
        if (this.store.hasOwnProperty(key)) {
            ar[counter] = this.store[key][keyField]
            counter += 1
        }
    }
    return ar
}

IMLibContext.prototype.clearAll = function () {
    'use strict'
    this.store = {}
    this.binding = {}
}

IMLibContext.prototype.setContextName = function (name) {
    'use strict'
    this.contextName = name
}

IMLibContext.prototype.getContextDef = function () {
    'use strict'
    return INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', this.contextName)
}

IMLibContext.prototype.setTableName = function (name) {
    'use strict'
    this.tableName = name
}

IMLibContext.prototype.setViewName = function (name) {
    'use strict'
    this.viewName = name
}

IMLibContext.prototype.addDependingObject = function (idNumber) {
    'use strict'
    this.dependingObject.push(idNumber)
}

IMLibContext.prototype.addForeignValue = function (field, value) {
    'use strict'
    this.foreignValue[field] = value
}

IMLibContext.prototype.setOriginal = function (repeaters) {
    'use strict'
    var i
    this.original = []
    for (i = 0; i < repeaters.length; i += 1) {
        this.original.push(repeaters[i].cloneNode(true))
    }
}

IMLibContext.prototype.setTable = function (context) {
    'use strict'
    var contextDef
    if (!context || !INTERMediatorOnPage.getDataSources) {
        this.tableName = this.contextName
        this.viewName = this.contextName
        this.sourceName = this.contextName
        // This is not a valid case, it just prevent the error in the unit test.
        return
    }
    contextDef = this.getContextDef()
    if (contextDef) {
        this.viewName = contextDef.view ? contextDef.view : contextDef.name
        this.tableName = contextDef.table ? contextDef.table : contextDef.name
        this.sourceName = (contextDef.source ? contextDef.source
            : (contextDef.table ? contextDef.table
                : (contextDef.view ? contextDef.view : contextDef.name)))
    }
}

IMLibContext.prototype.removeContext = function () {
    'use strict'
    var regIds = []
    let childContexts = []
    seekRemovingContext(this)
    regIds = IMLibContextPool.removeContextsFromPool(childContexts)
    while (this.enclosureNode.firstChild) {
        this.enclosureNode.removeChild(this.enclosureNode.firstChild)
    }
    INTERMediator_DBAdapter.unregister(regIds)

    function seekRemovingContext(context) {
        var i, myChildren
        childContexts.push(context)
        regIds.push(context.registeredId)
        myChildren = IMLibContextPool.getChildContexts(context)
        for (i = 0; i < myChildren.length; i += 1) {
            seekRemovingContext(myChildren[i])
        }
    }
}

IMLibContext.prototype.setModified = function (recKey, key, value) {
    'use strict'
    if (this.modified[recKey] === undefined) {
        this.modified[recKey] = {}
    }
    this.modified[recKey][key] = value
}

IMLibContext.prototype.getModified = function () {
    'use strict'
    return this.modified
}

IMLibContext.prototype.clearModified = function () {
    'use strict'
    this.modified = {}
}

IMLibContext.prototype.getContextDef = function () {
    'use strict'
    var contextDef
    contextDef = INTERMediatorLib.getNamedObject(
        INTERMediatorOnPage.getDataSources(), 'name', this.contextName)
    return contextDef
}

/*
 * The isDebug parameter is for debugging and testing. Usually you should not specify it.
 */
IMLibContext.prototype.checkOrder = function (oneRecord, isDebug) {
    'use strict'
    var i
    let fields = []
    let directions = []
    let oneSortKey, condtextDef, lower, upper, index, targetRecord, contextValue, checkingValue, stop
    if (isDebug !== true) {
        if (INTERMediator && INTERMediator.additionalSortKey[this.contextName]) {
            for (i = 0; i < INTERMediator.additionalSortKey[this.contextName].length; i += 1) {
                oneSortKey = INTERMediator.additionalSortKey[this.contextName][i]
                if (!(oneSortKey.field in fields)) {
                    fields.push(oneSortKey.field)
                    directions.push(oneSortKey.direction)
                }
            }
        }
        condtextDef = this.getContextDef()
        if (condtextDef && condtextDef.sort) {
            for (i = 0; i < condtextDef.sort.length; i += 1) {
                oneSortKey = condtextDef.sort[i]
                if (!(oneSortKey.field in fields)) {
                    fields.push(oneSortKey.field)
                    directions.push(oneSortKey.direction)
                }
            }
        }
    } else {
        fields = ['field1', 'field2']
    }
    lower = 0
    upper = this.recordOrder.length
    for (i = 0; i < fields.length; i += 1) {
        if (oneRecord[fields[i]]) {
            index = parseInt((upper + lower) / 2)
            do {
                targetRecord = this.store[this.recordOrder[index]]
                contextValue = targetRecord[fields[i]]
                checkingValue = oneRecord[fields[i]]
                if (contextValue < checkingValue) {
                    lower = index
                } else if (contextValue > checkingValue) {
                    upper = index
                } else {
                    lower = upper = index
                }
                index = parseInt((upper + lower) / 2)
            } while (upper - lower > 1)
            targetRecord = this.store[this.recordOrder[index]]
            contextValue = targetRecord[fields[i]]
            if (contextValue === checkingValue) {
                lower = upper = index
                stop = false
                do {
                    targetRecord = this.store[this.recordOrder[lower - 1]]
                    if (targetRecord && targetRecord[fields[i]] && targetRecord[fields[i]] === checkingValue) {
                        lower--
                    } else {
                        stop = true
                    }
                } while (!stop)
                stop = false
                do {
                    targetRecord = this.store[this.recordOrder[upper + 1]]
                    if (targetRecord && targetRecord[fields[i]] && targetRecord[fields[i]] === checkingValue) {
                        upper++
                    } else {
                        stop = true
                    }
                } while (!stop)
                if (lower === upper) {
                    // index is the valid order number.
                    break
                }
                upper++
            } else if (contextValue < checkingValue) {
                // index is the valid order number.
                break
            } else if (contextValue > checkingValue) {
                index--
                break
            }
        }
    }
    // if (isDebug === true) {
    //     console.log('#lower=' + lower + ',upper=' + upper + ',index=' + index +
    //         ',contextValue=' + contextValue + ',checkingValue=' + checkingValue)
    // }
    return index
}

/*
 * The isDebug parameter is for debugging and testing. Usually you should not specify it.
 */
IMLibContext.prototype.rearrangePendingOrder = function (isDebug) {
    'use strict'
    var i, index, targetRecord
    for (i = 0; i < this.pendingOrder.length; i += 1) {
        targetRecord = this.store[this.pendingOrder[i]]
        index = this.checkOrder(targetRecord, isDebug)
        if (index >= -1) {
            this.recordOrder.splice(index + 1, 0, this.pendingOrder[i])
        }
    }
    this.pendingOrder = []
}

IMLibContext.prototype.getRepeaterEndNode = function (index) {
    'use strict'
    var nodeId, field
    let repeaters = []
    let repeater, node, i, enclosure, children

    var recKey = this.recordOrder[index]
    for (field in this.binding[recKey]) {
        if (this.binding[recKey].hasOwnProperty(field)) {
            nodeId = this.binding[recKey][field].nodeId
            repeater = INTERMediatorLib.getParentRepeaters(document.getElementById(nodeId))
            for (i = 0; i < repeater.length; i += 1) {
                if (!(repeater[i] in repeaters)) {
                    repeaters.push(repeater[i])
                }
            }
        }
    }
    if (repeaters.length < 1) {
        return null
    }
    node = repeaters[0]
    enclosure = INTERMediatorLib.getParentEnclosure(node)
    children = enclosure.childNodes
    for (i = 0; i < children.length; i += 1) {
        if (children[i] in repeaters) {
            node = repeaters[i]
            break
        }
    }
    return node
}

IMLibContext.prototype.storeRecords = function (records) {
    'use strict'
    var ix, record, field, keyField, keyValue
    var contextDef = INTERMediatorLib.getNamedObject(
        INTERMediatorOnPage.getDataSources(), 'name', this.contextName)
    keyField = contextDef.key ? contextDef.key : 'id'
    if (records.dbresult) {
        for (ix = 0; ix < records.dbresult.length; ix++) {
            record = records.dbresult[ix]
            for (field in record) {
                if (record.hasOwnProperty(field)) {
                    keyValue = record[keyField] ? record[keyField] : ix
                    this.setValue(keyField + '=' + keyValue, field, record[field])
                }
            }
        }
    }
}

IMLibContext.prototype.getDataAtLastRecord = function (key) {
    'use strict'
    var lastKey
    var storekeys = Object.keys(this.store)
    if (storekeys.length > 0) {
        lastKey = storekeys[storekeys.length - 1]
        return this.getValue(lastKey, key)
    }
    return undefined
}

// setData____ methods are for storing data both the model and the database.
//
IMLibContext.prototype.setDataAtLastRecord = function (key, value) {
    'use strict'
    var lastKey, keyAndValue, contextName
    var storekeys = Object.keys(this.store)
    if (storekeys.length > 0) {
        lastKey = storekeys[storekeys.length - 1]
        this.setValue(lastKey, key, value)
        contextName = this.contextName
        keyAndValue = lastKey.split('=')
        IMLibQueue.setTask((function () {
            var params = {
                name: contextName,
                conditions: [{field: keyAndValue[0], operator: '=', value: keyAndValue[1]}],
                dataset: [{field: key, value: value}]
            }
            return function (completeTask) {
                INTERMediator_DBAdapter.db_update_async(
                    params,
                    function () {
                        IMLibCalc.recalculation()
                        INTERMediatorLog.flushMessage()
                        completeTask()
                    },
                    function () {
                        INTERMediatorLog.flushMessage()
                        completeTask()
                    }
                )
            }
        })())
    }
}

IMLibContext.prototype.setDataWithKey = function (pkValue, key, value) {
    'use strict'
    var targetKey, contextDef, storeElements, contextName
    contextDef = this.getContextDef()
    if (!contextDef) {
        return
    }
    targetKey = contextDef.key + '=' + pkValue
    storeElements = this.store[targetKey]
    if (storeElements) {
        this.setValue(targetKey, key, value)
        contextName = this.contextName
        IMLibQueue.setTask((function () {
            var params = {
                name: contextName,
                conditions: [{field: contextDef.key, operator: '=', value: pkValue}],
                dataset: [{field: key, value: value}]
            }
            return function (completeTask) {
                INTERMediator_DBAdapter.db_update_async(
                    params,
                    (result) => {
                        INTERMediatorLog.flushMessage()
                        completeTask()
                    },
                    () => {
                        INTERMediatorLog.flushMessage()
                        completeTask()
                    }
                )
            }
        })())
    }
}

IMLibContext.prototype.setValue = function (recKey, key, value, nodeId, target, portal) {
    'use strict'
    var updatedNodeIds = null
    if (portal) {
        /* eslint no-console: ["error", {allow: ["error"]}] */
        console.error('Using the portal parameter in IMLibContext.setValue')
    }
    if (recKey) {
        if (this.store[recKey] === undefined) {
            this.store[recKey] = {}
        }
        if (portal && this.store[recKey][key] === undefined) {
            this.store[recKey][key] = {}
        }
        if (this.binding[recKey] === undefined) {
            this.binding[recKey] = {}
            if (this.sequencing) {
                this.recordOrder.push(recKey)
            } else {
                this.pendingOrder.push(recKey)
            }
        }
        if (this.binding[recKey][key] === undefined) {
            this.binding[recKey][key] = []
        }
        if (portal && this.binding[recKey][key][portal] === undefined) {
            if (this.binding[recKey][key].length < 1) {
                this.binding[recKey][key] = {}
            }
            this.binding[recKey][key][portal] = []
        }
        if (key) {
            if (portal) {
                // this.store[recKey][key][portal] = value
                this.store[recKey][key] = value
            } else {
                this.store[recKey][key] = value
            }
            if (nodeId) {
                if (portal) {
                    // this.binding[recKey][key][portal].push({id: nodeId, target: target})
                    this.binding[recKey][key].push({id: nodeId, target: target})
                } else {
                    this.binding[recKey][key].push({id: nodeId, target: target})
                }
                if (this.contextInfo[nodeId] === undefined) {
                    this.contextInfo[nodeId] = {}
                }
                this.contextInfo[nodeId][target ? target : '_im_no_target'] =
                    {context: this, record: recKey, field: key}
                if (portal) {
                    this.contextInfo[nodeId][target ? target : '_im_no_target'].portal = portal
                }
            } else {
                if (INTERMediator.partialConstructing) {
                    updatedNodeIds = IMLibContextPool.synchronize(this, recKey, key, value, target, portal)
                }
            }
        }
    }
    return updatedNodeIds
}

IMLibContext.prototype.getValue = function (recKey, key, portal) {
    'use strict'
    var value
    try {
        if (portal) {
            value = this.store[portal][key]
        } else {
            value = this.store[recKey][key]
        }
        if (Array.isArray(value)) {
            value = value.join()
        }
        return value === undefined ? null : value
    } catch (ex) {
        return null
    }
}

IMLibContext.prototype.isValueUndefined = function (recKey, key, portal) {
    'use strict'
    var value, tableOccurence, relatedRecId
    try {
        if (portal) {
            tableOccurence = key.split('::')[0]
            relatedRecId = portal.split('=')[1]
            value = this.store[recKey][0][tableOccurence][relatedRecId][key]
        } else {
            value = this.store[recKey][key]
        }
        return value === undefined ? true : false
    } catch (ex) {
        return null
    }
}

IMLibContext.prototype.getContextInfo = function (nodeId, target) {
    'use strict'
    try {
        var info = this.contextInfo[nodeId][target ? target : '_im_no_target']
        return info === undefined ? null : info
    } catch (ex) {
        return null
    }
}

IMLibContext.prototype.getContextValue = function (nodeId, target) {
    'use strict'
    try {
        var info = this.contextInfo[nodeId][target ? target : '_im_no_target']
        var value = info.context.getValue(info.record, info.field)
        return value === undefined ? null : value
    } catch (ex) {
        return null
    }
}

IMLibContext.prototype.getContextRecord = function (nodeId) {
    'use strict'
    var infos, keys, i
    try {
        infos = this.contextInfo[nodeId]
        keys = Object.keys(infos)
        for (i = 0; i < keys.length; i += 1) {
            if (infos[keys[i]]) {
                return this.store[infos[keys[i]].record]
            }
        }
        return null
    } catch (ex) {
        return null
    }
}

IMLibContext.prototype.removeEntry = function (pkvalue) {
    'use strict'
    var keyField, keying, bindingInfo, contextDef, targetNode, repeaterNodes, i
    let removingNodeIds = []
    contextDef = this.getContextDef()
    keyField = contextDef.key
    keying = keyField + '=' + pkvalue
    bindingInfo = this.binding[keying]
    if (bindingInfo) {
        repeaterNodes = bindingInfo._im_repeater
        if (repeaterNodes) {
            for (i = 0; i < repeaterNodes.length; i += 1) {
                removingNodeIds.push(repeaterNodes[i].id)
            }
        }
    }
    if (removingNodeIds.length > 0) {
        for (i = 0; i < removingNodeIds.length; i += 1) {
            IMLibContextPool.removeRecordFromPool(removingNodeIds[i])
        }
        for (i = 0; i < removingNodeIds.length; i += 1) {
            targetNode = document.getElementById(removingNodeIds[i])
            if (targetNode) {
                targetNode.parentNode.removeChild(targetNode)
            }
        }
    }
}

IMLibContext.prototype.isContaining = function (value) {
    'use strict'
    var contextDef, contextName
    let checkResult = []
    let i, fieldName, result, opePosition, leftHand, rightHand, leftResult, rightResult

    contextDef = this.getContextDef()
    contextName = contextDef.name
    if (contextDef.query) {
        for (i in contextDef.query) {
            if (contextDef.query.hasOwnProperty(i)) {
                checkResult.push(checkCondition(contextDef.query[i], value))
            }
        }
    }
    if (INTERMediator.additionalCondition[contextName]) {
        for (i = 0; i < INTERMediator.additionalCondition[contextName].length; i += 1) {
            checkResult.push(checkCondition(INTERMediator.additionalCondition[contextName][i], value))
        }
    }

    result = true
    if (checkResult.length !== 0) {
        opePosition = checkResult.indexOf('D')
        if (opePosition > -1) {
            leftHand = checkResult.slice(0, opePosition)
            rightHand = opePosition.slice(opePosition + 1)
            if (rightHand.length === 0) {
                result = (leftHand.indexOf(false) < 0)
            } else {
                leftResult = (leftHand.indexOf(false) < 0)
                rightResult = (rightHand.indexOf(false) < 0)
                result = leftResult || rightResult
            }
        } else {
            opePosition = checkResult.indexOf('EX')
            if (opePosition > -1) {
                leftHand = checkResult.slice(0, opePosition)
                rightHand = opePosition.slice(opePosition + 1)
                if (rightHand.length === 0) {
                    result = (leftHand.indexOf(true) > -1)
                } else {
                    leftResult = (leftHand.indexOf(true) > -1)
                    rightResult = (rightHand.indexOf(true) > -1)
                    result = leftResult && rightResult
                }
            } else {
                opePosition = checkResult.indexOf(false)
                if (opePosition > -1) {
                    result = (checkResult.indexOf(false) < 0)
                }
            }
        }

        if (result === false) {
            return false
        }
    }

    if (this.foreignValue) {
        for (fieldName in this.foreignValue) {
            if (contextDef.relation) {
                for (i in contextDef.relation) {
                    if (contextDef.relation[i]['join-field'] === fieldName) {
                        result &= (checkCondition({
                            field: contextDef.relation[i]['foreign-key'],
                            operator: '=',
                            value: this.foreignValue[fieldName]
                        }, value))
                    }
                }
            }
        }
    }

    return result

    function checkCondition(conditionDef, oneRecord) {
        var realValue

        if (conditionDef.field === '__operation__') {
            return conditionDef.operator === 'ex' ? 'EX' : 'D'
        }

        realValue = oneRecord[conditionDef.field]
        if (!realValue) {
            return false
        }
        switch (conditionDef.operator) {
            case '=':
            case 'eq':
                return String(realValue) === String(conditionDef.value)
            case '>':
            case 'gt':
                return realValue > conditionDef.value
            case '<':
            case 'lt':
                return realValue < conditionDef.value
            case '>=':
            case 'gte':
                return realValue >= conditionDef.value
            case '<=':
            case 'lte':
                return realValue <= conditionDef.value
            case '!=':
            case 'neq':
                return String(realValue) !== String(conditionDef.value)
            default:
                return false
        }
    }
}

IMLibContext.prototype.insertEntry = function (pkvalue, fields, values) {
    'use strict'
    var i, field, value
    for (i = 0; i < fields.length; i += 1) {
        field = fields[i]
        value = values[i]
        this.setValue(pkvalue, field, value)
    }
}

// @@IM@@IgnoringRestOfFile
module.exports = IMLibContext
const IMLibContextPool = require('../../src/js/INTER-Mediator-ContextPool')
const INTERMediatorOnPage = require('../../src/js/INTER-Mediator-Page')
const INTERMediator = require('../../src/js/INTER-Mediator')
