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
/* global IMLibContextPool, INTERMediatorOnPage, IMLibMouseEventDispatch, IMLibLocalContext, INTERMediatorLog,
 IMLibChangeEventDispatch, INTERMediatorLib, INTERMediator_DBAdapter, IMLibQueue, IMLibCalc, IMLibPageNavigation,
 IMLibEventResponder, IMLibElement, Parser, IMLib, IMLibUI, INTERMediatorLog, Pusher, IMParts_Catalog */
/* jshint -W083 */ // Function within a loop

/**
 * @fileoverview INTERMediator class is defined here.
 */

// Global type definition for JSDoc.
/**
 * @typedef {Object} IMType_VariablePropertiesClass
 * @property {string} __case_by_case__ The property name varies as case by case.
 * This means this object will have multiple properties, and their name don't fixed.
 * Each property has a value and should be described as the generic notation.
 * Anyway, this class is JavaScript's typical object.
 */

/**
 * Web page generator main class. This class has just static methods and properties.
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
var INTERMediator = {
    /**
     * The separator for target specification.
     * This must be referred as 'INTERMediator.separator'. Don't use 'this.separator'
     * @public
     * @type {string}
     */
    separator: '@',
    /**
     * The separator for multiple target specifications. The white space characters are
     * used as it in current version.
     * @deprecated
     * @type {string}
     */
    defDivider: '|',
    /**
     * If the target (i.e. 3rd component) of the target specification is omitted in generic tags,
     * the value will set into innerHTML property. Otherwise it's set as a text node.
     * @public
     * @type {boolean}
     */
    defaultTargetInnerHTML: false,
    /**
     * Navigation is controlled by this property.
     * @public
     * @type {object}
     */
    navigationLabel: null,
    /**
     * Storing the id value of linked elements.
     * @private
     * @type {Array}
     */
    elementIds: [],
    /**
     * If this property is true, any radio buttuns aren't set the 'check.'
     * The default value of false.
     * @public
     * @type {boolean}
     */
    dontSelectRadioCheck: false,
    /**
     * If this property is true, the optimistic lock in editing field won't work, and update
     * database without checking of modification by other users.
     * The default value of false.
     * @public
     * @type {boolean}
     */
    ignoreOptimisticLocking: false,
    /**
     * The debug messages are suppressed if it's true. This can temporally stop messages.
     * The default value of false.
     * @public
     * @type {object}
     */
    additionalFieldValueOnNewRecord: {},
    /**
     * @public
     * @type {object}
     */
    additionalFieldValueOnUpdate: {},
    /**
     * @public
     * @type {object}
     */
    additionalFieldValueOnDelete: {},
    /**
     * @public
     * @type {integer}
     */
    waitSecondsAfterPostMessage: 4,
    /**
     * @public
     * @type {integer}
     */
    pagedAllCount: 0,
    /**
     * This property is for DB_FileMaker_FX.
     * @public
     * @type {integer}
     */
    totalRecordCount: null,
    /**
     * @private
     * @type {integer}
     */
    currentEncNumber: 0,
    /**
     * @type {boolean}
     */
    isIE: false,
    /**
     * @type {boolean}
     */
    isTrident: false,
    /**
     * @type {boolean}
     */
    isEdge: false,
    /**
     * @type {integer}
     */
    ieVersion: -1,
    /**
     * @type {boolean}
     */
    titleAsLinkInfo: true,
    /**
     * @type {boolean}
     */
    classAsLinkInfo: true,
    /**
     * @type {boolean}
     */
    isDBDataPreferable: false,
    /**
     * @type {string}
     */
    noRecordClassName: '_im_for_noresult_',
    /**
     * Storing the innerHTML property of the BODY tagged node to retrieve the page to initial condition.
     * @private
     * @type {string}
     */
    rootEnclosure: null,
    /**
     * @type {boolean}
     */
    useSessionStorage: true,
    // Use sessionStorage for the Local Context instead of Cookie.

    /**
     * @type {boolean}
     */
    partialConstructing: true,
    /**
     * @type {integer}
     */
    linkedElmCounter: 0,
    /**
     * @type {object}
     */
    pusherObject: null,
    /**
     * @type {integer}
     */
    buttonIdNum: 0,
    /**
     * @type {string}
     */
    masterNodeOriginalDisplay: 'block',
    /**
     * @type {string}
     */
    detailNodeOriginalDisplay: 'none',
    /**
     * @type {boolean}
     */
    pusherAvailable: false,
    /**
     * @type {boolean}
     */
    dateTimeFunction: false,
    /**
     * @type {integer}
     */
    postOnlyNumber: 1,
    /**
     * @type {boolean}
     */
    isTablet: false,
    /**
     * @type {boolean}
     */
    isMobile: false,
    /**
     * @type {integer}
     */
    crossTableStage: 0, // 0: not cross table, 1: column label, 2: row label, 3 interchange cells

    eventListenerPostAdding: null,
    appendingNodesAtLast: null,

// Detect Internet Explorer and its version.
    propertyIETridentSetup: function () {
      'use strict';
      var ua, position, c, i;
      ua = navigator.userAgent;
      position = ua.toLocaleUpperCase().indexOf('MSIE');
      if (position >= 0) {
        INTERMediator.isIE = true;
        for (i = position + 4; i < ua.length; i++) {
          c = ua.charAt(i);
          if (!(c === ' ' || c === '.' || (c >= '0' && c <= '9'))) {
            INTERMediator.ieVersion = INTERMediatorLib.toNumber(ua.substring(position + 4, i));
            break;
          }
        }
      }
      position = ua.indexOf('; Trident/');
      if (position >= 0) {
        INTERMediator.isTrident = true;
        for (i = position + 10; i < ua.length; i++) {
          c = ua.charAt(i);
          if (!(c === ' ' || c === '.' || (c >= '0' && c <= '9'))) {
            INTERMediator.ieVersion = INTERMediatorLib.toNumber(ua.substring(position + 10, i)) + 4;
            if (INTERMediator.ieVersion === 11) {
              INTERMediator.isIE = true;
            }
            break;
          }
        }
      }
      position = ua.indexOf(' Edge/');
      if (position >= 0) {
        INTERMediator.isEdge = true;
        for (i = position + 6; i < ua.length; i++) {
          c = ua.charAt(i);
          if (!(c === ' ' || c === '.' || (c >= '0' && c <= '9')) || i === ua.length - 1) {
            INTERMediator.ieVersion = INTERMediatorLib.toNumber(ua.substring(position + 6, i));
            break;
          }
        }
      }
    },

