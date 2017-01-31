/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

// window.onload = function () {
//     var nodeUnsupport = document.getElementById('nonsupportmessage');
//     if (INTERMediatorOnPage.INTERMediatorCheckBrowser(nodeUnsupport)) {
//         INTERMediator.construct(true);
//     }
// };

function test() {
    INTERMediator_DBAdapter.db_createRecord({
        name: "history_to",
        dataset: [
            {field: "startdate", value: document.getElementById('startdate').value}
        ]
    });
    INTERMediator.flushMessage();
}