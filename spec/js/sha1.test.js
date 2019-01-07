// JSHint support
/* global SHA1,buster */

const assert = require('power-assert')
const SHA1 = require('../../src/lib/js_lib/tinySHA1')

test('Valid password hash should be generated using SHA1()\'', () => {
  'use strict'
  assert(SHA1('1234').length, 40)
})

test('The result of SHA1() should be SHA-1 based hash', () => {
  'use strict'
  assert(SHA1('1234'), '7110eda4d09e062aa5e4a390b0a572ac0d2c0220')
})
