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
 * Interface defining behavior specifications for database handlers.
 * Provides methods for key handling, field checks, nullability, aggregation support,
 * operator validation, and order specifier validation.
 */
interface DB_Spec_Behavior
{
    /**
     * Returns the default key name (static method, PHP 5.3+).
     *
     * @return string Default key name.
     */
    public static function defaultKey(): string;

    /**
     * Returns the default key name (instance method, PHP 5.2 compatibility).
     *
     * @return string Default key name.
     */
    public function getDefaultKey(): string;

    /**
     * Checks if the given field name is in the provided list of field names.
     *
     * @param string $fname Field name to check.
     * @param array $fieldnames Array of available field names.
     * @return bool True if $fname is in $fieldnames, false otherwise.
     */
    public function isContainingFieldName(string $fname, array $fieldnames): bool;

    /**
     * Checks if NULL values are acceptable for this specification.
     *
     * @return bool True if NULL is acceptable, false otherwise.
     */
    public function isNullAcceptable(): bool;

    /**
     * Checks if this specification supports aggregation functions.
     *
     * @return bool True if aggregation is supported, false otherwise.
     */
    public function isSupportAggregation(): bool;

    /**
     * Checks if the given operator does not require a value (e.g., IS NULL).
     *
     * @param string $operator Operator to check.
     * @return bool True if the operator does not require a value, false otherwise.
     */
    public function isOperatorWithoutValue(string $operator): bool;

    /**
     * Checks if the given operator is a valid/possible operator for this specification.
     *
     * @param string $operator Operator to check.
     * @return bool True if the operator is valid, false otherwise.
     */
    public function isPossibleOperator(string $operator): bool;

    /**
     * Checks if the given specifier is a valid order specifier (e.g., ASC, DESC).
     *
     * @param string $specifier Order specifier to check.
     * @return bool True if the specifier is valid, false otherwise.
     */
    public function isPossibleOrderSpecifier(string $specifier): bool;
}
