<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Logger;
use INTERMediator\DB\Support\ProxyElements\CheckAuthenticationElement;
use INTERMediator\DB\Support\ProxyElements\DataOperationElement;
use INTERMediator\DB\Support\ProxyElements\HandleChallengeElement;
use INTERMediator\IMUtil;
use INTERMediator\Messaging\MessagingProxy;
use INTERMediator\Params;

class CredentialVisitor extends OperationVisitor
{
    public function visitCheckAuthentication(CheckAuthenticationElement $e): void
    {
        $proxy = $this->proxy;

        $e->resultOfCheckAuthentication = $this->prepareCheckAuthentication($e);
        if ($e->resultOfCheckAuthentication) {
            $referingCredential = $proxy->generateCredential($this->storedChallenge, $proxy->clientId, $proxy->hashedPassword);
            Logger::getInstance()->setDebugMessage("[visitCheckAuthentication] Credential "
                . "send={$proxy->credential}, refer={$referingCredential}", 2);
            if ($proxy->credential == $referingCredential) {
                Logger::getInstance()->setDebugMessage("[visitCheckAuthentication] Credential (SHA-256) auth passed.", 2);
                $e->resultOfCheckAuthentication = true;
            } else { // Hash Auth checking
                $e->resultOfCheckAuthentication = $this->sessionStorageCheckAuth();
            }
        }
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
            switch ($proxy->authStoring) {
                case 'credential':
                    $code2FA = Params::getParameterValue("fixed2FACode", IMUtil::randomDigit($proxy->digitsOf2FACode));
                    $challenge = $this->generateAndSaveChallenge($proxy->signedUser, $proxy->generatedClientID, "+",
                        ($proxy->required2FA ? $code2FA : ""));
                    $proxy->outputOfProcessing['challenge'] = "{$challenge}{$userSalt}";
                    $this->setCookieOfChallenge(
                        '_im_credential_token', $challenge, $proxy->generatedClientID, $proxy->hashedPassword);
                    if ($proxy->required2FA && !Params::getParameterValue("fixed2FACode",false)) { // Send mail containing 2FA code.
                        $proxy->logger->setDebugMessage("Try to send a message.", 2);
                        $email = $proxy->dbClass->authHandler->authSupportEmailFromUnifiedUsername($proxy->signedUser);
                        if(!$email) {
                            $proxy->logger->setWarningMessage("The logging-in user has no email info.", 2);
                            break;
                        }
                        $msgProxy = new MessagingProxy("mail");
                        $msgProxy->processing($proxy, ['template-context'=>$proxy->mailContext2FA],
                            [['mail'=>$email,'code'=>$code2FA]]);
                    }
                    break;
                case
                'session-storage':
                    $challenge = $this->generateAndSaveChallenge($proxy->signedUser, $proxy->generatedClientID, "#");
                    $proxy->outputOfProcessing['challenge'] = "{$challenge}{$userSalt}";
                    break;
            }
        } else {
            $this->clearAuthenticationCookies();
        }
    }
}