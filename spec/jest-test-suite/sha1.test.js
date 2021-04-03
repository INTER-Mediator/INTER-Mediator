// JSHint support
/* global SHA1,buster */

const assert = require('power-assert')
const jsSHA = require('../../node_modules/jssha/dist/sha.js')
const INTERMediatorLib = require('../../src/js/INTER-Mediator-Lib')

test('Valid password hash should be generated using jssha\'', () => {
  'use strict'
  let shaObj = new jsSHA('SHA-1', 'TEXT')
  shaObj.update('1234')
  let hash = shaObj.getHash('HEX')
  assert(hash.length, 40)
})

test('The result of jssha should be SHA-1 based hash', () => {
  'use strict'
  let shaObj = new jsSHA('SHA-1', 'TEXT')
  shaObj.update('1234')
  let hash = shaObj.getHash('HEX')
  assert(hash, '7110eda4d09e062aa5e4a390b0a572ac0d2c0220')
})

test('Valid password hash should be generated using jssha\'', () => {
  'use strict'
  let shaObj = new jsSHA('SHA-256', 'TEXT')
  shaObj.update('1234')
  let hash = shaObj.getHash('HEX')
  assert(hash.length, 64)
})

test('The result of jssha should be SHA-1 based hash', () => {
  'use strict'
  let shaObj = new jsSHA('SHA-256', 'TEXT')
  shaObj.update('1234')
  let hash = shaObj.getHash('HEX')
  assert(hash, 'a883dafc480d466ee04e0d6da986bd78eb1fdd2178d04693723da3a8f95d42f4')
})

test('Valid password hash should be generated using jssha\'', () => {
  'use strict'
  let shaObj = new jsSHA('SHA-256', 'TEXT')
  shaObj.update('1234')
  let hash = shaObj.getHash('HEX')
  assert(hash.length, 64)
})

test('The result of jssha should be SHA-1 based hash', () => {
  'use strict'
  let shaObj = new jsSHA('SHA-256', 'TEXT')
  shaObj.update('1234')
  let hash = shaObj.getHash('HEX')
  assert(hash, 'a883dafc480d466ee04e0d6da986bd78eb1fdd2178d04693723da3a8f95d42f4')
})
