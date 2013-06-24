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
        $dir = $options['media-root-dir'];
        $target = "$dir/$file";

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
        if (isset($options['media-context']))  {
            $dbProxyInstance->dbSettings->setTargetName($options['media-context']);
            $context = $dbProxyInstance->dbSettings->getDataSourceTargetArray();
            if (isset($context['authentication'])
                && ( isset($context['authentication']['all']) || isset($context['authentication']['load']))) {
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
        if ( file_exists($target)) {
            $content = file_get_contents($target);
            if ($content !== false) {
                header("Content-Type: " . $this->getMimeType($target));
                header("Content-Length: " . strlen($content));
                $fileName = basename($file);
                header("Content-Disposition: inline; filename=\"{$fileName}\"");
                echo $content;
            } else {
                header( "HTTP/1.1 500 Internal Server Error" );
            }
        } else {
            header( "HTTP/1.1 500 Internal Server Error" );
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
