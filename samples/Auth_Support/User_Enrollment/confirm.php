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

use INTERMediator\DB\Proxy_ExtSupport;

require_once('../../../INTER-Mediator.php');   // Set the valid path to INTER-Mediator.php

class DBAccess
{
    use Proxy_ExtSupport;

    public function generatePassword(): string
    {
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
        return $password;
    }

    public function getTargetUserId(string $password): ?string
    {
        $dSource = [
            [
                "name" => "authuser",
                "key" => "id",
                "records" => 1,
                'send-mail' => ['read' => ['template-context' => 'mailtemplate@id=992',],],
            ],
            [
                "name" => "mailtemplate",
                "key" => "id",
                "records" => 1,
            ],
        ];
        $this->dbInit($dSource, [], ["db-class" => "PDO"], 2);
        $this->dbRead('mailtemplate'); // Dummy read for setting up the Proxy object.

        $proxy = $this->getExtProxy();
        $result = $proxy->userEnrollmentActivateUser($_GET['c'], $password, 'initialPassword');
        return $result;
    }

    public function updateUserRecord(string $userId): void
    {
        $this->dbRead('authuser', ["id" => $userId]);
    }
}

$ermessage = '';
$message = '';
$proc = new DBAccess();
if (count($_GET) > 0) {
    if (!isset($_GET['c']) || strlen($_GET['c']) < 47) {
        $ermessage .= '接続するときのURLが正しくありません。途中で欠けた文字で接続していないか確認してください。';
    } else {
        $password = $proc->generatePassword();
        $userId = $proc->getTargetUserId($password);
        if (!$userId) {
            $ermessage .= '確認しましたが、該当する申し込みがありません。';
        } else {
            $message .= 'アカウントを発行し、そのご案内をメールでお送りしました。';
            $proc->updateUserRecord($userId, $password);
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
<p style="color:red"><?php echo $ermessage; ?></p>
</body>
</html>
