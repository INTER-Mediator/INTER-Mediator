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

use \IntlDateFormatter;
use \DateTime;

// Setup autoloader
$imRoot = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;
$autoLoad = $imRoot . 'vendor/autoload.php';
if (file_exists($autoLoad)) { // If vendor is inside of INTER-Mediator
    require($autoLoad);
} else { // If INTER-Mediator is installed with composer.json
    $vendorRoot = dirname(dirname($imRoot)) . DIRECTORY_SEPARATOR;
    $autoLoad = $vendorRoot . 'autoload.php';
    if (file_exists($autoLoad)) {
        require($autoLoad);
    }
}

spl_autoload_register(function ($className) {
    $comps = explode('\\', $className);
    $className = $comps[count($comps) - 1];
    // Load from the file located on the same directory as definition file.
    $path = dirname($_SERVER['SCRIPT_FILENAME']) . "/{$className}.php";
    if (file_exists($path)) {
        require_once $path;
        return true;
    }
    // Load from the file inside files of FX.php.
    $imRoot = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;
    $path = "{$imRoot}/vendor/yodarunamok/fxphp/lib/datasource_classes/{$className}.class.php";
    if (file_exists($path)) {
        require_once $path;
        return true;
    }
    return false;
});

// Define constant
$fmt = new IntlDateFormatter(
    Params::getParameterValue("appLocale", "UTC"),
    IntlDateFormatter::FULL,
    IntlDateFormatter::NONE,
    Params::getParameterValue("defaultTimezone", "UTC"),
    IntlDateFormatter::GREGORIAN,
    'Y-MM-dd'
);
define("IM_TODAY", $fmt->format((new DateTime())->getTimestamp()));

/**
 * INTER-Mediator entry point
 * @param $datasource
 * @param $options
 * @param $dbspecification
 * @param bool $debug
 */
function IM_Entry($datasource, $options, $dbspecification, $debug = false)
{
    // check required PHP extensions
    $requiredFunctions = array(
        'mbstring' => 'mb_internal_encoding',
    );
//    if (isset($options) && is_array($options)) {
//        foreach ($options as $key => $option) {
//            if ($key == 'authentication'
//                && isset($option['user'])
//                && is_array($option['user'])
//                && array_search('database_native', $option['user']) !== false
//            ) {
//                // Native Authentication requires BC Math functions
//                $requiredFunctions = array_merge($requiredFunctions, array('bcmath' => 'bcadd'));
//                break;
//            }
//        }
//    }
    foreach ($requiredFunctions as $key => $value) {
        if (!function_exists($value)) {
            $generator = new GenerateJSCode();
            $generator->generateInitialJSCode($datasource, $options, $dbspecification, $debug);
            $generator->generateErrorMessageJS("PHP extension \"" . $key . "\" is required for running INTER-Mediator.");
            return;
        }
    }

    // Read from params.php
    [$defaultTimezone, $accessLogLevel] = Params::getParameterValue(["defaultTimezone", "accessLogLevel"], ["UTC", false]);
    // Setup Timezone
    if ($defaultTimezone) {
        date_default_timezone_set($defaultTimezone);
    } else if (ini_get('date.timezone') == null) {
        date_default_timezone_set('UTC');
    }
    // Setup Locale
    Locale\IMLocale::setLocale(LC_ALL);

    // Character set for mbstring
    if (function_exists('mb_internal_encoding')) {
        mb_internal_encoding('UTF-8');
    }

    $resultLog = [];
    if (isset($_GET['theme'])) {    // Get theme data
        $themeManager = new Theme();
        $themeManager->processing();
        $resultLog = $themeManager->getResultForLog();
    } else if (!isset($_POST['access']) && isset($_GET['uploadprocess'])) { // Upload progress
        $fileUploader = new FileUploader();
        $fileUploader->processInfo();
        $resultLog = $fileUploader->getResultForLog();
    } else if (!isset($_POST['access']) && isset($_GET['media'])) { // Media accessing
        $dbProxyInstance = new DB\Proxy();
        $dbProxyInstance->initialize($datasource, $options, $dbspecification, $debug);
        $mediaHandler = new MediaAccess();
        if (isset($_GET['attach'])) {
            $mediaHandler->asAttachment();
        }
        $mediaHandler->processing($dbProxyInstance, $options, $_GET['media']);
        $resultLog = $mediaHandler->getResultForLog();
//        file_put_contents('/tmp/1', var_export($dbProxyInstance->logger->getDebugMessages(),true));
//        file_put_contents('/tmp/2', var_export($dbProxyInstance->logger->getErrorMessages(),true));
    } else if ((isset($_POST['access']) && $_POST['access'] == 'uploadfile')
        || (isset($_GET['access']) && $_GET['access'] == 'uploadfile')
    ) {     // File uploading
        $fileUploader = new FileUploader();
        if (IMUtil::guessFileUploadError()) {
            $fileUploader->processingAsError(
                $datasource, $options, $dbspecification, $debug, $_POST["_im_contextname"], false);
        } else {
            $fileUploader->processing($datasource, $options, $dbspecification, $debug);
        }
        $resultLog = $fileUploader->getResultForLog();
    } else if (!isset($_POST['access']) && !isset($_GET['media'])) {    // Download JS module to client
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $db = new DB\Proxy();
            $db->initialize($datasource, $options, $dbspecification, $debug, '');
            $messages = IMUtil::getMessageClassInstance();
            $db->logger->setErrorMessage($messages->getMessageAs(3212));
            $db->processingRequest("noop");
            $db->finishCommunication();
            $db->exportOutputDataAsJSON();
            return;
        }
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
        // Bootstrap of Service Server
        ServiceServerProxy::instance()->checkServiceServer();
        $generator = new GenerateJSCode();
        $generator->generateInitialJSCode($datasource, $options, $dbspecification, $debug);
        foreach (ServiceServerProxy::instance()->getErrors() as $message) {
            $generator->generateErrorMessageJS($message);
        }
        foreach (ServiceServerProxy::instance()->getMessages() as $message) {
            $generator->generateDebugMessageJS($message);
        }
        ServiceServerProxy::instance()->stopServer();
    } else {    // Database accessing
        ServiceServerProxy::instance()->checkServiceServer();
        $dbInstance = new DB\Proxy();
        $isInitialized = $dbInstance->initialize($datasource, $options, $dbspecification, $debug);
        $dbInstance->logger->setErrorMessages(ServiceServerProxy::instance()->getErrors());
        $dbInstance->logger->setDebugMessages(ServiceServerProxy::instance()->getMessages());
        if (!$isInitialized) {
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
        $resultLog = $dbInstance->getResultForLog();
        ServiceServerProxy::instance()->stopServer();
    }
    if ($accessLogLevel) {
        $logging = new DB\OperationLog($options);
        $logging->setEntry($resultLog);
    }
}


