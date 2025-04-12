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

namespace INTERMediator\Auth;

use INTERMediator\DB\Logger;
use SimpleSAML\Auth\Simple;

/**
 *
 */
class SAMLAuth
{
    /**
     * @var Simple
     */
    private Simple $authSimple;
    /**
     * @var array|null
     */
    private ?array $samlAttrRules = null;
    /**
     * @var array|null
     */
    private ?array $samlAdditionalRules = null;

    /**
     * @param string $authSource
     */
    public function __construct(string $authSource)
    {
        $this->authSimple = new Simple($authSource);
    }

    /**
     * @param ?array $value
     * @return void
     */
    public function setSAMLAttrRules(?array $value): void
    {
        $this->samlAttrRules = $value;
    }

    /**
     * @param null|array $value
     * @return void
     */
    public function setSAMLAdditionalRules(?array $value): void
    {
        $this->samlAdditionalRules = $value;
    }

    /**
     * @return array
     */
    public function samlLoginCheck(): array
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

    /**
     * @return array|null
     */
    public function getAttributes(): ?array
    {
        return $this->authSimple->getAttributes();
    }

    /**
     * @return array|null
     */
    public function getValuesFromAttributes(): ?array
    {
        $extArray = null;
        if ($this->samlAttrRules) {
            foreach ($this->samlAttrRules as $key => $rule) {
                $extArray[$key] = $this->getValuesWithRule($rule);
            }
        }
        return $extArray;
    }

    /**
     * @param string|array $rule
     * @return string
     */
    private function getValuesWithRule($rule): string
    {
        $returnValue = null;
        $attributes = $this->authSimple->getAttributes();
        if (is_array($rule)) {
            $returnValue = '';
            foreach ($rule as $item) {
                $returnValue = ((strlen($returnValue) > 0) ? ' ' : '') . $returnValue;
                $returnValue .= $this->getValuesWithRule($item);
            }
        } else {
            $comps = explode('|', $rule);
            if (isset($attributes[$comps[0]][$comps[1]]) && count($comps) === 2) {
                $returnValue = $attributes[$comps[0]][$comps[1]];
            } else if (isset($attributes[$rule])) {
                $returnValue = $attributes[$rule];
            }
        }
        if (is_null($returnValue)) {
            Logger::getInstance()->setWarningMessage('You have to set up the variable $samlAttrRules in params.php'
                . ' to get the any value from saml attributes.');
            $returnValue = '';
        }
        return $returnValue;
    }

    /**
     * @param string|null $url
     * @return string|null
     */
    public function samlLoginURL(?string $url = null): ?string
    {
        return $this->authSimple->getLoginURL($url);
    }

    /**
     * @param string|null $url
     * @return string|null
     */
    public function samlLogoutURL(?string $url = null): ?string
    {
        return $this->authSimple->getLogoutURL($url);
    }
}
