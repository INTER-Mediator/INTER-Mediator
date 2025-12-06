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

use INTERMediator\IMUtil;
use INTERMediator\NotifyServer;

/**
 *
 */
class Settings
{
    /** @var string|null
     */
    private ?string $dbSpecServer = null;
    /** @var string|null
     */
    private ?string $dbSpecPort = null;
    /** @var string|null
     */
    private ?string $dbSpecUser = null;
    /** @var string|null
     */
    private ?string $dbSpecPassword = null;
    /** @var string|null
     */
    private ?string $dbSpecDatabase = null;
    /** @var string|null
     */
    private ?string $dbSpecDataType = null;
    /** @var string|null
     */
    private ?string $dbSpecProtocol = null;
    /** @var string|null
     */
    private ?string $dbSpecDSN = null;
    /** @var array|null
     */
    private ?array $dbSpecOption = null;

    /** @var array|null
     */
    private ?array $dataSource = null;
    /** @var array|null
     */
    private ?array $options = null;
    /** @var array|null
     */
    private ?array $dbSpec = null;
    /** @var string
     */
    private string $dataSourceName = '';
    /** @var int
     */
    private int $recordCount = 0;
    /** @var int
     */
    private int $start = 0;
    /** @var string|null
     */
    private ?string $separator = null;

    /** @var array
     */
    private array $extraCriteria = array();
    /** @var array
     */
    private array $extraSortKey = array();
    /** @var array
     */
    private array $fieldsRequired = array();
    /** @var array
     */
    private array $fieldsValues = array();
    /** @var array
     */
    private array $foreignFieldAndValue = array();
    /** @var DBClass|null
     */
    private ?DBClass $currentDataAccess = null;

    /** @var string|null
     */
    private ?string $currentUser = null;
    /** @var array|null
     */
    private ?array $authentication = null;
    /** @var string|null
     */
    private ?string $accessUser = null;
    /** @var string|null
     */
    private ?string $accessPassword = null;
    /** @var bool
     */
    private bool $primaryKeyOnly = false;
    /** @var bool
     */
    private bool $isDBNative = false;
    /** @var bool
     */
    private bool $requireAuthorization = false;
    /** @var bool
     */
    private bool $requireAuthentication = false;

    /** @var bool
     */
    private bool $emailAsAccount = false;
    /** @var array|null
     */
    private ?array $smtpConfiguration = null;
    /** @var array|null
     */
    private ?array $associated = null;
    /** @var NotifyServer|null
     */
    public ?NotifyServer $notifyServer = null;
    /** @var string
     */
    public string $registerTableName = "registeredcontext";
    /** @var string
     */
    public string $registerPKTableName = "registeredpks";
    /** @var int
     */
    private int $params_samlExpiringSeconds;
    /** @var string|null
     */
    private ?string $params_mediaRoot;
    /** @var bool
     */
    private bool $isSAML = false;
    /** @var string|null
     */
    private ?string $samlAuthSource = '';
    /** @var array|null
     */
    private ?array $samlAttrRules = null;
    /** @var array|null
     */
    private ?array $samlAdditionalRules = null;

    /** @var string|null
     */
    private ?string $aggregation_select = null;
    /** @var string|null
     */
    private ?string $aggregation_from = null;
    /** @var string|null
     */
    private ?string $aggregation_group_by = null;

    /** @var array
     */
    private array $attachedFiles = [];
    /** @var array|null
     */
    private ?array $attachedFields = null;
    /** @var bool
     */
    private bool $certVerifying = true;
    /** @var int
     */
    private int $timezoneOffset = 0;
    /** @var int
     */
    private int $expiringSeconds2FA = 100000;

    /** Get the expiring seconds for 2FA.
     * @return int Expiring seconds for 2FA.
     */
    public function getExpiringSeconds2FA(): int
    {
        return $this->expiringSeconds2FA;
    }

    /** Set the expiring seconds for 2FA.
     * @param int $n Expiring seconds for 2FA.
     * @return void
     */
    public function setExpiringSeconds2FA(int $n): void
    {
        $this->expiringSeconds2FA = $n;
    }

