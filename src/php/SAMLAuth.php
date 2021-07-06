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

namespace INTERMediator;

use INTERMediator\DB\Proxy;
use SimpleSAML\Auth\Simple;

class SAMLAuth
{
    private $authSimple;
    private $samlAttrRules = false;
    private $samlAdditionalRules = false;

    public function __construct($authSource)
    {
        $this->authSimple = new Simple($authSource);
    }

    public function setSAMLAttrRules($value)
    {
        $this->samlAttrRules = $value;
    }

    public function setSAMLAdditionalRules($value)
    {
        $this->samlAdditionalRules = $value;
    }

    public function samlLoginCheck()
    {
        $user = false;
        if ($this->authSimple->isAuthenticated()) {
            if ($this->samlAdditionalRules) {
                $totalJudge = true;
                $attrs = $this->getValuesFromAttributes();
                foreach ($this->samlAdditionalRules as $key => $rule) {
                    if (!preg_match($rule, $attrs[$key])) {
                        $totalJudge = false;
                    }
                }
                if (!$totalJudge) {
                    $logoutURL = $this->samlLogoutURL($_SERVER['HTTP_REFERER']);
                    $loginURL = $this->samlLoginURL($_SERVER['HTTP_REFERER']);
                    header("Location: error.html");
//                    $session = \SimpleSAML\Session::getSessionFromRequest();
//                    $session->cleanup();
//                    $this->authSimple->logout("error.html");
                    return false;
                }
            }
            $rule = isset($this->samlAttrRules['username']) ? $this->samlAttrRules['username'] : 'uid|0';
            $user = $this->getValuesWithRule($rule);
        }
        return $user;
    }

    public function getAttributes()
    {
        return $this->authSimple->getAttributes();
    }

    public function getValuesFromAttributes()
    {
        $extArray = null;
        if ($this->samlAttrRules) {
            foreach ($this->samlAttrRules as $key => $rule) {
                $extArray[$key] = $this->getValuesWithRule($rule);
            }
        }
        return $extArray;
    }

    private function getValuesWithRule($rule)
    {
        $returnValue = null;
        $attributes = $this->authSimple->getAttributes();
        $comps = explode('|', $rule);
        if (count($comps) == 2 && isset($attributes[$comps[0]]) && isset($attributes[$comps[0]][$comps[1]])) {
            $returnValue = $attributes[$comps[0]][$comps[1]];
        } else if (isset($attributes[$rule])) {
            $returnValue = $attributes[$rule];
        }
        return $returnValue;
    }

    public function samlLoginURL($url = null)
    {
        return $this->authSimple->getLoginURL($url);
    }

    public function samlLogoutURL($url = null)
    {
        return $this->authSimple->getLogoutURL($url);
    }
}