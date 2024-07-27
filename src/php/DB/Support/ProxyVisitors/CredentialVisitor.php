<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Logger;
use INTERMediator\DB\Support\ProxyElements\OperationElement;
use INTERMediator\IMUtil;
use INTERMediator\Messaging\MessagingProxy;
use INTERMediator\Params;

/**
 *
 */
class CredentialVisitor extends OperationVisitor
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
     * @return void
     */
    public function visitCheckAuthentication(OperationElement $e): bool
    {
        $result = $this->prepareCheckAuthentication($e);
        if ($result) {
            $result = $this->sessionStorageCheckAuth();
            // Hash Auth checking. Here comes not only 'session-storage' but also 'credential'.
        }
        return $result;
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
        $userSalt = $proxy->authSupportGetSalt($proxy->signedUser);

        if ($proxy->authSucceed) {
            switch ($proxy->authStoring) {
                case 'credential':
                    $code2FA = Params::getParameterValue("fixed2FACode", IMUtil::randomDigit($proxy->digitsOf2FACode));
                    $challenge = $this->generateAndSaveChallenge($proxy->signedUser, $proxy->generatedClientID, "+",
                        ($proxy->required2FA ? $code2FA : ""));
                    $proxy->outputOfProcessing['challenge'] = "{$challenge}{$userSalt}";
                    $proxy->outputOfProcessing['authUser'] = $proxy->signedUser;
                    $this->setCookieOfChallenge('_im_credential_token',
                        $challenge, $proxy->generatedClientID, $proxy->hashedPassword);
                    if ($proxy->required2FA && !Params::getParameterValue("fixed2FACode", false)) { // Send mail containing 2FA code.
                        $proxy->logger->setDebugMessage("Try to send a message.", 2);
                        $email = $proxy->dbClass->authHandler->authSupportEmailFromUnifiedUsername($proxy->signedUser);
                        if (!$email) {
                            $proxy->logger->setWarningMessage("The logging-in user has no email info.");
                            break;
                        }
                        if ($proxy->mailContext2FA) {
                            $msgProxy = new MessagingProxy("mail");
                            $msgProxy->processing($proxy, ['template-context' => $proxy->mailContext2FA],
                                [['mail' => $email, 'code' => $code2FA]]);
                        } else {
                            $messageClass = IMUtil::getMessageClassInstance();
                            $proxy->logger->setWarningMessage($messageClass->getMessageAs(2033));
                        }
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