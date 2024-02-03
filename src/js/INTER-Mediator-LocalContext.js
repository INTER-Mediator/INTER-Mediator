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
/* global INTERMediator, INTERMediatorOnPage, IMLibMouseEventDispatch, IMLibUI, IMLibKeyDownEventDispatch,
 IMLibChangeEventDispatch, INTERMediatorLib, INTERMediator_DBAdapter, IMLibQueue, IMLibCalc, IMLibPageNavigation,
 IMLibEventResponder, IMLibElement, Parser, IMLib, INTERMediatorLog */
/* jshint -W083 */ // Function within a loop

/**
 * @fileoverview IMLibContextPool, IMLibContext and IMLibLocalContext classes are defined here.
 */
/**
 *
 * Usually you don't have to instantiate this class with new operator.
 * @constructor
 */
const IMLibLocalContext = {
  contextName: '_',
  store: {},
  binding: {},
  keyRespondTypes: ['text', 'date', 'datetime-local', 'email', 'month', 'number', 'password', 'search', 'tel', 'time', 'url', 'week'],


  clearAll: function () {
    'use strict'
    this.store = {}
  },

  clearAllConditions: function () {
    for (const key in this.store) {
      if (key.indexOf('condition:') === 0) {
        this.setValue(key, '')
      }
    }
  },

  setValue: function (key, value, withoutArchive) {
    'use strict'
    let i, hasUpdated, refIds, node

    hasUpdated = false
    if (key) {
      if (typeof value === 'undefined' || value === null) {
        delete this.store[key]
      } else {
        this.store[key] = value
        hasUpdated = true
        refIds = this.binding[key]
        if (refIds) {
          for (i = 0; i < refIds.length; i += 1) {
            node = document.getElementById(refIds[i])
            IMLibElement.setValueToIMNode(node, '', value, true)
          }
        }
      }
    }
    if (hasUpdated && withoutArchive !== true) {
      this.archive()
    }
  },

  getValue: function (key) {
    'use strict'
    let value = this.store[key]
    return (typeof value === 'undefined') ? null : value
  },

  getHostNameForKey: function () {
    let key, searchLen, hashLen, trailLen
    searchLen = location.search ? location.search.length : 0
    hashLen = location.hash ? location.hash.length : 0
    trailLen = searchLen + hashLen
    key = '_im_localcontext' + document.URL.toString()
    key = (trailLen > 0) ? key.slice(0, -trailLen) : key
    return key
  },

  archive: function () {
    'use strict'
    // const x = INTERMediator.getLocalProperty('_im_startFrom', 0)
    INTERMediatorOnPage.removeCookie('_im_localcontext')
    let jsonString = JSON.stringify(this.store)
    if (INTERMediator.useSessionStorage === true && typeof sessionStorage !== 'undefined' && sessionStorage !== null) {
      try {
        sessionStorage.setItem(IMLibLocalContext.getHostNameForKey(), jsonString)
      } catch (ex) {
        INTERMediatorOnPage.setCookieWorker('_im_localcontext', jsonString, false, 0)
      }
    } else {
      INTERMediatorOnPage.setCookieWorker('_im_localcontext', jsonString, false, 0)
    }
  },

  unarchive: function () {
    'use strict'
    // const x = INTERMediator.getLocalProperty('_im_startFrom', 0)
    let localContext
    if (INTERMediator.useSessionStorage === true && typeof sessionStorage !== 'undefined' && sessionStorage !== null) {
      try {
        localContext = sessionStorage.getItem(IMLibLocalContext.getHostNameForKey())
      } catch (ex) {
        localContext = INTERMediatorOnPage.getCookie('_im_localcontext')
      }
    } else {
      localContext = INTERMediatorOnPage.getCookie('_im_localcontext')
    }
    if (localContext && localContext.length > 0) {
      this.store = JSON.parse(localContext)
      this.updateAll(true)
    }
  },

  bindingNode: function (node) {
    'use strict'
    if (node.nodeType !== 1) {
      return
    }
    const linkInfos = INTERMediatorLib.getLinkedElementInfo(node)
    const dataImControl = node.getAttribute('data-im-control')
    const unbinding = (dataImControl && dataImControl === 'unbind')
    for (let i = 0; i < linkInfos.length; i += 1) {
      const nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfos[i])
      if (nodeInfo.table === this.contextName) {
        if (!node.id) {
          node.id = INTERMediator.nextIdValue()
        }
        const idValue = node.id
        if (!this.binding[nodeInfo.field]) {
          this.binding[nodeInfo.field] = []
        }
        if (this.binding[nodeInfo.field].indexOf(idValue) < 0 && !unbinding) {
          this.binding[nodeInfo.field].push(idValue)
          // this.store[nodeInfo.field] = document.getElementById(idValue).value
        }
        let unexistId = -1
        while (unexistId >= 0) {
          for (let j = 0; j < this.binding[nodeInfo.field].length; j++) {
            if (!document.getElementById(this.binding[nodeInfo.field][j])) {
              unexistId = j
            }
          }
          if (unexistId >= 0) {
            delete this.binding[nodeInfo.field][unexistId]
          }
        }

        const value = this.store[nodeInfo.field]
        IMLibElement.setValueToIMNode(node, nodeInfo.target, value, true)

        const params = nodeInfo.field.split(':')
        switch (params[0]) {
          case 'addorder':
            IMLibMouseEventDispatch.setExecute(idValue, IMLibUI.eventAddOrderHandler)
            break
          case 'update':
            IMLibMouseEventDispatch.setExecute(idValue, (function () {
              const contextName = params[1]
              return async function () {
                updateFirstContext(contextName)
              }
            })())
            break
          case 'condition':
            const nodeIsInput = (node.tagName === 'INPUT')
            const nodeIsSelect = (node.tagName === 'SELECT')
            const attrType = node.getAttribute('type')
            if (nodeIsInput && attrType && IMLibLocalContext.keyRespondTypes.indexOf(attrType) > -1) {
              IMLibKeyDownEventDispatch.setExecuteByCode(idValue, 'Enter', (function () {
                const contextName = params[1]
                return async function (event) {
                  if (event.keyCode === 13) {
                    updateFirstContext(contextName)
                  }
                  /* We understand the keyCode property is already deprecated. But the code property is "Enter" for
                     both Enter key and the finalize key of an input method, and there is no way to distinguish these
                     keys. The keyCode property is 13 for Enter and 229 for the finalize key, and it's a better way
                     to prevent the context updating for the finalize key. msyk 2019-12-28 */
                }
              })())
            } else if ((nodeIsInput && attrType && (attrType === 'checkbox' || attrType === 'radio'))
              || nodeIsSelect) {
              IMLibChangeEventDispatch.setExecute(idValue, (function () {
                const contextName = params[1]
                return async function () {
                  updateFirstContext(contextName)
                }
              })())
            }
            break
          case 'limitnumber'
          :
            if (node.value) {
              this.store[nodeInfo.field] = node.value
            }
            IMLibChangeEventDispatch.setExecute(idValue, (function () {
              const contextName = params[1]
              return async function () {
                updateFirstContext(contextName)
              }
            })())
            node.setAttribute('data-imchangeadded', 'set')
            break
          default:
            IMLibChangeEventDispatch.setExecute(idValue, IMLibLocalContext.update)
            break
        }
      }
    }

    async function updateFirstContext(contextName) {
      INTERMediator.startFrom = 0
      // await IMLibUI.eventUpdateHandler(contextName)
      IMLibLocalContext.updateAll()
      const context = IMLibContextPool.getContextFromName(contextName)
      await INTERMediator.constructMain(context[0])
      //IMLibPageNavigation.navigationSetup()
    }
  },

  update: function (idValue) {
    'use strict'
    IMLibLocalContext.updateFromNodeValue(idValue)
  },

  updateFromNodeValue: function (idValue) {
    'use strict'
    let node, nodeValue, linkInfos, nodeInfo, i
    node = document.getElementById(idValue)
    nodeValue = IMLibElement.getValueFromIMNode(node)
    linkInfos = INTERMediatorLib.getLinkedElementInfo(node)
    for (i = 0; i < linkInfos.length; i += 1) {
      IMLibLocalContext.store[linkInfos[i]] = nodeValue
      nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfos[i])
      if (nodeInfo.table === IMLibLocalContext.contextName) {
        IMLibLocalContext.setValue(nodeInfo.field, nodeValue)
      }
    }
    IMLibCalc.recalculation(idValue)
  },

  updateFromStore: function (idValue) {
    'use strict'
    let node, nodeValue, linkInfos, nodeInfo, i, target, comp
    node = document.getElementById(idValue)
    target = node.getAttribute('data-im')
    comp = target.split(INTERMediator.separator)
    if (comp[1]) {
      nodeValue = IMLibLocalContext.store[comp[1]]
      linkInfos = INTERMediatorLib.getLinkedElementInfo(node)
      for (i = 0; i < linkInfos.length; i += 1) {
        IMLibLocalContext.store[linkInfos[i]] = nodeValue
        nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfos[i])
        if (nodeInfo.table === IMLibLocalContext.contextName) {
          IMLibLocalContext.setValue(nodeInfo.field, nodeValue)
        }
      }
    }
  },

  updateAll: function (isStore) {
    'use strict'
    let index, key, nodeIds, idValue, targetNode
    for (key in IMLibLocalContext.binding) {
      if (IMLibLocalContext.binding.hasOwnProperty(key)) {
        nodeIds = IMLibLocalContext.binding[key]
        for (index = 0; index < nodeIds.length; index++) {
          idValue = nodeIds[index]
          targetNode = document.getElementById(idValue)
          if (targetNode &&
            (targetNode.tagName === 'INPUT' ||
              targetNode.tagName === 'TEXTAREA' ||
              targetNode.tagName === 'SELECT')) {
            if (isStore === true) {
              IMLibLocalContext.updateFromStore(idValue)
            } else {
              IMLibLocalContext.updateFromNodeValue(idValue)
            }
            break
          }
        }
      }
    }
  },

  checkedBinding: [],

  bindingDescendant: function (rootNode) {
    'use strict'
    let self = this
    seek(rootNode)
    IMLibLocalContext.checkedBinding.push(rootNode)

    function seek(node) {
      let children, i
      if (node !== rootNode && IMLibLocalContext.checkedBinding.indexOf(node) > -1) {
        return // Stop on already checked enclosure nodes.
      }
      if (node.nodeType === 1) { // Work for an element
        try {
          self.bindingNode(node)
          children = node.childNodes // Check all child nodes.
          if (children) {
            for (i = 0; i < children.length; i += 1) {
              seek(children[i])
            }
          }
        } catch (ex) {
          if (ex.message === '_im_auth_required_') {
            throw ex
          } else {
            INTERMediatorLog.setErrorMessage(ex, 'EXCEPTION-31')
          }
        }
      }
    }
  }
}

// @@IM@@IgnoringRestOfFile
module.exports = IMLibLocalContext
const INTERMediator = require('../../src/js/INTER-Mediator')
const INTERMediatorOnPage = require('../../src/js/INTER-Mediator-Page')
