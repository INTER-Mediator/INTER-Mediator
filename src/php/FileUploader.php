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
use INTERMediator\DB\Logger;
use INTERMediator\DB\Proxy;
use INTERMediator\Media\MediaProcessing;

/**
 * Class FileUploader
 * Handles file upload processing, error handling, and integration with INTER-Mediator database proxies.
 *
 * @package INTERMediator
 */
class FileUploader
{
    /** @var Proxy Database proxy instance for communication with the backend.
     */
    private Proxy $db;

    /** @var int Access log level for logging purposes.
     */
    private int $accessLogLevel;

    /**
     * @var array<array-key, mixed> Output messages for logging or response.
     */
    private array $outputMessage = [];

    /**
     * @var array<array-key, mixed>|null Database result after processing (e.g., for CSV uploads).
     */
    public ?array $dbresult = null;

    /** FileUploader constructor.
     * Initializes access log level from parameters.
     */
    public function __construct()
    {
        $this->accessLogLevel = (int)Params::getParameterValue("accessLogLevel", 0);
    }

    /**
     * Gets the log result for the current upload process.
     * @return array<array-key, mixed> Output message array if access log level is enough, otherwise empty array.
     */
    public function getResultForLog(): array
    {
        if ($this->accessLogLevel < 1) {
            return [];
        }

        $this->outputMessage['name'] = $_POST["_im_contextname"] ?? null;
        return $this->outputMessage;
    }

    /** Finalizes communication with the database proxy.
     * @return void
     */
    public function finishCommunication(): void
    {
        $this->db->finishCommunication();
    }

    /**
     * Handles file upload errors and outputs error messages as JSON if needed.
     * @param array<array-key, mixed>|null $dataSource Data source definitions.
     * @param array<array-key, mixed>|null $options Options for INTER-Mediator.
     * @param array<array-key, mixed>|null $dbSpec Database specification.
     * @param int $debug Debug mode level.
     * @param string|null $contextName Context name for the upload.
     * @param bool $noOutput If true, suppresses output.
     * @return void
     * @throws Exception
     */
    public function processingAsError(?array $dataSource, ?array $options, ?array $dbSpec, int $debug, ?string $contextName, bool $noOutput): void
    {
        $this->db = new Proxy();
        $this->db->initialize($dataSource, $options, $dbSpec, $debug, $contextName);

        $messages = IMUtil::getMessageClassInstance();
        if (count($_FILES) === 0) {
            $this->db->logger->setErrorMessage($messages->getMessageAs(3201));
        } else {
            $errorCodes = [];
            foreach ($_FILES as $fileInfo) {
                if (isset($fileInfo["error"])) {
                    $errorCodes = array_merge($errorCodes, (array)$fileInfo["error"]);
                }
            }
            foreach ($errorCodes as $errorCode) {
                switch ($errorCode) {
                    case UPLOAD_ERR_OK:
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $this->db->logger->setErrorMessage($messages->getMessageAs(3202));
                        break;
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $this->db->logger->setErrorMessage($messages->getMessageAs(3203));
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $this->db->logger->setErrorMessage($messages->getMessageAs(3204));
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $this->db->logger->setErrorMessage($messages->getMessageAs(3205));
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $this->db->logger->setErrorMessage($messages->getMessageAs(3206));
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $this->db->logger->setErrorMessage($messages->getMessageAs(3207));
                        break;
                    default:
                        $this->db->logger->setErrorMessage($messages->getMessageAs(3208));
                }
            }
        }
        if (!$noOutput) {
            $this->db->processingRequest("nothing");
            $this->db->finishCommunication();
            $this->db->exportOutputDataAsJSON();
        }
    }

