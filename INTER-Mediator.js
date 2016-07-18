/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

//'use strict';

var INTERMediator = {
    /*
     Properties
     */
    debugMode: false,
    // Show the debug messages at the top of the page.
    separator: '@',
    // This must be referred as 'INTERMediator.separator'. Don't use 'this.separator'
    defDivider: '|',
    // Same as the 'separator'.
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
    totalRecordCount: null,  // for DB_FileMaker_FX
    currentEncNumber: 0,
    isIE: false,
    isTrident: false,
    ieVersion: -1,
    titleAsLinkInfo: true,
    classAsLinkInfo: true,
    isDBDataPreferable: false,
    noRecordClassName: '_im_for_noresult_',
    //   nullAcceptable: true,

    rootEnclosure: null,
    // Storing to retrieve the page to initial condition.
    // {node:xxx, parent:xxx, currentRoot:xxx, currentAfter:xxxx}

    useSessionStorage: true,
    // Use sessionStorage for the Local Context instead of Cookie.

    errorMessages: [],
    debugMessages: [],

    partialConstructing: false,
    linkedElmCounter: 0,
    pusherObject: null,
    buttonIdNum: 0,
    masterNodeOriginalDisplay: 'block',
    detailNodeOriginalDisplay: 'none',
    pusherAvailable: false,

    dateTimeFunction: false,
    postOnlyNodes: null,
    postOnlyNumber: 1,

    errorMessageByAlert: false,
    errorMessageOnAlert: null,

    isTablet: false,
    isMobile: false,

    crossTableStage: 0, // 0: not cross table, 1: column label, 2: row label, 3 interchange cells

    /* These following properties moved to the setter/getter architecture, and defined out side of this object.
     startFrom: 0,pagedSize: 0,additionalCondition: {},additionalSortKey: {},
     */

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
                console.log('INTER-Mediator[DEBUG:%s]: %s', new Date(), message);
            }
        }
    },

    setErrorMessage: function (ex, moreMessage) {
        moreMessage = moreMessage === undefined ? '' : (' - ' + moreMessage);

        if (INTERMediator.errorMessageByAlert) {
            alert(INTERMediator.errorMessageOnAlert === null ?
                (ex + moreMessage) : INTERMediator.errorMessageOnAlert);
        }

        if ((typeof ex == 'string' || ex instanceof String)) {
            INTERMediator.errorMessages.push(ex + moreMessage);
            if (typeof console != 'undefined') {
                console.error('INTER-Mediator[ERROR]: %s', ex + moreMessage);
            }
        } else {
            if (ex.message) {
                INTERMediator.errorMessages.push(ex.message + moreMessage);
                if (typeof console != 'undefined') {
                    console.error('INTER-Mediator[ERROR]: %s', ex.message + moreMessage);
                }
            }
            if (ex.stack && typeof console != 'undefined') {
                console.error(ex.stack);
            }
        }
    },

    flushMessage: function () {
        var debugNode, title, body, i, j, lines, clearButton, tNode, target;

        if (INTERMediator.errorMessageByAlert) {
            INTERMediator.supressErrorMessageOnPage = true;
        }
        if (!INTERMediator.supressErrorMessageOnPage &&
            INTERMediator.errorMessages.length > 0) {
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
                '============ERROR MESSAGE on ' + new Date() + '============'));
            debugNode.appendChild(document.createElement('hr'));
            for (i = 0; i < INTERMediator.errorMessages.length; i++) {
                lines = INTERMediator.errorMessages[i].split(IMLib.nl_char);
                for (j = 0; j < lines.length; j++) {
                    if (j > 0) {
                        debugNode.appendChild(document.createElement('br'));
                    }
                    debugNode.appendChild(document.createTextNode(lines[j]));
                }
                debugNode.appendChild(document.createElement('hr'));
            }
        }
        if (!INTERMediator.supressDebugMessageOnPage &&
            INTERMediator.debugMode &&
            INTERMediator.debugMessages.length > 0) {
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
                '============DEBUG INFO on ' + new Date() + '============ '));
            if (INTERMediatorOnPage.getEditorPath()) {
                var aLink = document.createElement('a');
                aLink.setAttribute('href', INTERMediatorOnPage.getEditorPath());
                aLink.appendChild(document.createTextNode('Definition File Editor'));
                debugNode.appendChild(aLink);
            }
            debugNode.appendChild(document.createElement('hr'));
            for (i = 0; i < INTERMediator.debugMessages.length; i++) {
                lines = INTERMediator.debugMessages[i].split(IMLib.nl_char);
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

    // Referred from https://w3g.jp/blog/js_browser_sniffing2015
    propertyW3CUserAgentSetup: function () {
        var u = window.navigator.userAgent.toLowerCase();
        INTERMediator.isTablet =
            (u.indexOf('windows') != -1 && u.indexOf('touch') != -1 && u.indexOf('tablet pc') == -1)
            || u.indexOf('ipad') != -1
            || (u.indexOf('android') != -1 && u.indexOf('mobile') == -1)
            || (u.indexOf('firefox') != -1 && u.indexOf('tablet') != -1)
            || u.indexOf('kindle') != -1
            || u.indexOf('silk') != -1
            || u.indexOf('playbook') != -1;
        INTERMediator.isMobile =
            (u.indexOf('windows') != -1 && u.indexOf('phone') != -1)
            || u.indexOf('iphone') != -1
            || u.indexOf('ipod') != -1
            || (u.indexOf('android') != -1 && u.indexOf('mobile') != -1)
            || (u.indexOf('firefox') != -1 && u.indexOf('mobile') != -1)
            || u.indexOf('blackberry') != -1;
    },

    initialize: function () {
        INTERMediatorOnPage.removeCookie('_im_localcontext');
//      INTERMediatorOnPage.removeCookie('_im_username');
//      INTERMediatorOnPage.removeCookie('_im_credential');
//      INTERMediatorOnPage.removeCookie('_im_mediatoken');

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
            if (INTERMediatorOnPage.isFinishToConstruct) {
                return;
            }
            INTERMediatorOnPage.isFinishToConstruct = true;

            timerTask = function () {
                INTERMediator.constructMain(true);
            };
        } else {
            timerTask = function () {
                INTERMediator.constructMain(indexOfKeyFieldObject);
            };
        }
        setTimeout(timerTask, 0);
    },

    /* ===========================================================

     INTERMediator.constructMain() is the public entry for generating page.
     This has 3-way calling conventions.

     [1] INTERMediator.constructMain() or INTERMediator.constructMain(true)
     This happens to generate page from scratch.

     [2] INTERMediator.constructMain(context)
     This will be reconstracted to nodes of the "context" parameter.
     The context parameter should be refered to a IMLIbContext object.

     [3] INTERMediator.constructMain(context, recordset)
     This will append nodes to the enclocure of the "context" as a repeater.
     The context parameter should be refered to a IMLIbContext object.
     The recordset parameter is the newly created record as the form of an array of an dictionary.

     */
    constructMain: function (updateRequiredContext, recordset) {
        var i, theNode, postSetFields = [],
            eventListenerPostAdding = [], isInsidePostOnly, nameAttrCounter = 1, imPartsShouldFinished = [],
            isAcceptNotify = false, originalNodes, appendingNodesAtLast, parentNode, sybilingNode;

        INTERMediator.crossTableStage = 0;
        appendingNodesAtLast = [];
        IMLibEventResponder.setup();
        INTERMediatorOnPage.retrieveAuthInfo();
        try {
            if (Pusher.VERSION) {
                INTERMediator.pusherAvailable = true;
                if (!INTERMediatorOnPage.clientNotificationKey) {
                    INTERMediator.setErrorMessage(
                        Error('Pusher Configuration Error'), INTERMediatorOnPage.getMessages()[1039]);
                    INTERMediator.pusherAvailable = false;
                }
            }
        } catch (ex) {
            INTERMediator.pusherAvailable = false;
            if (INTERMediatorOnPage.clientNotificationKey) {
                INTERMediator.setErrorMessage(
                    Error('Pusher Configuration Error'), INTERMediatorOnPage.getMessages()[1038]);
            }
        }

        try {
            if (updateRequiredContext === true || updateRequiredContext === undefined) {
                IMLibPageNavigation.deleteInsertOnNavi = [];
                this.partialConstructing = false;
                INTERMediator.buttonIdNum = 1;
                IMLibContextPool.clearAll();
                pageConstruct();
            } else {
                IMLibPageNavigation.deleteInsertOnNavi = [];
                this.partialConstructing = true;
                isInsidePostOnly = false;
                postSetFields = [];

                try {
                    if (!recordset) {
                        updateRequiredContext.removeContext();
                        originalNodes = updateRequiredContext.original;
                        for (i = 0; i < originalNodes.length; i++) {
                            updateRequiredContext.enclosureNode.appendChild(originalNodes[i].cloneNode(true));
                        }
                        seekEnclosureNode(
                            updateRequiredContext.enclosureNode,
                            updateRequiredContext.foreignValue,
                            updateRequiredContext.dependingParentObjectInfo,
                            updateRequiredContext);
                    }
                    else {
                        expandRepeaters(
                            updateRequiredContext,
                            updateRequiredContext.enclosureNode,
                            {recordset: recordset, targetTotalCount: 1, targetCount: 1}
                        );
                    }
                } catch (ex) {
                    if (ex == '_im_requath_request_') {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex, 'EXCEPTION-8');
                    }
                }

                for (i = 0; i < postSetFields.length; i++) {
                    if (postSetFields[i]['id'] && document.getElementById(postSetFields[i]['id'])) {
                        document.getElementById(postSetFields[i]['id']).value = postSetFields[i]['value'];
                    }
                }
                IMLibCalc.updateCalculationFields();
                //IMLibPageNavigation.navigationSetup();
                /*
                 If the pagination control should be setup, the property IMLibPageNavigation.deleteInsertOnNavi
                 to maintain to be a valid data.
                 */
            }
        } catch (ex) {
            if (ex == '_im_requath_request_') {
                if (INTERMediatorOnPage.requireAuthentication) {
                    if (!INTERMediatorOnPage.isComplementAuthData()) {
                        INTERMediatorOnPage.clearCredentials();
                        INTERMediatorOnPage.authenticating(
                            function () {
                                INTERMediator.constructMain(updateRequiredContext);
                            }
                        );
                        return;
                    }
                }
            } else {
                INTERMediator.setErrorMessage(ex, 'EXCEPTION-7');
            }
        }

        for (i = 0; i < imPartsShouldFinished.length; i++) {
            imPartsShouldFinished[i].finish();
        }

        INTERMediatorOnPage.hideProgress();

        for (i = 0; i < appendingNodesAtLast.length; i++) {
            theNode = appendingNodesAtLast[i].targetNode;
            parentNode = appendingNodesAtLast[i].parentNode;
            sybilingNode = appendingNodesAtLast[i].siblingNode;
            if (theNode && parentNode) {
                if (sybilingNode) {
                    parentNode.insertBefore(theNode, sybilingNode);
                } else {
                    parentNode.appendChild(theNode);
                }
            }
        }

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
        INTERMediatorOnPage.isFinishToConstruct = false;

        INTERMediator.flushMessage(); // Show messages

        /* --------------------------------------------------------------------
         This function is called on case of below.

         [1] INTERMediator.constructMain() or INTERMediator.constructMain(true)
         */
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
                seekEnclosureNode(bodyNode, null, null, null);
            } catch (ex) {
                if (ex == '_im_requath_request_') {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex, 'EXCEPTION-9');
                }
            }


            // After work to set up popup menus.
            for (i = 0; i < postSetFields.length; i++) {
                if (postSetFields[i]['value'] === '' &&
                    document.getElementById(postSetFields[i]['id']).tagName == 'SELECT') {
                    // for compatibility with Firefox when the value of select tag is empty.
                    emptyElement = document.createElement('option');
                    emptyElement.setAttribute('value', '');
                    document.getElementById(postSetFields[i]['id']).insertBefore(
                        emptyElement, document.getElementById(postSetFields[i]['id']).firstChild);
                }
                document.getElementById(postSetFields[i]['id']).value = postSetFields[i]['value'];
            }
            IMLibLocalContext.bindingDescendant(document.documentElement);
            IMLibCalc.updateCalculationFields();
            IMLibPageNavigation.navigationSetup();

            if (isAcceptNotify && INTERMediator.pusherAvailable) {
                var channelName = INTERMediatorOnPage.clientNotificationIdentifier();
                var appKey = INTERMediatorOnPage.clientNotificationKey();
                if (appKey && appKey != '_im_key_isnt_supplied' && !INTERMediator.pusherObject) {
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
                        INTERMediator.setErrorMessage(ex, 'EXCEPTION-47');
                    }
                }
            }
            appendCredit();
        }

        /** --------------------------------------------------------------------
         * Seeking nodes and if a node is an enclosure, proceed repeating.
         */

        function seekEnclosureNode(node, currentRecord, parentObjectInfo, currentContextObj) {
            var children, className, i, attr;
            if (node.nodeType === 1) { // Work for an element
                try {
                    if (INTERMediatorLib.isEnclosure(node, false)) { // Linked element and an enclosure
                        className = INTERMediatorLib.getClassAttributeFromNode(node);
                        attr = node.getAttribute('data-im-control');
                        if ((className && className.match(/_im_post/)) ||
                            (attr && attr.indexOf('post') >= 0)) {
                            setupPostOnlyEnclosure(node);
                        } else {
                            if (INTERMediator.isIE) {
                                try {
                                    expandEnclosure(node, currentRecord, parentObjectInfo, currentContextObj);
                                } catch (ex) {
                                    if (ex == '_im_requath_request_') {
                                        throw ex;
                                    }
                                }
                            } else {
                                expandEnclosure(node, currentRecord, parentObjectInfo, currentContextObj);
                            }
                        }
                    } else {
                        children = node.childNodes; // Check all child nodes.
                        if (children) {
                            for (i = 0; i < children.length; i++) {
                                if (children[i].nodeType === 1) {
                                    seekEnclosureNode(children[i], currentRecord, parentObjectInfo, currentContextObj);
                                }
                            }
                        }
                    }
                } catch (ex) {
                    if (ex == '_im_requath_request_') {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex, 'EXCEPTION-10');
                    }
                }

            }
        }

        /* --------------------------------------------------------------------
         Post only mode.
         */
        function setupPostOnlyEnclosure(node) {
            var nodes, postNodes;
            postNodes = INTERMediatorLib.getElementsByClassNameOrDataAttr(node, '_im_post');
            for (i = 0; i < postNodes.length; i++) {
                if (postNodes[i].tagName === 'BUTTON' ||
                    (postNodes[i].tagName === 'INPUT' &&
                    (postNodes[i].getAttribute('type').toLowerCase() === 'button' ||
                    postNodes[i].getAttribute('type').toLowerCase() === 'submit'))) {
                    INTERMediatorLib.addEvent(postNodes[i], 'click',
                        (function () {
                            var targetNode = postNodes[i];
                            return function () {
                                IMLibUI.clickPostOnlyButton(targetNode);
                            };
                        })());
                }
            }
            nodes = node.childNodes;

            isInsidePostOnly = true;
            for (i = 0; i < nodes.length; i++) {
                seekEnclosureInPostOnly(nodes[i]);
            }
            isInsidePostOnly = false;
            // -------------------------------------------
            function seekEnclosureInPostOnly(node) {
                var children, wInfo, i;
                if (node.nodeType === 1) { // Work for an element
                    try {
                        if (node.getAttribute('data-im')) { // Linked element
                            if (!node.id) {
                                node.id = 'IMPOST-' + INTERMediator.postOnlyNumber;
                                INTERMediator.postOnlyNumber++;
                            }
                            INTERMediatorLib.addEvent(node, 'blur', function (e) {
                                var idValue = node.id;
                                IMLibUI.valueChange(idValue, true);
                            });
                        }

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
                            expandEnclosure(node, null, null, null);
                        } else {
                            children = node.childNodes; // Check all child nodes.
                            for (i = 0; i < children.length; i++) {
                                seekEnclosureInPostOnly(children[i]);
                            }
                        }
                    } catch (ex) {
                        if (ex == '_im_requath_request_') {
                            throw ex;
                        } else {
                            INTERMediator.setErrorMessage(ex, 'EXCEPTION-11');
                        }
                    }
                }
            }
        }

        /** --------------------------------------------------------------------
         * Expanding an enclosure.
         */

        function expandEnclosure(node, currentRecord, parentObjectInfo, currentContextObj) {
            var recId, repNodeTag, repeatersOriginal;
            var imControl = node.getAttribute('data-im-control');

            if (currentContextObj && currentContextObj.contextName && currentRecord[currentContextObj.contextName]) {
                // for FileMaker portal access mode
                recId = currentRecord[currentContextObj.contextName][currentContextObj.contextName + '::-recid'];
                currentRecord = currentRecord[currentContextObj.contextName][recId];
            }

            if (imControl && imControl.match(/cross-table/)) {   // Cross Table
                expandCrossTableEnclosure(node, currentRecord, parentObjectInfo, currentContextObj);
            } else {    // Enclosure Processing as usual.
                repNodeTag = INTERMediatorLib.repeaterTagFromEncTag(node.tagName);
                repeatersOriginal = collectRepeatersOriginal(node, repNodeTag); // Collecting repeaters to this array.
                enclosureProcessing(node, repeatersOriginal, currentRecord, parentObjectInfo, currentContextObj);
            }

            /** --------------------------------------------------------------------
             * Expanding enclosure as usual (means not 'cross tabole').
             */
            function enclosureProcessing(enclosureNode,
                                         repeatersOriginal,
                                         currentRecord,
                                         parentObjectInfo,
                                         currentContextObj,
                                         procBeforeRetirieve,
                                         customExpandRepeater) {
                var linkedNodes, repeaters, linkDefs, voteResult, currentContextDef,
                    fieldList, joinField, relationDef, index, fieldName, i, ix, targetRecords, newNode,
                    keyValue, selectedNode, calcDef, calcFields, contextObj = null;

                // encNodeTag = enclosureNode.tagName;
                // repNodeTag = INTERMediatorLib.repeaterTagFromEncTag(encNodeTag);
                // repeatersOriginal = collectRepeatersOriginal(enclosureNode, repNodeTag); // Collecting repeaters to this array.
                repeaters = collectRepeaters(repeatersOriginal);  // Collecting repeaters to this array.
                linkedNodes = INTERMediatorLib.seekLinkedAndWidgetNodes(repeaters, true).linkedNode;
                linkDefs = collectLinkDefinitions(linkedNodes);
                voteResult = tableVoting(linkDefs);
                currentContextDef = voteResult.targettable;
                INTERMediator.currentEncNumber++;

                if (!enclosureNode.getAttribute('id')) {
                    enclosureNode.setAttribute('id', nextIdValue());
                }

                if (!currentContextDef) {
                    for (i = 0; i < repeatersOriginal.length; i++) {
                        newNode = enclosureNode.appendChild(repeatersOriginal[i]);

                        // for compatibility with Firefox
                        if (repeatersOriginal[i].getAttribute('selected') !== null) {
                            selectedNode = newNode;
                        }
                        if (selectedNode !== undefined) {
                            selectedNode.selected = true;
                        }

                        seekEnclosureNode(newNode, null, enclosureNode, currentContextObj);
                    }
                } else {
                    contextObj = new IMLibContext(currentContextDef['name']);
                    contextObj.enclosureNode = enclosureNode;
                    contextObj.repeaterNodes = repeaters;
                    contextObj.original = repeatersOriginal;
                    contextObj.parentContext = currentContextObj;
                    contextObj.sequencing = true;

                    fieldList = []; // Create field list for database fetch.
                    calcDef = currentContextDef['calculation'];
                    calcFields = [];
                    for (ix in calcDef) {
                        calcFields.push(calcDef[ix]['field']);
                    }
                    for (i = 0; i < voteResult.fieldlist.length; i++) {
                        if (!calcFields[voteResult.fieldlist[i]]) {
                            calcFields.push(voteResult.fieldlist[i]);
                        }
                        fieldList.push(voteResult.fieldlist[i]);
                    }
                    if (currentRecord) {
                        try {
                            relationDef = currentContextDef['relation'];
                            contextObj.setOriginal(repeatersOriginal);
                            if (relationDef) {
                                for (index in relationDef) {
                                    if (Boolean(relationDef[index].portal) === true) {
                                        currentContextDef['portal'] = true;
                                    }
                                    joinField = relationDef[index]['join-field'];
                                    contextObj.addForeignValue(joinField, currentRecord[joinField]);
                                    for (fieldName in parentObjectInfo) {
                                        if (fieldName == relationDef[index]['join-field']) {
                                            contextObj.addDependingObject(parentObjectInfo[fieldName]);
                                            contextObj.dependingParentObjectInfo =
                                                JSON.parse(JSON.stringify(parentObjectInfo));
                                        }
                                    }
                                }
                            }
                        } catch (ex) {
                            if (ex == '_im_requath_request_') {
                                throw ex;
                            } else {
                                INTERMediator.setErrorMessage(ex, 'EXCEPTION-25');
                            }
                        }
                    }

                    if (Boolean(currentContextDef.portal) === true) {
                        currentContextDef['currentrecord'] = currentRecord;
                        keyValue = currentRecord['-recid'];
                    }
                    if (procBeforeRetirieve) {
                        procBeforeRetirieve(contextObj);
                    }
                    targetRecords = retrieveDataForEnclosure(currentContextDef, fieldList, contextObj.foreignValue);
                    contextObj.storeRecords(targetRecords);
                    callbackForAfterQueryStored(currentContextDef, contextObj);
                    if (customExpandRepeater == undefined) {
                        contextObj.registeredId = targetRecords.registeredId;
                        contextObj.nullAcceptable = targetRecords.nullAcceptable;
                        isAcceptNotify |= !(INTERMediatorOnPage.notifySupport === false);
                        expandRepeaters(contextObj, enclosureNode, targetRecords);
                        setupInsertButton(currentContextDef, keyValue, enclosureNode, contextObj.foreignValue);
                        setupBackNaviButton(contextObj, enclosureNode);
                        callbackForEnclosure(currentContextDef, enclosureNode);
                    } else {
                        customExpandRepeater(contextObj, targetRecords);
                    }
                    contextObj.sequencing = false;
                }
                return contextObj;
            }

            /** --------------------------------------------------------------------
             * expanding enclosure for cross table
             */
            function expandCrossTableEnclosure(node, currentRecord, parentObjectInfo, currentContextObj) {
                var i, j, colArray, rowArray, nodeForKeyValues, record, targetRepeater, lineNode, colContext,
                    rowContext, appendingNode, trNodes, repeaters, linkedNodes, linkDefs,
                    crossCellContext, labelKeyColumn, labelKeyRow;

                // Collecting 4 parts of cross table.
                var ctComponentNodes = crossTableComponents(node);
                if (ctComponentNodes.length != 4) {
                    throw 'Exception-xx: Cross Table Components aren\'t prepared.';
                }

                // Remove all nodes under the TBODY tagged node.
                while (node.childNodes.length > 0) {
                    node.removeChild(node.childNodes[0]);
                }

                // Decide the context for cross point cell
                repeaters = collectRepeaters([ctComponentNodes[3].cloneNode(true)]);
                linkedNodes = INTERMediatorLib.seekLinkedAndWidgetNodes(repeaters, true).linkedNode;
                linkDefs = collectLinkDefinitions(linkedNodes);
                crossCellContext = tableVoting(linkDefs).targettable;
                labelKeyColumn = crossCellContext['relation'][0]['join-field'];
                labelKeyRow = crossCellContext['relation'][1]['join-field'];

                // Create the first row
                INTERMediator.crossTableStage = 1;
                lineNode = document.createElement('TR');
                targetRepeater = ctComponentNodes[0].cloneNode(true);
                lineNode.appendChild(targetRepeater);
                node.appendChild(lineNode);

                // Append the column context in the first row
                targetRepeater = ctComponentNodes[1].cloneNode(true);
                colContext = enclosureProcessing(lineNode, [targetRepeater], null, parentObjectInfo, currentContextObj);
                colArray = colContext.indexingArray(labelKeyColumn);

                // Create second and following rows, and the first columns are appended row context
                INTERMediator.crossTableStage = 2;
                targetRepeater = ctComponentNodes[2].cloneNode(true);
                lineNode = document.createElement('TR');
                lineNode.appendChild(targetRepeater);
                rowContext = enclosureProcessing(node, [lineNode], null, parentObjectInfo, currentContextObj);
                rowArray = rowContext.indexingArray(labelKeyRow);

                // Create all cross point cell
                INTERMediator.crossTableStage = 3;
                targetRepeater = ctComponentNodes[3].cloneNode(true);
                nodeForKeyValues = {};
                trNodes = node.getElementsByTagName('TR');
                for (i = 1; i < trNodes.length; i += 1) {
                    for (j = 0; j < colArray.length; j += 1) {
                        appendingNode = targetRepeater.cloneNode(true);
                        trNodes[i].appendChild(appendingNode);
                        setIdValue(appendingNode);
                        if (!nodeForKeyValues[colArray[j]]) {
                            nodeForKeyValues[colArray[j]] = {};
                        }
                        nodeForKeyValues[colArray[j]][rowArray[i - 1]] = appendingNode;
                    }
                }
                setIdValue(node);
                enclosureProcessing(node, [targetRepeater], null, parentObjectInfo, currentContextObj,
                    function (context) {
                        var currentContextDef = context.getContextDef();
                        INTERMediator.addCondition(currentContextDef.name, {
                            field: currentContextDef['relation'][0]['foreign-key'],
                            operator: 'IN',
                            value: colArray,
                            onetime: true
                        });
                        INTERMediator.addCondition(currentContextDef.name, {
                            field: currentContextDef['relation'][1]['foreign-key'],
                            operator: 'IN',
                            value: rowArray,
                            onetime: true
                        });
                    },
                    function (contextObj, targetRecords) {
                        var dataKeyColumn, dataKeyRow, currentContextDef, ix,
                            linkedElements, targetNode, setupResult;
                        currentContextDef = contextObj.getContextDef();
                        dataKeyColumn = currentContextDef['relation'][0]['foreign-key'];
                        dataKeyRow = currentContextDef['relation'][1]['foreign-key'];
                        if (targetRecords.recordset) {
                            for (ix = 0; ix < targetRecords.recordset.length; ix++) { // for each record
                                record = targetRecords.recordset[ix];
                                if (nodeForKeyValues[record[dataKeyColumn]]
                                    && nodeForKeyValues[record[dataKeyColumn]][record[dataKeyRow]]) {
                                    targetNode = nodeForKeyValues[record[dataKeyColumn]][record[dataKeyRow]];
                                    if (targetNode) {
                                        linkedElements = INTERMediatorLib.seekLinkedAndWidgetNodes([targetNode], false);
                                        setupResult = setupLinkedNode([targetNode], linkedElements, contextObj, targetRecords.recordset, ix);
                                    }
                                }
                            }
                        }

                    });
            } // The end of function expandCrossTableEnclosure().

            // Detect cross table components in a tbody enclosure.
            function crossTableComponents(node) {
                var components = [], count = 0;
                repeatCTComponents(node.childNodes);
                return components;

                function repeatCTComponents(nodes) {
                    var childNodes, i;
                    for (i = 0; i < nodes.length; i++) {
                        if (nodes[i].nodeType == 1 && (nodes[i].tagName == 'TH' || nodes[i].tagName == 'TD')) {
                            components[count] = nodes[i];
                            count += 1;
                        } else {
                            childNodes = nodes[i].childNodes;
                            if (childNodes) {
                                repeatCTComponents(childNodes);
                            }
                        }
                    }
                }
            }
        }

        /** --------------------------------------------------------------------
         * Set the value to node and context.
         */
        function setupLinkedNode(repeatersOneRec, linkedElements, contextObj, targetRecordset, ix) {
            var currentWidgetNodes, currentLinkedNodes, nInfo, currentContextDef,
                j, keyField, usePortal = false, foreignField, foreignValue, foreignFieldValue, keyValue, keyingValue,
                k, nodeId, curVal, replacedNode, typeAttr, children, wInfo, nameTable,
                idValuesForFieldName = {}, shouldDeleteNodes = [],
                nodeTag, linkInfoArray, nameTableKey, nameNumber, nameAttr, isContext = false, curTarget;

            currentContextDef = contextObj.getContextDef();
            try {
                currentWidgetNodes = linkedElements.widgetNode;
                currentLinkedNodes = linkedElements.linkedNode;
                for (i = 0; i < repeatersOneRec.length; i++) {
                    setIdValue(repeatersOneRec[i]);
                    shouldDeleteNodes.push(repeatersOneRec[i].getAttribute('id'));
                }

                if (INTERMediatorOnPage.dbClassName === 'DB_FileMaker_FX') {
                    keyField = currentContextDef['key'] ? currentContextDef['key'] : '-recid';
                } else {
                    keyField = currentContextDef['key'] ? currentContextDef['key'] : 'id';
                }

                if (currentContextDef['relation']) {
                    for (i = 0; i < Object.keys(currentContextDef['relation']).length; i++) {
                        if (currentContextDef['relation'][i]['portal'] &&
                            Number(currentContextDef['relation'][i]['portal']) === 1) {
                            usePortal = true;
                        }
                    }
                }
                if (usePortal === true) {
                    keyField = '-recid';
                    foreignField = currentContextDef['name'] + '::-recid';
                    foreignValue = targetRecordset[ix][foreignField];
                    foreignFieldValue = foreignField + '=' + foreignValue;
                } else {
                    foreignFieldValue = '=';
                    foreignValue = null;
                }
                if (targetRecordset[ix] && (targetRecordset[ix][keyField] || targetRecordset[ix][keyField] === 0)) {
                    keyValue = targetRecordset[ix][keyField];
                    if (keyField && !keyValue) {
                        //INTERMediator.setErrorMessage('The value of the key field is null.',
                        //    'This No.['+ix+'] record will should be ignored.');
                        keyValue = ix;
                    }
                    keyingValue = keyField + '=' + keyValue;

                    for (k = 0; k < currentLinkedNodes.length; k++) {
                        // for each linked element
                        nodeId = currentLinkedNodes[k].getAttribute('id');
                        replacedNode = setIdValue(currentLinkedNodes[k]);
                        typeAttr = replacedNode.getAttribute('type');
                        if (typeAttr == 'checkbox' || typeAttr == 'radio') {
                            children = replacedNode.parentNode.childNodes;
                            for (i = 0; i < children.length; i++) {
                                if (children[i].nodeType === 1 && children[i].tagName == 'LABEL' &&
                                    nodeId == children[i].getAttribute('for')) {
                                    children[i].setAttribute('for', replacedNode.getAttribute('id'));
                                    break;
                                }
                            }
                        }
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
                }
            } catch (ex) {
                if (ex == '_im_requath_request_') {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex, 'EXCEPTION-101');
                }
            }

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
                            nameAttrCounter++;
                        }
                        nameNumber = nameTable[nameTableKey];
                        nameAttr = currentLinkedNodes[k].getAttribute('name');
                        if (nameAttr) {
                            currentLinkedNodes[k].setAttribute('name', nameAttr + '-' + nameNumber);
                        } else {
                            currentLinkedNodes[k].setAttribute('name', 'IM-R-' + nameNumber);
                        }
                    }
                    for (j = 0; j < linkInfoArray.length; j++) {
                        nInfo = INTERMediatorLib.getNodeInfoArray(linkInfoArray[j]);
                        curVal = targetRecordset[ix][nInfo['field']];
                        if (!INTERMediator.isDBDataPreferable || curVal !== null) {
                            IMLibCalc.updateCalculationInfo(
                                contextObj, keyingValue, currentContextDef, nodeId, nInfo, targetRecordset[ix]);
                        }
                        if (nInfo['table'] == currentContextDef['name']) {
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
                            contextObj.setValue(
                                keyingValue, nInfo['field'], curVal, nodeId, curTarget, usePortal);
                            idValuesForFieldName[nInfo['field']] = nodeId;
                        }
                    }

                    if (isContext && !isInsidePostOnly &&
                        (nodeTag == 'INPUT' || nodeTag == 'SELECT' || nodeTag == 'TEXTAREA')) {
                        //IMLibChangeEventDispatch.setExecute(nodeId, IMLibUI.valueChange);
                        var changeFunction = function (a) {
                            var id = a;
                            return function () {
                                IMLibUI.valueChange(id);
                            };
                        };
                        eventListenerPostAdding.push({
                            'id': nodeId,
                            'event': 'change',
                            'todo': changeFunction(nodeId)
                        });
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
                    if (ex == '_im_requath_request_') {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex, 'EXCEPTION-27');
                    }
                }
            }
            if (usePortal === true) {
                keyField = '-recid';
                foreignField = currentContextDef['name'] + '::-recid';
                foreignValue = targetRecordset[ix][foreignField];
                foreignFieldValue = foreignField + '=' + foreignValue;
            } else {
                foreignField = '';
                foreignValue = '';
                foreignFieldValue = '=';
            }

            return {
                'keyField': keyField,
                'keyValue': keyValue,
                'foreignField': foreignField,
                'foreignValue': foreignValue,
                'foreignFieldValue': foreignFieldValue,
                'shouldDeleteNodes': shouldDeleteNodes,
                'isContext': isContext,
                'idValuesForFieldName': idValuesForFieldName,
                'keyingValue': keyingValue
            };
        }

        /** --------------------------------------------------------------------
         * Expanding an repeater.
         */
        function expandRepeaters(contextObj, node, targetRecords) {
            var newNode, nodeClass, dataAttr, repeatersOneRec, newlyAddedNodes, encNodeTag, repNodeTag, ix,
                repeatersOriginal, targetRecordset, targetTotalCount, i, currentContextDef, indexContext,
                insertNode, countRecord, setupResult, linkedElements;

            encNodeTag = node.tagName;
            repNodeTag = INTERMediatorLib.repeaterTagFromEncTag(encNodeTag);

            repeatersOriginal = contextObj.original;
            currentContextDef = contextObj.getContextDef();
            targetRecordset = targetRecords.recordset;
            targetTotalCount = targetRecords.totalCount;

            repeatersOneRec = cloneEveryNodes(repeatersOriginal);
            for (i = 0; i < repeatersOneRec.length; i++) {
                newNode = repeatersOneRec[i];
                dataAttr = newNode.getAttribute('data-im-control');
                if (dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsHeaderDataControlName) >= 0) {
                    if (!insertNode) {
                        node.appendChild(newNode);
                    }
                }
            }

            if (targetRecords.count === 0) {
                for (i = 0; i < repeatersOriginal.length; i++) {
                    newNode = repeatersOriginal[i].cloneNode(true);
                    nodeClass = INTERMediatorLib.getClassAttributeFromNode(newNode);
                    dataAttr = newNode.getAttribute('data-im-control');
                    if ((nodeClass && nodeClass.indexOf(INTERMediator.noRecordClassName) > -1)
                        || (dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsNoResultDataControlName) > -1)) {
                        node.appendChild(newNode);
                        setIdValue(newNode);
                    }
                }
            }

            countRecord = targetRecordset ? targetRecordset.length : 0;
            for (ix = 0; ix < countRecord; ix++) { // for each record
                repeatersOneRec = cloneEveryNodes(repeatersOriginal);
                linkedElements = INTERMediatorLib.seekLinkedAndWidgetNodes(repeatersOneRec, true);
                setupResult = setupLinkedNode(repeatersOneRec, linkedElements, contextObj, targetRecordset, ix);

                setupDeleteButton(
                    encNodeTag,
                    repeatersOneRec,
                    currentContextDef,
                    setupResult.keyField,
                    setupResult.keyValue,
                    setupResult.foreignField,
                    setupResult.foreignValue,
                    setupResult.shouldDeleteNodes
                );
                setupNavigationButton(
                    encNodeTag,
                    repeatersOneRec,
                    currentContextDef,
                    setupResult.keyField,
                    setupResult.keyValue,
                    setupResult.foreignField,
                    setupResult.foreignValue
                );
                setupCopyButton(
                    encNodeTag,
                    repNodeTag,
                    repeatersOneRec,
                    contextObj,
                    targetRecordset[ix]
                );

                if (Boolean(currentContextDef.portal) !== true ||
                    (Boolean(currentContextDef.portal) === true && targetTotalCount > 0)) {
                    newlyAddedNodes = [];
                    insertNode = null;
                    if (!contextObj.sequencing) {
                        indexContext = contextObj.checkOrder(targetRecordset[ix]);
                        insertNode = contextObj.getRepeaterEndNode(indexContext + 1);
                    }
                    for (i = 0; i < repeatersOneRec.length; i++) {
                        newNode = repeatersOneRec[i];
                        nodeClass = INTERMediatorLib.getClassAttributeFromNode(newNode);
                        dataAttr = newNode.getAttribute('data-im-control');
                        if (!(nodeClass && nodeClass.indexOf(INTERMediator.noRecordClassName) >= 0)
                            && !(dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsNoResultDataControlName) >= 0)
                            && !(dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsSeparatorDataControlName) >= 0)
                            && !(dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsFooterDataControlName) >= 0)
                            && !(dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsHeaderDataControlName) >= 0)
                        ) {
                            if (!insertNode) {
                                node.appendChild(newNode);
                            } else {
                                insertNode.parentNode.insertBefore(newNode, insertNode);
                            }
                            newlyAddedNodes.push(newNode);
                            if (!newNode.id) {  // ######## Is that right with if statement?
                                setIdValue(newNode);
                            }                   // ##########################################
                            contextObj.setValue(setupResult.keyingValue, '_im_repeater', '', newNode.id, '', setupResult.foreignValue);
                            //setupResult.idValuesForFieldName[nInfo['field']] = nodeId; // #### Is this irrelevant?
                            seekEnclosureNode(newNode, targetRecordset[ix], setupResult.idValuesForFieldName, contextObj);
                        }
                    }
                    if (ix + 1 != countRecord) {
                        for (i = 0; i < repeatersOneRec.length; i++) {
                            newNode = repeatersOneRec[i];
                            dataAttr = newNode.getAttribute('data-im-control');
                            if (dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsSeparatorDataControlName) >= 0) {
                                if (!insertNode) {
                                    node.appendChild(newNode);
                                } else {
                                    insertNode.parentNode.insertBefore(newNode, insertNode);
                                }
                            }
                        }
                    }
                    callbackForRepeaters(currentContextDef, node, newlyAddedNodes);
                }
                contextObj.rearrangePendingOrder();
            }
            repeatersOneRec = cloneEveryNodes(repeatersOriginal);
            for (i = 0; i < repeatersOneRec.length; i++) {
                newNode = repeatersOneRec[i];
                dataAttr = newNode.getAttribute('data-im-control');
                if (dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsFooterDataControlName) >= 0) {
                    if (!insertNode) {
                        node.appendChild(newNode);
                    }
                }
            }
        }


        /* --------------------------------------------------------------------

         */
        function setIdValue(node) {
            var i, elementInfo, comp, overwrite = true;

            if (node.getAttribute('id') === null) {
                node.setAttribute('id', nextIdValue());
            } else {
                if (INTERMediator.elementIds.indexOf(node.getAttribute('id')) >= 0) {
                    elementInfo = INTERMediatorLib.getLinkedElementInfo(node);
                    for (i = 0; i < elementInfo.length; i++) {
                        comp = elementInfo[i].split(INTERMediator.separator);
                        if (comp[2] == '#id') {
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

        /* --------------------------------------------------------------------

         */
        function nextIdValue() {
            INTERMediator.linkedElmCounter++;
            return currentIdValue();
        }

        /* --------------------------------------------------------------------

         */
        function currentIdValue() {
            return 'IM' + INTERMediator.currentEncNumber + '-' + INTERMediator.linkedElmCounter;
        }

        /* --------------------------------------------------------------------

         */
        function retrieveDataForEnclosure(currentContextDef, fieldList, relationValue) {
            var ix, keyField, targetRecords, counter, oneRecord, isMatch, index, fieldName, condition, portal, recId,
                recordNumber, useLimit, optionalCondition = [], pagingValue, recordsValue, i, recordset = [];

            if (Boolean(currentContextDef.cache) === true) {
                try {
                    if (!INTERMediatorOnPage.dbCache[currentContextDef['name']]) {
                        INTERMediatorOnPage.dbCache[currentContextDef['name']] =
                            INTERMediator_DBAdapter.db_query({
                                name: currentContextDef['name'],
                                records: null,
                                paging: null,
                                fields: fieldList,
                                parentkeyvalue: null,
                                conditions: null,
                                useoffset: false
                            });
                    }
                    if (relationValue === null) {
                        targetRecords = INTERMediatorOnPage.dbCache[currentContextDef['name']];
                    } else {
                        targetRecords = {recordset: [], count: 0};
                        counter = 0;
                        for (ix in INTERMediatorOnPage.dbCache[currentContextDef['name']].recordset) {
                            oneRecord = INTERMediatorOnPage.dbCache[currentContextDef['name']].recordset[ix];
                            isMatch = true;
                            index = 0;
                            for (keyField in relationValue) {
                                fieldName = currentContextDef['relation'][index]['foreign-key'];
                                if (oneRecord[fieldName] != relationValue[keyField]) {
                                    isMatch = false;
                                    break;
                                }
                                index++;
                            }
                            if (isMatch) {
                                pagingValue = currentContextDef['paging'] ? currentContextDef['paging'] : false;
                                recordsValue = currentContextDef['records'] ? currentContextDef['records'] : 10000000000;

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
                    if (ex == '_im_requath_request_') {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex, 'EXCEPTION-24');
                    }
                }
            } else {   // cache is not active.
                try {
                    if (currentContextDef['portal'] == true) {
                        for (condition in INTERMediator.additionalCondition) {
                            if (INTERMediator.additionalCondition.hasOwnProperty(condition)) {
                                optionalCondition.push(INTERMediator.additionalCondition[condition]);
                                break;
                            }
                        }
                    }
                    useLimit = false;
                    if (currentContextDef['records'] && currentContextDef['paging']) {
                        useLimit = true;
                    }

                    if (currentContextDef['navi-control'] &&
                        currentContextDef['navi-control'] === 'detail') {
                        recordNumber = 1;
                    } else {
                        if (currentContextDef.maxrecords) {
                            if (parseInt(INTERMediator.pagedSize, 10) === 0) {
                                if (currentContextDef.records) {
                                    recordNumber = parseInt(currentContextDef.records, 10);
                                } else {
                                    recordNumber = parseInt(currentContextDef.maxrecords, 10);
                                }
                            } else {
                                if (parseInt(currentContextDef.maxrecords, 10) < parseInt(INTERMediator.pagedSize, 10)) {
                                    if (parseInt(currentContextDef.maxrecords, 10) < parseInt(currentContextDef.records, 10)) {
                                        recordNumber = parseInt(currentContextDef.records, 10);
                                    } else {
                                        recordNumber = parseInt(currentContextDef.maxrecords, 10);
                                    }
                                } else {
                                    if (currentContextDef.relation) {
                                        recordNumber = parseInt(currentContextDef.records, 10);
                                    } else {
                                        recordNumber = parseInt(INTERMediator.pagedSize, 10);
                                    }
                                }
                            }
                        } else {
                            if (parseInt(INTERMediator.pagedSize, 10) === 0 ||
                                (parseInt(currentContextDef.records, 10) < parseInt(INTERMediator.pagedSize, 10))) {
                                recordNumber = parseInt(currentContextDef.records, 10);
                            } else {
                                if (currentContextDef.relation) {
                                    recordNumber = parseInt(currentContextDef.records, 10);
                                } else {
                                    recordNumber = parseInt(INTERMediator.pagedSize, 10);
                                }
                            }
                        }
                        if (!currentContextDef.relation &&
                            currentContextDef.paging && Boolean(currentContextDef.paging) === true) {
                            INTERMediator.setLocalProperty('_im_pagedSize', recordNumber);
                        }
                    }

                    targetRecords = {};
                    if (Boolean(currentContextDef.portal) === true) {
                        portal = currentContextDef['currentrecord'][0][currentContextDef['name']];
                        for (recId in portal) {
                            if (portal.hasOwnProperty(recId) && isFinite(recId)) {
                                recordset.push(portal[recId]);
                            }
                        }
                        targetRecords.recordset = recordset;
                    } else {
                        targetRecords = INTERMediator_DBAdapter.db_query({
                            'name': currentContextDef['name'],
                            'records': isNaN(recordNumber) ? 100000000 : recordNumber,
                            'paging': currentContextDef['paging'],
                            'fields': fieldList,
                            'parentkeyvalue': relationValue,
                            'conditions': optionalCondition,
                            'useoffset': true,
                            'uselimit': useLimit
                        });
                    }
                } catch (ex) {
                    if (ex == '_im_requath_request_') {
                        throw ex;
                    } else {
                        INTERMediator.setErrorMessage(ex, 'EXCEPTION-12');
                    }
                }
            }
            return targetRecords;
        }

        /* --------------------------------------------------------------------

         */
        function callbackForRepeaters(currentContextDef, node, newlyAddedNodes) {
            try {
                if (INTERMediatorOnPage.additionalExpandingRecordFinish[currentContextDef['name']]) {
                    INTERMediatorOnPage.additionalExpandingRecordFinish[currentContextDef['name']](node);
                    INTERMediator.setDebugMessage(
                        "Call the post enclosure method 'INTERMediatorOnPage.additionalExpandingRecordFinish["
                        + currentContextDef['name'] + "] with the context.", 2);
                }
            } catch (ex) {
                if (ex == '_im_requath_request_') {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex,
                        'EXCEPTION-33: hint: post-repeater of ' + currentContextDef.name);
                }
            }
            try {
                if (INTERMediatorOnPage.expandingRecordFinish != null) {
                    INTERMediatorOnPage.expandingRecordFinish(currentContextDef['name'], newlyAddedNodes);
                    INTERMediator.setDebugMessage(
                        "Call INTERMediatorOnPage.expandingRecordFinish with the context: "
                        + currentContextDef['name'], 2);
                }

                if (currentContextDef['post-repeater']) {
                    INTERMediatorOnPage[currentContextDef['post-repeater']](newlyAddedNodes);

                    INTERMediator.setDebugMessage("Call the post repeater method 'INTERMediatorOnPage."
                        + currentContextDef['post-repeater'] + "' with the context: "
                        + currentContextDef['name'], 2);
                }
            } catch (ex) {
                if (ex == '_im_requath_request_') {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex, 'EXCEPTION-23');
                }
            }

        }

        /* --------------------------------------------------------------------

         */
        function callbackForEnclosure(currentContextDef, node) {
            try {
                if (INTERMediatorOnPage.additionalExpandingEnclosureFinish[currentContextDef['name']]) {
                    INTERMediatorOnPage.additionalExpandingEnclosureFinish[currentContextDef['name']](node);
                    INTERMediator.setDebugMessage(
                        "Call the post enclosure method 'INTERMediatorOnPage.additionalExpandingEnclosureFinish["
                        + currentContextDef['name'] + "] with the context.", 2);
                }
            } catch (ex) {
                if (ex == '_im_requath_request_') {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex,
                        'EXCEPTION-32: hint: post-enclosure of ' + currentContextDef.name);
                }
            }
            try {
                if (INTERMediatorOnPage.expandingEnclosureFinish != null) {
                    INTERMediatorOnPage.expandingEnclosureFinish(currentContextDef['name'], node);
                    INTERMediator.setDebugMessage(
                        'Call INTERMediatorOnPage.expandingEnclosureFinish with the context: '
                        + currentContextDef['name'], 2);
                }
            } catch (ex) {
                if (ex == '_im_requath_request_') {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex, 'EXCEPTION-21');
                }
            }
            try {
                if (currentContextDef['post-enclosure']) {
                    INTERMediatorOnPage[currentContextDef['post-enclosure']](node);
                    INTERMediator.setDebugMessage(
                        "Call the post enclosure method 'INTERMediatorOnPage." + currentContextDef['post-enclosure']
                        + "' with the context: " + currentContextDef['name'], 2);
                }
            } catch (ex) {
                if (ex == '_im_requath_request_') {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex,
                        'EXCEPTION-22: hint: post-enclosure of ' + currentContextDef.name);
                }
            }
        }

        /* --------------------------------------------------------------------

         */
        function callbackForAfterQueryStored(currentContextDef, context) {
            try {
                if (currentContextDef['post-query-stored']) {
                    INTERMediatorOnPage[currentContextDef['post-query-stored']](context);
                    INTERMediator.setDebugMessage(
                        "Call the post query stored method 'INTERMediatorOnPage." + currentContextDef['post-enclosure']
                        + "' with the context: " + currentContextDef['name'], 2);
                }
            } catch (ex) {
                if (ex == '_im_requath_request_') {
                    throw ex;
                } else {
                    INTERMediator.setErrorMessage(ex,
                        'EXCEPTION-41: hint: post-query-stored of ' + currentContextDef.name);
                }
            }
        }

        /* --------------------------------------------------------------------

         */
        function collectRepeatersOriginal(node, repNodeTag) {
            var i, repeatersOriginal = [], children, imControl;

            children = node.childNodes; // Check all child node of the enclosure.
            for (i = 0; i < children.length; i++) {
                if (children[i].nodeType === 1) {
                    if (children[i].tagName == repNodeTag) {
                        // If the element is a repeater.
                        repeatersOriginal.push(children[i]); // Record it to the array.
                    } else if (repNodeTag == null && (children[i].getAttribute('data-im-control'))) {
                        imControl = children[i].getAttribute('data-im-control');
                        if (imControl.indexOf(INTERMediatorLib.roleAsRepeaterDataControlName) > -1
                            || imControl.indexOf(INTERMediatorLib.roleAsSeparatorDataControlName) > -1
                            || imControl.indexOf(INTERMediatorLib.roleAsFooterDataControlName) > -1
                            || imControl.indexOf(INTERMediatorLib.roleAsHeaderDataControlName) > -1
                            || imControl.indexOf(INTERMediatorLib.roleAsNoResultDataControlName) > -1
                        ) {
                            repeatersOriginal.push(children[i]);
                        }
                    } else if (repNodeTag == null && INTERMediatorLib.getClassAttributeFromNode(children[i]) &&
                        INTERMediatorLib.getClassAttributeFromNode(children[i]).match(/_im_repeater/)) {
                        imControl = INTERMediatorLib.getClassAttributeFromNode(children[i]);
                        if (imControl.indexOf(INTERMediatorLib.roleAsRepeaterClassName) > -1) {
                            repeatersOriginal.push(children[i]);
                        }
                    }
                }
            }
            return repeatersOriginal;
        }

        /* --------------------------------------------------------------------

         */
        function collectRepeaters(repeatersOriginal) {
            var i, repeaters = [], inDocNode, parentOfRep, cloneNode;
            for (i = 0; i < repeatersOriginal.length; i++) {
                inDocNode = repeatersOriginal[i];
                parentOfRep = repeatersOriginal[i].parentNode;
                cloneNode = repeatersOriginal[i].cloneNode(true);
                repeaters.push(cloneNode);
                cloneNode.setAttribute('id', nextIdValue());
                if (parentOfRep) {
                    parentOfRep.removeChild(inDocNode);
                }
            }
            return repeaters;
        }

        /* --------------------------------------------------------------------

         */
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

        /* --------------------------------------------------------------------

         */
        function tableVoting(linkDefs) {
            var j, nodeInfoArray, nodeInfoField, nodeInfoTable, maxVoted, maxTableName, tableName,
                nodeInfoTableIndex, context,
                tableVote = [],    // Containing editable elements or not.
                fieldList = []; // Create field list for database fetch.

            for (j = 0; j < linkDefs.length; j++) {
                nodeInfoArray = INTERMediatorLib.getNodeInfoArray(linkDefs[j]);
                nodeInfoField = nodeInfoArray['field'];
                nodeInfoTable = nodeInfoArray['table'];
                nodeInfoTableIndex = nodeInfoArray['tableindex'];   // Table name added '_im_index_' as the prefix.
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
            return {targettable: context, fieldlist: fieldList['_im_index_' + maxTableName]};
        }

        /* --------------------------------------------------------------------

         */
        function cloneEveryNodes(originalNodes) {
            var i, clonedNodes = [];
            for (i = 0; i < originalNodes.length; i++) {
                clonedNodes.push(originalNodes[i].cloneNode(true));
            }
            return clonedNodes;
        }

        /* --------------------------------------------------------------------

         */
        function setupCopyButton(encNodeTag, repNodeTag, repeaters, currentContext, currentRecord) {
            // Handling Copy buttons
            var buttonNode, thisId, copyJSFunction, tdNodes, tdNode, buttonName, currentContextDef;

            currentContextDef = currentContext.getContextDef();
            if (!currentContextDef['repeat-control']
                || !currentContextDef['repeat-control'].match(/copy/i)) {
                return;
            }
            if (currentContextDef['paging'] == true) {
                IMLibPageNavigation.deleteInsertOnNavi.push({
                    kind: 'COPY',
                    contextDef: currentContextDef,
                    keyValue: currentRecord[currentContextDef['key']]
                });
            } else {
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
                copyJSFunction = function (a, b) {
                    var currentContext = a, currentRecord = b;
                    return function () {
                        IMLibUI.copyButton(currentContext, currentRecord);
                    };
                };
                eventListenerPostAdding.push({
                    'id': thisId,
                    'event': 'click',
                    'todo': copyJSFunction(currentContext, currentRecord[currentContextDef['key']])
                });
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
            }
        }

        /* --------------------------------------------------------------------

         */
        function setupDeleteButton(encNodeTag, repeaters, currentContextDef, keyField, keyValue, foreignField, foreignValue, shouldDeleteNodes) {
            // Handling Delete buttons
            var buttonNode, thisId, deleteJSFunction, tdNodes, tdNode, buttonName;
            if (!currentContextDef['repeat-control']
                || !currentContextDef['repeat-control'].match(/delete/i)) {
                return;
            }
            if (currentContextDef['relation']
                || currentContextDef['records'] === undefined
                || (currentContextDef['records'] > 1 && Number(INTERMediator.pagedSize) != 1)) {

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
                deleteJSFunction = function (a, b, c, d, e) {
                    var contextName = a, keyField = b, keyValue = c, removeNodes = d, confirming = e;

                    return function () {
                        IMLibUI.deleteButton(
                            contextName,
                            keyField,
                            keyValue,
                            foreignField,
                            foreignValue,
                            removeNodes,
                            confirming
                        );
                    };
                };
                eventListenerPostAdding.push({
                    'id': thisId,
                    'event': 'click',
                    'todo': deleteJSFunction(
                        currentContextDef['name'],
                        keyField,
                        keyValue,
                        shouldDeleteNodes,
                        currentContextDef['repeat-control'].match(/confirm-delete/i))
                });
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
        }

        /* --------------------------------------------------------------------

         */
        function setupInsertButton(currentContextDef, keyValue, node, relationValue) {
            var buttonNode, shouldRemove, enclosedNode, footNode, trNode, tdNode, liNode, divNode, insertJSFunction, i,
                firstLevelNodes, targetNodeTag, existingButtons, keyField, thisId, encNodeTag,
                buttonName, setTop;

            encNodeTag = node.tagName;
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
                    shouldRemove = [];
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
                        if (existingButtons.length == 0) {
                            trNode = document.createElement('TR');
                            INTERMediatorLib.setClassAttributeToNode(trNode, 'IM_Insert_TR');
                            tdNode = document.createElement('TD');
                            INTERMediatorLib.setClassAttributeToNode(tdNode, 'IM_Insert_TD');
                            setIdValue(trNode);
                            if (setTop && footNode.childNodes) {
                                footNode.insertBefore(trNode, footNode.childNodes[0]);
                            } else {
                                footNode.appendChild(trNode);
                            }
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
                        if (existingButtons.length == 0) {
                            divNode.appendChild(buttonNode);
                            if (currentContextDef['repeat-control'].match(/top/i)) {
                                node.insertBefore(divNode, node.firstChild);
                            } else {
                                node.appendChild(divNode);
                            }
                        }
                        break;
                    }
                    insertJSFunction = function (a, b, c, d, e) {
                        var contextName = a, relationValue = b, nodeId = c, removeNodes = d, confirming = e;
                        return function () {
                            IMLibUI.insertButton(contextName, keyValue, relationValue, nodeId, removeNodes, confirming);
                        };
                    };

                    INTERMediatorLib.addEvent(
                        buttonNode,
                        'click',
                        insertJSFunction(
                            currentContextDef['name'],
                            relationValue,
                            node.getAttribute('id'),
                            shouldRemove,
                            currentContextDef['repeat-control'].match(/confirm-insert/i))
                    );

                } else {
                    if (INTERMediatorOnPage.dbClassName === 'DB_FileMaker_FX') {
                        keyField = currentContextDef['key'] ? currentContextDef['key'] : '-recid';
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
        }

        /* --------------------------------------------------------------------

         */
        function setupNavigationButton(encNodeTag, repeaters, currentContextDef, keyField, keyValue, foreignField, foreignValue) {
            // Handling Detail buttons
            var buttonNode, thisId, tdNodes, tdNode, firstInNode, contextDef,
                isHide, masterContext, detailContext, showingNode, isHidePageNavi, buttonName, i,
                isTouchRepeater, moveToDetailFunc;

            if (!currentContextDef['navi-control']
                || !currentContextDef['navi-control'].match(/master/i)
                || encNodeTag == 'SELECT') {
                return;
            }

            isTouchRepeater = INTERMediator.isMobile || INTERMediator.isTablet;
            isHide = currentContextDef['navi-control'].match(/hide/i);
            isHidePageNavi = isHide && (currentContextDef['paging'] == true);

            if (INTERMediator.detailNodeOriginalDisplay) {
                detailContext = IMLibContextPool.getDetailContext();
                if (detailContext) {
                    showingNode = detailContext.enclosureNode;
                    if (showingNode.tagName == 'TBODY') {
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
            masterContext = IMLibContextPool.getMasterContext();
            masterContext.setValue(keyField + '=' + keyValue, '_im_buttom_master_id', thisId, thisId);

            if (isTouchRepeater) {
                moveToDetailFunc = moveToDetail(encNodeTag, keyField, keyValue, foreignField, foreignValue, isHide, isHidePageNavi);
                for (i = 0; i < repeaters.length; i++) {
                    var originalColor = repeaters[i].style.backgroundColor;
                    eventListenerPostAdding.push({
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
                    eventListenerPostAdding.push({
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
                    eventListenerPostAdding.push({
                        'id': repeaters[i].id,
                        'event': 'touchmove',
                        'todo': (function () {
                            return function () {
                                IMLibEventResponder.touchEventCancel = true;
                            };
                        })()
                    });
                    eventListenerPostAdding.push({
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
                eventListenerPostAdding.push({
                    'id': thisId,
                    'event': 'click',
                    'todo': moveToDetail(encNodeTag, keyField, keyValue, foreignField, foreignValue, isHide, isHidePageNavi)
                });

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

            function moveToDetail(encNodeTag, keyField, keyValue, foreignField, foreignValue, isHide, isHidePageNavi) {
                var f = keyField, v = keyValue, ff = foreignField, fv = foreignValue;
                var fvalue = {}, etag = encNodeTag, isMasterHide = isHide, isPageHide = isHidePageNavi;
                fvalue[ff] = fv;

                return function () {
                    var masterContext, detailContext, contextName, masterEnclosure, detailEnclosure, conditions,
                        node;

                    masterContext = IMLibContextPool.getMasterContext();
                    detailContext = IMLibContextPool.getDetailContext();
                    if (detailContext) {
                        if (INTERMediatorOnPage.naviBeforeMoveToDetail) {
                            INTERMediatorOnPage.naviBeforeMoveToDetail(masterContext, detailContext);
                        }
                        contextDef = detailContext.getContextDef();
                        contextName = contextDef.name;
                        conditions = INTERMediator.additionalCondition;
                        conditions[contextName] = {field: f, operator: '=', value: v};
                        INTERMediator.additionalCondition = conditions;
                        INTERMediator.constructMain(detailContext);
                        if (isMasterHide) {
                            INTERMediatorOnPage.masterScrollPosition = {x: window.scrollX, y: window.scrollY};
                            window.scrollTo(0, 0);
                            masterEnclosure = masterContext.enclosureNode;
                            if (etag == 'TBODY') {
                                masterEnclosure = masterEnclosure.parentNode;
                            }
                            INTERMediator.masterNodeOriginalDisplay = masterEnclosure.style.display;
                            masterEnclosure.style.display = 'none';

                            detailEnclosure = detailContext.enclosureNode;
                            if (detailEnclosure.tagName == 'TBODY') {
                                detailEnclosure = detailEnclosure.parentNode;
                            }
                            detailEnclosure.style.display = INTERMediator.detailNodeOriginalDisplay;
                        }
                        if (isPageHide) {
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
                };
            }
        }

        /* --------------------------------------------------------------------

         */
        function setupBackNaviButton(currentContext, node) {
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
            isHidePageNavi = masterContext.getContextDef()['paging'] == true;
            isUpdateMaster = currentContextDef['navi-control'].match(/update/i);
            isTouchRepeater = INTERMediator.isMobile || INTERMediator.isTablet;
            isTop = !(currentContextDef['navi-control'].match(/bottom/i));

            showingNode = currentContext.enclosureNode;
            if (showingNode.tagName == 'TBODY') {
                showingNode = showingNode.parentNode;
            }
            if (INTERMediator.detailNodeOriginalDisplay) {
                INTERMediator.detailNodeOriginalDisplay = showingNode.style.display;
            }
            showingNode.style.display = 'none';

            if (isTouchRepeater) {
                nodes = document.getElementsByClassName('IM_Button_BackNavi');
                if (!nodes || nodes.length == 0) {
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
                    INTERMediatorLib.addEvent(
                        aNode,
                        'click',
                        moveToMaster(masterContext, currentContext, isHidePageNavi, isUpdateMaster)
                    );
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
                    appendingNodesAtLast.push({
                        targetNode: targetNode,
                        parentNode: enclosedNode,
                        siblingNode: (targetNodeTag == 'THEAD') ? enclosedNode.firstChild : null
                    });
                }
                existingButtons = INTERMediatorLib.getElementsByClassName(targetNode, 'IM_Button_BackNavi');
                if (existingButtons.length == 0) {
                    trNode = document.createElement('TR');
                    INTERMediatorLib.setClassAttributeToNode(trNode, 'IM_NaviBack_TR');
                    tdNode = document.createElement('TD');
                    INTERMediatorLib.setClassAttributeToNode(tdNode, 'IM_NaviBack_TD');
                    setIdValue(trNode);
                    targetNode.appendChild(trNode);
                    trNode.appendChild(tdNode);
                    tdNode.appendChild(buttonNode);
                }
            }

            function genericTargetNode(node, isTop, naviEncTag, buttonNode) {
                var newNode, existingButtons;
                newNode = document.createElement(naviEncTag);
                existingButtons = INTERMediatorLib.getElementsByClassName(divNode, 'IM_Button_BackNavi');
                if (existingButtons.length == 0) {
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
                    if (showingNode.tagName == 'TBODY') {
                        showingNode = showingNode.parentNode;
                    }
                    showingNode.style.display = 'none';

                    showingNode = masterContextCL.enclosureNode;
                    if (showingNode.tagName == 'TBODY') {
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

        function setIdForIMButtons(node) {
            var thisId;
            thisId = 'IM_Button_' + INTERMediator.buttonIdNum;
            node.setAttribute('id', thisId);
            INTERMediator.buttonIdNum++;
        }

        /* --------------------------------------------------------------------

         */
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

        /* --------------------------------------------------------------------

         */
        function appendCredit() {
            var bodyNode, creditNode, cNode, spNode, aNode, versionStrng;

            if (document.getElementById('IM_CREDIT') === null) {
                if (INTERMediatorOnPage.creditIncluding) {
                    bodyNode = document.getElementById(INTERMediatorOnPage.creditIncluding);
                }
                if (!bodyNode) {
                    bodyNode = document.getElementsByTagName('BODY')[0];
                }

                creditNode = document.createElement('div');
                bodyNode.appendChild(creditNode);
                creditNode.setAttribute('id', 'IM_CREDIT');
                creditNode.setAttribute('class', 'IM_CREDIT');

                cNode = document.createElement('div');
                creditNode.appendChild(cNode);
                cNode.style.backgroundColor = '#F6F7FF';
                cNode.style.height = '2px';
                cNode.style.margin = '0';
                cNode.style.padding = '0';

                cNode = document.createElement('div');
                creditNode.appendChild(cNode);
                cNode.style.backgroundColor = '#EBF1FF';
                cNode.style.height = '2px';
                cNode.style.margin = '0';
                cNode.style.padding = '0';

                cNode = document.createElement('div');
                creditNode.appendChild(cNode);
                cNode.style.backgroundColor = '#E1EAFF';
                cNode.style.height = '2px';
                cNode.style.margin = '0';
                cNode.style.padding = '0';

                cNode = document.createElement('div');
                creditNode.appendChild(cNode);
                cNode.setAttribute('align', 'right');
                cNode.style.backgroundColor = '#D7E4FF';
                cNode.style.padding = '2px';
                cNode.style.margin = '0';
                cNode.style.padding = '0';
                spNode = document.createElement('span');
                cNode.appendChild(spNode);
                cNode.style.color = '#666666';
                cNode.style.fontSize = '7pt';
                aNode = document.createElement('a');
                aNode.appendChild(document.createTextNode('INTER-Mediator'));
                aNode.setAttribute('href', 'http://inter-mediator.com/');
                aNode.setAttribute('target', '_href');
                spNode.appendChild(document.createTextNode('Generated by '));
                spNode.appendChild(aNode);
                versionStrng = ' Ver.' + INTERMediatorOnPage.metadata.version
                    + '(' + INTERMediatorOnPage.metadata.releasedate + ')';
                spNode.appendChild(document.createTextNode(versionStrng));
            }
        }
    },

    getLocalProperty: function (localKey, defaultValue) {
        var value;
        value = IMLibLocalContext.getValue(localKey);
        return value === null ? defaultValue : value;
    },

    setLocalProperty: function (localKey, value) {
        IMLibLocalContext.setValue(localKey, value, true);
    },

    addCondition: function (contextName, condition, notMatching) {
        var value, i, hasIdentical;
        if (notMatching != undefined) {
            condition['matching'] = !notMatching;
        } else {
            condition['matching'] = INTERMediator_DBAdapter.eliminateDuplicatedConditions;
        }
        if (INTERMediator.additionalCondition) {
            value = INTERMediator.additionalCondition;
            if (condition) {
                if (!value[contextName]) {
                    value[contextName] = [];
                }
                if (!condition.matching) {
                    value[contextName].push(condition);
                } else {
                    hasIdentical = false;
                    for (i = 0; i < value[contextName].length; i++) {
                        if (value[contextName][i].field == condition.field
                            && value[contextName][i].operator == condition.operator) {
                            hasIdentical = true;
                            value[contextName][i].value = condition.value;
                            break;
                        }
                    }
                    if (!hasIdentical) {
                        value[contextName].push(condition);
                    }
                }
            }
            INTERMediator.additionalCondition = value;
        }
        IMLibLocalContext.archive();
    },

    clearCondition: function (contextName) {
        var value = INTERMediator.additionalCondition;
        if (value[contextName]) {
            delete value[contextName];
            INTERMediator.additionalCondition = value;
            IMLibLocalContext.archive();
        } else {
            INTERMediator.additionalCondition = {};
            IMLibLocalContext.archive();
        }
    },

    addSortKey: function (contextName, sortKey) {
        var value = INTERMediator.additionalSortKey;
        if (value[contextName]) {
            value[contextName].push(sortKey);
        } else {
            value[contextName] = [sortKey];
        }
        INTERMediator.additionalSortKey = value;
        IMLibLocalContext.archive();
    },

    clearSortKey: function (contextName) {
        var value = INTERMediator.additionalSortKey;
        if (value[contextName]) {
            delete value[contextName];
            INTERMediator.additionalSortKey = value;
            IMLibLocalContext.archive();
        }
    }
};

/**
 * Compatibility for IE8
 */
if (!Object.keys) {
    Object.keys = function (obj) {
        var results = [], prop;
        if (obj !== Object(obj)) {
            throw new TypeError('Object.keys called on a non-object');
        }
        for (prop in obj) {
            if (Object.prototype.hasOwnProperty.call(obj, prop)) {
                results.push(prop);
            }
        }
        return results;
    };
}

if (!Array.indexOf) {
    var isWebkit = 'WebkitAppearance' in document.documentElement.style;
    if (!isWebkit) {
        Array.prototype.indexOf = function (target) {
            var i;
            for (i = 0; i < this.length; i++) {
                if (this[i] === target) {
                    return i;
                }
            }
            return -1;
        };
    }
}

if (typeof String.prototype.trim !== 'function') {
    String.prototype.trim = function () {
        return this.replace(/^\s+|\s+$/g, '');
    };
}
