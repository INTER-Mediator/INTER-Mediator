<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use Exception;
use INTERMediator\DB\Logger;
use INTERMediator\DB\Proxy;
use INTERMediator\DB\Support\ProxyElements\OperationElement;
use INTERMediator\FileUploader;
use INTERMediator\IMUtil;

/**
 * Abstract base class for visitor implementations in the Proxy authentication/authorization system.
 * Provides the visitor interface for authentication, authorization, data, and challenge operations.
 * Also provides service methods and protected properties for subclasses.
 */
abstract class OperationVisitor
{
    /**
     * Reference to the Proxy object, which is calling visitor methods.
     *
     * @var Proxy
     */
    protected Proxy $proxy;

    /**
     * ==== Constructor ====
     * Initializes the visitor with a reference to the Proxy object.
     *
     * @param Proxy $proxy The Proxy object using this visitor.
     */
    public function __construct(Proxy $proxy)
    {
        $this->proxy = $proxy;
        Logger::getInstance()->setDebugMessage("Visitor class generated: " . get_class($this));
    }

    // Visitor methods

    /**
     * Visits the IsAuthAccessing operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Result of the operation.
     */
    abstract public function visitIsAuthAccessing(OperationElement $e): bool;

    /**
     * Visits the CheckAuthentication operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Result of the operation.
     */
    abstract public function visitCheckAuthentication(OperationElement $e): bool;

    /**
     * Visits the CheckAuthorization operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Result of the operation.
     */
    abstract public function visitCheckAuthorization(OperationElement $e): bool;

    /**
     * Visits the DataOperation operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     */
    abstract public function visitDataOperation(OperationElement $e): void;

    /**
     * Visits the HandleChallenge operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     */
    abstract public function visitHandleChallenge(OperationElement $e): void;

    // ==== Service methods for the visitCheckAuthentication method. ====

    /**
     * Stored challenge string for authentication checks.
     *
     * @var string|null
     */
    protected ?string $storedChallenge;

    /**
     * Stored credential string for authentication checks.
     *
     * @var string|null
     */
    protected ?string $storedCredential;

    /**
     * Stored 2FA authentication string for authentication checks.
     *
     * @var string|null
     */
    protected ?string $stored2FAuth;

    /**
     * Prepares and validates authentication for the CheckAuthentication operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool True if preparation is successful, false otherwise.
     */
    protected function prepareCheckAuthentication(OperationElement $e): bool
    {
        $proxy = $this->proxy;
        $authHandler = $proxy->dbClass->authHandler;
        $dbSettings = $proxy->dbSettings;
        $authDBHandler = $proxy->authDbClass->authHandler;

        [$uid, $proxy->signedUser, $proxy->hashedPassword]
            = $authHandler->authSupportUnifyUsernameAndEmailAndGetInfo($dbSettings->getCurrentUser());
        $dbSettings->setCurrentUser($proxy->signedUser);
        $authDBHandler->authSupportRemoveOutdatedChallenges();
        if (is_null($uid) || $uid <= 0) {
            Logger::getInstance()->setDebugMessage("[prepareCheckAuthentication] user id couldn't get from " . $dbSettings->getCurrentUser());
            return false;
        }
        if ($dbSettings->getIsSAML() && !$authHandler->authSupportIsWithinSAMLLimit($uid)) {
            Logger::getInstance()->setDebugMessage("[prepareCheckAuthentication] In case of SAML is active, it's over the limit length. " . $dbSettings->getCurrentUser());
            return false;
        }

        $falseHash = hash("sha256", uniqid("", true)); // for failing auth.
        $proxy->paramResponse ??= $falseHash;
        $proxy->paramResponse2m ??= $falseHash;
        $proxy->paramResponse2 ??= $falseHash;
        Logger::getInstance()->setDebugMessage("[prepareCheckAuthentication] user={$proxy->signedUser},  uid={$uid},"
            . "paramResponse={$proxy->paramResponse}, paramResponse2m={$proxy->paramResponse2m}, "
            . "paramResponse2={$proxy->paramResponse2}, clientid={$proxy->clientId}", 2);

        $this->storedChallenge = $authDBHandler->authSupportRetrieveChallenge(
            $uid, $proxy->clientId, true, "#");
        Logger::getInstance()->setDebugMessage(
            "[prepareCheckAuthentication] storedChallenge={$this->storedChallenge}", 2);

        $this->storedCredential = $authDBHandler->authSupportRetrieveChallenge(
            $uid, $proxy->clientId, true, "+");
        Logger::getInstance()->setDebugMessage(
            "[prepareCheckAuthentication] storedCredential={$this->storedCredential}", 2);

        if ($proxy->required2FA) {
            $proxy->code2FA = $this->storedCredential ? substr($this->storedCredential, 48, $proxy->digitsOf2FACode) : "";
            $this->storedCredential = $this->storedCredential ? substr($this->storedCredential, 0, 48) : "";

            $this->stored2FAuth = $authDBHandler->authSupportRetrieveChallenge(
                $uid, $proxy->clientId, true, "=");
            Logger::getInstance()->setDebugMessage(
                "[prepareCheckAuthentication] stored2FAuth={$this->stored2FAuth}", 2);
        }
        return true;
    }

