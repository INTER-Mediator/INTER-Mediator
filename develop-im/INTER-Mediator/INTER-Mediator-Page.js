/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */


if (!Array.indexOf) {
    Array.prototype.indexOf = function (target) {
        var i;
        for (i = 0; i < this.length; i++) {
            if (this[i] === target) {
                return i;
            }
        }
        return -1;
    }
}

var INTERMediatorOnPage;

INTERMediatorOnPage = {
    authCountLimit: 4,
    authCount: 0,
    authUser: '',
    authHashedPassword: '',
    authUserSalt: '',
    authUserHexSalt: '',
    authChallenge: '',
    requireAuthentication: false,
    clientId: null,
    authRequiredContext: null,
    authStoring: 'cookie',
    authExpired: 3600,
    isOnceAtStarting: true,
    publickey: null,
    isNativeAuth: false,
    httpuser: null,
    httppasswd: null,
    mediaToken: null,
    realm: '',
    dbCache: {},
    isEmailAsUsername: false,

    isShowChangePassword: true,

    /*
     This method "getMessages" is going to be replaced valid one with the browser's language.
     Here is defined to prevent the warning of static check.
     */
    getMessages: function () {
        return null;
    },

    getURLParametersAsArray: function() {
        var i, params, eqPos, result, key, value;
        result = {};
        params = location.search.substring(1).split('&');
        for (i = 0; i < params.length; i++) {
            eqPos = params[i].indexOf("=");
            if (eqPos > 0) {
                key = params[i].substring(0, eqPos);
                value = params[i].substring(eqPos+1)
                result[key] = decodeURIComponent(value);
            }
        }
        return result;
    },

    getContextInfo: function(contextName)   {
        var dataSources, oneSource;
        dataSources = INTERMediatorOnPage.getDataSources();
        for (index in dataSources)    {
            if ( dataSources[index].name == contextName )   {
                return dataSources[index];
            }
        }
        return null;
    },

    isComplementAuthData: function () {
        if (this.authUser != null && this.authUser.length > 0
            && this.authHashedPassword != null && this.authHashedPassword.length > 0
            && this.authUserSalt != null && this.authUserSalt.length > 0
            && this.authChallenge != null && this.authChallenge.length > 0) {
            return true;
        }
        return false;
    },

    retrieveAuthInfo: function () {
        if (this.requireAuthentication) {
            if (this.isOnceAtStarting) {
                switch (this.authStoring) {
                    case 'cookie':
                    case 'cookie-domainwide':
                        this.authUser = this.getCookie('_im_username');
                        this.authHashedPassword = this.getCookie('_im_credential');
                        this.mediaToken = this.getCookie('_im_mediatoken');
                        break;
                    default:
                        this.removeCookie('_im_username');
                        this.removeCookie('_im_credential');
                        this.removeCookie('_im_mediatoken');
                        break;
                }
                this.isOnceAtStarting = false;
            }
            if (this.authUser.length > 0) {
                if (!INTERMediator_DBAdapter.getChallenge()) {
                    INTERMediator.flushMessage();
                }
            }
        }
    },

    logout: function () {
        this.authUser = "";
        this.authHashedPassword = "";
        this.authUserSalt = "";
        this.authChallenge = "";
        this.clientId = "";
        this.removeCookie('_im_username');
        this.removeCookie('_im_credential');
        this.removeCookie('_im_mediatoken');
    },

    storeCredencialsToCookie: function () {
        switch (INTERMediatorOnPage.authStoring) {
            case 'cookie':
                INTERMediatorOnPage.setCookie('_im_username', INTERMediatorOnPage.authUser);
                INTERMediatorOnPage.setCookie('_im_credential', INTERMediatorOnPage.authHashedPassword);
                if (INTERMediatorOnPage.mediaToken) {
                    INTERMediatorOnPage.setCookie('_im_mediatoken', INTERMediatorOnPage.mediaToken);
                }
                break;
            case 'cookie-domainwide':
                INTERMediatorOnPage.setCookieDomainWide('_im_username', INTERMediatorOnPage.authUser);
                INTERMediatorOnPage.setCookieDomainWide('_im_credential', INTERMediatorOnPage.authHashedPassword);
                if (INTERMediatorOnPage.mediaToken) {
                    INTERMediatorOnPage.setCookieDomainWide('_im_mediatoken', INTERMediatorOnPage.mediaToken);
                }
                break;
        }
    },

    defaultBackgroundImage: "url(data:image/png;base64,"
        + "iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAAXNSR0IArs4c6QAA"
        + "ACF0RVh0U29mdHdhcmUAR3JhcGhpY0NvbnZlcnRlciAoSW50ZWwpd4f6GQAAAHRJ"
        + "REFUeJzs0bENAEAMAjHWzBC/f5sxkPIurkcmSV65KQcAAAAAAAAAAAAAAAAAAAAA"
        + "AAAAAAAAAAAAAAAAAAAAAL4AaA9oHwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA"
        + "AAAAAAAAAAAAOA6wAAAA//8DAF3pMFsPzhYWAAAAAElFTkSuQmCC)",

    defaultBackgroundColor: null,
    loginPanelHTML: null,

    authenticating: function (doAfterAuth) {
        var bodyNode, backBox, frontPanel, labelWidth, userLabel, userSpan, userBox, msgNumber,
            passwordLabel, passwordSpan, passwordBox, breakLine, chgpwButton, authButton,
            newPasswordLabel, newPasswordSpan, newPasswordBox, newPasswordMessage, realmBox, keyCode;

        if (this.authCount > this.authCountLimit) {
            this.authenticationError();
            this.logout();
            INTERMediator.flushMessage();
            return;
        }

        bodyNode = document.getElementsByTagName('BODY')[0];
        backBox = document.createElement('div');
        bodyNode.insertBefore(backBox, bodyNode.childNodes[0]);
        backBox.style.height = "100%";
        backBox.style.width = "100%";
        if (INTERMediatorOnPage.defaultBackgroundImage) {
            backBox.style.backgroundImage = INTERMediatorOnPage.defaultBackgroundImage;
        }
        if (INTERMediatorOnPage.defaultBackgroundColor) {
            backBox.style.backgroundColor = INTERMediatorOnPage.defaultBackgroundColor;
        }
        backBox.style.position = "absolute";
        backBox.style.padding = " 50px 0 0 0";
        backBox.style.top = "0";
        backBox.style.left = "0";
        backBox.style.zIndex = "999998";

        if (INTERMediatorOnPage.loginPanelHTML) {
            backBox.innerHTML = INTERMediatorOnPage.loginPanelHTML;
            passwordBox = document.getElementById('_im_password');
            userBox = document.getElementById('_im_username');
            authButton = document.getElementById('_im_authbutton');
            chgpwButton = document.getElementById('_im_changebutton');
        } else {
            frontPanel = document.createElement('div');
            frontPanel.style.width = "450px";
            frontPanel.style.backgroundColor = "#333333";
            frontPanel.style.color = "#DDDDAA";
            frontPanel.style.margin = "50px auto 0 auto";
            frontPanel.style.padding = "20px";
            frontPanel.style.borderRadius = "10px";
            frontPanel.style.position = "relative";
            backBox.appendChild(frontPanel);

            if (INTERMediatorOnPage.realm.length > 0) {
                realmBox = document.createElement('DIV');
                realmBox.appendChild(document.createTextNode(INTERMediatorOnPage.realm));
                realmBox.style.textAlign = "left";
                frontPanel.appendChild(realmBox);
                breakLine = document.createElement('HR');
                frontPanel.appendChild(breakLine);
            }

            labelWidth = "200px";
            userLabel = document.createElement('LABEL');
            frontPanel.appendChild(userLabel);
            userSpan = document.createElement('div');
            userSpan.style.width = labelWidth;
            userSpan.style.textAlign = "right";
            userSpan.style.cssFloat = "left";
            userLabel.appendChild(userSpan);
            msgNumber = INTERMediatorOnPage.isEmailAsUsername ? 2011 : 2002;
            userSpan.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(msgNumber)));
            userBox = document.createElement('INPUT');
            userBox.type = "text";
            userBox.id = "_im_username";
            userBox.size = "24";
            userLabel.appendChild(userBox);

            breakLine = document.createElement('BR');
            breakLine.clear = "all";
            frontPanel.appendChild(breakLine);

            passwordLabel = document.createElement('LABEL');
            frontPanel.appendChild(passwordLabel);
            passwordSpan = document.createElement('SPAN');
            passwordSpan.style.minWidth = labelWidth;
            passwordSpan.style.textAlign = "right";
            passwordSpan.style.cssFloat = "left";
            passwordLabel.appendChild(passwordSpan);
            passwordSpan.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2003)));
            passwordBox = document.createElement('INPUT');
            passwordBox.type = "password";
            passwordBox.id = "_im_password";
            passwordBox.size = "24";
            passwordLabel.appendChild(passwordBox);

            authButton = document.createElement('BUTTON');
            authButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2004)));
            frontPanel.appendChild(authButton);

            breakLine = document.createElement('BR');
            breakLine.clear = "all";
            frontPanel.appendChild(breakLine);

            if (this.isShowChangePassword && !INTERMediatorOnPage.isNativeAuth) {

                breakLine = document.createElement('HR');
                frontPanel.appendChild(breakLine);

                newPasswordLabel = document.createElement('LABEL');
                frontPanel.appendChild(newPasswordLabel);
                newPasswordSpan = document.createElement('SPAN');
                newPasswordSpan.style.minWidth = labelWidth;
                newPasswordSpan.style.textAlign = "right";
                newPasswordSpan.style.cssFloat = "left";
                newPasswordSpan.style.fontSize = "0.7em";
                newPasswordSpan.style.paddingTop = "4px";
                newPasswordLabel.appendChild(newPasswordSpan);
                newPasswordSpan.appendChild(
                    document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2006)));
                newPasswordBox = document.createElement('INPUT');
                newPasswordBox.type = "password";
                newPasswordBox.id = "_im_newpassword";
                newPasswordBox.size = "12";
                newPasswordLabel.appendChild(newPasswordBox);
                chgpwButton = document.createElement('BUTTON');
                chgpwButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2005)));
                frontPanel.appendChild(chgpwButton);

                newPasswordMessage = document.createElement('DIV');
                newPasswordMessage.style.textAlign = "center";
                newPasswordMessage.style.textSize = "10pt";
                newPasswordMessage.style.color = "#994433";
                frontPanel.appendChild(newPasswordMessage);
            }
        }
        passwordBox.onkeydown = function (event) {
            keyCode = (window.event) ? window.event.which : event.keyCode;
            if (keyCode == 13) {
                authButton.onclick();
            }
        };
        userBox.value = INTERMediatorOnPage.authUser;
        userBox.onkeydown = function (event) {
            keyCode = (window.event) ? window.event.which : event.keyCode;
            if (keyCode == 13) {
                passwordBox.focus();
            }
        };
        authButton.onclick = function () {
            var inputUsername, inputPassword, challengeResult;

            inputUsername = document.getElementById('_im_username').value;
            inputPassword = document.getElementById('_im_password').value;
            INTERMediatorOnPage.authUser = inputUsername;
            bodyNode.removeChild(backBox);
            if (inputUsername != ''    // No usename and no challenge, get a challenge.
                && (INTERMediatorOnPage.authChallenge == null || INTERMediatorOnPage.authChallenge.length < 24 )) {
                INTERMediatorOnPage.authHashedPassword = "need-hash-pls";   // Dummy Hash for getting a challenge
                challengeResult = INTERMediator_DBAdapter.getChallenge();
                if (!challengeResult) {
                    INTERMediator.flushMessage();
                    return; // If it's failed to get a challenge, finish everything.
                }
            }
            if (INTERMediatorOnPage.isNativeAuth) {
                INTERMediatorOnPage.authHashedPassword = inputPassword;
            } else {
                INTERMediatorOnPage.authHashedPassword
                    = SHA1(inputPassword + INTERMediatorOnPage.authUserSalt)
                    + INTERMediatorOnPage.authUserHexSalt;
            }

            if (INTERMediatorOnPage.authUser.length > 0) {   // Authentication succeed, Store coockies.
                INTERMediatorOnPage.storeCredencialsToCookie();
            }

            doAfterAuth();  // Retry.
            INTERMediator.flushMessage();
        };
        if (chgpwButton) {
            chgpwButton.onclick = function () {
                var inputUsername, inputPassword, inputNewPassword, challengeResult, params, result;

                inputUsername = document.getElementById('_im_username').value;
                inputPassword = document.getElementById('_im_password').value;
                inputNewPassword = document.getElementById('_im_newpassword').value;
                if (inputUsername === '' || inputPassword === '' || inputNewPassword === '') {
                    newPasswordMessage.innerHTML = INTERMediatorLib.getInsertedStringFromErrorNumber(2007);
                    return;
                }
                INTERMediatorOnPage.authUser = inputUsername;
                if (inputUsername != ''    // No usename and no challenge, get a challenge.
                    && (INTERMediatorOnPage.authChallenge == null || INTERMediatorOnPage.authChallenge.length < 24 )) {
                    INTERMediatorOnPage.authHashedPassword = "need-hash-pls";   // Dummy Hash for getting a challenge
                    challengeResult = INTERMediator_DBAdapter.getChallenge();
                    if (!challengeResult) {
                        newPasswordMessage.innerHTML = INTERMediatorLib.getInsertedStringFromErrorNumber(2008);
                        INTERMediator.flushMessage();
                        return; // If it's failed to get a challenge, finish everything.
                    }
                }
                INTERMediatorOnPage.authHashedPassword
                    = SHA1(inputPassword + INTERMediatorOnPage.authUserSalt)
                    + INTERMediatorOnPage.authUserHexSalt;
                params = "access=changepassword&newpass=" + INTERMediatorLib.generatePasswordHash(inputNewPassword);
                try {
                    result = INTERMediator_DBAdapter.server_access(params, 1029, 1030);
                } catch (e) {
                    result = {newPasswordResult: false};
                }
                newPasswordMessage.innerHTML = INTERMediatorLib.getInsertedStringFromErrorNumber(
                    result.newPasswordResult === true ? 2009 : 2010);

                INTERMediator.flushMessage();
            }
        }

        window.scroll(0, 0);
        userBox.focus();
        INTERMediatorOnPage.authCount++;
    },

    authenticationError: function () {
        var bodyNode, backBox, frontPanel;

        INTERMediatorOnPage.hideProgress();

        bodyNode = document.getElementsByTagName('BODY')[0];
        backBox = document.createElement('div');
        bodyNode.insertBefore(backBox, bodyNode.childNodes[0]);
        backBox.style.height = "100%";
        backBox.style.width = "100%";
        //backBox.style.backgroundColor = "#BBBBBB";
        backBox.style.backgroundImage = "url(data:image/png;base64,"
            + "iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAAXNSR0IArs4c6QAA"
            + "ACF0RVh0U29mdHdhcmUAR3JhcGhpY0NvbnZlcnRlciAoSW50ZWwpd4f6GQAAAHlJ"
            + "REFUeJzs0UENACAQA8EzdAl2EIEg3CKjyTGP/TfTur1OuJ2sAAAAAAAAAAAAAAAA"
            + "AAAAAAAAAAAAAAAAAAAAAAAAAADAJwDRAekDAAAAAAAAAAAAAAAAAAAAAAAAAAAA"
            + "AAAAAAAAAAAAAAAAAADzAR4AAAD//wMAkUKRPI/rh/AAAAAASUVORK5CYII=)";
        backBox.style.position = "absolute";
        backBox.style.padding = " 50px 0 0 0";
        backBox.style.top = "0";
        backBox.style.left = "0";
        backBox.style.zIndex = "999999";

        frontPanel = document.createElement('div');
        frontPanel.style.width = "240px";
        frontPanel.style.backgroundColor = "#333333";
        frontPanel.style.color = "#DD6666";
        frontPanel.style.fontSize = "16pt";
        frontPanel.style.margin = "50px auto 0 auto";
        frontPanel.style.padding = "20px 4px 20px 4px";
        frontPanel.style.borderRadius = "10px";
        frontPanel.style.position = "relatvie";
        frontPanel.style.textAlign = "Center";
        frontPanel.onclick = function () {
            bodyNode.removeChild(backBox);
        };
        backBox.appendChild(frontPanel);
        frontPanel.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2001)));
    },

    INTERMediatorCheckBrowser: function (deleteNode) {
        var positiveList, matchAgent, matchOS, versionStr, agent, os, judge, specifiedVersion, versionNum,
            msieMark, dotPos, bodyNode, elm, childElm, grandChildElm, i;

        positiveList = INTERMediatorOnPage.browserCompatibility();
        matchAgent = false;
        matchOS = false;
        versionStr;
        for (agent in  positiveList) {
            if (navigator.userAgent.toUpperCase().indexOf(agent.toUpperCase()) > -1) {
                matchAgent = true;
                if (positiveList[agent] instanceof Object) {
                    for (os in positiveList[agent]) {
                        if (navigator.platform.toUpperCase().indexOf(os.toUpperCase()) > -1) {
                            matchOS = true;
                            versionStr = positiveList[agent][os];
                            break;
                        }
                    }
                } else {
                    matchOS = true;
                    versionStr = positiveList[agent];
                    break;
                }
            }
        }
        judge = false;
        if (matchAgent && matchOS) {
            specifiedVersion = parseInt(versionStr);
            if (navigator.appVersion.indexOf('MSIE') > -1) {
                msieMark = navigator.appVersion.indexOf('MSIE');
                dotPos = navigator.appVersion.indexOf('.', msieMark);
                versionNum = parseInt(navigator.appVersion.substring(msieMark + 4, dotPos));
                /*
                 As for the appVersion property of IE, refer http://msdn.microsoft.com/en-us/library/aa478988.aspx
                 */
            } else {
                dotPos = navigator.appVersion.indexOf('.');
                versionNum = parseInt(navigator.appVersion.substring(0, dotPos));
            }
            if (versionStr.indexOf('-') > -1) {
                judge = (specifiedVersion >= versionNum);
            } else if (versionStr.indexOf('+') > -1) {
                judge = (specifiedVersion <= versionNum);
            } else {
                judge = (specifiedVersion == versionNum);
            }
        }
        if (judge) {
            if (deleteNode != null) {
                deleteNode.parentNode.removeChild(deleteNode);
            }
        } else {
            bodyNode = document.getElementsByTagName('BODY')[0];
            elm = document.createElement("div");
            elm.setAttribute("align", "center");
            childElm = document.createElement("font");
            childElm.setAttribute("color", "gray");
            grandChildElm = document.createElement("font");
            grandChildElm.setAttribute("size", "+2");
            grandChildElm.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[1022]));
            childElm.appendChild(grandChildElm);
            childElm.appendChild(document.createElement("br"));
            childElm.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[1023]));
            childElm.appendChild(document.createElement("br"));
            childElm.appendChild(document.createTextNode(navigator.userAgent));
            elm.appendChild(childElm);
            for (i = bodyNode.childNodes.length-1; i >= 0; i--) {
                bodyNode.removeChild(bodyNode.childNodes[i]);
            }
            bodyNode.appendChild(elm);
        }
        return judge;
    },

    /*
     Seek nodes from the repeater of "fromNode" parameter.
     */
    getNodeIdFromIMDefinition: function (imDefinition, fromNode, justFromNode) {
        var repeaterNode;
        if (justFromNode) {
            repeaterNode = fromNode;
        } else {
            repeaterNode = INTERMediatorLib.getParentRepeater(fromNode);
        }
        return seekNode(repeaterNode, imDefinition);

        function seekNode(node, imDefinition) {
            var children, i, nodeDefs, returnValue;
            if (node.nodeType != 1) {
                return null;
            }
            children = node.childNodes;
            if (children) {
                for (i = 0; i < children.length; i++) {
                    if (children[i].nodeType == 1) {
                        if (INTERMediatorLib.isLinkedElement(children[i])) {
                            nodeDefs = INTERMediatorLib.getLinkedElementInfo(children[i]);
                            if (nodeDefs.indexOf(imDefinition) > -1) {
                                returnValue = children[i].getAttribute('id');
                                return returnValue;
                            }
                        }
                        returnValue = seekNode(children[i], imDefinition);
                        if (returnValue !== null) {
                            return returnValue;
                        }
                    }
                }
            }
            return null;
        }
    },

    getNodeIdFromIMDefinitionOnEnclosure: function (imDefinition, fromNode) {
        var repeaterNode;
        repeaterNode = INTERMediatorLib.getParentEnclosure(fromNode);
        return seekNode(repeaterNode, imDefinition);

        function seekNode(node, imDefinition) {
            var children, i, nodeDefs, returnValue;
            if (node.nodeType != 1) {
                return null;
            }
            children = node.childNodes;
            if (children) {
                for (i = 0; i < children.length; i++) {
                    if (children[i].nodeType == 1) {
                        if (INTERMediatorLib.isLinkedElement(children[i])) {
                            nodeDefs = INTERMediatorLib.getLinkedElementInfo(children[i]);
                            if (nodeDefs.indexOf(imDefinition) > -1) {
                                returnValue = children[i].getAttribute('id');
                                return returnValue;
                            }
                        }
                        returnValue = seekNode(children[i], imDefinition);
                        if (returnValue !== null) {
                            return returnValue;
                        }
                    }
                }
            }
            return null;
        }
    },

    getNodeIdsFromIMDefinition: function (imDefinition, fromNode, justFromNode) {
        var enclosureNode, nodeIds;

        if (justFromNode) {
            enclosureNode = fromNode;
        } else {
            enclosureNode = INTERMediatorLib.getParentEnclosure(fromNode);
        }
        if (enclosureNode != null) {
            nodeIds = [];
            seekNode(enclosureNode, imDefinition);
        }
        return nodeIds;

        function seekNode(node, imDefinition) {
            var thisClass, thisTitle, children, i;
            if (node.nodeType != 1) {
                return;
            }
            children = node.childNodes;
            if (children) {
                for (i = 0; i < children.length; i++) {
                    if (children[i].getAttribute != null) {
                        thisClass = children[i].getAttribute('class');
                        thisTitle = children[i].getAttribute('title');
                        if ((thisClass != null && thisClass.indexOf(imDefinition) > -1)
                            || (thisTitle != null && thisTitle.indexOf(imDefinition) > -1)) {
                            nodeIds.push(children[i].getAttribute('id'));
                        }
                    }
                    seekNode(children[i], imDefinition);
                }
            }
        }
    },

    getKeyWithRealm: function (str) {
        if (INTERMediatorOnPage.realm.length > 0) {
            return str + "_" + INTERMediatorOnPage.realm;
        }
        return str;
    },

    getCookie: function (key) {
        var s, i, targetKey;
        s = document.cookie.split('; ');
        targetKey = this.getKeyWithRealm(key);
        for (i = 0; i < s.length; i++) {
            if (s[i].indexOf(targetKey + '=') == 0) {
                return decodeURIComponent(s[i].substring(s[i].indexOf('=') + 1));
            }
        }
        return '';
    },
    removeCookie: function (key) {
        document.cookie = this.getKeyWithRealm(key) + "=; path=/; max-age=0; expires=Thu, 1-Jan-1900 00:00:00 GMT;";
        document.cookie = this.getKeyWithRealm(key) + "=; max-age=0;  expires=Thu, 1-Jan-1900 00:00:00 GMT;";
    },

    setCookie: function (key, val) {
        this.setCookieWorker(this.getKeyWithRealm(key), val, false);
    },

    setCookieDomainWide: function (key, val) {
        this.setCookieWorker(this.getKeyWithRealm(key), val, true);
    },

    setCookieWorker: function (key, val, isDomain) {
        var cookieString;
        var d = new Date();
        d.setTime(d.getTime() + INTERMediatorOnPage.authExpired * 1000);
        document.cookie = key + "=" + encodeURIComponent(val)
            + ( isDomain ? ";path=/" : "" )
            + ";max-age=" + INTERMediatorOnPage.authExpired
            + ";expires=" + d.toGMTString() + ';';
    },

    hideProgress: function () {
        var frontPanel;
        frontPanel = document.getElementById('_im_progress');
        if (frontPanel) {
            frontPanel.parentNode.removeChild(frontPanel);
        }
    },

    showProgress: function () {
        var bodyNode, frontPanel, imageProgress, imageIM;

        frontPanel = document.getElementById('_im_progress');
        if (!frontPanel) {
            bodyNode = document.getElementsByTagName('BODY')[0];
            frontPanel = document.createElement('div');
            frontPanel.setAttribute('id', '_im_progress');
            frontPanel.style.backgroundColor = "#000000";
            frontPanel.style.textAlign = "center";
            frontPanel.style.width = "130px";
            frontPanel.style.height = "55px";
            frontPanel.style.left = "0";
            frontPanel.style.top = "0";
            frontPanel.style.color = "#DDDDAA";
            frontPanel.style.fontSize = "6px";
            frontPanel.style.position = "absolute";
            frontPanel.style.padding = "6px";
            frontPanel.style.borderRadius = "0 0 10px 0";
            frontPanel.style.borderRight = frontPanel.style.borderBottom = "solid 4px #779933"
            frontPanel.style.zIndex = "999999";
            if (bodyNode.firstChild) {
                bodyNode.insertBefore(frontPanel, bodyNode.firstChild);
            } else {
                bodyNode.appendChild(frontPanel);
            }

            /*  GIF animation image was generated on
             But they describe no copyright or kind of message doesn't required.
             */
            imageIM = document.createElement('img');
            imageIM.setAttribute('src', "data:image/gif;base64," +
                "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");
            imageIM.style.marginRight = "12px";
            frontPanel.appendChild(imageIM);
            imageProgress = document.createElement('img');
            imageProgress.setAttribute('src', "data:image/gif;base64," +
                "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");
            frontPanel.appendChild(imageProgress);
            frontPanel.appendChild(document.createElement("BR"));
            frontPanel.appendChild(document.createTextNode("INTER-Mediator working"));
        }
    }

};

