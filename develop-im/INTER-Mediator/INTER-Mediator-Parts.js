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
    instanciate: function (parentNode) {
        var newId = parentNode.getAttribute('id') + '-e';
        this.ids.push(newId);
        var newNode = document.createElement('TEXTAREA');
        newNode.setAttribute('id', newId);
        INTERMediatorLib.setClassAttributeToNode(newNode, '_im_tinymce');
        parentNode.appendChild(newNode);
        this.ids.push(newId);

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

        // This method will be called before tinyMCE isn't initialized
        parentNode._im_setValue = function (str) {
            var targetNode = newNode;
            targetNode.innerHTML = str;
        };
    },
    ids: [],
    finish: function () {
        if (!tinymceOption) {
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

        for (var i = 0; i < this.ids.length; i++) {
            var targetNode = document.getElementById(this.ids[i]);
            if (targetNode) {
                targetNode._im_getValue = function () {
                    return tinymce.EditorManager.get(this.id).getContent();
                };
            }
        }
    }
};
var IMParts_codemirror = {
    instanciate: function (parentNode) {
        var newId = parentNode.getAttribute('id') + '-e';
        var newNode = document.createElement('TEXTAREA');
        newNode.setAttribute('id', newId);
        INTERMediatorLib.setClassAttributeToNode(newNode, '_im_codemirror');
        parentNode.appendChild(newNode);
        this.ids.push(newId);

        newNode._im_getValue = function () {
            var targetNode = newNode;
            return targetNode.getValue();
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
            IMParts_codemirror.initialValues[this._im_getComponentId()] = str;
        };
    },
    ids: [],
    initialValues: {},
    mode: "text/html",
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
                        INTERMediator.valueChange(nodeId)
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
    }
};

/*********
 *
 * File Uploader
 * @type {{html5DDSuported: boolean, instanciate: Function, ids: Array, finish: Function}}
 */
