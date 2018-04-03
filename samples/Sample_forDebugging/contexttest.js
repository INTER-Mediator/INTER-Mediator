/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */
//window.onload = function () {
//    INTERMediator.construct(true);
    INTERMediatorOnPage.doAfterConstruct = function() {
        IMLibMouseEventDispatch.setTargetExecute("personlist@id@data-x", function(value, target){
            INTERMediator.additionalCondition = {persondetail:{field:'id', operator:'=', value: value}};
            INTERMediator.construct(IMLibContextPool.contextFromName("persondetail"));
        });
    };
//};
