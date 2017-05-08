/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

INTERMediatorOnPage.doBeforeConstruct = function () {
    //INTERMediator.addCondition("postalcode", {field: "f9", operator: "LIKE", value: "%東%"});
    //INTERMediator.addCondition("postalcode", {field: "f9", operator: "LIKE", value: "%西%"});
    //INTERMediator.addCondition("postalcode", {field: "__operation__", operator: "ex"});
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
};

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
        INTERMediator.addCondition("postalcode", {field: 'f3', operator: 'LIKE', value: c1 + '%'});
        INTERMediator.addCondition("postalcode", {field: 'f7', operator: 'LIKE', value: '%' + c1 + '%'});
        INTERMediator.addCondition("postalcode", {field: 'f8', operator: 'LIKE', value: '%' + c1 + '%'});
        INTERMediator.addCondition("postalcode", {field: 'f9', operator: 'LIKE', value: '%' + c1 + '%'});
        INTERMediator.addCondition("postalcode", {field: '__operation__', operator: 'ex'});
    }
    INTERMediator.startFrom = 0;
    //IMLibLocalContext.archive();    // This isn't required other than IE8.
    INTERMediator.construct(IMLibContextPool.contextFromName("postalcode"));
}
