<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use Exception;
use INTERMediator\DB\Generator;
use INTERMediator\DB\Support\ProxyElements\OperationElement;
use INTERMediator\DB\Logger;

/**
 * Visitor class for handling maintenance operations in the Proxy pattern.
 * Implements methods for authentication, authorization, schema maintenance, and challenge handling.
 *
 * @property bool $activateGenerator Indicates if schema auto generation mode is enabled (from proxy).
 */
class MaintenanceVisitor extends OperationVisitor
{
    /**
     * Visits the IsAuthAccessing operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Always returns false for maintenance operations (no auth access required).
     */
    public function visitIsAuthAccessing(OperationElement $e): bool
    {
        return false;
    }

    /**
     * Visits the CheckAuthentication operation for maintenance operations.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool True if authentication succeeds, false otherwise.
     */
    public function visitCheckAuthentication(OperationElement $e): bool
    {
        return $this->prepareCheckAuthentication($e) && $this->checkAuthenticationCommon($e);
    }

    /**
     * Visits the CheckAuthorization operation for maintenance operations.
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
     * Visits the DataOperation operation to perform maintenance tasks (e.g., schema auto generation).
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     * @throws Exception If schema generation fails.
     */
    public function visitDataOperation(OperationElement $e): void
    {
        Logger::getInstance()->setDebugMessage("[processingRequest] start maintenance processing", 2);
        if ($this->proxy->activateGenerator) { // Schema auto generating mode
            (new Generator($this->proxy))->generate();
        }
    }

    /**
     * Visits the HandleChallenge operation for maintenance operations.
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     */
    public function visitHandleChallenge(OperationElement $e): void
    {
        $this->defaultHandleChallenge();
    }

}