    /** @var string
     */
    private string $parentOfTarget = '';

    /** Set the parent of the target.
     * @param string $cName Parent of the target.
     * @return void
     */
    public function setParentOfTarget(string $cName): void
    {
        $this->parentOfTarget = $cName;
    }

    /** Get the parent of the target.
     * @return string Parent of the target.
     */
    public function getParentOfTarget(): string
    {
        return $this->parentOfTarget;
    }

    /** Set the client timezone offset.
     * @param int $offset Client timezone offset.
     * @return void
     */
    public function setClientTZOffset(int $offset): void
    {
        $this->timezoneOffset = $offset;
    }

    /** Get the client timezone offset.
     * @return int Client timezone offset.
     */
    public function getClientTZOffset(): int
    {
        return $this->timezoneOffset;
    }

    /** Set the SAML additional rules.
     * @param array|null $value SAML additional rules array.
     * @return void
     */
    public function setSAMLAdditionalRules(?array $value): void
    {
        $this->samlAdditionalRules = $value;
    }

    /** Get the SAML additional rules.
     * @return array|null SAML additional rules array.
     */
    public function getSAMLAdditionalRules(): ?array
    {
        return $this->samlAdditionalRules;
    }

    /** Set the SAML attribute rules.
     * @param array|null $value SAML attribute rules array.
     * @return void
     */
    public function setSAMLAttrRules(?array $value): void
    {
        $this->samlAttrRules = $value;
    }

    /** Get the SAML attribute rules.
     * @return array|null SAML attribute rules array.
     */
    public function getSAMLAttrRules(): ?array
    {
        return $this->samlAttrRules;
    }

    /** Set the SAML authentication source.
     * @param string|null $value SAML authentication source.
     * @return void
     */
    public function setSAMLAuthSource(?string $value): void
    {
        $this->samlAuthSource = $value;
    }

    /** Get the SAML authentication source.
     * @return string|null SAML authentication source.
     */
    public function getSAMLAuthSource(): ?string
    {
        return $this->samlAuthSource;
    }

    /** Set whether SAML is enabled.
     * @param bool $value True to enable SAML, false otherwise.
     * @return void
     */
    public function setIsSaml(bool $value): void
    {
        $this->isSAML = $value;
    }

    /** Get whether SAML is enabled.
     * @return bool True if SAML is enabled, false otherwise.
     */
    public function getIsSaml(): bool
    {
        return $this->isSAML;
    }

    /** Set whether to verify certificates.
     * @param bool $value True to verify certificates, false otherwise.
     * @return void
     */
    public function setCertVerifying(bool $value): void
    {
        $this->certVerifying = $value;
    }

    /** Get whether certificates are verified.
     * @return bool True if certificates are verified, false otherwise.
     */
    public function getCertVerifying(): bool
    {
        return $this->certVerifying;
    }

    /** Set attached files for a context.
     * @param string $contextName The context name.
     * @param array $files Array of attached files.
     * @return void
     */
    public function setAttachedFiles(string $contextName, array $files): void
    {
        if ($contextName && count($files) > 0) {
            $this->attachedFiles[$contextName] = $files;
            if (isset($_POST['_im_filesfields'])) {
                $this->attachedFields = explode(',', $_POST['_im_filesfields']);
            }
        }
    }

    /** Get attached files for a context.
     * @param string $contextName The context name.
     * @return array|null Array of attached files or null if not set.
     */
    public function getAttachedFiles(string $contextName): ?array
    {
        if ($contextName && $this->attachedFiles && isset($this->attachedFiles[$contextName])) {
            return $this->attachedFiles[$contextName];
        }
        return null;
    }

    /** Get attached fields.
     * @return array|null Array of attached fields or null if not set.
     */
    public function getAttachedFields(): ?array
    {
        return $this->attachedFields;
    }

    /** Settings constructor.
     */
    function __construct()
    {
    }

