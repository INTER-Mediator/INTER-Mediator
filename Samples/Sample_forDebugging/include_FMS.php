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
    array(
        array(
            'name' => 'postalcode',
            'records' => 20,
            'paging' => true,
            'query' => array(array('field' => 'f3', 'operator' => 'bw', 'value' => "15"),),
            'sort' => array(array('field' => 'f3', 'direction' => 'ASC'),),
            'repeat-control' => 'insert delete',
        ),
    ),
    array(
        'transaction' => 'none',
    ),
    array('db-class' => 'FileMaker_FX'),
    false
);
