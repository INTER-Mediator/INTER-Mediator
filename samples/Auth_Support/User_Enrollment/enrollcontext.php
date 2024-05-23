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

require_once('../../../INTER-Mediator.php');   // Set the valid path to INTER-Mediator.php

IM_Entry(
    [
        [
            'name' => 'user-enroll',
            'view' => 'authuser',
            'table' => 'authuser',
            'key' => 'id',
            'extending-class' => 'EnrollStart',
//            'post-dismiss-message' => '確認メールを送信しました。そちらをご確認ください。',
            'authentication' => [
                'read' => ['group' => ['dummy']],
                'update' => ['group' => ['dummy']],
                'delete' => ['group' => ['dummy']],
            ],
            'validation' => [
                ['field' => 'realname',
                    'rule' => 'length(value) > 0',
                    'message' => '名前を入力してください。',],
                ['field' => 'email',
                    'rule' => 'length(value) > 0',
                    'message' => 'メールアドレスを入力してください。',],
                ['field' => 'email',
                    'rule' => "test(value, '^.+@.+$')",
                    'message' => 'メールアドレスの形式が正しくありません。',],
            ],
            'send-mail' => [
                'create' => [
                    'template-context' => 'mailtemplate@id=991',
                ],
            ],
        ],
        [
            "name" => "mailtemplate",
            "key" => "id",
            "records" => 1,
        ],
    ],
    [],
    ["db-class" => "PDO" /* or "FileMaker_FX" */],
    false
);
