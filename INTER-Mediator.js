/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

//"use strict"

var INTERMediator = {
    /*
     Properties
     */
    debugMode: false,
    // Show the debug messages at the top of the page.
    separator: '@',
    // This must be referred as 'INTERMediator.separator'. Don't use 'this.separator'
    defDivider: '|',
    // Same as the "separator".
    defaultTargetInnerHTML: false,
    // For general elements, if target isn't specified, the value will be set to innerHTML.
    // Otherwise, set as the text node.
    navigationLabel: null,
    // Navigation is controlled by this parameter.
    elementIds: [],
    //widgetElementIds: [],
    radioNameMode: false,
    dontSelectRadioCheck: false,
    ignoreOptimisticLocking: false,
    supressDebugMessageOnPage: false,
    supressErrorMessageOnPage: false,
    additionalFieldValueOnNewRecord: {},
    additionalFieldValueOnUpdate: {},
    additionalFieldValueOnDelete: {},
    waitSecondsAfterPostMessage: 4,
    pagedAllCount: 0,
    currentEncNumber: 0,
    isIE: false,
    isTrident: false,
    ieVersion: -1,
    titleAsLinkInfo: true,
    classAsLinkInfo: true,
    isDBDataPreferable: false,
    noRecordClassName: "_im_for_noresult_",
    nullAcceptable: true,

    rootEnclosure: null,
    // Storing to retrieve the page to initial condition.
    // {node:xxx, parent:xxx, currentRoot:xxx, currentAfter:xxxx}

    useSessionStorage: true,
    // Use sessionStorage for the Local Context instead of Cookie.

    errorMessages: [],
    debugMessages: [],

    partialConstructing: false,
    linkedElmCounter: 0,

    /* These following properties moved to the setter/getter architecture, and defined out side of this object.*/
    //startFrom: 0,
    //pagedSize: 0,
    //additionalCondition: {},
    //additionalSortKey: {},

    //=================================
    // Message for Programmers
    //=================================

    setDebugMessage: function (message, level) {
        if (level === undefined) {
            level = 1;
        }
        if (INTERMediator.debugMode >= level) {
            INTERMediator.debugMessages.push(message);
            if (typeof console != 'undefined') {
                console.log("INTER-Mediator[DEBUG:%s]: %s", new Date(), message);
            }
        }
    },

    setErrorMessage: function (ex, moreMessage) {
        moreMessage = moreMessage === undefined ? "" : (" - " + moreMessage);
        if ((typeof ex == 'string' || ex instanceof String)) {
            INTERMediator.errorMessages.push(ex + moreMessage);
            if (typeof console != 'undefined') {
                console.error("INTER-Mediator[ERROR]: %s", ex + moreMessage);
            }
        } else {
            if (ex.message) {
                INTERMediator.errorMessages.push(ex.message + moreMessage);
                if (typeof console != 'undefined') {
                    console.error("INTER-Mediator[ERROR]: %s", ex.message + moreMessage);
                }
            }
            if (ex.stack && typeof console != 'undefined') {
                console.error(ex.stack);
            }
        }
    },

    flushMessage: function () {
        var debugNode, title, body, i, j, lines, clearButton, tNode, target;

        if (!INTERMediator.supressErrorMessageOnPage
            && INTERMediator.errorMessages.length > 0) {
            debugNode = document.getElementById('_im_error_panel_4873643897897');
            if (debugNode === null) {
                debugNode = document.createElement('div');
                debugNode.setAttribute('id', '_im_error_panel_4873643897897');
                debugNode.style.backgroundColor = '#FFDDDD';
                title = document.createElement('h3');
                title.appendChild(document.createTextNode('Error Info from INTER-Mediator'));
                title.appendChild(document.createElement('hr'));
                debugNode.appendChild(title);
                body = document.getElementsByTagName('body')[0];
                body.insertBefore(debugNode, body.firstChild);
            }
            debugNode.appendChild(document.createTextNode(
                "============ERROR MESSAGE on " + new Date() + "============"));
            debugNode.appendChild(document.createElement('hr'));
            for (i = 0; i < INTERMediator.errorMessages.length; i++) {
                lines = INTERMediator.errorMessages[i].split("\n");
                for (j = 0; j < lines.length; j++) {
                    if (j > 0) {
                        debugNode.appendChild(document.createElement('br'));
                    }
                    debugNode.appendChild(document.createTextNode(lines[j]));
                }
                debugNode.appendChild(document.createElement('hr'));
            }
        }
        if (!INTERMediator.supressDebugMessageOnPage
            && INTERMediator.debugMode
            && INTERMediator.debugMessages.length > 0) {
            debugNode = document.getElementById('_im_debug_panel_4873643897897');
            if (debugNode === null) {
                debugNode = document.createElement('div');
                debugNode.setAttribute('id', '_im_debug_panel_4873643897897');
                debugNode.style.backgroundColor = '#DDDDDD';
                clearButton = document.createElement('button');
                clearButton.setAttribute('title', 'clear');
                INTERMediatorLib.addEvent(clearButton, 'click', function () {
                    target = document.getElementById('_im_debug_panel_4873643897897');
                    target.parentNode.removeChild(target);
                });
                tNode = document.createTextNode('clear');
                clearButton.appendChild(tNode);
                title = document.createElement('h3');
                title.appendChild(document.createTextNode('Debug Info from INTER-Mediator'));
                title.appendChild(clearButton);
                title.appendChild(document.createElement('hr'));
                debugNode.appendChild(title);
                body = document.getElementsByTagName('body')[0];
                if (body) {
                    if (body.firstChild) {
                        body.insertBefore(debugNode, body.firstChild);
                    } else {
                        body.appendChild(debugNode);
                    }
                }
            }
            debugNode.appendChild(document.createTextNode(
                "============DEBUG INFO on " + new Date() + "============ "));
            if (INTERMediatorOnPage.getEditorPath()) {
                var aLink = document.createElement('a');
                aLink.setAttribute('href', INTERMediatorOnPage.getEditorPath());
                aLink.appendChild(document.createTextNode('Definition File Editor'));
                debugNode.appendChild(aLink);
            }
            debugNode.appendChild(document.createElement('hr'));
            for (i = 0; i < INTERMediator.debugMessages.length; i++) {
                lines = INTERMediator.debugMessages[i].split("\n");
                for (j = 0; j < lines.length; j++) {
                    if (j > 0) {
                        debugNode.appendChild(document.createElement('br'));
                    }
                    debugNode.appendChild(document.createTextNode(lines[j]));
                }
                debugNode.appendChild(document.createElement('hr'));
            }
        }
        INTERMediator.errorMessages = [];
        INTERMediator.debugMessages = [];
    },

    // Detect Internet Explorer and its version.
    propertyIETridentSetup: function () {
        var ua, msiePos, c, i;
        ua = navigator.userAgent;
        msiePos = ua.toLocaleUpperCase().indexOf('MSIE');
        if (msiePos >= 0) {
            INTERMediator.isIE = true;
            for (i = msiePos + 4; i < ua.length; i++) {
                c = ua.charAt(i);
                if (!(c == ' ' || c == '.' || (c >= '0' && c <= '9'))) {
                    INTERMediator.ieVersion = INTERMediatorLib.toNumber(ua.substring(msiePos + 4, i));
                    break;
                }
            }
        }
        msiePos = ua.indexOf('; Trident/');
        if (msiePos >= 0) {
            INTERMediator.isTrident = true;
            for (i = msiePos + 10; i < ua.length; i++) {
                c = ua.charAt(i);
                if (!(c == ' ' || c == '.' || (c >= '0' && c <= '9'))) {
                    INTERMediator.ieVersion = INTERMediatorLib.toNumber(ua.substring(msiePos + 10, i)) + 4;
                    break;
                }
            }
        }
    },

    initialize: function () {
        INTERMediatorOnPage.removeCookie('_im_localcontext');
//    INTERMediatorOnPage.removeCookie('_im_username');
//    INTERMediatorOnPage.removeCookie('_im_credential');
//    INTERMediatorOnPage.removeCookie('_im_mediatoken');

        INTERMediator.additionalCondition = {};
        INTERMediator.additionalSortKey = {};
        INTERMediator.startFrom = 0;
        IMLibLocalContext.archive();
    },
    /**
     * //=================================
     * // Construct Page
     * //=================================

     * Construct the Web Page with DB Data
     * You should call here when you show the page.
     *
     * parameter: true=construct page, others=construct partially
     */
    construct: function (indexOfKeyFieldObject) {
        var timerTask;
        INTERMediatorOnPage.showProgress();
        if (indexOfKeyFieldObject === true || indexOfKeyFieldObject === undefined) {
            timerTask = function () {
                INTERMediator.constructMain(true)
            };
        } else {
            timerTask = function () {
                INTERMediator.constructMain(indexOfKeyFieldObject)
            };
        }
        setTimeout(timerTask, 0);
    },


    constructMain: function (updateRequiredContext) {
        var i, theNode, currentLevel = 0, postSetFields = [], buttonIdNum = 1,
            eventListenerPostAdding = [], isInsidePostOnly, nameAttrCounter = 1, imPartsShouldFinished = [],
            isAcceptNotify = false;
        IMLibPageNavigation.deleteInsertOnNavi = [];
        INTERMediatorOnPage.retrieveAuthInfo();
        try {
            if (updateRequiredContext === true || updateRequiredContext === undefined) {
                this.partialConstructing = false;
                IMLibContextPool.clearAll();
                pageConstruct();
            } else {
                this.partialConstructing = true;
                partialConstruct(updateRequiredContext);
            }
        } catch (ex) {
            if (ex == "_im_requath_request_") {
                if (INTERMediatorOnPage.requireAuthentication) {
                    if (!INTERMediatorOnPage.isComplementAuthData()) {
                        INTERMediatorOnPage.authChallenge = null;
                        INTERMediatorOnPage.authHashedPassword = null;
                        INTERMediatorOnPage.authenticating(
                            function () {
                                INTERMediator.constructMain(updateRequiredContext);
                            }
                        );
                        return;
                    }
                }
            } else {
                INTERMediator.setErrorMessage(ex, "EXCEPTION-7");
            }
        }

        for (i = 0; i < imPartsShouldFinished.length; i++) {
            imPartsShouldFinished[i].finish();
        }

        INTERMediatorOnPage.hideProgress();

        // Event listener should add after adding node to document.
        for (i = 0; i < eventListenerPostAdding.length; i++) {
            theNode = document.getElementById(eventListenerPostAdding[i].id);
            if (theNode) {
                INTERMediatorLib.addEvent(
                    theNode, eventListenerPostAdding[i].event, eventListenerPostAdding[i].todo);
            }
        }

        if (INTERMediatorOnPage.doAfterConstruct) {
            INTERMediatorOnPage.doAfterConstruct();
        }

        INTERMediator.flushMessage(); // Show messages

        /*

         */

        function partialConstruct(updateRequiredContext, appendKeying) {
            var updateNode, originalNodes, i;

            isInsidePostOnly = false;
            postSetFields = [];

            updateNode = updateRequiredContext.enclosureNode;
            try {
                if (!appendKeying) {
                    while (updateNode.firstChild) {
                        updateNode.removeChild(updateNode.firstChild);
                    }
                    originalNodes = updateRequiredContext.original;
                    for (i = 0; i < originalNodes.length; i++) {
                        updateNode.appendChild(originalNodes[i]);
                    }
                    seekEnclosureNode(updateNode, updateRequiredContext.foreignValue);
                }
                else {
                    expandRepeaters(updateRequiredContext, updateNode, {
                        recordset: [updateRequiredContext.store[appendKeying]],
                        targetTotalCount: 1,
                        targetCount: 1
                    });
                }
            } catch (ex) {
                if (ex == "_im_requath_request_") {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex, "EXCEPTION-8");
                }
            }

            for (i = 0; i < postSetFields.length; i++) {
                document.getElementById(postSetFields[i]['id']).value = postSetFields[i]['value'];
            }
            IMLibCalc.updateCalculationFields();
        }

        function pageConstruct() {
            var i, bodyNode, emptyElement;

            IMLibCalc.calculateRequiredObject = {};
            INTERMediator.currentEncNumber = 1;
            INTERMediator.elementIds = [];
            //INTERMediator.widgetElementIds = [];
            isInsidePostOnly = false;

            // Restoring original HTML Document from backup data.
            bodyNode = document.getElementsByTagName('BODY')[0];
            if (INTERMediator.rootEnclosure === null) {
                INTERMediator.rootEnclosure = bodyNode.innerHTML;
            } else {
                bodyNode.innerHTML = INTERMediator.rootEnclosure;
            }
            postSetFields = [];

            try {
                seekEnclosureNode(bodyNode, null, null);
            } catch (ex) {
                if (ex == "_im_requath_request_") {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex, "EXCEPTION-9");
                }
            }


            // After work to set up popup menus.
            for (i = 0; i < postSetFields.length; i++) {
                if (postSetFields[i]['value'] == ""
                    && document.getElementById(postSetFields[i]['id']).tagName == "SELECT") {
                    // for compatibility with Firefox when the value of select tag is empty.
                    emptyElement = document.createElement('option');
                    emptyElement.setAttribute("value", "");
                    document.getElementById(postSetFields[i]['id']).insertBefore(
                        emptyElement, document.getElementById(postSetFields[i]['id']).firstChild);
                }
                document.getElementById(postSetFields[i]['id']).value = postSetFields[i]['value'];
            }
            IMLibLocalContext.bindingDescendant(bodyNode);
            IMLibCalc.updateCalculationFields();
            IMLibPageNavigation.navigationSetup();

            if (isAcceptNotify) {
//                var channelName = INTERMediatorOnPage.clientNotificationChannel();
                var channelName = INTERMediatorOnPage.clientNotificationIdentifier();
                var appKey = INTERMediatorOnPage.clientNotificationKey();
                if (appKey && appKey != "_im_key_isnt_supplied") {
                    try {
                        Pusher.log = function (message) {
                            if (window.console && window.console.log) {
                                window.console.log(message);
                            }
                        };

                        INTERMediator.pusherObject = new Pusher(appKey);
                        INTERMediator.pusherChannel = INTERMediator.pusherObject.subscribe(channelName);
                        INTERMediator.pusherChannel.bind('update', function (data) {
                            IMLibContextPool.updateOnAnotherClient('update', data);
                        });
                        INTERMediator.pusherChannel.bind('create', function (data) {
                            IMLibContextPool.updateOnAnotherClient('create', data);
                        });
                        INTERMediator.pusherChannel.bind('delete', function (data) {
                            IMLibContextPool.updateOnAnotherClient('delete', data);
                        });
                    } catch (ex) {
                        INTERMediator.setErrorMessage(ex, "EXCEPTION-33");
                    }
                }
            }

            appendCredit();
        }

        /**
         * Seeking nodes and if a node is an enclosure, proceed repeating.
         */

        function seekEnclosureNode(node, currentRecord) {//}, objectReference) {
            var children, className, i, attr;
            if (node.nodeType === 1) { // Work for an element
                try {
                    if (INTERMediatorLib.isEnclosure(node, false)) { // Linked element and an enclosure
                        className = INTERMediatorLib.getClassAttributeFromNode(node);
                        attr = node.getAttribute("data-im-control");
                        if ((className && className.match(/_im_post/))
                            || (attr && attr == "post")) {
                            setupPostOnlyEnclosure(node);
                        } else {
                            if (INTERMediator.isIE) {
                                try {
                                    expandEnclosure(node, currentRecord);//, objectReference);
                                } catch (ex) {
                                    if (ex == "_im_requath_request_") {
                                        throw ex;
                                    }
                                }
                            } else {
                                expandEnclosure(node, currentRecord);//, objectReference);
                            }
                        }
                    } else {
                        children = node.childNodes; // Check all child nodes.
                        if (children) {
                            for (i = 0; i < children.length; i++) {
                                if (children[i].nodeType === 1) {
                                    seekEnclosureNode(children[i], currentRecord);//, objectReference);
                                }
                            }
                        }
                    }
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex, "EXCEPTION-10");
                    }
                }

            }
        }

        function setupPostOnlyEnclosure(node) {
            var nodes, k, currentWidgetNodes, plugin, setupWidget = false;
            var postNodes = INTERMediatorLib.getElementsByClassNameOrDataAttr(node, '_im_post');
            for (var i = 1; i < postNodes.length; i++) {
                INTERMediatorLib.addEvent(
                    postNodes[i],
                    'click',
                    (function () {
                        var targetNode = postNodes[i];
                        return function () {
                            IMLibUI.clickPostOnlyButton(targetNode);
                        }
                    })());
            }
            nodes = node.childNodes;

            isInsidePostOnly = true;
            for (i = 0; i < nodes.length; i++) {
                seekEnclosureInPostOnly(nodes[i]);
            }
//            if (setupWidget) {
//                for (plugin in IMParts_Catalog) {
//                    IMParts_Catalog[plugin].finish(false);
//                }
//            }
            isInsidePostOnly = false;
            // -------------------------------------------
            function seekEnclosureInPostOnly(node) {
                var children, i, wInfo;
                if (node.nodeType === 1) { // Work for an element
                    try {
                        if (INTERMediatorLib.isWidgetElement(node)) {
                            wInfo = INTERMediatorLib.getWidgetInfo(node);
                            if (wInfo[0]) {
//                                setupWidget = true;
                                //IMParts_Catalog[wInfo[0]].instanciate.apply(IMParts_Catalog[wInfo[0]], [node]);
                                IMParts_Catalog[wInfo[0]].instanciate(node);
                                if (imPartsShouldFinished.indexOf(IMParts_Catalog[wInfo[0]]) < 0) {
                                    imPartsShouldFinished.push(IMParts_Catalog[wInfo[0]]);
                                }
                            }
                        } else if (INTERMediatorLib.isEnclosure(node, false)) { // Linked element and an enclosure
                            expandEnclosure(node, null, null);
                        } else {
                            children = node.childNodes; // Check all child nodes.
                            for (i = 0; i < children.length; i++) {
                                seekEnclosureInPostOnly(children[i]);
                            }
                        }
                    } catch (ex) {
                        if (ex == "_im_requath_request_") {
                            throw ex;
                        } else {
                            INTERMediator.setErrorMessage(ex, "EXCEPTION-11");
                        }
                    }
                }
            }
        }

        /**
         * Expanding an enclosure.
         */

        function expandEnclosure(node, currentRecord, parentObjectInfo) {
            var linkedNodes, encNodeTag, repeatersOriginal, repeaters, linkDefs, voteResult,
                currentContext, fieldList, repNodeTag, joinField, relationDef, index, fieldName, i, ix, targetRecords,
                newNode, keyValue, selectedNode, calcDef, calcFields, contextObj;

            currentLevel++;
            INTERMediator.currentEncNumber++;

            if (!node.getAttribute('id')) {
                node.setAttribute('id', nextIdValue());
            }

            encNodeTag = node.tagName;
            repNodeTag = INTERMediatorLib.repeaterTagFromEncTag(encNodeTag);
            repeatersOriginal = collectRepeatersOriginal(node, repNodeTag); // Collecting repeaters to this array.
            repeaters = collectRepeaters(repeatersOriginal);  // Collecting repeaters to this array.
            linkedNodes = INTERMediatorLib.seekLinkedAndWidgetNodes(repeaters, true).linkedNode;
            linkDefs = collectLinkDefinitions(linkedNodes);
            voteResult = tableVoting(linkDefs);
            currentContext = voteResult.targettable;

            if (!currentContext) {
                for (i = 0; i < repeatersOriginal.length; i++) {
                    newNode = node.appendChild(repeatersOriginal[i]);

                    // for compatibility with Firefox
                    if (repeatersOriginal[i].getAttribute("selected") != null) {
                        selectedNode = newNode;
                    }
                    if (selectedNode !== undefined) {
                        selectedNode.selected = true;
                    }

                    seekEnclosureNode(newNode, null);
                }
            } else {
                contextObj = new IMLibContext(currentContext['name']);
                contextObj.enclosureNode = node;
                contextObj.repeaterNodes = repeaters;
                contextObj.original = repeatersOriginal;

                fieldList = []; // Create field list for database fetch.
                calcDef = currentContext['calculation'];
                calcFields = [];
                for (ix in calcDef) {
                    calcFields.push(calcDef[ix]["field"]);
                }
                for (i = 0; i < voteResult.fieldlist.length; i++) {
                    if (!calcFields[voteResult.fieldlist[i]]) {
                        calcFields.push(voteResult.fieldlist[i]);
                    }
                }

                if (currentRecord) {
                    try {
                        relationDef = currentContext['relation'];
                        contextObj.setOriginal(repeatersOriginal);
                        if (relationDef) {
                            for (index in relationDef) {
                                if (relationDef[index]['portal'] == true) {
                                    currentContext['portal'] = true;
                                }
                                joinField = relationDef[index]['join-field'];
                                contextObj.addForeignValue(joinField, currentRecord[joinField]);
                                for (fieldName in parentObjectInfo) {
                                    if (fieldName == relationDef[index]['join-field']) {
                                        contextObj.addDependingObject(parentObjectInfo[fieldName]);
                                    }
                                }
                            }
                        }
                    } catch (ex) {
                        if (ex == "_im_requath_request_") {
                            throw ex;
                        } else {
                            INTERMediator.setErrorMessage(ex, "EXCEPTION-25");
                        }
                    }
                }

                targetRecords = retrieveDataForEnclosure(currentContext, fieldList, contextObj.foreignValue);
                contextObj.nullAcceptable = INTERMediator.nullAcceptable;
                isAcceptNotify |= !(INTERMediatorOnPage.notifySupport === false);
                expandRepeaters(contextObj, node, targetRecords);
                setupInsertButton(currentContext, keyValue, node, contextObj.foreignValue);
                callbackForEnclosure(currentContext, node);

            }
            currentLevel--;
        }

        function expandRepeaters(contextObj, node, targetRecords) {
            var newNode, nodeClass, dataAttr, recordCounter, repeatersOneRec, linkedElements, currentWidgetNodes,
                currentLinkedNodes, shouldDeleteNodes, dbspec, keyField, foreignField, foreignValue, foreignFieldValue,
                keyValue, keyingValue, k, nodeId, replacedNode, children, wInfo, nameTable, nodeTag, typeAttr,
                linkInfoArray, nameTableKey, nameNumber, nameAttr, nInfo, curVal, j, curTarget, newlyAddedNodes,
                encNodeTag, repNodeTag, ix, repeatersOriginal, targetRecordset, targetCount, targetTotalCount, i,
                currentContext;

            encNodeTag = node.tagName;
            repNodeTag = INTERMediatorLib.repeaterTagFromEncTag(encNodeTag);

            repeatersOriginal = contextObj.original;
            currentContext = contextObj.getContextDef();
            targetRecordset = targetRecords.recordset;
            targetTotalCount = targetRecords.totalCount;
            targetCount = targetRecords.count;

            if (targetCount == 0) {
                for (i = 0; i < repeatersOriginal.length; i++) {
                    newNode = repeatersOriginal[i].cloneNode(true);
                    nodeClass = INTERMediatorLib.getClassAttributeFromNode(newNode);
                    dataAttr = newNode.getAttribute("data-im-control");
                    if (nodeClass == INTERMediator.noRecordClassName || dataAttr == "noresult") {
                        node.appendChild(newNode);
                        setIdValue(newNode);
                    }
                }
            }

            recordCounter = 0;
            for (ix in targetRecordset) { // for each record
                try {
                    recordCounter++;
                    repeatersOneRec = cloneEveryNodes(repeatersOriginal);
                    linkedElements = INTERMediatorLib.seekLinkedAndWidgetNodes(repeatersOneRec, true);
                    currentWidgetNodes = linkedElements.widgetNode;
                    currentLinkedNodes = linkedElements.linkedNode;
                    shouldDeleteNodes = shouldDeleteNodeIds(repeatersOneRec);
                    dbspec = INTERMediatorOnPage.getDBSpecification();
                    if (dbspec["db-class"] != null && dbspec["db-class"] == "FileMaker_FX") {
                        keyField = currentContext["key"] ? currentContext["key"] : "-recid";
                    } else {
                        keyField = currentContext["key"] ? currentContext["key"] : "id";
                    }
                    if (currentContext['portal'] == true) {
                        keyField = "-recid";
                        foreignField = currentContext['name'] + "::-recid";
                        foreignValue = targetRecordset[ix][foreignField];
                        foreignFieldValue = foreignField + "=" + foreignValue;
                    } else {
                        foreignFieldValue = "=";
                        foreignValue = null;
                    }
                    keyValue = targetRecordset[ix][keyField];
                    keyingValue = keyField + "=" + keyValue;

                    for (k = 0; k < currentLinkedNodes.length; k++) {
                        // for each linked element
                        nodeId = currentLinkedNodes[k].getAttribute("id");
                        replacedNode = setIdValue(currentLinkedNodes[k]);

                        //    if (targetRecords.recordset.length > 1) {
                        if (replacedNode.getAttribute("type") == "checkbox") {
                            children = replacedNode.parentNode.childNodes;
                            for (i = 0; i < children.length; i++) {
                                if (children[i].nodeType === 1 && children[i].tagName == "LABEL"
                                    && nodeId == children[i].getAttribute("for")) {
                                    children[i].setAttribute("for", replacedNode.getAttribute("id"));
                                    break;
                                }
                            }
                        }
                        //    }
                    }
                    for (k = 0; k < currentWidgetNodes.length; k++) {
                        wInfo = INTERMediatorLib.getWidgetInfo(currentWidgetNodes[k]);
                        if (wInfo[0]) {
                            IMParts_Catalog[wInfo[0]].instanciate(currentWidgetNodes[k]);
                            if (imPartsShouldFinished.indexOf(IMParts_Catalog[wInfo[0]]) < 0) {
                                imPartsShouldFinished.push(IMParts_Catalog[wInfo[0]]);
                            }
                        }
                    }
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex, "EXCEPTION-26");
                    }
                }

                if (currentContext['portal'] != true
                    || (currentContext['portal'] == true && targetTotalCount > 0)) {
                    nameTable = {};
                    for (k = 0; k < currentLinkedNodes.length; k++) {
                        try {
                            nodeTag = currentLinkedNodes[k].tagName;
                            nodeId = currentLinkedNodes[k].getAttribute('id');
                            if (INTERMediatorLib.isWidgetElement(currentLinkedNodes[k])) {
                                nodeId = currentLinkedNodes[k]._im_getComponentId();
                                // INTERMediator.widgetElementIds.push(nodeId);
                            }
                            // get the tag name of the element
                            typeAttr = currentLinkedNodes[k].getAttribute('type');
                            // type attribute
                            linkInfoArray = INTERMediatorLib.getLinkedElementInfo(currentLinkedNodes[k]);
                            // info array for it  set the name attribute of radio button
                            // should be different for each group
                            if (typeAttr == 'radio') { // set the value to radio button
                                nameTableKey = linkInfoArray.join('|');
                                if (!nameTable[nameTableKey]) {
                                    nameTable[nameTableKey] = nameAttrCounter;
                                    nameAttrCounter++
                                }
                                nameNumber = nameTable[nameTableKey];
                                nameAttr = currentLinkedNodes[k].getAttribute('name');
                                if (nameAttr) {
                                    currentLinkedNodes[k].setAttribute('name', nameAttr + '-' + nameNumber);
                                } else {
                                    currentLinkedNodes[k].setAttribute('name', 'IM-R-' + nameNumber);
                                }
                            }

                            var isContext = false;
                            for (j = 0; j < linkInfoArray.length; j++) {
                                nInfo = INTERMediatorLib.getNodeInfoArray(linkInfoArray[j]);
                                curVal = targetRecordset[ix][nInfo['field']];
                                if (!INTERMediator.isDBDataPreferable || curVal != null) {
                                    IMLibCalc.updateCalculationInfo(
                                        currentContext, nodeId, nInfo, targetRecordset[ix]);
                                }
                                if (nInfo['table'] == currentContext['name']) {
                                    isContext = true;
                                    curTarget = nInfo['target'];
                                    //    objectReference[nInfo['field']] = nodeId;

                                    // Set data to the element.
                                    if (curVal === null) {
                                        if (IMLibElement.setValueToIMNode(currentLinkedNodes[k], curTarget, '')) {
                                            postSetFields.push({'id': nodeId, 'value': curVal});
                                        }
                                    } else if ((typeof curVal == 'object' || curVal instanceof Object)) {
                                        if (curVal && curVal.length > 0) {
                                            if (IMLibElement.setValueToIMNode(
                                                currentLinkedNodes[k], curTarget, curVal[0])) {
                                                postSetFields.push({'id': nodeId, 'value': curVal[0]});
                                            }
                                        }
                                    } else {
                                        if (IMLibElement.setValueToIMNode(currentLinkedNodes[k], curTarget, curVal)) {
                                            postSetFields.push({'id': nodeId, 'value': curVal});
                                        }
                                    }
                                    contextObj.setValue(keyingValue, nInfo['field'], curVal, nodeId, curTarget, foreignValue);
                                }
                            }

                            if (isContext
                                && !isInsidePostOnly
                                && (nodeTag == 'INPUT' || nodeTag == 'SELECT' || nodeTag == 'TEXTAREA')) {
                                IMLibChangeEventDispatch.setExecute(nodeId, IMLibUI.valueChange);
                                if (nodeTag != 'SELECT') {
                                    eventListenerPostAdding.push({
                                        'id': nodeId,
                                        'event': 'keydown',
                                        'todo': IMLibUI.keyDown
                                    });
                                    eventListenerPostAdding.push({
                                        'id': nodeId,
                                        'event': 'keyup',
                                        'todo': IMLibUI.keyUp
                                    });
                                }
                            }

                        } catch (ex) {
                            if (ex == "_im_requath_request_") {
                                throw ex;
                            } else {
                                INTERMediator.setErrorMessage(ex, "EXCEPTION-27");
                            }
                        }
                    }
                }

                if (currentContext['portal'] == true) {
                    keyField = "-recid";
                    foreignField = currentContext['name'] + "::-recid";
                    foreignValue = targetRecordset[ix][foreignField];
                    foreignFieldValue = foreignField + "=" + foreignValue;
                } else {
                    foreignField = "";
                    foreignValue = "";
                    foreignFieldValue = "=";
                }

                setupDeleteButton(encNodeTag, repNodeTag, repeatersOneRec[repeatersOneRec.length - 1],
                    currentContext, keyField, keyValue, foreignField, foreignValue, shouldDeleteNodes);

                if (currentContext['portal'] != true
                    || (currentContext['portal'] == true && targetTotalCount > 0)) {
                    newlyAddedNodes = [];
                    for (i = 0; i < repeatersOneRec.length; i++) {
                        newNode = repeatersOneRec[i];
                        //newNode = repeatersOneRec[i].cloneNode(true);
                        nodeClass = INTERMediatorLib.getClassAttributeFromNode(newNode);
                        dataAttr = newNode.getAttribute("data-im-control");
                        if ((nodeClass != INTERMediator.noRecordClassName) && (dataAttr != "noresult")) {
                            node.appendChild(newNode);
                            newlyAddedNodes.push(newNode);
                            setIdValue(newNode);
                            seekEnclosureNode(newNode, targetRecordset[ix]);//, objectReference);
                        }
                    }
                    callbackForRepeaters(currentContext, node);
                }
            }
        }

        function retrieveDataForEnclosure(currentContext, fieldList, relationValue) {
            var ix, keyField, targetRecords, counter, oneRecord, isMatch, index, fieldName, condition,
                recordNumber, useLimit, optionalCondition = [], pagingValue, recordsValue;

            if (currentContext['cache'] == true) {
                try {
                    if (!INTERMediatorOnPage.dbCache[currentContext['name']]) {
                        INTERMediatorOnPage.dbCache[currentContext['name']] = INTERMediator_DBAdapter.db_query({
                            name: currentContext['name'],
                            records: null,
                            paging: null,
                            fields: fieldList,
                            parentkeyvalue: null,
                            conditions: null,
                            useoffset: false
                        });
                    }
                    if (relationValue === null) {
                        targetRecords = INTERMediatorOnPage.dbCache[currentContext['name']];
                    } else {
                        targetRecords = {recordset: [], count: 0};
                        counter = 0;
                        for (ix in INTERMediatorOnPage.dbCache[currentContext['name']].recordset) {
                            oneRecord = INTERMediatorOnPage.dbCache[currentContext['name']].recordset[ix];
                            isMatch = true;
                            index = 0;
                            for (keyField in relationValue) {
                                fieldName = currentContext['relation'][index]['foreign-key'];
                                if (oneRecord[fieldName] != relationValue[keyField]) {
                                    isMatch = false;
                                    break;
                                }
                                index++;
                            }
                            if (isMatch) {
                                pagingValue = currentContext['paging'] ? currentContext['paging'] : false;
                                recordsValue = currentContext['records'] ? currentContext['records'] : 10000000000;

                                if (!pagingValue || (pagingValue && ( counter >= INTERMediator.startFrom ))) {
                                    targetRecords.recordset.push(oneRecord);
                                    targetRecords.count++;
                                    if (recordsValue <= targetRecords.count) {
                                        break;
                                    }
                                }
                                counter++;
                            }
                        }
                    }
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex, "EXCEPTION-24");
                    }
                }
            } else {   // cache is not active.
                try {
                    if (currentContext["portal"] == true) {
                        for (condition in INTERMediator.additionalCondition) {
                            optionalCondition.push(INTERMediator.additionalCondition[condition]);
                            break;
                        }
                    }
                    useLimit = true;
                    if (currentContext["relation"] !== undefined) {
                        useLimit = false;
                    }
                    if (currentContext['maxrecords'] && useLimit && Number(INTERMediator.pagedSize) > 0
                        && Number(currentContext['maxrecords']) >= Number(INTERMediator.pagedSize)) {
                        recordNumber = Number(INTERMediator.pagedSize);
                    } else {
                        recordNumber = Number(currentContext['records']);
                    }
                    targetRecords = INTERMediator_DBAdapter.db_query({
                        "name": currentContext['name'],
                        "records": recordNumber,
                        "paging": currentContext['paging'],
                        "fields": fieldList,
                        "parentkeyvalue": relationValue,
                        "conditions": optionalCondition,
                        "useoffset": true,
                        "useLimit": useLimit
                    });
                } catch (ex) {
                    if (ex == "_im_requath_request_") {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex, "EXCEPTION-12");
                    }
                }
            }
            return targetRecords;
        }

        function setIdValue(node) {
            var i, elementInfo, comp, overwrite = true;

            if (node.getAttribute('id') === null) {
                node.setAttribute('id', nextIdValue());
            } else {
                if (INTERMediator.elementIds.indexOf(node.getAttribute('id')) >= 0) {
                    elementInfo = INTERMediatorLib.getLinkedElementInfo(node);
                    for (i = 0; i < elementInfo.length; i++) {
                        comp = elementInfo[i].split(INTERMediator.separator);
                        if (comp[2] == "#id") {
                            overwrite = false;
                        }
                    }
                    if (overwrite) {
                        node.setAttribute('id', nextIdValue());
                    }
                }
                INTERMediator.elementIds.push(node.getAttribute('id'));
            }
            return node;
        }

        function nextIdValue() {
            INTERMediator.linkedElmCounter++;
            return currentIdValue();
        }

        function currentIdValue() {
            return 'IM' + INTERMediator.currentEncNumber + '-' + INTERMediator.linkedElmCounter;
        }

        function callbackForRepeaters(currentContext, node) {
            try {
                if (INTERMediatorOnPage.additionalExpandingRecordFinish[currentContext['name']]) {
                    INTERMediatorOnPage.additionalExpandingRecordFinish[currentContext['name']](node);
                    INTERMediator.setDebugMessage(
                        "Call the post enclosure method 'INTERMediatorOnPage.additionalExpandingRecordFinish["
                            + currentContext['name'] + "] with the context.", 2);
                }
            } catch (ex) {
                if (ex == "_im_requath_request_") {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex,
                        "EXCEPTION-33: hint: post-repeater of " + currentContext.name);
                }
            }
            try {
                if (INTERMediatorOnPage.expandingRecordFinish != null) {
                    INTERMediatorOnPage.expandingRecordFinish(currentContext['name'], newlyAddedNodes);
                    INTERMediator.setDebugMessage(
                        "Call INTERMediatorOnPage.expandingRecordFinish with the context: "
                            + currentContext['name'], 2);
                }

                if (currentContext['post-repeater']) {
                    INTERMediatorOnPage[currentContext['post-repeater']](newlyAddedNodes);

                    INTERMediator.setDebugMessage("Call the post repeater method 'INTERMediatorOnPage."
                        + currentContext['post-repeater'] + "' with the context: "
                        + currentContext['name'], 2);
                }
            } catch (ex) {
                if (ex == "_im_requath_request_") {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex, "EXCEPTION-23");
                }
            }

        }

        function callbackForEnclosure(currentContext, node) {
            try {
                if (INTERMediatorOnPage.additionalExpandingEnclosureFinish[currentContext['name']]) {
                    INTERMediatorOnPage.additionalExpandingEnclosureFinish[currentContext['name']](node);
                    INTERMediator.setDebugMessage(
                        "Call the post enclosure method 'INTERMediatorOnPage.additionalExpandingEnclosureFinish["
                            + currentContext['name'] + "] with the context.", 2);
                }
            } catch (ex) {
                if (ex == "_im_requath_request_") {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex,
                        "EXCEPTION-32: hint: post-enclosure of " + currentContext.name);
                }
            }
            try {
                if (INTERMediatorOnPage.expandingEnclosureFinish != null) {
                    INTERMediatorOnPage.expandingEnclosureFinish(currentContext['name'], node);
                    INTERMediator.setDebugMessage(
                        "Call INTERMediatorOnPage.expandingEnclosureFinish with the context: "
                            + currentContext['name'], 2);
                }
            } catch (ex) {
                if (ex == "_im_requath_request_") {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex, "EXCEPTION-21");
                }
            }
            try {
                if (currentContext['post-enclosure']) {
                    INTERMediatorOnPage[currentContext['post-enclosure']](node);
                    INTERMediator.setDebugMessage(
                        "Call the post enclosure method 'INTERMediatorOnPage." + currentContext['post-enclosure']
                            + "' with the context: " + currentContext['name'], 2);
                }
            } catch (ex) {
                if (ex == "_im_requath_request_") {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex,
                        "EXCEPTION-22: hint: post-enclosure of " + currentContext.name);
                }
            }
        }

        function collectRepeatersOriginal(node, repNodeTag) {
            var i, repeatersOriginal = [], children;

            children = node.childNodes; // Check all child node of the enclosure.
            for (i = 0; i < children.length; i++) {
                if (children[i].nodeType === 1 && children[i].tagName == repNodeTag) {
                    // If the element is a repeater.
                    repeatersOriginal.push(children[i]); // Record it to the array.
                }
            }
            return repeatersOriginal;
        }

        function collectRepeaters(repeatersOriginal) {
            var i, repeaters = [], inDocNode, parentOfRep, cloneNode;
            for (i = 0; i < repeatersOriginal.length; i++) {
                inDocNode = repeatersOriginal[i];
                parentOfRep = repeatersOriginal[i].parentNode;
                cloneNode = repeatersOriginal[i].cloneNode(true);
                repeaters.push(cloneNode);
                cloneNode.setAttribute('id', nextIdValue());
                parentOfRep.removeChild(inDocNode);
            }
            return repeaters;
        }

        function collectLinkDefinitions(linkedNodes) {
            var linkDefs = [], nodeDefs, j, k;
            for (j = 0; j < linkedNodes.length; j++) {
                nodeDefs = INTERMediatorLib.getLinkedElementInfo(linkedNodes[j]);
                if (nodeDefs !== null) {
                    for (k = 0; k < nodeDefs.length; k++) {
                        linkDefs.push(nodeDefs[k]);
                    }
                }
            }
            return linkDefs;
        }

        function tableVoting(linkDefs) {
            var j, nodeInfoArray, nodeInfoField, nodeInfoTable, maxVoted, maxTableName, tableName,
                nodeInfoTableIndex, context,
                tableVote = [],    // Containing editable elements or not.
                fieldList = []; // Create field list for database fetch.

            for (j = 0; j < linkDefs.length; j++) {
                nodeInfoArray = INTERMediatorLib.getNodeInfoArray(linkDefs[j]);
                nodeInfoField = nodeInfoArray['field'];
                nodeInfoTable = nodeInfoArray['table'];
                nodeInfoTableIndex = nodeInfoArray['tableindex'];   // Table name added "_im_index_" as the prefix.
                if (nodeInfoTable != IMLibLocalContext.contextName) {
                    if (nodeInfoField != null
                        && nodeInfoField.length != 0
                        && nodeInfoTable.length != 0
                        && nodeInfoTable != null) {
                        if (!fieldList[nodeInfoTableIndex]) {
                            fieldList[nodeInfoTableIndex] = [];
                        }
                        fieldList[nodeInfoTableIndex].push(nodeInfoField);
                        if (!tableVote[nodeInfoTableIndex]) {
                            tableVote[nodeInfoTableIndex] = 1;
                        } else {
                            ++tableVote[nodeInfoTableIndex];
                        }
                    } else {
                        INTERMediator.setErrorMessage(
                            INTERMediatorLib.getInsertedStringFromErrorNumber(1006, [linkDefs[j]]));
                        //   return null;
                    }
                }
            }
            maxVoted = -1;
            maxTableName = ''; // Which is the maximum voted table name.
            for (tableName in tableVote) {
                if (maxVoted < tableVote[tableName]) {
                    maxVoted = tableVote[tableName];
                    maxTableName = tableName.substring(10);
                }
            }
            context = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', maxTableName);
            return {targettable: context, fieldlist: fieldList["_im_index_" + maxTableName]};
        }

        function cloneEveryNodes(originalNodes) {
            var i, clonedNodes = [];
            for (i = 0; i < originalNodes.length; i++) {
                clonedNodes.push(originalNodes[i].cloneNode(true));
            }
            return clonedNodes;
        }

        function shouldDeleteNodeIds(repeatersOneRec) {
            var shouldDeleteNodes = [], i;
            for (i = 0; i < repeatersOneRec.length; i++) {
                setIdValue(repeatersOneRec[i]);
                shouldDeleteNodes.push(repeatersOneRec[i].getAttribute('id'));
            }
            return shouldDeleteNodes;
        }

        function setupDeleteButton(encNodeTag, repNodeTag, endOfRepeaters, currentContext, keyField, keyValue, foreignField, foreignValue, shouldDeleteNodes) {
            // Handling Delete buttons
            var buttonNode, thisId, deleteJSFunction, tdNodes, tdNode;

            if (!currentContext['repeat-control']
                || !currentContext['repeat-control'].match(/delete/i)) {
                return;
            }
            if (currentContext['relation']
                || currentContext['records'] === undefined
                || (currentContext['records'] > 1 && Number(INTERMediator.pagedSize) != 1)) {

                buttonNode = document.createElement('BUTTON');
                INTERMediatorLib.setClassAttributeToNode(buttonNode, "IM_Button_Delete");
                buttonNode.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[6]));
                thisId = 'IM_Button_' + buttonIdNum;
                buttonNode.setAttribute('id', thisId);
                buttonIdNum++;
                deleteJSFunction = function (a, b, c, d, e) {
                    var contextName = a, keyField = b, keyValue = c, removeNodes = d, confirming = e;

                    return function () {
                        IMLibUI.deleteButton(
                            contextName, keyField, keyValue, foreignField, foreignValue, removeNodes, confirming);
                    };
                };
                eventListenerPostAdding.push({
                    'id': thisId,
                    'event': 'click',
                    'todo': deleteJSFunction(
                        currentContext['name'],
                        keyField,
                        keyValue,
                        shouldDeleteNodes,
                        currentContext['repeat-control'].match(/confirm-delete/i))
                });

                // endOfRepeaters = repeatersOneRec[repeatersOneRec.length - 1];
                switch (encNodeTag) {
                    case 'TBODY':
                        tdNodes = endOfRepeaters.getElementsByTagName('TD');
                        tdNode = tdNodes[tdNodes.length - 1];
                        tdNode.appendChild(buttonNode);
                        break;
                    case 'UL':
                    case 'OL':
                        endOfRepeaters.appendChild(buttonNode);
                        break;
                    case 'DIV':
                    case 'SPAN':
                        if (repNodeTag == "DIV" || repNodeTag == "SPAN") {
                            endOfRepeaters.appendChild(buttonNode);
                        }
                        break;
                }
            } else {
                IMLibPageNavigation.deleteInsertOnNavi.push({
                    kind: 'DELETE',
                    name: currentContext['name'],
                    key: keyField,
                    value: keyValue,
                    confirm: currentContext['repeat-control'].match(/confirm-delete/i)
                });
            }
        }

        function setupInsertButton(currentContext, keyValue, node, relationValue) {
            var buttonNode, shouldRemove, enclosedNode, footNode, trNode, tdNode, liNode, divNode, insertJSFunction, i,
                firstLevelNodes, targetNodeTag, existingButtons, keyField, dbspec, thisId, encNodeTag, repNodeTag;

            encNodeTag = node.tagName;
            repNodeTag = INTERMediatorLib.repeaterTagFromEncTag(encNodeTag);

            if (currentContext['repeat-control'] && currentContext['repeat-control'].match(/insert/i)) {
                if (relationValue.length > 0 || !currentContext['paging'] || currentContext['paging'] === false) {
                    buttonNode = document.createElement('BUTTON');
                    INTERMediatorLib.setClassAttributeToNode(buttonNode, "IM_Button_Insert");
                    buttonNode.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[5]));
                    thisId = 'IM_Button_' + buttonIdNum;
                    buttonNode.setAttribute('id', thisId);
                    buttonIdNum++;
                    shouldRemove = [];
                    switch (encNodeTag) {
                        case 'TBODY':
                            targetNodeTag = "TFOOT";
                            if (currentContext['repeat-control'].match(/top/i)) {
                                targetNodeTag = "THEAD";
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
                            if (existingButtons.length == 0) {
                                trNode = document.createElement('TR');
                                tdNode = document.createElement('TD');
                                setIdValue(trNode);
                                footNode.appendChild(trNode);
                                trNode.appendChild(tdNode);
                                tdNode.appendChild(buttonNode);
                                shouldRemove = [trNode.getAttribute('id')];
                            }
                            break;
                        case 'UL':
                        case 'OL':
                            liNode = document.createElement('LI');
                            existingButtons = INTERMediatorLib.getElementsByClassName(liNode, 'IM_Button_Insert');
                            if (existingButtons.length == 0) {
                                liNode.appendChild(buttonNode);
                                if (currentContext['repeat-control'].match(/top/i)) {
                                    node.insertBefore(liNode, node.firstChild);
                                } else {
                                    node.appendChild(liNode);
                                }
                            }
                            break;
                        case 'DIV':
                        case 'SPAN':
                            if (repNodeTag == "DIV" || repNodeTag == "SPAN") {
                                divNode = document.createElement(repNodeTag);
                                existingButtons = INTERMediatorLib.getElementsByClassName(divNode, 'IM_Button_Insert');
                                if (existingButtons.length == 0) {
                                    divNode.appendChild(buttonNode);
                                    if (currentContext['repeat-control'].match(/top/i)) {
                                        node.insertBefore(divNode, node.firstChild);
                                    } else {
                                        node.appendChild(divNode);
                                    }
                                }
                            }
                            break;
                    }
                    insertJSFunction = function (a, b, c, d, e) {
                        var contextName = a, relationValue = b, nodeId = c, removeNodes = d, confirming = e;
                        return function () {
                            IMLibUI.insertButton(contextName, keyValue, relationValue, nodeId, removeNodes, confirming);
                        }
                    };

                    INTERMediatorLib.addEvent(
                        buttonNode,
                        'click',
                        insertJSFunction(
                            currentContext['name'],
                            relationValue,
                            node.getAttribute('id'),
                            shouldRemove,
                            currentContext['repeat-control'].match(/confirm-insert/i))
                    );

                } else {
                    dbspec = INTERMediatorOnPage.getDBSpecification();
                    if (dbspec["db-class"] != null && dbspec["db-class"] == "FileMaker_FX") {
                        keyField = currentContext["key"] ? currentContext["key"] : "-recid";
                    } else {
                        keyField = currentContext["key"] ? currentContext["key"] : "id";
                    }
                    IMLibPageNavigation.deleteInsertOnNavi.push({
                        kind: 'INSERT',
                        name: currentContext['name'],
                        key: keyField,
                        confirm: currentContext['repeat-control'].match(/confirm-insert/i)
                    });
                }
            }
        }

        function getEnclosedNode(rootNode, tableName, fieldName) {
            var i, j, nodeInfo, nInfo, children, r;

            if (rootNode.nodeType == 1) {
                nodeInfo = INTERMediatorLib.getLinkedElementInfo(rootNode);
                for (j = 0; j < nodeInfo.length; j++) {
                    nInfo = INTERMediatorLib.getNodeInfoArray(nodeInfo[j]);
                    if (nInfo['table'] == tableName && nInfo['field'] == fieldName) {
                        return rootNode;
                    }
                }
            }
            children = rootNode.childNodes; // Check all child node of the enclosure.
            for (i = 0; i < children.length; i++) {
                r = getEnclosedNode(children[i], tableName, fieldName);
                if (r !== null) {
                    return r;
                }
            }
            return null;
        }

        function appendCredit() {
            var bodyNode, creditNode, cNode, spNode, aNode;

            if (document.getElementById('IM_CREDIT') === null) {
                bodyNode = document.getElementsByTagName('BODY')[0];
                creditNode = document.createElement('div');
                bodyNode.appendChild(creditNode);
                creditNode.setAttribute('id', 'IM_CREDIT');
                creditNode.setAttribute('class', 'IM_CREDIT');

                cNode = document.createElement('div');
                creditNode.appendChild(cNode);
                cNode.style.backgroundColor = '#F6F7FF';
                cNode.style.height = '2px';

                cNode = document.createElement('div');
                creditNode.appendChild(cNode);
                cNode.style.backgroundColor = '#EBF1FF';
                cNode.style.height = '2px';

                cNode = document.createElement('div');
                creditNode.appendChild(cNode);
                cNode.style.backgroundColor = '#E1EAFF';
                cNode.style.height = '2px';

                cNode = document.createElement('div');
                creditNode.appendChild(cNode);
                cNode.setAttribute('align', 'right');
                cNode.style.backgroundColor = '#D7E4FF';
                cNode.style.padding = '2px';
                spNode = document.createElement('span');
                cNode.appendChild(spNode);
                cNode.style.color = '#666666';
                cNode.style.fontSize = '7pt';
                aNode = document.createElement('a');
                aNode.appendChild(document.createTextNode('INTER-Mediator'));
                aNode.setAttribute('href', 'http://inter-mediator.org/');
                aNode.setAttribute('target', '_href');
                spNode.appendChild(document.createTextNode('Generated by '));
                spNode.appendChild(aNode);
                spNode.appendChild(document.createTextNode(' Ver.@@@@2@@@@(@@@@1@@@@)'));
            }
        }
    },

    getLocalProperty: function (localKey, defaultValue) {
        var value;
        value = IMLibLocalContext.getValue(localKey);
        return value === null ? defaultValue : value;
    },

    setLocalProperty: function (localKey, value) {
        IMLibLocalContext.setValue(localKey, value);
    }
};

