<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 2013/05/12
 * Time: 17:54
 * To change this template use File | Settings | File Templates.
 */
session_start(); // this MUST be called prior to any output including whitespaces and line breaks!

function sendPasswordResetMail($address, $cred)
{
    require_once("../../lib/mailsend/OME.php");

    $ome = new OME();
    $ome->setSendMailParam('-f info@msyk.net');
    $ome->setFromField('info@msyk.net', 'Masayuki Nii');
    $ome->setToField($address);
    $ome->setBccField('info@msyk.net');
    $ome->setSubject('パスワードのリセットを受付ました');
    $ome->setTemplateAsString(<<<EOL
パスワードのリセットを受け付けました。

メールアドレス：@@1@@

以下のリンクをクリックし、新しいパスワードをご入力ください。

<<Path to Auth_Support folder>>/resetpassword.php?c=@@2@@

___________________________________
info@msyk.net - Masayuki Nii
EOL
    );
    $ome->insertToTemplate(array($address, $cred));
    return $ome->send();
}

$message = '';
$mail = '';
$account = '';
if (count($_POST) > 0) {
    if ($_POST['ad1'] != $_POST['ad2']) {
        $message .= '2つのメールアドレスが異なっています。';
    } else {
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
        $result = $g_dbInstance->resetPasswordSequenceStart($_POST['ad1']);
        //$g_dbInstance->finishCommunication(true);
        if ($result === false) {
            $message .= 'パスワードのリセット処理に問題が発生しました。登録されたメールアドレスでない可能性があります。';
        } else {
            if (sendPasswordResetMail($_POST['ad1'], $result['randdata'])) {
                $message .= '<span style="color:black">';
                $message .= 'パスワードのリセットをご案内するメールが、指定されたメールアドレスに送信されました。';
                $message .= '</span>';
                $mail = $_POST['ad1'];
                $account = $result['username'];
            } else {
                $message .= 'パスワードのリセット処理に問題が発生しました。';
            }
        }
    }
}

header('Content-Type: text/html;charset="UTF-8"');
header('Cache-Control: no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
header('Expires: 0');

?>
<!DOCTYPE html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
<!--    <link type="text/css" rel="stylesheet" href="default.css"/>-->
    <title></title>
    <script type="text/javascript">
        document.cookie = "pwresetmail=" + encodeURIComponent("<?php echo $mail; ?>");
        document.cookie = "pwresetaccount=" + encodeURIComponent("<?php echo $account; ?>");
        function leastChecking() {
            var returnValue = true;
            if (document.getElementById('ad1').value == '') {
                document.getElementById('ad1err').innerHTML = '未入力です';
                returnValue = false;
            }
            if (document.getElementById('ad2').value == '') {
                document.getElementById('ad2err').innerHTML = '未入力です';
                returnValue = false;
            }
            if (document.getElementById('ad1').value != document.getElementById('ad2').value) {
                document.getElementById('add2err').innerHTML = '2つのメールアドレスが一致しません';
                returnValue = false;
            }
            return returnValue;
        }
    </script>
    <style>
        .errormsg {
            color: red;
        }
    </style>
</head>
<body>
<h1>パスワードのリセット要求</h1>

<p class="errormsg"><?php echo $message; ?></p>

<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <table>
        <tr>
            <th>メールアドレス</th>
            <td>
                <input id="ad1" type="text" name="ad1" size="40"/>
                <span id="ad1err" class="errormsg"></span>
            </td>
        </tr>
        <tr>
            <th>メールアドレス再入力</th>
            <td>
                <input id="ad2" type="text" name="ad2" size="40"/>
                <span id="ad2err" class="errormsg"></span>
            </td>
        </tr>
        <tr>
            <th></th>
            <td>
                <button onclick="return leastChecking();">リセット案内メール送信</button>
            </td>
        </tr>
    </table>
</form>

</body>
</html>