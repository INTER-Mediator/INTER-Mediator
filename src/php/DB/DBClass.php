<?php

namespace INTERMediator\DB;

use Exception;

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

}