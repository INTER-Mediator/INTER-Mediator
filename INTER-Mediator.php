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

if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

spl_autoload_register('loadClass');

require_once('DB_Interfaces.php');
require_once('IMUtil.php'); //
//require_once('DB_Logger.php');
//require_once('DB_Settings.php');
//require_once('DB_UseSharedObjects.php');
//require_once('DB_Proxy.php');

IMUtil::includeLibClasses(IMUtil::phpSecLibRequiredClasses());

$currentDirParam = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'params.php';
$parentDirParam = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'params.php';
if (file_exists($parentDirParam)) {
    include($parentDirParam);
} else if (file_exists($currentDirParam)) {
    include($currentDirParam);
}
if (isset($defaultTimezone)) {
    date_default_timezone_set($defaultTimezone);
} else if (ini_get('date.timezone') == null) {
    date_default_timezone_set('UTC');
}
IMLocale::setLocale(LC_ALL);

define("IM_TODAY", strftime('%Y-%m-%d'));
$g_dbInstance = null;

function IM_Entry($datasource, $options, $dbspecification, $debug = false)
{
    global $g_dbInstance, $g_serverSideCall;

    // check required PHP extensions
    $requiredFunctions = array(
        'mbstring' => 'mb_internal_encoding',
    );
    if (isset($options) && is_array($options)) {
        foreach ($options as $key => $option) {
            if ($key == 'authentication'
                && isset($option['user'])
                && is_array($option['user'])
                && array_search('database_native', $option['user']) !== false
            ) {
                // Native Authentication requires BC Math functions
                $requiredFunctions = array_merge($requiredFunctions, array('bcmath' => 'bcadd'));
                break;
            }
        }
    }
    foreach ($requiredFunctions as $key => $value) {
        if (!function_exists($value)) {
            $generator = new GenerateJSCode();
            $generator->generateInitialJSCode($datasource, $options, $dbspecification, $debug);
            $generator->generateErrorMessageJS("PHP extension \"" . $key . "\" is required for running INTER-Mediator.");
            return;
        }
    }

    if (isset($_GET['theme'])) {
        $themeManager = new Theme();
        $themeManager->processing();
    } else if (isset($g_serverSideCall) && $g_serverSideCall) {
        $dbInstance = new DB_Proxy();
        $dbInstance->initialize($datasource, $options, $dbspecification, $debug);
        $dbInstance->processingRequest("NON");
        $g_dbInstance = $dbInstance;
        error_log('Deprecated global variables $g_dbInstance, $g_serverSideCall in INTER-Mediator.php will be removed in Ver.6.0');
    } else if (!isset($_POST['access']) && isset($_GET['uploadprocess'])) {
        $fileUploader = new FileUploader();
        $fileUploader->processInfo();
    } else if (!isset($_POST['access']) && isset($_GET['media'])) {
        $dbProxyInstance = new DB_Proxy();
        $dbProxyInstance->initialize($datasource, $options, $dbspecification, $debug);
        $mediaHandler = new MediaAccess();
        if (isset($_GET['attach'])) {
            $mediaHandler->asAttachment();
        }
        $mediaHandler->processing($dbProxyInstance, $options, $_GET['media']);
    } else if ((isset($_POST['access']) && $_POST['access'] == 'uploadfile')
        || (isset($_GET['access']) && $_GET['access'] == 'uploadfile')
    ) {
        $fileUploader = new FileUploader();
        if (IMUtil::guessFileUploadError()) {
            $fileUploader->processingAsError($datasource, $options, $dbspecification, $debug);
        } else {
            $fileUploader->processing($datasource, $options, $dbspecification, $debug);
        }
    } else if (!isset($_POST['access']) && !isset($_GET['media'])) {
        if ($debug) {
            $dc = new DefinitionChecker();
            $defErrorMessage = $dc->checkDefinitions($datasource, $options, $dbspecification);
            if (strlen($defErrorMessage) > 0) {
                $generator = new GenerateJSCode();
                $generator->generateInitialJSCode($datasource, $options, $dbspecification, $debug);
                $generator->generateErrorMessageJS($defErrorMessage);
                return;
            }
        }

        $generator = new GenerateJSCode();
        $generator->generateInitialJSCode($datasource, $options, $dbspecification, $debug);
    } else {
        $dbInstance = new DB_Proxy();
        if (!$dbInstance->initialize($datasource, $options, $dbspecification, $debug)) {
            $dbInstance->finishCommunication(true);
        } else {
            $util = new IMUtil();
            if ($util->protectCSRF() === TRUE) {
                $dbInstance->processingRequest();
                $dbInstance->finishCommunication(false);
            } else {
                $dbInstance->addOutputData('debugMessages', 'Invalid Request Error.');
                $dbInstance->addOutputData('errorMessages', array('Invalid Request Error.'));
            }
        }
        $dbInstance->exportOutputDataAsJSON();
    }
}


/**
 * Dynamic class loader
 * @param $className
 */
