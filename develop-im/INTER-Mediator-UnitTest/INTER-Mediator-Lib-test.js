var assert = buster.assertions.assert;

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
    "small integer should not be converted.":   function()  {
        assert.equals(INTERMediatorLib.numberFormat(45,0),  "45");
        assert.equals(INTERMediatorLib.numberFormat(45.678,1),  "45.7");
        assert.equals(INTERMediatorLib.numberFormat(45.678,2),  "45.68");
        assert.equals(INTERMediatorLib.numberFormat(45.678,3),  "45.678");
        assert.equals(INTERMediatorLib.numberFormat(45.123,1),  "45.1");
        assert.equals(INTERMediatorLib.numberFormat(45.123,2),  "45.12");
        assert.equals(INTERMediatorLib.numberFormat(45.123,3),  "45.123");
    },
    "each 3-digits should be devided.":   function()  {
        assert.equals(INTERMediatorLib.numberFormat(999,0),  "999");
        assert.equals(INTERMediatorLib.numberFormat(1000,0),  "1,000");
        assert.equals(INTERMediatorLib.numberFormat(999999,0),  "999,999");
        assert.equals(INTERMediatorLib.numberFormat(1000000,0),  "1,000,000");
        assert.equals(INTERMediatorLib.numberFormat(1000000.678,1),  "1,000,000.7");
        assert.equals(INTERMediatorLib.numberFormat(1000000.678,2),  "1,000,000.68");
        assert.equals(INTERMediatorLib.numberFormat(1000000.678,3),  "1,000,000.678");
        assert.equals(INTERMediatorLib.numberFormat(1000000.678,4),  "1,000,000.6780");
        assert.equals(INTERMediatorLib.numberFormat(-1000000.678,1),  "-1,000,000.7");
        assert.equals(INTERMediatorLib.numberFormat(-1000000.678,2),  "-1,000,000.68");
        assert.equals(INTERMediatorLib.numberFormat(-1000000.678,3),  "-1,000,000.678");
        assert.equals(INTERMediatorLib.numberFormat(999999,-1),  "999,999.0");
        // A negative second parameter doesn't support so far.
    }
})