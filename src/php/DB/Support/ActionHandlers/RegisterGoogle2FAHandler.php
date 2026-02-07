<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use Exception;
use INTERMediator\DB\Logger;
use INTERMediator\Params;
use PragmaRX\Google2FA\Google2FA;

class RegisterGoogle2FAHandler extends ActionHandler
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
            Logger::getInstance()->setDebugMessage("[RegisterGoogle2FAHandler] dataOperation()", 2);
            $google2fa = new Google2FA();
            $secret = $google2fa->generateSecretKey();
            $userName = $this->proxy->paramAuthUser;
            $serverName = Params::getParameterValue("authRealm", $_SERVER["SERVER_NAME"]);
            [$uid, $realName] = $this->proxy->dbClass->authHandler->getLoginUserInfo($userName);
            $qrCodeUrl = $google2fa->getQRCodeUrl($serverName, $userName, $secret);
            $this->proxy->dbClass->authHandler->authSupportStore2FASecret($uid, $secret);
            $this->proxy->outputOfProcessing['qrcodeurl'] = $qrCodeUrl;
            Logger::getInstance()->setDebugMessage("[RegisterGoogle2FAHandler] *** Passkey registration succeed.***", 2);
        } catch (Exception $e) {
            Logger::getInstance()->setDebugMessage(
                "[RegisterGoogle2FAHandler] Exception:" . $e->getMessage(), 2);
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
