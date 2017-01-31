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
    /**
     * Create Navigation Bar to move previous/next page
     */

    navigationSetup: function () {
        var navigation, i, insideNav, navLabel, node, start, pageSize, allCount, disableClass, c_node,
            prevPageCount, nextPageCount, endPageCount, onNaviInsertFunction, onNaviDeleteFunction,
            onNaviCopyFunction, contextName, contextDef, buttonLabel;

        navigation = document.getElementById('IM_NAVIGATOR');
        if (navigation !== null) {
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
                INTERMediatorLib.addEvent(node, 'click', function () {
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
                INTERMediatorLib.addEvent(node, 'click', function () {
                    INTERMediator_DBAdapter.unregister();
                    INTERMediator.startFrom = 0;
                    INTERMediator.constructMain(true);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    (navLabel === null || navLabel[1] === null) ? '<' : navLabel[1]));
                node.setAttribute('class', 'IM_NAV_button' + (start === 0 ? disableClass : ''));
                prevPageCount = (start - pageSize > 0) ? start - pageSize : 0;
                INTERMediatorLib.addEvent(node, 'click', function () {
                    INTERMediator_DBAdapter.unregister();
                    INTERMediator.startFrom = prevPageCount;
                    INTERMediator.constructMain(true);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    (navLabel === null || navLabel[2] === null) ? '>' : navLabel[2]));
                node.setAttribute('class', 'IM_NAV_button' + (start + pageSize >= allCount ? disableClass : ''));
                nextPageCount =
                    (start + pageSize < allCount) ? start + pageSize : ((allCount - pageSize > 0) ? start : 0);
                INTERMediatorLib.addEvent(node, 'click', function () {
                    INTERMediator_DBAdapter.unregister();
                    INTERMediator.startFrom = nextPageCount;
                    INTERMediator.constructMain(true);
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
                INTERMediatorLib.addEvent(node, 'click', function () {
                    INTERMediator_DBAdapter.unregister();
                    INTERMediator.startFrom = (endPageCount > 0) ? endPageCount : 0;
                    INTERMediator.constructMain(true);
                });

                // Get from http://agilmente.com/blog/2013/08/04/inter-mediator_pagenation_1/
                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[10]));
                c_node = document.createElement('INPUT');
                c_node.setAttribute('class', 'IM_NAV_JUMP');
                c_node.setAttribute('type', 'text');
                c_node.setAttribute('value', Math.ceil(INTERMediator.startFrom / pageSize + 1));
                node.appendChild(c_node);
                node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[11]));
                INTERMediatorLib.addEvent(
                    c_node,
                    'change',
                    function () {
                        if (c_node.value < 1) {
                            c_node.value = 1;
                        }
                        var max_page = Math.ceil(allCount / pageSize);
                        if (max_page < c_node.value) {
                            c_node.value = max_page;
                        }
                        INTERMediator.startFrom = ( ~~c_node.value - 1 ) * pageSize;
                        INTERMediator.construct(true);
                    }
                );
                // ---------
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
                        INTERMediatorLib.addEvent(
                            node,
                            'click',
                            onNaviInsertFunction(
                                IMLibPageNavigation.deleteInsertOnNavi[i]['name'],
                                IMLibPageNavigation.deleteInsertOnNavi[i]['key'],
                                IMLibPageNavigation.deleteInsertOnNavi[i]['confirm'] ? true : false)
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
                                IMLibPageNavigation.deleteInsertOnNavi[i]['confirm'] ? true : false));
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
                        INTERMediatorLib.addEvent(
                            node,
                            'click',
                            onNaviCopyFunction(
                                IMLibPageNavigation.deleteInsertOnNavi[i]['contextDef'],
                                IMLibPageNavigation.deleteInsertOnNavi[i]['keyValue']));
                        break;
                    }
                }
            }
            if (navLabel === null || navLabel[10] !== false) {
                if (INTERMediatorOnPage.getOptionsTransaction() == 'none') {
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
                    INTERMediatorLib.addEvent(node, 'click',
                        function () {
                            INTERMediatorOnPage.logout();
                            location.reload();
                        });
                }
            }
        }
    },

    insertRecordFromNavi: function (targetName, keyField, isConfirm) {
        var conditions, restore, contextDef;

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

        try {
            INTERMediatorOnPage.retrieveAuthInfo();
            INTERMediator_DBAdapter.db_createRecord_async({name: targetName, dataset: []},
                function (response) {
                    var newId = response.newRecordKeyValue;
                    if (newId > -1) {
                        restore = INTERMediator.additionalCondition;
                        if (contextDef.records <= 1) {
                            INTERMediator.startFrom = 0;
                            INTERMediator.pagedAllCount = 1;
                            conditions = INTERMediator.additionalCondition;
                            conditions[targetName] = {field: keyField, value: newId};
                            INTERMediator.additionalCondition = conditions;
                            IMLibLocalContext.archive();
                        } else {
                            INTERMediator.pagedAllCount++;
                        }
                        INTERMediator_DBAdapter.unregister();
                        INTERMediator.constructMain(true);
                        INTERMediator.additionalCondition = restore;
                        IMLibPageNavigation.navigationSetup();
                    }
                    IMLibCalc.recalculation();
                    INTERMediatorOnPage.hideProgress();
                    INTERMediator.flushMessage();

                }, null);
        } catch (ex) {
            if (ex == '_im_requath_request_') {
                if (INTERMediatorOnPage.requireAuthentication) {
                    if (!INTERMediatorOnPage.isComplementAuthData()) {
                        INTERMediatorOnPage.clearCredentials();
                        INTERMediatorOnPage.authenticating(function () {
                            IMLibPageNavigation.insertRecordFromNavi(targetName, keyField, isConfirm);
                        });
                        INTERMediator.flushMessage();
                        return;
                    }
                }
            } else {
                INTERMediator.setErrorMessage(ex, 'EXCEPTION-5');
            }
        }

    },

    deleteRecordFromNavi: function (targetName, keyField, keyValue, isConfirm) {
        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1025])) {
                return;
            }
        }
        INTERMediatorOnPage.showProgress();
        try {
            INTERMediatorOnPage.retrieveAuthInfo();
            INTERMediator_DBAdapter.db_delete_async(
                {
                    name: targetName,
                    conditions: [{field: keyField, operator: '=', value: keyValue}]
                },
                function () {
                    INTERMediator.pagedAllCount--;
                    INTERMediator.totalRecordCount--;
                    if (INTERMediator.pagedAllCount - INTERMediator.startFrom < 1) {
                        INTERMediator.startFrom--;
                        if (INTERMediator.startFrom < 0) {
                            INTERMediator.startFrom = 0;
                        }
                    }
                    INTERMediator.constructMain(true);
                    INTERMediatorOnPage.hideProgress();
                    INTERMediator.flushMessage();
                },
                null
            );
        } catch (ex) {
            INTERMediator.setErrorMessage(ex, 'EXCEPTION-6');
        }
    },

    copyRecordFromNavi: function (contextDef, keyValue) {
        var assocDef, i, def, assocContexts, pStart, copyTerm, index;

        INTERMediatorOnPage.showProgress();
        if (contextDef['repeat-control'].match(/confirm-copy/)) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1041])) {
                return;
            }
        }
        try {
            if (contextDef['relation']) {
                for (index in contextDef['relation']) {
                    if (contextDef['relation'][index]['portal'] == true) {
                        contextDef['portal'] = true;
                    }
                }
            }

            assocDef = [];
            if (contextDef['repeat-control'].match(/copy-/)) {
                pStart = contextDef['repeat-control'].indexOf('copy-');
                copyTerm = contextDef['repeat-control'].substr(pStart + 5);
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
                            value: keyValue
                        });
                    }
                }
            }
            INTERMediatorOnPage.retrieveAuthInfo();
            INTERMediator_DBAdapter.db_copy_async(
                {
                    name: contextDef['name'],
                    conditions: [{field: contextDef['key'], operator: '=', value: keyValue}],
                    associated: assocDef.length > 0 ? assocDef : null
                },
                (function () {
                    var contextDefCapt = contextDef;
                    return function (result) {
                        var restore, conditions;
                        var newId = result.newRecordKeyValue;
                        if (newId > -1) {
                            restore = INTERMediator.additionalCondition;
                            INTERMediator.startFrom = 0;
                            if (contextDefCapt.records <= 1) {
                                conditions = INTERMediator.additionalCondition;
                                conditions[contextDefCapt.name] = {field: contextDefCapt.key, value: newId};
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
                null
            );
        } catch (ex) {
            INTERMediator.setErrorMessage(ex, 'EXCEPTION-43');
        }
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
                    if (!INTERMediator.ignoreOptimisticLocking) {
                        keyingComp = keying.split('=');
                        keyingField = keyingComp[0];
                        keyingComp.shift();
                        keyingValue = keyingComp.join('=');
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
    }
};
