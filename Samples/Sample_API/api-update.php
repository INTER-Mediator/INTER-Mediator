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

require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');
spl_autoload_register('loadClass');

$pid = mb_eregi_replace("/[^0-9]/", "", $_GET["id"]);
if ($pid < 1) {
    echo json_encode(array("ERROR" => "Invalid Product Number."));
    exit();
}
$prodPrice = mb_eregi_replace("/[^0-9]/", "", $_GET["p"]);

$contextDef = array(
    array(
        'name' => 'product',
        'key' => 'id',
    ),
);
$dbInstance = new DB_Proxy();
$dbInstance->ignoringPost();
$dbInstance->initialize($contextDef, array(), array("db-class" => "PDO"), 2, "product");
$dbInstance->dbSettings->addExtraCriteria('id', '=', $pid);
$dbInstance->dbSettings->addExtraCriteria("unitprice", $prodPrice);
$dbInstance->processingRequest("update");
$pInfo = $dbInstance->getDatabaseResult();
$logInfo = $dbInstance->logger->getMessagesForJS();
echo json_encode(array("data"=>$pInfo,"log"=>$logInfo));

