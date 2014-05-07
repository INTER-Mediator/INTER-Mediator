/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
window.onload = function () {
    IMLibKeyEventDispatch.setExecute('condition', 13, function () {
        IMLibLocalContext.update('condition');
        doSearch();
    });
    IMLibKeyEventDispatch.setExecute("number", 13, function () {
        IMLibLocalContext.update("_im_pagedSize");
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
    INTERMediator.construct(true);
};

function doSearch() {
    INTERMediator.additionalCondition = {};
    var c1 = IMLibLocalContext.getValue("condition");
    if (c1 && c1.length > 0) {
        INTERMediator.additionalCondition = {"postalcode": [
            {field: 'f3', operator: 'LIKE', value: c1 + '%'},
            {field: 'f7', operator: 'LIKE', value: '%' + c1 + '%'},
            {field: 'f8', operator: 'LIKE', value: '%' + c1 + '%'},
            {field: 'f9', operator: 'LIKE', value: '%' + c1 + '%'},
            {field: '__operation__', operator: 'ex'}
        ]};
    }
    INTERMediator.startFrom = 0;
    IMLibLocalContext.archive();    // This isn't required other than IE8.
    INTERMediator.construct(true);
}
