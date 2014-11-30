<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   by Masayuki Nii  msyk@msyk.net Copyright (c) 2013 Masayuki Nii, All rights reserved.
*
*   This project started at the end of 2009.
*   INTER-Mediator is supplied under MIT License.
*/
require_once('../INTER-Mediator.php');

$defContexts = array(
    array(
        'name' => 'pagefile',
        'records' => 1,
        'key' => 'id',
//            'post-enclosure' => 'pageContentGenerated',
    ),
);
/*
 * Don't remove comment slashes below on any 'release.'
 */
//IM_Entry($defContexts, null, array('db-class' => 'PageEditor'), false);

?>