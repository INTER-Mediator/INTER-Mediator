<?php

/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2016/07/09
 * Time: 0:54
 */

require_once("DB_PDO_MySQL_Handler.php");
require_once("DB_PDO_PostgreSQL_Handler.php");
require_once("DB_PDO_SQLite_Handler.php");

abstract class DB_PDO_Handler
{
    protected $dbClassObj = null;

    public static function generateHandler($dbObj, $dsn)
    {
        if (is_null($dbObj)) {
            return null;
        }
        if (strpos($dsn, 'mysql:') === 0) {
            $instance = new DB_PDO_MySQL_Handler();
            $instance->dbClassObj = $dbObj;
            return $instance;
        } else if (strpos($dsn, 'pgsql:') === 0) {
            $instance = new DB_PDO_PostgreSQL_Handler();
            $instance->dbClassObj = $dbObj;
            return $instance;
        } else if (strpos($dsn, 'sqlite:') === 0) {
            $instance = new DB_PDO_SQLite_Handler();
            $instance->dbClassObj = $dbObj;
            return $instance;
        }
        return null;
    }

    public abstract function sqlSELECTCommand();

    public abstract function sqlDELETECommand();

    public abstract function sqlUPDATECommand();

    public abstract function sqlINSERTCommand();

    public abstract function copyRecords($tableInfo, $queryClause, $assocField, $assocValue);
}