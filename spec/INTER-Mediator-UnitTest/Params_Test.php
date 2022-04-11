<?php
/**
 * Params_Test file
 */

use INTERMediator\Params;
use PHPUnit\Framework\TestCase;

class Params_Test extends TestCase
{
    protected function setUp(): void
    {
    }

    public function testGetVars()
    {
        $vars = Params::getVars();
        $this->assertTrue(is_array($vars) && count($vars) > 0, 'Parameter has to be corrected.');
    }

    public function testgetParameterValue1()
    {
        $this->assertEquals('PDO', Params::getParameterValue('dbClass', 'omg!'), "Can't read the variable dbClass.");
        $this->assertEquals('omg!', Params::getParameterValue('notExist', 'omg!'), "The variable notExist has to be a default value.");
    }

    public function testgetParameterValue2()
    {
        $vars = ['dbClass', 'dbUser', 'dbPassword', 'notExist'];
        $defs = ['omg!', 'omg!', 'omg!', 'omg!'];
        $expected = ['PDO', 'web', 'password', 'omg!'];
        $this->assertEquals($expected, Params::getParameterValue($vars, $defs), "Params class has to handle with Array parameter.");
    }

    public function testgetParameterValue3()
    {
        $vars = ['dbClass', 'dbUser', 'dbPassword', 'notExist'];
        $defs = 'omg!';
        $expected = ['PDO', 'web', 'password', 'omg!'];
        $this->assertEquals($expected, Params::getParameterValue($vars, $defs), "Params class has to handle with Array parameter.");
    }

    public function testgetParameterValue4()
    {
        $vars = 'dbClass';
        $defs = ['omg1!', 'omg!', 'omg!', 'omg!'];
        $expected = 'PDO';
        $this->assertEquals($expected, Params::getParameterValue($vars, $defs), "Params class has to handle with Array parameter.");
    }

    public function testgetParameterValue5()
    {
        $vars = 'notExist';
        $defs = ['omg1!', 'omg!', 'omg!', 'omg!'];
        $expected = 'omg1!';
        $this->assertEquals($expected, Params::getParameterValue($vars, $defs), "Params class has to handle with Array parameter.");
    }
}
