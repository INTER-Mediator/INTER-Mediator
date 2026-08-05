<?php

trait DB_PDO_Test_Conditions
{
    /* The method function checkConditions($query, $conditions, $conditionExpected)
       is defined on DB_PDO_Test_LocalContextConditions.php.
    */

    /**
     * Test condition 1.
     *
     * @return void
     */
    public function testCondition1(): void
    {
        $this->checkConditions(
            [['field' => 'num1', 'operator' => '=', 'value' => 100]],
            null,
            '("num1" = 100)');
    }

    /**
     * Test condition 2.
     *
     * @return void
     */
    public function testCondition2(): void
    {
        $this->checkConditions(
            [
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ],
            null,
            '("num1" = 100 AND "num1" < 300)');
    }

    /**
     * Test condition 3.
     *
     * @return void
     */
    public function testCondition3(): void
    {
        $this->checkConditions(
            [
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__',],
                ['field' => 'num1', 'operator' => '>', 'value' => 500],
            ],
            null,
            '("num1" = 100 AND "num1" < 300) OR ("num1" > 500)');
    }

    /**
     * Test condition 4.
     *
     * @return void
     */
    public function testCondition4(): void
    {
        $this->checkConditions(
            [
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => '__operation__',],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => 'num1', 'operator' => '>', 'value' => 500],
            ],
            null,
            '("num1" = 100) OR ("num1" < 300 AND "num1" > 500)');
    }

    /**
     * Test condition 5.
     *
     * @return void
     */
    public function testCondition5(): void
    {
        $this->checkConditions(
            null,
            [
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => '__operation__',],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => 'num1', 'operator' => '>', 'value' => 500],
            ],
            '(("num1" = 100) OR ("num1" < 300 AND "num1" > 500))');
    }

    /**
     * Test condition 6.
     *
     * @return void
     */
    public function testCondition6(): void
    {
        $this->checkConditions(
            null,
            [
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => '__operation__', 'operator' => 'ex',],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => 'num1', 'operator' => '>', 'value' => 500],
            ],
            '(("num1" = 100) AND ("num1" < 300 OR "num1" > 500))');
    }

    /**
     * Test condition 7.
     *
     * @return void
     */
    public function testCondition7(): void
    {
        $this->checkConditions(
            null,
            [
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__',],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => 'num1', 'operator' => '>', 'value' => 500],
                ['field' => '__operation__',],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => 'num1', 'operator' => '>', 'value' => 500],
            ], '(("num1" = 100 AND "num1" < 300) OR ("num1" < 300 AND "num1" > 500) OR ("num1" < 300 AND "num1" > 500))');
    }

    /**
     * Test condition 8.
     *
     * @return void
     */
    public function testCondition8(): void
    {
        $this->checkConditions(
            null,
            [
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'ex',],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => 'num1', 'operator' => '>', 'value' => 500],
                ['field' => '__operation__',],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => 'num1', 'operator' => '>', 'value' => 500],
            ],
            '(("num1" = 100 OR "num1" < 300) AND ("num1" < 300 OR "num1" > 500) AND ("num1" < 300 OR "num1" > 500))');
    }

    /**
     * Test condition 9.
     *
     * @return void
     */
    public function testCondition9(): void
    {
        $this->checkConditions(
            null,
            [
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__',],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => 'num1', 'operator' => '>', 'value' => 500],
                ['field' => '__operation__', 'operator' => 'ex',],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => 'num1', 'operator' => '>', 'value' => 500],
            ],
            '(("num1" = 100 OR "num1" < 300) AND ("num1" < 300 OR "num1" > 500) AND ("num1" < 300 OR "num1" > 500))');
    }

    /**
     * Test condition 10.
     *
     * @return void
     */
    public function testCondition10(): void
    {
        $this->checkConditions(
            null,
            [
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => '__operation__', 'operator' => 'ex',],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => 'num1', 'operator' => '>', 'value' => 500],
                ['field' => '__operation__', 'operator' => 'ex',],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
                ['field' => 'num1', 'operator' => '>', 'value' => 500],
            ],
            '(("num1" = 100 OR "num1" < 300) AND ("num1" < 300 OR "num1" > 500) AND ("num1" < 300 OR "num1" > 500))');
    }

    /**
     * Test condition 11.
     *
     * @return void
     */
    public function testCondition11(): void
    {
        $this->checkConditions(
            [['field' => 'num1', 'operator' => 'IS NULL'],],
            null,
            '("num1" IS NULL)');
        $this->checkConditions(
            null,
            [['field' => 'num1', 'operator' => 'IS NULL'],],
            '(("num1" IS NULL))');
    }

    /**
     * Test condition 12.
     *
     * @return void
     */
    public function testCondition12(): void
    {
        $this->checkConditions(
            [['field' => 'num1', 'value' => 100]], // No operator key
            null,
            '("num1" = 100)');
    }

    /**
     * Test condition 13.
     *
     * @return void
     */
    public function testCondition13(): void
    {
        $this->checkConditions(
            [
                ['field' => 'num1', 'value' => 100], // No operator key
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ],
            null,
            '("num1" = 100 AND "num1" < 300)');
    }

}
