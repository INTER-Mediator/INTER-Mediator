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
 * Params is a utility class for handling global configuration parameters for INTER-Mediator.
 * Provides static methods to read, retrieve, and set parameters from params.php.
 */
class Params
{
    /**
     * Stores all global variables loaded from params.php.
     *
     * @var array|null
     */
    private static ?array $vars = null;

    /**
     * Reads and loads the params.php file and stores its variables.
     * This is called automatically when accessing parameters.
     *
     * @return void
     */
    private static function readParamsPHPFile(): void
    {
        if (!self::$vars) {
            $imRootDir = IMUtil::pathToINTERMediator() . DIRECTORY_SEPARATOR;
            if (basename($imRootDir) == 'inter-mediator'
//                && basename(dirname($imRootDir)) == 'inter-mediator'
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
            global $follwingTimezones;
            if (isset($follwingTimezones)) {
                $followingTimezones = $follwingTimezones;
            }
            self::$vars = get_defined_vars();
        }
    }

    /**
     * Retrieves the value of a parameter or an array of parameters.
     * If the parameter does not exist, returns the default value.
     *
     * @param string|array $vName Name or array of names of the parameter(s).
     * @param mixed $defValue Default value(s) to return if parameter is not set.
     * @return mixed Parameter value(s) or default value(s).
     */
    public static function getParameterValue(string|array $vName, mixed $defValue): mixed
    {
        /*
         * The IDE try to let us modify the code "isset(self::$vars[$vName]) ? self::$vars[$vName] : $defValue" to
         * "self::$vars[$vName] ?? $defValue", but you don't modify like that. The variable in the params.php file
         * should be the boolean value false. In that case (i.e., the self::$vars[$vName] is false) the former code
         * returns self::$vars[$vName], but the later one does $defValue. We expect that the false value return false.
         * So please don't modify with the ?? Operator.
         */
        self::readParamsPHPFile();
        if (!is_array($vName) && !is_array($defValue)) {
            return self::$vars[$vName] ?? $defValue;
        } else if (is_array($vName) && is_array($defValue) && count($vName) == count($defValue)) {
            $arValue = [];
            $count = 0;
            foreach ($vName as $var) {
                $arValue[] = self::$vars[$var] ?? $defValue[$count];
                $count += 1;
            }
            return $arValue;
        } else if (is_array($vName)) {
            $arValue = [];
            $count = 0;
            foreach ($vName as $var) {
                $arValue[] = self::$vars[$var] ?? (is_array($defValue) ? $defValue[min($count, count($defValue) - 1)] : $defValue);
                $count += 1;
            }
            return $arValue;
        } else if (is_array($defValue)) {
            return self::$vars[$vName] ?? $defValue[0] ?? $defValue;
        } else {
            return self::$vars[$vName] ?? $defValue;
        }
    }

    /**
     * Returns all loaded parameter variables as an array.
     *
     * @return array|null Array of all variables, or null if not loaded.
     */
    public static function getVars(): ?array
    {
        self::readParamsPHPFile();
        return self::$vars;
    }

    /**
     * Sets a parameter variable to the specified value.
     *
     * @param string $varName Name of the variable to set.
     * @param mixed $value Value to set.
     * @return void
     */
    public static function setVar(string $varName, mixed $value): void {
        self::readParamsPHPFile();
        self::$vars[$varName] = $value;
    }
}
