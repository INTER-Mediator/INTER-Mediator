var assert = buster.assertions.assert;

buster.testCase("INTERMediatorOnPage.getMessages() Test", {
    "should return null": function () {
        assert.equals(INTERMediatorOnPage.getMessages(), null);
    }
});

buster.testCase("Local Context Test", {
    "should return null": function () {
        IMLibLocalContext.setValue('test', 'value');
        assert.equals(IMLibLocalContext.getValue('test'), 'value');
        assert.equals(IMLibLocalContext.getValue('unexist-key'), null);
        IMLibLocalContext.clearAll();
        assert.equals(IMLibLocalContext.getValue('test'), null);
        IMLibLocalContext.setValue('test1', 'value1');
        IMLibLocalContext.setValue('test2', 'value2');
        IMLibLocalContext.setValue('test3', 'value3');
        IMLibLocalContext.setValue('test4', 'value4');
        IMLibLocalContext.archive();
        IMLibLocalContext.clearAll();
        assert.equals(IMLibLocalContext.getValue('test1'), null);
        IMLibLocalContext.unarchive();
        assert.equals(IMLibLocalContext.getValue('test1'), 'value1');
        assert.equals(IMLibLocalContext.getValue('test2'), 'value2');
        assert.equals(IMLibLocalContext.getValue('test3'), 'value3');
        assert.equals(IMLibLocalContext.getValue('test4'), 'value4');

    }
});