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

class DB_Spec_Handler_PostgreSQL extends DB_Spec_Handler_PDO
{
    public static function defaultKey(): string
    {
        return "id";
    }

    public function getDefaultKey(): string
    {
        return "id";
    }

    public function isSupportAggregation(): bool
    {
        return true;
    }

    public function isContainingFieldName(string $fname,array $fieldnames): bool
    {
        return in_array($fname, $fieldnames);
    }

    public function isNullAcceptable(): bool
    {
        return true;
    }

    public function isOperatorWithoutValue(string $operator): bool
    {
        return in_array(strtoupper($operator), array(
            'IS NOT NULL', //	NOT NULL value test
            'IS NULL', //NULL value test
            'NOTNULL', //	NOT NULL value test
            'ISNULL', //NULL value test
            'IS TRUE',
            'IS NOT TRUE',
            'IS FALSE',
            'IS NOT FALSE',
            'IS UNKNOWN',
            'IS NOT UNKNOWN',
        ));
    }

    public function isPossibleOperator(string $operator): bool
    {
        return in_array(strtoupper($operator), array(
            'LIKE', //
            'SIMILAR TO', //
            '~*', //	正規表現に一致、大文字小文字の区別なし	'thomas' ~* '.*Thomas.*'
            '!~', //	正規表現に一致しない、大文字小文字の区別あり	'thomas' !~ '.*Thomas.*'
            '!~*', //	正規表現に一致しない、大文字小文字の区別なし	'thomas' !~* '.*vadim.*'
            '||', //  文字列の結合
            '+', //	和	2 + 3	5
            '-', //	差	2 - 3	-1
            '*', //	積	2 * 3	6
            '/', //	商（整数の割り算では余りを切り捨て）	4 / 2	2
            '%', //	剰余（余り）	5 % 4	1
            '^', //	累乗	2.0 ^ 3.0	8
            '|/', //	平方根	|/ 25.0	5
            '||/', //	立方根	||/ 27.0	3
            '!', //	階乗	5 !	120
            '!!', //	階乗（前置演算子）	!! 5	120
            '@', //	絶対値	@ -5.0	5
            '&', //	バイナリのAND	91 & 15	11
            '|', //	バイナリのOR	32 | 3	35
            '#', //	バイナリのXOR	17 # 5	20
            '~', //	バイナリのNOT	~1	-2
            '<<', //	バイナリの左シフト	1 << 4	16
            '>>', //	バイナリの右シフト
            'AND', //
            'OR', //
            'NOT', //
            '<', //	小なり
            '>', //	大なり
            '<=', //	等しいかそれ以下
            '>=', //	等しいかそれ以上
            '=', //	等しい
            '<>', // または !=	等しくない
            '||', //	結合	B'10001' || B'011'	10001011
            '&', //	ビットのAND	B'10001' & B'01101'	00001
            '|', //	ビットのOR	B'10001' | B'01101'	11101
            '#', //	ビットのXOR	B'10001' # B'01101'	11100
            '~', //	ビットのNOT	~ B'10001'	01110
            '<<', //ビットの左シフト	B'10001' << 3	01000
            '>>', //ビットの右シフト	B'10001' >> 2	00100
            'IN',
            //[上記に含まれないもの]
            //幾何データ型、ネットワークアドレス型、JSON演算子、配列演算子、範囲演算子
            'IS NOT NULL', //	NOT NULL value test
            'IS NULL', //NULL value test
            'NOTNULL', //	NOT NULL value test
            'ISNULL', //NULL value test
            'IS TRUE',
            'IS NOT TRUE',
            'IS FALSE',
            'IS NOT FALSE',
            'IS UNKNOWN',
            'IS NOT UNKNOWN',
        ));
    }

    public function isPossibleOrderSpecifier(string $specifier): bool
    {
        return in_array(strtoupper($specifier), array('ASC', 'DESC'));
    }
}
