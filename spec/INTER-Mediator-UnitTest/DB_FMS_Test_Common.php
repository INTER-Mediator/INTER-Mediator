<?php
/**
 * DB_FMS_Test_Common file
 */
use \PHPUnit\Framework\TestCase;

class DB_FMS_Test_Common extends TestCase
{
    protected $db_proxy;
    protected $schemaName = "";

    function setUp()
    {
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');
    }

    /**
     * @runInSeparateProcess
     */
    public function testQueriedEntity()
    {
        $layoutName = 'person_layout';
        $expected = $layoutName;

        $this->dbProxySetupForAccess($layoutName, 1);
        $this->db_proxy->readFromDB($layoutName);
        $this->assertEquals($expected, $this->db_proxy->dbClass->notifyHandler->queriedEntity());
    }

    /**
     * @runInSeparateProcess
     */
    public function testQueriedCondition()
    {
        $layoutName = 'person_layout';
        $expected = '-db=TestDB&-lay=person_layout&-lay.response=person_layout&-max=1&-sortfield.1=id&-sortorder.1=ascend&-findall';
        $this->dbProxySetupForAccess($layoutName, 1);
        if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') {
            $expected = '/fmi/rest/api/find/TestDB/person_layout';
        }
        $this->db_proxy->readFromDB($layoutName);
        $this->assertEquals($expected, $this->db_proxy->dbClass->notifyHandler->queriedCondition());
    }

    /**
     * @runInSeparateProcess
     */
    public function testExecuteScriptsforLoading()
    {
        if ((float)phpversion() >= 5.3) {
            $layoutName = 'person_layout';
            $this->dbProxySetupForAccess($layoutName, 1);
            $this->db_proxy->readFromDB($layoutName);
            $this->reflectionClass = new ReflectionClass(get_class($this->db_proxy->dbClass));
            if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_FX') {
                $method = $this->reflectionClass->getMethod('executeScriptsforLoading');
            } else if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') {
                $method = $this->reflectionClass->getMethod('executeScripts');
            }
            $method->setAccessible(true);

            $scriptContext = array('script' =>
                array(
                    'db-operation' => 'load',
                    'situation' => 'post',
                )
            );
            $expected = '';
            $this->assertEquals($expected, $method->invokeArgs($this->db_proxy->dbClass, array($scriptContext)));

            $scriptContext = array('script' =>
                array(
                    'db-operation' => 'load',
                    'definition' => 'testscript',
                )
            );
            $expected = '';
            $this->assertEquals($expected, $method->invokeArgs($this->db_proxy->dbClass, array($scriptContext)));

            $scriptContext = array('script' =>
                array(
                    'db-operation' => 'load',
                    'situation' => 'post',
                    'definition' => 'testscript',
                )
            );
            $expected = '&-script=testscript';
            if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') {
                $expected = array('script' => 'testscript');
            }
            $this->assertEquals($expected, $method->invokeArgs($this->db_proxy->dbClass, array($scriptContext)));

            $scriptContext = array('script' =>
                array(
                    'db-operation' => 'read',
                    'situation' => 'post',
                    'definition' => 'test&script',
                    'parameter' => '',
                )
            );
            $expected = '&-script=testscript';
            if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') {
                $expected = array('script' => 'testscript');
            }
            $this->assertEquals($expected, $method->invokeArgs($this->db_proxy->dbClass, array($scriptContext)));

            $scriptContext = array('script' =>
                array(
                    'db-operation' => 'load',
                    'situation' => 'post',
                    'definition' => 'test&script',
                    'parameter' => '1',
                )
            );
            $expected = '&-script=testscript&-script.param=1';
            if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') {
                $expected = array('script' => 'testscript', 'script.param' => '1');
            }
            $this->assertEquals($expected, $method->invokeArgs($this->db_proxy->dbClass, array($scriptContext)));

            $scriptContext = array('script' =>
                array(
                    'db-operation' => 'load',
                    'situation' => 'pre',
                    'definition' => 'testscript',
                )
            );
            $expected = '&-script.prefind=testscript';
            if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') {
                $expected = array('script.prerequest' => 'testscript');
            }
            $this->assertEquals($expected, $method->invokeArgs($this->db_proxy->dbClass, array($scriptContext)));

            $scriptContext = array('script' =>
                array(
                    'db-operation' => 'read',
                    'situation' => 'pre',
                    'definition' => 'testscript',
                    'parameter' => '',
                )
            );
            $expected = '&-script.prefind=testscript';
            if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') {
                $expected = array('script.prerequest' => 'testscript');
            }
            $this->assertEquals($expected, $method->invokeArgs($this->db_proxy->dbClass, array($scriptContext)));

            $scriptContext = array('script' =>
                array(
                    'db-operation' => 'load',
                    'situation' => 'pre',
                    'definition' => 'testscript',
                    'parameter' => '1&',
                )
            );
            $expected = '&-script.prefind=testscript&-script.prefind.param=1';
            if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') {
                $expected = array('script.prerequest' => 'testscript', 'script.prerequest.param' => '1');
            }
            $this->assertEquals($expected, $method->invokeArgs($this->db_proxy->dbClass, array($scriptContext)));

            $scriptContext = array('script' =>
                array(
                    'db-operation' => 'load',
                    'situation' => 'presort',
                    'definition' => 'testscript',
                )
            );
            $expected = '&-script.presort=testscript';
            if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') {
                $expected = array('script.presort' => 'testscript');
            }
            $this->assertEquals($expected, $method->invokeArgs($this->db_proxy->dbClass, array($scriptContext)));

            $scriptContext = array('script' =>
                array(
                    'db-operation' => 'read',
                    'situation' => 'presort',
                    'definition' => 'testscript',
                    'parameter' => '',
                )
            );
            $expected = '&-script.presort=testscript';
            if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') {
                $expected = array('script.presort' => 'testscript');
            }
            $this->assertEquals($expected, $method->invokeArgs($this->db_proxy->dbClass, array($scriptContext)));

            $scriptContext = array('script' =>
                array(
                    'db-operation' => 'load',
                    'situation' => 'presort',
                    'definition' => 'testscript',
                    'parameter' => '1',
                )
            );
            $expected = '&-script.presort=testscript&-script.presort.param=1';
            if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') {
                $expected = array('script.presort' => 'testscript', 'script.presort.param' => '1');
            }
            $this->assertEquals($expected, $method->invokeArgs($this->db_proxy->dbClass, array($scriptContext)));
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testIsPossibleOperator()
    {
        $this->dbProxySetupForAccess("person_layout", 1);
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOperator('eq'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOperator('cn'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOperator('bw'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOperator('ew'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOperator('gt'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOperator('gte'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOperator('gte'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOperator('lt'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOperator('lte'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOperator('neq'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOperator('and'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOperator('or'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOperator('AND'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOperator('OR'));
        $this->assertFalse($this->db_proxy->dbClass->specHandler->isPossibleOperator('='));
    }

    /**
     * @runInSeparateProcess
     */
    public function testIsPossibleOrderSpecifier()
    {
        $this->dbProxySetupForAccess("person_layout", 1);
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOrderSpecifier('ascend'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOrderSpecifier('descend'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOrderSpecifier('asc'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOrderSpecifier('desc'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOrderSpecifier('ASCEND'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOrderSpecifier('DESCEND'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOrderSpecifier('ASC'));
        $this->assertTrue($this->db_proxy->dbClass->specHandler->isPossibleOrderSpecifier('DESC'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testNormalizedCondition()
    {
        $this->dbProxySetupForAccess("person_layout", 1);

        $condition = array(
            'field' => 'f1',
            'operator' => '=',
            'value' => 'test',
        );
        $expected = array(
            'field' => 'f1',
            'operator' => 'eq',
            'value' => 'test',
        );
        $this->assertEquals($expected, $this->db_proxy->dbClass->normalizedCondition($condition));

        $condition['operator'] = '!=';
        $expected['operator'] = 'neq';
        $this->assertEquals($expected, $this->db_proxy->dbClass->normalizedCondition($condition));

        $condition['operator'] = '<';
        $expected['operator'] = 'lt';
        $this->assertEquals($expected, $this->db_proxy->dbClass->normalizedCondition($condition));

        $condition['operator'] = '<=';
        $expected['operator'] = 'lte';
        $this->assertEquals($expected, $this->db_proxy->dbClass->normalizedCondition($condition));

        $condition['operator'] = '>';
        $expected['operator'] = 'gt';
        $this->assertEquals($expected, $this->db_proxy->dbClass->normalizedCondition($condition));

        $condition['operator'] = '>=';
        $expected['operator'] = 'gte';
        $this->assertEquals($expected, $this->db_proxy->dbClass->normalizedCondition($condition));

        $condition['operator'] = 'match*';
        $expected['operator'] = 'bw';
        $this->assertEquals($expected, $this->db_proxy->dbClass->normalizedCondition($condition));

        $condition['operator'] = '*match';
        $expected['operator'] = 'ew';
        $this->assertEquals($expected, $this->db_proxy->dbClass->normalizedCondition($condition));

        $condition['operator'] = '*match*';
        $expected['operator'] = 'cn';
        $this->assertEquals($expected, $this->db_proxy->dbClass->normalizedCondition($condition));

        $condition = array(
            'operator' => '=',
            'value' => 'test',
        );
        $expected = array(
            'field' => '',
            'operator' => 'eq',
            'value' => 'test',
        );
        $this->assertEquals($expected, $this->db_proxy->dbClass->normalizedCondition($condition));

        $condition = array(
            'field' => 'f2',
            'operator' => '=',
        );
        $expected = array(
            'field' => 'f2',
            'operator' => 'eq',
            'value' => '',
        );
        $this->assertEquals($expected, $this->db_proxy->dbClass->normalizedCondition($condition));

        $condition = array(
            'operator' => '',
        );
        $expected = array(
            'field' => '',
            'value' => '',
            'operator' => '',
        );
        $this->assertEquals($expected, $this->db_proxy->dbClass->normalizedCondition($condition));
    }

    /**
     * @runInSeparateProcess
     */
    public function testAdjustSortDirection()
    {
        if ((float)phpversion() >= 5.3) {
            $layoutName = 'person_layout';

            $this->dbProxySetupForAccess($layoutName, 1);
            $this->db_proxy->readFromDB($layoutName);

            $this->reflectionClass = new ReflectionClass(get_class($this->db_proxy->dbClass));
            $method = $this->reflectionClass->getMethod('_adjustSortDirection');
            $method->setAccessible(true);

            $this->assertEquals('ascend', $method->invokeArgs($this->db_proxy->dbClass, array('ASC')));
            $this->assertEquals('ascend', $method->invokeArgs($this->db_proxy->dbClass, array('asc')));
            $this->assertEquals('descend', $method->invokeArgs($this->db_proxy->dbClass, array('DESC')));
            $this->assertEquals('descend', $method->invokeArgs($this->db_proxy->dbClass, array('desc')));
            $this->assertEquals('default', $method->invokeArgs($this->db_proxy->dbClass, array('default')));
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testIsNullAcceptable()
    {
        $layoutName = 'person_layout';

        $this->dbProxySetupForAccess($layoutName, 1);
        $this->db_proxy->readFromDB($layoutName);
        $this->assertFalse($this->db_proxy->dbClass->specHandler->isNullAcceptable());
    }

    /**
     * @runInSeparateProcess
     */
    public function testQuery1_singleRecord()
    {
        $this->dbProxySetupForAccess("person_layout", 1);
        $result = $this->db_proxy->readFromDB();
        $recordCount = $this->db_proxy->countQueryResult();
        $this->assertTrue(count($result) == 1, "After the query, just one should be retrieved.");
        $this->assertEquals(3, $recordCount, "This table contanins 3 records");
        $this->assertTrue($result[0]["id"] == 1, "Field value is not same as the definition.");
        //        var_export($this->db_proxy->logger->getAllErrorMessages());
        //        var_export($this->db_proxy->logger->getDebugMessage());
    }

    /**
     * @runInSeparateProcess
     */
    public function testQuery2_multipleRecord()
    {
        $this->dbProxySetupForAccess("person_layout", 1000000);
        $result = $this->db_proxy->readFromDB();
        $recordCount = $this->db_proxy->countQueryResult();
        $this->assertTrue(count($result) == 3, "After the query, some records should be retrieved.");
        $this->assertEquals(3, $recordCount, "This table contanins 3 records");
        $this->assertTrue($result[2]["name"] === 'Anyone', "Field value is not same as the definition.");
        $this->assertTrue($result[2]["id"] == 3, "Field value is not same as the definition.");

        //        var_export($this->db_proxy->logger->getAllErrorMessages());
        //        var_export($this->db_proxy->logger->getDebugMessage());
    }

    /**
     * @runInSeparateProcess
     */
    public function testQuery_findPostalCodeWithSimpleSearchCriteria()
    {
        $this->dbProxySetupForAccess('postalcode', 1000000);
        $this->db_proxy->dbSettings->addExtraCriteria('f3', 'cn', '167');
        $result = $this->db_proxy->readFromDB();
        $totalCount = $this->db_proxy-> getTotalCount();
        $this->assertEquals(15, count($result));
        $this->assertEquals(3654, $totalCount);
    }

    /**
     * @runInSeparateProcess
     */
    public function testQuery_findPostalCodeWithLimit()
    {
        $limit = 5;
        $this->dbProxySetupForAccess('postalcode', 1000000);
        $this->db_proxy->dbSettings->setDataSource(array(array('records' => 1000000, 'name' => 'postalcode', 'key' => 'id', 'records' => $limit)));
        $this->db_proxy->dbSettings->addExtraSortKey('id', 'asc');
        $result = $this->db_proxy->readFromDB();
        $totalCount = $this->db_proxy-> getTotalCount();
        $this->assertEquals($limit, count($result));
        $this->assertEquals(3654, $totalCount);
        $this->assertEquals('1000000', $result[0]['f3']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testQuery_findPostalCodeWithQueryKey()
    {
        $this->dbProxySetupForAccess('postalcode', 1000000);
        $this->db_proxy->dbSettings->setDataSource(array(array('records' => 1000000, 'name' => 'postalcode', 'key' => 'id', 'query' => array(array('field' => 'f3', 'value' => '167', 'operator' => 'bw')))));
        $this->db_proxy->dbSettings->addExtraSortKey('id', 'asc');
        $result = $this->db_proxy->readFromDB();
        $totalCount = $this->db_proxy-> getTotalCount();
        $this->assertEquals(15, count($result));
        $this->assertEquals(3654, $totalCount);
        $this->assertEquals('1670032', $result[0]['f3']);

        $this->dbProxySetupForAccess('postalcode', 1000000);
        $this->db_proxy->dbSettings->setDataSource(array(array('records' => 1000000, 'name' => 'postalcode', 'key' => 'id', 'query' => array(array('field' => 'f3', 'value' => '167', 'operator' => 'bw'), array('field' => 'f9', 'value' => '天沼', 'operator' => 'neq')))));
        $this->db_proxy->dbSettings->addExtraSortKey('id', 'asc');
        $result = $this->db_proxy->readFromDB();
        $totalCount = $this->db_proxy-> getTotalCount();
        $this->assertEquals(14, count($result));
        $this->assertEquals(3654, $totalCount);
        $this->assertEquals('1670021', $result[0]['f3']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testQuery_findPostalCodeWithQueryKeyAndSearchCriteria()
    {
        $this->dbProxySetupForAccess('postalcode', 1000000);
        $this->db_proxy->dbSettings->setDataSource(array(array('records' => 1000000, 'name' => 'postalcode', 'key' => 'id', 'query' => array(array('field' => 'f3', 'value' => '022', 'operator' => 'ew')))));
        $this->db_proxy->dbSettings->addExtraSortKey('id', 'asc');
        $this->db_proxy->dbSettings->addExtraCriteria('f9', 'cn', '井草');
        $result = $this->db_proxy->readFromDB();
        $totalCount = $this->db_proxy-> getTotalCount();
        $this->assertEquals(1, count($result));
        $this->assertEquals(3654, $totalCount);
        $this->assertEquals('1670022', $result[0]['f3']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testQuery_findPostalCodeWithSimpleSearchCriteriaAndLimit()
    {
        $limit = 5;
        $this->dbProxySetupForAccess('postalcode', 1000000);
        $this->db_proxy->dbSettings->setDataSource(array(array('records' => 1000000, 'name' => 'postalcode', 'key' => 'id', 'records' => $limit)));
        $this->db_proxy->dbSettings->addExtraSortKey('id', 'asc');
        $this->db_proxy->dbSettings->addExtraCriteria('f3', 'cn', '167');
        $result = $this->db_proxy->readFromDB();
        $totalCount = $this->db_proxy-> getTotalCount();
        $this->assertEquals($limit, count($result));
        $this->assertEquals(3654, $totalCount);
        $this->assertEquals('1670032', $result[0]['f3']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testQuery_findPostalCodeWithSimpleSearchCriteriaAndSorting()
    {
        $this->dbProxySetupForAccess('postalcode', 1000000);
        $this->db_proxy->dbSettings->addExtraCriteria('f3', 'cn', '167');
        $this->db_proxy->dbSettings->addExtraSortKey('f3', 'desc');
        $result = $this->db_proxy->readFromDB();
        $totalCount = $this->db_proxy-> getTotalCount();
        $this->assertEquals(15, count($result));
        $this->assertEquals(3654, $totalCount);
        $this->assertEquals('1670032', $result[0]['f3']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testQuery_findPostalCodeWithAndSearchCriteria()
    {
        $this->dbProxySetupForAccess('postalcode', 1000000);
        $this->db_proxy->dbSettings->addExtraCriteria('f3', 'bw', '167');
        $this->db_proxy->dbSettings->addExtraCriteria('f9', 'cn', '荻窪');
        $result = $this->db_proxy->readFromDB();
        $totalCount = $this->db_proxy-> getTotalCount();
        $this->assertEquals(2, count($result));
        $this->assertEquals(3654, $totalCount);
    }

    /**
     * @runInSeparateProcess
     */
    public function testQuery_findPostalCodeWithOrSearchCriteria()
    {
        $this->dbProxySetupForAccess('postalcode', 1000000);
        $this->db_proxy->dbSettings->addExtraCriteria('f3', 'bw', '167');
        $this->db_proxy->dbSettings->addExtraCriteria('f9', 'ew', '荻窪');
        $this->db_proxy->dbSettings->addExtraCriteria('__operation__', 'ex', '');
        $result = $this->db_proxy->readFromDB();
        $totalCount = $this->db_proxy-> getTotalCount();
        $this->assertEquals(15, count($result));
        $this->assertEquals(3654, $totalCount);
    }

    /**
     * @runInSeparateProcess
     */
    public function testQuery_findPostalCodeWithSearchCriteriaByRecId()
    {
        $this->dbProxySetupForAccess('postalcode', 1);
        $result = $this->db_proxy->readFromDB();
        $totalCount = $this->db_proxy-> getTotalCount();
        $this->assertEquals(1, count($result));
        $this->assertEquals(3654, $totalCount);


        $this->dbProxySetupForAccess('postalcode', 1000000);
        if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_FX') {
            $recId = $result[0]['-recid'];
            $this->db_proxy->dbSettings->addExtraCriteria('-recid', 'eq', $recId);
        } else if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') {
            foreach ($result as $record) {
                $recId = $record['recordId'];
                $this->db_proxy->dbSettings->addExtraCriteria('recordId', 'eq', $recId);
            }
        }
        $result = $this->db_proxy->readFromDB();
        $totalCount = $this->db_proxy-> getTotalCount();
        $this->assertEquals(1, count($result));
        $this->assertEquals(3654, $totalCount);
        $this->assertEquals('1000000', $result[0]['f3']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testQuery_findPostalCodeWithOrSearchCriteriaWithSameField()
    {
        $this->dbProxySetupForAccess('postalcode', 1000000);
        $this->db_proxy->dbSettings->addExtraCriteria('f3', 'bw', '167');
        $this->db_proxy->dbSettings->addExtraCriteria('f3', 'ew', '32');
        $this->db_proxy->dbSettings->addExtraCriteria('__operation__', 'ex', '');
        $result = $this->db_proxy->readFromDB();
        $totalCount = $this->db_proxy-> getTotalCount();
        $this->assertEquals(93, count($result));
        $this->assertEquals(3654, $totalCount);
    }

    /**
     * @runInSeparateProcess
     */
    public function testInsertAndUpdateRecord()
    {
        $this->dbProxySetupForAccess("contact_to", 1000000);
        $this->db_proxy->requireUpdatedRecord(true);
        $newKeyValue = $this->db_proxy->createInDB(true);
        $this->assertTrue($newKeyValue > 0, "If a record was created, it returns the new primary key value.");
        $createdRecord = $this->db_proxy->updatedRecord();
        $this->assertTrue($createdRecord != null, "Created record should be exists.");
        $this->assertTrue(count($createdRecord) == 1, "It should be just one record.");

        $this->dbProxySetupForAccess("person_layout", 1000000);
        $this->db_proxy->requireUpdatedRecord(true);
        $newKeyValue = $this->db_proxy->createInDB(true);
        $this->assertTrue($newKeyValue > 0, "If a record was created, it returns the new primary key value.");
        $createdRecord = $this->db_proxy->updatedRecord();
        $this->assertTrue($createdRecord != null, "Created record should be exists.");
        $this->assertTrue(count($createdRecord) == 1, "It should be just one record.");

        $nameValue = "unknown, oh mygod!";
        $addressValue = "anyplace, who knows!";
        $this->dbProxySetupForAccess("person_layout", 1000000);
        $this->db_proxy->dbSettings->addExtraCriteria("id", "=", $newKeyValue);
        $this->db_proxy->dbSettings->addTargetField("name");
        $this->db_proxy->dbSettings->addValue($nameValue);
        $this->db_proxy->dbSettings->addTargetField("address");
        $this->db_proxy->dbSettings->addValue($addressValue);
        $this->db_proxy->requireUpdatedRecord(true);
        $result = $this->db_proxy->updateDB("person_layout", true);
        $createdRecord = $this->db_proxy->updatedRecord();
        $this->assertTrue($createdRecord != null, "Update record should be exists.");
        $this->assertTrue(count($createdRecord) == 1, "It should be just one record.");
        $this->assertTrue($createdRecord[0]["name"] === $nameValue, "Field value is not same as the definition.");
        $this->assertTrue($createdRecord[0]["address"] === $addressValue, "Field value is not same as the definition.");

        $this->dbProxySetupForAccess("person_layout", 1000000);
        $this->db_proxy->dbSettings->addExtraCriteria("id", "=", $newKeyValue);
        $result = $this->db_proxy->readFromDB();
        $this->assertTrue($result !== FALSE, "Found record should be exists.");
        $recordCount = $this->db_proxy->countQueryResult();
        $this->assertTrue(count($result) == 1, "It should be just one record.");
        $this->assertTrue($result[0]["name"] === $nameValue, "Field value is not same as the definition.");
        $this->assertTrue($result[0]["address"] === $addressValue, "Field value is not same as the definition.");

        //        var_export($this->db_proxy->logger->getAllErrorMessages());
        //        var_export($this->db_proxy->logger->getDebugMessage());

    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAuthUser1()
    {
        $testName = "Check time calc feature of PHP";
        $expiredDT = new DateTime('2012-02-13 11:32:40');
        $currentDate = new DateTime('2012-02-14 11:32:51');
        //    $expiredDT = new DateTime('2012-02-13 00:00:00');
        //    $currentDate = new DateTime('2013-04-13 01:02:03');
        $intervalDT = $expiredDT->diff($currentDate, true);
        // var_export($intervalDT);
        $calc = (($intervalDT->days * 24 + $intervalDT->h) * 60 + $intervalDT->i) * 60 + $intervalDT->s;
        //echo $calc;
        $this->assertTrue($calc === (11 + 3600 * 24), $testName);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAuthUser2()
    {
        $this->dbProxySetupForAuth();

        $testName = "Password Retrieving";
        $username = 'user1';
        $expectedPasswd = 'd83eefa0a9bd7190c94e7911688503737a99db0154455354';

        $retrievedPasswd = $this->db_proxy->dbClass->authHandler->authSupportRetrieveHashedPassword($username);
        //echo var_export($this->db_proxy->logger->getDebugMessage(), true);
        $this->assertEquals($expectedPasswd, $retrievedPasswd, $testName);

    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAuthUser3()
    {
        $this->dbProxySetupForAuth();

        $testName = "Salt retrieving";
        $username = 'user1';
        $retrievedSalt = $this->db_proxy->authSupportGetSalt($username);
        $this->assertEquals('54455354', $retrievedSalt, $testName);

    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAuthUser4()
    {
        $this->dbProxySetupForAuth();

        $testName = "Generate Challenge and Retrieve it";
        $username = 'user1';
        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->dbClass->authHandler->authSupportStoreChallenge($username, $challenge, "TEST");
        $this->assertEquals($challenge, $this->db_proxy->dbClass->authHandler->authSupportRetrieveChallenge($username, "TEST"), $testName);
        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->dbClass->authHandler->authSupportStoreChallenge($username, $challenge, "TEST");
        $this->assertEquals($challenge, $this->db_proxy->dbClass->authHandler->authSupportRetrieveChallenge($username, "TEST"), $testName);
        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->dbClass->authHandler->authSupportStoreChallenge($username, $challenge, "TEST");
        $this->assertEquals($challenge, $this->db_proxy->dbClass->authHandler->authSupportRetrieveChallenge($username, "TEST"), $testName);

    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAuthUser5()
    {
        $this->dbProxySetupForAuth();

        $testName = "Simulation of Authentication";
        $username = 'user1';
        $password = 'user1'; //'d83eefa0a9bd7190c94e7911688503737a99db0154455354';
        $uid = $this->db_proxy->dbClass->authHandler->authSupportGetUserIdFromUsername($username);

        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->dbClass->authHandler->authSupportStoreChallenge($uid, $challenge, "TEST");

        //        $challenge = $this->db_pdo->authHandler->authSupportRetrieveChallenge($username, "TEST");
        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));

        $hashedvalue = sha1($password . $retrievedSalt) . bin2hex($retrievedSalt);
        $calcuratedHash = hash_hmac('sha256', $hashedvalue, $challenge);

        $this->assertTrue(
            $this->db_proxy->checkAuthorization($username, $calcuratedHash, "TEST"), $testName);
    }

    /**
     * @runInSeparateProcess
     */
    public function testAuthByValidUser()
    {
        $this->dbProxySetupForAuth();

        $testName = 'Simulation of Authentication by Valid User';
        $username = 'user1';
        $password = 'user1'; //'d83eefa0a9bd7190c94e7911688503737a99db0154455354';
        $clientId = 'test1234test1234';

        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->saveChallenge($username, $challenge, $clientId);
        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));
        $hashedvalue = sha1($password . $retrievedSalt) . bin2hex($retrievedSalt);
        $calcuratedHash = hash_hmac('sha256', $hashedvalue, $challenge);

        $this->db_proxy->dbSettings->setCurrentUser($username);
        $this->db_proxy->dbSettings->setDataSourceName('person');
        $this->db_proxy->paramAuthUser = $username;
        $this->db_proxy->clientId = $clientId;
        $this->db_proxy->paramResponse = $calcuratedHash;

        $this->db_proxy->processingRequest('read');
        $result = $this->db_proxy->getDatabaseResult();
        $this->assertTrue(count($result) == $this->db_proxy->getDatabaseResultCount(), $testName);

        //based on INSERT person SET id=2,name='Someone',address='Tokyo, Japan',mail='msyk@msyk.net';
        if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_FX') {
            foreach ($result as $index => $record) {
                if ($record['id'] == 2) {
                    $this->assertTrue($result[1]['id'] == 2, $testName);
                    $this->assertTrue($result[1]['name'] == 'Someone', $testName);
                    $this->assertTrue($result[1]['address'] == 'Tokyo, Japan', $testName);
                }
            }
        } else if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') {
            // [WIP]
            if (!is_null($result)) {
                foreach ($result as $record) {
                    if ($record->id == 2) {
                        $this->assertTrue($result->id == 2, $testName);
                        $this->assertTrue($result->name == 'Someone', $testName);
                        $this->assertTrue($result->address == 'Tokyo, Japan', $testName);
                    }
                }
            }
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testAuthByInvalidUser()
    {
        $this->dbProxySetupForAuth();

        $testName = "Simulation of Authentication by Inalid User";
        $username = 'user2';
        $password = 'user2';
        $clientId = 'test1234test1234';

        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->saveChallenge($username, $challenge, $clientId);
        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));
        $hashedvalue = sha1($password . $retrievedSalt) . bin2hex($retrievedSalt);
        $calcuratedHash = hash_hmac('sha256', $hashedvalue, $challenge);

        $this->db_proxy->dbSettings->setCurrentUser($username);
        $this->db_proxy->dbSettings->setDataSourceName("person");
        $this->db_proxy->paramAuthUser = $username;
        $this->db_proxy->clientId = $clientId;
        $this->db_proxy->paramResponse = $calcuratedHash;

        $this->db_proxy->processingRequest("read");
        $this->assertTrue(is_null($this->db_proxy->getDatabaseResult()), $testName);
        $this->assertTrue(is_null($this->db_proxy->getDatabaseResultCount()), $testName);
        $this->assertTrue(is_null($this->db_proxy->getDatabaseTotalCount()), $testName);
        $this->assertTrue(is_null($this->db_proxy->getDatabaseResult()), $testName);
        $this->assertTrue($this->db_proxy->dbSettings->getRequireAuthentication(), $testName);
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAuthUser6()
    {
        $this->dbProxySetupForAuth();

        $testName = "Create New User and Authenticate";
        $username = "testuser1";
        $password = "testuser1";

        $addUserResult = $this->db_proxy->addUser($username, $password);
        //var_export($this->db_proxy->logger->getAllErrorMessages());
        //var_export($this->db_proxy->logger->getDebugMessage());
        $this->assertTrue($addUserResult);

        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));

        $clientId = "TEST";
        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->saveChallenge($username, $challenge, $clientId);

        $hashedvalue = sha1($password . $retrievedSalt) . bin2hex($retrievedSalt);
        //echo $hashedvalue;

        $this->assertTrue(
            $this->db_proxy->checkAuthorization($username, hash_hmac('sha256', $hashedvalue, $challenge), $clientId),
            $testName);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    function testUserGroup()
    {
        $this->dbProxySetupForAuth();

        $testName = "Resolve containing group";
        $groupArray = $this->db_proxy->dbClass->authHandler->authSupportGetGroupsOfUser('user1');
        //echo var_export($groupArray);
        $this->assertTrue(count($groupArray) > 0, $testName);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testNativeUser()
    {
        $this->dbProxySetupForAuth();

        $testName = "Native User Challenge Check";
        $cliendId = "12345";

        $challenge = $this->db_proxy->generateChallenge();
        //echo "\ngenerated=", $challenge;
        $this->db_proxy->dbClass->authHandler->authSupportStoreChallenge(0, $challenge, $cliendId);

        $this->assertTrue(
            $this->db_proxy->checkChallenge($challenge, $cliendId), $testName);
    }

    public function testDefaultKey()
    {
        $this->dbProxySetupForAccess('person_layout', 1);

        $className = get_class($this->db_proxy->dbClass->specHandler);
        if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_FX') {
            $this->assertEquals('-recid', call_user_func(array($className, 'defaultKey')));
        } else if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') {
            $this->assertEquals('recordId', call_user_func(array($className, 'defaultKey')));
        }
    }

    public function testGetDefaultKey()
    {
        $this->dbProxySetupForAccess('person_layout', 1);

        $value = $this->db_proxy->dbClass->specHandler->getDefaultKey();
        if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_FX') {
            $this->assertEquals('-recid', $value);
        } else if (get_class($this->db_proxy->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') {
            $this->assertEquals('recordId', $value);
        }
    }

    public function testMultiClientSyncTableExsistence()
    {
        $testName = "Tables for storing the context and ids should be existing.";
        $this->dbProxySetupForAuth();
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->isExistRequiredTable(), $testName);
    }

    /**
     * @runInSeparateProcess
     */
    public function testMultiClientSyncRegisterAndUnregister()
    {
        $testName = "Register and Unregister.";
        $this->dbProxySetupForAuth();
        //$this->db_proxy->dbClass->deleteForTest("registeredcontext");
        //$this->db_proxy->dbClass->deleteForTest("registeredpks");
        $clientId = "123456789ABCDEF";
        $condition = "WHERE id=1001 ORDER BY xdate LIMIT 10";
        $pkArray = array(1001, 2001, 3003, 4004);

        $entity = "table1";
        $registResult = $this->db_proxy->dbClass->notifyHandler->register($clientId, $entity, $condition, $pkArray);
        //var_export($this->db_proxy->logger->getDebugMessage());
        $this->assertTrue($registResult !== false, "Register table1");
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredcontext",
            array("clientid"=>$clientId, "entity"=>$entity));
        $this->assertTrue(count($recSet) == 1, "Count table1");
        $this->assertTrue($recSet[0]["conditions"] == $condition, "the 'clientId' value in table1");
        $regId = $recSet[0]["id"];
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredpks",
            array("context_id"=>$regId));
        $this->assertTrue(count($recSet) == 4, "Count pk values");
        $this->assertTrue(count(array_diff(
                $pkArray,
                array($recSet[0]["pk"], $recSet[1]["pk"], $recSet[2]["pk"], $recSet[3]["pk"])
            )) == 0, "Stored pk values");

        $entity = "table2";
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId, $entity, $condition, $pkArray) !== false,
            "Register table2");
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredcontext",
            array("clientid"=>$clientId, "entity"=>$entity));
        $this->assertTrue(count($recSet) == 1, "Count table1");
        $this->assertTrue($recSet[0]["conditions"] == $condition, "tha 'clientId' value in table1");
        $regId = $recSet[0]["id"];
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredpks",
            array("context_id"=>$regId));
        $this->assertTrue(count($recSet) == 4, "Count pk values");
        $this->assertTrue(count(array_diff(
                $pkArray,
                array($recSet[0]["pk"], $recSet[1]["pk"], $recSet[2]["pk"], $recSet[3]["pk"])
            )) == 0, "Stored pk values");

        $entity = "table3";
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId, $entity, $condition, $pkArray) !== false,
            "Register table3");
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredcontext",
            array("clientid"=>$clientId, "entity"=>$entity));
        $this->assertTrue(count($recSet) == 1, "Count table1");
        $this->assertTrue($recSet[0]["conditions"] == $condition, "tha 'clientId' value in table1");
        $regId = $recSet[0]["id"];
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredpks",
            array("context_id"=>$regId));
        $this->assertTrue(count($recSet) == 4, "Count pk values");
        $this->assertTrue(count(array_diff(
                $pkArray,
                array($recSet[0]["pk"], $recSet[1]["pk"], $recSet[2]["pk"], $recSet[3]["pk"])
            )) == 0, "Stored pk values");

        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId, null), $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredcontext");
        $this->assertTrue(count($recSet) == 0, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks");
        $this->assertTrue(count($recSet) == 0, "Count pk values");
    }

    /**
     * @runInSeparateProcess
     */
    public function testMultiClientSyncRegisterAndUnregisterPartial()
    {
        $testName = "Register and Unregister partically.";
        $this->dbProxySetupForAuth();
        //$this->db_proxy->dbClass->deleteForTest("registeredcontext");
        //$this->db_proxy->dbClass->deleteForTest("registeredpks");
        $clientId = "123456789ABCDEF";
        $condition = "WHERE id=1001 ORDER BY xdate LIMIT 10";
        $pkArray = array(1001, 2001, 3003, 4004);

        $entity = "table1";
        $registResult1 = $this->db_proxy->dbClass->notifyHandler->register($clientId, $entity, $condition, $pkArray);
        $registResult2 = $this->db_proxy->dbClass->notifyHandler->register($clientId, $entity, $condition, $pkArray);
        $registResult3 = $this->db_proxy->dbClass->notifyHandler->register($clientId, $entity, $condition, $pkArray);
        //var_export($this->db_proxy->logger->getDebugMessage());
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredcontext",
            array("clientid"=>$clientId, "entity"=>$entity));
        $this->assertTrue(count($recSet) == 3, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredpks",
            array("context_id"=>$registResult1));
        $this->assertTrue(count($recSet) == 4, "Count pk values");
        $this->assertTrue(count(array_diff(
                $pkArray,
                array($recSet[0]["pk"], $recSet[1]["pk"], $recSet[2]["pk"], $recSet[3]["pk"])
            )) == 0, "Stored pk values");

        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId, array($registResult2)), $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredcontext",
            array("clientid"=>$clientId, "entity"=>$entity));
        $this->assertTrue(count($recSet) == 2, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredpks",
            array("context_id"=>$registResult2));
        $this->assertTrue(count($recSet) == 0, "Count pk values");

        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId, null), $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredcontext");
        $this->assertTrue(count($recSet) == 0, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks");
        $this->assertTrue(count($recSet) == 0, "Count pk values");
    }

    /**
     * @runInSeparateProcess
     */
    public function testMultiClientSyncMatching()
    {
        $testName = "Match the sync info.";
        $this->dbProxySetupForAuth();
        //$this->db_proxy->dbClass->deleteForTest("registeredcontext");
        //$this->db_proxy->dbClass->deleteForTest("registeredpks");
        $condition = "WHERE id=1001 ORDER BY xdate LIMIT 10";
        $pkArray1 = array(1001, 2001, 3003, 4004);
        $pkArray2 = array(9001, 8001, 3003, 4004);

        $entity = "table1";
        $clientId1 = "123456789ABCDEF";
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId1, $entity, $condition, $pkArray1) !== false, $testName);
        $clientId2 = "ZZYYEEDDFF39887";
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId2, $entity, $condition, $pkArray2) !== false, $testName);

        $result = $this->db_proxy->dbClass->notifyHandler->matchInRegisterd($clientId2, $entity, array(3003));
        $this->assertTrue(count($result) == 1, "Count matching");
        $this->assertTrue($result[0] == $clientId1, "Matched client id");

        $result = $this->db_proxy->dbClass->notifyHandler->matchInRegisterd($clientId2, $entity, array(2001));
        $this->assertTrue(count($result) == 1, "Count matching");
        $this->assertTrue($result[0] == $clientId1, "Matched client id");

        $result = $this->db_proxy->dbClass->notifyHandler->matchInRegisterd($clientId2, $entity, array(4567));
        $this->assertTrue(count($result) == 0, "Count matching 3");

        $result = $this->db_proxy->dbClass->notifyHandler->matchInRegisterd($clientId2, $entity, array(8001));
        $this->assertTrue(count($result) == 0, "Count matching 4");

        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId1, null) !== false, $testName);
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId2, null) !== false, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredcontext");
        $this->assertTrue(count($recSet) == 0, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks");
        $this->assertTrue(count($recSet) == 0, "Count pk values");
    }

    /**
     * @runInSeparateProcess
     */
    public function testMultiClientSyncAppend()
    {
        $testName = "Append Sync Info.";
        $this->dbProxySetupForAuth();
        //$this->db_proxy->dbClass->deleteForTest("registeredcontext");
        //$this->db_proxy->dbClass->deleteForTest("registeredpks");
        $condition = "WHERE id=1001 ORDER BY xdate LIMIT 10";
        $pkArray1 = array(1001, 2001, 3003, 4004);
        $pkArray2 = array(9001, 8001, 3003, 4004);

        $entity = "table1";
        $clientId1 = "123456789ABCDEF";
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId1, $entity, $condition, $pkArray1) !== false, $testName);
        $clientId2 = "ZZYYEEDDFF39887";
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId2, $entity, $condition, $pkArray2) !== false, $testName);
        $clientId3 = "555588888DDDDDD";
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId3, "table2", $condition, $pkArray2) !== false, $testName);

        $result = $this->db_proxy->dbClass->notifyHandler->appendIntoRegisterd($clientId1, $entity, array(101));
        $this->assertTrue($result[0] == $clientId2, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks", array("pk"=>101));
        $this->assertTrue(count($recSet) == 2 , $testName);

        $result = $this->db_proxy->dbClass->notifyHandler->appendIntoRegisterd($clientId2, $entity, array(102));
        $this->assertTrue($result[0] == $clientId1, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks", array("pk"=>102));
        $this->assertTrue(count($recSet) == 2 , $testName);

        $result = $this->db_proxy->dbClass->notifyHandler->appendIntoRegisterd($clientId3, "table2", array(103));
        $this->assertTrue(count($result) == 0, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks", array("pk"=>103));
        $this->assertTrue(count($recSet) == 1 , $testName);

        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId1, null) !== false, $testName);
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId2, null) !== false, $testName);
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId3, null) !== false, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredcontext");
        $this->assertTrue(count($recSet) == 0, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks");
        $this->assertTrue(count($recSet) == 0, "Count pk values");

        //$result = $this->db_proxy->dbClass->notifyHandler->removeFromRegisterd($clientId, $entity, $pkArray);
    }

    /**
     * @runInSeparateProcess
     */
    public function testMultiClientSyncRemove()    {
        $testName = "Remove Sync Info.";
        $this->dbProxySetupForAuth();
        //$this->db_proxy->dbClass->deleteForTest("registeredcontext");
        //$this->db_proxy->dbClass->deleteForTest("registeredpks");
        $condition = "WHERE id=1001 ORDER BY xdate LIMIT 10";
        $pkArray1 = array(1001, 2001, 3003, 4004);
        $pkArray2 = array(9001, 8001, 3003, 4004);

        $entity = "table1";
        $clientId1 = "123456789ABCDEF";
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId1, $entity, $condition, $pkArray1) !== false, $testName);
        $clientId2 = "ZZYYEEDDFF39887";
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId2, $entity, $condition, $pkArray2) !== false, $testName);
        $clientId3 = "555588888DDDDDD";

        $result = $this->db_proxy->dbClass->notifyHandler->removeFromRegisterd($clientId1, $entity, array(3003));
        $this->assertTrue($result[0] == $clientId2, $testName);

        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks", array("pk"=>3003));
        $this->assertTrue(count($recSet) == 0 , $testName);

        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId1, null), $testName);
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId2, null), $testName);
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId3, null), $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredcontext");
        $this->assertTrue(count($recSet) == 0, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks");
        $this->assertTrue(count($recSet) == 0, "Count pk values");
    }

    public function testIsSupportAggregation()
    {
        $this->dbProxySetupForAccess('person_layout', 1);
        $this->assertFalse($this->db_proxy->dbClass->specHandler->isSupportAggregation());
    }

    public function testGetAuthorizedUsers()
    {
        $this->dbProxySetupForAuth();
        $authorizedUsers = $this->db_proxy->dbClass->authHandler->getAuthorizedUsers('read');
        $this->assertTrue($authorizedUsers == array('user1'));
    }

    public function testGetAuthorizedGroups()
    {
        $this->dbProxySetupForAuth();
        $authorizedGroups = $this->db_proxy->dbClass->authHandler->getAuthorizedGroups('read');
        $this->assertTrue($authorizedGroups == array('group2'));
    }
}
