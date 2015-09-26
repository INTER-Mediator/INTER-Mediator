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

class LDAPAuth
{
    public $errorString;
    public $isActive;

    private $server = "";
    private $port = 389;
    private $base = "";
    private $container = "";
    private $accountKey = "uid";
    private $logger = null;

    public function __construct()
    {
        $ldapServer = "";
        $ldapPort = "";
        $ldapBase = "";
        $ldapContainer = "";
        $ldapAccountKey = "";

        $currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $currentDirParam = $currentDir . 'params.php';
        $parentDirParam = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'params.php';
        if (file_exists($parentDirParam)) {
            include($parentDirParam);
        } else if (file_exists($currentDirParam)) {
            include($currentDirParam);
        }
        $this->server = $ldapServer;
        $this->port = $ldapPort;
        $this->base = $ldapBase;
        $this->container = $ldapContainer;
        $this->accountKey = $ldapAccountKey;

        $this->isActive = (strlen($this->server) > 0);
    }

    public function setLogger($log) {
        $this->logger = $log;
    }

    function bindCheck($username, $password)
    {
        if (! function_exists("ldap_connect"))  {
            $this->errorString = "This PHP doesn't support LDAP, check the result of infophp() function.";
            return false;
        }

        $this->errorString = "";
        if (! $this->isActive)  {
            $this->errorString = "LDAP Setting isn't supplied.";
            return false;
        }
        if (! $username || ! $password)  {
            $this->errorString = "Account Info isn't supplied.";
            return false;
        }
        $ds = ldap_connect($this->server, $this->port);
        if (!$ds) {
            $this->errorString = ldap_err2str(ldap_errno($ds));
            return false;
        }
        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
        $rdn = "{$this->accountKey}={$username},{$this->container},{$this->base}";
        try {
            $currentErrorReporting = error_reporting();
            error_reporting(0);
            $r = ldap_bind($ds, $rdn, $password);
            error_reporting($currentErrorReporting);
        } catch (Exception $e) {
            $this->errorString = ldap_err2str(ldap_errno($ds)) . " by {$rdn}";
            $r = false;
        }
        ldap_close($ds);
        if (strlen($this->errorString)) {
            $this->logger->setErrorMessage($this->errorString);
        }
        return $r;
    }
}