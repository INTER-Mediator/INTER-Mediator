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
        $this->assertEquals('password', Params::getParameterValue('dbPassword', 'omg!'), "Can't read the variable dbClass.");
        $this->assertEquals('omg!', Params::getParameterValue('notExist', 'omg!'), "The variable notExist has to be a default value.");
    }

    public function testgetParameterValue2()
    {
        $vars = ['dbPassword', 'dbUser', 'dbPassword', 'notExist'];
        $defs = ['omg!', 'omg!', 'omg!', 'omg!'];
        $expected = ['password', 'web', 'password', 'omg!'];
        $this->assertEquals($expected, Params::getParameterValue($vars, $defs), "Params class has to handle with Array parameter.");
    }

    public function testgetParameterValue3()
    {
        $vars = ['dbPassword', 'dbUser', 'dbPassword', 'notExist'];
        $defs = 'omg!';
        $expected = ['password', 'web', 'password', 'omg!'];
        $this->assertEquals($expected, Params::getParameterValue($vars, $defs), "Params class has to handle with Array parameter.");
    }

    public function testgetParameterValue4()
    {
        $vars = 'dbPassword';
        $defs = ['omg1!', 'omg!', 'omg!', 'omg!'];
        $expected = 'password';
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
