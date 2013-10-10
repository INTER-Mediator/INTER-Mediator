var assert = buster.assertions.assert;
buster.testCase("repeaterTagFromEncTag() Test", {
    "should return 'TR' if parameter is 'TBODY'" : function(){
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag("TBODY"), "TR");
    },
    "should return 'OPTION' if parameter is 'SELECT'" : function(){
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag("SELECT"), "OPTION");
    },
    "should return 'LI' if parameter is 'UL'" : function(){
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag("UL"), "LI");
    },
    "should return 'LI' if parameter is 'OL'" : function(){
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag("OL"), "LI");
    },
    "should return 'DIV' if parameter is 'DIV'" : function(){
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag("DIV"), "DIV");
    },
    "should return 'SPAN' if parameter is 'SPAN'" : function(){
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag("SPAN"), "SPAN");
    },
    "should return null if parameter is 'BODY'" : function(){
        assert.equals(INTERMediatorLib.repeaterTagFromEncTag("BODY"), null);
    }

})