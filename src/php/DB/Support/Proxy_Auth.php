<?php

/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace INTERMediator\DB\Support;

use Exception;
use INTERMediator\Auth\SAMLAuth;
use INTERMediator\DB\Logger;
use INTERMediator\DB\PDO;
use INTERMediator\DB\Support\ProxyElements\CheckAuthenticationElement;
use INTERMediator\DB\Support\ProxyElements\CheckAuthorizationElement;
use INTERMediator\DB\Support\ProxyElements\IsAuthAccessingElement;
use INTERMediator\IMUtil;
use INTERMediator\Params;

/**
 *
 */
trait Proxy_Auth
{
    /** Calling from Proxy::initialize method to initialize parameters for authentication and outholization.
     * @param array|null $options
     * @return void
     * @throws Exception
     */
    public function collectAuthInfo(?array $options): void
    {
        $this->dbSettings->setSAMLExpiringSeconds(Params::getParameterValue('ldapExpiringSeconds', 3600));
        $this->dbSettings->setSAMLExpiringSeconds(Params::getParameterValue('samlExpiringSeconds', 3600));
        $this->credentialCookieDomain = Params::getParameterValue('credentialCookieDomain', "");
        $this->passwordHash = Params::getParameterValue('passwordHash', 1);
        $this->alwaysGenSHA2 = boolval(Params::getParameterValue('alwaysGenSHA2', false));
        $this->migrateSHA1to2 = boolval(Params::getParameterValue('migrateSHA1to2', false));
        $emailAsAliasOfUserName = Params::getParameterValue('emailAsAliasOfUserName', false);
        $this->authStoring = $options['authentication']['storing']
            ?? Params::getParameterValue("authStoring", 'credential');
        if (strtolower($this->authStoring) === 'passkey') {
            $this->authStoring = "credential";
            $this->isPasskey = true;
        } else {
            $this->isPasskey = false;
        }
//        $this->authExpired = $options['authentication']['authexpired']
//            ?? Params::getParameterValue("authExpired", 3600);
//        $this->realm = $options['authentication']['realm']
//            ?? Params::getParameterValue("authRealm", '');
        $this->required2FA = $options['authentication']['is-required-2FA']
            ?? Params::getParameterValue("isRequired2FA", '');
        $this->digitsOf2FACode = $options['authentication']['digits-of-2FA-Code']
            ?? Params::getParameterValue("digitsOf2FACode", 4);
        $this->mailContext2FA = $options['authentication']['mail-context-2FA']
            ?? Params::getParameterValue("mailContext2FA", '');
        $this->dbSettings->setExpiringSeconds2FA($options['authentication']['expiring-seconds-2FA']
            ?? Params::getParameterValue("expiringSeconds2FA", 100000));

        /* Authentication and Authorization Judgment */
        $challengeDSN = $options['authentication']['issuedhash-dsn'] ?? Params::getParameterValue('issuedHashDSN', null);
        if (!is_null($challengeDSN)) {
            $this->authDbClass = new PDO();
            $this->authDbClass->setUpSharedObjects($this);
            $this->authDbClass->setupWithDSN($challengeDSN);
            $this->authDbClass->setupHandlers($challengeDSN);
            $this->logger->setDebugMessage(
                "The class 'PDO' was instantiated for issuedhash with {$challengeDSN}.", 2);
        } else {
            $this->authDbClass = $this->dbClass;
        }

        if (isset($options['authentication']['email-as-username'])) {
            $this->dbSettings->setEmailAsAccount($options['authentication']['email-as-username']);
        } else if (isset($emailAsAliasOfUserName) && $emailAsAliasOfUserName) {
            $this->dbSettings->setEmailAsAccount($emailAsAliasOfUserName);
        }
        $this->paramAuthUser = $this->PostData['authuser'] ?? "";
        $this->paramResponse = $this->PostData['response'] ?? "";
        $this->paramResponse2m = $this->PostData['response2m'] ?? "";
        $this->paramResponse2 = $this->PostData['response2'] ?? "";
        $this->credential = $_COOKIE['_im_credential_token'] ?? "";
        $this->credential2FA = $_COOKIE['_im_credential_2FA'] ?? "";
        $this->clientId = $this->PostData['clientid'] ?? ($_SERVER['REMOTE_ADDR'] ?? "Non-browser-client");

        $this->dbSettings->setMediaRoot($options['media-root-dir']
            ?? Params::getParameterValue('mediaRootDir', null) ?? null);

        $this->logger->setDebugMessage("Server side locale: " . setlocale(LC_ALL, "0"), 2);

        if (isset($options['authentication']['is-saml'])) {
            $this->dbSettings->setIsSAML($options['authentication']['is-saml']);
        } else {
            $this->dbSettings->setIsSAML(Params::getParameterValue('isSAML', false));
        }

        $this->dbSettings->setSAMLAuthSource(Params::getParameterValue('samlAuthSource', null));
        $this->dbSettings->setSAMLAttrRules(Params::getParameterValue("samlAttrRules", null));
        $this->dbSettings->setSAMLAdditionalRules(Params::getParameterValue("samlAdditionalRules", null));
    }

