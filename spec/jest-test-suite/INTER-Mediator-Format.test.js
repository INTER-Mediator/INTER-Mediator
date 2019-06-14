// JSHint support
/* global INTERMediator,buster,INTERMediatorLib, IMLibFormat,INTERMediatorOnPage,IMLibElement */

const IMLibFormat = require('../../node_modules/inter-mediator-formatter/index')

beforeEach(() => {
  INTERMediatorLocale = {
    'decimal_point': '.',
    'thousands_sep': ',',
    'int_curr_symbol': 'JPY ',
    'currency_symbol': '¥',
    'mon_decimal_point': '.',
    'mon_thousands_sep': ',',
    'positive_sign': '',
    'negative_sign': '-',
    'int_frac_digits': '0',
    'frac_digits': '0',
    'p_cs_precedes': '1',
    'p_sep_by_space': '0',
    'n_cs_precedes': '1',
    'n_sep_by_space': '0',
    'p_sign_posn': '1',
    'n_sign_posn': '4',
    'grouping': {
      '0': '3',
      '1': '3'
    },
    'mon_grouping': {
      '0': '3',
      '1': '3'
    },
    'D_FMT_LONG': '%Y\u5e74%M\u6708%D\u65e5 %W',
    'T_FMT_LONG': '%H\u6642%I\u5206%S\u79d2',
    'D_FMT_MIDDLE': '%Y\/%M\/%D(%w)',
    'T_FMT_MIDDLE': '%H:%I:%S',
    'D_FMT_SHORT': '%Y\/%m\/%d',
    'T_FMT_SHORT': '%H:%I',
    'ABDAY': ['\u65e5', '\u6708', '\u706b', '\u6c34', '\u6728', '\u91d1', '\u571f'],
    'DAY': ['\u65e5\u66dc\u65e5', '\u6708\u66dc\u65e5', '\u706b\u66dc\u65e5', '\u6c34\u66dc\u65e5', '\u6728\u66dc\u65e5', '\u91d1\u66dc\u65e5', '\u571f\u66dc\u65e5'],
    'MON': ['睦月', '如月', '弥生', '卯月', '皐月', '水無月', '文月', '葉月', '長月', '神無月', '霜月', '師走'],
    'ABMON': ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
    'AM_STR': '午前',
    'PM_STR': '午後',
  }
})


test('IMLibFormat.numberFormat(): small integer should not be converted.', function () {
  'use strict'
  expect(IMLibFormat.numberFormat(45, 0)).toBe('45')
  expect(IMLibFormat.numberFormat(45.678, 1)).toBe('45.7')
  expect(IMLibFormat.numberFormat(45.678, 2)).toBe('45.68')
  expect(IMLibFormat.numberFormat(45.678, 3)).toBe('45.678')
  expect(IMLibFormat.numberFormat(45.123, 1)).toBe('45.1')
  expect(IMLibFormat.numberFormat(45.123, 2)).toBe('45.12')
  expect(IMLibFormat.numberFormat(45.123, 3)).toBe('45.123')
})
test('IMLibFormat.numberFormat(): each 3-digits should be devided.', function () {
  'use strict'
  expect(IMLibFormat.numberFormat(999, 0)).toBe('999')
  expect(IMLibFormat.numberFormat(1000, 0)).toBe('1,000')
  expect(IMLibFormat.numberFormat(999999, 0)).toBe('999,999')
  expect(IMLibFormat.numberFormat(1000000, 0)).toBe('1,000,000')
  expect(IMLibFormat.numberFormat(1000000.678, 1)).toBe('1,000,000.7')
  expect(IMLibFormat.numberFormat(1000000.678, 2)).toBe('1,000,000.68')
  expect(IMLibFormat.numberFormat(1000000.678, 3)).toBe('1,000,000.678')
  expect(IMLibFormat.numberFormat(1000000.678, 4)).toBe('1,000,000.6780')
  expect(IMLibFormat.numberFormat(-1000000.678, 1)).toBe('-1,000,000.7')
  expect(IMLibFormat.numberFormat(-1000000.678, 2)).toBe('-1,000,000.68')
  expect(IMLibFormat.numberFormat(-1000000.678, 3)).toBe('-1,000,000.678')
  expect(IMLibFormat.numberFormat(999999, -1)).toBe('1,000,000')
  expect(IMLibFormat.numberFormat(999999, -2)).toBe('1,000,000')
  expect(IMLibFormat.numberFormat(999999, -3)).toBe('1,000,000')
  // A negative second parameter doesn't support so far.
})
test('IMLibFormat.numberFormat(): minus value and having fractions', function () {
  'use strict'
  expect(IMLibFormat.numberFormat(0.5678, 2)).toBe('0.57')
  expect(IMLibFormat.numberFormat(-0.5678, 2)).toBe('-0.57')
})
test('IMLibFormat.numberFormat(): format string detection', function () {
  'use strict'
  expect(INTERMediatorLocale.mon_decimal_point).toBe('.')
  expect(INTERMediatorLocale.mon_thousands_sep).toBe(',')
  expect(INTERMediatorLocale.currency_symbol).toBe('¥')
})


