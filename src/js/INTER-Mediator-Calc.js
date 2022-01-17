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
/* global IMLibContextPool, INTERMediator, INTERMediatorOnPage, IMLibMouseEventDispatch, IMLibLocalContext,
 IMLibChangeEventDispatch, INTERMediatorLib, INTERMediator_DBAdapter, IMLibQueue, IMLibPageNavigation,
 IMLibEventResponder, IMLibElement, Parser, IMLib, INTERMediatorLog, IMLibNodeGraph */

/**
 * @fileoverview IMLibCalc class is defined here.
 */

/**
 * @typedef {Object} IMType_CalculateFieldDefinition
 * @property {string} field The field name.
 * @property {string} expression The expression which is defined for this field.
 * @property {PrivateNodeInfo} nodeInfo The NodeInfo object for this target node.
 * @property {PrivateVariablePropertiesClass} values This property refers object
 * which is each property is the item name in expression, and its value is the real value.
 * If the referring field is for calculation required, the value is 'undefined.'
 * @property {PrivateVariablePropertiesClass} refers TBD
 */

/**
 *
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
const IMLibCalc = {
  /**
   * This property stores IMType_CalculateFieldDefinition objects for each calculation required nodes.
   * The property name is the id attribute of the node which bond to the calculated property
   * following 'target' which is the 3rd component of target spec of the node.
   * After calling the INTERMediator.constructMain() method, this property has to be set any array.
   * @type {IMType_VariablePropertiesClass<IMType_CalculateFieldDefinition>}
   */
  calculateRequiredObject: null,

  /**
   *
   * @param contextObj
   * @param keyingValue
   * @param currentContext
   * @param nodeId
   * @param nInfo
   * @param currentRecord
   */
  updateCalculationInfo: function (contextObj, keyingValue, nodeId, nInfo, currentRecord) {
    'use strict'
    let calcDef, exp, field, elements, i, index, objectKey, itemIndex, values, referes,
      calcDefField, atPos, fieldLength

    calcDef = contextObj.getContextDef().calculation
    for (index in calcDef) {
      if (calcDef.hasOwnProperty(index)) {
        atPos = calcDef[index].field.indexOf(INTERMediator.separator)
        fieldLength = calcDef[index].field.length
        calcDefField = calcDef[index].field.substring(0, atPos >= 0 ? atPos : fieldLength)
        if (calcDefField === nInfo.field) {
          try {
            exp = calcDef[index].expression
            field = calcDef[index].field
            elements = Parser.parse(exp).variables()
            objectKey = nodeId +
              (nInfo.target.length > 0 ? (INTERMediator.separator + nInfo.target) : '')
          } catch (ex) {
            INTERMediatorLog.setErrorMessage(ex,
              INTERMediatorLib.getInsertedString(
                INTERMediatorOnPage.getMessages()[1036], [field, exp]))
          }
          if (elements && objectKey) {
            values = {}
            referes = {}
            for (i = 0; i < elements.length; i++) {
              itemIndex = elements[i]
              if (itemIndex) {
                values[itemIndex] = currentRecord[itemIndex]
                referes[itemIndex] = undefined
              }
              contextObj.setValue(
                keyingValue, itemIndex, currentRecord[itemIndex], nodeId, nInfo.target, null)
            }
            IMLibCalc.calculateRequiredObject[objectKey] = {
              'field': field,
              'expression': exp,
              'nodeInfo': nInfo,
              'values': values,
              'referes': referes
            }
          }
        }
      }
    }
  },

  /**
   *
   */
  updateCalculationFields: function () {
    'use strict'
    let nodeId, exp, nInfo, valuesArray, leafNodes, calcObject, ix, refersArray, key, fName, vArray
    let val, targetNode, field, valueSeries, targetElement, i, hasReferes, contextInfo, idValue, record

    IMLibCalc.setUndefinedToAllValues()
    IMLibNodeGraph.clear()
    for (nodeId in IMLibCalc.calculateRequiredObject) {
      if (IMLibCalc.calculateRequiredObject.hasOwnProperty(nodeId)) {
        calcObject = IMLibCalc.calculateRequiredObject[nodeId]
        if (calcObject) {
          hasReferes = false
          for (field in calcObject.referes) {
            if (calcObject.referes.hasOwnProperty(field)) {
              for (ix = 0; ix < calcObject.referes[field].length; ix++) {
                IMLibNodeGraph.addEdge(nodeId, calcObject.referes[field][ix])
                hasReferes = false
              }
            }
          }
          if (!hasReferes) {
            IMLibNodeGraph.addEdge(nodeId)
          }
        }
      }
    }

    do {
      leafNodes = IMLibNodeGraph.getLeafNodesWithRemoving()
      for (i = 0; i < leafNodes.length; i++) {
        calcObject = IMLibCalc.calculateRequiredObject[leafNodes[i]]
        // calcFieldInfo = INTERMediatorLib.getCalcNodeInfoArray(leafNodes[i])
        if (calcObject) {
          idValue = leafNodes[i].match(IMLibCalc.regexpForSeparator) ? leafNodes[i].split(IMLibCalc.regexpForSeparator)[0] : leafNodes[i]
          targetNode = document.getElementById(idValue)
          exp = calcObject.expression
          nInfo = calcObject.nodeInfo
          valuesArray = calcObject.values
          refersArray = calcObject.referes
          contextInfo = IMLibContextPool.getContextInfoFromId(idValue, nInfo.target)
          if (contextInfo && contextInfo.context) {
            record = contextInfo.context.getContextRecord(idValue)
          } else {
            record = null
          }
          for (field in valuesArray) {
            fName = field.substr(field.indexOf('@') + 1)
            if (valuesArray.hasOwnProperty(field)) {
              vArray = []
              if (field.indexOf('@') < 0) { // In same context
                vArray.push((record && record[fName]) ? record[fName] : null)
              } else {  // Other context
                const expCName = field.substr(0, field.indexOf('@'))
                if (expCName == '_') { // Local Context
                  if (IMLibLocalContext.store.hasOwnProperty(fName)) {
                    vArray.push(IMLibLocalContext.store[fName])
                  }
                } else {
                  let hasRelation = false
                  const contexts = IMLibContextPool.getContextFromName(expCName)
                  for (const context of contexts) {
                    if (record && context.contextDefinition.relation && context.contextDefinition.relation[0]) {
                      const fValue = record[context.contextDefinition.relation[0]['join-field']]
                      const fField = context.contextDefinition.relation[0]['foreign-key']
                      if (IMLibCalc.isIncludeInRecord(context.store, fField, fValue)) {
                        for (key in context.store) {    // Collect field data from all records
                          if (context.store.hasOwnProperty(key) && context.store[key][fName]) {
                            vArray.push(context.store[key][fName])
                            hasRelation = true
                          }
                        }
                      }
                    }
                  }
                  if (!hasRelation) {
                    const context = IMLibContextPool.contextFromName(expCName)
                    if(context) {
                      for (key in context.store) {    // Collect field data from all records
                        if (context.store.hasOwnProperty(key) && context.store[key][fName]) {
                          vArray.push(context.store[key][fName])
                          hasRelation = true
                        }
                      }
                    }
                  }
                }
              }
              valuesArray[field] = vArray
            }
          }
          for (field in valuesArray) {
            if (valuesArray.hasOwnProperty(field)) {
              valueSeries = []
              for (ix = 0; ix < valuesArray[field].length; ix++) {
                if (typeof(valuesArray[field][ix]) === 'undefined') {
                  if (record[field]) {
                    valueSeries.push(record[field])
                  } else if (refersArray[field][ix]) {
                    targetElement = document.getElementById(refersArray[field][ix])
                    valueSeries.push(IMLibElement.getValueFromIMNode(targetElement))
                  }
                } else {
                  valueSeries.push(valuesArray[field][ix])
                }
              }
              calcObject.values[field] = valueSeries
            }
          }
          val = Parser.evaluate(exp, valuesArray)
          IMLibElement.setValueToIMNode(targetNode, nInfo.target, val, true)
          if (contextInfo && contextInfo.context && contextInfo.record && contextInfo.field)
            contextInfo.context.setValue(contextInfo.record, contextInfo.field, val, nodeId, targetNode, false)
        }
      }
    } while (leafNodes.length > 0)
    if (IMLibNodeGraph.nodes.length > 0) {
      INTERMediatorLog.setErrorMessage(new Error('Expressons are cyclic.'),
        INTERMediatorLib.getInsertedString(
          INTERMediatorOnPage.getMessages()[1037], []))
    }
  },

  isIncludeInRecord: function (obj, key, value) {
    if (value === '' || value == null || isNaN(value) || typeof value === 'undefined') {
      return false
    }
    for (const index of Object.keys(obj)) {
      if (obj[index] && obj[index] && obj[index][key] == value) {
        return true
      }
    }
    return false
  },

  /**
   * On updating, the updatedNodeId should be set to the updating node id.
   * On deleting, parameter doesn't required.
   * @param updatedNodeId
   */
  recalculation: function (updatedNodeId) {
    'use strict'
    let nodeId, newValueAdded, leafNodes, calcObject, ix, updatedValue, isRecalcAll = false
    let newValue, field, i, updatedNodeIds, updateNodeValues, cachedIndex, nInfo, valuesArray
    let refersArray, valueSeries, targetElement, contextInfo, record, idValue, key, fName, vArray

    if (typeof updatedNodeId === 'undefined') {
      isRecalcAll = true
      updatedNodeIds = []
      updateNodeValues = []
    } else {
      newValue = IMLibElement.getValueFromIMNode(document.getElementById(updatedNodeId))
      updatedNodeIds = [updatedNodeId]
      updateNodeValues = [newValue]
    }

    IMLibCalc.setUndefinedToAllValues()
    IMLibNodeGraph.clear()
    for (nodeId in IMLibCalc.calculateRequiredObject) {
      if (IMLibCalc.calculateRequiredObject.hasOwnProperty(nodeId)) {
        calcObject = IMLibCalc.calculateRequiredObject[nodeId]
        for (field in calcObject.referes) {
          if (calcObject.referes.hasOwnProperty(field)) {
            for (ix = 0; ix < calcObject.referes[field].length; ix++) {
              IMLibNodeGraph.addEdge(nodeId, calcObject.referes[field][ix])
            }
          }
        }
      }
    }

    do {
      leafNodes = IMLibNodeGraph.getLeafNodesWithRemoving()
      for (i = 0; i < leafNodes.length; i++) {
        calcObject = IMLibCalc.calculateRequiredObject[leafNodes[i]]
        if (calcObject) {
          idValue = leafNodes[i].match(IMLibCalc.regexpForSeparator) ? leafNodes[i].split(IMLibCalc.regexpForSeparator)[0] : leafNodes[i]
          nInfo = calcObject.nodeInfo
          valuesArray = calcObject.values
          refersArray = calcObject.referes
          contextInfo = IMLibContextPool.getContextInfoFromId(idValue, nInfo.target)
          if (contextInfo && contextInfo.context) {
            record = contextInfo.context.getContextRecord(idValue)
          } else {
            record = null
          }
          for (field in valuesArray) {
            fName = field.substr(field.indexOf('@') + 1)
            if (valuesArray.hasOwnProperty(field)) {
              vArray = []
              if (field.indexOf('@') < 0) { // In same context
                vArray.push((record && record[fName]) ? record[fName] : null)
              } else {  // Other context
                const expCName = field.substr(0, field.indexOf('@'))
                if (expCName == '_') { // Local Context
                  if (IMLibLocalContext.store.hasOwnProperty(fName)) {
                    vArray.push(IMLibLocalContext.store[fName])
                  }
                } else {
                  let hasRelation = false
                  const contexts = IMLibContextPool.getContextFromName(expCName)
                  for (const context of contexts) {
                    if (record && context.contextDefinition.relation && context.contextDefinition.relation[0]) {
                      const fValue = record[context.contextDefinition.relation[0]['join-field']]
                      const fField = context.contextDefinition.relation[0]['foreign-key']
                      if (IMLibCalc.isIncludeInRecord(context.store, fField, fValue)) {
                        for (key in context.store) {    // Collect field data from all records
                          if (context.store.hasOwnProperty(key) && context.store[key][fName]) {
                            vArray.push(context.store[key][fName])
                            hasRelation = true
                          }
                        }
                      }
                    }
                  }
                  if (!hasRelation) {
                    const context = IMLibContextPool.contextFromName(expCName)
                    for (key in context.store) {    // Collect field data from all records
                      if (context.store.hasOwnProperty(key) && context.store[key][fName]) {
                        vArray.push(context.store[key][fName])
                        hasRelation = true
                      }
                    }
                  }
                }
              }
              valuesArray[field] = vArray
            }
          }
          for (field in valuesArray) {
            if (valuesArray.hasOwnProperty(field)) {
              valueSeries = []
              for (ix = 0; ix < valuesArray[field].length; ix++) {
                if (typeof(valuesArray[field][ix]) === 'undefined') {
                  if (record[field]) {
                    valueSeries.push(record[field])
                  } else if (refersArray[field][ix]) {
                    targetElement = document.getElementById(refersArray[field][ix])
                    valueSeries.push(IMLibElement.getValueFromIMNode(targetElement))
                  }
                } else {
                  valueSeries.push(valuesArray[field][ix])
                }
              }
              calcObject.values[field] = valueSeries
            }
          }
          if (isRecalcAll) {
            newValueAdded = true
          } else {
            newValueAdded = false
            for (field in calcObject.referes) {
              if (calcObject.referes.hasOwnProperty(field)) {
                for (ix = 0; ix < calcObject.referes[field].length; ix++) {
                  cachedIndex = updatedNodeIds.indexOf(calcObject.referes[field][ix])
                  if (cachedIndex >= 0) {
                    calcObject.values[field][ix] = updateNodeValues[cachedIndex]
                    newValueAdded = true
                  }
                }
              }
            }
          }
          if (newValueAdded) {
            updatedValue = Parser.evaluate(calcObject.expression, calcObject.values)
            IMLibElement.setValueToIMNode(document.getElementById(idValue), nInfo.target, updatedValue, true)
            updatedNodeIds.push(idValue)
            updateNodeValues.push(updatedValue)
            if (contextInfo && contextInfo.context && contextInfo.record && contextInfo.field) {
              contextInfo.context.setValue(contextInfo.record, contextInfo.field, updatedValue, idValue, nInfo.target, false)
            }
          }
        }
      }
    } while (leafNodes.length > 0)

    if (IMLibNodeGraph.nodes.length > 0) {
      INTERMediatorLog.setErrorMessage(new Error('Expressions are cyclic.'),
        INTERMediatorLib.getInsertedString(
          INTERMediatorOnPage.getMessages()[1037], []))
    }

  },

  /**
   *
   */
  setUndefinedToAllValues: function () {
    'use strict'
    let isRemoved
    do {
      isRemoved = false
      for (const nodeId in IMLibCalc.calculateRequiredObject) {
        const idValue = nodeId.match(IMLibCalc.regexpForSeparator) ? nodeId.split(IMLibCalc.regexpForSeparator)[0] : nodeId
        if (!document.getElementById(idValue)) {
          delete IMLibCalc.calculateRequiredObject[nodeId]
          isRemoved = true
          break
        }
      }
    } while (isRemoved)

    for (const nodeId in IMLibCalc.calculateRequiredObject) {
      const calcObject = IMLibCalc.calculateRequiredObject[nodeId]
      const idValue = nodeId.match(IMLibCalc.regexpForSeparator) ? nodeId.split(IMLibCalc.regexpForSeparator)[0] : nodeId
      const targetNode = document.getElementById(idValue)
      let linkInfos = INTERMediatorLib.getLinkedElementInfo(targetNode)
      if (INTERMediatorLib.is_array(linkInfos)) {
        linkInfos = linkInfos[0]
      }
      const nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfos)

      let targetExp, targetIds, isContextName
      for (const field in calcObject.values) {
        if (field.indexOf(INTERMediator.separator) > -1) {
          targetExp = field
          isContextName = true
        } else {
          targetExp = calcObject.nodeInfo.table + INTERMediator.separator + field
          isContextName = false
        }
        if (nodeInfo && nodeInfo.crossTable) {
          let repeaterTop = targetNode
          while (repeaterTop.tagName !== 'TD' && repeaterTop.tagName !== 'TH') {
            repeaterTop = repeaterTop.parentNode
          }
          do {
            targetIds = INTERMediatorOnPage.getNodeIdsHavingTargetFromNode(repeaterTop, targetExp)
            if (targetIds && targetIds.length > 0) {
              break
            }
            repeaterTop = getParentRepeater(INTERMediatorLib.getParentEnclosure(repeaterTop))
          } while (repeaterTop)
        } else {
          let checkRepeater = targetNode
          do {
            targetIds = INTERMediatorOnPage.getNodeIdsHavingTargetFromRepeater(checkRepeater, targetExp)
            if (targetIds && targetIds.length > 0) {
              break
            }
            if (isContextName) {
              targetIds = INTERMediatorOnPage.getNodeIdsHavingTargetFromEnclosure(checkRepeater, targetExp)
              if (targetIds && targetIds.length > 0) {
                break
              }
            }
            checkRepeater = getParentRepeater(INTERMediatorLib.getParentEnclosure(checkRepeater))
          } while (checkRepeater)
        }
        if (INTERMediatorLib.is_array(targetIds) && targetIds.length > 0) {
          calcObject.referes[field] = []
          calcObject.values[field] = []
          for (let ix = 0; ix < targetIds.length; ix++) {
            if (typeof (targetIds[ix]) == 'string' || targetIds[ix] instanceof String) {
              calcObject.referes[field].push(targetIds[ix])
              calcObject.values[field].push(undefined)
            }
          }
        } else {
          calcObject.referes[field] = [undefined]
          calcObject.values[field] = [undefined]
        }
      }
    }

    function getParentRepeater(node) {
      let currentNode = node
      while (currentNode !== null) {
        if (INTERMediatorLib.isRepeater(currentNode, true)) {
          return currentNode
        }
        currentNode = currentNode.parentNode
      }
      return null
    }
  }
}

// @@IM@@IgnoringRestOfFile
module.exports = IMLibCalc
