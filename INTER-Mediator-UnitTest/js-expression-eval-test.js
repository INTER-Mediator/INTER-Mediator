var assert = buster.assertions.assert;

buster.testCase("Parser.evaluate Test", {
    "should be equal to": function () {
        assert.equals(Parser.evaluate("2 ^ x", { x: 3 }), 8);
        assert.equals(Parser.evaluate("x + y", { x: 3, y: 5 }), 8);
        assert.equals(Parser.evaluate("2 * x + 1", { x: 3 }), 7);
        assert.equals(Parser.evaluate("2 + 3 * x", { x: 4 }), 14);
        assert.equals(Parser.evaluate("(2 + 3) * x", { x: 4 }), 20);
        assert.equals(Parser.evaluate("2-3^x", { x: 4 }), -79);
        assert.equals(Parser.evaluate("-2-3^x", { x: 4 }), -83);
        assert.equals(Parser.evaluate("-3^x", { x: 4 }), -81);
        assert.equals(Parser.evaluate("(-3)^x", { x: 4 }), 81);
        assert.equals(Parser.evaluate("(x+(x-3)*2)", { x: 5 }), 9);
        assert.equals(Parser.evaluate("(x/(x-3)*2)", { x: 5 }), 5);
    }
});
buster.testCase("Operators Test", {
    "should be equal to": function () {
        assert.equals(Parser.evaluate("a = b ", { a: 100, b: 100 }), true);
        assert.equals(Parser.evaluate("a = b ", { a: 99, b: 100 }), false);
        assert.equals(Parser.evaluate("a == b ", { a: 100, b: 100 }), true);
        assert.equals(Parser.evaluate("a == b ", { a: 99, b: 100 }), false);
        assert.equals(Parser.evaluate("a >= b ", { a: 99, b: 100 }), false);
        assert.equals(Parser.evaluate("a <= b ", { a: 99, b: 100 }), true);
        assert.equals(Parser.evaluate("a > b ", { a: 99, b: 100 }), false);
        assert.equals(Parser.evaluate("a < b ", { a: 99, b: 100 }), true);
        assert.equals(Parser.evaluate("a != b ", { a: 99, b: 100 }), true);
        assert.equals(Parser.evaluate("a != b ", { a: 100, b: 100 }), false);
        assert.equals(Parser.evaluate("a <> b ", { a: 99, b: 100 }), true);
        assert.equals(Parser.evaluate("a && b ", { a: true, b: false }), false);
        assert.equals(Parser.evaluate("a || b ", { a: true, b: false }), true);
        assert.equals(Parser.evaluate("a > 10 && b < 10", { a: 11, b: 9 }), true);
        assert.equals(Parser.evaluate("a > 10 || b < 10", { a: 11, b: 12 }), true);
    }
});
buster.testCase("Operators Test2", {
    "should be equal to": function () {
        assert.equals(Parser.evaluate("a + b ", { a: 'abc', b: 'def' }), 'abcdef');
        assert.equals(Parser.evaluate("a - b ", { a: 'abcdef', b: 'def' }), 'abc');
        assert.equals(Parser.evaluate("a - b ", { a: 'abcdef', b: 'xyz' }), 'abcdef');
        assert.equals(Parser.evaluate("a - b ", { a: 'abcdef', b: 'dbfx' }), 'abcdef');
        assert.equals(Parser.evaluate("a ∩ b ", { a: 'abcdef', b: 'bdfx' }), 'bdf');
        assert.equals(Parser.evaluate("a ∪ b ", { a: 'abcdef', b: 'bdfx' }), 'abcdefx');
        assert.equals(Parser.evaluate("a ⊁ b ", { a: 'abcdef', b: 'bdfx' }), 'ace');
        assert.equals(Parser.evaluate("a ⋀ b ", { a: "abc\ndff\nghi", b: "dff\nstu\n" }), "dff\n");
        assert.equals(Parser.evaluate("a ⋀ b ", { a: "abc\rdff\r\nghi", b: "dff\r\nstu" }), "dff\r");
        assert.equals(Parser.evaluate("a ⋀ b ", { a: "abc\r\ndff\r\nghi", b: "dff\r\nstu" }), "dff\r\n");
        assert.equals(Parser.evaluate("a ⋁ b ", { a: "abc\ndff\nghi", b: "xyz\nstu\n" }), "abc\ndff\nghi\nxyz\nstu\n");
        assert.equals(Parser.evaluate("a ⊬ b ", { a: "abc\ndff\nghi", b: "ghi\ndkg\n" }), "abc\ndff\n");
    }
});

