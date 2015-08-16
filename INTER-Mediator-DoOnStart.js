/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */

/*
  This source file should be described statements to execute on the loading time of header's script tag.
 */

INTERMediator.propertyIETridentSetup();

if (INTERMediator.isIE && INTERMediator.ieVersion < 9) {
    INTERMediator.startFrom = 0;
    INTERMediator.pagedSize = 0;
    INTERMediator.pagination = false;
    INTERMediator.additionalCondition = {};
    INTERMediator.additionalSortKey = {};
    IMLibCalc.regexpForSeparator = INTERMediator.separator;
} else {
    Object.defineProperty(INTERMediator, 'startFrom', {
        get: function () {
            return INTERMediator.getLocalProperty("_im_startFrom", 0);
        },
        set: function (value) {
            INTERMediator.setLocalProperty("_im_startFrom", value);
        }
    });
    Object.defineProperty(INTERMediator, 'pagedSize', {
        get: function () {
            return INTERMediator.getLocalProperty("_im_pagedSize", 0);
        },
        set: function (value) {
            INTERMediator.setLocalProperty("_im_pagedSize", value);
        }
    });
    Object.defineProperty(INTERMediator, 'pagination', {
        get: function () {
            return INTERMediator.getLocalProperty("_im_pagination", 0);
        },
        set: function (value) {
            INTERMediator.setLocalProperty("_im_pagination", value);
        }
    });
    Object.defineProperty(INTERMediator, 'additionalCondition', {
        get: function () {
            return INTERMediator.getLocalProperty("_im_additionalCondition", {});
        },
        set: function (value) {
            INTERMediator.setLocalProperty("_im_additionalCondition", value);
        }
    });
    Object.defineProperty(INTERMediator, 'additionalSortKey', {
        get: function () {
            return INTERMediator.getLocalProperty("_im_additionalSortKey", {});
        },
        set: function (value) {
            INTERMediator.setLocalProperty("_im_additionalSortKey", value);
        }
    });
    Object.defineProperty(IMLibCalc, 'regexpForSeparator', {
        get: function () {
            if (INTERMediator) {
                return new RegExp(INTERMediator.separator);
            }
            return new RegExp("@");
        }
    });
}

if (!INTERMediator.additionalCondition) {
    INTERMediator.additionalCondition = {};
}

if (!INTERMediator.additionalSortKey) {
    INTERMediator.additionalSortKey = {};
}


INTERMediatorLib.addEvent(window, "beforeunload", function (e) {
    var confirmationMessage = "";

//    (e || window.event).returnValue = confirmationMessage;     //Gecko + IE
//    return confirmationMessage;                                //Webkit, Safari, Chrome etc.

});

INTERMediatorLib.addEvent(window, "unload", function (e) {
    INTERMediator_DBAdapter.unregister();
});

// ****** This file should terminate on the new line. INTER-Mediator adds some codes before here. ****
