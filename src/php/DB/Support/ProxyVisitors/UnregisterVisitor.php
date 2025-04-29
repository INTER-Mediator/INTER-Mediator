<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\OperationElement;
use INTERMediator\DB\Logger;

/**
 * Visitor class for handling unregister operations in the Proxy pattern.
 * Implements methods for authentication, authorization, unregistering notifications, and challenge handling.
 *
 * @property object|null $notifyServer The notification server object, if available (from dbSettings).
 * @property bool $clientSyncAvailable Indicates if client synchronization is available (from proxy).
 * @property array $PostData The POST data array (from proxy).
 */
class UnregisterVisitor extends OperationVisitor
{
    /**
     * Visits the IsAuthAccessing operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Always returns false for unregister operations (no auth access required).
     */
    public function visitIsAuthAccessing(OperationElement $e): bool
    {
        return false;
    }

    /**
     * Visits the CheckAuthentication operation for unregister operations.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool True if authentication succeeds, false otherwise.
     */
    public function visitCheckAuthentication(OperationElement $e): bool
    {
        return $this->prepareCheckAuthentication($e) && $this->checkAuthenticationCommon($e);
    }

    /**
     * Visits the CheckAuthorization operation for unregister operations.
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
     * Visits the DataOperation operation to perform the unregister process (notification unregister).
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     */
    public function visitDataOperation(OperationElement $e): void
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

    /**
     * Visits the HandleChallenge operation for unregister operations.
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     */
    public function visitHandleChallenge(OperationElement $e): void
    {
        $this->defaultHandleChallenge();
    }

}