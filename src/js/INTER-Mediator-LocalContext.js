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
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
const IMLibLocalContext = {
  contextName: '_',
  store: {},
  binding: {},

  clearAll: function () {
    'use strict'
    this.store = {}
  },

  setValue: function (key, value, withoutArchive) {
    'use strict'
    let i, hasUpdated, refIds, node

    hasUpdated = false
    if (key) {
      if (value === undefined || value === null) {
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
    return value === undefined ? null : value
  },

  archive: function () {
    'use strict'
    let jsonString, key, searchLen, hashLen, trailLen
    INTERMediatorOnPage.removeCookie('_im_localcontext')
    if (INTERMediator.isIE && INTERMediator.ieVersion < 9) {
      this.store._im_additionalCondition = INTERMediator.additionalCondition
      this.store._im_additionalSortKey = INTERMediator.additionalSortKey
      this.store._im_startFrom = INTERMediator.startFrom
      this.store._im_pagedSize = INTERMediator.pagedSize
      /*
       IE8 issue: '' string is modified as 'null' on JSON stringify.
       http://blogs.msdn.com/b/jscript/archive/2009/06/23/serializing-the-value-of-empty-dom-elements-using-native-json-in-ie8.aspx
       */
      jsonString = JSON.stringify(this.store, function (k, v) {
        return v === '' ? '' : v
      })
    } else {
      jsonString = JSON.stringify(this.store)
    }
    if (INTERMediator.useSessionStorage === true &&
      typeof sessionStorage !== 'undefined' &&
      sessionStorage !== null) {
      try {
        searchLen = location.search ? location.search.length : 0
        hashLen = location.hash ? location.hash.length : 0
        trailLen = searchLen + hashLen
        key = '_im_localcontext' + document.URL.toString()
        key = (trailLen > 0) ? key.slice(0, -trailLen) : key
        sessionStorage.setItem(key, jsonString)
      } catch (ex) {
        INTERMediatorOnPage.setCookieWorker('_im_localcontext', jsonString, false, 0)
      }
    } else {
      INTERMediatorOnPage.setCookieWorker('_im_localcontext', jsonString, false, 0)
    }
  },

  unarchive: function () {
    'use strict'
    let localContext = ''
    let searchLen, hashLen, key, trailLen
    if (INTERMediator.useSessionStorage === true &&
      typeof sessionStorage !== 'undefined' &&
      sessionStorage !== null) {
      try {
        searchLen = location.search ? location.search.length : 0
        hashLen = location.hash ? location.hash.length : 0
        trailLen = searchLen + hashLen
        key = '_im_localcontext' + document.URL.toString()
        key = (trailLen > 0) ? key.slice(0, -trailLen) : key
        localContext = sessionStorage.getItem(key)
      } catch (ex) {
        localContext = INTERMediatorOnPage.getCookie('_im_localcontext')
      }
    } else {
      localContext = INTERMediatorOnPage.getCookie('_im_localcontext')
    }
    if (localContext && localContext.length > 0) {
      this.store = JSON.parse(localContext)
      if (INTERMediator.isIE && INTERMediator.ieVersion < 9) {
        if (this.store._im_additionalCondition) {
          INTERMediator.additionalCondition = this.store._im_additionalCondition
        }
        if (this.store._im_additionalSortKey) {
          INTERMediator.additionalSortKey = this.store._im_additionalSortKey
        }
        if (this.store._im_startFrom) {
          INTERMediator.startFrom = this.store._im_startFrom
        }
        if (this.store._im_pagedSize) {
          INTERMediator.pagedSize = this.store._im_pagedSize
        }
      }
      this.updateAll(true)
    }
  },

  bindingNode: function (node) {
    'use strict'
    let linkInfos, nodeInfo, idValue, i, j, value, params, unbinding, unexistId, dataImControl
    if (node.nodeType !== 1) {
      return
    }
    linkInfos = INTERMediatorLib.getLinkedElementInfo(node)
    dataImControl = node.getAttribute('data-im-control')
    unbinding = (dataImControl && dataImControl === 'unbind')
    for (i = 0; i < linkInfos.length; i += 1) {
      nodeInfo = INTERMediatorLib.getNodeInfoArray(linkInfos[i])
      if (nodeInfo.table === this.contextName) {
        if (!node.id) {
          node.id = INTERMediator.nextIdValue()
        }
        idValue = node.id
        if (!this.binding[nodeInfo.field]) {
          this.binding[nodeInfo.field] = []
        }
        if (this.binding[nodeInfo.field].indexOf(idValue) < 0 && !unbinding) {
          this.binding[nodeInfo.field].push(idValue)
          // this.store[nodeInfo.field] = document.getElementById(idValue).value
        }
        unexistId = -1
        while (unexistId >= 0) {
          for (j = 0; j < this.binding[nodeInfo.field].length; j++) {
            if (!document.getElementById(this.binding[nodeInfo.field][j])) {
              unexistId = j
            }
          }
          if (unexistId >= 0) {
            delete this.binding[nodeInfo.field][unexistId]
          }
        }

        value = this.store[nodeInfo.field]
        IMLibElement.setValueToIMNode(node, nodeInfo.target, value, true)

        params = nodeInfo.field.split(':')
        switch (params[0]) {
          case 'addorder':
            IMLibMouseEventDispatch.setExecute(idValue, IMLibUI.eventAddOrderHandler)
            break
          case 'update':
            IMLibMouseEventDispatch.setExecute(idValue, (function () {
              let contextName = params[1]
              return async function () {
                INTERMediator.startFrom = 0
                await IMLibUI.eventUpdateHandler(contextName)
                IMLibPageNavigation.navigationSetup()
              }
            })())
            break
          case 'condition':
            let attrType = node.getAttribute('type')
            if (attrType && attrType === 'text') {
              IMLibKeyDownEventDispatch.setExecuteByCode(idValue, 13, (function () {
                let contextName = params[1]
                return async function () {
                  INTERMediator.startFrom = 0
                  await IMLibUI.eventUpdateHandler(contextName)
                  IMLibPageNavigation.navigationSetup()
                }
              })())
            } else if (attrType && (attrType === 'checkbox' || attrType === 'radio')) {
              IMLibChangeEventDispatch.setExecute(idValue, (function () {
                let contextName = params[1]
                return async function () {
                  INTERMediator.startFrom = 0
                  await IMLibUI.eventUpdateHandler(contextName)
                  IMLibPageNavigation.navigationSetup()
                }
              })())
            }
            break
          case 'limitnumber':
            if (node.value) {
              this.store[nodeInfo.field] = node.value
            }
            IMLibChangeEventDispatch.setExecute(idValue, (function () {
              let contextName = params[1]
              return async function () {
                await IMLibUI.eventUpdateHandler(contextName)
                IMLibPageNavigation.navigationSetup()
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

    function seek (node) {
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
