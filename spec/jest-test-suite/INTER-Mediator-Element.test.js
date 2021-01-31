// JSHint support
/* global INTERMediator,buster,INTERMediatorLib,INTERMediatorOnPage,IMLibElement */

const IMLibElement = require('../../src/js/INTER-Mediator-Element')

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

test('IMLibElement.setValueToIMNode() should return false without TypeError (curVal.replace is not a function)', () => {
  let tempElement = document.createElement('textarea')
  expect(IMLibElement.setValueToIMNode(tempElement, 'textNode', null, true)).toBe(false)
  expect(IMLibElement.setValueToIMNode(tempElement, 'textNode', false, true)).toBe(false)
})

test('IMLibElement.setValueToIMNode() has to set the value to textarea', () => {
  let value
  let tempElement = document.createElement('textarea')
  value = 'abc'
  IMLibElement.setValueToIMNode(tempElement, '', value, true)
  expect(tempElement.value).toBe(value)
  value = '123'
  IMLibElement.setValueToIMNode(tempElement, '', value, true)
  expect(tempElement.value).toBe(value)
  value = null
  IMLibElement.setValueToIMNode(tempElement, '', value, true)
  expect(tempElement.value).toBe('')
  value = []
  IMLibElement.setValueToIMNode(tempElement, '', value, true)
  expect(tempElement.value).toBe('')
  value = [999, 888, 777]
  IMLibElement.setValueToIMNode(tempElement, '', value, true)
  expect(tempElement.value).toBe('999')
  value = 'qwe\n122'
  IMLibElement.setValueToIMNode(tempElement, '', value, true)
  expect(tempElement.value).toBe(value)
})

test('IMLibElement.setValueToIMNode() has to set the value to text field', () => {
  let value
  let tempElement = document.createElement('INPUT')
  tempElement.type = 'text'
  value = 'abc'
  IMLibElement.setValueToIMNode(tempElement, '', value, true)
  expect(tempElement.value).toBe(value)
  value = '123'
  IMLibElement.setValueToIMNode(tempElement, '', value, true)
  expect(tempElement.value).toBe(value)
  value = null
  IMLibElement.setValueToIMNode(tempElement, '', value, true)
  expect(tempElement.value).toBe('')
  value = []
  IMLibElement.setValueToIMNode(tempElement, '', value, true)
  expect(tempElement.value).toBe('')
  value = [999, 888, 777]
  IMLibElement.setValueToIMNode(tempElement, '', value, true)
  expect(tempElement.value).toBe('999')
  value = 'qwe122'
  IMLibElement.setValueToIMNode(tempElement, '', value, true)
  expect(tempElement.value).toBe(value)
})

test('IMLibElement.setValueToIMNode() has to set the value to checkbox', () => {
  'use strict'
  let tempElement = document.createElement('INPUT')
  tempElement.type = 'checkbox'
  tempElement.value = '1'
  IMLibElement.setValueToIMNode(tempElement, '', 1, true)
  expect(tempElement.checked).toBe(true)
  IMLibElement.setValueToIMNode(tempElement, '', '1', true)
  expect(tempElement.checked).toBe(true)
  IMLibElement.setValueToIMNode(tempElement, '', 0, true)
  expect(tempElement.checked).toBe(false)
  IMLibElement.setValueToIMNode(tempElement, '', -1, true)
  expect(tempElement.checked).toBe(false)
  tempElement.value = 'anytext'
  IMLibElement.setValueToIMNode(tempElement, '', 'anytext', true)
  expect(tempElement.checked).toBe(true)
  IMLibElement.setValueToIMNode(tempElement, '', '1', true)
  expect(tempElement.checked).toBe(false)
  IMLibElement.setValueToIMNode(tempElement, '', 0, true)
  expect(tempElement.checked).toBe(false)
  IMLibElement.setValueToIMNode(tempElement, '', -1, true)
  expect(tempElement.checked).toBe(false)
})

