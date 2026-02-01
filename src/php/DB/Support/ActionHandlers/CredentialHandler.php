<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use INTERMediator\DB\Logger;
use INTERMediator\IMUtil;
use INTERMediator\Messaging\MessagingProxy;
use INTERMediator\Params;

/**
 * Visitor class for handling credential-based authentication operations in the Proxy pattern.
 * Implements methods for authentication, authorization, challenge handling, and 2FA support.
 */
class CredentialHandler extends ActionHandler
{
    /** Visits the IsAuthAccessing operation.
     *
     * @return bool Always returns true for credential access.
     */
    public function isAuthAccessing(): bool
    {
        return true;
    }

    /** Visits the CheckAuthentication operation for credential access.
     *
     * @return bool True if authentication succeeds, false otherwise.
     */
    public function checkAuthentication(): bool
    {
        $result = $this->prepareCheckAuthentication();
        if ($result) {
            $result = $this->sessionStorageCheckAuth();
            // Hash Auth checking. Here comes not only 'session-storage' but also 'credential'.
        }
        return $result;
    }

    /** Visits the CheckAuthorization operation for credential access.
     *
     * @return bool True if authorization succeeds, false otherwise.
     */
    public function checkAuthorization(): bool
    {
        $proxy = $this->proxy;
        return $proxy->authSucceed && $this->checkAuthorizationImpl();
    }

    /** Visits the DataOperation operation. No operation for a credential visitor.
     *
     * @return void
     */
    public function dataOperation(): void
    {
    }

    /** Visits the HandleChallenge operation to process challenge/response for credential access and 2FA.
     *
     * @return void
     */
    public function handleChallenge(): void
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
                    $has2FASetting = false;
                    if ($proxy->required2FA) {
                        if (!Params::getParameterValue("fixed2FACode", false)) {
                            $userName = $this->proxy->dbSettings->getCurrentUser();
                            [, , $email, , $secret] = $this->proxy->dbClass->authHandler->getLoginUserInfo($userName);
                            switch ($proxy->dbSettings->getMethod2FA()) {
                                case'email':  // Send mail containing 2FA code.
                                    $has2FASetting = !!$email;
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
                                    break;
                                case 'authenticator':
                                default:
                                    $has2FASetting = !!$secret;
                                    break;
                            }
                        } else {
                            $has2FASetting = true;
                        }
                    }
                    $proxy->outputOfProcessing['has2FASetting'] = $has2FASetting;
                    break;
                case
                'session-storage':
                    $challenge = $this->generateAndSaveChallenge($proxy->signedUser, $proxy->generatedClientID, "#");
                    $proxy->outputOfProcessing['challenge'] = "{$challenge}{$userSalt}";
            }
        } else {
            $this->clearAuthenticationCookies();
        }

        if ($proxy->isPasskey) {
            $challenge = $this->generateAndSaveChallenge($proxy->paramAuthUser ?? "", $proxy->generatedClientID, "&");
            $proxy->outputOfProcessing['passkeyChallenge'] = "{$challenge}";
        }
    }
}