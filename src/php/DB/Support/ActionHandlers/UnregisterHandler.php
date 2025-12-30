<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use INTERMediator\DB\Logger;

/**
 * Visitor class for handling unregister operations in the Proxy pattern.
 * Implements methods for authentication, authorization, unregistering notifications, and challenge handling.
 *
 * @property object|null $notifyServer The notification server object, if available (from dbSettings).
 * @property bool $clientSyncAvailable Indicates if client synchronization is available (from proxy).
 * @property array $PostData The POST data array (from proxy).
 */
class UnregisterHandler extends ActionHandler
{
    /** Visits the IsAuthAccessing operation.
     * 
     * @return bool Always returns false for unregistered operations (no auth access required).
     */
    public function isAuthAccessing(): bool
    {
        return false;
    }

    /** Visits the CheckAuthentication operation for unregistered operations.
     * 
     * @return bool True if authentication succeeds, false otherwise.
     */
    public function checkAuthentication(): bool
    {
        return $this->prepareCheckAuthentication() && $this->checkAuthenticationCommon();
    }

    /** Visits the CheckAuthorization operation for unregistered operations.
     * 
     * @return bool True if authorization succeeds, false otherwise.
     */
    public function checkAuthorization(): bool
    {
        $proxy = $this->proxy;
        return $proxy->authSucceed && $this->checkAuthorizationImpl();
    }

    /** Visits the DataOperation operation to perform the unregistered process (notification unregister).
     * 
     * @return void
     */
    public function dataOperation(): void
    {
        Logger::getInstance()->setDebugMessage("[processingRequest] start unregister processing", 2);
        if (!is_null($this->proxy->dbSettings->notifyServer) && $this->proxy->clientSyncAvailable) {
            $tableKeys = null;
            if (isset($this->proxy->PostData['pks'])) {
                $tableKeys = json_decode($this->proxy->PostData['pks'], true);
            }
            $this->proxy->dbSettings->notifyServer->unregister($this->proxy->PostData['notifyid'], $tableKeys);
        }
    }

    /** Visits the HandleChallenge operation for unregistered operations.
     * 
     * @return void
     */
    public function handleChallenge(): void
    {
        $this->defaultHandleChallenge();
    }

}