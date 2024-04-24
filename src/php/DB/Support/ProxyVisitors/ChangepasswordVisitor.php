<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\CheckAuthenticationElement;
use INTERMediator\DB\Support\ProxyElements\DataOperationElement;
use INTERMediator\DB\Support\ProxyElements\HandleChallengeElement;
use INTERMediator\DB\Logger;

/**
 *
 */
class ChangepasswordVisitor extends OperationVisitor
{
    /**
     * @param CheckAuthenticationElement $e
     * @return void
     */
    public function visitCheckAuthentication(CheckAuthenticationElement $e): void
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
     * @param DataOperationElement $e
     * @return void
     */
    public function visitDataOperation(DataOperationElement $e): void
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
     * @param HandleChallengeElement $e
     * @return void
     */
    public function visitHandleChallenge(HandleChallengeElement $e): void
    {
        $this->defaultHandleChallenge();
    }

}