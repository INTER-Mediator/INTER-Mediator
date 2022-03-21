/**
 * @jest-environment jsdom
 */
// JSHint support
/* global INTERMediator,buster,INTERMediatorLib,INTERMediatorOnPage,IMLibElement */

// const INTERMediator = require('../../src/js/INTER-Mediator')
const INTERMediator_DBAdapter = require('../../src/js/Adapter_DBServer')
const IMLibContextPool = require("../../src/js/INTER-Mediator-ContextPool");
const IMLibLocalContext = require("../../src/js/INTER-Mediator-LocalContext");

beforeEach(() => {
})

afterEach(() => {
})

test('Service method for array checking', function () {
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

  [params, conditions, extCount, extCountSort] = INTERMediator_DBAdapter.parseLocalContext(args, params, conditions, extCount, extCountSort)
  expect(extCount).toBe(105)
  expect(extCountSort).toBe(202)
  expect(conditions.length).toBe(5)
  expect(conditions[0]).toBe('first')
  expect(conditions[1]).toBe('account_id#=#value1')
  expect(conditions[4]).toBe('issued_date#>=#value3')
  const expectParams = `START&
condition100field=__operation__&
condition100operator=ex&
condition101field=account_id&
condition101operator=%3D&
condition101value=value1&
condition102field=parent_account_id&
condition102operator=%3D&
condition102value=value1&
condition103field=issued_date&
condition103operator=%3C%3D&
condition103value=value2&
condition104field=issued_date&
condition104operator=%3E%3D&
condition104value=value3&
sortkey200field=issued_date&
sortkey200direction=desc&
sortkey201field=issued_date&
sortkey201direction=asc`.replace(/\n/g, "")
  expect(params).toBe(expectParams)
})