    /** Get the aggregation SELECT clause.
     * @return string|null Aggregation SELECT clause.
     */
    public function getAggregationSelect(): ?string
    {
        return $this->aggregation_select;
    }

    /** Set the aggregation SELECT clause.
     * @param string|null $value Aggregation SELECT clause.
     * @return void
     */
    public function setAggregationSelect(?string $value): void
    {
        $this->aggregation_select = $value;
    }

    /** Get the aggregation FROM clause.
     * @return string|null Aggregation FROM clause.
     */
    public function getAggregationFrom(): ?string
    {
        return $this->aggregation_from;
    }

    /** Set the aggregation FROM clause.
     * @param string|null $value Aggregation FROM clause.
     * @return void
     */
    public function setAggregationFrom(?string $value): void
    {
        $this->aggregation_from = $value;
    }

    /** Get the aggregation GROUP BY clause.
     * @return string|null Aggregation GROUP BY clause.
     */
    public function getAggregationGroupBy(): ?string
    {
        return $this->aggregation_group_by;
    }

    /** Set the aggregation GROUP BY clause.
     * @param string|null $value Aggregation GROUP BY clause.
     * @return void
     */
    public function setAggregationGroupBy(?string $value): void
    {
        $this->aggregation_group_by = $value;
    }

    /** Add an associated context/field/value tuple.
     * @param string|null $name Associated context name.
     * @param string|null $field Associated field name.
     * @param string|null $value Associated value.
     * @return void
     */
    public function addAssociated(?string $name, ?string $field, ?string $value): void
    {
        if (!$this->associated) {
            $this->associated = array();
        }
        $this->associated[] = array("name" => $name, "field" => $field, "value" => $value);
    }

    /** Get the associated array.
     * @return array|null Associated array.
     */
    public function getAssociated(): ?array
    {
        return $this->associated;
    }

    /** Set SMTP configuration.
     * @param array|null $config SMTP configuration array.
     * @return void
     */
    public function setSmtpConfiguration(?array $config): void
    {
        if (is_null($config)) {
            $this->smtpConfiguration = null;
            return;
        }
        $this->smtpConfiguration = [];
        if (isset($config["server"])) {
            $this->smtpConfiguration["server"] = $config["server"];
        }
        if (isset($config["protocol"])) {
            $this->smtpConfiguration["protocol"] = $config["protocol"];
        }
        if (isset($config["port"])) {
            $this->smtpConfiguration["port"] = $config["port"];
        }
        if (isset($config["username"])) {
            $this->smtpConfiguration["username"] = IMUtil::getFromProfileIfAvailable($config["username"]);
        }
        if (isset($config["password"])) {
            $this->smtpConfiguration["password"] = IMUtil::getFromProfileIfAvailable($config["password"]);
        }
    }

    /** Get SMTP configuration.
     * @return array|null SMTP configuration array.
     */
    public function getSmtpConfiguration(): ?array
    {
        return $this->smtpConfiguration;
    }

    /** Set the data source name.
     * @param string $dataSourceName Data source name.
     * @return void
     */
    public function setDataSourceName(string $dataSourceName): void
    {
        $this->dataSourceName = $dataSourceName;
    }

    /** Check if a context exists.
     * @param string $contextName Context name.
     * @return bool True if context exists, false otherwise.
     */
    public function isExistContext(string $contextName): bool
    {
//        if (!$this->dataSourceName || !is_array($this->dataSourceName)) {
//            return false;
//        }
        foreach ($this->dataSource as $contextDef) {
            if (isset($contextDef['name']) && $contextDef['name'] == $contextName) {
                return true;
            }
        }
        return false;
    }

    /** Get the data source name.
     * @return string Data source name.
     */
    public function getDataSourceName(): string
    {
        return $this->dataSourceName;
    }

    /** Set the fields required.
     * @param array|null $fieldsRequired Fields required array.
     * @return void
     */
    public function setFieldsRequired(?array $fieldsRequired): void
    {
        $this->fieldsRequired = $fieldsRequired;
    }

