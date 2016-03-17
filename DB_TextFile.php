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

class DB_TextFile extends DB_AuthCommon implements DB_Access_Interface
{
    private $recordCount;

    function readFromDB()
    {
        $textFormat = strtolower($this->dbSettings->getDbSpecDataType());
        if ($textFormat == "csv") {
            $fileRoot = $this->dbSettings->getDbSpecDatabase();
            $fileName = $this->dbSettings->getEntityForRetrieve();
            $metaFilePath = "{$fileRoot}/{$fileName}.meta";
            $dataFilePath = "{$fileRoot}/{$fileName}.csv";
//        $this->logger->setErrorMessage($metaFilePath);
//        $this->logger->setErrorMessage($dataFilePath);

            if (substr_count($metaFilePath, '../') > 2 || substr_count($dataFilePath, '../') > 2) {
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

    public function countQueryResult()
    {
        return $this->recordCount;
    }

    private function getWhereClause($currentOperation, $includeContext = true, $includeExtra = true, $signedUser = '')
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $queryClause = '';
        $primaryKey = isset($tableInfo['key']) ? $tableInfo['key'] : 'id';

        // 'field' => '__operation__' is not supported.
        // Authentication and Authorization are NOT supported.

        $queryClauseArray = array();
        if ($includeContext && isset($tableInfo['query'][0])) {
            foreach ($tableInfo['query'] as $condition) {
                if (!$this->dbSettings->getPrimaryKeyOnly() || $condition['field'] == $primaryKey) {
                    if (!$this->isPossibleOperator($condition['operator'])) {
                        throw new Exception("Invalid Operator.: {$condition['operator']}");
                    }
                    $queryClauseArray[] = array(
                        "field" => $condition['field'],
                        "value" => $condition['value'],
                        "operator" => isset($condition['operator']) ? $condition['operator'] : "=",
                    );
                }
            }
        }
        $exCriteria = $this->dbSettings->getExtraCriteria();
        if ($includeExtra && isset($exCriteria[0])) {
            foreach ($this->dbSettings->getExtraCriteria() as $condition) {
                if (!$this->dbSettings->getPrimaryKeyOnly() || $condition['field'] == $primaryKey) {
                    if (!$this->isPossibleOperator($condition['operator'])) {
                        throw new Exception("Invalid Operator.: {$condition['operator']}");
                    }
                    $queryClauseArray[] = array(
                        "field" => $condition['field'],
                        "value" => $condition['value'],
                        "operator" => isset($condition['operator']) ? $condition['operator'] : "=",
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
                            "operator" => isset($relDef['operator']) ? $relDef['operator'] : "=",
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
    private function getSortClause()
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
                    "direction" => isset($condition['direction']) ? $condition['direction'] : "ASC",
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
                    "direction" => isset($condition['direction']) ? $condition['direction'] : "ASC",
                );
            }
        }
        return $sortClause;
    }

    public
    function updateDB()
    {

    }

    public
    function deleteFromDB()
    {
    }

    function getFieldInfo($dataSourceName)
    {
        // TODO: Implement getFieldInfo() method.
    }

    function authSupportStoreChallenge($username, $challenge, $clientId)
    {
        // TODO: Implement authSupportStoreChallenge() method.
    }

    function authSupportRemoveOutdatedChallenges()
    {
        // TODO: Implement authSupportRemoveOutdatedChallenges() method.
    }

    function authSupportRetrieveChallenge($username, $clientId, $isDelete = true)
    {
        // TODO: Implement authSupportRetrieveChallenge() method.
    }

    function authSupportRetrieveHashedPassword($username)
    {
        // TODO: Implement authSupportRetrieveHashedPassword() method.
    }

    function authSupportCreateUser($username, $hashedpassword, $isLDAP = false, $ldapPassword = null)
    {
        // TODO: Implement authSupportCreateUser() method.
    }

    function authSupportChangePassword($username, $hashednewpassword)
    {
        // TODO: Implement authSupportChangePassword() method.
    }

    function authSupportCheckMediaToken($user)
    {
        // TODO: Implement authSupportCheckMediaToken() method.
    }

    function authSupportCheckMediaPrivilege($tableName, $userField, $user, $keyField, $keyValue)
    {
        // TODO: Implement authSupportCheckMediaPrivilege() method.
    }

    function authSupportGetUserIdFromEmail($email)
    {
        // TODO: Implement authSupportGetUserIdFromEmail() method.
    }

    function authSupportGetUserIdFromUsername($username)
    {
        // TODO: Implement authSupportGetUserIdFromUsername() method.
    }

    function authSupportGetUsernameFromUserId($userid)
    {
        // TODO: Implement authSupportGetUsernameFromUserId() method.
    }

    function authSupportGetGroupNameFromGroupId($groupid)
    {
        // TODO: Implement authSupportGetGroupNameFromGroupId() method.
    }

    function authSupportGetGroupsOfUser($user)
    {
        // TODO: Implement authSupportGetGroupsOfUser() method.
    }

    function authSupportUnifyUsernameAndEmail($username)
    {
        // TODO: Implement authSupportUnifyUsernameAndEmail() method.
    }

    function authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash)
    {
        // TODO: Implement authSupportStoreIssuedHashForResetPassword() method.
    }

    function authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash)
    {
        // TODO: Implement authSupportCheckIssuedHashForResetPassword() method.
    }

    public
    function setupConnection()
    {
        // TODO: Implement setupConnection() method.
    }

    public
    static function defaultKey()
    {
        // TODO: Implement defaultKey() method.
    }

    public function getDefaultKey()
    {
        // TODO: Implement getDefaultKey() method.
    }

    public function isPossibleOperator($operator)
    {
        return !(FALSE === array_search(strtoupper($operator), array(
                '=',
            )));
    }

    public function isPossibleOrderSpecifier($specifier)
    {
        return !(array_search(strtoupper($specifier), array('ASC', 'DESC')) === FALSE);
    }

    public function requireUpdatedRecord($value)
    {
        // TODO: Implement requireUpdatedRecord() method.
    }

    public function updatedRecord()
    {
        // TODO: Implement updatedRecord() method.
    }

    public function isContainingFieldName($fname, $fieldnames)
    {
        // TODO: Implement isContainingFieldName() method.
    }

    public function isNullAcceptable()
    {
        // TODO: Implement isNullAcceptable() method.
    }

    public function createInDB($bypassAuth)
    {
        // TODO: Implement newToDB() method.
    }

    public function softDeleteActivate($field, $value)
    {
        // TODO: Implement softDeleteActivate() method.
    }

    public function copyInDB()
    {
        return false;
    }

    public function getTotalCount()
    {
        // TODO: Implement getTotalCount() method.
    }

    public function isSupportAggregation()
    {
        return false;
    }

    public function authSupportUserEnrollmentStart($userid, $hash)
    {
        // TODO: Implement authSupportUserEnrollmentStart() method.
    }

    public function authSupportUserEnrollmentActivateUser($hash, $password)
    {
        // TODO: Implement authSupportUserEnrollmentActivateUser() method.
    }
}