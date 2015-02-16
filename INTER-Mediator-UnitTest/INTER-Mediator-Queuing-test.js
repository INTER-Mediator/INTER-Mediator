var assert = buster.referee.assert;

var counter1 = 0, counter2 = 0, counter3 = 0;

buster.testCase("INTERMediatorQueue Test", {
    "depending tasks": function () {
        var m2 = INTERMediatorQueue.dependency.length;
        var m3 = INTERMediatorQueue.asyncTasks.length;
        INTERMediatorQueue.setSequencialTasks(
            [
                function (finishProc) {
                    console.log("===check point 5-1 ===");
                    setTimeout(function() {
                        var proc = finishProc;
                        console.log("===check point 5-2 ===");
                        proc();
                    }, 0);
                },
                function (finishProc) {
                    console.log("===check point 5-3 ===");
                    setTimeout(function() {
                        console.log("===check point 5-4 ===");
                        finishProc();
                    }, 0);
                },
                function (finishProc) {
                    console.log("===check point 5-5 ===");
                    setTimeout(function() {
                        console.log("===check point 5-6 ===");
                        finishProc();
                    }, 0);
                }]
        );
        assert.equals(INTERMediatorQueue.dependency.length, m2 + 2);
        assert.equals(INTERMediatorQueue.asyncTasks.length, m3 + 3);
        console.log("===check point 5-7 ===");
    },
    "should execute immidiately": function () {
        counter1 = 1;
        INTERMediatorQueue.setTask(function (finishProc) {
            console.log("===check point 1-1 ===");
            counter1++;
            assert.equals(counter1, 2);
            finishProc();
        }, true);
        INTERMediatorQueue.setTask(function (finishProc) {
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
        INTERMediatorQueue.setTask(function (finishProc) {
            console.log("===check point 2-1 ===");
            counter2++;
            assert.equals(counter2, 2);
            finishProc();
        });
        INTERMediatorQueue.setTask(function (finishProc) {
            console.log("===check point 2-2 ===");
            counter2++;
            assert.equals(counter2, 3);
            finishProc();
        });
        console.log("===check point 2-3 ===");
        assert.equals(counter2, 1);
    },
    "data relaying": function () {
        counter3 = 1;
        INTERMediatorQueue.setTask(function (finishProc) {
            console.log("===check point 3-1 ===");
            INTERMediatorQueue.setData("key1", 100);
            INTERMediatorQueue.setData("key2", 200);
            INTERMediatorQueue.setData("key3", 300);
            finishProc();
        });
        INTERMediatorQueue.setTask(function (finishProc) {
            console.log("===check point 3-2 ===");
            assert.equals(INTERMediatorQueue.takeData("key3"), 300);
            assert.equals(INTERMediatorQueue.takeData("key3"), undefined);
            finishProc();
        });
        INTERMediatorQueue.setTask(function (finishProc) {
            console.log("===check point 3-3 ===");
            assert.equals(INTERMediatorQueue.takeData("key2"), 200);
            INTERMediatorQueue.setData("key4", 400);
            counter3++;
            assert.equals(counter3, 2);
            finishProc();
        });
        INTERMediatorQueue.setTask(function (finishProc) {
            console.log("===check point 3-4 ===");
            assert.equals(INTERMediatorQueue.takeData("key4"), 400);
            assert.equals(INTERMediatorQueue.takeData("key1"), 100);
            finishProc();
        });
        console.log("===check point 3-5 ===");
        assert.equals(counter3, 1);
    },
    "should execute immidiately once more": function () {
        INTERMediatorQueue.setData("counter", 1);
        INTERMediatorQueue.setTask(function (finishProc) {
            console.log("===check point 4-1 ===");
            var counter = INTERMediatorQueue.takeData("counter");
            assert.equals(counter, 1);
            INTERMediatorQueue.setData("counter", counter + 1);
            finishProc();
        }, true);
        INTERMediatorQueue.setTask(function (finishProc) {
            console.log("===check point 4-2 ===");
            var counter = INTERMediatorQueue.takeData("counter");
            assert.equals(counter, 2);
            INTERMediatorQueue.setData("counter", counter + 1);
            finishProc();
        }, true);
        console.log("===check point 4-3 ===");
        var counter = INTERMediatorQueue.takeData("counter");
        assert.equals(counter, 3);
    }
});
