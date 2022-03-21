/**
 * @jest-environment jsdom
 */
// JSHint support
/* global INTERMediator,buster,IMLibLocalContext,IMLibContext,IMLibContextPool */

const IMLibLocalContext = require('../../src/js/INTER-Mediator-LocalContext')
const IMLibContext = require('../../src/js/INTER-Mediator-Context')
const IMLibContextPool = require('../../src/js/INTER-Mediator-ContextPool')
const INTERMediator = require('../../src/js/INTER-Mediator')

test('Local Context Test', () => {
  IMLibLocalContext.setValue('test', 'value')
  expect(IMLibLocalContext.getValue('test')).toBe('value')
  expect(IMLibLocalContext.getValue('unexist-key')).toBe(null)
  IMLibLocalContext.clearAll()
  expect(IMLibLocalContext.getValue('test')).toBe(null)
  IMLibLocalContext.setValue('test1', 'value1')
  IMLibLocalContext.setValue('test2', 'value2')
  IMLibLocalContext.setValue('test3', 'value3')
  IMLibLocalContext.setValue('test4', 'value4')
  IMLibLocalContext.archive()
  IMLibLocalContext.clearAll()
  expect(IMLibLocalContext.getValue('test1')).toBe(null)
  IMLibLocalContext.unarchive()
  expect(IMLibLocalContext.getValue('test1')).toBe('value1')
  expect(IMLibLocalContext.getValue('test2')).toBe('value2')
  expect(IMLibLocalContext.getValue('test3')).toBe('value3')
  expect(IMLibLocalContext.getValue('test4')).toBe('value4')
})

test('Remote Context Test', () => {
  const context1 = new IMLibContext('test')
  context1.setValue('1', 'test', 'value')
  expect(context1.getValue('1', 'test')).toBe('value')
  expect(context1.getValue('2', 'test')).toBe(null)
  expect(context1.getValue('1', 'unexist-key')).toBe(null)
  context1.clearAll()
  expect(context1.getValue('1', 'test')).toBe(null)
  context1.setValue('1', 'test1', 'value1')
  context1.setValue('1', 'test2', 'value2')
  context1.setValue('1', 'test3', 'value3')
  context1.setValue('1', 'test4', 'value4')
  expect(context1.getValue('1', 'test1')).toBe('value1')
  expect(context1.getValue('1', 'test2')).toBe('value2')
  expect(context1.getValue('1', 'test3')).toBe('value3')
  expect(context1.getValue('1', 'test4')).toBe('value4')
})

test('Remote-Context-Test2', () => {
  let i, j
  IMLibContextPool.clearAll()
  const context1 = new IMLibContext('context1')
  expect(IMLibContextPool.poolingContexts.length).toBe(1)
  const context2 = new IMLibContext('context2')
  expect(IMLibContextPool.poolingContexts.length).toBe(2)
  const context3 = new IMLibContext('context3')
  expect(IMLibContextPool.poolingContexts.length).toBe(3)
  const context = [context1, context2, context3]

  let counter = 1
  for (j = 0; j < 3; j++) {
    context[j].setTableName('table')
    context[j].setViewName('table')
    for (i = 1; i < 4; i++) {
      context[j].setValue(i, 'test1', 'value1', 'node-' + (counter++))
      context[j].setValue(i, 'test2', 'value2', 'node-' + (counter++))
      context[j].setValue(i, 'test3', 'value3', 'node-' + (counter++))
      context[j].setValue(i, 'test4', 'value4', 'node-' + (counter++))
    }
  }
  expect(context1.getValue(1, 'test1')).toBe('value1')
  expect(context1.getValue(2, 'test2')).toBe('value2')
  expect(context1.getValue(3, 'test3')).toBe('value3')

  context1.setValue(1, 'test1', 'change1')
  context2.setValue(2, 'test2', 'change2')

  for (j = 0; j < 3; j++) {
    for (i = 1; i < 4; i++) {
      expect(context[j].getValue(i, 'test1')).toBe(i === 1 ? 'change1' : 'value1')
      expect(context[j].getValue(i, 'test2')).toBe(i === 2 ? 'change2' : 'value2')
      expect(context[j].getValue(i, 'test3')).toBe('value3')
      expect(context[j].getValue(i, 'test4')).toBe('value4')
    }
  }
})

