/**
 * @jest-environment jsdom
 */
// JSHint support
/* global INTERMediator,buster,INTERMediatorLib,INTERMediatorOnPage,IMLibElement */

const INTERMediatorOnPage = require('../../src/js/INTER-Mediator-Page')

beforeEach(() => {
  INTERMediatorOnPage.getIMRootPath = function () {
    'use strict'
    return '/INTER-Mediator'
  }
  INTERMediatorOnPage.getEntryPath = function () {
    'use strict'
    return '/INTER-Mediator'
  }
  INTERMediatorOnPage.getTheme = function () {
    'use strict'
    return 'default'
  }
})

test('INTERMediatorOnPage.getMessages() should return null', function () {
  'use strict'
  expect(INTERMediatorOnPage.getMessages()).toBe(null)
})

test('INTERMediatorOnPage\'s password Policy assigned', function () {
  'use strict'
  let policy = '', message
  let authFunc = (new INTERMediatorOnPage.authenticating())

  // No policy returns no error.
  message = authFunc.checkPasswordPolicy('', 'username', policy, true)
  expect(message.length).toBe(0)

  // Full policy applied
  policy = 'useAlphabet useNumber useUpper useLower usePunctuation length(10) notUserName'
  message = authFunc.checkPasswordPolicy('1234567890', 'username', policy, true)
  expect(message.length).toBe(4)
  message = authFunc.checkPasswordPolicy('1234567890a', 'username', policy, true)
  expect(message.length).toBe(2)
  message = authFunc.checkPasswordPolicy('1234567890aS', 'username', policy, true)
  expect(message.length).toBe(1)
  message = authFunc.checkPasswordPolicy('1234567890aS#', 'username', policy, true)
  expect(message.length).toBe(0)
  message = authFunc.checkPasswordPolicy('0aS#', 'username', policy, true)
  expect(message.length).toBe(1)
  message = authFunc.checkPasswordPolicy('aaaaaaaS#', 'username', policy, true)
  expect(message.length).toBe(2)
  message = authFunc.checkPasswordPolicy('aaaaaaa0S#', 'username', policy, true)
  expect(message.length).toBe(0)

  // Check length
  policy = 'length(4)'
  message = authFunc.checkPasswordPolicy('1234', 'username', policy, true)
  expect(message.length).toBe(0)
  message = authFunc.checkPasswordPolicy('123', 'username', policy, true)
  expect(message.length).toBe(1)

  // Check notUserName
  policy = 'notUserName'
  message = authFunc.checkPasswordPolicy('username', 'username', policy, true)
  expect(message.length).toBe(1)
})
