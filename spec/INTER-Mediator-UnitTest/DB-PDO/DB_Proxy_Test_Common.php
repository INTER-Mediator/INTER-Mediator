<?php

use INTERMediator\DB\Proxy;
use PHPUnit\Framework\TestCase;
use INTERMediator\DB\UseSharedObjects;
use INTERMediator\DB\Extending\AfterRead;
use INTERMediator\DB\Extending\AfterUpdate;
use INTERMediator\DB\Extending\AfterCreate;
use INTERMediator\DB\Proxy_ExtSupport;

abstract class DB_Proxy_Test_Common extends TestCase
{
    use Proxy_ExtSupport;

    public string $schemaName;
    public Proxy $db_proxy;
    public ?array $dataSource;
    public ?array $options;
    public ?array $dbSpec;

    abstract function dbProxySetupForAccess(string $contextName, int $maxRecord, int $hasExtend = 0): void;

    abstract function dbProxySetupForAuthAccess(string $contextName, int $maxRecord, $subContextName = null): void;

    function setUp(): void
    {
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');
    }

    /**
     * #[runInSeparateProcess]
     * #[preserveGlobalState(false)]
     */
    function test___construct()
    {
        $this->dbProxySetupForAuthAccess("person", 1);
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
        $this->dbProxySetupForAuthAccess("person", 1);
        $aGroup = $this->db_proxy->dbClass->authHandler->getAuthorizedGroups("read");
        $this->assertContains('group1', $aGroup);
        $this->assertContains('group2', $aGroup);
        $this->assertNotContains('group3', $aGroup);
    }

    function testAuthUser()
    {
        $this->dbProxySetupForAuthAccess("person", 1);
        $aGroup = $this->db_proxy->dbClass->authHandler->getAuthorizedUsers("read");
        $this->assertContains('user1', $aGroup);
        $this->assertNotContains('user2', $aGroup);
        $this->assertNotContains('user3', $aGroup);
        $this->assertNotContains('user4', $aGroup);
        $this->assertNotContains('user5', $aGroup);
    }

    function testAdvisorClassOnRead()
    {
        $this->dbProxySetupForAccess("person", 1);
//        $msg = $this->db_proxy->logger->clearLogs();
        $result = $this->db_proxy->readFromDB();
//        var_dump($result);
//        $msg = $this->db_proxy->logger->getErrorMessages();
//        var_dump($msg);
//        $msg = $this->db_proxy->logger->getDebugMessages();
//        var_dump($msg);
        $recordCount = $this->db_proxy->countQueryResult();
        $this->assertTrue(is_array($result) && count($result) == 1, "After the query, just one should be retrieved.");
        $this->assertTrue($recordCount == 1, "After the query, just one should be retrieved.");
        $this->assertTrue($result[0]["id"] == 3, "Field value is not same as the definition.");
        $this->assertFalse(isset($result[0]["adding"]), "Field adding doesn't exist.");

        $this->dbProxySetupForAccess("person", 3, 1);
        $result = $this->db_proxy->readFromDB();
        $recordCount = $this->db_proxy->countQueryResult();
        $this->assertTrue(is_array($result) && count($result) == 1, "After the query, just one should be retrieved.");
        $this->assertTrue($recordCount == 1, "After the query, just one should be retrieved.");
        $this->assertTrue($result[0]["id"] == 3, "Field value is not same as the definition.");
        $this->assertTrue(isset($result[0]["adding"]), "Field adding exists.");
        $this->assertTrue($result[0]["adding"] == 999, "Field adding has the value 999.");
    }

    function testAdvisorClassOnUpdate()
    {
        $this->advisorClassOnUpdate(1);
    }

    function testAdvisorClassOnUpdateNew()
    {
        $this->advisorClassOnUpdate(2);
    }

