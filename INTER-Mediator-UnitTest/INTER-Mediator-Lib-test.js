/*
 * How to test locally.
 *
 * [Preparation]
 * - Install Node.js locally.
 * - Set the current directory to the INTER-Mediator dirctory.
 * - Execute command "sudo npm install buster"
 *     The "node_modules" folder is going to create on the current folder.
 *
 * [At the start of your development]
 * - Set the current directory to the INTER-Mediator dirctory.
 * - Execute command "buster-server"
 *     Don't stop the process started by above command.
 * - Open any browser and connect to http://localhost:1111
 * - Click "Capture browser" button on the browser page
 * - Execute command "buster-telinkst"   <-- Repeat it!
 */

var assert = buster.referee.assert;

buster.testCase("repeaterTagFromEncTag() Test", {
    "should return 'TR' if parameter is 'TBODY'": function () {
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag("TBODY"), "TR");
    },
    "should return 'OPTION' if parameter is 'SELECT'": function () {
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag("SELECT"), "OPTION");
    },
    "should return 'LI' if parameter is 'UL'": function () {
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag("UL"), "LI");
    },
    "should return 'LI' if parameter is 'OL'": function () {
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag("OL"), "LI");
    },
    "should return 'DIV' if parameter is 'DIV'": function () {
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag("DIV"), "DIV");
    },
    "should return 'SPAN' if parameter is 'SPAN'": function () {
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag("SPAN"), "SPAN");
    },
    "should return null if parameter is 'BODY'": function () {
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag("BODY"), null);
    }
});

buster.testCase("INTERMediatorLib.generatePasswordHash() Test", {
    "Valid password hash should be generated": function () {
        var hash = INTERMediatorLib.generatePasswordHash("1234");
        assert.equals(hash.length, 48);
    }
});

