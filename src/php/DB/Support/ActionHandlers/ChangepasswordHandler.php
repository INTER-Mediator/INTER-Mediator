<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use INTERMediator\DB\Logger;

/**
 * Visitor class for handling password change operations in the Proxy pattern.
 * Implements methods for authentication, authorization, password update, and challenge handling.
 */
class ChangepasswordHandler extends ActionHandler
{
    /** Visits the IsAuthAccessing operation.
     * 
     * @return bool Always returns true for password change access.
     */
    public function isAuthAccessing(): bool
    {
        return true;
    }

    /** Visits the CheckAuthentication operation for password change.
     * 
     * @return bool True if authentication succeeds, false otherwise.
     */
    public function checkAuthentication(): bool
    {
        $proxy = $this->proxy;
        if ($this->prepareCheckAuthentication()) {
            if ($proxy->credential
                == $proxy->generateCredential($this->storedChallenge, $proxy->clientId, $proxy->hashedPassword)) {
                Logger::getInstance()->setDebugMessage("[checkAuthentication] Credential (SHA-256) auth passed.", 2);
                return true;
            } else { // Hash Auth checking
                return $this->sessionStorageCheckAuth();
            }
        }
        return false;
    }

    /** Visits the CheckAuthorization operation for password change.
     * 
     * @return bool True if authorization succeeds, false otherwise.
     */
    public function checkAuthorization(): bool
    {
        $proxy = $this->proxy;
        return $proxy->authSucceed && $this->checkAuthorizationImpl();
    }

    /** Visits the DataOperation operation to perform the password change.
     * 
     * @return void
     */
    public function dataOperation(): void
    {
        $proxy = $this->proxy;
        Logger::getInstance()->setDebugMessage("[dataOperation] start changepassword processing", 2);

        if (isset($proxy->PostData['newpass']) && $proxy->authSucceed) {
            $changeResult = $proxy->changePassword($proxy->paramAuthUser, $proxy->PostData['newpass']);
            $proxy->outputOfProcessing['changePasswordResult'] = $changeResult;
        } else {
            $proxy->outputOfProcessing['changePasswordResult'] = false;
        }
    }

    /** Visits the HandleChallenge operation for password change.
     * 
     * @return void
     */
    public function handleChallenge(): void
    {
        $this->defaultHandleChallenge();
    }

}