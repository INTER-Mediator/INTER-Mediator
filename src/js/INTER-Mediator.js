/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (https://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

// JSHint support
/* global IMLibContextPool, INTERMediatorOnPage, IMLibMouseEventDispatch, IMLibLocalContext, INTERMediatorLog,
 INTERMediatorLib, INTERMediator_DBAdapter, IMLibCalc, IMLibPageNavigation,
 IMLibEventResponder, IMLibElement, IMLibUI, INTERMediatorLog, IMParts_Catalog */
/* jshint -W083 */ // Function within a loop

/**
 * Preventing error on module.export in merged js file.
 */
// let module = {}
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
const INTERMediator = {
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
   * @type {int}
   */
  waitSecondsAfterPostMessage: 4,
  /**
   * @public
   * @type {int}
   */
  pagedAllCount: 0,
  /**
   * This property is for FileMaker_FX.
   * @public
   * @type {int}
   */
  totalRecordCount: null,
  /**
   * @private
   * @type {int}
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
   * @type {int}
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
   * @type {int}
   */
  linkedElmCounter: 0,
  /**
   * @type {int}
   */
  buttonIdNum: 0,
  /**
   * @type {string}
   */
  detailNodeOriginalDisplay: 'none',
  /**
   * @type {boolean}
   */
  dateTimeFunction: false,
  /**
   * @type {int}
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
   * @type {int}
   */
  crossTableStage: 0, // 0: not cross table, 1: column label, 2: row label, 3 interchange cells

  eventListenerPostAdding: null,
  appendingNodesAtLast: null,
  currentContext: null,
  currentRecordset: null,
  socketMarkNode: null,

  // Local Context Conditions behaviors
  alwaysAddOperationExchange: false, // for compatible previous ver.10
  lcConditionsOP1AND: false,
  lcConditionsOP2AND: false,
  lcConditionsOP3AND: false,

  // Detect Internet Explorer and its version.
  propertyIETridentSetup: () => {
    'use strict'
    let ua = ''
    try {
      ua = navigator.userAgent
    } catch (e) {
      //
    }
    let position = ua.toLocaleUpperCase().indexOf('MSIE')
    if (position >= 0) {
      INTERMediator.isIE = true
      for (let i = position + 4; i < ua.length; i++) {
        const c = ua.charAt(i)
        if (!(c === ' ' || c === '.' || (c >= '0' && c <= '9'))) {
          INTERMediator.ieVersion = parseFloat(ua.substring(position + 4, i))
          break
        }
      }
    }
    position = ua.indexOf('; Trident/')
    if (position >= 0) {
      INTERMediator.isTrident = true
      for (let i = position + 10; i < ua.length; i++) {
        const c = ua.charAt(i)
        if (!(c === ' ' || c === '.' || (c >= '0' && c <= '9'))) {
          INTERMediator.ieVersion = parseFloat(ua.substring(position + 10, i)) + 4
          if (INTERMediator.ieVersion === 11) {
            INTERMediator.isIE = true
          }
          break
        }
      }
    }
    position = ua.indexOf(' Edge/')
    if (position >= 0) {
      INTERMediator.isEdge = true
      for (let i = position + 6; i < ua.length; i++) {
        const c = ua.charAt(i)
        if (!(c === ' ' || c === '.' || (c >= '0' && c <= '9')) || i === ua.length - 1) {
          INTERMediator.ieVersion = parseFloat(ua.substring(position + 6, i))
          break
        }
      }
    }
  },

  // Referred from https://w3g.jp/blog/js_browser_sniffing2015
  propertyW3CUserAgentSetup: () => {
    'use strict'
    let u = ''
    try {
      u = window.navigator.userAgent.toLowerCase()
    } catch (e) {
      //
    }
    INTERMediator.isTablet =
      (u.indexOf('windows') > -1 && u.indexOf('touch') > -1 && u.indexOf('tablet pc') === -1) ||
      u.indexOf('ipad') > -1 ||
      (u.indexOf('android') > -1 && u.indexOf('mobile') === -1) ||
      (u.indexOf('firefox') > -1 && u.indexOf('tablet') > -1) ||
      u.indexOf('kindle') > -1 ||
      u.indexOf('silk') > -1 ||
      u.indexOf('playbook') > -1
    INTERMediator.isMobile =
      (u.indexOf('windows') > -1 && u.indexOf('phone') > -1) ||
      u.indexOf('iphone') > -1 ||
      u.indexOf('ipod') > -1 ||
      (u.indexOf('android') > -1 && u.indexOf('mobile') > -1) ||
      (u.indexOf('firefox') > -1 && u.indexOf('mobile') > -1) ||
      u.indexOf('blackberry') > -1
  },

  initialize: () => {
    'use strict'
    INTERMediatorOnPage.removeCookie('_im_localcontext')

    INTERMediator.additionalCondition = {}
    INTERMediator.additionalSortKey = {}
    INTERMediator.startFrom = 0
    IMLibLocalContext.archive()
  },

  ssSocket: null,
  connectToServiceServer: () => {
    if (!INTERMediatorOnPage.serviceServerStatus || !INTERMediatorOnPage.activateClientService) {
      return
    }
    INTERMediator.ssSocket = io(INTERMediatorOnPage.serviceServerURL)
    INTERMediator.ssSocket.on('connected', INTERMediator.serviceServerConnected)
    // window.addEventListener('unload', INTERMediator.serviceServerShouldDisconnect)
    INTERMediator.ssSocket.on('notify', (msg) => {
      let isContinue = true
      switch (msg.operation) {
        case 'update':
          if (INTERMediatorOnPage.syncBeforeUpdate) {
            isContinue = INTERMediatorOnPage.syncBeforeUpdate(msg.data)
          }
          if (isContinue) {
            if (!msg.justnotify) {
              IMLibContextPool.updateOnAnotherClientUpdated(msg.data)
            }
            if (INTERMediatorOnPage.syncAfterUpdate) {
              INTERMediatorOnPage.syncAfterUpdate(msg.data)
            }
          }
          break
        case 'create':
          if (INTERMediatorOnPage.syncBeforeCreate) {
            isContinue = INTERMediatorOnPage.syncBeforeCreate(msg.data)
          }
          if (isContinue) {
            if (!msg.justnotify) {
              IMLibContextPool.updateOnAnotherClientCreated(msg.data)
            }
            if (INTERMediatorOnPage.syncAfterCreate) {
              INTERMediatorOnPage.syncAfterCreate(msg.data)
            }
          }
          break
        case 'delete':
          if (INTERMediatorOnPage.syncBeforeDelete) {
            isContinue = INTERMediatorOnPage.syncBeforeDelete(msg.data)
          }
          if (isContinue) {
            IMLibContextPool.updateOnAnotherClientDeleted(msg.data)
            if (INTERMediatorOnPage.syncAfterDelete) {
              INTERMediatorOnPage.syncAfterDelete(msg.data)
            }
          }
          break
      }
    })
  },

  serviceServerConnected: () => {
    INTERMediator.ssSocket.emit('init', {'clientid': INTERMediatorOnPage.clientNotificationIdentifier()})
    if (INTERMediator.socketMarkNode) {
      INTERMediator.socketMarkNode.style.color = 'yellow'
    }
  },

  serviceServerShouldDisconnect: () => {
    if (!INTERMediatorOnPage.serviceServerStatus || !INTERMediatorOnPage.activateClientService) {
      return
    }
    INTERMediator_DBAdapter.unregister()
    INTERMediator.socketMarkNode.style.color = 'red'
    INTERMediator.ssSocket.disconnect()
  },

  /** Construct Page **
   * Construct the Web Page with DB Data. Usually this method will be called automatically.
   * @param indexOfKeyFieldObject If this parameter is omitted or set to true,
   *    INTER-Mediator is going to generate entire page. If ths parameter is set as the Context object,
   *    INTER-Mediator is going to generate a part of page which relies on just its context.
   */
  construct: (indexOfKeyFieldObject) => {
    'use strict'
    if (indexOfKeyFieldObject === true || typeof indexOfKeyFieldObject === 'undefined') {
      if (INTERMediatorOnPage.isFinishToConstruct) {
        return
      }
      INTERMediatorOnPage.isFinishToConstruct = true
      INTERMediator.constructMain(true)
    } else {
      INTERMediator.constructMain(indexOfKeyFieldObject)
    }
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
  constructMain: async (updateRequiredContext, recordset) => {
    'use strict'
    let radioName = {}
    let nameSerial = 1
    let nameAttrCounter = 1
    let imPartsShouldFinished = []
    let postSetFields = []
    INTERMediator.currentContext = updateRequiredContext
    INTERMediator.currentRecordset = recordset

    INTERMediator.eventListenerPostAdding = []
    if (INTERMediatorOnPage.doBeforeConstruct) {
      INTERMediatorOnPage.doBeforeConstruct()
    }
    if (!INTERMediatorOnPage.isAutoConstruct) {
      return
    }
    INTERMediatorOnPage.showProgress(false)

    INTERMediator.crossTableStage = 0
    INTERMediator.appendingNodesAtLast = []
    if (updateRequiredContext !== true && typeof updateRequiredContext !== 'undefined' && updateRequiredContext &&
      INTERMediatorOnPage.doBeforePartialConstruct) {
      INTERMediatorOnPage.doBeforePartialConstruct(updateRequiredContext)
    }
    IMLibEventResponder.setup()
    await INTERMediatorOnPage.retrieveAuthInfo()
    INTERMediator.connectToServiceServer()

    if (!IMLibPageNavigation.isKeepOnNaviArray) {
      IMLibPageNavigation.deleteInsertOnNavi = []
    }
    try {
      if (updateRequiredContext === true || typeof updateRequiredContext === 'undefined') {
        INTERMediator.partialConstructing = false
        INTERMediator.buttonIdNum = 1
        IMLibContextPool.clearAll()
        await pageConstruct()
      } else {
        INTERMediator.partialConstructing = true
        try {
          if (!recordset) {
            updateRequiredContext.removeContext()
            const originalNodes = updateRequiredContext.original
            for (let i = 0; i < originalNodes.length; i++) {
              updateRequiredContext.enclosureNode.appendChild(originalNodes[i].cloneNode(true))
            }
            await seekEnclosureNode(
              updateRequiredContext.enclosureNode,
              updateRequiredContext.foreignValue,
              updateRequiredContext.dependingParentObjectInfo,
              updateRequiredContext)
          } else {
            await expandRepeaters(
              updateRequiredContext,
              updateRequiredContext.enclosureNode,
              {recordset: recordset, targetTotalCount: 1, targetCount: 1}
            )
          }
        } catch (ex) {
          if (ex.message === '_im_auth_required_') {
            throw ex
          } else {
            INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-8')
          }
        }

        for (let i = 0; i < postSetFields.length; i++) {
          if (postSetFields[i].id && document.getElementById(postSetFields[i].id)) {
            document.getElementById(postSetFields[i].id).value = postSetFields[i].value
          }
        }
        IMLibCalc.updateCalculationFields()
        IMLibPageNavigation.navigationSetup()
        /*
         If the pagination control should be setup, the property IMLibPageNavigation.deleteInsertOnNavi
         to maintain to be a valid data.
         */
      }
    } catch (ex) {
      if (ex.message === '_im_auth_required_') {
        if (INTERMediatorOnPage.requireAuthentication) {
          if (!INTERMediatorOnPage.isComplementAuthData()) {
            INTERMediatorOnPage.clearCredentials()
            INTERMediatorOnPage.hideProgress()
            INTERMediatorOnPage.authenticating(
              function () {
                INTERMediator.constructMain(updateRequiredContext)
              }
            )
            INTERMediator.partialConstructing = true
            return
          }
        }
      } else {
        INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-7')
        INTERMediator.partialConstructing = true
      }
    }

    for (let i = 0; i < imPartsShouldFinished.length; i++) {
      imPartsShouldFinished[i].finish()
    }

    for (let i = 0; i < INTERMediator.appendingNodesAtLast.length; i++) {
      const theNode = INTERMediator.appendingNodesAtLast[i].targetNode
      const parentNode = INTERMediator.appendingNodesAtLast[i].parentNode
      const sybilingNode = INTERMediator.appendingNodesAtLast[i].siblingNode
      if (theNode && parentNode) {
        if (sybilingNode) {
          parentNode.insertBefore(theNode, sybilingNode)
        } else {
          parentNode.appendChild(theNode)
        }
      }
    }

    // Event listener should add after adding node to document.
    for (let i = 0; i < INTERMediator.eventListenerPostAdding.length; i++) {
      const theNode = document.getElementById(INTERMediator.eventListenerPostAdding[i].id)
      if (theNode) {
        INTERMediatorLib.addEvent(
          theNode,
          INTERMediator.eventListenerPostAdding[i].event,
          INTERMediator.eventListenerPostAdding[i].todo)
      }
    }

    if (updateRequiredContext !== true && typeof updateRequiredContext !== 'undefined'
      && updateRequiredContext && INTERMediatorOnPage.doAfterPartialConstruct) {
      INTERMediatorOnPage.doAfterPartialConstruct(updateRequiredContext)
    }
    if (INTERMediatorOnPage.doAfterConstruct) {
      INTERMediatorOnPage.doAfterConstruct()
    }
    INTERMediatorOnPage.isFinishToConstruct = false
    INTERMediator.partialConstructing = true
    INTERMediatorOnPage.hideProgress()
    INTERMediatorLog.flushMessage() // Show messages

    /* --------------------------------------------------------------------
     This function is called on case of below.

     [1] INTERMediator.constructMain() or INTERMediator.constructMain(true)
     */
    async function pageConstruct() {
      IMLibCalc.calculateRequiredObject = {}
      INTERMediator.currentEncNumber = 1
      INTERMediator.elementIds = []

      // Restoring original HTML Document from backup data.
      const bodyNode = document.getElementsByTagName('BODY')[0]
      if (INTERMediator.rootEnclosure === null) {
        INTERMediator.rootEnclosure = bodyNode.innerHTML
      } else {
        bodyNode.innerHTML = INTERMediator.rootEnclosure
      }
      INTERMediator.localizing()
      postSetFields = []
      INTERMediatorOnPage.setReferenceToTheme()
      IMLibPageNavigation.initializeStepInfo(false)

      IMLibLocalContext.bindingDescendant(document.documentElement)

      try {
        await seekEnclosureNode(bodyNode, null, null, null)
      } catch (ex) {
        if (ex.message === '_im_auth_required_') {
          throw ex
        } else {
          INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-9')
        }
      }

      // After work to set up popup menus.
      for (let i = 0; i < postSetFields.length; i++) {
        const node = document.getElementById(postSetFields[i].id)
        if (postSetFields[i].value === '' && node && node.tagName === 'SELECT') {
          // for compatibility with Firefox when the value of select tag is empty.
          const emptyElement = document.createElement('option')
          emptyElement.setAttribute('id', INTERMediator.nextIdValue())
          emptyElement.setAttribute('value', '')
          emptyElement.setAttribute('data-im-element', 'auto-generated')
          document.getElementById(postSetFields[i].id).insertBefore(
            emptyElement, document.getElementById(postSetFields[i].id).firstChild)
        }
        if (node) {
          node.value = postSetFields[i].value
        }
      }
      IMLibCalc.updateCalculationFields()
      IMLibPageNavigation.navigationSetup()
      IMLibLocalContext.archive()
      appendCredit()
    }

    /** --------------------------------------------------------------------
     * Seeking nodes and if a node is an enclosure, proceed repeating.
     */

    async function seekEnclosureNode(node, currentRecord, parentObjectInfo, currentContextObj) {
      if (node.nodeType === 1) { // Work for an element
        try {
          if (INTERMediatorLib.isEnclosure(node, false)) { // Linked element and an enclosure
            const className = node.getAttribute('class')
            const attr = node.getAttribute('data-im-control')
            if ((className && className.match(/_im_post/)) ||
              (attr && attr.indexOf('post') >= 0)) {
              setupPostOnlyEnclosure(node)
            } else {
              await expandEnclosure(node, currentRecord, parentObjectInfo, currentContextObj)
            }
          } else {
            const children = node.childNodes // Check all child nodes.
            if (children) {
              for (let i = 0; i < children.length; i++) {
                if (children[i].nodeType === 1) {
                  await seekEnclosureNode(children[i], currentRecord, parentObjectInfo, currentContextObj)
                }
              }
            }
          }
        } catch (ex) {
          if (ex.message === '_im_auth_required_') {
            throw ex
          } else {
            INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-10')
          }
        }
      }
    }

    /* --------------------------------------------------------------------
     Post only mode.
     */
    function setupPostOnlyEnclosure(node) {
      const postNodes = INTERMediatorLib.getElementsByClassNameOrDataAttr(node, '_im_post')
      for (let i = 0; i < postNodes.length; i++) {
        if (postNodes[i].tagName === 'BUTTON' ||
          (postNodes[i].tagName === 'INPUT' &&
            (postNodes[i].getAttribute('type').toLowerCase() === 'button' ||
              postNodes[i].getAttribute('type').toLowerCase() === 'submit'))) {
          if (!postNodes[i].id) {
            postNodes[i].id = INTERMediator.nextIdValue()
          }
          IMLibMouseEventDispatch.setExecute(postNodes[i].id,
            (function () {
              let targetNode = postNodes[i]
              return function () {
                IMLibUI.clickPostOnlyButton(targetNode)
              }
            })())
        }
      }
      const nodes = node.childNodes
      for (let i = 0; i < nodes.length; i++) {
        seekEnclosureInPostOnly(nodes[i])
      }

      // -------------------------------------------
      async function seekEnclosureInPostOnly(node) {
        if (node.nodeType === 1) { // Work for an element
          try {
            const target = node.getAttribute('data-im')
            if (target) { // Linked element
              if (!node.id) {
                node.id = 'IMPOST-' + INTERMediator.postOnlyNumber
                INTERMediator.postOnlyNumber++
              }
              INTERMediatorLib.addEvent(node, 'blur', function () {
                let idValue = node.id
                IMLibUI.valueChange(idValue, true)
              })
              if (node.tagName === 'INPUT' && node.getAttribute('type') === 'radio') {
                if (!radioName[target]) {
                  radioName[target] = 'Name-' + nameSerial
                  nameSerial++
                }
                node.setAttribute('name', radioName[target])
              }
            }

            if (INTERMediatorLib.isWidgetElement(node)) {
              const wInfo = INTERMediatorLib.getWidgetInfo(node)
              if (wInfo[0]) {
                IMParts_Catalog[wInfo[0]].instanciate(node)
                if (imPartsShouldFinished.indexOf(IMParts_Catalog[wInfo[0]]) < 0) {
                  imPartsShouldFinished.push(IMParts_Catalog[wInfo[0]])
                }
              }
            } else if (INTERMediatorLib.isEnclosure(node, false)) { // Linked element and an enclosure
              await expandEnclosure(node, null, null, null)
            } else {
              const children = node.childNodes // Check all child nodes.
              for (let i = 0; i < children.length; i++) {
                seekEnclosureInPostOnly(children[i])
              }
            }
          } catch (ex) {
            if (ex.message === '_im_auth_required_') {
              throw ex
            } else {
              INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-11')
            }
          }
        }
      }
    }

    /** --------------------------------------------------------------------
     * Expanding an enclosure.
     */

    async function expandEnclosure(node, currentRecord, parentObjectInfo, currentContextObj) {
      const imControl = node.getAttribute('data-im-control')
      if (currentContextObj &&
        currentContextObj.contextName &&
        currentRecord &&
        currentRecord[currentContextObj.contextName] &&
        currentRecord[currentContextObj.contextName][currentContextObj.contextName + '::' + INTERMediatorOnPage.defaultKeyName]) {
        // for FileMaker portal access mode
        const recId = currentRecord[currentContextObj.contextName][currentContextObj.contextName + '::' + INTERMediatorOnPage.defaultKeyName]
        currentRecord = currentRecord[currentContextObj.contextName][recId]
      }
      if (imControl && imControl.match(/cross-table/)) { // Cross Table
        await expandCrossTableEnclosure(node, parentObjectInfo, currentContextObj)
      } else { // Enclosure Processing as usual.
        const repNodeTag = INTERMediatorLib.repeaterTagFromEncTag(node.tagName)
        const repeatersOriginal = collectRepeatersOriginal(node, repNodeTag) // Collecting repeaters to this array.
        await enclosureProcessing(node, repeatersOriginal, currentRecord, parentObjectInfo, currentContextObj)
      }
      IMLibLocalContext.bindingDescendant(node)

      /** --------------------------------------------------------------------
       * Expanding enclosure as usual (means not 'cross tabole').
       */
      async function enclosureProcessing(enclosureNode, repeatersOriginal, currentRecord, parentObjectInfo,
                                         currentContextObj, procBeforeRetrieve, customExpandRepeater) {
        let selectedNode = null, keyValue = null, targetRecords, contextObj = null, tempObj = {}

        try {
          const repeaters = collectRepeaters(repeatersOriginal) // Collecting repeaters to this array.
          let linkedNodes = INTERMediatorLib.seekLinkedAndWidgetNodes(repeaters, true).linkedNode
          const linkDefs = collectLinkDefinitions(linkedNodes)
          const voteResult = tableVoting(linkDefs)
          const currentContextDef = voteResult.targettable
          INTERMediator.currentEncNumber++

          if (!enclosureNode.getAttribute('id')) {
            enclosureNode.setAttribute('id', INTERMediator.nextIdValue())
          }

          if (!currentContextDef) {
            for (let i = 0; i < repeatersOriginal.length; i++) {
              const newNode = enclosureNode.appendChild(repeatersOriginal[i])

              // for compatibility with Firefox
              if (repeatersOriginal[i].getAttribute('selected')) {
                selectedNode = newNode
              }
              if (selectedNode) {
                selectedNode.selected = true
              }
              await seekEnclosureNode(newNode, null, enclosureNode, currentContextObj)
            }
          } else {
            const isExpanding = !IMLibPageNavigation.isNotExpandingContext(currentContextDef)
            contextObj = IMLibContextPool.generateContextObject(
              currentContextDef, enclosureNode, repeaters, repeatersOriginal)
            const calcFields = contextObj.getCalculationFields()
            const fieldList = voteResult.fieldlist.map(function (elm) {
              if (!calcFields[elm]) {
                calcFields.push(elm)
              }
              return elm
            })

            if (isPortalAccessMode(currentContextDef)) {
              contextObj.isPortal = true
              if (!currentRecord) {
                tempObj = IMLibContextPool.generateContextObject(
                  {'name': contextObj.sourceName}, enclosureNode, repeaters, repeatersOriginal)
                if (typeof targetRecords === 'undefined') {
                  targetRecords = retrieveDataForEnclosure(tempObj, fieldList, contextObj.foreignValue)
                }
                // const recId = targetRecords.recordset[0][INTERMediatorOnPage.defaultKeyName]
                currentRecord = targetRecords.recordset[0]
              }
            }

            contextObj.setRelationWithParent(currentRecord, parentObjectInfo, currentContextObj)
            if (contextObj.isPortal === true) {
              if (currentRecord) {
                currentContextDef.currentrecord = currentRecord
                keyValue = currentRecord[currentContextDef.relation[0]['join-field']]
              }
            }

            if (procBeforeRetrieve) {
              procBeforeRetrieve(contextObj)
            }
            if (isExpanding) {
              targetRecords = await retrieveDataForEnclosure(contextObj, fieldList, contextObj.foreignValue)
            } else {
              targetRecords = []
              if (enclosureNode.tagName === 'TBODY') {
                enclosureNode.parentNode.style.display = 'none'
              } else {
                enclosureNode.style.display = 'none'
              }
            }
            contextObj.storeRecords(targetRecords)

            callbackForAfterQueryStored(currentContextDef, contextObj)
            if (typeof customExpandRepeater === 'undefined') {
              contextObj.registeredId = targetRecords.registeredId
              contextObj.nullAcceptable = targetRecords.nullAcceptable
              await expandRepeaters(contextObj, enclosureNode, targetRecords)
              IMLibPageNavigation.setupInsertButton(contextObj, keyValue, enclosureNode, contextObj.foreignValue)
              IMLibPageNavigation.setupBackNaviButton(contextObj, enclosureNode)
              callbackForEnclosure(currentContextDef, enclosureNode)
            } else {
              customExpandRepeater(contextObj, targetRecords)
            }
            contextObj.sequencing = false

            if (enclosureNode.tagName === 'TBODY') {
              const footerNodes = enclosureNode.parentNode.getElementsByTagName('TFOOT')
              linkedNodes = seekWithAttribute(footerNodes[0], 'data-im')
              if (linkedNodes) {
                INTERMediator.setIdValue(footerNodes[0])
                const targetRecordset = {}
                const keyingValue = '_im_footer'
                for (let i = 0; i < linkedNodes.length; i++) {
                  const nInfo = INTERMediatorLib.getNodeInfoArray(INTERMediatorLib.getLinkedElementInfo(linkedNodes[i])[0])
                  if (linkedNodes[i] && currentContextDef.name === nInfo.table) {
                    INTERMediator.setIdValue(linkedNodes[i])
                  }
                  IMLibCalc.updateCalculationInfo(contextObj, keyingValue, linkedNodes[i].id, nInfo, targetRecordset)
                  if (contextObj.binding._im_footer) {
                    contextObj.binding._im_footer._im_repeater = footerNodes
                  }
                }
              }
              const headerNodes = enclosureNode.parentNode.getElementsByTagName('THEAD')
              linkedNodes = seekWithAttribute(headerNodes[0], 'data-im')
              if (linkedNodes) {
                INTERMediator.setIdValue(headerNodes[0])
                const targetRecordset = {}
                const keyingValue = '_im_header'
                for (let i = 0; i < linkedNodes.length; i++) {
                  const nInfo = INTERMediatorLib.getNodeInfoArray(INTERMediatorLib.getLinkedElementInfo(linkedNodes[i])[0])
                  if (linkedNodes[i] && currentContextDef.name === nInfo.table) {
                    INTERMediator.setIdValue(linkedNodes[i])
                  }
                  IMLibCalc.updateCalculationInfo(contextObj, keyingValue, linkedNodes[i].id, nInfo, targetRecordset)
                  if (contextObj.binding._im_header) {
                    contextObj.binding._im_header._im_repeater = headerNodes
                  }
                }
              }
            }
            contextObj.captureCurrentStore()
          }
          return contextObj
        } catch (ex) {
          throw ex
        }

        function seekWithAttribute(node, attrName) {
          if (!node || node.nodeType !== 1) {
            return null
          }
          let result = seekWithAttributeImpl(node, attrName)
          return result
        }

        function seekWithAttributeImpl(node, attrName) {
          let result = []
          if (node && node.nodeType === 1) {
            if (node.getAttribute(attrName)) {
              result.push(node)
            }
            if (node.childNodes) {
              for (let ix = 0; ix < node.childNodes.length; ix++) {
                const adding = seekWithAttributeImpl(node.childNodes[ix], attrName)
                if (adding.length > 0) {
                  [].push.apply(result, adding)
                }
              }
            }
          }
          return result
        }
      }

      /** --------------------------------------------------------------------
       * expanding enclosure for cross table
       */
      async function expandCrossTableEnclosure(node, parentObjectInfo, currentContextObj) {
        // Collecting 4 parts of cross table.
        let ctComponentNodes = crossTableComponents(node)
        if (ctComponentNodes.length !== 4) {
          throw new Error('Exception-xx: Cross Table Components aren\'t prepared.')
        }
        // Remove all nodes under the TBODY tagged node.
        while (node.childNodes.length > 0) {
          node.removeChild(node.childNodes[0])
        }

        // Decide the context for cross point cell
        const repeaters = collectRepeaters([ctComponentNodes[3].cloneNode(true)])
        const linkedNodes = INTERMediatorLib.seekLinkedAndWidgetNodes(repeaters, true).linkedNode
        const linkDefs = collectLinkDefinitions(linkedNodes)
        const crossCellContext = tableVoting(linkDefs).targettable
        const labelKeyColumn = crossCellContext.relation[0]['join-field']
        const labelKeyRow = crossCellContext.relation[1]['join-field']

        // Create the first row
        INTERMediator.crossTableStage = 1
        let lineNode = document.createElement('TR')
        let targetRepeater = ctComponentNodes[0].cloneNode(true)
        lineNode.appendChild(targetRepeater)
        node.appendChild(lineNode)

        // Append the column context in the first row
        targetRepeater = ctComponentNodes[1].cloneNode(true)
        const colContext = await enclosureProcessing(
          lineNode, [targetRepeater], null, parentObjectInfo, currentContextObj)
        const colArray = colContext.indexingArray(labelKeyColumn)

        // Create second and following rows, and the first columns are appended row context
        INTERMediator.crossTableStage = 2
        targetRepeater = ctComponentNodes[2].cloneNode(true)
        lineNode = document.createElement('TR')
        lineNode.appendChild(targetRepeater)
        const rowContext = await enclosureProcessing(
          node, [lineNode], null, parentObjectInfo, currentContextObj)
        const rowArray = rowContext.indexingArray(labelKeyRow)

        // Create all cross point cell
        INTERMediator.crossTableStage = 3
        targetRepeater = ctComponentNodes[3].cloneNode(true)
        const nodeForKeyValues = {}
        const trNodes = node.getElementsByTagName('TR')
        if (!trNodes || trNodes.length == 0 || colArray.length == 0) {
          const tableNode = node.parentNode
          tableNode.parentNode.removeChild(tableNode)
          return
        }
        for (let i = 1; i < trNodes.length; i += 1) {
          for (let j = 0; j < colArray.length; j += 1) {
            const appendingNode = targetRepeater.cloneNode(true)
            trNodes[i].appendChild(appendingNode)
            INTERMediator.setIdValue(appendingNode)
            if (!nodeForKeyValues[colArray[j]]) {
              nodeForKeyValues[colArray[j]] = {}
            }
            nodeForKeyValues[colArray[j]][rowArray[i - 1]] = appendingNode
          }
        }
        INTERMediator.setIdValue(node)
        let expandContextObj = null
        await enclosureProcessing(
          node, [targetRepeater], null, parentObjectInfo, currentContextObj,
          function (context) {
            let currentContextDef = context.getContextDef()
            INTERMediator.clearCondition(currentContextDef.name, '_imlabel_crosstable')
            INTERMediator.addCondition(currentContextDef.name, {
              field: currentContextDef.relation[0]['foreign-key'],
              operator: 'IN',
              value: colArray,
              onetime: true
            }, undefined, '_imlabel_crosstable')
            INTERMediator.addCondition(currentContextDef.name, {
              field: currentContextDef.relation[1]['foreign-key'],
              operator: 'IN',
              value: rowArray,
              onetime: true
            }, undefined, '_imlabel_crosstable')
          },
          function (contextObj, targetRecords) {
            expandContextObj = contextObj
            let dataKeyColumn, dataKeyRow, currentContextDef,
              linkedElements, targetNode, keyField, keyValue, keyingValue
            currentContextDef = contextObj.getContextDef()
            keyField = contextObj.getKeyField()
            dataKeyColumn = currentContextDef.relation[0]['foreign-key']
            dataKeyRow = currentContextDef.relation[1]['foreign-key']
            if (targetRecords.dbresult) {
              for (let ix = 0; ix < targetRecords.dbresult.length; ix++) { // for each record
                const record = targetRecords.dbresult[ix]
                if (nodeForKeyValues[record[dataKeyColumn]] &&
                  nodeForKeyValues[record[dataKeyColumn]][record[dataKeyRow]]) {
                  targetNode = nodeForKeyValues[record[dataKeyColumn]][record[dataKeyRow]]
                  if (targetNode) {
                    keyValue = record[keyField]
                    if (keyField && !keyValue && keyValue !== 0) {
                      keyValue = ix
                    }
                    keyingValue = keyField + '=' + keyValue
                  }
                  setupLinkedNode([targetNode], contextObj, targetRecords.dbresult, ix, keyingValue)
                }
              }
            }
          }
        )
        if (node.getAttribute("data-im-control").match(/cross-table-sum/)) {
          const lineNodes = node.getElementsByTagName("TR")
          const colSum = {}
          let rowSum = {id: -1}
          let colCount = 0
          for (let n = 0; n < lineNodes.length; n += 1) {
            const cellNodes = lineNodes[n].getElementsByTagName("TD")
            colCount = cellNodes.length
            rowSum = {id: -1}
            for (let m = 0; m < colCount; m += 1) {
              if (!colSum[m]) {
                colSum[m] = {id: -1}
              }
              const targetNodes = INTERMediatorLib.seekLinkedAndWidgetNodes([cellNodes[m]], false).linkedNode
              for (let p = 0; p < targetNodes.length; p += 1) {
                const target = targetNodes[p].getAttribute("data-im").split("@")[1]
                const value = parseFloat(targetNodes[p].value ? targetNodes[p].value : targetNodes[p].innerHTML)
                if (value) {
                  if (!rowSum[target]) {
                    rowSum[target] = value
                  } else {
                    rowSum[target] += value
                  }
                  if (!colSum[m][target]) {
                    colSum[m][target] = value
                  } else {
                    colSum[m][target] += value
                  }
                }
              }
            }
            const sumCell = ctComponentNodes[(n == 0) ? 1 : 3].cloneNode(true)
            lineNodes[n].appendChild(sumCell)
            sumCell.className = (sumCell.className ? (sumCell.className + ' ') : '') + '_im_cross_summary'
            if (n == 0) {
              sumCell.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[1052]))
            } else {
              setupLinkedNode([sumCell], expandContextObj, [rowSum], 0, "id=-1")
            }
          }
          const lastLine = lineNodes[1].cloneNode(false)
          node.appendChild(lastLine)
          let sumCell = ctComponentNodes[2].cloneNode(true)
          lastLine.appendChild(sumCell)
          sumCell.className = (sumCell.className ? (sumCell.className + ' ') : '') + '_im_cross_summary'
          sumCell.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[1052]))
          rowSum = {id: -1}
          for (let m = 0; m < colCount; m += 1) {
            sumCell = ctComponentNodes[3].cloneNode(true)
            lastLine.appendChild(sumCell)
            sumCell.className = (sumCell.className ? (sumCell.className + ' ') : '') + '_im_cross_summary'
            setupLinkedNode([sumCell], expandContextObj, [colSum[m]], 0, "id=-1")
            const targetNodes = INTERMediatorLib.seekLinkedAndWidgetNodes([sumCell], false).linkedNode
            for (let p = 0; p < targetNodes.length; p += 1) {
              const target = targetNodes[p].getAttribute("data-im").split("@")[1]
              const value = parseFloat(targetNodes[p].value ? targetNodes[p].value : targetNodes[p].innerHTML)
              if (value) {
                if (!rowSum[target]) {
                  rowSum[target] = value
                } else {
                  rowSum[target] += value
                }
              }
            }
          }
          sumCell = ctComponentNodes[3].cloneNode(true)
          lastLine.appendChild(sumCell)
          sumCell.className = (sumCell.className ? (sumCell.className + ' ') : '') + '_im_cross_summary'
          setupLinkedNode([sumCell], expandContextObj, [rowSum], 0, "id=-1")
        }
      } // The end of function expandCrossTableEnclosure().

      // Detect cross table components in a tbody enclosure.
      function crossTableComponents(node) {
        let components = []
        let count = 0
        repeatCTComponents(node.childNodes)
        return components

        function repeatCTComponents(nodes) {
          for (let i = 0; i < nodes.length; i++) {
            if (nodes[i].nodeType === 1 && (nodes[i].tagName === 'TH' || nodes[i].tagName === 'TD')) {
              components[count] = nodes[i]
              count += 1
            } else {
              const childNodes = nodes[i].childNodes
              if (childNodes) {
                repeatCTComponents(childNodes)
              }
            }
          }
        }
      }
    }

    /** --------------------------------------------------------------------
     * Set the value to node and context.
     */
    function setupLinkedNode(nodes, contextObj, targetRecordset, ix, keyingValue) {
      if (targetRecordset.length < 1 || targetRecordset[0] === null) {
        return null;
      }
      let idValuesForFieldName = {}
      const currentContextDef = contextObj.getContextDef()
      const linkedElements = INTERMediatorLib.seekLinkedAndWidgetNodes(nodes, INTERMediator.crossTableStage == 0)
      const currentWidgetNodes = linkedElements.widgetNode
      const currentLinkedNodes = linkedElements.linkedNode
      try {
        const keyField = contextObj.getKeyField()
        if (targetRecordset[ix] && (targetRecordset[ix][keyField] || targetRecordset[ix][keyField] === 0)) {
          for (let k = 0; k < currentLinkedNodes.length; k++) {
            // for each linked element
            const nodeId = currentLinkedNodes[k].getAttribute('id')
            const replacedNode = INTERMediator.setIdValue(currentLinkedNodes[k])
            contextObj.setupLookup(currentLinkedNodes[k], targetRecordset[ix][keyField])
            const typeAttr = replacedNode.getAttribute('type')
            if (typeAttr === 'checkbox' || typeAttr === 'radio') {
              const children = replacedNode.parentNode.childNodes
              for (let i = 0; i < children.length; i++) {
                if (children[i].nodeType === 1 && children[i].tagName === 'LABEL' &&
                  nodeId === children[i].getAttribute('for')) {
                  children[i].setAttribute('for', replacedNode.getAttribute('id'))
                  break
                }
              }
            }
          }
          for (let k = 0; k < currentWidgetNodes.length; k++) {
            const wInfo = INTERMediatorLib.getWidgetInfo(currentWidgetNodes[k])
            if (wInfo[0]) {
              IMParts_Catalog[wInfo[0]].instanciate(currentWidgetNodes[k])
              if (imPartsShouldFinished.indexOf(IMParts_Catalog[wInfo[0]]) < 0) {
                imPartsShouldFinished.push(IMParts_Catalog[wInfo[0]])
              }
            }
          }
        }
      } catch (ex) {
        if (ex.message === '_im_auth_required_') {
          throw ex
        } else {
          INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-101')
        }
      }

      const nameTable = {}
      for (let k = 0; k < currentLinkedNodes.length; k++) {
        try {
          let nodeId = currentLinkedNodes[k].getAttribute('id')
          if (INTERMediatorLib.isWidgetElement(currentLinkedNodes[k])) {
            nodeId = currentLinkedNodes[k]._im_getComponentId()
            // INTERMediator.widgetElementIds.push(nodeId)
          }
          // get the tag name of the element
          const typeAttr = currentLinkedNodes[k].getAttribute('type')// type attribute
          const linkInfoArray = INTERMediatorLib.getLinkedElementInfo(currentLinkedNodes[k])
          // info array for it  set the name attribute of radio button
          // should be different for each group
          if (typeAttr === 'radio') { // set the value to radio button
            const nameTableKey = linkInfoArray.join('|')
            if (!nameTable[nameTableKey]) {
              nameTable[nameTableKey] = nameAttrCounter
              nameAttrCounter++
            }
            const nameNumber = nameTable[nameTableKey]
            const nameAttr = currentLinkedNodes[k].getAttribute('name')
            if (nameAttr) {
              currentLinkedNodes[k].setAttribute('name', nameAttr + '-' + nameNumber)
            } else {
              currentLinkedNodes[k].setAttribute('name', 'IM-R-' + nameNumber)
            }
          }
          for (let j = 0; j < linkInfoArray.length; j++) {
            const nInfo = INTERMediatorLib.getNodeInfoArray(linkInfoArray[j])
            const curVal = targetRecordset[ix][nInfo.field]
            if (!INTERMediator.isDBDataPreferable || curVal) {
              IMLibCalc.updateCalculationInfo(contextObj, keyingValue, nodeId, nInfo, targetRecordset[ix])
            }
            if (nInfo.table === currentContextDef.name) {
              const curTarget = nInfo.target
              if (IMLibElement.setValueToIMNode(currentLinkedNodes[k], curTarget, curVal)) {
                postSetFields.push({'id': nodeId, 'value': curVal})
              }
              contextObj.setValue(keyingValue, nInfo.field, curVal, nodeId, curTarget)
              if (typeof (idValuesForFieldName[nInfo.field]) === 'undefined') {
                idValuesForFieldName[nInfo.field] = []
              }
              idValuesForFieldName[nInfo.field].push(nodeId)
            }
          }
        } catch (ex) {
          if (ex.message === '_im_auth_required_') {
            throw ex
          } else {
            INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-27')
          }
        }
      }
      return idValuesForFieldName
    }

    /** --------------------------------------------------------------------
     * Expanding an repeater.
     */
    async function expandRepeaters(contextObj, node, targetRecords) {
      const encNodeTag = node.tagName
      const repNodeTag = INTERMediatorLib.repeaterTagFromEncTag(encNodeTag)

      const repeatersOriginal = contextObj.original
      const currentContextDef = contextObj.getContextDef()
      const targetTotalCount = targetRecords.totalCount

      let targetRecordset = currentContextDef.data ? targetRecords.recordset : targetRecords.dbresult
      if (isPortalAccessMode(currentContextDef)) {
        targetRecordset = targetRecords.recordset
      }

      let repeatersOneRec = cloneEveryNodes(repeatersOriginal)
      if (!INTERMediatorOnPage.notShowHeaderFooterOnNoResult || targetRecords.count !== 0) {
        for (let i = 0; i < repeatersOneRec.length; i++) {
          const newNode = repeatersOneRec[i]
          const dataAttr = newNode.getAttribute('data-im-control')
          if (dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsHeaderDataControlName) >= 0) {
            node.appendChild(newNode)
          }
        }
      }
      if (targetRecords.count === 0) {
        for (let i = 0; i < repeatersOriginal.length; i++) {
          const newNode = repeatersOriginal[i].cloneNode(true)
          const nodeClass = newNode.getAttribute('class')
          const dataAttr = newNode.getAttribute('data-im-control')
          if ((nodeClass && nodeClass.indexOf(INTERMediator.noRecordClassName) > -1) ||
            (dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsNoResultDataControlName) > -1)) {
            node.appendChild(newNode)
            INTERMediator.setIdValue(newNode)
          }
        }
      }

      const countRecord = targetRecordset ? targetRecordset.length : 0
      let keyValue = null
      let keyingValue = null
      for (let ix = 0; ix < countRecord; ix++) { // for each record
        repeatersOneRec = cloneEveryNodes(repeatersOriginal)
        const keyField = contextObj.getKeyField()
        for (let i = 0; i < repeatersOneRec.length; i++) {
          INTERMediator.setIdValue(repeatersOneRec[i])
        }
        if (targetRecordset[ix] && (targetRecordset[ix][keyField] || targetRecordset[ix][keyField] === 0)) {
          keyValue = targetRecordset[ix][keyField]
          if (keyField && !keyValue && keyValue !== 0) {
            INTERMediatorLog.setErrorMessage('The value of the key field is null.',
              'This No.[' + ix + '] record should be ignored.')
            keyValue = ix
          }
          keyingValue = keyField + '=' + keyValue
        }
        let idValuesForFieldName = setupLinkedNode(repeatersOneRec, contextObj, targetRecordset, ix, keyingValue)
        contextObj.setValue(keyingValue, "_im_seq", ix + 1);
        contextObj.setValue(keyingValue, "_im_count", countRecord);
        IMLibPageNavigation.setupDeleteButton(encNodeTag, repeatersOneRec, contextObj, keyField, keyValue)
        IMLibPageNavigation.setupNavigationButton(encNodeTag, repeatersOneRec, currentContextDef, keyField, keyValue, contextObj)
        IMLibPageNavigation.setupCopyButton(encNodeTag, repNodeTag, repeatersOneRec, contextObj, targetRecordset[ix])

        if (!currentContextDef.portal || (!!currentContextDef.portal && targetTotalCount > 0)) {
          const newlyAddedNodes = []
          let insertNode = null
          if (!contextObj.sequencing) {
            const indexContext = contextObj.checkOrder(targetRecordset[ix])
            insertNode = contextObj.getRepeaterEndNode(indexContext + 1)
          }
          for (let i = 0; i < repeatersOneRec.length; i++) {
            const newNode = repeatersOneRec[i]
            const nodeClass = newNode.getAttribute('class')
            const dataAttr = newNode.getAttribute('data-im-control')
            if (!(nodeClass && nodeClass.indexOf(INTERMediator.noRecordClassName) >= 0) &&
              !(dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsNoResultDataControlName) >= 0) &&
              !(dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsSeparatorDataControlName) >= 0) &&
              !(dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsFooterDataControlName) >= 0) &&
              !(dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsHeaderDataControlName) >= 0)
            ) {
              if (!insertNode) {
                node.appendChild(newNode)
              } else {
                insertNode.parentNode.insertBefore(newNode, insertNode)
              }
              newlyAddedNodes.push(newNode)
              if (!newNode.id) {
                INTERMediator.setIdValue(newNode)
              }
              contextObj.setValue(keyingValue, '_im_repeater', newNode, newNode.id, '', currentContextDef.portal)
              await seekEnclosureNode(newNode, targetRecordset[ix], idValuesForFieldName, contextObj)
            }
          }
          if ((ix + 1) !== countRecord) {
            for (let i = 0; i < repeatersOneRec.length; i++) {
              const newNode = repeatersOneRec[i]
              const dataAttr = newNode.getAttribute('data-im-control')
              if (dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsSeparatorDataControlName) >= 0) {
                if (!insertNode) {
                  node.appendChild(newNode)
                } else {
                  insertNode.parentNode.insertBefore(newNode, insertNode)
                }
              }
            }
          }
          callbackForRepeaters(currentContextDef, node, newlyAddedNodes)
        }
        contextObj.rearrangePendingOrder()
      }

      IMLibPageNavigation.setupDetailAreaToFirstRecord(currentContextDef, contextObj)

      repeatersOneRec = cloneEveryNodes(repeatersOriginal)
      if (!INTERMediatorOnPage.notShowHeaderFooterOnNoResult || targetRecords.count !== 0) {
        for (let i = 0; i < repeatersOneRec.length; i++) {
          const newNode = repeatersOneRec[i]
          const dataAttr = newNode.getAttribute('data-im-control')
          if (dataAttr && dataAttr.indexOf(INTERMediatorLib.roleAsFooterDataControlName) >= 0) {
            node.appendChild(newNode)
          }
        }
      }
    }

    /* --------------------------------------------------------------------

     */
    async function retrieveDataForEnclosure(contextObj, fieldList, relationValue) {
      let targetRecords = {}
      let recordset = []

      if (Boolean(contextObj.contextDefinition.cache) === true) {
        await retrieveDataFromCache(contextObj.contextDefinition, relationValue,
          function (result) {
            targetRecords.dbresult = result.recordset
            targetRecords.count = result.count
          })
      } else if (contextObj.contextDefinition.data) {
        for (const key in contextObj.contextDefinition.data) {
          if (contextObj.contextDefinition.data.hasOwnProperty(key)) {
            recordset.push(contextObj.contextDefinition.data[key])
          }
        }
        targetRecords = {
          'recordset': recordset,
          'count': recordset.length,
          'totalCount': recordset.length,
          'nullAcceptable': true
        }
      } else { // cache is not active.
        try {
          targetRecords = contextObj.getPortalRecords()
          if (!targetRecords) {
            const useLimit = contextObj.isUseLimit()
            const recordNumber = contextObj.getRecordNumber()
            await INTERMediator_DBAdapter.db_query_async(
              {
                'name': contextObj.contextDefinition.name,
                'records': isNaN(recordNumber) ? 100000000 : recordNumber,
                'paging': contextObj.contextDefinition.paging,
                'fields': fieldList,
                'parentkeyvalue': relationValue,
                'conditions': null,
                'useoffset': true,
                'uselimit': useLimit
              },
              function (result) {
                targetRecords = result
              },
              null)
          }
        } catch (ex) {
          if (ex.message === '_im_auth_required_') {
            throw ex
          } else {
            INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-12')
          }
        }
      }
      if (contextObj.contextDefinition['appending-data']) {
        for (const key in contextObj.contextDefinition['appending-data']) {
          if (contextObj.contextDefinition['appending-data'].hasOwnProperty(key)) {
            targetRecords.dbresult.push(contextObj.contextDefinition['appending-data'][key])
          }
        }
      }
      return targetRecords
    }

    /* --------------------------------------------------------------------
     This implementation for cache is quite limited.
     */
    async function retrieveDataFromCache(currentContextDef, relationValue, completion) {
      let targetRecords = null
      try {
        if (!INTERMediatorOnPage.dbCache[currentContextDef.name]) {
          await INTERMediator_DBAdapter.db_query_async(
            {
              name: currentContextDef.name,
              records: null,
              paging: null,
              fields: null,
              parentkeyvalue: null,
              conditions: null,
              useoffset: false
            },
            (result) => {
              INTERMediatorOnPage.dbCache[currentContextDef.name] = result
              completion(result)
            },
            null
          )
        }
        if (relationValue === null) {
          targetRecords = INTERMediatorOnPage.dbCache[currentContextDef.name]
        } else {
          targetRecords = {recordset: [], count: 0}
          let counter = 0
          for (let ix in INTERMediatorOnPage.dbCache[currentContextDef.name].dbresult) {
            if (INTERMediatorOnPage.dbCache[currentContextDef.name].dbresult.hasOwnProperty(ix)) {
              const oneRecord = INTERMediatorOnPage.dbCache[currentContextDef.name].dbresult[ix]
              let isMatch = true
              let index = 0
              for (const keyField in relationValue) {
                if (relationValue.hasOwnProperty(keyField)) {
                  const fieldName = currentContextDef.relation[index]['foreign-key']
                  if (oneRecord[fieldName] !== relationValue[keyField]) {
                    isMatch = false
                    break
                  }
                  index++
                }
              }
              if (isMatch) {
                const pagingValue = currentContextDef.paging ? currentContextDef.paging : false
                const recordsValue = currentContextDef.records ? currentContextDef.records : 10000000000

                if (!pagingValue || (pagingValue && (counter >= INTERMediator.startFrom))) {
                  targetRecords.recordset.push(oneRecord)
                  targetRecords.count++
                  if (recordsValue <= targetRecords.count) {
                    break
                  }
                }
                counter++
              }
            }
          }
          completion(targetRecords)
        }
      } catch (ex) {
        if (ex.message === '_im_auth_required_') {
          throw ex
        } else {
          INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-24')
        }
      }
    }

    /* --------------------------------------------------------------------

     */
    function callbackForRepeaters(currentContextDef, node, newlyAddedNodes) {
      try {
        if (INTERMediatorOnPage.additionalExpandingRecordFinish[currentContextDef.name]) {
          INTERMediatorOnPage.additionalExpandingRecordFinish[currentContextDef.name](node)
          INTERMediatorLog.setDebugMessage(
            'Call the post enclosure method INTERMediatorOnPage.additionalExpandingRecordFinish[' +
            currentContextDef.name + '] with the context.', 2)
        }
      } catch (ex) {
        if (ex.message === '_im_auth_required_') {
          throw ex
        } else {
          INTERMediatorLog.setErrorMessage(ex,
            'EXCEPTION-33: hint: post-repeater of ' + currentContextDef.name)
        }
      }
      try {
        if (INTERMediatorOnPage.expandingRecordFinish) {
          INTERMediatorOnPage.expandingRecordFinish(currentContextDef.name, newlyAddedNodes)
          INTERMediatorLog.setDebugMessage(
            'Call INTERMediatorOnPage.expandingRecordFinish with the context: ' +
            currentContextDef.name, 2)
        }

        if (INTERMediatorOnPage[`postRepeater_${currentContextDef.name}`]) {
          INTERMediatorOnPage[`postRepeater_${currentContextDef.name}`](newlyAddedNodes)
          INTERMediatorLog.setDebugMessage('Call the post repeater method INTERMediatorOnPage.postRepeater_' +
            currentContextDef['name'] + ' with the context: ' + currentContextDef.name, 2)
        } else if (currentContextDef['post-repeater'] && INTERMediatorOnPage[currentContextDef['post-repeater']]) {
          INTERMediatorOnPage[currentContextDef['post-repeater']](newlyAddedNodes)
          INTERMediatorLog.setDebugMessage('Call the post repeater method INTERMediatorOnPage.' +
            currentContextDef['post-repeater'] + ' with the context: ' + currentContextDef.name, 2)
        }
      } catch (ex) {
        if (ex.message === '_im_auth_required_') {
          throw ex
        } else {
          INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-23')
        }
      }
    }

    /* --------------------------------------------------------------------

     */
    function callbackForEnclosure(currentContextDef, node) {
      try {
        if (INTERMediatorOnPage.additionalExpandingEnclosureFinish[currentContextDef.name]) {
          INTERMediatorOnPage.additionalExpandingEnclosureFinish[currentContextDef.name](node)
          INTERMediatorLog.setDebugMessage(
            'Call the post enclosure method INTERMediatorOnPage.additionalExpandingEnclosureFinish[' +
            currentContextDef.name + '] with the context.', 2)
        }
      } catch (ex) {
        if (ex.message === '_im_auth_required_') {
          throw ex
        } else {
          INTERMediatorLog.setErrorMessage(ex,
            'EXCEPTION-32: hint: post-enclosure of ' + currentContextDef.name)
        }
      }
      try {
        if (INTERMediatorOnPage.expandingEnclosureFinish) {
          INTERMediatorOnPage.expandingEnclosureFinish(currentContextDef.name, node)
          INTERMediatorLog.setDebugMessage(
            'Call INTERMediatorOnPage.expandingEnclosureFinish with the context: ' +
            currentContextDef.name, 2)
        }
      } catch (ex) {
        if (ex.message === '_im_auth_required_') {
          throw ex
        } else {
          INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-21')
        }
      }
      try {
        if (INTERMediatorOnPage[`postEnclosure_${currentContextDef.name}`]) {
          INTERMediatorOnPage[`postEnclosure_${currentContextDef.name}`](node)
          INTERMediatorLog.setDebugMessage(
            'Call the post enclosure method INTERMediatorOnPage.postEnclosure_' + currentContextDef.name +
            ' with the context: ' + currentContextDef.name, 2)
        } else if (currentContextDef['post-enclosure'] && INTERMediatorOnPage[currentContextDef['post-enclosure']]) {
          INTERMediatorOnPage[currentContextDef['post-enclosure']](node)
          INTERMediatorLog.setDebugMessage(
            'Call the post enclosure method INTERMediatorOnPage.' + currentContextDef['post-enclosure'] +
            ' with the context: ' + currentContextDef.name, 2)
        }
      } catch (ex) {
        if (ex.message === '_im_auth_required_') {
          throw ex
        } else {
          INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-22: hint: post-enclosure of ' + currentContextDef.name)
        }
      }
    }

    /* --------------------------------------------------------------------

     */
    function callbackForAfterQueryStored(currentContextDef, context) {
      try {
        if (currentContextDef['post-query-stored']) {
          INTERMediatorOnPage[currentContextDef['post-query-stored']](context)
          INTERMediatorLog.setDebugMessage(
            'Call the post query stored method INTERMediatorOnPage.' +
            currentContextDef['post-enclosure'] + ' with the context: ' + currentContextDef.name, 2)
        }
      } catch (ex) {
        if (ex.message === '_im_auth_required_') {
          throw ex
        } else {
          INTERMediatorLog.setErrorMessage(ex,
            'EXCEPTION-41: hint: post-query-stored of ' + currentContextDef.name)
        }
      }
    }

    /* --------------------------------------------------------------------

     */
    function collectRepeatersOriginal(node, repNodeTag) {
      const repeatersOriginal = []
      const children = node.childNodes // Check all child node of the enclosure.
      for (let i = 0; i < children.length; i++) {
        if (children[i].nodeType === 1) {
          if (children[i].tagName === repNodeTag) { // If the element is a repeater.
            repeatersOriginal.push(children[i]) // Record it to the array.
          } else if (!repNodeTag && (children[i].getAttribute('data-im-control'))) {
            const imControl = children[i].getAttribute('data-im-control')
            if (imControl.indexOf(INTERMediatorLib.roleAsRepeaterDataControlName) > -1 ||
              imControl.indexOf(INTERMediatorLib.roleAsSeparatorDataControlName) > -1 ||
              imControl.indexOf(INTERMediatorLib.roleAsFooterDataControlName) > -1 ||
              imControl.indexOf(INTERMediatorLib.roleAsHeaderDataControlName) > -1 ||
              imControl.indexOf(INTERMediatorLib.roleAsNoResultDataControlName) > -1
            ) {
              repeatersOriginal.push(children[i])
            }
          } else if (!repNodeTag && children[i].getAttribute('class') &&
            children[i].getAttribute('class').match(/_im_repeater/)) {
            const imControl = children[i].getAttribute('class')
            if (imControl.indexOf(INTERMediatorLib.roleAsRepeaterClassName) > -1) {
              repeatersOriginal.push(children[i])
            }
          }
        }
      }
      return repeatersOriginal
    }

    /* --------------------------------------------------------------------

     */
    function collectRepeaters(repeatersOriginal) {
      const repeaters = []
      for (let i = 0; i < repeatersOriginal.length; i++) {
        const inDocNode = repeatersOriginal[i]
        const parentOfRep = repeatersOriginal[i].parentNode
        const cloneNode = repeatersOriginal[i].cloneNode(true)
        repeaters.push(cloneNode)
        cloneNode.setAttribute('id', INTERMediator.nextIdValue())
        if (parentOfRep) {
          parentOfRep.removeChild(inDocNode)
        }
      }
      return repeaters
    }

    /* --------------------------------------------------------------------

     */
    function collectLinkDefinitions(linkedNodes) {
      const linkDefs = []
      for (let j = 0; j < linkedNodes.length; j++) {
        const nodeDefs = INTERMediatorLib.getLinkedElementInfo(linkedNodes[j])
        if (nodeDefs) {
          for (let k = 0; k < nodeDefs.length; k++) {
            linkDefs.push(nodeDefs[k])
          }
        }
      }
      return linkDefs
    }

    /* --------------------------------------------------------------------

     */
    function tableVoting(linkDefs) {
      let restDefs = []
      let tableVote = [] // Containing editable elements or not.
      let fieldList = [] // Create field list for database fetch.

      for (let j = 0; j < linkDefs.length; j++) {
        const nodeInfoArray = INTERMediatorLib.getNodeInfoArray(linkDefs[j])
        const nodeInfoField = nodeInfoArray.field
        const nodeInfoTable = nodeInfoArray.table
        const nodeInfoTableIndex = nodeInfoArray.tableindex // Table name added '_im_index_' as the prefix.
        if (nodeInfoTable !== IMLibLocalContext.contextName) {
          if (nodeInfoField && nodeInfoField.length !== 0 && nodeInfoTable && nodeInfoTable.length !== 0) {
            if (!fieldList[nodeInfoTableIndex]) {
              fieldList[nodeInfoTableIndex] = []
            }
            fieldList[nodeInfoTableIndex].push(nodeInfoField)
            if (!tableVote[nodeInfoTableIndex]) {
              tableVote[nodeInfoTableIndex] = 1
            } else {
              ++tableVote[nodeInfoTableIndex]
            }
          } else {
            INTERMediatorLog.setErrorMessage(
              INTERMediatorLib.getInsertedStringFromErrorNumber(1006, [linkDefs[j]]))
          }
        }
      }
      let maxVoted = -1
      let maxTableName = '' // Which is the maximum voted table name.
      for (const tableName in tableVote) {
        if (tableVote.hasOwnProperty(tableName)) {
          if (maxVoted < tableVote[tableName]) {
            maxVoted = tableVote[tableName]
            maxTableName = tableName.substring(10)
          }
        }
      }
      const context = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), 'name', maxTableName)
      if (linkDefs.length > 0 && !context && maxTableName) {
        INTERMediatorLog.setErrorMessage(
          INTERMediatorLib.getInsertedStringFromErrorNumber(1046, [maxTableName]))
      }
      for (let j = 0; j < linkDefs.length; j++) {
        if (linkDefs[j].indexOf(maxTableName) !== 0 && linkDefs[j].indexOf('_@') !== 0) {
          restDefs.push(linkDefs[j])
        }
      }
      if (linkDefs.length > 0 && context && restDefs.length > 0) {
        INTERMediatorLog.setErrorMessage(
          INTERMediatorLib.getInsertedStringFromErrorNumber(1047, [maxTableName, restDefs.toString()]))
      }
      return {targettable: context, fieldlist: fieldList['_im_index_' + maxTableName]}
    }

    /* --------------------------------------------------------------------

     */
    function cloneEveryNodes(originalNodes) {
      const clonedNodes = []
      for (let i = 0; i < originalNodes.length; i++) {
        clonedNodes.push(originalNodes[i].cloneNode(true))
      }
      return clonedNodes
    }

    /* --------------------------------------------------------------------

     */
    function getEnclosedNode(rootNode, tableName, fieldName) {
      if (rootNode.nodeType === 1) {
        const nodeInfo = INTERMediatorLib.getLinkedElementInfo(rootNode)
        for (let i = 0; i < nodeInfo.length; i++) {
          const nInfo = INTERMediatorLib.getNodeInfoArray(nodeInfo[i])
          if (nInfo.table === tableName && nInfo.field === fieldName) {
            return rootNode
          }
        }
      }
      const children = rootNode.childNodes // Check all child node of the enclosure.
      for (let i = 0; i < children.length; i++) {
        const r = getEnclosedNode(children[i], tableName, fieldName)
        if (r) {
          return r
        }
      }
      return null
    }

    /* --------------------------------------------------------------------

     */
    function appendCredit() {
      if (document.getElementById('IM_CREDIT') === null) {
        let bodyNode
        if (INTERMediatorOnPage.creditIncluding) {
          bodyNode = document.getElementById(INTERMediatorOnPage.creditIncluding)
        }
        if (!bodyNode) {
          bodyNode = document.getElementsByTagName('BODY')[0]
        }
        const creditNode = document.createElement('div')
        bodyNode.appendChild(creditNode)
        creditNode.setAttribute('id', 'IM_CREDIT')
        creditNode.setAttribute('class', 'IM_CREDIT')

        let cNode = document.createElement('div')
        creditNode.appendChild(cNode)
        cNode.className = '_im_credit1'
        cNode = document.createElement('div')
        creditNode.appendChild(cNode)
        cNode.className = '_im_credit2'
        cNode = document.createElement('div')
        creditNode.appendChild(cNode)
        cNode.className = '_im_credit3'
        cNode = document.createElement('div')
        creditNode.appendChild(cNode)
        cNode.className = '_im_credit4'

        if (INTERMediatorOnPage.useServiceServer) {
          let spNode = document.createElement('span')
          spNode.className = '_im_credit_ssstatus'
          cNode.appendChild(spNode)
          const mark = document.createTextNode('Service Server Status:')
          spNode.appendChild(mark)
          const markNode = document.createElement('span')
          markNode.className = '_im_credit_mark'
          markNode.setAttribute('title', 'Service Server is a server side helper for just validation on Ver.6.')
          spNode.appendChild(markNode)
          markNode.appendChild(document.createTextNode('◆'))
          markNode.style.color = INTERMediatorOnPage.serviceServerStatus ? 'green' : 'red'
          const markSktNode = document.createElement('span')
          markSktNode.appendChild(document.createTextNode('➤'))
          markSktNode.className = '_im_socket_mark'
          spNode.appendChild(markSktNode)
          INTERMediator.socketMarkNode = markSktNode
          if (INTERMediator.ssSocket) {
            markSktNode.style.color = 'yellow'
          }
        }
        let spNode = document.createElement('span')
        spNode.className = '_im_credit_vstring'
        cNode.appendChild(spNode)
        const aNode = document.createElement('a')
        aNode.appendChild(document.createTextNode('INTER-Mediator'))
        aNode.setAttribute('href', 'http://inter-mediator.com/')
        aNode.setAttribute('target', '_href')
        spNode.appendChild(document.createTextNode('Generated by '))
        spNode.appendChild(aNode)
        const versionString = INTERMediatorOnPage.metadata
          ? (` Ver.${INTERMediatorOnPage.metadata.version}(${INTERMediatorOnPage.metadata.releasedate})`)
          : ' Ver. Development Now!'
        spNode.appendChild(document.createTextNode(versionString))
      }
    }

    /* --------------------------------------------------------------------
     * detect FileMaker portal access mode
     */
    function isPortalAccessMode(currentContextDef) {
      'use strict'
      if (currentContextDef.relation && currentContextDef.relation[0] &&
        Boolean(currentContextDef.relation[0].portal) === true) {
        return true
      } else {
        return false
      }
    }
  },

  /* --------------------------------------------------------------------

   */
  setIdValue: (node) => {
    'use strict'
    let overwrite = true
    if (node.getAttribute('id') === null) {
      node.setAttribute('id', INTERMediator.nextIdValue())
    } else {
      if (INTERMediator.elementIds.indexOf(node.getAttribute('id')) >= 0) {
        const elementInfo = INTERMediatorLib.getLinkedElementInfo(node)
        for (let i = 0; i < elementInfo.length; i++) {
          const comp = elementInfo[i].split(INTERMediator.separator)
          if (comp[2] === '#id') {
            overwrite = false
          }
        }
        if (overwrite) {
          node.setAttribute('id', INTERMediator.nextIdValue())
        }
      }
      INTERMediator.elementIds.push(node.getAttribute('id'))
    }
    return node
  },

  nextIdValue: (adding = false) => {
    INTERMediator.linkedElmCounter++
    let idString = 'IM'
    if (adding) {
      idString += '_' + adding + '_'
    }
    return idString + INTERMediator.currentEncNumber + '-' + INTERMediator.linkedElmCounter
  },

  getLocalProperty: (localKey, defaultValue) => {
    'use strict'
    const value = IMLibLocalContext.getValue(localKey)
    return value === null ? defaultValue : value
  },

  setLocalProperty: (localKey, value) => {
    'use strict'
    IMLibLocalContext.setValue(localKey, value, true)
  },

  addCondition: (contextName, condition, notMatching, label) => {
    'use strict'
    if (notMatching) {
      condition.matching = !notMatching
    } else {
      condition.matching = INTERMediator_DBAdapter.eliminateDuplicatedConditions
    }
    if (label) {
      condition.label = label
    }
    if (INTERMediator.additionalCondition) {
      const value = INTERMediator.additionalCondition
      if (condition) {
        if (!value[contextName]) {
          value[contextName] = []
        }
        if (!condition.matching) {
          value[contextName].push(condition)
        } else {
          let hasIdentical = false
          for (let i = 0; i < value[contextName].length; i++) {
            if (value[contextName][i].field === condition.field &&
              value[contextName][i].operator === condition.operator) {
              hasIdentical = true
              value[contextName][i].value = condition.value
              break
            }
          }
          if (!hasIdentical) {
            value[contextName].push(condition)
          }
        }
      }
      INTERMediator.additionalCondition = value
    }
    IMLibLocalContext.archive()
  },

  clearCondition: (contextName, label) => {
    'use strict'
    let value = INTERMediator.additionalCondition
    if (!value) {
      value = {}
    }
    if (typeof label === 'undefined') {
      if (value[contextName]) {
        delete value[contextName]
        INTERMediator.additionalCondition = value
        IMLibLocalContext.archive()
      }
    } else {
      if (value[contextName]) {
        for (let i = 0; i < value[contextName].length; i++) {
          if (value[contextName][i].label === label) {
            value[contextName].splice(i, 1)
            i--
          }
        }
        INTERMediator.additionalCondition = value
        IMLibLocalContext.archive()
      }
    }
  },

  addSortKey: (contextName, sortKey) => {
    'use strict'
    const value = INTERMediator.additionalSortKey
    if (value[contextName]) {
      value[contextName].push(sortKey)
    } else {
      value[contextName] = [sortKey]
    }
    INTERMediator.additionalSortKey = value
    IMLibLocalContext.archive()
  },

  clearSortKey: (contextName) => {
    'use strict'
    const value = INTERMediator.additionalSortKey
    if (value[contextName]) {
      delete value[contextName]
      INTERMediator.additionalSortKey = value
      IMLibLocalContext.archive()
    }
  },

  setRecordLimit: (contextName, limit) => {
    'use strict'
    const value = INTERMediator.recordLimit
    value[contextName] = limit
    INTERMediator.recordLimit = value
    IMLibLocalContext.archive()
  },

  clearRecordLimit: (contextName) => {
    'use strict'
    const value = INTERMediator.recordLimit
    if (value[contextName]) {
      delete value[contextName]
      INTERMediator.recordLimit = value
      IMLibLocalContext.archive()
    }
  },

  localizing: () => {
    const targetNodes = document.querySelectorAll("*[data-im-locale]");
    for (const node of targetNodes) {
      const localeValue = node.getAttribute("data-im-locale")
      const bindValue = node.getAttribute("data-im")
      if (!bindValue) {
        const value = INTERMediator.getLocalizedString(localeValue)
        if (value) { // If the value is null, do nothing anyway.
          const bros = node.childNodes
          for (const item of bros) {
            if (item.nodeType == Node.TEXT_NODE) {
              node.removeChild(item)
            }
          }
          node.appendChild(document.createTextNode(value))
        }
      }
    }
  },

  getLocalizedString: (localeValue) => {
    const terms = INTERMediatorOnPage.getTerms()
    if (!terms || !localeValue) {
      return null;
    }
    const localeKey = localeValue.split('|')
    let value = terms
    for (const key of localeKey) {
      if (!value[key]) {
        value = null
        break;
      }
      value = value[key]
    }
    return value
  },

  /* Compatibility for previous version. These methos are defined here ever.
   * Now these are defined in INTERMediatorLog object. */
  flushMessage: () => {
    'use strict'
    INTERMediatorLog.flushMessage()
  },
  setErrorMessage: (ex, moreMessage) => {
    'use strict'
    INTERMediatorLog.setErrorMessage(ex, moreMessage)
  },
  setDebugMessage: (message, level) => {
    'use strict'
    INTERMediatorLog.setDebugMessage(message, level)
  }
}

// @@IM@@IgnoringRestOfFile
module.exports = INTERMediator
const INTERMediator_DBAdapter = require('../../src/js/Adapter_DBServer')
const IMLibLocalContext = require('../../src/js/INTER-Mediator-LocalContext')
