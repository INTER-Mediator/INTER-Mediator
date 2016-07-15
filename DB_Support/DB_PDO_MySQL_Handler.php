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

    public function copyRecords($tableInfo, $queryClause, $assocField, $assocValue)
    {
        $tableName = isset($tableInfo["table"]) ? $tableInfo["table"] : $tableInfo["name"];
        $sql = "SHOW COLUMNS FROM {$tableName}";
        $this->dbClassObj->logger->setDebugMessage($sql);
        $result = $this->dbClassObj->link->query($sql);
        if (!$result) {
            $this->dbClassObj->errorMessageStore('Show Columns Error:' . $sql);
            return false;
        }
        $fieldArray = array();
        $listArray = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($tableInfo['key'] === $row['Field'] || !is_null($row['Default'])) {

            } else if ($assocField === $row['Field']) {
                $fieldArray[] = $this->dbClassObj->quotedFieldName($row['Field']);
                $listArray[] = $this->dbClassObj->link->quote($assocValue);
            } else {
                $fieldArray[] = $this->dbClassObj->quotedFieldName($row['Field']);
                $listArray[] = $this->dbClassObj->quotedFieldName($row['Field']);
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

}