<?php
/**
 * PDO-SQLite_Test file
 */
require_once('DB_PDO_Test_Common.php');

use INTERMediator\DB\Proxy;

class DB_PDO_SQLite_Test extends DB_PDO_Test_Common
{
    public $dsn = 'sqlite:/var/db/im/sample.sq3';

    function setUp(): void
    {
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');
        if (getenv('GITHUB_ACTIONS') === 'true') {
            $this->dsn = 'sqlite:/home/runner/work/INTER-Mediator/INTER-Mediator/sample.sq3';
        }
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAggregation()
    {
        // The sample schema doesn't have a data to check this feature.
    }

    function dbProxySetupForAccess($contextName, $maxRecord, $subContextName = null)
    {
        $this->schemaName = "";
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
            'dsn' => $this->dsn,
        );
        $this->db_proxy = new Proxy(true);
        $this->db_proxy->initialize($contexts, $options, $dbSettings, 2, $contextName);
    }

    function dbProxySetupForAccessSetKey($contextName, $maxRecord, $keyName)
    {
        $this->schemaName = "";
        $contexts = array(
            array(
                'records' => $maxRecord,
                'name' => $contextName,
                'view' => $contextName,
                'table' => $contextName,
                'key' => $keyName,
                'sort' => array(
                    array('field' => $keyName, 'direction' => 'asc'),
                ),
            )
        );
        $options = null;
        $dbSettings = array(
            'db-class' => 'PDO',
            'dsn' => $this->dsn,
        );
        $this->db_proxy = new Proxy(true);
        $this->db_proxy->initialize($contexts, $options, $dbSettings, 2, $contextName);
    }

    function dbProxySetupForAuth()
    {
        $this->db_proxy = new Proxy(true);
        $this->db_proxy->initialize(
            array(
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
                    'group' => array('group2'), // Itemize permitted groups
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
            ),
            false, 'person'
        );
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testNativeUser()
    {
        // SQLite doesn't have native users.
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
    }

    public function testCreateRecord2()
    {
        // SQLite doesn't support the record creation with the key field as non AUTOINCREMENT field.
    }

    protected $sqlSETClause1 = "(\"num1\",\"num2\",\"date1\",\"date2\",\"time1\",\"time2\",\"dt1\",\"dt2\",\"vc1\",\"vc2\",\"text1\",\"text2\") "
    . "VALUES(100,200,'2022-04-01','2022-04-01','10:21:31','10:21:31','2022-04-01 10:21:31','2022-04-01 10:21:31','TEST','TEST','TEST','TEST')";
    protected $sqlSETClause2 = "(\"num1\",\"num2\",\"date1\",\"date2\",\"time1\",\"time2\",\"dt1\",\"dt2\",\"vc1\",\"vc2\",\"text1\",\"text2\") "
    . "VALUES(0,NULL,'',NULL,'',NULL,'',NULL,'',NULL,'',NULL)";
    protected $sqlSETClause3 = "(\"num1\",\"num2\",\"date1\",\"date2\",\"time1\",\"time2\",\"dt1\",\"dt2\",\"vc1\",\"vc2\",\"text1\",\"text2\") "
    . "VALUES(0,0,'','','','','','','','','','')";

}
