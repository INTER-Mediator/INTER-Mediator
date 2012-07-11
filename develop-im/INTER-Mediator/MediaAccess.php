<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 2012/06/24
 * Time: 7:37
 * To change this template use File | Settings | File Templates.
 */

class MediaAccess
{
    function processing($dbProxyInstance, $dir, $file)
    {
        $target = "$dir/$file";
        if (! $dbProxyInstance->checkMediaToken($_COOKIE['_im_username'], $_COOKIE['_im_mediatoken']))    {
            header( "HTTP/1.1 401 Unauthorized" );
            return;
        }
        if ( file_exists($target)) {
            $content = file_get_contents($target);
            if ($content !== false) {
                header("Content-Type: " . mime_content_type($target));
                header("Content-Length: " . strlen($content));
                echo $content;
            } else {
                header( "HTTP/1.1 500 Internal Server Error" );
            }
        } else {
            header( "HTTP/1.1 500 Internal Server Error" );
        }
    }
}
