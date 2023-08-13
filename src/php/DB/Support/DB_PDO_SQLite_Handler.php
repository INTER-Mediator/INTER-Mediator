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

namespace INTERMediator\DB\Support;

use Exception;

class DB_PDO_SQLite_Handler extends DB_PDO_Handler
{
    protected array $tableInfo = array();
    protected string $fieldNameForField = 'name';
    protected string $fieldNameForType = 'type';
    protected string $fieldNameForNullable = 'notnull';
    protected array $numericFieldTypes = array('integer', 'int', 'real', 'numeric',
        'tinyint', 'smallint', 'mediumint', 'bigint', 'unsigned big int', 'int2', 'int8',
        'double', 'double precision', 'float', 'decimal', 'boolean');
    protected array $timeFieldTypes = ['datetime', 'time', 'timestamp'];
    protected array $dateFieldTypes = ['datetime', 'date', 'timestamp'];
    protected array $booleanFieldTypes = [];

    public function sqlSELECTCommand(): string
    {
        return "SELECT ";
    }

    public function sqlLimitCommand(string $param): string
    {
        return "LIMIT {$param}";
    }

    public function sqlOffsetCommand(string $param): string
    {
        return "OFFSET {$param}";
    }

    public function sqlDELETECommand(): string
    {
        return "DELETE FROM ";
    }

    public function sqlUPDATECommand(): string
    {
        return "UPDATE ";
    }

    public function sqlINSERTCommand(string $tableRef, string $setClause): string
    {
        return "INSERT INTO {$tableRef} {$setClause}";
    }

    public function sqlREPLACECommand(string $tableRef, string $setClause): string
    {
        return "REPLACE INTO {$tableRef} {$setClause}";
    }

    public function sqlSETClause(string $tableName, array $setColumnNames, string $keyField, array $setValues): string
    {
        [$setNames, $setValuesConv] = $this->sqlSETClauseData($tableName, $setColumnNames, $setValues);
        return (count($setColumnNames) == 0) ? "DEFAULT VALUES" :
            '(' . implode(',', $setNames) . ') VALUES(' . implode(',', $setValuesConv) . ')';
    }

    public function dateResetForNotNull(): string
    {
        return '1970-01-01';
    }

    protected function checkNullableField(string $info): bool
    {
        return $info == 0;
    }

    protected function getAutoIncrementField(string $tableName): ?string
    {
//        if ($this->dbClassObj->link) {
//            $seqCount = $this->dbClassObj->link->query('SELECT COUNT(*) FROM sqlite_sequence WHERE name=' . $tableName);
//            $row = $seqCount->fetch(\PDO::FETCH_NUM);
//            if($row && isset($row[0])) {
//                if($row[0]>0) {
//                    return null; // OMG
//                }
//            }
//        }
        return '_CANCEL_THE_INCR_FIELD_DETECT_';
        // SQLite doesn't support to create a record with non AUTOINCREMENT field as the primary key.
    }

    protected function getTableInfoSQL(string $tableName): string
    {
        return "PRAGMA table_info({$tableName})";
    }

    /*
sqlite> .mode column
sqlite> PRAGMA table_info(testtable);
cid  name   type          notnull  dflt_value             pk
---  -----  ------------  -------  ---------------------  --
0    id     INTEGER       0                               1
1    num1   INT           1        0                      0
2    num2   INT           0                               0
3    num3   INT           0                               0
4    dt1    DateTime      1        CURRENT_TIMESTAMP      0
5    dt2    DateTime      0                               0
6    dt3    DateTime      0                               0
7    date1  Date          1        CURRENT_TIMESTAMP      0
8    date2  Date          0                               0
9    time1  Time          1        CURRENT_TIMESTAMP      0
10   time2  Time          0                               0
11   ts1    Timestamp     1        CURRENT_TIMESTAMP      0
12   ts2    Timestamp     0        '2001-01-01 00:00:00'  0
13   vc1    VARCHAR(100)  1        ''                     0
14   vc2    VARCHAR(100)  0                               0
15   vc3    VARCHAR(100)  0                               0
16   text1  TEXT          1        ''                     0
17   text2  TEXT          0                               0

    https://stackoverflow.com/questions/20979239/how-to-tell-if-a-sqlite-column-is-autoincrement

sqlite> SELECT COUNT(*) FROM sqlite_sequence WHERE name='testtable';
1
sqlite> SELECT * FROM sqlite_sequence WHERE name='testtable';
testtable|106
sqlite> SELECT COUNT(*) FROM sqlite_sequence WHERE name='person';
1
sqlite> SELECT * FROM sqlite_sequence;
person|90
contact|52
contact_way|6

       */

