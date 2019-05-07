<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 15/06/20
 * Time: 23:49
 */
use \PHPUnit\Framework\TestCase;
use \INTERMediator\LDAPAuth;

//require_once(dirname(__FILE__) . '/../LDAPAuth.php');

class LDAPAuth_Test extends TestCase {

    private $obj;
    public function setUp()
    {
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        $this->obj = new LDAPAuth();
    }

    public function test_valueForJSInsert()
    {
        $user = "test1";
        $pass = "whoarey";

        if ( $this->obj->isActive) {
            $r = $this->obj->bindCheck("xxx", "xxxx");
            $this->assertFalse($r, "non-existing account");

            $r = $this->obj->bindCheck($user, $pass);
            $this->assertTrue($r, "valid account");

            $r = $this->obj->bindCheck($user, "xxxxx");
            $this->assertfalse($r, "wrong password");
        }
    }
}