    /** Get the fields required.
     * @return array|null Fields required array.
     */
    public function getFieldsRequired(): ?array
    {
        return $this->fieldsRequired;
    }

    /** Get the value.
     * @return array|null Value array.
     */
    public function getValue(): ?array
    {
        return $this->fieldsValues;
    }

    /** Get the values with fields.
     * @return array|null Values with fields array.
     */
    public function getValuesWithFields(): ?array
    {
        $result = array();
        $requiredFields = $this->getFieldsRequired();
        $countFields = count($requiredFields);
        $fieldValues = $this->getValue();
        for ($i = 0; $i < $countFields; $i++) {
            $field = $requiredFields[$i];
            $value = $fieldValues[$i];
            $result[$field] = $value;
        }
        return $result;
    }

    /** Add a value with field.
     * @param string|null $field Field name.
     * @param string|null $value Field value.
     * @return void
     */
    public function addValueWithField(?string $field, ?string $value): void
    {
        $this->fieldsValues[] = $value;
        $this->fieldsRequired[] = $field;
    }

    /** Set the foreign field and value.
     * @param array|null $foreignFieldAndValue Foreign field and value array.
     * @return void
     */
    public function setForeignFieldAndValue(?array $foreignFieldAndValue): void
    {
        $this->foreignFieldAndValue = $foreignFieldAndValue;
    }

    /** Get the foreign field and value.
     * @return array|null Foreign field and value array.
     */
    public function getForeignFieldAndValue(): ?array
    {
        return $this->foreignFieldAndValue;
    }

    /** Set whether the database is native.
     * @param bool $isDBNative True if database is native, false otherwise.
     * @return void
     */
    public function setDBNative(bool $isDBNative): void
    {
        $this->isDBNative = $isDBNative;
    }

    /** Get whether the database is native.
     * @return bool True if database is native, false otherwise.
     */
    public function isDBNative(): bool
    {
        return $this->isDBNative;
    }

    /** Set whether authentication is required.
     * @param bool $requireAuthentication True to require authentication, false otherwise.
     * @return void
     */
    public function setRequireAuthentication(bool $requireAuthentication): void
    {
        $this->requireAuthentication = $requireAuthentication;
    }

    /** Get whether authentication is required.
     * @return bool True if authentication is required, false otherwise.
     */
    public function getRequireAuthentication(): bool
    {
        return $this->requireAuthentication;
    }

    /** Set whether authorization is required.
     * @param bool $requireAuthorization True to require authorization, false otherwise.
     * @return void
     */
    public function setRequireAuthorization(bool $requireAuthorization): void
    {
        $this->requireAuthorization = $requireAuthorization;
    }

    /** Get whether authorization is required.
     * @return bool True if authorization is required, false otherwise.
     */
    public function getRequireAuthorization(): bool
    {
        return $this->requireAuthorization;
    }

    /** Set whether to use primary key only.
     * @param bool $primaryKeyOnly True to use primary key only, false otherwise.
     * @return void
     */
    public function setPrimaryKeyOnly(bool $primaryKeyOnly): void
    {
        $this->primaryKeyOnly = $primaryKeyOnly;
    }

    /** Get whether to use primary key only.
     * @return bool True if using primary key only, false otherwise.
     */
    public function getPrimaryKeyOnly(): bool
    {
        return $this->primaryKeyOnly;
    }

    /** Set whether to use email as account.
     * @param bool $emailAsAccount True to use email as account, false otherwise.
     * @return void
     */
    public function setEmailAsAccount(bool $emailAsAccount): void
    {
        $this->emailAsAccount = $emailAsAccount;
    }

    /** Get whether to use email as account.
     * @return bool True if using email as account, false otherwise.
     */
    public function getEmailAsAccount(): bool
    {
        return $this->emailAsAccount;
    }

    /** Get the current data access.
     * @return DBClass Current data access.
     */
    public function getCurrentDataAccess(): DBClass
    {
        return $this->currentDataAccess;
    }

