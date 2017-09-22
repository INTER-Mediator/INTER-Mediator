var assert = buster.referee.assert;

var counter1 = 0, counter2 = 0, counter3 = 0, counter4 = 0, counter5 = 0, counter6 = 0;

buster.testCase("IMLibQueue Test", {
    // Depended tasks ware supported in those days, but now not.
    // "depending tasks": function () {
    //     var m2 = IMLibQueue.dependency.length;
    //     var m3 = IMLibQueue.asyncTasks.length;
    //     IMLibQueue.setSequencialTasks(
    //         [
    //             function (finishProc) {
    //                 console.log("===check point 5-1 ===");
    //                 setTimeout(function() {
    //                     var proc = finishProc;
    //                     console.log("===check point 5-2 ===");
    //                     proc();
    //                 }, 0);
    //             },
    //             function (finishProc) {
    //                 console.log("===check point 5-3 ===");
    //                 setTimeout(function() {
    //                     console.log("===check point 5-4 ===");
    //                     finishProc();
    //                 }, 0);
    //             },
    //             function (finishProc) {
    //                 console.log("===check point 5-5 ===");
    //                 setTimeout(function() {
    //                     console.log("===check point 5-6 ===");
    //                     finishProc();
    //                 }, 0);
    //             }]
    //     );
    //     assert.equals(IMLibQueue.dependency.length, m2 + 2);
    //     assert.equals(IMLibQueue.asyncTasks.length, m3 + 3);
    //     console.log("===check point 5-7 ===");
    // },
    "should execute immidiately": function () {
        counter1 = 1;
        IMLibQueue.setTask(function (finishProc) {
            console.log("===check point 1-1 ===");
            counter1++;
            assert.equals(counter1, 2);
            finishProc();
        }, true);
        IMLibQueue.setTask(function (finishProc) {
            console.log("===check point 1-2 ===");
            counter1++;
            assert.equals(counter1, 3);
            finishProc();
        }, true);
        console.log("===check point 1-3 ===");
        assert.equals(counter1, 3);
    },
    "execute lately": function () {
        counter2 = 1;
        IMLibQueue.setTask(function (finishProc) {
            console.log("===check point 2-1 ===");
            counter2++;
            assert.equals(counter2, 2);
            finishProc();
        });
        IMLibQueue.setTask(function (finishProc) {
            console.log("===check point 2-2 ===");
            counter2++;
            assert.equals(counter2, 3);
            finishProc();
        });
        console.log("===check point 2-3 ===");
        assert.equals(counter2, 1);
    },
    "execute sequential tasks": function () {
        counter5 = 1;
        IMLibQueue.setTask(function (finishProc) {
            console.log("===check point 5-1 ===");
            counter5++;
            assert.equals(counter5, 2);
            finishProc();
        });
        IMLibQueue.setSequentialTasks([function (finishProc) {
            console.log("===check point 5-2 ===");
            counter5++;
            assert.equals(counter5, 3);
            finishProc();
        },function (finishProc) {
            console.log("===check point 5-3 ===");
            counter5++;
            assert.equals(counter5, 4);
            finishProc();
        },function (finishProc) {
            console.log("===check point 5-4 ===");
            counter5++;
            assert.equals(counter5, 5);
            finishProc();
        },function (finishProc) {
            console.log("===check point 5-5 ===");
            counter5++;
            assert.equals(counter5, 6);
            finishProc();
        }]);
        IMLibQueue.setTask(function (finishProc) {
            console.log("===check point 5-6 ===");
            counter5++;
            assert.equals(counter5, 7);
            finishProc();
        });
        console.log("===check point 5-7 ===");
        assert.equals(counter5, 1);
    },
    "execute sequential tasks reverse order": function () {
        counter6 = 1;
        IMLibQueue.setPriorTask(function (finishProc) {
            console.log("===check point 6-1 ===");
            counter6++;
            assert.equals(counter6, 7);
            finishProc();
        });
        IMLibQueue.setSequentialPriorTasks([function (finishProc) {
            console.log("===check point 6-2 ===");
            counter6++;
            assert.equals(counter6, 3);
            finishProc();
        },function (finishProc) {
            console.log("===check point 6-3 ===");
            counter6++;
            assert.equals(counter6, 4);
            finishProc();
        },function (finishProc) {
            console.log("===check point 6-4 ===");
            counter6++;
            assert.equals(counter6, 5);
            finishProc();
        },function (finishProc) {
            console.log("===check point 6-5 ===");
            counter6++;
            assert.equals(counter6, 6);
            finishProc();
        }]);
        IMLibQueue.setPriorTask(function (finishProc) {
            console.log("===check point 6-6 ===");
            counter6++;
            assert.equals(counter6, 2);
            finishProc();
        });
        console.log("===check point 6-7 ===");
        assert.equals(counter6, 1);
    },
    "execute lately with reverse order": function () {
        counter4 = 1;
        IMLibQueue.setPriorTask(function (finishProc) {
            console.log("===check point 4-1 ===");
            counter4++;
            assert.equals(counter4, 3);
            finishProc();
        });
        IMLibQueue.setPriorTask(function (finishProc) {
            console.log("===check point 4-2 ===");
            counter4++;
            assert.equals(counter4, 2);
            finishProc();
        });
        console.log("===check point 4-3 ===");
        assert.equals(counter4, 1);
    },
    "data relaying": function () {
        var label = IMLibQueue.getNewLabel();
        counter3 = 1;
        IMLibQueue.setTask(function (finishProc) {
            var l = label;
            console.log("===check point 3-1 ===");
            IMLibQueue.setDataStore(l, "key1", 100);
            IMLibQueue.setDataStore(l, "key2", 200);
            IMLibQueue.setDataStore(l, "key3", 300);
            finishProc();
        });
        IMLibQueue.setTask(function (finishProc) {
            var l = label;
            console.log("===check point 3-2 ===");
            assert.equals(IMLibQueue.getDataStore(l, "key3"), 300);
            finishProc();
        });
        IMLibQueue.setTask(function (finishProc) {
            var l = label;
            console.log("===check point 3-3 ===");
            assert.equals(IMLibQueue.getDataStore(l, "key2"), 200);
            IMLibQueue.setDataStore(l, "key4", 400);
            counter3++;
            assert.equals(counter3, 2);
            finishProc();
        });
        IMLibQueue.setTask(function (finishProc) {
            var l = label;
            console.log("===check point 3-4 ===");
            assert.equals(IMLibQueue.getDataStore(l, "key4"), 400);
            assert.equals(IMLibQueue.getDataStore(l, "key1"), 100);
            finishProc();
        });
        console.log("===check point 3-5 ===");
        assert.equals(counter3, 1);
    },
    "should execute immidiately once more": function () {
        var label = IMLibQueue.getNewLabel();
        IMLibQueue.setDataStore(label, "counter", 100);
        IMLibQueue.setTask(function (finishProc) {
            var l = label;
            var c = IMLibQueue.getDataStore(l, "counter");
            console.log("===check point 11-1 ===");
            assert.equals(c, 100);
            IMLibQueue.setDataStore(l, "counter", c + 1);
            finishProc();
        }, true);
        IMLibQueue.setTask(function (finishProc) {
            var l = label;
            var c = IMLibQueue.getDataStore(l, "counter");
            console.log("===check point 11-2 ===");
            assert.equals(c, 101);
            IMLibQueue.setDataStore(l, "counter", c + 1);
            assert.equals(IMLibQueue.getDataStore(l, "counter"), 102);
            finishProc();
        }, true);
        console.log("===check point 11-3 ===");
        var c = IMLibQueue.getDataStore(label, "counter");
        IMLibQueue.setDataStore(label, "counter", c + 1);
        assert.equals(IMLibQueue.getDataStore(label, "counter"), 103);
    }
});
