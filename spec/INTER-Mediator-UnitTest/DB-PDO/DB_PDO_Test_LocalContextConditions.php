<?php

trait DB_PDO_Test_LocalContextConditions
{
    private function checkConditions($query, $conditions, $conditionExpected)
    {
        if ($this->isMySQL()) {
            $conditionExpected = str_replace('"', '`', $conditionExpected);
        }
        $this->dbProxySetupForCondition($query);
        if (is_array($conditions)) {
            foreach ($conditions as $item) {
                $this->db_proxy->dbSettings->addExtraCriteria($item['field'],
                    $item['operator'] ?? "=", $item['value'] ?? null);
            }
        }
        $this->db_proxy->dbClass->setupHandlers();
        try {
            $clause = $this->db_proxy->dbClass->getWhereClauseForTest('read');
            $this->assertEquals($conditionExpected, $clause, "Condition must be followed settings.");
        } catch (Exception $ex) {
            var_dump($conditionExpected);
            var_dump($query);
            var_dump($clause); //$clause
            $this->assertTrue(null, "Exception in getWhereClauseForTest().");
        }
    }

    public function testAddingNoLCCondtions1()
    {
        $this->checkConditions(null,
            [
                ['field' => 'num0', 'operator' => '=', 'value' => 100],
                ['field' => 'num0', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__'],
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ],
            '(("num0" = \'100\' AND "num0" < \'300\') OR ("num1" = 100 AND "num1" < 300))');
    }

    public function testAddingLCCondtions1()
    {
        $this->checkConditions(null,
            [
                ['field' => 'num0', 'operator' => '=', 'value' => 100],
                ['field' => 'num0', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__'],
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'block'],
                ['field' => 'f1,f2', 'operator' => '=', 'value' => 'valueA'],
                ['field' => 'f1,f2', 'operator' => '<', 'value' => 'valueB'],
                ['field' => 'f3', 'operator' => '=', 'value' => 'valueC'],
            ],
            '((("num0" = \'100\' AND "num0" < \'300\') OR ("num1" = 100 AND "num1" < 300))'
            . ' AND (("f1" = \'valueA\' OR "f2" = \'valueA\') OR ("f1" < \'valueB\' OR "f2" < \'valueB\') OR ("f3" = \'valueC\')))');
    }

    public function testAddingLCCondtions2()
    {
        $this->checkConditions(null,
            [
                ['field' => 'num0', 'operator' => '=', 'value' => 100],
                ['field' => 'num0', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__'],
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'block'],
                ['field' => 'f1,f2', 'operator' => '=', 'value' => 'valueA'],
            ],
            '((("num0" = \'100\' AND "num0" < \'300\') OR ("num1" = 100 AND "num1" < 300))'
            . ' AND (("f1" = \'valueA\' OR "f2" = \'valueA\')))');
    }

    public function testAddingLCCondtions3()
    {
        $this->checkConditions(null,
            [
                ['field' => 'num0', 'operator' => '=', 'value' => 100],
                ['field' => 'num0', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__'],
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'block'],
                ['field' => 'f3', 'operator' => '=', 'value' => 'valueC'],
            ],
            '((("num0" = \'100\' AND "num0" < \'300\') OR ("num1" = 100 AND "num1" < 300))'
            . ' AND (("f3" = \'valueC\')))');
    }

    public function testAddingLCCondtions4()
    {
        $this->checkConditions(null,
            [
                ['field' => 'num0', 'operator' => '=', 'value' => 100],
                ['field' => 'num0', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__'],
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'block/true'],
                ['field' => 'f1,f2', 'operator' => '=', 'value' => 'valueA'],
                ['field' => 'f1,f2', 'operator' => '<', 'value' => 'valueB'],
                ['field' => 'f3', 'operator' => '=', 'value' => 'valueC'],
            ],
            '((("num0" = \'100\' AND "num0" < \'300\') OR ("num1" = 100 AND "num1" < 300))'
            . ' AND (("f1" = \'valueA\' AND "f2" = \'valueA\') OR ("f1" < \'valueB\' AND "f2" < \'valueB\') OR ("f3" = \'valueC\')))');
    }

    public function testAddingLCCondtions5()
    {
        $this->checkConditions(null,
            [
                ['field' => 'num0', 'operator' => '=', 'value' => 100],
                ['field' => 'num0', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__'],
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'block/true/true'],
                ['field' => 'f1,f2', 'operator' => '=', 'value' => 'valueA'],
                ['field' => 'f1,f2', 'operator' => '<', 'value' => 'valueB'],
                ['field' => 'f3', 'operator' => '=', 'value' => 'valueC'],
            ],
            '((("num0" = \'100\' AND "num0" < \'300\') OR ("num1" = 100 AND "num1" < 300))'
            . ' AND (("f1" = \'valueA\' AND "f2" = \'valueA\') AND ("f1" < \'valueB\' AND "f2" < \'valueB\') AND ("f3" = \'valueC\')))');
    }

    public function testAddingLCCondtions6()
    {
        $this->checkConditions(null,
            [
                ['field' => 'num0', 'operator' => '=', 'value' => 100],
                ['field' => 'num0', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__'],
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'block/false/true'],
                ['field' => 'f1,f2', 'operator' => '=', 'value' => 'valueA'],
                ['field' => 'f1,f2', 'operator' => '<', 'value' => 'valueB'],
                ['field' => 'f3', 'operator' => '=', 'value' => 'valueC'],
            ],
            '((("num0" = \'100\' AND "num0" < \'300\') OR ("num1" = 100 AND "num1" < 300))'
            . ' AND (("f1" = \'valueA\' OR "f2" = \'valueA\') AND ("f1" < \'valueB\' OR "f2" < \'valueB\') AND ("f3" = \'valueC\')))');
    }

    public function testAddingLCCondtions7()
    {
        $this->checkConditions(null,
            [
                ['field' => 'num0', 'operator' => '=', 'value' => 100],
                ['field' => 'num0', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__'],
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'block/false/false'],
                ['field' => 'f1,f2', 'operator' => '=', 'value' => 'valueA'],
                ['field' => 'f1,f2', 'operator' => '<', 'value' => 'valueB'],
                ['field' => 'f3', 'operator' => '=', 'value' => 'valueC'],
            ],
            '((("num0" = \'100\' AND "num0" < \'300\') OR ("num1" = 100 AND "num1" < 300))'
            . ' AND (("f1" = \'valueA\' OR "f2" = \'valueA\') OR ("f1" < \'valueB\' OR "f2" < \'valueB\') OR ("f3" = \'valueC\')))');
    }

    public function testAddingLCCondtions8()
    {
        $this->checkConditions(null,
            [
                ['field' => 'num0', 'operator' => '=', 'value' => 100],
                ['field' => 'num0', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'ex',],
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'block/false/false'],
                ['field' => 'f1,f2', 'operator' => '=', 'value' => 'valueA'],
                ['field' => 'f1,f2', 'operator' => '<', 'value' => 'valueB'],
                ['field' => 'f3', 'operator' => '=', 'value' => 'valueC'],
            ],
            '((("num0" = \'100\' OR "num0" < \'300\') AND ("num1" = 100 OR "num1" < 300))'
            . ' AND (("f1" = \'valueA\' OR "f2" = \'valueA\') OR ("f1" < \'valueB\' OR "f2" < \'valueB\') OR ("f3" = \'valueC\')))');
    }

