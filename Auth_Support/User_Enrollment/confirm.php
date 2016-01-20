<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 2013/05/29
 * Time: 2:11
 * To change this template use File | Settings | File Templates.
 */

$message = '';
$ermessage = '';
if ( count( $_GET ) > 0 )  {
    if ( ! isset($_GET['c']) || strlen($_GET['c']) < 10 )  {
        $ermessage .= '接続するときのURLが正しくありません。途中で欠けた文字で接続していないか確認してください。';
    } else {

        $seed = '234578ABDEFGHJLMNPRTUYadefghprty';
        $password = '';
        for ($i = 0; $i < 6; $i++) {
            $n = rand(0, strlen($seed) - 1);
            $password .= substr($seed, $n, 1);
        }

        $g_serverSideCall = true;
        require_once('../../INTER-Mediator.php');
        IM_Entry(
            array(),
            null,
            array('db-class' => 'PDO'),
            false
        );
        header('Content-Type: text/html;charset="UTF-8"');
        $result = $g_dbInstance->userEnrollmentActivateUser($_GET['c'], $password);
        $g_dbInstance->finishCommunication(true);

        if ( $result === false )  {
            $ermessage .= '確認しましたが、該当する申し込みがありません。';
        } else {
            $message .= 'アカウントを発行し、そのご案内をメールでお送りしました。';

            require_once("../../lib/mailsend/OME.php");

            $ome = new OME();
            $ome->setSendMailParam('-f info@msyk.net');
            $ome->setFromField('info@msyk.net', 'Masayuki Nii');
            $ome->setToField($result['email']);
            $ome->setBccField('info@msyk.net');
            $ome->setSubject('ユーザ登録を受け付けました');
            $ome->setTemplateAsString(<<<EOL
@@2@@ 様（@@1@@）

ユーザ登録が完了しました。こちらのページにログインできるようになりました。

ログインページ：
<< URL to any page >>

ユーザ名： @@1@@
初期パスワード： @@3@@

※ 初期パスワードは極力早めに変更してください。
___________________________________
info@msyk.net - Masayuki Nii
EOL
            );
            $ome->insertToTemplate(array(
                $result['email'],
                $result['realname'],
                $password,
            ));
            if( ! $ome->send() )    {
                $ermessage .= 'メール送信エラー';
            };
        }
    }
}



?>
<!DOCTYPE html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
    <title>登録確認</title>
<body>
<h1>登録確認</h1>
    <p style="color:black;font-weight:900"><?php echo $message ?></p>
    <p style="color:red"><?php echo $ermessage;/* echo $errors; */?></p>
    </div>
</body>
</html>
