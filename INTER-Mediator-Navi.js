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
 * @fileoverview IMLibPageNavigation class is defined here.
 */
/**
 *
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
IMLibPageNavigation = {
    deleteInsertOnNavi: [],
    previousModeDetail: null,
    stepNavigation: [],
    stepCurrentContextName: null,
    stepStartContextName: null,

    /**
     * Create Navigation Bar to move previous/next page
     */

    navigationSetup: function () {
        var navigation, i, insideNav, navLabel, node, start, pageSize, allCount, disableClass, c_node,
            prevPageCount, nextPageCount, endPageCount, onNaviInsertFunction, onNaviDeleteFunction,
            onNaviCopyFunction, contextName, contextDef, buttonLabel;

        navigation = document.getElementById('IM_NAVIGATOR');
        if (navigation !== null) {
            if (!IMLibContextPool.getPagingContext()) {
                navigation.style.display = 'none';
                return;
            }
            insideNav = navigation.childNodes;
            for (i = 0; i < insideNav.length; i++) {
                navigation.removeChild(insideNav[i]);
            }
            navigation.innerHTML = '';
            navigation.setAttribute('class', 'IM_NAV_panel');
            navLabel = INTERMediator.navigationLabel;

            if (navLabel === null || navLabel[8] !== false) {
                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    ((navLabel === null || navLabel[8] === null) ? INTERMediatorOnPage.getMessages()[2] : navLabel[8])));
                node.setAttribute('class', 'IM_NAV_button');
                if (!node.id) {
                    node.id = INTERMediator.nextIdValue();
                }
                IMLibMouseEventDispatch.setExecute(node.id, function () {
                    INTERMediator.initialize();
                    IMLibLocalContext.archive();
                    location.reload();
                });
            }

            if (navLabel === null || navLabel[4] !== false) {
                start = Number(INTERMediator.startFrom);
                pageSize = Number(INTERMediator.pagedSize);
                allCount = Number(INTERMediator.pagedAllCount);
                disableClass = ' IM_NAV_disabled';
                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    ((navLabel === null || navLabel[4] === null) ?
                        INTERMediatorOnPage.getMessages()[1] : navLabel[4]) +
                    (allCount === 0 ? 0 : start + 1) +
                    ((Math.min(start + pageSize, allCount) - start > 1) ?
                        (((navLabel === null || navLabel[5] === null) ? '-' : navLabel[5]) +
                        Math.min(start + pageSize, allCount)) : '') +
                    ((navLabel === null || navLabel[6] === null) ? ' / ' : navLabel[6]) + (allCount) +
                    ((navLabel === null || navLabel[7] === null) ? '' : navLabel[7])));
                node.setAttribute('class', 'IM_NAV_info');
            }

            if ((navLabel === null || navLabel[0] !== false) && INTERMediator.pagination === true) {
                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    (navLabel === null || navLabel[0] === null) ? '<<' : navLabel[0]));
                node.setAttribute('class', 'IM_NAV_button' + (start === 0 ? disableClass : ''));
                if (!node.id) {
                    node.id = INTERMediator.nextIdValue();
                }
                IMLibMouseEventDispatch.setExecute(node.id, function () {
                    IMLibPageNavigation.moveRecordFromNavi('navimoving', 0);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    (navLabel === null || navLabel[1] === null) ? '<' : navLabel[1]));
                node.setAttribute('class', 'IM_NAV_button' + (start === 0 ? disableClass : ''));
                prevPageCount = (start - pageSize > 0) ? start - pageSize : 0;
                if (!node.id) {
                    node.id = INTERMediator.nextIdValue();
                }
                IMLibMouseEventDispatch.setExecute(node.id, function () {
                    IMLibPageNavigation.moveRecordFromNavi('navimoving', prevPageCount);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    (navLabel === null || navLabel[2] === null) ? '>' : navLabel[2]));
                node.setAttribute('class', 'IM_NAV_button' + (start + pageSize >= allCount ? disableClass : ''));
                nextPageCount =
                    (start + pageSize < allCount) ? start + pageSize : ((allCount - pageSize > 0) ? start : 0);
                if (!node.id) {
                    node.id = INTERMediator.nextIdValue();
                }
                IMLibMouseEventDispatch.setExecute(node.id, function () {
                    IMLibPageNavigation.moveRecordFromNavi('navimoving', nextPageCount);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    (navLabel === null || navLabel[3] === null) ? '>>' : navLabel[3]));
                node.setAttribute('class', 'IM_NAV_button' + (start + pageSize >= allCount ? disableClass : ''));
                if (allCount % pageSize === 0) {
                    endPageCount = allCount - (allCount % pageSize) - pageSize;
                } else {
                    endPageCount = allCount - (allCount % pageSize);
                }
                if (!node.id) {
                    node.id = INTERMediator.nextIdValue();
                }
                IMLibMouseEventDispatch.setExecute(node.id, function () {
                    IMLibPageNavigation.moveRecordFromNavi('navimoving', (endPageCount > 0) ? endPageCount : 0);
                });

                // Get from http://agilmente.com/blog/2013/08/04/inter-mediator_pagenation_1/
                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[10]));
                c_node = document.createElement('INPUT');
                c_node.setAttribute('class', 'IM_NAV_JUMP');
                c_node.setAttribute('type', 'text');
                if (!c_node.id) {
                    c_node.id = INTERMediator.nextIdValue();
                }
                c_node.setAttribute('value', Math.ceil(INTERMediator.startFrom / pageSize + 1));
                node.appendChild(c_node);
                node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[11]));
                // ---------
                IMLibChangeEventDispatch.setExecute(c_node.id, function () {
                    var moveTo, max_page;
                    moveTo = INTERMediatorLib.toNumber(c_node.value);
                    if (moveTo < 1) {
                        moveTo = 1;
                    }
                    max_page = Math.ceil(allCount / pageSize);
                    if (max_page < moveTo) {
                        moveTo = max_page;
                    }
                    INTERMediator.startFrom = ( moveTo - 1 ) * pageSize;
                    INTERMediator.constructMain(true);
                });
            }

            if (navLabel === null || navLabel[9] !== false) {
                for (i = 0; i < IMLibPageNavigation.deleteInsertOnNavi.length; i++) {
                    switch (IMLibPageNavigation.deleteInsertOnNavi[i]['kind']) {
                    case 'INSERT':
                        node = document.createElement('SPAN');
                        navigation.appendChild(node);
                        contextName = IMLibPageNavigation.deleteInsertOnNavi[i]['name'];
                        contextDef = IMLibContextPool.getContextDef(contextName);
                        if (contextDef && contextDef['button-names'] && contextDef['button-names']['insert']) {
                            buttonLabel = contextDef['button-names']['insert'];
                        } else {
                            buttonLabel = INTERMediatorOnPage.getMessages()[3] + ': ' + contextName;
                        }
                        node.appendChild(document.createTextNode(buttonLabel));
                        node.setAttribute('class', 'IM_NAV_button');
                        onNaviInsertFunction = function (a, b, c) {
                            var contextName = a, keyValue = b, confirming = c;
                            return function () {
                                IMLibPageNavigation.insertRecordFromNavi(contextName, keyValue, confirming);
                            };
                        };
                        if (!node.id) {
                            node.id = INTERMediator.nextIdValue();
                        }
                        IMLibMouseEventDispatch.setExecute(node.id,
                            onNaviInsertFunction(
                                IMLibPageNavigation.deleteInsertOnNavi[i]['name'],
                                IMLibPageNavigation.deleteInsertOnNavi[i]['key'],
                                IMLibPageNavigation.deleteInsertOnNavi[i]['confirm'])
                        );
                        break;
                    case 'DELETE':
                        node = document.createElement('SPAN');
                        navigation.appendChild(node);
                        contextName = IMLibPageNavigation.deleteInsertOnNavi[i]['name'];
                        contextDef = IMLibContextPool.getContextDef(contextName);
                        if (contextDef && contextDef['button-names'] && contextDef['button-names']['delete']) {
                            buttonLabel = contextDef['button-names']['delete'];
                        } else {
                            buttonLabel = INTERMediatorOnPage.getMessages()[4] + ': ' + contextName;
                        }
                        node.appendChild(document.createTextNode(buttonLabel));
                        node.setAttribute('class', 'IM_NAV_button');
                        onNaviDeleteFunction = function (a, b, c, d) {
                            var contextName = a, keyName = b, keyValue = c, confirming = d;
                            return function () {
                                IMLibPageNavigation.deleteRecordFromNavi(contextName, keyName, keyValue, confirming);
                            };
                        };
                        INTERMediatorLib.addEvent(
                            node,
                            'click',
                            onNaviDeleteFunction(
                                IMLibPageNavigation.deleteInsertOnNavi[i]['name'],
                                IMLibPageNavigation.deleteInsertOnNavi[i]['key'],
                                IMLibPageNavigation.deleteInsertOnNavi[i]['value'],
                                IMLibPageNavigation.deleteInsertOnNavi[i]['confirm']));
                        break;
                    case 'COPY':
                        node = document.createElement('SPAN');
                        navigation.appendChild(node);
                        contextName = IMLibPageNavigation.deleteInsertOnNavi[i]['name'];
                        contextDef = IMLibContextPool.getContextDef(contextName);
                        if (contextDef && contextDef['button-names'] && contextDef['button-names']['copy']) {
                            buttonLabel = contextDef['button-names']['copy'];
                        } else {
                            buttonLabel = INTERMediatorOnPage.getMessages()[15] + ': ' + contextName;
                        }
                        node.appendChild(document.createTextNode(buttonLabel));
                        node.setAttribute('class', 'IM_NAV_button');
                        onNaviCopyFunction = function (a, b) {
                            var contextDef = a, record = b;
                            return function () {
                                IMLibPageNavigation.copyRecordFromNavi(contextDef, record);
                            };
                        };
                        if (!node.id) {
                            node.id = INTERMediator.nextIdValue();
                        }
                        IMLibMouseEventDispatch.setExecute(node.id,
                            onNaviCopyFunction(
                                IMLibPageNavigation.deleteInsertOnNavi[i]['contextDef'],
                                IMLibPageNavigation.deleteInsertOnNavi[i]['keyValue']));
                        break;
                    }
                }
            }
            if (navLabel === null || navLabel[10] !== false) {
                if (INTERMediatorOnPage.getOptionsTransaction() === 'none') {
                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        (navLabel === null || navLabel[10] === null) ?
                            INTERMediatorOnPage.getMessages()[7] : navLabel[10]));
                    node.setAttribute('class', 'IM_NAV_button');
                    INTERMediatorLib.addEvent(node, 'click', IMLibPageNavigation.saveRecordFromNavi);
                }
            }
            if (navLabel === null || navLabel[11] !== false) {
                if (INTERMediatorOnPage.requireAuthentication) {
                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        INTERMediatorOnPage.getMessages()[8] + INTERMediatorOnPage.authUser));
                    node.setAttribute('class', 'IM_NAV_info');

                    node = document.createElement('SPAN');
                    navigation.appendChild(node);
                    node.appendChild(document.createTextNode(
                        (navLabel === null || navLabel[11] === null) ?
                            INTERMediatorOnPage.getMessages()[9] : navLabel[11]));
                    node.setAttribute('class', 'IM_NAV_button');
                    if (!node.id) {
                        node.id = INTERMediator.nextIdValue();
                    }
                    IMLibMouseEventDispatch.setExecute(node.id,
                        function () {
                            INTERMediatorOnPage.logout();
                            location.reload();
                        });
                }
            }
        }
    },

    moveRecordFromNavi: function (targetName, page) {
        INTERMediator_DBAdapter.unregister();
        INTERMediator.startFrom = page;
        INTERMediator.constructMain(true);
    },

    insertRecordFromNavi: function (targetName, keyField, isConfirm) {
        var contextDef;

        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1026])) {
                return;
            }
        }
        INTERMediatorOnPage.showProgress();
        contextDef = INTERMediatorLib.getNamedObject(
            INTERMediatorOnPage.getDataSources(), 'name', targetName);
        if (contextDef === null) {
            alert('no targetname :' + targetName);
            INTERMediatorOnPage.hideProgress();
            return;
        }

        IMLibQueue.setTask((function () {
            var conditions, restore;
            var contextDefCapt = contextDef;
            var targetNameCapt = targetName;
            var keyFieldCapt = keyField;
            var isConfirmCapt = isConfirm;
            return function (completeTask) {
                try {
                    INTERMediatorOnPage.retrieveAuthInfo();
                    INTERMediator_DBAdapter.db_createRecord_async(
                        {name: targetNameCapt, dataset: []},
                        function (response) {
                            var newId = response.newRecordKeyValue;
                            if (newId > -1) {
                                restore = INTERMediator.additionalCondition;
                                if (contextDefCapt.records <= 1) {
                                    INTERMediator.startFrom = 0;
                                    INTERMediator.pagedAllCount = 1;
                                    conditions = INTERMediator.additionalCondition;
                                    conditions[targetNameCapt] = {field: keyFieldCapt, value: newId};
                                    INTERMediator.additionalCondition = conditions;
                                    IMLibLocalContext.archive();
                                } else {
                                    INTERMediator.pagedAllCount++;
                                }
                                completeTask();
                                INTERMediator_DBAdapter.unregister();
                                INTERMediator.constructMain(true);
                                INTERMediator.additionalCondition = restore;
                                IMLibPageNavigation.navigationSetup();
                            }
                            IMLibCalc.recalculation();
                            INTERMediatorOnPage.hideProgress();
                            INTERMediator.flushMessage();

                        },
                        completeTask
                    );
                } catch (ex) {
                    completeTask();
                    if (ex == '_im_requath_request_') {
                        if (INTERMediatorOnPage.requireAuthentication) {
                            if (!INTERMediatorOnPage.isComplementAuthData()) {
                                INTERMediatorOnPage.clearCredentials();
                                INTERMediatorOnPage.authenticating(function () {
                                    IMLibPageNavigation.insertRecordFromNavi(
                                        targetNameCapt, keyFieldCapt, isConfirmCapt);
                                });
                                INTERMediator.flushMessage();
                                return;
                            }
                        }
                    } else {
                        INTERMediator.setErrorMessage(ex, 'EXCEPTION-5');
                    }
                }
            };
        })());
    },

    deleteRecordFromNavi: function (targetName, keyField, keyValue, isConfirm) {
        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1025])) {
                return;
            }
        }
        IMLibQueue.setTask((function () {
            var deleteArgs = {
                name: targetName,
                conditions: [{field: keyField, operator: '=', value: keyValue}]
            };
            return function (completeTask) {
                INTERMediatorOnPage.showProgress();
                try {
                    INTERMediatorOnPage.retrieveAuthInfo();
                    INTERMediator_DBAdapter.db_delete_async(
                        deleteArgs,
                        (function () {
                            return function () {
                                INTERMediator.pagedAllCount--;
                                INTERMediator.totalRecordCount--;
                                if (INTERMediator.pagedAllCount - INTERMediator.startFrom < 1) {
                                    INTERMediator.startFrom--;
                                    if (INTERMediator.startFrom < 0) {
                                        INTERMediator.startFrom = 0;
                                    }
                                }
                                completeTask();
                                INTERMediator.constructMain(true);
                                INTERMediatorOnPage.hideProgress();
                                INTERMediator.flushMessage();
                            };
                        })(),
                        completeTask()
                    );
                } catch (ex) {
                    INTERMediator.setErrorMessage(ex, 'EXCEPTION-6');
                    completeTask();
                }
            };
        })());
    },

    copyRecordFromNavi: function (contextDef, keyValue) {
        if (contextDef['repeat-control'].match(/confirm-copy/)) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1041])) {
                return;
            }
        }
        IMLibQueue.setTask((function () {
            var contextDefCapt = contextDef;
            var keyValueCapt = keyValue;
            return function (completeTask) {
                var assocDef, i, def, assocContexts, pStart, copyTerm, index;
                INTERMediatorOnPage.showProgress();
                try {
                    if (contextDefCapt['relation']) {
                        for (index in contextDefCapt['relation']) {
                            if (contextDefCapt['relation'][index]['portal'] === true) {
                                contextDefCapt['portal'] = true;
                            }
                        }
                    }
                    assocDef = [];
                    if (contextDefCapt['repeat-control'].match(/copy-/)) {
                        pStart = contextDefCapt['repeat-control'].indexOf('copy-');
                        copyTerm = contextDefCapt['repeat-control'].substr(pStart + 5);
                        if ((pStart = copyTerm.search(/\s/)) > -1) {
                            copyTerm = copyTerm.substr(0, pStart);
                        }
                        assocContexts = copyTerm.split(',');
                        for (i = 0; i < assocContexts.length; i++) {
                            def = IMLibContextPool.getContextDef(assocContexts[i]);
                            if (def['relation'][0]['foreign-key']) {
                                assocDef.push({
                                    name: def['name'],
                                    field: def['relation'][0]['foreign-key'],
                                    value: keyValueCapt
                                });
                            }
                        }
                    }
                    INTERMediatorOnPage.retrieveAuthInfo();
                    INTERMediator_DBAdapter.db_copy_async(
                        {
                            name: contextDefCapt['name'],
                            conditions: [{field: contextDefCapt['key'], operator: '=', value: keyValueCapt}],
                            associated: assocDef.length > 0 ? assocDef : null
                        },
                        (function () {
                            var contextDefCapt2 = contextDefCapt;
                            return function (result) {
                                var restore, conditions;
                                var newId = result.newRecordKeyValue;
                                completeTask();
                                if (newId > -1) {
                                    restore = INTERMediator.additionalCondition;
                                    INTERMediator.startFrom = 0;
                                    if (contextDefCapt2.records <= 1) {
                                        conditions = INTERMediator.additionalCondition;
                                        conditions[contextDefCapt2.name] = {field: contextDefCapt2.key, value: newId};
                                        INTERMediator.additionalCondition = conditions;
                                        IMLibLocalContext.archive();
                                    }
                                    INTERMediator_DBAdapter.unregister();
                                    INTERMediator.constructMain(true);
                                    INTERMediator.additionalCondition = restore;
                                }
                                IMLibCalc.recalculation();
                                INTERMediatorOnPage.hideProgress();
                                INTERMediator.flushMessage();
                            };
                        })(),
                        completeTask
                    );
                } catch (ex) {
                    INTERMediator.setErrorMessage(ex, 'EXCEPTION-43');
                    completeTask();
                }
            };
        })());
    },

    saveRecordFromNavi: function (dontUpdate) {
        var keying, field, keyingComp, keyingField, keyingValue, checkQueryParameter, i, initialValue,
            currentVal, fieldArray, valueArray, difference, needUpdate = true, context, updateData, response;

        INTERMediatorOnPage.showProgress();
        INTERMediatorOnPage.retrieveAuthInfo();
        for (i = 0; i < IMLibContextPool.poolingContexts.length; i++) {
            context = IMLibContextPool.poolingContexts[i];
            updateData = context.getModified();
            for (keying in updateData) {
                if (updateData.hasOwnProperty(keying)) {
                    fieldArray = [];
                    valueArray = [];
                    for (field in updateData[keying]) {
                        if (updateData[keying].hasOwnProperty(field)) {
                            fieldArray.push(field);
                            valueArray.push({field: field, value: updateData[keying][field]});
                        }
                    }
                    keyingComp = keying.split('=');
                    keyingField = keyingComp[0];
                    keyingComp.shift();
                    keyingValue = keyingComp.join('=');
                    if (!INTERMediator.ignoreOptimisticLocking) {
                        checkQueryParameter = {
                            name: context.contextName,
                            records: 1,
                            paging: false,
                            fields: fieldArray,
                            parentkeyvalue: null,
                            conditions: [
                                {field: keyingField, operator: '=', value: keyingValue}
                            ],
                            useoffset: false,
                            primaryKeyOnly: true
                        };
                        try {
                            currentVal = INTERMediator_DBAdapter.db_query(checkQueryParameter);
                        } catch (ex) {
                            if (ex == '_im_requath_request_') {
                                if (INTERMediatorOnPage.requireAuthentication && !INTERMediatorOnPage.isComplementAuthData()) {
                                    INTERMediatorOnPage.clearCredentials();
                                    INTERMediatorOnPage.authenticating(
                                        (function () {
                                            var qParam = checkQueryParameter;
                                            return function () {
                                                INTERMediator.db_query(qParam);
                                            };
                                        })()
                                    );
                                    return;
                                }
                            } else {
                                INTERMediator.setErrorMessage(ex, 'EXCEPTION-28');
                            }
                        }

                        if (currentVal.recordset === null ||
                            currentVal.recordset[0] === null) {
                            alert(INTERMediatorLib.getInsertedString(
                                INTERMediatorOnPage.getMessages()[1003], [fieldArray.join(',')]));
                            return;
                        }
                        if (currentVal.count > 1) {
                            response = confirm(INTERMediatorOnPage.getMessages()[1024]);
                            if (!response) {
                                return;
                            }
                        }

                        difference = false;
                        for (field in updateData[keying]) {
                            if (updateData[keying].hasOwnProperty(field)) {
                                initialValue = context.getValue(keying, field);
                                if (initialValue != currentVal.recordset[0][field]) {
                                    difference += INTERMediatorLib.getInsertedString(
                                        INTERMediatorOnPage.getMessages()[1035], [
                                            field,
                                            currentVal.recordset[0][field],
                                            updateData[keying][field]
                                        ]);
                                }
                            }
                        }
                        if (difference !== false) {
                            if (!confirm(INTERMediatorLib.getInsertedString(
                                    INTERMediatorOnPage.getMessages()[1034], [difference]))) {
                                return;
                            }
                            INTERMediatorOnPage.retrieveAuthInfo(); // This is required. Why?
                        }
                    }

                    try {
                        INTERMediator_DBAdapter.db_update({
                            name: context.contextName,
                            conditions: [
                                {field: keyingField, operator: '=', value: keyingValue}
                            ],
                            dataset: valueArray
                        });

                    } catch (ex) {
                        if (ex == '_im_requath_request_') {
                            if (INTERMediatorOnPage.requireAuthentication && !INTERMediatorOnPage.isComplementAuthData()) {
                                INTERMediatorOnPage.clearCredentials();
                                INTERMediatorOnPage.authenticating(
                                    function () {
                                        IMLibPageNavigation.saveRecordFromNavi(dontUpdate);
                                    }
                                );
                                return;
                            }
                        } else {
                            INTERMediator.setErrorMessage(ex, 'EXCEPTION-29');
                        }
                    }
                    context.clearModified();
                }
            }
        }
        if (needUpdate && (dontUpdate !== true)) {
            INTERMediator.constructMain(true);
        }
        INTERMediatorOnPage.hideProgress();
        INTERMediator.flushMessage();
    },

    setupCopyButton: function (encNodeTag, repNodeTag, repeaters, currentContext, currentRecord) {
        // Handling Copy buttons
        var buttonNode, thisId, tdNodes, tdNode, buttonName, currentContextDef;

        currentContextDef = currentContext.getContextDef();
        if (!currentContextDef['repeat-control']
            || !currentContextDef['repeat-control'].match(/copy/i)) {
            return;
        }
        if (currentContextDef['relation']
            || currentContextDef['records'] === undefined
            || !currentContextDef['paging']
            || (currentContextDef['records'] > 1 && Number(INTERMediator.pagedSize) !== 1)) {
            buttonNode = document.createElement('BUTTON');
            INTERMediatorLib.setClassAttributeToNode(buttonNode, 'IM_Button_Copy');
            buttonName = INTERMediatorOnPage.getMessages()[14];
            if (currentContextDef['button-names'] && currentContextDef['button-names']['copy']) {
                buttonName = currentContextDef['button-names']['copy'];
            }
            buttonNode.appendChild(document.createTextNode(buttonName));
            thisId = 'IM_Button_' + INTERMediator.buttonIdNum;
            buttonNode.setAttribute('id', thisId);
            INTERMediator.buttonIdNum++;
            IMLibMouseEventDispatch.setExecute(thisId, (function () {
                var currentContextCapt = currentContext,
                    currentRecordCapt = currentRecord[currentContextDef['key']];
                return function () {
                    IMLibUI.copyButton(currentContextCapt, currentRecordCapt);
                };
            })());
            switch (encNodeTag) {
            case 'TBODY':
                tdNodes = repeaters[repeaters.length - 1].getElementsByTagName('TD');
                tdNode = tdNodes[tdNodes.length - 1];
                tdNode.appendChild(buttonNode);
                break;
            case 'SELECT':
                break;
            default:
                if (repeaters[0] && repeaters[0].childNodes) {
                    repeaters[repeaters.length - 1].appendChild(buttonNode);
                } else {
                    repeaters.push(buttonNode);
                }
                break;
            }
        } else {
            IMLibPageNavigation.deleteInsertOnNavi.push({
                kind: 'COPY',
                name: currentContextDef['name'],
                contextDef: currentContextDef,
                keyValue: currentRecord[currentContextDef['key']]
            });
        }
    },

    /* --------------------------------------------------------------------

     */
    setupDeleteButton: function (encNodeTag, repeaters, currentContext, keyField, keyValue) {
        // Handling Delete buttons
        var buttonNode, thisId, tdNodes, tdNode, buttonName, currentContextDef;

        currentContextDef = currentContext.contextDefinition;
        if (!currentContextDef['repeat-control']
            || !currentContextDef['repeat-control'].match(/delete/i)) {
            return;
        }
        if (currentContextDef['relation']
            || currentContextDef['records'] === undefined
            || !currentContextDef['paging']
            || (currentContextDef['records'] > 1 && Number(INTERMediator.pagedSize) !== 1)) {

            buttonNode = document.createElement('BUTTON');
            INTERMediatorLib.setClassAttributeToNode(buttonNode, 'IM_Button_Delete');
            buttonName = INTERMediatorOnPage.getMessages()[6];
            if (currentContextDef['button-names'] && currentContextDef['button-names']['delete']) {
                buttonName = currentContextDef['button-names']['delete'];
            }
            buttonNode.appendChild(document.createTextNode(buttonName));
            thisId = 'IM_Button_' + INTERMediator.buttonIdNum;
            buttonNode.setAttribute('id', thisId);
            INTERMediator.buttonIdNum++;
            IMLibMouseEventDispatch.setExecute(thisId, (function () {
                var currentContextCapt = currentContext,
                    keyFieldCapt = keyField,
                    keyValueCapt = keyValue,
                    confirmingCapt = currentContextDef['repeat-control'].match(/confirm-delete/i);
                return function () {
                    IMLibUI.deleteButton(currentContextCapt, keyFieldCapt, keyValueCapt, confirmingCapt);
                };
            })());
            switch (encNodeTag) {
            case 'TBODY':
                tdNodes = repeaters[repeaters.length - 1].getElementsByTagName('TD');
                tdNode = tdNodes[tdNodes.length - 1];
                tdNode.appendChild(buttonNode);
                break;
            case 'SELECT':
                // OPTION tag can't contain any other tags.
                break;
            default:
                if (repeaters[0] && repeaters[0].childNodes) {
                    repeaters[repeaters.length - 1].appendChild(buttonNode);
                } else {
                    repeaters.push(buttonNode);
                }
                break;
            }
        } else {
            IMLibPageNavigation.deleteInsertOnNavi.push({
                kind: 'DELETE',
                name: currentContextDef['name'],
                key: keyField,
                value: keyValue,
                confirm: currentContextDef['repeat-control'].match(/confirm-delete/i)
            });
        }
    },

    /* --------------------------------------------------------------------

     */
    setupInsertButton: function (currentContext, keyValue, node, relationValue) {
        var buttonNode, enclosedNode, footNode, trNode, tdNode, liNode, divNode, i,
            firstLevelNodes, targetNodeTag, existingButtons, keyField, thisId, encNodeTag,
            buttonName, setTop, currentContextDef;

        encNodeTag = node.tagName;
        currentContextDef = currentContext.getContextDef();
        if (currentContextDef['repeat-control'] && currentContextDef['repeat-control'].match(/insert/i)) {
            if (relationValue.length > 0 || !currentContextDef['paging'] || currentContextDef['paging'] === false) {
                buttonNode = document.createElement('BUTTON');
                INTERMediatorLib.setClassAttributeToNode(buttonNode, 'IM_Button_Insert');
                buttonName = INTERMediatorOnPage.getMessages()[5];
                if (currentContextDef['button-names'] && currentContextDef['button-names']['insert']) {
                    buttonName = currentContextDef['button-names']['insert'];
                }
                buttonNode.appendChild(document.createTextNode(buttonName));
                thisId = 'IM_Button_' + INTERMediator.buttonIdNum;
                buttonNode.setAttribute('id', thisId);
                INTERMediator.buttonIdNum++;
                switch (encNodeTag) {
                case 'TBODY':
                    setTop = false;
                    targetNodeTag = 'TFOOT';
                    if (currentContextDef['repeat-control'].match(/top/i)) {
                        targetNodeTag = 'THEAD';
                        setTop = true;
                    }
                    enclosedNode = node.parentNode;
                    firstLevelNodes = enclosedNode.childNodes;
                    footNode = null;
                    for (i = 0; i < firstLevelNodes.length; i++) {
                        if (firstLevelNodes[i].tagName === targetNodeTag) {
                            footNode = firstLevelNodes[i];
                            break;
                        }
                    }
                    if (footNode === null) {
                        footNode = document.createElement(targetNodeTag);
                        enclosedNode.appendChild(footNode);
                    }
                    existingButtons = INTERMediatorLib.getElementsByClassName(footNode, 'IM_Button_Insert');
                    if (existingButtons.length === 0) {
                        trNode = document.createElement('TR');
                        INTERMediatorLib.setClassAttributeToNode(trNode, 'IM_Insert_TR');
                        tdNode = document.createElement('TD');
                        tdNode.setAttribute('colspan', 100);
                        INTERMediatorLib.setClassAttributeToNode(tdNode, 'IM_Insert_TD');
                        INTERMediator.setIdValue(trNode);
                        if (setTop && footNode.childNodes) {
                            footNode.insertBefore(trNode, footNode.childNodes[0]);
                        } else {
                            footNode.appendChild(trNode);
                        }
                        trNode.appendChild(tdNode);
                        tdNode.appendChild(buttonNode);
                    }
                    break;
                case 'UL':
                case 'OL':
                    liNode = document.createElement('LI');
                    existingButtons = INTERMediatorLib.getElementsByClassName(liNode, 'IM_Button_Insert');
                    if (existingButtons.length === 0) {
                        liNode.appendChild(buttonNode);
                        if (currentContextDef['repeat-control'].match(/top/i)) {
                            node.insertBefore(liNode, node.firstChild);
                        } else {
                            node.appendChild(liNode);
                        }
                    }
                    break;
                case 'SELECT':
                    // Select enclosure can't include Insert button.
                    break;
                default:
                    divNode = document.createElement('DIV');
                    existingButtons = INTERMediatorLib.getElementsByClassName(divNode, 'IM_Button_Insert');
                    if (existingButtons.length === 0) {
                        divNode.appendChild(buttonNode);
                        if (currentContextDef['repeat-control'].match(/top/i)) {
                            node.insertBefore(divNode, node.firstChild);
                        } else {
                            node.appendChild(divNode);
                        }
                    }
                    break;
                }
                IMLibMouseEventDispatch.setExecute(buttonNode.id, (function () {
                    var context = currentContext,
                        keyValueCapt = keyValue,
                        relationValueCapt = relationValue,
                        nodeId = node.getAttribute('id'),
                        confirming = currentContextDef['repeat-control'].match(/confirm-insert/i);
                    return function () {
                        IMLibUI.insertButton(context, keyValueCapt, relationValueCapt, nodeId, confirming);
                    };
                })());
            } else {
                if (INTERMediatorOnPage.dbClassName === 'DB_FileMaker_FX'
                    || INTERMediatorOnPage.dbClassName === 'DB_FileMaker_DataAPI') {
                    keyField = currentContextDef['key'] ? currentContextDef['key'] : INTERMediatorOnPage.defaultKeyName;
                } else {
                    keyField = currentContextDef['key'] ? currentContextDef['key'] : 'id';
                }
                IMLibPageNavigation.deleteInsertOnNavi.push({
                    kind: 'INSERT',
                    name: currentContextDef['name'],
                    key: keyField,
                    confirm: currentContextDef['repeat-control'].match(/confirm-insert/i)
                });
            }
        }
    },

    /* --------------------------------------------------------------------

     */
    setupNavigationButton: function (encNodeTag, repeaters, currentContextDef, keyField, keyValue, contextObj) {
        // Handling Detail buttons
        var buttonNode, thisId, tdNodes, tdNode, firstInNode, isMasterDetail, isStep, isHide, masterContext,
            detailContext, showingNode, isHidePageNavi, buttonName, i, isTouchRepeater, moveToDetailFunc;

        if (!currentContextDef['navi-control'] ||
            (!currentContextDef['navi-control'].match(/master/i) &&
            !currentContextDef['navi-control'].match(/step/i)) ||
            encNodeTag === 'SELECT') {
            return;
        }

        isTouchRepeater = INTERMediator.isMobile || INTERMediator.isTablet;
        isHide = currentContextDef['navi-control'].match(/hide/i);
        isHidePageNavi = isHide && (currentContextDef['paging'] === true);
        isMasterDetail = currentContextDef['navi-control'].match(/master/i);
        isStep = currentContextDef['navi-control'].match(/step/i);

        if (isMasterDetail && INTERMediator.detailNodeOriginalDisplay) {
            detailContext = IMLibContextPool.getDetailContext();
            if (detailContext) {
                showingNode = detailContext.enclosureNode;
                if (showingNode.tagName === 'TBODY') {
                    showingNode = showingNode.parentNode;
                }
                INTERMediator.detailNodeOriginalDisplay = showingNode.style.display;
            }
        }

        buttonNode = document.createElement('BUTTON');
        INTERMediatorLib.setClassAttributeToNode(buttonNode, 'IM_Button_Master');
        buttonName = INTERMediatorOnPage.getMessages()[12];
        if (currentContextDef['button-names'] && currentContextDef['button-names']['navi-detail']) {
            buttonName = currentContextDef['button-names']['navi-detail'];
        }
        buttonNode.appendChild(document.createTextNode(buttonName));
        thisId = 'IM_Button_' + INTERMediator.buttonIdNum;
        buttonNode.setAttribute('id', thisId);
        INTERMediator.buttonIdNum++;
        if (isMasterDetail) {
            masterContext = IMLibContextPool.getMasterContext();
            masterContext.setValue(keyField + '=' + keyValue, '_im_button_master_id', thisId, thisId);
        }
        if (isMasterDetail) {
            moveToDetailFunc = IMLibPageNavigation.moveToDetail(
                encNodeTag, keyField, keyValue, isHide, isHidePageNavi);
        }
        if (isStep) {
            moveToDetailFunc = IMLibPageNavigation.moveToNextStep(contextObj, keyField, keyValue);
        }
        if (isTouchRepeater) {
            for (i = 0; i < repeaters.length; i++) {
                var originalColor = repeaters[i].style.backgroundColor;
                INTERMediator.eventListenerPostAdding.push({
                    'id': repeaters[i].id,
                    'event': 'touchstart',
                    'todo': (function () {
                        var targetNode = repeaters[i];
                        return function () {
                            IMLibEventResponder.touchEventCancel = false;
                            targetNode.style.backgroundColor = IMLibUI.mobileSelectionColor;
                        };
                    })()
                });
                INTERMediator.eventListenerPostAdding.push({
                    'id': repeaters[i].id,
                    'event': 'touchend',
                    'todo': (function () {
                        var targetNode = repeaters[i];
                        var orgColor = originalColor;
                        return function () {
                            targetNode.style.backgroundColor = orgColor;
                            if (!IMLibEventResponder.touchEventCancel) {
                                IMLibEventResponder.touchEventCancel = false;
                                moveToDetailFunc();
                            }
                        };
                    })()
                });
                INTERMediator.eventListenerPostAdding.push({
                    'id': repeaters[i].id,
                    'event': 'touchmove',
                    'todo': (function () {
                        return function () {
                            IMLibEventResponder.touchEventCancel = true;
                        };
                    })()
                });
                INTERMediator.eventListenerPostAdding.push({
                    'id': repeaters[i].id,
                    'event': 'touchcancel',
                    'todo': (function () {
                        return function () {
                            IMLibEventResponder.touchEventCancel = true;
                        };
                    })()
                });
            }
        } else {
            IMLibMouseEventDispatch.setExecute(thisId, moveToDetailFunc);
            switch (encNodeTag) {
            case 'TBODY':
                tdNodes = repeaters[repeaters.length - 1].getElementsByTagName('TD');
                tdNode = tdNodes[0];
                firstInNode = tdNode.childNodes[0];
                if (firstInNode) {
                    tdNode.insertBefore(buttonNode, firstInNode);
                } else {
                    tdNode.appendChild(buttonNode);
                }
                break;
            case 'SELECT':
                break;
            default:
                firstInNode = repeaters[repeaters.length - 1].childNodes[0];
                if (firstInNode) {
                    repeaters[repeaters.length - 1].insertBefore(buttonNode, firstInNode);
                } else {
                    repeaters[repeaters.length - 1].appendChild(buttonNode);
                }
                break;
            }
        }
    },

    getStepLastSelectedRecord: function () {
        var lastSelection = IMLibPageNavigation.stepNavigation[IMLibPageNavigation.stepNavigation.length - 1];
        return lastSelection.context.store[lastSelection.key];
    },

    isNotExpandingContext: function (contextDef) {
        if (!IMLibPageNavigation.stepCurrentContextName) {
            return false;
        }
        return IMLibPageNavigation.stepCurrentContextName !== contextDef['name'];
    },

    initializeStepInfo: function () {
        var key, dataSrcs, cDef, nodes, i;
        IMLibPageNavigation.stepNavigation = [];
        IMLibPageNavigation.stepCurrentContextName = null;
        dataSrcs = INTERMediatorOnPage.getDataSources();
        for (key in dataSrcs) {
            cDef = dataSrcs[key];
            if (cDef['navi-control'] && cDef['navi-control'].match(/step/i) && !cDef['navi-control'].match(/hide/i)) {
                IMLibPageNavigation.stepCurrentContextName = cDef['name'];
                IMLibPageNavigation.stepStartContextName = IMLibPageNavigation.stepCurrentContextName;
                nodes = document.getElementsByClassName('IM_Button_StepBack');
                for (i = 0; i < nodes.length; i++) {
                    nodes[i].style.display = 'none';
                    INTERMediatorLib.addEvent(nodes[i], 'click', function () {
                        IMLibPageNavigation.backToPreviousStep();
                    });
                }
                return;
            }
        }
    },

    moveToNextStep: function (contextObj, keyField, keyValue) {
        var context = contextObj, keying = keyField + '=' + keyValue;
        return function () {
            return IMLibPageNavigation.moveToNextSteplImpl(context, keying);
        };
    },

    moveToNextSteplImpl: function (contextObj, keying) {
        var key, cDef, dataSrcs, nodes, i, contextDef, isAfterCurrent = false;
        // IMLibPageNavigation.stepCurrentContextName = null;
        contextDef = contextObj.getContextDef();
        dataSrcs = INTERMediatorOnPage.getDataSources();
        for (key in dataSrcs) {
            cDef = dataSrcs[key];
            if (cDef['name'] === contextDef.name) {
                isAfterCurrent = true;
            } else if (isAfterCurrent && cDef['navi-control'].match(/step/i)) {
                IMLibPageNavigation.stepNavigation.push({context: contextObj, key: keying});
                if (INTERMediatorOnPage[contextDef['before-move-nextstep']]) {
                    INTERMediatorOnPage[contextDef['before-move-nextstep']]();
                }
                IMLibPageNavigation.stepCurrentContextName = cDef['name'];
                if (contextObj.enclosureNode.tagName === 'TBODY') {
                    contextObj.enclosureNode.parentNode.style.display = 'none';
                } else {
                    contextObj.enclosureNode.style.display = 'none';
                }
                INTERMediator.constructMain(IMLibContextPool.contextFromName(IMLibPageNavigation.stepCurrentContextName));
                nodes = document.getElementsByClassName('IM_Button_StepBack');
                for (i = 0; i < nodes.length; i++) {
                    nodes[i].style.display = '';
                }
                return;
            }
        }
        if (INTERMediatorOnPage[contextDef['before-move-nextstep']]) {
            INTERMediatorOnPage[contextDef['before-move-nextstep']]();
        }
    },

    backToPreviousStep: function () {
        var nodes, i, currentContext, prevInfo;
        currentContext = IMLibContextPool.contextFromName(IMLibPageNavigation.stepCurrentContextName);
        prevInfo = IMLibPageNavigation.stepNavigation.pop();
        IMLibPageNavigation.stepCurrentContextName = prevInfo.context.contextName;
        if (prevInfo.context.enclosureNode.tagName === 'TBODY') {
            prevInfo.context.enclosureNode.parentNode.style.display = '';
        } else {
            prevInfo.context.enclosureNode.style.display = '';
        }
        if (IMLibPageNavigation.stepStartContextName === IMLibPageNavigation.stepCurrentContextName) {
            nodes = document.getElementsByClassName('IM_Button_StepBack');
            for (i = 0; i < nodes.length; i++) {
                nodes[i].style.display = 'none';
            }
        }
        INTERMediator.constructMain(currentContext);
        INTERMediator.constructMain(prevInfo.context);
    },

    moveToDetail: function (encNodeTag, keyField, keyValue, isHide, isHidePageNavi) {
        var f = keyField, v = keyValue, etag = encNodeTag, mh = isHide, pnh = isHidePageNavi;

        return function () {
            return IMLibPageNavigation.moveToDetailImpl(etag, f, v, mh, pnh);
        };
    },

    moveToDetailImpl: function (encNodeTag, keyField, keyValue, isHide, isHidePageNavi) {
        var masterContext, detailContext, contextName, masterEnclosure, detailEnclosure, node, contextDef;

        IMLibPageNavigation.previousModeDetail = {
            encNodeTag: encNodeTag,
            keyField: keyField,
            keyValue: keyValue,
            isHide: isHide,
            isHidePageNavi: isHidePageNavi
        };

        masterContext = IMLibContextPool.getMasterContext();
        detailContext = IMLibContextPool.getDetailContext();
        if (detailContext) {
            if (INTERMediatorOnPage.naviBeforeMoveToDetail) {
                INTERMediatorOnPage.naviBeforeMoveToDetail(masterContext, detailContext);
            }
            contextDef = detailContext.getContextDef();
            contextName = contextDef.name;
            INTERMediator.clearCondition(contextName, '_imlabel_crosstable');
            INTERMediator.addCondition(contextName, {
                field: keyField,
                operator: '=',
                value: keyValue
            }, undefined, '_imlabel_crosstable');
            INTERMediator.constructMain(detailContext);
            INTERMediator.clearCondition(contextName);
            if (isHide) {
                INTERMediatorOnPage.masterScrollPosition = {x: window.scrollX, y: window.scrollY};
                window.scrollTo(0, 0);
                masterEnclosure = masterContext.enclosureNode;
                if (encNodeTag === 'TBODY') {
                    masterEnclosure = masterEnclosure.parentNode;
                }
                INTERMediator.masterNodeOriginalDisplay = masterEnclosure.style.display;
                masterEnclosure.style.display = 'none';

                detailEnclosure = detailContext.enclosureNode;
                if (detailEnclosure.tagName === 'TBODY') {
                    detailEnclosure = detailEnclosure.parentNode;
                }
                detailEnclosure.style.display = INTERMediator.detailNodeOriginalDisplay;
            }
            if (isHidePageNavi) {
                document.getElementById('IM_NAVIGATOR').style.display = 'none';
            }
            if (IMLibUI.mobileNaviBackButtonId) {
                node = document.getElementById(IMLibUI.mobileNaviBackButtonId);
                node.style.display = 'inline-block';
            }
            if (INTERMediatorOnPage.naviAfterMoveToDetail) {
                masterContext = IMLibContextPool.getMasterContext();
                detailContext = IMLibContextPool.getDetailContext();
                INTERMediatorOnPage.naviAfterMoveToDetail(masterContext, detailContext);
            }
        }
    },

    setupDetailAreaToFirstRecord: function (currentContextDef, masterContext) {
        var i, comp;
        if (currentContextDef['navi-control']
            && currentContextDef['navi-control'].match(/master/i)) {
            var contextDefs = INTERMediatorOnPage.getDataSources();
            for (i in contextDefs) {
                if (contextDefs.hasOwnProperty(i) &&
                    contextDefs[i] &&
                    contextDefs[i]['name'] &&
                    contextDefs[i]['navi-control'] &&
                    contextDefs[i]['navi-control'].match(/detail/i)) {
                    if (Object.keys(masterContext.store).length > 0) {
                        comp = Object.keys(masterContext.store)[0].split('=');
                        if (comp.length > 1) {
                            INTERMediator.clearCondition(contextDefs[i]['name'], '_imlabel_crosstable');
                            INTERMediator.addCondition(contextDefs[i]['name'],
                                {field: comp[0], operator: '=', value: comp[1]},
                                undefined, '_imlabel_crosstable'
                            );
                        }
                    }
                }
            }
        }
    },

    moveDetailOnceAgain: function () {
        var p = IMLibPageNavigation.previousModeDetail;
        IMLibPageNavigation.moveToDetailImpl(
            p.encNodeTag, p.keyField, p.keyValue, p.isHide, p.isHidePageNavi);
    },


    /* --------------------------------------------------------------------

     */
    setupBackNaviButton: function (currentContext, node) {
        var buttonNode, divNode, i, masterContext, naviControlValue, currentContextDef, showingNode,
            isHidePageNavi, isUpdateMaster, isTouchRepeater, aNode, nodes, isTop;

        currentContextDef = currentContext.getContextDef();

        if (!currentContextDef['navi-control']
            || !currentContextDef['navi-control'].match(/detail/i)) {
            return;
        }

        masterContext = IMLibContextPool.getMasterContext();
        naviControlValue = masterContext.getContextDef()['navi-control'];
        if (!naviControlValue
            || (!naviControlValue.match(/hide/i))) {
            return;
        }
        isHidePageNavi = (masterContext.getContextDef()['paging'] === true);
        isUpdateMaster = currentContextDef['navi-control'].match(/update/i);
        isTouchRepeater = INTERMediator.isMobile || INTERMediator.isTablet;
        isTop = !(currentContextDef['navi-control'].match(/bottom/i));

        showingNode = currentContext.enclosureNode;
        if (showingNode.tagName === 'TBODY') {
            showingNode = showingNode.parentNode;
        }
        if (INTERMediator.detailNodeOriginalDisplay) {
            INTERMediator.detailNodeOriginalDisplay = showingNode.style.display;
        }
        showingNode.style.display = 'none';

        if (isTouchRepeater) {
            nodes = document.getElementsByClassName('IM_Button_BackNavi');
            if (!nodes || nodes.length === 0) {
                aNode = createBackButton('DIV', currentContextDef);
                IMLibUI.mobileNaviBackButtonId = aNode.id;
                aNode.style.display = 'none';
                nodes = INTERMediatorLib.getElementsByAttributeValue(   // Check jQuery Mobile
                    document.getElementsByTagName('BODY')[0], 'data-role', isTop ? 'header' : 'footer');
                if (nodes && nodes[0]) {
                    if (nodes[0].firstChild) {
                        nodes[0].insertBefore(aNode, nodes[0].firstChild);
                    } else {
                        nodes[0].appendChild(aNode);
                    }
                } else {   // If the page doesn't use JQuery Mobile
                    switch (node.tagName) {
                    case 'TBODY':
                        tbodyTargetNode(node, isTop, aNode);
                        break;
                    case 'UL':
                    case 'OL':
                        genericTargetNode(node, isTop, 'LI', aNode);
                        break;
                    case 'SELECT':
                        break;
                    default:
                        genericTargetNode(node, isTop, 'DIV', aNode);
                        break;
                    }
                }
                if (!aNode.id) {
                    aNode.id = INTERMediator.nextIdValue();
                }
                INTERMediator.eventListenerPostAdding.push({
                    'id': aNode.id,
                    'event': 'touchstart',
                    'todo': moveToMaster(
                        masterContext, currentContext, isHidePageNavi, isUpdateMaster)
                });
                //
                //
                // IMLibMouseEventDispatch.setExecute(aNode.id,
                //     moveToMaster(masterContext, currentContext, isHidePageNavi, isUpdateMaster)
                // );
            }
        } else {
            buttonNode = createBackButton('BUTTON', currentContextDef);
            switch (node.tagName) {
            case 'TBODY':
                tbodyTargetNode(node, isTop, buttonNode);
                break;
            case 'UL':
            case 'OL':
                genericTargetNode(node, isTop, 'LI', buttonNode);
                break;
            case 'SELECT':
                break;
            default:
                genericTargetNode(node, isTop, 'DIV', buttonNode);
                break;
            }
            INTERMediatorLib.addEvent(
                buttonNode,
                'click',
                moveToMaster(masterContext, currentContext, isHidePageNavi, isUpdateMaster)
            );
        }

        function createBackButton(tagName, currentContextDef) {
            var buttonNode, buttonName;
            buttonNode = document.createElement(tagName);
            INTERMediatorLib.setClassAttributeToNode(buttonNode, 'IM_Button_BackNavi');
            buttonName = INTERMediatorOnPage.getMessages()[13];
            if (currentContextDef['button-names'] && currentContextDef['button-names']['navi-back']) {
                buttonName = currentContextDef['button-names']['navi-back'];
            }
            buttonNode.appendChild(document.createTextNode(buttonName));
            setIdForIMButtons(buttonNode);
            return buttonNode;
        }

        function setIdForIMButtons(node) {
            var thisId;
            thisId = 'IM_Button_' + INTERMediator.buttonIdNum;
            node.setAttribute('id', thisId);
            INTERMediator.buttonIdNum++;
        }

        function tbodyTargetNode(node, isTop, buttonNode) {
            var targetNodeTag, enclosedNode, firstLevelNodes, targetNode, existingButtons, trNode, tdNode;

            targetNodeTag = isTop ? 'THEAD' : 'TFOOT';
            enclosedNode = node.parentNode;
            firstLevelNodes = enclosedNode.childNodes;
            targetNode = null;
            for (i = 0; i < firstLevelNodes.length; i++) {
                if (firstLevelNodes[i].tagName === targetNodeTag) {
                    targetNode = firstLevelNodes[i];
                    break;
                }
            }
            if (targetNode === null) {
                targetNode = document.createElement(targetNodeTag);
                INTERMediator.appendingNodesAtLast.push({
                    targetNode: targetNode,
                    parentNode: enclosedNode,
                    siblingNode: (targetNodeTag === 'THEAD') ? enclosedNode.firstChild : null
                });
            }
            existingButtons = INTERMediatorLib.getElementsByClassName(targetNode, 'IM_Button_BackNavi');
            if (existingButtons.length === 0) {
                trNode = document.createElement('TR');
                INTERMediatorLib.setClassAttributeToNode(trNode, 'IM_NaviBack_TR');
                tdNode = document.createElement('TD');
                tdNode.setAttribute('colspan', 100);
                INTERMediatorLib.setClassAttributeToNode(tdNode, 'IM_NaviBack_TD');
                INTERMediator.setIdValue(trNode);
                targetNode.appendChild(trNode);
                trNode.appendChild(tdNode);
                tdNode.appendChild(buttonNode);
            }
        }

        function genericTargetNode(node, isTop, naviEncTag, buttonNode) {
            var newNode, existingButtons;
            newNode = document.createElement(naviEncTag);
            existingButtons = INTERMediatorLib.getElementsByClassName(divNode, 'IM_Button_BackNavi');
            if (existingButtons.length === 0) {
                newNode.appendChild(buttonNode);
                if (!isTop) {
                    node.appendChild(newNode);
                } else {
                    node.insertBefore(newNode, node.firstChild);
                }
            }
        }

        function moveToMaster(a, b, c, d) {
            var masterContextCL = a, detailContextCL = b, pageNaviShow = c, masterUpdate = d, node;
            return function () {
                var showingNode;
                if (INTERMediatorOnPage.naviBeforeMoveToMaster) {
                    INTERMediatorOnPage.naviBeforeMoveToMaster(masterContextCL, detailContextCL);
                }
                showingNode = detailContextCL.enclosureNode;
                if (showingNode.tagName === 'TBODY') {
                    showingNode = showingNode.parentNode;
                }
                showingNode.style.display = 'none';

                showingNode = masterContextCL.enclosureNode;
                if (showingNode.tagName === 'TBODY') {
                    showingNode = showingNode.parentNode;
                }
                showingNode.style.display = INTERMediator.masterNodeOriginalDisplay;

                if (pageNaviShow) {
                    document.getElementById('IM_NAVIGATOR').style.display = 'block';
                }
                if (masterUpdate) {
                    INTERMediator.constructMain(masterContextCL);
                }
                if (IMLibUI.mobileNaviBackButtonId) {
                    node = document.getElementById(IMLibUI.mobileNaviBackButtonId);
                    node.style.display = 'none';
                }
                if (INTERMediatorOnPage.naviAfterMoveToMaster) {
                    masterContextCL = IMLibContextPool.getMasterContext();
                    detailContextCL = IMLibContextPool.getDetailContext();
                    INTERMediatorOnPage.naviAfterMoveToMaster(masterContextCL, detailContextCL);
                }
                if (INTERMediatorOnPage.masterScrollPosition) {
                    window.scrollTo(
                        INTERMediatorOnPage.masterScrollPosition.x,
                        INTERMediatorOnPage.masterScrollPosition.y);
                }
            };
        }
    }
};
