<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/06/09
 * Time: 8:29
 * To change this template use File | Settings | File Templates.
 */

require_once('../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'user-enroll',
            'view' => 'authuser',
            'table' => 'authuser',
            'key' => 'id',
            'extending-class' => 'EnrollStart',
            'post-dismiss-message' => '確認メールを送信しました。そちらをご確認ください。',
            'authentication' => array(
                'load' => array( 'group' => array( 'dummy' ) ),
                'update' => array( 'group' => array( 'dummy' ) ),
                'delete' => array( 'group' => array( 'dummy' ) ),
            ),
            'validation' => array(
                array(
                    'field' => 'realname',
                    'rule' => 'length(value) > 0',
                    'message' => '名前を入力してください。',
                ),
                array(
                    'field' => 'email',
                    'rule' => 'length(value) > 0',
                    'message' => 'メールアドレスを入力してください。',
                ),
//                array(
//                    'field' => 'email',
//                    'rule' => 'match(value, /^[A-Za-z0-9]+[\w\.-]+@[\w\.-]+\.\w{2,}$/)',
//                    'message' => 'メールアドレスの形式が正しくありません。',
//                ),
            ),
        ),
    ),
    array(),
    array(
        'db-class' => 'PDO',
    ),
    false
);
