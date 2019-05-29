/*
 * How to test locally.
 *
 * [Preparation]
 * - Install Node.js locally.
 * - Set the current directory to the INTER-Mediator dirctory.
 * - Execute command 'sudo npm install buster -g'
 *
 * [At the start of your development]
 * - Set the current directory to the INTER-Mediator dirctory.
 * - Execute command 'buster-server'
 *     Don't stop the process started by above command.
 * - Open any browser and connect to http://localhost:1111
 * - Click 'Capture browser' button on the browser page
 * - Execute command 'buster-test'   <-- Repeat it!
 */

// JSHint support
/* global INTERMediator,buster,INTERMediatorLib, IMLibFormat,INTERMediatorOnPage,IMLibElement */

var assert = buster.referee.assert;

buster.testCase('repeaterTagFromEncTag() Test', {
    'should return \'TR\' if parameter is "TBODY"': function () {
        'use strict';
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag('TBODY'), 'TR');
    },
    'should return \'OPTION\' if parameter is "SELECT"': function () {
        'use strict';
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag('SELECT'), 'OPTION');
    },
    'should return \'LI\' if parameter is "UL"': function () {
        'use strict';
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag('UL'), 'LI');
    },
    'should return \'LI\' if parameter is "OL"': function () {
        'use strict';
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag('OL'), 'LI');
    },
    //'should return 'DIV' if parameter is 'DIV'': function () {'use strict';
    //    assert.equals(INTERMediatorLib.repeaterTagFromEncTag('DIV'), 'DIV');
    //},
    //'should return 'SPAN' if parameter is 'SPAN'': function () {'use strict';
    //    assert.equals(INTERMediatorLib.repeaterTagFromEncTag('SPAN'), 'SPAN');
    //},
    //'should return null if parameter is 'BODY'': function () {'use strict';
    //    assert.equals(INTERMediatorLib.repeaterTagFromEncTag('BODY'), null);
    //}
});

buster.testCase('INTERMediatorLib.generatePasswordHash() Test', {
    'Valid password hash should be generated': function () {
        'use strict';
        var hash = INTERMediatorLib.generatePasswordHash('1234');
        assert.equals(hash.length, 48);
    }
});

buster.testCase('IMLibFormat.numberFormat() Test', {
    setUp: function () {
        'use strict';
        INTERMediatorOnPage.localeInfo = {
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
            }
        };
    },
    'small integer should not be converted.': function () {
        'use strict';
        assert.equals(IMLibFormat.numberFormat(45, 0), '45');
        assert.equals(IMLibFormat.numberFormat(45.678, 1), '45.7');
        assert.equals(IMLibFormat.numberFormat(45.678, 2), '45.68');
        assert.equals(IMLibFormat.numberFormat(45.678, 3), '45.678');
        assert.equals(IMLibFormat.numberFormat(45.123, 1), '45.1');
        assert.equals(IMLibFormat.numberFormat(45.123, 2), '45.12');
        assert.equals(IMLibFormat.numberFormat(45.123, 3), '45.123');
    },
    'each 3-digits should be devided.': function () {
        'use strict';
        assert.equals(IMLibFormat.numberFormat(999, 0), '999');
        assert.equals(IMLibFormat.numberFormat(1000, 0), '1,000');
        assert.equals(IMLibFormat.numberFormat(999999, 0), '999,999');
        assert.equals(IMLibFormat.numberFormat(1000000, 0), '1,000,000');
        assert.equals(IMLibFormat.numberFormat(1000000.678, 1), '1,000,000.7');
        assert.equals(IMLibFormat.numberFormat(1000000.678, 2), '1,000,000.68');
        assert.equals(IMLibFormat.numberFormat(1000000.678, 3), '1,000,000.678');
        assert.equals(IMLibFormat.numberFormat(1000000.678, 4), '1,000,000.6780');
        assert.equals(IMLibFormat.numberFormat(-1000000.678, 1), '-1,000,000.7');
        assert.equals(IMLibFormat.numberFormat(-1000000.678, 2), '-1,000,000.68');
        assert.equals(IMLibFormat.numberFormat(-1000000.678, 3), '-1,000,000.678');
        assert.equals(IMLibFormat.numberFormat(999999, -1), '1,000,000');
        assert.equals(IMLibFormat.numberFormat(999999, -2), '1,000,000');
        assert.equals(IMLibFormat.numberFormat(999999, -3), '1,000,000');
        // A negative second parameter doesn't support so far.
    },
    'format string detection': function () {
        'use strict';
        assert.equals(INTERMediatorOnPage.localeInfo.mon_decimal_point, '.');
        assert.equals(INTERMediatorOnPage.localeInfo.mon_thousands_sep, ',');
        assert.equals(INTERMediatorOnPage.localeInfo.currency_symbol, '¥');
    }
});

