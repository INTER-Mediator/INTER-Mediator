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
                '&',// (Bitwise AND)
                '&=',// (Bitwise AND EQUALS)
                '|',// (Bitwise OR)
                '|=',// (Bitwise OR EQUALS)
                '^',// (Bitwise Exclusive OR)
                '^=',// (Bitwise Exclusive OR EQUALS)
                '~',// (Bitwise NOT)
                '=',// (Equals)	Equal to
                '>',// (Greater Than)	Greater than
                '<',// (Less Than)	Less than
                '>=',// (Greater Than or Equal To)	Greater than or equal to
                '<=',// (Less Than or Equal To)	Less than or equal to
                '<>',// (Not Equal To)	Not equal to
                '!=',// (Not Equal To)	Not equal to (not ISO standard)
                '!<',// (Not Less Than)	Not less than (not ISO standard)
                '!>',// (Not Greater Than)	Not greater than (not ISO standard)
                'ALL',//	TRUE if all of a set of comparisons are TRUE.
                'AND',//	TRUE if both Boolean expressions are TRUE.
                'ANY',//	TRUE if any one of a set of comparisons are TRUE.
                'BETWEEN',//	TRUE if the operand is within a range.
                'EXISTS',//	TRUE if a subquery contains any rows.
                'IN',//	TRUE if the operand is equal to one of a list of expressions.
                'LIKE',//	TRUE if the operand matches a pattern.
                'NOT',//	Reverses the value of any other Boolean operator.
                'OR',//	TRUE if either Boolean expression is TRUE.
            )));
    }

    public function isPossibleOrderSpecifier($specifier)
    {
        return !(array_search(strtoupper($specifier), array('ASC', 'DESC')) === FALSE);
    }

}