<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 2013/05/12
 * Time: 20:39
 * To change this template use File | Settings | File Templates.
 */
function sendPasswordResetMail($address, $username)
{
    require_once("../lib/mailsend/OME.php");

    $ome = new OME();
    $ome->setSendMailParam('-f info@msyk.net');
    $ome->setFromField('info@msyk.net', 'Masayuki Nii');
    $ome->setToField($address);
    $ome->setBccField('info@msyk.net');
    $ome->setSubject('パスワードのリセットを受付ました');
    $ome->setTemplateAsString(<<<EOL
以下のアカウントのパスワードをリセットしました。

アカウント（メールアドレス）：@@1@@

以下のリンクをクリックし、新しいパスワードでマイページにログインしてください。

<< Path to any page >>

___________________________________
info@msyk.net - Masayuki Nii
EOL
    );
    $ome->insertToTemplate(array($address, $username));
    return $ome->send();
}

session_start(); // this MUST be called prior to any output including whitespaces and line breaks!

$message = '';
$cred = '';
if (count($_GET) > 0) {
    if (!isset($_GET['c']) || strlen($_GET['c']) < 10) {
        $message .= '接続するときのURLが正しくありません。途中で欠けた文字で接続していないか確認してください。';
    } else {
        $cred = $_GET['c'];
    }
}
if (count($_POST) > 0) {
    $g_serverSideCall = true;
    require_once('../../INTER-Mediator.php');
    IM_Entry(
        array(),
        array(
            'authentication' => array(
                'email-as-username' => true,
            ),
        ),
        array(
            'db-class' => 'PDO',
        ),
        false
    );
    header('Content-Type: text/html;charset="UTF-8"');
    $result = $g_dbInstance->resetPasswordSequenceReturnBack(
        $_POST['account'], $_POST['mail'], $_POST['cred'], $_POST['hashedpw']);
    $cred = $_POST['cred'];
    if ($result) {
        $message .= '<span style="color:black">';
        $message .= 'パスワードがリセットされました。';
        $message .= '</span>';
        sendPasswordResetMail($_POST['mail'], $_POST['account']);
    } else {
        $message .= 'パスワードのリセット処理に問題が発生しました。';
    }
}
?>
<!DOCTYPE html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
<!--    <link type="text/css" rel="stylesheet" href="default.css"/>-->
    <script src="resetcontext.php"></script>
    <title></title>
    <script type="text/javascript">
        var returnValue = true;
        function leastChecking() {
            returnValue = true;
            alertEmptyField('mail');
            alertEmptyField('pass1');
            alertEmptyField('pass2');
            if (document.getElementById('pass2').value != document.getElementById('pass1').value) {
                document.getElementById('pass2err').innerHTML = '2つのパスワードが一致しません';
                returnValue = false;
            }
            if (returnValue) {
                document.getElementById('hashedpw').value
                    = INTERMediatorLib.generatePasswordHash(document.getElementById('pass2').value);
                document.getElementById('pass1').value = '';
                document.getElementById('pass2').value = '';
            }
            return returnValue;
        }
        function alertEmptyField(fieldId) {
            if (document.getElementById(fieldId).value == '') {
                document.getElementById(fieldId + 'err').innerHTML = '未入力です';
                returnValue = false;
            }
        }
        window.onload = function () {
            document.getElementById("mail").value = getCookie("pwresetmail");
        };
        function getCookie(key) {
            var s, i;
            s = document.cookie.split('; ');
            for (i = 0; i < s.length; i++) {
                if (s[i].indexOf(key + '=') == 0) {
                    return decodeURIComponent(s[i].substring(s[i].indexOf('=') + 1));
                }
            }
            return '';
        }
    </script>
    <style>
        .errormsg {
            color: red;
        }
    </style>
</head>
<body>
<h1>パスワードリセット</h1>

<p class="errormsg"><?php echo $message; ?></p>

<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>"
      onsubmit="return leastChecking();">
    <input id="cred" type="hidden" name="cred" value="<?php echo $cred; ?>"/>
    <input id="hashedpw" type="hidden" name="hashedpw"/>
    <table>
        <tr>
            <th>登録メールアドレス</th>
            <td>
                <input id="mail" type="text" name="mail" size="30"/>
                <span id="mailerr" class="errormsg"></span>
            </td>
        </tr>
        <tr>
            <th>パスワード</th>
            <td>
                <input id="pass1" type="password" name="pass1" size="30"/>
                <span id="pass1err" class="errormsg"></span>
            </td>
        </tr>
        <tr>
            <th>パスワード再入力</th>
            <td>
                <input id="pass2" type="password" name="pass2" size="30"/>
                <span id="pass2err" class="errormsg"></span>
            </td>
        </tr>
        <tr>
            <th></th>
            <td>
                <button type="submit">パスワード再設定</button>
            </td>
        </tr>
    </table>
</form>
</body>
</html>