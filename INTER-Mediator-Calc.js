/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

var IMLibCalc = {
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
     */

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

    updateCalculationInfo: function (currentContext, keyingValue, nodeId, nInfo, currentRecord) {
        var calcDef, exp, field, elements, i, index, objectKey, calcFieldInfo, itemIndex, values, referes,
            calcDefField, atPos, fieldLength, contextObj;

        calcDef = currentContext.getContextDef()['calculation'];
        field = null;
        exp = null;
        for (index in calcDef) {
            atPos = calcDef[index]["field"].indexOf(INTERMediator.separator);
            fieldLength = calcDef[index]["field"].length;
            calcDefField = calcDef[index]["field"].substring(0, atPos >= 0 ? atPos : fieldLength);
            if (calcDefField == nInfo["field"]) {
                try {
                    exp = calcDef[index]["expression"];
                    field = calcDef[index]["field"];
                    elements = Parser.parse(exp).variables();
                    calcFieldInfo = INTERMediatorLib.getCalcNodeInfoArray(field);
                    objectKey = nodeId +
                        (nInfo.target.length > 0 ? (INTERMediator.separator + nInfo.target) : "");
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
                        currentContext.setValue(
                            keyingValue, itemIndex, currentRecord[itemIndex], nodeId, nInfo.target, null);
                    }
                    IMLibCalc.calculateRequiredObject[objectKey] = {
                        "field": field,
                        "expression": exp,
                        "nodeInfo": nInfo,
                        "values": values,
                        "referes": referes
                    };
                }
            }
        }
    },


    updateCalculationFields: function () {
        var nodeId, exp, nInfo, valuesArray, leafNodes, calcObject, ix, refersArray, calcFieldInfo;
        var targetNode, field, valueSeries, targetElement, i, hasReferes, contextInfo, idValue, record;

        IMLibCalc.setUndefinedToAllValues();
        IMLibNodeGraph.clear();
        for (nodeId in IMLibCalc.calculateRequiredObject) {
            calcObject = IMLibCalc.calculateRequiredObject[nodeId];
            idValue = nodeId.match(IMLibCalc.regexpForSeparator) ?
                nodeId.split(IMLibCalc.regexpForSeparator)[0] : nodeId;
            if (calcObject) {
                calcFieldInfo = INTERMediatorLib.getCalcNodeInfoArray(idValue);
                targetNode = document.getElementById(calcFieldInfo.field);
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
                    targetNode = document.getElementById(calcFieldInfo.field);
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
                                    targetElement = document.getElementById(idValue);
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
                    IMLibElement.setValueToIMNode(targetNode, nInfo.target, Parser.evaluate(exp, valuesArray), true);
                } else {

                }
            }
        } while (leafNodes.length > 0);
        if (IMLibNodeGraph.nodes.length > 0) {
            INTERMediator.setErrorMessage(new Exception(),
                INTERMediatorLib.getInsertedString(
                    INTERMediatorOnPage.getMessages()[1037], []));
        }
    },
    /*
     On updating, the updatedNodeId should be set to the updating node id.
     On deleting, parameter doesn't required.
     */
    recalculation: function (updatedNodeId, updateOnServer) {
        var nodeId, newValueAdded, leafNodes, calcObject, ix, calcFieldInfo, updatedValue, isRecalcAll = false;
        var targetNode, newValue, field, i, updatedNodeIds, updateNodeValues, cachedIndex, exp, nInfo, valuesArray;
        var refersArray, valueSeries, targetElement, contextInfo, record, idValue;
        var serverSideContexts, aContext, isNoRemoved;

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
            if (IMLibCalc.calculateRequiredObject.hasOwnProperty(nodeId)) {
                calcObject = IMLibCalc.calculateRequiredObject[nodeId];
                if (IMLibCalc.calculateOnServer != calcObject.expression) {
                    idValue = nodeId.match(IMLibCalc.regexpForSeparator) ?
                        nodeId.split(IMLibCalc.regexpForSeparator)[0] : nodeId;
                    calcFieldInfo = INTERMediatorLib.getCalcNodeInfoArray(idValue);
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
                calcFieldInfo = INTERMediatorLib.getCalcNodeInfoArray(leafNodes[i]);
                if (calcObject) {
                    idValue = leafNodes[i].match(IMLibCalc.regexpForSeparator) ?
                        leafNodes[i].split(IMLibCalc.regexpForSeparator)[0] : leafNodes[i];
                    targetNode = document.getElementById(calcFieldInfo.field);
                    exp = calcObject.expression;
                    nInfo = calcObject.nodeInfo;
                    valuesArray = calcObject.values;
                    refersArray = calcObject.referes;
                    for (field in valuesArray) {
                        if (valuesArray.hasOwnProperty(field)) {
                            valueSeries = [];
                            for (ix = 0; ix < valuesArray[field].length; ix++) {
                                if (valuesArray[field][ix] == undefined) {
                                    if (refersArray[field][ix]) {
                                        targetElement = document.getElementById(refersArray[field][ix]);
                                        valueSeries.push(IMLibElement.getValueFromIMNode(targetElement));
                                    } else {
                                        targetElement = document.getElementById(idValue);
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
                        updatedValue = Parser.evaluate(
                            calcObject.expression,
                            calcObject.values
                        );
                        IMLibElement.setValueToIMNode(
                            document.getElementById(calcFieldInfo.field), nInfo.target, updatedValue, true);
                        updatedNodeIds.push(calcFieldInfo.field);
                        updateNodeValues.push(updatedValue);
                    }
                }
                else {

                }
            }
        } while (leafNodes.length > 0);
        if (IMLibNodeGraph.nodes.length > 0) {
            // Spanning Tree Detected.
        }
    },


    setUndefinedToAllValues: function () {
        var nodeId, calcObject, ix, calcFieldInfo, targetNode, field, targetExp, targetIds, isRemoved, idValue;

        do {
            isRemoved = false;
            for (nodeId in IMLibCalc.calculateRequiredObject) {
                if (IMLibCalc.calculateRequiredObject.hasOwnProperty(nodeId)) {
                    idValue = nodeId.match(IMLibCalc.regexpForSeparator) ?
                        nodeId.split(IMLibCalc.regexpForSeparator)[0] : nodeId;
                    if (!document.getElementById(idValue)) {
                        delete IMLibCalc.calculateRequiredObject[nodeId];
                        isRemoved = true;
                        break;
                    }
                }
            }
        } while (isRemoved);

        for (nodeId in IMLibCalc.calculateRequiredObject) {
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
                        else {

                            calcObject.referes[field] = [undefined];
                            calcObject.values[field] = [undefined];
                        }
                    }
                }
            }
        }
    }
};
