/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

//'use strict';
/**
 * @fileoverview INTERMediatorOnPage class is defined here.
 */
/**
 *
 * Usually you don't have to instanciate this class with new operator.
 * @constructor
 */
var INTERMediatorOnPage = {
    authCountLimit: 4,
    authCount: 0,
    authUser: '',
    authHashedPassword: '',
    authCryptedPassword: '',
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
    passwordPolicy: null,
    creditIncluding: null,
    masterScrollPosition: null,
    nonSupportMessageId: 'nonsupportmessage',
    isFinishToConstruct: false,
    isAutoConstruct: true,

    isShowChangePassword: true,
    isSetDefaultStyle: true,
    authPanelTitle: null,
    isOAuthAvailable: false,
    oAuthClientID: null,
    oAuthClientSecret: null,
    oAuthBaseURL: null,
    oAuthRedirect: null,
    oAuthScope: null,

    additionalExpandingEnclosureFinish: {},
    additionalExpandingRecordFinish: {},

    getEditorPath: null,
    getEntryPath: null,
    getIMRootPath: null,
    getDataSources: null,
    getOptionsAliases: null,
    getOptionsTransaction: null,
    dbClassName: null,
    browserCompatibility: null,
    clientNotificationIdentifier: null,
    metadata: null,
    isLDAP: null,

    clearCredentials: function () {
        'use strict';
        INTERMediatorOnPage.authChallenge = null;
        INTERMediatorOnPage.authHashedPassword = null;
        INTERMediatorOnPage.authCryptedPassword = null;
    },
    /*
     This method 'getMessages' is going to be replaced valid one with the browser's language.
     Here is defined to prevent the warning of static check.
     */
    getMessages: function () {
        'use strict';
        return null;
    },

    getURLParametersAsArray: function () {
        'use strict';
        var i, params, eqPos, result, key, value;
        result = {};
        params = location.search.substring(1).split('&');
        for (i = 0; i < params.length; i++) {
            eqPos = params[i].indexOf('=');
            if (eqPos > 0) {
                key = params[i].substring(0, eqPos);
                value = params[i].substring(eqPos + 1);
                result[key] = decodeURIComponent(value);
            }
        }
        return result;
    },

    getContextInfo: function (contextName) {
        'use strict';
        var dataSources, index;
        dataSources = INTERMediatorOnPage.getDataSources();
        for (index in dataSources) {
            if (dataSources.hasOwnProperty(index) && dataSources[index].name == contextName) {
                return dataSources[index];
            }
        }
        return null;
    },

    isComplementAuthData: function () {
        'use strict';
        return INTERMediatorOnPage.authUser !== null && INTERMediatorOnPage.authUser.length > 0 &&
            INTERMediatorOnPage.authHashedPassword !== null && INTERMediatorOnPage.authHashedPassword.length > 0 &&
            INTERMediatorOnPage.authUserSalt !== null && INTERMediatorOnPage.authUserSalt.length > 0 &&
            INTERMediatorOnPage.authChallenge !== null && INTERMediatorOnPage.authChallenge.length > 0;
    },

    retrieveAuthInfo: function () {
        'use strict';
        if (INTERMediatorOnPage.requireAuthentication) {
            if (INTERMediatorOnPage.isOnceAtStarting) {
                switch (INTERMediatorOnPage.authStoring) {
                case 'cookie':
                case 'cookie-domainwide':
                    INTERMediatorOnPage.authUser =
                        INTERMediatorOnPage.getCookie('_im_username');
                    INTERMediatorOnPage.authHashedPassword =
                        INTERMediatorOnPage.getCookie('_im_credential');
                    INTERMediatorOnPage.mediaToken =
                        INTERMediatorOnPage.getCookie('_im_mediatoken');
                    INTERMediatorOnPage.authCryptedPassword =
                        INTERMediatorOnPage.getCookie('_im_crypted');
                    break;
                case 'session-storage':
                    INTERMediatorOnPage.authUser =
                        INTERMediatorOnPage.getSessionStorageWithFallDown('_im_username');
                    INTERMediatorOnPage.authHashedPassword =
                        INTERMediatorOnPage.getSessionStorageWithFallDown('_im_credential');
                    INTERMediatorOnPage.mediaToken =
                        INTERMediatorOnPage.getSessionStorageWithFallDown('_im_mediatoken');
                    INTERMediatorOnPage.authCryptedPassword =
                        INTERMediatorOnPage.getSessionStorageWithFallDown('_im_crypted');
                    break;
                default:
                    INTERMediatorOnPage.removeCookie('_im_username');
                    INTERMediatorOnPage.removeCookie('_im_credential');
                    INTERMediatorOnPage.removeCookie('_im_mediatoken');
                    INTERMediatorOnPage.removeCookie('_im_crypted');
                    break;
                }
                INTERMediatorOnPage.isOnceAtStarting = false;
            }
            if (INTERMediatorOnPage.authUser.length > 0) {
                if (!INTERMediator_DBAdapter.getChallenge()) {
                    INTERMediator.flushMessage();
                }
            }
        }
    },

    logout: function () {
        'use strict';
        INTERMediatorOnPage.authUser = '';
        INTERMediatorOnPage.authHashedPassword = '';
        INTERMediatorOnPage.authCryptedPassword = '';
        INTERMediatorOnPage.authUserSalt = '';
        INTERMediatorOnPage.authChallenge = '';
        INTERMediatorOnPage.clientId = '';
        INTERMediatorOnPage.removeCredencialsFromCookieOrStorage();
        INTERMediatorOnPage.removeFromSessionStorageWithFallDown('_im_localcontext');
    },

    storeSessionStorageWithFallDown: function (key, value) {
        if (INTERMediator.useSessionStorage === true &&
            typeof sessionStorage !== 'undefined' &&
            sessionStorage !== null) {
            try {
                sessionStorage.setItem(INTERMediatorOnPage.getKeyWithRealm(key), value);
            } catch (ex) {
                INTERMediatorOnPage.setCookie(key, value);
            }
        } else {
            INTERMediatorOnPage.setCookie(key, value);
        }
    },

    getSessionStorageWithFallDown: function (key) {
        var value;
        if (INTERMediator.useSessionStorage === true &&
            typeof sessionStorage !== 'undefined' &&
            sessionStorage !== null) {
            try {
                value = sessionStorage.getItem(INTERMediatorOnPage.getKeyWithRealm(key));
                value = value ? value : '';
            } catch (ex) {
                value = INTERMediatorOnPage.getCookie(key);
            }
        } else {
            value = INTERMediatorOnPage.getCookie(key);
        }
        return value;
    },

    removeFromSessionStorageWithFallDown: function (key) {
        if (INTERMediator.useSessionStorage === true &&
            typeof sessionStorage !== 'undefined' &&
            sessionStorage !== null) {
            try {
                sessionStorage.removeItem(INTERMediatorOnPage.getKeyWithRealm(key));
            } catch (ex) {
                INTERMediatorOnPage.removeCookie(key);
            }
        } else {
            INTERMediatorOnPage.removeCookie(key);
        }
    },

    removeCredencialsFromCookieOrStorage: function () {
        'use strict';
        switch (INTERMediatorOnPage.authStoring) {
        case 'cookie':
        case 'cookie-domainwide':
            INTERMediatorOnPage.removeCookie('_im_username');
            INTERMediatorOnPage.removeCookie('_im_credential');
            INTERMediatorOnPage.removeCookie('_im_mediatoken');
            INTERMediatorOnPage.removeCookie('_im_crypted');
            break;
        case 'session-storage':
            INTERMediatorOnPage.removeFromSessionStorageWithFallDown('_im_username');
            INTERMediatorOnPage.removeFromSessionStorageWithFallDown('_im_credential');
            INTERMediatorOnPage.removeFromSessionStorageWithFallDown('_im_mediatoken');
            INTERMediatorOnPage.removeFromSessionStorageWithFallDown('_im_crypted');
            break;
        }
    },

    storeCredentialsToCookieOrStorage: function () {
        'use strict';
        switch (INTERMediatorOnPage.authStoring) {
        case 'cookie':
            if (INTERMediatorOnPage.authUser) {
                INTERMediatorOnPage.setCookie('_im_username', INTERMediatorOnPage.authUser);
            }
            if (INTERMediatorOnPage.authHashedPassword) {
                INTERMediatorOnPage.setCookie('_im_credential', INTERMediatorOnPage.authHashedPassword);
            }
            if (INTERMediatorOnPage.mediaToken) {
                INTERMediatorOnPage.setCookie('_im_mediatoken', INTERMediatorOnPage.mediaToken);
            }
            if (INTERMediatorOnPage.authCryptedPassword) {
                INTERMediatorOnPage.setCookie('_im_crypted', INTERMediatorOnPage.authCryptedPassword);
            }
            break;
        case 'cookie-domainwide':
            if (INTERMediatorOnPage.authUser) {
                INTERMediatorOnPage.setCookieDomainWide('_im_username', INTERMediatorOnPage.authUser);
            }
            if (INTERMediatorOnPage.authHashedPassword) {
                INTERMediatorOnPage.setCookieDomainWide('_im_credential', INTERMediatorOnPage.authHashedPassword);
            }
            if (INTERMediatorOnPage.mediaToken) {
                INTERMediatorOnPage.setCookieDomainWide('_im_mediatoken', INTERMediatorOnPage.mediaToken);
            }
            if (INTERMediatorOnPage.authCryptedPassword) {
                INTERMediatorOnPage.setCookieDomainWide('_im_crypted', INTERMediatorOnPage.authCryptedPassword);
            }
            break;
        case 'session-storage':
            if (INTERMediatorOnPage.authUser) {
                INTERMediatorOnPage.storeSessionStorageWithFallDown('_im_username', INTERMediatorOnPage.authUser);
            }
            if (INTERMediatorOnPage.authHashedPassword) {
                INTERMediatorOnPage.storeSessionStorageWithFallDown('_im_credential', INTERMediatorOnPage.authHashedPassword);
            }
            if (INTERMediatorOnPage.mediaToken) {
                INTERMediatorOnPage.storeSessionStorageWithFallDown('_im_mediatoken', INTERMediatorOnPage.mediaToken);
            }
            if (INTERMediatorOnPage.authCryptedPassword) {
                INTERMediatorOnPage.storeSessionStorageWithFallDown('_im_crypted', INTERMediatorOnPage.authCryptedPassword);
            }
            break;
        }
    },

    defaultBackgroundImage: null,
    defaultBackgroundColor: null,
    loginPanelHTML: null,

    authenticating: function (doAfterAuth, doTest) {
        'use strict';
        var bodyNode, backBox, frontPanel, labelWidth, userLabel, userSpan, userBox, msgNumber,
            passwordLabel, passwordSpan, passwordBox, breakLine, chgpwButton, authButton, panelTitle,
            newPasswordLabel, newPasswordSpan, newPasswordBox, newPasswordMessage, realmBox, keyCode,
            messageNode, oAuthButton;

        this.checkPasswordPolicy = function (newPassword, userName, policyString) {
            var terms, i, policyCheck, message = [], minLen;
            if (!policyString) {
                return message;
            }
            terms = policyString.split(/[\s,]/);
            for (i = 0; i < terms.length; i++) {
                switch (terms[i].toUpperCase()) {
                case 'USEALPHABET':
                    if (!newPassword.match(/[A-Za-z]/)) {
                        policyCheck = false;
                        message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2015));
                    }
                    break;
                case 'USENUMBER':
                    if (!newPassword.match(/[0-9]/)) {
                        policyCheck = false;
                        message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2016));
                    }
                    break;
                case 'USEUPPER':
                    if (!newPassword.match(/[A-Z]/)) {
                        policyCheck = false;
                        message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2017));
                    }
                    break;
                case 'USELOWER':
                    if (!newPassword.match(/[a-z]/)) {
                        policyCheck = false;
                        message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2018));
                    }
                    break;
                case 'USEPUNCTUATION':
                    if (!newPassword.match(/[^A-Za-z0-9]/)) {
                        policyCheck = false;
                        message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2019));
                    }
                    break;
                case 'NOTUSERNAME':
                    if (newPassword == userName) {
                        policyCheck = false;
                        message.push(INTERMediatorLib.getInsertedStringFromErrorNumber(2020));
                    }
                    break;
                default:
                    if (terms[i].toUpperCase().indexOf('LENGTH') === 0) {
                        minLen = terms[i].match(/[0-9]+/)[0];
                        if (newPassword.length < minLen) {
                            policyCheck = false;
                            message.push(
                                INTERMediatorLib.getInsertedStringFromErrorNumber(2021, [minLen]));
                        }
                    }
                }
            }
            return message;
        };

        if (doTest) {
            return;
        }

        if (INTERMediatorOnPage.authCount > INTERMediatorOnPage.authCountLimit) {
            INTERMediatorOnPage.authenticationError();
            INTERMediatorOnPage.logout();
            INTERMediator.flushMessage();
            return;
        }

        bodyNode = document.getElementsByTagName('BODY')[0];
        backBox = document.createElement('div');
        bodyNode.insertBefore(backBox, bodyNode.childNodes[0]);
        backBox.style.height = '100%';
        backBox.style.width = '100%';
        if (INTERMediatorOnPage.defaultBackgroundImage) {
            backBox.style.backgroundImage = 'url(' + INTERMediatorOnPage.defaultBackgroundImage + ')';
        } else if (INTERMediatorOnPage.isSetDefaultStyle) {
            backBox.style.backgroundImage = 'url(' + INTERMediatorOnPage.getEntryPath()
                + '?theme=' + INTERMediatorOnPage.getTheme() + '&type=images&name=background.gif)';
        }
        if (INTERMediatorOnPage.defaultBackgroundColor) {
            backBox.style.backgroundColor = INTERMediatorOnPage.defaultBackgroundColor;
        }
        backBox.style.position = 'absolute';
        backBox.style.padding = ' 50px 0 0 0';
        backBox.style.top = '0';
        backBox.style.left = '0';
        backBox.style.zIndex = '999998';

        if (INTERMediatorOnPage.loginPanelHTML) {
            backBox.innerHTML = INTERMediatorOnPage.loginPanelHTML;
            passwordBox = document.getElementById('_im_password');
            userBox = document.getElementById('_im_username');
            authButton = document.getElementById('_im_authbutton');
            chgpwButton = document.getElementById('_im_changebutton');
            oAuthButton = document.getElementById('_im_oauthbutton');
        } else {
            frontPanel = document.createElement('div');
            if (INTERMediatorOnPage.isSetDefaultStyle) {
                frontPanel.style.width = '450px';
                frontPanel.style.backgroundColor = '#333333';
                frontPanel.style.color = '#DDDDAA';
                frontPanel.style.margin = '50px auto 0 auto';
                frontPanel.style.padding = '20px';
                frontPanel.style.borderRadius = '10px';
                frontPanel.style.position = 'relative';
            }
            frontPanel.id = '_im_authpanel';
            backBox.appendChild(frontPanel);

            panelTitle = '';
            if (INTERMediatorOnPage.authPanelTitle && INTERMediatorOnPage.authPanelTitle.length > 0) {
                panelTitle = INTERMediatorOnPage.authPanelTitle;
            } else if (INTERMediatorOnPage.realm && INTERMediatorOnPage.realm.length > 0) {
                panelTitle = INTERMediatorOnPage.realm;
            }
            if (panelTitle && panelTitle.length > 0) {
                realmBox = document.createElement('DIV');
                realmBox.appendChild(document.createTextNode(panelTitle));
                realmBox.style.textAlign = 'left';
                frontPanel.appendChild(realmBox);
                breakLine = document.createElement('HR');
                frontPanel.appendChild(breakLine);
            }

            labelWidth = '100px';
            userLabel = document.createElement('LABEL');
            frontPanel.appendChild(userLabel);
            userSpan = document.createElement('span');
            if (INTERMediatorOnPage.isSetDefaultStyle) {
                userSpan.style.minWidth = labelWidth;
                userSpan.style.textAlign = 'right';
                userSpan.style.cssFloat = 'left';
            }
            INTERMediatorLib.setClassAttributeToNode(userSpan, '_im_authlabel');
            userLabel.appendChild(userSpan);
            msgNumber = INTERMediatorOnPage.isEmailAsUsername ? 2011 : 2002;
            userSpan.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(msgNumber)));
            userBox = document.createElement('INPUT');
            userBox.type = 'text';
            userBox.id = '_im_username';
            userBox.size = '20';
            userBox.setAttribute('autocapitalize', 'off');
            userLabel.appendChild(userBox);

            breakLine = document.createElement('BR');
            breakLine.clear = 'all';
            frontPanel.appendChild(breakLine);

            passwordLabel = document.createElement('LABEL');
            frontPanel.appendChild(passwordLabel);
            passwordSpan = document.createElement('SPAN');
            if (INTERMediatorOnPage.isSetDefaultStyle) {
                passwordSpan.style.minWidth = labelWidth;
                passwordSpan.style.textAlign = 'right';
                passwordSpan.style.cssFloat = 'left';
            }
            INTERMediatorLib.setClassAttributeToNode(passwordSpan, '_im_authlabel');
            passwordLabel.appendChild(passwordSpan);
            passwordSpan.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2003)));
            passwordBox = document.createElement('INPUT');
            passwordBox.type = 'password';
            passwordBox.id = '_im_password';
            passwordBox.size = '20';
            passwordLabel.appendChild(passwordBox);

            authButton = document.createElement('BUTTON');
            authButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2004)));
            frontPanel.appendChild(authButton);

            breakLine = document.createElement('BR');
            breakLine.clear = 'all';
            frontPanel.appendChild(breakLine);

            newPasswordMessage = document.createElement('DIV');
            newPasswordMessage.style.textAlign = 'center';
            newPasswordMessage.style.textSize = '10pt';
            newPasswordMessage.style.color = '#994433';
            newPasswordMessage.id = '_im_login_message';
            frontPanel.appendChild(newPasswordMessage);

            if (this.isShowChangePassword && !INTERMediatorOnPage.isNativeAuth) {
                breakLine = document.createElement('HR');
                frontPanel.appendChild(breakLine);

                newPasswordLabel = document.createElement('LABEL');
                frontPanel.appendChild(newPasswordLabel);
                newPasswordSpan = document.createElement('SPAN');
                if (INTERMediatorOnPage.isSetDefaultStyle) {
                    newPasswordSpan.style.minWidth = labelWidth;
                    newPasswordSpan.style.textAlign = 'right';
                    newPasswordSpan.style.cssFloat = 'left';
                    newPasswordSpan.style.fontSize = '0.7em';
                    newPasswordSpan.style.paddingTop = '4px';
                }
                INTERMediatorLib.setClassAttributeToNode(newPasswordSpan, '_im_authlabel');
                newPasswordLabel.appendChild(newPasswordSpan);
                newPasswordSpan.appendChild(
                    document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2006)));
                newPasswordBox = document.createElement('INPUT');
                newPasswordBox.type = 'password';
                newPasswordBox.id = '_im_newpassword';
                newPasswordBox.size = '12';
                newPasswordLabel.appendChild(newPasswordBox);
                chgpwButton = document.createElement('BUTTON');
                chgpwButton.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2005)));
                frontPanel.appendChild(chgpwButton);

                newPasswordMessage = document.createElement('DIV');
                newPasswordMessage.style.textAlign = 'center';
                newPasswordMessage.style.textSize = '10pt';
                newPasswordMessage.style.color = '#994433';
                newPasswordMessage.id = '_im_newpass_message';
                frontPanel.appendChild(newPasswordMessage);
            }
            if (this.isOAuthAvailable) {
                breakLine = document.createElement('HR');
                frontPanel.appendChild(breakLine);
                oAuthButton = document.createElement('BUTTON');
                oAuthButton.appendChild(document.createTextNode(
                    INTERMediatorLib.getInsertedStringFromErrorNumber(2014)));
                frontPanel.appendChild(oAuthButton);
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
            var inputUsername, inputPassword, challengeResult, messageNode;

            messageNode = document.getElementById('_im_newpass_message');
            if (messageNode) {
                INTERMediatorLib.removeChildNodes(messageNode);
            }

            inputUsername = document.getElementById('_im_username').value;
            inputPassword = document.getElementById('_im_password').value;

            if (inputUsername === '' || inputPassword === '') {
                messageNode = document.getElementById('_im_login_message');
                INTERMediatorLib.removeChildNodes(messageNode);
                messageNode.appendChild(
                    document.createTextNode(
                        INTERMediatorLib.getInsertedStringFromErrorNumber(2013)));
                return;
            }
            INTERMediatorOnPage.authUser = inputUsername;
            bodyNode.removeChild(backBox);
            if (inputUsername !== '' &&  // No usename and no challenge, get a challenge.
                (INTERMediatorOnPage.authChallenge === null || INTERMediatorOnPage.authChallenge.length < 24 )) {
                INTERMediatorOnPage.authHashedPassword = 'need-hash-pls';   // Dummy Hash for getting a challenge
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
                INTERMediatorOnPage.storeCredentialsToCookieOrStorage();
            }

            doAfterAuth();  // Retry.
            INTERMediator.flushMessage();
        };
        if (chgpwButton) {
            var checkPolicyMethod = this.checkPasswordPolicy;
            chgpwButton.onclick = function () {
                var inputUsername, inputPassword, inputNewPassword, result, messageNode, message;

                messageNode = document.getElementById('_im_login_message');
                INTERMediatorLib.removeChildNodes(messageNode);
                messageNode = document.getElementById('_im_newpass_message');
                INTERMediatorLib.removeChildNodes(messageNode);

                inputUsername = document.getElementById('_im_username').value;
                inputPassword = document.getElementById('_im_password').value;
                inputNewPassword = document.getElementById('_im_newpassword').value;
                if (inputUsername === '' || inputPassword === '' || inputNewPassword === '') {
                    messageNode = document.getElementById('_im_newpass_message');
                    INTERMediatorLib.removeChildNodes(messageNode);
                    messageNode.appendChild(
                        document.createTextNode(
                            INTERMediatorLib.getInsertedStringFromErrorNumber(2007)));
                    return;
                }

                message = checkPolicyMethod(inputNewPassword, inputUsername, INTERMediatorOnPage.passwordPolicy);
                if (message.length > 0) {  // Policy violated.
                    messageNode.appendChild(document.createTextNode(message.join(', ')));
                    return;
                }

                result = INTERMediator_DBAdapter.changePassword(inputUsername, inputPassword, inputNewPassword);
                messageNode.appendChild(
                    document.createTextNode(
                        INTERMediatorLib.getInsertedStringFromErrorNumber(result ? 2009 : 2010)));

                INTERMediator.flushMessage();
            };
        }
        if (this.isOAuthAvailable && oAuthButton) {
            oAuthButton.onclick = function () {
                var authURL;
                INTERMediatorOnPage.setCookieDomainWide('_im_oauth_backurl', location.href, true);
                INTERMediatorOnPage.setCookieDomainWide('_im_oauth_realm', INTERMediatorOnPage.realm, true);
                INTERMediatorOnPage.setCookieDomainWide('_im_oauth_expired', INTERMediatorOnPage.authExpired, true);
                INTERMediatorOnPage.setCookieDomainWide('_im_oauth_storing', INTERMediatorOnPage.authStoring, true);
                authURL = INTERMediatorOnPage.oAuthBaseURL +
                    '?scope=' + encodeURIComponent(INTERMediatorOnPage.oAuthScope) +
                    '&redirect_uri=' + encodeURIComponent(INTERMediatorOnPage.oAuthRedirect) +
                    '&response_type=code' +
                    '&client_id=' + encodeURIComponent(INTERMediatorOnPage.oAuthClientID);
                location.href = authURL;
            };
        }

        if (INTERMediatorOnPage.authCount > 0) {
            messageNode = document.getElementById('_im_login_message');
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
        'use strict';
        var bodyNode, backBox, frontPanel;

        INTERMediatorOnPage.hideProgress();

        bodyNode = document.getElementsByTagName('BODY')[0];
        backBox = document.createElement('div');
        bodyNode.insertBefore(backBox, bodyNode.childNodes[0]);
        backBox.style.height = '100%';
        backBox.style.width = '100%';
        //backBox.style.backgroundColor = '#BBBBBB';
        if (INTERMediatorOnPage.isSetDefaultStyle) {
            backBox.style.backgroundImage = 'url(' + INTERMediatorOnPage.getEntryPath()
                + '?theme=' + INTERMediatorOnPage.getTheme() + '&type=images&name=background-error.gif)';
        }
        backBox.style.position = 'absolute';
        backBox.style.padding = ' 50px 0 0 0';
        backBox.style.top = '0';
        backBox.style.left = '0';
        backBox.style.zIndex = '999999';

        frontPanel = document.createElement('div');
        frontPanel.style.width = '240px';
        frontPanel.style.backgroundColor = '#333333';
        frontPanel.style.color = '#DD6666';
        frontPanel.style.fontSize = '16pt';
        frontPanel.style.margin = '50px auto 0 auto';
        frontPanel.style.padding = '20px 4px 20px 4px';
        frontPanel.style.borderRadius = '10px';
        frontPanel.style.position = 'relatvie';
        frontPanel.style.textAlign = 'Center';
        frontPanel.onclick = function () {
            bodyNode.removeChild(backBox);
        };
        backBox.appendChild(frontPanel);
        frontPanel.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2001)));
    },

    /**
     *
     * @param deleteNode
     * @returns {boolean}
     */
    INTERMediatorCheckBrowser: function (deleteNode) {
        'use strict';
        var positiveList, matchAgent, matchOS, versionStr, agent, os, judge = false, specifiedVersion,
            versionNum, agentPos = -1, dotPos, bodyNode, elm, childElm, grandChildElm, i;

        positiveList = INTERMediatorOnPage.browserCompatibility();
        matchAgent = false;
        matchOS = false;

        if (positiveList.edge && navigator.userAgent.indexOf('Edge/') > -1) {
            positiveList = {'edge': positiveList.edge};
        } else if (positiveList.trident && navigator.userAgent.indexOf('Trident/') > -1) {
            positiveList = {'trident': positiveList.trident};
        } else if (positiveList.msie && navigator.userAgent.indexOf('MSIE ') > -1) {
            positiveList = {'msie': positiveList.msie};
        } else if (positiveList.opera &&
            (navigator.userAgent.indexOf('Opera/') > -1 || navigator.userAgent.indexOf('OPR/') > -1)) {
            positiveList = {'opera': positiveList.opera, 'opr': positiveList.opera};
        }

        for (agent in positiveList) {
            if (positiveList.hasOwnProperty(agent)) {
                if (navigator.userAgent.toUpperCase().indexOf(agent.toUpperCase()) > -1) {
                    matchAgent = true;
                    if (positiveList[agent] instanceof Object) {
                        for (os in positiveList[agent]) {
                            if (positiveList[agent].hasOwnProperty(os) &&
                                navigator.platform.toUpperCase().indexOf(os.toUpperCase()) > -1) {
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
        }

        if (matchAgent && matchOS) {
            specifiedVersion = parseInt(versionStr, 10);

            if (navigator.appVersion.indexOf('Edge/') > -1) {
                agentPos = navigator.appVersion.indexOf('Edge/') + 5;
            } else if (navigator.appVersion.indexOf('Trident/') > -1) {
                agentPos = navigator.appVersion.indexOf('Trident/') + 8;
            } else if (navigator.appVersion.indexOf('MSIE ') > -1) {
                agentPos = navigator.appVersion.indexOf('MSIE ') + 5;
            } else if (navigator.appVersion.indexOf('OPR/') > -1) {
                agentPos = navigator.appVersion.indexOf('OPR/') + 4;
            } else if (navigator.appVersion.indexOf('Opera/') > -1) {
                agentPos = navigator.appVersion.indexOf('Opera/') + 6;
            } else if (navigator.appVersion.indexOf('Chrome/') > -1) {
                agentPos = navigator.appVersion.indexOf('Chrome/') + 7;
            } else if (navigator.appVersion.indexOf('Safari/') > -1 &&
                navigator.appVersion.indexOf('Version/') > -1) {
                agentPos = navigator.appVersion.indexOf('Version/') + 8;
            } else if (navigator.userAgent.indexOf('Firefox/') > -1) {
                agentPos = navigator.userAgent.indexOf('Firefox/') + 8;
            }

            if (agentPos > -1) {
                if (navigator.userAgent.indexOf('Firefox/') > -1) {
                    dotPos = navigator.userAgent.indexOf('.', agentPos);
                    versionNum = parseInt(navigator.userAgent.substring(agentPos, dotPos), 10);
                } else {
                    dotPos = navigator.appVersion.indexOf('.', agentPos);
                    versionNum = parseInt(navigator.appVersion.substring(agentPos, dotPos), 10);
                }
                /*
                 As for the appVersion property of IE, refer http://msdn.microsoft.com/en-us/library/aa478988.aspx
                 */
            } else {
                dotPos = navigator.appVersion.indexOf('.');
                versionNum = parseInt(navigator.appVersion.substring(0, dotPos), 10);
            }
            if (INTERMediator.isTrident) {
                specifiedVersion = specifiedVersion + 4;
            }
            if (versionStr.indexOf('-') > -1) {
                judge = (specifiedVersion >= versionNum);
                if (document.documentMode) {
                    judge = (specifiedVersion >= document.documentMode);
                }
            } else if (versionStr.indexOf('+') > -1) {
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
            if (deleteNode) {
                deleteNode.parentNode.removeChild(deleteNode);
            }
        } else {
            bodyNode = document.getElementsByTagName('BODY')[0];
            elm = document.createElement('div');
            elm.setAttribute('align', 'center');
            childElm = document.createElement('font');
            childElm.setAttribute('color', 'gray');
            grandChildElm = document.createElement('font');
            grandChildElm.setAttribute('size', '+2');
            grandChildElm.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[1022]));
            childElm.appendChild(grandChildElm);
            childElm.appendChild(document.createElement('br'));
            childElm.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[1023]));
            childElm.appendChild(document.createElement('br'));
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
     Seek nodes from the repeater of 'fromNode' parameter.
     */
    getNodeIdFromIMDefinition: function (imDefinition, fromNode, justFromNode) {
        'use strict';
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
        'use strict';
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
        'use strict';
        var enclosureNode, nodeIds, i;

        if (justFromNode === true) {
            enclosureNode = fromNode;
        } else if (justFromNode === false) {
            enclosureNode = INTERMediatorLib.getParentEnclosure(fromNode);
        } else {
            enclosureNode = INTERMediatorLib.getParentRepeater(fromNode);
        }
        if (enclosureNode !== null) {
            nodeIds = [];
            if (Array.isArray(enclosureNode)) {
                for (i = 0; i < enclosureNode.length; i++) {
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
                            if (children[i].getAttribute('id')) {
                                nodeIds.push(children[i].getAttribute('id'));
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
        'use strict';
        return INTERMediatorOnPage.getNodeIdsFromIMDefinition(imDefinition, fromNode, true);
    },

    getNodeIdsHavingTargetFromRepeater: function (fromNode, imDefinition) {
        'use strict';
        return INTERMediatorOnPage.getNodeIdsFromIMDefinition(imDefinition, fromNode, IMLib.zerolength_str);
    },

    getNodeIdsHavingTargetFromEnclosure: function (fromNode, imDefinition) {
        'use strict';
        return INTERMediatorOnPage.getNodeIdsFromIMDefinition(imDefinition, fromNode, false);
    },

    /* Cookies support */
    getKeyWithRealm: function (str) {
        'use strict';
        if (INTERMediatorOnPage.realm.length > 0) {
            return str + '_' + INTERMediatorOnPage.realm;
        }
        return str;
    },

    getCookie: function (key) {
        'use strict';
        var s, i, targetKey;
        s = document.cookie.split('; ');
        targetKey = this.getKeyWithRealm(key);
        for (i = 0; i < s.length; i++) {
            if (s[i].indexOf(targetKey + '=') === 0) {
                return decodeURIComponent(s[i].substring(s[i].indexOf('=') + 1));
            }
        }
        return '';
    },
    removeCookie: function (key) {
        'use strict';
        document.cookie = this.getKeyWithRealm(key) + '=; path=/; max-age=0; expires=Thu, 1-Jan-1900 00:00:00 GMT;';
        document.cookie = this.getKeyWithRealm(key) + '=; max-age=0;  expires=Thu, 1-Jan-1900 00:00:00 GMT;';
    },

    setCookie: function (key, val) {
        'use strict';
        this.setCookieWorker(this.getKeyWithRealm(key), val, false, INTERMediatorOnPage.authExpired);
    },

    setCookieDomainWide: function (key, val, noRealm) {
        'use strict';
        var realKey;
        realKey = (noRealm === true) ? key : this.getKeyWithRealm(key);
        this.setCookieWorker(realKey, val, true, INTERMediatorOnPage.authExpired);
    },

    setCookieWorker: function (key, val, isDomain, expired) {
        'use strict';
        var cookieString;
        var d = new Date();
        d.setTime(d.getTime() + expired * 1000);
        cookieString = key + '=' + encodeURIComponent(val) + ( isDomain ? ';path=/' : '' ) + ';';
        if (expired > 0) {
            cookieString += 'max-age=' + expired + ';expires=' + d.toUTCString() + ';';
        }
        if (document.URL.substring(0, 8) == 'https://') {
            cookieString += 'secure;';
        }
        document.cookie = cookieString;
    },

    hideProgress: function () {
        'use strict';
        var frontPanel;
        frontPanel = document.getElementById('_im_progress');
        if (frontPanel) {
            frontPanel.parentNode.removeChild(frontPanel);
        }
    },

    showProgress: function () {
        'use strict';
        var rootPath, headNode, bodyNode, frontPanel, linkElement, imageProgress, imageIM;

        frontPanel = document.getElementById('_im_progress');
        if (!frontPanel) {
            //rootPath = INTERMediatorOnPage.getIMRootPath();
            //headNode = document.getElementsByTagName('HEAD')[0];
            bodyNode = document.getElementsByTagName('BODY')[0];
            frontPanel = document.createElement('div');
            frontPanel.setAttribute('id', '_im_progress');
            frontPanel.style.position = 'fixed';
            linkElement = document.createElement('link');
            // linkElement.setAttribute('href', rootPath + '/themes/default/css/style.css');
            // linkElement.setAttribute('rel', 'stylesheet');
            // linkElement.setAttribute('type', 'text/css');
            // headNode.appendChild(linkElement);
            if (bodyNode.firstChild) {
                bodyNode.insertBefore(frontPanel, bodyNode.firstChild);
            } else {
                bodyNode.appendChild(frontPanel);
            }

            /*  GIF animation image was generated on
             But they describe no copyright or kind of message doesn't required.
             */
            imageIM = document.createElement('img');
            imageIM.setAttribute('id', '_im_logo');
            imageIM.setAttribute('src', INTERMediatorOnPage.getEntryPath()
                + '?theme=' + INTERMediatorOnPage.getTheme() + '&type=images&name=logo.gif');
            frontPanel.appendChild(imageIM);
            imageProgress = document.createElement('img');
            imageProgress.setAttribute('src',
                INTERMediatorOnPage.getEntryPath()
                + '?theme=' + INTERMediatorOnPage.getTheme() + '&type=images&name=inprogress.gif');
            frontPanel.appendChild(imageProgress);
            frontPanel.appendChild(document.createElement('BR'));
            frontPanel.appendChild(document.createTextNode('INTER-Mediator working'));
        }
    },

    setReferenceToTheme: function () {
        var headNode, bodyNode, linkElement, i, styleIndex = -1;
        headNode = document.getElementsByTagName('HEAD')[0];
        bodyNode = document.getElementsByTagName('BODY')[0];
        linkElement = document.createElement('link');
        linkElement.setAttribute('href', INTERMediatorOnPage.getEntryPath()
            + '?theme=' + INTERMediatorOnPage.getTheme() + '&type=css');
        linkElement.setAttribute('rel', 'stylesheet');
        linkElement.setAttribute('type', 'text/css');
        for (i = 0; i < headNode.childNodes.length; i++) {
            if (headNode.childNodes[i] &&
                headNode.childNodes[i].nodeType == 1 &&
                headNode.childNodes[i].tagName == 'STYLE') {
                styleIndex = i;
            }
        }
        if (styleIndex > -1) {
            headNode.insertBefore(linkElement, headNode.childNodes[styleIndex]);
        } else {
            headNode.appendChild(linkElement);
        }
    }
};
