/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
window.onload = function () {
    var param = location.search.split("&");
    for (var i = 0; i < param.length; i++) {
        if (param[i].match(/id=/)) {
            var values = param[i].split("=");
            INTERMediator.additionalCondition = {"product": [
                    { field: "id", operator: "=", value: values[1] },
            ]};
        }
    }
    INTERMediator.construct(true);
};
