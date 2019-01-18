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
class DB_PDO_MySQL_Handler extends DB_PDO_Handler
{
    public function sqlSELECTCommand()
    {
        return "SELECT ";
    }

    public function sqlLimitCommand($param)
    {
        return "LIMIT {$param}";
    }

    public function sqlOffsetCommand($param)
    {
        return "OFFSET {$param}";
    }

    public function sqlDELETECommand()
    {
        return "DELETE FROM ";
    }

    public function sqlUPDATECommand()
    {
        return "UPDATE IGNORE ";
    }

    public function sqlINSERTCommand()
    {
        return "INSERT IGNORE INTO ";
    }

    public function sqlSETClause($setColumnNames, $keyField, $setValues)
    {
        return (count($setColumnNames) == 0) ? "SET {$keyField}=DEFAULT" :
            '(' . implode(',', $setColumnNames) . ') VALUES(' . implode(',', $setValues) . ')';
    }

    public function getNullableNumericFields($tableName)
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $fieldNameForField = 'Field';
        $fieldNameForNullable = 'Null';
        $fieldNameForType = 'Type';
        $fieldArray = array();
        $numericFieldTypes = array('int', 'integer', 'numeric', 'smallint', 'tinyint', 'mediumint',
            'bigint', 'decimal', 'float', 'double', 'bit', 'dec', 'fixed', 'double percision',
            'date', 'datetime', 'timestamp', 'time', 'year',);
        $matches = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            preg_match("/[a-z]+/", strtolower($row[$fieldNameForType]), $matches);
            if ($row[$fieldNameForNullable] &&
                in_array($matches[0], $numericFieldTypes)
            ) {
                $fieldArray[] = $row[$fieldNameForField];
            }
        }
        return $fieldArray;
    }

    private $tableInfo = array();

    protected function getTableInfo($tableName)
    {
        if (!isset($this->tableInfo[$tableName])) {
            $sql = "SHOW COLUMNS FROM " . $this->quotedEntityName($tableName);
            $this->dbClassObj->logger->setDebugMessage($sql);
            $result = $this->dbClassObj->link->query($sql);
            $this->tableInfo[$tableName] = $result;
            if (!$result) {
                throw new Exception('INSERT Error:' . $sql);
            }
        } else {
            $result = $this->tableInfo[$tableName];
        }
        return $result;
    }

    /*
      * mysql> show columns from func;
+-------+------------------------------+------+-----+---------+-------+
| Field | Type                         | Null | Key | Default | Extra |
+-------+------------------------------+------+-----+---------+-------+
| name  | char(64)                     | NO   | PRI |         |       |
| ret   | tinyint(1)                   | NO   |     | 0       |       |
| dl    | char(128)                    | NO   |     |         |       |
| type  | enum('function','aggregate') | NO   |     | NULL    |       |
+-------+------------------------------+------+-----+---------+-------+
4 rows in set (0.00 sec)
    */

    protected function getFieldListsForCopy($tableName, $keyField, $assocField, $assocValue, $defaultValues)
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $fieldArray = array();
        $listArray = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($keyField === $row['Field'] || !is_null($row['Default'])) {
                // skip key field to asing value.
            } else if ($assocField === $row['Field']) {
                $fieldArray[] = $this->quotedEntityName($row['Field']);
                $listArray[] = $this->dbClassObj->link->quote($assocValue);
            } else if (isset($defaultValues[$row['Field']])) {
                $fieldArray[] = $this->quotedEntityName($row['Field']);
                $listArray[] = $this->dbClassObj->link->quote($defaultValues[$row['Field']]);
            } else {
                $fieldArray[] = $this->quotedEntityName($row['Field']);
                $listArray[] = $this->quotedEntityName($row['Field']);
            }
        }
        return array(implode(',', $fieldArray), implode(',', $listArray));
    }


    public function quotedEntityName($entityName)
    {
        if (strpos($entityName, ".") !== false) {
            $components = explode(".", $entityName);
            $quotedName = array();
            foreach ($components as $item) {
                $quotedName[] = "`{$item}`";
            }
            return implode(".", $quotedName);
        }
        return "`{$entityName}`";
    }

    public function optionalOperationInSetup()
    {
        $this->dbClassObj->link->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }
}
