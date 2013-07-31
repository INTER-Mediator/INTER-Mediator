var assert = buster.assertions.assert;
buster.testCase("repeaterTagFromEncTag() Test", {
        "should return 'TR' if parameter is 'TBODY'" : function(){
            assert.equals(INTERMediatorLib.repeaterTagFromEncTag("TBODY"), "TR");
        }
})