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
 * Handler for PostgreSQL-specific specification behavior.
 * Implements the DB_Spec_Behavior interface for PostgreSQL backend.
 */
class DB_Spec_Handler_PostgreSQL extends DB_Spec_Handler_PDO
{
    /**
     * Returns the default key name for PostgreSQL (static method).
     *
     * @return string Default key name.
     */
    public static function defaultKey(): string
    {
        return "id";
    }

    /**
     * Returns the default key name for PostgreSQL (instance method).
     *
     * @return string Default key name.
     */
    public function getDefaultKey(): string
    {
        return "id";
    }

    /**
     * Checks if aggregation is supported (always true for PostgreSQL).
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
     * Checks if NULL values are acceptable (always true for PostgreSQL).
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
            'IS NOT NULL', // NOT NULL value test
            'IS NULL', // NULL value test
            'NOTNULL', // NOT NULL value test
            'ISNULL', // NULL value test
            'IS TRUE',
            'IS NOT TRUE',
            'IS FALSE',
            'IS NOT FALSE',
            'IS UNKNOWN',
            'IS NOT UNKNOWN',
        ));
    }

    /**
     * Checks if the given operator is valid for PostgreSQL.
     *
     * @param string $operator Operator to check.
     * @return bool True if the operator is valid, false otherwise.
     */
    public function isPossibleOperator(string $operator): bool
    {
        return in_array(strtoupper($operator), array(
            'LIKE', //
            'SIMILAR TO', //
            '~*', // Case-insensitive regex match
            '!~', // Case-sensitive regex non-match
            '!~*', // Case-insensitive regex non-match
            '||', // String concatenation
            '+', // Addition
            '-', // Subtraction
            '*', // Multiplication
            '/', // Division
            '%', // Modulo
            '^', // Exponentiation
            '=', // Equal
            '>=', // Greater than or equal
            '>', // Greater than
            '<=', // Less than or equal
            '<', // Less than
            '!=', // Not equal
            '<>', // Not equal
            'BETWEEN',
            'NOT BETWEEN',
            'IN',
            'NOT IN',
            'IS',
            'IS NOT',
            'ANY',
            'ALL',
            'AND',
            'OR',
            'NOT',
        ));
    }

    /**
     * Checks if the given specifier is a valid order specifier for PostgreSQL.
     *
     * @param string $specifier Order specifier to check.
     * @return bool True if the specifier is valid, false otherwise.
     */
    public function isPossibleOrderSpecifier(string $specifier): bool
    {
        return in_array(strtoupper($specifier), array('ASC', 'DESC', 'USING'));
    }
}
