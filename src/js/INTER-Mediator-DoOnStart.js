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
 IMLibEventResponder, IMLibElement, Parser, IMLib, IMLibLocalContext */
/* jshint -W083 */ // Function within a loop

/**
 * @fileoverview This source file should be described statements to execute
 * on the loading time of header's script tag.
 */

INTERMediator.propertyIETridentSetup()
INTERMediator.propertyW3CUserAgentSetup()

INTERMediatorLib.initialize()

Object.defineProperty(INTERMediator, 'startFrom', {
  get: function () {
    'use strict'
    return INTERMediator.getLocalProperty('_im_startFrom', 0)
  },
  set: function (value) {
    'use strict'
    INTERMediator.setLocalProperty('_im_startFrom', value)
  }
})
Object.defineProperty(INTERMediator, 'pagedSize', {
  get: function () {
    'use strict'
    return INTERMediator.getLocalProperty('_im_pagedSize', 0)
  },
  set: function (value) {
    'use strict'
    INTERMediator.setLocalProperty('_im_pagedSize', value)
  }
})
Object.defineProperty(INTERMediator, 'pagination', {
  get: function () {
    'use strict'
    return INTERMediator.getLocalProperty('_im_pagination', 0)
  },
  set: function (value) {
    'use strict'
    INTERMediator.setLocalProperty('_im_pagination', value)
  }
})
Object.defineProperty(INTERMediator, 'additionalCondition', {
  get: function () {
    'use strict'
    return INTERMediator.getLocalProperty('_im_additionalCondition', {})
  },
  set: function (value) {
    'use strict'
    INTERMediator.setLocalProperty('_im_additionalCondition', value)
  }
})
Object.defineProperty(INTERMediator, 'additionalSortKey', {
  get: function () {
    'use strict'
    return INTERMediator.getLocalProperty('_im_additionalSortKey', {})
  },
  set: function (value) {
    'use strict'
    INTERMediator.setLocalProperty('_im_additionalSortKey', value)
  }
})
Object.defineProperty(INTERMediator, 'recordLimit', {
  get: function () {
    'use strict'
    return INTERMediator.getLocalProperty('_im_recordLimit', {})
  },
  set: function (value) {
    'use strict'
    INTERMediator.setLocalProperty('_im_recordLimit', value)
  }
})
Object.defineProperty(IMLibCalc, 'regexpForSeparator', {
  get: function () {
    'use strict'
    if (INTERMediator) {
      return new RegExp(INTERMediator.separator)
    }
    return new RegExp('@')
  }
})

if (!INTERMediator.additionalCondition) {
  INTERMediator.additionalCondition = {}
}

if (!INTERMediator.additionalSortKey) {
  INTERMediator.additionalSortKey = {}
}

if (window) {
  INTERMediatorLib.addEvent(window, 'beforeunload', function (event) {
    if (IMLibQueue.tasks.length > 0) {
      const confirmationMessage = 'Test'
      event.returnValue = confirmationMessage //Gecko + IE
      return confirmationMessage //Webkit, Safari, Chrome etc.
    } else {
      return undefined
    }
  })
  //
  // INTERMediatorLib.addEvent(window, 'unload', function () {
  //   'use strict'
  //   INTERMediator_DBAdapter.unregister()
  // })

  INTERMediatorLib.addEvent(window, 'load', function () {
    'use strict'
    let key, errorNode
    IMLibFormat.isFollowTZ = INTERMediatorOnPage.isFollowingTimezone
    if (INTERMediatorOnPage.initLocalContext) {
      for (key in INTERMediatorOnPage.initLocalContext) {
        if (INTERMediatorOnPage.initLocalContext.hasOwnProperty(key)) {
          IMLibLocalContext.setValue(key, INTERMediatorOnPage.initLocalContext[key], true)
        }
      }
    }
    errorNode = document.getElementById(INTERMediatorOnPage.nonSupportMessageId)

    // if (INTERMediatorOnPage.dbClassName === 'FileMaker_FX') {
    //   INTERMediator_DBAdapter.eliminateDuplicatedConditions = true
    // }

    if (INTERMediatorOnPage.isAutoConstruct) {
      if (errorNode) {
        if (INTERMediatorOnPage.INTERMediatorCheckBrowser(errorNode)) {
          INTERMediator.construct(true)
        }
      } else {
        INTERMediator.construct(true)
      }
    }
  })
}

let IMParts_Catalog = {}
// ****** This file should terminate on the new line. INTER-Mediator adds some codes before here. ****
