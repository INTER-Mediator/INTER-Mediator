/**
 * @jest-environment jsdom
 */
// JSHint support
/* global INTERMediator,buster,INTERMediatorLib,INTERMediatorOnPage,IMLibElement */

const INTERMediatorOnPage = require('../../src/js/INTER-Mediator-Page')
const IMLibAuthenticationUI = require('../../src/js/INTER-Mediator-AuthUI')

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

test('IMLibAuthenticationUI\'s password Policy assigned', function () {
  'use strict'
  let policy = '', message

  // No policy returns no error.
  message = IMLibAuthenticationUI.checkPasswordPolicy('', 'username', policy, true)
  expect(message.length).toBe(0)

  // Full policy applied
  policy = 'useAlphabet useNumber useUpper useLower usePunctuation length(10) notUserName'
  message = IMLibAuthenticationUI.checkPasswordPolicy('1234567890', 'username', policy, true)
  expect(message.length).toBe(4)
  message = IMLibAuthenticationUI.checkPasswordPolicy('1234567890a', 'username', policy, true)
  expect(message.length).toBe(2)
  message = IMLibAuthenticationUI.checkPasswordPolicy('1234567890aS', 'username', policy, true)
  expect(message.length).toBe(1)
  message = IMLibAuthenticationUI.checkPasswordPolicy('1234567890aS#', 'username', policy, true)
  expect(message.length).toBe(0)
  message = IMLibAuthenticationUI.checkPasswordPolicy('0aS#', 'username', policy, true)
  expect(message.length).toBe(1)
  message = IMLibAuthenticationUI.checkPasswordPolicy('aaaaaaaS#', 'username', policy, true)
  expect(message.length).toBe(2)
  message = IMLibAuthenticationUI.checkPasswordPolicy('aaaaaaa0S#', 'username', policy, true)
  expect(message.length).toBe(0)

  // Check length
  policy = 'length(4)'
  message = IMLibAuthenticationUI.checkPasswordPolicy('1234', 'username', policy, true)
  expect(message.length).toBe(0)
  message = IMLibAuthenticationUI.checkPasswordPolicy('123', 'username', policy, true)
  expect(message.length).toBe(1)

  // Check notUserName
  policy = 'notUserName'
  message = IMLibAuthenticationUI.checkPasswordPolicy('username', 'username', policy, true)
  expect(message.length).toBe(1)
})
