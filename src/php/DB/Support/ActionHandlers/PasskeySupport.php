<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use INTERMediator\DB\Logger;
use INTERMediator\IMUtil;
use INTERMediator\Params;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

trait PasskeySupport
{
    protected function publicKeyCredentialCreationOptions(?string $userName, ?string $challenge = ""): PublicKeyCredentialCreationOptions
    {
        Logger::getInstance()->setDebugMessage(
            "[publicKeyCredentialCreationOptions] userName={$userName} for host {$_SERVER["SERVER_NAME"]}", 2);

        [$userId, $realName] = $this->proxy->dbClass->authHandler->getLoginUserInfo($userName);
        $appName = Params::getParameterValue("applicationName", "INTER-Mediator Application");
        $rpEntity = PublicKeyCredentialRpEntity::create($appName, $_SERVER["SERVER_NAME"]);
        $userEntity = PublicKeyCredentialUserEntity::create($userName, strval($userId), $realName ?? "");
        if (!$challenge) {
            $challenge = hex2bin($this->generateAndSaveChallenge(
                $userName ?? "", $this->proxy->generatedClientID, "$", "", $challenge));
        }
        return PublicKeyCredentialCreationOptions::create($rpEntity, $userEntity, $challenge);
    }

    protected function createPublicKeyCredentialRequestOptions(?string $challenge = "", string $clientId = ""): PublicKeyCredentialRequestOptions
    {
        Logger::getInstance()->setDebugMessage(
            "[createPublicKeyCredentialRequestOptions] for host {$_SERVER["SERVER_NAME"]}", 2);
        if (!$challenge) {
            $challenge = hex2bin($this->generateAndSaveChallenge($userName ?? "", $clientId, "$", "", $challenge));
        }
        return PublicKeyCredentialRequestOptions::create($challenge,
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED
        );
    }

    protected function storePublicKey(string $uid, PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        $publicKey = $this->passKeySeriarize($publicKeyCredentialSource);
        $publicKeyCredentialId = base64_encode($publicKeyCredentialSource->publicKeyCredentialId);
        $this->proxy->dbClass->authHandler->authSupportStorePublicKey($uid, $publicKey, $publicKeyCredentialId);
    }

    protected function passKeySeriarize(object $option): string
    {
        $attestationStatementSupportManager = AttestationStatementSupportManager::create();
        $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());
        $factory = new WebauthnSerializerFactory($attestationStatementSupportManager);
        $serializer = $factory->create();

        return $serializer->serialize($option, 'json',
            [AbstractObjectNormalizer::SKIP_NULL_VALUES => true, JsonEncode::OPTIONS => JSON_THROW_ON_ERROR],
        );
    }

    protected function passKeyDeserializePublicKeyCredential(string $json): PublicKeyCredential
    {
        $attestationStatementSupportManager = AttestationStatementSupportManager::create();
        $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());
        $factory = new WebauthnSerializerFactory($attestationStatementSupportManager);
        $serializer = $factory->create();
        return $serializer->deserialize($json, PublicKeyCredential::class, 'json');
    }

    protected function passKeyDeserializePublicKeyCredentialSource(string $json): PublicKeyCredentialSource
    {
        $attestationStatementSupportManager = AttestationStatementSupportManager::create();
        $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());
        $factory = new WebauthnSerializerFactory($attestationStatementSupportManager);
        $serializer = $factory->create();
        return $serializer->deserialize($json, PublicKeyCredentialSource::class, 'json');
    }
}