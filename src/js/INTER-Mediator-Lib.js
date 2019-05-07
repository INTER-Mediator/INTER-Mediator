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
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
const IMLib = {
  nl_char: '\n',
  cr_char: '\r',
  tab_char: '\t',
  singleQuote_char: '\'',
  doubleQuote_char: '"',
  backSlash_char: '\\',

  get zerolength_str () {
    'use strict'
    return ''
  },
  set zerolength_str (value) {
    // do nothing
  },

  get crlf_str () {
    'use strict'
    return '\r\n'
  },
  set crlf_str (value) {
    // do nothing
  }
}

/**
 *
 * Usually you don't have to instanciate this class with new operator.
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
    } else if (window.attachEvent) { // for IE
      window.attachEvent('onload', INTERMediatorLib.initialize)
    } else {
      window.onload = INTERMediatorLib.initialize
    }

    return null
  },

  markProcessed: function (node) {
    'use strict'
    node.setAttribute('data-im-element', 'processed')
  },

  isProcessed: function (node) {
    'use strict'
    return node.getAttribute('data-im-element') === 'processed'
  },

  generatePasswordHash: function (password) {
    'use strict'
    var numToHex, salt, saltHex, code, lowCode, highCode, i
    numToHex = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F']
    salt = ''
    saltHex = ''
    for (i = 0; i < 4; i += 1) {
      code = Math.floor(Math.random() * (128 - 32) + 32)
      lowCode = code & 0xF
      highCode = (code >> 4) & 0xF
      salt += String.fromCharCode(code)
      saltHex += numToHex[highCode] + numToHex[lowCode]
    }
    return encodeURIComponent(SHA1(password + salt) + saltHex)
  },

  getParentRepeater: function (node) {
    'use strict'
    console.error('INTERMediatorLib.getParentRepeater method in INTER-Mediator-Lib.js will be removed in Ver.6.0. ' +
      'The alternative method is getParentRepeaters.')
    var currentNode = node
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
    let i, target = '', linkInfo, result = [], linkComp, nInfos, repeaters = null

    if (!node) {
      return null
    }
    linkInfo = INTERMediatorLib.getLinkedElementInfo(node)
    if (linkInfo) {
      linkComp = linkInfo[0].split('@')
      if (linkComp.length > 2) {
        target = linkComp[2]
      }
      nInfos = IMLibContextPool.getContextInfoFromId(node.id, target)
      if (nInfos) {
        repeaters = nInfos.context.binding[nInfos.record]._im_repeater
      }
    }
    if (!repeaters) {
      repeaters = seekFromContextPool(node)
    }
    for (i = 0; i < repeaters.length; i += 1) {
      result.push(document.getElementById(repeaters[i].id))
    }
    return result

    function seekFromContextPool(node) {
      let i, j, k, currentNode;
      if (!node) {
        return null
      }
      currentNode = node
      while (currentNode !== null) {
        if (INTERMediatorLib.isRepeater(currentNode, true)) {
          for (i = 0; i < IMLibContextPool.poolingContexts.length; i++) {
            for (j in IMLibContextPool.poolingContexts[i].binding) {
              if (IMLibContextPool.poolingContexts[i].binding.hasOwnProperty(j) &&
                  IMLibContextPool.poolingContexts[i].binding[j].hasOwnProperty('_im_repeater')) {
                for (k = 0; k < IMLibContextPool.poolingContexts[i].binding[j]._im_repeater.length; k++) {
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
    var currentNode = node
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
    var tagName, className, children, k, controlAttr

    if (!node || node.nodeType !== 1) {
      return false
    }
    className = INTERMediatorLib.getClassAttributeFromNode(node)
    if (className && className.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterClassName) >= 0) {
      return false
    }
    controlAttr = node.getAttribute('data-im-control')
    if (controlAttr && controlAttr.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterControlName) >= 0) {
      return false
    }
    tagName = node.tagName
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
        children = node.childNodes
        for (k = 0; k < children.length; k++) {
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
    var tagName, className, children, k, controlAttr

    if (!node || node.nodeType !== 1) {
      return false
    }
    className = INTERMediatorLib.getClassAttributeFromNode(node)
    if (className && className.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterClassName) >= 0) {
      return false
    }
    controlAttr = node.getAttribute('data-im-control')
    if (controlAttr && controlAttr.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterControlName) >= 0) {
      return false
    }
    tagName = node.tagName
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

    function searchLinkedElement (node) {
      if (INTERMediatorLib.isLinkedElement(node)) {
        return true
      }
      children = node.childNodes
      for (k = 0; k < children.length; k++) {
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
   * Cheking the argument is the Linked Element or not.
   */

  isLinkedElement: function (node) {
    'use strict'
    var classInfo, matched, attr

    if (node !== null && node.getAttribute) {
      attr = node.getAttribute('data-im')
      if (attr) {
        return true
      }
      if (INTERMediator.titleAsLinkInfo) {
        if (node.getAttribute('TITLE') !== null && node.getAttribute('TITLE').length > 0) {
          // IE: If the node doesn't have a title attribute, getAttribute
          // doesn't return null.
          // So it requrired check if it's empty string.
          return true
        }
      }
      if (INTERMediator.classAsLinkInfo) {
        classInfo = INTERMediatorLib.getClassAttributeFromNode(node)
        if (classInfo !== null) {
          matched = classInfo.match(/IM\[.*\]/)
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
    var classInfo, matched, attr, parentNode

    if (!node) {
      return false
    }
    if (INTERMediatorLib.getLinkedElementInfo(node)) {
      attr = node.getAttribute('data-im-widget')
      if (attr) {
        return true
      }
      classInfo = INTERMediatorLib.getClassAttributeFromNode(node)
      if (classInfo !== null) {
        matched = classInfo.match(/IM_WIDGET\[.*\]/)
        if (matched) {
          return true
        }
      }
    } else {
      parentNode = node.parentNode
      if (!parentNode && INTERMediatorLib.getLinkedElementInfoImpl(parentNode)) {
        attr = parentNode.getAttribute('data-im-widget')
        if (attr) {
          return true
        }
        classInfo = INTERMediatorLib.getClassAttributeFromNode(parentNode)
        if (classInfo !== null) {
          matched = classInfo.match(/IM_WIDGET\[.*\]/)
          if (matched) {
            return true
          }
        }
      }
    }
    return false
  },

  isNamedElement: function (node) {
    'use strict'
    var nameInfo, matched

    if (node !== null) {
      nameInfo = node.getAttribute('data-im-group')
      if (nameInfo) {
        return true
      }
      nameInfo = node.getAttribute('name')
      if (nameInfo) {
        matched = nameInfo.match(/IM\[.*\]/)
        if (matched) {
          return true
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
    var currentNode, detectedRepeater

    currentNode = node
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
     * Check the pair of nodes in argument is valid for repater/enclosure.
     */

    function isRepeaterOfEnclosure (repeater, enclosure) {
      var repeaterTag, enclosureTag, enclosureClass, repeaterClass, enclosureDataAttr,
        repeaterDataAttr, repeaterType
      if (!repeater || !enclosure) {
        return false
      }
      repeaterTag = repeater.tagName
      enclosureTag = enclosure.tagName
      if ((repeaterTag === 'TR' && enclosureTag === 'TBODY') ||
        (repeaterTag === 'OPTION' && enclosureTag === 'SELECT') ||
        (repeaterTag === 'LI' && enclosureTag === 'OL') ||
        (repeaterTag === 'LI' && enclosureTag === 'UL')) {
        return true
      }
      enclosureClass = INTERMediatorLib.getClassAttributeFromNode(enclosure)
      enclosureDataAttr = enclosure.getAttribute('data-im-control')
      if ((enclosureClass && enclosureClass.indexOf(INTERMediatorLib.roleAsEnclosureClassName) >= 0) ||
        (enclosureDataAttr && enclosureDataAttr.indexOf('enclosure') >= 0)) {
        repeaterClass = INTERMediatorLib.getClassAttributeFromNode(repeater)
        repeaterDataAttr = repeater.getAttribute('data-im-control')
        if ((repeaterClass && repeaterClass.indexOf(INTERMediatorLib.roleAsRepeaterClassName) >= 0) ||
          (repeaterDataAttr && repeaterDataAttr.indexOf(INTERMediatorLib.roleAsRepeaterDataControlName) >= 0) ||
          (repeaterDataAttr && repeaterDataAttr.indexOf(INTERMediatorLib.roleAsSeparatorDataControlName) >= 0) ||
          (repeaterDataAttr && repeaterDataAttr.indexOf(INTERMediatorLib.roleAsFooterDataControlName) >= 0) ||
          (repeaterDataAttr && repeaterDataAttr.indexOf(INTERMediatorLib.roleAsHeaderDataControlName) >= 0) ||
          (repeaterDataAttr && repeaterDataAttr.indexOf(INTERMediatorLib.roleAsNoResultDataControlName) >= 0)
        ) {
          return true
        } else if (repeaterTag === 'INPUT') {
          repeaterType = repeater.getAttribute('type')
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
    var result = INTERMediatorLib.getLinkedElementInfoImpl(node)
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
    var defs = []
    let eachDefs, reg, i, attr, matched
    if (INTERMediatorLib.isLinkedElement(node)) {
      attr = node.getAttribute('data-im')
      if (attr !== null && attr.length > 0) {
        reg = new RegExp('[\\s' + INTERMediator.defDivider + ']+')
        eachDefs = attr.split(reg)
        for (i = 0; i < eachDefs.length; i += 1) {
          if (eachDefs[i] && eachDefs[i].length > 0) {
            defs.push(resolveAlias(eachDefs[i]))
          }
        }
        return defs
      }
      if (INTERMediator.titleAsLinkInfo && node.getAttribute('TITLE')) {
        eachDefs = node.getAttribute('TITLE').split(INTERMediator.defDivider)
        for (i = 0; i < eachDefs.length; i += 1) {
          defs.push(resolveAlias(eachDefs[i]))
        }
        return defs
      }
      if (INTERMediator.classAsLinkInfo) {
        attr = INTERMediatorLib.getClassAttributeFromNode(node)
        if (attr !== null && attr.length > 0) {
          matched = attr.match(/IM\[([^\]]*)\]/)
          eachDefs = matched[1].split(INTERMediator.defDivider)
          for (i = 0; i < eachDefs.length; i += 1) {
            defs.push(resolveAlias(eachDefs[i]))
          }
        }
        return defs
      }
    }
    return false

    function resolveAlias (def) {
      var aliases = INTERMediatorOnPage.getOptionsAliases()
      if (aliases && aliases[def]) {
        return aliases[def]
      }
      return def
    }
  },

  getWidgetInfo: function (node) {
    'use strict'
    var defs = []
    let eachDefs, i, classAttr, matched, reg
    if (INTERMediatorLib.isWidgetElement(node)) {
      classAttr = node.getAttribute('data-im-widget')
      if (classAttr && classAttr.length > 0) {
        reg = new RegExp('[\\s' + INTERMediator.defDivider + ']+')
        eachDefs = classAttr.split(reg)
        for (i = 0; i < eachDefs.length; i += 1) {
          if (eachDefs[i] && eachDefs[i].length > 0) {
            defs.push(eachDefs[i])
          }
        }
        return defs
      }
      classAttr = INTERMediatorLib.getClassAttributeFromNode(node)
      if (classAttr && classAttr.length > 0) {
        matched = classAttr.match(/IM_WIDGET\[([^\]]*)\]/)
        eachDefs = matched[1].split(INTERMediator.defDivider)
        for (i = 0; i < eachDefs.length; i += 1) {
          defs.push(eachDefs[i])
        }
        return defs
      }
    }
    return false
  },

  getNamedInfo: function (node) {
    'use strict'
    var defs = []
    let eachDefs, i, nameAttr, matched, reg
    if (INTERMediatorLib.isNamedElement(node)) {
      nameAttr = node.getAttribute('data-im-group')
      if (nameAttr && nameAttr.length > 0) {
        reg = new RegExp('[\\s' + INTERMediator.defDivider + ']+')
        eachDefs = nameAttr.split(reg)
        for (i = 0; i < eachDefs.length; i += 1) {
          if (eachDefs[i] && eachDefs[i].length > 0) {
            defs.push(eachDefs[i])
          }
        }
        return defs
      }
      nameAttr = node.getAttribute('name')
      if (nameAttr && nameAttr.length > 0) {
        matched = nameAttr.match(/IM\[([^\]]*)\]/)
        eachDefs = matched[1].split(INTERMediator.defDivider)
        for (i = 0; i < eachDefs.length; i += 1) {
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
    var comps, tableName, fieldName, targetName

    if (!nodeInfo || !nodeInfo.split) {
      return {
        'table': null,
        'field': null,
        'target': null,
        'tableindex': null,
        'crossTable': false
      }
    }
    comps = nodeInfo.split(INTERMediator.separator)
    tableName = ''
    fieldName = ''
    targetName = ''
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

    var comps, tableName, fieldName, targetName, node, attribute

    if (!idValue) {
      return null
    }
    node = document.getElementById(idValue)
    if (!node) {
      return null
    }
    attribute = node.getAttribute('data-im')
    if (!attribute) {
      return null
    }
    comps = attribute.split(INTERMediator.separator)
    tableName = ''
    fieldName = ''
    targetName = ''
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

  /* As for IE7, DOM element can't have any prototype. */

  getClassAttributeFromNode: function (node) {
    'use strict'
    var str = ''
    if (node === null) {
      return ''
    }
    if (INTERMediator.isIE && INTERMediator.ieVersion < 8) {
      str = node.getAttribute('className')
    } else {
      str = node.getAttribute('class')
    }
    return str
  },

  setClassAttributeToNode: function (node, className) {
    'use strict'
    if (node === null) {
      return
    }
    if (INTERMediator.isIE && INTERMediator.ieVersion < 8) {
      node.setAttribute('className', className)
    } else {
      node.setAttribute('class', className)
    }
  },

  /*
   INTER-Mediator supporting browser is over Ver.9 for IE. So this method is already deprecated.
   The eventInfos property doesn't use other than below methods.
   */
  eventInfos: [],

  addEvent: function (node, evt, func) {
    'use strict'
    if (node.addEventListener) {
      node.addEventListener(evt, func, false)
      this.eventInfos.push({'node': node, 'event': evt, 'function': func})
      return this.eventInfos.length - 1
    } else if (node.attachEvent) {
      node.attachEvent('on' + evt, func)
      this.eventInfos.push({'node': node, 'event': evt, 'function': func})
      return this.eventInfos.length - 1
    }
    return -1
  },

  removeEvent: function (serialId) {
    'use strict'
    if (this.eventInfos[serialId].node.removeEventListener) {
      this.eventInfos[serialId].node.removeEventListener(this.eventInfos[serialId].evt, this.eventInfos[serialId].func, false)
    } else if (this.eventInfos[serialId].node.detachEvent) {
      this.eventInfos[serialId].node.detachEvent('on' + this.eventInfos[serialId].evt, this.eventInfos[serialId].func)
    }
  },

  // - - - - -

  toNumber: function (str) {
    'use strict'
    var s = ''
    let i, c
    var dp = INTERMediatorOnPage.localeInfo.mon_decimal_point ? INTERMediatorOnPage.localeInfo.mon_decimal_point : '.'
    str = str.toString()
    for (i = 0; i < str.length; i += 1) {
      c = str.charAt(i)
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
   * @param {integer} digit Positive number means after the decimal point, and negative menas before it.
   * @returns {number}
   */
  Round: function (value, digit) {
    'use strict'
    var powers = Math.pow(10, digit)
    return Math.round(value * powers) / powers
  },

  normalizeNumerics: function (value) {
    'use strict'
    var i
    var punc = INTERMediatorOnPage.localeInfo.decimal_point ? INTERMediatorOnPage.localeInfo.decimal_point : '.'
    var mpunc = INTERMediatorOnPage.localeInfo.mon_decimal_point ? INTERMediatorOnPage.localeInfo.mon_decimal_point : '.'
    var rule = '0123456789'
    if (punc) {
      rule += '\\' + punc
    }
    if (mpunc && mpunc !== punc) {
      rule += '\\' + mpunc
    }
    rule = '[^' + rule + ']'
    value = String(value)
    if (value && value.match(/[０１２３４５６７８９]/)) {
      for (i = 0; i < 10; i += 1) {
        value = value.split(String.fromCharCode(65296 + i)).join(String(i))
        // Full-width numeric characters start from 0xFF10(65296). This is convert to Full to ASCII char for numeric.
      }
      value = value.replace('．', '.')
    }
    return value ? parseFloat(value.replace(new RegExp(rule, 'g'), '')) : ''
  },

  objectToString: function (obj) {
    'use strict'
    var str, i, key
    let sq = String.fromCharCode(39)

    if (obj === null) {
      return 'null'
    }
    if (typeof obj === 'object') {
      str = ''
      if (obj.constructor === Array) {
        for (i = 0; i < obj.length; i += 1) {
          str += INTERMediatorLib.objectToString(obj[i]) + ', '
        }
        return '[' + str + ']'
      } else {
        for (key in obj) {
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

  numberFormat: function (str, digit, flags) {
    'use strict'
    return IMLibFormat.numberFormat(str, digit, flags)
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
    var resultStr, counter

    resultStr = tmpStr
    if (dataArray !== null) {
      for (counter = 1; counter <= dataArray.length; counter++) {
        resultStr = resultStr.replace('@' + counter + '@', dataArray[counter - 1])
      }
    }
    return resultStr
  },

  getInsertedStringFromErrorNumber: function (errNum, dataArray) {
    'use strict'
    var resultStr, counter, messageArray

    messageArray = INTERMediatorOnPage.getMessages()
    resultStr = messageArray ? messageArray[errNum] : 'Error:' + errNum
    if (dataArray) {
      for (counter = 1; counter <= dataArray.length; counter++) {
        resultStr = resultStr.replace('@' + counter + '@', dataArray[counter - 1])
      }
    }
    return resultStr
  },

  getNamedObject: function (obj, key, named) {
    'use strict'
    var index
    for (index in obj) {
      if (obj[index][key] === named) {
        return obj[index]
      }
    }
    return null
  },

  getNamedObjectInObjectArray: function (ar, key, named) {
    'use strict'
    var i
    for (i = 0; i < ar.length; i += 1) {
      if (ar[i][key] === named) {
        return ar[i]
      }
    }
    return null
  },

  getNamedValueInObject: function (ar, key, named, retrieveKey) {
    var result = []
    let index
    for (index in ar) {
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
    var result = []
    let index
    for (index in ar) {
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
    var recordset = {}
    let index
    for (index in obj) {
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
    if (element.tagName === 'SELECT') {
      return true
    }
    return false
  },

  /*
   If the cNode parameter is like '_im_post', this function will search data-im-control='post' elements.
   */
  getElementsByClassNameOrDataAttr: function (node, cName) {
    'use strict'
    var nodes = []
    let attrValue

    attrValue = (cName.match(/^_im_/)) ? cName.substr(4) : cName
    if (attrValue) {
      checkNode(node)
    }
    return nodes

    function checkNode (target) {
      var value, i, items
      if (target === undefined || target.nodeType !== 1) {
        return
      }
      value = INTERMediatorLib.getClassAttributeFromNode(target)
      if (value) {
        items = value.split('|')
        for (i = 0; i < items.length; i += 1) {
          if (items[i] === attrValue) {
            nodes.push(target)
          }
        }
      }
      value = target.getAttribute('data-im-control')
      if (value) {
        items = value.split(/[| ]/)
        for (i = 0; i < items.length; i += 1) {
          if (items[i] === attrValue) {
            nodes.push(target)
          }
        }
      }
      value = target.getAttribute('data-im')
      if (value) {
        items = value.split(/[| ]/)
        for (i = 0; i < items.length; i += 1) {
          if (items[i] === attrValue) {
            nodes.push(target)
          }
        }
      }
      for (i = 0; i < target.children.length; i += 1) {
        checkNode(target.children[i])
      }
    }
  },

  getElementsByAttributeValue: function (node, attribute, value) {
    'use strict'
    var nodes = []
    var reg = new RegExp(value)
    checkNode(node)
    return nodes

    function checkNode (target) {
      var aValue, i
      if (target === undefined || target.nodeType !== 1) {
        return
      }
      aValue = target.getAttribute(attribute)
      if (aValue && aValue.match(reg)) {
        nodes.push(target)
      }
      for (i = 0; i < target.children.length; i += 1) {
        checkNode(target.children[i])
      }
    }
  },

  getElementsByClassName: function (node, cName) {
    'use strict'
    var nodes = []
    var reg = new RegExp(cName)
    checkNode(node)
    return nodes

    function checkNode (target) {
      var className, i
      if (target === undefined || target.nodeType !== 1) {
        return
      }
      className = INTERMediatorLib.getClassAttributeFromNode(target)
      if (className && className.match(reg)) {
        nodes.push(target)
      }
      for (i = 0; i < target.children.length; i += 1) {
        checkNode(target.children[i])
      }
    }
  },

  getElementsByIMManaged: function (node) {
    'use strict'
    var nodes = []
    var reg = new RegExp(/^IM/)
    checkNode(node)
    return nodes

    function checkNode (target) {
      var nodeId, i
      if (target === undefined || target.nodeType !== 1) {
        return
      }
      nodeId = target.getAttribute('id')
      if (nodeId && nodeId.match(reg)) {
        nodes.push(target)
      }
      for (i = 0; i < target.children.length; i += 1) {
        checkNode(target.children[i])
      }
    }
  },

  seekLinkedAndWidgetNodes: function (nodes, ignoreEnclosureCheck) {
    'use strict'
    var linkedNodesCollection = [] // Collecting linked elements to this array.
    var widgetNodesCollection = []
    var i
    let doEncCheck = ignoreEnclosureCheck

    if (ignoreEnclosureCheck === undefined || ignoreEnclosureCheck === null) {
      doEncCheck = false
    }

    for (i = 0; i < nodes.length; i += 1) {
      seekLinkedElement(nodes[i])
    }
    return {linkedNode: linkedNodesCollection, widgetNode: widgetNodesCollection}

    function seekLinkedElement (node) {
      var nType, currentEnclosure, children, i
      nType = node.nodeType
      if (nType === 1) {
        if (INTERMediatorLib.isLinkedElement(node)) {
          currentEnclosure = doEncCheck ? INTERMediatorLib.getEnclosure(node) : null
          if (currentEnclosure === null) {
            linkedNodesCollection.push(node)
          } else {
            return currentEnclosure
          }
        }
        if (INTERMediatorLib.isWidgetElement(node)) {
          currentEnclosure = doEncCheck ? INTERMediatorLib.getEnclosure(node) : null
          if (currentEnclosure === null) {
            widgetNodesCollection.push(node)
          } else {
            return currentEnclosure
          }
        }
        children = node.childNodes
        for (i = 0; i < children.length; i += 1) {
          seekLinkedElement(children[i])
        }
      }
      return null
    }
  },

  createErrorMessageNode: function (tag, message) {
    'use strict'
    var messageNode
    messageNode = document.createElement(tag)
    INTERMediatorLib.setClassAttributeToNode(messageNode, '_im_alertmessage')
    messageNode.appendChild(document.createTextNode(message))
    return messageNode
  },

  removeChildNodes: function (node) {
    'use strict'
    if (node) {
      while (node.childNodes.length > 0) {
        node.removeChild(node.childNodes[0])
      }
    }
  },

  clearErrorMessage: function (node) {
    'use strict'
    var errorMsgs, j
    if (node) {
      errorMsgs = INTERMediatorLib.getElementsByClassName(node.parentNode, '_im_alertmessage')
      for (j = 0; j < errorMsgs.length; j++) {
        errorMsgs[j].parentNode.removeChild(errorMsgs[j])
      }
    }
  },

  dateTimeStringISO: function (dt) {
    'use strict'
    dt = (!dt) ? new Date() : dt
    return dt.getFullYear() + '-' + ('0' + (dt.getMonth() + 1)).substr(-2, 2) + '-' +
      ('0' + dt.getDate()).substr(-2, 2) + ' ' + ('0' + dt.getHours()).substr(-2, 2) + ':' +
      ('0' + dt.getMinutes()).substr(-2, 2) + ':' + ('0' + dt.getSeconds()).substr(-2, 2)
  },

  dateTimeStringFileMaker: function (dt) {
    'use strict'
    dt = (!dt) ? new Date() : dt
    return ('0' + (dt.getMonth() + 1)).substr(-2, 2) + '/' + ('0' + dt.getDate()).substr(-2, 2) + '/' +
      dt.getFullYear() + ' ' + ('0' + dt.getHours()).substr(-2, 2) + ':' +
      ('0' + dt.getMinutes()).substr(-2, 2) + ':' + ('0' + dt.getSeconds()).substr(-2, 2)
  },

  dateStringISO: function (dt) {
    'use strict'
    dt = (!dt) ? new Date() : dt
    return dt.getFullYear() + '-' + ('0' + (dt.getMonth() + 1)).substr(-2, 2) +
      '-' + ('0' + dt.getDate()).substr(-2, 2)
  },

  dateStringFileMaker: function (dt) {
    'use strict'
    dt = (!dt) ? new Date() : dt
    return ('0' + (dt.getMonth() + 1)).substr(-2, 2) + '/' +
      ('0' + dt.getDate()).substr(-2, 2) + '/' + dt.getFullYear()
  },

  timeString: function (dt) {
    'use strict'
    dt = (!dt) ? new Date() : dt
    return ('0' + dt.getHours()).substr(-2, 2) + ':' +
      ('0' + dt.getMinutes()).substr(-2, 2) + ':' +
      ('0' + dt.getSeconds()).substr(-2, 2)
  }
}

// @@IM@@IgnoringRestOfFile
module.exports = INTERMediatorLib
const IMLibLocalContext = require('../../src/js/INTER-Mediator-LocalContext')
const INTERMediator = require('../../src/js/INTER-Mediator')
const INTERMediatorOnPage = require('../../src/js/INTER-Mediator-Page')


