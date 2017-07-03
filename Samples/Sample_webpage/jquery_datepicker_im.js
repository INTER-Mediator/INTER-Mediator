/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

IMParts_Catalog["jquery_datepicker"] = {
    instanciate: function (targetNode) {
        var nodeId = targetNode.getAttribute('id');
        this.ids.push(nodeId);

        targetNode._im_getComponentId = (function () {
            var theId = nodeId;
            return function () {
                return theId;
            };
        })();
        targetNode._im_setValue = (function () {
            var aNode = targetNode;
            return function (str) {
                aNode.value = str;
            };
        })();
        targetNode._im_getValue = (function () {
            var aNode = targetNode;
            return function () {
                return aNode.value;
            };
        })();
    },

    ids: [],

    finish: function () {
        for (var i = 0; i < this.ids.length; i++) {
            var targetId = this.ids[i];
            var targetNode = $('#'+targetId);
            if (targetNode) {
                targetNode.datepicker({
                    onSelect: function(dateText) {
                        this.value = dateText;
                        IMLibUI.valueChange(this.id);
                    }
                });
             }
        }
        this.ids = [];
    }
};
