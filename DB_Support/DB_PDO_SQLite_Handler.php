<?php

/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2016/07/09
 * Time: 0:47
 */
class DB_PDO_SQLite_Handler extends DB_PDO_Handler
{
    public function sqlSELECTCommand()
    {
        return "SELECT ";
    }

    public function sqlDELETECommand()
    {
        return "DELETE ";
    }

    public function sqlUPDATECommand()
    {
        return "UPDATE ";
    }

    public function sqlINSERTCommand()
    {
        return "INSERT INTO ";
    }

    public function copyRecords($tableInfo, $queryClause, $assocField, $assocValue)
    {
        $tableName = isset($tableInfo["table"]) ? $tableInfo["table"] : $tableInfo["name"];

        /*
         sqlite> PRAGMA table_info(person);
         cid         name        type        notnull     dflt_value  pk
         ----------  ----------  ----------  ----------  ----------  ----------
         0           id          INTEGER     0                       1
         1           name        TEXT        0                       0
         2           address     TEXT        0                       0
         3           mail        TEXT        0                       0
         4           category    INTEGER     0                       0
         5           checking    INTEGER     0                       0
         6           location    INTEGER     0                       0
         7           memo        TEXT        0                       0
          */
        $sql = "PRAGMA table_info({$tableName})";
        $this->dbClassObj->logger->setDebugMessage($sql);
        $result = $this->dbClassObj->link->query($sql);
        if (!$result) {
            $this->dbClassObj->errorMessageStore('PRAGMA table_info Error:' . $sql);
            return false;
        }
        $fieldArray = array();
        $listArray = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($tableInfo['key'] === $row['name'] || !is_null($row['dflt_value'])) {

            } else if ($assocField === $row['name']) {
                $fieldArray[] = $this->quotedEntityName($row['name']);
                $listArray[] = $this->dbClassObj->link->quote($assocValue);
            } else {
                $fieldArray[] = $this->quotedEntityName($row['name']);
                $listArray[] = $this->quotedEntityName($row['name']);
            }
        }
        $fieldList = implode(',', $fieldArray);
        $listList = implode(',', $listArray);

        $sql = "{$this->sqlINSERTCommand()}{$tableName} ({$fieldList}) SELECT {$listList} FROM {$tableName} WHERE {$queryClause}";
        $this->dbClassObj->logger->setDebugMessage($sql);
        $result = $this->dbClassObj->link->query($sql);
        if (!$result) {
            $this->dbClassObj->errorMessageStore('INSERT Error:' . $sql);
            return false;
        }
        return $this->dbClassObj->link->lastInsertId($tableName);
    }

    public function isPossibleOperator($operator)
    {
        return !(FALSE === array_search(strtoupper($operator), array(
                '||',
                '*', '/', '%',
                '+', '-',
                '<<', '>>', '&', '|',
                '<', '<=', '>', '>=',
                '=', '==', '!=', '<>', 'IS', 'IS NOT', 'IN', 'LIKE', 'GLOB', 'MATCH', 'REGEXP',
                'AND',
                'IS NULL', //NULL value test
                'OR',
                'IN',
                '-', '+', '~', 'NOT',
            )));
    }

    public function isPossibleOrderSpecifier($specifier)
    {
        return !(array_search(strtoupper($specifier), array('ASC', 'DESC')) === FALSE);
    }

    public function quotedEntityName($entityName)
    {
        $q = '"';
        if (strpos($entityName, ".") !== false) {
            $components = explode(".", $entityName);
            $quotedName = array();
            foreach ($components as $item) {
                $quotedName[] = $q . str_replace($q, $q . $q, $item) . $q;
            }
            return implode(".", $quotedName);
        }
        return $q . str_replace($q, $q . $q, $entityName) . $q;

    }
}