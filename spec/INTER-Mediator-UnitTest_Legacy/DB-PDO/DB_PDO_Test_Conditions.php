<?php

trait DB_PDO_Test_Conditions
{
    /* The method function checkConditions($query, $conditions, $conditionExpected)
       is defined on DB_PDO_Test_LocalContextConditions.php.
    */

    public function testCondition1()
    {
        $this->checkConditions(
            [['field' => 'num1', 'operator' => '=', 'value' => 100]],
            null,
            '("num1" = 100)');
    }

    public function testCondition2()
    {
        $this->checkConditions(
            [
                ['field' => 'num1', 'operator' => '=', 'value' => 100],
                ['field' => 'num1', 'operator' => '<', 'value' => 300],
            ],
            null,
            '("num1" = 100 AND "num1" < 300)');
    }

    public function testCondition3()
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

    public function testCondition4()
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

    public function testCondition5()
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

    public function testCondition6()
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

    public function testCondition7()
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

    public function testCondition8()
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

    public function testCondition9()
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

    public function testCondition10()
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

    public function testCondition11()
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

    public function testCondition12()
    {
        $this->checkConditions(
            [['field' => 'num1', 'value' => 100]], // No operator key
            null,
            '("num1" = 100)');
    }

    public function testCondition13()
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
