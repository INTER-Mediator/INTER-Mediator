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
    let targetContext, element, linkInfo, nodeInfo, targetName
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
      return result
    }
    for (let i = 0; i < this.poolingContexts.length; i += 1) {
      targetContext = this.poolingContexts[i]
      if (targetContext.contextInfo[idValue] &&
        targetContext.contextInfo[idValue][targetName] &&
        targetContext.contextInfo[idValue][targetName].context.contextName === nodeInfo.table) {
        result = targetContext.contextInfo[idValue][targetName]
        return result
      }
    }
    return result
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
      //contextInfo.context.updateContext(idValue, target, contextInfo, value)
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
    if (cName == '_') {
      return IMLibLocalContext
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
    function getContextAndKeyFromId(repeaterIdValue) {
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

  generateContextObject: function (contextDef, enclosure, repeaters, repeatersOriginal ) {
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

// @@IM@@IgnoringRestOfFile
module.exports = IMLibContextPool
