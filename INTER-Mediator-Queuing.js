/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */

/*
 http://stackoverflow.com/questions/17718673/how-is-a-promise-defer-library-implemented
 */
var INTERMediatorQueue = {
    tasks: {},
    dependency: [],
    asyncTasks: [],
    previousData: {},
    isExecute: false,
    counter: 0,

    setTask: function (aTask, startHere) {
        if (startHere) {
            INTERMediatorQueue.isExecute = true;
            aTask(function () {});
            INTERMediatorQueue.isExecute = false;
        } else {
            var serial = INTERMediatorQueue.counter++;
            INTERMediatorQueue.tasks[serial] = aTask;
            setTimeout(INTERMediatorQueue.startNextTask, 0);
        }
    },

    setSequencialTasks: function (tasksArray) {
        var i;
        for (i = 0; i < tasksArray.length; i++) {
            var serial = INTERMediatorQueue.counter++;
            INTERMediatorQueue.tasks[serial] = tasksArray[i];
            if (i > 0) {
                INTERMediatorQueue.dependency.push(serial);
            }
            INTERMediatorQueue.asyncTasks.push(serial);
        }
        setTimeout(INTERMediatorQueue.startNextTask, 0);
    },

    setData: function (key, data) {
        INTERMediatorQueue.previousData[key] = data;
    },

    takeData: function (key) {
        var data = INTERMediatorQueue.previousData[key];
        delete INTERMediatorQueue.previousData[key];
        return data;
    },

    startNextTask: function () {
        if (INTERMediatorQueue.isExecute || INTERMediatorQueue.tasks.length == 0) {
            return;
        }
        for (var taskId in INTERMediatorQueue.tasks) {
            var aTask = INTERMediatorQueue.tasks[taskId];
            if (INTERMediatorQueue.dependency.indexOf(taskId) < 0
                || (   !(INTERMediatorQueue.dependency.indexOf(taskId) < 0)
                    && INTERMediatorQueue.asyncTasks.indexOf(taskId-1) < 0)) {
                INTERMediatorQueue.isExecute = true;
                aTask(function () {
                    var serial = taskId;
                    delete INTERMediatorQueue.tasks[serial];
                    INTERMediatorQueue.isExecute = false;
                    setTimeout(INTERMediatorQueue.startNextTask, 0);
                });
            } else {
                setTimeout(INTERMediatorQueue.startNextTask, 0);
            }
            break;
        }
    }
};