test('IMLibElement.setValueToIMNode() with # target has to add the value to node', () => {
  let value, value1, value2, value3, attr = 'href', tag = 'a'
  let tempElement = document.createElement(tag)
  value = 'abc'
  IMLibElement.setValueToIMNode(tempElement, attr, value, true)
  expect(tempElement.getAttribute(attr)).toBe(value)
  value = '123'
  IMLibElement.setValueToIMNode(tempElement, attr, value, true)
  expect(tempElement.getAttribute(attr)).toBe(value)
  value = null
  IMLibElement.setValueToIMNode(tempElement, attr, value, true)
  expect(tempElement.getAttribute(attr)).toBe('')
  value = []
  IMLibElement.setValueToIMNode(tempElement, attr, value, true)
  expect(tempElement.getAttribute(attr)).toBe('')
  value = [999, 888, 777]
  IMLibElement.setValueToIMNode(tempElement, attr, value, true)
  expect(tempElement.getAttribute(attr)).toBe(String(value[0]))

  tempElement = document.createElement(tag)
  value1 = 'base-url'
  tempElement.setAttribute(attr, value1)
  value2 = 'params'
  IMLibElement.setValueToIMNode(tempElement, '#' + attr, value2, true)
  expect(tempElement.getAttribute(attr)).toBe(value1 + value2)
  value2 = 'another'
  IMLibElement.setValueToIMNode(tempElement, '#' + attr, value2, true)
  expect(tempElement.getAttribute(attr)).toBe(value1 + value2)

  tempElement = document.createElement(tag)
  value1 = 'base-url$$'
  tempElement.setAttribute(attr, value1)
  value1 = 'base-url'
  value2 = 'params'
  IMLibElement.setValueToIMNode(tempElement, '$' + attr, value2, true)
  expect(tempElement.getAttribute(attr)).toBe(value1 + value2 + '$')
  value3 = 'another'
  IMLibElement.setValueToIMNode(tempElement, '$' + attr, value3, true)
  expect(tempElement.getAttribute(attr)).toBe(value1 + value2 + value3)
})

test('IMLibElement.setValueToIMNode() with innerHTML target', () => {
  'use strict'
  let value, value1, value2, attr = 'innerHTML', tag = 'div'
  let tempElement = document.createElement(tag)
  value = 'abc'
  IMLibElement.setValueToIMNode(tempElement, attr, value, true)
  expect(tempElement.innerHTML).toBe(value)
  value = '123'
  IMLibElement.setValueToIMNode(tempElement, attr, value, true)
  expect(tempElement.innerHTML).toBe(value)
  value = null
  IMLibElement.setValueToIMNode(tempElement, attr, value, true)
  expect(tempElement.innerHTML).toBe('')
  value = []
  IMLibElement.setValueToIMNode(tempElement, attr, value, true)
  expect(tempElement.innerHTML).toBe('')
  value = [999, 888, 777]
  IMLibElement.setValueToIMNode(tempElement, attr, value, true)
  expect(tempElement.innerHTML).toBe(String(value[0]))
  value = '<table><tbody><tr><td>aa</td></tr><tr><td>bb</td></tr></tbody></table>'
  IMLibElement.setValueToIMNode(tempElement, attr, value, true)
  expect(tempElement.innerHTML).toBe(value)

  tempElement = document.createElement(tag)
  value1 = '<table><tbody><tr><td>aa</td></tr><tr><td>bb</td></tr></tbody></table>'
  tempElement.innerHTML = value1
  value2 = '<div>ccc</div>'
  IMLibElement.setValueToIMNode(tempElement, '#' + attr, value2, true)
  expect(tempElement.innerHTML).toBe(value1 + value2)
  value2 = '<p>ddd</p>'
  IMLibElement.setValueToIMNode(tempElement, '#' + attr, value2, true)
  expect(tempElement.innerHTML).toBe(value1 + value2)

  tempElement = document.createElement(tag)
  value1 = '<table><tbody><tr><td>$$</td></tr><tr><td>bb</td></tr></tbody></table>'
  tempElement.innerHTML = value1
  value1 = '<table><tbody><tr><td>params$</td></tr><tr><td>bb</td></tr></tbody></table>'
  value2 = 'params'
  IMLibElement.setValueToIMNode(tempElement, '$' + attr, value2, true)
  expect(tempElement.innerHTML).toBe(value1)
  value1 = '<table><tbody><tr><td>paramsanother</td></tr><tr><td>bb</td></tr></tbody></table>'
  value2 = 'another'
  IMLibElement.setValueToIMNode(tempElement, '$' + attr, value2, true)
  expect(tempElement.innerHTML).toBe(value1)
})

