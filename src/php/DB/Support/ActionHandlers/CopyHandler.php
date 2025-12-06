<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use INTERMediator\DB\Logger;

/**
 * Visitor class for handling copy operations in the Proxy pattern.
 * Implements methods for authentication, authorization, data copy, and challenge handling.
 */
class CopyHandler extends ActionHandler
{
    /** Visits the IsAuthAccessing operation.
     * 
     * @return bool Always returns false for copy operations (no auth access required).
     */
    public function isAuthAccessing(): bool
    {
        return false;
    }

    /** Visits the CheckAuthentication operation for copy operations.
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

    /** Visits the CheckAuthorization operation for copy operations.
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

    /** Visits the DataOperation operation to perform the copy in the database.
     * 
     * @return void
     */
    public function dataOperation(): void
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

    /** Visits the HandleChallenge operation for copy operations.
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