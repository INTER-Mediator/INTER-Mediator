<?php
/*
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 11/12/14
 * Time: 14:21
 * Unit Test by PHPUnit (http://phpunit.de)
 *
 */

require_once('DB_PDO_Test_Common.php');

use INTERMediator\DB\Proxy;

class DB_PDO_MySQL_Test extends DB_PDO_Test_Common
{
    public $dsn;

    function setUp(): void
    {
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');

        $this->dsn = 'mysql:host=localhost;dbname=test_db;charset=utf8mb4';
        if (getenv('TRAVIS') === 'true') {
            $this->dsn = 'mysql:host=localhost;dbname=test_db;charset=utf8mb4';
        } else if (getenv('GITHUB_ACTIONS') === 'true') {
            $this->dsn = 'mysql:host=127.0.0.1;dbname=test_db;charset=utf8mb4';
        } else if (file_exists('/etc/alpine-release')) {
            $this->dsn = 'mysql:dbname=test_db;host=127.0.0.1';
        } else if (file_exists('/etc/redhat-release')) {
            $this->dsn = 'mysql:unix_socket=/var/lib/mysql/mysql.sock;dbname=test_db;charset=utf8mb4';
        }
    }

    function dbProxySetupForAccess($contextName, $maxRecord, $subContextName = null)
    {
        $this->schemaName = "";
        $contexts = array(
            array(
                'records' => $maxRecord,
                'name' => $contextName,
                'view' => $contextName,
                'table' => $contextName,
                'key' => 'id',
                'repeat-control' => is_null($subContextName) ? 'copy' : "copy-{$subContextName}",
                'sort' => array(
                    array('field' => 'id', 'direction' => 'asc'),
                ),
            )
        );
        if (!is_null($subContextName)) {
            $contexts[] = array(
                'records' => $maxRecord,
                'name' => $subContextName,
                'view' => $subContextName,
                'table' => $subContextName,
                'key' => 'id',
                'relation' => array(
                    "foreign-key" => "{$contextName}_id",
                    "join-field" => "id",
                    "operator" => "=",
                ),
            );
        }
        $options = null;
        $dbSettings = array(
            'db-class' => 'PDO',
            'dsn' => $this->dsn,
            'user' => 'web',
            'password' => 'password',
        );
        $this->db_proxy = new Proxy(true);
        $this->db_proxy->initialize($contexts, $options, $dbSettings, 2, $contextName);
    }

    function dbProxySetupForAuth()
    {
        $this->db_proxy = new Proxy(true);
        $this->db_proxy->initialize(array(
            array(
                'records' => 1000,
                'paging' => true,
                'name' => 'person',
                'key' => 'id',
                'query' => array( /* array( 'field'=>'id', 'value'=>'5', 'operator'=>'eq' ),*/),
                'sort' => array(array('field' => 'id', 'direction' => 'asc'),),
                'sequence' => 'im_sample.serial',
            )
        ),
            array(
                'authentication' => array( // table only, for all operations
                    'user' => array('user1'), // Itemize permitted users
                    'group' => array('group2'), // gropu2 contain user4 and user5
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
                'dsn' => $this->dsn,
                'user' => 'web',
                'password' => 'password',
            ),
            2
        );
    }

    function dbProxySetupForAggregation()
    {
        $this->db_proxy = new Proxy(true);
        $this->db_proxy->initialize(
            array(
                array(
                    'name' => 'summary',
                    'view' => 'saleslog',
                    'query' => array(
                        array('field' => 'dt', 'operator' => '>=', 'value' => '2010-01-01',),
                        array('field' => 'dt', 'operator' => '<', 'value' => '2010-02-01',),
                    ),
                    'sort' => array(
                        array('field' => 'total', 'direction' => 'desc'),
                    ),
                    'records' => 10,
                    'aggregation-select' => "item_master.name as item_name,sum(total) as total",
                    'aggregation-from' => "saleslog inner join item_master on saleslog.item_id=item_master.id",
                    'aggregation-group-by' => "item_id",
                ),
            ),
            null,
            array(
                'db-class' => 'PDO',
                'dsn' => $this->dsn,
                'user' => 'web',
                'password' => 'password',
            ),
            2,
            "summary"
        );
    }

    function dbProxySetupForCondition($queryArray)
    {
        $this->schemaName = "";
        $contextName = 'testtable';
        $contexts = array(
            array(
                'records' => 10000000,
                'name' => $contextName,
                'key' => 'id',
            )
        );
        if (!is_null($queryArray)) {
            $contexts[0]['query'] = $queryArray;
        }
        $options = null;
        $dbSettings = array(
            'db-class' => 'PDO',
            'dsn' => $this->dsn,
            'user' => 'web',
            'password' => 'password',
        );
        $this->db_proxy = new Proxy(true);
        $this->db_proxy->initialize($contexts, $options, $dbSettings, 2, $contextName);

        $this->condition1expected = $this->condition1expected1;
        $this->condition2expected = $this->condition2expected1;
        $this->condition3expected = $this->condition3expected1;
        $this->condition4expected = $this->condition4expected1;
        $this->condition5expected = $this->condition5expected1;
        $this->condition6expected = $this->condition6expected1;
        $this->condition7expected = $this->condition7expected1;
        $this->condition8expected = $this->condition8expected1;
        $this->condition9expected = $this->condition9expected1;
        $this->condition10expected = $this->condition10expected1;
    }

    protected $sqlSETClause1 = "(`num1`,`num2`,`date1`,`date2`,`time1`,`time2`,`dt1`,`dt2`,`vc1`,`vc2`,`text1`,`text2`) "
    . "VALUES(100,200,'2022-04-01','2022-04-01','10:21:31','10:21:31','2022-04-01 10:21:31','2022-04-01 10:21:31','TEST','TEST','TEST','TEST')";
    protected $sqlSETClause2 = "(`num1`,`num2`,`date1`,`date2`,`time1`,`time2`,`dt1`,`dt2`,`vc1`,`vc2`,`text1`,`text2`) "
    . "VALUES(0,NULL,'',NULL,'',NULL,'',NULL,'',NULL,'',NULL)";
    protected $sqlSETClause3 = "(`num1`,`num2`,`date1`,`date2`,`time1`,`time2`,`dt1`,`dt2`,`vc1`,`vc2`,`text1`,`text2`) "
    . "VALUES(0,0,'','','','','','','','','','')";

}