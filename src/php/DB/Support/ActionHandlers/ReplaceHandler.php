<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use Exception;

/**
 * Visitor class for handling replace operations in the Proxy pattern.
 * Implements methods for authentication, authorization, data replacement, and challenge handling.
 *
 * @property bool $bypassAuth Indicates if authentication/authorization should be bypassed (from proxy).
 */
class ReplaceHandler extends ActionHandler
{
    /** Visits the IsAuthAccessing operation.
     * 
     * @return bool Always returns false for replace operations (no auth access required).
     */
    public function isAuthAccessing(): bool
    {
        return false;
    }

    /** Visits the CheckAuthentication operation for replace operations.
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

    /** Visits the CheckAuthorization operation for replace operations.
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

    /** Visits the DataOperation operation to perform the replacement in the database.
     * 
     * @return void
     * @throws Exception If the replace operation fails.
     */
    public function dataOperation(): void
    {
        $this->CreateReplaceImpl("replace");
    }

    /** Visits the HandleChallenge operation for replace operations.
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