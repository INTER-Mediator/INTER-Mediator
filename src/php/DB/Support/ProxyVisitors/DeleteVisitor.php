<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\OperationElement;
use INTERMediator\DB\Logger;

/**
 * Visitor class for handling delete operations in the Proxy pattern.
 * Implements methods for authentication, authorization, data deletion, and challenge handling.
 */
class DeleteVisitor extends OperationVisitor
{
    /**
     * Visits the IsAuthAccessing operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Always returns false for delete operations (no auth access required).
     */
    public function visitIsAuthAccessing(OperationElement $e): bool
    {
        return false;
    }

    /**
     * Visits the CheckAuthentication operation for delete operations.
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
     * Visits the CheckAuthorization operation for delete operations.
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
     * Visits the DataOperation operation to perform the delete in the database.
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     */
    public function visitDataOperation(OperationElement $e): void
    {
        Logger::getInstance()->setDebugMessage("[processingRequest] start delete processing", 2);
        $this->proxy->deleteFromDB();
    }

    /**
     * Visits the HandleChallenge operation for delete operations.
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