    /**
     * Performs common authentication checks for the CheckAuthentication operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool True if authentication is successful, false otherwise.
     */
    protected function checkAuthenticationCommon(OperationElement $e): bool
    {
        $proxy = $this->proxy;
        Logger::getInstance()->setDebugMessage("[checkAuthenticationCommon] authStoring={$proxy->authStoring} required2FA={$proxy->required2FA}.", 2);

        if (strlen($proxy->signedUser) === 0) // Parameters required
        { // No username
            Logger::getInstance()->setDebugMessage("[checkAuthenticationCommon] Credential failed. No user info.", 2);
            $proxy->accessSetToNothing();  // Not Authenticated!
            return false;
        }

        switch ($proxy->authStoring) {
            case 'credential':
                if (strlen($proxy->credential) === 0) // Parameters required
                { // No username or password
                    Logger::getInstance()->setDebugMessage("[checkAuthenticationCommon] Credential failed. No credential.", 2);
                    $proxy->accessSetToNothing();  // Not Authenticated!
                    return false;
                }
                $referingCredential = $proxy->generateCredential(
                    $this->storedCredential, $proxy->clientId, $proxy->hashedPassword);
                Logger::getInstance()->setDebugMessage("[checkAuthenticationCommon] credential={$proxy->credential} "
                    . "storedChallenge={$this->storedChallenge} clientId={$proxy->clientId} hashedPassword={$proxy->hashedPassword}", 2);
                if ($proxy->credential === $referingCredential) {
                    if ($proxy->required2FA) {
                        if ($proxy->credential2FA === $proxy->generateCredential(
                                $this->stored2FAuth, $proxy->clientId, $proxy->hashedPassword)) {
                            Logger::getInstance()->setDebugMessage("[checkAuthenticationCommon] Credential and 2FA passed.", 2);
                            return true;
                        }
                    } else {
                        Logger::getInstance()->setDebugMessage("[checkAuthenticationCommon] Credential passed.", 2);
                        return true;
                    }
                }
                break;
            case 'session-storage':
                if (strlen($proxy->paramResponse) === 0 && strlen($proxy->paramResponse2m) === 0 && strlen($proxy->paramResponse2) === 0) { // password hash on
                    Logger::getInstance()->setDebugMessage("[checkAuthenticationCommon] Credential failed. No parameters.", 2);
                    $proxy->accessSetToNothing();  // Not Authenticated!
                    return false;
                }
                return $this->sessionStorageCheckAuth();
        }
        Logger::getInstance()->setDebugMessage("[checkAuthenticationCommon] Credential failed.", 2);
        return false;
    }

    /**
     * Performs authorization checks for the CheckAuthorization operation.
     *
     * @return bool True if authorization is successful, false otherwise.
     */
    protected function checkAuthorization(): bool
    {
        $proxy = $this->proxy;
        $authHandler = $proxy->dbClass->authHandler;
        $dbSettings = $proxy->dbSettings;
        $authorizedGroups = $authHandler->getAuthorizedGroups($proxy->access);
        $authorizedUsers = $authHandler->getAuthorizedUsers($proxy->access);

        if ((count($authorizedUsers) === 0 && count($authorizedGroups) === 0)) { // No user and group settings.
            Logger::getInstance()->setDebugMessage("[checkAuthorization] return true", 2);
            return true;
        } else {
            $belongGroups = $authHandler->authSupportGetGroupsOfUser($proxy->signedUser);

            Logger::getInstance()->setDebugMessage(str_replace("\n", "",
                ("[checkAuthorization] contextName={$dbSettings->getDataSourceName()}/access={$proxy->access}/"
                    . "signedUser={$proxy->signedUser}"
                    . " belongGroups=" . var_export($belongGroups, true))
                . "/authorizedUsers=" . var_export($authorizedUsers, true)
                . "/authorizedGroups=" . var_export($authorizedGroups, true)
            ), 2);

            if (in_array($proxy->signedUser, $authorizedUsers)) {
                Logger::getInstance()->setDebugMessage("[checkAuthorization] return true", 2);
                return true;
            } else {
                if (count($authorizedGroups) > 0 && count(array_intersect($belongGroups, $authorizedGroups)) != 0) {
                    Logger::getInstance()->setDebugMessage("[checkAuthorization] return true", 2);
                    return true;
                }
            }
        }
        Logger::getInstance()->setDebugMessage("[checkAuthorization] return false", 2);
        return false;
    }

