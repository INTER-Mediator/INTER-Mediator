<?php

trait DB_PDO_Test_LocalContextConditions
{
    /**
     * Check Conditions.
     *
     * @param mixed $query The query.
     * @param mixed $conditions The conditions.
     * @param mixed $conditionExpected The condition expected.
     * @return void
     */
    private function checkConditions($query, $conditions, $conditionExpected): void
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
            $this->assertTrue(null, "Exception in getWhereClauseForTest().");
        }
    }

    /**
     * Test adding No LC Condtions 1.
     *
     * @return void
     */
    public function testAddingNoLCCondtions1(): void
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

    /**
     * Test adding LC Condtions 1.
     *
     * @return void
     */
    public function testAddingLCCondtions1(): void
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

    /**
     * Test adding LC Condtions 2.
     *
     * @return void
     */
    public function testAddingLCCondtions2(): void
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

    /**
     * Test adding LC Condtions 3.
     *
     * @return void
     */
    public function testAddingLCCondtions3(): void
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

    /**
     * Test adding LC Condtions 4.
     *
     * @return void
     */
    public function testAddingLCCondtions4(): void
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

    /**
     * Test adding LC Condtions 5.
     *
     * @return void
     */
    public function testAddingLCCondtions5(): void
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

    /**
     * Test adding LC Condtions 6.
     *
     * @return void
     */
    public function testAddingLCCondtions6(): void
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

    /**
     * Test adding LC Condtions 7.
     *
     * @return void
     */
    public function testAddingLCCondtions7(): void
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

    /**
     * Test adding LC Condtions 8.
     *
     * @return void
     */
    public function testAddingLCCondtions8(): void
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

    /**
     * Test adding LC Condtions 9.
     *
     * @return void
     */
    public function testAddingLCCondtions9(): void
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

    /**
     * Test adding LC Condtions 10.
     *
     * @return void
     */
    public function testAddingLCCondtions10(): void
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

    /**
     * Test adding LC Condtions 11.
     *
     * @return void
     */
    public function testAddingLCCondtions11(): void
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

    /**
     * Test adding LC Condtions 12.
     *
     * @return void
     */
    public function testAddingLCCondtions12(): void
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
