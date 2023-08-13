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

use Exception;

class TextFile extends DBClass
{
    private $recordCount;

    public function readFromDB(): ?array
    {
        $textFormat = strtolower($this->dbSettings->getDbSpecDataType());
        if ($textFormat == "csv") {
            $fileRoot = $this->dbSettings->getDbSpecDatabase();
            $fileName = $this->dbSettings->getEntityForRetrieve();
            $metaFilePath = "{$fileRoot}/{$fileName}.meta";
            $dataFilePath = "{$fileRoot}/{$fileName}.csv";
//        $this->logger->setErrorMessage($metaFilePath);
//        $this->logger->setErrorMessage($dataFilePath);

            if (substr_count($metaFilePath, '../') > 3 || substr_count($dataFilePath, '../') > 3) {
                $this->logger->setErrorMessage("You can't access files in inhibit area: {$metaFilePath} {$dataFilePath}.");
                return null;
            }

            $metaContent = file_get_contents($metaFilePath);
            if ($metaContent === false) {
                $this->logger->setErrorMessage("The meta file doesn't exist: {$metaFilePath}.");
                $this->recordCount = 0;
                return null;
            }
            $jsonContent = json_decode($metaContent, true);
            if ($jsonContent === false) {
                $this->logger->setErrorMessage("The meta file is invalid format: {$metaFilePath}.");
                $this->recordCount = 0;
                return null;
            }
            $fieldNames = $jsonContent["fields"];

            $fileContent = file_get_contents($dataFilePath);
            if ($fileContent === false) {
                $this->logger->setErrorMessage("The 'target' parameter doesn't point the valid file path in context: {$dataFilePath}.");
                $this->recordCount = 0;
                return null;
            }

            $sortArray = $this->getSortClause();
            $sortKey = isset($sortArray[0]) ? $sortArray[0]["field"] : null;
            $sortDirection = isset($sortArray[0]) ? $sortArray[0]["direction"] : null;
            $queryArray = $this->getWhereClause('read');

            $crlfPosition = strpos($fileContent, "\r\n");
            $crlfPosition = $crlfPosition === false ? 999999 : $crlfPosition;
            $crPosition = strpos($fileContent, "\r");
            $crPosition = $crPosition === false ? 999999 : $crPosition;
            $lfPosition = strpos($fileContent, "\n");
            $lfPosition = $lfPosition === false ? 999999 : $lfPosition;
            $minPosition = min($crlfPosition, $crPosition, $lfPosition);
            $lineSeparator = "\n";
            if ($minPosition == $crlfPosition) {
                $lineSeparator = "\r\n";
            } else if ($minPosition == $crPosition) {
                $lineSeparator = "\r";
            } else if ($minPosition == $lfPosition) {
                $lineSeparator = "\n";
            }
            $eachLines = explode($lineSeparator, $fileContent);
            $resultArray = array();
            $sortKeyArray = array();
            foreach ($eachLines as $oneLine) {
                $oneLineArray = array();
                $parsedLine = str_getcsv($oneLine);
                $fieldCounter = 0;
                foreach ($parsedLine as $value) {
                    $oneLineArray[$fieldNames[$fieldCounter]] = $value;
                    $fieldCounter++;
                }
                $isTrueCondition = true;
                foreach ($queryArray as $condition) {
                    if (isset($oneLineArray[$condition["field"]]) && $oneLineArray[$condition["field"]] != $condition["value"]) {
                        $isTrueCondition = false;
                        break;
                    }
                }
                if ($isTrueCondition) {
                    $resultArray[] = $oneLineArray;
                    if (isset($oneLineArray[$sortKey])) {
                        $sortKeyArray[] = $oneLineArray[$sortKey];
                    }
                }
            }

            if (!is_null($sortKey)) {
                if ($sortDirection != "DESC") {
                    asort($sortKeyArray);
                } else {
                    arsort($sortKeyArray);
                }
                $originalArray = $resultArray;
                $resultArray = array();
                foreach ($sortKeyArray as $index => $value) {
                    $resultArray[] = $originalArray[$index];
                }
            }

            $this->recordCount = count($resultArray);
            return $resultArray;
        } else {
            $this->logger->setErrorMessage("The format '{$textFormat}' is not supported so far.");
            $this->recordCount = 0;
            return null;
        }
    }

    public function countQueryResult(): int
    {
        return $this->recordCount;
    }

    private function getWhereClause(string $currentOperation, bool $includeContext = true, bool $includeExtra = true,
                                    string $signedUser = ''): array
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $queryClause = '';
        $primaryKey = $tableInfo['key'] ?? 'id';

        // 'field' => '__operation__' is not supported.
        // Authentication and Authorization are NOT supported.

