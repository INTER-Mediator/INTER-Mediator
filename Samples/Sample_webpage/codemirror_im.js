/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */

IMParts_Catalog["codemirror"] = {
    instanciate: function (parentNode) {
        var newId = parentNode.getAttribute('id') + '-cm';
        var newNode = document.createElement('TEXTAREA');
        newNode.setAttribute('id', newId);
        INTERMediatorLib.setClassAttributeToNode(newNode, '_im_codemirror');
        parentNode.appendChild(newNode);
        this.ids.push(newId);

        parentNode._im_getComponentId = function () {
            var theId = newId;
            return theId;
        };

        var self = this;
        parentNode._im_setValue = function (str) {
            var theId = newId;
            self.initialValues[theId] = str;
        };
    },

    ids: [],
    initialValues: {},
    mode: "htmlmixed",

    finish: function () {
        for (var i = 0; i < this.ids.length; i++) {
            var targetId = this.ids[i];
            var targetNode = document.getElementById(targetId);
            if (targetNode) {
                var editor = CodeMirror.fromTextArea(targetNode, {mode: this.mode});
                editor.setValue(this.initialValues[targetId]);
                editor.on("change", function () {
                    var nodeId = targetId;
                    return function (instance, obj) {
                        IMLibUI.valueChange(nodeId)
                    };
                }());
                targetNode._im_getValue = function () {
                    var insideEditor = editor;
                    return function () {
                        return insideEditor.getValue();
                    }
                }();
            }
        }
        this.ids = [];
        this.initialValues = {};
    }
}
