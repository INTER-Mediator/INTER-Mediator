<?php
/*
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 11/12/14
 * Time: 14:21
 * Unit Test by PHPUnit (http://phpunit.de)
 *
 */

require_once(dirname(__FILE__) . '/../DB_PDO_Test_Common.php');

use INTERMediator\DB\Proxy;

class DB_PDO_PostgreSQL_Test extends DB_PDO_Test_Common
{
    public string $dsn;

    function setUp(): void
    {
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');
        $this->dsn = 'pgsql:host=localhost;port=5432;dbname=test_db';
    }

    #[doesNotPerformAssertions]
    public function testAggregation():void
    {
        // The sample schema doesn't have a data to check this feature.
    }

    function dbProxySetupForAccess(string $contextName, int $maxRecord, ?string $subContextName = null):void
    {
        $this->schemaName = "im_sample.";
        $contexts = array(
            array(
                'records' => $maxRecord,
                'name' => $contextName,
                'view' => "{$this->schemaName}{$contextName}",
                'table' => "{$this->schemaName}{$contextName}",
                'key' => 'id',
                'repeat-control' => is_null($subContextName) ? 'copy' : "copy-{$subContextName}",
                'sort' => array(
                    array('field' => 'id', 'direction' => 'asc'),
                ),
                //'sequence' => $seqName,
            )
        );
        if (!is_null($subContextName)) {
            $contexts[] = array(
                'records' => $maxRecord,
                'name' => $subContextName,
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
            'dsn' => 'pgsql:host=localhost;port=5432;dbname=test_db',
            'user' => 'web',
            'password' => 'password',
        );
        $this->db_proxy = new Proxy(true);
        $resultInit = $this->db_proxy->initialize($contexts, $options, $dbSettings, 2, $contextName);
        $this->assertNotFalse($resultInit, 'Proxy::initialize must return true.');
    }

    function dbProxySetupForAccessSetKey(string $contextName, int $maxRecord, string $keyName):void
    {
        $this->schemaName = "im_sample.";
        $contexts = array(
            array(
                'records' => $maxRecord,
                'name' => $contextName,
                'view' => "{$this->schemaName}{$contextName}",
                'table' => "{$this->schemaName}{$contextName}",
                'key' => $keyName,
                'sort' => array(
                    array('field' => $keyName, 'direction' => 'asc'),
                ),
            )
        );
        $options = null;
        $dbSettings = array(
            'db-class' => 'PDO',
            'dsn' => 'pgsql:host=localhost;port=5432;dbname=test_db',
            'user' => 'web',
            'password' => 'password',
        );
        $this->db_proxy = new Proxy(true);
        $resultInit = $this->db_proxy->initialize($contexts, $options, $dbSettings, 2, $contextName);
        $this->assertNotFalse($resultInit, 'Proxy::initialize must return true.');
    }

    function dbProxySetupForAuth():void
    {
        $this->schemaName = "im_sample.";
        $this->db_proxy = new Proxy(true);
        $resultInit = $this->db_proxy->initialize(
            array(
                array(
                    'records' => 1000,
                    'paging' => true,
                    'name' => 'person',
                    'key' => 'id',
                    'query' => array( /* array( 'field'=>'id', 'value'=>'5', 'operator'=>'eq' ),*/),
                    'sort' => array(array('field' => 'id', 'direction' => 'asc'),),
                    //'sequence' => 'im_sample.person_id_seq',
                )
            ),
            array(
                'authentication' => array( // table only, for all operations
                    'user' => array('user1'), // Itemize permitted users
                    'group' => array('group2'), // Itemize permitted groups
                    'user-table' => 'im_sample.authuser', // Default value
                    'group-table' => 'im_sample.authgroup',
                    'corresponding-table' => 'im_sample.authcor',
                    'challenge-table' => 'im_sample.issuedhash',
                    'authexpired' => '300', // Set as seconds.
                    'storing' => 'credential', // 'cookie'(default), 'cookie-domainwide', 'none'
                ),
            ),
            array(
                'db-class' => 'PDO',
                'dsn' => 'pgsql:host=localhost;port=5432;dbname=test_db',
                'user' => 'web',
                'password' => 'password',
            ),
            2, 'person'
        );
        $this->assertNotFalse($resultInit, 'Proxy::initialize must return true.');
    }

    function dbProxySetupForAggregation():void
    {
        $this->schemaName = "im_sample.";
        $this->db_proxy = new Proxy(true);
        $resultInit = $this->db_proxy->initialize(
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
                'dsn' => 'pgsql:host=localhost;port=5432;dbname=test_db',
                'user' => 'web',
                'password' => 'password',
            ),
            2,
            "summary"
        );
        $this->assertNotFalse($resultInit, 'Proxy::initialize must return true.');
    }

    function dbProxySetupForCondition(?array $queryArray):void
    {
        $this->schemaName = "im_sample.";
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
        $resultInit = $this->db_proxy->initialize($contexts, $options, $dbSettings, 2, $contextName);
        $this->assertNotFalse($resultInit, 'Proxy::initialize must return true.');
    }

    protected string $sqlSETClause1 = "(\"num1\",\"num2\",\"date1\",\"date2\",\"time1\",\"time2\",\"dt1\",\"dt2\",\"vc1\",\"vc2\",\"text1\",\"text2\") "
    . "VALUES(100,200,'2022-04-01','2022-04-01','10:21:31','10:21:31','2022-04-01 10:21:31','2022-04-01 10:21:31','TEST','TEST','TEST','TEST')";
    protected string $sqlSETClause2 = "(\"num1\",\"num2\",\"date1\",\"date2\",\"time1\",\"time2\",\"dt1\",\"dt2\",\"vc1\",\"vc2\",\"text1\",\"text2\") "
    . "VALUES(0,NULL,'',NULL,'',NULL,'',NULL,'',NULL,'',NULL)";
    protected string $sqlSETClause3 = "(\"num1\",\"num2\",\"date1\",\"date2\",\"time1\",\"time2\",\"dt1\",\"dt2\",\"vc1\",\"vc2\",\"text1\",\"text2\") "
    . "VALUES(0,0,'','','','','','','','','','')";

    protected string $lcConditionLike = '((("num0" = \'100\' OR "num0" < \'300\') AND ("num1" = 100 OR "num1" < 300))'
    . ' AND ((CAST("num1" AS TEXT) LIKE \'%999%\')))';

}
