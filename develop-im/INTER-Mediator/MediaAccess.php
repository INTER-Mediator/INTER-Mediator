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
        if ( file_exists($target)) {
            $content = file_get_contents($target);
            if ($content !== false) {
                header("Content-Type: " . $this->getMimeType($target));
                header("Content-Length: " . strlen($content));
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
