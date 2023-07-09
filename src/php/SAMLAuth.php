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
        $additional = true;
        $user = null;
        if ($this->authSimple->isAuthenticated()) {
            $additional = true;
            if (is_array($this->samlAdditionalRules)) {
                $totalJudge = true;
                $attrs = $this->getValuesFromAttributes();
                foreach ($this->samlAdditionalRules as $key => $rule) {
                    if (isset($attrs[$key]) && !preg_match($rule, $attrs[$key])) {
                        $totalJudge = false;
                    }
                }
                if (!$totalJudge) {
                    $additional = false;
                    return [$additional, $user];
                }
            }
            $rule = $this->samlAttrRules['username'] ?? 'uid|0';
            $user = $this->getValuesWithRule($rule);
        }
        return [$additional, $user];
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
        if (isset($attributes[$comps[0]][$comps[1]]) && count($comps) == 2) {
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