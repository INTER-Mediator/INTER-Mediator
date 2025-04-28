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

use INTERMediator\DB\DBClass;

/**
 * Handler for PDO-based database specification behavior.
 * Implements the DB_Spec_Behavior interface for general PDO backends (MySQL, PostgreSQL, SQLite, SQLServer).
 */
class DB_Spec_Handler_PDO implements DB_Spec_Behavior
{
    /**
     * @var DBClass|null Reference to the associated DBClass object.
     */
    public ?DBClass $dbClassObj = null;

    /**
     * Generates a handler instance based on DSN for a specific PDO backend.
     *
     * @param DBClass|null $dbObj Reference to DBClass object.
     * @param string $dsn Data Source Name (DSN) string.
     * @return DB_Spec_Handler_PDO|null Handler instance for the backend, or null if not supported.
     */
    public static function generateHandler(?DBClass $dbObj, string $dsn): ?DB_Spec_Handler_PDO
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

    /**
     * Returns the default key name for PDO-based backends (static method).
     *
     * @return string Default key name.
     */
    public static function defaultKey(): string
    {
        return "id";
    }

    /**
     * Returns the default key name for PDO-based backends (instance method).
     *
     * @return string Default key name.
     */
    public function getDefaultKey(): string
    {
        return "id";
    }

    /**
     * Checks if aggregation is supported (always true for PDO-based backends).
     *
     * @return bool True (aggregation supported).
     */
    public function isSupportAggregation(): bool
    {
        return true;
    }

    /**
     * Checks if the given field name is in the provided list of field names.
     *
     * @param string $fname Field name to check.
     * @param array $fieldnames Array of available field names.
     * @return bool True if $fname is in $fieldnames, false otherwise.
     */
    public function isContainingFieldName(string $fname, array $fieldnames): bool
    {
        return in_array($fname, $fieldnames);
    }

    /**
     * Checks if NULL values are acceptable (always true for PDO-based backends).
     *
     * @return bool True (NULL acceptable).
     */
    public function isNullAcceptable(): bool
    {
        return true;
    }

    /**
     * Checks if the given operator does not require a value (e.g., IS NULL).
     *
     * @param string $operator Operator to check.
     * @return bool True if the operator does not require a value, false otherwise.
     */
    public function isOperatorWithoutValue(string $operator): bool
    {
        return in_array(strtoupper($operator), array(
            'IS NOT NULL', //	NOT NULL value test
            'IS NULL', //NULL value test
        ));
    }

    /**
     * Checks if the given operator is valid for PDO-based backends.
     *
     * @param string $operator Operator to check.
     * @return bool True if the operator is valid, false otherwise.
     */
    public function isPossibleOperator(string $operator): bool
    {
        return in_array(strtoupper($operator), array(
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
        ));
    }

    /**
     * Checks if the given specifier is a valid order specifier for PDO-based backends.
     *
     * @param string $specifier Order specifier to check.
     * @return bool True if the specifier is valid, false otherwise.
     */
    public function isPossibleOrderSpecifier(string $specifier): bool
    {
        return in_array(strtoupper($specifier), array('ASC', 'DESC'));
    }
}
