<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use Exception;
use INTERMediator\DB\Logger;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredential;

class RegisterPasskeyHandler extends ActionHandler
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
            // Get the dump of the received parameter.
            $publicKeyCredential = $this->passKeyDeserializePublicKeyCredential($this->proxy->pubkeyInfo);
            Logger::getInstance()->setDebugMessage(
                "[RegisterPasskeyHandler] dataOperation() type={$publicKeyCredential->type}", 2);

            // Retrieve the challenge data stored on the server
            $userName = $this->proxy->paramAuthUser;
            [$uid, $realName] = $this->proxy->dbClass->authHandler->getLoginUserInfo($userName);
            $clientId = $this->proxy->clientId;
            $hostName = $_SERVER["SERVER_NAME"];
            $challenge = $this->proxy->dbClass->authHandler->authSupportRetrieveChallenge($uid, $clientId, true, "$", false);

            // Checking the received response.
            $csmFactory = new CeremonyStepManagerFactory();
            $creationCSM = $csmFactory->creationCeremony();
            $responseValidator = AuthenticatorAttestationResponseValidator::create($creationCSM);
            $creationOption = $this->publicKeyCredentialCreationOptions($userName, hex2bin($challenge));
            try {
                $publicKeyCredentialSource = $responseValidator->check($publicKeyCredential->response, $creationOption, $hostName);
                // Storing passkey data into the "authuser" table.
                $publicKey = $this->passKeySeriarize($publicKeyCredentialSource);
                $publicKeyCredentialId = base64_encode($publicKeyCredentialSource->publicKeyCredentialId);
                $this->proxy->dbClass->authHandler->authSupportStorePublicKey($uid, $publicKey, $publicKeyCredentialId);
                Logger::getInstance()->setDebugMessage(
                    "[RegisterPasskeyHandler] *** Passkey registration succeed.***", 2);
            } catch (\Throwable $e) {
                Logger::getInstance()->setErrorMessage("Passkey Registration Error: {$e->getMessage()}");
            }
        } catch (Exception $e) {
            Logger::getInstance()->setDebugMessage(
                "[RegisterPasskeyHandler] Exception:" . $e->getMessage(), 2);

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
