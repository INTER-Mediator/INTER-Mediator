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
 IMLibChangeEventDispatch, INTERMediator_DBAdapter, IMLibQueue, IMLibCalc, IMLibPageNavigation,
 IMLibEventResponder, Parser, IMLibLocalContext, IMLibFormat, IMLibInputEventDispatch */
/* jshint -W083 */ // Function within a loop

/**
 * @fileoverview IMLib and INTERMediatorLib classes are defined here.
 */
/**
 *
 * Usually you don't have to instantiate this class with new operator.
 * @constructor
 */
const IMLib = {
  nl_char: '\n',
  cr_char: '\r',
  tab_char: '\t',
  singleQuote_char: '\'',
  doubleQuote_char: '"',
  backSlash_char: '\\',

  get zerolength_str() {
    'use strict'
    return ''
  },
  set zerolength_str(value) {
    // do nothing
  },

  get crlf_str() {
    'use strict'
    return '\r\n'
  },
  set crlf_str(value) {
    // do nothing
  }
}

/**
 *
 * Usually you don't have to instantiate this class with new operator.
 * @constructor
 */
const INTERMediatorLib = {

  ignoreEnclosureRepeaterClassName: '_im_ignore_enc_rep',
  ignoreEnclosureRepeaterControlName: 'ignore_enc_rep',
  roleAsRepeaterClassName: '_im_repeater',
  roleAsEnclosureClassName: '_im_enclosure',
  roleAsRepeaterDataControlName: 'repeater',
  roleAsEnclosureDataControlName: 'enclosure',
  roleAsSeparatorDataControlName: 'separator',
  roleAsHeaderDataControlName: 'header',
  roleAsFooterDataControlName: 'footer',
  roleAsNoResultDataControlName: 'noresult',

  initialize: function () {
    'use strict'
    IMLibLocalContext.unarchive()
    return null
  },

  setup: function () {
    'use strict'
    if (window.addEventListener) {
      window.addEventListener('load', INTERMediatorLib.initialize, false)
    } else {
      window.onload = INTERMediatorLib.initialize
    }

    return null
  },

  // Refer to: https://qiita.com/amamamaou/items/ef0b797156b324bb4ef3
  isObject: (val) => {
    return Object.prototype.toString.call(val).slice(8, -1).toLowerCase() === 'object'
  },

  isArray: (val) => {
    return Array.isArray(val)
  },

  isNaN: (val) => {
    return Number.isNaN(val)
  },

  isNull: (val) => {
    return Object.prototype.toString.call(val).slice(8, -1).toLowerCase() === 'null'
  },

  isUndefined: (val) => {
    return Object.prototype.toString.call(val).slice(8, -1).toLowerCase() === 'undefined'
  },

  isBoolean: (val) => {
    return Object.prototype.toString.call(val).slice(8, -1).toLowerCase() === 'boolean'
  },

  markProcessed: function (node) {
    'use strict'
    const nodeAttr = node.getAttribute('data-im-element')
    node.setAttribute('data-im-element', nodeAttr + ' processed')
  },

  isProcessed: function (node) {
    'use strict'
    const nodeAttr = node.getAttribute('data-im-element')
    return nodeAttr && nodeAttr.match(/processed/)
  },

  markProcessedInsert: function (node) {
    'use strict'
    const nodeAttr = node.getAttribute('data-im-element')
    node.setAttribute('data-im-element', nodeAttr + ' insert')
  },

  isProcessedInsert: function (node) {
    'use strict'
    const nodeAttr = node.getAttribute('data-im-element')
    return nodeAttr && nodeAttr.match(/insert/)
  },

  generateSalt() {
    'use strict'
    let salt = ''
    let saltHex = ''
    for (let i = 0; i < 4; i += 1) {
      const code = Math.floor(Math.random() * (128 - 32) + 32)
      salt += String.fromCharCode(code)
      saltHex += code.toString(16)
    }
    return [salt, saltHex]
  },

  generatePasswrdHashV1: (password, salt) => {
    let shaObj = new jsSHA('SHA-1', 'TEXT')
    shaObj.update(password + salt)
    let hash = shaObj.getHash('HEX')
    return hash + INTERMediatorLib.stringToHex(salt)
  },
  generatePasswrdHashV2m: (password, salt) => {
    let shaObj = new jsSHA('SHA-1', 'TEXT')
    shaObj.update(password + salt)
    let hash = shaObj.getHash('HEX')
    shaObj = new jsSHA('SHA-256', 'TEXT', {"numRounds": 5000})
    shaObj.update(hash + salt)
    let hashNext = shaObj.getHash('HEX')
    return hashNext + INTERMediatorLib.stringToHex(salt)
  },
  generatePasswrdHashV2: (password, salt) => {
    let shaObj = new jsSHA('SHA-256', 'TEXT', {"numRounds": 5000})
    shaObj.update(password + salt)
    let hash = shaObj.getHash('HEX')
    return hash + INTERMediatorLib.stringToHex(salt)
  },

  generatePasswordHash: function (password, saltHex = false) {
    let salt = null
    const shaObj = (INTERMediatorOnPage.passwordHash > 1.4 || INTERMediatorOnPage.alwaysGenSHA2)
      ? new jsSHA('SHA-256', 'TEXT', {"numRounds": 5000}) : new jsSHA('SHA-1', 'TEXT')
    if (salt) {
      salt = INTERMediatorLib.hexToString(saltHex)
    } else {
      const [saltValue, saltValueHex] = INTERMediatorLib.generateSalt()
      salt = saltValue
      saltHex = saltValueHex
    }
    shaObj.update(password + salt)
    return encodeURIComponent(shaObj.getHash('HEX') + saltHex)
  },

  generateHexHash: (d, key) => {
    const shaObj = new jsSHA('SHA-256', 'TEXT')
    shaObj.setHMACKey(key, 'TEXT')
    shaObj.update(d)
    return shaObj.getHMAC('HEX')
  },

  stringToHex: (str) => {
    return str.split('').reduce((acc, cur) => {
      return acc + cur.charCodeAt(0).toString(16).padStart(2, '0');
    }, "")
  },

  hexToString: (str) => {
    return str.match(/.{2}/g).reduce((acc, cur) => {
      return acc + String.fromCharCode(parseInt(cur, 16));
    }, "")
  },

  getParentRepeater: function (node) {
    'use strict'
    console.error('INTERMediatorLib.getParentRepeater method in INTER-Mediator-Lib.js will be removed in Ver.6.0. ' +
      'The alternative method is getParentRepeaters.')
    let currentNode = node
    while (currentNode !== null) {
      if (INTERMediatorLib.isRepeater(currentNode, true)) {
        return currentNode
      }
      currentNode = currentNode.parentNode
    }
    return null
  },

  getParentRepeaters: function (node) {
    'use strict'
    if (!node) {
      return null
    }
    let target = '', repeaters = null
    const linkInfo = INTERMediatorLib.getLinkedElementInfo(node)
    if (linkInfo) {
      const linkComp = linkInfo[0].split('@')
      if (linkComp.length > 2) {
        target = linkComp[2]
      }
      const nInfos = IMLibContextPool.getContextInfoFromId(node.id, target)
      if (nInfos) {
        repeaters = nInfos.context.binding[nInfos.record]._im_repeater
      }
    }
    if (!repeaters) {
      repeaters = seekFromContextPool(node)
    }
    const result = []
    if (repeaters) {
      for (let i = 0; i < repeaters.length; i += 1) {
        result.push(document.getElementById(repeaters[i].id))
      }
    }
    return result

    function seekFromContextPool(node) {
      if (!node) {
        return null
      }
      let currentNode = node
      while (currentNode !== null) {
        if (INTERMediatorLib.isRepeater(currentNode, true)) {
          for (let i = 0; i < IMLibContextPool.poolingContexts.length; i++) {
            for (let j in IMLibContextPool.poolingContexts[i].binding) {
              if (IMLibContextPool.poolingContexts[i].binding.hasOwnProperty(j) &&
                IMLibContextPool.poolingContexts[i].binding[j].hasOwnProperty('_im_repeater')) {
                for (let k = 0; k < IMLibContextPool.poolingContexts[i].binding[j]._im_repeater.length; k++) {
                  if (IMLibContextPool.poolingContexts[i].binding[j]._im_repeater[k].id === currentNode.id) {
                    return IMLibContextPool.poolingContexts[i].binding[j]._im_repeater
                  }
                }
              }
            }
          }
        }
        currentNode = currentNode.parentNode
      }
      return null
    }
  },

  getParentEnclosure: function (node) {
    'use strict'
    let currentNode = node
    while (currentNode !== null) {
      if (INTERMediatorLib.isEnclosure(currentNode, true)) {
        return currentNode
      }
      currentNode = currentNode.parentNode
    }
    return null
  },

  isEnclosure: function (node, nodeOnly) {
    'use strict'
    if (!node || node.nodeType !== 1) {
      return false
    }
    const className = node.getAttribute('class')
    if (className && className.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterClassName) >= 0) {
      return false
    }
    const controlAttr = node.getAttribute('data-im-control')
    if (controlAttr && controlAttr.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterControlName) >= 0) {
      return false
    }
    const tagName = node.tagName
    if ((tagName === 'TBODY') ||
      (tagName === 'UL') ||
      (tagName === 'OL') ||
      (tagName === 'SELECT') ||
      ((tagName === 'DIV' || tagName === 'SPAN') &&
        className &&
        className.indexOf(INTERMediatorLib.roleAsEnclosureClassName) >= 0) ||
      (controlAttr &&
        controlAttr.indexOf(INTERMediatorLib.roleAsEnclosureDataControlName) >= 0)) {
      if (nodeOnly) {
        return true
      } else {
        const children = node.childNodes
        for (let k = 0; k < children.length; k++) {
          if (INTERMediatorLib.isRepeater(children[k], true)) {
            return true
          }
        }
      }
    }
    return false
  },

  isRepeater: function (node, nodeOnly) {
    'use strict'
    if (!node || node.nodeType !== 1) {
      return false
    }
    const className = node.getAttribute('class')
    if (className && className.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterClassName) >= 0) {
      return false
    }
    const controlAttr = node.getAttribute('data-im-control')
    if (controlAttr && controlAttr.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterControlName) >= 0) {
      return false
    }
    const tagName = node.tagName
    if ((tagName === 'TR') || (tagName === 'LI') || (tagName === 'OPTION') ||
      (className && className.indexOf(INTERMediatorLib.roleAsRepeaterClassName) >= 0) ||
      (controlAttr && controlAttr.indexOf(INTERMediatorLib.roleAsRepeaterDataControlName) >= 0) ||
      (controlAttr && controlAttr.indexOf(INTERMediatorLib.roleAsSeparatorDataControlName) >= 0) ||
      (controlAttr && controlAttr.indexOf(INTERMediatorLib.roleAsFooterDataControlName) >= 0) ||
      (controlAttr && controlAttr.indexOf(INTERMediatorLib.roleAsHeaderDataControlName) >= 0) ||
      (controlAttr && controlAttr.indexOf(INTERMediatorLib.roleAsNoResultDataControlName) >= 0)
    ) {
      if (nodeOnly) {
        return true
      } else {
        return searchLinkedElement(node)
      }
    }
    return false

    function searchLinkedElement(node) {
      if (INTERMediatorLib.isLinkedElement(node)) {
        return true
      }
      const children = node.childNodes
      for (let k = 0; k < children.length; k++) {
        if (children[k].nodeType === 1) { // Work for an element
          if (INTERMediatorLib.isLinkedElement(children[k])) {
            return true
          } else if (searchLinkedElement(children[k])) {
            return true
          }
        }
      }
      return false
    }
  },

  /**
   * Checking the argument is the Linked Element or not.
   */

  isLinkedElement: function (node) {
    'use strict'
    if (node !== null && node.getAttribute) {
      const attr = node.getAttribute('data-im')
      if (attr) {
        return true
      }
      if (INTERMediator.titleAsLinkInfo) {
        if (node.getAttribute('TITLE') !== null && node.getAttribute('TITLE').length > 0) {
          // IE: If the node doesn't have a title attribute, getAttribute
          // doesn't return null.
          // So it required check if it's empty string.
          return true
        }
      }
      if (INTERMediator.classAsLinkInfo) {
        const classInfo = node.getAttribute('class')
        if (classInfo !== null) {
          const matched = classInfo.match(/IM\[.*\]/)
          if (matched) {
            return true
          }
        }
      }
    }
    return false
  },

  isWidgetElement: function (node) {
    'use strict'
    if (!node) {
      return false
    }
    if (INTERMediatorLib.getLinkedElementInfo(node)) {
      const attr = node.getAttribute('data-im-widget')
      if (attr) {
        return true
      }
      const classInfo = node.getAttribute('class')
      if (classInfo !== null) {
        const matched = classInfo.match(/IM_WIDGET\[.*\]/)
        if (matched) {
          return true
        }
      }
    } else {
      const parentNode = node.parentNode
      if (!parentNode && INTERMediatorLib.getLinkedElementInfoImpl(parentNode)) {
        const attr = parentNode.getAttribute('data-im-widget')
        if (attr) {
          return true
        }
        const classInfo = parentNode.getAttribute('class')
        if (classInfo !== null) {
          const matched = classInfo.match(/IM_WIDGET\[.*\]/)
          if (matched) {
            return true
          }
        }
      }
    }
    return false
  },

  getEnclosureSimple: function (node) {
    'use strict'
    if (INTERMediatorLib.isEnclosure(node, true)) {
      return node
    }
    return INTERMediatorLib.getEnclosureSimple(node.parentNode)
  },

  getEnclosure: function (node) {
    'use strict'
    let detectedRepeater = null
    let currentNode = node
    while (currentNode !== null) {
      if (INTERMediatorLib.isRepeater(currentNode, true)) {
        detectedRepeater = currentNode
      } else if (isRepeaterOfEnclosure(detectedRepeater, currentNode)) {
        detectedRepeater = null
        return currentNode
      }
      currentNode = currentNode.parentNode
    }
    return null

    /**
     * Check the pair of nodes in argument is valid for repeater/enclosure.
     */

    function isRepeaterOfEnclosure(repeater, enclosure) {
      if (!repeater || !enclosure) {
        return false
      }
      const repeaterTag = repeater.tagName
      const enclosureTag = enclosure.tagName
      if ((repeaterTag === 'TR' && enclosureTag === 'TBODY') ||
        (repeaterTag === 'OPTION' && enclosureTag === 'SELECT') ||
        (repeaterTag === 'LI' && enclosureTag === 'OL') ||
        (repeaterTag === 'LI' && enclosureTag === 'UL')) {
        return true
      }
      const enclosureClass = enclosure.getAttribute('class')
      const enclosureDataAttr = enclosure.getAttribute('data-im-control')
      if ((enclosureClass && enclosureClass.indexOf(INTERMediatorLib.roleAsEnclosureClassName) >= 0) ||
        (enclosureDataAttr && enclosureDataAttr.indexOf('enclosure') >= 0)) {
        const repeaterClass = repeater.getAttribute('class')
        const repeaterDataAttr = repeater.getAttribute('data-im-control')
        if ((repeaterClass && repeaterClass.indexOf(INTERMediatorLib.roleAsRepeaterClassName) >= 0) ||
          (repeaterDataAttr && repeaterDataAttr.indexOf(INTERMediatorLib.roleAsRepeaterDataControlName) >= 0) ||
          (repeaterDataAttr && repeaterDataAttr.indexOf(INTERMediatorLib.roleAsSeparatorDataControlName) >= 0) ||
          (repeaterDataAttr && repeaterDataAttr.indexOf(INTERMediatorLib.roleAsFooterDataControlName) >= 0) ||
          (repeaterDataAttr && repeaterDataAttr.indexOf(INTERMediatorLib.roleAsHeaderDataControlName) >= 0) ||
          (repeaterDataAttr && repeaterDataAttr.indexOf(INTERMediatorLib.roleAsNoResultDataControlName) >= 0)
        ) {
          return true
        } else if (repeaterTag === 'INPUT') {
          const repeaterType = repeater.getAttribute('type')
          if (repeaterType &&
            ((repeaterType.indexOf('radio') >= 0 || repeaterType.indexOf('check') >= 0))) {
            return true
          }
        }
      }
      return false
    }
  },

  /**
   * Get the table name / field name information from node as the array of
   * definitions.
   */

  getLinkedElementInfo: function (node) {
    'use strict'
    const result = INTERMediatorLib.getLinkedElementInfoImpl(node)
    if (result !== false) {
      return result
    }
    if (INTERMediatorLib.isWidgetElement(node.parentNode)) {
      return INTERMediatorLib.getLinkedElementInfo(node.parentNode)
    }
    return false
  },

  getLinkedElementInfoImpl: function (node) {
    'use strict'
    let defs = []
    if (INTERMediatorLib.isLinkedElement(node)) {
      let attr = node.getAttribute('data-im')
      if (attr !== null && attr.length > 0) {
        const reg = new RegExp('[\\s' + INTERMediator.defDivider + ']+')
        const eachDefs = attr.split(reg)
        for (let i = 0; i < eachDefs.length; i += 1) {
          if (eachDefs[i] && eachDefs[i].length > 0) {
            defs.push(resolveAlias(eachDefs[i]))
          }
        }
        return defs
      }
      if (INTERMediator.titleAsLinkInfo && node.getAttribute('TITLE')) {
        const eachDefs = node.getAttribute('TITLE').split(INTERMediator.defDivider)
        for (let i = 0; i < eachDefs.length; i += 1) {
          defs.push(resolveAlias(eachDefs[i]))
        }
        return defs
      }
      if (INTERMediator.classAsLinkInfo) {
        attr = node.getAttribute('class')
        if (attr !== null && attr.length > 0) {
          const matched = attr.match(/IM\[([^\]]*)\]/)
          const eachDefs = matched[1].split(INTERMediator.defDivider)
          for (let i = 0; i < eachDefs.length; i += 1) {
            defs.push(resolveAlias(eachDefs[i]))
          }
        }
        return defs
      }
    }
    return false

    function resolveAlias(def) {
      const aliases = INTERMediatorOnPage.getOptionsAliases()
      if (aliases && aliases[def]) {
        return aliases[def]
      }
      return def
    }
  },

  getWidgetInfo: function (node) {
    'use strict'
    const defs = []
    if (INTERMediatorLib.isWidgetElement(node)) {
      let classAttr = node.getAttribute('data-im-widget')
      if (classAttr && classAttr.length > 0) {
        const reg = new RegExp('[\\s' + INTERMediator.defDivider + ']+')
        const eachDefs = classAttr.split(reg)
        for (let i = 0; i < eachDefs.length; i += 1) {
          if (eachDefs[i] && eachDefs[i].length > 0) {
            defs.push(eachDefs[i])
          }
        }
        return defs
      }
      classAttr = node.getAttribute('class')
      if (classAttr && classAttr.length > 0) {
        const matched = classAttr.match(/IM_WIDGET\[([^\]]*)\]/)
        const eachDefs = matched[1].split(INTERMediator.defDivider)
        for (let i = 0; i < eachDefs.length; i += 1) {
          defs.push(eachDefs[i])
        }
        return defs
      }
    }
    return false
  },

  /**
   * Get the repeater tag from the enclosure tag.
   */
  repeaterTagFromEncTag: function (tag) {
    'use strict'
    if (tag === 'TBODY') {
      return 'TR'
    } else if (tag === 'SELECT') {
      return 'OPTION'
    } else if (tag === 'UL') {
      return 'LI'
    } else if (tag === 'OL') {
      return 'LI'
    }
    // else if (tag == 'DIV') return 'DIV'
    // else if (tag == 'SPAN') return 'SPAN'
    return null
  },

  getNodeInfoArray: function (nodeInfo) {
    'use strict'
    if (!nodeInfo || !nodeInfo.split) {
      return {
        'table': null,
        'field': null,
        'target': null,
        'tableindex': null,
        'crossTable': false
      }
    }
    const comps = nodeInfo.split(INTERMediator.separator)
    let tableName = ''
    let fieldName
    let targetName = ''
    if (comps.length === 3) {
      tableName = comps[0]
      fieldName = comps[1]
      targetName = comps[2]
    } else if (comps.length === 2) {
      tableName = comps[0]
      fieldName = comps[1]
    } else {
      fieldName = nodeInfo
    }
    return {
      'table': tableName,
      'field': fieldName,
      'target': targetName,
      'tableindex': '_im_index_' + tableName,
      'crossTable': INTERMediator.crossTableStage === 3
    }
  },

  /**
   * @typedef {Object} IMType_NodeInfo
   * @property {string} field The field name.
   * @property {string} table The context name defined in the relevant definition file.
   * @property {string} target The target information which specified in the 3rd component of target spec.
   * @property {string} tableidnex This is used for FileMaker database's portal expanding.
   */

  /**
   * This method returns the IMType_NodeInfo object of the node specified with the parameter.
   * @param idValue the id attribute of the linked node.
   * @returns {IMType_NodeInfo}
   */
  getCalcNodeInfoArray: function (idValue) {
    'use strict'
    console.error('INTERMediatorLib.getCalcNodeInfoArray method in INTER-Mediator-Page.js will be removed in Ver.6.0. ' +
      'Here is no alternative method.')
    if (!idValue) {
      return null
    }
    const node = document.getElementById(idValue)
    if (!node) {
      return null
    }
    const attribute = node.getAttribute('data-im')
    if (!attribute) {
      return null
    }
    const comps = attribute.split(INTERMediator.separator)
    let tableName = ''
    let fieldName
    let targetName = ''
    if (comps.length === 3) {
      tableName = comps[0]
      fieldName = comps[1]
      targetName = comps[2]
    } else if (comps.length === 2) {
      fieldName = comps[0]
      targetName = comps[1]
    } else {
      fieldName = attribute
    }
    return {
      'table': tableName,
      'field': fieldName,
      'target': targetName,
      'tableindex': '_im_index_' + tableName
    }
  },

  eventInfos: [],

  addEvent: function (node, evt, func) {
    'use strict'
    node.addEventListener(evt, func, evt.match(/touch/) ? {passive: true} : false)
    this.eventInfos.push({'node': node, 'event': evt, 'function': func})
    return this.eventInfos.length - 1
  },

  removeEvent: function (serialId) {
    'use strict'
    if (this.eventInfos[serialId].node.removeEventListener) {
      this.eventInfos[serialId].node.removeEventListener(this.eventInfos[serialId].event, this.eventInfos[serialId].func, false)
    } else if (this.eventInfos[serialId].node.detachEvent) {
      this.eventInfos[serialId].node.detachEvent('on' + this.eventInfos[serialId].event, this.eventInfos[serialId].func)
    }
  },

  // - - - - -

  toNumber: function (str) {
    'use strict'
    let s = ''
    let dp = (INTERMediatorLocale && INTERMediatorLocale.mon_decimal_point) ? INTERMediatorLocale.mon_decimal_point : '.'
    str = str.toString()
    for (let i = 0; i < str.length; i += 1) {
      const c = str.charAt(i)
      if ((c >= '0' && c <= '9') || c === '.' || c === '-' ||
        c === dp) {
        s += c
      } else if (c >= '０' && c <= '９') {
        s += String.fromCharCode(c.charCodeAt(0) - '０'.charCodeAt(0) + '0'.charCodeAt(0))
      }
    }
    return parseFloat(s)
  },

  RoundHalfToEven: function (value, digit) {
    'use strict'
    throw 'RoundHalfToEven method is NOT implemented.'
  },

  /**
   * This method returns the rounded value of the 1st parameter to the 2nd parameter from decimal point.
   * @param {number} value The source value.
   * @param {number} digit Positive number means after the decimal point, and negative means before it.
   * @returns {number}
   */
  Round: function (value, digit) {
    'use strict'
    const powers = Math.pow(10, digit)
    return Math.round(value * powers) / powers
  },

  normalizeNumerics: function (value) {
    'use strict'
    const punc = (INTERMediatorLocale && INTERMediatorLocale.decimal_point) ? INTERMediatorLocale.decimal_point : '.'
    const mpunc = (INTERMediatorLocale && INTERMediatorLocale.mon_decimal_point) ? INTERMediatorLocale.mon_decimal_point : '.'
    let rule = '0123456789'
    if (punc) {
      rule += '\\' + punc
    }
    if (mpunc && mpunc !== punc) {
      rule += '\\' + mpunc
    }
    rule = '[^' + rule + ']'
    value = String(value)
    if (value && value.match(/[０１２３４５６７８９]/)) {
      for (let i = 0; i < 10; i += 1) {
        value = value.split(String.fromCharCode(65296 + i)).join(String(i))
        // Full-width numeric characters start from 0xFF10(65296). This is convert to Full to ASCII char for numeric.
      }
      value = value.replace('．', '.')
    }
    return value ? parseFloat(value.replace(new RegExp(rule, 'g'), '')) : ''
  },

  objectToString: function (obj) {
    'use strict'
    if (obj === null) {
      return 'null'
    }
    const sq = String.fromCharCode(39)
    if (typeof obj === 'object') {
      let str = ''
      if (obj.constructor === Array) {
        for (let i = 0; i < obj.length; i += 1) {
          str += INTERMediatorLib.objectToString(obj[i]) + ', '
        }
        return '[' + str + ']'
      } else {
        for (const key in obj) {
          if (obj.hasOwnProperty(key)) {
            str += sq + key + sq + ':' + INTERMediatorLib.objectToString(obj[key]) + ', '
          }
        }
        return '{' + str + '}'
      }
    } else {
      return sq + obj + sq
    }
  },

  getTargetTableForRetrieve: function (element) {
    'use strict'
    if (element.view !== null) {
      return element.view
    }
    return element.name
  },

  getTargetTableForUpdate: function (element) {
    'use strict'
    if (element.table !== null) {
      return element.table
    }
    return element.name
  },

  getInsertedString: function (tmpStr, dataArray) {
    'use strict'
    let resultStr = tmpStr
    if (dataArray !== null) {
      for (let counter = 1; counter <= dataArray.length; counter++) {
        resultStr = resultStr.replace('@' + counter + '@', dataArray[counter - 1])
      }
    }
    return resultStr
  },

  getInsertedStringFromErrorNumber: function (errNum, dataArray) {
    'use strict'
    const messageArray = INTERMediatorOnPage.getMessages()
    let resultStr = messageArray ? messageArray[errNum] : 'Error:' + errNum
    if (dataArray) {
      for (let counter = 1; counter <= dataArray.length; counter++) {
        resultStr = resultStr.replace('@' + counter + '@', dataArray[counter - 1])
      }
    }
    return resultStr
  },

  getNamedObject: function (obj, key, named) {
    'use strict'
    for (const index in obj) {
      if (obj[index][key] === named) {
        return obj[index]
      }
    }
    return null
  },

  getNamedObjectInObjectArray: function (ar, key, named) {
    'use strict'
    for (let i = 0; i < ar.length; i += 1) {
      if (ar[i][key] === named) {
        return ar[i]
      }
    }
    return null
  },

  getNamedValueInObject: function (ar, key, named, retrieveKey) {
    const result = []
    for (const index in ar) {
      if (ar[index][key] === named) {
        result.push(ar[index][retrieveKey])
      }
    }
    if (result.length === 0) {
      return null
    } else if (result.length === 1) {
      return result[0]
    } else {
      return result
    }
  },

  is_array: function (target) {
    'use strict'
    return target &&
      typeof target === 'object' &&
      typeof target.length === 'number' &&
      typeof target.splice === 'function' &&
      !(target.propertyIsEnumerable('length'))
  },

  getNamedValuesInObject: function (ar, key1, named1, key2, named2, retrieveKey) {
    'use strict'
    const result = []
    for (const index in ar) {
      if (ar.hasOwnProperty(index) && ar[index][key1] === named1 && ar[index][key2] === named2) {
        result.push(ar[index][retrieveKey])
      }
    }
    if (result.length === 0) {
      return null
    } else if (result.length === 1) {
      return result[0]
    } else {
      return result
    }
  },

  getRecordsetFromFieldValueObject: function (obj) {
    'use strict'
    const recordset = {}
    for (const index in obj) {
      if (obj.hasOwnProperty(index)) {
        recordset[obj[index].field] = obj[index].value
      }
    }
    return recordset
  },

  getNodePath: function (node) {
    'use strict'
    if (node.tagName === null) {
      return ''
    } else {
      return INTERMediatorLib.getNodePath(node.parentNode) + '/' + node.tagName
    }
  },

  isPopupMenu: function (element) {
    'use strict'
    if (!element || !element.tagName) {
      return false
    }
    return element.tagName === 'SELECT';
  },

  /*
   If the cNode parameter is like '_im_post', this function will search data-im-control='post' elements.
   */
  getElementsByClassNameOrDataAttr: function (node, cName) {
    'use strict'
    const nodes = []
    const attrValue = (cName.match(/^_im_/)) ? cName.substr(4) : cName
    if (attrValue) {
      checkNode(node)
    }
    return nodes

    function checkNode(target) {
      if (typeof target === 'undefined' || target.nodeType !== 1) {
        return
      }
      let value = target.getAttribute('class')
      if (value) {
        const items = value.split('|')
        for (let i = 0; i < items.length; i += 1) {
          if (items[i] === attrValue) {
            nodes.push(target)
          }
        }
      }
      value = target.getAttribute('data-im-control')
      if (value) {
        const items = value.split(/[| ]/)
        for (let i = 0; i < items.length; i += 1) {
          if (items[i] === attrValue) {
            nodes.push(target)
          }
        }
      }
      value = target.getAttribute('data-im')
      if (value) {
        const items = value.split(/[| ]/)
        for (let i = 0; i < items.length; i += 1) {
          if (items[i] === attrValue) {
            nodes.push(target)
          }
        }
      }
      for (let i = 0; i < target.children.length; i += 1) {
        checkNode(target.children[i])
      }
    }
  },

  getElementsByAttributeValue: function (node, attribute, value) {
    'use strict'
    const nodes = []
    const reg = new RegExp(value)
    checkNode(node)
    return nodes

    function checkNode(target) {
      if (typeof target === 'undefined' || target.nodeType !== 1) {
        return
      }
      const aValue = target.getAttribute(attribute)
      if (aValue && aValue.match(reg)) {
        nodes.push(target)
      }
      for (let i = 0; i < target.children.length; i += 1) {
        checkNode(target.children[i])
      }
    }
  },

  getElementsByClassName: function (node, cName) {
    'use strict'
    const nodes = []
    const reg = new RegExp(cName)
    checkNode(node)
    return nodes

    function checkNode(target) {
      if (typeof target === 'undefined' || target.nodeType !== 1) {
        return
      }
      const className = target.getAttribute('class')
      if (className && className.match(reg)) {
        nodes.push(target)
      }
      for (let i = 0; i < target.children.length; i += 1) {
        checkNode(target.children[i])
      }
    }
  },

  getElementsByIMManaged: function (node) {
    'use strict'
    const nodes = []
    const reg = new RegExp(/^IM/)
    checkNode(node)
    return nodes

    function checkNode(target) {
      if (typeof target === 'undefined' || target.nodeType !== 1) {
        return
      }
      const nodeId = target.getAttribute('id')
      if (nodeId && nodeId.match(reg)) {
        nodes.push(target)
      }
      for (let i = 0; i < target.children.length; i += 1) {
        checkNode(target.children[i])
      }
    }
  },

  seekLinkedAndWidgetNodes: function (nodes, ignoreEnclosureCheck) {
    'use strict'
    const linkedNodesCollection = [] // Collecting linked elements to this array.
    const widgetNodesCollection = []
    let doEncCheck = ignoreEnclosureCheck
    if (typeof ignoreEnclosureCheck === 'undefined' || ignoreEnclosureCheck === null) {
      doEncCheck = false
    }
    for (let i = 0; i < nodes.length; i += 1) {
      seekLinkedElement(nodes[i])
    }
    return {linkedNode: linkedNodesCollection, widgetNode: widgetNodesCollection}

    function seekLinkedElement(node) {
      const nType = node.nodeType
      if (nType === 1) {
        if (INTERMediatorLib.isLinkedElement(node)) {
          const currentEnclosure = doEncCheck ? INTERMediatorLib.getEnclosure(node) : null
          if (currentEnclosure === null) {
            linkedNodesCollection.push(node)
          } else {
            return currentEnclosure
          }
        }
        if (INTERMediatorLib.isWidgetElement(node)) {
          const currentEnclosure = doEncCheck ? INTERMediatorLib.getEnclosure(node) : null
          if (currentEnclosure === null) {
            widgetNodesCollection.push(node)
          } else {
            return currentEnclosure
          }
        }
        const children = node.childNodes
        for (let i = 0; i < children.length; i += 1) {
          seekLinkedElement(children[i])
        }
      }
      return null
    }
  },

  createErrorMessageNode: function (tag, message) {
    'use strict'
    const messageNode = document.createElement(tag)
    messageNode.setAttribute('class', '_im_alertmessage')
    messageNode.appendChild(document.createTextNode(message))
    return messageNode
  },

  removeChildNodes: function (node) {
    'use strict'
    if (node) {
      while (node.firstChild) {
        node.removeChild(node.lastChild)
      }
    }
  },

  removeChildNodesAppendText: function (node, textNum) {
    'use strict'
    if (node) {
      while (node.firstChild) {
        node.removeChild(node.lastChild)
      }
    }
    node.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(textNum)))
  },

  clearErrorMessage: function (node) {
    'use strict'
    if (node) {
      const errorMessages = INTERMediatorLib.getElementsByClassName(node.parentNode, '_im_alertmessage')
      for (let j = 0; j < errorMessages.length; j++) {
        errorMessages[j].parentNode.removeChild(errorMessages[j])
      }
    }
  },

  dateTimeStringISO: function (dt) {
    'use strict'
    dt = (!dt) ? new Date() : dt
    // if (INTERMediatorOnPage.isFollowingTimezone) {
    //   return dt.getUTCFullYear() + '-' + ('0' + (dt.getUTCMonth() + 1)).slice(-2) + '-' +
    //     ('0' + dt.getUTCDate()).slice(-2) + ' ' + ('0' + dt.getUTCHours()).slice(-2) + ':' +
    //     ('0' + dt.getUTCMinutes()).slice(-2) + ':' + ('0' + dt.getUTCSeconds()).slice(-2)
    // }
    return dt.getFullYear() + '-' + ('0' + (dt.getMonth() + 1)).slice(-2) + '-' +
      ('0' + dt.getDate()).slice(-2) + ' ' + ('0' + dt.getHours()).slice(-2) + ':' +
      ('0' + dt.getMinutes()).slice(-2) + ':' + ('0' + dt.getSeconds()).slice(-2)
  },

  dateTimeStringFileMaker: function (dt) {
    'use strict'
    dt = (!dt) ? new Date() : dt
    // if (INTERMediatorOnPage.isFollowingTimezone) {
    //   return ('0' + (dt.getUTCMonth() + 1)).slice(-2) + '/' + ('0' + dt.getUTCDate()).slice(-2) + '/' +
    //     dt.getUTCFullYear() + ' ' + ('0' + dt.getUTCHours()).slice(-2) + ':' +
    //     ('0' + dt.getUTCMinutes()).slice(-2) + ':' + ('0' + dt.getUTCSeconds()).slice(-2)
    // }
    return ('0' + (dt.getMonth() + 1)).slice(-2) + '/' + ('0' + dt.getDate()).slice(-2) + '/' +
      dt.getFullYear() + ' ' + ('0' + dt.getHours()).slice(-2) + ':' +
      ('0' + dt.getMinutes()).slice(-2) + ':' + ('0' + dt.getSeconds()).slice(-2)
  },

  dateStringISO: function (dt) {
    'use strict'
    dt = (!dt) ? new Date() : dt
    // if (INTERMediatorOnPage.isFollowingTimezone) {
    //   return dt.getUTCFullYear() + '-' + ('0' + (dt.getUTCMonth() + 1)).slice(-2) +
    //     '-' + ('0' + dt.getUTCDate()).slice(-2)
    // }
    return dt.getFullYear() + '-' + ('0' + (dt.getMonth() + 1)).slice(-2) +
      '-' + ('0' + dt.getDate()).slice(-2)
  },

  dateStringFileMaker: function (dt) {
    'use strict'
    dt = (!dt) ? new Date() : dt
    // if (INTERMediatorOnPage.isFollowingTimezone) {
    //   return ('0' + (dt.getUTCMonth() + 1)).slice(-2) + '/' +
    //     ('0' + dt.getUTCDate()).slice(-2) + '/' + dt.getUTCFullYear()
    // }
    return ('0' + (dt.getMonth() + 1)).slice(-2) + '/' +
      ('0' + dt.getDate()).slice(-2) + '/' + dt.getFullYear()
  },

  timeString: function (dt) {
    'use strict'
    dt = (!dt) ? new Date() : dt
    return ('0' + dt.getHours()).slice(-2) + ':' +
      ('0' + dt.getMinutes()).slice(-2) + ':' +
      ('0' + dt.getSeconds()).slice(-2)
  },

  mergeURLParameter: function (url, key, value) {
    if (url.indexOf('?') > 0) {
      return `${url}&${key}=${value}`
    } else {
      return `${url}?${key}=${value}`
    }
  }
}

// @@IM@@IgnoringRestOfFile
module.exports = INTERMediatorLib
const IMLibLocalContext = require('../../src/js/INTER-Mediator-LocalContext')
const INTERMediator = require('../../src/js/INTER-Mediator')
const INTERMediatorOnPage = require('../../src/js/INTER-Mediator-Page')
const jsSHA = require('../../node_modules/jssha/dist/sha.js')
