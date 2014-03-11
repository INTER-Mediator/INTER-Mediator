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
    "Sum function and array variable.": function () {
        var result = Parser.evaluate("sum(p)", {p: [1, 2, 3, 4, 5]});
        assert.equals(result, 15);
    },
    "If function and array variable.": function () {
        var result = Parser.evaluate("if(a = 1,'b','c')", {a: [1]});
        assert.equals(result, 'b');
    },
    "If function and array variable.": function () {
        var result = Parser.evaluate("if(a = 1,'b','c')", {a: [2]});
        assert.equals(result, 'c');
    },

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
    }
});
