/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */
window.onload = function () {
    INTERMediator.construct(true);
    showList();
};

function showList() {
    if (INTERMediator.additionalCondition.productlist) {
        INTERMediator.additionalCondition.productlist = null;
    }
    if (window.document.title.indexOf("FileMaker") == -1) {
        INTERMediator.additionalCondition = {"productdetail": [
                { field: "id", operator: "<", value: 0 },
        ]};
    } else {
        INTERMediator.additionalCondition = {"productdetail": [
                { field: "id", operator: "lt", value: 0 },
        ]};
    }
    INTERMediator.construct(true);
    
    INTERMediatorOnPage.doAfterConstruct = function () {
        document.getElementById("listarea").className = "shown";
        document.getElementById("detailarea").className = "hidden";
    };
}

function showDetail(id) {
    if (window.document.title.indexOf("FileMaker") == -1) {
        INTERMediator.additionalCondition = {"productlist": [
                { field: "id", operator: "<", value: 0 },
        ]};
        INTERMediator.additionalCondition = {"productdetail": [
                { field: "id", operator: "=", value: id },
        ]};
    } else {
        INTERMediator.additionalCondition = {"productlist": [
                { field: "id", operator: "lt", value: 0 },
        ]};
        INTERMediator.additionalCondition = {"productdetail": [
                { field: "id", operator: "eq", value: id },
        ]};
    }
    INTERMediator.construct(true);
    INTERMediatorOnPage.doAfterConstruct = function () {
        IMLibMouseEventDispatch.setExecute('showlist', function () {
            showList();
        });
        document.getElementById("listarea").className = "hidden";
        document.getElementById("detailarea").className = "shown";
    };
}

INTERMediatorOnPage.move = function(repeaters) {
    var buttonlist = repeaters[0].getElementsByTagName("button");
    var id = buttonlist[0].id.replace("showdetail", "");
    IMLibMouseEventDispatch.setExecute("showdetail" + id, function () {
        showDetail(id);
    });
};
