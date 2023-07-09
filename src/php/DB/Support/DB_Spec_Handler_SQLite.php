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

class DB_Spec_Handler_SQLite extends DB_Spec_Handler_PDO
{
    public static function defaultKey()
    {
        return "id";
    }

    public function getDefaultKey()
    {
        return "id";
    }

    public function isSupportAggregation()
    {
        return true;
    }

    public function isContainingFieldName($fname, $fieldnames)
    {
        return in_array($fname, $fieldnames);
    }

    public function isNullAcceptable()
    {
        return true;
    }

    public function isPossibleOperator($operator)
    {
        return in_array(strtoupper($operator), array(
            '||',
            '*', '/', '%',
            '+', '-',
            '<<', '>>', '&', '|',
            '<', '<=', '>', '>=',
            '=', '==', '!=', '<>', 'IS', 'IS NOT', 'IN', 'LIKE', 'GLOB', 'MATCH', 'REGEXP',
            'AND',
            'IS NULL', //NULL value test
            'OR',
            'IN',
            '-', '+', '~', 'NOT',
            'IS NOT NULL', //	NOT NULL value test
        ));
    }

    public function isPossibleOrderSpecifier($specifier)
    {
        return in_array(strtoupper($specifier), array('ASC', 'DESC'));
    }
}
