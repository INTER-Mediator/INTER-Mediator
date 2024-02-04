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
/* global INTERMediator, INTERMediatorOnPage, IMLibUI, INTERMediatorLib, INTERMediator_DBAdapter, IMLibQueue,
 IMLibCalc, IMLibPageNavigation, IMLib, IMLibLocalContext, IMLibElement */
/* jshint -W083 */ // Function within a loop

/**
 * @fileoverview IMLibEventResponder class is defined here.
 */
let IMLibChangeEventDispatch = null
let IMLibKeyDownEventDispatch = null
let IMLibKeyUpEventDispatch = null
let IMLibInputEventDispatch = null
let IMLibMouseEventDispatch = null
let IMLibBlurEventDispatch = null
let IMLibFocusInEventDispatch = null
let IMLibFocusOutEventDispatch = null

function IMLibEventDispatch() {
  'use strict'
  this.dispatchTable = {}
  this.dispatchTableTarget = {}
}

IMLibEventDispatch.prototype.clearAll = function () {
  'use strict'
  this.dispatchTable = {}
  this.dispatchTableTarget = {}
}

IMLibEventDispatch.prototype.setExecute = function (idValue, exec) {
  'use strict'
  if (idValue && exec) {
    this.dispatchTable[idValue] = exec
  }
}

IMLibEventDispatch.prototype.setTargetExecute = function (targetValue, exec) {
  'use strict'
  if (targetValue && exec) {
    this.dispatchTableTarget[targetValue] = exec
  }
}

IMLibEventDispatch.prototype.setExecuteByCode = function (idValue, keyCode, exec) {
  'use strict'
  if (idValue && keyCode) {
    if (!this.dispatchTable[idValue]) {
      this.dispatchTable[idValue] = {}
    }
    this.dispatchTable[idValue][keyCode] = exec
  }
}

/**
 *
 * Usually you don't have to instantiate this class with new operator.
 * @constructor
 */
let IMLibEventResponder = {
  touchEventCancel: false,

  isSetup: false,

  setup: function () {
    'use strict'
    let body

    if (IMLibEventResponder.isSetup) {
      return
    }

    IMLibEventResponder.isSetup = true
    IMLibChangeEventDispatch = new IMLibEventDispatch()
    IMLibKeyDownEventDispatch = new IMLibEventDispatch()
    IMLibKeyUpEventDispatch = new IMLibEventDispatch()
    IMLibMouseEventDispatch = new IMLibEventDispatch()
    IMLibBlurEventDispatch = new IMLibEventDispatch()
    IMLibInputEventDispatch = new IMLibEventDispatch()
    IMLibFocusInEventDispatch = new IMLibEventDispatch()
    IMLibFocusOutEventDispatch = new IMLibEventDispatch()
    body = document.getElementsByTagName('BODY')[0]

    INTERMediatorLib.addEvent(body, 'change', function (event) {
      if (event.defaultPrevented) {
        return
      }
      if (!event || !event.target || !event.target.id || !IMLibChangeEventDispatch.dispatchTable[event.target.id]) {
        return
      }
      let executable = IMLibChangeEventDispatch.dispatchTable[event.target.id]
      if (!executable) {
        return
      }
      executable(event.target.id)
    })

    INTERMediatorLib.addEvent(body, 'blur', function (event) {
      if (event.defaultPrevented) {
        return
      }
      if (!event || !event.target || !event.target.id || !IMLibBlurEventDispatch.dispatchTable[event.target.id]) {
        return
      }
      let executable = IMLibBlurEventDispatch.dispatchTable[event.target.id]
      if (!executable) {
        return
      }
      executable(event.target.id)
    })

    INTERMediatorLib.addEvent(body, 'input', function (event) {
      if (event.defaultPrevented) {
        return
      }
      if (!event || !event.target || !event.target.id || !IMLibInputEventDispatch.dispatchTable[event.target.id]) {
        return
      }
      let executable = IMLibInputEventDispatch.dispatchTable[event.target.id]
      if (!executable) {
        return
      }
      executable(event.target.id)
    })

    INTERMediatorLib.addEvent(body, 'keydown', function (event) {
      if (event.defaultPrevented) {
        return
      }
      if (!event || !event.target || !event.target.id || !IMLibKeyDownEventDispatch.dispatchTable[event.target.id]) {
        return
      }
      let executable = IMLibKeyDownEventDispatch.dispatchTable[event.target.id][event.code]
      if (!executable) {
        executable = IMLibKeyDownEventDispatch.dispatchTable[event.target.id]
        if (!executable) {
          return
        }
      }
      executable(event)
    })

    INTERMediatorLib.addEvent(body, 'keyup', function (event) {
      if (event.defaultPrevented) {
        return
      }
      if (!event || !event.target || !event.target.id || !IMLibKeyUpEventDispatch.dispatchTable[event.target.id]) {
        return
      }
      if (!IMLibKeyUpEventDispatch.dispatchTable[event.target.id]) {
        return
      }
      let executable = IMLibKeyUpEventDispatch.dispatchTable[event.target.id][event.code]
      if (!executable) {
        executable = IMLibKeyUpEventDispatch.dispatchTable[event.target.id]
        if (!executable) {
          return
        }
      }
      executable(event)
    })

    INTERMediatorLib.addEvent(body, 'click', function (event) {
      let executable, targetDefs, i, nodeInfo, value
      if (event.defaultPrevented) {
        //return
      }
      if (!event || !event.target || !event.target.id || !IMLibMouseEventDispatch.dispatchTable[event.target.id]) {
        return
      }
      executable = IMLibMouseEventDispatch.dispatchTable[event.target.id]
      if (executable) {
        executable(event)
        return
      }
      targetDefs = INTERMediatorLib.getLinkedElementInfo(event.target)
      for (i = 0; i < targetDefs.length; i += 1) {
        executable = IMLibMouseEventDispatch.dispatchTableTarget[targetDefs[i]]
        if (executable) {
          nodeInfo = INTERMediatorLib.getNodeInfoArray(targetDefs[i])
          if (nodeInfo.target) {
            value = event.target.getAttribute(nodeInfo.target)
          } else {
            value = IMLibElement.getValueFromIMNode(event.target)
          }
          executable(value, event.target)
          return
        }
      }
    })

    INTERMediatorLib.addEvent(body, 'focusin', function (event) {
      if (event.defaultPrevented) {
        return
      }
      if (!event || !event.target || !event.target.id || !IMLibFocusInEventDispatch.dispatchTable[event.target.id]) {
        return
      }
      let executable = IMLibFocusInEventDispatch.dispatchTable[event.target.id]
      if (!executable) {
        return
      }
      executable(event.target.id)
    })

    INTERMediatorLib.addEvent(body, 'focusout', function (event) {
      if (event.defaultPrevented) {
        return
      }
      if (!event || !event.target || !event.target.id || !IMLibFocusOutEventDispatch.dispatchTable[event.target.id]) {
        return
      }
      let executable = IMLibFocusOutEventDispatch.dispatchTable[event.target.id]
      if (!executable) {
        return
      }
      executable(event.target.id)
    })
  }
}
// @@IM@@IgnoringRestOfFile
const INTERMediatorLib = require('../../src/js/INTER-Mediator-Lib')
IMLibEventResponder.setup()
module.exports = IMLibChangeEventDispatch
