/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

// https://github.com/blueimp/jQuery-File-Upload
// https://blueimp.github.io/jQuery-File-Upload/index.html

IMParts_Catalog["jquery_fileupload"] = {
    panelWidth:  "200px",

    instanciate: function (targetNode) {
        var container, node, pNode = targetNode;
        var nodeId = targetNode.getAttribute('id');
        this.ids.push(nodeId);

        container = document.createElement("DIV");
        container.setAttribute("class", "container");
        container.style.width = this.panelWidth;
        pNode.appendChild(container);

        node = document.createElement("SPAN");
        node.setAttribute("class", "btn btn-success fileinput-button");
        container.appendChild(node);
        pNode = node;

        node = document.createElement("I");
        node.setAttribute("class", "glyphicon glyphicon-plus");
        pNode.appendChild(node);
        node = document.createElement("SPAN");
        node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[3209]));
        pNode.appendChild(node);
        node = document.createElement("INPUT");
        node.setAttribute("id", nodeId + "-fileupload");
        node.setAttribute("type", "file");
        node.setAttribute("name", "files[]");
        pNode.appendChild(node);
        container.appendChild(document.createElement("BR"));

        node = document.createElement("DIV");
        node.setAttribute("id", nodeId + "-filenamearea");
        node.style.display = "none";
        node.style.width = "100%";
        container.appendChild(node);
        pNode = node;

        node = document.createElement("SPAN");
        node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[3210]));
        node.style.color = "gray";
        pNode.appendChild(node);
        pNode.appendChild(document.createElement("BR"));
        node = document.createElement("SPAN");
        node.setAttribute("id", nodeId + "-filename");
        node.style.width = "100%";
        pNode.appendChild(node);
        pNode.appendChild(document.createElement("BR"));
        pNode.appendChild(document.createTextNode(" "));
        pNode.appendChild(document.createElement("BR"));

        node = document.createElement("DIV");
        node.setAttribute("id", nodeId + "-uploadarea");
        node.style.display = "none";
        node.style.marginTop = "20px";
        node.setAttribute("class", "btn btn-primary");
        container.appendChild(node);
        pNode = node;

        node = document.createElement("I");
        node.setAttribute("class", "glyphicon");
        pNode.appendChild(node);
        node = document.createElement("SPAN");
        node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[3211]));
        pNode.appendChild(node);

        node = document.createElement("DIV");
        node.style.marginTop = "6px";
        node.setAttribute("class", "progress");
        container.appendChild(node);
        pNode = node;

        node = document.createElement("DIV");
        node.setAttribute("id", nodeId + "-progress");
        node.style.height = "18px";
        node.style.background = "green";
        node.style.width = "0";
        pNode.appendChild(node);

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
        var shaObj, hmacValue, formData, targetId, targetNode, cInfo, keyValue;
        for (var i = 0; i < this.ids.length; i++) {
            targetId = this.ids[i];
            cInfo = IMLibContextPool.getContextInfoFromId(targetId, "");
            keyValue = cInfo.record.split("=");
            targetNode = $('#' + targetId + "-fileupload");
            if (targetNode) {
                formData = [
                    {name: 'access', value: 'uploadfile'},
                    {name: '_im_contextnewrecord', value: 'uploadfile'},
                    {name: '_im_contextname', value: cInfo.context.contextName},
                    {name: '_im_field', value: cInfo.field},
                    {name: '_im_keyfield', value: keyValue[0]},
                    {name: '_im_keyvalue', value: keyValue[1]},
                    {name: 'authuser', value: INTERMediatorOnPage.authUser}
                ];
                if (INTERMediatorOnPage.authUser.length > 0) {
                    formData.push({name: 'clientid', value: INTERMediatorOnPage.clientId});
                    if (INTERMediatorOnPage.authHashedPassword && INTERMediatorOnPage.authChallenge) {
                        shaObj = new jsSHA(INTERMediatorOnPage.authHashedPassword, "ASCII");
                        hmacValue = shaObj.getHMAC(INTERMediatorOnPage.authChallenge,
                            "ASCII", "SHA-256", "HEX");
                        formData.push({name: 'response', value: hmacValue});
                    } else {
                        formData.push({name: 'response', value: 'dummydummy'});
                    }
                    formData.push({
                        name: 'cresponse',
                        value: INTERMediatorOnPage.publickey.biEncryptedString(
                            INTERMediatorOnPage.authCryptedPassword + "\n" +
                            INTERMediatorOnPage.authChallenge)
                    });
                }
                targetNode.fileupload({
                    dataType: 'json',
                    url: INTERMediatorOnPage.getEntryPath() + "?access=uploadfile",
                    limitConcurrentUploads: 1,
                    formData: formData,
                    add: (function () {
                        var idValue = targetId;
                        return function (e, data) {
                            $('#' + idValue + '-filename').text(data.files[0].name);
                            $('#' + idValue + '-filenamearea').css('display', 'inline');
                            $('#' + idValue + '-uploadarea').css('display', 'inline');
                            $('#' + idValue + '-uploadarea').click(function () {
                                data.submit();
                            })
                        };
                    })(),
                    done:(function () {
                        var cName = cInfo.context.contextName;
                        return function (e, data) {
                            var result = INTERMediator_DBAdapter.uploadFileAfterSucceed(
                                data.jqXHR, function () {}, function () {}, true);
                            data.jqXHR.abort();
                            if (result) {
                                INTERMediator.construct(IMLibContextPool.contextFromName(cName));
                            }
                        };
                    })(),
                    fail: function (e, data) {
                        window.alert(data.jqXHR.responseText);
                        data.jqXHR.abort();
                    },
                    progressall: (function () {
                        var idValue = targetId;
                        return function (e, data) {
                            var progress = parseInt(data.loaded / data.total * 100, 10);
                            $('#' + idValue + '-progress').css('width', progress + '%');
                        };
                    })()
                });
            }
        }
        this.ids = [];
    }
};
