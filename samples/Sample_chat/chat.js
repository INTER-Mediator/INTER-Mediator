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
//     INTERMediator.construct(true);
// };

var doAfter = false;
// The flag to prevent executing the INTERMediatorOnPage.doAfterConstruct method more than once.

INTERMediatorOnPage.doAfterConstruct = function () {
    if (!doAfter) {
        IMLibMouseEventDispatch.setExecute('logoutbutton', function () {
            INTERMediatorOnPage.logout();
            INTERMediator.construct(true);
        });
        IMLibMouseEventDispatch.setExecute('postbutton', function () {
            IMLibQueue.setTask(function (completeTask) {
                INTERMediator_DBAdapter.db_createRecord_async({
                        name: 'chat',
                        dataset: [
                            {
                                field: 'message',
                                value: document.getElementById('message').value
                            }
                        ]
                    },
                    function () {
                        INTERMediator.constructMain(IMLibContextPool.contextFromName('chat'));
                        document.getElementById('message').value = '';
                        INTERMediatorLog.flushMessage();
                        completeTask();
                    },
                    function () {
                        alert('Error');
                        INTERMediatorLog.flushMessage();
                        completeTask();
                    });
            });
        });
        doAfter = true;
    }
}

