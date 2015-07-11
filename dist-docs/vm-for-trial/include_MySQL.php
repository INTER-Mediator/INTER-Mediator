<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */
 require_once(dirname(dirname(dirname(__FILE__))) . '/INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'information',
            'key' => 'id',
            'records' => 1,
            'maxrecords' => 1,
        ),
    ),
    array(
        'formatter' => array(
            array(
                'field' => 'information@lastupdated',
                'converter-class' => 'MySQLDateTime',
                'parameter'=>'%Y年%-m月%-d日',
            )
        ),
    ),
    array(
        'db-class' => 'PDO',
    ),
    FALSE
);
