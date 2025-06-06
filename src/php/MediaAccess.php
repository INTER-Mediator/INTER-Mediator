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

use Exception;
use INTERMediator\DB\Proxy;

/**
 * MediaAccess handles secure access and delivery of media files (images, PDFs, etc.)
 * for INTER-Mediator. It performs authentication, authorization, and context-aware
 * record checking before serving the requested file.
 */
class MediaAccess
{
    /**
     * Content disposition for the media file (inline or attachment).
     *
     * @var string
     */
    private string $disposition = "inline";    // default disposition.
    /**
     * Target key field extracted from the request (set by analyzeTarget).
     *
     * @var string|null
     */
    private ?string $targetKeyField;    // set with the analyzeTarget method.
    /**
     * Target key value extracted from the request (set by analyzeTarget).
     *
     * @var string|null
     */
    private ?string $targetKeyValue;  // set with the analyzeTarget method.
    /**
     * Target context name extracted from the request (set by analyzeTarget).
     *
     * @var string|null
     */
    private ?string $targetContextName = null;  // set with the analyzeTarget method.
    /**
     * Authenticated user from cookie (set by checkAuthentication).
     *
     * @var string|null
     */
    private ?string $cookieUser = null;    // set with the checkAuthentication method.
    /**
     * Access log level setting.
     *
     * @var int
     */
    private int $accessLogLevel;
    /**
     * Output message array for logging.
     *
     * @var array
     */
    private array $outputMessage = [];
    /**
     * Whether an exception was thrown during processing.
     *
     * @var bool
     */
    private bool $thrownException = false;

    /**
     * Database proxy instance for DB operations.
     *
     * @var Proxy|null
     */
    private ?Proxy $dbProxyInstance = null;

    /**
     * MediaAccess constructor. Initializes access log level.
     */
    public function __construct()
    {
        $this->accessLogLevel = Params::getParameterValue("accessLogLevel", false);
    }

    /**
     * Gets the result message array for access logging.
     *
     * @return array Output message array for logging.
     */
    public function getResultForLog(): array
    {
        if ($this->accessLogLevel < 1) {
            return [];
        }
        $this->outputMessage["name"] = $this->targetContextName;
        $this->outputMessage["authuser"] = $this->cookieUser;
        return $this->outputMessage;
    }

    /**
     * Sets the content disposition to attachment (download).
     *
     * @return void
     */
    public function asAttachment(): void
    {
        $this->disposition = "attachment";
    }

    /**
     * Handles error logging and sets error messages in the logger.
     *
     * @param string $message Error message to log.
     * @return void
     */
    private function errorHandling(string $message): void
    {
        error_log($message);
        $this->dbProxyInstance->logger->setErrorMessage($message);
    }

