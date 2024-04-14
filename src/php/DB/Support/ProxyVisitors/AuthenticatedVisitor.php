<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Logger;
use INTERMediator\DB\Support\ProxyElements\CheckAuthenticationElement;
use INTERMediator\DB\Support\ProxyElements\DataOperationElement;
use INTERMediator\DB\Support\ProxyElements\HandleChallengeElement;
use INTERMediator\IMUtil;

class AuthenticatedVisitor extends OperationVisitor
{
    public function visitCheckAuthentication(CheckAuthenticationElement $e): void
    {
        $proxy = $this->proxy;

        $proxy->dbSettings->setRequireAuthorization(true);
        $e->resultOfCheckAuthentication = $this->prepareCheckAuthentication($e);
        if ($e->resultOfCheckAuthentication) {
            Logger::getInstance()->setDebugMessage(
                "[visitCheckAuthentication] 2FA code={$proxy->code2FA}", 2);
            $authCredential = $proxy->generateCredential($this->storedCredential, $proxy->clientId, $proxy->hashedPassword);
            if ($proxy->credential == $authCredential && $proxy->code2FA && $proxy->hashedPassword) {
                $hmacValue = hash_hmac('sha256', $proxy->code2FA, $this->storedCredential);
                Logger::getInstance()->setDebugMessage(
                    "[visitCheckAuthentication] 2FA paramResponse2={$proxy->paramResponse2}/hmac_value={$hmacValue}", 2);
                if ($proxy->paramResponse2 == $hmacValue) {
                    Logger::getInstance()->setDebugMessage("[visitCheckAuthentication] 2FA authentication succeed.", 2);
                    $e->resultOfCheckAuthentication = true;
                    return;
                } else {
                    Logger::getInstance()->setDebugMessage("[visitCheckAuthentication] 2FA authentication failed.", 2);
                }
            }
        }
        $e->resultOfCheckAuthentication = false;
    }


    public function visitDataOperation(DataOperationElement $e): void
    {
    }


    public function visitHandleChallenge(HandleChallengeElement $e): void
    {
        $proxy = $this->proxy;
        Logger::getInstance()->setDebugMessage("[handleChallenge] access={$proxy->access}, succeed={$proxy->authSucceed}", 2);

        $proxy->generatedClientID = IMUtil::generateClientId('', $proxy->passwordHash);
        $userSalt = $proxy->authSupportGetSalt($proxy->signedUser);

        if ($proxy->authSucceed) {
            $challenge = $this->generateAndSaveChallenge($proxy->signedUser, $proxy->generatedClientID, "+");
            $this->setCookieOfChallenge('_im_credential_token',
                $challenge, $proxy->generatedClientID, $proxy->hashedPassword);

            $challenge = $this->generateAndSaveChallenge($proxy->signedUser, $proxy->generatedClientID, "=");
            $this->setCookieOfChallenge('_im_credential_2FA',
                $challenge, $proxy->generatedClientID, $proxy->hashedPassword);

            $proxy->outputOfProcessing['succeed_2FA'] = "1";
        } else { // Retry 2FA
            $challenge = $this->generateAndSaveChallenge(
                $proxy->signedUser, $proxy->generatedClientID, "+", $proxy->code2FA);
            $proxy->outputOfProcessing['challenge'] = "{$challenge}{$userSalt}";
            if ($proxy->authStoring == 'credential') {
                $this->setCookieOfChallenge('_im_credential_token',
                    $challenge, $proxy->generatedClientID, $proxy->hashedPassword);
            }
        }
    }
}