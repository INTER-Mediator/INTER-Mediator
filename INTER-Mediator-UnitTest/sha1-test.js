var assert = buster.referee.assert;

buster.testCase("SHA1() Test:", {
    "Valid password hash should be generated using SHA1()": function () {
        assert.equals(SHA1("1234").length, 40);
    },
    "The result of SHA1() should be SHA-1 based hash": function () {
        assert.equals(SHA1("1234"), "7110eda4d09e062aa5e4a390b0a572ac0d2c0220");
    }
});
