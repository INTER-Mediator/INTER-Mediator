/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */
//window.onload = function () {
    INTERMediator.navigationLabel = [null, null, null, null, null, null, null, null, false];
//    IMLibLocalContext.setValue("placeCondition", "");
//    IMLibLocalContext.setValue("zipCondition", "");
//    INTERMediator.construct(true);
//};

INTERMediatorOnPage.doAfterConstruct = function () {
    if (document.getElementById("searchbutton")) {
        document.getElementById("searchbutton").onclick = function () {
            doSearch();
        };
    }

    if (document.getElementById("ascendingsortbypostalcode")) {
        document.getElementById("ascendingsortbypostalcode").onclick = function () {
            doSortFieldChange("f3", "ASC");
        };
    }

    if (document.getElementById("descendingsortbypostalcode")) {
        document.getElementById("descendingsortbypostalcode").onclick = function () {
            doSortFieldChange("f3", "DESC");
        };
    }

    if (document.getElementById("ascendingsortbyname")) {
        document.getElementById("ascendingsortbyname").onclick = function () {
            doSortFieldChange("f9", "ASC");
        };
    }

    if (document.getElementById("descendingsortbyname")) {
        document.getElementById("descendingsortbyname").onclick = function () {
            doSortFieldChange("f9", "DESC");
        };
    }
};


function doSearch() {
    var criteria;
    var c1 = IMLibLocalContext.getValue("placeCondition");
    if (c1 && c1.length > 0) {
        if (window.document.title.indexOf("FileMaker") == -1) {
            criteria = {field: 'f9', operator: 'LIKE', value: '%' + c1 + '%'};
        } else {
            criteria = {field: 'f9', operator: 'cn', value: c1};
        }
    }
    var c2 = IMLibLocalContext.getValue("zipCondition");
    if (c2 && c2.length > 0) {
        if (window.document.title.indexOf("FileMaker") == -1) {
            criteria = {field: 'f3', operator: 'LIKE', value: c2 + '%'};
        } else {
            criteria = {field: 'f3', operator: 'bw', value: c2};
        }
    }
    INTERMediator.additionalCondition.postalcode = criteria;
//    IMLibLocalContext.archive();    // This isn't required other than IE8.
    INTERMediator.startFrom = 0;
    INTERMediator.construct(true);
}

function doSortFieldChange(key, direction) {
    INTERMediator.additionalSortKey = {"postalcode": {field: key, direction: direction}};
    doSearch();
}