buster.testCase("INTERMediatorLib.numberFormat() Test", {
    setUp: function () {
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
    "small integer should not be converted.": function () {
        assert.equals(INTERMediatorLib.numberFormat(0, 0), "0");
        assert.equals(INTERMediatorLib.numberFormat(45, 0), "45");
        assert.equals(INTERMediatorLib.numberFormat(45.678, 1), "45.7");
        assert.equals(INTERMediatorLib.numberFormat(45.678, 2), "45.68");
        assert.equals(INTERMediatorLib.numberFormat(45.678, 3), "45.678");
        assert.equals(INTERMediatorLib.numberFormat(45.123, 1), "45.1");
        assert.equals(INTERMediatorLib.numberFormat(45.123, 2), "45.12");
        assert.equals(INTERMediatorLib.numberFormat(45.123, 3), "45.123");
    },
    "each 3-digits should be devided.": function () {
        assert.equals(INTERMediatorLib.numberFormat(999, 0), "999");
        assert.equals(INTERMediatorLib.numberFormat(1000, 0), "1,000");
        assert.equals(INTERMediatorLib.numberFormat(999999, 0), "999,999");
        assert.equals(INTERMediatorLib.numberFormat(1000000, 0), "1,000,000");
        assert.equals(INTERMediatorLib.numberFormat(1000000.678, 1), "1,000,000.7");
        assert.equals(INTERMediatorLib.numberFormat(1000000.678, 2), "1,000,000.68");
        assert.equals(INTERMediatorLib.numberFormat(1000000.678, 3), "1,000,000.678");
        assert.equals(INTERMediatorLib.numberFormat(1000000.678, 4), "1,000,000.6780");
        assert.equals(INTERMediatorLib.numberFormat(-1000000.678, 1), "-1,000,000.7");
        assert.equals(INTERMediatorLib.numberFormat(-1000000.678, 2), "-1,000,000.68");
        assert.equals(INTERMediatorLib.numberFormat(-1000000.678, 3), "-1,000,000.678");
        assert.equals(INTERMediatorLib.numberFormat(999999, -1), "1,000,000");
        assert.equals(INTERMediatorLib.numberFormat(999999, -2), "1,000,000");
        assert.equals(INTERMediatorLib.numberFormat(999999, -3), "1,000,000");
        // A negative second parameter doesn't support so far.
    },
    "format string detection": function() {
        assert.equals(INTERMediatorLib.digitSeparator(), [".", ",", 3]);
    }
});

buster.testCase("INTERMediatorLib.decimalFormat() Test", {
    setUp: function () {
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
    "small integer should not be converted.": function () {
        assert.equals(INTERMediatorLib.decimalFormat(45, 0), "45");
        assert.equals(INTERMediatorLib.decimalFormat(45.678, 1), "45.7");
        assert.equals(INTERMediatorLib.decimalFormat(45.678, 2), "45.68");
        assert.equals(INTERMediatorLib.decimalFormat(45.678, 3), "45.678");
        assert.equals(INTERMediatorLib.decimalFormat(45.123, 1), "45.1");
        assert.equals(INTERMediatorLib.decimalFormat(45.123, 2), "45.12");
        assert.equals(INTERMediatorLib.decimalFormat(45.123, 3), "45.123");
    },
    "each 3-digits should not be devided.": function () {
        assert.equals(INTERMediatorLib.decimalFormat(999, 0), "999");
        assert.equals(INTERMediatorLib.decimalFormat(1000, 0), "1000");
        assert.equals(INTERMediatorLib.decimalFormat(999999, 0), "999999");
        assert.equals(INTERMediatorLib.decimalFormat(1000000, 0), "1000000");
        assert.equals(INTERMediatorLib.decimalFormat(1000000.678, 1), "1000000.7");
        assert.equals(INTERMediatorLib.decimalFormat(1000000.678, 2), "1000000.68");
        assert.equals(INTERMediatorLib.decimalFormat(1000000.678, 3), "1000000.678");
        assert.equals(INTERMediatorLib.decimalFormat(1000000.678, 4), "1000000.6780");
        assert.equals(INTERMediatorLib.decimalFormat(-1000000.678, 1), "-1000000.7");
        assert.equals(INTERMediatorLib.decimalFormat(-1000000.678, 2), "-1000000.68");
        assert.equals(INTERMediatorLib.decimalFormat(-1000000.678, 3), "-1000000.678");
        assert.equals(INTERMediatorLib.decimalFormat(999999, -1), "1000000");
        assert.equals(INTERMediatorLib.decimalFormat(999999, -2), "1000000");
        assert.equals(INTERMediatorLib.decimalFormat(999999, -3), "1000000");
    },
    "each 3-digits should be devided \"\" if useseperator is enabled.": function () {
        var flags = { useSeparator: true };
        assert.equals(INTERMediatorLib.decimalFormat(999, 0, flags), "999");
        assert.equals(INTERMediatorLib.decimalFormat(1000, 0, flags), "1,000");
        assert.equals(INTERMediatorLib.decimalFormat(999999, 0, flags), "999,999");
        assert.equals(INTERMediatorLib.decimalFormat(1000000, 0, flags), "1,000,000");
        assert.equals(INTERMediatorLib.decimalFormat(1000000.678, 1, flags), "1,000,000.7");
        assert.equals(INTERMediatorLib.decimalFormat(1000000.678, 2, flags), "1,000,000.68");
        assert.equals(INTERMediatorLib.decimalFormat(1000000.678, 3, flags), "1,000,000.678");
        assert.equals(INTERMediatorLib.decimalFormat(1000000.678, 4, flags), "1,000,000.6780");
        assert.equals(INTERMediatorLib.decimalFormat(-1000000.678, 1, flags), "-1,000,000.7");
        assert.equals(INTERMediatorLib.decimalFormat(-1000000.678, 2, flags), "-1,000,000.68");
        assert.equals(INTERMediatorLib.decimalFormat(-1000000.678, 3, flags), "-1,000,000.678");
        assert.equals(INTERMediatorLib.decimalFormat(999999, -1, flags), "1,000,000");
        assert.equals(INTERMediatorLib.decimalFormat(999999, -2, flags), "1,000,000");
        assert.equals(INTERMediatorLib.decimalFormat(999999, -3, flags), "1,000,000");
    },
    "INTERMediatorLib.decimalFormat(0) should return \"\" if blankifzero is enabled.": function () {
        var flags = { blankIfZero: true };
        assert.equals(INTERMediatorLib.decimalFormat("0", 0, flags), "");
        assert.equals(INTERMediatorLib.decimalFormat("０", 0, flags), "");
        assert.equals(INTERMediatorLib.decimalFormat("0.", 0, flags), "");
    },
    "INTERMediatorLib.decimalFormat(1) should return \"1\" if blankifzero is enabled.": function () {
        var flags = { blankIfZero: true };
        assert.equals(INTERMediatorLib.decimalFormat("1", 0, flags), "1");
    },
    "INTERMediatorLib.decimalFormat(\"１\", 0, flags) should return \"1\" if charStyle is 0.": function () {
        var flags = { charStyle: 0 };
        assert.equals(INTERMediatorLib.decimalFormat("１", 0, flags), "1");
        assert.equals(INTERMediatorLib.decimalFormat("２", 0, flags), "2");
        assert.equals(INTERMediatorLib.decimalFormat("３", 0, flags), "3");
        assert.equals(INTERMediatorLib.decimalFormat("４", 0, flags), "4");
        assert.equals(INTERMediatorLib.decimalFormat("５", 0, flags), "5");
        assert.equals(INTERMediatorLib.decimalFormat("６", 0, flags), "6");
        assert.equals(INTERMediatorLib.decimalFormat("７", 0, flags), "7");
        assert.equals(INTERMediatorLib.decimalFormat("８", 0, flags), "8");
        assert.equals(INTERMediatorLib.decimalFormat("９", 0, flags), "9");
        assert.equals(INTERMediatorLib.decimalFormat("０", 0, flags), "0");
    },
    "INTERMediatorLib.decimalFormat(1, 0, flags) should return \"１\" if charStyle is 1.": function () {
        var flags = { charStyle: 1 };
        assert.equals(INTERMediatorLib.decimalFormat("1", 0, flags), "１");
        assert.equals(INTERMediatorLib.decimalFormat("2", 0, flags), "２");
        assert.equals(INTERMediatorLib.decimalFormat("3", 0, flags), "３");
        assert.equals(INTERMediatorLib.decimalFormat("4", 0, flags), "４");
        assert.equals(INTERMediatorLib.decimalFormat("5", 0, flags), "５");
        assert.equals(INTERMediatorLib.decimalFormat("6", 0, flags), "６");
        assert.equals(INTERMediatorLib.decimalFormat("7", 0, flags), "７");
        assert.equals(INTERMediatorLib.decimalFormat("8", 0, flags), "８");
        assert.equals(INTERMediatorLib.decimalFormat("9", 0, flags), "９");
        assert.equals(INTERMediatorLib.decimalFormat("0", 0, flags), "０");
    },
    "INTERMediatorLib.decimalFormat(1, 0, flags) should return \"一\" if charStyle is 2.": function () {
        var flags = { charStyle: 2 };
        assert.equals(INTERMediatorLib.decimalFormat("1", 0, flags), "一");
        assert.equals(INTERMediatorLib.decimalFormat("2", 0, flags), "二");
        assert.equals(INTERMediatorLib.decimalFormat("3", 0, flags), "三");
        assert.equals(INTERMediatorLib.decimalFormat("4", 0, flags), "四");
        assert.equals(INTERMediatorLib.decimalFormat("5", 0, flags), "五");
        assert.equals(INTERMediatorLib.decimalFormat("6", 0, flags), "六");
        assert.equals(INTERMediatorLib.decimalFormat("7", 0, flags), "七");
        assert.equals(INTERMediatorLib.decimalFormat("8", 0, flags), "八");
        assert.equals(INTERMediatorLib.decimalFormat("9", 0, flags), "九");
        assert.equals(INTERMediatorLib.decimalFormat("0", 0, flags), "〇");
    },
    "INTERMediatorLib.decimalFormat(1, 0, flags) should return \"壱\" if charStyle is 3.": function () {
        var flags = { charStyle: 3 };
        assert.equals(INTERMediatorLib.decimalFormat("1", 0, flags), "壱");
        assert.equals(INTERMediatorLib.decimalFormat("2", 0, flags), "弐");
        assert.equals(INTERMediatorLib.decimalFormat("3", 0, flags), "参");
        assert.equals(INTERMediatorLib.decimalFormat("4", 0, flags), "四");
        assert.equals(INTERMediatorLib.decimalFormat("5", 0, flags), "伍");
        assert.equals(INTERMediatorLib.decimalFormat("6", 0, flags), "六");
        assert.equals(INTERMediatorLib.decimalFormat("7", 0, flags), "七");
        assert.equals(INTERMediatorLib.decimalFormat("8", 0, flags), "八");
        assert.equals(INTERMediatorLib.decimalFormat("9", 0, flags), "九");
        assert.equals(INTERMediatorLib.decimalFormat("0", 0, flags), "〇");
    }
});

buster.testCase("INTERMediatorLib.booleanFormat() Test", {
    "should return \"\" if the first parameter is \"\"": function () {
        assert.equals(INTERMediatorLib.booleanFormat("", "non-zeros", "zeros"), "");
    },
    "should return \"\" if the first parameter is null": function () {
        assert.equals(INTERMediatorLib.booleanFormat(null, "non-zeros", "zeros"), "");
    },
    "should return \"non-zeros\" if the first parameter is 1": function () {
        assert.equals(INTERMediatorLib.booleanFormat(1, "non-zeros", "zeros"), "non-zeros");
    },
    "should return \"zeros\" if the first parameter is 0": function () {
        assert.equals(INTERMediatorLib.booleanFormat(0, "non-zeros", "zeros"), "zeros");
    }
});

buster.testCase("INTERMediatorLib.percentFormat() Test", {
    setUp: function () {
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
    "should return \"\" if the parameter is \"\"": function () {
        assert.equals(INTERMediatorLib.percentFormat(""), "");
    },
    "should return \"\" if the parameter is null": function () {
        assert.equals(INTERMediatorLib.percentFormat(null), "");
    },
    "should return \"\" if the parameter is \"1\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("1"), "100%");
    },
    "should return \"\" if the parameter is \"-2\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("-2"), "-200%");
    },
    "should return \"\" if the parameter is \"10\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("10"), "1000%");
    },
    "should return \"\" if the parameter is \"test\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("test"), "");
    },
    "should return \"\" if the parameter is \"3A\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("3A"), "300%");
    },
    "should return \"\" if the parameter is \"4-0\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("4-0"), "4000%");
    },
    "should return \"\" if the parameter is \"-50-0\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("-50-0"), "-50000%");
    },
    "should return \"\" if the parameter is \"６７-0\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("６７-0"), "67000%");
    },
    "should return \"\" if the parameter is \"0.1\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("0.1"), "10%");
    }
});

