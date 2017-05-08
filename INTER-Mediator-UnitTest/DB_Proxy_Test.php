<?php

require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
require_once(dirname(__FILE__) . '/../DB_Interfaces.php');
require_once(dirname(__FILE__) . '/../DB_Logger.php');
require_once(dirname(__FILE__) . '/../DB_Settings.php');
require_once(dirname(__FILE__) . '/../DB_UseSharedObjects.php');
require_once(dirname(__FILE__) . '/../DB_Proxy.php');
require_once(dirname(__FILE__) . '/../DB_Formatters.php');
require_once(dirname(__FILE__) . '/../DB_AuthCommon.php');

class DB_Proxy_Test extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
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

            $this->assertContains('X-XSS-Protection: 1; mode=block', $headers);
            $this->assertContains('X-Content-Type-Options: nosniff', $headers);
            $this->assertContains('X-Frame-Options: SAMEORIGIN', $headers);
        }
    }

    function testAuthGroup()
    {
        $aGroup = $this->db_proxy->dbClass->getAuthorizedGroups("read");
        $this->assertContains('group1', $aGroup);
        $this->assertContains('group2', $aGroup);
        $this->assertNotContains('group3', $aGroup);
    }

    function testAuthUser()
    {
        $aGroup = $this->db_proxy->dbClass->getAuthorizedUsers("read");
        $this->assertContains('user1', $aGroup);
        $this->assertNotContains('user2', $aGroup);
        $this->assertNotContains('user3', $aGroup);
        $this->assertNotContains('user4', $aGroup);
        $this->assertNotContains('user5', $aGroup);
    }

}