<?php
/**
 * DB_PDO-SQLite_Test file
 */
require_once('DB_PDO_Test_Common.php');

class DB_PDO_SQLite_Test extends DB_PDO_Test_Common
{
    function setUp()
    {
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');

    }

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
            'dsn' => 'sqlite:/var/db/im/sample.sq3',
        );
        $this->db_proxy = new DB_Proxy(true);
        $this->db_proxy->initialize($contexts, $options, $dbSettings, 2, $contextName);
    }

    function dbProxySetupForAuth()
    {
        $this->db_proxy = new DB_Proxy(true);
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
                'dsn' => 'sqlite:/var/db/im/sample.sq3',
            ),
            false
        );
    }

    public function testNativeUser()
    {
        // SQLite doesn't have native users.
    }

    function dbProxySetupForAggregation()
    {
        $this->db_proxy = new DB_Proxy(true);
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
                'dsn' => 'sqlite:/var/db/im/sample.sq3',
            ),
            2,
            "summary"
        );
    }
}
