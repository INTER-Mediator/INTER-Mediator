<?php

namespace INTERMediator\DB\Support;

use Exception;

trait DB_PDO_SQLSupport
{
    /*
      * Generate SQL style WHERE clause.
      */
    public function getWhereClauseForTest($currentOperation)
    {
        return $this->getWhereClause($currentOperation);
    }

    private function arrayToClause($queryClauseArray, $insideOp, $outsideOp): string
    {
        $oneClause = [];
        foreach ($queryClauseArray as $oneTerm) {
            $oneClause[] = '(' . implode($insideOp, $oneTerm) . ')';
        }
        return implode($outsideOp, $oneClause);
    }

    private function determineOperatorsInBlock($term)
    {
        $divideOp = explode("/", $term);
        $fieldOp = (isset($divideOp[1]) && $this->isTrue($divideOp[1])) ? " AND " : " OR ";
        $groupOp = (isset($divideOp[2]) && $this->isTrue($divideOp[2])) ? " AND " : " OR ";
        $blockOp = (isset($divideOp[3])
            ? (strtolower($divideOp[3]) === "and" ? " AND "
                : ($this->isTrue($divideOp[3]) ? " OR " : false)) : false);
        return [$fieldOp, $groupOp, $blockOp];
    }

    private function arrayToItemizedString($value, $isNumeric)
    {
        $escapedValue = "(";
        $isFirst = true;
        foreach (json_decode($value) as $item) {
            $escapedValue .= (!$isFirst ? "," : "") . ($isNumeric ? $item : $this->link->quote($item));
            $isFirst = false;
        }
        $escapedValue .= ")";
        return $escapedValue;
    }

    private function generateWhereClause($conditions, $primaryKey, $numericFields, $isExtra = false, $insideOp = ' AND ', $outsideOp = ' OR ')
    {
        $fieldOp = ' OR ';
        $groupOp = ' OR ';
        $blockOp = ' OR ';
        $result = '';
        $chunkCount = 0;
        $queryClauseArray = [];
        $isInBlock = false;
        foreach ($conditions as $condition) {
            if (isset($condition['field'])) {
                if ($isExtra && $condition['field'] == $primaryKey && isset($condition['value'])) {
                    $this->notifyHandler->setQueriedPrimaryKeys(array($condition['value']));
                }
                if ($condition['field'] == '__operation__') {
                    $chunkCount++;
                    if (isset($condition['operator'])) {
                        if ($condition['operator'] == 'ex') {
                            $insideOp = ' OR ';
                            $outsideOp = ' AND ';
                        } else if (strpos($condition['operator'], 'block') === 0) {
                            // ASSUMPTION: field=__operation__, operator=block/*/*/* must be at the end of condition settings.
                            // ASSUMPTION: After 'block', there are just condition and __operation__ only item.
                            $result .= "({$this->arrayToClause($queryClauseArray, $insideOp, $outsideOp)}) AND (";
                            $queryClauseArray = [];
                            $isInBlock = 1;
                            [$fieldOp, $groupOp, $blockOp] = $this->determineOperatorsInBlock($condition['operator']);
                        }
                    }
                } else if ($isInBlock) {
                    $fieldList = explode(",", $condition['field']);
                    $lcConditions = [];
                    $isMultiValue = false;
                    if ($blockOp) {
                        $valueList = explode(" ", $condition['value']);
                        if (count($valueList) === 1) {
                            $valueList = explode("　", $condition['value']);
                        }
                        $isMultiValue = count($valueList) > 1;
                        foreach ($valueList as $value) {
                            foreach ($fieldList as $field) {
                                $lcConditions[] = ['field' => $field, 'operator' => $condition['operator'], 'value' => $value];
                            }
                            $lcConditions[] = ['field' => '__operation__'];
                        }
                    } else {
                        foreach ($fieldList as $field) {
                            $lcConditions[] = ['field' => $field, 'operator' => $condition['operator'], 'value' => $condition['value']];
                        }
                    }
                    $resultItem = $this->generateWhereClause($lcConditions, $primaryKey, $numericFields, $isExtra, $fieldOp, $blockOp);
                    if ($isInBlock !== 1) {
                        $result .= $groupOp;
                    }
                    $result .= ($isMultiValue ? '(' : '') . $resultItem . ($isMultiValue ? ')' : '');
                    $isInBlock += 1;
                } else if ((!$this->dbSettings->getPrimaryKeyOnly() || $condition['field'] == $primaryKey)
                    && isset($condition['operator'])) {
                    $escapedField = $this->handler->quotedEntityName($condition['field']);
                    $condition = $this->normalizedCondition($condition);
                    if (!$this->specHandler->isPossibleOperator($condition['operator'])) {
                        throw new Exception("Invalid Operator.: {$condition['operator']}");
                    }
                    if (isset($condition['value'])) {
                        $isNumeric = in_array($condition['field'], $numericFields);
                        $isINOperator = strtolower(trim($condition['operator'])) == "in";
                        $escapedValue = $this->link->quote($condition['value']);
                        if ($isINOperator) {
                            $escapedValue = $this->arrayToItemizedString($condition['value'], $isNumeric);
                        }
                        $queryClauseArray[$chunkCount][]
                            = (!$isNumeric || $isINOperator)
                            ? "{$escapedField} {$condition['operator']} {$escapedValue}"
                            : ("{$escapedField} {$condition['operator']} " . floatval($condition['value']));
                    } else {
                        $queryClauseArray[$chunkCount][] = "{$escapedField} {$condition['operator']}";
                    }
                }
            }
        }
        if (!$isInBlock) {
            $result = $this->arrayToClause($queryClauseArray, $insideOp, $outsideOp);
        } else {
            $result .= ")";
        }
        return $result;
    }

