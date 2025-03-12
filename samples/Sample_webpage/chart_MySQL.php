<?php
/**
 * INTER-Mediator
 * Copyright (c] INTER-Mediator Directive Committee (http://inter-mediator.org]
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c] INTER-Mediator Directive Committee (http://inter-mediator.org]
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

require_once('../../INTER-Mediator.php');

IM_Entry(
    [
        [
            'records' => 10000,
            'name' => 'customer',
            'key' => 'id',
            'data' => [
                ['id' =>2, 'value' => "B%"],
                ['id' =>3, 'value' => "C%"],
                ['id' =>4, 'value' => "D%"],
                ['id' =>5, 'value' => "S%"],
            ],
            'sort' => [['field' => 'name', 'direction' => 'asc'],],
        ],
        [
            'records' => 10000,
            'name' => 'saleslog',
            'relation' => [['foreign-key' => 'customer', 'operator' => 'like', 'join-field' => "value"],],
            'sort' => [['field' => 'item', 'direction' => 'asc'],],
        ],
    ],
    [],
    ['db-class' => 'PDO'],
    2
);
