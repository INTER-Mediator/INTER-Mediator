/**
 * @jest-environment jsdom
 */
/*
 * How to test locally.
 *
 * [Preparation]
 * - Install Node.js locally.
 * - Set the current directory to the INTER-Mediator dirctory.
 * - Execute command 'npm install'
 *
 * [At the start of your development]
 * - Set the current directory to the INTER-Mediator dirctory.
 * - Execute command 'composer jest'
 */

// JSHint support
/* global INTERMediator, INTERMediatorLib, INTERMediatorOnPage, IMLibElement, test, expect */

const INTERMediatorLib = require('../../src/js/INTER-Mediator-Lib')
const INTERMediatorLocale = require('../../node_modules/inter-mediator-locale/index').INTERMediatorLocale

test('repeaterTagFromEncTag() should return \'TR\' if parameter is "TBODY"', function () {
  'use strict'
  expect(INTERMediatorLib.repeaterTagFromEncTag('TBODY')).toBe('TR')
})

test('repeaterTagFromEncTag() should return \'OPTION\' if parameter is "SELECT"', function () {
  'use strict'
  expect(INTERMediatorLib.repeaterTagFromEncTag('SELECT')).toBe('OPTION')
})

test('repeaterTagFromEncTag() should return \'LI\' if parameter is "UL"', function () {
  'use strict'
  expect(INTERMediatorLib.repeaterTagFromEncTag('UL')).toBe('LI')
})

test('repeaterTagFromEncTag() should return \'LI\' if parameter is "OL"', function () {
  'use strict'
  expect(INTERMediatorLib.repeaterTagFromEncTag('OL')).toBe('LI')
})

test('INTERMediatorLib.generatePasswordHash() should generate a valid password hash', function () {
  'use strict'
  //expect(INTERMediatorLib.generatePasswordHash('1234').length).toBe(72)
  expect(INTERMediatorLib.generatePasswordHash('1234').length).toBe(48)
  const [salt, saltHex] = INTERMediatorLib.generateSalt()
  expect(salt.length).toBe(4)
  expect(saltHex.length).toBe(8)
  const hexStr = String.fromCharCode(parseInt(saltHex.substring(0, 2), 16))
    + String.fromCharCode(parseInt(saltHex.substring(2, 4), 16))
    + String.fromCharCode(parseInt(saltHex.substring(4, 6), 16))
    + String.fromCharCode(parseInt(saltHex.substring(6, 8), 16))
  expect(hexStr).toBe(salt)
})

test('INTERMediatorLib.Round() Test for positive value.', function () {
  'use strict'
  expect(INTERMediatorLib.Round(Math.PI, 0)).toBe(3)
  expect(INTERMediatorLib.Round(Math.PI, 1)).toBe(3.1)
  expect(INTERMediatorLib.Round(Math.PI, 2)).toBe(3.14)
  expect(INTERMediatorLib.Round(Math.PI, 3)).toBe(3.142)
})
test('INTERMediatorLib.Round() Test  for negative value.', function () {
  'use strict'
  let v = 45678
  expect(INTERMediatorLib.Round(v, 0)).toBe(v)
  expect(INTERMediatorLib.Round(v, -1)).toBe(45680)
  expect(INTERMediatorLib.Round(v, -2)).toBe(45700)
  expect(INTERMediatorLib.Round(v, -3)).toBe(46000)
  // expect(INTERMediatorLib.Round(v, -4)).toBe(50000) [WIP]
  expect(INTERMediatorLib.Round(v, -5)).toBe(0)
  expect(INTERMediatorLib.Round(v, -6)).toBe(0)
})

test(
  'dateTimeStringISO() should return the valid date time string.', function () {
    'use strict'
    let dt = new Date(2015, 7, 25, 12, 43, 51)
    expect(INTERMediatorLib.dateTimeStringISO(dt)).toBe('2015-08-25 12:43:51')
  })
test('dateTimeStringFileMaker should return the valid date time.', function () {
  'use strict'
  let dt = new Date(2015, 7, 25, 12, 43, 51)
  expect(INTERMediatorLib.dateTimeStringFileMaker(dt)).toBe('08/25/2015 12:43:51')
})
test('dateStringISO should return the valid date.', function () {
  'use strict'
  let dt = new Date(2015, 7, 25, 12, 43, 51)
  expect(INTERMediatorLib.dateStringISO(dt)).toBe('2015-08-25')
})
test(' dateStringFileMakershould return the valid date.', function () {
  'use strict'
  let dt = new Date(2015, 7, 25, 12, 43, 51)
  expect(INTERMediatorLib.dateStringFileMaker(dt)).toBe('08/25/2015')
})
test('timeString should return the valid time.', function () {
  'use strict'
  let dt = new Date(2015, 7, 25, 12, 43, 51)
  expect(INTERMediatorLib.timeString(dt)).toBe('12:43:51')
})

test('INTERMediatorLib.normalizeNumerics(str) should return the numeric characters only numbers.', function () {
  'use strict'
  expect(INTERMediatorLib.normalizeNumerics(0)).toBe(0)
  expect(INTERMediatorLib.normalizeNumerics(99)).toBe(99)
  expect(INTERMediatorLib.normalizeNumerics(120.5)).toBe(120.5)
  expect(INTERMediatorLib.normalizeNumerics('15,236.77')).toBe(15236.77)
  expect(INTERMediatorLib.normalizeNumerics('$15,236.77')).toBe(15236.77)
  expect(INTERMediatorLib.normalizeNumerics('¥15,236.77')).toBe(15236.77)
  expect(INTERMediatorLib.normalizeNumerics('¥15,236.77-')).toBe(15236.77)
  expect(INTERMediatorLib.normalizeNumerics('４３０')).toBe(430)
  expect(INTERMediatorLib.normalizeNumerics('４３０．９９９')).toBe(430.999)
})

test('stringToHex returns valid hex strings.', function () {
  'use strict'
  expect(INTERMediatorLib.stringToHex("1234")).toBe("31323334")
  expect(INTERMediatorLib.stringToHex("abABzZ")).toBe("616241427a5a")
  expect(INTERMediatorLib.stringToHex("C\nC\n")).toBe("430a430a")
})

test('HexToString returns valid strings from hex strings.', function () {
  'use strict'
  expect(INTERMediatorLib.hexToString("31323334")).toBe("1234")
  expect(INTERMediatorLib.hexToString("616241427a5a")).toBe("abABzZ")
  expect(INTERMediatorLib.hexToString("430a430a")).toBe("C\nC\n")
})