    private function advisorClassOnUpdate($classNum)
    {
        $isPgsql = (strpos($this->dbSpec['dsn'], 'pgsql') === 0);
        $dataSrcPgsql = [['name' => "testtable",
            'view' => "{$this->schemaName}testtable",
            'table' => "{$this->schemaName}testtable",
            'key' => 'id'/*, 'sequence' => "{$this->schemaName}serial"*/]];

        $this->dbProxySetupForAccess("person", 1, true);
//        $this->db_proxy->logger->clearLogs();

        $this->setTestMode();
        $this->setFixedKey('id');
        $this->dbInit(null, null, $this->dbSpec);
        $testResult = $this->dbRead("testtable", null, null, $isPgsql ? $dataSrcPgsql : null);

        $countTTBefore = count($testResult);
        $this->assertTrue($countTTBefore >= 0, "Exist test table.");

        $nameValue = random_int(10000000, 99999999);
        $addressValue = random_int(10000000, 99999999);
        $pkValue = 2;
        $this->dbProxySetupForAccess("person", 1, $classNum);
        $this->db_proxy->dbSettings->addExtraCriteria("id", "=", $pkValue);
        $this->db_proxy->dbSettings->addTargetField("name");
        $this->db_proxy->dbSettings->addValue($nameValue);
        $this->db_proxy->dbSettings->addTargetField("address");
        $this->db_proxy->dbSettings->addValue($addressValue);
        $this->db_proxy->requireUpdatedRecord(true);

//        $msg = $this->db_proxy->logger->clearLogs();

        $result = $this->db_proxy->updateDB(false);
        $updatedResult = $this->db_proxy->getUpdatedRecord();
//        var_dump($updatedResult);
        $this->assertTrue($updatedResult != null, "Update record should be exists.");
        $this->assertTrue(count($updatedResult) == 1, "It should be just one record.");
        $this->assertTrue($updatedResult[0]["name"] == $nameValue, "Field value is not same as the definition.");
        $this->assertTrue($updatedResult[0]["address"] == $addressValue, "Field value is not same as the definition.");
        $this->assertTrue($updatedResult[0]["mail"] > 0, "Mail field has a value.");

        $testResult = $this->dbRead("testtable", null, null, $isPgsql ? $dataSrcPgsql : null);
        $countTTAfter = count($testResult);
        $this->assertTrue(($countTTAfter - $countTTBefore) == 1, "The testtable has one more record.");

        if ($isPgsql) {
            $testResult = $this->dbRead("{$this->schemaName}testtable", ['id' => $updatedResult[0]["mail"]], null, $dataSrcPgsql);
        } else {
            $testResult = $this->dbRead("testtable", ['id' => $updatedResult[0]["mail"]]);
        }

        //var_dump($updatedResult);
//        $msg = $this->db_proxy->logger->getErrorMessages();
//        var_dump($msg);
//        $msg = $this->db_proxy->logger->getDebugMessages();
//        var_dump($msg);

        $this->assertTrue(count($testResult) == 1, "The testtable has one more record.");
        $this->assertTrue($testResult[0]['vc1'] == $nameValue, "The testtable has one more record.");
        $this->assertTrue($testResult[0]['vc2'] == $addressValue, "The testtable has one more record.");
    }

    function testAdvisorClassOnCreate()
    {
        $this->advisorClassOnCreate(1);
    }

    function testAdvisorClassOnCreateNew()
    {
        $this->advisorClassOnCreate(2);
    }

    private function advisorClassOnCreate($classNum)
    {
        $isPgsql = (strpos($this->dbSpec['dsn'], 'pgsql') === 0);
        $dataSrcPgsql = [['name' => "testtable",
            'view' => "{$this->schemaName}testtable",
            'table' => "{$this->schemaName}testtable",
            'extending-class' => $classNum == 1 ? "AdvisorSample" : "AdvisorSampleNew",
            'key' => 'id', /*'sequence' => "{$this->schemaName}serial"*/]];
        $dataSrcOthers = [['name' => "testtable",
            'view' => "testtable",
            'table' => "testtable",
            'extending-class' => $classNum == 1 ? "AdvisorSample" : "AdvisorSampleNew",
            'key' => 'id', /*'sequence' => 'im_sample.serial'*/]];

        $this->dbProxySetupForAccess("person", 1, $classNum);
//        $msg = $this->db_proxy->logger->clearLogs();

        $this->setTestMode();
        $this->setFixedKey('id');
        $this->dbInit(null, null, $this->dbSpec);
        $testResult = $this->dbRead("testtable", null, null,
            $isPgsql ? $dataSrcPgsql : $dataSrcOthers);

        $countTTBefore = count($testResult);
        $this->assertTrue($countTTBefore >= 0, "Exist test table.");

        $nameValue = random_int(10000000, 99999999);
        $addressValue = random_int(10000000, 99999999);
        $testResult = $this->dbCreate("testtable", ['vc1' => $nameValue, 'vc2' => $addressValue],
            $isPgsql ? $dataSrcPgsql : $dataSrcOthers);
//        var_dump($testResult);
//        $msg = $this->db_proxy->logger->getErrorMessages();
//        var_dump($msg);
//        $msg = $this->db_proxy->logger->getDebugMessages();
//        var_dump($msg);
        $this->assertNotNull($testResult, "Update record should be exists.");
        $this->assertTrue(count($testResult) == 1, "It should be just one record.");
        $this->assertTrue($testResult[0]["vc1"] == $nameValue, "Field value is not same as the definition.");
        $this->assertTrue($testResult[0]["vc2"] == $addressValue, "Field value is not same as the definition.");
        $this->assertTrue($testResult[0]["num1"] == $nameValue + 1, "Field value is not same as the definition.");
        $this->assertTrue($testResult[0]["num2"] == $addressValue + 1, "Field value is not same as the definition.");

        $testResult = $this->dbRead("testtable", null, null, $isPgsql ? $dataSrcPgsql : null);
        $countTTAfter = count($testResult);
        $this->assertTrue(($countTTAfter - $countTTBefore) == 1, "The testtable has one more record.");
    }
}