test('IMLibFormat.decimalFormat() Test: small integer should not be converted.', function () {
  'use strict'
  expect(IMLibFormat.decimalFormat(45, 0)).toBe('45')
  expect(IMLibFormat.decimalFormat(45.678, 1)).toBe('45.7')
  expect(IMLibFormat.decimalFormat(45.678, 2)).toBe('45.68')
  expect(IMLibFormat.decimalFormat(45.678, 3)).toBe('45.678')
  expect(IMLibFormat.decimalFormat(45.123, 1)).toBe('45.1')
  expect(IMLibFormat.decimalFormat(45.123, 2)).toBe('45.12')
  expect(IMLibFormat.decimalFormat(45.123, 3)).toBe('45.123')
})
test('IMLibFormat.decimalFormat() Test: each 3-digits should not be devided.', function () {
  'use strict'
  expect(IMLibFormat.decimalFormat(999, 0)).toBe('999')
  expect(IMLibFormat.decimalFormat(1000, 0)).toBe('1000')
  expect(IMLibFormat.decimalFormat(999999, 0)).toBe('999999')
  expect(IMLibFormat.decimalFormat(1000000, 0)).toBe('1000000')
  expect(IMLibFormat.decimalFormat(1000000.678, 1)).toBe('1000000.7')
  expect(IMLibFormat.decimalFormat(1000000.678, 2)).toBe('1000000.68')
  expect(IMLibFormat.decimalFormat(1000000.678, 3)).toBe('1000000.678')
  expect(IMLibFormat.decimalFormat(1000000.678, 4)).toBe('1000000.6780')
  expect(IMLibFormat.decimalFormat(-1000000.678, 1)).toBe('-1000000.7')
  expect(IMLibFormat.decimalFormat(-1000000.678, 2)).toBe('-1000000.68')
  expect(IMLibFormat.decimalFormat(-1000000.678, 3)).toBe('-1000000.678')
  expect(IMLibFormat.decimalFormat(999999, -1)).toBe('1000000')
  expect(IMLibFormat.decimalFormat(999999, -2)).toBe('1000000')
  expect(IMLibFormat.decimalFormat(999999, -3)).toBe('1000000')
})
test('IMLibFormat.decimalFormat() Test: each 3-digits should be devided \'\' if useseperator is enabled.', function () {
  'use strict'
  var flags = {useSeparator: true}
  expect(IMLibFormat.decimalFormat(999, 0, flags)).toBe('999')
  expect(IMLibFormat.decimalFormat(1000, 0, flags)).toBe('1,000')
  expect(IMLibFormat.decimalFormat(999999, 0, flags)).toBe('999,999')
  expect(IMLibFormat.decimalFormat(1000000, 0, flags)).toBe('1,000,000')
  expect(IMLibFormat.decimalFormat(1000000.678, 1, flags)).toBe('1,000,000.7')
  expect(IMLibFormat.decimalFormat(1000000.678, 2, flags)).toBe('1,000,000.68')
  expect(IMLibFormat.decimalFormat(1000000.678, 3, flags)).toBe('1,000,000.678')
  expect(IMLibFormat.decimalFormat(1000000.678, 4, flags)).toBe('1,000,000.6780')
  expect(IMLibFormat.decimalFormat(-1000000.678, 1, flags)).toBe('-1,000,000.7')
  expect(IMLibFormat.decimalFormat(-1000000.678, 2, flags)).toBe('-1,000,000.68')
  expect(IMLibFormat.decimalFormat(-1000000.678, 3, flags)).toBe('-1,000,000.678')
  expect(IMLibFormat.decimalFormat(999999, -1, flags)).toBe('1,000,000')
  expect(IMLibFormat.decimalFormat(999999, -2, flags)).toBe('1,000,000')
  expect(IMLibFormat.decimalFormat(999999, -3, flags)).toBe('1,000,000')
})
test('IMLibFormat.decimalFormat(0) should return \'\' if blankifzero is enabled.', function () {
  'use strict'
  var flags = {blankIfZero: true}
  expect(IMLibFormat.decimalFormat('0', 0, flags)).toBe('')
  expect(IMLibFormat.decimalFormat('1', 0, flags)).toBe('1')
  expect(IMLibFormat.decimalFormat('０', 0, flags)).toBe('')
  expect(IMLibFormat.decimalFormat('0.', 0, flags)).toBe('')
})
test('IMLibFormat.decimalFormat(1) should return \'1\' if blankifzero is enabled.', function () {
  'use strict'
  var flags = {blankIfZero: true}
  expect(IMLibFormat.decimalFormat('1', 0, flags)).toBe('1')
})
test('IMLibFormat.decimalFormat(\'１\', 0, flags) should return \'1\' if charStyle is 0.', function () {
  'use strict'
  var flags = {charStyle: 0}
  expect(IMLibFormat.decimalFormat('１', 0, flags)).toBe('1')
  expect(IMLibFormat.decimalFormat('２', 0, flags)).toBe('2')
  expect(IMLibFormat.decimalFormat('３', 0, flags)).toBe('3')
  expect(IMLibFormat.decimalFormat('４', 0, flags)).toBe('4')
  expect(IMLibFormat.decimalFormat('５', 0, flags)).toBe('5')
  expect(IMLibFormat.decimalFormat('６', 0, flags)).toBe('6')
  expect(IMLibFormat.decimalFormat('７', 0, flags)).toBe('7')
  expect(IMLibFormat.decimalFormat('８', 0, flags)).toBe('8')
  expect(IMLibFormat.decimalFormat('９', 0, flags)).toBe('9')
  expect(IMLibFormat.decimalFormat('０', 0, flags)).toBe('0')
})
test('IMLibFormat.decimalFormat(1, 0, flags) should return \'１\' if charStyle is 1.', function () {
  'use strict'
  var flags = {charStyle: 1}
  expect(IMLibFormat.decimalFormat('1', 0, flags)).toBe('１')
  expect(IMLibFormat.decimalFormat('2', 0, flags)).toBe('２')
  expect(IMLibFormat.decimalFormat('3', 0, flags)).toBe('３')
  expect(IMLibFormat.decimalFormat('4', 0, flags)).toBe('４')
  expect(IMLibFormat.decimalFormat('5', 0, flags)).toBe('５')
  expect(IMLibFormat.decimalFormat('6', 0, flags)).toBe('６')
  expect(IMLibFormat.decimalFormat('8', 0, flags)).toBe('８')
  expect(IMLibFormat.decimalFormat('9', 0, flags)).toBe('９')
  expect(IMLibFormat.decimalFormat('0', 0, flags)).toBe('０')
})
test('IMLibFormat.decimalFormat(1, 0, flags) should return \'一\' if charStyle is 2.', function () {
  'use strict'
  var flags = {charStyle: 2}
  expect(IMLibFormat.decimalFormat('1', 0, flags)).toBe('一')
  expect(IMLibFormat.decimalFormat('2', 0, flags)).toBe('二')
  expect(IMLibFormat.decimalFormat('3', 0, flags)).toBe('三')
  expect(IMLibFormat.decimalFormat('4', 0, flags)).toBe('四')
  expect(IMLibFormat.decimalFormat('5', 0, flags)).toBe('五')
  expect(IMLibFormat.decimalFormat('6', 0, flags)).toBe('六')
  expect(IMLibFormat.decimalFormat('7', 0, flags)).toBe('七')
  expect(IMLibFormat.decimalFormat('8', 0, flags)).toBe('八')
  expect(IMLibFormat.decimalFormat('9', 0, flags)).toBe('九')
  expect(IMLibFormat.decimalFormat('0', 0, flags)).toBe('〇')
})
test('IMLibFormat.decimalFormat(1, 0, flags) should return \'壱\' if charStyle is 3.', function () {
  'use strict'
  var flags = {charStyle: 3}
  expect(IMLibFormat.decimalFormat('1', 0, flags)).toBe('壱')
  expect(IMLibFormat.decimalFormat('2', 0, flags)).toBe('弐')
  expect(IMLibFormat.decimalFormat('3', 0, flags)).toBe('参')
  expect(IMLibFormat.decimalFormat('4', 0, flags)).toBe('四')
  expect(IMLibFormat.decimalFormat('5', 0, flags)).toBe('伍')
  expect(IMLibFormat.decimalFormat('6', 0, flags)).toBe('六')
  expect(IMLibFormat.decimalFormat('7', 0, flags)).toBe('七')
  expect(IMLibFormat.decimalFormat('8', 0, flags)).toBe('八')
  expect(IMLibFormat.decimalFormat('9', 0, flags)).toBe('九')
  expect(IMLibFormat.decimalFormat('0', 0, flags)).toBe('〇')
})
test('IMLibFormat.decimalFormat(12345, 0, flags) should return \'1万2345\' if kanjiSeparator is 1.', function () {
  'use strict'
  var flags = {useSeparator: true, kanjiSeparator: 1}
  expect(IMLibFormat.decimalFormat('12345', 0, flags)).toBe('1万2345')
  expect(IMLibFormat.decimalFormat('1234567800000000', 0, flags)).toBe('1234兆5678億')
  expect(IMLibFormat.decimalFormat('1234567800000009', 0, flags)).toBe('1234兆5678億9')
  expect(IMLibFormat.decimalFormat('1234567800010009', 0, flags)).toBe('1234兆5678億1万9')
})
test('IMLibFormat.decimalFormat(12345, 0, flags) should return \'1万2千3百4十5\' if kanjiSeparator is 2.', function () {
  'use strict'
  var flags = {useSeparator: true, kanjiSeparator: 2}
  expect(IMLibFormat.decimalFormat('12345', 0, flags)).toBe('1万2千3百4十5')
  expect(IMLibFormat.decimalFormat('1234567800000000', 0, flags)).toBe('千2百3十4兆5千6百7十8億')
  expect(IMLibFormat.decimalFormat('1234567800000009', 0, flags)).toBe('千2百3十4兆5千6百7十8億9')
  expect(IMLibFormat.decimalFormat('1234567800010009', 0, flags),).toBe('千2百3十4兆5千6百7十8億1万9')
})

