<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Logger;
use INTERMediator\DB\Support\ProxyElements\OperationElement;
use INTERMediator\IMUtil;

/**
 *
 */
class AuthenticatedVisitor extends OperationVisitor
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

        $proxy->dbSettings->setRequireAuthorization(true);
        if ($this->prepareCheckAuthentication($e)) {
            Logger::getInstance()->setDebugMessage(
                "[visitCheckAuthentication] 2FA code={$proxy->code2FA}", 2);
            $authCredential = $proxy->generateCredential($this->storedCredential, $proxy->clientId, $proxy->hashedPassword);
            if ($proxy->credential === $authCredential && $proxy->code2FA && $proxy->hashedPassword) {
                $hmacValue = hash_hmac('sha256', $proxy->code2FA, $this->storedCredential);
                Logger::getInstance()->setDebugMessage(
                    "[visitCheckAuthentication] 2FA paramResponse2={$proxy->paramResponse2}/hmac_value={$hmacValue}", 2);
                if ($proxy->paramResponse2 === $hmacValue) {
                    Logger::getInstance()->setDebugMessage("[visitCheckAuthentication] 2FA authentication succeed.", 2);
                    return true;
                } else {
                    Logger::getInstance()->setDebugMessage("[visitCheckAuthentication] 2FA authentication failed.", 2);
                }
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
    }


    /**
     * @param OperationElement $e
     * @return void
     */
    public function visitHandleChallenge(OperationElement $e): void
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