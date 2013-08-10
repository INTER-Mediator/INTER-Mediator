<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 2013/03/20
 * Time: 19:09
 * To change this template use File | Settings | File Templates.
 */

require_once( '../INTER-Mediator/INTER-Mediator.php');
IM_Entry(
    array(
        array(
            'name'=>'survey',
//            'key'=>'survey_id',
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
