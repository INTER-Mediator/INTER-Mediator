var assert = buster.assertions.assert;

buster.testCase("INTERMediatorOnPage.getMessages() Test", {
    "should return null": function () {
        assert.equals(INTERMediatorOnPage.getMessages(), null);
    }
});
