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
 * Usually you don't have to instantiate this class with the new operator.
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
    let result = []
    let calcKey
    const viewName = context.viewName
    if (this.poolingContexts === null) {
      return null
    }
    if (portal) {
      for (const pContext of this.poolingContexts) {
        if (isItTargetContextPortal(pContext, viewName, recKey, key, portal)) {
          pContext.store[recKey][key][portal] = value
          for (const targetNode of pContext.binding[recKey][key][portal]) {
            const refNode = document.getElementById(targetNode.id)
            if (refNode) {
              IMLibElement.setValueToIMNode(refNode, targetNode.target, value, true)
              if (result.indexOf(pContext) < 0) {
                result.push(pContext)
              }
            }
          }
        }
      }
    } else {
      for (const pContext of this.poolingContexts) {
        if (isItTargetContext(pContext, viewName, recKey, key)) {
          pContext.store[recKey][key] = value
          for (const targetNode of pContext.binding[recKey][key]) {
            const refNode = document.getElementById(targetNode.id)
            calcKey = targetNode.id
            if (targetNode.target && targetNode.target.length > 0) {
              calcKey += INTERMediator.separator + targetNode.target
            }
            if (refNode && !(calcKey in IMLibCalc.calculateRequiredObject)) {
              IMLibElement.setValueToIMNode(refNode, targetNode.target, value, true)
              if (result.indexOf(pContext) < 0) {
                result.push(pContext)
              }
            }
          }
        }
      }
    }
    return result

    function isItTargetContext(context, viewName, recKey, key) {
      return (context.viewName === viewName &&
        typeof (context.binding[recKey]) !== 'undefined' &&
        typeof (context.binding[recKey][key]) !== 'undefined' &&
        typeof (context.store[recKey]) !== 'undefined' &&
        typeof (context.store[recKey][key]) !== 'undefined')
    }

    function isItTargetContextPortal(context, viewName, recKey, key, portal) {
      return (context.viewName === viewName &&
        typeof (context.binding[recKey]) !== 'undefined' &&
        typeof (context.binding[recKey][key]) !== 'undefined' &&
        typeof (context.binding[recKey][key][portal]) !== 'undefined' &&
        typeof (context.store[recKey]) !== 'undefined' &&
        typeof (context.store[recKey][key]) !== 'undefined' &&
        typeof (context.store[recKey][key][portal]) !== 'undefined')
    }
  },

  getContextInfoFromId: function (idValue, target) {
    'use strict'
    let result = null
    if (!idValue) {
      return result
    }

    const element = document.getElementById(idValue)
    if (!element) {
      return result
    }

    let linkInfo = INTERMediatorLib.getLinkedElementInfo(element)
    if (!linkInfo && INTERMediatorLib.isWidgetElement(element.parentNode)) {
      linkInfo = INTERMediatorLib.getLinkedElementInfo(element.parentNode)
    }
    const nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfo[0])

    const targetName = target ? target : '_im_no_target'
    if (this.poolingContexts === null) {
      return result
    }
    for (let i = 0; i < this.poolingContexts.length; i += 1) {
      const targetContext = this.poolingContexts[i]
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
    const contextInfo = this.getContextInfoFromId(idValue, target)
    if (!contextInfo) {
      return null
    }
    const contextName = contextInfo.context.contextName
    const contextDef = IMLibContextPool.getContextDef(contextName)
    if (!contextDef) {
      return null
    }
    const keyField = contextDef.key ? contextDef.key : 'id'
    return contextInfo.record.substr(keyField.length + 1)
  },

  updateContext: function (idValue, target) {
    'use strict'
    const contextInfo = IMLibContextPool.getContextInfoFromId(idValue, target)
    const value = IMLibElement.getValueFromIMNode(document.getElementById(idValue))
    if (contextInfo) {
      const updatingContexts = contextInfo.context.setValue(
        contextInfo.record, contextInfo.field, value, false, target, contextInfo.portal)
      //contextInfo.context.updateContext(idValue, target, contextInfo, value)
      const masterContext = IMLibContextPool.getMasterContext()
      if (masterContext) {
        const masterContextDef = masterContext.getContextDef()
        let uniqueArray = []
        if (masterContextDef && !masterContextDef['navi-control'].match(/hide/)) {
          for (const context of updatingContexts) {
            if (uniqueArray.indexOf(context) < 0) {
              if (context.sortKeys && context.sortKeys.indexOf(contextInfo.field) >= 0) {
                uniqueArray.push(context)
              }
              // const contextDef = context.getContextDef()
              // if (contextDef['navi-control'] && contextDef['navi-control'].match(/master/)) {
              //   uniqueArray = ['*']
              // }
            }
          }
        }
        if (uniqueArray.length > 0) {
          IMLibQueue.setTask((complate) => {
            if (uniqueArray[0] === '*') {
              INTERMediator.constructMain()
            } else {
              for (const context of uniqueArray) {
                IMLibQueue.setTask(async (complate) => {
                  await INTERMediator.constructMain(context)
                  complate()
                })
              }
            }
            complate()
          }, false, true)
          IMLibQueue.setTask((complate) => {
            IMLibPageNavigation.moveDetailOnceAgain()
            complate()
          }, false, true)
        }
      }
    }
  },

  getDetailContext: function () {
    'use strict'
    for (const context of this.poolingContexts) {
      const contextDef = context.getContextDef()
      if (contextDef['navi-control'] && contextDef['navi-control'].match(/detail/)) {
        return context
      }
    }
    return null
  },

  getContext: function (contextName) {
    'use strict'
    for (let i = 0; i < this.poolingContexts.length; i += 1) {
      if (this.poolingContexts[i].contextName === contextName) {
        return this.poolingContexts[i]
      }
    }
    return null
  },

  getContextFromEnclosure:
    function (enclosureNode) {
      'use strict'
      for (let i = 0; i < this.poolingContexts.length; i += 1) {
        if (this.poolingContexts[i].enclosureNode === enclosureNode) {
          return this.poolingContexts[i]
        }
      }
    },

  contextFromEnclosureId: function (idValue) {
    'use strict'
    if (!idValue) {
      return false
    }
    for (let i = 0; i < this.poolingContexts.length; i += 1) {
      const enclosure = this.poolingContexts[i].enclosureNode
      if (enclosure && enclosure.getAttribute('id') === idValue) {
        return this.poolingContexts[i]
      }
    }
    return null
  },

  contextFromName: function (cName) {
    'use strict'
    if (!cName) {
      return false
    }
    if (cName === '_') {
      return IMLibLocalContext
    }
    for (let i = 0; i < this.poolingContexts.length; i += 1) {
      if (this.poolingContexts[i].contextName === cName) {
        return this.poolingContexts[i]
      }
    }
    return null
  },

  getContextFromName: function (cName) {
    'use strict'
    let result = []
    if (!cName) {
      return false
    }
    for (let i = 0; i < this.poolingContexts.length; i += 1) {
      if (this.poolingContexts[i].contextName === cName) {
        result.push(this.poolingContexts[i])
      }
    }
    return result
  },

  getContextsFromNameAndForeignValue: function (cName, fValue, parentKeyField) {
    'use strict'
    let result = []
    if (!cName) {
      return false
    }
    // parentKeyField = 'id'
    for (let i = 0; i < this.poolingContexts.length; i += 1) {
      if (this.poolingContexts[i].contextName === cName &&
        this.poolingContexts[i].foreignValue[parentKeyField] === fValue) {
        result.push(this.poolingContexts[i])
      }
    }
    return result
  },

  dependingObjects: function (idValue) {
    'use strict'
    let result = []
    if (!idValue) {
      return false
    }
    for (let i = 0; i < this.poolingContexts.length; i += 1) {
      for (let j = 0; j < this.poolingContexts[i].dependingObject.length; j++) {
        if (this.poolingContexts[i].dependingObject[j] === idValue) {
          result.push(this.poolingContexts[i])
        }
      }
    }
    return result.length === 0 ? false : result
  },

  getChildContexts: function (parentContext) {
    'use strict'
    let childContexts = []
    if (this.poolingContexts) {
      for (let i = 0; i < this.poolingContexts.length; i += 1) {
        if (this.poolingContexts[i].parentContext === parentContext) {
          childContexts.push(this.poolingContexts[i])
        }
      }
    }
    return childContexts
  },

  childContexts: null,

  removeContextsFromPool: function (contexts) {
    'use strict'
    let regIds = []
    let delIds = []
    if (this.poolingContexts) {
      for (let i = 0; i < this.poolingContexts.length; i += 1) {
        if (contexts.indexOf(this.poolingContexts[i]) > -1) {
          regIds.push(this.poolingContexts[i].registeredId)
          delIds.push(i)
        }
      }
      for (let i = delIds.length - 1; i > -1; i--) {
        this.poolingContexts.splice(delIds[i], 1)
      }
    }
    return regIds
  },

  removeRecordFromPool: function (repeaterIdValue) {
    'use strict'
    let nodeIds = [], delNodes = []
    const contextAndKey = getContextAndKeyFromId(repeaterIdValue)
    if (contextAndKey === null) {
      return
    }
    const sameOriginContexts = this.getContextsWithSameOrigin(contextAndKey.context)
    // sameOriginContexts.push(contextAndKey.context)
    const targetKeying = contextAndKey.key
    // targetKeyingObj = contextAndKey.context.binding[targetKeying]

    for (let i = 0; i < sameOriginContexts.length; i += 1) {
      const targetKeyingObj = sameOriginContexts[i].binding[targetKeying]
      for (const field in targetKeyingObj) {
        if (targetKeyingObj.hasOwnProperty(field)) {
          for (let j = 0; j < targetKeyingObj[field].length; j++) {
            if (nodeIds.indexOf(targetKeyingObj[field][j].id) < 0) {
              nodeIds.push(targetKeyingObj[field][j].id)
            }
          }
        }
      }
      sameOriginContexts[i].count--
      sameOriginContexts[i].resultCount--
      sameOriginContexts[i].totalCount--

      if (INTERMediatorOnPage.dbClassName.match(/FileMaker_FX/) ||
        INTERMediatorOnPage.dbClassName.match(/FileMaker_DataAPI/)) {
        // for FileMaker portal access mode
        const parentKeying = Object.keys(contextAndKey.context.binding)[0]
        const relatedId = targetKeying.split('=')[1]
        if (sameOriginContexts[i].binding[parentKeying] &&
          sameOriginContexts[i].binding[parentKeying]._im_repeater &&
          sameOriginContexts[i].binding[parentKeying]._im_repeater[relatedId] &&
          sameOriginContexts[i].binding[parentKeying]._im_repeater[relatedId][0]) {
          nodeIds.push(sameOriginContexts[i].binding[parentKeying]._im_repeater[relatedId][0].id)
        }
      }
    }
    for (let i = 0; i < sameOriginContexts.length; i += 1) {
      for (const idValue in sameOriginContexts[i].contextInfo) {
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
    const countDeleteNodes = delNodes.length
    IMLibElement.deleteNodes(delNodes)

    this.poolingContexts = this.poolingContexts.filter(function (context) {
      return nodeIds.indexOf(context.enclosureNode.id) < 0
    })

    return countDeleteNodes

    // Private functions
    function getContextAndKeyFromId(repeaterIdValue) {
      for (let i = 0; i < IMLibContextPool.poolingContexts.length; i += 1) {
        for (const keying in IMLibContextPool.poolingContexts[i].binding) {
          if (IMLibContextPool.poolingContexts[i].binding.hasOwnProperty(keying)) {
            for (const field in IMLibContextPool.poolingContexts[i].binding[keying]) {
              if (IMLibContextPool.poolingContexts[i].binding[keying].hasOwnProperty(field) &&
                field === '_im_repeater') {
                for (let j = 0; j < IMLibContextPool.poolingContexts[i].binding[keying][field].length; j++) {
                  if (repeaterIdValue === IMLibContextPool.poolingContexts[i].binding[keying][field][j].id) {
                    return ({context: IMLibContextPool.poolingContexts[i], key: keying})
                  }
                }

                if (INTERMediatorOnPage.dbClassName.match(/FileMaker_FX/) ||
                  INTERMediatorOnPage.dbClassName.match(/FileMaker_DataAPI/)) {
                  // for FileMaker portal access mode
                  for (const foreignKey in IMLibContextPool.poolingContexts[i].binding[keying][field]) {
                    if (IMLibContextPool.poolingContexts[i].binding[keying][field].hasOwnProperty(foreignKey)) {
                      for (let j = 0; j < IMLibContextPool.poolingContexts[i].binding[keying][field][foreignKey].length; j++) {
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
    const contexts = []
    let isPortal = false

    const contextDef = IMLibContextPool.getContextDef(originalContext.contextName)
    if (contextDef && contextDef.relation) {
      for (const i in contextDef.relation) {
        if (contextDef.relation.hasOwnProperty(i) && contextDef.relation[i].portal) {
          isPortal = true
          break
        }
      }
    }
    for (let i = 0; i < IMLibContextPool.poolingContexts.length; i += 1) {
      if (IMLibContextPool.poolingContexts[i].sourceName === originalContext.sourceName) {
        if (!isPortal || originalContext.parentContext !== IMLibContextPool.poolingContexts[i]) {
          contexts.push(IMLibContextPool.poolingContexts[i])
        }
      }
    }
    return contexts
  },

  /*
   Sample of info variable.

   {entity: "Patient",
    field:  ['patientIDx', 'first_name', 'last_name', 'date_of_birth', 'status'],
    justnotify: false,
    pkvalue: [1],
    value:  ['PC1R85MS_44631.1', 'Test', 'Patient', '1990-01-01', 1]}
   */
  updateOnAnotherClientUpdated: async function (info) {
    const entityName = info.entity
    for (let i = 0; i < this.poolingContexts.length; i += 1) {
      const contextDef = this.getContextDef(this.poolingContexts[i].contextName)
      const contextSource = contextDef.source ?? contextDef.table ?? contextDef.view ?? contextDef.name
      if (contextSource === entityName) {
        const keyField = contextDef.key
        const recKey = keyField + '=' + info.pkvalue[0]
        for (let j = 0; j < info.field.length; j += 1) {
          if (this.poolingContexts[i].getValue(recKey, info.field[j]) !== info.value[j]) {
            this.poolingContexts[i].setValue(recKey, info.field[j], info.value[j])
          }
        }
        const bindingInfo = this.poolingContexts[i].binding[recKey][info.field[0]]
        for (let j = 0; j < bindingInfo.length; j++) {
          const updateRequiredContext = IMLibContextPool.dependingObjects(bindingInfo[j].id)
          for (let k = 0; k < updateRequiredContext.length; k++) {
            updateRequiredContext[k].foreignValue = {}
            updateRequiredContext[k].foreignValue[info.field[0]] = info.value[0]
            if (updateRequiredContext[k]) {
              INTERMediatorOnPage.updatingWithSynchronize += 1;
              await INTERMediator.constructMain(updateRequiredContext[k])
              INTERMediatorOnPage.updatingWithSynchronize -= 1;
            }
          }
        }
      }
    }
    IMLibCalc.recalculation()
  },

  updateOnAnotherClientCreated: async function (info) {
    const entityName = info.entity
    for (let i = 0; i < this.poolingContexts.length; i += 1) {
      const contextDef = this.getContextDef(this.poolingContexts[i].contextName)
      const contextSource = contextDef.source ?? contextDef.table ?? contextDef.view ?? contextDef.name
      if (contextSource === entityName) {
        if (this.poolingContexts[i].isContaining(info.value[0])) {
          IMLibQueue.setTask(async (complete) => {
            INTERMediatorOnPage.updatingWithSynchronize += 1;
            await INTERMediator.constructMain(this.poolingContexts[i])
            INTERMediatorOnPage.updatingWithSynchronize -= 1;
            complete()
          })
        }
      }
    }
    IMLibCalc.recalculation()
  },

  updateOnAnotherClientDeleted: function (info) {
    const entityName = info.entity
    for (let i = 0; i < this.poolingContexts.length; i += 1) {
      const contextDef = this.getContextDef(this.poolingContexts[i].contextName)
      const contextSource = contextDef.source ?? contextDef.table ?? contextDef.view ?? contextDef.name
      if (contextSource === entityName) {
        this.poolingContexts[i].removeEntry(info.pkvalue)
      }
    }
    IMLibCalc.recalculation()
  },

  getMasterContext: function () {
    'use strict'
    if (!this.poolingContexts) {
      return null
    }
    for (let i = 0; i < this.poolingContexts.length; i += 1) {
      const contextDef = this.poolingContexts[i].getContextDef()
      if (contextDef['navi-control'] && contextDef['navi-control'].match(/master/)) {
        return this.poolingContexts[i]
      }
    }
    return null
  },

  getDetailContext: function () {
    'use strict'
    if (!this.poolingContexts) {
      return null
    }
    for (let i = 0; i < this.poolingContexts.length; i += 1) {
      const contextDef = this.poolingContexts[i].getContextDef()
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
    if (!this.poolingContexts) {
      return null
    }
    for (let i = 0; i < this.poolingContexts.length; i += 1) {
      const context = this.poolingContexts[i]
      const contextDef = context.getContextDef()
      let isPortal = false
      if (contextDef.relation) {
        for (let rKey in contextDef.relation) {
          if (contextDef.relation[rKey].portal) {
            isPortal = true
          }
        }
      }
      for (const rKey in context.binding) {
        if (context.binding.hasOwnProperty(rKey)) {
          for (const fKey in context.binding[rKey]) {
            if (isPortal) {
              for (const pKey in context.binding[rKey][fKey]) {
                if (context.binding[rKey][fKey].hasOwnProperty(pKey)) {
                  const bindInfo = context.binding[rKey][fKey][pKey]
                  if (bindInfo.nodeId === nodeId) {
                    return context
                  }
                }
              }
            } else {
              const bindInfo = context.binding[rKey][fKey]
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
    if (!this.poolingContexts) {
      return null
    }
    for (let i = 0; i < this.poolingContexts.length; i += 1) {
      const context = this.poolingContexts[i]
      if (context.enclosureNode === enclosureNode) {
        return context
      }
    }
    return null
  },

  generateContextObject: function (contextDef, enclosure, repeaters, repeatersOriginal) {
    'use strict'
    const contextObj = new IMLibContext(contextDef.name)
    contextObj.contextDefinition = contextDef
    contextObj.enclosureNode = enclosure
    contextObj.repeaterNodes = repeaters
    contextObj.original = repeatersOriginal
    contextObj.sequencing = true
    return contextObj
  },

  getPagingContext: function () {
    'use strict'
    if (this.poolingContexts) {
      for (let i = 0; i < this.poolingContexts.length; i += 1) {
        const context = this.poolingContexts[i]
        const contextDef = context.getContextDef()
        if (contextDef.paging) {
          return context
        }
      }
    }
    return null
  },

  getNearestContext: function (target, contextName) {
    const xpTarget = getXpath(target)
    let isFirstTime = true
    let maxDistance = 0
    let maxContext = null
    for (const context of this.poolingContexts) {
      if (context.contextName === contextName) {
        const xpEnclosure = getXpath(context.enclosureNode)
        const distance = commonLength(xpTarget, xpEnclosure)
        if (isFirstTime || distance > maxDistance) {
          maxDistance = distance
          maxContext = context
          isFirstTime = false
        }
      }
    }
    return maxContext

    function commonLength(path1, path2) {
      const maxLen = Math.min(path1.length, path2.length)
      for (let i = 0; i < maxLen; i += 1) {
        if (path1.substring(i, i + 1) !== path2.substring(i, i + 1)) {
          return i;
        }
      }
      return maxLen;
    }

    // Thanks for https://qiita.com/narikei/items/fb62b543ca386fcee211
    function getXpath(element) {
      if (element && element.parentNode) {
        let i;
        let xpath = getXpath(element.parentNode) + '/' + element.tagName;
        const s = [];

        for (i = 0; i < element.parentNode.childNodes.length; i++) {
          const e = element.parentNode.childNodes[i];
          if (e.tagName === element.tagName) {
            s.push(e);
          }
        }

        if (1 < s.length) {
          for (i = 0; i < s.length; i++) {
            if (s[i] === element) {
              xpath += '[' + (i + 1) + ']';
              break;
            }
          }
        }

        return xpath.toLowerCase();
      } else {
        return '';
      }
    }
  }
}

// @@IM@@IgnoringRestOfFile
module.exports = IMLibContextPool