    /** Set the current data access.
     * @param DBClass $dbaccess Current data access.
     * @return void
     */
    public function setCurrentDataAccess(DBClass $dbaccess): void
    {
        $this->currentDataAccess = $dbaccess;
    }

    /** Set the database specification server.
     * @param string|null $str Database specification server.
     * @return void
     */
    public function setDbSpecServer(?string $str): void
    {
        $this->dbSpecServer = $str;
    }

    /** Get the database specification server.
     * @return string|null Database specification server.
     */
    public function getDbSpecServer(): ?string
    {
        return $this->dbSpecServer;
    }

    /** Set the database specification port.
     * @param string|null $str Database specification port.
     * @return void
     */
    public function setDbSpecPort(?string $str): void
    {
        $this->dbSpecPort = $str;
    }

    /** Get the database specification port.
     * @return string|null Database specification port.
     */
    public function getDbSpecPort(): ?string
    {
        return $this->dbSpecPort;
    }

    /** Set the database specification user.
     * @param string|null $str Database specification user.
     * @return void
     */
    public function setDbSpecUser(?string $str): void
    {
        $this->dbSpecUser = $str;
    }

    /** Get the database specification user.
     * @return string|null Database specification user.
     */
    public function getDbSpecUser(): ?string
    {
        return $this->dbSpecUser;
    }

    /** Set the database specification password.
     * @param string|null $str Database specification password.
     * @return void
     */
    public function setDbSpecPassword(?string $str): void
    {
        $this->dbSpecPassword = $str;
    }

    /** Get the database specification password.
     * @return string|null Database specification password.
     */
    public function getDbSpecPassword(): ?string
    {
        return $this->dbSpecPassword;
    }

    /** Set the database specification data type.
     * @param string|null $str Database specification data type.
     * @return void
     */
    public function setDbSpecDataType(?string $str): void
    {
        $this->dbSpecDataType = $str;
    }

    /** Get the database specification data type.
     * @return string|null Database specification data type.
     */
    public function getDbSpecDataType(): ?string
    {
        return is_null($this->dbSpecDataType) ? "FMPro12" : $this->dbSpecDataType;
    }

    /** Set the database specification database.
     * @param string|null $str Database specification database.
     * @return void
     */
    public function setDbSpecDatabase(?string $str): void
    {
        $this->dbSpecDatabase = $str;
    }

    /** Get the database specification database.
     * @return string|null Database specification database.
     */
    public function getDbSpecDatabase(): ?string
    {
        return $this->dbSpecDatabase;
    }

    /** Set the database specification protocol.
     * @param string|null $str Database specification protocol.
     * @return void
     */
    public function setDbSpecProtocol(?string $str): void
    {
        $this->dbSpecProtocol = $str;
    }

    /** Get the database specification protocol.
     * @return string|null Database specification protocol.
     */
    public function getDbSpecProtocol(): ?string
    {
        return $this->dbSpecProtocol;
    }

    /** Set the database specification DSN.
     * @param string|null $str Database specification DSN.
     * @return void
     */
    public function setDbSpecDSN(?string $str): void
    {
        $this->dbSpecDSN = $str;
    }

    /** Get the database specification DSN.
     * @return string|null Database specification DSN.
     */
    public function getDbSpecDSN(): ?string
    {
        return $this->dbSpecDSN;
    }

    /** Set the database specification option.
     * @param array|null $options Database specification option array.
     * @return void
     */
    public function setDbSpecOption(?array $options): void
    {
        $this->dbSpecOption = $options;
    }

    /** Get the database specification option.
     * @return array|null Database specification option array.
     */
    public function getDbSpecOption(): ?array
    {
        return $this->dbSpecOption;
    }

    /** Get the access user.
     * @return string|null Access user.
     */
    public function getAccessUser(): ?string
    {
        return $this->accessUser ?? $this->dbSpecUser;
    }

    /** Get the access password.
     * @return string|null Access password.
     */
    public function getAccessPassword(): ?string
    {
        return $this->accessPassword ?? $this->dbSpecPassword;
    }

