<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use Exception;
use INTERMediator\DB\Generator;
use INTERMediator\DB\Logger;

/**
 * Visitor class for handling maintenance operations in the Proxy pattern.
 * Implements methods for authentication, authorization, schema maintenance, and challenge handling.
 *
 * @property bool $activateGenerator Indicates if schema auto generation mode is enabled (from proxy).
 */
class MaintenanceHandler extends ActionHandler
{
    /** Visits the IsAuthAccessing operation.
     * 
     * @return bool Always returns false for maintenance operations (no auth access required).
     */
    public function isAuthAccessing(): bool
    {
        return false;
    }

    /** Visits the CheckAuthentication operation for maintenance operations.
     * 
     * @return bool True if authentication succeeds, false otherwise.
     */
    public function checkAuthentication(): bool
    {
        return $this->prepareCheckAuthentication() && $this->checkAuthenticationCommon();
    }

    /** Visits the CheckAuthorization operation for maintenance operations.
     * 
     * @return bool True if authorization succeeds, false otherwise.
     */
    public function checkAuthorization(): bool
    {
        $proxy = $this->proxy;
        return $proxy->authSucceed && $this->checkAuthorizationImpl();
    }

    /** Visits the DataOperation operation to perform maintenance tasks (e.g., schema auto generation).
     * 
     * @return void
     * @throws Exception If schema generation fails.
     */
    public function dataOperation(): void
    {
        Logger::getInstance()->setDebugMessage("[processingRequest] start maintenance processing", 2);
        if ($this->proxy->activateGenerator) { // Schema auto generating mode
            (new Generator($this->proxy))->generate();
        }
    }

    /** Visits the HandleChallenge operation for maintenance operations.
     * 
     * @return void
     */
    public function handleChallenge(): void
    {
        $this->defaultHandleChallenge();
    }

}