        $queryClauseArray = array();
        if ($includeContext && isset($tableInfo['query'][0])) {
            foreach ($tableInfo['query'] as $condition) {
                if (!$this->dbSettings->getPrimaryKeyOnly() || $condition['field'] == $primaryKey) {
                    if (!$this->specHandler->isPossibleOperator($condition['operator'])) {
                        throw new Exception("Invalid Operator.: {$condition['operator']}");
                    }
                    $queryClauseArray[] = array(
                        "field" => $condition['field'],
                        "value" => $condition['value'],
                        "operator" => $condition['operator'] ?? "=",
                    );
                }
            }
        }
        $exCriteria = $this->dbSettings->getExtraCriteria();
        if ($includeExtra && isset($exCriteria[0])) {
            foreach ($this->dbSettings->getExtraCriteria() as $condition) {
                if (!$this->dbSettings->getPrimaryKeyOnly() || $condition['field'] == $primaryKey) {
                    if (!$this->specHandler->isPossibleOperator($condition['operator'])) {
                        throw new Exception("Invalid Operator.: {$condition['operator']}");
                    }
                    $queryClauseArray[] = array(
                        "field" => $condition['field'],
                        "value" => $condition['value'],
                        "operator" => $condition['operator'] ?? "=",
                    );
                }
            }
        }

        if (count($this->dbSettings->getForeignFieldAndValue()) > 0) {
            foreach ($tableInfo['relation'] as $relDef) {
                foreach ($this->dbSettings->getForeignFieldAndValue() as $foreignDef) {
                    if ($relDef['join-field'] == $foreignDef['field']) {
                        $queryClauseArray[] = array(
                            "field" => $relDef['foreign-key'],
                            "value" => $foreignDef['value'],
                            "operator" => $relDef['operator'] ?? "=",
                        );
                    }
                }
            }
        }
        return $queryClauseArray;
    }


    /* Genrate SQL Sort and Where clause */
    /**
     * @return string
     */
    private function getSortClause(): array
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $sortClause = array();
        if (count($this->dbSettings->getExtraSortKey()) > 0) {
            foreach ($this->dbSettings->getExtraSortKey() as $condition) {
                if (!$this->isPossibleOrderSpecifier($condition['direction'])) {
                    throw new Exception("Invalid Sort Specifier.");
                }
                $sortClause[] = array(
                    "field" => $condition['field'],
                    "direction" => $condition['direction'] ?? "ASC",
                );
            }
        }
        if (isset($tableInfo['sort'])) {
            foreach ($tableInfo['sort'] as $condition) {
                if (isset($condition['direction']) && !$this->isPossibleOrderSpecifier($condition['direction'])) {
                    throw new Exception("Invalid Sort Specifier.");
                }
                $sortClause[] = array(
                    "field" => $condition['field'],
                    "direction" => $condition['direction'] ?? "ASC",
                );
            }
        }
        return $sortClause;
    }

    public function updateDB(bool $bypassAuth): bool
    {
        return false;
    }

    public function deleteFromDB(): bool
    {
        return false;
    }

    public function getFieldInfo(string $dataSourceName): ?array
    {
        return null;
    }

    public function setupConnection(): bool
    {
        return true;
    }

    public function isPossibleOperator(string $operator): bool
    {
        return in_array(strtoupper($operator), array('='));
    }

    public function isPossibleOrderSpecifier(string $specifier): bool
    {
        return in_array(strtoupper($specifier), array('ASC', 'DESC'));
    }

    public function requireUpdatedRecord(bool $value): void
    {
        // TODO: Implement requireUpdatedRecord() method.
    }

    public function getUpdatedRecord(): ?array
    {
        return [];
    }

    public function updatedRecord(): ?array
    {
        return [];
    }

    public function setUpdatedRecord(array $record, string $value = null, int $index = 0): void
    {
    }

    public function createInDB(bool $isReplace = false): ?string
    {
        return "created";
    }

    public function softDeleteActivate(string $field, string $value): void
    {
    }

    public function copyInDB(): ?string
    {
        return null;
    }

    public function getTotalCount(): int
    {
        return 0;
    }

    public function setupHandlers(?string $dsn = null): void
    {
    }

    public function setDataToUpdatedRecord(string $field, string $value, int $index = 0): void
    {
    }

    public function queryForTest(string $table, ?array $conditions = null): ?array
    {
        return null;
    }

    public function deleteForTest(string $table, ?array $conditions = null): bool
    {
        return false;
    }

    /*
* Transaction
*/
    public function hasTransaction(): bool
    {
        return false;
    }

    public function inTransaction(): bool
    {
        return false;
    }

    public function beginTransaction(): void
    {
    }

    public function commitTransaction(): void
    {
    }

    public function rollbackTransaction(): void
    {
    }

    public function getUseSetDataToUpdatedRecord(): bool
    {
        return false;
    }

    public function clearUseSetDataToUpdatedRecord(): void
    {
    }

    public function closeDBOperation(): void
    {
    }
}