    /** Set the user and password for access.
     * @param string|null $user User for access.
     * @param string|null $pass Password for access.
     * @return void
     */
    public function setUserAndPasswordForAccess(?string $user, ?string $pass): void
    {
        $this->accessUser = $user;
        $this->accessPassword = $pass;
    }

    /* Call on INTER-Mediator.php */

    /** Set the authentication.
     * @param array|null $authentication Authentication array.
     * @return void
     */
    public function setAuthentication(?array $authentication): void
    {
        if (isset($authentication['authexpired']) && $authentication['authexpired'] == 0) {
            $authentication['authexpired'] = $this->getAuthenticationItem('authexpired');
        }
        $this->authentication = $authentication;
    }

    /** Get the authentication.
     * @return array|null Authentication array.
     */
    public function getAuthentication(): ?array
    {
        return $this->authentication;
    }

    /** Get an authentication item.
     * @param string|null $key Authentication item key.
     * @return float|int|mixed|string|null Authentication item value.
     */
    public function getAuthenticationItem(?string $key): mixed
    {
        if (isset($this->authentication[$key])) {
            return $this->authentication[$key];
        }
        return match ($key) {
            'user-table' => 'authuser',
            'group-table' => 'authgroup',
            'corresponding-table' => 'authcor',
            'challenge-table' => 'issuedhash',
            'authexpired' => 3600 * 8,
            'storing' => 'credential',
            default => null,
        };
    }

    /** Get the user table.
     * @return string|null User table.
     */
    public function getUserTable(): ?string
    {
        return $this->getAuthenticationItem('user-table');
    }

    /** Get the group table.
     * @return string|null Group table.
     */
    public function getGroupTable(): ?string
    {
        return $this->getAuthenticationItem('group-table');
    }

    /** Get the corresponding table.
     * @return string|null Corresponding table.
     */
    public function getCorrTable(): ?string
    {
        return $this->getAuthenticationItem('corresponding-table');
    }

    /** Get the hash table.
     * @return string|null Hash table.
     */
    public function getHashTable(): ?string
    {
        return $this->getAuthenticationItem('challenge-table');
    }

    /** Get the expiring seconds.
     * @return int Expiring seconds.
     */
    public function getExpiringSeconds(): int
    {
        return $this->getAuthenticationItem('authexpired');
    }

    /** Set the SAML expiring seconds.
     * @param int $sec SAML expiring seconds.
     * @return void
     */
    public function setSAMLExpiringSeconds(int $sec): void
    {
        $this->params_samlExpiringSeconds = $sec;
    }

    /** Get the SAML expiring seconds.
     * @return int SAML expiring seconds.
     */
    public function getSAMLExpiringSeconds(): int
    {
        return $this->params_samlExpiringSeconds;
    }

    /** Set the current user.
     * @param string|null $str Current user.
     * @return void
     */
    public function setCurrentUser(?string $str): void
    {
        $this->currentUser = $str;
    }

    /** Get the current user.
     * @return string|null Current user.
     */
    public function getCurrentUser(): ?string
    {
        return $this->currentUser;
    }

    /** Set the data source.
     * @param array|null $src Data source array.
     * @return void
     */
    public function setDataSource(?array $src): void
    {
        $this->dataSource = $src;
    }

    /** Get the data source.
     * @return array|null Data source array.
     */
    public function getDataSource(): ?array
    {
        return $this->dataSource;
    }

    /** Get the data source definition.
     * @param string|null $dataSourceName Data source name.
     * @return array|null Data source definition array.
     */
    public function getDataSourceDefinition(?string $dataSourceName): ?array
    {
        foreach ($this->dataSource as $value) {
            if ($value['name'] == $dataSourceName) {
                return $value;
            }
        }
        return null;
    }

    /** Set the options.
     * @param array|null $src Options array.
     * @return void
     */
    public function setOptions(?array $src): void
    {
        $this->options = $src;
    }

