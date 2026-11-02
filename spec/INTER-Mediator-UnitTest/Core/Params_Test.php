<?php
/**
 * Params_Test file
 */

use INTERMediator\Params;
use PHPUnit\Framework\TestCase;

class Params_Test extends TestCase
{
    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
    }

    /**
     * Test get Vars.
     *
     * @return void
     */
    public function testGetVars(): void
    {
        $vars = Params::getVars();
        $this->assertTrue(is_array($vars) && count($vars) > 0, 'Parameter has to be corrected.');
    }

    /**
     * Test get Parameter Value 1.
     *
     * @return void
     */
    public function testgetParameterValue1(): void
    {
        $this->assertEquals('password', Params::getParameterValue('dbPassword', 'omg!'), "Can't read the variable dbClass.");
        $this->assertEquals('omg!', Params::getParameterValue('notExist', 'omg!'), "The variable notExist has to be a default value.");
    }

    /**
     * Test get Parameter Value 2.
     *
     * @return void
     */
    public function testgetParameterValue2(): void
    {
        $vars = ['dbPassword', 'dbUser', 'dbPassword', 'notExist'];
        $defs = ['omg!', 'omg!', 'omg!', 'omg!'];
        $expected = ['password', 'web', 'password', 'omg!'];
        $this->assertEquals($expected, Params::getParameterValue($vars, $defs), "Params class has to handle with Array parameter.");
    }

    /**
     * Test get Parameter Value 3.
     *
     * @return void
     */
    public function testgetParameterValue3(): void
    {
        $vars = ['dbPassword', 'dbUser', 'dbPassword', 'notExist'];
        $defs = 'omg!';
        $expected = ['password', 'web', 'password', 'omg!'];
        $this->assertEquals($expected, Params::getParameterValue($vars, $defs), "Params class has to handle with Array parameter.");
    }

    /**
     * Test get Parameter Value 4.
     *
     * @return void
     */
    public function testgetParameterValue4(): void
    {
        $vars = 'dbPassword';
        $defs = ['omg1!', 'omg!', 'omg!', 'omg!'];
        $expected = 'password';
        $this->assertEquals($expected, Params::getParameterValue($vars, $defs), "Params class has to handle with Array parameter.");
    }

    /**
     * Test get Parameter Value 5.
     *
     * @return void
     */
    public function testgetParameterValue5(): void
    {
        $vars = 'notExist';
        $defs = ['omg1!', 'omg!', 'omg!', 'omg!'];
        $expected = 'omg1!';
        $this->assertEquals($expected, Params::getParameterValue($vars, $defs), "Params class has to handle with Array parameter.");
    }

    public function testNoInternalVariables(): void
    {
        Params::reset();
        $vars = Params::getVars();
        $this->assertArrayNotHasKey('imRootDir', $vars);
        $this->assertArrayNotHasKey('appRootDir', $vars);
        $this->assertArrayNotHasKey('paramsFile', $vars);
        $this->assertEquals('password', Params::getParameterValue('dbPassword', 'omg!'));
    }

    public function testArrayDefaultEdgeCases(): void
    {
        $this->assertEquals([null, null], Params::getParameterValue(['notExist1', 'notExist2'], []));
        $this->assertEquals(['x', 'y', 'y'],
            Params::getParameterValue(['notExist1', 'notExist2', 'notExist3'], ['a' => 'x', 'b' => 'y']));
        $this->assertEquals([], Params::getParameterValue('notExist', []));
    }

    public function testArrayDefaultViaNullFallbackStaysArray(): void
    {
        // Regression test for the misuse pattern that broke FileURL::getMedia():
        // passing an array literal directly as the default for a scalar key returns
        // only its first element (documented/tested behavior above), not the array itself.
        $mediaRootDir = 'omg!';
        $wrongDefault = Params::getParameterValue('notExist', [$mediaRootDir]);
        $this->assertSame($mediaRootDir, $wrongDefault,
            "Documents the pitfall: an array literal default for a scalar key degrades to its first element.");

        // The safe pattern used by FileURL::getMedia() keeps the array intact.
        $safeDefault = Params::getParameterValue('notExist', null) ?? [$mediaRootDir];
        $this->assertSame([$mediaRootDir], $safeDefault,
            "getParameterValue(name, null) ?? [default] must keep the array default intact.");
    }

    public function testReset(): void
    {
        Params::setVar('dbPassword', 'changed');
        $this->assertEquals('changed', Params::getParameterValue('dbPassword', 'omg!'));
        Params::reset();
        $this->assertEquals('password', Params::getParameterValue('dbPassword', 'omg!'));
    }
}
