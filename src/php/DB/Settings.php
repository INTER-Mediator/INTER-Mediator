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

class Settings
{
    private ?string $dbSpecServer = null;
    private ?string $dbSpecPort = null;
    private ?string $dbSpecUser = null;
    private ?string $dbSpecPassword = null;
    private ?string $dbSpecDatabase = null;
    private ?string $dbSpecDataType = null;
    private ?string $dbSpecProtocol = null;
    private ?string $dbSpecDSN = null;
    private ?array $dbSpecOption = null;

    private ?array $dataSource = null;
    private ?array $options = null;
    private ?array $dbSpec = null;
//    private $targetDataSource = null;
    private ?string $dataSourceName = '';
    private int $recordCount = 0;
    private int $start = 0;
    private ?string $separator = null;

    private array $extraCriteria = array();
    private array $extraSortKey = array();
    private array $fieldsRequired = array();
    private array $fieldsValues = array();
    private array $foreignFieldAndValue = array();
    private ?DBClass $currentDataAccess = null;

    private ?string $currentUser = null;
    private ?array $authentication = null;
    private ?string $accessUser = null;
    private ?string $accessPassword = null;
    private bool $primaryKeyOnly = false;
    private bool $isDBNative = false;
    private bool $requireAuthorization = false;
    private bool $requireAuthentication = false;

    private bool $emailAsAccount = false;
    private ?array $smtpConfiguration = null;
    private ?array $associated = null;
    public ?NotifyServer $notifyServer = null;
    public string $registerTableName = "registeredcontext";
    public string $registerPKTableName = "registeredpks";
    private int $params_samlExpiringSeconds;
    private ?string $params_mediaRoot;
    private bool $isSAML = false;
    private ?string $samlAuthSource = '';
    private ?array $samlAttrRules = null;
    private ?array $samlAdditionalRules = null;

    private ?string $aggregation_select = null;
    private ?string $aggregation_from = null;
    private ?string $aggregation_group_by = null;

    private array $attachedFiles = [];
    private ?array $attachedFields = null;
    private bool $certVerifying = true;

    public function setSAMLAdditionalRules(?array $value): void
    {
        $this->samlAdditionalRules = $value;
    }

    public function getSAMLAdditionalRules(): ?array
    {
        return $this->samlAdditionalRules;
    }

    public function setSAMLAttrRules(?array $value): void
    {
        $this->samlAttrRules = $value;
    }

    public function getSAMLAttrRules(): ?array
    {
        return $this->samlAttrRules;
    }

    public function setSAMLAuthSource(?string $value): void
    {
        $this->samlAuthSource = $value;
    }

    public function getSAMLAuthSource(): ?string
    {
        return $this->samlAuthSource;
    }

    public function setIsSaml(bool $value): void
    {
        $this->isSAML = $value;
    }

    public function getIsSaml(): bool
    {
        return $this->isSAML;
    }

    public function setCertVerifying(bool $value): void
    {
        $this->certVerifying = $value;
    }

    public function getCertVerifying(): bool
    {
        return $this->certVerifying;
    }

    public function setAttachedFiles(string $contextName, array $files): void
    {
        if ($contextName && $files && count($files) > 0) {
            $this->attachedFiles[$contextName] = $files;
            if (isset($_POST['_im_filesfields'])) {
                $this->attachedFields = explode(',', $_POST['_im_filesfields']);
            }
        }
    }

    public function getAttachedFiles(string $contextName): ?array
    {
        if ($contextName && $this->attachedFiles && isset($this->attachedFiles[$contextName])) {
            return $this->attachedFiles[$contextName];
        }
        return null;
    }

    public function getAttachedFields(): ?array
    {
        return $this->attachedFields;
    }

    function __construct()
    {
    }

    public function getAggregationSelect(): ?string
    {
        return $this->aggregation_select;
    }

    public function setAggregationSelect(?string $value): void
    {
        $this->aggregation_select = $value;
    }

    public function getAggregationFrom(): ?string
    {
        return $this->aggregation_from;
    }

    public function setAggregationFrom(?string $value): void
    {
        $this->aggregation_from = $value;
    }

