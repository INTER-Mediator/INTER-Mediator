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

use DateTime;
use DateTimeZone;
use Exception;
use INTERMediator\IMUtil;
use PDOException;
use INTERMediator\Params;

/**
 * Class PDO
 */
class PDO extends DBClass
{
    use Support\DB_PDO_SQLSupport;

    /**
     * @var \PDO|null
     */
    public ?\PDO $link = null;       // Connection with PDO's link
    /**
     * @var int
     */
    private int $mainTableCount = 0;
    /**
     * @var int
     */
    private int $mainTableTotalCount = 0;
    /**
     * @var array|null
     */
    private ?array $fieldInfo = null;
    /**
     * @var bool
     */
    private bool $isAlreadySetup = false;
    /**
     * @var bool
     */
    private bool $isRequiredUpdated = false;
    /**
     * @var array|null
     */
    private ?array $updatedRecord = null;
    /**
     * @var string|null
     */
    private ?string $softDeleteField = null;
    /**
     * @var string|null
     */
    private ?string $softDeleteValue = null;
    /**
     * @var bool
     */
    private bool $useSetDataToUpdatedRecord = false;
    /**
     * @var bool
     */
    private bool $isFollowingTimezones;
    /**
     * @var bool
     */
    private bool $isSuppressDVOnCopy;
    /**
     * @var bool
     */
    private bool $isSuppressDVOnCopyAssoc;
    /**
     * @var bool
     */
    private bool $isSuppressAuthTargetFillingOnCreate;
    /**
     * @var string
     */
    private string $defaultTimezone;

    /**
     *
     */
    public function __construct()
    {
        $this->isFollowingTimezones = Params::getParameterValue("followingTimezones", true);
        $this->defaultTimezone = Params::getParameterValue("defaultTimezone", date_default_timezone_get());
        $this->isSuppressDVOnCopy
            = Params::getParameterValue("suppressDefaultValuesOnCopy", false);
        $this->isSuppressDVOnCopyAssoc
            = Params::getParameterValue("suppressDefaultValuesOnCopyAssoc", false);
        $this->isSuppressAuthTargetFillingOnCreate
            = Params::getParameterValue("suppressAuthTargetFillingOnCreate", false);
    }

    /**
     * @return array|null
     */
    public function getUpdatedRecord(): ?array
    {
        return $this->updatedRecord;
    }

    /**
     * @return array|null
     */
    public function updatedRecord(): ?array
    {
        return $this->updatedRecord;
    }

    /**
     * @param array $record
     * @return void
     */
    public function setUpdatedRecord(array $record): void
    {
        $this->updatedRecord = $record;
    }

    /**
     * @param string $field
     * @param string $value
     * @param int $index
     * @return void
     */
    public function setDataToUpdatedRecord(string $field, string $value, int $index = 0): void
    {
        $this->updatedRecord[$index][$field] = $value;
        $this->useSetDataToUpdatedRecord = true;
    }

    /**
     * @return bool
     */
    public function getUseSetDataToUpdatedRecord(): bool
    {
        return $this->useSetDataToUpdatedRecord;
    }

