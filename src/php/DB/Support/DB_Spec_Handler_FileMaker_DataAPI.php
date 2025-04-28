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
 * Handler for FileMaker Data API-specific specification behavior.
 * Implements the DB_Spec_Behavior interface for FileMaker Data API backend.
 */
class DB_Spec_Handler_FileMaker_DataAPI implements DB_Spec_Behavior
{
    /**
     * Returns the default key name for FileMaker Data API (static method).
     *
     * @return string Default key name.
     */
    public static function defaultKey(): string
    {
        return "recordId";
    }

    /**
     * Returns the default key name for FileMaker Data API (instance method).
     *
     * @return string Default key name.
     */
    public function getDefaultKey(): string
    {
        return "recordId";
    }

    /**
     * Checks if aggregation is supported (always false for FileMaker Data API).
     *
     * @return bool False (aggregation not supported).
     */
    public function isSupportAggregation(): bool
    {
        return false;
    }

    /**
     * Checks if the given field name is in the provided list of field names, with FileMaker-specific rules.
     *
     * @param string $fname Field name to check.
     * @param array $fieldnames Array of available field names.
     * @return bool True if $fname is in $fieldnames or matches FileMaker conventions, false otherwise.
     */
    public function isContainingFieldName(string $fname, array $fieldnames): bool
    {
        if (in_array($fname, $fieldnames)) {
            return true;
        }

        if (strpos($fname, "::") !== false) {
            $lastPeriodPosition = strrpos($fname, ".");
            if ($lastPeriodPosition !== false) {
                if (in_array(substr($fname, 0, $lastPeriodPosition), $fieldnames)) {
                    return true;
                }
            }
        }
        if ($fname === "-delete.related") {
            return true;
        }
        return false;
    }

    /**
     * Checks if NULL values are acceptable (always false for FileMaker Data API).
     *
     * @return bool False (NULL not acceptable).
     */
    public function isNullAcceptable(): bool
    {
        return false;
    }

    /**
     * Checks if the given operator does not require a value (always false for FileMaker Data API).
     *
     * @param string $operator Operator to check.
     * @return bool False (all operators require a value).
     */
    public function isOperatorWithoutValue(string $operator): bool
    {
        return false;
    }

    /**
     * Checks if the given operator is valid for FileMaker Data API.
     *
     * @param string $operator Operator to check.
     * @return bool True if the operator is valid, false otherwise.
     */
    public function isPossibleOperator(string $operator): bool
    {
        return !(!in_array(strtoupper($operator), array(
            'EQ', 'CN', 'BW', 'EW', 'GT', 'GTE', 'LT', 'LTE', 'NEQ', 'AND', 'OR', 'ASIS',
        )));
    }

    /**
     * Checks if the given specifier is a valid order specifier for FileMaker Data API.
     *
     * @param string $specifier Order specifier to check.
     * @return bool True if the specifier is valid, false otherwise.
     */
    public function isPossibleOrderSpecifier(string $specifier): bool
    {
        return in_array(strtoupper($specifier), array('ASCEND', 'DESCEND', 'ASC', 'DESC'));
    }
}
