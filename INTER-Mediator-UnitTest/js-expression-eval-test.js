var assert = buster.referee.assert;

buster.testCase("Parser.evaluate Test", {
    "should be equal to": function () {
        assert.equals(Parser.evaluate("2 ^ x", {x: 3}), 8);
        assert.equals(Parser.evaluate("x + y", {x: 3, y: 5}), 8);
        assert.equals(Parser.evaluate("2 * x + 1", {x: 3}), 7);
        assert.equals(Parser.evaluate("2 + 3 * x", {x: 4}), 14);
        assert.equals(Parser.evaluate("(2 + 3) * x", {x: 4}), 20);
        assert.equals(Parser.evaluate("2-3^x", {x: 4}), -79);
        assert.equals(Parser.evaluate("-2-3^x", {x: 4}), -83);
        assert.equals(Parser.evaluate("-3^x", {x: 4}), -81);
        assert.equals(Parser.evaluate("(-3)^x", {x: 4}), 81);
        assert.equals(Parser.evaluate("(x+(x-3)*2)", {x: 5}), 9);
        assert.equals(Parser.evaluate("(x/(x-3)*2)", {x: 5}), 5);
        assert.equals(Parser.evaluate("x + y", {x: 5.1, y: 3.1}), 8.2);
    }
});

buster.testCase("Choice function Test", {
    "should be equal to": function () {
        assert.equals(Parser.evaluate("choice(x, a1, a2, a3)", {x: 0, a1: 'zero', a2: 1, a3: 2}), 'zero');
        assert.equals(Parser.evaluate("choice(x, a1, a2, a3)", {x: 1, a1: 'zero', a2: 1, a3: 2}), 1);
        assert.equals(Parser.evaluate("choice(x, a1, a2, a3)", {x: 2, a1: 'zero', a2: 1, a3: 2}), 2);
        assert.equals(Parser.evaluate("choice(x, a1, a2, a3)", {x: 3, a1: 'zero', a2: 1, a3: 2}), undefined);
        assert.equals(Parser.evaluate("choice(x, a1, a2, a3)", {x: 4, a1: 'zero', a2: 1, a3: 2}), undefined);
        assert.equals(Parser.evaluate("choice(x, a1, a2, a3)", {x: -1, a1: 'zero', a2: 1, a3: 2}), undefined);
        assert.equals(Parser.evaluate("choice(x, a1, a2, a3)", {x: null, a1: 'zero', a2: 1, a3: 2}), null);
        assert.equals(Parser.evaluate("choice(x, a1, a2, a3)", {x: undefined, a1: 'zero', a2: 1, a3: 2}), undefined);
    }
});

buster.testCase("Condition function Test", {
    "should be equal to": function () {
        assert.equals(Parser.evaluate(
            "condition(z<x1, a1, z<x2, a2, z<x3, a3)",
            {z: -5, x1: 0, a1: 120, x2: 10, a2: 130, x3:20, a3: 140}), 120);
        assert.equals(Parser.evaluate(
            "condition(z<x1, a1, z<x2, a2, z<x3, a3)",
            {z: 5, x1: 0, a1: 120, x2: 10, a2: 130, x3:20, a3: 140}), 130);
        assert.equals(Parser.evaluate(
            "condition(z<x1, a1, z<x2, a2, z<x3, a3)",
            {z: 15, x1: 0, a1: 120, x2: 10, a2: 130, x3:20, a3: 140}), 140);
        assert.equals(Parser.evaluate(
            "condition(z<x1, a1, z<x2, a2, z<x3, a3)",
            {z: 25, x1: 0, a1: 120, x2: 10, a2: 130, x3:20, a3: 140}), undefined);
        assert.equals(Parser.evaluate(
            "condition(z<x1, a1, z<x2, a2, z<x3)",
            {z: 5, x1: 0, a1: 120, x2: 10, a2: 130, x3:20, a3: 140}), 130);
        assert.equals(Parser.evaluate(
            "condition(z<x1, a1, z<x2, a2, z<x3)",
            {z: 15, x1: 0, a1: 120, x2: 10, a2: 130, x3:20, a3: 140}), undefined);
    }
});