buster.testCase('IMLibFormat.decimalFormat() Test', {
    setUp: function () {
        'use strict';
        INTERMediatorOnPage.localeInfo = {
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
            }
        };
    },
    'small integer should not be converted.': function () {
        'use strict';
        assert.equals(IMLibFormat.decimalFormat(45, 0), '45');
        assert.equals(IMLibFormat.decimalFormat(45.678, 1), '45.7');
        assert.equals(IMLibFormat.decimalFormat(45.678, 2), '45.68');
        assert.equals(IMLibFormat.decimalFormat(45.678, 3), '45.678');
        assert.equals(IMLibFormat.decimalFormat(45.123, 1), '45.1');
        assert.equals(IMLibFormat.decimalFormat(45.123, 2), '45.12');
        assert.equals(IMLibFormat.decimalFormat(45.123, 3), '45.123');
    },
    'each 3-digits should not be devided.': function () {
        'use strict';
        assert.equals(IMLibFormat.decimalFormat(999, 0), '999');
        assert.equals(IMLibFormat.decimalFormat(1000, 0), '1000');
        assert.equals(IMLibFormat.decimalFormat(999999, 0), '999999');
        assert.equals(IMLibFormat.decimalFormat(1000000, 0), '1000000');
        assert.equals(IMLibFormat.decimalFormat(1000000.678, 1), '1000000.7');
        assert.equals(IMLibFormat.decimalFormat(1000000.678, 2), '1000000.68');
        assert.equals(IMLibFormat.decimalFormat(1000000.678, 3), '1000000.678');
        assert.equals(IMLibFormat.decimalFormat(1000000.678, 4), '1000000.6780');
        assert.equals(IMLibFormat.decimalFormat(-1000000.678, 1), '-1000000.7');
        assert.equals(IMLibFormat.decimalFormat(-1000000.678, 2), '-1000000.68');
        assert.equals(IMLibFormat.decimalFormat(-1000000.678, 3), '-1000000.678');
        assert.equals(IMLibFormat.decimalFormat(999999, -1), '1000000');
        assert.equals(IMLibFormat.decimalFormat(999999, -2), '1000000');
        assert.equals(IMLibFormat.decimalFormat(999999, -3), '1000000');
    },
    'each 3-digits should be devided \'\' if useseperator is enabled.': function () {
        'use strict';
        var flags = {useSeparator: true};
        assert.equals(IMLibFormat.decimalFormat(999, 0, flags), '999');
        assert.equals(IMLibFormat.decimalFormat(1000, 0, flags), '1,000');
        assert.equals(IMLibFormat.decimalFormat(999999, 0, flags), '999,999');
        assert.equals(IMLibFormat.decimalFormat(1000000, 0, flags), '1,000,000');
        assert.equals(IMLibFormat.decimalFormat(1000000.678, 1, flags), '1,000,000.7');
        assert.equals(IMLibFormat.decimalFormat(1000000.678, 2, flags), '1,000,000.68');
        assert.equals(IMLibFormat.decimalFormat(1000000.678, 3, flags), '1,000,000.678');
        assert.equals(IMLibFormat.decimalFormat(1000000.678, 4, flags), '1,000,000.6780');
        assert.equals(IMLibFormat.decimalFormat(-1000000.678, 1, flags), '-1,000,000.7');
        assert.equals(IMLibFormat.decimalFormat(-1000000.678, 2, flags), '-1,000,000.68');
        assert.equals(IMLibFormat.decimalFormat(-1000000.678, 3, flags), '-1,000,000.678');
        assert.equals(IMLibFormat.decimalFormat(999999, -1, flags), '1,000,000');
        assert.equals(IMLibFormat.decimalFormat(999999, -2, flags), '1,000,000');
        assert.equals(IMLibFormat.decimalFormat(999999, -3, flags), '1,000,000');
    },
    'IMLibFormat.decimalFormat(0) should return \'\' if blankifzero is enabled.': function () {
        'use strict';
        var flags = {blankIfZero: true};
        assert.equals(IMLibFormat.decimalFormat('0', 0, flags), '');
        assert.equals(IMLibFormat.decimalFormat('1', 0, flags), '1');
        assert.equals(IMLibFormat.decimalFormat('０', 0, flags), '');
        assert.equals(IMLibFormat.decimalFormat('0.', 0, flags), '');
    },
    'IMLibFormat.decimalFormat(1) should return \'1\' if blankifzero is enabled.': function () {
        'use strict';
        var flags = {blankIfZero: true};
        assert.equals(IMLibFormat.decimalFormat('1', 0, flags), '1');
    },
    'IMLibFormat.decimalFormat(\'１\', 0, flags) should return \'1\' if charStyle is 0.': function () {
        'use strict';
        var flags = {charStyle: 0};
        assert.equals(IMLibFormat.decimalFormat('１', 0, flags), '1');
        assert.equals(IMLibFormat.decimalFormat('２', 0, flags), '2');
        assert.equals(IMLibFormat.decimalFormat('３', 0, flags), '3');
        assert.equals(IMLibFormat.decimalFormat('４', 0, flags), '4');
        assert.equals(IMLibFormat.decimalFormat('５', 0, flags), '5');
        assert.equals(IMLibFormat.decimalFormat('６', 0, flags), '6');
        assert.equals(IMLibFormat.decimalFormat('７', 0, flags), '7');
        assert.equals(IMLibFormat.decimalFormat('８', 0, flags), '8');
        assert.equals(IMLibFormat.decimalFormat('９', 0, flags), '9');
        assert.equals(IMLibFormat.decimalFormat('０', 0, flags), '0');
    },
    'IMLibFormat.decimalFormat(1, 0, flags) should return \'１\' if charStyle is 1.': function () {
        'use strict';
        var flags = {charStyle: 1};
        assert.equals(IMLibFormat.decimalFormat('1', 0, flags), '１');
        assert.equals(IMLibFormat.decimalFormat('2', 0, flags), '２');
        assert.equals(IMLibFormat.decimalFormat('3', 0, flags), '３');
        assert.equals(IMLibFormat.decimalFormat('4', 0, flags), '４');
        assert.equals(IMLibFormat.decimalFormat('5', 0, flags), '５');
        assert.equals(IMLibFormat.decimalFormat('6', 0, flags), '６');
        assert.equals(IMLibFormat.decimalFormat('7', 0, flags), '７');
        assert.equals(IMLibFormat.decimalFormat('8', 0, flags), '８');
        assert.equals(IMLibFormat.decimalFormat('9', 0, flags), '９');
        assert.equals(IMLibFormat.decimalFormat('0', 0, flags), '０');
    },
    'IMLibFormat.decimalFormat(1, 0, flags) should return \'一\' if charStyle is 2.': function () {
        'use strict';
        var flags = {charStyle: 2};
        assert.equals(IMLibFormat.decimalFormat('1', 0, flags), '一');
        assert.equals(IMLibFormat.decimalFormat('2', 0, flags), '二');
        assert.equals(IMLibFormat.decimalFormat('3', 0, flags), '三');
        assert.equals(IMLibFormat.decimalFormat('4', 0, flags), '四');
        assert.equals(IMLibFormat.decimalFormat('5', 0, flags), '五');
        assert.equals(IMLibFormat.decimalFormat('6', 0, flags), '六');
        assert.equals(IMLibFormat.decimalFormat('7', 0, flags), '七');
        assert.equals(IMLibFormat.decimalFormat('8', 0, flags), '八');
        assert.equals(IMLibFormat.decimalFormat('9', 0, flags), '九');
        assert.equals(IMLibFormat.decimalFormat('0', 0, flags), '〇');
    },
    'IMLibFormat.decimalFormat(1, 0, flags) should return \'壱\' if charStyle is 3.': function () {
        'use strict';
        var flags = {charStyle: 3};
        assert.equals(IMLibFormat.decimalFormat('1', 0, flags), '壱');
        assert.equals(IMLibFormat.decimalFormat('2', 0, flags), '弐');
        assert.equals(IMLibFormat.decimalFormat('3', 0, flags), '参');
        assert.equals(IMLibFormat.decimalFormat('4', 0, flags), '四');
        assert.equals(IMLibFormat.decimalFormat('5', 0, flags), '伍');
        assert.equals(IMLibFormat.decimalFormat('6', 0, flags), '六');
        assert.equals(IMLibFormat.decimalFormat('7', 0, flags), '七');
        assert.equals(IMLibFormat.decimalFormat('8', 0, flags), '八');
        assert.equals(IMLibFormat.decimalFormat('9', 0, flags), '九');
        assert.equals(IMLibFormat.decimalFormat('0', 0, flags), '〇');
    },
    'IMLibFormat.decimalFormat(12345, 0, flags) should return \'1万2345\' if kanjiSeparator is 1.': function () {
        'use strict';
        var flags = {useSeparator: true, kanjiSeparator: 1};
        assert.equals(IMLibFormat.decimalFormat('12345', 0, flags), '1万2345');
        assert.equals(IMLibFormat.decimalFormat('1234567800000000', 0, flags), '1234兆5678億');
        assert.equals(IMLibFormat.decimalFormat('1234567800000009', 0, flags), '1234兆5678億9');
        assert.equals(IMLibFormat.decimalFormat('1234567800010009', 0, flags), '1234兆5678億1万9');
    },
    'IMLibFormat.decimalFormat(12345, 0, flags) should return \'1万2千3百4十5\' if kanjiSeparator is 2.': function () {
        'use strict';
        var flags = {useSeparator: true, kanjiSeparator: 2};
        assert.equals(IMLibFormat.decimalFormat('12345', 0, flags), '1万2千3百4十5');
        assert.equals(IMLibFormat.decimalFormat('1234567800000000', 0, flags), '千2百3十4兆5千6百7十8億');
        assert.equals(IMLibFormat.decimalFormat('1234567800000009', 0, flags), '千2百3十4兆5千6百7十8億9');
        assert.equals(IMLibFormat.decimalFormat('1234567800010009', 0, flags), '千2百3十4兆5千6百7十8億1万9');
    }
});

