<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use INTERMediator\DB\Generator;
use INTERMediator\DB\Logger;

/**
 * Visitor class for handling read operations in the Proxy pattern.
 * Implements methods for authentication, authorization, data reading, and challenge handling.
 *
 * @property bool $bypassAuth Indicates if authentication/authorization should be bypassed (from proxy).
 * @property bool $activateGenerator Indicates if schema auto generation mode is enabled (from proxy).
 * @property bool $suppressMediaToken Indicates if media token output should be suppressed (from proxy).
 */
class ReadHandler extends ActionHandler
{
    /** Visits the IsAuthAccessing operation.
     *
     * @return bool Always returns false for read operations (no auth access required).
     */
    public function isAuthAccessing(): bool
    {
        return false;
    }

    /** Visits the CheckAuthentication operation for read operations.
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

    /** Visits the CheckAuthorization operation for read operations.
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

    /** Visits the DataOperation operation to perform the read in the database.
     * Handles schema auto generation and normal data reading with field protection.
     * 
     * @return void
     */
    public function dataOperation(): void
    {
        Logger::getInstance()->setDebugMessage("[processingRequest] start read processing", 2);
        $tableInfo = $this->proxy->dbSettings->getDataSourceTargetArray();
        if ($this->proxy->activateGenerator) { // Schema auto generating mode
            $this->proxy->outputOfProcessing['dbresult'] = (new Generator($this->proxy))->acquire();
            $this->proxy->outputOfProcessing['resultCount'] = 1;
            $this->proxy->outputOfProcessing['totalCount'] = 1;
        } else { // normal access
            $result = $this->proxy->readFromDB();
            if (isset($tableInfo['protect-reading']) && is_array($tableInfo['protect-reading'])) {
                $recordCount = count($result);
                for ($index = 0; $index < $recordCount; $index++) {
                    foreach ($result[$index] as $field => $value) {
                        if (in_array($field, $tableInfo['protect-reading'])) {
                            $result[$index][$field] = "[protected]";
                        }
                    }
                }
            }
            $this->proxy->outputOfProcessing['dbresult'] = $result;
            $this->proxy->outputOfProcessing['resultCount'] = $this->proxy->countQueryResult();
            $this->proxy->outputOfProcessing['totalCount'] = $this->proxy->getTotalCount();
            $this->proxy->suppressMediaToken = false;
        }
    }

    /** Visits the HandleChallenge operation for read operations.
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