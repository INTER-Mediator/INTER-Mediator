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
     * @return bool
     */
    public function visitIsAuthAccessing(OperationElement $e): bool
    {
        return true;
    }

    /**
     * @param OperationElement $e
     * @return bool
     */
    public function visitCheckAuthentication(OperationElement $e): bool
    {
        $proxy = $this->proxy;
        if ($this->prepareCheckAuthentication($e)) {
            if ($proxy->credential
                == $proxy->generateCredential($this->storedChallenge, $proxy->clientId, $proxy->hashedPassword)) {
                Logger::getInstance()->setDebugMessage("[visitCheckAuthentication] Credential (SHA-256) auth passed.", 2);
                return true;
            } else { // Hash Auth checking
                return $this->sessionStorageCheckAuth();
            }
        }
        return false;
    }

    /**
     * @param OperationElement $e
     * @return bool
     */
    public function visitCheckAuthorization(OperationElement $e): bool
    {
        $proxy = $this->proxy;
        return $proxy->authSucceed && $this->checkAuthorization();
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