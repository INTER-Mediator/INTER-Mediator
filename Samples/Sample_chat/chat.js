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

    document.getElementById("postbutton").onclick = function () {
        INTERMediator_DBAdapter.db_createRecordWithAuth({
                    name:"chat",
                    dataset:[{
                        field: "message",
                        value: document.getElementById("message").value}]},
                function () {
                    INTERMediator.construct(0);
                    document.getElementById("message").value = "";
                    INTERMediator.flushMessage();
                });
    }
    
    document.getElementById("logoutbutton").onclick = function () {
        INTERMediatorOnPage.logout();
        INTERMediator.construct(true);
    }
}