    /**
     * Performs session storage authentication checks.
     *
     * @return bool True if authentication is successful, false otherwise.
     */
    protected function sessionStorageCheckAuth(): bool
    {
        $proxy = $this->proxy;
        $hmacValue = ($proxy->hashedPassword && $this->storedChallenge)
            ? hash_hmac('sha256', $proxy->hashedPassword, $this->storedChallenge) : 'no-value';
        $hmacValue2m = ($proxy->hashedPassword && $this->storedChallenge)
            ? hash_hmac('sha256', $proxy->hashedPassword, $this->storedChallenge) : 'no-value';
        Logger::getInstance()->setDebugMessage(
            "[sessionStorageCheckAuth] hashedPassword={$proxy->hashedPassword}/hmac_value={$hmacValue}", 2);
        if (strlen($proxy->hashedPassword) > 0) {
            if ($proxy->paramResponse === $hmacValue) {
                Logger::getInstance()->setDebugMessage("[sessionStorageCheckAuth] sha1 hash used.", 2);
                if ($proxy->migrateSHA1to2) {
                    $salt = hex2bin(substr($proxy->hashedPassword, -8));
                    $hashedPw = IMUtil::convertHashedPassword(
                        $proxy->hashedPassword, $proxy->passwordHash, true, $salt);
                    $proxy->dbClass->authHandler->authSupportChangePassword($proxy->signedUser, $hashedPw);
                }
                return true;
            } else if ($proxy->paramResponse2m === $hmacValue2m) {
                Logger::getInstance()->setDebugMessage("[sessionStorageCheckAuth] sha2 hash from sha1 hash used.", 2);
                return true;
            } else if ($proxy->paramResponse2 === $hmacValue) {
                Logger::getInstance()->setDebugMessage("[sessionStorageCheckAuth] sha2 hash used.", 2);
                return true;
            } else {
                Logger::getInstance()->setDebugMessage("[sessionStorageCheckAuth] Built-in authorization fail.", 2);
            }
        }
        return false;
    }

    // ==== Service methods for the visitDataOperation method. ====

    /**
     * Creates or replaces data in the database.
     *
     * @param string $access The access type (create or replace).
     * @return void
     * @throws Exception
     */
    protected function CreateReplaceImpl(string $access): void
    {
        Logger::getInstance()->setDebugMessage("[processingRequest] start create processing", 2);
        $proxy = $this->proxy;
        $dbSettings = $proxy->dbSettings;

        $tableInfo = $dbSettings->getDataSourceTargetArray();
        $attachedFields = $dbSettings->getAttachedFields();
        if (!$proxy->ignoreFiles && isset($attachedFields) && $attachedFields[0] === '_im_csv_upload') {
            Logger::getInstance()->setDebugMessage("CSV File importing operation gets stated.", 2);
            $uploadFiles = $dbSettings->getAttachedFiles($tableInfo['name']);
            if ($uploadFiles && count($tableInfo) > 0) {
                $fileUploader = new FileUploader();
                if (IMUtil::guessFileUploadError()) {
                    $fileUploader->processingAsError(
                        $dbSettings->getDataSource(),
                        $dbSettings->getOptions(),
                        $dbSettings->getDbSpec(), true,
                        $dbSettings->getDataSourceName(), true);
                } else {
                    $fileUploader->processingWithParameters(
                        $dbSettings->getDataSource(),
                        $dbSettings->getOptions(),
                        $dbSettings->getDbSpec(),
                        Logger::getInstance()->getDebugLevel(),
                        $tableInfo['name'], $tableInfo['key'], null,
                        $dbSettings->getAttachedFields(), $uploadFiles, true
                    );
                    $proxy->outputOfProcessing['dbresult'] = $fileUploader->dbresult;
                }
            }
        } else {
            if ($proxy->checkValidation()) {
                $uploadFiles = $dbSettings->getAttachedFiles($tableInfo['name']);
                if ($proxy->ignoreFiles || !$uploadFiles || count($tableInfo) < 1) { // No attached file.
                    $result = $proxy->createInDB($access === 'replace');
                    $proxy->outputOfProcessing['newRecordKeyValue'] = $result;
                    $proxy->outputOfProcessing['dbresult'] = $proxy->getUpdatedRecord();
                } else { // Some files are attached.
                    $fileUploader = new FileUploader();
                    if (IMUtil::guessFileUploadError()) { // Detect file upload error.
                        $fileUploader->processingAsError(
                            $dbSettings->getDataSource(),
                            $dbSettings->getOptions(),
                            $dbSettings->getDbSpec(), true,
                            $dbSettings->getDataSourceName(), true);
                    } else { // No file upload error.
                        $dbresult = [];
                        $result = $proxy->createInDB($access == 'replace');
                        $proxy->outputOfProcessing['newRecordKeyValue'] = $result;
                        $counter = 0;
                        foreach ($uploadFiles as $oneFile) {
                            $dbresult[] = $proxy->getUpdatedRecord()[0];
                            if ($result) {
                                $fileUploader->processingWithParameters(
                                    $dbSettings->getDataSource(),
                                    $dbSettings->getOptions(),
                                    $dbSettings->getDbSpec(),
                                    Logger::getInstance()->getDebugLevel(),
                                    $tableInfo['name'], $tableInfo['key'], $result,
                                    [$attachedFields[$counter]], [$oneFile], true
                                );
                            }
                            $proxy->outputOfProcessing['dbresult'] = $dbresult;
                            $counter += 1;
                        }
                    }
                }
            } else {
                Logger::getInstance()->setErrorMessage("Invalid data. Any validation rule was violated.");
            }
        }
    }

