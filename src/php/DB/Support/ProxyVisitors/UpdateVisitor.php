<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use Exception;
use INTERMediator\DB\Support\ProxyElements\OperationElement;
use INTERMediator\DB\Logger;

/**
 *
 */
class UpdateVisitor extends OperationVisitor
{
    /**
     * @param OperationElement $e
     * @return bool
     */
    public function visitIsAuthAccessing(OperationElement $e): bool
    {
        return false;
    }

    /**
     * @param OperationElement $e
     * @return bool
     */
    public function visitCheckAuthentication(OperationElement $e): bool
    {
        return $this->prepareCheckAuthentication($e) && $this->checkAuthenticationCommon($e);
    }

    /**
     * @param OperationElement $e
     * @return bool
     */
    public function visitCheckAuthorization(OperationElement $e): bool
    {
        $proxy = $this->proxy;
        return $proxy->authSucceed && $this->checkAuthorization();
    }

    /**
     * @param OperationElement $e
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
     * @param OperationElement $e
     * @return void
     */
    public function visitHandleChallenge(OperationElement $e): void
    {
        $this->defaultHandleChallenge();
    }

}