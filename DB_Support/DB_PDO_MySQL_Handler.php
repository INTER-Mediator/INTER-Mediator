<?php

/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2016/07/09
 * Time: 0:46
 */
class DB_PDO_MySQL_Handler extends DB_PDO_Handler
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
        if (! isset($this->tableInfo[$tableName])) {
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

    protected function getFieldLists($tableName, $keyField, $assocField, $assocValue)
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
            } else {
                $fieldArray[] = $this->quotedEntityName($row['Field']);
                $listArray[] = $this->quotedEntityName($row['Field']);
            }
        }
        return array(implode(',', $fieldArray), implode(',', $listArray));
    }

    public function isPossibleOperator($operator)
    {
        return !(FALSE === array_search(strtoupper($operator), array(
                'AND', '&&', //Logical AND
                '=', //Assign a value (as part of a SET statement, or as part of the SET clause in an UPDATE statement)
                ':=', //Assign a value
                'BETWEEN', //Check whether a value is within a range of values
                'BINARY', //Cast a string to a binary string
                '&', //Bitwise AND
                '~', //Invert bits
                '|', //Bitwise OR
                '^', //Bitwise XOR
                'CASE', //Case operator
                'DIV', //Integer division
                '/', //Division operator
                '<=>', //NULL-safe equal to operator
                '=', //Equal operator
                '>=', //Greater than or equal operator
                '>', //Greater than operator
                'IS NOT NULL', //	NOT NULL value test
                'IS NOT', //Test a value against a boolean
                'IS NULL', //NULL value test
                'IS', //Test a value against a boolean
                '<<', //Left shift
                '<=', //Less than or equal operator
                '<', //Less than operator
                'LIKE', //Simple pattern matching
                '-', //Minus operator
                '%', 'MOD', //Modulo operator
                'NOT BETWEEN', //Check whether a value is not within a range of values
                '!=', '<>', //Not equal operator
                'NOT LIKE', //Negation of simple pattern matching
                'NOT REGEXP', //Negation of REGEXP
                'NOT', '!', //Negates value
                '||', 'OR', //Logical OR
                '+', //Addition operator
                'REGEXP', //Pattern matching using regular expressions
                '>>', //Right shift
                'RLIKE', //Synonym for REGEXP
                'SOUNDS LIKE', //Compare sounds
                '*', //Multiplication operator
                '-', //Change the sign of the argument
                'XOR', //Logical XOR
                'IN',
            )));
    }

    public function isPossibleOrderSpecifier($specifier)
    {
        return !(array_search(strtoupper($specifier), array('ASC', 'DESC')) === FALSE);
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