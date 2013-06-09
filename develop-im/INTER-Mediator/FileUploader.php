<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2013 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
class FileUploader
{
    var $db;

    function finishCommunication()  {
        $this->db->finishCommunication();
    }
    /*
            array(6) { ["_im_redirect"]=> string(54) "http://localhost/im/Sample_webpage/messages_MySQL.html" ["_im_contextname"]=> string(4) "chat" ["_im_field"]=> string(7) "message" ["_im_keyfield"]=> string(2) "id" ["_im_keyvalue"]=> string(2) "38" ["access"]=> string(10) "uploadfile" } array(1) { ["_im_uploadfile"]=> array(5) { ["name"]=> string(16) "ac0600_aoiro.pdf" ["type"]=> string(15) "application/pdf" ["tmp_name"]=> string(26) "/private/var/tmp/phpkk9RXn" ["error"]=> int(0) ["size"]=> int(77732) } }

    */

    function processing($datasource, $options, $dbspec, $debug) {
        $dbProxyInstance = new DB_Proxy();
        $this->db = $dbProxyInstance;
        $dbProxyInstance->initialize($datasource, $options, $dbspec, $debug, $_POST["_im_contextname"]);

        if (!isset($options['media-root-dir'])) {
            $dbProxyInstance->logger->setErrorMessage("'media-root-dir' isn't specified");
            $dbProxyInstance->processingRequest($options, "noop");
            return;
        }
        // requires media-root-dir specification.
        $fileRoot = $options['media-root-dir'];
        if ( substr($fileRoot, strlen($fileRoot)-1, 1) != '/' )    {
            $fileRoot .= '/';
        }

        // get the uploaded last file info to $fileInfo
        foreach($_FILES as $fn=>$fileInfo)  {
        }

        $filePathInfo = pathinfo($fileInfo["name"]);
        $dirPath  = $fileRoot . $_POST["_im_contextname"] . '/'
            . $_POST["_im_keyfield"] . "=". $_POST["_im_keyvalue"] . '/' . $_POST["_im_field"];
        $filePath  = $dirPath . '/' . $filePathInfo['filename'] . '_'
            . rand (1000 , 9999 ). '.' . $filePathInfo['extension'];
        if ( ! file_exists($dirPath))   {
            mkdir($dirPath, 0744, true);
        }
        $result = move_uploaded_file($fileInfo["tmp_name"], $filePath);
        if (!$result) {
            $dbProxyInstance->logger->setErrorMessage("Fail to move the uploaded file in the media folder.");
            $dbProxyInstance->processingRequest($options, "noop");
            return;
        }

        $dbKeyValue = $_POST["_im_keyvalue"];

        $relatedContext = null;
        $dbProxyContext = $dbProxyInstance->dbSettings->getDataSourceTargetArray();
        if( isset($dbProxyContext['file-upload']))   {
            $relatedContextName = $dbProxyContext['file-upload'];
            $relatedContext = new DB_Proxy();
            $relatedContext->initialize($datasource, $options, $dbspec, $debug, $relatedContextName);
            $relatedContextInfo = $relatedContext->dbSettings->getDataSourceTargetArray();
            $relatedContext->dbSettings->setTargetFields(
                array($relatedContextInfo["relation"][0]["foreign-key"], "path"));
            $relatedContext->dbSettings->setValues(
                array($dbKeyValue, $filePath));
            $relatedContext->processingRequest($options, "new");
        } else {
            $dbProxyInstance->dbSettings->setExtraCriteria($_POST["_im_keyfield"], "=", $dbKeyValue);
            $dbProxyInstance->dbSettings->setTargetFields(array($_POST["_im_field"]));
            $dbProxyInstance->dbSettings->setValues(array($filePath));
            $dbProxyInstance->processingRequest($options, "update");
        }

        if ( isset( $_POST["_im_redirect"] ))   {
        //    header("Location: {$_POST["_im_redirect"]}");
        }
        if ( ! is_null( $relatedContext ))    {
            $relatedContext->finishCommunication(true);
        }
    }
}
