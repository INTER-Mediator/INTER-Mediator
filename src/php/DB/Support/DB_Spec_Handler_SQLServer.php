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
 *
 */
class DB_Spec_Handler_SQLServer extends DB_Spec_Handler_PDO
{
    /**
     * @return string
     */
    public static function defaultKey(): string
    {
        return "id";
    }

    /**
     * @return string
     */
    public function getDefaultKey(): string
    {
        return "id";
    }

    /**
     * @return bool
     */
    public function isSupportAggregation(): bool
    {
        return true;
    }

    /**
     * @param string $fname
     * @param array $fieldnames
     * @return bool
     */
    public function isContainingFieldName(string $fname, array $fieldnames): bool
    {
        return in_array($fname, $fieldnames);
    }

    /**
     * @return bool
     */
    public function isNullAcceptable(): bool
    {
        return true;
    }

    /**
     * @param string $operator
     * @return bool
     */
    public function isPossibleOperator(string $operator): bool
    {
        return in_array(strtoupper($operator), array(
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
        ));
    }

    /**
     * @param string $specifier
     * @return bool
     */
    public function isPossibleOrderSpecifier(string $specifier): bool
    {
        return in_array(strtoupper($specifier), array('ASC', 'DESC'));
    }

}