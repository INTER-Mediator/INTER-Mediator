<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use Exception;
use INTERMediator\DB\Logger;
use INTERMediator\IMUtil;
use INTERMediator\Params;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialSource;

class AuthPasskeyHandler extends ActionHandler
{
    use PasskeySupport;

    /** Visits the IsAuthAccessing operation.
     *
     * @return bool Result of the operation.
     */
    public function isAuthAccessing(): bool
    {
        return true;
    }

    private string $credential;
    private string $username;

    /** Visits the CheckAuthentication operation.
     *
     * @return bool Result of the operation.
     */
    public function checkAuthentication(): bool
    {
        $proxy = $this->proxy;
        $proxy->dbSettings->setRequireAuthorization(true);
        try {
            // Get the dump of the received parameter.
            $publicKeyCredential = $this->passKeyDeserializePublicKeyCredential($this->proxy->pubkeyInfo);
            Logger::getInstance()->setDebugMessage(
                "[AuthPasskeyHandler] checkAuthentication() type={$publicKeyCredential->type}", 2);
//            Logger::getInstance()->setDebugMessage(
//                "[AuthPasskeyHandler] checkAuthentication() publicKeyCredential-->" . var_export($publicKeyCredential, true), 2);
            $rowId = base64_encode($publicKeyCredential->rawId);

            // Retrieve the challenge data stored on the server
            $clientId = $this->proxy->clientId;
            $hostName = $_SERVER["SERVER_NAME"];
            [$challenge, $uid] = $this->proxy->dbClass->authHandler->authSupportRetrieveChallenge(null, $clientId, true, "$", false);
            Logger::getInstance()->setDebugMessage(
                "[AuthPasskeyHandler] challenge={$challenge}, uid={$uid}, rowId={$rowId}", 2);

            // Checking the received response.
            $csmFactory = new CeremonyStepManagerFactory();
            $requestCSM = $csmFactory->requestCeremony();
            $authenticatorValidator = AuthenticatorAssertionResponseValidator::create($requestCSM);

            // Get the user information.
            $userInfo = $this->proxy->dbClass->authHandler->authSupportUserInfoFromPublickeyId($rowId);
            if (!isset($userInfo['hashedpasswd'])) { // No user bond to the public key.
                Logger::getInstance()->setErrorMessage(IMUtil::getMessageClassInstance()->getMessageAs(1066));
                return false;
            }
            $this->credential = $userInfo['hashedpasswd'];
            $this->username = $userInfo['username'];
            $publicKeyCredentialSource = $this->passKeyDeserializePublicKeyCredentialSource($userInfo['publicKey']);
            $creationOption = $this->createPublicKeyCredentialRequestOptions(hex2bin($challenge), $clientId);
//            Logger::getInstance()->setDebugMessage(
//                "[AuthPasskeyHandler] creationOption=" . var_export($creationOption, true), 2);

            // Varidating the response.
            try {
                $publicKeyCredentialSource = $authenticatorValidator->check(
                    $publicKeyCredentialSource, $publicKeyCredential->response, $creationOption, $hostName, null);
//                Logger::getInstance()->setDebugMessage(
//                    "[AuthPasskeyHandler] publicKeyCredentialSource=" . var_export($publicKeyCredentialSource, true), 2);
                Logger::getInstance()->setDebugMessage(
                    "[AuthPasskeyHandler] *** Passkey authentication succeed.***", 2);
                return true;
            } catch (\Throwable $e) {
                Logger::getInstance()->setErrorMessage("Passkey Authentication Error: {$e->getMessage()}");
            }
        } catch (Exception $e) {
            Logger::getInstance()->setDebugMessage(
                "[AuthPasskeyHandler] Exception:" . $e->getMessage(), 2);
        }
        return false;
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

        if ($this->proxy->authSucceed) {
            $authRealm = Params::getParameterValue("authRealm", "");
            $authExpired = Params::getParameterValue("authExpired", 3600);
            // Set the logging-in situation for the local user to continue from log-in.
            $generatedClientID = IMUtil::generateClientId('', $this->credential);
            $challenge = IMUtil::generateChallenge();
            $this->proxy->saveChallenge($this->username, $challenge, $generatedClientID, "+");
            setcookie('_im_credential_token',
                $this->proxy->generateCredential($challenge, $generatedClientID, $this->credential),
                time() + $authExpired, '/', "", false, true);
            setcookie("_im_username_{$authRealm}",
                $this->username, time() + $authExpired, '/', "", false, false);
            setcookie("_im_clientid_{$authRealm}",
                $generatedClientID, time() + $authExpired, '/', "", false, false);
        }
    }
}
