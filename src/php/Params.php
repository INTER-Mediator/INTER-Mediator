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

use INTERMediator\DB\Logger;

/**
 * Params is a utility class for handling global configuration parameters for INTER-Mediator.
 * Provides static methods to read, retrieve, and set parameters from params.php.
 */
class Params
{
    /**
     * Stores all global variables loaded from params.php.
     * @var array<array-key, mixed>|null
     */
    private static ?array $vars = null;

    /** Reads and loads the params.php file and stores its variables.
     * This is called automatically when accessing parameters.
     * @return void
     */
    private static function readParamsPHPFile(): void
    {
        if (!is_null(self::$vars)) {
            return;
        }
        $paramsFile = self::findParamsPHPFile();
        $vars = is_null($paramsFile) ? [] : self::includeParamsPHPFile($paramsFile);
        // The recovering of misspelling a variable
        if (isset($vars['follwingTimezones']) && !isset($vars['followingTimezones'])) {
            $vars['followingTimezones'] = $vars['follwingTimezones'];
        }
        self::$vars = $vars;
        if (is_null($paramsFile)) {
            Logger::getInstance()->setWarningMessage("The params.php file was not found. Default values are used for all parameters.");
        }
    }

    /** Returns the path of the params.php file, or null if it isn't found.
     * @return string|null
     */
    private static function findParamsPHPFile(): ?string
    {
        $imRootDir = IMUtil::pathToINTERMediator() . DIRECTORY_SEPARATOR;
        $candidates = [];
        if (basename($imRootDir) == 'inter-mediator'
            && basename(dirname($imRootDir, 2)) == 'vendor') { // This means IM is installed by Composer.
            $appRootDir = dirname($imRootDir, 3);
            $candidates[] = $appRootDir . DIRECTORY_SEPARATOR . 'params.php';
            $candidates[] = $appRootDir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'params.php';
        } else {
            $candidates[] = dirname($imRootDir) . DIRECTORY_SEPARATOR . 'params.php';
            $candidates[] = $imRootDir . 'params.php';
        }
        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }
        return null;
    }

    /** Includes the params.php file in an isolated scope and returns only the variables it defines.
     * @param string $paramsFile
     * @return array<array-key, mixed>
     */
    private static function includeParamsPHPFile(string $paramsFile): array
    {
        return (static function (string $__imParamsFile): array {
            include $__imParamsFile;
            unset($__imParamsFile);
            return get_defined_vars();
        })($paramsFile);
    }

    /** Clears the loaded parameters so that params.php is read again on the next access.
     * @return void
     */
    public static function reset(): void
    {
        self::$vars = null;
    }

    /**
     * Retrieves the value of a parameter or an array of parameters.
    If the parameter does not exist, returns the default value.
     * @param string|array<array-key, mixed> $vName Name or array of names in the parameter(s).
     * @param mixed $defValue Default value(s) to return if parameter is not set.
     * @return mixed Parameter value(s) or default value(s).
     */
    public static function getParameterValue(string|array $vName, mixed $defValue): mixed
    {
        /*
         * The ?? operator returns the default value only if the parameter is not defined or null.
         * The parameter with the false value returns false.
         */
        self::readParamsPHPFile();
        if (!is_array($vName) && !is_array($defValue)) {
            return self::$vars[$vName] ?? $defValue;
        } else if (is_array($vName)) {
            $defValues = is_array($defValue) ? array_values($defValue) : [$defValue];
            $lastIndex = count($defValues) - 1;
            $arValue = [];
            $count = 0;
            foreach ($vName as $var) {
                $arValue[] = self::$vars[$var] ?? ($lastIndex < 0 ? null : $defValues[min($count, $lastIndex)]);
                $count += 1;
            }
            return $arValue;
        } else {
            return self::$vars[$vName] ?? $defValue[0] ?? $defValue;
        }
    }

    /** Returns all loaded parameter variables as an array.
     * Note: The returned array contains secret values like database passwords. Don't send it to clients.
     * @return array<array-key, mixed>|null Array of all variables, or null if not loaded.
     */
    public static function getVars(): ?array
    {
        self::readParamsPHPFile();
        return self::$vars;
    }

    /** Sets a parameter variable to the specified value.
     * @param string $varName Name of the variable to set.
     * @param mixed $value Value to set.
     * @return void
     */
    public static function setVar(string $varName, mixed $value): void {
        self::readParamsPHPFile();
        self::$vars[$varName] = $value;
    }
}
