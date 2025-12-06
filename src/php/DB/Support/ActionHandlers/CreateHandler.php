<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use Exception;

/**
 * Visitor class for handling create operations in the Proxy pattern.
 * Implements methods for authentication, authorization, data creation, and challenge handling.
 */
class CreateHandler extends ActionHandler
{
    /** Visits the IsAuthAccessing operation.
     * 
     * @return bool Always returns false for create operations (no auth access required).
     */
    public function isAuthAccessing(): bool
    {
        return false;
    }

    /** Visits the CheckAuthentication operation to create operations.
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

    /** Visits the CheckAuthorization operation to create operations.
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

    /** Visits the DataOperation operation to perform the creation in the database.
     * 
     * @return void
     * @throws Exception If the create operation fails.
     */
    public function dataOperation(): void
    {
        $this->CreateReplaceImpl("create");
    }

    /** Visits the HandleChallenge operation to create operations.
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