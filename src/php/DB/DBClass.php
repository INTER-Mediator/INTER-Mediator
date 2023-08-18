<?php

namespace INTERMediator\DB;

use Exception;
use INTERMediator\FileMakerServer\RESTAPI\FMDataAPI;

/**
 *
 */
abstract class DBClass extends UseSharedObjects implements DBClass_Interface
{
    /**
     * @param array $condition
     * @return mixed
     * @throws Exception
     */
    public function normalizedCondition(array $condition)
    {
        throw new Exception("Don't use normalizedCondition method on DBClass instance without FileMaker ones.");
    }

    /**
     * @param string $currentOperation
     * @return mixed
     * @throws Exception
     */
    public function getWhereClauseForTest(string $currentOperation)
    {
        $currentClass = get_class($this);
        throw new Exception("This '{$currentClass}' class doesn't support the getWhereClauseForTest method.");
    }

    /**
     * @param string $dsnString
     * @return mixed
     * @throws Exception
     */
    public function setupWithDSN(string $dsnString)
    {
        $currentClass = get_class($this);
        throw new Exception("This '{$currentClass}' class doesn't support the setupWithDSN method.");
    }

    /**
     * @param string $dataSourceName
     * @return mixed
     * @throws Exception
     */
    public function getSchema(string $dataSourceName)
    {
        $currentClass = get_class($this);
        throw new Exception("This '{$currentClass}' class doesn't support the getSchema method.");
    }

    /**
     * @param string $layoutName
     * @param int $recordCount
     * @return mixed
     * @throws Exception
     */
    public function setupFMDataAPIforDB(string $layoutName, int $recordCount)
    {
        $currentClass = get_class($this);
        throw new Exception("This '{$currentClass}' class doesn't support the setupFMDataAPIforDB method.");
    }

    /**
     * @return FMDataAPI
     * @throws Exception
     */
    public function getFMDataInstance(): FMDataAPI
    {
        $currentClass = get_class($this);
        throw new Exception("This '{$currentClass}' class doesn't support the getFMDataInstance method.");
    }

    /**
     * @param array $tableInfo
     * @return int
     */
    protected function getLimitParam(array $tableInfo): int
    {
        $limitParam = 100000000;
        if (isset($tableInfo['maxrecords'])) {
            if (intval($tableInfo['maxrecords']) < $this->dbSettings->getRecordCount()) {
                $limitParam = max(intval($tableInfo['maxrecords']), intval($tableInfo['records']));
            } else {
                $limitParam = $this->dbSettings->getRecordCount();
            }
        } else if (isset($tableInfo['records'])) {
            if (intval($tableInfo['records']) < $this->dbSettings->getRecordCount()) {
                $limitParam = intval($tableInfo['records']);
            } else {
                $limitParam = $this->dbSettings->getRecordCount();
            }
        }
        return $limitParam;
    }

}