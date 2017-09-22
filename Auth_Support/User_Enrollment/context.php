<?php
/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

require_once('../../INTER-Mediator.php');   // Set the valid path to INTER-Mediator.php

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
                'read' => array('group' => array('dummy')),
                'update' => array('group' => array('dummy')),
                'delete' => array('group' => array('dummy')),
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
                array(
                    'field' => 'email',
                    'rule' => "test(value, '^.+@.+$')",
                    'message' => 'メールアドレスの形式が正しくありません。',
                ),
            ),
            'send-mail' => array(
                'create' => array(
                    'to' => 'email',
                    'bcc' => 'info@msyk.net',
                    'subject-constant' => 'ユーザ登録を受け付けました',
                    'from-constant' => 'Masayuki Nii <info@msyk.net>',
                    'body-template' => 'enrollmail.txt',
                    'body-fields' => 'email,realname,hash',
                    'f-option' => true,
                    'body-wrap' => 78,
                ),
            ),
        ),
    ),
    array(),
    array("db-class" => "PDO" /* or "FileMaker_FX" */),
    false
);