    /** Calling from Proxy::processing method to cheking the auth infos.
     */
    public function authenticationAndAuthorization(): void
    {
        if ($this->bypassAuth) {
            return;
        }
        $this->dbSettings->setRequireAuthentication(false);
        $isAuthAccessing = $this->visitor->isAuthAccessing();
        $this->dbSettings->setRequireAuthorization($isAuthAccessing || $this->checkAuthSettings());
        $this->authSucceed = false;
        if ($this->dbSettings->getRequireAuthorization()) { // Authentication required
            $this->logger->setDebugMessage("[authenticationAndAuthorization] Authentication process started.");
            if ($this->passwordHash != '1' || $this->alwaysGenSHA2) {
                $this->dbClass->authHandler->authSupportCanMigrateSHA256Hash();
            }
            if ($this->visitor->checkAuthentication()) {
                $this->dbSettings->setCurrentUser($this->signedUser);
                $this->logger->setDebugMessage("[authenticationAndAuthorization] IM-built-in Authentication succeed.");
                $this->authSucceed = true;
            } else { // Timeout with SAML or Authentication failed
                $this->dbSettings->setRequireAuthentication(true);
                if (!$this->dbSettings->getIsSAML()) { // NOT Set up as SAML
                    $this->logger->setDebugMessage("[authenticationAndAuthorization] Authentication doesn't meet valid."
                        . "{$this->signedUser}/{$this->paramResponse}/{$this->clientId}");
                    if (!$isAuthAccessing) {
                        $this->accessSetToNothing();  // Not Authenticated!
                    }
                } else if (!$isAuthAccessing) {  // Set up as SAML
                    $SAMLAuth = new SAMLAuth($this->dbSettings->getSAMLAuthSource());
                    $SAMLAuth->setSAMLAttrRules($this->dbSettings->getSAMLAttrRules());
                    $SAMLAuth->setSAMLAdditionalRules($this->dbSettings->getSAMLAdditionalRules());
                    [$additional, $this->signedUser] = $SAMLAuth->samlLoginCheck();
                    $this->logger->setDebugMessage("[authenticationAndAuthorization] SAML Auth result: user={$this->signedUser}, "
                        . "additional={$additional}, attributes=" . var_export($SAMLAuth->getAttributes(), true));
                    $this->outputOfProcessing['samlloginurl'] = $SAMLAuth->samlLoginURL($_SERVER['HTTP_REFERER']);
                    $this->outputOfProcessing['samllogouturl'] = $SAMLAuth->samlLogoutURL($_SERVER['HTTP_REFERER']);
                    if (!$additional) {
                        $this->outputOfProcessing['samladditionalfail'] = $SAMLAuth->samlLogoutURL($_SERVER['HTTP_REFERER']);
                    }
                    $this->paramAuthUser = $this->signedUser;
                    if ($this->signedUser) {
                        $attrs = $SAMLAuth->getValuesFromAttributes();
                        $this->logger->setDebugMessage(
                            "[authenticationAndAuthorization] SAML Authentication succeed. Attributes=" . var_export($attrs, true));
                        $this->authSucceed = true;
                        $password = IMUtil::generateRandomPW();
                        [$addResult, $hashedpw] = $this->addUser($this->signedUser, $password, true, $attrs);
                        if ($addResult) {
                            $this->dbSettings->setRequireAuthentication(false);
                            $this->dbSettings->setCurrentUser($this->signedUser);
                            $this->access = $this->originalAccess;
                            $this->outputOfProcessing['samluser'] = $this->signedUser;
                            $this->outputOfProcessing['temppw'] = $hashedpw;
                        }
                    }
                }
            }
            if (!$this->visitor->checkAuthorization() && $this->authSucceed) {// Checking authorization.
                Logger::getInstance()->setDebugMessage(
                    "[authenticationAndAuthorization] Authorization doesn't meet the settings.");
                $this->accessSetToNothing();  // Not Authenticated!
                $this->dbSettings->setRequireAuthentication(true);
                $this->authSucceed = false;
            }
        }
    }

    private function checkAuthSettings(): bool
    {
        $authOptions = $this->dbSettings->getAuthentication();
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $rule1 = !is_null($authOptions);
        //$rule2 = $this->isAuthAccessing();
        $rule3 = isset($tableInfo['authentication']['all']);
        $rule4 = isset($tableInfo['authentication'][$this->access]);
        return $rule1 /*|| $rule2 */ || $rule3 || $rule4;
    }

//    private function isAuthAccessing(): bool
//    {
//        return $this->access === 'challenge' || $this->access === 'changepassword'
//            || $this->access === 'credential' || $this->access === 'authenticated';
//    }

    /** @return void
     */
    public function accessSetToNothing()
    {
        $this->dbSettings->setRequireAuthentication(true);
        $this->access = "nothing";
        $visitorClasName = IMUtil::getActionHandlerClassName($this->access);
        $this->visitor = new $visitorClasName($this);
    }

