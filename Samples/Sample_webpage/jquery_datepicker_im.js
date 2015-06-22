/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */

IMParts_Catalog["jquery_datepicker"] = {
    instanciate: function (targetNode) {
        var nodeId = targetNode.getAttribute('id');
        this.ids.push(nodeId);

        targetNode._im_getComponentId = function () {
            var theId = nodeId;
            return theId;
        };
        targetNode._im_setValue = function (str) {
            var aNode = targetNode;
            aNode.value = str;
        };
        targetNode._im_getValue = function (str) {
            var aNode = targetNode;
            return aNode.value;
        };
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
