<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/06/09
 * Time: 8:29
 * To change this template use File | Settings | File Templates.
 */

require_once('../../INTER-Mediator.php');   // Set the valid path to INTER-Mediator.php

IM_Entry(
    array(
    ),
    array(),
    array("db-class" => "PDO" /* or "FileMaker_FX" */),
    false
);
