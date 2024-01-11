<?php

/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace INTERMediator\DB;

class PageEditor extends UseSharedObjects implements DBClass_Interface
{
    private $recordCount;
    private $isRequiredUpdated = false;
    private $updatedRecord = null;

    public function readFromDB():?array
    {
        $dataSourceName = $this->dbSettings->getDataSourceName();
        $filePath = $this->dbSettings->getCriteriaValue('target');
        if (substr_count($filePath, '../') > 5) {
            $this->logger->setErrorMessage("You can't access files in inhibit area: {$dataSourceName}.");
            return null;
        }
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            $this->logger->setErrorMessage("The 'target' parameter doesn't point the valid file path in context: {$dataSourceName}.");
            $this->recordCount = 0;
            return null;
        }
        $this->recordCount = 1;
        return array(array('id' => 1, 'content' => $fileContent));
    }

    public function countQueryResult(): int
    {
        return $this->recordCount;
    }

    public function getTotalCount(): int
    {
        return $this->recordCount;
    }

    public function updateDB(bool $bypassAuth): bool
    {
        $dataSourceName = $this->dbSettings->getDataSourceName();
        $filePath = $this->dbSettings->getValueOfField('target');
        if (substr_count($filePath, '../') > 5) {
            $this->logger->setErrorMessage("You can't access files in inhibit area: {$dataSourceName}.");
            return false;
        }

        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            $this->logger->setErrorMessage("The 'target' parameter doesn't point the valid file path in context: {$dataSourceName}.");
            return false;
        }
        $fileWriteResult = file_put_contents($filePath, $this->dbSettings->getValueOfField('content'));
        if ($fileWriteResult === false) {
            $this->logger->setErrorMessage("The file {$filePath} doesn't have the permission to write.");
            return false;
        }
        $result = array(array('id' => 1, 'content' => $this->dbSettings->getValueOfField('content')));
        $this->updatedRecord = $result;
        return true;
    }

    public function createInDB($isReplace = false):?string
    {
        return null;
    }

    public function deleteFromDB():bool
    {
        return false;
    }

    function getFieldInfo($dataSourceName): ?array
    {
        return null;
    }

    public function setupConnection():bool
    {
        return true;
    }

    public function requireUpdatedRecord(bool $value): void
    {
        $this->isRequiredUpdated = $value;
    }

    public function getUpdatedRecord(): ?array
    {
        return $this->updatedRecord;
    }

    public function updatedRecord(){
        return $this->updatedRecord;
    }

    public function softDeleteActivate(string $field, string $value): void
    {
        // TODO: Implement softDeleteActivate() method.
    }

    public function copyInDB():?string
    {
        return null;
    }

    public function setupHandlers(?string $dsn = null):void
    {
    }

    public function setDataToUpdatedRecord(string $field, string $value, int $index = 0):void
    {
    }

    public function queryForTest(string $table, ?array $conditions = null):?array
    {
        return null;
    }

    public function deleteForTest(string $table, ?array $conditions = null): bool
    {
        return false;
    }

    public function setUpdatedRecord(array $record): void
    {
    }

    public function hasTransaction():bool
    {
        return false;
    }

    public function inTransaction():bool
    {
        return false;
    }

    public function beginTransaction():void
    {
    }

    public function commitTransaction():void
    {
    }

    public function rollbackTransaction():void
    {
    }

    public function getUseSetDataToUpdatedRecord():bool
    {
        return false;
    }

    public function clearUseSetDataToUpdatedRecord():void
    {

    }

    public function closeDBOperation():void
    {
    }

    public function normalizedCondition(array $condition)
    {
    }
}
