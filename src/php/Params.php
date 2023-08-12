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

namespace INTERMediator;

/**
 *
 */
class Params
{
    /**
     * @var array|null
     */
    private static ?array $vars = null;

    /**
     * @return void
     */
    public static function readParamsPHPFile(): void
    {
        if (!self::$vars) {
            $imRootDir = IMUtil::pathToINTERMediator() . DIRECTORY_SEPARATOR;
            if (basename($imRootDir) == 'inter-mediator'
                && basename(dirname($imRootDir)) == 'inter-mediator'
                && basename(dirname($imRootDir, 2)) == 'vendor') { // This means IM is installed by Composer.
                $appRootDir = dirname($imRootDir, 3);
                if (file_exists($appRootDir . DIRECTORY_SEPARATOR . 'params.php')) {
                    include($appRootDir . DIRECTORY_SEPARATOR . 'params.php');
                } else if (file_exists($appRootDir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'params.php')) {
                    include($appRootDir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'params.php');
                }
            } else if (file_exists(dirname($imRootDir) . DIRECTORY_SEPARATOR . 'params.php')) {
                include(dirname($imRootDir) . DIRECTORY_SEPARATOR . 'params.php');
            } else if (file_exists($imRootDir . 'params.php')) {
                include($imRootDir . 'params.php');
            }
            // The recovering of misspelling a global variable
            if (isset($follwingTimezones)) {
                $followingTimezones = $follwingTimezones;
            }
            self::$vars = get_defined_vars();
        }
    }

    /**
     * @param mixed $vName
     * @param mixed $defValue
     * @return mixed
     */
    public static function getParameterValue($vName, $defValue)
    {
        /*
         * The IDE try to let us modify the code "isset(self::$vars[$vName]) ? self::$vars[$vName] : $defValue" to
         * "self::$vars[$vName] ?? $defValue", but you don't modify like that. The variable in the params.php file
         * should be the boolean value false. In that case (i.e., the self::$vars[$vName] is false) the former code
         * returns self::$vars[$vName], but the later one does $defValue. We expect that the false value return false.
         * So please don't modify with the ?? operator.
         */
        self::readParamsPHPFile();
        if (!is_array($vName) && !is_array($defValue)) {
            return isset(self::$vars[$vName]) ? self::$vars[$vName] : $defValue;
        } else if (is_array($vName) && is_array($defValue) && count($vName) == count($defValue)) {
            $arValue = [];
            $count = 0;
            foreach ($vName as $var) {
                $arValue[] = isset(self::$vars[$var]) ? self::$vars[$var] : $defValue[$count];
                $count += 1;
            }
            return $arValue;
        } else if (is_array($vName)) {
            $arValue = [];
            $count = 0;
            foreach ($vName as $var) {
                $arValue[] = isset(self::$vars[$var]) ? self::$vars[$var]
                    : (is_array($defValue) ? $defValue[min($count, count($defValue) - 1)] : $defValue);
                $count += 1;
            }
            return $arValue;
        } else if (is_array($defValue)) {
            return isset(self::$vars[$vName]) ? self::$vars[$vName] : $defValue[0];
        } else {
            return isset(self::$vars[$vName]) ? self::$vars[$vName] : $defValue;
        }
    }

    /**
     * @return array|null
     */
    public static function getVars(): ?array
    {
        self::readParamsPHPFile();
        return self::$vars;
    }
}
