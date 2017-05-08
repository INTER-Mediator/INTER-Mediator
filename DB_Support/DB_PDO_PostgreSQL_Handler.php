<?php

/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2016/07/09
 * Time: 0:46
 */
class DB_PDO_PostgreSQL_Handler extends DB_PDO_Handler
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

    public function sqlSETClause($setColumnNames, $keyField, $setValues)
    {
        return (count($setColumnNames) == 0) ? "DEFAULT VALUES" :
            '(' . implode(',', $setColumnNames) . ') VALUES(' . implode(',', $setValues) . ')';
    }

    public function getNullableNumericFields($tableName)
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $fieldNameForField = 'column_name';
        $fieldNameForNullable = 'is_nullable';
        $fieldNameForType = 'data_type';
        $fieldArray = array();
        $numericFieldTypes = array('smallint', 'integer', 'bigint', 'decimal', 'numeric',
            'real', 'double precision', 'smallserial', 'serial', 'bigserial', 'money',
            'timestamp', 'date', 'time', 'interval', );
        $matches = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            preg_match("/[a-z ]+/", strtolower($row[$fieldNameForType]), $matches);
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
            if (strpos($tableName, ".") !== false) {
                $tName = substr($tableName, strpos($tableName, ".") + 1);
                $schemaName = substr($tableName, 0, strpos($tableName, "."));
                $sql = "SELECT column_name, column_default, is_nullable, data_type FROM information_schema.columns "
                    . "WHERE table_schema=" . $this->dbClassObj->link->quote($schemaName)
                    . " AND table_name=" . $this->dbClassObj->link->quote($tName);
            } else {
                $sql = "SELECT column_name, column_default, is_nullable, data_type FROM information_schema.columns "
                    . "WHERE table_name=" . $this->dbClassObj->link->quote($tableName);
            }
            $this->dbClassObj->logger->setDebugMessage($sql);
            $result = $this->dbClassObj->link->query($sql);
            if (!$result) {
                throw new Exception('INSERT Error:' . $sql);
            }
        } else {
            $result = $this->tableInfo[$tableName];
        }
        return $result;
    }
    /*
# select table_catalog,table_schema,table_name,column_name,column_default from information_schema.columns where table_name='person';
table_catalog | table_schema | table_name | column_name |                column_default
---------------+--------------+------------+-------------+----------------------------------------------
test_db       | im_sample    | person     | id          | nextval('im_sample.person_id_seq'::regclass)
test_db       | im_sample    | person     | name        |
test_db       | im_sample    | person     | address     |
test_db       | im_sample    | person     | mail        |
test_db       | im_sample    | person     | category    |
test_db       | im_sample    | person     | checking    |
test_db       | im_sample    | person     | location    |
test_db       | im_sample    | person     | memo        |
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
            if ($keyField === $row['column_name'] || !is_null($row['column_default'])) {

            } else if ($assocField === $row['column_name']) {
                $fieldArray[] = $this->quotedEntityName($row['column_name']);
                $listArray[] = $this->dbClassObj->link->quote($assocValue);
            } else {
                $fieldArray[] = $this->quotedEntityName($row['column_name']);
                $listArray[] = $this->quotedEntityName($row['column_name']);
            }
        }
        return array(implode(',', $fieldArray), implode(',', $listArray));
    }

    public function isPossibleOperator($operator)
    {
        return !(FALSE === array_search(strtoupper($operator), array(
                'LIKE', //
                'SIMILAR TO', //
                '~*', //	正規表現に一致、大文字小文字の区別なし	'thomas' ~* '.*Thomas.*'
                '!~', //	正規表現に一致しない、大文字小文字の区別あり	'thomas' !~ '.*Thomas.*'
                '!~*', //	正規表現に一致しない、大文字小文字の区別なし	'thomas' !~* '.*vadim.*'
                '||', //  文字列の結合
                '+', //	和	2 + 3	5
                '-', //	差	2 - 3	-1
                '*', //	積	2 * 3	6
                '/', //	商（整数の割り算では余りを切り捨て）	4 / 2	2
                '%', //	剰余（余り）	5 % 4	1
                '^', //	累乗	2.0 ^ 3.0	8
                '|/', //	平方根	|/ 25.0	5
                '||/', //	立方根	||/ 27.0	3
                '!', //	階乗	5 !	120
                '!!', //	階乗（前置演算子）	!! 5	120
                '@', //	絶対値	@ -5.0	5
                '&', //	バイナリのAND	91 & 15	11
                '|', //	バイナリのOR	32 | 3	35
                '#', //	バイナリのXOR	17 # 5	20
                '~', //	バイナリのNOT	~1	-2
                '<<', //	バイナリの左シフト	1 << 4	16
                '>>', //	バイナリの右シフト
                'AND', //
                'OR', //
                'NOT', //
                '<', //	小なり
                '>', //	大なり
                '<=', //	等しいかそれ以下
                '>=', //	等しいかそれ以上
                '=', //	等しい
                '<>', // または !=	等しくない
                '||', //	結合	B'10001' || B'011'	10001011
                '&', //	ビットのAND	B'10001' & B'01101'	00001
                '|', //	ビットのOR	B'10001' | B'01101'	11101
                '#', //	ビットのXOR	B'10001' # B'01101'	11100
                '~', //	ビットのNOT	~ B'10001'	01110
                '<<', //ビットの左シフト	B'10001' << 3	01000
                '>>', //ビットの右シフト	B'10001' >> 2	00100
                'IN'
                //[上記に含まれないもの]
                //幾何データ型、ネットワークアドレス型、JSON演算子、配列演算子、範囲演算子
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

    public function optionalOperationInSetup()
    {
    }

}