buster.testCase("INTERMediatorLib.Round() Test", {
    "Round library function test for positive value.": function () {
        assert.equals(INTERMediatorLib.Round(Math.PI, 0), 3);
        assert.equals(INTERMediatorLib.Round(Math.PI, 1), 3.1);
        assert.equals(INTERMediatorLib.Round(Math.PI, 2), 3.14);
        assert.equals(INTERMediatorLib.Round(Math.PI, 3), 3.142);
    },
    "Round library function test for negative value.": function () {
        var v = 45678;
        assert.equals(INTERMediatorLib.Round(v, 0), v);
        assert.equals(INTERMediatorLib.Round(v, -1), 45680);
        assert.equals(INTERMediatorLib.Round(v, -2), 45700);
        assert.equals(INTERMediatorLib.Round(v, -3), 46000);
        assert.equals(INTERMediatorLib.Round(v, -4), 50000);
        assert.equals(INTERMediatorLib.Round(v, -5), 0);
        assert.equals(INTERMediatorLib.Round(v, -6), 0);
    }
});

buster.testCase("INTERMediatorLib.booleanFormat() Test", {
    "should return \"\" if the first parameter is \"\"": function () {
        assert.equals(INTERMediatorLib.booleanFormat("", "non-zeros", "zeros"), "");
    },
    "should return \"\" if the first parameter is null": function () {
        assert.equals(INTERMediatorLib.booleanFormat(null, "non-zeros", "zeros"), "");
    },
    "should return \"non-zeros\" if the first parameter is 1": function () {
        assert.equals(INTERMediatorLib.booleanFormat(1, "non-zeros", "zeros"), "non-zeros");
    },
    "should return \"zeros\" if the first parameter is 0": function () {
        assert.equals(INTERMediatorLib.booleanFormat(0, "non-zeros", "zeros"), "zeros");
    }
});

