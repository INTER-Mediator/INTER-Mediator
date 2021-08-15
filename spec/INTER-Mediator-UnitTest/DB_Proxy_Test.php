<?php

use \PHPUnit\Framework\TestCase;
use \INTERMediator\DB\Proxy;

//$imRoot = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..';
//require "{$imRoot}" . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR .'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class DB_Proxy_Test extends TestCase
{
    function setUp(): void
    {
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');

        $this->db_proxy = new Proxy(true);
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
                    'storing' => 'credential', // 'cookie'(default), 'cookie-domainwide', 'none'
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
        $testName = "Check __construct function in Proxyp.";
        if (function_exists('xdebug_get_headers')) {
            ob_start();
            $this->db_proxy->__construct();
            $headers = xdebug_get_headers();
            header_remove();
            ob_end_flush();
            ob_clean();

            $this->assertContains('X-XSS-Protection: 1; mode=block', $headers);
            $this->assertContains('X-Content-Type-Options: nosniff', $headers);
            $this->assertContains('X-Frame-Options: SAMEORIGIN', $headers);
        } else {
            $this->assertTrue(true, "Preventing Risky warning.");
        }
    }

    function testAuthGroup()
    {
        $aGroup = $this->db_proxy->dbClass->authHandler->getAuthorizedGroups("read");
        $this->assertContains('group1', $aGroup);
        $this->assertContains('group2', $aGroup);
        $this->assertNotContains('group3', $aGroup);
    }

    function testAuthUser()
    {
        $aGroup = $this->db_proxy->dbClass->authHandler->getAuthorizedUsers("read");
        $this->assertContains('user1', $aGroup);
        $this->assertNotContains('user2', $aGroup);
        $this->assertNotContains('user3', $aGroup);
        $this->assertNotContains('user4', $aGroup);
        $this->assertNotContains('user5', $aGroup);
    }

}