test('IMLibElement.setValueToIMNode() has to set the numeric value to div', () => {
  'use strict'
  let tempElement = document.createElement('input')
  tempElement.setAttribute('type', 'text')

  tempElement.setAttribute('data-im-format', 'number(0)')
  IMLibElement.setValueToIMNode(tempElement, '', '1234.567', true)
  expect(tempElement.value).toBe('1235')

  tempElement.setAttribute('data-im-format', 'number(2)')
  IMLibElement.setValueToIMNode(tempElement, '', '1234.567', true)
  expect(tempElement.value).toBe('1234.57')

  tempElement.setAttribute('data-im-format', 'number(0)')
  tempElement.setAttribute('data-im-format-options', 'useseparator blankifzero')
  IMLibElement.setValueToIMNode(tempElement, '', '1234.567', true)
  expect(tempElement.value).toBe('1,235')
  IMLibElement.setValueToIMNode(tempElement, '', '234.567', true)
  expect(tempElement.value).toBe('235')
  IMLibElement.setValueToIMNode(tempElement, '', '', true)
  expect(tempElement.value).toBe('')

  tempElement.setAttribute('data-im-format', 'number(2)')
  tempElement.setAttribute('data-im-format-options', 'useseparator blankifzero')
  IMLibElement.setValueToIMNode(tempElement, '', '1234.567', true)
  expect(tempElement.value).toBe('1,234.57')
  IMLibElement.setValueToIMNode(tempElement, '', '234.567', true)
  expect(tempElement.value).toBe('234.57')
  IMLibElement.setValueToIMNode(tempElement, '', '', true)
  expect(tempElement.value).toBe('')
})

