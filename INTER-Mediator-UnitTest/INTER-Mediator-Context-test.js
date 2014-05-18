var assert = buster.assertions.assert;

buster.testCase("Local Context Test", {
    "Local-Context-Test1": function () {
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

buster.testCase("Remote Context Test", {
    "Remote-Context-Test1": function () {
        var context1 = new IMLibContext("test");
        context1.setValue('1', 'test', 'value');
        assert.equals(context1.getValue('1', 'test'), 'value');
        assert.equals(context1.getValue('2', 'test'), null);
        assert.equals(context1.getValue('1', 'unexist-key'), null);
        context1.clearAll();
        assert.equals(context1.getValue('1', 'test'), null);
        context1.setValue('1', 'test1', 'value1');
        context1.setValue('1', 'test2', 'value2');
        context1.setValue('1', 'test3', 'value3');
        context1.setValue('1', 'test4', 'value4');
        assert.equals(context1.getValue('1', 'test1'), 'value1');
        assert.equals(context1.getValue('1', 'test2'), 'value2');
        assert.equals(context1.getValue('1', 'test3'), 'value3');
        assert.equals(context1.getValue('1', 'test4'), 'value4');

    },
    "Remote-Context-Test2": function () {
        var i, j;
        IMLibContextPool.clearAll();
        var context1 = new IMLibContext("context1");
        assert.equals(IMLibContextPool.poolingContexts.length, 1);
        var context2 = new IMLibContext("context2");
        assert.equals(IMLibContextPool.poolingContexts.length, 2);
        var context3 = new IMLibContext("context3");
        assert.equals(IMLibContextPool.poolingContexts.length, 3);
        var context = [context1, context2, context3];

        var counter = 1;
        for (j = 0; j < 3; j++) {
            context[j].setTableName("table");
            context[j].setViewName("table");
            for (i = 1; i < 4; i++) {
                context[j].setValue(i, 'test1', 'value1', 'node-'+(counter++));
                context[j].setValue(i, 'test2', 'value2', 'node-'+(counter++));
                context[j].setValue(i, 'test3', 'value3', 'node-'+(counter++));
                context[j].setValue(i, 'test4', 'value4', 'node-'+(counter++));
            }
        }
        assert.equals(context1.getValue(1, 'test1'), 'value1');
        assert.equals(context1.getValue(2, 'test2'), 'value2');
        assert.equals(context1.getValue(3, 'test3'), 'value3');

//        console.log(context);

        context1.setValue(1, 'test1', 'change1');
        context2.setValue(2, 'test2', 'change2');

//       console.log(context);

        for (j = 0; j < 3; j++) {
            for (i = 1; i < 4; i++) {
                assert.equals(context[j].getValue(i, 'test1'), i == 1 ? 'change1' : 'value1');
                assert.equals(context[j].getValue(i, 'test2'), i == 2 ? 'change2' : 'value2');
                assert.equals(context[j].getValue(i, 'test3'), 'value3');
                assert.equals(context[j].getValue(i, 'test4'), 'value4');
            }
        }
    }
});