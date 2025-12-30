<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use Exception;
use INTERMediator\DB\Logger;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\AuthenticatorAttestationResponseValidator;

class UnregisterPasskeyHandler extends ActionHandler
{
    use PasskeySupport;

    /** Visits the IsAuthAccessing operation.
     *
     * @return bool Result of the operation.
     */
    public function isAuthAccessing(): bool
    {
        return false;
    }

    /** Visits the CheckAuthentication operation.
     *
     * @return bool Result of the operation.
     */
    public function checkAuthentication(): bool
    {
        if ($this->proxy->bypassAuth) {
            return true;
        }
        return $this->prepareCheckAuthentication() && $this->checkAuthenticationCommon();
    }

    /** Visits the CheckAuthorization operation.
     *
     * @return bool Result of the operation.
     */
    public function checkAuthorization(): bool
    {
        $proxy = $this->proxy;
        if ($proxy->bypassAuth) {
            return true;
        }
        return $proxy->authSucceed && $this->checkAuthorizationImpl();
    }

    /** Visits the DataOperation operation.
     *
     * @return void
     */
    public function dataOperation(): void
    {
        try {
            $userName = $this->proxy->paramAuthUser;
            [$uid, $realName] = $this->proxy->dbClass->authHandler->getLoginUserInfo($userName);
            $this->proxy->dbClass->authHandler->authSupportRemovePublicKey($uid);
        } catch (Exception $e) {
            Logger::getInstance()->setDebugMessage(
                "[UnregisterPasskeyHandler] Exception:" . $e->getMessage(), 2);
        }
    }

    /** Visits the HandleChallenge operation.
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
