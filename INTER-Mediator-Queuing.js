/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

/**
 * @fileoverview IMLibQueue class is defined here.
 */
/**
 *
 * Usually you don't have to instanciate this class with new operator.
 * Thanks for nice idea from: http://stackoverflow.com/questions/17718673/how-is-a-promise-defer-library-implemented
 * @constructor
 */
var IMLibQueue = {
    tasks: [],
    isExecute: false,
    dataStore: {},
    dsLabel: 0,
    readyTo: false,

    getNewLabel: function () {
        IMLibQueue.dsLabel++;
        return IMLibQueue.dsLabel;
    },

    getDataStore: function (label, key) {
        if (!IMLibQueue.dataStore[label]) {
            IMLibQueue.dataStore[label] = {};
        }
        return IMLibQueue.dataStore[label][key];
    },

    setDataStore: function (label, key, value) {
        if (!IMLibQueue.dataStore[label]) {
            IMLibQueue.dataStore[label] = {};
        }
        return IMLibQueue.dataStore[label][key] = value;
    },

    setTask: function (aTask, startHere) {
        if (startHere) {
            IMLibQueue.isExecute = true;
            aTask(function () {
            });
            IMLibQueue.isExecute = false;
        } else {
            IMLibQueue.tasks.push(aTask);
            if (!IMLibQueue.readyTo) {
                setTimeout(IMLibQueue.startNextTask, 0);
                IMLibQueue.readyTo = true;
            }
        }
    },

    setPriorTask: function (aTask) {
        IMLibQueue.tasks.unshift(aTask);
        if (!IMLibQueue.readyTo) {
            setTimeout(IMLibQueue.startNextTask, 0);
            IMLibQueue.readyTo = true;
        }
    },

    setSequentialTasks: function (tasksArray) {
        Array.prototype.push.apply(IMLibQueue.tasks, tasksArray);
        if (!IMLibQueue.readyTo) {
            setTimeout(IMLibQueue.startNextTask, 0);
            IMLibQueue.readyTo = true;
        }
    },

    setSequentialPriorTasks: function (tasksArray) {
        Array.prototype.push.apply(tasksArray, IMLibQueue.tasks);
        IMLibQueue.tasks = tasksArray;
        if (!IMLibQueue.readyTo) {
            setTimeout(IMLibQueue.startNextTask, 0);
            IMLibQueue.readyTo = true;
        }
    },

    startNextTask: function () {
        if (IMLibQueue.isExecute) {
            if (IMLibQueue.tasks.length > 0) {
                setTimeout(IMLibQueue.startNextTask, 0);
                IMLibQueue.readyTo = true;
            }
            return;
        }
        if (IMLibQueue.tasks.length > 0) {
            var aTask = IMLibQueue.tasks.shift();
            IMLibQueue.isExecute = true;
            IMLibQueue.readyTo = false;
            aTask(function () {
                IMLibQueue.isExecute = false;
                if (IMLibQueue.tasks.length > 0) {
                    setTimeout(IMLibQueue.startNextTask, 0);
                    IMLibQueue.readyTo = true;
                }
            });
        }
    }
};