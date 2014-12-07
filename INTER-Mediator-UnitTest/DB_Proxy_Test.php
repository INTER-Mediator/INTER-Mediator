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
        $this->db_proxy->initialize(array(),
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
            false);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    function test___construct()    {
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

}