buster.testCase("Accumulate function Test", {
    "should be equal to": function () {
        assert.equals(Parser.evaluate(
            "accumulate(z<x1, a1, z<x2, a2, z<x3, a3)",
            {z: -5, x1: 0, a1: 120, x2: 10, a2: 130, x3:20, a3: 140}), '120\n130\n140\n');
        assert.equals(Parser.evaluate(
            "accumulate(z<x1, a1, z<x2, a2, z<x3, a3)",
            {z: 5, x1: 0, a1: 120, x2: 10, a2: 130, x3:20, a3: 140}), '130\n140\n');
        assert.equals(Parser.evaluate(
            "accumulate(z<x1, a1, z<x2, a2, z<x3, a3)",
            {z: 15, x1: 0, a1: 120, x2: 10, a2: 130, x3:20, a3: 140}), '140\n');
        assert.equals(Parser.evaluate(
            "accumulate(z<x1, a1, z<x2, a2, z<x3, a3)",
            {z: 25, x1: 0, a1: 120, x2: 10, a2: 130, x3:20, a3: 140}), '');
        assert.equals(Parser.evaluate(
            "accumulate(z<x1, a1, z<x2, a2, z<x3)",
            {z: 5, x1: 0, a1: 120, x2: 10, a2: 130, x3:20, a3: 140}), '130\n');
        assert.equals(Parser.evaluate(
            "accumulate(z<x1, a1, z<x2, a2, z<x3)",
            {z: 15, x1: 0, a1: 120, x2: 10, a2: 130, x3:20, a3: 140}), '');
    }
});

buster.testCase("String operation Test", {
    "should be equal to": function () {
        assert.equals(0 + 0, 0);
        assert.equals('' + 0, "0");
        assert.equals(0 + '', "0");
        assert.equals('' + '', '');
        assert.equals(Parser.evaluate("x + y", {x: 0, y: 0}), 0);
        assert.equals(Parser.evaluate("x + y", {x: '', y: 0}), "0");
        assert.equals(Parser.evaluate("x + y", {x: 0, y: ''}), "0");
        assert.equals(Parser.evaluate("x + y", {x: '', y: ''}), '');
    }
});

buster.testCase("Operators Test", {
    "should be equal to": function () {
        assert.equals(Parser.evaluate("a = b ", {a: 100, b: 100}), true);
        assert.equals(Parser.evaluate("a = b ", {a: 99, b: 100}), false);
        assert.equals(Parser.evaluate("a == b ", {a: 100, b: 100}), true);
        assert.equals(Parser.evaluate("a == b ", {a: 99, b: 100}), false);
        assert.equals(Parser.evaluate("a >= b ", {a: 99, b: 100}), false);
        assert.equals(Parser.evaluate("a <= b ", {a: 99, b: 100}), true);
        assert.equals(Parser.evaluate("a > b ", {a: 99, b: 100}), false);
        assert.equals(Parser.evaluate("a < b ", {a: 99, b: 100}), true);
        assert.equals(Parser.evaluate("a != b ", {a: 99, b: 100}), true);
        assert.equals(Parser.evaluate("a != b ", {a: 100, b: 100}), false);
        assert.equals(Parser.evaluate("a <> b ", {a: 99, b: 100}), true);
        assert.equals(Parser.evaluate("a && b ", {a: true, b: false}), false);
        assert.equals(Parser.evaluate("a || b ", {a: true, b: false}), true);
        assert.equals(Parser.evaluate("a > 10 && b < 10", {a: 11, b: 9}), true);
        assert.equals(Parser.evaluate("a > 10 || b < 10", {a: 11, b: 12}), true);
    }
});
buster.testCase("Operators Test2", {
    "should be equal to": function () {
        assert.equals(Parser.evaluate("a + b ", {a: 'abc', b: 'def'}), 'abcdef');
        assert.equals(Parser.evaluate("a ⊕ b ", {a: 123, b: 456}), '123456');
        assert.equals(Parser.evaluate("a ⊕ b ", {a: '123', b: '456'}), '123456');
        assert.equals(Parser.evaluate("a - b ", {a: 'abcdef', b: 'def'}), 'abc');
        assert.equals(Parser.evaluate("a - b ", {a: 'abcdef', b: 'xyz'}), 'abcdef');
        assert.equals(Parser.evaluate("a - b ", {a: 'abcdef', b: 'dbfx'}), 'abcdef');
        assert.equals(Parser.evaluate("a ∩ b ", {a: 'abcdef', b: 'bdfx'}), 'bdf');
        assert.equals(Parser.evaluate("a ∪ b ", {a: 'abcdef', b: 'bdfx'}), 'abcdefx');
        assert.equals(Parser.evaluate("a ⊁ b ", {a: 'abcdef', b: 'bdfx'}), 'ace');
        assert.equals(Parser.evaluate("a ⋀ b ", {a: "abc\ndff\nghi", b: "dff\nstu\n"}), "dff\n");
        assert.equals(Parser.evaluate("a ⋀ b ", {a: "abc\rdff\r\nghi", b: "dff\r\nstu"}), "dff\r");
        assert.equals(Parser.evaluate("a ⋀ b ", {a: "abc\r\ndff\r\nghi", b: "dff\r\nstu"}), "dff\r\n");
        assert.equals(Parser.evaluate("a ⋁ b ", {a: "abc\ndff\nghi", b: "xyz\nstu\n"}), "abc\ndff\nghi\nxyz\nstu\n");
        assert.equals(Parser.evaluate("a ⊬ b ", {a: "abc\ndff\nghi", b: "ghi\ndkg\n"}), "abc\ndff\n");
    }
});

