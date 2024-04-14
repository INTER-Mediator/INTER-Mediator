<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\CheckAuthenticationElement;
use INTERMediator\DB\Support\ProxyElements\DataOperationElement;
use INTERMediator\DB\Support\ProxyElements\HandleChallengeElement;
use INTERMediator\DB\Logger;

class UnregisterVisitor extends OperationVisitor
{
    public function visitCheckAuthentication(CheckAuthenticationElement $e): void
    {
        $e->resultOfCheckAuthentication
            = $this->prepareCheckAuthentication($e) && $this->checkAuthenticationCommon($e);
    }


    public function visitDataOperation(DataOperationElement $e): void
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


    public function visitHandleChallenge(HandleChallengeElement $e): void
    {
        $this->defaultHandleChallenge();
    }

}