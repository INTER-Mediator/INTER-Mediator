<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\OperationElement;
use INTERMediator\DB\Logger;

/**
 * Visitor class for handling update operations in the Proxy pattern.
 * Implements methods for authentication, authorization, data updating, and challenge handling.
 *
 * @property bool $bypassAuth Indicates if authentication/authorization should be bypassed (from proxy).
 * @property array $outputOfProcessing The array holding output data after processing (from proxy).
 * @property object $dbSettings The database settings object (from proxy).
 * @property object $dbClass The database class object (from proxy).
 * @property object $logger The logger object (from proxy).
 */
class UpdateVisitor extends OperationVisitor
{
    /**
     * Visits the IsAuthAccessing operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Always returns false for update operations (no auth access required).
     */
    public function visitIsAuthAccessing(OperationElement $e): bool
    {
        return false;
    }

    /**
     * Visits the CheckAuthentication operation for update operations.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool True if authentication succeeds, false otherwise.
     */
    public function visitCheckAuthentication(OperationElement $e): bool
    {
        return $this->prepareCheckAuthentication($e) && $this->checkAuthenticationCommon($e);
    }

    /**
     * Visits the CheckAuthorization operation for update operations.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool True if authorization succeeds, false otherwise.
     */
    public function visitCheckAuthorization(OperationElement $e): bool
    {
        $proxy = $this->proxy;
        return $proxy->authSucceed && $this->checkAuthorization();
    }

    /**
     * Visits the DataOperation operation to perform the update in the database.
     * Handles validation, field protection, and updates output after processing.
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     */
    public function visitDataOperation(OperationElement $e): void
    {
        Logger::getInstance()->setDebugMessage("[processingRequest] start update processing", 2);
        $tableInfo = $this->proxy->dbSettings->getDataSourceTargetArray();
        if ($this->proxy->checkValidation()) {
            if (isset($tableInfo['protect-writing']) && is_array($tableInfo['protect-writing'])) {
                $fieldArray = array();
                $valueArray = array();
                $counter = 0;
                $fieldValues = $this->proxy->dbSettings->getValue();
                foreach ($this->proxy->dbSettings->getFieldsRequired() as $field) {
                    if (!in_array($field, $tableInfo['protect-writing'])) {
                        $fieldArray[] = $field;
                        $valueArray[] = $fieldValues[$counter];
                    }
                    $counter++;
                }
                $this->proxy->dbSettings->setFieldsRequired($fieldArray);
                $this->proxy->dbSettings->setValue($valueArray);
            }
            $this->proxy->dbClass->requireUpdatedRecord(true);
            $this->proxy->updateDB($this->proxy->bypassAuth);
            $this->proxy->outputOfProcessing['dbresult'] = $this->proxy->getUpdatedRecord();
        } else {
            $this->proxy->logger->setErrorMessage("Invalid data. Any validation rule was violated.");
        }
    }

    /**
     * Visits the HandleChallenge operation for update operations.
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     */
    public function visitHandleChallenge(OperationElement $e): void
    {
        $this->defaultHandleChallenge();
    }

}