INTERMediator.propertyIETridentSetup();

if (INTERMediator.isIE && INTERMediator.ieVersion < 9) {
    INTERMediator.startFrom = 0;
    INTERMediator.pagedSize = 0;
    INTERMediator.additionalCondition = {};
    INTERMediator.additionalSortKey = {};
} else {
    Object.defineProperty(INTERMediator, 'startFrom', {
        get: function () {
            return INTERMediator.getLocalProperty("_im_startFrom", 0);
        },
        set: function (value) {
            INTERMediator.setLocalProperty("_im_startFrom", value);
        }
    });
    Object.defineProperty(INTERMediator, 'pagedSize', {
        get: function () {
            return INTERMediator.getLocalProperty("_im_pagedSize", 0);
        },
        set: function (value) {
            INTERMediator.setLocalProperty("_im_pagedSize", value);
        }
    });
    Object.defineProperty(INTERMediator, 'additionalCondition', {
        get: function () {
            return INTERMediator.getLocalProperty("_im_additionalCondition", {});
        },
        set: function (value) {
            INTERMediator.setLocalProperty("_im_additionalCondition", value);
        }
    });
    Object.defineProperty(INTERMediator, 'additionalSortKey', {
        get: function () {
            return INTERMediator.getLocalProperty("_im_additionalSortKey", {});
        },
        set: function (value) {
            INTERMediator.setLocalProperty("_im_additionalSortKey", value);
        }
    });
}

if (!INTERMediator.additionalCondition) {
    INTERMediator.additionalCondition = {};
}


if (!INTERMediator.additionalSortKey) {
    INTERMediator.additionalSortKey = {};
}