var assert = buster.referee.assert;

buster.testCase("INTER-Mediator Element Test", {
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
            },
            "D_FMT_LONG": "%Y\u5e74%m\u6708%d\u65e5 %W",
            "T_FMT_LONG": "%H\u6642%M\u5206%S\u79d2 %Z",
            "D_FMT_MIDDLE": "%Y\/%m\/%d(%w)",
            "T_FMT_MIDDLE": "%H:%M:%S",
            "D_FMT_SHORT": "%Y\/%m\/%d",
            "T_FMT_SHORT": "%H:%M",
            "ABDAY": ["\u65e5", "\u6708", "\u706b", "\u6c34", "\u6728", "\u91d1", "\u571f"],
            "DAY": ["\u65e5\u66dc\u65e5", "\u6708\u66dc\u65e5", "\u706b\u66dc\u65e5", "\u6c34\u66dc\u65e5", "\u6728\u66dc\u65e5", "\u91d1\u66dc\u65e5", "\u571f\u66dc\u65e5"],
            "ABMON": [" 1", " 2", " 3", " 4", " 5", " 6", " 7", " 8", " 9", "10", "11", "12"],
            "MON": ["1\u6708", "2\u6708", "3\u6708", "4\u6708", "5\u6708", "6\u6708", "7\u6708", "8\u6708", "9\u6708", "10\u6708", "11\u6708", "12\u6708"],
            "AM_STR": "AM",
            "PM_STR": "PM",
            "D_T_FMT": "%a %b\/%e %T %Y",
            "D_FMT": "%Y\/%m\/%d",
            "T_FMT": "%H\u6642%M\u5206%S\u79d2",
            "T_FMT_AMPM": "%I:%M:%S %p",
            "ERA": "",
            "ERA_D_T_FMT": "",
            "ERA_D_FMT": "",
            "ERA_T_FMT": ""
        };
    },
    "IMLibElement.setValueToIMNode() should return false without TypeError (curVal.replace is not a function)": function () {
        var tempElement = document.createElement("textarea");
        assert.equals(IMLibElement.setValueToIMNode(tempElement, "textNode", null, true), false);
        assert.equals(IMLibElement.setValueToIMNode(tempElement, "textNode", false, true), false);
    }, /*,
     "IMLibElement.checkOptimisticLock() should return false in case of handling of the local context without TypeError (contextInfo is null)": function () {
     var inputElement = document.createElement("input");
     inputElement.setAttribute("data-im", "_@localcontext");
     INTERMediatorOnPage.getOptionsAliases=function(){return {'kindid':'cor_way_kindname@kind_id@value'};};
     assert.equals(IMLibElement.checkOptimisticLock(inputElement, null), false);
     }*/
    "IMLibElement.setValueToIMNode() has to set the value to textarea": function () {
        var value;
        var tempElement = document.createElement("textarea");
        value = 'abc';
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, value);
        value = '123';
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, value);
        value = null;
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, "");
        value = [];
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, "");
        value = [999, 888, 777];
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, "999");
        value = "qwe\n122";
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, value);
    },
    "IMLibElement.setValueToIMNode() has to set the value to text field": function () {
        var value;
        var tempElement = document.createElement("INPUT");
        tempElement.type = "text";
        value = 'abc';
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, value);
        value = '123';
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, value);
        value = null;
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, "");
        value = [];
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, "");
        value = [999, 888, 777];
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, "999");
        value = "qwe122";
        IMLibElement.setValueToIMNode(tempElement, "", value, true);
        assert.equals(tempElement.value, value);
    },
    "IMLibElement.setValueToIMNode() has to set the value to checkbox": function () {
        var value;
        var tempElement = document.createElement("INPUT");
        tempElement.type = "checkbox";
        tempElement.value = "1";
        IMLibElement.setValueToIMNode(tempElement, "", 1, true);
        assert.equals(tempElement.checked, true);
        IMLibElement.setValueToIMNode(tempElement, "", "1", true);
        assert.equals(tempElement.checked, true);
        IMLibElement.setValueToIMNode(tempElement, "", 0, true);
        assert.equals(tempElement.checked, false);
        IMLibElement.setValueToIMNode(tempElement, "", -1, true);
        assert.equals(tempElement.checked, false);
        tempElement.value = "anytext";
        IMLibElement.setValueToIMNode(tempElement, "", "anytext", true);
        assert.equals(tempElement.checked, true);
        IMLibElement.setValueToIMNode(tempElement, "", "1", true);
        assert.equals(tempElement.checked, false);
        IMLibElement.setValueToIMNode(tempElement, "", 0, true);
        assert.equals(tempElement.checked, false);
        IMLibElement.setValueToIMNode(tempElement, "", -1, true);
        assert.equals(tempElement.checked, false);
    },
    "IMLibElement.setValueToIMNode() with # target has to add the value to node": function () {
        var value, value1, value2, attr = "href", tag = "a";
        var tempElement = document.createElement(tag);
        value = 'abc';
        IMLibElement.setValueToIMNode(tempElement, attr, value, true);
        assert.equals(tempElement.getAttribute(attr), value);
        value = '123';
        IMLibElement.setValueToIMNode(tempElement, attr, value, true);
        assert.equals(tempElement.getAttribute(attr), value);
        value = null;
        IMLibElement.setValueToIMNode(tempElement, attr, value, true);
        assert.equals(tempElement.getAttribute(attr), "");
        value = [];
        IMLibElement.setValueToIMNode(tempElement, attr, value, true);
        assert.equals(tempElement.getAttribute(attr), "");
        value = [999, 888, 777];
        IMLibElement.setValueToIMNode(tempElement, attr, value, true);
        assert.equals(tempElement.getAttribute(attr), String(value[0]));

        tempElement = document.createElement(tag);
        value1 = "base-url";
        tempElement.setAttribute(attr, value1);
        value2 = "params";
        IMLibElement.setValueToIMNode(tempElement, "#" + attr, value2, true);
        assert.equals(tempElement.getAttribute(attr), value1 + value2);
        value2 = "another";
        IMLibElement.setValueToIMNode(tempElement, "#" + attr, value2, true);
        assert.equals(tempElement.getAttribute(attr), value1 + value2);

        tempElement = document.createElement(tag);
        value1 = "base-url$";
        tempElement.setAttribute(attr, value1);
        value1 = "base-url";
        value2 = "params";
        IMLibElement.setValueToIMNode(tempElement, "$" + attr, value2, true);
        assert.equals(tempElement.getAttribute(attr), value1 + value2);
        value2 = "another";
        IMLibElement.setValueToIMNode(tempElement, "$" + attr, value2, true);
        assert.equals(tempElement.getAttribute(attr), value1 + value2);
    },
    "IMLibElement.setValueToIMNode() with innerHTML target": function () {
        var value, value1, value2, attr = "innerHTML", tag = "div";
        var tempElement = document.createElement(tag);
        value = 'abc';
        IMLibElement.setValueToIMNode(tempElement, attr, value, true);
        assert.equals(tempElement.innerHTML, value);
        value = '123';
        IMLibElement.setValueToIMNode(tempElement, attr, value, true);
        assert.equals(tempElement.innerHTML, value);
        value = null;
        IMLibElement.setValueToIMNode(tempElement, attr, value, true);
        assert.equals(tempElement.innerHTML, "");
        value = [];
        IMLibElement.setValueToIMNode(tempElement, attr, value, true);
        assert.equals(tempElement.innerHTML, "");
        value = [999, 888, 777];
        IMLibElement.setValueToIMNode(tempElement, attr, value, true);
        assert.equals(tempElement.innerHTML, String(value[0]));
        value = "<table><tbody><tr><td>aa</td></tr><tr><td>bb</td></tr></tbody></table>";
        IMLibElement.setValueToIMNode(tempElement, attr, value, true);
        assert.equals(tempElement.innerHTML, value);

        tempElement = document.createElement(tag);
        value1 = "<table><tbody><tr><td>aa</td></tr><tr><td>bb</td></tr></tbody></table>";
        tempElement.innerHTML = value1;
        value2 = "<div>ccc</div>";
        IMLibElement.setValueToIMNode(tempElement, "#" + attr, value2, true);
        assert.equals(tempElement.innerHTML, value1 + value2);
        value2 = "<p>ddd</p>";
        IMLibElement.setValueToIMNode(tempElement, "#" + attr, value2, true);
        assert.equals(tempElement.innerHTML, value1 + value2);

        tempElement = document.createElement(tag);
        value1 = "<table><tbody><tr><td>$</td></tr><tr><td>bb</td></tr></tbody></table>";
        tempElement.innerHTML = value1;
        value1 = "<table><tbody><tr><td>params</td></tr><tr><td>bb</td></tr></tbody></table>";
        value2 = "params";
        IMLibElement.setValueToIMNode(tempElement, "$" + attr, value2, true);
        assert.equals(tempElement.innerHTML, value1);
        value1 = "<table><tbody><tr><td>another</td></tr><tr><td>bb</td></tr></tbody></table>";
        value2 = "another";
        IMLibElement.setValueToIMNode(tempElement, "$" + attr, value2, true);
        assert.equals(tempElement.innerHTML, value1);
    },
    "IMLibElement.setValueToIMNode() has to set the date/time value to div": function () {
        var tempElement = document.createElement("input");
        tempElement.setAttribute("type","text");

        tempElement.setAttribute("data-im-format","date(<<%Y>>)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "<<2017>>");
        tempElement.setAttribute("data-im-format","date([%Y][%M])");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "[2017][07]");
        tempElement.setAttribute("data-im-format","date(%Y/%M/%D)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "2017/07/23");

        tempElement.setAttribute("data-im-format","date(%Y)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "2017");
        tempElement.setAttribute("data-im-format","date(%y)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "17");
        tempElement.setAttribute("data-im-format","date(%g)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "平成29年");
        tempElement.setAttribute("data-im-format","date(%M)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "07");
        tempElement.setAttribute("data-im-format","date(%m)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "7");
        tempElement.setAttribute("data-im-format","date(%B)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "7月");
        tempElement.setAttribute("data-im-format","date(%b)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, " 7");
        tempElement.setAttribute("data-im-format","date(%D)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "23");
        tempElement.setAttribute("data-im-format","date(%d)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "23");
        tempElement.setAttribute("data-im-format","date(%A)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "Sunday");
        tempElement.setAttribute("data-im-format","date(%a)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "Sun");
        tempElement.setAttribute("data-im-format","date(%W)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "日曜日");
        tempElement.setAttribute("data-im-format","date(%w)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "日");
        tempElement.setAttribute("data-im-format","date(%H)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "14");
        tempElement.setAttribute("data-im-format","date(%h)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "14");
        tempElement.setAttribute("data-im-format","date(%I)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "39");
        tempElement.setAttribute("data-im-format","date(%i)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "39");
        tempElement.setAttribute("data-im-format","date(%S)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "06");
        tempElement.setAttribute("data-im-format","date(%s)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "6");
        /*
        tempElement.setAttribute("data-im-format","date(%H %P)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "02 PM");
        tempElement.setAttribute("data-im-format","date(%H %p)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "02 pm");
        tempElement.setAttribute("data-im-format","date(%Z)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "JST");
        tempElement.setAttribute("data-im-format","date(%z)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "+0900");
        tempElement.setAttribute("data-im-format","date(20%%)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "+0900");


        tempElement.setAttribute("data-im-format","datetime(long)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "2017年07月23日 日曜日 14時39分06秒 JST");
        tempElement.setAttribute("data-im-format","date(long)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "2017年07月23日 日曜日");
        tempElement.setAttribute("data-im-format","time(long)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "14時39分06秒");
        tempElement.setAttribute("data-im-format","datetime(middle)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "2017/07/23(日) 14:39:06");
        tempElement.setAttribute("data-im-format","date(middle)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "2017/07/23(日)");
        tempElement.setAttribute("data-im-format","time(middle)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "14:39:06");
        tempElement.setAttribute("data-im-format","datetime(short)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "2017/07/23 14:39");
        tempElement.setAttribute("data-im-format","date(short)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "2017/07/23");
        tempElement.setAttribute("data-im-format","time(short)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "14:39");

        tempElement.setAttribute("data-im-format","time(  short  )");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "14:39");
        tempElement.setAttribute("data-im-format","time(short)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23", true);
        assert.equals(tempElement.value, "00:00");
        tempElement.setAttribute("data-im-format","time(Short)");
        IMLibElement.setValueToIMNode(tempElement, "", "2017-07-23 14:39:06", true);
        assert.equals(tempElement.value, "14:39");
        */
    }
});
