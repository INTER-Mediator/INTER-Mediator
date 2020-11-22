<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2014/02/10
 * Time: 15:40
 */

use INTERMediator\DB\Proxy;
use PHPUnit\Framework\TestCase;

class SendMail_Test extends TestCase
{
    var $db_proxy = null;
    var $context = [
        [
            'records' => 1000,
            'paging' => true,
            'name' => 'person',
            'key' => 'id',
            'query' => [['field' => 'id', 'value' => '1', 'operator' => '=']],
            'sort' => [['field' => 'id', 'direction' => 'asc'],],
            'send-mail' => [
                'driver' => 'mail',
                'read' => [
                    'from-constant' => 'msyk@msyk.net',
                    'to-constant' => 'msyk@mac.com',
                    'subject-constant' => 'Mail From INTER-Mediator',
                    'body-constant' => 'INTER-Mediator Sample. testSendMailOnRead.',
                ],
                'update' => [
                    'from-constant' => 'msyk@msyk.net',
                    'to-constant' => 'msyk@mac.com',
                    'subject-constant' => 'Mail From INTER-Mediator',
                    'body-constant' => 'INTER-Mediator Sample. testSendMailOnUpdate. [@@name@@] [@@name@@]',
                ],
                'create' => [
                    'from-constant' => 'msyk@msyk.net',
                    'to-constant' => 'msyk@mac.com',
                    'subject' => 'id',
                    'body-constant' => 'INTER-Mediator Sample. testSendMailOnCreate. [@@id@@] [@@name@@]',
                ],
            ],
        ],
    ];
    var $option = [
        'smtp' => [
            'server' => 'msyk.sakura.ne.jp',
            'port' => 587,
            'username' => 'msyktest@msyk.net',
            'password' => 'quo3aiMavoM5vohl',
        ],
    ];

    /*
      * This SMTP account won't access any time. Masayuki Nii has this account, and he will be activate it
      * just on his testing only. Usually this password might be wrong.
      */

    public function testSendMailOnRead()
    {
        $this->db_proxy = new Proxy(true);
        $this->db_proxy->initialize($this->context, $this->option, ['db-class' => 'PDO',], 2, 'person');
        $result = $this->db_proxy->readFromDB();
        $recordCount = $this->db_proxy->countQueryResult();

//        var_export($result);
//        var_export($this->db_proxy->logger->getDebugMessages());
        var_export($this->db_proxy->logger->getDebugMessages());

        $this->assertEquals($recordCount, 1, "The queried record has to be just one.");
    }

    public function testSendMailOnUpdate()
    {
        $this->db_proxy = new Proxy(true);
        $this->db_proxy->initialize($this->context, $this->option, ['db-class' => 'PDO',], 2, 'person');
        $this->db_proxy->dbSettings->addExtraCriteria("id", "=", 1);
        $this->db_proxy->dbSettings->addTargetField("name");
        $this->db_proxy->dbSettings->addValue("Modified Name");
        $this->db_proxy->requireUpdatedRecord(true);
        $result = $this->db_proxy->updateDB(false);
        $result = $this->db_proxy->updatedRecord();


//        var_export($result);
//        var_export($this->db_proxy->logger->getDebugMessages());
        var_export($this->db_proxy->logger->getDebugMessages());

        $this->assertEquals(count($result), 1, "The queried record has to be just one.");
    }

    public function testSendMailOnCreate()
    {
        $this->db_proxy = new Proxy(true);
        $this->db_proxy->initialize($this->context, $this->option, ['db-class' => 'PDO',], 2, 'person');
        $this->db_proxy->requireUpdatedRecord(true);
        $newKeyValue = $this->db_proxy->createInDB();
        $result = $this->db_proxy->updatedRecord();

//        var_export($result);
//        var_export($this->db_proxy->logger->getDebugMessages());
        var_export($this->db_proxy->logger->getDebugMessages());

        $this->assertEquals(count($result), 1, "The queried record has to be just one.");
    }

}
