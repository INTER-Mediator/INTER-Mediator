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

/**
 * Class DefinitionChecker
 * Checks and validates INTER-Mediator definition arrays for prohibited or invalid keywords and types.
 *
 * @package INTERMediator
 */
class DefinitionChecker
{

    /**
     * Checks the main definitions (data source, options, db specification) for invalid or prohibited keywords.
     * @param array<array-key, mixed>|null $dataSource Data source definitions.
     * @param array<array-key, mixed>|null $options Options for INTER-Mediator.
     * @param array<array-key, mixed>|null $dbSpecification Database specification.
     * @return string Aggregated error or warning messages.
     */
    public function checkDefinitions(?array $dataSource, ?array $options, ?array $dbSpecification): string
    {
        $allMessage = '';
        if ($dataSource === NULL) {
            $allMessage .= "*** The Data Sources of the Definition must be specified. ***";
        }
        $this->checkDefinition($dataSource, $this->prohibitKeywordsForDataSource);
        if (strlen($this->message) > 0) {
            $allMessage .= "The Data Sources of the Definition: " . $this->message;
        }
        $this->checkDefinition($options, $this->prohibitKeywordsForOption);
        if (strlen($this->message) > 0) {
            $allMessage .= "The Options of the Definition: " . $this->message;
        }
        $this->checkDefinition($dbSpecification, $this->prohibitKeywordsForDBSpec);
        if (strlen($this->message) > 0) {
            $allMessage .= "The DB Specification of the Definition: " . $this->message;
        }
        return $allMessage;
    }

    /**
     * Checks a single definition array against a set of prohibited keywords and types.
     * @param array<array-key, mixed>|null $definition The definition array to check.
     * @param array<array-key, mixed> $prohibit The prohibited keywords and structure.
     * @return void
     */
    public function checkDefinition(?array $definition, array $prohibit): void
    {
        $this->message = '';
        if ($definition === NULL) {
            return;
        }
        $this->path = [];
        $this->currentProhibit = $prohibit;
        $this->moveChildren($definition);
    }

    /** Recursively traverses the definition items and checks for prohibited or invalid values.
     * @param mixed $items Items or values to check.
     * @return void
     */
    private function moveChildren(mixed $items): void
    {
        $endPoint = $this->currentProhibit;
        $currentPath = '';
        foreach ($this->path as $value) {
            $nextEndPoint = $this->findChild($endPoint, $value);
            if (is_null($nextEndPoint) && is_integer($value)) {
                $nextEndPoint = $endPoint['*'] ?? null;
            }
            if (is_null($nextEndPoint) && is_string($value)) {
                $nextEndPoint = $endPoint['#'] ?? null;
            }
            $endPoint = $nextEndPoint;
            $currentPath .= "[{$value}]";
        }
        if (is_array($endPoint)) {
            if (is_array($items)) {
                foreach ($items as $key => $value) {
                    $this->path[] = $key;
                    $this->moveChildren($value);
                    array_pop($this->path);
                }
            } else {
                $this->message .= "$currentPath should be defined as array. ";
            }
        } else if (is_null($endPoint)) {
            $this->message .= "$currentPath includes an undefined keyword. ";
        } else {
            $judge = false;
            $descriptions = [];
            foreach ($this->splitTypes((string)$endPoint) as $type) {
                $descriptions[] = $this->describeType($type);
                try {
                    if ($this->matchType($type, $items)) {
                        $judge = true;
                        break;
                    }
                } catch (\InvalidArgumentException $e) {
                    $this->message .= $e->getMessage();
                }
            }
            if (!$judge) {
                $this->message .= "$currentPath should be defined as " . implode(' or ', $descriptions) . ". ";
            }
        }
    }

    /** It looks up a child of the keyword structure, comparing string keys case-insensitively.
     * @param mixed $endPoint Current keyword structure.
     * @param int|string $key Key to look up.
     * @return mixed The child structure or type specification, or null if not found.
     */
    private function findChild(mixed $endPoint, int|string $key): mixed
    {
        if (!is_array($endPoint)) {
            return null;
        }
        if (isset($endPoint[$key])) {
            return $endPoint[$key];
        }
        if (is_string($key)) {
            foreach ($endPoint as $candidate => $child) {
                if (is_string($candidate) && strcmp($candidate, $key) === 0) {
                    return $child;
                }
            }
        }
        return null;
    }

