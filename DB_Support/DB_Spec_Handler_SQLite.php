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
class DB_Spec_Handler_MySQL extends DB_Spec_Handler_PDO
{
    protected $dbClassObj = null;

    public static function generateHandler($dbObj, $dsn)
    {
        if (is_null($dbObj)) {
            return null;
        }
        if (strpos($dsn, 'mysql:') === 0) {
            $instance = new DB_Spec_Handler_MySQL();
            $instance->dbClassObj = $dbObj;
            return $instance;
        } else if (strpos($dsn, 'pgsql:') === 0) {
            $instance = new DB_Spec_Handler_PostgreSQL();
            $instance->dbClassObj = $dbObj;
            return $instance;
        } else if (strpos($dsn, 'sqlite:') === 0) {
            $instance = new DB_Spec_Handler_SQLite();
            $instance->dbClassObj = $dbObj;
            return $instance;
        } else if (strpos($dsn, 'sqlsrv:') === 0) {
            $instance = new DB_Spec_Handler_SQLServer();
            $instance->dbClassObj = $dbObj;
            return $instance;
        }
        return null;
    }

    public static function defaultKey()
    {
        return "id";
    }

    public function getDefaultKey()
    {
        return "id";
    }

    public function isSupportAggregation()
    {
        return true;
    }

    public function isContainingFieldName($fname, $fieldnames)
    {
        return in_array($fname, $fieldnames);
    }

    public function isNullAcceptable()
    {
        return true;
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
}