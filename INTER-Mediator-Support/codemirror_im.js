/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

IMParts_Catalog["codemirror"] = {
    instanciate: function (parentNode) {
        var newId = parentNode.getAttribute('id') + '-cm';
        var newNode = document.createElement('TEXTAREA');
        newNode.setAttribute('id', newId);
        INTERMediatorLib.setClassAttributeToNode(newNode, '_im_codemirror');
        parentNode.appendChild(newNode);
        this.ids.push(newId);

        parentNode._im_getComponentId = (function () {
            var theId = newId;
            return function () {
                return theId;
            }
        })();

        parentNode._im_setValue = (function () {
            var self = IMParts_Catalog["codemirror"];
            var theId = newId;
            return function (str) {
                self.initialValues[theId] = str;
            }
        })();
    },

    ids: [],
    initialValues: {},
    mode: "htmlmixed",

    finish: function () {
        for (var i = 0; i < this.ids.length; i++) {
            var targetId = this.ids[i];
            var targetNode = document.getElementById(targetId);
            if (targetNode) {
                var editor = CodeMirror.fromTextArea(targetNode, {
                    mode: this.mode,
                    lineNumbers: true,
                    viewportMargin: Infinity,
                    autoRefresh: true
                });
                editor.setValue(this.initialValues[targetId]);
                editor.on("change", function () {
                    var nodeId = targetId;
                    return function (instance, obj) {
                        IMLibUI.valueChange(nodeId);
                    };
                }());
                targetNode._im_getValue = function () {
                    var insideEditor = editor;
                    return function () {
                        return insideEditor.getValue();
                    };
                }();
            }
        }
        this.ids = [];
        this.initialValues = {};
    }
};
