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