buster.testCase("INTERMediatorLib.percentFormat() Test", {
    setUp: function () {
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
    "should return \"\" if the parameter is \"\"": function () {
        assert.equals(INTERMediatorLib.percentFormat(""), "");
    },
    "should return \"\" if the parameter is null": function () {
        assert.equals(INTERMediatorLib.percentFormat(null), "");
    },
    "should return \"\" if the parameter is \"1\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("1"), "100%");
    },
    "should return \"\" if the parameter is \"-2\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("-2"), "-200%");
    },
    "should return \"\" if the parameter is \"10\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("10"), "1000%");
    },
    "should return \"\" if the parameter is \"test\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("test"), "");
    },
    "should return \"\" if the parameter is \"3A\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("3A"), "300%");
    },
    "should return \"\" if the parameter is \"4-0\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("4-0"), "4000%");
    },
    "should return \"\" if the parameter is \"-50-0\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("-50-0"), "-50000%");
    },
    "should return \"\" if the parameter is \"６７-0\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("６７-0"), "67000%");
    },
    "should return \"\" if the parameter is \"0.1\"": function () {
        assert.equals(INTERMediatorLib.percentFormat("0.1"), "10%");
    }
});

buster.testCase("IMLibElement.getValueFromIMNode() Test", {
    "should return '' if parameter is null.": function () {
        assert.equals(IMLibElement.getValueFromIMNode(null), "");
    }
});

buster.testCase("IMLib Date/Time String Test", {
    "should return the valid date time string(1)": function () {
        var dt = new Date(2015, 7, 25, 12, 43, 51);
        assert.equals(INTERMediatorLib.dateTimeStringISO(dt), "2015-08-25 12:43:51");
    },
    "should return the valid date time string(2)": function () {
        var dt = new Date(2015, 7, 25, 12, 43, 51);
        assert.equals(INTERMediatorLib.dateTimeStringFileMaker(dt), "08/25/2015 12:43:51");
    },
    "should return the valid date string(1)": function () {
        var dt = new Date(2015, 7, 25, 12, 43, 51);
        assert.equals(INTERMediatorLib.dateStringISO(dt), "2015-08-25");
    },
    "should return the valid date string(2)": function () {
        var dt = new Date(2015, 7, 25, 12, 43, 51);
        assert.equals(INTERMediatorLib.dateStringFileMaker(dt), "08/25/2015");
    },
    "should return the valid time string(1)": function () {
        var dt = new Date(2015, 7, 25, 12, 43, 51);
        assert.equals(INTERMediatorLib.timeString(dt), "12:43:51");
    }
});
