/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
// Cleaning-up by http://jsbeautifier.org/ or Eclipse's Formatting


var INTERMediatorOnPage = {
    authCount: 0,
    authUser: '',
    authHashedPassword: '',
    authChallenge: '',
    requreAuthentication: false,
    authRequiredContext: null,

    isComplementAuthData: function()    {
        if (   this.authUser != null && this.authUser.length > 0
            && this.authHashedPassword != null && this.authHashedPassword.length > 0
            && this.authChallenge != null && this.authChallenge.length > 0 )  {
            return true;
        }
        return false;
    },

    authenticating: function(doAfterAuth)   {
        var bodyNode = document.getElementsByTagName('BODY')[0];
        var backBox = document.createElement('div');
        bodyNode.insertBefore( backBox, bodyNode.childNodes[0] );
        backBox.style.height = "100%";
        backBox.style.width = "100%";
    //    backBox.style.backgroundColor = "#BBBBBB";
        backBox.style.backgroundImage = "url(data:image/png;base64,"
            +"iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAAXNSR0IArs4c6QAA"
            +"ACF0RVh0U29mdHdhcmUAR3JhcGhpY0NvbnZlcnRlciAoSW50ZWwpd4f6GQAAAHRJ"
            +"REFUeJzs0bENAEAMAjHWzBC/f5sxkPIurkcmSV65KQcAAAAAAAAAAAAAAAAAAAAA"
            +"AAAAAAAAAAAAAAAAAAAAAL4AaA9oHwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA"
        +"AAAAAAAAAAAAOA6wAAAA//8DAF3pMFsPzhYWAAAAAElFTkSuQmCC)";
        backBox.style.position = "absolute";
        backBox.style.padding = " 50px 0 0 0";
        backBox.style.top = "0";
        backBox.style.left = "0";

        var frontPanel = document.createElement('div');
        frontPanel.style.width = "240px";
        frontPanel.style.backgroundColor = "#333333";
        frontPanel.style.color = "#DDDDAA";
        frontPanel.style.margin = "50px auto 0 auto";
        frontPanel.style.padding = "20px";
        frontPanel.style.borderRadius = "10px";
        frontPanel.style.position = "relatvie";
        backBox.appendChild( frontPanel );

        var labelWidth = "100px";
        var userLabel = document.createElement('LABEL');
        frontPanel.appendChild( userLabel );
        var userSpan = document.createElement('div');
        userSpan.style.width = labelWidth;
        userSpan.style.textAlign = "right";
        userSpan.style.cssFloat = "left";
        userLabel.appendChild( userSpan );
        userSpan.appendChild( document.createTextNode( INTERMediatorLib.getInsertedStringFromErrorNumber(2002) ));
        var userBox = document.createElement('INPUT');
        userBox.type = "text";
        userBox.value = INTERMediatorOnPage.authUser;
        userBox.id = "_im_username";
        userBox.size = "12";
        userLabel.appendChild( userBox );

        var breakLine = document.createElement('BR');
        breakLine.clear = "all";
        frontPanel.appendChild( breakLine );

        var passwordLabel = document.createElement('LABEL');
        frontPanel.appendChild( passwordLabel );
        var passwordSpan = document.createElement('SPAN');
        passwordSpan.style.minWidth = labelWidth;
        passwordSpan.style.textAlign = "right";
        passwordSpan.style.cssFloat = "left";
        passwordLabel.appendChild( passwordSpan );
        passwordSpan.appendChild( document.createTextNode( INTERMediatorLib.getInsertedStringFromErrorNumber(2003) ));
        var passwordBox = document.createElement('INPUT');
        passwordBox.type = "password";
        passwordBox.id = "_im_password";
        passwordBox.size = "12";
        passwordBox.onkeydown = function(event) {
            if ( event.keyCode == 13)   {
                authButton.onclick();
            };};
        userBox.onkeydown = function(event) {
            if ( event.keyCode == 13)   {
                passwordBox.focus();
            };};
        passwordLabel.appendChild( passwordBox );

        var breakLine = document.createElement('BR');
        breakLine.clear = "all";
        frontPanel.appendChild( breakLine );

        var authButton = document.createElement('BUTTON');
        authButton.style.marginLeft = labelWidth;
        authButton.appendChild( document.createTextNode( INTERMediatorLib.getInsertedStringFromErrorNumber(2004) ));
        authButton.onclick = function() {
            INTERMediatorOnPage.authUser = document.getElementById('_im_username').value;
            INTERMediatorOnPage.authHashedPassword = SHA1(document.getElementById('_im_password').value);
            bodyNode.removeChild(backBox);
            if ( INTERMediatorOnPage.authChallenge == null || INTERMediatorOnPage.authChallenge.length < 16 )    {
                try {
                if ( ! INTERMediaotr_DBAdapter.getChallenge() )     {
                    INTERMediator.flushMessage();
                    return;
                }
                } catch (ex) {
                    // nothing to do
                }
            }
            doAfterAuth();
            INTERMediator.flushMessage();
        };
        frontPanel.appendChild( authButton );

        window.scroll(0 ,0);
        userBox.focus();
        INTERMediatorOnPage.authCount++;
    },

    authenticationError: function()   {
        var bodyNode = document.getElementsByTagName('BODY')[0];
        var backBox = document.createElement('div');
        bodyNode.insertBefore( backBox, bodyNode.childNodes[0] );
        backBox.style.height = "100%";
        backBox.style.width = "100%";
        //backBox.style.backgroundColor = "#BBBBBB";
        backBox.style.backgroundImage = "url(data:image/png;base64,"
            +"iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAAXNSR0IArs4c6QAA"
            +"ACF0RVh0U29mdHdhcmUAR3JhcGhpY0NvbnZlcnRlciAoSW50ZWwpd4f6GQAAAHlJ"
            +"REFUeJzs0UENACAQA8EzdAl2EIEg3CKjyTGP/TfTur1OuJ2sAAAAAAAAAAAAAAAA"
            +"AAAAAAAAAAAAAAAAAAAAAAAAAADAJwDRAekDAAAAAAAAAAAAAAAAAAAAAAAAAAAA"
            +"AAAAAAAAAAAAAAAAAADzAR4AAAD//wMAkUKRPI/rh/AAAAAASUVORK5CYII=)";
        backBox.style.position = "absolute";
        backBox.style.padding = " 50px 0 0 0";
        backBox.style.top = "0";
        backBox.style.left = "0";

        var frontPanel = document.createElement('div');
        frontPanel.style.width = "240px";
        frontPanel.style.backgroundColor = "#333333";
        frontPanel.style.color = "#DD6666";
        frontPanel.style.fontSize = "16pt";
        frontPanel.style.margin = "50px auto 0 auto";
        frontPanel.style.padding = "20px 4px 20px 4px";
        frontPanel.style.borderRadius = "10px";
        frontPanel.style.position = "relatvie";
        frontPanel.style.textAlign = "Center";
        backBox.appendChild( frontPanel );
        frontPanel.appendChild( document.createTextNode( INTERMediatorLib.getInsertedStringFromErrorNumber(2001) ));
    },

    INTERMediatorCheckBrowser: function(deleteNode) {
        var positiveList = INTERMediatorOnPage.browserCompatibility();
        var matchAgent = false;
        var matchOS = false;
        var versionStr;
        for (var agent in  positiveList) {
            if (navigator.userAgent.toUpperCase().indexOf(agent.toUpperCase()) > -1) {
                matchAgent = true;
                if (positiveList[agent] instanceof Object) {
                    for (var os in positiveList[agent]) {
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
        var judge = false;
        if (matchAgent && matchOS) {
            var specifiedVersion = parseInt(versionStr);
            var versionNum;
            if (navigator.appVersion.indexOf('MSIE') > -1) {
                var msieMark = navigator.appVersion.indexOf('MSIE');
                var dotPos = navigator.appVersion.indexOf('.', msieMark);
                versionNum = parseInt(navigator.appVersion.substring(msieMark + 4, dotPos));
                /*
                 As for the appVersion property of IE, refer http://msdn.microsoft.com/en-us/library/aa478988.aspx
                 */
            } else {
                var dotPos = navigator.appVersion.indexOf('.');
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
            var bodyNode = document.getElementsByTagName('BODY')[0];
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
        var repeaterNode = INTERMediatorLib.getParentRepeater(fromNode);
        return seekNode(repeaterNode, imDefinition);

        function seekNode(node, imDefinition) {
            if (node.nodeType != 1) {
                return null;
            }
            var children = node.childNodes;
            if (children == null) {
                return null;
            } else {
                for (var i = 0; i < children.length; i++) {
                    if (children[i].getAttribute != null) {
                        var thisClass = children[i].getAttribute('class');
                        var thisTitle = children[i].getAttribute('title');
                        if ((thisClass != null && thisClass.indexOf(imDefinition) > -1)
                            || (thisTitle != null && thisTitle.indexOf(imDefinition) > -1)) {
                            return children[i].getAttribute('id');
                            break;
                        } else {
                            var returnValue = seekNode(children[i], imDefinition);
                            if (returnValue != null) {
                                return returnValue;
                            }
                        }
                    }
                }
            }
            return null;
        }
    },

    getNodeIdsFromIMDefinition:function (imDefinition, fromNode) {
        var enclosureNode = INTERMediatorLib.getParentEnclosure(fromNode);
        if (enclosureNode != null) {
            var nodeIds = [];
            seekNode(enclosureNode, imDefinition);
        }
        return nodeIds;

        function seekNode(node, imDefinition) {
            if (node.nodeType != 1) {
                return null;
            }
            var children = node.childNodes;
            if (children == null) {
                return null;
            } else {
                for (var i = 0; i < children.length; i++) {
                    if (children[i].getAttribute != null) {
                        var thisClass = children[i].getAttribute('class');
                        var thisTitle = children[i].getAttribute('title');
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
    }
};
