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
 * Usually you don't have to instantiate this class with the new operator.
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
    let hasUpdated = false
    if (key) {
      if (typeof value === 'undefined' || value === null) {
        delete this.store[key]
      } else {
        this.store[key] = value
        hasUpdated = true
        const refIds = this.binding[key]
        if (refIds) {
          for (let i = 0; i < refIds.length; i += 1) {
            const node = document.getElementById(refIds[i])
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
    const searchLen = location.search ? location.search.length : 0
    const hashLen = location.hash ? location.hash.length : 0
    const trailLen = searchLen + hashLen
    let key = '_im_localcontext' + document.URL.toString()
    key = (trailLen > 0) ? key.slice(0, -trailLen) : key
    return key
  },

  archive: function () {
    'use strict'
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
            const attrType = node.getAttribute('type')
            if (node.tagName === 'INPUT' && attrType && IMLibLocalContext.keyRespondTypes.indexOf(attrType) > -1) {
              IMLibKeyDownEventDispatch.setExecuteByCode(idValue, 'Enter', (function () {
                const contextName = params[1]
                return async function (event) {
                  if (!event.isComposing && event.code === 'Enter') {
                    updateFirstContext(contextName)
                  }
                }
              })())
              IMLibKeyDownEventDispatch.setExecuteByCode(idValue, 'NumpadEnter', (function () {
                const contextName = params[1]
                return async function (event) {
                  if (!event.isComposing && event.code === 'NumpadEnter') {
                    updateFirstContext(contextName)
                  }
                }
              })())
            } else if ((node.tagName === 'INPUT' && attrType && (attrType === 'checkbox' || attrType === 'radio'))
              || node.tagName === 'SELECT') {
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
      await INTERMediator.constructMain(INTERMediator.hasCrossTable ? null : context[0])
      //IMLibPageNavigation.navigationSetup()
    }
  },

  update: function (idValue) {
    'use strict'
    IMLibLocalContext.updateFromNodeValue(idValue)
  },

  updateFromNodeValue: function (idValue) {
    'use strict'
    const node = document.getElementById(idValue)
    const nodeValue = IMLibElement.getValueFromIMNode(node)
    const linkInfos = INTERMediatorLib.getLinkedElementInfo(node)
    for (let i = 0; i < linkInfos.length; i += 1) {
      IMLibLocalContext.store[linkInfos[i]] = nodeValue
      const nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfos[i])
      if (nodeInfo.table === IMLibLocalContext.contextName) {
        IMLibLocalContext.setValue(nodeInfo.field, nodeValue)
      }
    }
    IMLibCalc.recalculation(idValue)
  },

  updateFromStore: function (idValue) {
    'use strict'
    const node = document.getElementById(idValue)
    const target = node.getAttribute('data-im')
    const comp = target.split(INTERMediator.separator)
    if (comp[1]) {
      const nodeValue = IMLibLocalContext.store[comp[1]]
      const linkInfos = INTERMediatorLib.getLinkedElementInfo(node)
      for (let i = 0; i < linkInfos.length; i += 1) {
        IMLibLocalContext.store[linkInfos[i]] = nodeValue
        const nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfos[i])
        if (nodeInfo.table === IMLibLocalContext.contextName) {
          IMLibLocalContext.setValue(nodeInfo.field, nodeValue)
        }
      }
    }
  },

  updateAll: function (isStore) {
    'use strict'
    for (const key in IMLibLocalContext.binding) {
      if (IMLibLocalContext.binding.hasOwnProperty(key)) {
        const nodeIds = IMLibLocalContext.binding[key]
        for (let index = 0; index < nodeIds.length; index++) {
          const idValue = nodeIds[index]
          const targetNode = document.getElementById(idValue)
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
      if (node !== rootNode && IMLibLocalContext.checkedBinding.indexOf(node) > -1) {
        return // Stop on already checked enclosure nodes.
      }
      if (node.nodeType === 1) { // Work for an element
        try {
          self.bindingNode(node)
          const children = node.childNodes // Check all child nodes.
          if (children) {
            for (let i = 0; i < children.length; i += 1) {
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
