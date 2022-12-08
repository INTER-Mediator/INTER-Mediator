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

require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(
    [
        [
            'records' => 3,
            'maxrecords' => 3,
            'name' => 'productlist',
            'view' => 'product',
            'key' => 'id',
            'sort' => [['field' => 'name', 'direction' => 'ASC'],],
            'navi-control' => 'master-hide',
            'paging' => true,
        ],
        [
            'records' => 1,
            'name' => 'productdetail',
            'view' => 'product',
            'table' => 'product',
            'key' => 'id',
            'navi-control' => 'detail-top-update',
        ],
    ],
    [],
    ['db-class' => 'PDO'],
    2
);
