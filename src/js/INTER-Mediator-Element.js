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
 IMLibEventResponder, Parser, IMLib, IMLibLocalContext, IMLibFormat, INTERMediatorLog, IMLibInputEventDispatch */
/* jshint -W083 */ // Function within a loop

/**
 * @fileoverview IMLibElement class is defined here.
 */
// @@IM@@IgnoringNextLine
const IMLibFormat = require('../../node_modules/inter-mediator-formatter/index')

/**
 *
 * Usually you don't have to instantiate this class with new operator.
 * @constructor
 */
const IMLibElement = {

  formatters: {
    number: IMLibFormat.decimalFormat,
    currency: IMLibFormat.currencyFormat,
    boolean: IMLibFormat.booleanFormat,
    percent: IMLibFormat.percentFormat,
    date: IMLibFormat.dateFormat,
    datetime: IMLibFormat.datetimeFormat,
    datetimelocal: IMLibFormat.dateTimeLocalFormat,
    time: IMLibFormat.timeFormat,
    timelocal: IMLibFormat.timeFormatLocal
  },

  unformatters: {
    number: IMLibFormat.convertNumeric,
    currency: IMLibFormat.convertNumeric,
    boolean: IMLibFormat.convertBoolean,
    percent: IMLibFormat.convertPercent,
    date: IMLibFormat.convertDate,
    datetime: IMLibFormat.convertDateTime,
    datetimelocal: IMLibFormat.convertDateTimeLocal,
    time: IMLibFormat.convertTime,
    timelocal: IMLibFormat.convertTimeLocal
  },

  formatOptions: {
    'useseparator': {useSeparator: true},
    'blankifzero': {blankIfZero: true},
    'thousands': {upTo3Digits: 3},
    'millions': {upTo3Digits: 6},
    'billions': {upTo3Digits: 9},
  },
  formatNegativeStyle: {
    leadingminus: {negativeStyle: 0},
    'leading-minus': {negativeStyle: 0},
    trailingminus: {negativeStyle: 1},
    'trailing-minus': {negativeStyle: 1},
    parenthesis: {negativeStyle: 2},
    angle: {negativeStyle: 3},
    credit: {negativeStyle: 4},
    triangle: {negativeStyle: 5}
  },
  formatNumeralType: {
    'half-width': {charStyle: 0},
    'full-width': {charStyle: 1},
    'kanji-numeral-modern': {charStyle: 2},
    'kanji-numeral': {charStyle: 3}
  },
  formatKanjiSeparator: {
    'every-4th-place': {kanjiSeparator: 1, useSeparator: true},
    'full-notation': {kanjiSeparator: 2, useSeparator: true}
  },

  appendObject: function (obj, adding) {
    'use strict'
    const result = obj
    if (adding) {
      for (let key in adding) {
        if (adding.hasOwnProperty(key)) {
          result[key] = adding[key]
        }
      }
    }
    return result
  },

  // Formatting values
  //
  initilaizeFlags: function (element) {
    'use strict'
    let flags = {
      useSeparator: false,
      blankIfZero: false,
      negativeStyle: 0,
      charStyle: 0,
      kanjiSeparator: 0,
      upTo3Digits: 0
    }
    const formatOption = element.getAttribute('data-im-format-options')
    if (formatOption) {
      for (const oneOption of formatOption.split(' ')) {
        flags = IMLibElement.appendObject(flags, IMLibElement.formatOptions[oneOption.toLowerCase()])
      }
    }
    const negativeStyle = element.getAttribute('data-im-format-negative-style')
    flags = IMLibElement.appendObject(flags, IMLibElement.formatNegativeStyle[negativeStyle])
    const charStyle = element.getAttribute('data-im-format-numeral-type')
    flags = IMLibElement.appendObject(flags, IMLibElement.formatNumeralType[charStyle])
    const kanjiSeparator = element.getAttribute('data-im-format-kanji-separator')
    flags = IMLibElement.appendObject(flags, IMLibElement.formatKanjiSeparator[kanjiSeparator])
    return flags
  },

  getFormattedValue: function (element, curVal) {
    'use strict'
    const formatSpec = element.getAttribute('data-im-format')
    if (!formatSpec) {
      return null
    }
    const flags = IMLibElement.initilaizeFlags(element)
    let params = 0
    let formatFunc = IMLibElement.formatters[formatSpec.trim().toLocaleLowerCase()] // in case of no parameters in attribute
    if (!formatFunc) {
      const firstParen = formatSpec.indexOf('(')
      const lastParen = formatSpec.lastIndexOf(')')
      const parsed = formatSpec.substr(0, firstParen).match(/[^a-zA-Z]*([a-zA-Z]+).*/)
      formatFunc = IMLibElement.formatters[parsed[1].toLocaleLowerCase()]
      params = formatSpec.substring(firstParen + 1, lastParen)
      if (params.length === 0) { // in case of parameter is just ().
        params = 0
      }
    }
    if (formatFunc) {
      return formatFunc(curVal, params, flags)
    }
    return null
  },

  getUnformattedValue: function (element, value) {
    'use strict'
    const formatSpec = element.getAttribute('data-im-format')
    if (!formatSpec) {
      return null
    }
    let params = null
    const flags = IMLibElement.initilaizeFlags(element)
    let unformatFunc = IMLibElement.unformatters[formatSpec.trim().toLocaleLowerCase()] // in case of no parameters in attribute
    if (!unformatFunc) {
      const firstParen = formatSpec.indexOf('(')
      const lastParen = formatSpec.lastIndexOf(')')
      if (firstParen >= 0 && lastParen >= 0) {
        const parsed = formatSpec.substr(0, firstParen).match(/[^a-zA-Z]*([a-zA-Z]+).*/)
        unformatFunc = IMLibElement.unformatters[parsed[1].toLocaleLowerCase()]
        params = formatSpec.substring(firstParen + 1, lastParen)
      }
    }
    if (unformatFunc) {
      return unformatFunc(value, params, flags)
    }
    return null
  },

  setValueToIMNode: function (element, curTarget, curVal, clearField) {
    'use strict'
    let needPostValueSet = false
    let isReplaceOrAppend = false

    // IE should \r for textNode and <br> for innerHTML, Others is not required to convert

    if (typeof curVal === 'undefined') {
      return false // Or should be an error?
    }
    if (!element) {
      return false // Or should be an error?
    }
    if (curVal === null || curVal === false) {
      curVal = ''
    }
    if (typeof curVal === 'object' && curVal.constructor === Array && curVal.length > 0) {
      curVal = curVal[0]
    }

    const imControl = element.getAttribute('data-im-control')
    if (clearField && curTarget === '') {
      switch (element.tagName) {
        case 'INPUT':
          switch (element.getAttribute('type')) {
            case 'text':
              element.value = ''
              break
          }
          break
        case 'SELECT':
          break
        default:
          while (element.childNodes.length > 0) {
            if (INTERMediatorLib.isProcessed(element.parentNode)) { // for data-im-widget
              return false
            } else if (!INTERMediatorOnPage.updateProcessedNode && INTERMediatorLib.isWidgetElement(element.parentNode)) {
              return false
            }
            element.removeChild(element.childNodes[0])
          }
          break
      }
    }
    const formattedValue = IMLibElement.getFormattedValue(element, curVal)
    if (element.getAttribute('data-im-format')) {
      if (formattedValue === null) {
        INTERMediatorLog.setErrorMessage(
          'The \'data-im-format\' attribute is not valid: ' + element.getAttribute('data-im-format'))
      } else {
        curVal = formattedValue
      }
    }

    curVal = String(curVal)
    const imLocale = element.getAttribute('data-im-locale')
    if (imLocale) {
      const value = INTERMediator.getLocalizedString(`${imLocale}|${curVal}`)
      if (value) {
        curVal = value
      }
    }
    let currentValue
    const negativeColor = element.getAttribute('data-im-format-negative-color')
    if (curTarget !== null && curTarget.length > 0) { // target is specified
      if (curTarget.charAt(0) === '#') { // Appending
        curTarget = curTarget.substring(1)
        const originalValue = element.getAttribute('data-im-original-' + curTarget)
        if (curTarget === 'innerHTML') {
          currentValue = originalValue ? originalValue : element.innerHTML
          element.innerHTML = currentValue + curVal
        } else if (curTarget === 'textNode' || curTarget === 'script') {
          currentValue = originalValue ? originalValue : element.textContent
          element.textContent = currentValue + curVal
        } else if (curTarget.indexOf('style.') === 0) {
          const styleName = curTarget.substring(6, curTarget.length)
          currentValue = originalValue ? originalValue : element.style[styleName]
          if (curTarget !== 'style.color' ||
            (curTarget === 'style.color' && !negativeColor)) {
            element.style[styleName] = currentValue + curVal
          }
        } else {
          currentValue = originalValue ? originalValue : element.getAttribute(curTarget)
          if (curVal.indexOf('/fmi/xml/cnt/') === 0 && currentValue.indexOf('?media=') === -1) {
            curVal = INTERMediatorLib.mergeURLParameter(INTERMediatorOnPage.getEntryPath(), 'media', curVal)
          } else if (curVal.indexOf('https://' + location.hostname + '/Streaming_SSL/MainDB') === 0 &&
            currentValue.indexOf('?media=') === -1) {
            curVal = INTERMediatorLib.mergeURLParameter(INTERMediatorOnPage.getEntryPath(), 'media',
              encodeURIComponent(curVal.replace('https://' + location.hostname, '')))
          }
          element.setAttribute(curTarget, currentValue + curVal)
        }
        isReplaceOrAppend = true
        if (!originalValue) {
          element.setAttribute('data-im-original-' + curTarget, currentValue)
        }
      } else if (curTarget.charAt(0) === '$') { // Replacing
        curTarget = curTarget.substring(1)
        const originalValue = element.getAttribute('data-im-original-' + curTarget)
        if (curTarget === 'innerHTML') {
          currentValue = element.innerHTML
          if (currentValue) {
            curVal = currentValue.replace('$', curVal)
            element.innerHTML = curVal
          }
        } else if (curTarget === 'textNode' || curTarget === 'script') {
          currentValue = element.textContent
          if (currentValue) {
            element.textContent = currentValue.replace('$', curVal)
          }
        } else if (curTarget.indexOf('style.') === 0) {
          const styleName = curTarget.substring(6, curTarget.length)
          currentValue = element.style[styleName]
          if (currentValue && (curTarget !== 'style.color' ||
            (curTarget === 'style.color' && !negativeColor))) {
            element.style[styleName] = currentValue.replace('$', curVal)
          }
        } else {
          currentValue = element.getAttribute(curTarget)
          if (curVal.indexOf('/fmi/xml/cnt/') === 0 && currentValue.indexOf('?media=') === -1) {
            curVal = INTERMediatorLib.mergeURLParameter(INTERMediatorOnPage.getEntryPath(), 'media', curVal)
          } else if (curVal.indexOf('https://' + location.hostname + '/Streaming_SSL/MainDB') === 0 &&
            currentValue.indexOf('?media=') === -1) {
            curVal = INTERMediatorLib.mergeURLParameter(INTERMediatorOnPage.getEntryPath(), 'media',
              curVal.replace('https://' + location.hostname, ''))
          }
          if (currentValue) {
            element.setAttribute(curTarget, currentValue.replace('$', curVal))
          }
        }
        isReplaceOrAppend = true
        if (!originalValue) {
          element.setAttribute('data-im-original-' + curTarget, currentValue)
        }
      } else { // Setting
        if (INTERMediatorLib.isWidgetElement(element)) {
          if (element._im_setValue) {
            element._im_setValue(curVal)
          }
        } else if (curTarget === 'innerHTML') { // Setting
          element.innerHTML = curVal
        } else if (curTarget === 'textNode') {
          const textNode = document.createTextNode(curVal)
          element.appendChild(textNode)
        } else if (curTarget === 'script') {
          const textNode = document.createTextNode(curVal)
          if (element.tagName === 'SCRIPT') {
            element.appendChild(textNode)
          } else {
            const scriptNode = document.createElement('script')
            scriptNode.type = 'text/javascript'
            scriptNode.appendChild(textNode)
            element.appendChild(scriptNode)
          }
        } else if (curTarget.indexOf('style.') === 0) {
          const styleName = curTarget.substring(6, curTarget.length)
          if (curTarget !== 'style.color' ||
            (curTarget === 'style.color' && !negativeColor)) {
            element.style[styleName] = curVal
          }
        } else {
          element.setAttribute(curTarget, curVal)
        }
      }
    } else { // if the 'target' is not specified.
      if (INTERMediatorLib.isWidgetElement(element)) {
        if (element._im_setValue) {
          element._im_setValue(curVal)
        }
      } else if (element.tagName === 'INPUT') {
        IMLibElement.setupSavingTimer(element.id)
        const typeAttr = element.getAttribute('type')
        if (typeAttr === 'checkbox' || typeAttr === 'radio') { // set the value
          const valueAttr = element.value
          let curValues
          if (INTERMediatorOnPage.dbClassName && INTERMediatorOnPage.dbClassName.match(/FileMaker_DataAPI/)) {
            curValues = curVal.split(IMLib.cr_char)
          } else {
            curValues = curVal.split(IMLib.nl_char)
          }
          if (typeAttr === 'checkbox' && curValues.length > 1) {
            for (let i = 0; i < curValues.length; i += 1) {
              if (compareAsNumeric(valueAttr, curValues[i]) && !INTERMediator.dontSelectRadioCheck) {
                // The above operator should be '==' not '==='
                element.checked = true
              }
            }
          } else {
            if (compareAsNumeric(valueAttr, curVal) && !INTERMediator.dontSelectRadioCheck) {
              // The above operator should be '==' not '==='
              element.checked = true
            } else {
              element.checked = false
            }
          }
        } else if (typeAttr === 'date') {
          element.value = !curVal ? "" : IMLibFormat.dateFormat(curVal, '%Y-%M-%D')
        } else if (typeAttr === 'time') {
          element.value = !curVal ? "" : IMLibFormat.timeFormat(curVal, '%H:%I:%S')
        } else if (typeAttr === 'datetime-local') {
          element.value = !curVal ? "" : IMLibFormat.datetimeFormat(curVal, '%Y-%M-%DT%H:%I:%S')
        } else { // this node must be text field
          element.value = curVal
        }
      } else if (element.tagName === 'SELECT') {
        needPostValueSet = true
        element.value = curVal
      } else if (element.tagName === 'TEXTAREA') {
        IMLibElement.setupSavingTimer(element.id)
        if (INTERMediator.defaultTargetInnerHTML) {
          element.innerHTML = curVal
        } else {
          element.value = curVal
        }
      } else { // include option tag node
        if (INTERMediator.defaultTargetInnerHTML) {
          element.innerHTML = curVal
        } else {
          element.appendChild(document.createTextNode(curVal))
        }
      }
    }
    if ((element.tagName === 'INPUT' || element.tagName === 'SELECT' || element.tagName === 'TEXTAREA') &&
      !isReplaceOrAppend &&
      (!imControl || imControl.indexOf('unbind') > 0 || imControl.indexOf('lookup') === 0)) {
      if (!element.getAttribute('data-imbluradded')) {
        INTERMediatorLib.addEvent(element, 'blur', (function () {
          const idValue = element.id
          const elementCapt = element
          return function () {
            if (!IMLibUI.valueChange(idValue, true)) {
              elementCapt.focus()
            }
          }
        })())
        // blur event is NOT excited on other elements, so we can't use IMLibBlurEventDispatch.setExecute.
        element.setAttribute('data-imbluradded', 'set')
      }
      if (!element.getAttribute('data-imchangeadded')) {
        IMLibChangeEventDispatch.setExecute(element.id, (function () {
          const idValue = element.id
          const elementCapt = element
          return function () {
            if (!IMLibUI.valueChange(idValue, false)) {
              elementCapt.focus()
            }
          }
        })())
        element.setAttribute('data-imchangeadded', 'set')
      }
      if ((INTERMediator.isTrident || INTERMediator.isEdge) && !element.getAttribute('data-iminputadded')) {
        IMLibInputEventDispatch.setExecute(element.id, (function () {
          const idValue = element.id
          const elementCapt = element
          return function () {
            if (document.getElementById(idValue).value === '') {
              if (!IMLibUI.valueChange(idValue, false)) {
                elementCapt.focus()
              }
            }
          }
        })())
        element.setAttribute('data-iminputadded', 'set')
      }
    }
    INTERMediatorLib.markProcessed(element)
    return needPostValueSet

    function compareAsNumeric(a, b) {
      const comb = String(a) + String(b)
      for (let c = 0; c < comb.length; c += 1) {
        if ("0123456789.+-".indexOf(comb.substring(c, c + 1)) < 0) { // a or b might not be numeric.
          return a === b
        }
      }
      return Number(a) === Number(b) // don't set the operator ===
    }
  },

  getValueFromIMNode: function (element) {
    'use strict'
    if (!element) {
      return ''
    }
    const nodeTag = element.tagName
    const typeAttr = element.getAttribute('type')
    let newValue
    if (INTERMediatorLib.isWidgetElement(element) ||
      (INTERMediatorLib.isWidgetElement(element.parentNode))) {
      newValue = element._im_getValue()
    } else if (nodeTag === 'INPUT') {
      if (typeAttr === 'checkbox') {
        if (INTERMediatorOnPage.dbClassName.match(/FileMaker_FX/) ||
          INTERMediatorOnPage.dbClassName.match(/FileMaker_DataAPI/)) {
          const mergedValues = []
          const targetNodes = element.parentNode.getElementsByTagName('INPUT')
          for (let k = 0; k < targetNodes.length; k++) {
            if (targetNodes[k].checked) {
              mergedValues.push(targetNodes[k].getAttribute('value'))
            }
          }
          if (INTERMediatorOnPage.dbClassName.match(/FileMaker_DataAPI/)) {
            newValue = mergedValues.join(IMLib.cr_char)
          } else {
            newValue = mergedValues.join(IMLib.nl_char)
          }
        } else {
          const valueAttr = element.getAttribute('value')
          if (element.checked) {
            newValue = valueAttr
          } else {
            newValue = ''
          }
        }
      } else if (typeAttr === 'radio') {
        newValue = element.value
      } else { // text, password
        newValue = element.value
      }
    } else if (nodeTag === 'SELECT') {
      newValue = element.value
    } else if (nodeTag === 'TEXTAREA') {
      newValue = element.value
    } else {
      newValue = element.innerHTML
    }
    const convertedValue = IMLibElement.getUnformattedValue(element, newValue)
    newValue = convertedValue ? convertedValue : newValue
    return newValue
  },

  /*
   <<Multiple lines in TEXTAREA before IE 10>> 2017-08-05, Masayuki Nii

   Most of modern browsers can handle the 'next line(\n)' character as the line separator.
   Otherwise, IE 9 requires special handling for multiple line strings.

   - If such a strings sets to value property, it shows just a single line.
   - To prevent the above situation, it has to replace the line separating characters to <br>,
   and set it to innerHTML property.
   - The value property of multi-line strings doesn't contain any line separating characters.
   - The innerHTML property of multi-line strings contains <br> for line separators.
   - If the value of TEXTAREA can get with replacing <br> to \n from the innerHTML property.

   */

  deleteNodes: function (removeNodes) {
    'use strict'
    for (let key = 0; key < removeNodes.length; key++) {
      const removeNode = document.getElementById(removeNodes[key])
      if (removeNode) {
        const removingNodes = INTERMediatorLib.getElementsByIMManaged(removeNode)
        if (removingNodes) {
          for (let i = 0; i < removingNodes.length; i += 1) {
            const removeNodeId = removingNodes[i].id
            if (removeNodeId in IMLibCalc.calculateRequiredObject) {
              delete IMLibCalc.calculateRequiredObject[removeNodeId]
            }
          }
          for (let i = 0; i < removingNodes.length; i += 1) {
            const removeNodeId = removingNodes[i].id
            for (const nodeId in IMLibCalc.calculateRequiredObject) {
              if (IMLibCalc.calculateRequiredObject.hasOwnProperty(nodeId)) {
                const calcObject = IMLibCalc.calculateRequiredObject[nodeId]
                const referes = {}
                const values = {}
                for (let j in calcObject.referes) {
                  if (calcObject.referes.hasOwnProperty(j)) {
                    referes[j] = []
                    values[j] = []
                    for (let k = 0; k < calcObject.referes[j].length; k++) {
                      if (removeNodeId !== calcObject.referes[j][k]) {
                        referes[j].push(calcObject.referes[j][k])
                        values[j].push(calcObject.values[j][k])
                      }
                    }
                  }
                }
                calcObject.referes = referes
                calcObject.values = values
              }
            }
          }
        }
        try {
          removeNode.parentNode.removeChild(removeNode)
        } catch (ex) {
          // Avoid an error for Safari
        }
      }
    }
  },

  textAutoSave: true, // Public
  editingTargetId: null,
  lastEditDT: null,
  editWatchingTimer: null,
  checkingSeconds: 1, // Public
  waitSeconds: 5, // Public
  //ignoreKeys: ['Tab', 'Enter'],
  isAlreadySaved: false, // Checking within timer process
  isNonTimerSaved: false, // Checking saving in other process

  setupSavingTimer: (elementId) => {
    if (!IMLibElement.textAutoSave) {
      return
    }
    const startWatching = (targetId) => {
      IMLibElement.editingTargetId = targetId
      if (!IMLibElement.editWatchingTimer) {
        IMLibElement.editWatchingTimer = setInterval(IMLibElement.repeatedlyCall, IMLibElement.checkingSeconds * 1000)
      }
    }
    IMLibInputEventDispatch.setExecute(elementId, (targetId) => {
      IMLibElement.lastEditDT = new Date()
      startWatching(targetId)
      IMLibElement.isAlreadySaved = false
      IMLibElement.isNonTimerSaved = false
    })
    IMLibFocusInEventDispatch.setExecute(elementId, (targetId) => {
      IMLibElement.lastEditDT = null
      startWatching(targetId)
    })
    IMLibFocusOutEventDispatch.setExecute(elementId, (targetId) => {
      IMLibElement.isAlreadySaved = false
      IMLibElement.isNonTimerSaved = false
      if (IMLibElement.editingTargetId !== targetId) {
        return
      }
      IMLibElement.editingTargetId = null
      IMLibElement.lastEditDT = null
      clearTimeout(IMLibElement.editWatchingTimer);
      IMLibElement.editWatchingTimer = null
    })
    IMLibKeyUpEventDispatch.setExecute(elementId, (event) => {
      if (event.key === 'Z' && !event.altKey && event.ctrlKey && event.shiftKey) { //Control+Shift+Z
        if (IMLibElement.editingTargetId) {
          const nodeInfo = IMLibContextPool.getContextInfoFromId(IMLibElement.editingTargetId, null)
          if (nodeInfo) {
            nodeInfo.context.backToInitialValue(nodeInfo.record, nodeInfo.field)
          }
        }
      }
    })
  },

  repeatedlyCall: () => {
    if (!IMLibElement.lastEditDT || IMLibElement.isNonTimerSaved) {
      return
    }
    const interval = (new Date()).getTime() - IMLibElement.lastEditDT.getTime()
    if (interval > IMLibElement.waitSeconds * 1000) {
      if (!IMLibElement.editingTargetId) {
        return
      }
      IMLibElement.lastEditDT = null
      const node = document.getElementById(IMLibElement.editingTargetId)
      if (!node) {
        return
      }
      if (IMLibUI.validation(node, true)) {
        IMLibUI.valueChange(node.id)
        IMLibElement.lastEditDT = null
        IMLibElement.isAlreadySaved = true
      }
    }
  },
}

// @@IM@@IgnoringRestOfFile
module.exports = IMLibElement
const IMLib = {nl_char: '\n', cr_char: '\r'}
const INTERMediatorOnPage = require('./INTER-Mediator-Page')
const INTERMediatorLib = require('../../src/js/INTER-Mediator-Lib')
const INTERMediator = require('../../src/js/INTER-Mediator')
const IMLibChangeEventDispatch = require('../../src/js/INTER-Mediator-Events')
const IMLibInputEventDispatch = require('../../src/js/INTER-Mediator-Events')
const IMLibFocusInEventDispatch = require('../../src/js/INTER-Mediator-Events')
const IMLibFocusOutEventDispatch = require('../../src/js/INTER-Mediator-Events')
const IMLibKeyUpEventDispatch = require('../../src/js/INTER-Mediator-Events')
const IMLibKeyDownEventDispatch = require('../../src/js/INTER-Mediator-Events')