class AdvisorSample extends UseSharedObjects implements AfterRead, AfterUpdate, AfterCreate
{
    use Proxy_ExtSupport;

    public function doAfterReadFromDB($result): ?array
    {
        $modResult = [];
        foreach ($result as $record) {
            $record['adding'] = 999;
            $modResult[] = $record;
        }
        return $modResult;
    }

    public function doAfterUpdateToDB($output): ?array
    {
        $result = $this->dbClass->getUpdatedRecord();
        $nameValue = $result[0]["name"];
        $addressValue = $result[0]["address"];
        $this->setTestMode();
        $this->setFixedKey('id');
        $dbSpec = $this->dbSettings->getDbSpec();
        $this->dbInit(null, null, $dbSpec, 2);
        if (strpos($dbSpec['dsn'], 'pgsql') === 0) { // In case of PostgreSQL
            $result = $this->dbCreate("testtable",
                ['vc1' => $nameValue, 'vc2' => $addressValue],
                [['name' => "testtable", 'view' => "im_sample.testtable",
                    'table' => "im_sample.testtable", 'key' => 'id', /*'sequence' => 'im_sample.serial',*/]]);
        } else {
            $result = $this->dbCreate("testtable", ['vc1' => $nameValue, 'vc2' => $addressValue]);
        }
        $this->dbClass->setDataToUpdatedRecord('mail', $result[0]['id'], 0);
        return $output;
    }

    public function doAfterCreateToDB($output): ?array
    {
        $result = $this->dbClass->getUpdatedRecord();
        $nameValue = $result[0]["vc1"];
        $addressValue = $result[0]["vc2"];
        $this->dbClass->setDataToUpdatedRecord('num1', intval($nameValue) + 1, 0);
        $this->dbClass->setDataToUpdatedRecord('num2', intval($addressValue) + 1, 0);
        return $output;
    }
}

class AdvisorSampleNew extends UseSharedObjects implements AfterRead, AfterUpdate, AfterCreate
{
    use Proxy_ExtSupport;

    public function doAfterReadFromDB($result): ?array
    {
        $modResult = [];
        foreach ($result as $record) {
            $record['adding'] = 999;
            $modResult[] = $record;
        }
        return $modResult;
    }

    public function doAfterUpdateToDB($result): ?array
    {
        $nameValue = $result[0]["name"];
        $addressValue = $result[0]["address"];
        $this->setTestMode();
        $this->setFixedKey('id');
        $dbSpec = $this->dbSettings->getDbSpec();
        $this->dbInit(null, null, $dbSpec, 2);
        if (strpos($dbSpec['dsn'], 'pgsql') === 0) { // In case of PostgreSQL
            $resultCreate = $this->dbCreate("testtable",
                ['vc1' => $nameValue, 'vc2' => $addressValue],
                [['name' => "testtable", 'view' => "im_sample.testtable", 'table' => "im_sample.testtable",
                    'key' => 'id'/*, 'sequence' => 'im_sample.serial',*/]]);
        } else {
            $resultCreate = $this->dbCreate("testtable", ['vc1' => $nameValue, 'vc2' => $addressValue]);
        }
        $result[0]["mail"] = $resultCreate[0]['id'];
        return $result;
    }

    public function doAfterCreateToDB($result): ?array
    {
        $nameValue = $result[0]["vc1"];
        $addressValue = $result[0]["vc2"];
        $result[0]["num1"] = intval($nameValue) + 1;
        $result[0]["num2"] = intval($addressValue) + 1;
        return $result;
    }

}