function loadClass($className)
{
    if (strpos($className, '\\') === false &&
        strpos($className, 'PHPUnit_') === false &&
        $className !== 'PHP_Invoker' &&
        strpos($className, 'PHPExcel_') === false &&
        $className !== 'Composer\Autoload\ClassLoader'
    ) {
        if ($className === 'NumberFormatter' && !class_exists($className)) {
            $className = 'IMNumberFormatter';
        }
        $imClassPath = array("", "DB_Support" . DIRECTORY_SEPARATOR, "Data_Converter" . DIRECTORY_SEPARATOR);
        $incSeparator = IMUtil::isPHPExecutingWindows() ? ";" : ":";
        $incPath = explode($incSeparator, get_include_path());
        $isFileExists = false;
        foreach ($incPath as $path) {
            if (strlen($path)>0) {
                foreach ($imClassPath as $imPath) {
                    $classPath = $path . DIRECTORY_SEPARATOR . $imPath;
                    if (IMUtil::isPHPExecutingWindows() ?
                        (substr($classPath, 1, 1) !== ':') :
                        (substr($classPath, 0, 1) !== '/'))   {
                        $classPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . $classPath;
                    }
                    if (file_exists("{$classPath}{$className}.php")) {
                        $isFileExists = true;
                        break 2;
                    }
                }
            }
        }
        if ($isFileExists) {
            $result = require_once("{$classPath}{$className}.php");
        } else {
            $result = require_once("{$className}.php");
        }
        if (!$result) {
            $errorGenerator = new GenerateJSCode();
            if (strpos($className, "MessageStrings_") !== 0) {
                $errorGenerator->generateErrorMessageJS("The class '{$className}' is not defined.");
            }
        }
    }
}

/**
 * Convert strings to JavaScript friendly strings.
 * Contributed by Atsushi Matsuo at Jan 17, 2010
 * @return string strings for JavaScript
 */
function valueForJSInsert($str)
{
    return str_replace("'", "\\'",
        str_replace('"', '\\"',
            str_replace("/", "\\/",
                str_replace(">", "\\x3e",
                    str_replace("<", "\\x3c",
                        str_replace("\n", "\\n",
                            str_replace("\r", "\\r",
                                str_replace("\xe2\x80\xa8", "\\n",      // U+2028
                                    str_replace("\xe2\x80\xa9", "\\n",  // U+2029
                                        str_replace("\\", "\\\\", $str))))))))));
}

/**
 * Create JavaScript source from array
 * @param array ar parameter array
 * @param string prefix strings for the prefix for key
 * @return string JavaScript source
 */
function arrayToJS($ar, $prefix = "")
{
    if (is_array($ar)) {
        $items = array();
        foreach ($ar as $key => $value) {
            $items[] = arrayToJS($value, $key);
        }
        $currentKey = (string)$prefix;
        if ($currentKey == '')
            $returnStr = "{" . implode(',', $items) . '}';
        else
            $returnStr = "'{$currentKey}':{" . implode(',', $items) . '}';
    } else {
        $currentKey = (string)$prefix;
        if ($currentKey == '') {
            $returnStr = "'" . valueForJSInsert($ar) . "'";
        } else {
            $returnStr = "'{$prefix}':'" . valueForJSInsert($ar) . "'";
        }
    }
    return $returnStr;
}

/**
 * Create JavaScript source from array
 * @param array ar parameter array
 * @param string prefix strings for the prefix for key
 * @param array exarray array containing excluding keys
 * @return string JavaScript source
 */
function arrayToJSExcluding($ar, $prefix, $exarray)
{
    $returnStr = '';

    if (is_array($ar)) {
        $items = array();
        foreach ($ar as $key => $value) {
            $items[] = arrayToJSExcluding($value, $key, $exarray);
        }
        $currentKey = (string)$prefix;
        foreach ($items as $item) {
            if (!in_array($currentKey, $exarray) && $item != '') {
                if ($returnStr == '') {
                    $returnStr .= $item;
                } else {
                    $returnStr .= ',' . $item;
                }
            }
        }
        if ($currentKey == '') {
            $returnStr = '{' . $returnStr . '}';
        } else {
            $returnStr = "'{$currentKey}':{" . $returnStr . '}';
        }
    } else {
        $currentKey = (string)$prefix;
        if ($currentKey == '') {
            $returnStr = "'" . valueForJSInsert($ar) . "'";
        } else if (!in_array($currentKey, $exarray)) {
            $returnStr = "'{$prefix}':'" . valueForJSInsert($ar) . "'";
        }
    }

    return $returnStr;
}

/**
 * Create parameter strng from array
 * @param array ar parameter array
 * @param string prefix strings for the prefix for key
 * @return string parameter string
 */
function arrayToQuery($ar, $prefix)
{
    if (is_array($ar)) {
        $items = array();
        foreach ($ar as $key => $value) {
            $items[] = arrayToQuery($value, "{$prefix}_{$key}");
        }
        $returnStr = implode('', $items);
    } else {
        $returnStr = "&{$prefix}=" . urlencode($ar);
    }
    return $returnStr;
}

/**
 * Get the relative path from the caller to the directory of 'INTER-Mediator.php'.
 * @return Relative path as a part of URL.
 */
function getRelativePath()
{
    $caller = explode(DIRECTORY_SEPARATOR, dirname($_SERVER['SCRIPT_FILENAME']));
    $imDirectory = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
    $shorterLength = min(count($caller), count($imDirectory));
    for ($i = 0; $i < $shorterLength; $i++) {
        if ($caller[$i] != $imDirectory[$i]) {
            break;
        }
    }
    $relPath = str_repeat('../', count($caller) - $i)
        . implode('/', array_slice($imDirectory, $i));
    return $relPath;
}

function hex2bin_for53($str)
{
    return pack("H*", $str);
}

function randomString($digit)
{
    $resultStr = '';
    for ($i = 0; $i < $digit; $i++) {
        $resultStr .= chr(rand(20, 126));
    }
    return $resultStr;
}
