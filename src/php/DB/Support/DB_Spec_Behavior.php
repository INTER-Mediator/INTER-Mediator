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
    public static function defaultKey();   // For PHP 5.3 or above

    public function getDefaultKey();   // For PHP 5.2

    public function isContainingFieldName($fname, $fieldnames);

    public function isNullAcceptable();

    public function isSupportAggregation();

    public function isOperatorWithoutValue($operator);

    public function isPossibleOperator($operator);

    public function isPossibleOrderSpecifier($specifier);
}
