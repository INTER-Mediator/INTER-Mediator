/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

//"use strict"

var INTERMediatorOnPage;

INTERMediatorOnPage = {
    authCountLimit: 4,
    authCount: 0,
    authUser: "",
    authHashedPassword: "",
    authCryptedPassword: "",
    authUserSalt: "",
    authUserHexSalt: "",
    authChallenge: "",
    requireAuthentication: false,
    clientId: null,
    authRequiredContext: null,
    authStoring: "cookie",
    authExpired: 3600,
    isOnceAtStarting: true,
    publickey: null,
    isNativeAuth: false,
    httpuser: null,
    httppasswd: null,
    mediaToken: null,
    realm: "",
    dbCache: {},
    isEmailAsUsername: false,

    isShowChangePassword: true,
    isSetDefaultStyle: true,
    authPanelTitle: null,

    additionalExpandingEnclosureFinish: {},
    additionalExpandingRecordFinish: {},

    clearCredentials: function () {
        "use strict";
        INTERMediatorOnPage.authChallenge = null;
        INTERMediatorOnPage.authHashedPassword = null;
        INTERMediatorOnPage.authCryptedPassword = null;
    },
    /*
     This method "getMessages" is going to be replaced valid one with the browser's language.
     Here is defined to prevent the warning of static check.
     */
    getMessages: function () {
        "use strict";
        return null;
    },

    getURLParametersAsArray: function () {
        "use strict";
        var i, params, eqPos, result, key, value;
        result = {};
        params = location.search.substring(1).split("&");
        for (i = 0; i < params.length; i++) {
            eqPos = params[i].indexOf("=");
            if (eqPos > 0) {
                key = params[i].substring(0, eqPos);
                value = params[i].substring(eqPos + 1);
                result[key] = decodeURIComponent(value);
            }
        }
        return result;
    },

    getContextInfo: function (contextName) {
        "use strict";
        var dataSources, index;
        dataSources = INTERMediatorOnPage.getDataSources();
        for (index in dataSources) {
            if (dataSources[index].name == contextName) {
                return dataSources[index];
            }
        }
        return null;
    },

    isComplementAuthData: function () {
        "use strict";
        if (this.authUser != null && this.authUser.length > 0 &&
            this.authHashedPassword != null && this.authHashedPassword.length > 0 &&
            this.authUserSalt != null && this.authUserSalt.length > 0 &&
            this.authChallenge != null && this.authChallenge.length > 0) {
            return true;
        }
        return false;
    },

    retrieveAuthInfo: function () {
        "use strict";
        if (INTERMediatorOnPage.requireAuthentication) {
            if (INTERMediatorOnPage.isOnceAtStarting) {
                switch (INTERMediatorOnPage.authStoring) {
                    case "cookie":
                    case "cookie-domainwide":
                        INTERMediatorOnPage.authUser = this.getCookie("_im_username");
                        INTERMediatorOnPage.authHashedPassword = this.getCookie("_im_credential");
                        INTERMediatorOnPage.mediaToken = this.getCookie("_im_mediatoken");
                        INTERMediatorOnPage.authCryptedPassword = this.getCookie("_im_crypted");
                        break;
                    default:
                        INTERMediatorOnPage.removeCookie("_im_username");
                        INTERMediatorOnPage.removeCookie("_im_credential");
                        INTERMediatorOnPage.removeCookie("_im_mediatoken");
                        INTERMediatorOnPage.removeCookie("_im_crypted");
                        break;
                }
                INTERMediatorOnPage.isOnceAtStarting = false;
            }
            if (this.authUser.length > 0) {
                if (!INTERMediator_DBAdapter.getChallenge()) {
                    INTERMediator.flushMessage();
                }
            }
        }
    },

    logout: function () {
        "use strict";
        INTERMediatorOnPage.authUser = "";
        INTERMediatorOnPage.authHashedPassword = "";
        INTERMediatorOnPage.authCryptedPassword = "";
        INTERMediatorOnPage.authUserSalt = "";
        INTERMediatorOnPage.authChallenge = "";
        INTERMediatorOnPage.clientId = "";
        INTERMediatorOnPage.removeCookie("_im_username");
        INTERMediatorOnPage.removeCookie("_im_credential");
        INTERMediatorOnPage.removeCookie("_im_mediatoken");
        INTERMediatorOnPage.removeCookie("_im_crypted");
        INTERMediatorOnPage.removeCookie("_im_localcontext");
        if (INTERMediator.useSessionStorage === true &&
            typeof sessionStorage !== "undefined" &&
            sessionStorage !== null) {
            try {
                sessionStorage.removeItem("_im_localcontext");
            } catch (ex) {
                INTERMediatorOnPage.removeCookie("_im_localcontext");
            }
        } else {
            INTERMediatorOnPage.removeCookie("_im_localcontext");
        }
    },

    storeCredencialsToCookie: function () {
        "use strict";
        switch (INTERMediatorOnPage.authStoring) {
            case "cookie":
                if (INTERMediatorOnPage.authUser) {
                    INTERMediatorOnPage.setCookie("_im_username", INTERMediatorOnPage.authUser);
                }
                if (INTERMediatorOnPage.authHashedPassword) {
                    INTERMediatorOnPage.setCookie("_im_credential", INTERMediatorOnPage.authHashedPassword);
                }
                if (INTERMediatorOnPage.mediaToken) {
                    INTERMediatorOnPage.setCookie("_im_mediatoken", INTERMediatorOnPage.mediaToken);
                }
                if (INTERMediatorOnPage.authCryptedPassword) {
                    INTERMediatorOnPage.setCookie("_im_crypted", INTERMediatorOnPage.authCryptedPassword);
                }
                break;
            case "cookie-domainwide":
                if (INTERMediatorOnPage.authUser) {
                    INTERMediatorOnPage.setCookieDomainWide("_im_username", INTERMediatorOnPage.authUser);
                }
                if (INTERMediatorOnPage.authHashedPassword) {
                    INTERMediatorOnPage.setCookieDomainWide("_im_credential", INTERMediatorOnPage.authHashedPassword);
                }
                if (INTERMediatorOnPage.mediaToken) {
                    INTERMediatorOnPage.setCookieDomainWide("_im_mediatoken", INTERMediatorOnPage.mediaToken);
                }
                if (INTERMediatorOnPage.authCryptedPassword) {
                    INTERMediatorOnPage.setCookieDomainWide("_im_crypted", INTERMediatorOnPage.authCryptedPassword);
                }
                break;
        }
    },

    defaultBackgroundImage: "url(data:image/png;base64," +
        "iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAAXNSR0IArs4c6QAA" +
        "ACF0RVh0U29mdHdhcmUAR3JhcGhpY0NvbnZlcnRlciAoSW50ZWwpd4f6GQAAAHRJ" +
        "REFUeJzs0bENAEAMAjHWzBC/f5sxkPIurkcmSV65KQcAAAAAAAAAAAAAAAAAAAAA" +
        "AAAAAAAAAAAAAAAAAAAAAL4AaA9oHwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA" +
        "AAAAAAAAAAAAOA6wAAAA//8DAF3pMFsPzhYWAAAAAElFTkSuQmCC)",

    defaultBackgroundColor: null,
    loginPanelHTML: null,

    authenticating: function (doAfterAuth) {
        "use strict";
        var bodyNode, backBox, frontPanel, labelWidth, userLabel, userSpan, userBox, msgNumber,
            passwordLabel, passwordSpan, passwordBox, breakLine, chgpwButton, authButton, panelTitle,
            newPasswordLabel, newPasswordSpan, newPasswordBox, newPasswordMessage, realmBox, keyCode,
            messageNode;

        if (INTERMediatorOnPage.authCount > INTERMediatorOnPage.authCountLimit) {
            INTERMediatorOnPage.authenticationError();
            INTERMediatorOnPage.logout();
            INTERMediator.flushMessage();
            return;
        }

        bodyNode = document.getElementsByTagName("BODY")[0];
        backBox = document.createElement("div");
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
            passwordBox = document.getElementById("_im_password");
            userBox = document.getElementById("_im_username");
            authButton = document.getElementById("_im_authbutton");
            chgpwButton = document.getElementById("_im_changebutton");
        } else {
            frontPanel = document.createElement("div");
            if (INTERMediatorOnPage.isSetDefaultStyle) {
                frontPanel.style.width = "450px";
                frontPanel.style.backgroundColor = "#333333";
                frontPanel.style.color = "#DDDDAA";
                frontPanel.style.margin = "50px auto 0 auto";
                frontPanel.style.padding = "20px";
                frontPanel.style.borderRadius = "10px";
                frontPanel.style.position = "relative";
            }
            frontPanel.id = "_im_authpanel";
            backBox.appendChild(frontPanel);

            panelTitle = "";
            if (INTERMediatorOnPage.authPanelTitle && INTERMediatorOnPage.authPanelTitle.length > 0) {
                panelTitle = INTERMediatorOnPage.authPanelTitle;
            } else if (INTERMediatorOnPage.realm && INTERMediatorOnPage.realm.length > 0) {
                panelTitle = INTERMediatorOnPage.realm;
            }
            if (panelTitle && panelTitle.length > 0) {
                realmBox = document.createElement("DIV");
                realmBox.appendChild(document.createTextNode(panelTitle));
                realmBox.style.textAlign = "left";
                frontPanel.appendChild(realmBox);
                breakLine = document.createElement("HR");
                frontPanel.appendChild(breakLine);
            }

            labelWidth = "200px";
            userLabel = document.createElement("LABEL");
            frontPanel.appendChild(userLabel);
            userSpan = document.createElement("span");
            if (INTERMediatorOnPage.isSetDefaultStyle) {
                userSpan.style.width = labelWidth;
                userSpan.style.textAlign = "right";
                userSpan.style.cssFloat = "left";
            }
            INTERMediatorLib.setClassAttributeToNode(userSpan, "_im_authlabel");
            userLabel.appendChild(userSpan);
            msgNumber = INTERMediatorOnPage.isEmailAsUsername ? 2011 : 2002;
            userSpan.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(msgNumber)));
            userBox = document.createElement("INPUT");
            userBox.type = "text";
            userBox.id = "_im_username";
            userBox.size = "24";
            userBox.setAttribute("autocapitalize", "off");
            userLabel.appendChild(userBox);

            breakLine = document.createElement("BR");
            breakLine.clear = "all";
            frontPanel.appendChild(breakLine);

            passwordLabel = document.createElement("LABEL");
            frontPanel.appendChild(passwordLabel);
            passwordSpan = document.createElement("SPAN");
            if (INTERMediatorOnPage.isSetDefaultStyle) {
                passwordSpan.style.minWidth = labelWidth;
                passwordSpan.style.textAlign = "right";
                passwordSpan.style.cssFloat = "left";
            }
            INTERMediatorLib.setClassAttributeToNode(passwordSpan, "_im_authlabel");
            passwordLabel.appendChild(passwordSpan);
            passwordSpan.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2003)));
            passwordBox = document.createElement("INPUT");
            passwordBox.type = "password";
            passwordBox.id = "_im_password";
            passwordBox.size = "24";
            passwordLabel.appendChild(passwordBox);

            authButton = document.createElement("BUTTON");
            authButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2004)));
            frontPanel.appendChild(authButton);

            breakLine = document.createElement("BR");
            breakLine.clear = "all";
            frontPanel.appendChild(breakLine);

            newPasswordMessage = document.createElement("DIV");
            newPasswordMessage.style.textAlign = "center";
            newPasswordMessage.style.textSize = "10pt";
            newPasswordMessage.style.color = "#994433";
            newPasswordMessage.id = "_im_login_message";
            frontPanel.appendChild(newPasswordMessage);

            if (this.isShowChangePassword && !INTERMediatorOnPage.isNativeAuth) {

                breakLine = document.createElement("HR");
                frontPanel.appendChild(breakLine);

                newPasswordLabel = document.createElement("LABEL");
                frontPanel.appendChild(newPasswordLabel);
                newPasswordSpan = document.createElement("SPAN");
                if (INTERMediatorOnPage.isSetDefaultStyle) {
                    newPasswordSpan.style.minWidth = labelWidth;
                    newPasswordSpan.style.textAlign = "right";
                    newPasswordSpan.style.cssFloat = "left";
                    newPasswordSpan.style.fontSize = "0.7em";
                    newPasswordSpan.style.paddingTop = "4px";
                }
                INTERMediatorLib.setClassAttributeToNode(newPasswordSpan, "_im_authlabel");
                newPasswordLabel.appendChild(newPasswordSpan);
                newPasswordSpan.appendChild(
                    document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2006)));
                newPasswordBox = document.createElement("INPUT");
                newPasswordBox.type = "password";
                newPasswordBox.id = "_im_newpassword";
                newPasswordBox.size = "12";
                newPasswordLabel.appendChild(newPasswordBox);
                chgpwButton = document.createElement("BUTTON");
                chgpwButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2005)));
                frontPanel.appendChild(chgpwButton);

                newPasswordMessage = document.createElement("DIV");
                newPasswordMessage.style.textAlign = "center";
                newPasswordMessage.style.textSize = "10pt";
                newPasswordMessage.style.color = "#994433";
                newPasswordMessage.id = "_im_newpass_message";
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

            inputUsername = document.getElementById("_im_username").value;
            inputPassword = document.getElementById("_im_password").value;

            if (inputUsername === "" || inputPassword === "") {
                messageNode = document.getElementById("_im_login_message");
                INTERMediatorLib.removeChildNodes(messageNode);
                messageNode.appendChild(
                    document.createTextNode(
                        INTERMediatorLib.getInsertedStringFromErrorNumber(2013)));
                return;
            }
            INTERMediatorOnPage.authUser = inputUsername;
            bodyNode.removeChild(backBox);
            if (inputUsername !== "" &&  // No usename and no challenge, get a challenge.
                (INTERMediatorOnPage.authChallenge === null || INTERMediatorOnPage.authChallenge.length < 24 )) {
                INTERMediatorOnPage.authHashedPassword = "need-hash-pls";   // Dummy Hash for getting a challenge
                challengeResult = INTERMediator_DBAdapter.getChallenge();
                if (!challengeResult) {
                    INTERMediator.flushMessage();
                    return; // If it's failed to get a challenge, finish everything.
                }
            }
            INTERMediatorOnPage.authCryptedPassword =
                INTERMediatorOnPage.publickey.biEncryptedString(inputPassword);
            INTERMediatorOnPage.authHashedPassword =
                SHA1(inputPassword + INTERMediatorOnPage.authUserSalt) +
                INTERMediatorOnPage.authUserHexSalt;

            if (INTERMediatorOnPage.authUser.length > 0) {   // Authentication succeed, Store coockies.
                INTERMediatorOnPage.storeCredencialsToCookie();
            }

            doAfterAuth();  // Retry.
            INTERMediator.flushMessage();
        };
        if (chgpwButton) {
            chgpwButton.onclick = function () {
                var inputUsername, inputPassword, inputNewPassword, challengeResult, params, result, messageNode;

                inputUsername = document.getElementById("_im_username").value;
                inputPassword = document.getElementById("_im_password").value;
                inputNewPassword = document.getElementById("_im_newpassword").value;
                if (inputUsername === "" || inputPassword === "" || inputNewPassword === "") {
                    messageNode = document.getElementById("_im_newpass_message");
                    INTERMediatorLib.removeChildNodes(messageNode);
                    messageNode.appendChild(
                        document.createTextNode(
                            INTERMediatorLib.getInsertedStringFromErrorNumber(2007)));
                    return;
                }
                INTERMediatorOnPage.authUser = inputUsername;
                if (inputUsername !== "" &&  // No usename and no challenge, get a challenge.
                    (INTERMediatorOnPage.authChallenge === null || INTERMediatorOnPage.authChallenge.length < 24 )) {
                    INTERMediatorOnPage.authHashedPassword = "need-hash-pls";   // Dummy Hash for getting a challenge
                    challengeResult = INTERMediator_DBAdapter.getChallenge();
                    if (!challengeResult) {
                        messageNode = document.getElementById("_im_newpass_message");
                        INTERMediatorLib.removeChildNodes(messageNode);
                        messageNode.appendChild(
                            document.createTextNode(
                                INTERMediatorLib.getInsertedStringFromErrorNumber(2008)));
                        INTERMediator.flushMessage();
                        return; // If it's failed to get a challenge, finish everything.
                    }
                }
                INTERMediatorOnPage.authHashedPassword =
                    SHA1(inputPassword + INTERMediatorOnPage.authUserSalt) +
                    INTERMediatorOnPage.authUserHexSalt;
                params = "access=changepassword&newpass=" + INTERMediatorLib.generatePasswordHash(inputNewPassword);
                try {
                    result = INTERMediator_DBAdapter.server_access(params, 1029, 1030);
                } catch (e) {
                    result = {newPasswordResult: false};
                }
                messageNode = document.getElementById("_im_newpass_message");
                INTERMediatorLib.removeChildNodes(messageNode);
                messageNode.appendChild(
                    document.createTextNode(
                        INTERMediatorLib.getInsertedStringFromErrorNumber(
                            result.newPasswordResult === true ? 2009 : 2010)));

                INTERMediator.flushMessage();
            };
        }

        if (INTERMediatorOnPage.authCount > 0) {
            messageNode = document.getElementById("_im_login_message");
            INTERMediatorLib.removeChildNodes(messageNode);
            messageNode.appendChild(
                document.createTextNode(
                    INTERMediatorLib.getInsertedStringFromErrorNumber(2012)));
        }

        window.scroll(0, 0);
        userBox.focus();
        INTERMediatorOnPage.authCount++;
    },

    authenticationError: function () {
        "use strict";
        var bodyNode, backBox, frontPanel;

        INTERMediatorOnPage.hideProgress();

        bodyNode = document.getElementsByTagName("BODY")[0];
        backBox = document.createElement("div");
        bodyNode.insertBefore(backBox, bodyNode.childNodes[0]);
        backBox.style.height = "100%";
        backBox.style.width = "100%";
        //backBox.style.backgroundColor = "#BBBBBB";
        backBox.style.backgroundImage = "url(data:image/png;base64," +
            "iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAAXNSR0IArs4c6QAA" +
            "ACF0RVh0U29mdHdhcmUAR3JhcGhpY0NvbnZlcnRlciAoSW50ZWwpd4f6GQAAAHlJ" +
            "REFUeJzs0UENACAQA8EzdAl2EIEg3CKjyTGP/TfTur1OuJ2sAAAAAAAAAAAAAAAA" +
            "AAAAAAAAAAAAAAAAAAAAAAAAAADAJwDRAekDAAAAAAAAAAAAAAAAAAAAAAAAAAAA" +
            "AAAAAAAAAAAAAAAAAADzAR4AAAD//wMAkUKRPI/rh/AAAAAASUVORK5CYII=)";
        backBox.style.position = "absolute";
        backBox.style.padding = " 50px 0 0 0";
        backBox.style.top = "0";
        backBox.style.left = "0";
        backBox.style.zIndex = "999999";

        frontPanel = document.createElement("div");
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
        "use strict";
        var positiveList, matchAgent, matchOS, versionStr, agent, os, judge = false, specifiedVersion,
            versionNum, agentPos = -1, dotPos, bodyNode, elm, childElm, grandChildElm, i;

        positiveList = INTERMediatorOnPage.browserCompatibility();
        matchAgent = false;
        matchOS = false;

        if (positiveList.edge && navigator.userAgent.indexOf("Edge/") > -1) {
            positiveList = {"edge": positiveList.edge};
        } else if (positiveList.trident && navigator.userAgent.indexOf("Trident/") > -1) {
            positiveList = {"trident": positiveList.trident};
        } else if (positiveList.msie && navigator.userAgent.indexOf("MSIE ") > -1) {
            positiveList = {"msie": positiveList.msie};
        } else if (positiveList.opera &&
            (navigator.userAgent.indexOf("Opera/") > -1 || navigator.userAgent.indexOf("OPR/") > -1)) {
            positiveList = {"opera": positiveList.opera, "opr": positiveList.opera};
        }

        for (agent in positiveList) {
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

        if (matchAgent && matchOS) {
            specifiedVersion = parseInt(versionStr, 10);

            if (navigator.appVersion.indexOf("Edge/") > -1) {
                agentPos = navigator.appVersion.indexOf("Edge/") + 5;
            } else if (navigator.appVersion.indexOf("Trident/") > -1) {
                agentPos = navigator.appVersion.indexOf("Trident/") + 8;
            } else if (navigator.appVersion.indexOf("MSIE ") > -1) {
                agentPos = navigator.appVersion.indexOf("MSIE ") + 5;
            } else if (navigator.appVersion.indexOf("OPR/") > -1) {
                agentPos = navigator.appVersion.indexOf("OPR/") + 4;
            } else if (navigator.appVersion.indexOf("Opera/") > -1) {
                agentPos = navigator.appVersion.indexOf("Opera/") + 6;
            } else if (navigator.appVersion.indexOf("Chrome/") > -1) {
                agentPos = navigator.appVersion.indexOf("Chrome/") + 7;
            } else if (navigator.appVersion.indexOf("Safari/") > -1 &&
                navigator.appVersion.indexOf("Version/") > -1) {
                agentPos = navigator.appVersion.indexOf("Version/") + 8;
            } else if (navigator.userAgent.indexOf("Firefox/") > -1) {
                agentPos = navigator.userAgent.indexOf("Firefox/") + 8;
            }

            if (agentPos > -1) {
                if (navigator.userAgent.indexOf("Firefox/") > -1) {
                    dotPos = navigator.userAgent.indexOf(".", agentPos);
                    versionNum = parseInt(navigator.userAgent.substring(agentPos, dotPos), 10);
                } else {
                    dotPos = navigator.appVersion.indexOf(".", agentPos);
                    versionNum = parseInt(navigator.appVersion.substring(agentPos, dotPos), 10);
                }
                /*
                 As for the appVersion property of IE, refer http://msdn.microsoft.com/en-us/library/aa478988.aspx
                 */
            } else {
                dotPos = navigator.appVersion.indexOf(".");
                versionNum = parseInt(navigator.appVersion.substring(0, dotPos), 10);
            }
            if (INTERMediator.isTrident) {
                specifiedVersion = specifiedVersion + 4;
            }
            if (versionStr.indexOf("-") > -1) {
                judge = (specifiedVersion >= versionNum);
                if (document.documentMode) {
                    judge = (specifiedVersion >= document.documentMode);
                }
            } else if (versionStr.indexOf("+") > -1) {
                judge = (specifiedVersion <= versionNum);
                if (document.documentMode) {
                    judge = (specifiedVersion <= document.documentMode);
                }
            } else {
                judge = (specifiedVersion == versionNum);
                if (document.documentMode) {
                    judge = (specifiedVersion == document.documentMode);
                }
            }
        }
        if (judge === true) {
            if (deleteNode !== null) {
                deleteNode.parentNode.removeChild(deleteNode);
            }
        } else {
            bodyNode = document.getElementsByTagName("BODY")[0];
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
            for (i = bodyNode.childNodes.length - 1; i >= 0; i--) {
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
        "use strict";
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
                                returnValue = children[i].getAttribute("id");
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
        "use strict";
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
                            if (nodeDefs.indexOf(imDefinition) > -1 && children[i].getAttribute) {
                                returnValue = children[i].getAttribute("id");
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
        "use strict";
        var enclosureNode, nodeIds, i;

        if (justFromNode === true) {
            enclosureNode = fromNode;
        } else if (justFromNode === false) {
            enclosureNode = INTERMediatorLib.getParentEnclosure(fromNode);
        } else {
            enclosureNode = INTERMediatorLib.getParentRepeater(fromNode);
        }
        if (enclosureNode != null) {
            nodeIds = [];
            if (Array.isArray(enclosureNode))   {
                for (i = 0 ; i < enclosureNode.length ; i++)    {
                    seekNode(enclosureNode[i], imDefinition);
                }
            } else {
                seekNode(enclosureNode, imDefinition);
            }
        }
        return nodeIds;

        function seekNode(node, imDefinition) {
            var children, i, nodeDefs;
            if (node.nodeType != 1) {
                return;
            }
            children = node.childNodes;
            if (children) {
                for (i = 0; i < children.length; i++) {
                    if (children[i].nodeType == 1) {
                        nodeDefs = INTERMediatorLib.getLinkedElementInfo(children[i]);
                        if (nodeDefs && nodeDefs.indexOf(imDefinition) > -1) {
                            if (children[i].getAttribute("id")) {
                                nodeIds.push(children[i].getAttribute("id"));
                            } else {
                                nodeIds.push(children[i]);
                            }
                        }
                    }
                    seekNode(children[i], imDefinition);
                }
            }
        }
    },

    getNodeIdsHavingTargetFromNode: function (fromNode, imDefinition) {
        "use strict";
        return INTERMediatorOnPage.getNodeIdsFromIMDefinition(imDefinition, fromNode, true);
    },

    getNodeIdsHavingTargetFromRepeater: function (fromNode, imDefinition) {
        "use strict";
        return INTERMediatorOnPage.getNodeIdsFromIMDefinition(imDefinition, fromNode, "");
    },

    getNodeIdsHavingTargetFromEnclosure: function (fromNode, imDefinition) {
        "use strict";
        return INTERMediatorOnPage.getNodeIdsFromIMDefinition(imDefinition, fromNode, false);
    },

    /* Cookies support */
    getKeyWithRealm: function (str) {
        "use strict";
        if (INTERMediatorOnPage.realm.length > 0) {
            return str + "_" + INTERMediatorOnPage.realm;
        }
        return str;
    },

    getCookie: function (key) {
        "use strict";
        var s, i, targetKey;
        s = document.cookie.split("; ");
        targetKey = this.getKeyWithRealm(key);
        for (i = 0; i < s.length; i++) {
            if (s[i].indexOf(targetKey + "=") == 0) {
                return decodeURIComponent(s[i].substring(s[i].indexOf("=") + 1));
            }
        }
        return "";
    },
    removeCookie: function (key) {
        "use strict";
        document.cookie = this.getKeyWithRealm(key) + "=; path=/; max-age=0; expires=Thu, 1-Jan-1900 00:00:00 GMT;";
        document.cookie = this.getKeyWithRealm(key) + "=; max-age=0;  expires=Thu, 1-Jan-1900 00:00:00 GMT;";
    },

    setCookie: function (key, val) {
        "use strict";
        this.setCookieWorker(this.getKeyWithRealm(key), val, false, INTERMediatorOnPage.authExpired);
    },

    setCookieDomainWide: function (key, val) {
        "use strict";
        this.setCookieWorker(this.getKeyWithRealm(key), val, true, INTERMediatorOnPage.authExpired);
    },

    setCookieWorker: function (key, val, isDomain, expired) {
        "use strict";
        var cookieString;
        var d = new Date();
        d.setTime(d.getTime() + expired * 1000);
        cookieString = key + "=" + encodeURIComponent(val) + ( isDomain ? ";path=/" : "" ) + ";";
        if (expired > 0) {
            cookieString += "max-age=" + expired + ";expires=" + d.toGMTString() + ";";
        }
        if (document.URL.substring(0, 8) == "https://") {
            cookieString += "secure;";
        }
        document.cookie = cookieString;
    },

    hideProgress: function () {
        "use strict";
        var frontPanel;
        frontPanel = document.getElementById("_im_progress");
        if (frontPanel) {
            frontPanel.parentNode.removeChild(frontPanel);
        }
    },

    showProgress: function () {
        "use strict";
        var rootPath, headNode, bodyNode, frontPanel, linkElement, imageProgress, imageIM;

        frontPanel = document.getElementById("_im_progress");
        if (!frontPanel) {
            rootPath = INTERMediatorOnPage.getIMRootPath();
            headNode = document.getElementsByTagName("HEAD")[0];
            bodyNode = document.getElementsByTagName("BODY")[0];
            frontPanel = document.createElement("div");
            frontPanel.setAttribute("id", "_im_progress");
            linkElement = document.createElement("link");
            linkElement.setAttribute("href", rootPath + "/themes/default/css/style.css");
            linkElement.setAttribute("rel", "stylesheet");
            linkElement.setAttribute("type", "text/css");
            headNode.appendChild(linkElement);
            if (bodyNode.firstChild) {
                bodyNode.insertBefore(frontPanel, bodyNode.firstChild);
            } else {
                bodyNode.appendChild(frontPanel);
            }

            /*  GIF animation image was generated on
             But they describe no copyright or kind of message doesn't required.
             */
            imageIM = document.createElement("img");
            imageIM.setAttribute("id", "_im_logo");
            imageIM.setAttribute("src", rootPath + "/themes/default/images/logo.gif");
            frontPanel.appendChild(imageIM);
            imageProgress = document.createElement("img");
            imageProgress.setAttribute("src", rootPath + "/themes/default/images/inprogress.gif");
            frontPanel.appendChild(imageProgress);
            frontPanel.appendChild(document.createElement("BR"));
            frontPanel.appendChild(document.createTextNode("INTER-Mediator working"));
        }
    }
};