    // ==== Service methods for the visitHandleChallenge method. ====

    /**
     * Handles the default challenge response.
     *
     * @return void
     */
    protected function defaultHandleChallenge(): void
    {
        $proxy = $this->proxy;
        Logger::getInstance()->setDebugMessage("[handleChallenge] access={$proxy->access}, succeed={$proxy->authSucceed}", 2);

        if ($proxy->signedUser) {
            $userSalt = $proxy->authSupportGetSalt($proxy->signedUser);
            $proxy->generatedClientID = IMUtil::generateClientId('', $proxy->passwordHash);
            $challenge = $this->generateAndSaveChallenge($proxy->signedUser, $proxy->generatedClientID, "#");
            $proxy->outputOfProcessing['challenge'] = "{$challenge}{$userSalt}";
            if ($proxy->authSucceed) {
                $challenge = $this->generateAndSaveChallenge($proxy->signedUser, $proxy->generatedClientID, "+");
                if (!$proxy->hashedPassword) {
                    $proxy->hashedPassword = $proxy->dbClass->authHandler->authSupportRetrieveHashedPassword($proxy->signedUser);
                }
                if ($proxy->authStoring == 'credential') {
                    $this->setCookieOfChallenge('_im_credential_token',
                        $challenge, $proxy->generatedClientID, $proxy->hashedPassword);
                }
                if ($proxy->required2FA) { // 2FA final step
                    $challenge = $this->generateAndSaveChallenge($proxy->signedUser, $proxy->generatedClientID, "=");
                    $this->setCookieOfChallenge('_im_credential_2FA', $challenge, $proxy->generatedClientID, $proxy->hashedPassword);
                }
            }
        }
        if (!$proxy->authSucceed) {
            $this->clearAuthenticationCookies();
        }
    }

    /**
     * Generates and saves a challenge for the given user and client ID.
     *
     * @param string $user The user ID.
     * @param string $generatedClientID The client ID.
     * @param string $prefix The prefix for the challenge.
     * @param string $suffix The suffix for the challenge.
     * @return string The generated challenge.
     */
    public function generateAndSaveChallenge(string $user, string $generatedClientID, string $prefix, string $suffix = ""): string
    {
        $proxy = $this->proxy;
        $generated = IMUtil::generateChallenge();
        $generatedChallenge = $generated . $suffix;
        $proxy->saveChallenge($user, $generatedChallenge, $generatedClientID, $prefix);
        Logger::getInstance()->setDebugMessage("[generateAndSaveChallenge] challenge = {$prefix}{$generatedChallenge}", 2);
        return $generated;
    }

    /**
     * Sets a cookie for the given challenge.
     *
     * @param string $key The cookie key.
     * @param string $challenge The challenge value.
     * @param string $generatedClientID The client ID.
     * @param string $hashedPassword The hashed password.
     * @return void
     */
    protected function setCookieOfChallenge(string $key, string $challenge, string $generatedClientID, string $hashedPassword): void
    {
        Logger::getInstance()->setDebugMessage("[setCookieOfChallenge] key={$key} value{$challenge}/{$generatedClientID}/{$hashedPassword}", 2);
        $proxy = $this->proxy;
        $dbSettings = $proxy->dbSettings;
        setcookie($key,
            $proxy->generateCredential($challenge, $generatedClientID, $hashedPassword),
            time() + $dbSettings->getAuthenticationItem('authexpired'), '/',
            $proxy->credentialCookieDomain, false, true);
    }

    /**
     * Clears authentication cookies.
     *
     * @return void
     */
    protected function clearAuthenticationCookies(): void
    {
        setcookie("_im_credential_token", "", time() - 3600); // Should be removed.
        setcookie("_im_credential_2FA", "", time() - 3600); // Should be removed.
    }
}
