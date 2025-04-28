<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\OperationElement;

/**
 * Visitor class for handling no-operation (noop) in the Proxy pattern.
 * Implements methods that effectively bypass all authentication, authorization, data, and challenge operations.
 */
class NothingVisitor extends OperationVisitor
{
    /**
     * Visits the IsAuthAccessing operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Always returns false (no authentication access required).
     */
    public function visitIsAuthAccessing(OperationElement $e): bool
    {
        return false;
    }

    /**
     * Visits the CheckAuthentication operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Always returns true (authentication always succeeds).
     */
    public function visitCheckAuthentication(OperationElement $e): bool
    {
        return true;
    }

    /**
     * Visits the CheckAuthorization operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Always returns true (authorization always succeeds).
     */
    public function visitCheckAuthorization(OperationElement $e): bool
    {
        return true;
    }

    /**
     * Visits the DataOperation operation. No operation performed.
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     */
    public function visitDataOperation(OperationElement $e): void
    {
    }

    /**
     * Visits the HandleChallenge operation. No operation performed.
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     */
    public function visitHandleChallenge(OperationElement $e): void
    {
    }

}