    public function testAddingLCCondtions9()
    {
        $this->checkConditions(null,
            [
                ['field' => 'num0', 'operator' => '=', 'value' => 100],
                ['field' => 'num0', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'ex',],
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'block/false/false'],
                ['field' => 'f1,f2', 'operator' => '=', 'value' => 'valueA extra'],
                ['field' => 'f1,f2', 'operator' => '<', 'value' => 'valueB'],
                ['field' => 'f3', 'operator' => '=', 'value' => 'valueC'],
            ],
            '((("num0" = \'100\' OR "num0" < \'300\') AND ("num1" = 100 OR "num1" < 300))'
            . ' AND (("f1" = \'valueA extra\' OR "f2" = \'valueA extra\') OR ("f1" < \'valueB\' OR "f2" < \'valueB\') OR ("f3" = \'valueC\')))');
    }

    public function testAddingLCCondtions10()
    {
        $this->checkConditions(null,
            [
                ['field' => 'num0', 'operator' => '=', 'value' => 100],
                ['field' => 'num0', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'ex',],
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'block/false/false/false'],
                ['field' => 'f1,f2', 'operator' => '=', 'value' => 'valueA extra'],
                ['field' => 'f1,f2', 'operator' => '<', 'value' => 'valueB'],
                ['field' => 'f3', 'operator' => '=', 'value' => 'valueC'],
            ],
            '((("num0" = \'100\' OR "num0" < \'300\') AND ("num1" = 100 OR "num1" < 300))'
            . ' AND (("f1" = \'valueA extra\' OR "f2" = \'valueA extra\') OR ("f1" < \'valueB\' OR "f2" < \'valueB\') OR ("f3" = \'valueC\')))');
    }

    public function testAddingLCCondtions11()
    {
        $this->checkConditions(null,
            [
                ['field' => 'num0', 'operator' => '=', 'value' => 100],
                ['field' => 'num0', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'ex',],
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'block/false/false/true'],
                ['field' => 'f1,f2', 'operator' => '=', 'value' => 'valueA extra'],
                ['field' => 'f1,f2', 'operator' => '<', 'value' => 'valueB'],
                ['field' => 'f3', 'operator' => '=', 'value' => 'valueC'],
            ],
            '((("num0" = \'100\' OR "num0" < \'300\') AND ("num1" = 100 OR "num1" < 300))'
            . ' AND ((("f1" = \'valueA\' OR "f2" = \'valueA\') OR ("f1" = \'extra\' OR "f2" = \'extra\')) OR ("f1" < \'valueB\' OR "f2" < \'valueB\') OR ("f3" = \'valueC\')))');
    }

    public function testAddingLCCondtions12()
    {
        $this->checkConditions(null,
            [
                ['field' => 'num0', 'operator' => '=', 'value' => 100],
                ['field' => 'num0', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'ex',],
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'block/false/false/false'],
                ['field' => 'num1', 'operator' => '*match*', 'value' => '999'],
            ],
            $this->lcConditionLike);
    }
}
