<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/06/09
 * Time: 8:29
 * To change this template use File | Settings | File Templates.
 */

require_once('../../../INTER-Mediator.php');   // Set the valid path to INTER-Mediator.php

IM_Entry(
    [
        [
            "name" => "authuser_request",
            "view" => "authuser",
            "table" => "dummy",
            "key" => "id",
            "records" => 1,
            'extending-class' => 'ResetStart',
            'send-mail' => ['read' => ['template-context' => 'mailtemplate@id=993',],],
        ],
        [
            "name" => "authuser_finish",
            "view" => "authuser",
            "table" => "dummy",
            "key" => "id",
            "records" => 1,
            'extending-class' => 'ResetFinish',
            'send-mail' => ['read' => ['template-context' => 'mailtemplate@id=994',],],
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
