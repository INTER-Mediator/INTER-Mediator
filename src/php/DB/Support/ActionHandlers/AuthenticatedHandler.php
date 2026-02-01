<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use INTERMediator\DB\Logger;
use INTERMediator\IMUtil;
use PragmaRX\Google2FA\Google2FA;

/**
 * Visitor class for handling authenticated operations in the Proxy pattern.
 * Implements methods for authentication, authorization, and challenge handling.
 */
class AuthenticatedHandler extends ActionHandler
{
    /** Visits the IsAuthAccessing operation.
     *
     * @return bool Always returns true for authenticated access.
     */
    public function isAuthAccessing(): bool
    {
        return true;
    }

    /** Visits the CheckAuthentication operation to verify authentication and 2FA.
     *
     * @return bool True if authentication (including 2FA) succeeds; false otherwise.
     */
    public function checkAuthentication(): bool
    {
        $proxy = $this->proxy;

        $proxy->dbSettings->setRequireAuthorization(true);
        if ($this->prepareCheckAuthentication()) {
            Logger::getInstance()->setDebugMessage(
                "[checkAuthentication] 2FA code={$proxy->code2FA}", 2);
            switch ($proxy->dbSettings->getMethod2FA()) {
                case'email':  // Send mail containing 2FA code.
                    $authCredential = $proxy->generateCredential($this->storedCredential, $proxy->clientId, $proxy->hashedPassword);
                    if ($proxy->credential === $authCredential && $proxy->code2FA && $proxy->hashedPassword) {
                        $hmacValue = hash_hmac('sha256', $proxy->code2FA, $this->storedCredential);
                        Logger::getInstance()->setDebugMessage(
                            "[checkAuthentication] 2FA_email paramResponse2={$proxy->paramResponse2}/hmac_value={$hmacValue}", 2);
                        if ($proxy->paramResponse2 === $hmacValue) {
                            Logger::getInstance()->setDebugMessage("[checkAuthentication] 2FA_email authentication succeed.", 2);
                            return true;
                        } else {
                            Logger::getInstance()->setDebugMessage("[checkAuthentication] 2FA_email authentication failed.", 2);
                        }
                    }
                    break;
                default:
                case 'authenticator':
                    $userName = $this->proxy->dbSettings->getCurrentUser();
                    if ($userName) {
                        [, , , , $secret] = $this->proxy->dbClass->authHandler->getLoginUserInfo($userName);
                        Logger::getInstance()->setDebugMessage(
                            "[checkAuthentication] 2FA_authenticator userName={$userName}/code={$proxy->code2FA}", 2);
                        $google2fa = new Google2FA();
                        if ($secret && $proxy->code2FA && $google2fa->verifyKey($secret, $proxy->code2FA)) {
                            Logger::getInstance()->setDebugMessage("[checkAuthentication] 2FA_authenticator authentication succeed.", 2);
                            return true;
                        } else {
                            Logger::getInstance()->setDebugMessage("[checkAuthentication] 2FA_authenticator authentication failed.", 2);
                        }
                    }
                    break;
            }
        }
        return false;
    }

    /** Visits the CheckAuthorization operation to verify authorization status.
     *
     * @return bool True if authorization succeeds; false otherwise.
     */
    public function checkAuthorization(): bool
    {
        $proxy = $this->proxy;
        return $proxy->authSucceed && $this->checkAuthorizationImpl();
    }

    /** Visits the DataOperation operation. No operation for authenticated visitor.
     *
     * @return void
     */
    public function dataOperation(): void
    {
    }

    /** Visits the HandleChallenge operation to process challenge/response for 2FA.
     *
     * @return void
     */
    public function handleChallenge(): void
    {
        $proxy = $this->proxy;
        Logger::getInstance()->setDebugMessage("[handleChallenge] access={$proxy->access}, succeed={$proxy->authSucceed}", 2);

        $proxy->generatedClientID = IMUtil::generateClientId('', $proxy->passwordHash);

        if ($proxy->authSucceed) {
            $challenge = $this->generateAndSaveChallenge($proxy->signedUser, $proxy->generatedClientID, "+");
            $this->setCookieOfChallenge('_im_credential_token',
                $challenge, $proxy->generatedClientID, $proxy->hashedPassword);

            $challenge = $this->generateAndSaveChallenge($proxy->signedUser, $proxy->generatedClientID, "=");
            $this->setCookieOfChallenge('_im_credential_2FA',
                $challenge, $proxy->generatedClientID, $proxy->hashedPassword);

            $proxy->outputOfProcessing['succeed_2FA'] = "1";
        } else { // Retry 2FA
            $userSalt = $proxy->signedUser ? $proxy->authSupportGetSalt($proxy->signedUser) : "0000";
            $challenge = $this->generateAndSaveChallenge(
                $proxy->signedUser, $proxy->generatedClientID, "+", $proxy->code2FA);
            $proxy->outputOfProcessing['challenge'] = "{$challenge}{$userSalt}";
            if ($proxy->authStoring === 'credential') {
                $this->setCookieOfChallenge('_im_credential_token',
                    $challenge, $proxy->generatedClientID, $proxy->hashedPassword);
            }
        }
    }
}