test('IMLibFormat.percentFormat() should return \'\' if the parameter is \'\'', function () {
  'use strict'
  expect(IMLibFormat.percentFormat('')).toBe('')
})
test('IMLibFormat.percentFormat() should return \'\' if the parameter is null', function () {
  'use strict'
  expect(IMLibFormat.percentFormat(null)).toBe('')
})
test('IMLibFormat.percentFormat() should return \'\' if the parameter is \'1\'', function () {
  'use strict'
  expect(IMLibFormat.percentFormat('1')).toBe('100%')
})
test('IMLibFormat.percentFormat() should return \'\' if the parameter is \'-2\'', function () {
  'use strict'
  expect(IMLibFormat.percentFormat('-2')).toBe('-200%')
})
test('IMLibFormat.percentFormat() should return \'\' if the parameter is \'10\'', function () {
  'use strict'
  expect(IMLibFormat.percentFormat('10')).toBe('1000%')
})
test('IMLibFormat.percentFormat() should return \'\' if the parameter is \'test\'', function () {
  'use strict'
  expect(IMLibFormat.percentFormat('test')).toBe('')
})
test('IMLibFormat.percentFormat() should return \'\' if the parameter is \'3A\'', function () {
  'use strict'
  expect(IMLibFormat.percentFormat('3A')).toBe('300%')
})
test('IMLibFormat.percentFormat() should return \'\' if the parameter is \'4-0\'', function () {
  'use strict'
  expect(IMLibFormat.percentFormat('4-0')).toBe('4000%')
})
test('IMLibFormat.percentFormat() should return \'\' if the parameter is \'-50-0\'', function () {
  'use strict'
  expect(IMLibFormat.percentFormat('-50-0')).toBe('-50000%')
})
test('IMLibFormat.percentFormat() should return \'\' if the parameter is \'６７-0\'', function () {
  'use strict'
  expect(IMLibFormat.percentFormat('６７-0')).toBe('67000%')
})
test('IMLibFormat.percentFormat() should return \'\' if the parameter is \'0.1\'', function () {
  'use strict'
  expect(IMLibFormat.percentFormat('0.1')).toBe('10%')
})

