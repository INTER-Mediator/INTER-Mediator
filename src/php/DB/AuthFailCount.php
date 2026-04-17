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
        $this->authHandler = $authHandler;
    }

    /** Checks whether the brute-force attack protection feature is enabled.
     * @return bool True if the fail rate threshold is greater than 0, meaning protection is active.
     */
    public function isActive(): bool
    {
        return $this->failRate > 0;
    }
    /** Determines whether an authentication attempt should be allowed based on a recent failure count.
     * @param string $ip The client IP address.
     * @param string|null $username The username attempting authentication.
     * @return bool True if the attempt is acceptable (not blocked), false if it should be blocked.
     */
    public function isAcceptableAuthFail(string $ip, string|null $username = ""): bool
    {
        if ($this->isActive() && $this->failRate < $this->getFailCount($ip, $username)) {
            return true;
        }
        return false;
    }

    /** Records an authentication failure for the given IP address and username.
     * @param string $ip The client IP address.
     * @param string $username The username that failed authentication.
     * @return void
     */
    public function addFailRecord(string $ip, string $username): void
    {
        if ($username) {
            $this->authHandler->authSupportAddAuthFail($ip, $username);
        }
    }

    /** Returns the number of authentication failures within the configured time window.
     * @param string $ip The client IP address.
     * @param string|null $username The username to filter by, or null to count all failures for the IP.
     * @return int The number of recent authentication failures.
     */
    public function getFailCount(string $ip, string|null $username = ""): int
    {
        return $this->authHandler->authSupportCheckAuthFailCount(
            $ip, $this->checkUsername ? $username : null, $this->seconds);
    }
}