buster.testCase('IMLibFormat.booleanFormat() Test', {
    'should return \'\' if the first parameter is \'\'': function () {
        'use strict';
        assert.equals(IMLibFormat.booleanFormat('', 'non-zeros, zeros', null), '');
    },
    'should return \'\' if the first parameter is null': function () {
        'use strict';
        assert.equals(IMLibFormat.booleanFormat(null, 'non-zeros, zeros', null), '');
    },
    'should return \'non-zeros\' if the first parameter is 1': function () {
        'use strict';
        assert.equals(IMLibFormat.booleanFormat(1, 'non-zeros, zeros', null), 'non-zeros');
    },
    'should return \'zeros\' if the first parameter is 0': function () {
        'use strict';
        assert.equals(IMLibFormat.booleanFormat(0, 'non-zeros, zeros', null), 'zeros');
    }
});

buster.testCase('IMLibFormat.percentFormat() Test', {
    setUp: function () {
        'use strict';
        INTERMediatorOnPage.localeInfo = {
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
        };
    },
    'should return \'\' if the parameter is \'\'': function () {
        'use strict';
        assert.equals(IMLibFormat.percentFormat(''), '');
    },
    'should return \'\' if the parameter is null': function () {
        'use strict';
        assert.equals(IMLibFormat.percentFormat(null), '');
    },
    'should return \'\' if the parameter is \'1\'': function () {
        'use strict';
        assert.equals(IMLibFormat.percentFormat('1'), '100%');
    },
    'should return \'\' if the parameter is \'-2\'': function () {
        'use strict';
        assert.equals(IMLibFormat.percentFormat('-2'), '-200%');
    },
    'should return \'\' if the parameter is \'10\'': function () {
        'use strict';
        assert.equals(IMLibFormat.percentFormat('10'), '1000%');
    },
    'should return \'\' if the parameter is \'test\'': function () {
        'use strict';
        assert.equals(IMLibFormat.percentFormat('test'), '');
    },
    'should return \'\' if the parameter is \'3A\'': function () {
        'use strict';
        assert.equals(IMLibFormat.percentFormat('3A'), '300%');
    },
    'should return \'\' if the parameter is \'4-0\'': function () {
        'use strict';
        assert.equals(IMLibFormat.percentFormat('4-0'), '4000%');
    },
    'should return \'\' if the parameter is \'-50-0\'': function () {
        'use strict';
        assert.equals(IMLibFormat.percentFormat('-50-0'), '-50000%');
    },
    'should return \'\' if the parameter is \'６７-0\'': function () {
        'use strict';
        assert.equals(IMLibFormat.percentFormat('６７-0'), '67000%');
    },
    'should return \'\' if the parameter is \'0.1\'': function () {
        'use strict';
        assert.equals(IMLibFormat.percentFormat('0.1'), '10%');
    }
});