    /**
     * Main processing method for serving media files.
     * Handles authentication, authorization, and file delivery.
     *
     * @param Proxy $dbProxyInstance Database proxy instance.
     * @param array|null $options Options for media access.
     * @param string $file Requested file path or URL.
     * @return void
     * @throws Exception If processing fails.
     */
    public function processing(Proxy $dbProxyInstance, ?array $options, string $file): void
    {
        $this->dbProxyInstance = $dbProxyInstance;
        $this->thrownException = false;
        $contextRecord = null;
        try {
            // If the $file ('media'parameter) isn't specified, it doesn't respond an error.
            if (strlen($file) === 0) {
                $erMessage = "[INTER-Mediator] The value of the 'media' key in url isn't specified.";
                echo $erMessage;
                $this->errorHandling($erMessage);
                $this->exitAsError(200);
            }
            // If the media parameter is a URL, the variable isURL will be set to true.
            $isURL = $this->isPossibleSchema($file);
            $mediaRootDir = $options['media-root-dir'] ?? Params::getParameterValue('mediaRootDir', null) ?? null;
            if (!$isURL && !$mediaRootDir) {
                $erMessage = "[INTER-Mediator] MediaAccess operation requires the option value of the 'media-root-dir' "
                    . "key or \$mediaRootDir variable in the params.php file.";
                echo $erMessage;
                $this->errorHandling($erMessage);
                $this->exitAsError(200); // The file accessing requires the media-root-dir keyed value.
            }
            /*
             * If the FileMaker's object field is storing a PDF, the $file could be "http://server:16000/..."
             * style URL. In case of an image, $file is just the path info as like above.
             */
            list($file, $isURL) = $this->checkForFileMakerMedia($dbProxyInstance, $file, $isURL);
            // Set the target variable
            $file = IMUtil::removeNull($file);
            if (strpos($file, '../') !== false) { // Stop for security reason.
                $erMessage = "[INTER-Mediator] The '..' path component isn't permitted.";
                echo $erMessage;
                $this->errorHandling($erMessage);
                $this->exitAsError(200);
            }
            $target = $isURL ? $file : "{$mediaRootDir}/{$file}";
            // Analyze the target variable if it contains context name and key parameters.
            $analyzeResult = $this->analyzeTarget($target);  // Check the context name and key fields.
            if ($analyzeResult) {
                $dbProxyInstance->dbSettings->setDataSourceName($this->targetContextName);
//                $context = $dbProxyInstance->dbSettings->getDataSourceTargetArray();
            }
            // Check the authentication and authorization
            //if (isset($options['media-context'])) { // media-context is removed. This comment is for my memo.
            $this->cookieUser = null;
            $authResult = $this->checkAuthentication($dbProxyInstance, $options);
            // Authentication error or authorization error rise an exception within checkAuthentication
            if ($analyzeResult) { // Get the relevant relation to the context.
                switch ($authResult) {
                    case  'field_user':
                    case  'field_group':
                        $authInfoField = $dbProxyInstance->dbClass->authHandler->getFieldForAuthorization("load");
                        $tableName = $dbProxyInstance->dbSettings->getEntityForRetrieve();
                        $contextRecord = $dbProxyInstance->dbClass->authHandler->authSupportCheckMediaPrivilege(
                            $tableName, $authResult, $authInfoField, $this->cookieUser, $this->targetKeyField, $this->targetKeyValue);
                        if (!$contextRecord) {
                            $this->exitAsError(401);
                        }
                        $contextRecord = [$contextRecord];
                        break;
                    default: // 'context_auth' or 'no_auth'
                        if ($this->targetContextName) {
                            if ($this->targetKeyField && $this->targetKeyValue) {
                                $dbProxyInstance->dbSettings->addExtraCriteria($this->targetKeyField, "=", $this->targetKeyValue);
                            }
                            $dbProxyInstance->dbSettings->setCurrentUser($this->cookieUser);
                            $contextRecord = $dbProxyInstance->readFromDB();
                        }
                }
            }

            $isClass = (stripos($target, 'class://') === 0);
            $isNoRec = !is_array($contextRecord) || (count($contextRecord) === 0);
            $isOneRec = is_array($contextRecord) && (count($contextRecord) === 1);
            // $condition = !$isOneRec && (!$isClass || ($isClass && $isNoRec));
            // In case of the "class:" schema, the record set can have 1 or more than 1 record.
            // In case of not class: schema, the record set has to have just 1 record.
            $isNoTarget = !$this->targetContextName;
            $condition = ($isClass && !$isNoRec && !$isNoTarget)
                || (!$isClass && ((!$isNoRec && $isOneRec && !$isNoTarget) || ($isNoRec && !$isOneRec && $isNoTarget)));
//              if (!$isOneRec && (!$isClass || ($isClass && $isNoRec))) {
            if (!$condition) {
                $erMessage = "[INTER-Mediator] No record which is associated with the parameters in the url({$target}).";
                echo $erMessage;
                $this->errorHandling($erMessage);
                $this->exitAsError(500);
            }

            // Responding the contents
            $dq = '"';

            if (stripos($target, 'class://') === 0) { // class url is special handling.
                $noscheme = substr($target, 8);
                $className = substr($noscheme, 0, strpos($noscheme, "/"));
                $processingObject = new $className();
                $processingObject->processing($contextRecord, $options);
            } else {
                $className = $this->getClassNameForMedia($isURL, $target); // Decide class from URL
                $dbProxyInstance->logger->setDebugMessage("Instantiate the class '{$className}'", 2);
                $processing = new $className();
                $content = $processing->getMedia($file, $target, $dbProxyInstance);
                $fileName = $processing->getFileName($file);
                header("Content-Type: " . IMUtil::getMimeType($fileName));
                header("Content-Length: " . strlen($content));
                header("Content-Disposition: {$this->disposition}; filename={$dq}{$fileName}{$dq}");
                $util = new IMUtil();
                $util->outputSecurityHeaders();
                $this->outputImage($content);
            }
        } catch (Exception $ex) {
            $this->errorHandling($ex->getMessage());
            $this->exitAsError(500);
        }
    }

    /**
     * Determines the class name for media processing based on the URL scheme.
     *
     * @param bool $isURL Whether the target is a URL.
     * @param string $target Target URL or file path.
     * @return string Class name for media processing.
     * @throws Exception If the URL scheme is unknown.
     */
    private function getClassNameForMedia(bool $isURL, string $target): string
    {
        if (!$isURL) { // File path.
            $className = "FileSystem";
        } else if (stripos($target, 'http://') === 0 || stripos($target, 'https://') === 0) { // http or https
            $className = "FileMakerContainer";
        } else if (stripos($target, 's3://') === 0) {
            $className = "AWSS3";
        } else if (stripos($target, 'dropbox://') === 0) {
            $className = "Dropbox";
        } else if (stripos($target, 'file://') === 0) {
            $className = "FileURL";
        } else {
            throw new Exception('Undefined schema in URL.');
        }
        return "INTERMediator\\Media\\{$className}";
    }

