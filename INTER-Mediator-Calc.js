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
var IMLibCalc = {
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
    updateCalculationInfo: function (contextObj, keyingValue, currentContext, nodeId, nInfo, currentRecord) {
        var calcDef, exp, field, elements, i, index, objectKey, itemIndex, values, referes,
            calcDefField, atPos, fieldLength;

        calcDef = currentContext['calculation'];
        for (index in calcDef) {
            atPos = calcDef[index]['field'].indexOf(INTERMediator.separator);
            fieldLength = calcDef[index]['field'].length;
            calcDefField = calcDef[index]['field'].substring(0, atPos >= 0 ? atPos : fieldLength);
            if (calcDefField == nInfo['field']) {
                try {
                    exp = calcDef[index]['expression'];
                    field = calcDef[index]['field'];
                    elements = Parser.parse(exp).variables();
                    objectKey = nodeId +
                        (nInfo.target.length > 0 ? (INTERMediator.separator + nInfo.target) : '');
                } catch (ex) {
                    INTERMediator.setErrorMessage(ex,
                        INTERMediatorLib.getInsertedString(
                            INTERMediatorOnPage.getMessages()[1036], [field, exp]));
                }
                if (elements && objectKey) {
                    values = {};
                    referes = {};
                    for (i = 0; i < elements.length; i++) {
                        itemIndex = elements[i];
                        if (itemIndex) {
                            values[itemIndex] = [currentRecord[itemIndex]];
                            referes[itemIndex] = [undefined];
                        }
                        contextObj.setValue(
                            keyingValue, itemIndex, currentRecord[itemIndex], nodeId, nInfo.target, null);
                    }
                    IMLibCalc.calculateRequiredObject[objectKey] = {
                        'field': field,
                        'expression': exp,
                        'nodeInfo': nInfo,
                        'values': values,
                        'referes': referes
                    };
                }
            }
        }
    },

    /**
     *
     */
    updateCalculationFields: function () {
        var nodeId, exp, nInfo, valuesArray, leafNodes, calcObject, ix, refersArray, calcFieldInfo;
        var targetNode, field, valueSeries, targetElement, i, hasReferes, contextInfo, idValue, record;

        IMLibCalc.setUndefinedToAllValues();
        IMLibNodeGraph.clear();
        for (nodeId in IMLibCalc.calculateRequiredObject) {
            calcObject = IMLibCalc.calculateRequiredObject[nodeId];
            if (calcObject) {
                hasReferes = false;
                for (field in calcObject.referes) {
                    for (ix = 0; ix < calcObject.referes[field].length; ix++) {
                        IMLibNodeGraph.addEdge(nodeId, calcObject.referes[field][ix]);
                        hasReferes = false;
                    }
                }
                if (!hasReferes) {
                    IMLibNodeGraph.addEdge(nodeId);
                }
            }
        }

        do {
            leafNodes = IMLibNodeGraph.getLeafNodesWithRemoving();
            for (i = 0; i < leafNodes.length; i++) {
                calcObject = IMLibCalc.calculateRequiredObject[leafNodes[i]];
                calcFieldInfo = INTERMediatorLib.getCalcNodeInfoArray(leafNodes[i]);
                if (calcObject) {
                    idValue = leafNodes[i].match(IMLibCalc.regexpForSeparator) ?
                        leafNodes[i].split(IMLibCalc.regexpForSeparator)[0] : leafNodes[i];
                    targetNode = document.getElementById(idValue);
                    exp = calcObject.expression;
                    nInfo = calcObject.nodeInfo;
                    valuesArray = calcObject.values;
                    refersArray = calcObject.referes;
                    for (field in valuesArray) {
                        valueSeries = [];
                        for (ix = 0; ix < valuesArray[field].length; ix++) {
                            if (valuesArray[field][ix] == undefined) {
                                if (refersArray[field][ix]) {
                                    targetElement = document.getElementById(refersArray[field][ix]);
                                    valueSeries.push(IMLibElement.getValueFromIMNode(targetElement));
                                } else {
                                    contextInfo = IMLibContextPool.getContextInfoFromId(idValue, nInfo.target);
                                    if (contextInfo && contextInfo.context) {
                                        record = contextInfo.context.getContextRecord(idValue);
                                        valueSeries.push(record[field]);
                                    }
                                }
                            } else {
                                valueSeries.push(valuesArray[field][ix]);
                            }
                        }
                        calcObject.values[field] = valueSeries;
                    }
                    IMLibElement.setValueToIMNode(targetNode, nInfo.target, Parser.evaluate(exp, valuesArray), true);
                }
            }
        } while (leafNodes.length > 0);
        if (IMLibNodeGraph.nodes.length > 0) {
            INTERMediator.setErrorMessage(new Exception(),
                INTERMediatorLib.getInsertedString(
                    INTERMediatorOnPage.getMessages()[1037], []));
        }
    },
    /**
     * On updating, the updatedNodeId should be set to the updating node id.
     * On deleting, parameter doesn't required.
     * @param updatedNodeId
     */
    recalculation: function (updatedNodeId) {
        var nodeId, newValueAdded, leafNodes, calcObject, ix, updatedValue, isRecalcAll = false;
        var newValue, field, i, updatedNodeIds, updateNodeValues, cachedIndex, exp, nInfo, valuesArray;
        var refersArray, valueSeries, targetElement, contextInfo, record, idValue;

        if (updatedNodeId === undefined) {
            isRecalcAll = true;
            updatedNodeIds = [];
            updateNodeValues = [];
        } else {
            newValue = IMLibElement.getValueFromIMNode(document.getElementById(updatedNodeId));
            updatedNodeIds = [updatedNodeId];
            updateNodeValues = [newValue];
        }

        IMLibCalc.setUndefinedToAllValues();
        IMLibNodeGraph.clear();
        for (nodeId in IMLibCalc.calculateRequiredObject) {
            calcObject = IMLibCalc.calculateRequiredObject[nodeId];
            idValue = nodeId.match(IMLibCalc.regexpForSeparator) ?
                nodeId.split(IMLibCalc.regexpForSeparator)[0] : nodeId;
            for (field in calcObject.referes) {
                for (ix = 0; ix < calcObject.referes[field].length; ix++) {
                    IMLibNodeGraph.addEdge(nodeId, calcObject.referes[field][ix]);
                }
            }
        }
        do {
            leafNodes = IMLibNodeGraph.getLeafNodesWithRemoving();
            for (i = 0; i < leafNodes.length; i++) {
                calcObject = IMLibCalc.calculateRequiredObject[leafNodes[i]];
                if (calcObject) {
                    idValue = leafNodes[i].match(IMLibCalc.regexpForSeparator) ?
                        leafNodes[i].split(IMLibCalc.regexpForSeparator)[0] : leafNodes[i];
                    exp = calcObject.expression;
                    nInfo = calcObject.nodeInfo;
                    valuesArray = calcObject.values;
                    refersArray = calcObject.referes;
                    for (field in valuesArray) {
                        valueSeries = [];
                        for (ix = 0; ix < valuesArray[field].length; ix++) {
                            if (valuesArray[field][ix] == undefined) {
                                if (refersArray[field][ix]) {
                                    targetElement = document.getElementById(refersArray[field][ix]);
                                    valueSeries.push(IMLibElement.getValueFromIMNode(targetElement));
                                } else {
                                    contextInfo = IMLibContextPool.getContextInfoFromId(idValue, nInfo.target);
                                    record = contextInfo.context.getContextRecord(idValue);
                                    valueSeries.push(record[field]);
                                }
                            } else {
                                valueSeries.push(valuesArray[field][ix]);
                            }
                        }
                        calcObject.values[field] = valueSeries;
                    }
                    if (isRecalcAll) {
                        newValueAdded = true;
                    } else {
                        newValueAdded = false;
                        for (field in calcObject.referes) {
                            for (ix = 0; ix < calcObject.referes[field].length; ix++) {
                                cachedIndex = updatedNodeIds.indexOf(calcObject.referes[field][ix]);
                                if (cachedIndex >= 0) {
                                    calcObject.values[field][ix] = updateNodeValues[cachedIndex];
                                    newValueAdded = true;
                                }
                            }
                        }
                    }
                    if (newValueAdded) {
                        //console.log('calc-test', calcObject.expression, calcObject.values);
                        updatedValue = Parser.evaluate(
                            calcObject.expression,
                            calcObject.values
                        );
                        IMLibElement.setValueToIMNode(
                            document.getElementById(idValue), nInfo.target, updatedValue, true);
                        updatedNodeIds.push(idValue);
                        updateNodeValues.push(updatedValue);
                    }
                }
            }
        } while (leafNodes.length > 0);
        if (IMLibNodeGraph.nodes.length > 0) {
            // Spanning Tree Detected.
        }

    },

    /**
     *
     */
    setUndefinedToAllValues: function () {
        var nodeId, calcObject, ix, targetNode, field, targetExp, targetIds, isRemoved, idValue, repeaterTop;

        do {
            isRemoved = false;
            for (nodeId in IMLibCalc.calculateRequiredObject) {
                idValue = nodeId.match(IMLibCalc.regexpForSeparator) ?
                    nodeId.split(IMLibCalc.regexpForSeparator)[0] : nodeId;
                if (!document.getElementById(idValue)) {
                    delete IMLibCalc.calculateRequiredObject[nodeId];
                    isRemoved = true;
                    break;
                }
            }
        } while (isRemoved);

        for (nodeId in IMLibCalc.calculateRequiredObject) {
            calcObject = IMLibCalc.calculateRequiredObject[nodeId];
            targetNode = document.getElementById(nodeId);
            for (field in calcObject.values) {
                if (field.indexOf(INTERMediator.separator) > -1) {
                    targetExp = field;
                } else {
                    targetExp = calcObject.nodeInfo.table + INTERMediator.separator + field;
                }
                if (nodeId.nodeInfo && nodeId.nodeInfo.crossTable) {
                    repeaterTop = targetNode;
                    while (repeaterTop.tagName != 'TD' && repeaterTop.tagName != 'TH') {
                        repeaterTop = repeaterTop.parentNode;
                    }
                    do {
                        targetIds = INTERMediatorOnPage.getNodeIdsHavingTargetFromNode(targetNode, targetExp);
                        if (targetIds && targetIds.length > 0) {
                            break;
                        }
                        targetNode = INTERMediatorLib.getParentRepeater(
                            INTERMediatorLib.getParentEnclosure(targetNode));
                    } while (targetNode);
                } else {
                    do {
                        targetIds = INTERMediatorOnPage.getNodeIdsHavingTargetFromRepeater(targetNode, targetExp);
                        if (targetIds && targetIds.length > 0) {
                            break;
                        }
                        targetIds = INTERMediatorOnPage.getNodeIdsHavingTargetFromEnclosure(targetNode, targetExp);
                        if (targetIds && targetIds.length > 0) {
                            break;
                        }
                        targetNode = INTERMediatorLib.getParentRepeater(
                            INTERMediatorLib.getParentEnclosure(targetNode));
                    } while (targetNode);
                }
                if (INTERMediatorLib.is_array(targetIds) && targetIds.length > 0) {
                    calcObject.referes[field] = [];
                    calcObject.values[field] = [];
                    for (ix = 0; ix < targetIds.length; ix++) {
                        calcObject.referes[field].push(targetIds[ix]);
                        calcObject.values[field].push(undefined);
                    }
                } else {
                    calcObject.referes[field] = [undefined];
                    calcObject.values[field] = [undefined];
                }
            }
        }
    }
};
