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

require_once('../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'records' => 1,
            'name' => 'person',
            'key' => 'id',
            //    'repeat-control' => 'insert delete',
            //    'paging' => true,
        ),
    ),
    null,
    array('db-class' => 'FileMaker_FX'),
//    array('db-class' => 'PDO'),
    false
);