var IMParts_im_fileupload = {
    html5DDSuported: false,
    progressSupported: false,   // see http://www.johnboyproductions.com/php-upload-progress-bar/
    forceOldStyleForm: false,
    uploadId: "sign" + Math.random(),
    instanciate: function (parentNode) {
        var inputNode, formNode, buttonNode;
        var newId = parentNode.getAttribute('id') + '-e';
        var newNode = document.createElement('DIV');
        INTERMediatorLib.setClassAttributeToNode(newNode, '_im_fileupload');
        newNode.setAttribute('id', newId);
        IMParts_im_fileupload.ids.push(newId);
        if (IMParts_im_fileupload.forceOldStyleForm) {
            IMParts_im_fileupload.html5DDSuported = false;
        } else {
            IMParts_im_fileupload.html5DDSuported = true;
            try {
                var x = new FileReader();
                var y = new FormData();
            } catch (ex) {
                IMParts_im_fileupload.html5DDSuported = false;
            }
        }
        if (IMParts_im_fileupload.html5DDSuported) {
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

            if (IMParts_im_fileupload.progressSupported) {
                inputNode = document.createElement('INPUT');
                inputNode.setAttribute('type', 'hidden');
                inputNode.setAttribute('name', 'APC_UPLOAD_PROGRESS');
                inputNode.setAttribute('id', 'progress_key');
                inputNode.setAttribute('value',
                    IMParts_im_fileupload.uploadId + (IMParts_im_fileupload.ids.length - 1));
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
            IMParts_im_fileupload.formFromId[newId] = formNode;
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
            if (IMParts_im_fileupload.html5DDSuported) {
                //    targetNode.innerHTML = str;
            } else {

            }
        };
    },
    ids: [],
    formFromId: {},
    finish: function () {
        if (IMParts_im_fileupload.html5DDSuported) {
            for (var i = 0; i < IMParts_im_fileupload.ids.length; i++) {
                var targetNode = document.getElementById(IMParts_im_fileupload.ids[i]);
                if (targetNode) {
                    INTERMediatorLib.addEvent(targetNode, "dragleave", function (event) {
                        event.preventDefault();
                        event.target.style.backgroundColor = "#AAAAAA";
                    });
                    INTERMediatorLib.addEvent(targetNode, "dragover", function (event) {
                        event.preventDefault();
                        event.target.style.backgroundColor = "#AADDFF";
                    });
                    INTERMediatorLib.addEvent(targetNode, "drop", (function () {
                        var iframeId = i;
                        return function (event) {
                            var file, fileNameNode;
                            event.preventDefault();
                            var eventTarget = event.currentTarget;
                            if (IMParts_im_fileupload.progressSupported) {
                                var infoFrame = document.createElement('iframe');
                                infoFrame.setAttribute('id', 'upload_frame' + (IMParts_im_fileupload.ids.length - 1));
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
                            var updateInfo = INTERMediator.updateRequiredObject[eventTarget.getAttribute('id')];

                            if (IMParts_im_fileupload.progressSupported) {
                                infoFrame.style.display = "block";
                                setTimeout(function () {
                                    infoFrame.setAttribute('src',
                                        'upload_frame.php?up_id=' + IMParts_im_fileupload.uploadId + iframeId);
                                });
                            }

                            INTERMediator_DBAdapter.uploadFile(
                                '&_im_contextname=' + encodeURIComponent(updateInfo['name'])
                                    + '&_im_field=' + encodeURIComponent(updateInfo['field'])
                                    + '&_im_keyfield=' + encodeURIComponent(updateInfo['keying'].split("=")[0])
                                    + '&_im_keyvalue=' + encodeURIComponent(updateInfo['keying'].split("=")[1])
                                    + '&_im_contextnewrecord=' + encodeURIComponent('uploadfile')
                                    + (IMParts_im_fileupload.progressSupported ?
                                    ('&APC_UPLOAD_PROGRESS=' + encodeURIComponent(
                                        IMParts_im_fileupload.uploadId + iframeId)) : ""),
                                {
                                    fileName: file.name,
                                    content: file
                                },
                                function () {
                                    var indexContext = true;
                                    var context = INTERMediatorLib.getNamedObject(
                                        INTERMediatorOnPage.getDataSources(), 'name', updateInfo['name']);
                                    if (context['file-upload']) {
                                        var relatedContextName = '';
                                        for (var i = 0; i < context['file-upload'].length; i++) {
                                            if (context['file-upload'][i]['field'] == updateInfo['field']) {
                                                relatedContextName = context['file-upload'][i]['context'];
                                                break;
                                            }
                                        }
                                        for (var i = 0; i < INTERMediator.keyFieldObject.length; i++) {
                                            if (INTERMediator.keyFieldObject[i]['name'] == relatedContextName) {
                                                indexContext = i;
                                                break;
                                            }
                                        }
                                    } else {
                                        for (var i = 0; i < INTERMediator.keyFieldObject.length; i++) {
                                            if (INTERMediator.keyFieldObject[i]['name'] == updateInfo['name']) {
                                                indexContext = i;
                                                break;
                                            }
                                        }
                                    }
                                    INTERMediator.construct(indexContext);
                                });
                        }
                    })());
                }
            }

        } else {
            for (var i = 0; i < IMParts_im_fileupload.ids.length; i++) {
                var targetNode = document.getElementById(IMParts_im_fileupload.ids[i]);
                if (targetNode) {
                    var updateInfo = INTERMediator.updateRequiredObject[IMParts_im_fileupload.ids[i]];
                    var formNode = targetNode.getElementsByTagName('FORM')[0];
                    var inputNode = document.createElement('INPUT');
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', '_im_contextname');
                    inputNode.setAttribute('value', updateInfo['name']);
                    formNode.appendChild(inputNode);

                    inputNode = document.createElement('INPUT');
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', '_im_field');
                    inputNode.setAttribute('value', updateInfo['field']);
                    formNode.appendChild(inputNode);

                    inputNode = document.createElement('INPUT');
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', '_im_keyfield');
                    inputNode.setAttribute('value', updateInfo['keying'].split("=")[0]);
                    formNode.appendChild(inputNode);

                    inputNode = document.createElement('INPUT');
                    inputNode.setAttribute('type', 'hidden');
                    inputNode.setAttribute('name', '_im_keyvalue');
                    inputNode.setAttribute('value', updateInfo['keying'].split("=")[1]);
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
                    if (IMParts_im_fileupload.progressSupported) {

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
                                        + IMParts_im_fileupload.uploadId + iframeId;
                                    iframeNode.setAttribute('src', infoURL);
                                });
                                return true;
                            }
                        })());
                    }
                }
            }
        }

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

        function createRecordOnRelatedTable(updateInfo, path) {
            var index, ix, dataset, filesContext;
            var context = INTERMediatorLib.getNamedObject(
                INTERMediatorOnPage.getDataSources(), 'name', updateInfo['name']);
            if (context['file-upload']) {
                for (index in context['file-upload']) {
                    if (context['file-upload'][index]['field'] == updateInfo['field']) {
                        filesContext = INTERMediatorLib.getNamedObject(
                            INTERMediatorOnPage.getDataSources(),
                            'name',
                            context['file-upload'][index]['context']);
                        dataset = [
                            {field: "path", value: path}
                        ];
                        if (filesContext['relation']) {
                            for (ix in filesContext['relation']) {
                                dataset.push({
                                    field: filesContext['relation'][ix]['foreign-key'],
                                    value: updateInfo['keying'].split("=")[1]});
                            }
                        }
                        if (filesContext['query']) {
                            for (ix in filesContext['query']) {
                                dataset.push({
                                    field: filesContext['query'][ix]['field'],
                                    value: filesContext['query'][ix]['value']});
                            }
                        }
                        INTERMediator_DBAdapter.db_createRecord({
                            name: context['file-upload'][index]['context'],
                            dataset: dataset
                        });
                    }
                }
            }
        }
    }
};
