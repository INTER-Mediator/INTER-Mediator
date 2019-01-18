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
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
const IMLibContextPool = {
  poolingContexts: null,

  clearAll: function () {
    'use strict'
    this.poolingContexts = null
  },

  registerContext: function (context) {
    'use strict'
    if (this.poolingContexts === null) {
      this.poolingContexts = [context]
    } else {
      this.poolingContexts.push(context)
    }
  },

  excludingNode: null,

  synchronize: function (context, recKey, key, value, target, portal) {
    'use strict'
    var i, j, viewName, refNode, targetNodes
    let result = []
    let calcKey
    viewName = context.viewName
    if (this.poolingContexts === null) {
      return null
    }
    if (portal) {
      for (i = 0; i < this.poolingContexts.length; i += 1) {
        if (this.poolingContexts[i].viewName === viewName &&
          this.poolingContexts[i].binding[recKey] !== undefined &&
          this.poolingContexts[i].binding[recKey][key] !== undefined &&
          this.poolingContexts[i].binding[recKey][key][portal] !== undefined &&
          this.poolingContexts[i].store[recKey] !== undefined &&
          this.poolingContexts[i].store[recKey][key] !== undefined &&
          this.poolingContexts[i].store[recKey][key][portal] !== undefined) {
          this.poolingContexts[i].store[recKey][key][portal] = value
          targetNodes = this.poolingContexts[i].binding[recKey][key][portal]
          for (j = 0; j < targetNodes.length; j++) {
            refNode = document.getElementById(targetNodes[j].id)
            if (refNode) {
              IMLibElement.setValueToIMNode(refNode, targetNodes[j].target, value, true)
              result.push(targetNodes[j].id)
            }
          }
        }
      }
    } else {
      for (i = 0; i < this.poolingContexts.length; i += 1) {
        if (this.poolingContexts[i].viewName === viewName &&
          this.poolingContexts[i].binding[recKey] !== undefined &&
          this.poolingContexts[i].binding[recKey][key] !== undefined &&
          this.poolingContexts[i].store[recKey] !== undefined &&
          this.poolingContexts[i].store[recKey][key] !== undefined) {
          this.poolingContexts[i].store[recKey][key] = value
          targetNodes = this.poolingContexts[i].binding[recKey][key]
          for (j = 0; j < targetNodes.length; j++) {
            refNode = document.getElementById(targetNodes[j].id)
            calcKey = targetNodes[j].id
            if (targetNodes[j].target && targetNodes[j].target.length > 0) {
              calcKey += INTERMediator.separator + targetNodes[j].target
            }
            if (refNode && !(calcKey in IMLibCalc.calculateRequiredObject)) {
              IMLibElement.setValueToIMNode(refNode, targetNodes[j].target, value, true)
              result.push(targetNodes[j].id)
            }
          }
        }
      }
    }
    return result
  },

  getContextInfoFromId: function (idValue, target) {
    'use strict'
    var i, targetContext, element, linkInfo, nodeInfo, targetName
    let result = null
    if (!idValue) {
      return result
    }

    element = document.getElementById(idValue)
    if (!element) {
      return result
    }

    linkInfo = INTERMediatorLib.getLinkedElementInfo(element)
    if (!linkInfo && INTERMediatorLib.isWidgetElement(element.parentNode)) {
      linkInfo = INTERMediatorLib.getLinkedElementInfo(element.parentNode)
    }
    nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfo[0])

    targetName = target ? target : '_im_no_target'
    if (this.poolingContexts === null) {
      return null
    }
    for (i = 0; i < this.poolingContexts.length; i += 1) {
      targetContext = this.poolingContexts[i]
      if (targetContext.contextInfo[idValue] &&
        targetContext.contextInfo[idValue][targetName] &&
        targetContext.contextInfo[idValue][targetName].context.contextName === nodeInfo.table) {
        result = targetContext.contextInfo[idValue][targetName]
        return result
      }
    }
    return null
  },

  getKeyFieldValueFromId: function (idValue, target) {
    'use strict'
    var contextInfo = this.getContextInfoFromId(idValue, target)
    if (!contextInfo) {
      return null
    }
    var contextName = contextInfo.context.contextName
    var contextDef = IMLibContextPool.getContextDef(contextName)
    if (!contextDef) {
      return null
    }
    var keyField = contextDef.key ? contextDef.key : 'id'
    return contextInfo.record.substr(keyField.length + 1)
  },

  updateContext: function (idValue, target) {
    'use strict'
    var contextInfo, value
    contextInfo = IMLibContextPool.getContextInfoFromId(idValue, target)
    value = IMLibElement.getValueFromIMNode(document.getElementById(idValue))
    if (contextInfo) {
      contextInfo.context.setValue(
        contextInfo.record, contextInfo.field, value, false, target, contextInfo.portal)
    }
  },

  getContextFromEnclosure: function (enclosureNode) {
    'use strict'
    var i

    for (i = 0; i < this.poolingContexts.length; i += 1) {
      if (this.poolingContexts[i].enclosureNode === enclosureNode) {
        return this.poolingContexts[i]
      }
    }
  },

  contextFromEnclosureId: function (idValue) {
    'use strict'
    var i, enclosure
    if (!idValue) {
      return false
    }
    for (i = 0; i < this.poolingContexts.length; i += 1) {
      enclosure = this.poolingContexts[i].enclosureNode
      if (enclosure.getAttribute('id') === idValue) {
        return this.poolingContexts[i]
      }
    }
    return null
  },

  contextFromName: function (cName) {
    'use strict'
    var i
    if (!cName) {
      return false
    }
    for (i = 0; i < this.poolingContexts.length; i += 1) {
      if (this.poolingContexts[i].contextName === cName) {
        return this.poolingContexts[i]
      }
    }
    return null
  },

  getContextFromName: function (cName) {
    'use strict'
    var i
    let result = []
    if (!cName) {
      return false
    }
    for (i = 0; i < this.poolingContexts.length; i += 1) {
      if (this.poolingContexts[i].contextName === cName) {
        result.push(this.poolingContexts[i])
      }
    }
    return result
  },

  getContextsFromNameAndForeignValue: function (cName, fValue, parentKeyField) {
    'use strict'
    var i
    let result = []
    if (!cName) {
      return false
    }
    // parentKeyField = 'id'
    for (i = 0; i < this.poolingContexts.length; i += 1) {
      if (this.poolingContexts[i].contextName === cName &&
        this.poolingContexts[i].foreignValue[parentKeyField] === fValue) {
        result.push(this.poolingContexts[i])
      }
    }
    return result
  },

  dependingObjects: function (idValue) {
    'use strict'
    var i, j
    let result = []
    if (!idValue) {
      return false
    }
    for (i = 0; i < this.poolingContexts.length; i += 1) {
      for (j = 0; j < this.poolingContexts[i].dependingObject.length; j++) {
        if (this.poolingContexts[i].dependingObject[j] === idValue) {
          result.push(this.poolingContexts[i])
        }
      }
    }
    return result.length === 0 ? false : result
  },

  getChildContexts: function (parentContext) {
    'use strict'
    var i
    let childContexts = []
    for (i = 0; i < this.poolingContexts.length; i += 1) {
      if (this.poolingContexts[i].parentContext === parentContext) {
        childContexts.push(this.poolingContexts[i])
      }
    }
    return childContexts
  },

  childContexts: null,

  removeContextsFromPool: function (contexts) {
    'use strict'
    var i
    let regIds = []
    let delIds = []
    for (i = 0; i < this.poolingContexts.length; i += 1) {
      if (contexts.indexOf(this.poolingContexts[i]) > -1) {
        regIds.push(this.poolingContexts[i].registeredId)
        delIds.push(i)
      }
    }
    for (i = delIds.length - 1; i > -1; i--) {
      this.poolingContexts.splice(delIds[i], 1)
    }
    return regIds
  },

  removeRecordFromPool: function (repeaterIdValue) {
    'use strict'
    var i, j, field
    let nodeIds = []
    let targetKeying, targetKeyingObj, parentKeying, relatedId, idValue, delNodes,
      contextAndKey, sameOriginContexts, countDeleteNodes

    contextAndKey = getContextAndKeyFromId(repeaterIdValue)
    if (contextAndKey === null) {
      return
    }
    sameOriginContexts = this.getContextsWithSameOrigin(contextAndKey.context)
    // sameOriginContexts.push(contextAndKey.context)
    targetKeying = contextAndKey.key
    // targetKeyingObj = contextAndKey.context.binding[targetKeying]

    for (i = 0; i < sameOriginContexts.length; i += 1) {
      targetKeyingObj = sameOriginContexts[i].binding[targetKeying]
      for (field in targetKeyingObj) {
        if (targetKeyingObj.hasOwnProperty(field)) {
          for (j = 0; j < targetKeyingObj[field].length; j++) {
            if (nodeIds.indexOf(targetKeyingObj[field][j].id) < 0) {
              nodeIds.push(targetKeyingObj[field][j].id)
            }
          }
        }
      }

      if (INTERMediatorOnPage.dbClassName === 'FileMaker_FX' ||
        INTERMediatorOnPage.dbClassName === 'FileMaker_DataAPI') {
        // for FileMaker portal access mode
        parentKeying = Object.keys(contextAndKey.context.binding)[0]
        relatedId = targetKeying.split('=')[1]
        if (sameOriginContexts[i].binding[parentKeying] &&
          sameOriginContexts[i].binding[parentKeying]._im_repeater &&
          sameOriginContexts[i].binding[parentKeying]._im_repeater[relatedId] &&
          sameOriginContexts[i].binding[parentKeying]._im_repeater[relatedId][0]) {
          nodeIds.push(sameOriginContexts[i].binding[parentKeying]._im_repeater[relatedId][0].id)
        }
      }
    }
    delNodes = []
    for (i = 0; i < sameOriginContexts.length; i += 1) {
      for (idValue in sameOriginContexts[i].contextInfo) {
        if (sameOriginContexts[i].contextInfo.hasOwnProperty(idValue)) {
          if (nodeIds.indexOf(idValue) >= 0) {
            delete contextAndKey.context.contextInfo[idValue]
            delNodes.push(idValue)
          }
        }
      }
      delete sameOriginContexts[i].binding[targetKeying]
      delete sameOriginContexts[i].store[targetKeying]
    }
    countDeleteNodes = delNodes.length
    IMLibElement.deleteNodes(delNodes)

    this.poolingContexts = this.poolingContexts.filter(function (context) {
      return nodeIds.indexOf(context.enclosureNode.id) < 0
    })

    return countDeleteNodes

    // Private functions
    function getContextAndKeyFromId (repeaterIdValue) {
      var i, field, j, keying, foreignKey

      for (i = 0; i < IMLibContextPool.poolingContexts.length; i += 1) {
        for (keying in IMLibContextPool.poolingContexts[i].binding) {
          if (IMLibContextPool.poolingContexts[i].binding.hasOwnProperty(keying)) {
            for (field in IMLibContextPool.poolingContexts[i].binding[keying]) {
              if (IMLibContextPool.poolingContexts[i].binding[keying].hasOwnProperty(field) &&
                field === '_im_repeater') {
                for (j = 0; j < IMLibContextPool.poolingContexts[i].binding[keying][field].length; j++) {
                  if (repeaterIdValue === IMLibContextPool.poolingContexts[i].binding[keying][field][j].id) {
                    return ({context: IMLibContextPool.poolingContexts[i], key: keying})
                  }
                }

                if (INTERMediatorOnPage.dbClassName === 'FileMaker_FX' ||
                  INTERMediatorOnPage.dbClassName === 'FileMaker_DataAPI') {
                  // for FileMaker portal access mode
                  for (foreignKey in IMLibContextPool.poolingContexts[i].binding[keying][field]) {
                    if (IMLibContextPool.poolingContexts[i].binding[keying][field].hasOwnProperty(foreignKey)) {
                      for (j = 0; j < IMLibContextPool.poolingContexts[i].binding[keying][field][foreignKey].length; j++) {
                        if (repeaterIdValue === IMLibContextPool.poolingContexts[i].binding[keying][field][foreignKey][j].id) {
                          return ({
                            context: IMLibContextPool.poolingContexts[i],
                            key: INTERMediatorOnPage.defaultKeyName + '=' + foreignKey
                          })
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
      return null
    }
  },

  getContextsWithSameOrigin: function (originalContext) {
    'use strict'
    var i
    let contexts = []
    let contextDef
    let isPortal = false

    contextDef = IMLibContextPool.getContextDef(originalContext.contextName)
    if (contextDef && contextDef.relation) {
      for (i in contextDef.relation) {
        if (contextDef.relation.hasOwnProperty(i) && contextDef.relation[i].portal) {
          isPortal = true
          break
        }
      }
    }
    for (i = 0; i < IMLibContextPool.poolingContexts.length; i += 1) {
      if (IMLibContextPool.poolingContexts[i].sourceName === originalContext.sourceName) {
        if (!isPortal || originalContext.parentContext !== IMLibContextPool.poolingContexts[i]) {
          contexts.push(IMLibContextPool.poolingContexts[i])
        }
      }
    }
    return contexts
  },

  updateOnAnotherClient: async function (eventName, info) {
    'use strict'
    var i, j, k
    let entityName = info.entity
    let contextDef, contextView, keyField, recKey

    if (eventName === 'update') {
      for (i = 0; i < this.poolingContexts.length; i += 1) {
        contextDef = this.getContextDef(this.poolingContexts[i].contextName)
        contextView = contextDef.view ? contextDef.view : contextDef.name
        if (contextView === entityName) {
          keyField = contextDef.key
          recKey = keyField + '=' + info.pkvalue
          this.poolingContexts[i].setValue(recKey, info.field[0], info.value[0])

          var bindingInfo = this.poolingContexts[i].binding[recKey][info.field[0]]
          for (j = 0; j < bindingInfo.length; j++) {
            var updateRequiredContext = IMLibContextPool.dependingObjects(bindingInfo[j].id)
            for (k = 0; k < updateRequiredContext.length; k++) {
              updateRequiredContext[k].foreignValue = {}
              updateRequiredContext[k].foreignValue[info.field[0]] = info.value[0]
              if (updateRequiredContext[k]) {
                await INTERMediator.constructMain(updateRequiredContext[k])
              }
            }
          }
        }
      }
      IMLibCalc.recalculation()
    } else if (eventName === 'create') {
      for (i = 0; i < this.poolingContexts.length; i += 1) {
        contextDef = this.getContextDef(this.poolingContexts[i].contextName)
        contextView = contextDef.view ? contextDef.view : contextDef.name
        if (contextView === entityName) {
          if (this.poolingContexts[i].isContaining(info.value[0])) {
            await INTERMediator.constructMain(this.poolingContexts[i], info.value)
          }
        }
      }
      IMLibCalc.recalculation()
    } else if (eventName === 'delete') {
      for (i = 0; i < this.poolingContexts.length; i += 1) {
        contextDef = this.getContextDef(this.poolingContexts[i].contextName)
        contextView = contextDef.view ? contextDef.view : contextDef.name
        if (contextView === entityName) {
          this.poolingContexts[i].removeEntry(info.pkvalue)
        }
      }
      IMLibCalc.recalculation()
    }
  },

  getMasterContext: function () {
    'use strict'
    var i, contextDef
    if (!this.poolingContexts) {
      return null
    }
    for (i = 0; i < this.poolingContexts.length; i += 1) {
      contextDef = this.poolingContexts[i].getContextDef()
      if (contextDef['navi-control'] && contextDef['navi-control'].match(/master/)) {
        return this.poolingContexts[i]
      }
    }
    return null
  },

  getDetailContext: function () {
    'use strict'
    var i, contextDef
    if (!this.poolingContexts) {
      return null
    }
    for (i = 0; i < this.poolingContexts.length; i += 1) {
      contextDef = this.poolingContexts[i].getContextDef()
      if (contextDef['navi-control'] && contextDef['navi-control'].match(/detail/)) {
        return this.poolingContexts[i]
      }
    }
    return null
  },

  getContextDef: function (contextName) {
    'use strict'
    return INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', contextName)
  },

  getContextFromNodeId: function (nodeId) {
    'use strict'
    var i, context, contextDef, rKey, fKey, pKey, isPortal, bindInfo
    if (!this.poolingContexts) {
      return null
    }
    for (i = 0; i < this.poolingContexts.length; i += 1) {
      context = this.poolingContexts[i]
      contextDef = context.getContextDef()
      isPortal = false
      if (contextDef.relation) {
        for (rKey in contextDef.relation) {
          if (contextDef.relation[rKey].portal) {
            isPortal = true
          }
        }
      }
      for (rKey in context.binding) {
        if (context.binding.hasOwnProperty(rKey)) {
          for (fKey in context.binding[rKey]) {
            if (isPortal) {
              for (pKey in context.binding[rKey][fKey]) {
                if (context.binding[rKey][fKey].hasOwnProperty(pKey)) {
                  bindInfo = context.binding[rKey][fKey][pKey]
                  if (bindInfo.nodeId === nodeId) {
                    return context
                  }
                }
              }
            } else {
              bindInfo = context.binding[rKey][fKey]
              if (bindInfo.nodeId === nodeId) {
                return context
              }
            }
          }
        }
      }
    }
    return null
  },

  getContextFromEnclosureNode: function (enclosureNode) {
    'use strict'
    var i, context
    if (!this.poolingContexts) {
      return null
    }
    for (i = 0; i < this.poolingContexts.length; i += 1) {
      context = this.poolingContexts[i]
      if (context.enclosureNode === enclosureNode) {
        return context
      }
    }
    return null
  },

  generateContextObject: function (contextDef, enclosure, repeaters, repeatersOriginal) {
    'use strict'
    var contextObj = new IMLibContext(contextDef.name)
    contextObj.contextDefinition = contextDef
    contextObj.enclosureNode = enclosure
    contextObj.repeaterNodes = repeaters
    contextObj.original = repeatersOriginal
    contextObj.sequencing = true
    return contextObj
  },

  getPagingContext: function () {
    'use strict'
    var i, context, contextDef
    if (this.poolingContexts) {
      for (i = 0; i < this.poolingContexts.length; i += 1) {
        context = this.poolingContexts[i]
        contextDef = context.getContextDef()
        if (contextDef.paging) {
          return context
        }
      }
    }
    return null
  }
}

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

  function checkSameValue (initialValue, currentFieldVal) {
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
    recordNumber = parseInt(this.contextDefinition.records, 10)
    // From INTERMediator.recordLimit property
    for (key in INTERMediator.recordLimit) {
      if (INTERMediator.recordLimit.hasOwnProperty(key)) {
        value = String(INTERMediator.recordLimit[key])
        if (key === this.contextDefinition.name &&
          value.length > 0) {
          recordNumber = parseInt(value)
          INTERMediator.setLocalProperty('_im_pagedSize', recordNumber)
        }
      }
    }
    // From INTERMediator.pagedSize
    if (parseInt(INTERMediator.pagedSize, 10) > 0) {
      recordNumber = INTERMediator.pagedSize
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

  function seekRemovingContext (context) {
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

// @@IM@@IgnoringRestOfFile
module.exports = IMLibContextPool
