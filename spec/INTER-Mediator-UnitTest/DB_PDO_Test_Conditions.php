<?php

use PHPUnit\Framework\TestCase;
use INTERMediator\IMUtil;
use INTERMediator\DB\Proxy;

trait DB_PDO_Test_Conditions {

    protected $condition1expected;
    protected $condition1expected1 = '(`num1` = 100)';
    protected $condition1expected2 = '("num1" = 100)';

    public function testCondition1()
    {
        $this->dbProxySetupForCondition([['field' => 'num1', 'operator' => '=', 'value' => 100]]);
        $this->db_proxy->dbClass->setupHandlers();
        $clause = $this->db_proxy->dbClass->getWhereClauseForTest('read');
        $this->assertEquals($this->condition1expected, $clause, "Condition must be followed settings.");
    }

    protected $condition2expected;
    protected $condition2expected1 = '(`num1` = 100 AND `num1` < 300)';
    protected $condition2expected2 = '("num1" = 100 AND "num1" < 300)';

    public function testCondition2()
    {
        $query = [
            ['field' => 'num1', 'operator' => '=', 'value' => 100],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
        ];
        $this->dbProxySetupForCondition($query);
        $this->db_proxy->dbClass->setupHandlers();
        $clause = $this->db_proxy->dbClass->getWhereClauseForTest('read');
        $this->assertEquals($this->condition2expected, $clause, "Condition must be followed settings.");
    }

    protected $condition3expected;
    protected $condition3expected1 = '(`num1` = 100 AND `num1` < 300) OR (`num1` > 500)';
    protected $condition3expected2 = '("num1" = 100 AND "num1" < 300) OR ("num1" > 500)';

    public function testCondition3()
    {
        $query = [
            ['field' => 'num1', 'operator' => '=', 'value' => 100],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ['field' => '__operation__',],
            ['field' => 'num1', 'operator' => '>', 'value' => 500],
        ];
        $this->dbProxySetupForCondition($query);
        $this->db_proxy->dbClass->setupHandlers();
        $clause = $this->db_proxy->dbClass->getWhereClauseForTest('read');
        $this->assertEquals($this->condition3expected, $clause, "Condition must be followed settings.");
    }

    protected $condition4expected;
    protected $condition4expected1 = '(`num1` = 100) OR (`num1` < 300 AND `num1` > 500)';
    protected $condition4expected2 = '("num1" = 100) OR ("num1" < 300 AND "num1" > 500)';

    public function testCondition4()
    {
        $query = [
            ['field' => 'num1', 'operator' => '=', 'value' => 100],
            ['field' => '__operation__',],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ['field' => 'num1', 'operator' => '>', 'value' => 500],
        ];
        $this->dbProxySetupForCondition($query);
        $this->db_proxy->dbClass->setupHandlers();
        $clause = $this->db_proxy->dbClass->getWhereClauseForTest('read');
        $this->assertEquals($this->condition4expected, $clause, "Condition must be followed settings.");
    }

    protected $condition5expected;
    protected $condition5expected1 = '((`num1` = 100) OR (`num1` < 300 AND `num1` > 500))';
    protected $condition5expected2 = '(("num1" = 100) OR ("num1" < 300 AND "num1" > 500))';

    public function testCondition5()
    {
        $query = [
            ['field' => 'num1', 'operator' => '=', 'value' => 100],
            ['field' => '__operation__',],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ['field' => 'num1', 'operator' => '>', 'value' => 500],
        ];
        $this->dbProxySetupForCondition(null);
        foreach ($query as $item) {
            $this->db_proxy->dbSettings->addExtraCriteria($item['field'],
                $item['operator'] ?? "=", $item['value'] ?? null);
        }
        $this->db_proxy->dbClass->setupHandlers();
        $clause = $this->db_proxy->dbClass->getWhereClauseForTest('read');
        $this->assertEquals($this->condition5expected, $clause, "Condition must be followed settings.");
    }

    protected $condition6expected;
    protected $condition6expected1 = '((`num1` = 100) AND (`num1` < 300 OR `num1` > 500))';
    protected $condition6expected2 = '(("num1" = 100) AND ("num1" < 300 OR "num1" > 500))';

    public function testCondition6()
    {
        $query = [
            ['field' => 'num1', 'operator' => '=', 'value' => 100],
            ['field' => '__operation__', 'operator' => 'ex',],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ['field' => 'num1', 'operator' => '>', 'value' => 500],
        ];
        $this->dbProxySetupForCondition(null);
        foreach ($query as $item) {
            $this->db_proxy->dbSettings->addExtraCriteria($item['field'],
                $item['operator'] ?? "=", $item['value'] ?? null);
        }
        $this->db_proxy->dbClass->setupHandlers();
        $clause = $this->db_proxy->dbClass->getWhereClauseForTest('read');
        $this->assertEquals($this->condition6expected, $clause, "Condition must be followed settings.");
    }

