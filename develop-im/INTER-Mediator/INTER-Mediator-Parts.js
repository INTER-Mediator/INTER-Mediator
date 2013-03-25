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

        for( var i = 0 ; i < this.ids.length ; i++ )    {
            var targetNode = document.getElementById(this.ids[i]);
            if (targetNode) {
                targetNode._im_getValue = function()    {
                    return tinymce.EditorManager.get(this.id).getContent();
                };
            }
        }
    }
};

var IMParts_im_fileupload = {
    html5DDSuported: false,
    instanciate: function(parentNode) {
        var newId = parentNode.getAttribute('id') + '-e';
        var newNode = document.createElement( 'DIV' );
        INTERMediatorLib.setClassAttributeToNode(newNode, '_im_fileupload');
        newNode.setAttribute('id', newId);
        IMParts_im_fileupload.ids.push(newId);
        if ( false /*newNode.hasOwnProperty('dropzone') */) {
            IMParts_im_fileupload.html5DDSuported = true;
            newNode.dropzone = "copy";
        } else {
            IMParts_im_fileupload.html5DDSuported = false;
            var formNode = document.createElement( 'FORM' );
            formNode.setAttribute('method','post');
            formNode.setAttribute('action',INTERMediatorOnPage.getEntryPath());
            formNode.setAttribute('enctype','multipart/form-data');
            newNode.appendChild(formNode);
            var inputNode = document.createElement( 'INPUT' );
            inputNode.setAttribute('type', 'file');
            inputNode.setAttribute('name', '_im_uploadfile');
            formNode.appendChild(inputNode);
            var buttonNode = document.createElement( 'BUTTON' );
            buttonNode.setAttribute('type', 'submit');
            buttonNode.appendChild(document.createTextNode('送信'));
            formNode.appendChild(buttonNode);
        }
        parentNode.appendChild(newNode);

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

        parentNode._im_setValue = function(str)    {
            var targetNode = newNode;
            if ( IMParts_im_fileupload.html5DDSuported )    {
                targetNode.innerHTML = str;
            } else {

            }
        };
    },
    ids: [],
    finish: function()  {
        if ( IMParts_im_fileupload.html5DDSuported ) {
            for( var i = 0 ; i < IMParts_im_fileupload.ids.length ; i++ )    {
                var targetNode = document.getElementById(IMParts_im_fileupload.ids[i]);
                if (targetNode) {
                    INTERMediatorLib.addEvent(targetNode, "dragenter",function(event){
                        event.preventDefault();
                        event.target.style.backgourndColor = "red";
                    });
                    INTERMediatorLib.addEvent(targetNode, "dragleave",function(event){
                        event.preventDefault();
                        event.target.style.backgourndColor = "blue";
                    });
                    INTERMediatorLib.addEvent(targetNode, "dragover",function(event){
                        event.preventDefault();
                        event.target.style.backgourndColor = "green";
                    });
                    INTERMediatorLib.addEvent(targetNode, "drop",function(event){
                        event.preventDefault();
                        for(var i = 0 ; i < event.dataTransfer.items.length ; i++){
                            var data = event.dataTransfer.items[i];

                            if( data.kind == "file" ){
                                var file = data.getAsFile();

                                var fileName =document.createElement("DIV");
                                fileName.appendChild( document.createTextNode("Draged File: " + file.name));
                                event.target.appendChild(fileName);
                            }
                        }
                        var reader = new FileReader();
                        var request = new XMLHttpRequest();
                        reader.onload = function(evt) {
                            reader.readAsBinaryString(file);
                        };
                    });
                }
            }

        } else {
            for( var i = 0 ; i < IMParts_im_fileupload.ids.length ; i++ )    {
                var targetNode = document.getElementById(IMParts_im_fileupload.ids[i]);
                if (targetNode) {
                    var updateInfo = INTERMediator.updateRequiredObject[IMParts_im_fileupload.ids[i]];
                    var formNode = targetNode.getElementsByTagName('FORM')[0];
                    var inputNode = document.createElement( 'INPUT' );
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', '_im_redirect');
                    inputNode.setAttribute('value', location.href);
                    formNode.appendChild(inputNode);
                    inputNode = document.createElement( 'INPUT' );
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', '_im_contextname');
                    inputNode.setAttribute('value', updateInfo['name']);
                    formNode.appendChild(inputNode);
                    inputNode = document.createElement( 'INPUT' );
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', '_im_field');
                    inputNode.setAttribute('value', updateInfo['field']);
                    formNode.appendChild(inputNode);
                    inputNode = document.createElement( 'INPUT' );
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', '_im_keyfield');
                    inputNode.setAttribute('value', updateInfo['keying'].split("=")[0]);
                    formNode.appendChild(inputNode);
                    inputNode = document.createElement( 'INPUT' );
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', '_im_keyvalue');
                    inputNode.setAttribute('value', updateInfo['keying'].split("=")[1]);
                    formNode.appendChild(inputNode);
                    inputNode = document.createElement( 'INPUT' );
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', '_im_contextnewrecord');
                    inputNode.setAttribute('value', 'uploadfile');
                    formNode.appendChild(inputNode);
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', 'access');
                    inputNode.setAttribute('value', 'uploadfile');
                    formNode.appendChild(inputNode);

                    INTERMediatorLib.addEvent(formNode, "submit",function(event){
                    });
                }
            }
        }
    }
};
