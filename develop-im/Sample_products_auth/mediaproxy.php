<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 2012/06/24
 * Time: 8:32
 * To change this template use File | Settings | File Templates.
 */

include("../INTER-Mediator/MediaAccess.php");
include("contexts_MySQL.php");

$reqURI = explode('/', $_SERVER['REQUEST_URI']);

$mediaProxy = new MediaAccess();
$mediaProxy->processing("/Library/WebServer/Documents/im/Sample_products/images", array_pop($reqURI));