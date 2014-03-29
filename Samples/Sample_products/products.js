/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
window.onload = function () {
    INTERMediator.construct();
    showList();
}

function showDetail(id) {
    INTERMediator.additionalCondition['productlist']
            = {field:'id',operator:'<',value:0};
    INTERMediator.additionalCondition['productdetail']
            = {field:'id',operator:'=',value:id};
    INTERMediator.construct();
//            document.getElementById("listarea").style.display = "none";
//            document.getElementById("detailarea").style.display = "block";
}

function showList() {
    if ( INTERMediator.additionalCondition['productlist'] ) {
    INTERMediator.additionalCondition['productlist'] = null;
    }
    INTERMediator.additionalCondition['productdetail']
            = {field:'id',operator:'<',value:0};
    INTERMediator.construct();
//            document.getElementById("listarea").style.display = "block";
//            document.getElementById("detailarea").style.display = "none";
}