buster.testCase("Functions Test", {
    "should be equal to": function () {
        var x;
        assert.equals(Math.round(Parser.evaluate("sin(PI/4)") * 100), 71);
        assert.equals(Math.round(Parser.evaluate("cos(PI/4)") * 100), 71);
        assert.equals(Math.round(Parser.evaluate("tan(PI/4)") * 100), 100);
        assert.equals(Math.round(Parser.evaluate("asin(0.707106781186547)/PI*4*100")), 100);
        assert.equals(Math.round(Parser.evaluate("acos(0.707106781186547)/PI*4*100")), 100);
        assert.equals(Parser.evaluate("atan(1)/PI*4"), 1);
        assert.equals(Math.round(Parser.evaluate("sqrt(3)") * 100), 173);
        assert.equals(Parser.evaluate("abs(3.6)"), 3.6);
        assert.equals(Parser.evaluate("abs(-3.6)"), 3.6);
        assert.equals(Parser.evaluate("ceil(4.6)"), 5);
        assert.equals(Parser.evaluate("floor(4.6)"), 4);
        assert.equals(Parser.evaluate("round(4.6)"), 5);
        assert.equals(Parser.evaluate("ceil(4.4)"), 5);
        assert.equals(Parser.evaluate("floor(4.4)"), 4);
        assert.equals(Parser.evaluate("round(4.4)"), 4);
        assert.equals(Parser.evaluate("round(2837.4629, 0)"), 2837);
        assert.equals(Parser.evaluate("round(2837.4629, 1)"), 2837.5);
        assert.equals(Parser.evaluate("round(2837.4629, 2)"), 2837.46);
        assert.equals(Parser.evaluate("round(2837.4629, 6)"), 2837.4629);
        assert.equals(Parser.evaluate("round(2837.4629, -1)"), 2840);
        assert.equals(Parser.evaluate("round(2837.4629, -3)"), 3000);
        assert.equals(Parser.evaluate("round(2837.4629, -4)"), 0);
        assert.equals(Parser.evaluate("ceil(-4.6)"), -4);
        assert.equals(Parser.evaluate("floor(-4.6)"), -5);
        assert.equals(Parser.evaluate("round(-4.6)"), -5);
        assert.equals(Parser.evaluate("ceil(-4.4)"), -4);
        assert.equals(Parser.evaluate("floor(-4.4)"), -5);
        assert.equals(Parser.evaluate("round(-4.4)"), -4);
        assert.equals(Parser.evaluate("format(1500)"), "1,500");
        assert.equals(Parser.evaluate("format(1500.9)"), "1,501");
        assert.equals(Parser.evaluate("format(-1500)"), "-1,500");
        assert.equals(Parser.evaluate("format(-1500.9)"), "-1,501");
        assert.equals(Math.round(Parser.evaluate("exp(0.5)") * 100), 165);
        assert.equals(Math.round(Parser.evaluate("log(0.5)") * 100), -69);
        x = Parser.evaluate("random()");
        assert.equals(x > 0 && x < 1, true);
        x = Parser.evaluate("random()+1");
        assert.equals(x > 1 && x < 2, true);
        assert.equals(Parser.evaluate("pow(2,3)"), 8);
        assert.equals(Parser.evaluate("min(3,1,2,1,5,1)"), 1);
        assert.equals(Parser.evaluate("max(3,1,2,1,5,1)"), 5);
        assert.equals(Parser.evaluate("list(3,1,2,1,5,1)"), "3\n1\n2\n1\n5\n1\n");
        assert.equals(Parser.evaluate("fac(5)"), 120);
        assert.equals(Parser.evaluate("pyt(3,4)"), 5);
        assert.equals(Parser.evaluate("atan2(0.5, 0.5)/PI"), 0.25);

        assert.equals(Parser.evaluate("min(a)", {a: [3, 3, 2, 1, 5, 1]}), 1);
        assert.equals(Parser.evaluate("max(a)", {a: [3, 3, 2, 1, 5, 1]}), 5);

        assert.equals(Parser.evaluate("length(f)", {f: "Test"}), 4);
        assert.equals(Parser.evaluate("length(f)", {f: "日本語"}), 3);
        assert.equals(Parser.evaluate("length(f)", {f: -3152}), 5);
        assert.equals(Parser.evaluate("length(f)", {f: 23.5678}), 7);
        assert.equals(Parser.evaluate("length(f)", {f: true}), 4);
        assert.equals(Parser.evaluate("length(f)", {f: false}), 5);
        assert.equals(Parser.evaluate("length(f)", {f: "&lt;&amp;&gt;"}), 13); // not 3
    }
});

