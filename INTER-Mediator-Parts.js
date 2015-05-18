/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */


/**
 * TinyMCE bridgeObject
 * @type {{instanciate: Function, ids: Array, finish: Function}}
 */

//"use strict"

var IMParts_Catalog = {};
/*********
 *
 * File Uploader
 * @type {{html5DDSuported: boolean, instanciate: Function, ids: Array, finish: Function}}
 */
IMParts_Catalog["fileupload"] = {
    html5DDSuported: false,
    progressSupported: false,   // see http://www.johnboyproductions.com/php-upload-progress-bar/
    forceOldStyleForm: false,
    uploadId: "sign" + Math.random(),
    instanciate: function (parentNode) {
        var inputNode, formNode, buttonNode, hasTapEvent;
        var newId = parentNode.getAttribute('id') + '-e';
        var newNode = document.createElement('DIV');
        INTERMediatorLib.setClassAttributeToNode(newNode, '_im_fileupload');
        newNode.setAttribute('id', newId);
        this.ids.push(newId);
        if (this.forceOldStyleForm) {
            this.html5DDSuported = false;
        } else {
            this.html5DDSuported = true;
            try {
                var x = new FileReader();
                var y = new FormData();
            } catch (ex) {
                this.html5DDSuported = false;
            }
        }
        hasTapEvent = ("ontouchstart" in window);
        if (hasTapEvent) {
            this.html5DDSuported = false;
        }
        if (this.html5DDSuported) {
            newNode.dropzone = "copy";
            newNode.style.width = "200px";
            newNode.style.height = "100px";
            newNode.style.paddingTop = "20px";
            newNode.style.backgroundColor = "#AAAAAA";
            newNode.style.border = "3px dotted #808080";
            newNode.style.textAlign = "center";
            newNode.style.fontSize = "75%";
            var eachLine = INTERMediatorOnPage.getMessages()[3101].split(/\n/);
            for (var i = 0; i < eachLine.length; i++) {
                if (i > 0) {
                    newNode.appendChild(document.createElement("BR"));
                }
                newNode.appendChild(document.createTextNode(eachLine[i]));
            }
        } else {
            formNode = document.createElement('FORM');
            formNode.setAttribute('method', 'post');
            formNode.setAttribute('action', INTERMediatorOnPage.getEntryPath() + "?access=uploadfile");
            formNode.setAttribute('enctype', 'multipart/form-data');
            newNode.appendChild(formNode);

            if (this.progressSupported) {
                inputNode = document.createElement('INPUT');
                inputNode.setAttribute('type', 'hidden');
                inputNode.setAttribute('name', 'APC_UPLOAD_PROGRESS');
                inputNode.setAttribute('id', 'progress_key');
                inputNode.setAttribute('value',
                    this.uploadId + (this.ids.length - 1));
                formNode.appendChild(inputNode);
            }

            inputNode = document.createElement('INPUT');
            inputNode.setAttribute('type', 'hidden');
            inputNode.setAttribute('name', '_im_redirect');
            inputNode.setAttribute('value', location.href);
            formNode.appendChild(inputNode);

            inputNode = document.createElement('INPUT');
            inputNode.setAttribute('type', 'hidden');
            inputNode.setAttribute('name', '_im_contextnewrecord');
            inputNode.setAttribute('value', 'uploadfile');
            formNode.appendChild(inputNode);

            inputNode = document.createElement('INPUT');
            inputNode.setAttribute('type', 'hidden');
            inputNode.setAttribute('name', 'access');
            inputNode.setAttribute('value', 'uploadfile');
            formNode.appendChild(inputNode);

            inputNode = document.createElement('INPUT');
            inputNode.setAttribute('type', 'file');
            inputNode.setAttribute('accept', '*/*');
            inputNode.setAttribute('name', '_im_uploadfile');
            formNode.appendChild(inputNode);

            buttonNode = document.createElement('BUTTON');
            buttonNode.setAttribute('type', 'submit');
            buttonNode.appendChild(document.createTextNode('送信'));
            formNode.appendChild(buttonNode);
            this.formFromId[newId] = formNode;
        }
        parentNode.appendChild(newNode);

        newNode._im_getValue = function () {
            var targetNode = newNode;
            return targetNode.value;
        };
        parentNode._im_getValue = function () {
            var targetNode = newNode;
            return targetNode.value;
        };
        parentNode._im_getComponentId = function () {
            var theId = newId;
            return theId;
        };

        parentNode._im_setValue = function (str) {
            var targetNode = newNode;
            if (this.html5DDSuported) {
                //    targetNode.innerHTML = str;
            } else {

            }
        };
    },
    ids: [],
    formFromId: {},
    finish: function () {
        var shaObj, hmacValue;

        if (this.html5DDSuported) {
            for (var i = 0; i < this.ids.length; i++) {
                var tagetIdLocal = this.ids[i];
                var targetNode = document.getElementById(tagetIdLocal);
                var contextInfo = IMLibContextPool.getContextInfoFromId(tagetIdLocal);
                if (targetNode) {
                    INTERMediatorLib.addEvent(targetNode, "dragleave", function (event) {
                        event.preventDefault();
                        event.target.style.backgroundColor = "#AAAAAA";
                    });
                    INTERMediatorLib.addEvent(targetNode, "dragover", function (event) {
                        event.preventDefault();
                        event.target.style.backgroundColor = "#AADDFF";
                    });
                    var isProgressingLocal = this.progressSupported;
                    var serialIdLocal = this.ids.length;
                    var uploadIdLocal = this.uploadId;
                    INTERMediatorLib.addEvent(targetNode, "drop", (function () {
                        var iframeId = i;
                        var isProgressing = isProgressingLocal;
                        var serialId = serialIdLocal;
                        var uploadId = uploadIdLocal;
                        var tagetId = tagetIdLocal;
                        return function (event) {
                            var file, fileNameNode;
                            event.preventDefault();
                            var eventTarget = event.currentTarget;
                            if (isProgressing) {
                                var infoFrame = document.createElement('iframe');
                                infoFrame.setAttribute('id', 'upload_frame' + serialId);
                                infoFrame.setAttribute('name', 'upload_frame');
                                infoFrame.setAttribute('frameborder', '0');
                                infoFrame.setAttribute('border', '0');
                                infoFrame.setAttribute('scrolling', 'no');
                                infoFrame.setAttribute('scrollbar', 'no');
                                infoFrame.style.width = "100%";
                                infoFrame.style.height = "24px";
                                eventTarget.appendChild(infoFrame);
                            }
                            for (var i = 0; i < event.dataTransfer.files.length; i++) {
                                file = event.dataTransfer.files[i];
                                fileNameNode = document.createElement("DIV");
                                fileNameNode.appendChild(document.createTextNode(
                                    INTERMediatorOnPage.getMessages()[3102] + file.name));
                                fileNameNode.style.marginTop = "20px";
                                fileNameNode.style.backgroundColor = "#FFFFFF";
                                fileNameNode.style.textAlign = "center";
                                event.target.appendChild(fileNameNode);
                            }
                            var updateInfo = IMLibContextPool.getContextInfoFromId(eventTarget.getAttribute('id'), "");
                            //INTERMediator.updateRequiredObject[eventTarget.getAttribute('id')];

                            if (isProgressing) {
                                infoFrame.style.display = "block";
                                setTimeout(function () {
                                    infoFrame.setAttribute('src',
                                        'upload_frame.php?up_id=' + uploadId + iframeId);
                                });
                            }

                            INTERMediator_DBAdapter.uploadFile(
                                '&_im_contextname=' + encodeURIComponent(updateInfo.context.contextName)
                                    + '&_im_field=' + encodeURIComponent(updateInfo.field)
                                    + '&_im_keyfield=' + encodeURIComponent(updateInfo.record.split("=")[0])
                                    + '&_im_keyvalue=' + encodeURIComponent(updateInfo.record.split("=")[1])
                                    + '&_im_contextnewrecord=' + encodeURIComponent('uploadfile')
                                    + (isProgressing ?
                                    ('&APC_UPLOAD_PROGRESS=' + encodeURIComponent(uploadId + iframeId)) : ""),
                                {
                                    fileName: file.name,
                                    content: file
                                },
                                function (dbresult) {
                                    var contextObj, contextInfo, contextObjects = null, fvalue, i, context;
                                    context = IMLibContextPool.getContextDef(updateInfo.context.contextName);
                                    if (context['file-upload']) {
                                        var relatedContextName = '';
                                        for (var index in context['file-upload']) {
                                            if (context['file-upload'][index]['field'] == updateInfo.field) {
                                                relatedContextName = context['file-upload'][index]['context'];
                                                break;
                                            }
                                        }
                                        fvalue = IMLibContextPool.getKeyFieldValueFromId(tagetId, "");
                                        contextObjects = IMLibContextPool.getContextsFromNameAndForeignValue(
                                            relatedContextName, fvalue, context.key);
                                    } else {
                                        contextObjects = IMLibContextPool.getContextFromName(updateInfo.context.contextName);
                                    }
                                    contextInfo = IMLibContextPool.getContextInfoFromId(tagetId, "");
                                    contextInfo.context.setValue(contextInfo.record, contextInfo.field, dbresult);
                                    if (contextObjects) {
                                        for (i = 0; i < contextObjects.length; i++) {
                                            contextObj = contextObjects[i];
                                            INTERMediator.construct(contextObj);
                                        }
                                    }
                                    INTERMediator.flushMessage();
                                });
                        };
                    })());
                }
            }

        } else {
            for (var i = 0; i < this.ids.length; i++) {
                var targetNode = document.getElementById(this.ids[i]);
                if (targetNode) {
                    var updateInfo = IMLibContextPool.getContextInfoFromId(this.ids[i], "");
                    //= INTERMediator.updateRequiredObject[IMParts_im_fileupload.ids[i]];
                    var formNode = targetNode.getElementsByTagName('FORM')[0];
                    var inputNode = document.createElement('INPUT');
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', '_im_contextname');
                    inputNode.setAttribute('value', updateInfo.context.contextName);
                    formNode.appendChild(inputNode);

                    inputNode = document.createElement('INPUT');
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', '_im_field');
                    inputNode.setAttribute('value', updateInfo.field);
                    formNode.appendChild(inputNode);

                    inputNode = document.createElement('INPUT');
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', '_im_keyfield');
                    inputNode.setAttribute('value', updateInfo.record.split("=")[0]);
                    formNode.appendChild(inputNode);

                    inputNode = document.createElement('INPUT');
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', '_im_keyvalue');
                    inputNode.setAttribute('value', updateInfo.record.split("=")[1]);
                    formNode.appendChild(inputNode);

                    inputNode = document.createElement('INPUT');
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', 'clientid');
                    if (INTERMediatorOnPage.authUser.length > 0) {
                        inputNode.value = INTERMediatorOnPage.clientId;
                    }
                    formNode.appendChild(inputNode);
                    inputNode = document.createElement('INPUT');
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', 'authuser');
                    if (INTERMediatorOnPage.authUser.length > 0) {
                        inputNode.value = INTERMediatorOnPage.authUser;
                    }
                    formNode.appendChild(inputNode);
                    inputNode = document.createElement('INPUT');
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', 'response');
                    if (INTERMediatorOnPage.authUser.length > 0) {
                        if (INTERMediatorOnPage.isNativeAuth) {
                            thisForm.elements["response"].value = INTERMediatorOnPage.publickey.biEncryptedString(
                                INTERMediatorOnPage.authHashedPassword + "\n" + INTERMediatorOnPage.authChallenge);
                        } else {
                            if (INTERMediatorOnPage.authHashedPassword && INTERMediatorOnPage.authChallenge) {
                                shaObj = new jsSHA(INTERMediatorOnPage.authHashedPassword, "ASCII");
                                hmacValue = shaObj.getHMAC(INTERMediatorOnPage.authChallenge,
                                    "ASCII", "SHA-256", "HEX");
                                inputNode.value = hmacValue;
                            } else {
                                inputNode.value = "dummy";
                            }
                        }
                    }
                    formNode.appendChild(inputNode);
                    if (this.progressSupported) {

                        inputNode = document.createElement('iframe');
                        inputNode.setAttribute('id', 'upload_frame' + i);
                        inputNode.setAttribute('name', 'upload_frame');
                        inputNode.setAttribute('frameborder', '0');
                        inputNode.setAttribute('border', '0');
                        inputNode.setAttribute('scrolling', 'no');
                        inputNode.setAttribute('scrollbar', 'no');
                        formNode.appendChild(inputNode);

                        INTERMediatorLib.addEvent(formNode, "submit", (function () {
                            var iframeId = i;
                            return function (event) {

                                var iframeNode = document.getElementById('upload_frame' + iframeId);
                                iframeNode.style.display = "block";
                                setTimeout(function () {
                                    var infoURL = selfURL() + '?uploadprocess='
                                        + this.uploadId + iframeId;
                                    iframeNode.setAttribute('src', infoURL);
                                });
                                return true;
                            };
                        })());
                    }
                }
            }
        }
        this.ids = [];
        this.formFromId = {};

        function selfURL() {
            var nodes = document.getElementsByTagName("SCRIPT");
            for (var i = 0; i < nodes.length; i++) {
                var srcAttr = nodes[i].getAttribute("src");
                if (srcAttr.match(/\.php/)) {
                    return srcAttr;
                }
            }
            return null;
        }
    }
};
