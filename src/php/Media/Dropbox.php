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

namespace INTERMediator\Media;

use Exception;
use msyk\DropboxAPIShortLivedToken\DropboxClientModified;
use Spatie\Dropbox\Client;
use msyk\DropboxAPIShortLivedToken\AutoRefreshingDropBoxTokenService;
use INTERMediator\DB\Proxy;
use INTERMediator\IMUtil;
use INTERMediator\Params;

/**
 * Dropbox class handles file uploads and downloads to/from Dropbox via Dropbox API.
 * Implements UploadingSupport and DownloadingSupport interfaces for integration with INTER-Mediator.
 */
class Dropbox extends UploadingSupport implements DownloadingSupport
{
    /**
     * Dropbox API App Key
     * @var string|null
     */
    private ?string $appKey;
    /**
     * Dropbox API App Secret
     * @var string|null
     */
    private ?string $appSecret;
    /**
     * Dropbox API Refresh Token
     * @var string|null
     */
    private ?string $refreshToken;
    /**
     * Path to store the Dropbox API Access Token
     * @var string|null
     */
    private ?string $accessTokenPath;
    /**
     * Root directory in Dropbox for file operations
     * @var string|null
     */
    private ?string $rootInDropbox;
    /**
     * The file name of the current file being processed
     * @var string|null
     */
    private ?string $fileName = null;

    /**
     * Dropbox constructor. Initializes Dropbox API credentials and settings from parameters.
     */
    public function __construct()
    {
        $this->appKey = IMUtil::getFromProfileIfAvailable(
            Params::getParameterValue('dropboxAppKey', ''));
        $this->appSecret = IMUtil::getFromProfileIfAvailable(
            Params::getParameterValue('dropboxAppSecret', ''));
        $this->refreshToken = Params::getParameterValue('dropboxRefreshToken', '');
        $this->accessTokenPath = Params::getParameterValue('dropboxAccessTokenPath', '');
        $this->rootInDropbox = Params::getParameterValue('rootInDropbox', '/');
    }

    /**
     * Retrieves the contents of a file from Dropbox.
     *
     * @param string $file The file name (unused, for interface compatibility).
     * @param string $target The Dropbox file path or URL.
     * @param Proxy $dbProxyInstance The database proxy instance.
     * @return string The file contents.
     * @throws Exception If the file cannot be retrieved.
     */
    public function getMedia(string $file, string $target, Proxy $dbProxyInstance): string
    {
        $startOfPath = strpos($target, "/", 5);
        $urlPath = substr($target, $startOfPath + 2);
        $this->fileName = str_replace("+", "%20", urlencode(basename($urlPath)));
        $tokenProvider = new AutoRefreshingDropBoxTokenService(
            $this->refreshToken, $this->appKey, $this->appSecret, $this->accessTokenPath);
        $client = new DropboxClientModified($tokenProvider);
        return $client->download($urlPath);
    }

    /**
     * Returns the file name of the last accessed or processed file.
     *
     * @param string $file The file path (unused).
     * @return string|null The file name, or null if not set.
     */
    public function getFileName(string $file): ?string
    {
        return $this->fileName;
    }

    /**
     * Handles file upload processing to Dropbox.
     *
     * @param Proxy $db The database proxy instance.
     * @param string|null $url The redirect URL on error.
     * @param array|null $options Additional options for processing.
     * @param array $files Uploaded files array.
     * @param bool $noOutput Whether to suppress output.
     * @param array $field Array of target field names.
     * @param string $contextName The context name for processing.
     * @param string|null $keyField The key field for database update.
     * @param string|null $keyValue The key value for database update.
     * @param array|null $dataSource Data source definition.
     * @param array|null $dbSpec Database specification.
     * @param int $debug Debug level.
     * @throws Exception If an error occurs during processing.
     * @return void
     */
    public function processing(Proxy  $db, ?string $url, ?array $options, array $files, bool $noOutput, array $field,
                               string $contextName, ?string $keyField, ?string $keyValue,
                               ?array $dataSource, ?array $dbSpec, int $debug): void
    {
        $counter = -1;
        foreach ($files as $fileInfo) { // Single file only
            $counter += 1;
            if (is_array($fileInfo['name'])) {   // JQuery File Upload Style
                $fileInfoName = $fileInfo['name'][0];
                $fileInfoTemp = $fileInfo['tmp_name'][0];
            } else {
                $fileInfoName = $fileInfo['name'];
                $fileInfoTemp = $fileInfo['tmp_name'];
            }
            if (!is_uploaded_file($fileInfoTemp)) { // Security check
                return;
            }
            $filePathInfo = pathinfo(IMUtil::removeNull(basename($fileInfoName)));
            $targetFieldName = $field[$counter];
            $dirPath = $contextName . DIRECTORY_SEPARATOR
                . $keyField . "=" . $keyValue . DIRECTORY_SEPARATOR . $targetFieldName;
            try {
                $rand4Digits = random_int(1000, 9999);
            } catch (Exception $ex) {
                $rand4Digits = rand(1000, 9999);
            }

            $objectPath = $this->rootInDropbox . '/' . $dirPath
                . '/' . $filePathInfo['filename'] . '_' . $rand4Digits . '.' . $filePathInfo['extension'];
            $storedURL = "dropbox://$objectPath";

            try {
                $tokenProvider = new AutoRefreshingDropBoxTokenService(
                    $this->refreshToken, $this->appKey, $this->appSecret, $this->accessTokenPath);
                $client = new Client($tokenProvider);
                $client->upload($objectPath, file_get_contents($fileInfoTemp), 'add');
            } catch (Exception $ex) {
                if (!is_null($url)) {
                    header('Location: ' . $url);
                } else {
                    $db->logger->setErrorMessage($ex->getMessage());
                    $db->processingRequest("nothing");
                    if (!$noOutput) {
                        $db->finishCommunication();
                        $db->exportOutputDataAsJSON();
                    }
                }
                return;
            }
            unlink($fileInfoTemp); // Remove upload file

            $this->processingFile($db, $options, $storedURL, $storedURL, $targetFieldName,
                $keyField, $keyValue, $dataSource, $dbSpec, $debug);
        }
    }
}