    /** Get the options.
     * @return array|null Options array.
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /** Set the database specification.
     * @param array|null $src Database specification array.
     * @return void
     */
    public function setDbSpec(?array $src): void
    {
        $this->dbSpec = $src;
    }

    /** Get the database specification.
     * @return array|null Database specification array.
     */
    public function getDbSpec(): ?array
    {
        return $this->dbSpec;
    }

    /** Set the separator.
     * @param string|null $sep Separator.
     * @return void
     */
    public function setSeparator(?string $sep): void
    {
        $this->separator = $sep;
    }

    /** Get the separator.
     * @return string|null Separator.
     */
    public function getSeparator(): ?string
    {
        return $this->separator;
    }

    /** Add a target field.
     * @param string|null $field Target field.
     * @return void
     */
    public function addTargetField(?string $field): void
    {
        $this->fieldsRequired[] = $field;
    }

    /** Get the field of index.
     * @param int $ix Index.
     * @return string|null Field of index.
     */
    public function getFieldOfIndex(int $ix): ?string
    {
        return $this->fieldsRequired[$ix];
    }

    /** Add a value.
     * @param string|null $value Value.
     * @return void
     */
    public function addValue(?string $value): void
    {
        $this->fieldsValues[] = $value;
    }

    /** Set the value.
     * @param array|null $values Value array.
     * @return void
     */
    public function setValue(?array $values): void
    {
        $this->fieldsValues = $values;
    }

    /** Get the value of field.
     * @param string|null $targetField Target field.
     * @return string|null Value of field.
     */
    public function getValueOfField(?string $targetField): ?string
    {
        $counter = 0;
        foreach ($this->fieldsRequired as $field) {
            if ($targetField == $field) {
                return $this->fieldsValues[$counter];
            }
            $counter++;
        }
        return null;
    }

    /** Set the start.
     * @param string|null $st Start.
     * @return void
     */
    public function setStart(?string $st): void
    {
        $this->start = intval(mb_ereg_replace('[^0-9]', '', $st));
    }

    /** Get the start.
     * @return int Start.
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /** Get the record count.
     * @return int Record count.
     */
    public function getRecordCount(): int
    {
        return $this->recordCount;
    }

    /** Set the record count.
     * @param string|null $sk Record count.
     * @return void
     */
    public function setRecordCount(?string $sk): void
    {
        $this->recordCount = intval(mb_ereg_replace('[^0-9]', '', $sk));
    }

    /** Get the extra criteria.
     * @return array|null Extra criteria array.
     */
    public function getExtraCriteria(): ?array
    {
        return $this->extraCriteria;
    }

    /** Unset the extra criteria.
     * @param int $index Index.
     * @return void
     */
    public function unsetExtraCriteria(int $index): void
    {
        unset($this->extraCriteria[$index]);
    }

    /** Add an extra criteria.
     * @param string|null $field Field.
     * @param string|null $operator Operator.
     * @param string|null $value Value.
     * @return void
     */
    public function addExtraCriteria(?string $field, ?string $operator = '=', ?string $value = null): void
    {
        $this->extraCriteria[] = array('field' => $field, 'operator' => $operator, 'value' => $value);
    }

    /** Get the criteria value.
     * @param string|null $targetField Target field.
     * @return string|null Criteria value.
     */
    public function getCriteriaValue(?string $targetField): ?string
    {
        foreach ($this->getExtraCriteria() as $ar) {
            if ($targetField == $ar["field"]) {
                return $ar["value"];
            }
        }
        return null;
    }

    /** Get the criteria operator.
     * @param string|null $targetField Target field.
     * @return string|null Criteria operator.
     */
    public function getCriteriaOperator(?string $targetField): ?string
    {
        foreach ($this->getExtraCriteria() as $ar) {
            if ($targetField == $ar["field"]) {
                return $ar["operator"];
            }
        }
        return null;
    }

