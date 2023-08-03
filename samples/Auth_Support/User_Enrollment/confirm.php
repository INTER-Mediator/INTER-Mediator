<?php
/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

use INTERMediator\DB\Proxy;

$message = '';
$ermessage = '';
if (count($_GET) > 0) {
    if (!isset($_GET['c']) || strlen($_GET['c']) < 10) {
        $ermessage .= '接続するときのURLが正しくありません。途中で欠けた文字で接続していないか確認してください。';
    } else {

        // Generate an initial password. You can modify here to adapt your requirement for password.
        $seed = '234578ABDEFGHJLMNPRTUYadefghprty';
        $password = '';
        for ($i = 0; $i < 6; $i++) {
            try {
                $n = random_int(0, strlen($seed) - 1);
            } catch (\Exception $ex) {
                $n = rand(0, strlen($seed) - 1);
            }
            $password .= substr($seed, $n, 1);
        }

        require_once('../../../INTER-Mediator.php');   // Set the valid path to INTER-Mediator.php
        $contextDef = array(
            "name" => "authuser",
            "view" => "authuser",
            "table" => "dummydummy",
            "records" => 1,
            'send-mail' => array(
                'read' => array(
                    'to' => 'email',
                    'bcc-constant' => 'info@msyk.net',
                    'subject-constant' => 'ユーザ登録を完了しました',
                    'from-constant' => 'Masayuki Nii <info@msyk.net>',
                    'body-template' => 'confirmmail.txt',
                    'body-fields' => "email,realname,@{$password}",
                    'f-option' => true,
                    'body-wrap' => 78,
                ),
            )
        );
        $dbInstance = new Proxy();
        $dbInstance->initialize(
            array($contextDef),
            array(),
            array("db-class" => "PDO" /* or "FileMaker_FX" */),
            2
        );
        $result = $dbInstance->userEnrollmentActivateUser($_GET['c'], $password);

        if (!$result) {
            $ermessage .= '確認しましたが、該当する申し込みがありません。';
        } else {
            $message .= 'アカウントを発行し、そのご案内をメールでお送りしました。';
            $dbInstance = new Proxy();
            $dbInstance->initialize(
                array($contextDef),
                array(),
                array("db-class" => "PDO" /* or "FileMaker_FX" */),
                2,
                "authuser"
            );
            $dbInstance->dbSettings->addExtraCriteria("id", "=", $result);
            $dbInstance->processingRequest("read");
        }
    }
}
header('Content-Type: text/html;charset="UTF-8"');

?>
<!DOCTYPE html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
    <title>登録確認 - INTER-Mediator</title>
<body>
<h1>登録確認</h1>
<p style="color:black;font-weight:900"><?php echo $message ?></p>
<p style="color:red"><?php echo $ermessage;/* echo $errors; */ ?></p>
</body>
</html>