test('IMLibElement.setValueToIMNode() has to set the date/time value to div', () => {
  'use strict'
  let tempElement = document.createElement('input')
  tempElement.setAttribute('type', 'text')

  tempElement.setAttribute('data-im-format', 'date(<<%Y>>)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('<<2017>>')
  tempElement.setAttribute('data-im-format', 'date([%Y][%M])')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('[2017][07]')
  tempElement.setAttribute('data-im-format', 'date(%Y/%M/%D)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('2017/07/23')

  tempElement.setAttribute('data-im-format', 'date(%Y)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('2017')
  tempElement.setAttribute('data-im-format', 'date(%y)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('17')

  tempElement.setAttribute('data-im-format', 'date(%g)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('平成29年')
  tempElement.setAttribute('data-im-format', 'date(%G)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('平成二十九年')

  tempElement.setAttribute('data-im-format', 'date(%M)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('07')
  tempElement.setAttribute('data-im-format', 'date(%m)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('7')
  tempElement.setAttribute('data-im-format', 'date(%B)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('文月')
  tempElement.setAttribute('data-im-format', 'date(%b)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('七月')
  tempElement.setAttribute('data-im-format', 'date(%T)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('July')
  tempElement.setAttribute('data-im-format', 'date(%t)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('Jul')
  tempElement.setAttribute('data-im-format', 'date(%D)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('23')
  tempElement.setAttribute('data-im-format', 'date(%D)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-03 14:39:06', true)
  expect(tempElement.value).toBe('03')
  tempElement.setAttribute('data-im-format', 'date(%d)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-03 14:39:06', true)
  expect(tempElement.value).toBe('3')
  tempElement.setAttribute('data-im-format', 'date(%A)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('Sunday')
  tempElement.setAttribute('data-im-format', 'date(%a)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('Sun')
  tempElement.setAttribute('data-im-format', 'date(%W)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('日曜日')
  tempElement.setAttribute('data-im-format', 'date(%w)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('日')

  tempElement.setAttribute('data-im-format', 'date(%H)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('14')
  tempElement.setAttribute('data-im-format', 'date(%h)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('14')
  tempElement.setAttribute('data-im-format', 'date(%H)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 04:39:06', true)
  expect(tempElement.value).toBe('04')
  tempElement.setAttribute('data-im-format', 'date(%h)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 04:39:06', true)
  expect(tempElement.value).toBe('4')
  tempElement.setAttribute('data-im-format', 'date(%I)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('39')
  tempElement.setAttribute('data-im-format', 'date(%i)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('39')
  tempElement.setAttribute('data-im-format', 'date(%I)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:09:06', true)
  expect(tempElement.value).toBe('09')
  tempElement.setAttribute('data-im-format', 'date(%i)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:09:06', true)
  expect(tempElement.value).toBe('9')
  tempElement.setAttribute('data-im-format', 'date(%S)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:46', true)
  expect(tempElement.value).toBe('46')
  tempElement.setAttribute('data-im-format', 'date(%s)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:46', true)
  expect(tempElement.value).toBe('46')
  tempElement.setAttribute('data-im-format', 'date(%S)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('06')
  tempElement.setAttribute('data-im-format', 'date(%s)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('6')

  tempElement.setAttribute('data-im-format', 'date(%J %P)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 12:39:06', true)
  expect(tempElement.value).toBe('00 PM')
  tempElement.setAttribute('data-im-format', 'date(%j %p)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 12:39:06', true)
  expect(tempElement.value).toBe('0 pm')
  tempElement.setAttribute('data-im-format', 'date(%K %P)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 12:39:06', true)
  expect(tempElement.value).toBe('12 PM')
  tempElement.setAttribute('data-im-format', 'date(%k %p)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 12:39:06', true)
  expect(tempElement.value).toBe('12 pm')
  tempElement.setAttribute('data-im-format', 'date(%J %P)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 13:39:06', true)
  expect(tempElement.value).toBe('01 PM')
  tempElement.setAttribute('data-im-format', 'date(%j %p)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 13:39:06', true)
  expect(tempElement.value).toBe('1 pm')
  tempElement.setAttribute('data-im-format', 'date(%K %P)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 13:39:06', true)
  expect(tempElement.value).toBe('01 PM')
  tempElement.setAttribute('data-im-format', 'date(%k %p)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 13:39:06', true)
  expect(tempElement.value).toBe('1 pm')
  tempElement.setAttribute('data-im-format', 'date(%k %N)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 12:39:06', true)
  expect(tempElement.value).toBe('12 午後')

  tempElement.setAttribute('data-im-format', 'datetime(long)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('2017年07月23日 日曜日 14時39分06秒')

  tempElement.setAttribute('data-im-format', 'date(long)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('2017年07月23日 日曜日')

  tempElement.setAttribute('data-im-format', 'time(long)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('14時39分06秒')

  tempElement.setAttribute('data-im-format', 'datetime(middle)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('2017/07/23(日) 14:39:06')

  tempElement.setAttribute('data-im-format', 'date(middle)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('2017/07/23(日)')

  tempElement.setAttribute('data-im-format', 'time(middle)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('14:39:06')

  tempElement.setAttribute('data-im-format', 'datetime(short)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('2017/7/23 14:39')

  tempElement.setAttribute('data-im-format', 'date(short)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('2017/7/23')

  tempElement.setAttribute('data-im-format', 'time(short)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('14:39')

  tempElement.setAttribute('data-im-format', 'time(  short  )')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('14:39')

  tempElement.setAttribute('data-im-format', 'time(short)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23', true)
  expect(tempElement.value).toBe('00:00')

  tempElement.setAttribute('data-im-format', 'time(Short)')
  IMLibElement.setValueToIMNode(tempElement, '', '2017-07-23 14:39:06', true)
  expect(tempElement.value).toBe('14:39')
})

test('IMLibElement.getValueFromIMNode() should return \'\' if parameter is null.', function () {
  'use strict'
  expect(IMLibElement.getValueFromIMNode(null)).toBe('')
})


