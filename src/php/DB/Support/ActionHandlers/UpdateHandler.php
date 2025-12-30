<?php

namespace INTERMediator\DB\Support\ActionHandlers;

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
class UpdateHandler extends ActionHandler
{
    /** Visits the IsAuthAccessing operation.
     * 
     * @return bool Always returns false for update operations (no auth access required).
     */
    public function isAuthAccessing(): bool
    {
        return false;
    }

    /** Visits the CheckAuthentication operation for update operations.
     * 
     * @return bool True if authentication succeeds, false otherwise.
     */
    public function checkAuthentication(): bool
    {
        return $this->prepareCheckAuthentication() && $this->checkAuthenticationCommon();
    }

    /** Visits the CheckAuthorization operation for update operations.
     * 
     * @return bool True if authorization succeeds, false otherwise.
     */
    public function checkAuthorization(): bool
    {
        $proxy = $this->proxy;
        return $proxy->authSucceed && $this->checkAuthorizationImpl();
    }

    /** Visits the DataOperation operation to perform the update in the database.
     * Handles validation, field protection, and updates output after processing.
     * 
     * @return void
     */
    public function dataOperation(): void
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

    /** Visits the HandleChallenge operation for update operations.
     * 
     * @return void
     */
    public function handleChallenge(): void
    {
        $this->defaultHandleChallenge();
    }

}