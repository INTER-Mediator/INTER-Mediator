/**
 * @jest-environment jsdom
 */
// JSHint support
/* global INTERMediator,buster,INTERMediatorLib,INTERMediatorOnPage,IMLibElement */

// const INTERMediator = require('../../src/js/INTER-Mediator')
const INTERMediator_DBAdapter = require('../../src/js/Adapter_DBServer')
const IMLibContextPool = require("../../src/js/INTER-Mediator-ContextPool");
const IMLibLocalContext = require("../../src/js/INTER-Mediator-LocalContext");
const INTERMediator = require("../../src/js/INTER-Mediator");

beforeEach(() => {
})

afterEach(() => {
})

test('Service method parseLocalContext checking', function () {
  'use strict'
  const args = {
    name: 'account_list'
  }
  IMLibLocalContext.store = {
    'condition:account_list:account_id,parent_account_id:=': "value1",
    'condition:account_list:company,debit_item_name,credit_item_name,description:*match*': "",
    'condition:account_list:company:*match*': "",
    'condition:account_list:issued_date:<=': "value2",
    'condition:account_list:issued_date:>=': "value3",
    'condition:account_list:item_total:<=': "",
    'condition:account_list:item_total:>=': "",
    'limitnumber:account_list': "200",
    'valueofaddorder:account_list:issued_date:asc': 2,
    'valueofaddorder:account_list:issued_date:desc': 1,
    '_@condition:account_list:account_id,parent_account_id:=': "value11",
    '_@condition:account_list:company,debit_item_name,credit_item_name,description:*match*': "",
    '_@condition:account_list:company:*match*': "",
    '_@condition:account_list:issued_date:<=': "value22",
    '_@condition:account_list:issued_date:>=': "",
    '_@condition:account_list:item_total:<=': "",
    '_@condition:account_list:item_total:>=': "",
    '_@limitnumber:account_list': "200",
    '_im_pagedSize': 200,
    '_im_pagination': true,
    '_im_startFrom': 0
  }
  let params = "START"
  let conditions = ['first']
  let extCount = 100;
  let extCountSort = 200;

  [params, extCount, extCountSort] = INTERMediator_DBAdapter.parseLocalContext(args, params, extCount, extCountSort)
  expect(extCount).toBe(104)
  expect(extCountSort).toBe(202)
  expect(conditions.length).toBe(1)
  expect(conditions[0]).toBe('first')
  const expectParams = `START&
condition100field=__operation__&condition100operator=block/F/F/F&
condition101field=account_id%2Cparent_account_id&condition101operator=%3D&condition101value=value1&
condition102field=issued_date&condition102operator=%3C%3D&condition102value=value2&
condition103field=issued_date&condition103operator=%3E%3D&condition103value=value3&
sortkey200field=issued_date&sortkey200direction=desc&
sortkey201field=issued_date&sortkey201direction=asc`.replace(/\n/g, "")
  expect(params).toBe(expectParams)
})

test('Service method parseAdditionalSortParameter checking', function () {
  'use strict'
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

  const contextName = 'account_list'
  INTERMediator.addSortKey(contextName, {field: "issued_date", direction: "asc"})
  INTERMediator.addSortKey(contextName, {field: "item_total", direction: "desc"})
  let params = "START"
  let sortkeyObject = INTERMediator.additionalSortKey[contextName]
  let extCountSort = 200;

  [params, extCountSort]
    = INTERMediator_DBAdapter.parseAdditionalSortParameter(params, sortkeyObject, extCountSort)
  expect(extCountSort).toBe(202)
  const expectParams = `START&
sortkey200field=issued_date&
sortkey200direction=asc&
sortkey201field=item_total&
sortkey201direction=desc`.replace(/\n/g, "")
  expect(params).toBe(expectParams)
})

test('Service method parseAdditionalCriteria checking', function () {
  'use strict'
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

  const contextName = 'account_list'
  INTERMediator.addCondition(contextName, {field: "issued_date", operator: ">=", value: "val1"})
  INTERMediator.addCondition(contextName, {field: "issued_date", operator: "<=", value: "val2"})
  INTERMediator.addCondition(contextName, {field: "issued_date", operator: "=", value: "val3"})
  INTERMediator.addCondition(contextName, {field: "issued_date", operator: "IS NOT NULL"})
  INTERMediator.addCondition(contextName, {field: "total_price", operator: ">", value: "0"})
  let params = "START"
  let conditions = ['first']
  let extCount = 100;

  [params, conditions, extCount] = INTERMediator_DBAdapter.parseAdditionalCriteria(
    params, INTERMediator.additionalCondition[contextName], conditions, extCount)
  expect(extCount).toBe(105)
  expect(conditions.length).toBe(6)
  expect(conditions[0]).toBe('first')
  expect(conditions[1]).toBe('issued_date#>=#val1')
  expect(conditions[4]).toBe('issued_date#IS NOT NULL#')
  const expectParams = `START&
condition100field=issued_date&
condition100operator=%3E%3D&
condition100value=val1&
condition101field=issued_date&
condition101operator=%3C%3D&
condition101value=val2&
condition102field=issued_date&
condition102operator=%3D&
condition102value=val3&
condition103field=issued_date&
condition103operator=IS%20NOT%20NULL&
condition104field=total_price&
condition104operator=%3E&
condition104value=0`.replace(/\n/g, "")
  expect(params).toBe(expectParams)
})

