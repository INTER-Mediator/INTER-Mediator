<?php
/**
 * DB_FMS_FX_Test file
 */
require_once('DB_FMS_Test_Common.php');

class DB_FMS_FX_Test extends DB_FMS_Test_Common
{
    function setUp()
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
                    array('field'=>'id','direction'=>'asc'),
                ),
            )
        );
        $options = null;
        $dbSettings = array(
            'db-class' => 'FileMaker_FX',
            'user' => 'web',
            'password' => 'password',
        );
        $this->db_proxy = new DB_Proxy(true);
        $this->db_proxy->initialize($contexts, $options, $dbSettings, false, $contextName);
    }

    function dbProxySetupForAuth()
    {
        $this->db_proxy = new DB_Proxy(true);
        $this->db_proxy->initialize(array(
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
                'db-class' => 'FileMaker_FX',
                'user' => 'web',
                'password' => 'password',
            ),
            false);
    }
}