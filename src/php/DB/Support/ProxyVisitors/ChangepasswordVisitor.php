<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\OperationElement;
use INTERMediator\DB\Logger;

/**
 *
 */
class ChangepasswordVisitor extends OperationVisitor
{
    /**
     * @param OperationElement $e
     * @return void
     */
    public function visitCheckAuthentication(OperationElement $e): void
    {
        $proxy = $this->proxy;
        if ($this->prepareCheckAuthentication($e)) {
            if ($proxy->credential
                == $proxy->generateCredential($this->storedChallenge, $proxy->clientId, $proxy->hashedPassword)) {
                Logger::getInstance()->setDebugMessage("[visitCheckAuthentication] Credential (SHA-256) auth passed.", 2);
                $e->resultOfCheckAuthentication = true;
            } else { // Hash Auth checking
                $e->resultOfCheckAuthentication = $this->sessionStorageCheckAuth();
            }
        }
    }


    /**
     * @param OperationElement $e
     * @return void
     */
    public function visitDataOperation(OperationElement $e): void
    {
        $proxy = $this->proxy;
        Logger::getInstance()->setDebugMessage("[visitDataOperation] start changepassword processing", 2);

        if (isset($proxy->PostData['newpass']) && $proxy->authSucceed) {
            $changeResult = $proxy->changePassword($proxy->paramAuthUser, $proxy->PostData['newpass']);
            $proxy->outputOfProcessing['changePasswordResult'] = $changeResult;
        } else {
            $proxy->outputOfProcessing['changePasswordResult'] = false;
        }
    }


    /**
     * @param OperationElement $e
     * @return void
     */
    public function visitHandleChallenge(OperationElement $e): void
    {
        $this->defaultHandleChallenge();
    }

}