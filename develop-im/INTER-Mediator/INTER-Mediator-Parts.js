/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

/**
 * TinyMCE bridgeObject
 * @type {{instanciate: Function, ids: Array, finish: Function}}
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

/*********
 *
 * File Uploader
 * @type {{html5DDSuported: boolean, instanciate: Function, ids: Array, finish: Function}}
 */
var IMParts_im_fileupload = {
    html5DDSuported: false,
    instanciate: function(parentNode) {
        var newId = parentNode.getAttribute('id') + '-e';
        var newNode = document.createElement( 'DIV' );
        INTERMediatorLib.setClassAttributeToNode(newNode, '_im_fileupload');
        newNode.setAttribute('id', newId);
        IMParts_im_fileupload.ids.push(newId);
        IMParts_im_fileupload.html5DDSuported = true;
        try {
            var x = new FileReader();
        } catch(ex) {
            IMParts_im_fileupload.html5DDSuported = false;
        }
        if (IMParts_im_fileupload.html5DDSuported) {
            newNode.dropzone = "copy";
            newNode.style.width = "200px";
            newNode.style.height = "150px";
            newNode.style.paddingTop = "40px";
            newNode.style.backgroundColor = "#AAAAAA";
            newNode.style.border = "3px dotted #808080";
            newNode.style.align = "center;"
            newNode.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[3101]));
        } else {
            var formNode = document.createElement( 'FORM' );
            formNode.setAttribute('method','post');
            formNode.setAttribute('action',INTERMediatorOnPage.getEntryPath());
            formNode.setAttribute('enctype','multipart/form-data');
            newNode.appendChild(formNode);
            var inputNode = document.createElement( 'INPUT' );
            inputNode.setAttribute('type', 'file');
            inputNode.setAttribute('accept', '*/*');
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
            //    targetNode.innerHTML = str;
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
                    var idmyself = (function(){
                        var myid = IMParts_im_fileupload.ids[i];
                        return function() {
                            return myid;
                        }
                    })();
                    INTERMediatorLib.addEvent(targetNode, "dragleave",function(event){
                        event.preventDefault();
                        event.target.style.backgroundColor = "#AAAAAA";
                    });
                    INTERMediatorLib.addEvent(targetNode, "dragover",function(event){
                        event.preventDefault();
                        event.target.style.backgroundColor = "#AAFFAA";
                    });
                    INTERMediatorLib.addEvent(targetNode, "drop",function(event){
                        var file, fileNameNode;
                        event.preventDefault();
                        for(var i = 0 ; i < event.dataTransfer.items.length ; i++){
                            var data = event.dataTransfer.items[i];

                            if( data.kind == "file" ){
                                file = data.getAsFile();
                                fileNameNode = document.createElement("DIV");
                                fileNameNode.appendChild( document.createTextNode(
                                    INTERMediatorOnPage.getMessages()[3102] + file.name));
                                fileNameNode.style.marginTop = "20px";
                                fileNameNode.style.backgroundColor = "#FFFFFF";
                                fileNameNode.style.textAlign = "center";
                                event.target.appendChild(fileNameNode);
                            }
                        }
                        var dragedFileName = file.name;
                        var reader = new FileReader();
                        reader.readAsBinaryString(file);
                        reader.onload = function(evt) {
                            var updateInfo = INTERMediator.updateRequiredObject[idmyself()];
                            INTERMediator_DBAdapter.uploadFile(
                                '&_im_contextname=' + encodeURIComponent(updateInfo['name'])
                                    + '&_im_field=' + encodeURIComponent(updateInfo['field'])
                                    + '&_im_keyfield=' + encodeURIComponent(updateInfo['keying'].split("=")[0])
                                    + '&_im_keyvalue=' + encodeURIComponent(updateInfo['keying'].split("=")[1])
                                    + '&_im_contextnewrecord=' + encodeURIComponent('uploadfile'),
                                {
                                    fileName: dragedFileName,
                                    content: evt.target.result
                                });
                            INTERMediator.flushMessage();
                            INTERMediator.construct(true);
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
                    inputNode = document.createElement( 'INPUT' );
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', 'access');
                    inputNode.setAttribute('value', 'uploadfile');
                    formNode.appendChild(inputNode);
                    inputNode = document.createElement( 'INPUT' );
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', 'clientid');
                    formNode.appendChild(inputNode);
                    inputNode = document.createElement( 'INPUT' );
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', 'authuser');
                    formNode.appendChild(inputNode);
                    inputNode = document.createElement( 'INPUT' );
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', 'response');
                    formNode.appendChild(inputNode);

                    INTERMediatorLib.addEvent(formNode, "submit",function(event){
                        var thisForm = formNode;
                        if (INTERMediatorOnPage.authUser.length > 0) {
                            thisForm.elements["clientid"].value = INTERMediatorOnPage.clientId;
                            thisForm.elements["authuser"].value = INTERMediatorOnPage.authUser;
                            if (INTERMediatorOnPage.isNativeAuth) {
                                thisForm.elements["response"].value = INTERMediatorOnPage.publickey.biEncryptedString(
                                    INTERMediatorOnPage.authHashedPassword + "\n" + INTERMediatorOnPage.authChallenge);
                            } else {
                                if (INTERMediatorOnPage.authHashedPassword && INTERMediatorOnPage.authChallenge) {
                                    shaObj = new jsSHA(INTERMediatorOnPage.authHashedPassword, "ASCII");
                                    hmacValue = shaObj.getHMAC(INTERMediatorOnPage.authChallenge, "ASCII", "SHA-256", "HEX");
                                    thisForm.elements["response"].value = hmacValue;
                                } else {
                                    thisForm.elements["response"].value = "dummy";
                                }
                            }
                        }
                        return true;
                    });
                }
            }
        }
    }
};
