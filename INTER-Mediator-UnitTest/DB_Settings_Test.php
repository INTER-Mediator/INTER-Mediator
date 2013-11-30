<?php
/**
 * DB_Settings_Test file
 */
require_once(dirname(__FILE__) . '/../DB_Settings.php');

class DB_Settings_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->settings = new DB_Settings();
    }
    
    public function test_getStart()
    {
        $testName = "Check setStart and getStart function in DB_Settings.php.";
        
        $startFrom = '10';
        $expected = 10;
        $this->settings->setStart($startFrom);
        $this->assertSame($this->settings->getStart(), $expected, $testName);

        $startFrom = ';1';
        $expected = 1;
        $this->settings->setStart($startFrom);
        $this->assertSame($this->settings->getStart(), $expected, $testName);
    }

    public function test_getRecordCount()
    {
        $testName = "Check setRecordCount and getRecordCount function in DB_Settings.php.";
        
        $maxSize = '10';
        $expected = 10;
        $this->settings->setRecordCount($maxSize);
        $this->assertSame($this->settings->getRecordCount(), $expected, $testName);

        $maxSize = ';1';
        $expected = 1;
        $this->settings->setRecordCount($maxSize);
        $this->assertSame($this->settings->getRecordCount(), $expected, $testName);
    }
}