    /** Splits a type specification by '|' outside of parentheses.
     * @param string $spec Type specification such as 'boolean|string(a|b)'.
     * @return string[] Individual type specifications.
     */
    private function splitTypes(string $spec): array
    {
        $result = [];
        $depth = 0;
        $current = '';
        foreach (str_split($spec) as $c) {
            if ($c === '(') {
                $depth++;
            } else if ($c === ')') {
                $depth--;
            }
            if ($c === '|' && $depth === 0) {
                $result[] = $current;
                $current = '';
            } else {
                $current .= $c;
            }
        }
        $result[] = $current;
        return $result;
    }

    /** Returns a human-readable description of a single type specification.
     * @param string $type Single type specification.
     * @return string Description.
     */
    private function describeType(string $type): string
    {
        if (preg_match('/^string\((.*)\)$/', $type, $matches)) {
            return "string within [{$matches[1]}]";
        }
        return $type;
    }

    /** Checks whether a value matches a single type specification.
     * @param string $type Single type specification.
     * @param mixed $items Value to check.
     * @return bool True if the value matches.
     */
    private function matchType(string $type, mixed $items): bool
    {
        switch ($type) {
            case 'string':
                return is_string($items);
            case 'scalar':
                return is_scalar($items);
            case 'boolean':
                return is_bool($items);
            case 'integer':
                return is_integer($items);
            case 'array':
                return is_array($items);
        }
        if (preg_match('/^string\((.*)\)$/', $type, $matches)) {
            if (!is_string($items)) {
                return false;
            }
            $words = preg_split('/\s+/', strtolower($items), -1, PREG_SPLIT_NO_EMPTY);
            if (empty($words)) {
                return false;
            }
            $possibleValues = explode('|', strtolower($matches[1]));
            foreach ($words as $word) {
                $isMatched = false;
                foreach ($possibleValues as $str) {
                    if ($str === $word || (str_contains($str, '*') && fnmatch($str, $word))) {
                        $isMatched = true;
                        break;
                    }
                }
                if (!$isMatched) {
                    return false;
                }
            }
            return true;
        }
        throw new \InvalidArgumentException("Unknown type specification: {$type}");
    }

    /** DefinitionChecker constructor.
     * Initializes prohibited keyword structures for data sources and messaging.
     */
    function __construct()
    {
        $this->prohibitKeywordsForDataSource['*']['send-mail'] = array(
            'load' => $this->prohibitKeywordsMessaging,
            'read' => $this->prohibitKeywordsMessaging,
            'new' => $this->prohibitKeywordsMessaging,
            'create' => $this->prohibitKeywordsMessaging,
            'edit' => $this->prohibitKeywordsMessaging,
            'update' => $this->prohibitKeywordsMessaging,
        );
        $this->prohibitKeywordsForDataSource['*']['messaging'] = array(
            'driver' => 'string',
            'load' => $this->prohibitKeywordsMessaging,
            'read' => $this->prohibitKeywordsMessaging,
            'new' => $this->prohibitKeywordsMessaging,
            'create' => $this->prohibitKeywordsMessaging,
            'edit' => $this->prohibitKeywordsMessaging,
            'update' => $this->prohibitKeywordsMessaging,
        );
    }

