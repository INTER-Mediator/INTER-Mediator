/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (https://inter-mediator.org)
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
class IMLibContext {
  constructor(contextName) {
    this.contextName = contextName // Context Name, set on initialization.
    this.tableName = null
    this.viewName = null
    this.sourceName = null
    this.contextDefinition = null // Context Definition object, set on initialization.
    this.store = {}
    this.storeCaptured = null
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
    this.lookingUp = {}
    this.lookingUpInfo = null
    this.original = null // Set on initialization.
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

  async updateFieldValue(idValue, succeedProc, errorProc, warnMultipleRecProc, warnOthersModifyProc) {
    'use strict'
    let criteria, newValue

    const changedObj = document.getElementById(idValue)
    const linkInfo = INTERMediatorLib.getLinkedElementInfo(changedObj)
    const nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfo[0]) // Suppose to be the first definition.
    const contextInfo = IMLibContextPool.getContextInfoFromId(idValue, nodeInfo.target) // suppose to target = ''

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
      let targetContext = contextInfo.context
      let parentContext, keyingComp
      if (targetContext.isPortal === true) {
        parentContext = IMLibContextPool.getContextFromName(targetContext.sourceName)[0]
      } else {
        parentContext = targetContext.parentContext
      }
      let targetField = contextInfo.field
      if (targetContext.isPortal === true) {
        keyingComp = Object.keys(parentContext.store)[0].split('=')
      } else {
        keyingComp = (targetContext.isPortal ? targetContext.potalContainingRecordKV : contextInfo.record).split('=')
      }
      let keyingField = keyingComp[0]
      keyingComp.shift()
      let keyingValue = keyingComp.join('=')
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
          let targetFieldCapt = targetField
          let contextInfoCapt = contextInfo
          let targetContextCapt = targetContext
          let changedObjectCapt = changedObj
          let nodeInfoCapt = nodeInfo
          let idValueCapt = idValue
          return function (result) {
            let recordset = []
            let initialvalue, newValue, isOthersModified, currentFieldVal,
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
              typeof (recordset[0][targetFieldCapt]) === 'undefined') {
              errorProc()
              return
            }
            if (result.dbresult.length > 1) { // the resultCount property returns 'view' result and might be wrong.
              if (!warnMultipleRecProc()) {
                return
              }
            }
            if (targetContextCapt.isPortal) {
              for (let i = 0; i < recordset.length; i += 1) {
                if (recordset[i][INTERMediatorOnPage.defaultKeyName] === contextInfo.record.split('=')[1]) {
                  currentFieldVal = recordset[i][targetFieldCapt]
                  break
                }
              }
              initialvalue = targetContextCapt.getValue(
                Object.keys(parentContext.store)[0],
                targetFieldCapt,
                INTERMediatorOnPage.defaultKeyName + '=' + recordset[0][INTERMediatorOnPage.defaultKeyName]
              )
            } else {
              currentFieldVal = recordset[0][targetFieldCapt]
              initialvalue = targetContextCapt.getValue(contextInfoCapt.record, targetFieldCapt)
            }
            if (INTERMediatorOnPage.dbClassName.match(/FileMaker_DataAPI/)) {
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
                //} else {
                //isOthersModified = true
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
      let handleAsNullValue = ['0000-00-00', '0000-00-00 00:00:00']
      if (handleAsNullValue.indexOf(initialValue) >= 0) {
        initialValue = ''
      }
      if (handleAsNullValue.indexOf(currentFieldVal) >= 0) {
        currentFieldVal = ''
      }
      return initialValue !== currentFieldVal
    }
  }

  getKeyField() {
    'use strict'
    let keyField
    if (INTERMediatorOnPage.dbClassName.match(/FileMaker_FX/) ||
      INTERMediatorOnPage.dbClassName.match(/FileMaker_DataAPI/)) {
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

  getCalculationFields() {
    'use strict'
    let calcDef = this.contextDefinition.calculation, calcFields = []
    if (calcDef) {
      for (const key in calcDef) {
        calcFields.push(calcDef[key].field)
      }
    }
    return calcFields
  }

  isUseLimit() {
    'use strict'
    let useLimit = false
    if (this.contextDefinition.records && this.contextDefinition.paging) {
      useLimit = true
    }
    return useLimit
  }

  getPortalRecords() {
    'use strict'
    let targetRecords = {}
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

  getPortalRecordsetImpl(store, contextName) {
    'use strict'
    let result, recId, recordset = [], key, contextDef
    if (store[0]) {
      if (!store[0][contextName]) {
        for (key of store[0]) {
          contextDef = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', key)
          if (contextName === contextDef.view && !store[0][contextName]) {
            contextName = key
            break
          }
        }
      }
      if (store[0][contextName]) {
        result = store[0][contextName]
        for (recId of Object.keys(result)) {
          if (isFinite(recId)) {
            recordset.push(result[recId])
          }
        }
      }
    }
    return recordset
  }

  getRecordNumber() {
    'use strict'
    let recordNumber, key, value, keyParams

    if (this.contextDefinition['navi-control'] &&
      this.contextDefinition['navi-control'] === 'detail') {
      recordNumber = 1
    } else {
      // The number of records is the records keyed value.
      if (this.contextDefinition.records) {
        recordNumber = parseInt(this.contextDefinition.records)
      }
      if (parseInt(INTERMediator.pagedSize) > 0 &&
        this.contextDefinition.paging &&
        Boolean(this.contextDefinition.paging) === true) {
        recordNumber = parseInt(INTERMediator.pagedSize)
        INTERMediator.setLocalProperty('_im_pagedSize', recordNumber)
      }
      // From Local context's limitnumber directive
      for (key of Object.keys(IMLibLocalContext.store)) {
        value = String(IMLibLocalContext.store[key])
        keyParams = key.split(':')
        if (keyParams &&
          keyParams.length > 1 &&
          keyParams[1].trim() === this.contextDefinition.name &&
          value.length > 0 &&
          keyParams[0].trim() === 'limitnumber') {
          recordNumber = parseInt(value)
          if (value >= parseInt(this.contextDefinition.maxrecords)) {
            recordNumber = parseInt(this.contextDefinition.maxrecords)
          }
          INTERMediator.setLocalProperty('_im_pagedSize', recordNumber)
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

  setRelationWithParent(currentRecord, parentObjectInfo, parentContext) {
    'use strict'
    this.parentContext = parentContext
    if (currentRecord) {
      try {
        const relationDef = this.contextDefinition.relation
        if (relationDef) {
          for (const index of Object.keys(relationDef)) {
            if (Boolean(relationDef[index].portal) === true) {
              this.isPortal = true
              this.potalContainingRecordKV = INTERMediatorOnPage.defaultKeyName + '=' +
                currentRecord[INTERMediatorOnPage.defaultKeyName]
            }
            const joinField = relationDef[index]['join-field']
            this.addForeignValue(joinField, currentRecord[joinField])
            for (const fieldName in parentObjectInfo) {
              if (fieldName === relationDef[index]['join-field']) {
                for (let i = 0; i < parentObjectInfo[fieldName].length; i += 1) {
                  this.addDependingObject(parentObjectInfo[fieldName][i])
                }
                this.dependingParentObjectInfo =
                  JSON.parse(JSON.stringify(parentObjectInfo))
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

  getInsertOrder(/* record */) {
    'use strict'
    let contextDef
    let sortKeys = []
    let sortFields = []
    let sortDirections = []
    for (const cName in INTERMediator.additionalSortKey) {
      if (cName === this.contextName) {
        sortKeys.push(INTERMediator.additionalSortKey[cName])
      }
    }
    contextDef = this.getContextDef()
    if (contextDef.sort) {
      sortKeys.push(contextDef.sort)
    }
    for (let i = 0; i < sortKeys.length; i += 1) {
      if (sortFields.indexOf(sortKeys[i].field) < 0) {
        sortFields.push(sortKeys[i].field)
        sortDirections.push(sortKeys[i].direction)
      }
    }
  }

  indexingArray(keyField) {
    'use strict'
    let ar = []
    let counter = 0
    for (const key in this.store) {
      if (this.store.hasOwnProperty(key)) {
        ar[counter] = this.store[key][keyField]
        counter += 1
      }
    }
    return ar
  }

  clearAll() {
    'use strict'
    this.store = {}
    this.binding = {}
  }

  setContextName(name) {
    'use strict'
    this.contextName = name
  }

  getContextDef() {
    'use strict'
    return INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', this.contextName)
  }

  setTableName(name) {
    'use strict'
    this.tableName = name
  }

  setViewName(name) {
    'use strict'
    this.viewName = name
  }

  addDependingObject(idNumber) {
    'use strict'
    this.dependingObject.push(idNumber)
  }

  addForeignValue(field, value) {
    'use strict'
    this.foreignValue[field] = value
  }

  setOriginal(repeaters) {
    'use strict'
    this.original = []
    for (let i = 0; i < repeaters.length; i += 1) {
      this.original.push(repeaters[i].cloneNode(true))
    }
  }

  setTable(context) {
    'use strict'
    let contextDef
    if (!context || !INTERMediatorOnPage.getDataSources) {
      this.tableName = this.contextName
      this.viewName = this.contextName
      this.sourceName = this.contextName
      // This is not a valid case, it just prevents the error in the unit test.
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

  async removeContext() {
    'use strict'
    let regIds = []
    let childContexts = []
    seekRemovingContext(this)
    regIds = IMLibContextPool.removeContextsFromPool(childContexts)
    while (this.enclosureNode.firstChild) {
      this.enclosureNode.removeChild(this.enclosureNode.firstChild)
    }
    await INTERMediator_DBAdapter.unregister(regIds)

    function seekRemovingContext(context) {
      let myChildren
      childContexts.push(context)
      regIds.push(context.registeredId)
      myChildren = IMLibContextPool.getChildContexts(context)
      for (let i = 0; i < myChildren.length; i += 1) {
        seekRemovingContext(myChildren[i])
      }
    }
  }

  setModified(recKey, key, value) {
    'use strict'
    if (typeof (this.modified[recKey]) === 'undefined') {
      this.modified[recKey] = {}
    }
    this.modified[recKey][key] = value
  }

  getModified() {
    'use strict'
    return this.modified
  }

  clearModified() {
    'use strict'
    this.modified = {}
  }

  getContextDef() {
    'use strict'
    return INTERMediatorLib.getNamedObject(
      INTERMediatorOnPage.getDataSources(), 'name', this.contextName)
  }

  /*
   * The isDebug parameter is for debugging and testing. Usually you should not specify it.
   */
  checkOrder(oneRecord, isDebug) {
    'use strict'
    let fields = []
    let directions = []
    let oneSortKey, condtextDef, lower, upper, index, targetRecord, contextValue, checkingValue, stop
    if (isDebug !== true) {
      if (INTERMediator && INTERMediator.additionalSortKey[this.contextName]) {
        for (let i = 0; i < INTERMediator.additionalSortKey[this.contextName].length; i += 1) {
          oneSortKey = INTERMediator.additionalSortKey[this.contextName][i]
          if (!(oneSortKey.field in fields)) {
            fields.push(oneSortKey.field)
            directions.push(oneSortKey.direction)
          }
        }
      }
      condtextDef = this.getContextDef()
      if (condtextDef && condtextDef.sort) {
        for (let i = 0; i < condtextDef.sort.length; i += 1) {
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
    for (let i = 0; i < fields.length; i += 1) {
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
  rearrangePendingOrder(isDebug) {
    'use strict'
    let index, targetRecord
    for (let i = 0; i < this.pendingOrder.length; i += 1) {
      targetRecord = this.store[this.pendingOrder[i]]
      index = this.checkOrder(targetRecord, isDebug)
      if (index >= -1) {
        this.recordOrder.splice(index + 1, 0, this.pendingOrder[i])
      }
    }
    this.pendingOrder = []
  }

  getRepeaterEndNode(index) {
    'use strict'
    let nodeId, field
    let repeaters = []
    let repeater, node, i, enclosure, children

    let recKey = this.recordOrder[index]
    for (field of this.binding[recKey]) {
      nodeId = this.binding[recKey][field].nodeId
      repeater = INTERMediatorLib.getParentRepeaters(document.getElementById(nodeId))
      for (i = 0; i < repeater.length; i += 1) {
        if (!(repeater[i] in repeaters)) {
          repeaters.push(repeater[i])
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

  storeRecords(records) {
    'use strict'
    let record, field, keyField, keyValue
    let contextDef = INTERMediatorLib.getNamedObject(
      INTERMediatorOnPage.getDataSources(), 'name', this.contextName)
    keyField = contextDef.key ? contextDef.key : 'id'
    if (records.dbresult) {
      for (let ix = 0; ix < records.dbresult.length; ix++) {
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

  getDataAtLastRecord(key = false) {
    let lastKey
    let storekeys = Object.keys(this.store)
    if (storekeys.length > 0) {
      lastKey = storekeys[storekeys.length - 1]
      return this.getValue(lastKey, key)
    }
    return undefined
  }

// setData____ methods are for storing data both the model and the database.
//
  setDataAtLastRecord(key, value = false) {
    let lastKey, keyAndValue, contextName, dataset = []
    let storekeys = Object.keys(this.store)
    if (storekeys.length > 0) {
      lastKey = storekeys[storekeys.length - 1]
      keyAndValue = lastKey.split('=')
      this.setDataWithKey(keyAndValue[1], key, value)
    }
  }

  setDataWithKey(pkValue, key, value = false) {
    let contextDef = this.getContextDef()
    if (!contextDef) {
      return
    }
    let targetKey = contextDef.key + '=' + pkValue
    if (!this.store[targetKey]) {
      return
    }
    let dataset = []
    if (INTERMediatorLib.isObject(key) && value === false) {
      for (const field of Object.keys(key)) {
        dataset.push({field: field, value: key[field]})
        this.setValue(targetKey, field, key[field])
      }
    } else {
      dataset.push({field: key, value: value})
      this.setValue(targetKey, key, value)
    }
    let contextName = this.contextName
    IMLibQueue.setTask((function () {
      let params = {
        name: contextName,
        conditions: [{field: contextDef.key, operator: '=', value: pkValue}],
        dataset: dataset
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

  setValue(recKey, key, value, nodeId, target, portal) {
    'use strict'
    let updatedNodeIds = null
    if (portal) {
      /* eslint no-console: ["error", {allow: ["error"]}] */
      console.error('Using the portal parameter in IMLibContext.setValue')
    }
    if (recKey) {
      if (typeof (this.store[recKey]) === 'undefined') {
        this.store[recKey] = {}
      }
      if (portal && typeof (this.store[recKey][key]) === 'undefined') {
        this.store[recKey][key] = {}
      }
      if (typeof (this.binding[recKey]) === 'undefined') {
        this.binding[recKey] = {}
        if (this.sequencing) {
          this.recordOrder.push(recKey)
        } else {
          this.pendingOrder.push(recKey)
        }
      }
      if (typeof (this.binding[recKey][key]) === 'undefined') {
        this.binding[recKey][key] = []
      }
      if (portal && typeof (this.binding[recKey][key][portal]) === 'undefined') {
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
          if (typeof (this.contextInfo[nodeId]) === 'undefined') {
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

  getValue(recKey, key = false, portal = false) {
    let value
    try {
      if (portal) {
        value = (key === false) ? this.store[portal] : this.store[portal][key]
      } else {
        value = (key === false) ? this.store[recKey] : this.store[recKey][key]
      }
      if (Array.isArray(value)) {
        value = value.join()
      }
      return (typeof value === 'undefined') ? null : value
    } catch (ex) {
      return null
    }
  }

  isValueUndefined(recKey, key, portal) {
    'use strict'
    let value, tableOccurrence, relatedRecId
    try {
      if (portal) {
        tableOccurrence = key.split('::')[0]
        relatedRecId = portal.split('=')[1]
        value = this.store[recKey][0][tableOccurrence][relatedRecId][key]
      } else {
        value = this.store[recKey][key]
      }
      return (typeof value === 'undefined')
    } catch (ex) {
      return null
    }
  }

  getContextInfo(nodeId, target) {
    'use strict'
    try {
      let info = this.contextInfo[nodeId][target ? target : '_im_no_target']
      return (typeof info === 'undefined') ? null : info
    } catch (ex) {
      return null
    }
  }

  getContextValue(nodeId, target) {
    'use strict'
    try {
      let info = this.contextInfo[nodeId][target ? target : '_im_no_target']
      let value = info.context.getValue(info.record, info.field)
      return (typeof value === 'undefined') ? null : value
    } catch (ex) {
      return null
    }
  }

  getContextRecord(nodeId) {
    'use strict'
    let infos, keys
    try {
      infos = this.contextInfo[nodeId]
      keys = Object.keys(infos)
      for (let i = 0; i < keys.length; i += 1) {
        if (infos[keys[i]]) {
          return this.store[infos[keys[i]].record]
        }
      }
      return null
    } catch (ex) {
      return null
    }
  }

  removeEntry(pkvalue) {
    'use strict'
    let keyField, keying, bindingInfo, contextDef, targetNode, repeaterNodes
    let removingNodeIds = []
    contextDef = this.getContextDef()
    keyField = contextDef.key
    keying = keyField + '=' + pkvalue
    bindingInfo = this.binding[keying]
    if (bindingInfo) {
      repeaterNodes = bindingInfo._im_repeater
      if (repeaterNodes) {
        for (let i = 0; i < repeaterNodes.length; i += 1) {
          removingNodeIds.push(repeaterNodes[i].id)
        }
      }
    }
    if (removingNodeIds.length > 0) {
      for (let i = 0; i < removingNodeIds.length; i += 1) {
        IMLibContextPool.removeRecordFromPool(removingNodeIds[i])
      }
      for (let i = 0; i < removingNodeIds.length; i += 1) {
        targetNode = document.getElementById(removingNodeIds[i])
        if (targetNode) {
          targetNode.parentNode.removeChild(targetNode)
        }
      }
    }
  }

  isContaining(value) {
    'use strict'
    let contextDef, contextName
    let checkResult = []
    let result, opePosition, leftHand, rightHand, leftResult, rightResult

    contextDef = this.getContextDef()
    contextName = contextDef.name
    if (contextDef.query) {
      for (const i in contextDef.query) {
        checkResult.push(checkCondition(contextDef.query[i], value))
      }
    }
    if (INTERMediator.additionalCondition[contextName]) {
      for (let i = 0; i < INTERMediator.additionalCondition[contextName].length; i += 1) {
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
      for (const fieldName in this.foreignValue) {
        if (contextDef.relation) {
          for (let i in contextDef.relation) {
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
      let realValue

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

  insertEntry(pkvalue, fields, values) {
    'use strict'
    let field, value
    for (let i = 0; i < fields.length; i += 1) {
      field = fields[i]
      value = values[i]
      this.setValue(pkvalue, field, value)
    }
  }

  // ****** Look-up processing ******
  // data-im-control="lookup:item@product_id:product@name"
  /*
  lookingUp:
    IM3-27:
      keying: 0
      trigger: "item@product_id"
      from: "product@name"
      target: "item@product_name"
    IM3-28:
      keying: 0
      trigger: "item@product_id"
      from: "product@unitprice"
      target: "item@product_unitprice"
   */
  setupLookup(node, ix) {
    let components
    const imControl = node.getAttribute('data-im-control')
    if (imControl && imControl.match(/^lookup:/)) {
      components = imControl.split(':')
      if (components.length === 3) {
        this.lookingUp[node.id] = {
          keying: ix,
          trigger: components[1],
          from: components[2],
          target: node.getAttribute('data-im')
        }
        this.lookingUpInfo = null
      }
    }
  }

  updateLookupInfo(force = false) {
    let key, keying, obj
    const keyField = this.getKeyField()
    // IMLibUI.recalculationOnValueChange = false
    /*
this.lookingUpInfo
  id=1:
    0:
      keying: "id=1"
      key_value: 1
      node_id: "IM3-27"
      trigger: "item@product_id"
      from: "product@name"
      target: "item@product_name"
    1:
      keying: "id=1"
      key_value: 1
      node_id: "IM3-28"
      trigger: "item@product_id"
      from: "product@unitprice"
      target: "item@product_unitprice"
   id=2: (2) [{…}, {…}]
   id=3: (2) [{…}, {…}]
 */
    if (force || this.lookingUpInfo === null) {
      this.lookingUpInfo = {}
      for (key of Object.keys(this.lookingUp)) {
        keying = keyField + '=' + this.lookingUp[key].keying
        obj = {
          keying: keying,
          key_value: this.lookingUp[key].keying,
          node_id: key,
          trigger: this.lookingUp[key].trigger,
          from: this.lookingUp[key].from,
          target: this.lookingUp[key].target
        }
        if (!this.lookingUpInfo[keying]) {
          this.lookingUpInfo[keying] = [obj]
        } else {
          this.lookingUpInfo[keying].push(obj)
        }
      }
    }
  }

  updateContextAfterInsertAsLookup(newRecordId) {
    let nodes, binds
    const keyField = this.getContextDef().key
    this.updateLookupInfo(true)
    nodes = []
    binds = this.binding[keyField + '=' + newRecordId]
    if (binds) {
      for (const field of Object.keys(binds)) {
        for (const bind of binds[field]) {
          if (nodes.indexOf(bind.id) < 0) {
            nodes.push(bind.id)
          }
        }
      }
      for (const node of nodes) {
        const nodeRef = document.getElementById(node)
        if (nodeRef) {
          const value = nodeRef.value
          if (value) {
            this.updateContextAsLookup(node, value)
          }
        }
      }
    }
  }


  updateContextAsLookup(idValue = null, value = null) {
    let keying, obj, imTarget, lookingContexts, fromValue, newContext, contexts,
      contextDef, isModified, changedObj, linkInfo, nodeInfo, contextInfo

    if (Object.keys(this.lookingUp).length === 0) { // In case of no lookup node.
      return
    }
    if (!idValue) { // call with null, non operations required
      return
    }
    let fromStore = {}
    this.updateLookupInfo()// Update the table for looking-up operations
    if (Object.keys(this.lookingUpInfo).length === 0) { // Just in case.
      return
    }
    changedObj = document.getElementById(idValue)
    linkInfo = INTERMediatorLib.getLinkedElementInfo(changedObj)
    nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfo[0]) // Suppose to be the first definition.
    contextInfo = IMLibContextPool.getContextInfoFromId(idValue, nodeInfo.target)
    lookingContexts = [] // Correcting the context names of contexts looked up. i.e. 'from' key data
    if (this.lookingUpInfo[contextInfo.record]) {
      for (const obj of this.lookingUpInfo[contextInfo.record]) {
        if (obj.trigger === linkInfo[0]) { // Suppose to be the first definition.
          fromValue = obj.from.split('@')
          if (lookingContexts.indexOf(fromValue[0]) < 0) {
            lookingContexts.push(fromValue[0])
          }
        }
      }
    }
    // Search for the valid record to be looked up.
    for (const contextName of lookingContexts) {
      contexts = IMLibContextPool.getContextFromName(contextName)
      for (const context of contexts) {
        contextDef = context.getContextDef()
        if (context.parentContext === this && contextDef.relation  /* && relation[0]['foreign-key'] == keyField*/) {
          keying = contextDef.relation[0]['foreign-key'] + '=' + value
          if (context.store[keying]) {  // There is the lookup required data on any context
            fromStore[contextName] = context.store[keying]
            isModified = false
            if (this.lookingUpInfo[contextInfo.record]) {
              for (const obj of this.lookingUpInfo[contextInfo.record]) {
                imTarget = obj.target.split('@')
                fromValue = obj.from.split('@')
                if (imTarget[1] && fromValue[0] && fromStore[fromValue[0]] && fromValue[1] && fromStore[fromValue[0]][fromValue[1]]) {
                  this.setDataWithKey(obj.key_value, imTarget[1], fromStore[fromValue[0]][fromValue[1]])
                  isModified = true
                }
              }
            }
            if (isModified) {
              IMLibQueue.setTask((completeTask) => {
                IMLibCalc.recalculation()
                completeTask()
              })
            }
            return
          }
        }
      }
    }
    for (const contextName of lookingContexts) {
      if (this.lookingUpInfo[contextInfo.record]) {
        contextDef = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', contextName)
        newContext = IMLibContextPool.generateContextObject(contextDef)
        newContext.parentContext = this
        IMLibQueue.setTask((() => {
          const targetContext = newContext
          const cDef = contextDef
          const pValue = value
          const parentObj = {}
          parentObj[cDef.relation[0]['join-field']] = pValue
          const lookingUpInfoObj = this.lookingUpInfo[contextInfo.record]
          const thisObj = this
          let fields = []
          for (obj of lookingUpInfoObj) {
            let fromValue = obj.from.split('@')
            if (fromValue[0] === targetContext.contextName && fromValue[1]) {
              fields.push(fromValue[1])
            }
          }
          return (completeTask) => {
            try {
              INTERMediator_DBAdapter.db_query_async(
                {
                  'name': cDef.name,
                  'records': 1,
                  'paging': false,
                  'fields': fields,
                  'parentkeyvalue': parentObj,
                  'conditions': null,
                  'useoffset': false,
                  'uselimit': false
                },
                (result) => {
                  let imTarget, fromValue, aRecord
                  targetContext.storeRecords(result)
                  keying = cDef.relation[0]['foreign-key'] + '=' + pValue
                  aRecord = targetContext.store[keying]
                  if (aRecord) {
                    for (const obj of lookingUpInfoObj) {
                      imTarget = obj.target.split('@')
                      fromValue = obj.from.split('@')
                      if (imTarget[1] && fromValue[0] && fromValue[1]
                        && fromValue[0] === targetContext.contextName && aRecord[fromValue[1]]) {
                        thisObj.setDataWithKey(obj.key_value, imTarget[1], aRecord[fromValue[1]])
                      }
                    }
                  }
                  IMLibQueue.setTask((completeTask) => {
                    IMLibCalc.recalculation()
                    completeTask()
                  })
                  INTERMediatorLog.flushMessage()
                  completeTask()
                },
                () => {
                  completeTask()
                }
              )
            } catch (ex) {
              if (ex.message === '_im_auth_required_') {
                throw ex
              } else {
                INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-13')
              }
            }
          }
        })())
      }
    }
  }

  captureCurrentStore() {
    if (!this.storeCaptured) {
      this.storeCaptured = Object.create(this.store)
      for (const key in this.store) {
        this.storeCaptured[key] = {}
        Object.assign(this.storeCaptured[key], this.store[key])
      }
    }
  }

  backToInitialValue(record, field) {
    if (this.storeCaptured && this.storeCaptured[record] && this.storeCaptured[record][field]) {
      const origData = this.storeCaptured[record][field]
      this.setValue(record, field, origData)
      this.setDataWithKey(record.split('=')[1], field, origData)
    }
  }
}

// @@IM@@IgnoringRestOfFile
module.exports = IMLibContext
const IMLibContextPool = require('../../src/js/INTER-Mediator-ContextPool')
const INTERMediatorOnPage = require('../../src/js/INTER-Mediator-Page')
const INTERMediator = require('../../src/js/INTER-Mediator')