buster.testCase('INTERMediatorLib.Round() Test', {
    'Round library function test for positive value.': function () {
        'use strict';
        assert.equals(INTERMediatorLib.Round(Math.PI, 0), 3);
        assert.equals(INTERMediatorLib.Round(Math.PI, 1), 3.1);
        assert.equals(INTERMediatorLib.Round(Math.PI, 2), 3.14);
        assert.equals(INTERMediatorLib.Round(Math.PI, 3), 3.142);
    },
    'Round library function test for negative value.': function () {
        'use strict';
        var v = 45678;
        assert.equals(INTERMediatorLib.Round(v, 0), v);
        assert.equals(INTERMediatorLib.Round(v, -1), 45680);
        assert.equals(INTERMediatorLib.Round(v, -2), 45700);
        assert.equals(INTERMediatorLib.Round(v, -3), 46000);
        // assert.equals(INTERMediatorLib.Round(v, -4), 50000); [WIP]
        assert.equals(INTERMediatorLib.Round(v, -5), 0);
        assert.equals(INTERMediatorLib.Round(v, -6), 0);
    }
});

buster.testCase('IMLibElement.getValueFromIMNode() Test', {
    'should return \'\' if parameter is null.': function () {
        'use strict';
        assert.equals(IMLibElement.getValueFromIMNode(null), '');
    }
});

