// JSHint support
/* global INTERMediator,buster,INTERMediatorLib,INTERMediatorOnPage,IMLibElement */

const INTERMediator = require('../../src/js/INTER-Mediator')

beforeEach(() => {
  INTERMediator.clearCondition('context1')
  INTERMediator.clearCondition('context2')
})

afterEach(() => {
  INTERMediator.clearCondition('context1')
  INTERMediator.clearCondition('context2')
})

test('AdditionalCondition-Add_Clear', function () {
  'use strict'
  INTERMediator.additionalCondition = {}

  INTERMediator.clearCondition('context1')

  INTERMediator.addCondition('context1', {field: 'f1', operator: '=', value: 1})
  expect(INTERMediator.additionalCondition.context1.length).toBe(1)

  INTERMediator.addCondition('context1', {field: 'f2', operator: '=', value: 1})
  expect(INTERMediator.additionalCondition.context1.length).toBe(2)

  INTERMediator.clearCondition('context2')

  INTERMediator.addCondition('context2', {field: 'f1', operator: '=', value: 1})
  expect(INTERMediator.additionalCondition.context1.length).toBe(2)
  expect(INTERMediator.additionalCondition.context2.length).toBe(1)

  INTERMediator.clearCondition('context1')
  expect(INTERMediator.additionalCondition.context1).toBe(undefined)
  expect(INTERMediator.additionalCondition.context2.length).toBe(1)
})

test('AdditionalCondition-Add_Clear_Label', function () {
  'use strict'
  INTERMediator.additionalCondition = {}

  INTERMediator.clearCondition('context1')

  INTERMediator.addCondition('context1', {field: 'f1', operator: '=', value: 1})
  INTERMediator.addCondition('context1', {field: 'f2', operator: '=', value: 1})
  INTERMediator.addCondition('context1', {field: 'f3', operator: '=', value: 1}, undefined, 'label')
  expect(INTERMediator.additionalCondition.context1.length).toBe(3)

  INTERMediator.clearCondition('context1', 'label')
  expect(INTERMediator.additionalCondition.context1.length).toBe(2)

  INTERMediator.clearCondition('context2')

  INTERMediator.addCondition('context2', {field: 'f1', operator: '=', value: 1})
  INTERMediator.addCondition('context2', {field: 'f2', operator: '=', value: 1})
  INTERMediator.addCondition('context2', {field: 'f3', operator: '=', value: 1}, true, 'label')
  INTERMediator.addCondition('context2', {field: 'f4', operator: '=', value: 1}, true, 'label')
  INTERMediator.addCondition('context2', {field: 'f5', operator: '=', value: 1})
  expect(INTERMediator.additionalCondition.context2.length).toBe(5)

  INTERMediator.clearCondition('context2', 'label')
  expect(INTERMediator.additionalCondition.context2.length).toBe(3)
})
