<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\OperationElement;
use INTERMediator\DB\Logger;

/**
 * Visitor class for handling copy operations in the Proxy pattern.
 * Implements methods for authentication, authorization, data copy, and challenge handling.
 */
class CopyVisitor extends OperationVisitor
{
    /**
     * Visits the IsAuthAccessing operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Always returns false for copy operations (no auth access required).
     */
    public function visitIsAuthAccessing(OperationElement $e): bool
    {
        return false;
    }

    /**
     * Visits the CheckAuthentication operation for copy operations.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool True if authentication succeeds or bypassAuth is enabled, false otherwise.
     */
    public function visitCheckAuthentication(OperationElement $e): bool
    {
        if ($this->proxy->bypassAuth) {
            return true;
        }
        return $this->prepareCheckAuthentication($e) && $this->checkAuthenticationCommon($e);
    }

    /**
     * Visits the CheckAuthorization operation for copy operations.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool True if authorization succeeds or bypassAuth is enabled, false otherwise.
     */
    public function visitCheckAuthorization(OperationElement $e): bool
    {
        $proxy = $this->proxy;
        if ($proxy->bypassAuth) {
            return true;
        }
        return $proxy->authSucceed && $this->checkAuthorization();
    }

    /**
     * Visits the DataOperation operation to perform the copy in the database.
     *
     * @param OperationElement $e The operation element being visited.
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
     * Visits the HandleChallenge operation for copy operations.
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     */
    public function visitHandleChallenge(OperationElement $e): void
    {
        if ($this->proxy->bypassAuth) {
            return;
        }
        $this->defaultHandleChallenge();
    }

}