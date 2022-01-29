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

class DB_PDO_SQLServer_Test extends DB_PDO_Test_Common
{
    public $dsn;

    function setUp(): void
    {
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');

        $this->dsn = 'sqlsrv:server=localhost;database=test_db';
        if (getenv('TRAVIS') === 'true') {
            $this->dsn = 'sqlsrv:database=test_db;server=localhost';
        } else if (file_exists('/etc/alpine-release')) {
            $this->dsn = 'sqlsrv:server=localhost;database=test_db';
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
                    'aggregation-group-by' => "item_id, item_master.name",
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
        $contextName = 'testContext';
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

        $this->condition1expected = $this->condition1expected2;
        $this->condition2expected = $this->condition2expected2;
        $this->condition3expected = $this->condition3expected2;
        $this->condition4expected = $this->condition4expected2;
        $this->condition5expected = $this->condition5expected2;
        $this->condition6expected = $this->condition6expected2;
        $this->condition7expected = $this->condition7expected2;
        $this->condition8expected = $this->condition8expected2;
        $this->condition9expected = $this->condition9expected2;
        $this->condition10expected = $this->condition10expected2;
    }

    protected function getSampleComdition()
    {
        return "WHERE id=1001 ORDER BY xdate OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY";;
    }
}