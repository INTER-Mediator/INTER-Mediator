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
            'records' => 10000,
            'name' => 'testtable',
            'sort' => array(
                array('field' => 'dt1', 'direction' => 'desc'),
            ),
            'repeat-control'=>'insert delete',
            'default-values'=>array(
                array('field'=>'dt1', 'value'=>date('Y-m-d H:i:s')),
            ),
            'file-upload' => array(
                array('field'=>'vc1', 'container' => true)
            ),
        ),
        array(
            'name' => 'fileupload',
            'repeat-control'=>'delete',
        ),
    ),
    array(
        'formatter' => array(
            array('field' => 'testtable@dt1', 'converter-class' => 'FMDateTime'),
        ),
        //'authentication' => array(
        //    'user' => array('database_native'),
        //    'storing' => 'cookie-domainwide', // 'cookie'(default), 'cookie-domainwide', 'none'
        //),
    ),
    array('db-class' => 'FileMaker_FX'),
    false
);
