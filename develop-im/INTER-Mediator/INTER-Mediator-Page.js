/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

var INTERMediatorOnPage;
INTERMediatorOnPage = {
    authCount:0,
    authUser:'',
    authHashedPassword:'',
    authUserSalt:'',
    authUserHexSalt:'',
    authChallenge:'',
    requireAuthentication:false,
    clientId:null,
    authRequiredContext:null,
    authStoring:'cookie',
    authExpired:3600,
    isOnceAtStarting:true,
    publickey:null,
    isNativeAuth:false,
    httpuser:null,
    httppasswd:null,
    mediaToken:null,

    isShowChangePassword: true,

    /*
     This method "getMessages" is going to be replaced valid one with the browser's language.
     Here is defined to prevent the warning of static check.
     */
    getMessages:function () {
        return null;
    },


    isComplementAuthData:function () {
        if (this.authUser != null && this.authUser.length > 0
            && this.authHashedPassword != null && this.authHashedPassword.length > 0
            && this.authUserSalt != null && this.authUserSalt.length > 0
            && this.authChallenge != null && this.authChallenge.length > 0) {
            return true;
        }
        return false;
    },

    retrieveAuthInfo:function () {
        if (this.requireAuthentication) {
            if (this.isOnceAtStarting) {
                switch (this.authStoring) {
                    case 'cookie':
                    case 'cookie-domainwide':
                        this.authUser = this.getCookie('_im_username');
                        this.authHashedPassword = this.getCookie('_im_crendential');
                        this.mediaToken = this.getCookie('_im_mediatoken');
                        break;
                    default:
                        this.removeCookie('_im_username');
                        this.removeCookie('_im_crendential');
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

    logout:function () {
        this.authUser = "";
        this.authHashedPassword = "";
        this.authUserSalt = "";
        this.authChallenge = "";
        this.clientId = "";
        this.removeCookie('_im_username');
        this.removeCookie('_im_crendential');
        this.removeCookie('_im_mediatoken');
    },

    storeCredencialsToCookie:function () {
        switch (INTERMediatorOnPage.authStoring) {
            case 'cookie':
                INTERMediatorOnPage.setCookie('_im_username', INTERMediatorOnPage.authUser);
                INTERMediatorOnPage.setCookie('_im_crendential', INTERMediatorOnPage.authHashedPassword);
                if ( INTERMediatorOnPage.mediaToken )   {
                    INTERMediatorOnPage.setCookieDomainWide('_im_mediatoken', INTERMediatorOnPage.mediaToken);
                }break;
            case 'cookie-domainwide':
                INTERMediatorOnPage.setCookieDomainWide('_im_username', INTERMediatorOnPage.authUser);
                INTERMediatorOnPage.setCookieDomainWide('_im_crendential', INTERMediatorOnPage.authHashedPassword);
                if ( INTERMediatorOnPage.mediaToken )   {
                    INTERMediatorOnPage.setCookieDomainWide('_im_mediatoken', INTERMediatorOnPage.mediaToken);
                }
                break;
        }
    },

    authenticating:function (doAfterAuth) {
        var bodyNode, backBox, frontPanel, labelWidth, userLabel, userSpan, userBox;
        var passwordLabel, passwordSpan, passwordBox, breakLine, chgpwButton, authButton;
        var newPasswordLabel, newPasswordSpan, newPasswordBox, newPasswordMessage;

        if (this.authCount > 10) {
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
        backBox.style.backgroundImage = "url(data:image/png;base64,"
            + "iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAAXNSR0IArs4c6QAA"
            + "ACF0RVh0U29mdHdhcmUAR3JhcGhpY0NvbnZlcnRlciAoSW50ZWwpd4f6GQAAAHRJ"
            + "REFUeJzs0bENAEAMAjHWzBC/f5sxkPIurkcmSV65KQcAAAAAAAAAAAAAAAAAAAAA"
            + "AAAAAAAAAAAAAAAAAAAAAL4AaA9oHwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA"
            + "AAAAAAAAAAAAOA6wAAAA//8DAF3pMFsPzhYWAAAAAElFTkSuQmCC)";
        backBox.style.position = "absolute";
        backBox.style.padding = " 50px 0 0 0";
        backBox.style.top = "0";
        backBox.style.left = "0";
        backBox.style.zIndex = "999998";

        frontPanel = document.createElement('div');
        frontPanel.style.width = "300px";
        frontPanel.style.backgroundColor = "#333333";
        frontPanel.style.color = "#DDDDAA";
        frontPanel.style.margin = "50px auto 0 auto";
        frontPanel.style.padding = "20px";
        frontPanel.style.borderRadius = "10px";
        frontPanel.style.position = "relatvie";
        backBox.appendChild(frontPanel);

        labelWidth = "110px";
        userLabel = document.createElement('LABEL');
        frontPanel.appendChild(userLabel);
        userSpan = document.createElement('div');
        userSpan.style.width = labelWidth;
        userSpan.style.textAlign = "right";
        userSpan.style.cssFloat = "left";
        userLabel.appendChild(userSpan);
        userSpan.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2002)));
        userBox = document.createElement('INPUT');
        userBox.type = "text";
        userBox.value = INTERMediatorOnPage.authUser;
        userBox.id = "_im_username";
        userBox.size = "12";
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
        passwordBox.size = "12";
        passwordBox.onkeydown = function (event) {
            if (event.keyCode == 13) {
                authButton.onclick();
            }
        };
        userBox.onkeydown = function (event) {
            if (event.keyCode == 13) {
                passwordBox.focus();
            }
        };
        passwordLabel.appendChild(passwordBox);

        authButton = document.createElement('BUTTON');
//        authButton.style.fontSize = "12pt";
        authButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2004)));
        authButton.onclick = function () {
            var inputUsername,  inputPassword, challengeResult;

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
        frontPanel.appendChild(authButton);

        breakLine = document.createElement('BR');
        breakLine.clear = "all";
        frontPanel.appendChild(breakLine);

        if ( this.isShowChangePassword && ! INTERMediatorOnPage.isNativeAuth )   {

            breakLine = document.createElement('HR');
            frontPanel.appendChild(breakLine);

            newPasswordLabel = document.createElement('LABEL');
            frontPanel.appendChild(newPasswordLabel);
            newPasswordSpan = document.createElement('SPAN');
            newPasswordSpan.style.minWidth = labelWidth;
            newPasswordSpan.style.textAlign = "right";
            newPasswordSpan.style.cssFloat = "left";
            newPasswordLabel.appendChild(newPasswordSpan);
            newPasswordSpan.appendChild(
                document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2006)));
            newPasswordBox = document.createElement('INPUT');
            newPasswordBox.type = "password";
            newPasswordBox.id = "_im_newpassword";
            newPasswordBox.size = "12";
            newPasswordLabel.appendChild(newPasswordBox);
            chgpwButton = document.createElement('BUTTON');
            //chgpwButton.style.marginLeft = labelWidth;
            chgpwButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2005)));
            chgpwButton.onclick = function () {
                var inputUsername,  inputPassword, inputNewPassword, challengeResult, params, result;

                inputUsername = document.getElementById('_im_username').value;
                inputPassword = document.getElementById('_im_password').value;
                inputNewPassword = document.getElementById('_im_newpassword').value;
                if ( inputUsername === '' || inputPassword === '' || inputNewPassword === '' )  {
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
                } catch(e) {
                    result = {newPasswordResult: false};
                }
                newPasswordMessage.innerHTML = INTERMediatorLib.getInsertedStringFromErrorNumber(
                    result.newPasswordResult===true?2009:2010);

                INTERMediator.flushMessage();
            };
            frontPanel.appendChild(chgpwButton);

            newPasswordMessage = document.createElement('DIV');
            newPasswordMessage.style.textAlign = "center";
            newPasswordMessage.style.textSize = "10pt";
            newPasswordMessage.style.color = "#994433";
            frontPanel.appendChild(newPasswordMessage);


        }

        window.scroll(0, 0);
        userBox.focus();
        INTERMediatorOnPage.authCount++;
    },

    authenticationError:function () {
        var bodyNode, backBox, frontPanel;

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

    INTERMediatorCheckBrowser:function (deleteNode) {
        var positiveList, matchAgent, matchOS, versionStr,agent, os, judge, specifiedVersion, versionNum;
        var msieMark, dotPos, bodyNode;

         positiveList = INTERMediatorOnPage.browserCompatibility();
         matchAgent = false;
         matchOS = false;
         versionStr;
        for ( agent in  positiveList) {
            if (navigator.userAgent.toUpperCase().indexOf(agent.toUpperCase()) > -1) {
                matchAgent = true;
                if (positiveList[agent] instanceof Object) {
                    for ( os in positiveList[agent]) {
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
             versionNum;
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
            bodyNode.innerHTML = '<div align="center"><font color="gray"><font size="+2">'
                + INTERMediatorOnPage.getMessages()[1022] + '</font><br>'
                + INTERMediatorOnPage.getMessages()[1023] + '<br>' + navigator.userAgent + '</font></div>';
        }
        return judge;
    },

    /*
     Seek nodes from the repeater of "fromNode" parameter.
     */
    getNodeIdFromIMDefinition:function (imDefinition, fromNode) {
        var repeaterNode;

        repeaterNode = INTERMediatorLib.getParentRepeater(fromNode);
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

    getNodeIdsFromIMDefinition:function (imDefinition, fromNode) {
        var children, i, thisClass, thisTitle, enclosureNode, nodeIds;

         enclosureNode = INTERMediatorLib.getParentEnclosure(fromNode);
        if (enclosureNode != null) {
             nodeIds = [];
            seekNode(enclosureNode, imDefinition);
        }
        return nodeIds;

        function seekNode(node, imDefinition) {
            if (node.nodeType != 1) {
                return null;
            }
            children = node.childNodes;
            if (children == null) {
                return null;
            } else {
                for ( i = 0; i < children.length; i++) {
                    if (children[i].getAttribute != null) {
                        thisClass = children[i].getAttribute('class');
                        thisTitle = children[i].getAttribute('title');
                        if ((thisClass != null && thisClass.indexOf(imDefinition) > -1)
                            || (thisTitle != null && thisTitle.indexOf(imDefinition) > -1)) {
                            nodeIds.push(children[i].getAttribute('id'));
                        }
                        seekNode(children[i], imDefinition);
                    }
                }
            }
            return null;
        }
    },

    getCookie:function (key) {
        var s, i;
         s = document.cookie.split(';');
        for ( i = 0; i < s.length; i++) {
            if (s[i].indexOf(key + '=') > -1) {
                return decodeURIComponent(s[i].substring(s[i].indexOf('=') + 1));
            }
        }
        return '';
    },
    removeCookie:function (key) {
        document.cookie = key + "=";
    },

    setCookie:function (key, val) {
        this.setCookieWorker(key, val, false);
    },

    setCookieDomainWide:function (key, val) {
        this.setCookieWorker(key, val, true);
    },

    setCookieWorker:function (key, val, isDomain) {
        var cookieString, expDate;
         expDate = new Date();
        expDate.setTime(expDate.getTime() + (INTERMediatorOnPage.authExpired * 1000));
        cookieString = key + "=" + encodeURIComponent(val)
            + ( isDomain ? ";path=/" : "" )
            + ";expires=" + expDate.toGMTString();
        document.cookie = cookieString;
    }
};

