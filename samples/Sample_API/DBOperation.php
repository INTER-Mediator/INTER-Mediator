<?php

require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

use INTERMediator\DB\Proxy_ExtSupport;
use \INTERMediator\DB\Logger;

class DBOperation
{
    use Proxy_ExtSupport;

    private $contextDef = [
        [
            'name' => 'product',
            'key' => 'id',
//            'query' => array(array('field' => 'name', 'value' => '%', 'operator' => 'LIKE')),
//            'sort' => array(array('field' => 'name', 'direction' => 'ASC'),),
        ],
    ];

    public function readData(int $pid): array
    {
        $this->dbInit($this->contextDef, null, null, 2);
        $condition = ["id" => $pid];
        $pInfo = $this->dbRead("product", $condition);
        return ["data" => $pInfo, "log" => $this->getAllLogs()];
    }

    private function getAllLogs(): array
    {
        $logInfo = [];
        $logger = Logger::getInstance();
        $logInfo['error'] = $logger->getErrorMessages();
        $logInfo['warning'] = $logger->getWarningMessages();
        $logInfo['debug'] = $logger->getDebugMessages();
        return $logInfo;
    }

    public function createData(string $prodName, int $prodPrice)
    {
        $this->dbInit($this->contextDef, null, null, 2);
        $data = ["name" => $prodName, "unitprice" => $prodPrice];
        $pInfo = $this->dbCreate("product", $data);
        return ["data" => $pInfo, "log" => $this->getAllLogs()];
    }

    public function updateData(int $pid, string $prodName, int $prodPrice): array
    {
        $this->dbInit($this->contextDef, null, null, 2);
        $condition = ["id" => $pid];
        $data = ["name" => $prodName, "unitprice" => $prodPrice];
        $pInfo = $this->dbUpdate("product", $condition, $data);
        return ["data" => $pInfo, "log" => $this->getAllLogs()];
    }
}