// Referred from https://w3g.jp/blog/js_browser_sniffing2015
    propertyW3CUserAgentSetup: function () {
      'use strict';
      var u = window.navigator.userAgent.toLowerCase();
      INTERMediator.isTablet =
        (u.indexOf('windows') > -1 && u.indexOf('touch') > -1 && u.indexOf('tablet pc') === -1) ||
        u.indexOf('ipad') > -1 ||
        (u.indexOf('android') > -1 && u.indexOf('mobile') === -1) ||
        (u.indexOf('firefox') > -1 && u.indexOf('tablet') > -1) ||
        u.indexOf('kindle') > -1 ||
        u.indexOf('silk') > -1 ||
        u.indexOf('playbook') > -1;
      INTERMediator.isMobile =
        (u.indexOf('windows') > -1 && u.indexOf('phone') > -1) ||
        u.indexOf('iphone') > -1 ||
        u.indexOf('ipod') > -1 ||
        (u.indexOf('android') > -1 && u.indexOf('mobile') > -1) ||
        (u.indexOf('firefox') > -1 && u.indexOf('mobile') > -1) ||
        u.indexOf('blackberry') > -1;
    }
    ,

    initialize: function () {
      'use strict';
      INTERMediatorOnPage.removeCookie('_im_localcontext');
      //INTERMediatorOnPage.removeCookie('_im_username');
      //INTERMediatorOnPage.removeCookie('_im_credential');
      //INTERMediatorOnPage.removeCookie('_im_mediatoken');

      INTERMediator.additionalCondition = {};
      INTERMediator.additionalSortKey = {};
      INTERMediator.startFrom = 0;
      IMLibLocalContext.archive();
    }
    ,