    /**
     * @param $currentOperation
     * @param bool $includeContext
     * @param bool $includeExtra
     * @param string $signedUser
     * @return string
     */
    private function getWhereClause(
        $currentOperation, $includeContext = true, $includeExtra = true, $signedUser = '', $bypassAuth = false)
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $queryClause = '';
        $primaryKey = $tableInfo['key'] ?? 'id';
        if ($currentOperation == 'read' || $currentOperation == 'query') {
            $targetEntity = $this->dbSettings->getEntityForRetrieve();
        } else {
            $targetEntity = $this->dbSettings->getEntityForUpdate();
        }
        $numericFields = $this->handler->getNumericFields($targetEntity);
        if (isset($tableInfo['numeric-fields']) && is_array($tableInfo['numeric-fields'])) {
            $numericFields = array_merge($numericFields, $tableInfo['numeric-fields']);
        }
        if ($includeContext && isset($tableInfo['query'][0])) {
            $queryClause = $this->generateWhereClause($tableInfo['query'], $primaryKey, $numericFields);
        }
        $exCriteria = $this->dbSettings->getExtraCriteria();
        if ($includeExtra && isset($exCriteria[0])) {
            $queryClause = ($queryClause == '' ? '' : "($queryClause) AND ")
                . '(' . $this->generateWhereClause($exCriteria, $primaryKey, $numericFields, true) . ')';
        }
        if (count($this->dbSettings->getForeignFieldAndValue()) > 0) {
            foreach ($tableInfo['relation'] as $relDef) {
                foreach ($this->dbSettings->getForeignFieldAndValue() as $foreignDef) {
                    if ($relDef['join-field'] == $foreignDef['field']) {
                        $escapedField = $this->handler->quotedEntityName($relDef['foreign-key']);
                        $escapedValue = $this->link->quote($foreignDef['value']);
                        $op = $relDef['operator'] ?? '=';
                        if (!$this->specHandler->isPossibleOperator($op)) {
                            throw new Exception("Invalid Operator.");
                        }
                        $queryClause = (($queryClause != '') ? "({$queryClause}) AND " : '')
                            . ((!in_array($relDef['foreign-key'], $numericFields) || strtolower($op) == 'in')
                                ? "{$escapedField}{$op}{$escapedValue}"
                                : ("{$escapedField}{$op}" . floatval($foreignDef['value'])));
                    }
                }
            }
        }
        $keywordAuth = (($currentOperation == "load") || ($currentOperation == "select"))
            ? "read" : $currentOperation;
        if (isset($tableInfo['authentication'])
            && ((isset($tableInfo['authentication']['all'])
                || isset($tableInfo['authentication'][$keywordAuth])))
        ) {
            $authInfoField = $this->authHandler->getFieldForAuthorization($keywordAuth);
            $authInfoTarget = $this->authHandler->getTargetForAuthorization($keywordAuth);
            if ($authInfoTarget == 'field-user') {
                if (strlen($signedUser) == 0 && !$bypassAuth) {
                    $queryClause = 'FALSE';
                } else {
                    $queryClause = (($queryClause != '') ? "({$queryClause}) AND " : '')
                        . "({$authInfoField}=" . $this->link->quote($signedUser) . ")";
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($signedUser);
                $groupCriteria = array();
                foreach ($belongGroups as $oneGroup) {
                    $groupCriteria[] = "{$authInfoField}=" . $this->link->quote($oneGroup);
                }
                if ((strlen($signedUser) == 0 || count($groupCriteria) == 0) && !$bypassAuth) {
                    $queryClause = 'FALSE';
                } else {
                    $queryClause = (($queryClause != '') ? "({$queryClause}) AND " : '')
                        . "(" . implode(' OR ', $groupCriteria) . ")";
                }
            } else {
                $authorizedUsers = $this->authHandler->getAuthorizedUsers($keywordAuth);
                $authorizedGroups = $this->authHandler->getAuthorizedGroups($keywordAuth);
                $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($signedUser);
                if (count($authorizedUsers) > 0 || count($authorizedGroups) > 0) {
                    if (!in_array($signedUser, $authorizedUsers)
                        && count(array_intersect($belongGroups, $authorizedGroups)) == 0
                        && !$bypassAuth
                    ) {
                        $queryClause = 'FALSE';
                    }
                }
            }
        }
        if (!is_null($this->softDeleteField) && !is_null($this->softDeleteValue)) {
            $dfEsc = $this->handler->quotedEntityName($this->softDeleteField);
            $dvEsc = $this->link->quote($this->softDeleteValue);
            if (strlen($queryClause) > 0) {
                $queryClause = "($queryClause) AND ($dfEsc <> $dvEsc OR $dfEsc IS NULL)";
            } else {
                $queryClause = "($dfEsc <> $dvEsc OR $dfEsc IS NULL)";
            }
        }
        return $queryClause;
    }

