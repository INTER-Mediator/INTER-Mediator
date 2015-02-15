/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 * 
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */
window.onload = function () {
    var nodeUnsupport = document.getElementById('nonsupportmessage');
    if (INTERMediatorOnPage.INTERMediatorCheckBrowser(nodeUnsupport)) {
        INTERMediator.construct(true);
    }
}

function test() {
    INTERMediator_DBAdapter.db_createRecord({
        name: "history_to",
        dataset: [
            {field: "startdate", value: document.getElementById('startdate').value}
        ]
    });
    INTERMediator.flushMessage();
}