test('booleanFormat() should return \'\' if the first parameter is \'\'', function () {
  'use strict'
  expect(IMLibFormat.booleanFormat('', 'non-zeros, zeros', null)).toBe('')
})

test('booleanFormat() should return \'\' if the first parameter is null', function () {
  'use strict'
  expect(IMLibFormat.booleanFormat(null, 'non-zeros, zeros', null)).toBe('')
})

test('booleanFormat() should return \'non-zeros\' if the first parameter is 1', function () {
  'use strict'
  expect(IMLibFormat.booleanFormat(1, 'non-zeros, zeros', null)).toBe('non-zeros')
})

test('booleanFormat() should return \'zeros\' if the first parameter is 0', function () {
  'use strict'
  expect(IMLibFormat.booleanFormat(0, 'non-zeros, zeros', null)).toBe('zeros')
})

test('IMLibFormat.getLocalYear() should return the gengo year.', function () {
  'use strict'
  expect(IMLibFormat.getLocalYear(new Date('2021/5/1'))).toBe('令和3年')
  expect(IMLibFormat.getLocalYear(new Date('2019/5/1'))).toBe('令和元年')
  expect(IMLibFormat.getLocalYear(new Date('2019/4/30'))).toBe('平成31年')
  expect(IMLibFormat.getLocalYear(new Date('2017/3/3'))).toBe('平成29年')
  expect(IMLibFormat.getLocalYear(new Date('1989/1/9'))).toBe('平成元年')
  expect(IMLibFormat.getLocalYear(new Date('1989/1/8'))).toBe('平成元年')
  expect(IMLibFormat.getLocalYear(new Date('1989/1/7'))).toBe('昭和64年')
  expect(IMLibFormat.getLocalYear(new Date('1926/12/26')),).toBe('昭和元年')
})

test('IMLibFormat.getKanjiNumber() should return the kanji numbers.', function () {
  'use strict'
  expect(IMLibFormat.getKanjiNumber(0)).toBe('〇')
  expect(IMLibFormat.getKanjiNumber(3)).toBe('三')
  expect(IMLibFormat.getKanjiNumber(45)).toBe('四十五')
  expect(IMLibFormat.getKanjiNumber(2345)).toBe('二千三百四十五')
})
