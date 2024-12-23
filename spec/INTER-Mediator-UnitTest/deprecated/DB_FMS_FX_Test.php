<?php
/**
 * DB_FMS_FX_Test file
 */

namespace deprecated;
use DB_FMS_Test_Common;

require_once(dirname(__FILE__) . '/DB-FileMaker/DB_FMS_Test_Common.php');
class DB_FMS_FX_Test extends DB_FMS_Test_Common
{
    function setUp(): void
    {
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');
    }

    function dbProxySetupForAccess($contextName, $maxRecord)
    {
        $this->schemaName = "";
        $contexts = array(
            array(
                'records' => $maxRecord,
                'name' => $contextName,
                'key' => 'id',
                'sort' => array(
                    array('field' => 'id', 'direction' => 'asc'),
                ),
            )
        );
        $options = null;
        $dbSettings = array(
            'db-class' => 'FileMaker_FX',
            'server' => '10.211.56.2',//'localserver','127.0.0.1', //
            'user' => 'web',
            'password' => 'password',
        );
        $this->db_proxy = new \INTERMediator\DB\Proxy(true);
        $resultInit = $this->db_proxy->initialize($contexts, $options, $dbSettings, false, $contextName);
        $this->assertNotFalse($resultInit, 'Proxy::initialize must return true.');
    }

    function dbProxySetupForAuth()
    {
        $this->db_proxy = new \INTERMediator\DB\Proxy(true);
        $resultInit = $this->db_proxy->initialize(array(
            array(
                'records' => 1000,
                'paging' => true,
                'name' => 'person',
                'view' => 'person_layout',
                'table' => 'person_layout',
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
                    'is-required-2FA' => false,
                ),
            ),
            array(
                'db-class' => 'FileMaker_FX',
                'server' => '10.211.56.2',//'localserver','127.0.0.1', //
                'user' => 'web',
                'password' => 'password',
            ),
            false);
        $this->assertNotFalse($resultInit, 'Proxy::initialize must return true.');
    }
}
