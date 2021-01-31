/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */
INTERMediatorOnPage.doBeforeConstruct = function() {
    const param = location.search.split("&");
    for (let i = 0; i < param.length; i++) {
        if (param[i].match(/id=/)) {
            const values = param[i].split("=");
            INTERMediator.additionalCondition = {"product": [
                    { field: "id", operator: "=", value: values[1] },
            ]};
        }
    }
};
