/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 * 
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */
window.onload = function () {
    INTERMediator.construct(true);
    INTERMediatorOnPage.doAfterConstruct = function() {
        IMLibMouseEventDispatch.setTargetExecute("personlist@id@data-x", function(value, target){
            INTERMediator.additionalCondition = {persondetail:{field:'id', operator:'=', value: value}};
            INTERMediator.construct(IMLibContextPool.contextFromName("persondetail"));
        });
    };
};