    /**
     * @return void
     */
    public function clearUseSetDataToUpdatedRecord(): void
    {
        $this->useSetDataToUpdatedRecord = false;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function requireUpdatedRecord(bool $value): void
    {
        $this->isRequiredUpdated = $value;
    }

    /**
     * @param string $field
     * @param string $value
     * @return void
     */
    public function softDeleteActivate(string $field, string $value): void
    {
        $this->softDeleteField = $field;
        $this->softDeleteValue = $value;
    }

    /**
     * @param string $str
     */
    public function errorMessageStore(string $str): void
    {
        if ($this->link) {
            $errorInfo = var_export($this->link->errorInfo(), true);
            $this->logger->setErrorMessage("Query Error: [{$str}] Code={$this->link->errorCode()} Info ={$errorInfo}");
        } else {
            $this->logger->setErrorMessage("Query Error: [{$str}]");
        }
    }

    /**
     * @param string $sql
     * @param $result
     * @return bool
     */
    private function errorHandlingPDO(string $sql, $result): bool
    {
        $errorCode = $this->link->errorCode();
        $errorClass = strlen($errorCode) < 2 ? "00" : substr($errorCode, 0, 2);
        if ($errorClass !== "00") {
            if ($errorClass === "01") {
                $this->logger->setWarningMessage(var_export($this->link->errorInfo(), true));
            } else {
                $this->errorMessageStore('[ERROR] SQL:' . $sql);
                return false;
            }
        } else {
            $this->handler->specialErrorHandling($sql);
        }
        if ($result === false || is_null($result)) {
            $this->errorMessageStore('[ERROR] SQL:' . $sql);
            return false;
        }
        return true;
    }


    /**
     * @return bool
     */
    public function setupConnection(): bool
    {
        if ($this->isAlreadySetup) {
            return true;
        }
        try {
            $this->link = new \PDO($this->dbSettings->getDbSpecDSN(),
                $this->dbSettings->getDbSpecUser(),
                $this->dbSettings->getDbSpecPassword(),
                is_array($this->dbSettings->getDbSpecOption()) ? $this->dbSettings->getDbSpecOption() : array());
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage('Connection Error: ' . $ex->getMessage() .
                ", DSN=" . $this->dbSettings->getDbSpecDSN() .
                ", User=" . $this->dbSettings->getDbSpecUser());
            return false;
        }
        $this->isAlreadySetup = true;
        return true;
    }

    /**
     * @param string|null $dsn
     * @return void
     */
    public function setupHandlers(?string $dsn = null): void
    {
        if (!$dsn) {
            $dsn = $this->dbSettings->getDbSpecDSN();
        }
        if (!is_null($this->dbSettings)) {
            $this->handler = Support\DB_PDO_Handler::generateHandler($this, $dsn);
            $this->handler->optionalOperationInSetup();
            $this->specHandler = Support\DB_Spec_Handler_PDO::generateHandler($this, $dsn);
        }
        $this->authHandler = new Support\DB_Auth_Handler_PDO($this);
        $this->notifyHandler = new Support\DB_Notification_Handler_PDO($this);
    }

    /**
     * @param string $dsnString
     * @return bool
     */
    public function setupWithDSN(string $dsnString): bool
    {
        if ($this->isAlreadySetup) {
            return true;
        }
        try {
            $this->link = new \PDO($dsnString);
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage('Connection Error: ' . $ex->getMessage() . ", DSN=" . $dsnString);
            return false;
        }
        $this->isAlreadySetup = true;
        return true;
    }

    /**
     * @return ?array
     * @throws Exception
     */
    public function readFromDB(): ?array
    {
        $this->fieldInfo = null;
        $this->mainTableCount = 0;
        $this->mainTableTotalCount = 0;
        if (!$this->setupConnection()) { //Establish the connection
            return null;
        }

        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
        $updatingTable = $this->dbSettings->getEntityForUpdate();
        $sourceTable = $this->dbSettings->getEntityAsSource();
        $boolFields = $this->handler->getBooleanFields($updatingTable);

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] === 'load' || $condition['db-operation'] === 'read') {
                    if ($condition['situation'] === 'pre') {
                        $sql = $condition['definition'];
                        $this->logger->setDebugMessage($sql);
                        $result = $this->link->query($sql);
                        if (!$this->errorHandlingPDO($sql, $result)) {
                            return null;
                        }
                    }
                }
            }
        }

        $queryClause = $this->getWhereClause('read', true, true, $signedUser);
        if ($queryClause != '') {
            $queryClause = "WHERE {$queryClause}";
        }
        $sortClause = $this->getSortClause();
        $isAggregate = ($this->dbSettings->getAggregationSelect() != null);
        $tableName = $this->dbSettings->getEntityForRetrieve();
        $viewOrTableName = $isAggregate ? $this->dbSettings->getAggregationFrom()
            : $this->handler->quotedEntityName($tableName);
        $countingName = $isAggregate ? $this->dbSettings->getAggregationFrom()
            : $this->handler->quotedEntityName($this->dbSettings->getEntityForCount());

        // Create SQL
        $limitParam = $this->getLimitParam($tableInfo);
        $isPaging = (isset($tableInfo['paging']) and $tableInfo['paging'] === true);
        $skipParam = 0;
        if ($isPaging) {
            $skipParam = $this->dbSettings->getStart();
        }
        $fields = $isAggregate ? $this->dbSettings->getAggregationSelect()
            : (isset($tableInfo['specify-fields'])
                ? implode(',', array_unique($this->dbSettings->getFieldsRequired())) : "*");
        $groupBy = ($isAggregate && $this->dbSettings->getAggregationGroupBy())
            ? ("GROUP BY " . $this->dbSettings->getAggregationGroupBy()) : "";
        $offset = $skipParam;

        if ($isAggregate && !$isPaging) {
            $offset = '';
        } else {
            // Count all records matched with the condtions
            $sql = "{$this->handler->sqlSelectCommand()}count(*) FROM {$countingName} {$queryClause} {$groupBy}";
            $this->logger->setDebugMessage($sql);
            $result = $this->link->query($sql);
            if (!$this->errorHandlingPDO($sql, $result)) {
                return null;
            }
            $this->mainTableCount = $isAggregate ? $result->rowCount() : $result->fetchColumn(0);

            if ($queryClause === '') {
                $this->mainTableTotalCount = $this->mainTableCount;
            } else {
                // Count all records
                $sql = "{$this->handler->sqlSELECTCommand()}count(*) FROM {$countingName} {$groupBy}";
                $this->logger->setDebugMessage($sql);
                $result = $this->link->query($sql);
                if (!$this->errorHandlingPDO($sql, $result)) {
                    return null;
                }
                $this->mainTableTotalCount = $isAggregate ? $result->rowCount() : $result->fetchColumn(0);
            }
        }
        $sql = "{$this->handler->sqlSELECTCommand()}{$fields} FROM {$viewOrTableName} {$queryClause} {$groupBy} "
            . $this->handler->sqlOrderByCommand($sortClause, $limitParam, $offset);
        $this->logger->setDebugMessage($sql);
        $this->notifyHandler->setQueriedEntity($isAggregate ? $this->dbSettings->getAggregationFrom() : $sourceTable);
        $this->notifyHandler->setQueriedCondition(
            "{$viewOrTableName} {$queryClause} {$this->handler->sqlOrderByCommand($sortClause, $limitParam, $offset)}");

        $result = $this->link->query($sql);
        if (!$this->errorHandlingPDO($sql, $result)) {
            return null;
        }

        $this->notifyHandler->setQueriedPrimaryKeys(array());
        $keyField = $this->getKeyFieldOfContext($tableInfo);
        $timeFields = ($this->isFollowingTimezones && !$this->dbSettings->getAggregationFrom())
            ? $this->handler->getTimeFields($this->dbSettings->getEntityForRetrieve()) : [];
        if (isset($tableInfo['time-fields']) && is_array($tableInfo['time-fields'])) {
            $timeFields = array_merge($timeFields, $tableInfo['time-fields']);
        }
        $sqlResult = array();
        $isFirstRow = true;
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $rowArray = array();
            foreach ($row as $field => $val) {
                if ($isFirstRow) {
                    $this->fieldInfo[] = $field;
                }
                $filedInForm = "{$tableName}{$this->dbSettings->getSeparator()}{$field}";
                $rowArray[$field] = $this->formatter->formatterFromDB($filedInForm, $val);
                // Convert the time explanation from UTC to server setup timezone
                if (in_array($field, $timeFields) && !is_null($rowArray[$field]) && $rowArray[$field] !== '') {
                    $rowArray[$field] = $this->getDateTimeExpression($rowArray[$field]);
                } else if (in_array($field, $boolFields)) {
                    $rowArray[$field] = $this->isTrue($rowArray[$field]);
                }
            }
            $sqlResult[] = $rowArray;
            if ($keyField && isset($rowArray[$keyField])) {
                $this->notifyHandler->addQueriedPrimaryKeys($rowArray[$keyField]);
            }
            $isFirstRow = false;
        }
        //Logger::getInstance()->setDebugMessage("#####" . str_replace("\n", "",var_export($sqlResult, true)),2);
        if ($isAggregate && !$isPaging) {
            $this->mainTableCount = count($sqlResult);
            $this->mainTableTotalCount = count($sqlResult);
        }
        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] === 'load' || $condition['db-operation'] === 'read') {
                    if ($condition['situation'] === 'post') {
                        $sql = $condition['definition'];
                        $this->logger->setDebugMessage($sql);
                        $result = $this->link->query($sql);
                        if (!$this->errorHandlingPDO($sql, $result)) {
                            return null;
                        }
                    }
                }
            }
        }

        return $sqlResult;
    }

    /**
     * @return int
     */
    public function countQueryResult(): int
    {
        return $this->mainTableCount;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->mainTableTotalCount;
    }

    /**
     * @param string $datetime
     * @param bool $isToServer
     * @return string
     * @throws Exception
     */
    private function getDateTimeExpression(string $datetime, bool $isToServer = false): string
    {
        $serverTZ = new DateTimeZone($this->defaultTimezone);
        $dt = new DateTime($datetime, $serverTZ);
        $serverOffset = $serverTZ->getOffset($dt); // in minute
        $clientOffset = $this->dbSettings->getClientTZOffset() * 60; // in Second
        $shiftSec = $isToServer ? ($serverOffset + $clientOffset) : (-$clientOffset - $serverOffset);
        if ($shiftSec > 0) {
            $dt->add(new \DateInterval("PT{$shiftSec}S"));
        } else {
            $shiftSec = -$shiftSec;
            $dt->sub(new \DateInterval("PT{$shiftSec}S"));
        }
        $isTime = preg_match('/^\d{2}:\d{2}:\d{2}/', $datetime);
        $dt->setTimezone(new DateTimeZone($this->defaultTimezone));
        $converted = $dt->format($isTime ? 'H:i:s' : 'Y-m-d H:i:s');

//        $this->logger->setDebugMessage("[getDateTimeExpression] datetime={$datetime}->{$converted}, datetime={$datetime}, "
//            . "serverOffset={$serverOffset}, clientOffset={$clientOffset}, shiftSec={$shiftSec}",2);

        return $converted;
    }

    /**
     * @param bool $bypassAuth
     * @return bool
     * @throws Exception
     */
    public function updateDB(bool $bypassAuth): bool
    {
        $this->fieldInfo = null;
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }

        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $tableName = $this->handler->quotedEntityName($this->dbSettings->getEntityForUpdate());
        [$nullableFields, $numericFields, $boolFields, $timeFields, $dateFields]
            = $this->handler->getTypedFields($this->dbSettings->getEntityForUpdate());
