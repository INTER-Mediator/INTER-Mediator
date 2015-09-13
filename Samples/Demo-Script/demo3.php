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
            'name'=>'survey',
            'key'=>'survey_id',
//            'post-reconstruct' => true,
//            'post-dismiss-message' => '送信しました',
//            'post-move-url' => 'http://inter-mediator.org/',
        ),
    ),
    array(),
    array(
        'db-class'=>'FileMaker_FX',
        'server' => 'msyk.dyndns.org',
        'port' =>'80',
        'user' => 'web',
        'password' => 'password',
        'datatype' => 'FMPro7',
        'database' => 'TestDB',
        'protocol' => 'HTTP',
    ),
    false
);
