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
<<<<<<< HEAD
    calculateOnServer: "im_server",
    calculateRequiredObject: null,
    /*
     key => {    // Key is the id attribute of the node which is defined as "calcuration"
     "field":
     "expression": exp.replace(/ /g, ""),   // expression
     "nodeInfo": nInfo,     // node if object i.e. {field:.., table:.., target:..., tableidnex:....}
     "values": {}   // key=target name in expression, value=real value.
     // if value=undefined, it shows the value is calculation field
     "refers": {}
     }
=======
    /**
     * This property stores IMType_CalculateFieldDefinition objects for each calculation required nodes.
     * The property name is the id attribute of the node which bond to the calculated property
     * following 'target' which is the 3rd component of target spec of the node.
     * After calling the INTERMediator.constructMain() method, this property has to be set any array.
     * @type {IMType_VariablePropertiesClass<IMType_CalculateFieldDefinition>}
>>>>>>> INTER-Mediator/master
     */
    calculateRequiredObject: null,

<<<<<<< HEAD
    removeInvalidNodeInfo: function () {
        var objectKey;
        for (objectKey in IMLibCalc.calculateRequiredObject) {
            if (IMLibCalc.calculateRequiredObject.hasOwnProperty(objectKey)) {
                if (!document.getElementById(objectKey)) {
                    delete IMLibCalc.calculateRequiredObject[objectKey];
                }
            }
        }
    },

    updateCalculationInfo: function (currentContext, nodeId, nInfo, currentRecord) {
        var calcDef, exp, field, elements, i, index, objectKey, calcFieldInfo, itemIndex, values, referes,
=======
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
>>>>>>> INTER-Mediator/master
            calcDefField, atPos, fieldLength;


        calcDef = currentContext['calculation'];
        for (index in calcDef) {
<<<<<<< HEAD
            if (calcDef.hasOwnProperty(index)) {
                atPos = calcDef[index]["field"].indexOf("@");
                fieldLength = calcDef[index]["field"].length;
                calcDefField = calcDef[index]["field"].substring(0, atPos >= 0 ? atPos : fieldLength);
                if (calcDefField == nInfo["field"]) {
                    try {
                        exp = calcDef[index]["expression"];
                        field = calcDef[index]["field"];
                        elements = Parser.parse(exp).variables();
                        calcFieldInfo = INTERMediatorLib.getCalcNodeInfoArray(field);
                        objectKey = nodeId
                            + (calcFieldInfo.target.length > 0 ? (INTERMediator.separator + calcFieldInfo.target) : "");
                    } catch (ex) {
                        INTERMediator.setErrorMessage(ex,
                            INTERMediatorLib.getInsertedString(
                                INTERMediatorOnPage.getMessages()[1036], [field, exp]));
                    }
                    if (elements) {
                        values = {};
                        referes = {};
                        for (i = 0; i < elements.length; i++) {
                            itemIndex = elements[i];
                            if (itemIndex) {
                                values[itemIndex] = [currentRecord[itemIndex]];
                                referes[itemIndex] = [undefined];
                            }
                        }
                        IMLibCalc.calculateRequiredObject[objectKey] = {
                            "field": field,
                            "expression": exp,
                            "nodeInfo": nInfo,
                            "values": values,
                            "referes": referes
                        };
                    }
=======
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
>>>>>>> INTER-Mediator/master
                }
            }
        }
    },

    /**
     *
     */
    updateCalculationFields: function () {
        var nodeId, exp, nInfo, valuesArray, leafNodes, calcObject, ix, refersArray, calcFieldInfo;
<<<<<<< HEAD
        var targetNode, field, valueSeries, targetElement, i, hasReferes;
=======
        var targetNode, field, valueSeries, targetElement, i, hasReferes, contextInfo, idValue, record;
>>>>>>> INTER-Mediator/master

        IMLibCalc.setUndefinedToAllValues();
        IMLibNodeGraph.clear();
        for (nodeId in IMLibCalc.calculateRequiredObject) {
<<<<<<< HEAD
            if (IMLibCalc.calculateRequiredObject.hasOwnProperty(nodeId)) {
                calcObject = IMLibCalc.calculateRequiredObject[nodeId];
                if (calcObject) {
                    calcFieldInfo = INTERMediatorLib.getCalcNodeInfoArray(nodeId);
                    targetNode = document.getElementById(calcFieldInfo.field);
                    hasReferes = false;
                    for (field in calcObject.referes) {
                        if (calcObject.referes.hasOwnProperty(field)) {
                            for (ix = 0; ix < calcObject.referes[field].length; ix++) {
                                IMLibNodeGraph.addEdge(nodeId, calcObject.referes[field][ix]);
                                hasReferes = false;
                            }
                        }
                    }
                    if (!hasReferes) {
                        IMLibNodeGraph.addEdge(nodeId);
=======
            calcObject = IMLibCalc.calculateRequiredObject[nodeId];
            if (calcObject) {
                hasReferes = false;
                for (field in calcObject.referes) {
                    for (ix = 0; ix < calcObject.referes[field].length; ix++) {
                        IMLibNodeGraph.addEdge(nodeId, calcObject.referes[field][ix]);
                        hasReferes = false;
>>>>>>> INTER-Mediator/master
                    }
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
<<<<<<< HEAD
                        if (valuesArray.hasOwnProperty(field)) {
                            valueSeries = [];
                            for (ix = 0; ix < valuesArray[field].length; ix++) {
                                if (valuesArray[field][ix] == undefined) {
                                    targetElement = document.getElementById(refersArray[field][ix]);
                                    valueSeries.push(IMLibElement.getValueFromIMNode(targetElement));
                                } else {
                                    valueSeries.push(valuesArray[field][ix]);
                                }
=======
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
>>>>>>> INTER-Mediator/master
                            }
                            calcObject.values[field] = valueSeries;
                        }
                    }
<<<<<<< HEAD
                    if (exp != IMLibCalc.calculateOnServer) {
                        IMLibElement.setValueToIMNode(
                            targetNode,
                            calcFieldInfo.target.length > 0 ? calcFieldInfo.target : calcObject.nodeInfo.target,
                            Parser.evaluate(exp, valuesArray),
                            true);
                    }
                } else {

=======
                    IMLibElement.setValueToIMNode(targetNode, nInfo.target, Parser.evaluate(exp, valuesArray), true);
>>>>>>> INTER-Mediator/master
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
<<<<<<< HEAD
    recalculation: function (updatedNodeId, updateOnServer) {
        var nodeId, newValueAdded, leafNodes, calcObject, ix, calcFieldInfo, updatedValue, isRecalcAll = false;
        var targetNode, newValue, field, i, updatedNodeIds, updateNodeValues, cachedIndex, exp, nInfo, valuesArray;
        var refersArray, valueSeries, targetElement, serverSideContexts, aContext, isNoRemoved;
=======
    recalculation: function (updatedNodeId) {
        var nodeId, newValueAdded, leafNodes, calcObject, ix, updatedValue, isRecalcAll = false;
        var newValue, field, i, updatedNodeIds, updateNodeValues, cachedIndex, exp, nInfo, valuesArray;
        var refersArray, valueSeries, targetElement, contextInfo, record, idValue;
>>>>>>> INTER-Mediator/master

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
<<<<<<< HEAD
            if (IMLibCalc.calculateRequiredObject.hasOwnProperty(nodeId)) {
                calcObject = IMLibCalc.calculateRequiredObject[nodeId];
                if (IMLibCalc.calculateOnServer != calcObject.expression) {
                    calcFieldInfo = INTERMediatorLib.getCalcNodeInfoArray(nodeId);
                    targetNode = document.getElementById(calcFieldInfo.field);
                    for (field in calcObject.referes) {
                        if (calcObject.referes.hasOwnProperty(field)) {
                            for (ix = 0; ix < calcObject.referes[field].length; ix++) {
                                IMLibNodeGraph.addEdge(nodeId, calcObject.referes[field][ix]);
                            }
                        }
                    }
                }
            }
        }
        if (updatedNodeIds.length > 0) {
            do {
                leafNodes = IMLibNodeGraph.getLeafNodes();
                isNoRemoved = true;
                for (i = 0; i < leafNodes.length; i++) {
                    if (updatedNodeIds.indexOf(leafNodes[i]) < 0) {
                        IMLibNodeGraph.removeNode(leafNodes[i]);
                        isNoRemoved = false;
                    }
                }
            } while (leafNodes.length > 0 && isNoRemoved === false);
        }

        serverSideContexts = [];
        for (nodeId in IMLibCalc.calculateRequiredObject) {
            if (IMLibCalc.calculateRequiredObject.hasOwnProperty(nodeId)) {
                aContext = IMLibContextPool.getContextInfoFromId(nodeId, "").context;
                calcObject = IMLibCalc.calculateRequiredObject[nodeId];
                if (IMLibCalc.calculateOnServer == calcObject.expression && updateOnServer === true) {
                    if (serverSideContexts.indexOf(aContext) < 0) {
                        serverSideContexts.push(aContext);
                    }
=======
            calcObject = IMLibCalc.calculateRequiredObject[nodeId];
            idValue = nodeId.match(IMLibCalc.regexpForSeparator) ?
                nodeId.split(IMLibCalc.regexpForSeparator)[0] : nodeId;
            for (field in calcObject.referes) {
                for (ix = 0; ix < calcObject.referes[field].length; ix++) {
                    IMLibNodeGraph.addEdge(nodeId, calcObject.referes[field][ix]);
>>>>>>> INTER-Mediator/master
                }
            }
        }
        if (serverSideContexts.length > 0) {
            for (i = 0; i < serverSideContexts.length; i++) {
                INTERMediator.constructMain(serverSideContexts[i]);
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
<<<<<<< HEAD
                        if (valuesArray.hasOwnProperty(field)) {
                            valueSeries = [];
                            for (ix = 0; ix < valuesArray[field].length; ix++) {
                                if (valuesArray[field][ix] == undefined) {
                                    targetElement = document.getElementById(refersArray[field][ix]);
                                    valueSeries.push(IMLibElement.getValueFromIMNode(targetElement));
                                } else {
                                    valueSeries.push(valuesArray[field][ix]);
                                }
=======
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
>>>>>>> INTER-Mediator/master
                            }
                            calcObject.values[field] = valueSeries;
                        }
                    }
                    if (isRecalcAll) {
                        newValueAdded = true;
                    } else {
                        newValueAdded = false;
                        for (field in calcObject.referes) {
                            if (calcObject.referes.hasOwnProperty(field)) {
                                for (ix = 0; ix < calcObject.referes[field].length; ix++) {
                                    cachedIndex = updatedNodeIds.indexOf(calcObject.referes[field][ix]);
                                    if (cachedIndex >= 0) {
                                        calcObject.values[field][ix] = updateNodeValues[cachedIndex];
                                        newValueAdded = true;
                                    }
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
<<<<<<< HEAD
            if (IMLibCalc.calculateRequiredObject.hasOwnProperty(nodeId)) {
                calcObject = IMLibCalc.calculateRequiredObject[nodeId];
                calcFieldInfo = INTERMediatorLib.getCalcNodeInfoArray(nodeId);
                targetNode = document.getElementById(calcFieldInfo.field);
                for (field in calcObject.values) {
                    if (calcObject.values.hasOwnProperty(field)) {
                        if (field.indexOf(INTERMediator.separator) > -1) {
                            targetExp = field;
                        } else {
                            targetExp = calcObject.nodeInfo.table + INTERMediator.separator + field;
                        }
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
                        if (INTERMediatorLib.is_array(targetIds)) {
                            calcObject.referes[field] = [];
                            calcObject.values[field] = [];
                            for (ix = 0; ix < targetIds.length; ix++) {
                                calcObject.referes[field].push(targetIds[ix]);
                                calcObject.values[field].push(undefined);
                            }
                        }
=======
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
>>>>>>> INTER-Mediator/master
                    }
                } else {
                    calcObject.referes[field] = [undefined];
                    calcObject.values[field] = [undefined];
                }
            }
        }
    }
};
