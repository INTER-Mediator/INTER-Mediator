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
class DB_PageEditor extends DB_UseSharedObjects implements DB_Interface
{
    private $recordCount;
    private $isRequiredUpdated = false;
    private $updatedRecord = null;

    function readFromDB()
    {
        $dataSourceName = $this->dbSettings->getDataSourceName();
        $filePath = $this->dbSettings->getCriteriaValue('target');
        if (substr_count($filePath, '../') > 2) {
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

    function countQueryResult()
    {
        return $this->recordCount;
    }

    function getTotalCount()
    {
        return $this->recordCount;
    }

    function updateDB()
    {
        $dataSourceName = $this->dbSettings->getDataSourceName();
        $filePath = $this->dbSettings->getValueOfField('target');
        if (substr_count($filePath, '../') > 2) {
            $this->logger->setErrorMessage("You can't access files in inhibit area: {$dataSourceName}.");
            return null;
        }

        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            $this->logger->setErrorMessage("The 'target' parameter doesn't point the valid file path in context: {$dataSourceName}.");
            return null;
        }
        $fileWriteResult = file_put_contents($filePath, $this->dbSettings->getValueOfField('content'));
        if ($fileWriteResult === false) {
            $this->logger->setErrorMessage("The file {$filePath} doesn't have the permission to write.");
            return null;
        }
        $result = array(array('id' => 1, 'content' => $this->dbSettings->getValueOfField('content')));
        $this->updatedRecord = $result;
        return $result;
    }

    function createInDB($bypassAuth)
    {
    }

    function deleteFromDB()
    {
    }

    function getFieldInfo($dataSourceName)
    {
        // TODO: Implement getFieldInfo() method.
    }

    public function setupConnection()
    {
        return true;
    }

    public function requireUpdatedRecord($value)
    {
        $this->isRequiredUpdated = $value;
    }

    public function updatedRecord()
    {
        return $this->updatedRecord;
    }

    public function softDeleteActivate($field, $value)
    {
        // TODO: Implement softDeleteActivate() method.
    }

    public function copyInDB()
    {
        return false;
    }

    public function setupHandlers()
    {
        // TODO: Implement setupHandlers() method.
    }

    public function normalizedCondition($condition)
    {
        // TODO: Implement normalizedCondition() method.
    }

    public function setUpdatedRecord($field, $value, $index = 0)
    {
        // TODO: Implement setUpdatedRecord() method.
    }

    public function queryForTest($table, $conditions = null)
    {
        // TODO: Implement queryForTest() method.
    }

    public function deleteForTest($table, $conditions = null)
    {
        // TODO: Implement deleteForTest() method.
    }
}