    /** Add an extra sort key.
     * @param string|null $field Field.
     * @param string|null $direction Direction.
     * @return void
     */
    public function addExtraSortKey(?string $field, ?string $direction): void
    {
        $this->extraSortKey[] = array('field' => $field, 'direction' => $direction);
    }

    /** Get the extra sort key.
     * @return array|null Extra sort key array.
     */
    public function getExtraSortKey(): ?array
    {
        return $this->extraSortKey;
    }

    /** Add a foreign value.
     * @param string|null $field Field.
     * @param string|null $value Value.
     * @return void
     */
    public function addForeignValue(?string $field, ?string $value): void
    {
        $this->foreignFieldAndValue[] = array('field' => $field, 'value' => $value);
    }

    /** Get the foreign keys value.
     * @param string|null $targetField Target field.
     * @return string|null Foreign keys value.
     */
    public function getForeignKeysValue(?string $targetField): ?string
    {
        foreach ($this->foreignFieldAndValue as $ar) {
            if ($targetField == $ar["field"]) {
                return $ar["value"];
            }
        }
        return null;
    }

    /** Set the media root.
     * @param string|null $value Media root.
     * @return void
     */
    public function setMediaRoot(?string $value): void
    {
        $this->params_mediaRoot = $value;
    }

    /** Get the media root.
     * @return string|null Media root.
     */
    public function getMediaRoot(): ?string
    {
        return $this->params_mediaRoot;
    }

    /** Set the global in context.
     * @param string|null $contextName Context name.
     * @param string|null $operation Operation.
     * @param string|null $field Field.
     * @param string|null $value Value.
     * @return void
     */
    public function setGlobalInContext(?string $contextName, ?string $operation, ?string $field, ?string $value): void
    {
        foreach ($this->dataSource as $index => $record) {
            if ($record['name'] == $contextName) {
                if (!isset($this->dataSource[$index]['global'])) {
                    $this->dataSource[$index]['global'] = array();
                }
                $this->dataSource[$index]['global'][] = array(
                    'db-operation' => $operation,
                    'field' => $field,
                    'value' => $value);
                return;
            }
        }
    }

    /* get the information for the 'name'. */
    /** Get the data source target array.
     * @return array|null Data source target array.
     */
    public function getDataSourceTargetArray(): ?array
    {
        if ($this->dataSource == null) {
            return null;
        }
        foreach ($this->dataSource as $record) {
            if ($record['name'] == $this->dataSourceName) {
                return $record;
            }
        }
        return null;
    }

    /** Get the entity for retrieve.
     * @return string|null Entity for retrieve.
     */
    public function getEntityForRetrieve(): ?string
    {
        $dsrc = $this->getDataSourceTargetArray();
        if (is_null($dsrc)) {
            return null;
        }
        if (isset($dsrc['view'])) {
            return $dsrc['view'];
        }
        return $dsrc['name'];
    }

    /** Get the entity for count.
     * @return string|null Entity for count.
     */
    public function getEntityForCount(): ?string
    {
        $dsrc = $this->getDataSourceTargetArray();
        if (is_null($dsrc)) {
            return null;
        }
        if (isset($dsrc['count'])) {
            return $dsrc['count'];
        }
        if (isset($dsrc['view'])) {
            return $dsrc['view'];
        }
        return $dsrc['name'];
    }

    /** Get the entity for update.
     * @return string|null Entity for update.
     */
    public function getEntityForUpdate(): ?string
    {
        $dsrc = $this->getDataSourceTargetArray();
        if (is_null($dsrc)) {
            return null;
        }
        if (isset($dsrc['table'])) {
            return $dsrc['table'];
        }
        return $dsrc['name'];
    }

    /** Get the entity as source.
     * @return string|null Entity as source.
     */
    public function getEntityAsSource(): ?string
    {
        $dsrc = $this->getDataSourceTargetArray();
        if (is_null($dsrc)) {
            return null;
        }
        if (isset($dsrc['source'])) {
            return $dsrc['source'];
        }
        if (isset($dsrc['table'])) {
            return $dsrc['table'];
        }
        return $dsrc['name'];
    }

}
