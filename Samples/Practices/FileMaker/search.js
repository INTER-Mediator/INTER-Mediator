/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
window.onload = function () {
    INTERMediator.construct(true);
};

INTERMediatorOnPage.doAfterConstruct = function () {
    IMLibKeyEventDispatch.setExecuteByCode('condition', 13, function () {
        doSearch();
    });
    IMLibChangeEventDispatch.setExecute("number", function () {
        doSearch();
    });
    IMLibMouseEventDispatch.setExecute('search', function () {
        doSearch();
    });
    IMLibMouseEventDispatch.setExecute('sort1a', function () {
        INTERMediator.additionalSortKey = {"postalcode": {field: 'f3', direction: 'ASC'}};
        doSearch();
    });
    IMLibMouseEventDispatch.setExecute('sort1d', function () {
        INTERMediator.additionalSortKey = {"postalcode": {field: 'f3', direction: 'DESC'}};
        doSearch();
    });
    IMLibMouseEventDispatch.setExecute('sort2a', function () {
        INTERMediator.additionalSortKey = {"postalcode": {field: 'f9', direction: 'ASC'}};
        doSearch();
    });
    IMLibMouseEventDispatch.setExecute('sort2d', function () {
        INTERMediator.additionalSortKey = {"postalcode": {field: 'f9', direction: 'DESC'}};
        doSearch();
    });
}

function doSearch() {
    IMLibLocalContext.update('condition');
    IMLibLocalContext.update("number");
    var limit = IMLibLocalContext.getValue("pagedSize");
    if (parseInt(limit) > 0) {
        INTERMediator.pagedSize = limit;
    }
    var c1 = IMLibLocalContext.getValue("condition");
    INTERMediator.clearCondition("postalcode");
    if (c1 && c1.length > 0) {
        INTERMediator.addCondition("postalcode", {field: 'f3', operator: 'bw', value: c1});
        INTERMediator.addCondition("postalcode", {field: 'f7', operator: 'cn', value: c1});
        INTERMediator.addCondition("postalcode", {field: 'f8', operator: 'cn', value: c1});
        INTERMediator.addCondition("postalcode", {field: 'f9', operator: 'cn', value: c1});
        INTERMediator.addCondition("postalcode", {field: '__operation__', operator: 'ex'});
    }
    INTERMediator.startFrom = 0;
    //IMLibLocalContext.archive();    // This isn't required other than IE8.
    INTERMediator.construct(IMLibContextPool.contextFromName("postalcode"));
}