buster.testCase('IMLib Date/Time String Test', {
    'should return the valid date time string(1)': function () {
        'use strict';
        var dt = new Date(2015, 7, 25, 12, 43, 51);
        assert.equals(INTERMediatorLib.dateTimeStringISO(dt), '2015-08-25 12:43:51');
    },
    'should return the valid date time string(2)': function () {
        'use strict';
        var dt = new Date(2015, 7, 25, 12, 43, 51);
        assert.equals(INTERMediatorLib.dateTimeStringFileMaker(dt), '08/25/2015 12:43:51');
    },
    'should return the valid date string(1)': function () {
        'use strict';
        var dt = new Date(2015, 7, 25, 12, 43, 51);
        assert.equals(INTERMediatorLib.dateStringISO(dt), '2015-08-25');
    },
    'should return the valid date string(2)': function () {
        'use strict';
        var dt = new Date(2015, 7, 25, 12, 43, 51);
        assert.equals(INTERMediatorLib.dateStringFileMaker(dt), '08/25/2015');
    },
    'should return the valid time string(1)': function () {
        'use strict';
        var dt = new Date(2015, 7, 25, 12, 43, 51);
        assert.equals(INTERMediatorLib.timeString(dt), '12:43:51');
    }
});

buster.testCase('IMLibFormat.getLocalYear() Test', {
    'should return the gengo year.': function () {
        'use strict';
        assert.equals(IMLibFormat.getLocalYear(new Date('2019/5/1')), '令和元年');
        assert.equals(IMLibFormat.getLocalYear(new Date('2019/4/30')), '平成31年');
        assert.equals(IMLibFormat.getLocalYear(new Date('2017/3/3')), '平成29年');
        assert.equals(IMLibFormat.getLocalYear(new Date('1989/1/9')), '平成元年');
        assert.equals(IMLibFormat.getLocalYear(new Date('1989/1/8')), '平成元年');
        assert.equals(IMLibFormat.getLocalYear(new Date('1989/1/7')), '昭和64年');
        assert.equals(IMLibFormat.getLocalYear(new Date('1926/12/26')), '昭和元年');
    }
});

buster.testCase('IMLibFormat.getKanjiNumber() Test', {
    'should return the kanji numbers.': function () {
        'use strict';
        assert.equals(IMLibFormat.getKanjiNumber(0), '〇');
        assert.equals(IMLibFormat.getKanjiNumber(3), '三');
        assert.equals(IMLibFormat.getKanjiNumber(45), '四十五');
        assert.equals(IMLibFormat.getKanjiNumber(2345), '二千三百四十五');
    }
});

buster.testCase('INTERMediatorLib.normalizeNumerics(str) Test', {
    'should return the numeric characters only numbers.': function () {
        'use strict';
        assert.equals(INTERMediatorLib.normalizeNumerics(0), 0);
        assert.equals(INTERMediatorLib.normalizeNumerics(99), 99);
        assert.equals(INTERMediatorLib.normalizeNumerics(120.5), 120.5);
        assert.equals(INTERMediatorLib.normalizeNumerics('15,236.77'), 15236.77);
        assert.equals(INTERMediatorLib.normalizeNumerics('$15,236.77'), 15236.77);
        assert.equals(INTERMediatorLib.normalizeNumerics('¥15,236.77'), 15236.77);
        assert.equals(INTERMediatorLib.normalizeNumerics('¥15,236.77-'), 15236.77);
        assert.equals(INTERMediatorLib.normalizeNumerics('４３０'), 430);
        assert.equals(INTERMediatorLib.normalizeNumerics('４３０．９９９'), 430.999);
    }
});

