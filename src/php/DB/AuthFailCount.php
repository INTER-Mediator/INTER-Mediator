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

namespace INTERMediator\DB;

use Exception;
use INTERMediator\DB\Support\Auth_Interface_CommonDB;
use INTERMediator\Params;

/**
 * Provides brute-force attack protection by tracking and evaluating authentication failure counts.
 * Determines whether further authentication attempts should be blocked based on the number of
 * recent failures recorded per IP address (and optionally per username).
 */
class AuthFailCount
{
    /** @var int The maximum number of authentication failures allowed before blocking. 0 or less disables blocking.
     */
    private int $failRate;
    /** @var bool Whether to also consider the username when counting failures.
     */
    private bool $checkUsername;
    /** @var int The time window in seconds to look back when counting failures.
     */
    private int $seconds;
    /** @var bool|int The inactivating limit of authentication failures.
     */
    private bool|int $inactivatingOnFails;

    /** @var Auth_Interface_CommonDB The authentication handler for database operations.
     */
    private Auth_Interface_CommonDB $authHandler;
    
    /** Constructs an AuthFailCount instance with configuration from parameters.
     * @param Auth_Interface_CommonDB $authHandler The authentication handler for database operations.
     */
    public function __construct(Auth_Interface_CommonDB $authHandler)
    {
        $this->failRate = Params::getParameterValue("authFailRate", 0);
        $this->checkUsername = Params::getParameterValue("checkUsername", false);
        $this->seconds = Params::getParameterValue("authFailSeconds", 60);
        $this->inactivatingOnFails = Params::getParameterValue('inactivatingOnFails', false);
        $this->authHandler = $authHandler;
    }

    /** Checks whether the brute-force attack protection feature is enabled.
     * @return bool True if the fail rate threshold is greater than 0, meaning protection is active.
     */
    public function isActiveBluteForce(): bool
    {
        return $this->failRate > 0;
    }

    /** Checks whether the account inactivation on repeated failures feature is enabled.
     * @return bool True if the inactivating threshold is greater than 0, meaning inactivation is active.
     */
    public function isActiveInactivating(): bool
    {
        return $this->inactivatingOnFails > 0;
    }

    /** Determines whether an authentication attempt should be allowed based on a recent failure count.
     * @param string $ip The client IP address.
     * @param string|null $username The username attempting authentication.
     * @return bool True if the attempt is acceptable (not blocked), false if it should be blocked.
     * @throws Exception
     */
    public function isAcceptableAuthFailBruteForce(string $ip, string|null $username = ""): bool
    {
        Logger::getInstance()->setDebugMessage("[AuthFailCount::isAcceptableAuthFailBruteForce] ip={$ip}, username={$username}", 2);

        if ($this->isActiveBluteForce() && $this->failRate < $this->getFailCount($ip, $username)) {
            return true;
        }
        return false;
    }

    /** Determines whether the recent authentication failure count has reached the threshold for inactivating the account.
     * @param string $ip The client IP address.
     * @param string $username The username attempting authentication.
     * @return bool True if inactivation is active and the failure count exceeds the threshold, false otherwise.
     * @throws Exception
     */
    public function isAcceptableAuthInActivating(string $ip, string $username): bool
    {
        $failCount = $this->getFailCountInActivating($ip, $username);
        Logger::getInstance()->setDebugMessage("[AuthFailCount::isAcceptableAuthInActivating] "
            . "ip={$ip}, username={$username}, failCount={$failCount}", 2);

        if ($this->isActiveInactivating() && $this->inactivatingOnFails < $failCount) {
            return true;
        }
        return false;
    }

    /** Records an authentication failure for the given IP address and username.
     * @param string $ip The client IP address.
     * @param string|null $username The username that failed authentication.
     * @return void
     * @throws Exception
     */
    public function addFailRecord(string $ip, string|null $username): void
    {
        Logger::getInstance()->setDebugMessage("[AuthFailCount::addFailRecord] ip={$ip}, username={$username}", 2);

        $this->authHandler->authSupportAddAuthFail($ip, $username);
        if ($this->isAcceptableAuthInActivating($ip, $username)) {
            $this->authHandler->authSupportSetInactive($username, true);
        }
    }

    /** Returns the number of authentication failures within the configured time window.
     * @param string $ip The client IP address.
     * @param string|null $username The username to filter by, or null to count all failures for the IP.
     * @return int The number of recent authentication failures.
     * @throws Exception
     */
    public function getFailCount(string $ip, string|null $username = ""): int
    {
        Logger::getInstance()->setDebugMessage("[AuthFailCount::getFailCount] ip={$ip}, username={$username}", 2);

        return $this->authHandler->authSupportCheckAuthFailCount(
            $ip, $this->checkUsername ? $username : null, $this->seconds);
    }

    /** Returns the number of authentication failures within the fixed inactivation time window (600 seconds).
     * @param string $ip The client IP address.
     * @param string $username The username to filter by.
     * @return int The number of recent authentication failures.
     * @throws Exception
     */
    public function getFailCountInActivating(string $ip, string $username): int
    {
        Logger::getInstance()->setDebugMessage("[AuthFailCount::getFailCountInActivating] ip={$ip}, username={$username}", 2);

        return $this->authHandler->authSupportCheckAuthFailCount($ip, $username, 600);
    }

    /** Checks whether the given user is marked inactive when the inactivation feature is enabled.
     * @param string $username The username to check.
     * @return bool True if inactivation is active and the user is marked inactive, false otherwise.
     * @throws Exception
     */
    public function getInactive(string $username): bool
    {
        Logger::getInstance()->setDebugMessage("[AuthFailCount::getInactive] username={$username}", 2);

        if ($this->isActiveInactivating()) {
            return $this->authHandler->authSupportIsInactive($username);
        }
        return false;
    }
}