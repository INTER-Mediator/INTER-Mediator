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
/* global IMLibContextPool, INTERMediator, INTERMediatorOnPage, IMLibMouseEventDispatch, IMLibLocalContext,
 IMLibChangeEventDispatch, INTERMediatorLib, INTERMediator_DBAdapter, IMLibQueue, IMLibCalc, IMLibPageNavigation,
 IMLibEventResponder, IMLibElement, Parser, IMLib, console */

/**
 * @fileoverview INTERMediatorLog class is defined here.
 */

/**
 * Message for Programmers
 * @constructor
 */

const INTERMediatorLog = {
  /**
   * Show the debug messages at the top of the page.
   * @public
   * @type {boolean}
   */
  debugMode: false,
  /**
   * The debug messages are suppressed if it's true. This can temporally stop messages.
   * The default value of false.
   * @public
   * @type {boolean}
   */
  suppressDebugMessageOnPage: false,
  /**
   * The error messages are suppressed if it's true. This can temporally stop messages.
   * The default value of false.
   * @public
   * @type {boolean}
   */
  suppressErrorMessageOnPage: false,
  /**
   * @type {Array}
   */
  errorMessages: [],
  /**
   * @type {Array}
   */
  debugMessages: [],
  /**
   * @type {boolean}
   */
  errorMessageByAlert: false,
  /**
   * @type {boolean}
   */
  errorMessageOnAlert: null,
  /**
   * @type {boolean}
   */
  warningMessagePrevent: false,

  /**
   * Add a debug message with the specified level.
   * @param message The message strings.
   * @param level The level of message.
   */
  setDebugMessage: function (message, level) {
    'use strict'
    if (level === undefined) {
      level = 1
    }
    if (INTERMediatorLog.debugMode >= level) {
      INTERMediatorLog.debugMessages.push(message)
      if (typeof console !== 'undefined') {
        console.log('INTER-Mediator[DEBUG:%s]: %s', new Date(), message)
      }
    }
  },

  setWarningMessage: function (ex) {
    'use strict'
    if (!INTERMediatorLog.warningMessagePrevent) {
      IMLibQueue.setTask((complete) => {
        complete()
        window.alert(ex.join(', '))
      })
    }
    if (typeof console !== 'undefined') {
      console.info('INTER-Mediator[WARNING]:', ex)
    }
  },

  setErrorMessage: function (ex, moreMessage) {
    'use strict'
    moreMessage = moreMessage === undefined ? '' : (' - ' + moreMessage)

    if (INTERMediatorLog.errorMessageByAlert) {
      window.alert(INTERMediatorLog.errorMessageOnAlert === null ? (ex + moreMessage) : INTERMediatorLog.errorMessageOnAlert)
    }

    if ((typeof ex === 'string' || ex instanceof String)) {
      INTERMediatorLog.errorMessages.push(ex + moreMessage)
      if (typeof console !== 'undefined') {
        console.error('INTER-Mediator[ERROR]: %s', ex + moreMessage)
      }
    } else {
      if (ex.message) {
        INTERMediatorLog.errorMessages.push(ex.message + moreMessage)
        if (typeof console !== 'undefined') {
          console.error('INTER-Mediator[ERROR]: %s', ex.message + moreMessage)
        }
      }
      if (ex.stack && typeof console !== 'undefined') {
        console.error(ex.stack)
      }
    }
  },

  flushMessage: function () {
    'use strict'
    if (INTERMediatorLog.errorMessageByAlert) {
      INTERMediatorLog.suppressErrorMessageOnPage = true
    }
    if (!INTERMediatorLog.suppressErrorMessageOnPage &&
      INTERMediatorLog.errorMessages.length > 0) {
      let debugNode = document.getElementById('_im_error_panel_4873643897897')
      if (debugNode === null) {
        debugNode = document.createElement('div')
        debugNode.setAttribute('id', '_im_error_panel_4873643897897')
        debugNode.style.backgroundColor = '#FFDDDD'
        const title = document.createElement('h3')
        title.appendChild(document.createTextNode('Error Info from INTER-Mediator'))
        title.appendChild(document.createElement('hr'))
        debugNode.appendChild(title)
        const body = document.getElementsByTagName('body')[0]
        body.insertBefore(debugNode, body.firstChild)
      }
      debugNode.appendChild(document.createTextNode(
        '============ERROR MESSAGE on ' + new Date() + '============'))
      debugNode.appendChild(document.createElement('hr'))
      for (let i = 0; i < INTERMediatorLog.errorMessages.length; i += 1) {
        const lines = INTERMediatorLog.errorMessages[i].split(IMLib.nl_char)
        for (let j = 0; j < lines.length; j++) {
          if (j > 0) {
            debugNode.appendChild(document.createElement('br'))
          }
          debugNode.appendChild(document.createTextNode(lines[j]))
        }
        debugNode.appendChild(document.createElement('hr'))
      }
    }
    if (!INTERMediatorLog.suppressDebugMessageOnPage &&
      INTERMediatorLog.debugMode &&
      INTERMediatorLog.debugMessages.length > 0) {
      let debugNode = document.getElementById('_im_debug_panel_4873643897897')
      if (debugNode === null) {
        debugNode = document.createElement('div')
        debugNode.setAttribute('id', '_im_debug_panel_4873643897897')
        debugNode.style.backgroundColor = '#DDDDDD'
        const clearButton = document.createElement('button')
        clearButton.setAttribute('title', 'clear')
        clearButton.id = '_im_debug_panel_4873643897897_button'
        IMLibMouseEventDispatch.setExecute(clearButton.id, function () {
          const target = document.getElementById('_im_debug_panel_4873643897897')
          target.parentNode.removeChild(target)
        })
        const tNode = document.createTextNode('clear')
        clearButton.appendChild(tNode)
        const title = document.createElement('h3')
        title.appendChild(document.createTextNode('Debug Info from INTER-Mediator'))
        title.appendChild(clearButton)
        title.appendChild(document.createElement('hr'))
        debugNode.appendChild(title)
        const body = document.getElementsByTagName('body')[0]
        if (body) {
          if (body.firstChild) {
            body.insertBefore(debugNode, body.firstChild)
          } else {
            body.appendChild(debugNode)
          }
        }
      }
      debugNode.appendChild(document.createTextNode(
        '============DEBUG INFO on ' + new Date() + '============ '))
      if (INTERMediatorOnPage.getEditorPath()) {
        const aLink = document.createElement('a')
        aLink.setAttribute('href', INTERMediatorOnPage.getEditorPath())
        aLink.appendChild(document.createTextNode('Definition File Editor'))
        debugNode.appendChild(aLink)
      }
      debugNode.appendChild(document.createElement('hr'))
      for (let i = 0; i < INTERMediatorLog.debugMessages.length; i += 1) {
        const lines = INTERMediatorLog.debugMessages[i].split(IMLib.nl_char)
        for (let j = 0; j < lines.length; j++) {
          if (j > 0) {
            debugNode.appendChild(document.createElement('br'))
          }
          debugNode.appendChild(document.createTextNode(lines[j]))
        }
        debugNode.appendChild(document.createElement('hr'))
      }
    }
    INTERMediatorLog.errorMessages = []
    INTERMediatorLog.debugMessages = []
  }
}

// @@IM@@IgnoringRestOfFile
module.exports = INTERMediatorLog
