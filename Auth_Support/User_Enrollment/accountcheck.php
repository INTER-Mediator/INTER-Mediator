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
if (isset($_GET['m']) && strlen($_GET['m']) > 0) {
    require_once('../../INTER-Mediator.php');   // Set the valid path to INTER-Mediator.php
    $contextDef = array(
        'name' => 'authuser',
        'key' => 'id',
        'query' => array(
            array('field' => 'email', 'operator' => '=', 'value' => $_GET['m']),
        ),
    );
    $dbInstance = new DB_Proxy();
    $dbInstance->initialize(
        array($contextDef), 
        array(), 
        array("db-class" => "PDO" /* or "FileMaker_FX" */), 
        false, 
        "authuser"
    );
    $dbInstance->processingRequest("read");
    $result = $dbInstance->getDatabaseResult();

    echo count($result);
    exit;
}
echo 0;