test('checking db_queryParameters with local context conditions', function () {
  'use strict'
  const args = {
    conditions: null,
    fields: ['checkStyle', 'account_id', 'account_id', 'parent_account_id', 'issued_date', 'kind_str', 'attached', 'pattern_name', 'debit_item_name', 'credit_item_name', 'company', 'description', 'item_total', 'alertStyle', 'parent_total', 'net_total', 'tax_total'],
    name: "account_list",
    paging: "1",
    parentkeyvalue: {},
    records: 200,
    uselimit: true,
    useoffset: true,
  }
  IMLibLocalContext.store = {
    "condition:account_list:account_id,parent_account_id:=": "",
    "condition:account_list:issued_date:>=": "2022-03-31",
    "condition:account_list:issued_date:<=": "2022-07-30",
    "condition:account_list:company,debit_item_name,credit_item_name,description:*match*": "ライフマティックス",
    "condition:account_list:item_total:<=": "",
    "condition:account_list:item_total:>=": "",
    "limitnumber:account_list": "200",
    "_@condition:account_list:account_id,parent_account_id:=": "",
    "_@condition:account_list:company,debit_item_name,credit_item_name,description:*match*": "ライフマティックス",
    "_@condition:account_list:issued_date:<=": "2022-07-30",
    "_@condition:account_list:issued_date:>=": "2022-03-31",
    "_@condition:account_list:item_total:<=": "",
    "_@condition:account_list:item_total:>=": "",
    "_@limitnumber:account_list": "200",
    _im_pagedSize: 200,
    _im_pagination: true,
    _im_startFrom: 0
  }
  INTERMediator.startFrom = 0
  INTERMediator.alwaysAddOperationExchange = false
  INTERMediator_DBAdapter.eliminateDuplicatedConditions = false
  const params = `access=read&name=account_list&
field_0=checkStyle&field_1=account_id&field_2=account_id&field_3=parent_account_id&field_4=issued_date&
field_5=kind_str&field_6=attached&field_7=pattern_name&field_8=debit_item_name&field_9=credit_item_name&
field_10=company&field_11=description&field_12=item_total&field_13=alertStyle&field_14=parent_total&
field_15=net_total&field_16=tax_total&
start=0&
records=200&
condition0field=__operation__&condition0operator=block/F/F/F&
condition1field=issued_date&condition1operator=%3E%3D&condition1value=2022-03-31&
condition2field=issued_date&condition2operator=%3C%3D&condition2value=2022-07-30&
condition3field=company%2Cdebit_item_name%2Ccredit_item_name%2Cdescription&condition3operator=*match*&
condition3value=%E3%83%A9%E3%82%A4%E3%83%95%E3%83%9E%E3%83%86%E3%82%A3%E3%83%83%E3%82%AF%E3%82%B9`.replace(/\n/g, "")
  expect(INTERMediator_DBAdapter.db_queryParameters(args)).toBe(params)
})


test('checking db_queryParameters with local context conditions with old global parameter', function () {
  'use strict'
  const args = {
    conditions: null,
    fields: ['checkStyle', 'account_id', 'account_id', 'parent_account_id', 'issued_date', 'kind_str', 'attached', 'pattern_name', 'debit_item_name', 'credit_item_name', 'company', 'description', 'item_total', 'alertStyle', 'parent_total', 'net_total', 'tax_total'],
    name: "account_list",
    paging: "1",
    parentkeyvalue: {},
    records: 200,
    uselimit: true,
    useoffset: true,
  }
  IMLibLocalContext.store = {
    "condition:account_list:account_id,parent_account_id:=": "",
    "condition:account_list:issued_date:>=": "2022-03-31",
    "condition:account_list:issued_date:<=": "2022-07-30",
    "condition:account_list:company,debit_item_name,credit_item_name,description:*match*": "ライフマティックス",
    "condition:account_list:item_total:<=": "",
    "condition:account_list:item_total:>=": "",
    "limitnumber:account_list": "200",
    "_@condition:account_list:account_id,parent_account_id:=": "",
    "_@condition:account_list:company,debit_item_name,credit_item_name,description:*match*": "ライフマティックス",
    "_@condition:account_list:issued_date:<=": "2022-07-30",
    "_@condition:account_list:issued_date:>=": "2022-03-31",
    "_@condition:account_list:item_total:<=": "",
    "_@condition:account_list:item_total:>=": "",
    "_@limitnumber:account_list": "200",
    _im_pagedSize: 200,
    _im_pagination: true,
    _im_startFrom: 0
  }
  INTERMediator.startFrom = 0
  INTERMediator.alwaysAddOperationExchange = true
  INTERMediator_DBAdapter.eliminateDuplicatedConditions = false
  const params = `access=read&name=account_list&
field_0=checkStyle&field_1=account_id&field_2=account_id&field_3=parent_account_id&field_4=issued_date&
field_5=kind_str&field_6=attached&field_7=pattern_name&field_8=debit_item_name&field_9=credit_item_name&
field_10=company&field_11=description&field_12=item_total&field_13=alertStyle&field_14=parent_total&
field_15=net_total&field_16=tax_total&
start=0&
records=200&
condition0field=__operation__&condition0operator=block/F/T/F&
condition1field=issued_date&condition1operator=%3E%3D&condition1value=2022-03-31&
condition2field=issued_date&condition2operator=%3C%3D&condition2value=2022-07-30&
condition3field=company%2Cdebit_item_name%2Ccredit_item_name%2Cdescription&condition3operator=*match*&
condition3value=%E3%83%A9%E3%82%A4%E3%83%95%E3%83%9E%E3%83%86%E3%82%A3%E3%83%83%E3%82%AF%E3%82%B9`.replace(/\n/g, "")
  expect(INTERMediator_DBAdapter.db_queryParameters(args)).toBe(params)
})