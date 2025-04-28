<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\OperationElement;
use INTERMediator\DB\Logger;

/**
 * Visitor class for handling password change operations in the Proxy pattern.
 * Implements methods for authentication, authorization, password update, and challenge handling.
 */
class ChangepasswordVisitor extends OperationVisitor
{
    /**
     * Visits the IsAuthAccessing operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Always returns true for password change access.
     */
    public function visitIsAuthAccessing(OperationElement $e): bool
    {
        return true;
    }

    /**
     * Visits the CheckAuthentication operation for password change.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool True if authentication succeeds, false otherwise.
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
     * Visits the CheckAuthorization operation for password change.
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
     * Visits the DataOperation operation to perform the password change.
     *
     * @param OperationElement $e The operation element being visited.
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
     * Visits the HandleChallenge operation for password change.
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     */
    public function visitHandleChallenge(OperationElement $e): void
    {
        $this->defaultHandleChallenge();
    }

}