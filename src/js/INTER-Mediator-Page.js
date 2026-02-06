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
/* global IMLibContextPool, INTERMediator, IMLibMouseEventDispatch, IMLibLocalContext,
 IMLibChangeEventDispatch, INTERMediatorLib, INTERMediator_DBAdapter, IMLibQueue, IMLibCalc, IMLibUI,
 IMLibEventResponder, INTERMediatorLog, IMLib, JSEncrypt */
/* jshint -W083 */ // Function within a loop
/**
 * @fileoverview INTERMediatorOnPage class is defined here.
 */
/**
 *
 * Usually you don't have to instantiate this class with the new operator.
 * @constructor
 */
/**
 * Class handling page-level functionality for INTER-Mediator
 * @type {Object}
 */
let INTERMediatorOnPage = {
  /** Maximum number of authentication attempts allowed
   * @type {number} */
  dbCache: {},
  creditIncluding: null,
  masterScrollPosition: null, // @Private
  nonSupportMessageId: 'nonsupportmessage',
  isFinishToConstruct: false,
  isAutoConstruct: true,
  additionalExpandingEnclosureFinish: {},
  additionalExpandingRecordFinish: {},
  getEntryPath: null,
  getDataSources: null,
  getOptionsAliases: null,
  getOptionsTransaction: null,
  dbClassName: null,
  defaultKeyName: null, // @Private
  browserCompatibility: null,
  clientNotificationIdentifier: null, // @Private
  metadata: null,
  appLocale: null,
  appCurrency: null,
  isShowProgress: true,
  notShowHeaderFooterOnNoResult: false,
  newRecordId: null,
  syncBeforeUpdate: null,
  syncAfterUpdate: null,
  syncBeforeCreate: null,
  syncAfterCreate: null,
  syncBeforeDelete: null,
  syncAfterDelete: null,
  useServiceServer: false,
  activateClientService: false,
  updateProcessedNode: false,
  updatingWithSynchronize: 0,
  isFollowingTimezone: false,
  activateMaintenanceCall: false,
  includingParts: [],
  serviceServerStatus: false,
  serviceServerURL: null,
  uiEventDT: null,
  isPasskey: false,
  /** CSS class for copy button on generated UI */
  buttonClassCopy: null,
  /** CSS class for delete button on generated UI */
  buttonClassDelete: null,
  /** CSS class for insert button on generated UI */
  buttonClassInsert: null,
  /** CSS class for master navigation button on generated UI */
  buttonClassMaster: null,
  /** CSS class for back navigation button on generated UI */
  buttonClassBackNavi: null,

  justUpdateWholePage: false,
  justMoveToDetail: false,

  /* This method is going to supply by accessing a definition file. This entry is just a definition for static analyzer. */
  getTerms: function () {
    return {dummy: 'dummy'}
  }, /*
  This method 'getMessages' is going to be replaced valid one with the browser's language.
  Here is defined to prevent the warning of static check.
  */
  getMessages: function () {
    'use strict'
    return null
  },

  /**
   * Check if enough time has passed since last UI event
   * @returns {boolean} True if enough time has passed or no previous event
   */
  checkUIEventDT: function () {
    const prevEventDT = INTERMediatorOnPage.uiEventDT
    const now = new Date()
    INTERMediatorOnPage.uiEventDT = now
    if (!prevEventDT) {
      return true
    }
    const diff = now.getTime() - prevEventDT.getTime()
    return diff > 500;
  },

  /**
   * Get URL parameters as an associative array
   * @returns {Object} Object containing URL parameters as key-value pairs
   */
  getURLParametersAsArray: function () {
    'use strict'
    const result = {}
    const params = location.search.substring(1).split('&')
    for (let i = 0; i < params.length; i++) {
      const eqPos = params[i].indexOf('=')
      if (eqPos > 0) {
        const key = params[i].substring(0, eqPos)
        const value = params[i].substring(eqPos + 1)
        result[key] = decodeURIComponent(value)
      }
    }
    return result
  },

  /**
   * Get context information for given context name
   * @param {string} contextName Name of the context to lookup
   * @returns {Object|null} Context information object if found, null otherwise
   */
  getContextInfo: function (contextName) {
    'use strict'
    const dataSources = INTERMediatorOnPage.getDataSources()
    for (const index in dataSources) {
      if (dataSources.hasOwnProperty(index) && dataSources[index].name === contextName) {
        return dataSources[index]
      }
    }
    return null
  },


  /**
   *
   * @param deleteNode
   * @returns {boolean}
   */
  INTERMediatorCheckBrowser: function (deleteNode) {
    'use strict'
    let judge = false
    let positiveList = INTERMediatorOnPage.browserCompatibility()

    if (positiveList === "*") {
      return true;
    }

    if (positiveList.edge && navigator.userAgent.indexOf('Edge/') > -1) {
      positiveList = {'edge': positiveList.edge}
    } else if (positiveList.trident && navigator.userAgent.indexOf('Trident/') > -1) {
      positiveList = {'trident': positiveList.trident}
    } else if (positiveList.msie && navigator.userAgent.indexOf('MSIE ') > -1) {
      positiveList = {'msie': positiveList.msie}
    } else if (positiveList.opera && (navigator.userAgent.indexOf('Opera/') > -1 || navigator.userAgent.indexOf('OPR/') > -1)) {
      positiveList = {'opera': positiveList.opera, 'opr': positiveList.opera}
    }

    let versionStr
    let matchAgent = false
    let matchOS = false
    for (const agent in positiveList) {
      if (positiveList.hasOwnProperty(agent)) {
        if (navigator.userAgent.toUpperCase().indexOf(agent.toUpperCase()) > -1) {
          matchAgent = true
          if (positiveList[agent] instanceof Object) {
            for (const os in positiveList[agent]) {
              if (positiveList[agent].hasOwnProperty(os) && navigator.platform.toUpperCase().indexOf(os.toUpperCase()) > -1) {
                matchOS = true
                versionStr = positiveList[agent][os]
                break
              }
            }
          } else {
            matchOS = true
            versionStr = positiveList[agent]
            break
          }
        }
      }
    }

    if (matchAgent && matchOS) {
      let specifiedVersion = parseInt(versionStr, 10)
      let agentPos = -1
      if (navigator.appVersion.indexOf('Edge/') > -1) {
        agentPos = navigator.appVersion.indexOf('Edge/') + 5
      } else if (navigator.appVersion.indexOf('Trident/') > -1) {
        agentPos = navigator.appVersion.indexOf('Trident/') + 8
      } else if (navigator.appVersion.indexOf('MSIE ') > -1) {
        agentPos = navigator.appVersion.indexOf('MSIE ') + 5
      } else if (navigator.appVersion.indexOf('OPR/') > -1) {
        agentPos = navigator.appVersion.indexOf('OPR/') + 4
      } else if (navigator.appVersion.indexOf('Opera/') > -1) {
        agentPos = navigator.appVersion.indexOf('Opera/') + 6
      } else if (navigator.appVersion.indexOf('Chrome/') > -1) {
        agentPos = navigator.appVersion.indexOf('Chrome/') + 7
      } else if (navigator.appVersion.indexOf('Safari/') > -1 && navigator.appVersion.indexOf('Version/') > -1) {
        agentPos = navigator.appVersion.indexOf('Version/') + 8
      } else if (navigator.userAgent.indexOf('Firefox/') > -1) {
        agentPos = navigator.userAgent.indexOf('Firefox/') + 8
      } else if (navigator.appVersion.indexOf('WebKit/') > -1) {
        agentPos = navigator.appVersion.indexOf('WebKit/') + 7
      }

      let dotPos, versionNum
      if (agentPos > -1) {
        if (navigator.userAgent.indexOf('Firefox/') > -1) {
          dotPos = navigator.userAgent.indexOf('.', agentPos)
          versionNum = parseInt(navigator.userAgent.substring(agentPos, dotPos), 10)
        } else {
          dotPos = navigator.appVersion.indexOf('.', agentPos)
          versionNum = parseInt(navigator.appVersion.substring(agentPos, dotPos), 10)
        }
        /*
         As for the appVersion property of IE, refer http://msdn.microsoft.com/en-us/library/aa478988.aspx
         */
      } else {
        dotPos = navigator.appVersion.indexOf('.')
        versionNum = parseInt(navigator.appVersion.substring(0, dotPos), 10)
      }
      if (INTERMediator.isTrident) {
        specifiedVersion = specifiedVersion + 4
      }
      if (versionStr.indexOf('-') > -1) {
        judge = (specifiedVersion >= versionNum)
        if (document.documentMode) {
          judge = (specifiedVersion >= document.documentMode)
        }
      } else if (versionStr.indexOf('+') > -1) {
        judge = (specifiedVersion <= versionNum)
        if (document.documentMode) {
          judge = (specifiedVersion <= document.documentMode)
        }
      } else {
        judge = (specifiedVersion === versionNum)
        if (document.documentMode) {
          judge = (specifiedVersion === document.documentMode)
        }
      }
    }
    if (judge === true) {
      if (deleteNode) {
        deleteNode.parentNode.removeChild(deleteNode)
      }
    } else {
      const bodyNode = document.getElementsByTagName('BODY')[0]
      const elm = document.createElement('div')
      elm.setAttribute('align', 'center')
      const childElm = document.createElement('font')
      childElm.setAttribute('color', 'gray')
      const grandChildElm = document.createElement('font')
      grandChildElm.setAttribute('size', '+2')
      grandChildElm.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[1022]))
      childElm.appendChild(grandChildElm)
      childElm.appendChild(document.createElement('br'))
      childElm.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[1023]))
      childElm.appendChild(document.createElement('br'))
      childElm.appendChild(document.createTextNode(navigator.userAgent))
      elm.appendChild(childElm)
      while (bodyNode.firstChild) {
        bodyNode.removeChild(bodyNode.firstChild)
      }
      // for (let i = bodyNode.childNodes.length - 1; i >= 0; i--) {
      //   bodyNode.removeChild(bodyNode.childNodes[i])
      // }
      bodyNode.appendChild(elm)
    }
    return judge
  },

  /*
   Seek nodes from the repeater of the 'fromNode' parameter.
   */
  getNodeIdFromIMDefinition: function (imDefinition, fromNode, justFromNode) {
    'use strict'
    console.error('INTERMediatorOnPage.getNodeIdFromIMDefinition method in INTER-Mediator-Page.js will be removed in Ver.6.0. ' + 'The alternative method is getNodeIdsHavingTargetFromNode or getNodeIdsHavingTargetFromRepeater.')
    let repeaterNode
    if (justFromNode) {
      repeaterNode = fromNode
    } else {
      repeaterNode = INTERMediatorLib.getParentRepeater(fromNode)
    }
    return seekNode(repeaterNode, imDefinition)

    function seekNode(node, imDefinition) {
      if (node.nodeType !== 1) {
        return null
      }
      const children = node.childNodes
      if (children) {
        for (let i = 0; i < children.length; i++) {
          if (children[i].nodeType === 1) {
            if (INTERMediatorLib.isLinkedElement(children[i])) {
              const nodeDefs = INTERMediatorLib.getLinkedElementInfo(children[i])
              if (nodeDefs.indexOf(imDefinition) > -1) {
                return children[i].getAttribute('id')
              }
            }
            const returnValue = seekNode(children[i], imDefinition)
            if (returnValue !== null) {
              return returnValue
            }
          }
        }
      }
      return null
    }
  },

  getNodeIdFromIMDefinitionOnEnclosure: function (imDefinition, fromNode) {
    'use strict'
    console.error('INTERMediatorOnPage.getNodeIdFromIMDefinitionOnEnclosure method in INTER-Mediator-Page.js will be removed in Ver.6.0. ' + 'The alternative method is getNodeIdsHavingTargetFromEnclosure.')
    const repeaterNode = INTERMediatorLib.getParentEnclosure(fromNode)
    return seekNode(repeaterNode, imDefinition)

    function seekNode(node, imDefinition) {
      if (node.nodeType !== 1) {
        return null
      }
      const children = node.childNodes
      if (children) {
        for (let i = 0; i < children.length; i++) {
          if (children[i].nodeType === 1) {
            if (INTERMediatorLib.isLinkedElement(children[i])) {
              const nodeDefs = INTERMediatorLib.getLinkedElementInfo(children[i])
              if (nodeDefs.indexOf(imDefinition) > -1 && children[i].getAttribute) {
                return children[i].getAttribute('id')
              }
            }
            const returnValue = seekNode(children[i], imDefinition)
            if (returnValue !== null) {
              return returnValue
            }
          }
        }
      }
      return null
    }
  },

  /**
   * Get node IDs that match the given IM definition
   * @param {string} imDefinition The IM definition to match
   * @param {Node} fromNode Starting node for search
   * @param {boolean|string} justFromNode Search mode - true: just fromNode, false: parent enclosure, 'others': parent repeaters
   * @returns {Array} Array of matching node IDs
   */
  getNodeIdsFromIMDefinition: function (imDefinition, fromNode, justFromNode) {
    'use strict'
    let nodeIds = []
    let enclosureNode
    if (justFromNode === true) {
      enclosureNode = [fromNode]
    } else if (justFromNode === false) {
      enclosureNode = [INTERMediatorLib.getParentEnclosure(fromNode)]
    } else {
      enclosureNode = INTERMediatorLib.getParentRepeaters(fromNode)
    }
    if (!enclosureNode) {
      return []
    }
    for (let i = 0; i < enclosureNode.length; i += 1) {
      if (enclosureNode[i] !== null) {
        if (Array.isArray(enclosureNode[i])) {
          for (let j = 0; j < enclosureNode[i].length; j++) {
            seekNode(enclosureNode[i][j], imDefinition)
          }
        } else {
          seekNode(enclosureNode[i], imDefinition)
        }
      }
    }
    return nodeIds

    function seekNode(node, imDefinition) {
      if (node.nodeType !== 1) {
        return
      }
      const children = node.childNodes
      if (children) {
        for (let i = 0; i < children.length; i++) {
          if (children[i].nodeType === 1) {
            const nodeDefs = INTERMediatorLib.getLinkedElementInfo(children[i])
            if (nodeDefs && nodeDefs.indexOf(imDefinition) > -1) {
              if (children[i].getAttribute('id')) {
                nodeIds.push(children[i].getAttribute('id'))
              } else {
                nodeIds.push(children[i])
              }
            }
          }
          seekNode(children[i], imDefinition)
        }
      }
    }
  },

  getNodeIdsHavingTargetFromNode: function (fromNode, imDefinition) {
    'use strict'
    return INTERMediatorOnPage.getNodeIdsFromIMDefinition(imDefinition, fromNode, true)
  },

  getNodeIdsHavingTargetFromRepeater: function (fromNode, imDefinition) {
    'use strict'
    return INTERMediatorOnPage.getNodeIdsFromIMDefinition(imDefinition, fromNode, 'others')
  },

  getNodeIdsHavingTargetFromEnclosure: function (fromNode, imDefinition) {
    'use strict'
    return INTERMediatorOnPage.getNodeIdsFromIMDefinition(imDefinition, fromNode, false)
  },

  /*
   * The hiding process is realized by _im_progress's div elements, but it's quite sensitive.
   * I've tried to set the CSS animations, but it seems to be a reason to stay in the progress panel.
   * So far I gave up using CSS animations. I think it's a matter of handling transitionend event.
   * Now this method is going to be called multiple times in the case of the edit text field.
   * But it doesn't work by excluding to call by flag variable. I don't know why.
   * 2017-05-04 Masayuki Nii
   */
  /**
   * Hide the progress indicator
   * @param {boolean} force Force hiding even if counter > 0
   * @returns {Promise<void>}
   */
  hideProgress: async function (force = false) {
    if (!INTERMediatorOnPage.isShowProgress) {
      return
    }

    INTERMediatorOnPage.progressCounter -= 1;
    if (INTERMediatorOnPage.progressCounter <= 0 || force) {
      // Waiting for debug
      // const wait = async (ms) => new Promise(resolve => setTimeout(resolve, ms));
      // await wait(1000)

      const frontPanel = document.getElementById('_im_progress')
      if (frontPanel) {
        const themeName = INTERMediatorOnPage.getTheme().toLowerCase()
        if (themeName === 'least' || themeName === 'thosedays') {
          frontPanel.style.display = 'none'
        } else {
          // frontPanel.style.display = 'none'
          frontPanel.style.transitionDuration = '0.3s'
          frontPanel.style.opacity = 0
          frontPanel.style.zIndex = -9999
        }
      }
      INTERMediatorOnPage.progressShowing = false
      INTERMediatorOnPage.progressCounter = 0
    }
  },

  // Gear SVG was generated on http://loading.io/.
  /**
   * Show the progress indicator
   * @param {boolean} isDelay If true, shows progress after delay
   */
  showProgress: function (isDelay = true) {
    if (!INTERMediatorOnPage.isShowProgress) {
      return
    }
    INTERMediatorOnPage.progressCounter += 1;
    if (isDelay) {
      setTimeout(INTERMediatorOnPage.showProgressImpl, INTERMediatorOnPage.progressStartDelay)
    } else {
      INTERMediatorOnPage.showProgressImpl()
    }
  },

  progressCounter: 0,
  progressShowing: false,
  progressStartDelay: 300,

  showProgressImpl: function () {
    'use strict'
    // if (!INTERMediatorOnPage.isShowProgress) {
    //   return
    // }
    if (INTERMediatorOnPage.progressShowing) {
      return
    }
    if (INTERMediatorOnPage.progressCounter === 0) {
      return
    }
    const themeName = INTERMediatorOnPage.getTheme().toLowerCase()
    let frontPanel = document.getElementById('_im_progress')
    if (!frontPanel) {
      frontPanel = document.createElement('div')

      frontPanel.setAttribute('id', '_im_progress')
      const bodyNode = document.getElementsByTagName('BODY')[0]
      if (bodyNode.firstChild) {
        bodyNode.insertBefore(frontPanel, bodyNode.firstChild)
      } else {
        bodyNode.appendChild(frontPanel)
      }
      if (themeName === 'least' || themeName === 'thosedays') {
        const imageIM = document.createElement('img')
        imageIM.setAttribute('id', '_im_logo')
        let url = INTERMediatorOnPage.getEntryPath()
        url = INTERMediatorLib.mergeURLParameter(url, 'theme', INTERMediatorOnPage.getTheme())
        url = INTERMediatorLib.mergeURLParameter(url, 'type', 'images')
        url = INTERMediatorLib.mergeURLParameter(url, 'name', 'logo.gif')
        imageIM.setAttribute('src', url)
        frontPanel.appendChild(imageIM)
        const imageProgress = document.createElement('img')
        imageProgress.setAttribute('id', '_im_animatedimage')
        imageProgress.setAttribute('src', INTERMediatorOnPage.getEntryPath() + '?theme=' + INTERMediatorOnPage.getTheme() + '&type=images&name=inprogress.gif')
        frontPanel.appendChild(imageProgress)
        const brNode = document.createElement('BR')
        brNode.setAttribute('clear', 'all')
        frontPanel.appendChild(brNode)
        frontPanel.appendChild(document.createTextNode('INTER-Mediator working'))
      } else {
        const imageIM = document.createElement('img')
        let url = INTERMediatorOnPage.getEntryPath()
        url = INTERMediatorLib.mergeURLParameter(url, 'theme', INTERMediatorOnPage.getTheme())
        url = INTERMediatorLib.mergeURLParameter(url, 'type', 'images')
        url = INTERMediatorLib.mergeURLParameter(url, 'name', 'gears.svg')
        imageIM.setAttribute('src', url)
        frontPanel.appendChild(imageIM)
      }
    }
    if (themeName !== 'least' && themeName !== 'thosedays') {
      frontPanel.style.transitionDuration = '0'
      frontPanel.style.opacity = 1.0
      frontPanel.style.zIndex = 555555
    }
    INTERMediatorOnPage.progressShowing = true;
  },

  /**
   * Set reference to a theme CSS file in the page header
   */
  setReferenceToTheme: function () {
    'use strict'
    const headNode = document.getElementsByTagName('HEAD')[0]
    const linkElement = document.createElement('link')
    let url = INTERMediatorOnPage.getEntryPath()
    url = INTERMediatorLib.mergeURLParameter(url, 'theme', INTERMediatorOnPage.getTheme())
    url = INTERMediatorLib.mergeURLParameter(url, 'type', 'css')
    linkElement.setAttribute('href', url)
    linkElement.setAttribute('rel', 'stylesheet')
    linkElement.setAttribute('type', 'text/css')
    let styleIndex = -1
    for (let i = 0; i < headNode.childNodes.length; i++) {
      if (headNode.childNodes[i] && headNode.childNodes[i].nodeType === 1 && headNode.childNodes[i].tagName === 'LINK' && headNode.childNodes[i].rel === 'stylesheet') {
        styleIndex = i
        break
      }
    }
    if (styleIndex > -1) {
      headNode.insertBefore(linkElement, headNode.childNodes[styleIndex])
    } else {
      headNode.appendChild(linkElement)
    }
  }
}

// @@IM@@IgnoringRestOfFile
module.exports = INTERMediatorOnPage
// const JSEncrypt = require('../../node_modules/jsencrypt/bin/jsencrypt.js')
const INTERMediatorLib = require('../../src/js/INTER-Mediator-Lib')
