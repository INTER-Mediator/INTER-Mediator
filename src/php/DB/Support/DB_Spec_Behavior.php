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

interface DB_Spec_Behavior
{
    public static function defaultKey():string ;   // For PHP 5.3 or above

    public function getDefaultKey():string;   // For PHP 5.2

    public function isContainingFieldName(string $fname, array $fieldnames):bool;

    public function isNullAcceptable():bool;

    public function isSupportAggregation():bool;

    public function isOperatorWithoutValue(string $operator): bool;

    public function isPossibleOperator(string $operator): bool;

    public function isPossibleOrderSpecifier(string $specifier): bool;
}