    protected function getFieldListsForCopy(string $tableName, string $keyField, ?string $assocField, ?string $assocValue,
                                            ?array $defaultValues): array
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $fieldArray = array();
        $listArray = array();
        foreach ($result as $row) {
            if ($keyField === $row['name'] || !is_null($row['dflt_value'])) {

            } else if ($assocField === $row['name']) {
                $fieldArray[] = $this->quotedEntityName($row['name']);
                $listArray[] = $this->dbClassObj->link->quote($assocValue);
            } else if (isset($defaultValues[$row['name']])) {
                $fieldArray[] = $this->quotedEntityName($row['name']);
                $listArray[] = $this->dbClassObj->link->quote($defaultValues[$row['name']]);
            } else {
                $fieldArray[] = $this->quotedEntityName($row['name']);
                $listArray[] = $this->quotedEntityName($row['name']);
            }
        }
        return array(implode(',', $fieldArray), implode(',', $listArray));
    }

    public function quotedEntityName(string $entityName): ?string
    {
        $q = '"';
        if (strpos($entityName, ".") !== false) {
            $components = explode(".", $entityName);
            $quotedName = array();
            foreach ($components as $item) {
                $quotedName[] = $q . str_replace($q, $q . $q, $item ?? "") . $q;
            }
            return implode(".", $quotedName);
        }
        return $q . str_replace($q, $q . $q, $entityName ?? "") . $q;

    }

    public function optionalOperationInSetup(): void
    {
    }


    public function authSupportCanMigrateSHA256Hash(string $userTable, string $hashTable): ?array  // authuser, issuedhash
    {
        $checkFieldDefinition = function (string $type, int $min): bool {
            $fDef = strtolower($type);
            if ($fDef != 'text' && strpos($fDef, 'varchar') !== false) {
                $openParen = strpos($fDef, '(');
                $closeParen = strpos($fDef, ')');
                $len = intval(substr($fDef, $openParen + 1, $closeParen - $openParen - 1));
                if ($len < $min) {
                    return false;
                }
            }
            return true;
        };

        $infoAuthUser = $this->getTableInfo($userTable);
        $infoIssuedHash = $this->getTableInfo($hashTable);
        $returnValue = [];
        if ($infoAuthUser) {
            foreach ($infoAuthUser as $fieldInfo) {
                if (isset($fieldInfo['name'])
                    && $fieldInfo['name'] == 'hashedpasswd'
                    && !$checkFieldDefinition($fieldInfo['type'], 72)) {
                    $returnValue[] = "The hashedpassword field of the authuser table has to be longer than 72 characters.";
                }
            }
        }
        if ($infoIssuedHash) {
            foreach ($infoIssuedHash as $fieldInfo) {
                if (isset($fieldInfo['name'])
                    && $fieldInfo['name'] == 'clienthost'
                    && !$checkFieldDefinition($fieldInfo['type'], 64)) {
                    $returnValue[] = "The clienthost field of the issuedhash table has to be longer than 64 characters.";
                }
                if (isset($fieldInfo['name'])
                    && $fieldInfo['name'] == 'hash'
                    && !$checkFieldDefinition($fieldInfo['type'], 64)) {
                    $returnValue[] = "The hash field of the issuedhash table has to be longer than 64 characters.";
                }
            }
        }
        return $returnValue;
    }
}
