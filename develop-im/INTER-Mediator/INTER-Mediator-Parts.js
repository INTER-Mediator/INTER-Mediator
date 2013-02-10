/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

var IMParts_tinymce = {
    instanciate: function(parentNode) {
        var newId = parentNode.getAttribute('id') + '-e';
        this.ids.push(newId);
        var newNode = document.createElement( 'TEXTAREA' );
        newNode.setAttribute('id', newId);
        INTERMediatorLib.setClassAttributeToNode(newNode, '_im_tinymce');
        newNode.blur = function(){alert("#");};
        parentNode.appendChild(newNode);
        this.ids.push(newId);

        newNode._im_getValue = function()    {
            var targetNode = newNode;
            return targetNode.value;
        };
        parentNode._im_getValue = function()    {
            var targetNode = newNode;
            return targetNode.value;
        };
        parentNode._im_getComponentId = function()    {
            var theId = newId;
            return theId;
        };

        // This method will be called before tinyMCE isn't initialized
        parentNode._im_setValue = function(str)    {
            var targetNode = newNode;
            targetNode.innerHTML = str;
        };
    },
    ids: [],
    finish: function()  {
        if ( ! tinymceOption )   {
            tinymceOption = {};
        }
        tinymceOption['mode'] = 'specific_textareas';
        tinymceOption['editor_selector'] = '_im_tinymce';
        tinymceOption['elements'] = this.ids.join(',');
        tinymceOption.setup = function (ed) {
            ed.onChange.add(function (ed, ev) {
                INTERMediator.valueChange(ed.id);
            });
            ed.onKeyDown.add(function (ed, ev) {
                INTERMediator.keyDown(ev);
            });
            ed.onKeyUp.add(function (ed, ev) {
                INTERMediator.keyUp(ev);
            });
        };

        tinyMCE.init(tinymceOption);

        for( var i = 0 ; this.ids.length ; i++ )    {
            var targetNode = document.getElementById(this.ids[i]);
            targetNode._im_getValue = function()    {
                return tinymce.EditorManager.get(this.id).getContent();
            };
        }
    }
};
