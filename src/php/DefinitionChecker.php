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

    /** Checks the main definitions (data source, options, db specification) for invalid or prohibited keywords.
     * @param array|null $dataSource Data source definitions.
     * @param array|null $options Options for INTER-Mediator.
     * @param array|null $dbSpecification Database specification.
     * @return string Aggregated error or warning messages.
     */
    public function checkDefinitions(?array $dataSource, ?array $options, ?array $dbSpecification): string
    {
//        if ($dbSpecification['db-class'] == 'FileMaker_FX') {
//            require_once('FileMaker_FX.php');
//        }
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

    /** Checks a single definition array against a set of prohibited keywords and types.
     * @param array|null $definition The definition array to check.
     * @param array $prohibit The prohibited keywords and structure.
     * @return void
     */
    public function checkDefinition(?array $definition, array $prohibit): void
    {
        if ($definition === NULL) {
            return;
        }
        $this->message = '';
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
            $nextEndPoint = $endPoint[$value] ?? null;
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
                $this->message .= "$currentPath should be define as array. ";
            }
        } else {
            $judge = false;
            if (is_null($endPoint)) {
                $this->message .= "$currentPath includes an undefined keyword. ";
            } else if ($endPoint === 'string') {
                if (!is_string($items)) {
                    $this->message .= "$currentPath should be define as string. ";
                }
            } else if ($endPoint === 'scalar') {
                if (!is_scalar($items)) {
                    $this->message .= "$currentPath should be define as string. ";
                }
            } else if ($endPoint === 'boolean') {
                if (!is_bool($items)) {
                    $this->message .= "$currentPath should be define as boolean. ";
                }
            } else if ($endPoint === 'integer') {
                if (!is_integer($items)) {
                    $this->message .= "$currentPath should be define as integer. ";
                }
            } else if ($endPoint === 'array') {
                if (!is_array($items)) {
                    $this->message .= "$currentPath should be define as array. ";
                }
            } else if (str_starts_with('string', $endPoint)) {
                $openParen = strpos('(', $endPoint);
                $closeParen = strpos(')', $endPoint);
                $possibleString = substr($endPoint, $openParen + 1, $closeParen - $openParen - 1);
                $possibleValues = explode("|", $possibleString);
                $possibleWilds = [];
                foreach ($possibleValues as $str) {
                    if (str_contains($str, '*')) {
                        $possibleWilds[] = $str;
                    }
                }
                if (in_array($items, $possibleValues)) {
                    $judge = true;
                } else {
                    foreach ($possibleWilds as $str) {
                        if (preg_match($str, $items)) {
                            $judge = true;
                            break;
                        }
                    }
                }
                if (!$judge) {
                    $this->message = "$currentPath should be define as string within [$possibleString]. ";
                }
            }
        }
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
    /** @var array Path stack for recursive checking.
     */
    private array $path = [];
    /** @var array Currently active prohibited keyword structure.
     */
    private array $currentProhibit;
    /** @var array Prohibited keywords and types for database specification.
     */
    private array $prohibitKeywordsForDBSpec = [
        'db-class' => 'string',
        'dsn' => 'string',
        'option' => 'array',
        'database' => 'string',
        'user' => 'string',
        'password' => 'string',
        'server' => 'string',
        'port' => 'string',
        'protocol' => 'string',
        'datatype' => 'string',
        'external-db' => ['#' => 'string'],
        'cert-verifying' => 'boolean',
    ];
    /** @var array Prohibited keywords and types for options.
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
            'storing' => 'string(cookie|cookie-domainwide|session-storage|credential)',
            'realm' => 'string',
            'email-as-username' => 'boolean',
            'issuedhash-dsn' => 'string',
            'password-policy' => 'string',
            'enroll-page' => 'string',
            'reset-page' => 'string',
            'is-saml' => 'boolean',
            'saml-builtin-auth' => 'boolean',
            'is-required-2FA' => 'boolean',
            'digits-of-2FA-Code' => 'integer',
            'mail-context-2FA' => 'string',
            'expiring-seconds-2FA' => 'interger',
            'passkey-only-on-auth' => 'boolean',
            'add-class-authn' => 'boolean',
            'passkey-error-alerting' => 'boolean',
        ],
        'media-root-dir' => 'string',
//        'media-context' => 'string',
        'smtp' => [
            'protocol' => 'string',
            'server' => 'string',
            'port' => 'integer',
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
    /** @var array|string[] Prohibited keywords and types for messaging.
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
    /** @var array|array[] Prohibited keywords and types for data sources.
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
            'repeat-control' => 'string(insert|delete|insert-confirm|confirm-insert|delete-confirm|confirm-delete|copy|copy-*)',
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
            'option' => 'string',
            'database' => 'string',
            'user' => 'string',
            'password' => 'string',
            'server' => 'string',
            'port' => 'string',
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
            'file-upload' => [
                '*' => [
                    'field' => 'string',
                    'context' => 'string',
                    'container' => 'boolean|string(FileSystem|FileMakerContainer|S3|Dropbox|FileURL)',
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
