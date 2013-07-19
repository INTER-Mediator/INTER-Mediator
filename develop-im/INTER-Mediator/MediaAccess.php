<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2012 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 2012/06/24
 * Time: 7:37
 * To change this template use File | Settings | File Templates.
 */

class MediaAccess
{
    function processing($dbProxyInstance, $options, $file)
    {
        // It the $file ('media'parameter) isn't specified, it doesn't respond an error.
        if ( strlen($file) === 0 )  {
            header( "HTTP/1.1 204 No Content" );
            return;
        }

        // If the media parameter is an URL, the variable isURL will be set to true.
        $schema = array("https:", "http:");
        $isURL = false;
        foreach ($schema as $scheme)    {
            if (strpos($file, $scheme) === 0)   {
                $isURL = true;
                break;
            }
        }
        if (strpos($file,"/fmi/xml/cnt/") === 0)    {   // FileMaker's object field storing an image.
            $file = $dbProxyInstance->dbSettings->getDbSpecProtocol() . "://"
                . urlencode($dbProxyInstance->dbSettings->getDbSpecUser()) . ":"
                . urlencode($dbProxyInstance->dbSettings->getDbSpecPassword()) . "@"
                . $dbProxyInstance->dbSettings->getDbSpecServer() . ":"
                . $dbProxyInstance->dbSettings->getDbSpecPort() . $file;
            foreach( $_GET as $key => $value)   {
                if ($key !== 'media')   {
                    $file .= "&" . $key . "=" . urlencode($value);
                }
            }
            $isURL = true;
        }
        /*
         * If the FileMaker's object field is storing a PDF, the $file could be "http://server:16000/...
         * style URL. In case of an image, $file is just the path info as like above.
         */

        $target = $isURL ? $file : "{$options['media-root-dir']}/{$file}";

        if (isset($options['media-context']))  {
            $dbProxyInstance->dbSettings->setTargetName($options['media-context']);
            $context = $dbProxyInstance->dbSettings->getDataSourceTargetArray();
            if (isset($context['authentication'])
                && ( isset($context['authentication']['all']) || isset($context['authentication']['load']))) {
                $cookieNameUser = '_im_username';
                $cookieNameToken = '_im_mediatoken';
                if(isset($options['authentication']['realm']))  {
                    $cookieNameUser .= '_' . $options['authentication']['realm'];
                    $cookieNameToken .= '_' . $options['authentication']['realm'];
                }

                if (! $dbProxyInstance->checkMediaToken($_COOKIE[$cookieNameUser], $_COOKIE[$cookieNameToken]))    {
                    header( "HTTP/1.1 401 Unauthorized" );
                    return;
                }
                $authInfoField = $dbProxyInstance->dbClass->getFieldForAuthorization("load");
                $authInfoTarget = $dbProxyInstance->dbClass->getTargetForAuthorization("load");
                $pathComponents = explode('/', $target);
                $indexKeying = -1;
                foreach($pathComponents as $index => $dname)  {
                    if (strpos($dname, '=') !== false)  {
                        $indexKeying = $index;
                        $fieldComponents = explode('=', $dname);
                        $keyField = $fieldComponents[0];
                        $keyValue = $fieldComponents[1];
                    }
                }
                if ($indexKeying == -1 )  {
                    header( "HTTP/1.1 401 Unauthorized" );
                    return;
                }
                $contextName = $pathComponents[$indexKeying - 1];
                if ($contextName != $options['media-context'])  {
                    header( "HTTP/1.1 401 Unauthorized" );
                    return;
                }
                $tableName = $dbProxyInstance->dbSettings->getEntityForRetrieve();
                if ($authInfoTarget == 'field-user') {
                    if (! $dbProxyInstance->dbClass->authSupportCheckMediaPrivilege(
                        $tableName, $authInfoField, $_COOKIE[$cookieNameUser], $keyField, $keyValue))   {
                        header( "HTTP/1.1 401 Unauthorized" );
                        return;
                    }
                } else if ($authInfoTarget == 'field-group') {
                    //
                } else {
                    $authorizedUsers = $dbProxyInstance->dbClass->getAuthorizedUsers("load");
                    $authorizedGroups = $dbProxyInstance->dbClass->getAuthorizedGroups("load");
                    $belongGroups = $dbProxyInstance->dbClass->authSupportGetGroupsOfUser($_COOKIE[$cookieNameUser]);
                    if (!in_array($this->dbSettings->currentUser, $authorizedUsers)
                        && array_intersect($belongGroups, $authorizedGroups)
                    ) {
                        header( "HTTP/1.1 401 Unauthorized" );
                        return;
                    }
                }
            }
        }
        if (!$isURL) {
            if (!empty($file) && !file_exists($target)) {
                header("HTTP/1.1 500 Internal Server Error");
            }
        } else {
            if (intval(get_cfg_var('allow_url_fopen')) === 1) {
                $content = file_get_contents($target);
            } else {
                if (function_exists('curl_init')) {
                    $session = curl_init($target);
                    curl_setopt($session, CURLOPT_HEADER, false);
                    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
                    $content = curl_exec($session);
                    curl_close($session);
                } else {
                    header("HTTP/1.1 500 Internal Server Error");
                }
            }
            if ($content === false) {
                header("HTTP/1.1 500 Internal Server Error");
            } else {
                $fileName = basename($file);
                $qPos = strpos($fileName, "?");
                if ($qPos !== false)    {
                    $fileName = substr($fileName, 0, $qPos);
                }
                header("Content-Type: " . $this->getMimeType($fileName));
                header("Content-Length: " . strlen($content));
                header("Content-Disposition: inline; filename=\"{$fileName}\"");
                header('X-Frame-Options: SAMEORIGIN');
                echo $content;
            }
        }
    }

    function getMimeType($path)  {
        $type = "application/octet-stream";
        switch(strtolower(substr($path, strrpos($path, '.') + 1)))  {
            case 'jpg': $type = 'image/jpeg';   break;
            case 'jpeg': $type = 'image/jpeg';   break;
            case 'png': $type = 'image/png';   break;
            case 'html': $type = 'text/html';   break;
            case 'txt': $type = 'text/plain';   break;
            case 'gif': $type = 'image/gif';   break;
            case 'bmp': $type = 'image/bmp';   break;
            case 'tif': $type = 'image/tiff';   break;
            case 'tiff': $type = 'image/tiff';   break;
            case 'pdf': $type = 'application/pdf';   break;
        }
        return $type;
    }
}
