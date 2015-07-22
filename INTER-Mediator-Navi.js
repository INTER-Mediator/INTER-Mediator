/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */

IMLibPageNavigation = {
    deleteInsertOnNavi: [],
    /**
     * Create Navigation Bar to move previous/next page
     */

    navigationSetup: function () {
        var navigation, i, insideNav, navLabel, node, start, pageSize, allCount, disableClass, c_node,
            prevPageCount, nextPageCount, endPageCount, onNaviInsertFunction, onNaviDeleteFunction, onNaviCopyFunction;

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
                disableClass = " IM_NAV_disabled";
                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    ((navLabel === null || navLabel[4] === null) ?
                        INTERMediatorOnPage.getMessages()[1] : navLabel[4]) + (start + 1) +
                    ((Math.min(start + pageSize, allCount) - start > 1) ?
                        (((navLabel === null || navLabel[5] === null) ? "-" : navLabel[5]) +
                            Math.min(start + pageSize, allCount)) : "") +
                    ((navLabel === null || navLabel[6] === null) ? " / " : navLabel[6]) + (allCount) +
                    ((navLabel === null || navLabel[7] === null) ? "" : navLabel[7])));
                node.setAttribute("class", "IM_NAV_info");
            }

            if ((navLabel === null || navLabel[0] !== false) && INTERMediator.pagination === true) {
                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    (navLabel === null || navLabel[0] === null) ? '<<' : navLabel[0]));
                node.setAttribute('class', 'IM_NAV_button' + (start === 0 ? disableClass : ""));
                INTERMediatorLib.addEvent(node, 'click', function () {
                    INTERMediator_DBAdapter.unregister();
                    INTERMediator.startFrom = 0;
                    INTERMediator.constructMain(true);
                });

                node = document.createElement('SPAN');
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(
                    (navLabel === null || navLabel[1] === null) ? '<' : navLabel[1]));
                node.setAttribute('class', 'IM_NAV_button' + (start === 0 ? disableClass : ""));
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
                node.setAttribute('class', 'IM_NAV_button' + (start + pageSize >= allCount ? disableClass : ""));
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
                node.setAttribute('class', 'IM_NAV_button' + (start + pageSize >= allCount ? disableClass : ""));
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
                node = document.createElement("SPAN");
                navigation.appendChild(node);
                node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[10]));
                c_node = document.createElement("INPUT");
                c_node.setAttribute("class", 'IM_NAV_JUMP');
                c_node.setAttribute("type", 'text');
                c_node.setAttribute("value", Math.ceil(INTERMediator.startFrom / pageSize + 1));
                node.appendChild(c_node);
                node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[11]));
                INTERMediatorLib.addEvent(
                    c_node,
                    "change",
                    function () {
                        if (this.value < 1) {
                            this.value = 1;
                        }
                        var max_page = Math.ceil(allCount / pageSize);
                        if (max_page < this.value) {
                            this.value = max_page;
                        }
                        INTERMediator.startFrom = ( ~~this.value - 1 ) * pageSize;
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
                            node.appendChild(
                                document.createTextNode(
                                    INTERMediatorOnPage.getMessages()[3] + ': ' +
                                        IMLibPageNavigation.deleteInsertOnNavi[i]['name']));
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
                            node.appendChild(
                                document.createTextNode(
                                    INTERMediatorOnPage.getMessages()[4] + ': ' +
                                        IMLibPageNavigation.deleteInsertOnNavi[i]['name']));
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
                            node.appendChild(
                                document.createTextNode(
                                    INTERMediatorOnPage.getMessages()[15] + ': ' +
                                        IMLibPageNavigation.deleteInsertOnNavi[i]['contextDef']['name']));
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
        var newId, conditions, fieldObj, contextDef, responseCreateRecord;

        if (isConfirm) {
            if (!confirm(INTERMediatorOnPage.getMessages()[1026])) {
                return;
            }
        }
        INTERMediatorOnPage.showProgress();
        contextDef = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), "name", targetName);
        if (contextDef === null) {
            alert("no targetname :" + targetName);
            INTERMediatorOnPage.hideProgress();
            return;
        }

        try {
            INTERMediatorOnPage.retrieveAuthInfo();
            responseCreateRecord = INTERMediator_DBAdapter.db_createRecord({name: targetName, dataset: []});
            newId = responseCreateRecord.newKeyValue;
        } catch (ex) {
            if (ex == "_im_requath_request_") {
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
                INTERMediator.setErrorMessage(ex, "EXCEPTION-5");
            }
        }

        if (newId > -1) {
            restore = INTERMediator.additionalCondition;
            INTERMediator.startFrom = 0;
            if (contextDef.records <= 1) {
                conditions = INTERMediator.additionalCondition;
                conditions[targetName] = {field: keyField, value: newId};
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
            INTERMediator_DBAdapter.db_delete({
                name: targetName,
                conditions: [
                    {field: keyField, operator: '=', value: keyValue}
                ]
            });
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                INTERMediatorOnPage.clearCredentials();
                INTERMediatorOnPage.authenticating(
                    function () {
                        IMLibPageNavigation.deleteRecordFromNavi(targetName, keyField, keyValue, isConfirm);
                    }
                );
                INTERMediator.flushMessage();
                return;
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-6");
            }
        }

        if (INTERMediator.pagedAllCount - INTERMediator.startFrom < 2) {
            INTERMediator.startFrom--;
            if (INTERMediator.startFrom < 0) {
                INTERMediator.startFrom = 0;
            }
        }
        INTERMediator.constructMain(true);
        INTERMediatorOnPage.hideProgress();
        INTERMediator.flushMessage();
    },

    copyRecordFromNavi: function(contextDef, keyValue)  {
        var newId;

        INTERMediatorOnPage.showProgress();
        newId = IMLibUI.copyRecordImpl(contextDef, keyValue);
        if (newId > -1) {
            restore = INTERMediator.additionalCondition;
            INTERMediator.startFrom = 0;
            if (contextDef.records <= 1) {
                conditions = INTERMediator.additionalCondition;
                conditions[contextDef.name] = {field: contextDef.key, value: newId};
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
    },

    saveRecordFromNavi: function (dontUpdate) {
        var keying, field, keyingComp, keyingField, keyingValue, checkQueryParameter, i, initialValue,
            currentVal, fieldArray, valueArray, difference, needUpdate = true, context, updateData;

        INTERMediatorOnPage.showProgress();
        INTERMediatorOnPage.retrieveAuthInfo();
        for (i = 0; i < IMLibContextPool.poolingContexts.length; i++) {
            context = IMLibContextPool.poolingContexts[i];
            updateData = context.getModified();
            for (keying in updateData) {
                fieldArray = [];
                valueArray = [];
                for (field in updateData[keying]) {
                    fieldArray.push(field);
                    valueArray.push({field: field, value: updateData[keying][field]});
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
                        if (ex == "_im_requath_request_") {
                            if (INTERMediatorOnPage.requireAuthentication &&
                                !INTERMediatorOnPage.isComplementAuthData()) {
                                INTERMediatorOnPage.clearCredentials();
                                INTERMediatorOnPage.authenticating(
                                    function () {
                                        INTERMediator.db_query(checkQueryParameter);
                                    }
                                );
                                return;
                            }
                        } else {
                            INTERMediator.setErrorMessage(ex, "EXCEPTION-28");
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
                    if (ex == "_im_requath_request_") {
                        if (INTERMediatorOnPage.requireAuthentication &&
                            !INTERMediatorOnPage.isComplementAuthData()) {
                            INTERMediatorOnPage.clearCredentials();
                            INTERMediatorOnPage.authenticating(
                                function () {
                                    IMLibPageNavigation.deleteRecordFromNavi(targetName, keyField, keyValue, isConfirm);
                                }
                            );
                            return;
                        }
                    } else {
                        INTERMediator.setErrorMessage(ex, "EXCEPTION-29");
                    }
                }
                context.clearModified();
            }
        }
        if (needUpdate && (dontUpdate !== true)) {
            INTERMediator.constructMain(true);
        }
        INTERMediatorOnPage.hideProgress();
        INTERMediator.flushMessage();
    }
};