    /**
     * Main entry point for handling a file upload request from POST/FILES.
     * @param array<array-key, mixed>|null $dataSource Data source definitions.
     * @param array<array-key, mixed>|null $options Options for INTER-Mediator.
     * @param array<array-key, mixed>|null $dbSpec Database specification.
     * @param int $debug Debug mode level.
     * @return void
     * @throws Exception
     */
    public function processing(?array $dataSource, ?array $options, ?array $dbSpec, int $debug): void
    {
        $contextName = $_POST["_im_contextname"] ?? null;
        $keyField = $_POST["_im_keyfield"] ?? null;
        $keyValue = $_POST["_im_keyvalue"] ?? null;
        $field = [$_POST["_im_field"] ?? null];
        $files = $_FILES;

        /*
1 File uploaded <$_FILES>
{
    "files": {
		"name": ["20190653_新居雅行.jpeg"],
		"full_path": ["20190653_新居雅行.jpeg"],
		"type": ["image/jpeg"],
		"tmp_name": ["/private/var/folders/19/_y61zdrd1gzf4kcb345rpnbw0000gn/T/phpdlgfufredmj23GLlaJj"],
		"error": [0],
		"size": [30290]
	}
}
        <<In case of Post-only Mode>>
[
	{
		"name": "DSC00031.jpg",
		"full_path": "DSC00031.jpg",
		"type": "image/jpeg",
		"tmp_name": "/private/var/folders/19/_y61zdrd1gzf4kcb345rpnbw0000gn/T/phpei6r9vpiamq1f0FWst8",
		"error": 0,
		"size": 40974
	}
]
        <$_POST>
{
	"access": "uploadfile",
	"_im_contextname": "testtable",
	"_im_field": "vc1",
	"_im_keyfield": "id",
	"_im_keyvalue": "2",
	"authuser": "null",
	"clientid": "undefined",
	"response": "undefined",
	"response2m": "undefined",
	"response2": "undefined"
}
2 Files uploaded <$_FILES>
{
	"files": {
		"name": ["20190653_新居雅行.jpeg","face.png"],
		"full_path": ["20190653_新居雅行.jpeg","face.png"],
		"type": ["image/jpeg","image/png"],
		"tmp_name": [
			"/private/var/folders/19/_y61zdrd1gzf4kcb345rpnbw0000gn/T/phpmmbd9vk3o13v3DsAgde",
			"/private/var/folders/19/_y61zdrd1gzf4kcb345rpnbw0000gn/T/phpv6mn36d3533nep6Z86h"
		],
		"error": [0,0],
		"size": [30290,242584]
	}
}
        <$_POST>
{
	"access": "uploadfile",
	"_im_contextname": "testtable",
	"_im_field": "vc1",
	"_im_keyfield": "id",
	"_im_keyvalue": "2",
	"authuser": "null",
	"clientid": "undefined",
	"response": "undefined",
	"response2m": "undefined",
	"response2": "undefined"
}
         */

        $this->processingWithParameters($dataSource, $options, $dbSpec, $debug,
            $contextName, $keyField, $keyValue, $field, $files, true);
        $this->db->finishCommunication();
        $this->db->exportOutputDataAsJSON();
    }