buster.testCase("Functions Test", {
    "should be equal to": function () {
        assert.equals(Parser.evaluate("sin(PI/4)"), 0.7071067811865475);
        assert.equals(Parser.evaluate("cos(PI/4)"), 0.7071067811865475);
        assert.equals(Parser.evaluate("tan(PI/4)"), 1);
        assert.equals(Math.round(Parser.evaluate("asin(0.707106781186547)/PI*4*100")), 100);
        assert.equals(Math.round(Parser.evaluate("acos(0.707106781186547)/PI*4*100")), 100);
        assert.equals(Parser.evaluate("atan(1)/PI*4"), 1);
        assert.equals(Parser.evaluate("sqrt(3)"), 1.7320508075688772);
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
        assert.equals(Parser.evaluate("exp(0.5)"), 1.6487212707001282);
        assert.equals(Parser.evaluate("log(0.5)"), -0.6931471805599453);
        var x = Parser.evaluate("random()");
        assert.equals(x > 0 && x < 1, true);
        var x = Parser.evaluate("random()+1");
        assert.equals(x > 1 && x < 2, true);
        assert.equals(Parser.evaluate("pow(2,3)"), 8);
        assert.equals(Parser.evaluate("min(3,1,2,1,5,1)"), 1);
        assert.equals(Parser.evaluate("max(3,1,2,1,5,1)"), 5);
        assert.equals(Parser.evaluate("fac(5)"), 120);
        assert.equals(Parser.evaluate("pyt(3,4)"), 5);
        assert.equals(Parser.evaluate("atan2(0.5, 0.5)/PI"), 0.25);

        assert.equals(Parser.evaluate("min(a)", {a: [3,3,2,1,5,1]}), 1);
        assert.equals(Parser.evaluate("max(a)", {a: [3,3,2,1,5,1]}), 5);
    }
});

buster.testCase("INTER-Mediator Specific Calculation Test: ", {
    "Calculate integer values.": function () {
        var exp, vals, result;

        exp = "dog * cat";
        vals = {dog: [20], cat: [4]}
        result = Parser.evaluate(exp, vals);
        assert.equals(result, 80);
    },
    "Calculate integer and float values.": function () {
        var exp, vals, result;

        exp = "dog * cat";
        vals = {dog: [29], cat: [4.1]}
        result = Parser.evaluate(exp, vals);
        assert.equals(INTERMediatorLib.Round(result, 1), 118.9);
    },
    "Sum function and array variable.1": function () {
        var result = Parser.evaluate("sum(p)", {p: [1, 2, 3, 4, 5]});
        assert.equals(result, 15);
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
        vals = {dog: ["Bowwow!"], cat: ["Mewww"]}
        result = Parser.evaluate(exp, vals);
        assert.equals(result, "Bowwow!Mewww");
    },
    "Calculate string and numeric.": function () {
        var exp, vals, result;

        exp = "dog + cat";
        vals = {dog: ["Bowwow!"], cat: ["4.3"]}
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
        assert.equals(Parser.evaluate("format(999999, -1)"), "999,999.0");
        // A negative second parameter doesn't support so far.
    },

    "String functions.": function () {
        assert.equals(Parser.evaluate("substr('abcdefg', 3, 2)"), "de");
        assert.equals(Parser.evaluate("substring('abcdefg', 3, 5)"), "de");
        assert.equals(Parser.evaluate("indexof('abcdefg','cd')"), 2);
        assert.equals(Parser.evaluate("replace('abcdefgabc', 5, 8, 'yz')"), "abcdeyzbc");
        assert.equals(Parser.evaluate("substitute('abcdefgabc', 'bc', 'yz')"), "ayzdefgayz");
    },

    "String Items.": function () {
        var items = "abc\ndef\nght\njkl\nwer\ntfv";
        assert.equals(Parser.evaluate("items(x,0,1)", {x: items}), "abc\n");
        assert.equals(Parser.evaluate("items(x,2,2)", {x: items}), "ght\njkl\n");
        assert.equals(Parser.evaluate("items(x,4,2)", {x: items}), "wer\ntfv\n");
        assert.equals(Parser.evaluate("items(x,4,20)", {x: items}), "wer\ntfv\n");
        assert.equals(Parser.evaluate("items(x,4)", {x: items}), "wer\ntfv\n");
    }

});

