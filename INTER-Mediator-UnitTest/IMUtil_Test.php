<?php
/**
 * IMUtil_Test file
 */
require_once(dirname(__FILE__) . '/../IMUtil.php');

class IMUtil_Test extends PHPUnit_Framework_TestCase {

    private $util;
    public function setUp()
    {
        $this->util = new IMUtil();
    }

    public function test_removeNull()
    {
        $str = $this->util->removeNull("INTER\x00-Mediator");
        $this->assertEquals($str, "INTER-Mediator");
    }
}
