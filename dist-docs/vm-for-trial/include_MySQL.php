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
