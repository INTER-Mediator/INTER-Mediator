<?php

require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
spl_autoload_register('loadClass');

if (!class_exists('PHPUnit_Framework_TestCase')) {
    class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
}

class DB_Proxy_Test extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');

        $this->db_proxy = new DB_Proxy(true);
        $this->db_proxy->initialize(array(
            array(
                'records' => 1,
                'paging' => true,
                'name' => 'person',
                'key' => 'id',
                'query' => array(array('field' => 'id', 'value' => '5', 'operator' => 'eq'),),
                'sort' => array(array('field' => 'id', 'direction' => 'asc'),),
                'repeat-control' => 'insert delete',
                'authentication' => array(
                    'read' => array( /* load, update, new, delete*/
                        'user' => array(),
                        'group' => array("group1", "group2"),
                    ),
                    'update' => array( /* load, update, new, delete*/
                        'user' => array(),
                        'group' => array("group2"),
                    ),
                ),
            ),
        ),
            array(
                'authentication' => array( // table only, for all operations
                    'user' => array('user1'), // Itemize permitted users
                    'group' => array('group2'), // Itemize permitted groups
                    'privilege' => array(), // Itemize permitted privileges
                    'user-table' => 'authuser', // Default value
                    'group-table' => 'authgroup',
                    'corresponding-table' => 'authcor',
                    'challenge-table' => 'issuedhash',
                    'authexpired' => '300', // Set as seconds.
                    'storing' => 'cookie-domainwide', // 'cookie'(default), 'cookie-domainwide', 'none'
                ),
            ),
            array(
                'db-class' => 'PDO',
                'dsn' => 'mysql:dbname=test_db;host=127.0.0.1;charset=utf8',
                'user' => 'web',
                'password' => 'password',
            ),
            false,
            'person'
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    function test___construct()
    {
        $testName = "Check __construct function in DB_Proxy.php.";
        if (function_exists('xdebug_get_headers')) {
            ob_start();
            $this->db_proxy->__construct();
            $headers = xdebug_get_headers();
            header_remove();
            ob_clean();

            if (((float)phpversion()) >= 5.3) {
                $this->assertNotFalse(array_search('X-XSS-Protection: 1; mode=block', $headers));
                $this->assertNotFalse(array_search('X-Content-Type-Options: nosniff', $headers));
                $this->assertNotFalse(array_search('X-Frame-Options: SAMEORIGIN', $headers));
            } else {
                $this->assertContains('X-XSS-Protection: 1; mode=block', $headers);
                $this->assertContains('X-Content-Type-Options: nosniff', $headers);
                $this->assertContains('X-Frame-Options: SAMEORIGIN', $headers);
            }
        }
    }

    function testAuthGroup()
    {
        $aGroup = $this->db_proxy->dbClass->authHandler->getAuthorizedGroups("read");
        if (((float)phpversion()) >= 5.3) {
            $this->assertNotFalse(array_search('group1', $aGroup));
            $this->assertNotFalse(array_search('group2', $aGroup));
            $this->assertFalse(array_search('group3', $aGroup));
        } else {
            $this->assertContains('group1', $aGroup);
            $this->assertContains('group2', $aGroup);
            $this->assertNotContains('group3', $aGroup);
        }
    }

    function testAuthUser()
    {
        $aGroup = $this->db_proxy->dbClass->authHandler->getAuthorizedUsers("read");
        if (((float)phpversion()) >= 5.3) {
            $this->assertNotFalse(array_search('user1', $aGroup));
            $this->assertFalse(array_search('user2', $aGroup));
            $this->assertFalse(array_search('user3', $aGroup));
            $this->assertFalse(array_search('user4', $aGroup));
            $this->assertFalse(array_search('user5', $aGroup));
        } else {
            $this->assertContains('user1', $aGroup);
            $this->assertNotContains('user2', $aGroup);
            $this->assertNotContains('user3', $aGroup);
            $this->assertNotContains('user4', $aGroup);
            $this->assertNotContains('user5', $aGroup);
        }
    }

}