    public function getAggregationGroupBy(): ?string
    {
        return $this->aggregation_group_by;
    }

    public function setAggregationGroupBy(?string $value): void
    {
        $this->aggregation_group_by = $value;
    }

    public function addAssociated(?string $name, ?string $field, ?string $value): void
    {
        if (!$this->associated) {
            $this->associated = array();
        }
        $this->associated[] = array("name" => $name, "field" => $field, "value" => $value);
    }

    public function getAssociated(): ?array
    {
        return $this->associated;
    }

    public function setSmtpConfiguration(?array $config): void
    {
        $this->smtpConfiguration = $config;
    }

    public function getSmtpConfiguration(): ?array
    {
        return $this->smtpConfiguration;
    }

    public function setDataSourceName(string $dataSourceName): void
    {
        $this->dataSourceName = $dataSourceName;
    }

    public function getDataSourceName(): string
    {
        return $this->dataSourceName;
    }

    public function setFieldsRequired(?array $fieldsRequired): void
    {
        $this->fieldsRequired = $fieldsRequired;
    }

    public function getFieldsRequired(): ?array
    {
        return $this->fieldsRequired;
    }

    public function getValue(): ?array
    {
        return $this->fieldsValues;
    }

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

    public function addValueWithField(?string $field, ?string $value): void
    {
        $this->fieldsValues[] = $value;
        $this->fieldsRequired[] = $field;
    }

    /**
     * @param array $foreignFieldAndValue
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

    public function getCurrentDataAccess(): DBClass
    {
        return $this->currentDataAccess;
    }

    public function setCurrentDataAccess(DBClass $dbaccess): void
    {
        $this->currentDataAccess = $dbaccess;
    }

    public function setDbSpecServer(?string $str): void
    {
        $this->dbSpecServer = $str;
    }

    public function getDbSpecServer(): ?string
    {
        return $this->dbSpecServer;
    }

    public function setDbSpecPort(?string $str): void
    {
        $this->dbSpecPort = $str;
    }

    public function getDbSpecPort(): ?string
    {
        return $this->dbSpecPort;
    }

    public function setDbSpecUser(?string $str): void
    {
        $this->dbSpecUser = $str;
    }

    public function getDbSpecUser(): ?string
    {
        return $this->dbSpecUser;
    }

    public function setDbSpecPassword(?string $str): void
    {
        $this->dbSpecPassword = $str;
    }

    public function getDbSpecPassword(): ?string
    {
        return $this->dbSpecPassword;
    }

    public function setDbSpecDataType(?string $str): void
    {
        $this->dbSpecDataType = $str;
    }

    public function getDbSpecDataType(): ?string
    {
        return is_null($this->dbSpecDataType) ? "FMPro12" : $this->dbSpecDataType;
    }

    public function setDbSpecDatabase(?string $str): void
    {
        $this->dbSpecDatabase = $str;
    }

    public function getDbSpecDatabase()
    {
        return $this->dbSpecDatabase;
    }

    public function setDbSpecProtocol(?string $str): void
    {
        $this->dbSpecProtocol = $str;
    }

    public function getDbSpecProtocol()
    {
        return $this->dbSpecProtocol;
    }

    public function setDbSpecDSN(?string $str): void
    {
        $this->dbSpecDSN = $str;
    }

    public function getDbSpecDSN(): ?string
    {
        return $this->dbSpecDSN;
    }

    public function setDbSpecOption(?array $options): void
    {
        $this->dbSpecOption = $options;
    }

    public function getDbSpecOption(): ?array
    {
        return $this->dbSpecOption;
    }

    public function getAccessUser(): ?string
    {
        return $this->accessUser ?? $this->dbSpecUser;
    }

    public function getAccessPassword(): ?string
    {
        return $this->accessPassword ?? $this->dbSpecPassword;
    }

    public function setUserAndPasswordForAccess(?string $user, ?string $pass): void
    {
        $this->accessUser = $user;
        $this->accessPassword = $pass;
    }

    /* Call on INTER-Mediator.php */

