<?php

use PHPUnit\Framework\TestCase;
use INTERMediator\IMUtil;
use INTERMediator\DB\Proxy;

trait DB_PDO_Test_LocalContextConditions
{

//    public function testAddingLCCondtions1()
//    {
//        $query = [
//            ['field' => 'num0', 'operator' => '=', 'value' => 100],
//            ['field' => 'num0', 'operator' => '<', 'value' => 300],
//            ['field' => '__operation__'],
//            ['field' => 'num1', 'operator' => '=', 'value' => 100],
//            ['field' => 'num1', 'operator' => '<', 'value' => 300],
//        ];
//        $condtionExpected = '("num1" = 100 AND "num1" < 300)';
//        if ($this->isMySQL()) {
//            $condtionExpected = str_replace('"', '`', $condtionExpected);
//        }
//        $this->dbProxySetupForCondition($query);
//        $this->db_proxy->dbClass->setupHandlers();
//        $clause = $this->db_proxy->dbClass->getWhereClauseForTest('read');
//        $this->assertEquals($condtionExpected, $clause, "Condition must be followed settings.");
//    }


}
