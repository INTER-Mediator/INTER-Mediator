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
//namespace INTERMediator;

setlocale(LC_ALL, 'ja_JP', 'ja');
date_default_timezone_set('Asia/Tokyo');
// The variable pathToIM has to point the INTER-Mediator directory.
$pathToIM = "../../";   // Modify this to match your directories.
//---------------------------------------------

require_once("{$pathToIM}/INTER-Mediator.php");
require_once("{$pathToIM}/src/php/DB/PDO.php");
require_once("{$pathToIM}/src/php/OAuthAuth.php");

$authObj = new INTERMediator\OAuthAuth($_COOKIE["_im_oauth_provider"] ?? "");
$authObj->debugMode = false; // or comment here
$authObj->setDoRedirect(true);
if (is_null($authObj)) {
    echo "Couldn't authenticate with parameters you supplied.";
    exit;
}
$jsCode = "";
if (!$authObj->isActive) {
    echo "Missing parameters for OAuth authentication.";
    exit;
}
$err = "No Error";
if ($authObj->afterAuth()) {
    $jsCode = $authObj->javaScriptCode();
    if ($authObj->debugMode) {
        $err = $authObj->errorMessages();
    }
    if ($authObj->isCreate()) {
        // In the case of newly logged-in, you can add any code for sending email or others.
    }
} else {
    $err = $authObj->errorMessages();
}
header("Content-Type: text/html; charset=UTF-8");
?>
<html>
<head>
    <script type="text/javascript" src="../../INTER-Mediator.php"></script>
    <script type="text/javascript"><?php echo $jsCode; ?></script>
</head>
<body>
Provider: <?php echo $authObj->oAuthProvider(); ?><br>
Status: <?php echo $err; ?>
<hr/>
<p>Any other messages...</p>
</body>
</html>
