<?php
/**
 * DB_Settings_Test file
 */

use PHPUnit\Framework\TestCase;
use INTERMediator\DB\Settings;

class DB_Settings_Test extends TestCase
{
    private Settings $settings;

    public function setUp(): void
    {
        $this->settings = new Settings();
    }
    
    public function test_getStart()
    {
        $testName = "Check setStart and getStart function in Settingsp.";
        
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
        $testName = "Check setRecordCount and getRecordCount function in Settings.php";
        
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