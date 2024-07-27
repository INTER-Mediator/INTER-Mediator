<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\OperationElement;
use INTERMediator\DB\Logger;

/**
 *
 */
class CopyVisitor extends OperationVisitor
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
        $proxy = $this->proxy;
        Logger::getInstance()->setDebugMessage("[processingRequest] start copy processing", 2);

        if ($proxy->checkValidation()) {
            $result = $proxy->copyInDB();
            $proxy->outputOfProcessing['newRecordKeyValue'] = $result;
            $proxy->outputOfProcessing['dbresult'] = $proxy->getUpdatedRecord();
        } else {
            $proxy->logger->setErrorMessage("Invalid data. Any validation rule was violated.");
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