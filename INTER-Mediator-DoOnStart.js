/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
/*
  This source file should be described statements to execute on the loading time of header's script tag.
 */

INTERMediator.propertyIETridentSetup();

if (INTERMediator.isIE && INTERMediator.ieVersion < 9) {
    INTERMediator.startFrom = 0;
    INTERMediator.pagedSize = 0;
    INTERMediator.additionalCondition = {};
    INTERMediator.additionalSortKey = {};
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

