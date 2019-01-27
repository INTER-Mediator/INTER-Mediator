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
var IMLibChangeEventDispatch
var IMLibKeyDownEventDispatch
var IMLibKeyUpEventDispatch
var IMLibInputEventDispatch
var IMLibMouseEventDispatch
var IMLibBlurEventDispatch

function IMLibEventDispatch () {
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
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
var IMLibEventResponder = {
  touchEventCancel: false,

  isSetup: false,

  setup: function () {
    'use strict'
    var body

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
    body = document.getElementsByTagName('BODY')[0]

    INTERMediatorLib.addEvent(body, 'change', function (e) {
      // console.log('Event Dispatcher: change')
      var event = e ? e : window.event
      if (!event) {
        return
      }
      var target = event.target
      if (!target) {
        target = event.srcElement
        if (!target) {
          return
        }
      }
      var idValue = target.id
      if (!idValue) {
        return
      }
      var executable = IMLibChangeEventDispatch.dispatchTable[idValue]
      if (!executable) {
        return
      }
      executable(idValue)
    })
    INTERMediatorLib.addEvent(body, 'blur', function (e) {
      var event = e ? e : window.event
      if (!event) {
        return
      }
      var target = event.target
      if (!target) {
        target = event.srcElement
        if (!target) {
          return
        }
      }
      var idValue = target.id
      if (!idValue) {
        return
      }
      var executable = IMLibBlurEventDispatch.dispatchTable[idValue]
      if (!executable) {
        return
      }
      executable(idValue)
    })
    INTERMediatorLib.addEvent(body, 'input', function (e) {
      var event = e ? e : window.event
      if (!event) {
        return
      }
      var target = event.target
      if (!target) {
        target = event.srcElement
        if (!target) {
          return
        }
      }
      var idValue = target.id
      if (!idValue) {
        return
      }
      var executable = IMLibInputEventDispatch.dispatchTable[idValue]
      if (!executable) {
        return
      }
      executable(idValue)
    })
    INTERMediatorLib.addEvent(body, 'keydown', function (e) {
      var event, target, idValue, keyCode
      event = e ? e : window.event
      if (!event) {
        return
      }
      keyCode = (window.event) ? e.which : e.keyCode
      target = event.target
      if (!target) {
        target = event.srcElement
        if (!target) {
          return
        }
      }
      idValue = target.id
      if (!idValue) {
        return
      }
      if (!IMLibKeyDownEventDispatch.dispatchTable[idValue]) {
        return
      }
      var executable = IMLibKeyDownEventDispatch.dispatchTable[idValue][keyCode]
      if (!executable) {
        return
      }
      executable(event)
    })
    INTERMediatorLib.addEvent(body, 'keyup', function (e) {
      var event, charCode, target, idValue
      event = e ? e : window.event
      if (event.charCode) {
        charCode = event.charCode
      } else {
        charCode = event.keyCode
      }
      if (!event) {
        return
      }
      target = event.target
      if (!target) {
        target = event.srcElement
        if (!target) {
          return
        }
      }
      idValue = target.id
      if (!idValue) {
        return
      }
      if (!IMLibKeyUpEventDispatch.dispatchTable[idValue]) {
        return
      }
      var executable = IMLibKeyUpEventDispatch.dispatchTable[idValue][charCode]
      if (!executable) {
        return
      }
      executable(event)
    })
    INTERMediatorLib.addEvent(body, 'click', function (e) {
      // console.log('Event Dispatcher: click')
      var event, target, idValue, executable, targetDefs, i, nodeInfo, value
      event = e ? e : window.event
      if (!event) {
        return
      }
      target = event.target
      if (!target) {
        target = event.srcElement
        if (!target) {
          return
        }
      }
      idValue = target.id
      if (!idValue) {
        return
      }
      executable = IMLibMouseEventDispatch.dispatchTable[idValue]
      if (executable) {
        executable(event)
        return
      }
      targetDefs = INTERMediatorLib.getLinkedElementInfo(target)
      for (i = 0; i < targetDefs.length; i += 1) {
        executable = IMLibMouseEventDispatch.dispatchTableTarget[targetDefs[i]]
        if (executable) {
          nodeInfo = INTERMediatorLib.getNodeInfoArray(targetDefs[i])
          if (nodeInfo.target) {
            value = target.getAttribute(nodeInfo.target)
          } else {
            value = IMLibElement.getValueFromIMNode(target)
          }
          executable(value, target)
          return
        }
      }
    })
  }
}
// @@IM@@IgnoringRestOfFile
const INTERMediatorLib = require('../../src/js/INTER-Mediator-Lib')
IMLibEventResponder.setup()
module.exports = IMLibChangeEventDispatch
