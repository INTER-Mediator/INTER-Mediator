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
 * Handler for SQL Server-specific specification behavior.
 * Implements the DB_Spec_Behavior interface for SQL Server backend.
 */
class DB_Spec_Handler_SQLServer extends DB_Spec_Handler_PDO
{
    /** Returns the default key name for SQL Server (static method).
     * @return string Default key name.
     */
    public static function defaultKey(): string
    {
        return "id";
    }

    /** Returns the default key name for SQL Server (instance method).
     * @return string Default key name.
     */
    public function getDefaultKey(): string
    {
        return "id";
    }

    /** Checks if aggregation is supported (always true for SQL Server).
     * @return bool True (aggregation supported).
     */
    public function isSupportAggregation(): bool
    {
        return true;
    }

    /** Checks if the given field name is in the provided list of field names.
     * @param string $fname Field name to check.
     * @param array $fieldnames Array of available field names.
     * @return bool True if $fname is in $fieldnames, false otherwise.
     */
    public function isContainingFieldName(string $fname, array $fieldnames): bool
    {
        return in_array($fname, $fieldnames);
    }

    /** Checks if NULL values are acceptable (always true for SQL Server).
     * @return bool True (NULL acceptable).
     */
    public function isNullAcceptable(): bool
    {
        return true;
    }

    /** Checks if the given operator is valid for SQL Server.
     * @param string $operator Operator to check.
     * @return bool True if the operator is valid, false otherwise.
     */
    public function isPossibleOperator(string $operator): bool
    {
        return in_array(strtoupper($operator), array(
            '&', // Bitwise AND
            '&=', // Bitwise AND EQUALS
            '|', // Bitwise OR
            '|=', // Bitwise OR EQUALS
            '^', // Bitwise Exclusive OR
            '^=', // Bitwise Exclusive OR EQUALS
            '~', // Bitwise NOT
            '=', // Equals
            '>', // Greater Than
            '<', // Less Than
            '>=', // Greater Than or Equal To
            '<=', // Less Than or Equal To
            '<>', // Not Equal To
            '!=', // Not Equal To (not ISO standard)
            '!<', // Not Less Than (not ISO standard)
            '!>', // Not Greater Than (not ISO standard)
            'ALL', // TRUE if all of a set of comparisons are TRUE
            'AND', // TRUE if both Boolean expressions are TRUE
            'ANY', // TRUE if any one of a set of comparisons are TRUE
            'BETWEEN', // TRUE if the operand is within a range
            'EXISTS', // TRUE if a subquery contains any rows
            'IN', // TRUE if the operand is equal to one of a list of expressions
            'LIKE', // TRUE if the operand matches a pattern
            'NOT', // Reverses the value of any other Boolean operator
            'OR', // TRUE if either Boolean expression is TRUE
        ));
    }

    /** Checks if the given specifier is a valid order specifier for SQL Server.
     * @param string $specifier Order specifier to check.
     * @return bool True if the specifier is valid, false otherwise.
     */
    public function isPossibleOrderSpecifier(string $specifier): bool
    {
        return in_array(strtoupper($specifier), array('ASC', 'DESC'));
    }
}