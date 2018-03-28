<?php
/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'placelist',
            'table' => 'not_available',
            'view' => 'postalcode',
            'records' => 1000,
            'maxrecords' => 1000,
            'key' => 'id',
            'navi-control' => 'master-hide-touch',
        ),
        array(
            'name' => 'placedetail',
            'table' => 'not_available',
            'view' => 'postalcode',
            'records' => 1,
            'maxrecords' => 1,
            'key' => 'id',
            'navi-control' => 'detail',
        ),
    ),
    array(
        'credit-including' => 'footer',
    ),
    array(
        'db-class' => 'FileMaker_DataAPI',
        'server' => 'localserver',
    ),
    //todo ## Set the debug level to false, 1 or 2.
    false
);
