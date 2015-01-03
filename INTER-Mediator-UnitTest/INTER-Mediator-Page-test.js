var assert = buster.referee.assert;

buster.testCase("INTERMediatorOnPage.getMessages() Test", {
    "should return null": function () {
        assert.equals(INTERMediatorOnPage.getMessages(), null);
    }
});
