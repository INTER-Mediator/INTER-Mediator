<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use Exception;
use INTERMediator\DB\Logger;

/**
 * Visitor class for handling describe operations in the Proxy pattern.
 * Implements methods for authentication, authorization, schema description, and challenge handling.
 */
class DescribeHandler extends ActionHandler
{
    /** Visits the IsAuthAccessing operation.
     * 
     * @return bool Always returns false for describe operations (no auth access required).
     */
    public function isAuthAccessing(): bool
    {
        return false;
    }

    /** Visits the CheckAuthentication operation to describe operations.
     * 
     * @return bool True if authentication succeeds, false otherwise.
     */
    public function checkAuthentication(): bool
    {
        return $this->prepareCheckAuthentication() && $this->checkAuthenticationCommon();
    }

    /** Visits the CheckAuthorization operation to describe operations.
     * 
     * @return bool True if authorization succeeds, false otherwise.
     */
    public function checkAuthorization(): bool
    {
        $proxy = $this->proxy;
        return $proxy->authSucceed && $this->checkAuthorizationImpl();
    }

    /** Visits the DataOperation operation to perform schema description.
     * 
     * @return void
     * @throws Exception If the schema description fails.
     */
    public function dataOperation(): void
    {
        Logger::getInstance()->setDebugMessage("[processingRequest] start describe processing", 2);
        $result = $this->proxy->dbClass->getSchema($this->proxy->dbSettings->getDataSourceName());
        $this->proxy->outputOfProcessing['dbresult'] = $result;
        $this->proxy->outputOfProcessing['resultCount'] = 0;
        $this->proxy->outputOfProcessing['totalCount'] = 0;
    }

    /** Visits the HandleChallenge operation to describe operations.
     * 
     * @return void
     */
    public function handleChallenge(): void
    {
        $this->defaultHandleChallenge();
    }

}