//        $this->logger->setDebugMessage("nullableFields=" . var_export($nullableFields, true)
//            . ",\nnumericFields=" . var_export($numericFields, true)
//            . ", \nboolFields = " . var_export($boolFields, true)
//            . ", \ntimeFields = " . var_export($timeFields, true)
//            . ", \ndateFields = " . var_export($dateFields, true));
        if (isset($tableInfo['numeric-fields']) && is_array($tableInfo['numeric-fields'])) {
            $numericFields = array_merge($nullableFields, $tableInfo['numeric-fields']);
        }
        if (isset($tableInfo['time-fields']) && is_array($tableInfo['time-fields'])) {
            $timeFields = array_merge($timeFields, $tableInfo['time-fields']);
        }
        $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] === 'update' && $condition['situation'] === 'pre') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage($sql);
                    $result = $this->link->query($sql);
                    if (!$this->errorHandlingPDO($sql, $result)) {
                        return false;
                    }
                }
            }
        }

        $setClause = array();
        $setParameter = array();
        $counter = 0;
        $fieldValues = $this->dbSettings->getValue();

        foreach ($this->dbSettings->getFieldsRequired() as $field) {
            $setClause[] = $this->handler->quotedEntityName($field) . "=?";
            $value = (is_array($fieldValues[$counter]))
                ? implode("\n", $fieldValues[$counter]) : $fieldValues[$counter];
            $origValue = $value;
            $counter++;
            $valueLen = ($value || $value === 0 || $value === 0.0) ? strlen((string)$value) : 0;
            if (is_null($value) || $value === '') {
                if (in_array($field, $nullableFields)) {
                    $value = NULL;
                } else if (in_array($field, $numericFields) || in_array($field, $boolFields)) {
                    $value = 0;
                } else if (in_array($field, $dateFields) && in_array($field, $timeFields)) {
                    $value = "{$this->handler->dateResetForNotNull()} 00:00:00";
                } else if (in_array($field, $dateFields)) {
                    $value = $this->handler->dateResetForNotNull();
                } else if (in_array($field, $timeFields)) {
                    $value = '00:00:00';
                }
//            } else if ($value === '' && in_array($field, $numericFields)) {
//                $value = in_array($field, $nullableFields) ? NULL : 0;
            } else if (in_array($field, $boolFields)) {
                $value = $this->isTrue($value);
            } else {
                $filedInForm = "{$this->dbSettings->getEntityForUpdate()}{$this->dbSettings->getSeparator()}{$field}";
                $value = $this->formatter->formatterToDB($filedInForm, $value);
                if ($this->isFollowingTimezones && in_array($field, $timeFields) && !is_null($value) && $value !== '') {
                    $value = $this->getDateTimeExpression($value, true);
                } else if (in_array($field, $nullableFields)) {
                    $value = $value ?? NULL;
                }
            }
            $setParameter[] = $value;
            $this->logger->setDebugMessage("field={$field}, value={$value}/original={$origValue}/, len={$valueLen}");
        }
        if (count($setClause) < 1) {
            $this->logger->setErrorMessage("No data to update for table {$tableName}.");
            return false;
        }
        $setClause = implode(',', $setClause);
        $queryClause = $this->getWhereClause('update',
            false, true, $signedUser, $bypassAuth);
        if ($queryClause != '') {
            $queryClause = "WHERE {$queryClause}";
        }
        $sql = "{$this->handler->sqlUPDATECommand()}{$tableName} SET {$setClause} {$queryClause}";
        $prepSQL = $this->link->prepare($sql);
        $this->notifyHandler->setQueriedEntity($this->dbSettings->getEntityAsSource());

        $this->logger->setDebugMessage($prepSQL->queryString
            . " with " . str_replace("\n", " ", var_export($setParameter, true)));
        // Thanks for the following code: https://koyhogetech.hatenablog.com/entry/20101217/pdo_pgsql
        $count = 1;
        foreach ($setParameter as $param) {
            $bindType = \PDO::PARAM_STR;
            $bindType = is_int($param) ? \PDO::PARAM_INT : $bindType;
            $bindType = is_bool($param) ? \PDO::PARAM_BOOL : $bindType;
            $bindType = is_null($param) ? \PDO::PARAM_NULL : $bindType;
            $bindResult = $prepSQL->bindValue($count, $param, $bindType);
            if (!$this->errorHandlingPDO($sql, $bindResult)) {
                return false;
            }
            $count += 1;
        }
        $result = $prepSQL->execute();
        if (!$this->errorHandlingPDO($sql, $result)) {
            return false;
        }

        if ($this->isRequiredUpdated) {
            $targetTable = $this->handler->quotedEntityName($this->dbSettings->getEntityForRetrieve());
            $sql = $this->handler->sqlSELECTCommand() . " * FROM {$targetTable} {$queryClause}";
            $result = $this->link->query($sql);
            $this->logger->setDebugMessage($sql);
            if (!$this->errorHandlingPDO($sql, $result)) {
                return false;
            }
            $this->notifyHandler->setQueriedPrimaryKeys(array());
            $keyField = $this->getKeyFieldOfContext($tableInfo);
            $sqlResult = array();
            $isFirstRow = true;
            foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $record = $this->getResultRecord($row, $isFirstRow, $timeFields);
                $this->notifyHandler->addQueriedPrimaryKeys($record[$keyField]);
                $isFirstRow = false;
                $sqlResult[] = $record;
            }
            $this->updatedRecord = count($sqlResult) ? $sqlResult : null;
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] === 'update' && $condition['situation'] === 'post') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage($sql);
                    $result = $this->link->query($sql);
                    if (!$this->errorHandlingPDO($sql, $result)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * @param bool $isReplace
     * @return string|null
     * @throws Exception
     */
    public function createInDB(bool $isReplace = false): ?string
    {
        $this->fieldInfo = null;
        if (!$this->setupConnection()) { //Establish the connection
            return null;
        }

        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $timeFields = $this->isFollowingTimezones
            ? $this->handler->getTimeFields($this->dbSettings->getEntityForUpdate()) : [];
        if (isset($tableInfo['time-fields']) && is_array($tableInfo['time-fields'])) {
            $timeFields = array_merge($timeFields, $tableInfo['time-fields']);
        }
        $tableNameRow = $this->dbSettings->getEntityForUpdate();
        $tableName = $this->handler->quotedEntityName($tableNameRow);
        $viewName = $this->handler->quotedEntityName($this->dbSettings->getEntityForRetrieve());

        $signedUser = null;
        if (isset($tableInfo['authentication'])) {
            $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
        }

        $setColumnNames = array();
        $setValues = array();
        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if (($condition['db-operation'] === 'new' || $condition['db-operation'] === 'create')
                    && $condition['situation'] === 'pre'
                ) {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage($sql);
                    $result = $this->link->query($sql);
                    if (!$this->errorHandlingPDO($sql, $result)) {
                        return null;
                    }
                }
            }
        }

        $requiredFields = $this->dbSettings->getFieldsRequired();
        $countFields = count($requiredFields);
        $fieldValues = $this->dbSettings->getValue();
        for ($i = 0; $i < $countFields; $i++) {
            $field = $requiredFields[$i];
            $setColumnNames[] = $field;
            $value = $fieldValues[$i];
            $filedInForm = "{$this->dbSettings->getEntityForUpdate()}{$this->dbSettings->getSeparator()}{$field}";
            $convertedValue = (is_array($value)) ? implode("\n", $value) : $value;
            // Convert the time explanation from UTC to server setup timezone
            if (in_array($field, $timeFields) && !is_null($convertedValue) && $convertedValue !== '') {
                $convertedValue = $this->getDateTimeExpression($convertedValue);
            }
            $setValues[] = $this->formatter->formatterToDB($filedInForm, $convertedValue);
        }
        if (isset($tableInfo['default-values'])) {
            foreach ($tableInfo['default-values'] as $itemDef) {
                $field = $itemDef['field'];
                $value = $itemDef['value'];
                if (!in_array($field, $setColumnNames)) {
                    $filedInForm = "{$this->dbSettings->getEntityForUpdate()}{$this->dbSettings->getSeparator()}{$field}";
                    $convertedValue = (is_array($value)) ? implode("\n", $value) : $value;
                    $setValues[] = $this->formatter->formatterToDB($filedInForm, $convertedValue);
                    $setColumnNames[] = $field;
                }
            }
        }
        if (isset($tableInfo['authentication']) && !$this->isSuppressAuthTargetFillingOnCreate) {
            $authInfoField = $this->authHandler->getFieldForAuthorization("create");
            $authInfoTarget = $this->authHandler->getTargetForAuthorization("create");
            if (!$this->authHandler->getNoSetForAuthorization("create")) {
                if ($authInfoTarget === 'field-user') {
                    $setColumnNames[] = $authInfoField;
                    $setValues[] = strlen($signedUser) === 0 ? IMUtil::randomString(10) : $signedUser;
                } else if ($authInfoTarget === 'field-group') {
                    $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($signedUser);
                    $setColumnNames[] = $authInfoField;
                    $setValues[] = strlen($belongGroups[0]) === 0 ? IMUtil::randomString(10) : $belongGroups[0];
                }
            }
        }

        $keyField = $tableInfo['key'] ?? 'id';
        $setClause = $this->handler->sqlSETClause($tableNameRow, $setColumnNames, $keyField, $setValues);
        if ($isReplace) {
            $sql = $this->handler->sqlREPLACECommand($tableName, $setClause);
        } else {
            $sql = $this->handler->sqlINSERTCommand($tableName, $setClause);
        }
        $this->logger->setDebugMessage($sql);
        $result = $this->link->exec($sql);
        if (!$this->errorHandlingPDO($sql, $result)) {
            return null;
        }
        $seqObject = $tableInfo['sequence'] ?? "{$this->dbSettings->getEntityForUpdate()}_{$keyField}_seq";
        $lastKeyValue = $this->handler->lastInsertIdAlt($seqObject, $tableNameRow); // $this->link->lastInsertId($seqObject);
        if (/* $isReplace && */ !$lastKeyValue) { // lastInsertId returns 0 after replace command.
            // Moreover, about MySQL, it returns 0 with the key field without AUTO_INCREMENT.
            $lastKeyValue = -999; // This means kind of error, so avoid to set non-zero value.
        }

        $this->notifyHandler->setQueriedPrimaryKeys(array($lastKeyValue));
        $this->notifyHandler->setQueriedEntity($this->dbSettings->getEntityAsSource());

        if ($this->isRequiredUpdated) {
            $sql = $this->handler->sqlSELECTCommand() . " * FROM " . $viewName
                . " WHERE " . $keyField . " = " . $this->link->quote($lastKeyValue);
            $this->logger->setDebugMessage($sql);
            $result = $this->link->query($sql);
            if (!$this->errorHandlingPDO($sql, $result)) {
                return null;
            }
            $sqlResult = $this->getResultRelation($result, $timeFields);
            $this->updatedRecord = count($sqlResult) ? $sqlResult : null;
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if (($condition['db-operation'] === 'new' || $condition['db-operation'] === 'create')
                    && $condition['situation'] === 'post'
                ) {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage($sql);
                    $result = $this->link->query($sql);
                    if (!$this->errorHandlingPDO($sql, $result)) {
                        return null;
                    }
                }
            }
        }
        return $lastKeyValue;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function deleteFromDB(): bool
    {
        $this->fieldInfo = null;
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }

        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $tableName = $this->handler->quotedEntityName($this->dbSettings->getEntityForUpdate());
        $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] === 'delete' && $condition['situation'] === 'pre') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage($sql);
                    $result = $this->link->query($sql);
                    if (!$this->errorHandlingPDO($sql, $result)) {
                        return false;
                    }
                }
            }
        }
        $queryClause = $this->getWhereClause('delete', false, true, $signedUser);
        if ($queryClause == '') {
            $this->errorMessageStore('Don\'t delete with no ciriteria. queryClause=' . $queryClause);
            return false;
        }
        $sql = "{$this->handler->sqlDELETECommand()}{$tableName} WHERE {$queryClause}";
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if (!$this->errorHandlingPDO($sql, $result)) {
            return false;
        }
        $this->notifyHandler->setQueriedEntity($this->dbSettings->getEntityAsSource());

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'delete' && $condition['situation'] == 'post') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage($sql);
                    $result = $this->link->query($sql);
                    if (!$this->errorHandlingPDO($sql, $result)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function copyInDB(): ?string
    {
        $this->fieldInfo = null;
        if (!$this->setupConnection()) { //Establish the connection
            return null;
        }

        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $tableName = $this->dbSettings->getEntityForUpdate();
        $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
        $timeFields = $this->isFollowingTimezones
            ? $this->handler->getTimeFields($this->dbSettings->getEntityForUpdate()) : [];
        if (isset($tableInfo['time-fields']) && is_array($tableInfo['time-fields'])) {
            $timeFields = array_merge($timeFields, $tableInfo['time-fields']);
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'copy' && $condition['situation'] == 'pre') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage($sql);
                    $result = $this->link->query($sql);
                    if (!$this->errorHandlingPDO($sql, $result)) {
                        return null;
                    }
                }
            }
        }
        //======
        $queryClause = $this->getWhereClause('delete', false, true, $signedUser);
        if ($queryClause == '') {
            $this->errorMessageStore('Don\'t copy with no ciriteria.');
            return null;
        }
        $defaultValues = array();
        if (!$this->isSuppressDVOnCopy && isset($tableInfo['default-values'])) {
            foreach ($tableInfo['default-values'] as $itemDef) {
                $defaultValues[$itemDef['field']] = $itemDef['value'];
            }
        }
        $lastKeyValue = $this->handler->copyRecords($tableInfo, $queryClause, null, null, $defaultValues);
        if (is_null($lastKeyValue)) {
            return null;
        }
        $this->notifyHandler->setQueriedPrimaryKeys(array($lastKeyValue));
        $this->notifyHandler->setQueriedEntity($this->dbSettings->getEntityAsSource());
        //======
        $assocArray = $this->dbSettings->getAssociated();
        if ($assocArray) {
            foreach ($assocArray as $assocInfo) {
                $assocContextDef = $this->dbSettings->getDataSourceDefinition($assocInfo['name']);
                $queryClause = $this->handler->quotedEntityName($assocInfo["field"]) . "=" .
                    $this->link->quote($assocInfo["value"]);
                $defaultValues = array();
                if (!$this->isSuppressDVOnCopyAssoc && isset($assocContextDef['default-values'])) {
                    foreach ($assocContextDef['default-values'] as $itemDef) {
                        $defaultValues[$itemDef['field']] = $itemDef['value'];
                    }
                }
                $this->handler->copyRecords($assocContextDef, $queryClause, $assocInfo["field"], $lastKeyValue, $defaultValues);
            }
        }
        //======
        if ($this->isRequiredUpdated) {
            $sql = "{$this->handler->sqlSELECTCommand()}* FROM " . $this->handler->quotedEntityName($tableName)
                . " WHERE " . $tableInfo['key'] . "=" . $this->link->quote($lastKeyValue);
            $result = $this->link->query($sql);
            $this->logger->setDebugMessage($sql);
            if (!$this->errorHandlingPDO($sql, $result)) {
                return null;
            }
            $sqlResult = $this->getResultRelation($result, $timeFields);
            $this->updatedRecord = $sqlResult;
        }
        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'copy' && $condition['situation'] == 'post') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage($sql);
                    $result = $this->link->query($sql);
                    if (!$this->errorHandlingPDO($sql, $result)) {
                        return null;
                    }
                }
            }
        }
        return $lastKeyValue;
    }

    /**
     * @param array $context
     * @return string
     */
    private function getKeyFieldOfContext(array $context): string
    {
        return $context['key'] ?? $this->specHandler->getDefaultKey();
    }

    /**
     * @param string $dataSourceName
     * @return null
     */
    public function getFieldInfo(string $dataSourceName): ?array
    {
        return $this->fieldInfo;
    }

    /**
     * @param $d
     * @return bool
     */
    private function isTrue($d): bool // $d is mixed
    {
        if (is_null($d)) {
            return false;
        }
        if (strtolower($d) == 'true' || strtolower($d) == 't') {
            return true;
        } else if (intval($d) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param string $table
     * @param array|null $conditions
     * @return array|null
     */
    public function queryForTest(string $table, ?array $conditions = null): ?array
    {
        if ($table == null) {
            $this->errorMessageStore("The table doesn't specified.");
            return null;
        }
        if (!$this->setupConnection()) { //Establish the connection
            $this->errorMessageStore("Can't open db connection.");
            return null;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}* FROM "
            . $this->handler->quotedEntityName($table)
            . $this->generateConditions($conditions);
        $result = $this->link->query($sql);
        if ($result === false) {
            var_dump($this->link->errorInfo());
            return null;
        }
        $this->logger->setDebugMessage("[queryForTest] {
        $sql}");
        $recordSet = array();
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $oneRecord = array();
            foreach ($row as $field => $value) {
                $oneRecord[$field] = $value;
            }
            $recordSet[] = $oneRecord;
        }
        return $recordSet;
    }

    /**
     * @param string $table
     * @param array|null $conditions
     * @return bool
     */
    public function deleteForTest(string $table, ?array $conditions = null): bool
    {
        if ($table == null) {
            $this->errorMessageStore("The table doesn't specified.");
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            $this->errorMessageStore("Can't open db connection.");
            return false;
        }
        $sql = "{$this->handler->sqlDELETECommand()}"
            . $this->handler->quotedEntityName($table)
            . $this->generateConditions($conditions);
        $result = $this->link->exec($sql);
        if ($result === false) {
            var_dump($this->link->errorInfo());
            return false;
        }
        $this->logger->setDebugMessage("[deleteForTest] {
        $sql}");
        return true;
    }

    /*
     * Transaction
     */
    /**
     * @return bool
     */
    public function hasTransaction(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->link->inTransaction();
    }

    /**
     * @return void
     */
    public function beginTransaction(): void
    {
        $this->link->beginTransaction();
    }

    /**
     * @return void
     */
    public function commitTransaction(): void
    {
        $this->link->commit();
    }

    /**
     * @return void
     */
    public function rollbackTransaction(): void
    {
        $this->link->rollBack();
    }

    /**
     * @param $result
     * @param array $timeFields
     * @return array
     * @throws Exception
     */
    private function getResultRelation($result, array $timeFields): array
    {
        $sqlResult = array();
        $isFirstRow = true;
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $sqlResult[] = $this->getResultRecord($row, $isFirstRow, $timeFields);
            $isFirstRow = false;
        }
        return $sqlResult;
    }

    /**
     * @return void
     */
    public function closeDBOperation(): void
    {
        // Do nothing
    }

    /**
     * @param array $row
     * @param bool $isFirstRow
     * @param array $timeFields
     * @return array
     * @throws Exception
     */
    private function getResultRecord(array $row, bool $isFirstRow, array $timeFields): array
    {
        $rowArray = array();
        foreach ($row as $field => $val) {
            if ($isFirstRow) {
                $this->fieldInfo[] = $field;
            }
            $filedInForm = "{$this->dbSettings->getEntityForUpdate()}{$this->dbSettings->getSeparator()}{$field}";
            $rowArray[$field] = $this->formatter->formatterFromDB($filedInForm, $val);
            // Convert the time explanation from UTC to server setup timezone
            if (in_array($field, $timeFields) && !is_null($rowArray[$field]) && $rowArray[$field] !== '') {
                $rowArray[$field] = $this->getDateTimeExpression($rowArray[$field]);
            }
        }
        return $rowArray;
    }

    /**
     * @param array|null $conditions
     * @return string
     */
    private function generateConditions(?array $conditions): string
    {
        $sql = '';
        if (is_array($conditions) && count($conditions) > 0) {
            $sql .= " WHERE ";
            $first = true;
            foreach ($conditions as $field => $value) {
                if (!$first) {
                    $sql .= " and ";
                }
                $sql .= $this->handler->quotedEntityName($field) . " = " . $this->link->quote($value);
                $first = false;
            }
        }
        return $sql;
    }
}
