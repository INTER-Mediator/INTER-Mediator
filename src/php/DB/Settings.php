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

use INTERMediator\NotifyServer;

/**
 *
 */
class Settings
{
    /**
     * @var string|null
     */
    private ?string $dbSpecServer = null;
    /**
     * @var string|null
     */
    private ?string $dbSpecPort = null;
    /**
     * @var string|null
     */
    private ?string $dbSpecUser = null;
    /**
     * @var string|null
     */
    private ?string $dbSpecPassword = null;
    /**
     * @var string|null
     */
    private ?string $dbSpecDatabase = null;
    /**
     * @var string|null
     */
    private ?string $dbSpecDataType = null;
    /**
     * @var string|null
     */
    private ?string $dbSpecProtocol = null;
    /**
     * @var string|null
     */
    private ?string $dbSpecDSN = null;
    /**
     * @var array|null
     */
    private ?array $dbSpecOption = null;

    /**
     * @var array|null
     */
    private ?array $dataSource = null;
    /**
     * @var array|null
     */
    private ?array $options = null;
    /**
     * @var array|null
     */
    private ?array $dbSpec = null;
//    private $targetDataSource = null;
    /**
     * @var string|null
     */
    private ?string $dataSourceName = '';
    /**
     * @var int
     */
    private int $recordCount = 0;
    /**
     * @var int
     */
    private int $start = 0;
    /**
     * @var string|null
     */
    private ?string $separator = null;

    /**
     * @var array
     */
    private array $extraCriteria = array();
    /**
     * @var array
     */
    private array $extraSortKey = array();
    /**
     * @var array
     */
    private array $fieldsRequired = array();
    /**
     * @var array
     */
    private array $fieldsValues = array();
    /**
     * @var array
     */
    private array $foreignFieldAndValue = array();
    /**
     * @var DBClass|null
     */
    private ?DBClass $currentDataAccess = null;

    /**
     * @var string|null
     */
    private ?string $currentUser = null;
    /**
     * @var array|null
     */
    private ?array $authentication = null;
    /**
     * @var string|null
     */
    private ?string $accessUser = null;
    /**
     * @var string|null
     */
    private ?string $accessPassword = null;
    /**
     * @var bool
     */
    private bool $primaryKeyOnly = false;
    /**
     * @var bool
     */
    private bool $isDBNative = false;
    /**
     * @var bool
     */
    private bool $requireAuthorization = false;
    /**
     * @var bool
     */
    private bool $requireAuthentication = false;

    /**
     * @var bool
     */
    private bool $emailAsAccount = false;
    /**
     * @var array|null
     */
    private ?array $smtpConfiguration = null;
    /**
     * @var array|null
     */
    private ?array $associated = null;
    /**
     * @var NotifyServer|null
     */
    public ?NotifyServer $notifyServer = null;
    /**
     * @var string
     */
    public string $registerTableName = "registeredcontext";
    /**
     * @var string
     */
    public string $registerPKTableName = "registeredpks";
    /**
     * @var int
     */
    private int $params_samlExpiringSeconds;
    /**
     * @var string|null
     */
    private ?string $params_mediaRoot;
    /**
     * @var bool
     */
    private bool $isSAML = false;
    /**
     * @var string|null
     */
    private ?string $samlAuthSource = '';
    /**
     * @var array|null
     */
    private ?array $samlAttrRules = null;
    /**
     * @var array|null
     */
    private ?array $samlAdditionalRules = null;

    /**
     * @var string|null
     */
    private ?string $aggregation_select = null;
    /**
     * @var string|null
     */
    private ?string $aggregation_from = null;
    /**
     * @var string|null
     */
    private ?string $aggregation_group_by = null;

    /**
     * @var array
     */
    private array $attachedFiles = [];
    /**
     * @var array|null
     */
    private ?array $attachedFields = null;
    /**
     * @var bool
     */
    private bool $certVerifying = true;
    /**
     * @var int
     */
    private int $timezoneOffset = 0;

    public function setClientTZOffset(int $offset): void
    {
        $this->timezoneOffset = $offset;
    }

    public function getClientTZOffset(): int
    {
        return $this->timezoneOffset;
    }

