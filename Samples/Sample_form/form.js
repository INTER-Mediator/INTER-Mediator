/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
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