    protected $condition7expected;
    protected $condition7expected1 = '((`num1` = 100 AND `num1` < 300) OR (`num1` < 300 AND `num1` > 500) OR (`num1` < 300 AND `num1` > 500))';
    protected $condition7expected2 = '(("num1" = 100 AND "num1" < 300) OR ("num1" < 300 AND "num1" > 500) OR ("num1" < 300 AND "num1" > 500))';

    public function testCondition7()
    {
        $query = [
            ['field' => 'num1', 'operator' => '=', 'value' => 100],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ['field' => '__operation__',],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ['field' => 'num1', 'operator' => '>', 'value' => 500],
            ['field' => '__operation__',],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ['field' => 'num1', 'operator' => '>', 'value' => 500],
        ];
        $this->dbProxySetupForCondition(null);
        foreach ($query as $item) {
            $this->db_proxy->dbSettings->addExtraCriteria($item['field'],
                $item['operator'] ?? "=", $item['value'] ?? null);
        }
        $this->db_proxy->dbClass->setupHandlers();
        $clause = $this->db_proxy->dbClass->getWhereClauseForTest('read');
        $this->assertEquals($this->condition7expected, $clause, "Condition must be followed settings.");
    }

    protected $condition8expected;
    protected $condition8expected1 = '((`num1` = 100 OR `num1` < 300) AND (`num1` < 300 OR `num1` > 500) AND (`num1` < 300 OR `num1` > 500))';
    protected $condition8expected2 = '(("num1" = 100 OR "num1" < 300) AND ("num1" < 300 OR "num1" > 500) AND ("num1" < 300 OR "num1" > 500))';

    public function testCondition8()
    {
        $query = [
            ['field' => 'num1', 'operator' => '=', 'value' => 100],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ['field' => '__operation__', 'operator' => 'ex',],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ['field' => 'num1', 'operator' => '>', 'value' => 500],
            ['field' => '__operation__',],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ['field' => 'num1', 'operator' => '>', 'value' => 500],
        ];
        $this->dbProxySetupForCondition(null);
        foreach ($query as $item) {
            $this->db_proxy->dbSettings->addExtraCriteria($item['field'],
                $item['operator'] ?? "=", $item['value'] ?? null);
        }
        $this->db_proxy->dbClass->setupHandlers();
        $clause = $this->db_proxy->dbClass->getWhereClauseForTest('read');
        $this->assertEquals($this->condition8expected, $clause, "Condition must be followed settings.");
    }

    protected $condition9expected;
    protected $condition9expected1 = '((`num1` = 100 OR `num1` < 300) AND (`num1` < 300 OR `num1` > 500) AND (`num1` < 300 OR `num1` > 500))';
    protected $condition9expected2 = '(("num1" = 100 OR "num1" < 300) AND ("num1" < 300 OR "num1" > 500) AND ("num1" < 300 OR "num1" > 500))';

    public function testCondition9()
    {
        $query = [
            ['field' => 'num1', 'operator' => '=', 'value' => 100],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ['field' => '__operation__',],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ['field' => 'num1', 'operator' => '>', 'value' => 500],
            ['field' => '__operation__', 'operator' => 'ex',],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ['field' => 'num1', 'operator' => '>', 'value' => 500],
        ];
        $this->dbProxySetupForCondition(null);
        foreach ($query as $item) {
            $this->db_proxy->dbSettings->addExtraCriteria($item['field'],
                $item['operator'] ?? "=", $item['value'] ?? null);
        }
        $this->db_proxy->dbClass->setupHandlers();
        $clause = $this->db_proxy->dbClass->getWhereClauseForTest('read');
        $this->assertEquals($this->condition9expected, $clause, "Condition must be followed settings.");
    }

    protected $condition10expected;
    protected $condition10expected1 = '((`num1` = 100 OR `num1` < 300) AND (`num1` < 300 OR `num1` > 500) AND (`num1` < 300 OR `num1` > 500))';
    protected $condition10expected2 = '(("num1" = 100 OR "num1" < 300) AND ("num1" < 300 OR "num1" > 500) AND ("num1" < 300 OR "num1" > 500))';

    public function testCondition10()
    {
        $query = [
            ['field' => 'num1', 'operator' => '=', 'value' => 100],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ['field' => '__operation__', 'operator' => 'ex',],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ['field' => 'num1', 'operator' => '>', 'value' => 500],
            ['field' => '__operation__', 'operator' => 'ex',],
            ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ['field' => 'num1', 'operator' => '>', 'value' => 500],
        ];
        $this->dbProxySetupForCondition(null);
        foreach ($query as $item) {
            $this->db_proxy->dbSettings->addExtraCriteria($item['field'],
                $item['operator'] ?? "=", $item['value'] ?? null);
        }
        $this->db_proxy->dbClass->setupHandlers();
        $clause = $this->db_proxy->dbClass->getWhereClauseForTest('read');
        $this->assertEquals($this->condition10expected, $clause, "Condition must be followed settings.");
    }
}