    /* Genrate SQL Sort and Where clause */
    /**
     * @return string
     */
    private function getSortClause()
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $sortClause = array();
        if (count($this->dbSettings->getExtraSortKey()) > 0) {
            foreach ($this->dbSettings->getExtraSortKey() as $condition) {
                $escapedField = $this->handler->quotedEntityName($condition['field']);
                if (isset($condition['direction'])) {
                    if (!$this->specHandler->isPossibleOrderSpecifier($condition['direction'])) {
                        throw new Exception("Invalid Sort Specifier.");
                    }
                    $sortClause[] = "{$escapedField} {$condition['direction']}";
                } else {
                    $sortClause[] = $escapedField;
                }
            }
        }
        if (isset($tableInfo['sort'])) {
            foreach ($tableInfo['sort'] as $condition) {
                if (isset($condition['direction']) && !$this->specHandler->isPossibleOrderSpecifier($condition['direction'])) {
                    throw new Exception("Invalid Sort Specifier.");
                }
                $escapedField = $this->handler->quotedEntityName($condition['field']);
                $direction = $condition['direction'] ?? "";
                $sortClause[] = "{$escapedField} {$direction}";
            }
        }
        return implode(',', $sortClause);
    }

    public function normalizedCondition($condition)
    {
        if (!isset($condition['field'])) {
            $condition['field'] = '';
        }
        if (!isset($condition['value'])) {
            $condition['value'] = '';
        }
        if (!isset($condition['operator'])) {
            $condition['operator'] = '=';
        }

        if ($condition['operator'] == 'match*') {
            return array(
                'field' => $condition['field'],
                'operator' => 'LIKE',
                'value' => "{$condition['value']}%",
            );
        } else if ($condition['operator'] == '*match') {
            return array(
                'field' => $condition['field'],
                'operator' => 'LIKE',
                'value' => "%{$condition['value']}",
            );
        } else if ($condition['operator'] == '*match*') {
            return array(
                'field' => $condition['field'],
                'operator' => 'LIKE',
                'value' => "%{$condition['value']}%",
            );
        } else if ($this->specHandler->isOperatorWithoutValue($condition['operator'])) {
            return array(
                'field' => $condition['field'],
                'operator' => $condition['operator'],
            );
        }
        return $condition;
    }

}