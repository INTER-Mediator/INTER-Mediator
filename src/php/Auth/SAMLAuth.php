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

/**
 * SAMLAuth provides authentication and attribute handling for SAML-based authentication in INTER-Mediator.
 * It manages SAML login checks, attribute extraction, and login/logout URL generation.
 */
namespace INTERMediator\Auth;

use INTERMediator\DB\Logger;
use SimpleSAML\Auth\Simple;

/**
 * Class SAMLAuth
 * @package INTERMediator\Auth
 */
class SAMLAuth
{
    /**
     * SimpleSAMLphp authentication object for handling SAML authentication.
     * @var Simple
     */
    private Simple $authSimple;
    /**
     * SAML attribute extraction rules, mapping logical names to SAML attribute keys.
     * @var array|null
     */
    private ?array $samlAttrRules = null;
    /**
     * Additional SAML attribute rules for further validation.
     * @var array|null
     */
    private ?array $samlAdditionalRules = null;

    /**
     * Constructor initializes the SAML authentication object with the given source.
     *
     * @param string $authSource The SAML authentication source name.
     */
    public function __construct(string $authSource)
    {
        $this->authSimple = new Simple($authSource);
    }

    /**
     * Sets the SAML attribute extraction rules.
     *
     * @param array|null $value Attribute rules to use for extraction.
     * @return void
     */
    public function setSAMLAttrRules(?array $value): void
    {
        $this->samlAttrRules = $value;
    }

    /**
     * Sets additional SAML attribute rules for further validation.
     *
     * @param array|null $value Additional attribute rules for validation.
     * @return void
     */
    public function setSAMLAdditionalRules(?array $value): void
    {
        $this->samlAdditionalRules = $value;
    }

    /**
     * Checks SAML login status and validates additional rules if present.
     *
     * @return array [bool $additional, string|null $user] Whether additional rules passed and the username.
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
     * Returns all SAML attributes from the authentication object.
     *
     * @return array|null The SAML attributes, or null if unavailable.
     */
    public function getAttributes(): ?array
    {
        return $this->authSimple->getAttributes();
    }

    /**
     * Extracts values from SAML attributes according to configured rules.
     *
     * @return array|null Associative array of extracted attribute values, or null if no rules are set.
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
     * Extracts a value from SAML attributes using a rule string or array.
     *
     * @param string|array $rule Rule or array of rules for attribute extraction.
     * @return string The extracted value or an empty string if not found.
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
     * Returns the SAML login URL for redirecting the user to the identity provider.
     *
     * @param string|null $url Optional URL to redirect to after login.
     * @return string|null The login URL.
     */
    public function samlLoginURL(?string $url = null): ?string
    {
        return $this->authSimple->getLoginURL($url);
    }

    /**
     * Returns the SAML logout URL for redirecting the user to the identity provider.
     *
     * @param string|null $url Optional URL to redirect to after logout.
     * @return string|null The logout URL.
     */
    public function samlLogoutURL(?string $url = null): ?string
    {
        return $this->authSimple->getLogoutURL($url);
    }
}
