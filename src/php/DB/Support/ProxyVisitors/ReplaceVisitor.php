<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use Exception;
use INTERMediator\DB\Support\ProxyElements\OperationElement;

/**
 * Visitor class for handling replace operations in the Proxy pattern.
 * Implements methods for authentication, authorization, data replacement, and challenge handling.
 *
 * @property bool $bypassAuth Indicates if authentication/authorization should be bypassed (from proxy).
 */
class ReplaceVisitor extends OperationVisitor
{
    /**
     * Visits the IsAuthAccessing operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Always returns false for replace operations (no auth access required).
     */
    public function visitIsAuthAccessing(OperationElement $e): bool
    {
        return false;
    }

    /**
     * Visits the CheckAuthentication operation for replace operations.
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
     * Visits the CheckAuthorization operation for replace operations.
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
     * Visits the DataOperation operation to perform the replace in the database.
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     * @throws Exception If the replace operation fails.
     */
    public function visitDataOperation(OperationElement $e): void
    {
        $this->CreateReplaceImpl("replace");
    }

    /**
     * Visits the HandleChallenge operation for replace operations.
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