    /** @param string $username
     * @param string $password
     * @param bool $isSAML
     * @param ?array $attrs
     * @return array
     */
    public function addUser(string $username, string $password, bool $isSAML = false, ?array $attrs = null): array
    {
        $this->logger->setDebugMessage("[addUser] username={$username}, isSAML={$isSAML}", 2);
        $hashedPw = IMUtil::convertHashedPassword($password, $this->passwordHash, $this->alwaysGenSHA2);
        $returnValue = $this->dbClass->authHandler->authSupportCreateUser($username, $hashedPw, $isSAML, $password, $attrs);
        $this->logger->setDebugMessage("[addUser] authSupportCreateUser returns: {$returnValue}", 2);
        return [$returnValue, $hashedPw];
    }

    /** Calling from Proxy::finishCommunication method to generate cookies.
     * @return void
     */
    public function handleMediaToken(): void
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        if (isset($tableInfo['authentication']['media-handling']) && $tableInfo['authentication']['media-handling'] === true && !$this->suppressMediaToken
        ) {
            $generatedChallenge = IMUtil::generateChallenge();
            $this->saveChallenge($this->paramAuthUser, $generatedChallenge, "_im_media");
            $cookieNameUser = '_im_username';
            $cookieNameToken = '_im_mediatoken';
            $realm = $this->dbSettings->getAuthenticationItem('realm');
            if ($realm) {
                $realm = str_replace(" ", "_", str_replace(".", "_", $realm));
                $cookieNameUser .= ('_' . $realm);
                $cookieNameToken .= ('_' . $realm);
            }
            setcookie($cookieNameToken, $generatedChallenge,
                time() + $this->dbSettings->getAuthenticationItem('authexpired'), '/',
                $this->credentialCookieDomain, false, true);
            setcookie($cookieNameUser, $this->paramAuthUser,
                time() + $this->dbSettings->getAuthenticationItem('authexpired'), '/',
                $this->credentialCookieDomain, false, false);
            $this->logger->setDebugMessage("mediatoken stored", 2);
        }
    }

    /** @param string|null $username The username as the username field of authuser table.
     * @return string
     */
    public function authSupportGetSalt(?string $username): ?string
    {
        if (is_null($username)) {
            return "";
        }
        $hashedpw = $this->proxy->hashedPassword ?? $this->dbClass->authHandler->authSupportRetrieveHashedPassword($username);
        if ($hashedpw) {
            return substr($hashedpw, -8);
        }
        return null;
    }

    /** @param string|null $username
     * @param string $challenge
     * @param string $clientId
     * @param string $prefix
     * @return void
     */
    public function saveChallenge(?string $username, string $challenge, string $clientId, string $prefix = ""): void
    {
        Logger::getInstance()->setDebugMessage(
            "[saveChallenge]user={$username}, challenge={$challenge}, clientid={$clientId}", 2);
        $username = $this->dbClass->authHandler->authSupportUnifyUsernameAndEmail($username);
        $uid = $this->dbClass->authHandler->authSupportGetUserIdFromUsername($username);
        $this->authDbClass->authHandler->authSupportStoreChallenge($uid, $challenge, $clientId, $prefix);
    }

// This method is just used to authenticate with database user

    /** @param string $challenge
     * @param string $clientId
     * @return bool
     */
//    function checkChallenge(string $challenge, string $clientId): bool
//    {
//        $returnValue = false;
//        $this->authDbClass->authHandler->authSupportRemoveOutdatedChallenges();
//        // Database user mode is user_id=0
//        $storedChallenge = $this->authDbClass->authHandler->authSupportRetrieveChallenge(0, $clientId);
//        if ($storedChallenge && strlen($storedChallenge) === 48 && $storedChallenge === $challenge) { // ex.fc0d54312ce33c2fac19d758
//            $returnValue = true;
//        }
//        return $returnValue;
//    }

    /** @param string $user
     * @param string $token
     * @return bool
     */
    public function checkMediaToken(string $user, string $token): bool
    {
        $this->logger->setDebugMessage("[checkMediaToken] user={$user}, token={$token}", 2);
        $returnValue = false;
        $this->authDbClass->authHandler->authSupportRemoveOutdatedChallenges();
        // Database user mode is user_id=0
        $user = $this->dbClass->authHandler->authSupportUnifyUsernameAndEmail($user);
        $uid = $this->dbClass->authHandler->authSupportGetUserIdFromUsername($user);
        if ($uid) {
            $storedChallenge = $this->authDbClass->authHandler->authSupportCheckMediaToken($uid);
            if (strlen($storedChallenge) === 48 && $storedChallenge === $token) { // ex.fc0d54312ce33c2fac19d758
                $returnValue = true;
            }
        }
        return $returnValue;
    }

    /** @param string|null $s1
     * @param string|null $s2
     * @param string|null $s3
     * @return string
     */
    public function generateCredential(?string $s1, ?string $s2, ?string $s3): string
    {
        return hash("sha256", $s1 . $s2 . $s3);
    }
}