buster.testCase("INTER-Mediator Specific Calculation Test: ", {
    "Calculate integer values.": function () {
        var exp, vals, result;

        exp = "dog * cat";
        vals = {dog: [20], cat: [4]};
        result = Parser.evaluate(exp, vals);
        assert.equals(result, 80);
    },
    "Calculate integer and float values.": function () {
        var exp, vals, result;

        exp = "dog * cat";
        vals = {dog: [29], cat: [4.1]};
        result = Parser.evaluate(exp, vals);
        assert.equals(INTERMediatorLib.Round(result, 1), 118.9);
    },
    "Sum function and array variable.1": function () {
        var result = Parser.evaluate("sum(p)", {p: [1, 2, 3, 4, 5]});
        assert.equals(result, 15);
    },
    "Sum function and array variable.2": function () {
        var result = Parser.evaluate("sum(p)", {p: ['1,000', '1,000', '1,000', 5]});
        assert.equals(result, 3005);
    },
    "Sum function and array variable.3": function () {
        var result = Parser.evaluate("sum(p)", {p: [1.1, 1.1, 1.1, 5]});
        assert.equals(result, 8.3);
    },
    "Sum function and array variable.4": function () {
        var result = Parser.evaluate("sum(p)", {p: ["1,111,111", "1,111,111", "1,111,111"]});
        assert.equals(result, 3333333);
    },
    "If function and array variable.2": function () {
        var result = Parser.evaluate("if(a = 1,'b','c')", {a: [1]});
        assert.equals(result, 'b');
    },
    "If function and array variable.3": function () {
        var result = Parser.evaluate("if(a = 1,'b','c')", {a: [2]});
        assert.equals(result, 'c');
    },
    "If function and array variable.4": function () {
        var result = Parser.evaluate("if((a+1) = (1+b),'b'+c,'c'+c)", {a: [2], b: [4], c: 'q'});
        assert.equals(result, 'cq');
    },
    "If function and array variable.5": function () {
        var result = Parser.evaluate("if((a+1) = (1+b),'b'+c,'c'+c)", {a: [4], b: [4], c: 'q'});
        assert.equals(result, 'bq');
    },
//    "Triple items function": function () {
//        var result = Parser.evaluate("(a = 1) ? 'YES' : 'NO'", {a: [1]});
//        assert.equals(result, 'YES');
//    },

    "Calculate strings.": function () {
        var exp, vals, result;

        exp = "dog + cat";
        vals = {dog: ["Bowwow!"], cat: ["Mewww"]};
        result = Parser.evaluate(exp, vals);
        assert.equals(result, "Bowwow!Mewww");
    },
    "Calculate string and numeric.": function () {
        var exp, vals, result;

        exp = "dog + cat";
        vals = {dog: ["Bowwow!"], cat: ["4.3"]};
        result = Parser.evaluate(exp, vals);
        assert.equals(result, "Bowwow!4.3");
    },
    "String constant concat variable.": function () {
        var result = Parser.evaluate("'I\\'m a ' + exp + '.'", {exp: ['singer']});
        assert.equals(result, "I'm a singer.");
    },
    "String substract concat variable.": function () {
        var result = Parser.evaluate("exp - 'cc'", {exp: ['saccbaccda']});
        assert.equals(result, "sabada");
    },
    "Variables containing @ character.": function () {
        var result = Parser.evaluate("c@x + c@y", {'c@x': [20], 'c@y': [2]});
        assert.equals(result, 22);
    },
    "Japanese characters variables.": function () {
        var result = Parser.evaluate("テーブル@値1 + テーブル@値2", {'テーブル@値1': [20], 'テーブル@値2': [2]});
        assert.equals(result, 22);
    },
    "Wrong expression.1": function () {
        assert.exception(function () {
            Parser.evaluate("(a + b", {'a': [20], 'b': [2]})
        });
    },
    "Wrong expression.2": function () {
        assert.exception(function () {
            Parser.evaluate("a + b + malfunction(a)", {'a': [20], 'b': [2]})
        });
    },

    "each 3-digits should be devided.": function () {
        assert.equals(Parser.evaluate("format(999, 0)"), "999");
        assert.equals(Parser.evaluate("format(1000, 0)"), "1,000");
        assert.equals(Parser.evaluate("format(999999, 0)"), "999,999");
        assert.equals(Parser.evaluate("format(1000000, 0)"), "1,000,000");
        assert.equals(Parser.evaluate("format(1000000.678, 1)"), "1,000,000.7");
        assert.equals(Parser.evaluate("format(1000000.678, 2)"), "1,000,000.68");
        assert.equals(Parser.evaluate("format(1000000.678, 3)"), "1,000,000.678");
        assert.equals(Parser.evaluate("format(1000000.678, 4)"), "1,000,000.6780");
        assert.equals(Parser.evaluate("format(-1000000.678, 1)"), "-1,000,000.7");
        assert.equals(Parser.evaluate("format(-1000000.678, 2)"), "-1,000,000.68");
        assert.equals(Parser.evaluate("format(-1000000.678, 3)"), "-1,000,000.678");
        assert.equals(Parser.evaluate("format(999999, -1)"), "1,000,000");
        // A negative second parameter doesn't support so far.
    },

    "String functions.": function () {
        assert.equals(Parser.evaluate("substr('abcdefg', 3, 2)"), "de");
        assert.equals(Parser.evaluate("substring('abcdefg', 3, 5)"), "de");
        assert.equals(Parser.evaluate("indexof('abcdefg','cd')"), 2);
        assert.equals(Parser.evaluate("replace('abcdefgabc', 5, 8, 'yz')"), "abcdeyzbc");
        assert.equals(Parser.evaluate("substitute('abcdefgabc', 'bc', 'yz')"), "ayzdefgayz");
        assert.equals(Parser.evaluate("length('abcdefgabc')"), 10);
        assert.equals(Parser.evaluate("left('abcdefgabc',3)"), 'abc');
        assert.equals(Parser.evaluate("right('abcdefgabc',3)"), 'abc');
        assert.equals(Parser.evaluate("mid('abcdefgabc', 3, 3)"), 'def');
    },

    "String Items.": function () {
        var items = "abc\ndef\nght\njkl\nwer\ntfv";
        assert.equals(Parser.evaluate("items(x,0,1)", {x: items}), "abc\n");
        assert.equals(Parser.evaluate("items(x,2,2)", {x: items}), "ght\njkl\n");
        assert.equals(Parser.evaluate("items(x,4,2)", {x: items}), "wer\ntfv\n");
        assert.equals(Parser.evaluate("items(x,4,20)", {x: items}), "wer\ntfv\n");
        assert.equals(Parser.evaluate("items(x,4)", {x: items}), "wer\ntfv\n");
    },

    "date time functions.": function () {
        assert.greater(Parser.evaluate("date()"), 15000);
        assert.greater(Parser.evaluate("datetime()"), 40000000);
        if (false) {
            assert.equals(Parser.evaluate("date('1970-01-02')"), 1);
            assert.equals(Parser.evaluate("date('2014-02-17')"), 16118);
            //assert.equals(Parser.evaluate("date('2014-02-17 09:00:00')"), 16118); //browser dependency (Ch:ok, Ff:no)
            assert.equals(Parser.evaluate("datetime('1970-01-02 09:00:00')"), 86400);
            assert.equals(Parser.evaluate("datetime('2014-02-17 09:00:00')"), 16118 * 86400);
            assert.equals(Parser.evaluate("datetime('2014-02-17 09:23:49')"), 16118 * 86400 + 23 * 60 + 49);
            assert.equals(Parser.evaluate("datetime('2014-02-17')"), 16118 * 86400 - 9 * 3600);
            assert.equals(Parser.evaluate("datecomponents(2014,2,17)"), 16118);
            assert.equals(Parser.evaluate("datetimecomponents(2014,2,17,9,0,0)"), 1392627600);
            assert.equals(Parser.evaluate("date('2014-02-18')-date('2014-02-17')"), 1);
            assert.equals(Parser.evaluate("date('2014-03-18')-date('2014-02-18')"), 28);
            assert.equals(Parser.evaluate("date('2014-02-18')-date('2013-02-18')"), 365);
            assert.equals(Parser.evaluate("datetime('2014-02-17 09:00:01') - datetime('2014-02-17 09:00:00')"), 1);
            assert.equals(Parser.evaluate("datetime('2014-02-17 09:01:00') - datetime('2014-02-17 09:00:00')"), 60);
            assert.equals(Parser.evaluate("datetime('2014-02-17 10:00:00') - datetime('2014-02-17 09:00:01')"), 3599);
            assert.equals(Parser.evaluate("year(date('2014-02-17'))"), 2014);
            assert.equals(Parser.evaluate("month(date('2014-02-17'))"), 2);
            assert.equals(Parser.evaluate("day(date('2014-02-17'))"), 17);
            assert.equals(Parser.evaluate("weekday(date('2014-02-17'))"), 1);
            assert.equals(Parser.evaluate("yeard(date('2014-02-17'))"), 2014);
            assert.equals(Parser.evaluate("monthd(date('2014-02-17'))"), 2);
            assert.equals(Parser.evaluate("dayd(date('2014-02-17'))"), 17);
            assert.equals(Parser.evaluate("weekdayd(date('2014-02-17'))"), 1);
            assert.equals(Parser.evaluate("yeardt(datetime('2014-02-17 09:23:49'))"), 2014);
            assert.equals(Parser.evaluate("monthdt(datetime('2014-02-17 09:23:49'))"), 2);
            assert.equals(Parser.evaluate("daydt(datetime('2014-02-17 09:23:49'))"), 17);
            assert.equals(Parser.evaluate("weekdaydt(datetime('2014-02-17 09:23:49'))"), 1);
            assert.equals(Parser.evaluate("hourdt(datetime('2014-02-17 09:23:49'))"), 9);
            assert.equals(Parser.evaluate("minutedt(datetime('2014-02-17 09:23:49'))"), 23);
            assert.equals(Parser.evaluate("seconddt(datetime('2014-02-17 09:23:49'))"), 49);
            assert.equals(Parser.evaluate("addyear(date('2014-02-17'), 2)"), 16848);
            assert.equals(Parser.evaluate("addmonth(date('2014-02-17'), 2)"), 16177);
            assert.equals(Parser.evaluate("addday(date('2014-02-17'), 2)"), 16120);
            assert.equals(isNaN(Parser.evaluate("addhour(datetime('2014-02-17 09:23:49'), 2)")), true);
            assert.equals(isNaN(Parser.evaluate("addminute(datetime('2014-02-17 09:23:49'), 2)")), true);
            assert.equals(isNaN(Parser.evaluate("addsecond(datetime('2014-02-17 09:23:49'), 2)")), true);
            assert.equals(Parser.evaluate("addyeard(date('2014-02-17'), 2)"), 16118 + 730);
            assert.equals(Parser.evaluate("addmonthd(date('2014-02-17'), 2)"), 16118 + 59);
            assert.equals(Parser.evaluate("adddayd(date('2014-02-17'), 2)"), 16118 + 2);
            assert.equals(Parser.evaluate("addyeardt(datetime('2014-02-17 09:23:49'), 2)"), 1392629029 + 730 * 86400);
            assert.equals(Parser.evaluate("addmonthdt(datetime('2014-02-17 09:23:49'), 2)"), 1392629029 + 59 * 86400);
            assert.equals(Parser.evaluate("adddaydt(datetime('2014-02-17 09:23:49'), 2)"), 1392629029 + 2 * 86400);
            assert.equals(Parser.evaluate("addhourdt(datetime('2014-02-17 09:23:49'), 2)"), 1392629029 + 2 * 3600);
            assert.equals(Parser.evaluate("addminutedt(datetime('2014-02-17 09:23:49'), 2)"), 1392629029 + 2 * 60);
            assert.equals(Parser.evaluate("addseconddt(datetime('2014-02-17 09:23:49'), 2)"), 1392629029 + 2);
            assert.equals(Parser.evaluate("endofmonth(date('2014-02-17'))"), 16118 + 11);
            assert.equals(Parser.evaluate("startofmonth(date('2014-02-17'))"), 16118 - 16);
            assert.equals(Parser.evaluate("endofmonthd(date('2014-02-17'))"), 16118 + 11);
            assert.equals(Parser.evaluate("startofmonthd(date('2014-02-17'))"), 16118 - 16);
            assert.equals(Parser.evaluate("endofmonthdt(datetime('2014-02-17 09:23:49'))"), 1393664399);
            assert.equals(Parser.evaluate("startofmonthdt(datetime('2014-02-17 09:23:49'))"), 1391180400);
            assert.greater(Parser.evaluate("today()"), 15000);
            assert.greater(Parser.evaluate("now()"), 40000000);
        }
    },

    "String regular expression matiching.": function () {
        var str = "1234";
        assert.equals(Parser.evaluate("test(x,'[0-9]')", {x: str}), true);
        assert.equals(Parser.evaluate("test(x,'[^0-9]')", {x: str}), false);
        var r = Parser.evaluate("match(x,'[0-9]')", {x: str});
        assert.equals(r[0], "1");
        assert.equals(Parser.evaluate("match(x,'[^0-9]')", {x: str}), null);
        str = "12abc34";
        r = Parser.evaluate("match(x,'[0-9]([a-z]+)([0-9])')", {x: str});
        assert.equals(r[0], "2abc3");
        assert.equals(r[1], "abc");
        assert.equals(r[2], "3");
    },

    "String Items search.": function () {
        var items = "abc\ndef\n\njkl\nwer\ntfv";
        assert.equals(Parser.evaluate("itemIndexOf(x,'abc')", {x: items}), 0);
        assert.equals(Parser.evaluate("itemIndexOf(x,'def')", {x: items}), 1);
        assert.equals(Parser.evaluate("itemIndexOf(x,'tfv')", {x: items}), 5);
        assert.equals(Parser.evaluate("itemIndexOf(x,'wer')", {x: items}), 4);
        assert.equals(Parser.evaluate("itemIndexOf(x,'a')", {x: items}), -1);
        assert.equals(Parser.evaluate("itemIndexOf(x,'')", {x: items}), 2);
    }


});
