<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 2013/05/12
 * Time: 17:54
 * To change this template use File | Settings | File Templates.
 */
session_start(); // this MUST be called prior to any output including whitespaces and line breaks!

$message = '';
$mail = '';
$account = '';
// http://phpspot.net/php/pg%E6%AD%A3%E8%A6%8F%E8%A1%A8%E7%8F%BE%EF%BC%9A%E3%83%A1%E3%83%BC%E3%83%AB%E3%82%A2%E3%83%89%E3%83%AC%E3%82%B9%E3%81%8B%E3%81%A9%E3%81%86%E3%81%8B%E8%AA%BF%E3%81%B9%E3%82%8B.html
$pattern = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";
if (count($_POST) > 0) {
    if ($_POST['ad1'] != $_POST['ad2']) {
        $message .= '2つのメールアドレスが異なっています。';
    } else if (preg_match($pattern, $_POST['ad1']) !== 1) {
        $message .= 'メールアドレスの形式が正しくありません。';
    } else {
        require_once('../../INTER-Mediator.php');   // Set the valid path to INTER-Mediator.php
        $dbInstance = new DB_Proxy();
        $dbInstance->initialize(
            array(),
            array(
                'authentication' => array(
                    'email-as-username' => true,
                ),
            ),
            array("db-class" => "PDO" /* or "FileMaker_FX" */),
            2);
        $result = $dbInstance->resetPasswordSequenceStart($_POST['ad1']);

        if ($result === false) {
            $message .= 'パスワードのリセット処理に問題が発生しました。登録されたメールアドレスでない可能性があります。';
        } else {
            $dbInstance = new DB_Proxy();
            $dbInstance->initialize(
                array(
                    array(
                        "name" => "authuser",
                        "view" => "authuser",
                        "table" => "dummydummy",
                        "records" => 1,
                        "query" => array(
                            // For MySQL, PostgreSQL, SQLite
                            array("field" => "email", "operator" => "=", "value" => $_POST['ad1']),
                            // For FileMaker Server
//                            array(
//                                "field" => "email",
//                                "operator" => "=",
//                                "value" => str_replace("@", "\\@", $_POST['ad1'])
//                            ),
                        ),
                        'send-mail' => array(
                            'read' => array(
                                'to' => 'email',
                                'bcc' => 'info@msyk.net',
                                'subject-constant' => 'パスワードのリセットを受付ました',
                                'from-constant' => 'Masayuki Nii <info@msyk.net>',
                                'body-template' => 'requestmail.txt',
                                'body-fields' => "@{$_POST['ad1']},@{$result['randdata']}",
                                'f-option' => true,
                                'body-wrap' => 78,
                            ),
                        ),
                    ),
                ),
                array(),
                array("db-class" => "PDO" /* or "FileMaker_FX" */),
                2,
                "authuser");
            $dbInstance->processingRequest("read");

            $message .= '<span style="color:black">';
            $message .= 'パスワードのリセットをご案内するメールが、指定されたメールアドレスに送信されました。';
            $message .= '</span>';
            $mail = $_POST['ad1'];
            $account = $result['username'];
        }
    }
}

header('Content-Type: text/html;charset="UTF-8"');
header('Cache-Control: no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
header('Expires: 0');

?>
<!DOCTYPE html>
<html>
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