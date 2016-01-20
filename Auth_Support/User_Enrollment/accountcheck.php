<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 2013/05/29
 * Time: 15:04
 * To change this template use File | Settings | File Templates.
 */
if (isset($_GET['m']) && strlen($_GET['m']) > 0) {
    $g_serverSideCall = true;
    require_once('../../INTER-Mediator.php');
    IM_Entry(
        array(
            array(
                'name' => 'authuser',
                'key' => 'id',
                'query' => array(
                    array('field' => 'email', 'operator' => '=', 'value' => $_GET['m']),
                ),
            ),
        ),
        null,
        array(
            'db-class' => 'PDO',
        ),
        false
    );
    $g_dbInstance->dbSettings->setTargetName('authuser');
    $result = $g_dbInstance->getFromDB('authuser');
    $errors = $g_dbInstance->logger->getAllErrorMessages();
    echo count($result);
    exit;
}
echo 0;
