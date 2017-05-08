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
 * @fileoverview IMLibEventResponder class is defined here.
 */
/**
 *
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
IMLibEventResponder = {
    touchEventCancel: false,

    isSetup: false,

    setup: function () {
        var body;

        if (IMLibEventResponder.isSetup) {
            return;
        }

        IMLibEventResponder.isSetup = true;
        IMLibChangeEventDispatch = new IMLibEventDispatch();
        IMLibKeyEventDispatch = new IMLibEventDispatch();
        IMLibMouseEventDispatch = new IMLibEventDispatch();
        body = document.getElementsByTagName('BODY')[0];

        INTERMediatorLib.addEvent(body, 'change', function (e) {
            //console.log('Event Dispatcher: change');
            var event = e ? e : window.event;
            if (!event) {
                return;
            }
            var target = event.target;
            if (!target) {
                target = event.srcElement;
                if (!target) {
                    return;
                }
            }
            var idValue = target.id;
            if (!idValue) {
                return;
            }
            var executable = IMLibChangeEventDispatch.dispatchTable[idValue];
            if (!executable) {
                return;
            }
            executable(idValue);
        });
        INTERMediatorLib.addEvent(body, 'keydown', function (e) {
            //console.log('Event Dispatcher: keydown');
            var event, charCode, target, idValue;
            event = e ? e : window.event;
            if (event.charCode) {
                charCode = event.charCode;
            } else {
                charCode = event.keyCode;
            }
            if (!event) {
                return;
            }
            target = event.target;
            if (!target) {
                target = event.srcElement;
                if (!target) {
                    return;
                }
            }
            idValue = target.id;
            if (!idValue) {
                return;
            }
            if (!IMLibKeyEventDispatch.dispatchTable[idValue]) {
                return;
            }
            var executable = IMLibKeyEventDispatch.dispatchTable[idValue][charCode];
            if (!executable) {
                return;
            }
            executable(event);
        });
        INTERMediatorLib.addEvent(body, 'click', function (e) {
            //console.log('Event Dispatcher: click');
            var event, target, idValue, executable, targetDefs, i, nodeInfo, value;
            event = e ? e : window.event;
            if (!event) {
                return;
            }
            target = event.target;
            if (!target) {
                target = event.srcElement;
                if (!target) {
                    return;
                }
            }
            idValue = target.id;
            if (!idValue) {
                return;
            }
            executable = IMLibMouseEventDispatch.dispatchTable[idValue];
            if (executable) {
                executable(event);
                return;
            }
            targetDefs = INTERMediatorLib.getLinkedElementInfo(target);
            for (i = 0; i < targetDefs.length; i++) {
                executable = IMLibMouseEventDispatch.dispatchTableTarget[targetDefs[i]];
                if (executable) {
                    nodeInfo = INTERMediatorLib.getNodeInfoArray(targetDefs[i]);
                    if (nodeInfo.target) {
                        value = target.getAttribute(nodeInfo.target);
                    } else {
                        value = IMLibElement.getValueFromIMNode(target);
                    }
                    executable(value, target);
                    return;
                }
            }
        });


    }
};

var IMLibChangeEventDispatch;
var IMLibKeyEventDispatch;
var IMLibMouseEventDispatch;

function IMLibEventDispatch() {
    this.dispatchTable = {};
    this.dispatchTableTarget = {};

    this.clearAll = function () {
        this.dispatchTable = {};
        this.dispatchTableTarget = {};
    };

    this.setExecute = function (idValue, exec) {
        if (idValue && exec) {
            this.dispatchTable[idValue] = exec;
        }
    };

    this.setTargetExecute = function (targetValue, exec) {
        if (targetValue && exec) {
            this.dispatchTableTarget[targetValue] = exec;
        }
    };

    this.setExecuteByCode = function (idValue, charCode, exec) {
        if (idValue && charCode) {
            if (!this.dispatchTable[idValue]) {
                this.dispatchTable[idValue] = {};
            }
            this.dispatchTable[idValue][charCode] = exec;
        }
    };
}