    /**
     * Handles file upload processing with explicit parameters and file data.
     * @param array<array-key, mixed>|null $dataSource Data source definitions.
     * @param array<array-key, mixed>|null $options Options for INTER-Mediator.
     * @param array<array-key, mixed>|null $dbSpec Database specification.
     * @param int $debug Debug mode level.
     * @param string|null $contextName Context name for the upload.
     * @param string|null $keyField Key field name for the record.
     * @param string|null $keyValue Key value for the record.
     * @param array<array-key, mixed>|null $field Field(s) for the upload.
     * @param array<array-key, mixed>|null $files Uploaded file(s) data.
     * @param bool $noOutput If true, suppresses output.
     * @return void
     * @throws Exception
     */
    public function processingWithParameters(?array  $dataSource, ?array $options, ?array $dbSpec, int $debug,
                                             ?string $contextName, ?string $keyField, ?string $keyValue, ?array $field,
                                             ?array  $files, bool $noOutput): void
    {
        $this->db = new DB\Proxy();
        $this->db->initialize($dataSource, $options, $dbSpec, $debug, $contextName);

        $this->db->logger->setDebugMessage("[FileUploader::processingWithParameters] contextName={$contextName}, "
            . "keyField={$keyField}, keyValue={$keyValue}, field=" . var_export($field, true)
            . ", files=" . var_export($files, true), 2);

        $contextDef = $this->db->dbSettings->getDataSourceTargetArray();
        $dbClass = ($contextDef['db-class'] ?? ($dbSpec['db-class'] ?? Params::getParameterValue('dbClass', '')));
        $targetField = $field[0] ?? null;
        $className = $this->getClassNameForMedia($dbClass, $targetField); // Decided media class name

        if (empty($files)) { // If no file is uploaded.
            $messages = IMUtil::getMessageClassInstance();
            $this->db->logger->setErrorMessage($messages->getMessageAs(3202));
            $this->db->processingRequest("nothing");
            if (!$noOutput) {
                $this->db->finishCommunication();
                $this->db->exportOutputDataAsJSON();
            }
            return;
        }

        // Getting the custom file name settings from context definition
        $customFileName = null;
        if (isset($contextDef['file-upload'])) {
            foreach ($contextDef['file-upload'] as $uploadDef) {
                if (isset($uploadDef['field']) && isset($uploadDef['file-name'])
                    && $uploadDef['field'] === $targetField) {
                    $customFileName = $uploadDef['file-name'];
                }
            }
        }
        // Handling the custom file name
        if (!is_null($customFileName)) {
            $this->db->dbSettings->addExtraCriteria($keyField, "=", $keyValue);
            $this->db->processingRequest("read");
            $currentRecord = ($this->db->getDatabaseResult())[0] ?? [];
            $this->db->logger->setDebugMessage(
                "[FileUploader::processingWithParameters] Current record: " . var_export($currentRecord, true), 2);
            $customFileName = IMUtil::templating($customFileName, $currentRecord);
        }
        $this->db->logger->setDebugMessage("[FileUploader::processingWithParameters] customFileName=" . $customFileName, 2);

        // Instantiating the media class object and call the processing method.
        $className = "INTERMediator\\Media\\{$className}"; // Instantiated media class object.
        $this->db->logger->setDebugMessage("Instantiate the class '{$className}'", 2);
        if (class_exists($className) && is_subclass_of($className, MediaProcessing::class)) {
            $mediaClassObj = new $className();
            $mediaClassObj->processing($this->db, null, $options, $files, $noOutput, $field,
                $contextName, $keyField, $keyValue, $dataSource, $dbSpec, $debug, $customFileName);
            if ($targetField == "_im_csv_upload") {    // CSV File uploading
                if (isset($this->db->outputOfProcessing['dbresult'])) { // For CSV importing
                    $this->dbresult = $this->db->outputOfProcessing['dbresult'];
                }
            }
        } else {
            $this->db->logger->setErrorMessage("[INTER-Mediator] The media handling class doesn't extend the MediaProcessing class.");
            $this->db->processingRequest("nothing");
            if (!$noOutput) {
                $this->db->finishCommunication();
                $this->db->exportOutputDataAsJSON();
            }
        }
    }

    /** Determines the media handler class name based on the database class and context definition.
     * @param string $dbclass The database class name.
     * @param string|null $targetField The target field name. The file-upload items for other fields are ignored.
     * @return string The media handler class name.
     */
    private function getClassNameForMedia(string $dbclass, ?string $targetField = null): string
    {
        return self::decideMediaClassName($dbclass,
            $this->db->dbSettings->getDataSourceTargetArray()['file-upload'] ?? null, $targetField);
    }

    /** Determines the media handler class name from the file-upload definitions.
     * @param string $dbclass The database class name.
     * @param array<array-key, mixed>|null $uploadDefs The 'file-upload' definitions of the context.
     * @param string|null $targetField The target field name. The items for other fields are ignored.
     * @return string The media handler class name.
     */
    public static function decideMediaClassName(string $dbclass, ?array $uploadDefs, ?string $targetField): string
    {
        $isFileMaker = ($dbclass === 'FileMaker_FX' || $dbclass === 'FileMaker_DataAPI');
        foreach ($uploadDefs ?? [] as $item) {
            if (!isset($item['container'])
                || (isset($item['field']) && !is_null($targetField) && $item['field'] !== $targetField)) {
                continue;
            }
            $container = $item['container'];
            if ($container === TRUE) {
                if ($isFileMaker) {
                    return "FileMakerContainer";
                }
                continue;
            }
            if (!is_string($container)) {
                continue;
            }
            switch (strtolower($container)) {
                case 'filemaker':
                case 'filemakercontainer':
                    if ($isFileMaker) {
                        return "FileMakerContainer";
                    }
                    break;
                case 's3':
                    return "AWSS3";
                case 'dropbox':
                    return "Dropbox";
                case 'fileurl':
                    return "FileURL";
                case 'filesystem':
                    return "FileSystem";
            }
        }
        return "FileSystem";
    }
}
