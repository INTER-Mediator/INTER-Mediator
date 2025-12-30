<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use INTERMediator\DB\Logger;

/**
 * Visitor class for handling delete operations in the Proxy pattern.
 * Implements methods for authentication, authorization, data deletion, and challenge handling.
 */
class DeleteHandler extends ActionHandler
{
    /** Visits the IsAuthAccessing operation.
     * 
     * @return bool Always returns false for delete operations (no auth access required).
     */
    public function isAuthAccessing(): bool
    {
        return false;
    }

    /** Visits the CheckAuthentication operation for delete operations.
     * 
     * @return bool True, if authentication succeeds or bypassAuth is enabled, false otherwise.
     */
    public function checkAuthentication(): bool
    {
        if ($this->proxy->bypassAuth) {
            return true;
        }
        return $this->prepareCheckAuthentication() && $this->checkAuthenticationCommon();
    }

    /** Visits the CheckAuthorization operation for delete operations.
     * 
     * @return bool True, if authorization succeeds or bypassAuth is enabled, false otherwise.
     */
    public function checkAuthorization(): bool
    {
        $proxy = $this->proxy;
        if ($proxy->bypassAuth) {
            return true;
        }
        return $proxy->authSucceed && $this->checkAuthorizationImpl();
    }

    /** Visits the DataOperation operation to perform to delete in the database.
     * 
     * @return void
     */
    public function dataOperation(): void
    {
        Logger::getInstance()->setDebugMessage("[processingRequest] start delete processing", 2);
        $this->proxy->deleteFromDB();
    }

    /** Visits the HandleChallenge operation for delete operations.
     * 
     * @return void
     */
    public function handleChallenge(): void
    {
        if ($this->proxy->bypassAuth) {
            return;
        }
        $this->defaultHandleChallenge();
    }

}