    /** @var string Holds the latest error or warning message.
     */
    private string $message = '';
    /**
     * @var array<array-key, mixed> Path stack for recursive checking.
     */
    private array $path = [];
    /**
     * @var array<array-key, mixed> Currently active prohibited keyword structure.
     */
    private array $currentProhibit;
    /**
     * @var array<array-key, mixed> Prohibited keywords and types for database specification.
     */
    private array $prohibitKeywordsForDBSpec = [
        'db-class' => 'string',
        'dsn' => 'string',
        'option' => 'array',
        'database' => 'string',
        'user' => 'string',
        'password' => 'string',
        'server' => 'string',
        'port' => 'string|integer',
        'protocol' => 'string',
        'datatype' => 'string',
        'external-db' => ['#' => 'string'],
        'cert-verifying' => 'boolean',
    ];
    /**
     * @var array<array-key, mixed> Prohibited keywords and types for options.
     */
    private array $prohibitKeywordsForOption = [
        'separator' => 'string',
        'formatter' => [
            '*' => [
                'field' => 'string',
                'converter-class' => 'string',
                'parameter' => 'string|boolean',
            ],
        ],
        'local-context' => [
            '*' => [
                'key' => 'string',
                'value' => 'string|boolean|integer',
            ],
        ],
        'aliases' => ['#' => 'string'],
        'browser-compatibility' => ['#' => 'string'],
        'transaction' => 'string(none|automatic)',
        'authentication' => [
            'user' => 'array',
            'group' => 'array',
            'user-table' => 'string',
            'group-table' => 'string',
            'corresponding-table' => 'string',
            'challenge-table' => 'string',
            'authexpired' => 'string|integer',
            'storing' => 'string(cookie|cookie-domainwide|session-storage|credential|passkey)',
            'realm' => 'string',
            'email-as-username' => 'boolean',
            'issuedhash-dsn' => 'string',
            'password-policy' => 'string',
            'enroll-page' => 'string',
            'reset-page' => 'string',
            'is-saml' => 'boolean',
            'saml-builtin-auth' => 'boolean',
            'is-required-2FA' => 'boolean',
            'digits-of-2FA-code' => 'integer',
            'mail-context-2FA' => 'string',
            'expiring-seconds-2FA' => 'integer',
            'passkey-only-on-auth' => 'boolean',
            'add-class-authn' => 'boolean',
            'passkey-error-alerting' => 'boolean',
            'method-2FA' => 'string(authenticator|email)',
            'is-pass-through-2FA' => 'boolean',
        ],
        'media-root-dir' => 'string',
        'upload-allowed-extensions' => 'array',
        'media-class-allowed' => 'array',
//        'media-context' => 'string',
        'smtp' => [
            'protocol' => 'string',
            'server' => 'string',
            'port' => 'string|integer',
            'encryption' => 'string',
            'username' => 'string',
            'password' => 'string',
        ],
        'slack' => [
            'token' => 'string',
            'channel' => 'string',
        ],
        'credit-including' => 'string',
        'theme' => 'string',
        'app-locale' => 'string',
        'app-currency' => 'string',
        'import' => [
            '1st-line' => 'boolean|string',
            'skip-lines' => 'integer',
            'format' => 'string(CSV|TSV)',
            'use-replace' => 'boolean',
            'encoding' => 'string',
            'convert-number' => ['*' => 'string'],
            'convert-date' => ['*' => 'string'],
            'convert-datetime' => ['*' => 'string'],
        ],
        'terms' => 'array',
    ];
    /**
     * @var array<array-key, mixed>|string[] Prohibited keywords and types for messaging.
     */
    private array $prohibitKeywordsMessaging = [
        'from' => 'string',
        'to' => 'string',
        'cc' => 'string',
        'bcc' => 'string',
        'subject' => 'string',
        'body' => 'string',
        'from-constant' => 'string',
        'to-constant' => 'string',
        'cc-constant' => 'string',
        'bcc-constant' => 'string',
        'subject-constant' => 'string',
        'body-constant' => 'string',
        'body-template' => 'string',
        'body-fields' => 'string',
        'f-option' => 'boolean',
        'body-wrap' => 'integer',
        'store' => 'string',
        'attachment' => 'string',
        'template-context' => 'string',
    ];
    /**
     * @var array<array-key, mixed>|array<array-key, array<array-key, mixed>> Prohibited keywords and types for data sources.
     */
    private array $prohibitKeywordsForDataSource = [
        '*' => [
            'name' => 'string',
            'table' => 'string',
            'view' => 'string',
            'count' => 'string',
            'source' => 'string',
            'portals' => ['*' => 'string'],
            'records' => 'integer',
            'maxrecords' => 'integer',
            'paging' => 'boolean',
            'key' => 'string',
            'sequence' => 'string',
            'relation' => [
                '*' => [
                    'foreign-key' => 'string',
                    'join-field' => 'string',
                    'operator' => 'string',
                    'portal' => 'boolean'
                ]
            ],
            'query' => [
                '*' => [
                    'field' => 'string',
                    'value' => 'scalar',
                    'operator' => 'string'
                ]
            ],
            'sort' => [
                '*' => [
                    'field' => 'string',
                    'direction' => 'string'
                ]
            ],
            'default-values' => [
                '*' => [
                    'field' => 'string',
                    'value' => 'scalar'
                ]
            ],
            'repeat-control' => 'string(insert|delete|insert-confirm|confirm-insert|delete-confirm|'
                .'confirm-delete|copy|copy-*|confirm-copy|confirm-copy-*|insert-top|confirm-top-insert)',
            'navi-control' => 'string(step|step-hide|step-nonavi|step-hide-nonavi|step-fullnavi|step-hide-fullnavi'
                . '|detail|detail-top|detail-bottom|detail-update|detail-top-update|detail-bottom-update'
                . '|master|master-nonavi|master-fullnavi|master-hide|master-hide-nonavi|master-hide-fullnavi)',
            'navi-title' => 'string',
            'sync-control' => 'string(update|update-notify|create|create-notify|delete)',
            'validation' => [
                '*' => [
                    'field' => 'string',
                    'rule' => 'string',
                    'message' => 'string',
                    'notify' => 'string(alert|inline|end-of-sibling)',
                ]
            ],
            'post-repeater' => 'string',
            'post-enclosure' => 'string',
            'post-query-stored' => 'string',
            'before-move-nextstep' => 'string',
            'just-move-thisstep' => 'string',
            'just-leave-thisstep' => 'string',
            'script' => [
                '*' => [
                    'db-operation' => 'string(load|read|update|new|create|delete)',
                    'situation' => 'string(pre|presort|post)',
                    'definition' => 'string',
                    'parameter' => 'string',
                ]
            ],
            'global' => [
                '*' => [
                    'db-operation' => 'string(load|read|update|new|create|delete)',
                    'field' => 'string',
                    'value' => 'scalar'
                ]
            ],
            'authentication' => [
                'media-handling' => 'boolean',
                'all' => [
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string',
                    'noset' => 'boolean',
                ],
                'load' => [
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string',
                    'noset' => 'boolean',
                ],
                'read' => [
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string',
                    'noset' => 'boolean',
                ],
                'update' => [
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string',
                    'noset' => 'boolean',
                ],
                'new' => [
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string',
                    'noset' => 'boolean',
                ],
                'create' => [
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string',
                    'noset' => 'boolean',
                ],
                'delete' => [
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string',
                    'noset' => 'boolean',
                ]
            ],
            'extending-class' => 'string',
            'numeric-fields' => 'array',
            'time-fields' => 'array',
            'protect-writing' => 'array',
            'protect-reading' => 'array',
            'db-class' => 'string',
            'dsn' => 'string',
            'option' => 'array',
            'database' => 'string',
            'user' => 'string',
            'password' => 'string',
            'server' => 'string',
            'port' => 'string|integer',
            'protocol' => 'string',
            'datatype' => 'string',
            'cert-verifying' => 'boolean',
            'cache' => 'boolean',
            'post-reconstruct' => 'boolean',
            'post-dismiss-message' => 'string',
            'post-move-url' => 'string',
            'soft-delete' => 'boolean|string',
            'aggregation-select' => 'string',
            'aggregation-from' => 'string',
            'aggregation-group-by' => 'string',
            'data' => 'array',
            'appending-data' => 'array',
            'no-default-values-on-copy' => 'boolean',
            'file-upload' => [
                '*' => [
                    'field' => 'string',
                    'context' => 'string',
                    'container' => 'boolean|string(FileSystem|FileMaker|FileMakerContainer|S3|Dropbox|FileURL)',
                    'file-name' => 'string',
                ]
            ],
            'calculation' => [
                '*' => [
                    'field' => 'string',
                    'expression' => 'string',
                ]
            ],
            'button-names' => [
                'insert' => 'string',
                'delete' => 'string',
                'navi-detail' => 'string',
                'navi-back' => 'string',
                'copy' => 'string',
            ],
            'confirm-messages' => [
                'insert' => 'string',
                'delete' => 'string',
                'copy' => 'string',
            ],
            'ignoring-field' => 'array',
            'import' => [
                '1st-line' => 'boolean|string',
                'skip-lines' => 'integer',
                'format' => 'string(CSV|TSV)',
                'use-replace' => 'boolean',
                'encoding' => 'string',
                'convert-number' => ['*' => 'string'],
                'convert-date' => ['*' => 'string'],
                'convert-datetime' => ['*' => 'string'],
            ],
        ], // There are additional definitions. See the constructor.
    ];
}
