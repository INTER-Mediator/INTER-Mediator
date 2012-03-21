/*
 * INTER-Mediator Ver.0.7.6 Released 2011-09-18
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
/**
 * Created by JetBrains PhpStorm.
 * User: nii
 * Date: 11/05/23
 * Time: 21:56
 * To change this template use File | Settings | File Templates.
 */

function pageLoad(id)  {
    if ( INTERMediatorOnPage.INTERMediatorCheckBrowser(document.getElementById('nonsupportmessage')) )  {
        INTERMediator.startFrom = 0;
        INTERMediator.additionalCondition["EachScript"] = {field:'Article_id',operator:'=',value:id};
        INTERMediator.additionalCondition["PageInfo"] = {field:'id',operator:'=',value:id};
        INTERMediator.construct( true );
    }
    fitToPage( document.getElementsByClassName('openingpicture')[0] );
}

function fitToPage(object)   {
    if( object != null) {
        var picw = 990;
        var otherw = 200 + 40 *2 + 30;
        var bodyw = document.getElementsByTagName('BODY')[0].clientWidth;
        if ( picw > bodyw-otherw)   {
            object.width = bodyw-otherw;
        }
    }
}

window.addEventListener('resize', function(){
    fitToPage( document.getElementsByClassName('openingpicture')[0] );
});


