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
            'view' => 'im_sample.postalcode',
            'records' => 10,
            'maxrecords' => 1000,
            'paging' => true,
            //	'sort'	    => array( array( 'field'=>'f3', 'direction'=>'ASC' ),),
        ),
    ),
    null,
    array('db-class' => 'PDO','dsn' => 'pgsql:host=localhost;port=5432;dbname=test_db'),
    false
);
