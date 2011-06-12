/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
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
    INTERMediator.additionalCondition["Contents"] = {field:'Article_id',operator:'eq',value:id};
    INTERMediator.startFrom = 0;
    INTERMediator.construct( true );
    document.getElementById('nonsupportmessage').style.display = 'none';
}
