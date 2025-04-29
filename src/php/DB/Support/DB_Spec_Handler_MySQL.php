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

/**
 * Handler for MySQL-specific specification behavior.
 * Implements the DB_Spec_Behavior interface for MySQL backend.
 */
class DB_Spec_Handler_MySQL extends DB_Spec_Handler_PDO
{
    /**
     * Returns the default key name for MySQL (static method).
     *
     * @return string Default key name.
     */
    public static function defaultKey(): string
    {
        return "id";
    }

    /**
     * Returns the default key name for MySQL (instance method).
     *
     * @return string Default key name.
     */
    public function getDefaultKey(): string
    {
        return "id";
    }

    /**
     * Checks if aggregation is supported (always true for MySQL).
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
     * Checks if NULL values are acceptable (always true for MySQL).
     *
     * @return bool True (NULL acceptable).
     */
    public function isNullAcceptable(): bool
    {
        return true;
    }

    /**
     * Checks if the given operator is valid for MySQL.
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
            'IS NOT NULL', // NOT NULL value test
            'IS NOT', //Test a value against a boolean
            'IS NULL', //NULL value test
            'IS', //Test a value against a boolean
            '<<', //Left shift
            '>>', //Right shift
            '<=', //Less than or equal operator
            '<', //Less than operator
            'LIKE', //Simple pattern matching
            '-', //Minus operator
            '%', 'MOD', //Modulo operator
            'NOT BETWEEN', //Check whether a value is not within a range of values
            '!=', '<>', //Not equal operator
            'NOT LIKE', //Negation of simple pattern matching
            'NOT REGEXP', //Negation of REGEXP
            'REGEXP', //Pattern matching using regular expressions
            '+', //Addition operator
            '||', //Logical OR
            'OR', //Logical OR
            'XOR', //Logical XOR
            '*', //Multiplication operator
            'RLIKE', //Synonym for REGEXP
            'SOUNDS LIKE', //Compare sounds
            'IN', //Check whether a value is within a set of values
            'NOT IN', //Check whether a value is not within a set of values
        ));
    }

    /**
     * Checks if the given specifier is a valid order specifier for MySQL.
     *
     * @param string $specifier Order specifier to check.
     * @return bool True if the specifier is valid, false otherwise.
     */
    public function isPossibleOrderSpecifier(string $specifier): bool
    {
        return in_array(strtoupper($specifier), array('ASC', 'DESC'));
    }
}