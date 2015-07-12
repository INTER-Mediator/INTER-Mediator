<?php
//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once('../../../../INTER-Mediator/INTER-Mediator.php');

IM_Entry(array(
    0 =>
        array(
            'name' => 'message',
            'table' => 'chat',
            'view' => 'chat',
            'key' => 'id',
            'query' =>
                array(
                    0 =>
                        array(
                            'field' => 'groupname',
                            'value' => '',
                            'operator' => 'IS NULL',
                        ),
                ),
            'sort' =>
                array(
                    0 =>
                        array(
                            'field' => 'postdt',
                            'direction' => 'desc',
                        ),
                ),
            'post-reconstruct' => true,
            'post-dismiss-message' => '投稿しました',
            'validation' =>
                array(
                    0 =>
                        array(
                            'field' => 'user',
                            'rule' => 'value != \'\'',
                            'message' => '入力してください。',
                        ),
                    1 =>
                        array(
                            'field' => 'message',
                            'rule' => 'value != \'\'',
                            'message' => '入力してください。',
                        ),
                ),
        ),
    1 =>
        array(
            'name' => 'comment',
            'table' => 'chat',
            'view' => 'chat',
            'key' => 'id',
            'relation' =>
                array(
                    0 =>
                        array(
                            'foreign-key' => 'groupname',
                            'join-field' => 'id',
                            'operator' => '=',
                        ),
                ),
            'post-reconstruct' => true,
            'post-dismiss-message' => '投稿しました',
            'sort' =>
                array(
                    0 =>
                        array(
                            'field' => 'postdt',
                            'direction' => 'desc',
                        ),
                ),
            'validation' =>
                array(
                    0 =>
                        array(
                            'field' => 'user',
                            'rule' => 'value != \'\'',
                            'message' => '入力してください。',
                        ),
                    1 =>
                        array(
                            'field' => 'message',
                            'rule' => 'value != \'\'',
                            'message' => '入力してください。',
                        ),
                ),
        ),
),
    array(
        'formatter' => array(
            array('field' => 'chat@message', 'converter-class' => 'HTMLString'),
        ),
    ),
    array(
        'db-class' => 'PDO',
    ),
    false);