test('Remote-Context-Test3', () => {
  const context1 = new IMLibContext('test')
  context1.sequencing = true
  context1.setValue('id=1', 'field1', 10)
  context1.setValue('id=1', 'field2', '500')
  context1.setValue('id=1', 'field3', 'value')
  context1.setValue('id=2', 'field1', 20)
  context1.setValue('id=3', 'field1', 30)
  context1.setValue('id=4', 'field1', 40)
  context1.setValue('id=5', 'field1', 50)
  context1.setValue('id=6', 'field1', 60)
  context1.setValue('id=6', 'field2', 500)
  context1.setValue('id=7', 'field1', 60)
  context1.setValue('id=7', 'field2', 510)
  context1.setValue('id=8', 'field1', 60)
  context1.setValue('id=8', 'field2', 520)
  context1.sequencing = false
  context1.setValue('id=9', 'field1', 25)
  context1.setValue('id=10', 'field1', 45)
  context1.setValue('id=11', 'field1', 9999)
  context1.setValue('id=12', 'field1', -100)
  context1.setValue('id=13', 'field1', 60)
  context1.setValue('id=13', 'field2', 490)
  context1.setValue('id=14', 'field1', 60)
  context1.setValue('id=14', 'field2', 515)
  context1.setValue('id=15', 'field1', 60)
  context1.setValue('id=15', 'field2', 555)
  expect(context1.recordOrder.length).toBe(8)
  expect(context1.pendingOrder.length).toBe(7)
  expect(context1.recordOrder[0]).toBe('id=1')
  expect(context1.recordOrder[5]).toBe('id=6')
  INTERMediator.additionalSortKey = {
    'test': {field: 'test', direction: 'ASC'}
  }
  expect(context1.checkOrder({field1: 45}, true)).toBe(3)
  expect(context1.checkOrder({field1: 50}, true)).toBe(4)
  expect(context1.checkOrder({field1: 55}, true)).toBe(4)
  expect(context1.checkOrder({field1: 60, field2: 505}, true)).toBe(5)
  expect(context1.checkOrder({field1: 60, field2: 515}, true)).toBe(6)
  expect(context1.checkOrder({field1: 60, field2: 99}, true)).toBe(4)
  expect(context1.checkOrder({field1: 60, field2: 999}, true)).toBe(7)
  expect(context1.checkOrder({field1: -1}, true)).toBe(-1)
  expect(context1.checkOrder({field1: 550}, true)).toBe(7)
  //console.log('context1.recordOrder='+context1.recordOrder.toString());
  context1.rearrangePendingOrder(true)
  expect(context1.recordOrder.length).toBe(15)
  expect(Object.keys(context1.store).length).toBe(15)
  expect(context1.pendingOrder.length).toBe(0)
  //console.log('context1.recordOrder='+context1.recordOrder.toString());
  expect(context1.recordOrder[0]).toBe('id=12')
  expect(context1.recordOrder[1]).toBe('id=1')
  expect(context1.recordOrder[2]).toBe('id=2')
  expect(context1.recordOrder[3]).toBe('id=9')
  expect(context1.recordOrder[4]).toBe('id=3')
  expect(context1.recordOrder[5]).toBe('id=4')
  expect(context1.recordOrder[6]).toBe('id=10')
  expect(context1.recordOrder[7]).toBe('id=5')
  expect(context1.recordOrder[8]).toBe('id=13')
  expect(context1.recordOrder[9]).toBe('id=6')
  expect(context1.recordOrder[10]).toBe('id=7')
  expect(context1.recordOrder[11]).toBe('id=14')
  expect(context1.recordOrder[12]).toBe('id=8')
  expect(context1.recordOrder[13]).toBe('id=15')
  expect(context1.recordOrder[14]).toBe('id=11')
})