//=================================
//Construct Page
//=================================
    /**
     * Construct the Web Page with DB Data. Usually this method will be called automatically.
     * @param indexOfKeyFieldObject If this parameter is omitted or set to true,
     *    INTER-Mediator is going to generate entire page. If ths parameter is set as the Context object,
     *    INTER-Mediator is going to generate a part of page which relies on just its context.
     */
    construct: function (indexOfKeyFieldObject) {
      'use strict';
      var timerTask;
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
    }
    ,

    /**
     * This method is page generation main method. This will be called with one of the following
     * 3 ways:
     * <ol>
     *     <li>INTERMediator.constructMain() or INTERMediator.constructMain(true)<br>
     *         This happens to generate page from scratch.</li>
     *     <li>INTERMediator.constructMain(context)<br>
     *         This will be reconstracted to nodes of the "context" parameter.
     *         The context parameter should be refered to a IMLIbContext object.</li>
     *     <li>INTERMediator.constructMain(context, recordset)<br>
     *         This will append nodes to the enclocure of the "context" as a repeater.
     *         The context parameter should be refered to a IMLIbContext object.
     *         The recordset parameter is the newly created record
     *         as the form of an array of an dictionary.</li>
     * </ol>
     * @param updateRequiredContext If this parameter is omitted or set to true,
     *    INTER-Mediator is going to generate entire page. If ths parameter is set as the Context object,
     *    INTER-Mediator is going to generate a part of page which relies on just its context.
     * @param recordset If the updateRequiredContext paramter is set as the Context object,
     *    This parameter is set to newly created record.
     */
    constructMain: function (updateRequiredContext, recordset) {
      'use strict';
      var i, theNode, postSetFields = [], radioName = {}, nameSerial = 1,
        nameAttrCounter = 1, imPartsShouldFinished = [],
        isAcceptNotify = false, originalNodes, parentNode, sybilingNode;

      INTERMediator.eventListenerPostAdding = [];
      if (INTERMediatorOnPage.doBeforeConstruct) {
        INTERMediatorOnPage.doBeforeConstruct();
      }
      if (!INTERMediatorOnPage.isAutoConstruct) {
        return;
      }
      INTERMediatorOnPage.showProgress();

      INTERMediator.crossTableStage = 0;
      INTERMediator.appendingNodesAtLast = [];
      IMLibEventResponder.setup();
      INTERMediatorOnPage.retrieveAuthInfo();
      try {
        if (Pusher.VERSION) {
          INTERMediator.pusherAvailable = true;
          if (!INTERMediatorOnPage.clientNotificationKey) {
            INTERMediatorLog.setErrorMessage(
              Error('Pusher Configuration Error'), INTERMediatorOnPage.getMessages()[1039]);
            INTERMediator.pusherAvailable = false;
          }
        }
      } catch (ex) {
        INTERMediator.pusherAvailable = false;
        if (INTERMediatorOnPage.clientNotificationKey) {
          INTERMediatorLog.setErrorMessage(
            Error('Pusher Configuration Error'), INTERMediatorOnPage.getMessages()[1038]);
        }
      }

      try {
        if (updateRequiredContext === true || updateRequiredContext === undefined) {
          IMLibPageNavigation.deleteInsertOnNavi = [];
          INTERMediator.partialConstructing = false;
          INTERMediator.buttonIdNum = 1;
          IMLibContextPool.clearAll();
          pageConstruct();
        } else {
          IMLibPageNavigation.deleteInsertOnNavi = [];
          INTERMediator.partialConstructing = true;
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
            if (ex.message === '_im_requath_request_') {
              throw ex;
            } else {
              INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-8');
            }
          }

          for (i = 0; i < postSetFields.length; i++) {
            if (postSetFields[i].id && document.getElementById(postSetFields[i].id)) {
              document.getElementById(postSetFields[i].id).value = postSetFields[i].value;
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
        if (ex.message === '_im_requath_request_') {
          if (INTERMediatorOnPage.requireAuthentication) {
            if (!INTERMediatorOnPage.isComplementAuthData()) {
              INTERMediatorOnPage.clearCredentials();
              INTERMediatorOnPage.hideProgress();
              INTERMediatorOnPage.authenticating(
                function () {
                  INTERMediator.constructMain(updateRequiredContext);
                }
              );
              INTERMediator.partialConstructing = true;
              return;
            }
          }
        } else {
          INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-7');
          INTERMediator.partialConstructing = true;
        }
      }

      for (i = 0; i < imPartsShouldFinished.length; i++) {
        imPartsShouldFinished[i].finish();
      }

      for (i = 0; i < INTERMediator.appendingNodesAtLast.length; i++) {
        theNode = INTERMediator.appendingNodesAtLast[i].targetNode;
        parentNode = INTERMediator.appendingNodesAtLast[i].parentNode;
        sybilingNode = INTERMediator.appendingNodesAtLast[i].siblingNode;
        if (theNode && parentNode) {
          if (sybilingNode) {
            parentNode.insertBefore(theNode, sybilingNode);
          } else {
            parentNode.appendChild(theNode);
          }
        }
      }

      // Event listener should add after adding node to document.
      for (i = 0; i < INTERMediator.eventListenerPostAdding.length; i++) {
        theNode = document.getElementById(INTERMediator.eventListenerPostAdding[i].id);
        if (theNode) {
          INTERMediatorLib.addEvent(
            theNode,
            INTERMediator.eventListenerPostAdding[i].event,
            INTERMediator.eventListenerPostAdding[i].todo);
        }
      }

      if (INTERMediatorOnPage.doAfterConstruct) {
        INTERMediatorOnPage.doAfterConstruct();
      }
      IMLibPageNavigation.navigationSetup();
      INTERMediatorOnPage.isFinishToConstruct = false;
      INTERMediator.partialConstructing = true;
      INTERMediatorOnPage.hideProgress();
      INTERMediatorLog.flushMessage(); // Show messages

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

        // Restoring original HTML Document from backup data.
        bodyNode = document.getElementsByTagName('BODY')[0];
        if (!INTERMediator.rootEnclosure) {
          INTERMediator.rootEnclosure = bodyNode.innerHTML;
        } else {
          bodyNode.innerHTML = INTERMediator.rootEnclosure;
        }
        postSetFields = [];
        INTERMediatorOnPage.setReferenceToTheme();
        IMLibPageNavigation.initializeStepInfo(false);
        IMLibLocalContext.bindingDescendant(document.documentElement);

        try {
          seekEnclosureNode(bodyNode, null, null, null);
        } catch (ex) {
          if (ex.message === '_im_requath_request_') {
            throw ex;
          } else {
            INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-9');
          }
        }

        // After work to set up popup menus.
        for (i = 0; i < postSetFields.length; i++) {
          if (postSetFields[i].value === '' &&
            document.getElementById(postSetFields[i].id).tagName === 'SELECT') {
            // for compatibility with Firefox when the value of select tag is empty.
            emptyElement = document.createElement('option');
            emptyElement.setAttribute('id', INTERMediator.nextIdValue());
            emptyElement.setAttribute('value', '');
            emptyElement.setAttribute('data-im-element', 'auto-generated');
            document.getElementById(postSetFields[i].id).insertBefore(
              emptyElement, document.getElementById(postSetFields[i].id).firstChild);
          }
          document.getElementById(postSetFields[i].id).value = postSetFields[i].value;
        }
        //IMLibLocalContext.bindingDescendant(document.documentElement);
        IMLibCalc.updateCalculationFields();
        //IMLibPageNavigation.navigationSetup();

        if (isAcceptNotify && INTERMediator.pusherAvailable) {
          var channelName = INTERMediatorOnPage.clientNotificationIdentifier();
          var appKey = INTERMediatorOnPage.clientNotificationKey();
          if (appKey && appKey !== '_im_key_isnt_supplied' && !INTERMediator.pusherObject) {
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
              INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-47');
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
              if ((className && className.match(/_im_post/)) || (attr && attr.indexOf('post') >= 0)) {
                setupPostOnlyEnclosure(node);
              } else {
                if (INTERMediator.isIE) {
                  try {
                    expandEnclosure(node, currentRecord, parentObjectInfo, currentContextObj);
                  } catch (ex) {
                    if (ex.message === '_im_requath_request_') {
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
            if (ex.message === '_im_requath_request_') {
              throw ex;
            } else {
              INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-10');
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
            if (!postNodes[i].id) {
              postNodes[i].id = INTERMediator.nextIdValue();
            }
            IMLibMouseEventDispatch.setExecute(postNodes[i].id,
              (function () {
                var targetNode = postNodes[i];
                return function () {
                  IMLibUI.clickPostOnlyButton(targetNode);
                };
              })());
          }
        }
        nodes = node.childNodes;

        for (i = 0; i < nodes.length; i++) {
          seekEnclosureInPostOnly(nodes[i]);
        }
        // -------------------------------------------
        function seekEnclosureInPostOnly(node) {
          var children, wInfo, i, target;
          if (node.nodeType === 1) { // Work for an element
            try {
              target = node.getAttribute('data-im');
              if (!target) {
                target = node.getAttribute('data-im-group');
              }
              if (target) { // Linked element
                if (!node.id) {
                  node.id = 'IMPOST-' + INTERMediator.postOnlyNumber;
                  INTERMediator.postOnlyNumber++;
                }
                INTERMediatorLib.addEvent(node, 'blur', function () {
                  var idValue = node.id;
                  IMLibUI.valueChange(idValue, true);
                });
                if (node.tagName === 'INPUT' && node.getAttribute('type') === 'radio') {
                  if (!radioName[target]) {
                    radioName[target] = 'Name-' + nameSerial;
                    nameSerial++;
                  }
                  node.setAttribute('name', radioName[target]);
                }
              }

              if (INTERMediatorLib.isWidgetElement(node)) {
                wInfo = INTERMediatorLib.getWidgetInfo(node);
                if (wInfo[0]) {
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
              if (ex.message === '_im_requath_request_') {
                throw ex;
              } else {
                INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-11');
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
        if (currentContextObj &&
          currentContextObj.contextName &&
          currentRecord &&
          currentRecord[currentContextObj.contextName] &&
          currentRecord[currentContextObj.contextName][currentContextObj.contextName + '::' + INTERMediatorOnPage.defaultKeyName]) {
          // for FileMaker portal access mode
          recId = currentRecord[currentContextObj.contextName][currentContextObj.contextName + '::' + INTERMediatorOnPage.defaultKeyName];
          currentRecord = currentRecord[currentContextObj.contextName][recId];
        }
        if (imControl && imControl.match(/cross-table/)) {   // Cross Table
          expandCrossTableEnclosure(node, currentRecord, parentObjectInfo, currentContextObj);
        } else {    // Enclosure Processing as usual.
          repNodeTag = INTERMediatorLib.repeaterTagFromEncTag(node.tagName);
          repeatersOriginal = collectRepeatersOriginal(node, repNodeTag); // Collecting repeaters to this array.
          enclosureProcessing(node, repeatersOriginal, currentRecord, parentObjectInfo, currentContextObj);
        }
        IMLibLocalContext.bindingDescendant(node);
        /** --------------------------------------------------------------------
         * Expanding enclosure as usual (means not 'cross tabole').
         */
        function enclosureProcessing(enclosureNode,
                                     repeatersOriginal,
                                     currentRecord,
                                     parentObjectInfo,
                                     currentContextObj,
                                     procBeforeRetrieve,
                                     customExpandRepeater) {
          var linkedNodes, repeaters, linkDefs, voteResult, currentContextDef, fieldList, i, targetRecords,
            newNode, keyValue, selectedNode, isExpanding, calcFields, contextObj = null,
            targetRecordset, ix, keyingValue, footerNodes, headerNodes, nInfo;
          var tempObj = {};

          repeaters = collectRepeaters(repeatersOriginal);  // Collecting repeaters to this array.
          linkedNodes = INTERMediatorLib.seekLinkedAndWidgetNodes(repeaters, true).linkedNode;
          linkDefs = collectLinkDefinitions(linkedNodes);
          voteResult = tableVoting(linkDefs);
          currentContextDef = voteResult.targettable;
          INTERMediator.currentEncNumber++;

          if (!enclosureNode.getAttribute('id')) {
            enclosureNode.setAttribute('id', INTERMediator.nextIdValue());
          }

          if (!currentContextDef) {
            for (i = 0; i < repeatersOriginal.length; i++) {
              newNode = enclosureNode.appendChild(repeatersOriginal[i]);

              // for compatibility with Firefox
              if (repeatersOriginal[i].getAttribute('selected')) {
                selectedNode = newNode;
              }
              if (selectedNode !== undefined) {
                selectedNode.selected = true;
              }
              seekEnclosureNode(newNode, null, enclosureNode, currentContextObj);
            }
          } else {
            isExpanding = !IMLibPageNavigation.isNotExpandingContext(currentContextDef);
            contextObj = IMLibContextPool.generateContextObject(
              currentContextDef, enclosureNode, repeaters, repeatersOriginal);
            calcFields = contextObj.getCalculationFields();
            fieldList = voteResult.fieldlist.map(function (elm) {
              if (!calcFields[elm]) {
                calcFields.push(elm);
              }
              return elm;
            });

            if (currentContextDef.relation && currentContextDef.relation[0] &&
              Boolean(currentContextDef.relation[0].portal) === true) {
              // for FileMaker portal access mode
              contextObj.isPortal = true;
              if (!currentRecord) {
                tempObj = IMLibContextPool.generateContextObject(
                  {'name': contextObj.sourceName}, enclosureNode, repeaters, repeatersOriginal);
                if (targetRecords === undefined) {
                  targetRecords = retrieveDataForEnclosure(tempObj, fieldList, contextObj.foreignValue);
                }
                recId = targetRecords.recordset[0][INTERMediatorOnPage.defaultKeyName];
                currentRecord = targetRecords.recordset[0];
              }
            }

            contextObj.setRelationWithParent(currentRecord, parentObjectInfo, currentContextObj);
            if (contextObj.isPortal === true) {
              if (currentRecord) {
                currentContextDef.currentrecord = currentRecord;
                keyValue = currentRecord[currentContextDef.relation[0]['join-field']];
              }
            }

            if (procBeforeRetrieve) {
              procBeforeRetrieve(contextObj);
            }
            if (isExpanding) {
              targetRecords = retrieveDataForEnclosure(contextObj, fieldList, contextObj.foreignValue);
            } else {
              targetRecords = [];
              if (enclosureNode.tagName === 'TBODY') {
                enclosureNode.parentNode.style.display = 'none';
              } else {
                enclosureNode.style.display = 'none';
              }
            }
            contextObj.storeRecords(targetRecords);

            callbackForAfterQueryStored(currentContextDef, contextObj);
            if (customExpandRepeater === undefined) {
              contextObj.registeredId = targetRecords.registeredId;
              contextObj.nullAcceptable = targetRecords.nullAcceptable;
              isAcceptNotify |= !(INTERMediatorOnPage.notifySupport === false);
              expandRepeaters(contextObj, enclosureNode, targetRecords);
              IMLibPageNavigation.setupInsertButton(contextObj, keyValue, enclosureNode, contextObj.foreignValue);
              IMLibPageNavigation.setupBackNaviButton(contextObj, enclosureNode);
              callbackForEnclosure(currentContextDef, enclosureNode);
            } else {
              customExpandRepeater(contextObj, targetRecords);
            }
            contextObj.sequencing = false;
            if (enclosureNode.tagName === 'TBODY') {
              footerNodes = enclosureNode.parentNode.getElementsByTagName('TFOOT');
              linkedNodes = seekWithAttribute(footerNodes[0], 'data-im');
              if (linkedNodes) {
                INTERMediator.setIdValue(footerNodes[0]);
                targetRecordset = {};
                ix = null;
                keyingValue = '_im_footer';
                for (i = 0; i < linkedNodes.length; i++) {
                  nInfo = INTERMediatorLib.getNodeInfoArray(INTERMediatorLib.getLinkedElementInfo(linkedNodes[i])[0]);
                  if(linkedNodes[i] && currentContextDef.name ===nInfo.table) {
                    INTERMediator.setIdValue(linkedNodes[i]);
                  }
                  IMLibCalc.updateCalculationInfo(contextObj, keyingValue, linkedNodes[i].id, nInfo, targetRecordset);
                  if(contextObj.binding._im_footer) {
                    contextObj.binding._im_footer._im_repeater = footerNodes;
                  }
                }
              }
              headerNodes = enclosureNode.parentNode.getElementsByTagName('THEAD');
              linkedNodes = seekWithAttribute(headerNodes[0], 'data-im');
              if (linkedNodes) {
                INTERMediator.setIdValue(headerNodes[0]);
                targetRecordset = {};
                ix = null;
                keyingValue = '_im_header';
                for (i = 0; i < linkedNodes.length; i++) {
                  INTERMediator.setIdValue(linkedNodes[i]);
                  nInfo = INTERMediatorLib.getNodeInfoArray(INTERMediatorLib.getLinkedElementInfo(linkedNodes[i])[0]);
                  IMLibCalc.updateCalculationInfo(
                    contextObj, keyingValue, linkedNodes[i].id, nInfo, targetRecordset);
                  if(contextObj.binding._im_header) {
                    contextObj.binding._im_header._im_repeater = headerNodes;
                  }
                }
              }
            }
          }
          return contextObj;
        }

        function seekWithAttribute(node, attrName) {
          if (!node || node.nodeType !== 1) {
            return null;
          }
          var result = seekWithAttributeImpl(node, attrName);
          return result;
        }

        function seekWithAttributeImpl(node, attrName) {
          var ix, adding, result = [];
          if (node && node.nodeType === 1) {
            if (node.getAttribute(attrName)) {
              result.push(node);
            }
            if (node.childNodes) {
              for (ix = 0; ix < node.childNodes.length; ix++) {
                adding = seekWithAttributeImpl(node.childNodes[ix], attrName);
                if (adding.length > 0) {
                  [].push.apply(result, adding);
                }
              }
            }
          }
          return result;
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
          if (ctComponentNodes.length !== 4) {
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
          labelKeyColumn = crossCellContext.relation[0]['join-field'];
          labelKeyRow = crossCellContext.relation[1]['join-field'];

          // Create the first row
          INTERMediator.crossTableStage = 1;
          lineNode = document.createElement('TR');
          targetRepeater = ctComponentNodes[0].cloneNode(true);
          lineNode.appendChild(targetRepeater);
          node.appendChild(lineNode);

          // Append the column context in the first row
          targetRepeater = ctComponentNodes[1].cloneNode(true);
          colContext = enclosureProcessing(
            lineNode, [targetRepeater], null, parentObjectInfo, currentContextObj);
          colArray = colContext.indexingArray(labelKeyColumn);

          // Create second and following rows, and the first columns are appended row context
          INTERMediator.crossTableStage = 2;
          targetRepeater = ctComponentNodes[2].cloneNode(true);
          lineNode = document.createElement('TR');
          lineNode.appendChild(targetRepeater);
          rowContext = enclosureProcessing(
            node, [lineNode], null, parentObjectInfo, currentContextObj);
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
              INTERMediator.setIdValue(appendingNode);
              if (!nodeForKeyValues[colArray[j]]) {
                nodeForKeyValues[colArray[j]] = {};
              }
              nodeForKeyValues[colArray[j]][rowArray[i - 1]] = appendingNode;
            }
          }
          INTERMediator.setIdValue(node);
          enclosureProcessing(
            node, [targetRepeater], null, parentObjectInfo, currentContextObj,
            function (context) {
              var currentContextDef = context.getContextDef();
              INTERMediator.clearCondition(currentContextDef.name, '_imlabel_crosstable');
              INTERMediator.addCondition(currentContextDef.name, {
                field: currentContextDef.relation[0]['foreign-key'],
                operator: 'IN',
                value: colArray,
                onetime: true
              }, undefined, '_imlabel_crosstable');
              INTERMediator.addCondition(currentContextDef.name, {
                field: currentContextDef.relation[1]['foreign-key'],
                operator: 'IN',
                value: rowArray,
                onetime: true
              }, undefined, '_imlabel_crosstable');
            },
            function (contextObj, targetRecords) {
              var dataKeyColumn, dataKeyRow, currentContextDef, ix,
                linkedElements, targetNode, keyField, keyValue, keyingValue;
              currentContextDef = contextObj.getContextDef();
              keyField = contextObj.getKeyField();
              dataKeyColumn = currentContextDef.relation[0]['foreign-key'];
              dataKeyRow = currentContextDef.relation[1]['foreign-key'];
              if (targetRecords.recordset) {
                for (ix = 0; ix < targetRecords.recordset.length; ix++) { // for each record
                  record = targetRecords.recordset[ix];
                  if (nodeForKeyValues[record[dataKeyColumn]] &&
                    nodeForKeyValues[record[dataKeyColumn]][record[dataKeyRow]]) {
                    targetNode = nodeForKeyValues[record[dataKeyColumn]][record[dataKeyRow]];
                    if (targetNode) {
                      linkedElements = INTERMediatorLib.seekLinkedAndWidgetNodes(
                        [targetNode], false);
                      keyValue = record[keyField];
                      if (keyField && !keyValue && keyValue !== 0) {
                        keyValue = ix;
                      }
                      keyingValue = keyField + '=' + keyValue;
                    }
                    setupLinkedNode(
                      linkedElements, contextObj, targetRecords.recordset, ix, keyingValue);
                  }
                }
              }
            }
          );
        } // The end of function expandCrossTableEnclosure().

        // Detect cross table components in a tbody enclosure.
        function crossTableComponents(node) {
          var components = [], count = 0;
          repeatCTComponents(node.childNodes);
          return components;

          function repeatCTComponents(nodes) {
            var childNodes, i;
            for (i = 0; i < nodes.length; i++) {
              if (nodes[i].nodeType === 1 && (nodes[i].tagName === 'TH' || nodes[i].tagName === 'TD')) {
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
      function setupLinkedNode(linkedElements, contextObj, targetRecordset, ix, keyingValue) {
        var currentWidgetNodes, currentLinkedNodes, nInfo, currentContextDef, j, keyField, k, nodeId,
          curVal, replacedNode, typeAttr, children, wInfo, nameTable, idValuesForFieldName = {},
          linkInfoArray, nameTableKey, nameNumber, nameAttr, curTarget;

        currentContextDef = contextObj.getContextDef();
        try {
          currentWidgetNodes = linkedElements.widgetNode;
          currentLinkedNodes = linkedElements.linkedNode;
          keyField = contextObj.getKeyField();
          if (targetRecordset[ix] && (targetRecordset[ix][keyField] || targetRecordset[ix][keyField] === 0)) {
            for (k = 0; k < currentLinkedNodes.length; k++) {
              // for each linked element
              nodeId = currentLinkedNodes[k].getAttribute('id');
              replacedNode = INTERMediator.setIdValue(currentLinkedNodes[k]);
              typeAttr = replacedNode.getAttribute('type');
              if (typeAttr === 'checkbox' || typeAttr === 'radio') {
                children = replacedNode.parentNode.childNodes;
                for (i = 0; i < children.length; i++) {
                  if (children[i].nodeType === 1 && children[i].tagName === 'LABEL' &&
                    nodeId === children[i].getAttribute('for')) {
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
          if (ex.message === '_im_requath_request_') {
            throw ex;
          } else {
            INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-101');
          }
        }

        nameTable = {};
        for (k = 0; k < currentLinkedNodes.length; k++) {
          try {
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
            if (typeAttr === 'radio') { // set the value to radio button
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
              curVal = targetRecordset[ix][nInfo.field];
              if (!INTERMediator.isDBDataPreferable || curVal) {
                IMLibCalc.updateCalculationInfo(contextObj, keyingValue, nodeId, nInfo, targetRecordset[ix]);
              }
              if (nInfo.table === currentContextDef.name) {
                curTarget = nInfo.target;
                if (IMLibElement.setValueToIMNode(currentLinkedNodes[k], curTarget, curVal)) {
                  postSetFields.push({'id': nodeId, 'value': curVal});
                }
                contextObj.setValue(keyingValue, nInfo.field, curVal, nodeId, curTarget);
                if (idValuesForFieldName[nInfo.field] === undefined) {
                  idValuesForFieldName[nInfo.field] = [];
                }
                idValuesForFieldName[nInfo.field].push(nodeId);
              }
            }
          } catch (ex) {
            if (ex.message === '_im_requath_request_') {
              throw ex;
            } else {
              INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-27');
            }
          }
        }
        return idValuesForFieldName;
      }

      /** --------------------------------------------------------------------
       * Expanding an repeater.
       */
      function expandRepeaters(contextObj, node, targetRecords) {
        var newNode, nodeClass, dataAttr, repeatersOneRec, newlyAddedNodes, encNodeTag, repNodeTag, ix, 
          repeatersOriginal, targetRecordset, targetTotalCount, i, currentContextDef, indexContext, insertNode, 
          countRecord, linkedElements, keyingValue, keyField, keyValue, idValuesForFieldName;

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
            if ((nodeClass && nodeClass.indexOf(INTERMediator.noRecordClassName) > -1) ||
              (dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsNoResultDataControlName) > -1)) {
              node.appendChild(newNode);
              INTERMediator.setIdValue(newNode);
              seekEnclosureNode(newNode, null, null, null);
            }
          }
        }

        countRecord = targetRecordset ? targetRecordset.length : 0;
        for (ix = 0; ix < countRecord; ix++) { // for each record
          repeatersOneRec = cloneEveryNodes(repeatersOriginal);
          linkedElements = INTERMediatorLib.seekLinkedAndWidgetNodes(repeatersOneRec, true);
          keyField = contextObj.getKeyField();
          for (i = 0; i < repeatersOneRec.length; i++) {
            INTERMediator.setIdValue(repeatersOneRec[i]);
          }
          if (targetRecordset[ix] && (targetRecordset[ix][keyField] || targetRecordset[ix][keyField] === 0)) {
            keyValue = targetRecordset[ix][keyField];
            if (keyField && !keyValue && keyValue !== 0) {
              INTERMediatorLog.setErrorMessage('The value of the key field is null.',
                'This No.[' + ix + '] record should be ignored.');
              keyValue = ix;
            }
            keyingValue = keyField + '=' + keyValue;
          }
          idValuesForFieldName = setupLinkedNode(linkedElements, contextObj, targetRecordset, ix, keyingValue);
          IMLibPageNavigation.setupDeleteButton(encNodeTag, repeatersOneRec, contextObj, keyField, keyValue);
          IMLibPageNavigation.setupNavigationButton(encNodeTag, repeatersOneRec, currentContextDef, keyField, keyValue, contextObj);
          IMLibPageNavigation.setupCopyButton(encNodeTag, repNodeTag, repeatersOneRec, contextObj, targetRecordset[ix]);

          if (!currentContextDef.portal || (!!currentContextDef.portal && targetTotalCount > 0)) {
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
              if (!(nodeClass && nodeClass.indexOf(INTERMediator.noRecordClassName) >= 0) &&
                !(dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsNoResultDataControlName) >= 0) &&
                !(dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsSeparatorDataControlName) >= 0) &&
                !(dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsFooterDataControlName) >= 0) &&
                !(dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsHeaderDataControlName) >= 0)
              ) {
                if (!insertNode) {
                  node.appendChild(newNode);
                } else {
                  insertNode.parentNode.insertBefore(newNode, insertNode);
                }
                newlyAddedNodes.push(newNode);
                if (!newNode.id) {
                  INTERMediator.setIdValue(newNode);
                }
                contextObj.setValue(keyingValue, '_im_repeater', '', newNode.id, '', currentContextDef.portal);
                seekEnclosureNode(newNode, targetRecordset[ix], idValuesForFieldName, contextObj);
              }
            }
            if ((ix + 1) !== countRecord) {
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

        IMLibPageNavigation.setupDetailAreaToFirstRecord(currentContextDef, contextObj);

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
      function retrieveDataForEnclosure(contextObj, fieldList, relationValue) {
        var targetRecords, recordNumber, useLimit, key, recordset = [];

        if (Boolean(contextObj.contextDefinition.cache) === true) {
          targetRecords = retrieveDataFromCache(contextObj.contextDefinition, relationValue);
        } else if (contextObj.contextDefinition.data) {
          for (key in contextObj.contextDefinition.data) {
            if (contextObj.contextDefinition.data.hasOwnProperty(key)) {
              recordset.push(contextObj.contextDefinition.data[key]);
            }
          }
          targetRecords = {
            'recordset': recordset,
            'count': recordset.length,
            'totalCount': recordset.length,
            'nullAcceptable': true
          };
        } else {   // cache is not active.
          try {
            targetRecords = contextObj.getPortalRecords();
            if (!targetRecords) {
              useLimit = contextObj.isUseLimit();
              recordNumber = contextObj.getRecordNumber();
              targetRecords = INTERMediator_DBAdapter.db_query({
                'name': contextObj.contextDefinition.name,
                'records': isNaN(recordNumber) ? 100000000 : recordNumber,
                'paging': contextObj.contextDefinition.paging,
                'fields': fieldList,
                'parentkeyvalue': relationValue,
                'conditions': null,
                'useoffset': true,
                'uselimit': useLimit
              });
            }
          } catch (ex) {
            if (ex.message === '_im_requath_request_') {
              throw ex;
            } else {
              INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-12');
            }
          }
        }
        if (contextObj.contextDefinition['appending-data']) {
          for (key in contextObj.contextDefinition['appending-data']) {
            if (contextObj.contextDefinition['appending-data'].hasOwnProperty(key)) {
              targetRecords.recordset.push(contextObj.contextDefinition['appending-data'][key]);
            }
          }
        }
        return targetRecords;
      }

      /* --------------------------------------------------------------------
       This implementation for cache is quite limited.
       */
      function retrieveDataFromCache(currentContextDef, relationValue) {
        var targetRecords = null, pagingValue, counter, ix, oneRecord, isMatch, index, keyField, fieldName,
          recordsValue;
        try {
          if (!INTERMediatorOnPage.dbCache[currentContextDef.name]) {
            INTERMediatorOnPage.dbCache[currentContextDef.name] =
              INTERMediator_DBAdapter.db_query({
                name: currentContextDef.name,
                records: null,
                paging: null,
                fields: null,
                parentkeyvalue: null,
                conditions: null,
                useoffset: false
              });
          }
          if (relationValue === null) {
            targetRecords = INTERMediatorOnPage.dbCache[currentContextDef.name];
          } else {
            targetRecords = {recordset: [], count: 0};
            counter = 0;
            for (ix in INTERMediatorOnPage.dbCache[currentContextDef.name].recordset) {
              if (INTERMediatorOnPage.dbCache[currentContextDef.name].recordset.hasOwnProperty(ix)) {
                oneRecord = INTERMediatorOnPage.dbCache[currentContextDef.name].recordset[ix];
                isMatch = true;
                index = 0;
                for (keyField in relationValue) {
                  if (relationValue.hasOwnProperty(keyField)) {
                    fieldName = currentContextDef.relation[index]['foreign-key'];
                    if (oneRecord[fieldName] !== relationValue[keyField]) {
                      isMatch = false;
                      break;
                    }
                    index++;
                  }
                }
                if (isMatch) {
                  pagingValue = currentContextDef.paging ? currentContextDef.paging : false;
                  recordsValue = currentContextDef.records ? currentContextDef.records : 10000000000;

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
            return targetRecords;
          }
        } catch (ex) {
          if (ex.message === '_im_requath_request_') {
            throw ex;
          } else {
            INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-24');
          }
        }
      }

      /* --------------------------------------------------------------------

       */
      function callbackForRepeaters(currentContextDef, node, newlyAddedNodes) {
        try {
          if (INTERMediatorOnPage.additionalExpandingRecordFinish[currentContextDef.name]) {
            INTERMediatorOnPage.additionalExpandingRecordFinish[currentContextDef.name](node);
            INTERMediatorLog.setDebugMessage(
              'Call the post enclosure method INTERMediatorOnPage.additionalExpandingRecordFinish[' +
              currentContextDef.name + '] with the context.', 2);
          }
        } catch (ex) {
          if (ex.message === '_im_requath_request_') {
            throw ex;
          } else {
            INTERMediatorLog.setErrorMessage(ex,
              'EXCEPTION-33: hint: post-repeater of ' + currentContextDef.name);
          }
        }
        try {
          if (INTERMediatorOnPage.expandingRecordFinish) {
            INTERMediatorOnPage.expandingRecordFinish(currentContextDef.name, newlyAddedNodes);
            INTERMediatorLog.setDebugMessage(
              'Call INTERMediatorOnPage.expandingRecordFinish with the context: ' +
              currentContextDef.name, 2);
          }

          if (currentContextDef['post-repeater']) {
            INTERMediatorOnPage[currentContextDef['post-repeater']](newlyAddedNodes);

            INTERMediatorLog.setDebugMessage('Call the post repeater method INTERMediatorOnPage.' +
              currentContextDef['post-repeater'] + ' with the context: ' +
              currentContextDef.name, 2);
          }
        } catch (ex) {
          if (ex.message === '_im_requath_request_') {
            throw ex;
          } else {
            INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-23');
          }
        }

      }

      /* --------------------------------------------------------------------

       */
      function callbackForEnclosure(currentContextDef, node) {
        try {
          if (INTERMediatorOnPage.additionalExpandingEnclosureFinish[currentContextDef.name]) {
            INTERMediatorOnPage.additionalExpandingEnclosureFinish[currentContextDef.name](node);
            INTERMediatorLog.setDebugMessage(
              'Call the post enclosure method INTERMediatorOnPage.additionalExpandingEnclosureFinish[' +
              currentContextDef.name + '] with the context.', 2);
          }
        } catch (ex) {
          if (ex.message === '_im_requath_request_') {
            throw ex;
          } else {
            INTERMediatorLog.setErrorMessage(ex,
              'EXCEPTION-32: hint: post-enclosure of ' + currentContextDef.name);
          }
        }
        try {
          if (INTERMediatorOnPage.expandingEnclosureFinish) {
            INTERMediatorOnPage.expandingEnclosureFinish(currentContextDef.name, node);
            INTERMediatorLog.setDebugMessage(
              'Call INTERMediatorOnPage.expandingEnclosureFinish with the context: ' +
              currentContextDef.name, 2);
          }
        } catch (ex) {
          if (ex.message === '_im_requath_request_') {
            throw ex;
          } else {
            INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-21');
          }
        }
        try {
          if (currentContextDef['post-enclosure']) {
            INTERMediatorOnPage[currentContextDef['post-enclosure']](node);
            INTERMediatorLog.setDebugMessage(
              'Call the post enclosure method INTERMediatorOnPage.' + currentContextDef['post-enclosure'] +
              ' with the context: ' + currentContextDef.name, 2);
          }
        } catch (ex) {
          if (ex.message === '_im_requath_request_') {
            throw ex;
          } else {
            INTERMediatorLog.setErrorMessage(ex,
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
            INTERMediatorLog.setDebugMessage(
              'Call the post query stored method INTERMediatorOnPage.' +
              currentContextDef['post-enclosure'] + ' with the context: ' + currentContextDef.name, 2);
          }
        } catch (ex) {
          if (ex.message === '_im_requath_request_') {
            throw ex;
          } else {
            INTERMediatorLog.setErrorMessage(ex,
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
            if (children[i].tagName === repNodeTag) {
              // If the element is a repeater.
              repeatersOriginal.push(children[i]); // Record it to the array.
            } else if (!repNodeTag && (children[i].getAttribute('data-im-control'))) {
              imControl = children[i].getAttribute('data-im-control');
              if (imControl.indexOf(INTERMediatorLib.roleAsRepeaterDataControlName) > -1 ||
                imControl.indexOf(INTERMediatorLib.roleAsSeparatorDataControlName) > -1 ||
                imControl.indexOf(INTERMediatorLib.roleAsFooterDataControlName) > -1 ||
                imControl.indexOf(INTERMediatorLib.roleAsHeaderDataControlName) > -1 ||
                imControl.indexOf(INTERMediatorLib.roleAsNoResultDataControlName) > -1
              ) {
                repeatersOriginal.push(children[i]);
              }
            } else if (!repNodeTag && INTERMediatorLib.getClassAttributeFromNode(children[i]) &&
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
          cloneNode.setAttribute('id', INTERMediator.nextIdValue());
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
          if (nodeDefs) {
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
          nodeInfoTableIndex, context, restDefs = [],
          tableVote = [],    // Containing editable elements or not.
          fieldList = []; // Create field list for database fetch.

        for (j = 0; j < linkDefs.length; j++) {
          nodeInfoArray = INTERMediatorLib.getNodeInfoArray(linkDefs[j]);
          nodeInfoField = nodeInfoArray.field;
          nodeInfoTable = nodeInfoArray.table;
          nodeInfoTableIndex = nodeInfoArray.tableindex;   // Table name added '_im_index_' as the prefix.
          if (nodeInfoTable != IMLibLocalContext.contextName) {
            if (nodeInfoField &&
              nodeInfoField.length !== 0 &&
              nodeInfoTable &&
              nodeInfoTable.length !== 0) {
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
              INTERMediatorLog.setErrorMessage(
                INTERMediatorLib.getInsertedStringFromErrorNumber(1006, [linkDefs[j]]));
            }
          }
        }
        maxVoted = -1;
        maxTableName = ''; // Which is the maximum voted table name.
        for (tableName in tableVote) {
          if (tableVote.hasOwnProperty(tableName)) {
            if (maxVoted < tableVote[tableName]) {
              maxVoted = tableVote[tableName];
              maxTableName = tableName.substring(10);
            }
          }
        }
        context = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', maxTableName);
        if (linkDefs.length > 0 && !context && maxTableName) {
          INTERMediatorLog.setErrorMessage(
            INTERMediatorLib.getInsertedStringFromErrorNumber(1046, [maxTableName]));
        }
        for (j = 0; j < linkDefs.length; j++) {
          if (linkDefs[j].indexOf(maxTableName) !== 0 && linkDefs[j].indexOf('_@') !== 0) {
            restDefs.push(linkDefs[j]);
          }
        }
        if (linkDefs.length > 0 && context && restDefs.length > 0) {
          INTERMediatorLog.setErrorMessage(
            INTERMediatorLib.getInsertedStringFromErrorNumber(1047, [maxTableName, restDefs.toString()]));
        }
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
      function getEnclosedNode(rootNode, tableName, fieldName) {
        var i, j, nodeInfo, nInfo, children, r;

        if (rootNode.nodeType === 1) {
          nodeInfo = INTERMediatorLib.getLinkedElementInfo(rootNode);
          for (j = 0; j < nodeInfo.length; j++) {
            nInfo = INTERMediatorLib.getNodeInfoArray(nodeInfo[j]);
            if (nInfo.table === tableName && nInfo.field === fieldName) {
              return rootNode;
            }
          }
        }
        children = rootNode.childNodes; // Check all child node of the enclosure.
        for (i = 0; i < children.length; i++) {
          r = getEnclosedNode(children[i], tableName, fieldName);
          if (r) {
            return r;
          }
        }
        return null;
      }

      /* --------------------------------------------------------------------

       */
      function appendCredit() {
        var bodyNode, creditNode, cNode, spNode, aNode, versionString;

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
          if (INTERMediatorOnPage.metadata) {
            versionString = ' Ver.' + INTERMediatorOnPage.metadata.version
              + '(' + INTERMediatorOnPage.metadata.releasedate + ')';
          } else {
            versionString = ' Ver. Development Now!';
          }
          spNode.appendChild(document.createTextNode(versionString));
        }
      }
    }
    ,

    /* --------------------------------------------------------------------

     */
    setIdValue: function (node) {
      'use strict';
      var i, elementInfo, comp, overwrite = true;

      if (node.getAttribute('id') === null) {
        node.setAttribute('id', INTERMediator.nextIdValue());
      } else {
        if (INTERMediator.elementIds.indexOf(node.getAttribute('id')) >= 0) {
          elementInfo = INTERMediatorLib.getLinkedElementInfo(node);
          for (i = 0; i < elementInfo.length; i++) {
            comp = elementInfo[i].split(INTERMediator.separator);
            if (comp[2] === '#id') {
              overwrite = false;
            }
          }
          if (overwrite) {
            node.setAttribute('id', INTERMediator.nextIdValue());
          }
        }
        INTERMediator.elementIds.push(node.getAttribute('id'));
      }
      return node;
    }
    ,

    nextIdValue: function () {
      'use strict';
      INTERMediator.linkedElmCounter++;
      return currentIdValue();

      function currentIdValue() {
        return 'IM' + INTERMediator.currentEncNumber + '-' + INTERMediator.linkedElmCounter;
      }
    }
    ,

    getLocalProperty: function (localKey, defaultValue) {
      'use strict';
      var value;
      value = IMLibLocalContext.getValue(localKey);
      return value === null ? defaultValue : value;
    }
    ,

    setLocalProperty: function (localKey, value) {
      'use strict';
      IMLibLocalContext.setValue(localKey, value, true);
    }
    ,

    addCondition: function (contextName, condition, notMatching, label) {
      'use strict';
      var value, i, hasIdentical;
      if (notMatching) {
        condition.matching = !notMatching;
      } else {
        condition.matching = INTERMediator_DBAdapter.eliminateDuplicatedConditions;
      }
      if (label) {
        condition.label = label;
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
              if (value[contextName][i].field === condition.field &&
                value[contextName][i].operator === condition.operator) {
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
    }
    ,

    clearCondition: function (contextName, label) {
      'use strict';
      var i, value = INTERMediator.additionalCondition;
      if (label === undefined) {
        if (value[contextName]) {
          delete value[contextName];
          INTERMediator.additionalCondition = value;
          IMLibLocalContext.archive();
        }
      }
      else {
        if (value[contextName]) {
          for (i = 0; i < value[contextName].length; i++) {
            if (value[contextName][i].label === label) {
              value[contextName].splice(i, 1);
              i--;
            }
          }
          INTERMediator.additionalCondition = value;
          IMLibLocalContext.archive();
        }
      }
    }
    ,

    addSortKey: function (contextName, sortKey) {
      'use strict';
      var value = INTERMediator.additionalSortKey;
      if (value[contextName]) {
        value[contextName].push(sortKey);
      } else {
        value[contextName] = [sortKey];
      }
      INTERMediator.additionalSortKey = value;
      IMLibLocalContext.archive();
    }
    ,

    clearSortKey: function (contextName) {
      'use strict';
      var value = INTERMediator.additionalSortKey;
      if (value[contextName]) {
        delete value[contextName];
        INTERMediator.additionalSortKey = value;
        IMLibLocalContext.archive();
      }
    }
    ,

    setRecordLimit: function (contextName, limit) {
      'use strict';
      var value = INTERMediator.recordLimit;
      value[contextName] = limit;
      INTERMediator.recordLimit = value;
      IMLibLocalContext.archive();
    }
    ,

    clearRecordLimit: function (contextName) {
      'use strict';
      var value = INTERMediator.recordLimit;
      if (value[contextName]) {
        delete value[contextName];
        INTERMediator.recordLimit = value;
        IMLibLocalContext.archive();
      }
    }
    ,

    /* Compatibility for previous version. These methos are defined here ever.
     * Now these are defined in INTERMediatorLog object. */
    flushMessage: function () {
      'use strict';
      INTERMediatorLog.flushMessage();
    }
    ,
    setErrorMessage: function (ex, moreMessage) {
      'use strict';
      INTERMediatorLog.setErrorMessage(ex, moreMessage);
    }
    ,
    setDebugMessage: function (message, level) {
      'use strict';
      INTERMediatorLog.setDebugMessage(message, level);
    }
  }
;

/**
 * Compatibility for IE8
 */
if (!Object.keys) {
  Object.keys = function (obj) {
    'use strict';
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
      'use strict';
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
    'use strict';
    return this.replace(/^\s+|\s+$/g, '');
  };
}
