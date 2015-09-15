var assert = buster.referee.assert;

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
                context[j].setValue(i, 'test1', 'value1', 'node-' + (counter++));
                context[j].setValue(i, 'test2', 'value2', 'node-' + (counter++));
                context[j].setValue(i, 'test3', 'value3', 'node-' + (counter++));
                context[j].setValue(i, 'test4', 'value4', 'node-' + (counter++));
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
    },
    "Remote-Context-Test3": function () {
        /*
        Object.defineProperty(INTERMediator, 'startFrom', {
            get: function () {
                return INTERMediator.getLocalProperty("_im_startFrom", 0);
            },
            set: function (value) {
                INTERMediator.setLocalProperty("_im_startFrom", value);
            }
        });
        Object.defineProperty(INTERMediator, 'pagedSize', {
            get: function () {
                return INTERMediator.getLocalProperty("_im_pagedSize", 0);
            },
            set: function (value) {
                INTERMediator.setLocalProperty("_im_pagedSize", value);
            }
        });
        Object.defineProperty(INTERMediator, 'additionalCondition', {
            get: function () {
                return INTERMediator.getLocalProperty("_im_additionalCondition", {});
            },
            set: function (value) {
                INTERMediator.setLocalProperty("_im_additionalCondition", value);
            }
        });
        Object.defineProperty(INTERMediator, 'additionalSortKey', {
            get: function () {
                return INTERMediator.getLocalProperty("_im_additionalSortKey", {});
            },
            set: function (value) {
                INTERMediator.setLocalProperty("_im_additionalSortKey", value);
            }
        });

        if (!INTERMediator.additionalCondition) {
            INTERMediator.additionalCondition = {};
        }

        if (!INTERMediator.additionalSortKey) {
            INTERMediator.additionalSortKey = {};
        }
*/

        var context1 = new IMLibContext("test");
        context1.sequencing = true;
        context1.setValue('id=1', 'field1', 10);
        context1.setValue('id=1', 'field2', '500');
        context1.setValue('id=1', 'field3', 'value');
        context1.setValue('id=2', 'field1', 20);
        context1.setValue('id=3', 'field1', 30);
        context1.setValue('id=4', 'field1', 40);
        context1.setValue('id=5', 'field1', 50);
        context1.setValue('id=6', 'field1', 60);
        context1.setValue('id=6', 'field2', 500);
        context1.setValue('id=7', 'field1', 60);
        context1.setValue('id=7', 'field2', 510);
        context1.setValue('id=8', 'field1', 60);
        context1.setValue('id=8', 'field2', 520);
        context1.sequencing = false;
        context1.setValue('id=9', 'field1', 25);
        context1.setValue('id=10', 'field1', 45);
        context1.setValue('id=11', 'field1', 9999);
        context1.setValue('id=12', 'field1', -100);
        context1.setValue('id=13', 'field1', 60);
        context1.setValue('id=13', 'field2', 490);
        context1.setValue('id=14', 'field1', 60);
        context1.setValue('id=14', 'field2', 515);
        context1.setValue('id=15', 'field1', 60);
        context1.setValue('id=15', 'field2', 555);
        assert.equals(context1.recordOrder.length, 8);
        assert.equals(context1.pendingOrder.length, 7);
        assert.equals(context1.recordOrder[0], "id=1");
        assert.equals(context1.recordOrder[5], "id=6");
        INTERMediator.additionalSortKey["test"] = [{field: "test", direction: "ASC"}];
        assert.equals(context1.checkOrder({field1: 45}, true), 3);
        assert.equals(context1.checkOrder({field1: 50}, true), 4);
        assert.equals(context1.checkOrder({field1: 55}, true), 4);
        assert.equals(context1.checkOrder({field1: 60, field2: 505}, true), 5);
        assert.equals(context1.checkOrder({field1: 60, field2: 515}, true), 6);
        assert.equals(context1.checkOrder({field1: 60, field2: 99}, true), 4);
        assert.equals(context1.checkOrder({field1: 60, field2: 999}, true), 7);
        assert.equals(context1.checkOrder({field1: -1}, true), -1);
        assert.equals(context1.checkOrder({field1: 550}, true), 7);
        //console.log("context1.recordOrder="+context1.recordOrder.toString());
        context1.rearrangePendingOrder(true);
        assert.equals(context1.recordOrder.length, 15);
        assert.equals(Object.keys(context1.store).length, 15);
        assert.equals(context1.pendingOrder.length, 0);
        //console.log("context1.recordOrder="+context1.recordOrder.toString());
        assert.equals(context1.recordOrder[0], "id=12");
        assert.equals(context1.recordOrder[1], "id=1");
        assert.equals(context1.recordOrder[2], "id=2");
        assert.equals(context1.recordOrder[3], "id=9");
        assert.equals(context1.recordOrder[4], "id=3");
        assert.equals(context1.recordOrder[5], "id=4");
        assert.equals(context1.recordOrder[6], "id=10");
        assert.equals(context1.recordOrder[7], "id=5");
        assert.equals(context1.recordOrder[8], "id=13");
        assert.equals(context1.recordOrder[9], "id=6");
        assert.equals(context1.recordOrder[10], "id=7");
        assert.equals(context1.recordOrder[11], "id=14");
        assert.equals(context1.recordOrder[12], "id=8");
        assert.equals(context1.recordOrder[13], "id=15");
        assert.equals(context1.recordOrder[14], "id=11");
    }
});