    /**
     * @param array $authentication
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

    public function getUserTable(): ?string
    {
        return $this->getAuthenticationItem('user-table');
    }

    public function getGroupTable(): ?string
    {
        return $this->getAuthenticationItem('group-table');
    }

    public function getCorrTable(): ?string
    {
        return $this->getAuthenticationItem('corresponding-table');
    }

    public function getHashTable(): ?string
    {
        return $this->getAuthenticationItem('challenge-table');
    }

    public function getExpiringSeconds(): int
    {
        return $this->getAuthenticationItem('authexpired');
    }

    public function setSAMLExpiringSeconds(int $sec): void
    {
        $this->params_samlExpiringSeconds = $sec;
    }

    public function getSAMLExpiringSeconds(): int
    {
        return $this->params_samlExpiringSeconds;
    }

    public function setCurrentUser(?string $str): void
    {
        $this->currentUser = $str;
    }

    public function getCurrentUser(): ?string
    {
        return $this->currentUser;
    }

    public function setDataSource(?array $src): void
    {
        $this->dataSource = $src;
    }

    public function getDataSource(): ?array
    {
        return $this->dataSource;
    }

    public function getDataSourceDefinition(?string $dataSourceName): ?array
    {
        foreach ($this->dataSource as $index => $value) {
            if ($value['name'] == $dataSourceName) {
                return $value;
            }
        }
        return null;
    }

    public function setOptions(?array $src): void
    {
        $this->options = $src;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setDbSpec(?array $src): void
    {
        $this->dbSpec = $src;
    }

    public function getDbSpec(): ?array
    {
        return $this->dbSpec;
    }

    public function setSeparator(?string $sep): void
    {
        $this->separator = $sep;
    }

    public function getSeparator(): ?string
    {
        return $this->separator;
    }

    public function addTargetField(?string $field): void
    {
        $this->fieldsRequired[] = $field;
    }

    public function getFieldOfIndex(int $ix): ?string
    {
        return $this->fieldsRequired[$ix];
    }

    public function addValue(?string $value): void
    {
        $this->fieldsValues[] = $value;
    }

    public function setValue(?array $values): void
    {
        $this->fieldsValues = $values;
    }

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

    public function setStart(?string $st): void
    {
        $this->start = intval(mb_ereg_replace('[^0-9]', '', $st));
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getRecordCount(): int
    {
        return $this->recordCount;
    }

    public function setRecordCount(?string $sk): void
    {
        $this->recordCount = intval(mb_ereg_replace('[^0-9]', '', $sk));
    }

    public function getExtraCriteria(): ?array
    {
        return $this->extraCriteria;
    }

    public function unsetExtraCriteria(int $index): void
    {
        unset($this->extraCriteria[$index]);
    }

    public function addExtraCriteria(?string $field, ?string $operator = '=', ?string $value = null): void
    {
        $this->extraCriteria[] = array('field' => $field, 'operator' => $operator, 'value' => $value);
    }

    public function getCriteriaValue(?string $targetField): ?string
    {
        foreach ($this->getExtraCriteria() as $ar) {
            if ($targetField == $ar["field"]) {
                return $ar["value"];
            }
        }
        return null;
    }

    public function getCriteriaOperator(?string $targetField): ?string
    {
        foreach ($this->getExtraCriteria() as $ar) {
            if ($targetField == $ar["field"]) {
                return $ar["operator"];
            }
        }
        return null;
    }

    public function addExtraSortKey(?string $field, ?string $direction): void
    {
        $this->extraSortKey[] = array('field' => $field, 'direction' => $direction);
    }

    public function getExtraSortKey(): ?array
    {
        return $this->extraSortKey;
    }

    public function addForeignValue(?string $field, ?string $value): void
    {
        $this->foreignFieldAndValue[] = array('field' => $field, 'value' => $value);
    }

    public function getForeignKeysValue(?string $targetField): ?string
    {
        foreach ($this->foreignFieldAndValue as $ar) {
            if ($targetField == $ar["field"]) {
                return $ar["value"];
            }
        }
        return null;
    }

    public function setMediaRoot(?string $value): void
    {
        $this->params_mediaRoot = $value;
    }

    public function getMediaRoot(): ?string
    {
        return $this->params_mediaRoot;
    }

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