    /**
     * Checks if the target URL has a known schema.
     *
     * @param string $file Target URL or file path.
     * @return bool Whether the target URL has a known schema.
     */
    private function isPossibleSchema(string $file): bool
    {
        $schema = ["https:", "http:", "class:", "s3:", "dropbox:", "file:"];
        foreach ($schema as $scheme) {
            if (strpos($file, $scheme) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Exits the script with an error code and sets the corresponding HTTP header.
     *
     * @param int $code Error code (204, 401, or 500).
     * @throws Exception Always thrown.
     */
    private function exitAsError(int $code): void
    {
        if ($this->thrownException) {
            return;
        }
        $this->thrownException = true;
        switch ($code) {
            case 204:
                header("HTTP/1.1 204 No Content");
                break;
            case 401:
                header("HTTP/1.1 401 Unauthorized");
                break;
            case 500:
                header("HTTP/1.1 500 Internal Server Error");
                break;
            default: // for debug purpose mainly.
        }
        throw new Exception('Respond HTTP Error.');
    }

    /**
     * Checks if the target URL is a FileMaker media URL and adjusts it accordingly.
     *
     * @param Proxy $dbProxyInstance Database proxy instance.
     * @param string $file Target URL or file path.
     * @param bool $isURL Whether the target is a URL.
     * @return array Adjusted target URL and whether it is a URL.
     */
    private function checkForFileMakerMedia(Proxy $dbProxyInstance, string $file, bool $isURL): array
    {
        if (strpos($file, '/fmi/xml/cnt/') === 0 ||
            strpos($file, '/Streaming_SSL/MainDB') === 0) {
            // FileMaker's container field storing an image.
            $urlHost = $dbProxyInstance->dbSettings->getDbSpecProtocol() . "://"
                . urlencode($dbProxyInstance->dbSettings->getDbSpecUser()) . ":"
                . urlencode($dbProxyInstance->dbSettings->getDbSpecPassword()) . "@"
                . $dbProxyInstance->dbSettings->getDbSpecServer() . ":"
                . $dbProxyInstance->dbSettings->getDbSpecPort();
            $file = $urlHost . $file;
            $oldLocale = setlocale(LC_CTYPE, 0);
            setlocale(LC_CTYPE, 'C');
            $path = parse_url($file, PHP_URL_PATH);
            $query = parse_url($file, PHP_URL_QUERY);
            setlocale(LC_CTYPE, $oldLocale);
            parse_str($query, $get_array);
            $get_array = $get_array + $_GET;
            foreach ($get_array as $key => $value) {
                if ($key !== 'media' && $key !== 'attach') {
                    if (strpos($path, '?') !== false) {
                        $path .= '&';
                    } else {
                        $path .= '?';
                    }
                    $path .= urlencode($key) . '=' . urlencode($value);
                }
            }
            return array($urlHost . $path, true);
        }
        return array($file, $isURL);
    }

    /**
     * Checks the authentication and authorization for media access.
     *
     * @param Proxy $dbProxyInstance Database proxy instance.
     * @param array|null $options Options for media access.
     * @return string Authentication result ('context_auth', 'no_auth', 'field_user', 'field_group').
     * @throws Exception If authentication fails.
     */
    private function checkAuthentication(Proxy $dbProxyInstance, ?array $options): string
    {
        $contextDef = $dbProxyInstance->dbSettings->getDataSourceTargetArray();
        $isContextAuth = (isset($contextDef['authentication']) && (isset($contextDef['authentication']['all'])
                || isset($contextDef['authentication']['load']) || isset($contextDef['authentication']['read'])));
        $isOptionAuth = isset($options['authentication']);
        if (!$isContextAuth && !$isOptionAuth) { // No authentication
            return 'no_auth';
        }
        // Check the authentication credential on cookie
        $cookieNameUser = "_im_username";
        $cookieNameToken = "_im_mediatoken";
        if (isset($options['authentication']['realm'])) {
            $realm = str_replace(" ", "_",
                str_replace(".", "_", $options['authentication']['realm']));
            $cookieNameUser .= ('_' . $realm);
            $cookieNameToken .= ('_' . $realm);
        }
        $cValueUser = $_COOKIE[$cookieNameUser] ?? '';
        $this->cookieUser = $cValueUser;
        $cValueToken = $_COOKIE[$cookieNameToken] ?? '';
        if (!$dbProxyInstance->checkMediaToken($cValueUser, $cValueToken)) {
            $this->exitAsError(401);
        }
        if ($isContextAuth) { // If the context definition has authentication keyed value.
            $authInfoTarget = $dbProxyInstance->dbClass->authHandler->getTargetForAuthorization("read");
            if ($authInfoTarget == 'field-user') {
                if (!$this->targetContextName) {
                    $this->exitAsError(401);
                }
                return 'field_user';
            } else if ($authInfoTarget == 'field-group') {
                // unimplemented
                return 'field_group';
            } else {
                $isOptionAuth = true; // Follow the below process if the target isn't specified.
            }
        }
        $authorizedUsers = $dbProxyInstance->dbClass->authHandler->getAuthorizedUsers("read");
        $authorizedGroups = $dbProxyInstance->dbClass->authHandler->getAuthorizedGroups("read");
        if (count($authorizedGroups) != 0 || count($authorizedUsers) != 0) {
            $belongGroups = $dbProxyInstance->dbClass->authHandler->authSupportGetGroupsOfUser($_COOKIE[$cookieNameUser]);
            if (!in_array($_COOKIE[$cookieNameUser], $authorizedUsers)
                && count(array_intersect($belongGroups, $authorizedGroups)) == 0
            ) {
                $this->exitAsError(400);
            }
        }
        return 'context_auth';
    }

    /**
     * Analyzes the target URL and extracts context name and key fields.
     *
     * @param string $target Target URL or file path.
     * @return bool Whether the target URL contains context name and key fields.
     */
    private function analyzeTarget(string $target): bool
    {
        // The following properties are the results of this method.
        $this->targetKeyField = null;
        $this->targetKeyValue = null;
        $this->targetContextName = null;

        $result = false;
        $endOfPath = strpos($target, "?");
        $endOfPath = ($endOfPath === false) ? strlen($target) : $endOfPath;
        $pathComponents = explode('/', substr($target, 0, $endOfPath));
        $indexKeying = -1;
        foreach ($pathComponents as $index => $dname) {
            $decodedComponent = urldecode($dname);
            if (strpos($decodedComponent, '=') !== false) {
                $indexKeying = $index;
                $fieldComponents = explode('=', $decodedComponent);
                $this->targetKeyField = $fieldComponents[0];
                $this->targetKeyValue = $fieldComponents[1];
            }
        }
        if ($indexKeying > 0) {
            $this->targetContextName = urldecode($pathComponents[$indexKeying - 1]);
            $result = true;
        } else {
            if ($pathComponents[0] == 'class:' && isset($pathComponents[3])) {
                $this->targetContextName = urldecode($pathComponents[3]);
                $result = true;
            }
        }
        return $result;
    }

    /**
     * Outputs the image content with proper headers and security settings.
     *
     * @param string $content Image content.
     * @return void
     */
    private function outputImage(string $content): void
    {
        $rotate = false;
        if (function_exists('exif_imagetype') && function_exists('imagejpeg') &&
            strlen($content) > 0
        ) {
            $tmpDir = ini_get('upload_tmp_dir');
            if ($tmpDir === '') {
                $tmpDir = sys_get_temp_dir();
            }
            $temp = 'IM_TEMP_' .
                str_replace(DIRECTORY_SEPARATOR, '-', base64_encode(IMUtil::randomString(12))) .
                '.jpg';
            if (mb_substr($tmpDir, 1) === DIRECTORY_SEPARATOR) {
                $tempPath = $tmpDir . $temp;
            } else {
                $tempPath = $tmpDir . DIRECTORY_SEPARATOR . $temp;
            }
            $fp = fopen($tempPath, 'w');
            if ($fp !== false) {
                fwrite($fp, $content);
                fclose($fp);

                if (file_exists($tempPath)) {
                    $imageType = image_type_to_mime_type(exif_imagetype($tempPath));
                    if ($imageType === 'image/jpeg') {
                        $image = imagecreatefromstring($content);
                        if ($image !== false) {
                            try {
                                $exif = @exif_read_data($tempPath);
                            } catch (Exception $ex) {
                                $exif = false;
                            }
                            if ($exif !== false && !empty($exif['Orientation'])) {
                                switch ($exif['Orientation']) {
                                    case 3:
                                        $content = imagerotate($image, 180, 0);
                                        $rotate = true;
                                        break;
                                    case 6:
                                        $content = imagerotate($image, -90, 0);
                                        $rotate = true;
                                        break;
                                    case 8:
                                        $content = imagerotate($image, 90, 0);
                                        $rotate = true;
                                        break;
                                }
                            }
                        }
                        if ($rotate === true) {
                            header('Content-Type: image/jpeg');
                            ob_start();
                            imagejpeg($content);
                            $size = ob_get_length();
                            header('Content-Length: ' . $size);
                            $util = new IMUtil();
                            $util->outputSecurityHeaders();
                            ob_end_flush();
                        }
                        imagedestroy($image);
                    }
                    unlink($tempPath);
                }
            }
        }
        if ($rotate === false) {
            echo $content;
        }
    }
}
