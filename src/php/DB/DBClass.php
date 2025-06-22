<?php

namespace INTERMediator\DB;

use Exception;
use INTERMediator\FileMakerServer\RESTAPI\FMDataAPI;

/**
 * Abstract base class for database classes in INTER-Mediator, providing common interface and error handling.
 */
abstract class DBClass extends UseSharedObjects implements DBClass_Interface
{
    /**
     * Throws an exception; should only be used by FileMaker subclasses.
     *
     * @param array $condition The condition array.
     * @return null|array
     * @throws Exception Always throws; not supported in base class.
     */
    public function normalizedCondition(array $condition): null|array
    {
        throw new Exception("Don't use normalizedCondition method on DBClass instance without FileMaker ones.");
    }

    /**
     * Throws an exception; should only be used by FileMaker subclasses.
     *
     * @param string $currentOperation The current operation name.
     * @return mixed
     * @throws Exception Always throws; not supported in base class.
     */
    public function getWhereClauseForTest(string $currentOperation)
    {
        $currentClass = get_class($this);
        throw new Exception("This '{$currentClass}' class doesn't support the getWhereClauseForTest method.");
    }

    /**
     * Throws an exception; should only be used by FileMaker subclasses.
     *
     * @param string $dsnString The DSN string.
     * @return mixed
     * @throws Exception Always throws; not supported in base class.
     */
    public function setupWithDSN(string $dsnString)
    {
        $currentClass = get_class($this);
        throw new Exception("This '{$currentClass}' class doesn't support the setupWithDSN method.");
    }

    /**
     * Throws an exception; should only be used by FileMaker subclasses.
     *
     * @param string $dataSourceName The data source name.
     * @return mixed
     * @throws Exception Always throws; not supported in base class.
     */
    public function getSchema(string $dataSourceName)
    {
        $currentClass = get_class($this);
        throw new Exception("This '{$currentClass}' class doesn't support the getSchema method.");
    }

    /**
     * Throws an exception; should only be used by FileMaker subclasses.
     *
     * @param string $layoutName The layout name.
     * @param int $recordCount The number of records.
     * @return mixed
     * @throws Exception Always throws; not supported in base class.
     */
    public function setupFMDataAPIforDB(string $layoutName, int $recordCount)
    {
        $currentClass = get_class($this);
        throw new Exception("This '{$currentClass}' class doesn't support the setupFMDataAPIforDB method.");
    }

    /**
     * Throws an exception; should only be used by FileMaker subclasses.
     *
     * @return FMDataAPI
     * @throws Exception Always throws; not supported in base class.
     */
    public function getFMDataInstance(): ?FMDataAPI
    {
        $currentClass = get_class($this);
        throw new Exception("This '{$currentClass}' class doesn't support the getFMDataInstance method.");
    }

    /**
     * Returns the record limit parameter based on table info and settings.
     *
     * @param array $tableInfo Table information array.
     * @return int The limit parameter.
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