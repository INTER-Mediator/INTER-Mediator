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
 * Handler for SQLite-specific specification behavior.
 * Implements the DB_Spec_Behavior interface for SQLite backend.
 */
class DB_Spec_Handler_SQLite extends DB_Spec_Handler_PDO
{
    /**
     * Returns the default key name for SQLite (static method).
     *
     * @return string Default key name.
     */
    public static function defaultKey(): string
    {
        return "id";
    }

    /**
     * Returns the default key name for SQLite (instance method).
     *
     * @return string Default key name.
     */
    public function getDefaultKey(): string
    {
        return "id";
    }

    /**
     * Checks if aggregation is supported (always true for SQLite).
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
     * Checks if NULL values are acceptable (always true for SQLite).
     *
     * @return bool True (NULL acceptable).
     */
    public function isNullAcceptable(): bool
    {
        return true;
    }

    /**
     * Checks if the given operator is valid for SQLite.
     *
     * @param string $operator Operator to check.
     * @return bool True if the operator is valid, false otherwise.
     */
    public function isPossibleOperator(string $operator): bool
    {
        return in_array(strtoupper($operator), array(
            '||',
            '*', '/', '%',
            '+', '-',
            '<<', '>>', '&', '|',
            '<', '<=', '>', '>=',
            '=', '==', '!=', '<>', 'IS', 'IS NOT', 'IN', 'LIKE', 'GLOB', 'MATCH', 'REGEXP',
            'AND',
            'IS NULL', // NULL value test
            'OR',
            'IN',
            '-', '+', '~', 'NOT',
            'IS NOT NULL', // NOT NULL value test
        ));
    }

    /**
     * Checks if the given specifier is a valid order specifier for SQLite.
     *
     * @param string $specifier Order specifier to check.
     * @return bool True if the specifier is valid, false otherwise.
     */
    public function isPossibleOrderSpecifier(string $specifier): bool
    {
        return in_array(strtoupper($specifier), array('ASC', 'DESC'));
    }
}
