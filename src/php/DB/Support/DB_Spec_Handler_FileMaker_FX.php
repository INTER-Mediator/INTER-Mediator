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

class DB_Spec_Handler_FileMaker_FX implements DB_Spec_Behavior
{
    public static function defaultKey(): string
    {
        return "-recid";
    }

    public function getDefaultKey(): string
    {
        return "-recid";
    }

    public function isOperatorWithoutValue(string $operator): bool
    {
        return false;
    }

    public function isPossibleOperator(string $operator): bool
    {
        return !(!in_array(strtoupper($operator), array(
            'EQ', 'CN', 'BW', 'EW', 'GT', 'GTE', 'LT', 'LTE', 'NEQ', 'AND', 'OR', 'ASIS',
        )));
    }

    public function isPossibleOrderSpecifier(string $specifier): bool
    {
        return !(!in_array(strtoupper($specifier), array('ASCEND', 'DESCEND', 'ASC', 'DESC')));
    }

    public function isContainingFieldName(string $fname,array $fieldnames): bool
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
        if ($fname == "-delete.related") {
            return true;
        }
        return false;
    }

    public function isNullAcceptable(): bool
    {
        return false;
    }

    public function isSupportAggregation(): bool
    {
        return false;
    }

}
