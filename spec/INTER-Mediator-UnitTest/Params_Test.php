<?php
/**
 * MediaAccess_Test file
 */

use INTERMediator\Params;
use PHPUnit\Framework\TestCase;

class Params_Test extends TestCase
{
    protected function setUp(): void
    {
    }

    public function testGetParameters()
    {
//        var_dump(Params::getVars());
        $this->assertEquals('PDO', Params::getParameterValue('dbClass','omg!'), "Can't read the variable dbClass.");
        $this->assertEquals('omg!', Params::getParameterValue('notExist','omg!'), "The variable notExist has to be a default value.");

        $vars = ['dbClass','dbUser','dbPassword','notExist'];
        $defs = ['omg!','omg!','omg!','omg!'];
        $expected = ['PDO','web','password','omg!'];
        $this->assertEquals($expected, Params::getParameterValue($vars,$defs), "Params class has to handle with Array parameter/ 1.");

        $vars = ['dbClass','dbUser','dbPassword','notExist'];
        $defs = 'omg!';
        $expected = ['PDO','web','password','omg!'];
        $this->assertEquals($expected, Params::getParameterValue($vars,$defs), "Params class has to handle with Array parameter/ 2.");

        $vars = 'dbClass';
        $defs = ['omg1!','omg!','omg!','omg!'];
        $expected = 'PDO';
        $this->assertEquals($expected, Params::getParameterValue($vars,$defs), "Params class has to handle with Array parameter/ 3.");

        $vars = 'notExist';
        $defs = ['omg1!','omg!','omg!','omg!'];
        $expected = 'omg1!';
        $this->assertEquals($expected, Params::getParameterValue($vars,$defs), "Params class has to handle with Array parameter/ 4.");
    }
}