    /**
     * @param array|null $value
     * @return void
     */
    public function setSAMLAdditionalRules(?array $value): void
    {
        $this->samlAdditionalRules = $value;
    }

    /**
     * @return array|null
     */
    public function getSAMLAdditionalRules(): ?array
    {
        return $this->samlAdditionalRules;
    }

    /**
     * @param array|null $value
     * @return void
     */
    public function setSAMLAttrRules(?array $value): void
    {
        $this->samlAttrRules = $value;
    }

    /**
     * @return array|null
     */
    public function getSAMLAttrRules(): ?array
    {
        return $this->samlAttrRules;
    }

    /**
     * @param string|null $value
     * @return void
     */
    public function setSAMLAuthSource(?string $value): void
    {
        $this->samlAuthSource = $value;
    }

    /**
     * @return string|null
     */
    public function getSAMLAuthSource(): ?string
    {
        return $this->samlAuthSource;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function setIsSaml(bool $value): void
    {
        $this->isSAML = $value;
    }

    /**
     * @return bool
     */
    public function getIsSaml(): bool
    {
        return $this->isSAML;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function setCertVerifying(bool $value): void
    {
        $this->certVerifying = $value;
    }

    /**
     * @return bool
     */
    public function getCertVerifying(): bool
    {
        return $this->certVerifying;
    }

    /**
     * @param string $contextName
     * @param array $files
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

    /**
     * @param string $contextName
     * @return array|null
     */
    public function getAttachedFiles(string $contextName): ?array
    {
        if ($contextName && $this->attachedFiles && isset($this->attachedFiles[$contextName])) {
            return $this->attachedFiles[$contextName];
        }
        return null;
    }

    /**
     * @return array|null
     */
    public function getAttachedFields(): ?array
    {
        return $this->attachedFields;
    }

    /**
     *
     */
    function __construct()
    {
    }

    /**
     * @return string|null
     */
    public function getAggregationSelect(): ?string
    {
        return $this->aggregation_select;
    }

    /**
     * @param string|null $value
     * @return void
     */
    public function setAggregationSelect(?string $value): void
    {
        $this->aggregation_select = $value;
    }

    /**
     * @return string|null
     */
    public function getAggregationFrom(): ?string
    {
        return $this->aggregation_from;
    }

    /**
     * @param string|null $value
     * @return void
     */
    public function setAggregationFrom(?string $value): void
    {
        $this->aggregation_from = $value;
    }

    /**
     * @return string|null
     */
    public function getAggregationGroupBy(): ?string
    {
        return $this->aggregation_group_by;
    }

    /**
     * @param string|null $value
     * @return void
     */
    public function setAggregationGroupBy(?string $value): void
    {
        $this->aggregation_group_by = $value;
    }

    /**
     * @param string|null $name
     * @param string|null $field
     * @param string|null $value
     * @return void
     */
    public function addAssociated(?string $name, ?string $field, ?string $value): void
    {
        if (!$this->associated) {
            $this->associated = array();
        }
        $this->associated[] = array("name" => $name, "field" => $field, "value" => $value);
    }

    /**
     * @return array|null
     */
    public function getAssociated(): ?array
    {
        return $this->associated;
    }

    /**
     * @param array|null $config
     * @return void
     */
    public function setSmtpConfiguration(?array $config): void
    {
        $this->smtpConfiguration = $config;
    }

    /**
     * @return array|null
     */
    public function getSmtpConfiguration(): ?array
    {
        return $this->smtpConfiguration;
    }

    /**
     * @param string $dataSourceName
     * @return void
     */
    public function setDataSourceName(string $dataSourceName): void
    {
        $this->dataSourceName = $dataSourceName;
    }

    /**
     * @return string
     */
    public function getDataSourceName(): string
    {
        return $this->dataSourceName;
    }

    /**
     * @param array|null $fieldsRequired
     * @return void
     */
    public function setFieldsRequired(?array $fieldsRequired): void
    {
        $this->fieldsRequired = $fieldsRequired;
    }

    /**
     * @return array|null
     */
    public function getFieldsRequired(): ?array
    {
        return $this->fieldsRequired;
    }

    /**
     * @return array|null
     */
    public function getValue(): ?array
    {
        return $this->fieldsValues;
    }

    /**
     * @return array|null
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

    /**
     * @param string|null $field
     * @param string|null $value
     * @return void
     */
    public function addValueWithField(?string $field, ?string $value): void
    {
        $this->fieldsValues[] = $value;
        $this->fieldsRequired[] = $field;
    }

    /**
     * @param array|null $foreignFieldAndValue
     */
    public function setForeignFieldAndValue(?array $foreignFieldAndValue): void
    {
        $this->foreignFieldAndValue = $foreignFieldAndValue;
    }

    /**
     * @return array
     */
    public function getForeignFieldAndValue(): ?array
    {
        return $this->foreignFieldAndValue;
    }

    /**
     * @param boolean $isDBNative
     */
    public function setDBNative(bool $isDBNative): void
    {
        $this->isDBNative = $isDBNative;
    }

    /**
     * @return boolean
     */
    public function isDBNative(): bool
    {
        return $this->isDBNative;
    }

    /**
     * @param boolean $requireAuthentication
     */
    public function setRequireAuthentication(bool $requireAuthentication): void
    {
        $this->requireAuthentication = $requireAuthentication;
    }

    /**
     * @return boolean
     */
    public function getRequireAuthentication(): bool
    {
        return $this->requireAuthentication;
    }

    /**
     * @param boolean $requireAuthorization
     */
    public function setRequireAuthorization(bool $requireAuthorization): void
    {
        $this->requireAuthorization = $requireAuthorization;
    }

    /**
     * @return boolean
     */
    public function getRequireAuthorization(): bool
    {
        return $this->requireAuthorization;
    }

    /**
     * @param boolean $primaryKeyOnly
     */
    public function setPrimaryKeyOnly(bool $primaryKeyOnly): void
    {
        $this->primaryKeyOnly = $primaryKeyOnly;
    }

    /**
     * @return boolean
     */
    public function getPrimaryKeyOnly(): bool
    {
        return $this->primaryKeyOnly;
    }


    /**
     * @param boolean $emailAsAccount
     */
    public function setEmailAsAccount(bool $emailAsAccount): void
    {
        $this->emailAsAccount = $emailAsAccount;
    }

    /**
     * @return boolean
     */
    public function getEmailAsAccount(): bool
    {
        return $this->emailAsAccount;
    }

    /**
     * @return DBClass
     */
    public function getCurrentDataAccess(): DBClass
    {
        return $this->currentDataAccess;
    }

    /**
     * @param DBClass $dbaccess
     * @return void
     */
    public function setCurrentDataAccess(DBClass $dbaccess): void
    {
        $this->currentDataAccess = $dbaccess;
    }

    /**
     * @param string|null $str
     * @return void
     */
    public function setDbSpecServer(?string $str): void
    {
        $this->dbSpecServer = $str;
    }

    /**
     * @return string|null
     */
    public function getDbSpecServer(): ?string
    {
        return $this->dbSpecServer;
    }

    /**
     * @param string|null $str
     * @return void
     */
    public function setDbSpecPort(?string $str): void
    {
        $this->dbSpecPort = $str;
    }

    /**
     * @return string|null
     */
    public function getDbSpecPort(): ?string
    {
        return $this->dbSpecPort;
    }

    /**
     * @param string|null $str
     * @return void
     */
    public function setDbSpecUser(?string $str): void
    {
        $this->dbSpecUser = $str;
    }

    /**
     * @return string|null
     */
    public function getDbSpecUser(): ?string
    {
        return $this->dbSpecUser;
    }

    /**
     * @param string|null $str
     * @return void
     */
    public function setDbSpecPassword(?string $str): void
    {
        $this->dbSpecPassword = $str;
    }

    /**
     * @return string|null
     */
    public function getDbSpecPassword(): ?string
    {
        return $this->dbSpecPassword;
    }

    /**
     * @param string|null $str
     * @return void
     */
    public function setDbSpecDataType(?string $str): void
    {
        $this->dbSpecDataType = $str;
    }

    /**
     * @return string|null
     */
    public function getDbSpecDataType(): ?string
    {
        return is_null($this->dbSpecDataType) ? "FMPro12" : $this->dbSpecDataType;
    }

    /**
     * @param string|null $str
     * @return void
     */
    public function setDbSpecDatabase(?string $str): void
    {
        $this->dbSpecDatabase = $str;
    }

    /**
     * @return string|null
     */
    public function getDbSpecDatabase(): ?string
    {
        return $this->dbSpecDatabase;
    }

    /**
     * @param string|null $str
     * @return void
     */
    public function setDbSpecProtocol(?string $str): void
    {
        $this->dbSpecProtocol = $str;
    }

    /**
     * @return string|null
     */
    public function getDbSpecProtocol(): ?string
    {
        return $this->dbSpecProtocol;
    }

    /**
     * @param string|null $str
     * @return void
     */
    public function setDbSpecDSN(?string $str): void
    {
        $this->dbSpecDSN = $str;
    }

    /**
     * @return string|null
     */
    public function getDbSpecDSN(): ?string
    {
        return $this->dbSpecDSN;
    }

    /**
     * @param array|null $options
     * @return void
     */
    public function setDbSpecOption(?array $options): void
    {
        $this->dbSpecOption = $options;
    }

    /**
     * @return array|null
     */
    public function getDbSpecOption(): ?array
    {
        return $this->dbSpecOption;
    }

    /**
     * @return string|null
     */
    public function getAccessUser(): ?string
    {
        return $this->accessUser ?? $this->dbSpecUser;
    }

    /**
     * @return string|null
     */
    public function getAccessPassword(): ?string
    {
        return $this->accessPassword ?? $this->dbSpecPassword;
    }

    /**
     * @param string|null $user
     * @param string|null $pass
     * @return void
     */
    public function setUserAndPasswordForAccess(?string $user, ?string $pass): void
    {
        $this->accessUser = $user;
        $this->accessPassword = $pass;
    }

    /* Call on INTER-Mediator.php */

    /**
     * @param array|null $authentication
     */
    public function setAuthentication(?array $authentication): void
    {
        if (isset($authentication['authexpired']) && $authentication['authexpired'] == 0) {
            $authentication['authexpired'] = $this->getAuthenticationItem('authexpired');
        }
        $this->authentication = $authentication;
    }

    /**
     * @return array
     */
    public function getAuthentication(): ?array
    {
        return $this->authentication;
    }

    /**
     * @param string|null $key
     * @return float|int|mixed|string|null
     */
    public function getAuthenticationItem(?string $key)
    {
        if (isset($this->authentication[$key])) {
            return $this->authentication[$key];
        }
        switch ($key) {
            case 'user-table':
                return 'authuser';
            case 'group-table':
                return 'authgroup';
            case 'corresponding-table':
                return 'authcor';
            case 'challenge-table':
                return 'issuedhash';
            case 'authexpired':
                return 3600 * 8;
            case 'storing':
                return 'credential';
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getUserTable(): ?string
    {
        return $this->getAuthenticationItem('user-table');
    }

    /**
     * @return string|null
     */
    public function getGroupTable(): ?string
    {
        return $this->getAuthenticationItem('group-table');
    }

    /**
     * @return string|null
     */
    public function getCorrTable(): ?string
    {
        return $this->getAuthenticationItem('corresponding-table');
    }

    /**
     * @return string|null
     */
    public function getHashTable(): ?string
    {
        return $this->getAuthenticationItem('challenge-table');
    }

    /**
     * @return int
     */
    public function getExpiringSeconds(): int
    {
        return $this->getAuthenticationItem('authexpired');
    }

    /**
     * @param int $sec
     * @return void
     */
    public function setSAMLExpiringSeconds(int $sec): void
    {
        $this->params_samlExpiringSeconds = $sec;
    }

    /**
     * @return int
     */
    public function getSAMLExpiringSeconds(): int
    {
        return $this->params_samlExpiringSeconds;
    }

    /**
     * @param string|null $str
     * @return void
     */
    public function setCurrentUser(?string $str): void
    {
        $this->currentUser = $str;
    }

    /**
     * @return string|null
     */
    public function getCurrentUser(): ?string
    {
        return $this->currentUser;
    }

    /**
     * @param array|null $src
     * @return void
     */
    public function setDataSource(?array $src): void
    {
        $this->dataSource = $src;
    }

    /**
     * @return array|null
     */
    public function getDataSource(): ?array
    {
        return $this->dataSource;
    }

    /**
     * @param string|null $dataSourceName
     * @return array|null
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

    /**
     * @param array|null $src
     * @return void
     */
    public function setOptions(?array $src): void
    {
        $this->options = $src;
    }

    /**
     * @return array|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @param array|null $src
     * @return void
     */
    public function setDbSpec(?array $src): void
    {
        $this->dbSpec = $src;
    }

    /**
     * @return array|null
     */
    public function getDbSpec(): ?array
    {
        return $this->dbSpec;
    }

    /**
     * @param string|null $sep
     * @return void
     */
    public function setSeparator(?string $sep): void
    {
        $this->separator = $sep;
    }

    /**
     * @return string|null
     */
    public function getSeparator(): ?string
    {
        return $this->separator;
    }

    /**
     * @param string|null $field
     * @return void
     */
    public function addTargetField(?string $field): void
    {
        $this->fieldsRequired[] = $field;
    }

    /**
     * @param int $ix
     * @return string|null
     */
    public function getFieldOfIndex(int $ix): ?string
    {
        return $this->fieldsRequired[$ix];
    }

    /**
     * @param string|null $value
     * @return void
     */
    public function addValue(?string $value): void
    {
        $this->fieldsValues[] = $value;
    }

    /**
     * @param array|null $values
     * @return void
     */
    public function setValue(?array $values): void
    {
        $this->fieldsValues = $values;
    }

    /**
     * @param string|null $targetField
     * @return string|null
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

    /**
     * @param string|null $st
     * @return void
     */
    public function setStart(?string $st): void
    {
        $this->start = intval(mb_ereg_replace('[^0-9]', '', $st));
    }

    /**
     * @return int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getRecordCount(): int
    {
        return $this->recordCount;
    }

    /**
     * @param string|null $sk
     * @return void
     */
    public function setRecordCount(?string $sk): void
    {
        $this->recordCount = intval(mb_ereg_replace('[^0-9]', '', $sk));
    }

    /**
     * @return array|null
     */
    public function getExtraCriteria(): ?array
    {
        return $this->extraCriteria;
    }

    /**
     * @param int $index
     * @return void
     */
    public function unsetExtraCriteria(int $index): void
    {
        unset($this->extraCriteria[$index]);
    }

    /**
     * @param string|null $field
     * @param string|null $operator
     * @param string|null $value
     * @return void
     */
    public function addExtraCriteria(?string $field, ?string $operator = '=', ?string $value = null): void
    {
        $this->extraCriteria[] = array('field' => $field, 'operator' => $operator, 'value' => $value);
    }

    /**
     * @param string|null $targetField
     * @return string|null
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

    /**
     * @param string|null $targetField
     * @return string|null
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

    /**
     * @param string|null $field
     * @param string|null $direction
     * @return void
     */
    public function addExtraSortKey(?string $field, ?string $direction): void
    {
        $this->extraSortKey[] = array('field' => $field, 'direction' => $direction);
    }

    /**
     * @return array|null
     */
    public function getExtraSortKey(): ?array
    {
        return $this->extraSortKey;
    }

    /**
     * @param string|null $field
     * @param string|null $value
     * @return void
     */
    public function addForeignValue(?string $field, ?string $value): void
    {
        $this->foreignFieldAndValue[] = array('field' => $field, 'value' => $value);
    }

    /**
     * @param string|null $targetField
     * @return string|null
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

    /**
     * @param string|null $value
     * @return void
     */
    public function setMediaRoot(?string $value): void
    {
        $this->params_mediaRoot = $value;
    }

    /**
     * @return string|null
     */
    public function getMediaRoot(): ?string
    {
        return $this->params_mediaRoot;
    }

    /**
     * @param string|null $contextName
     * @param string|null $operation
     * @param string|null $field
     * @param string|null $value
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
    /**
     * @return array|null
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

    /**
     * @return string|null
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

    /**
     * @return string|null
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

    /**
     * @return string|null
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

    /**
     * @return string|null
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
