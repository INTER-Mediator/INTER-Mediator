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

$pathToIM = "..";   // Modify this to match your directories.

require_once("{$pathToIM}/INTER-Mediator.php");
spl_autoload_register('loadClass');

$providerKey = "oAuthProvider";
$params = IMUtil::getFromParamsPHPFile(array($providerKey));
if ($params === false) {
    echo "Passed wrong parameters to the getFromParamsPHPFile method.";
    exit;
}
$authObj = new OAuthAuth($params[$providerKey]);
if (is_null($authObj)) {
    echo "Couldn't authenticate with parameters you supplied.";
    exit;
}
$jsCode = "";
$err = "No Error";
if ($authObj->afterAuth()) {
    $jsCode = $authObj->javaScriptCode();
} else {
    $err = $authObj->errorMessages();
}
header("Content-Type: text/html");
?>
<html>
<head>
    <script type="text/javascript"><?php echo $jsCode; ?></script>
</head>
<body>
Provider: <?php echo $params[$providerKey]; ?><br>
Status: <?php echo $err; ?>
</body>
</html>
