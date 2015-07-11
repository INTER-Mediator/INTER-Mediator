<!DOCTYPE html>
<?php $version = '5.2-dev'; ?>
<!--
/*
 * INTER-Mediator Server VM for Trial
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */  -->
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>INTER-Mediator <?php echo htmlspecialchars($version, ENT_QUOTES, 'UTF-8'); ?> - VM for Trial</title>
    <link href="/INTER-Mediator/Samples/sample.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/INTER-Mediator/dist-docs/vm-for-trial/include_MySQL.php"></script>
    <script type="text/javascript" src="/INTER-Mediator/dist-docs/vm-for-trial/index.js"></script>
</head>
<body>
<h1>INTER-Mediator <?php echo htmlspecialchars($version, ENT_QUOTES, 'UTF-8'); ?> - VM for Trial<span><?php echo htmlspecialchars(exec('date -d "`git --git-dir=/var/www/html/INTER-Mediator/.git log -1 | grep Date: | awk \'{print $2,$3,$4,$5,$6}\'`" +（%Y年%-m月%-d日更新）'), ENT_QUOTES, 'UTF-8'); ?></span></h1>

<h2>現在アクセスしているマシンについて</h2>

<p>このVirtual Machineは、INTER-Mediatorが動作するサーバを試用したり、あるいはINTER-MediatorによるWebアプリケーション開発を学習するために作成したものです。サンプルデータベースはスキーマを読み込ませており、すぐに動作を見ることができます。</p>

<h3>注意点など</h3>
<ul>
    <li>利便性のために、パスワードなどの情報はこのページに記載しています。</li>
    <li>原則として、稼働マシン以外からのアクセスができない状態で利用してください。</li>
</ul>

<h2>リンク</h2>

<p><a href="/INTER-Mediator/Samples/" target="_blank">サンプルプログラム</a></p>
<ul>
    <li>サンプルの中にある認証ユーザー用のデータベースには、user1〜user5の5つのユーザーが定義されており、パスワードはユーザー名と同一です。概ね、user1でログインができますが、アクセス権の設定のテストも行っており、すべてのユーザーでのログインができるとは限りません。設定を参照の上ログインの確認や、あるいはできないことの確認をしてください。</li>
    <li>FileMaker向けのサンプルプログラムはホストマシンで、FileMaker Serverが稼働している場合で、このVMのネットワークを「ホストオンリーアダプター」にしていれば、おそらくそのまま稼働します。他のホストや異なるネットワーク設定の場合は、/var/www/html/params.phpファイルの、$dbServer変数の値を変更してください。</li>
    <li><strong>サンプルデータベースの最終更新日</strong>：MySQL=<?php system('date -d "`git --git-dir=/var/www/html/INTER-Mediator/.git log -1 -- -p dist-docs/sample_schema_mysql.txt | grep Date: | awk \'{print $2,$3,$4,$5,$6}\'`" +%Y年%-m月%-d日') ?>、FileMaker=<?php system('date -d "`git --git-dir=/var/www/html/INTER-Mediator/.git log -1 -- -p dist-docs/TestDB.fmp12 | grep Date: | awk \'{print $2,$3,$4,$5,$6}\'`" +%Y年%-m月%-d日') ?><br><strong>あなたがお使いのサンプルデータベース</strong>：MySQL=<span data-im="information@lastupdated"></span><?php
        $filePath = '/var/www/html/params.php';
        try {
            if (file_exists($filePath)) {
                include($filePath);
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, strtolower($dbProtocol) . '://' . $dbServer . ':' . $dbPort . '/fmi/xml/fmresultset.xml');
            curl_setopt($ch, CURLOPT_USERPWD, $dbUser . ':' . $dbPassword);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '-db=' . $dbDatabase . '&-lay=information&-findall&-max=1');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $xml = curl_exec($ch);
            curl_close($ch);
            libxml_use_internal_errors(true);
            $parsedData = simplexml_load_string($xml);
            if ($parsedData === false) {
            }
            require_once('/var/www/html/INTER-Mediator/DataConverter_FMDateTime.php');
            $converter = new DataConverter_FMDateTime();
            error_reporting(0);
            foreach ($parsedData->resultset->record->field as $key => $field) {
                if ((string)$field->attributes()->name === 'lastupdated') {
                    $dateInfo = $converter->dateArrayFromFMDate($field->data);
                    echo '、FileMaker=' . intval($dateInfo['year']) . '年' .
                        intval($dateInfo['month']) . '月' .
                        intval($dateInfo['day']) . '日';
                    break;
                }
            }
        } catch (Exception $e) {
        }
    ?></span></li>
</ul>

<p><a href="/INTER-Mediator/Auth_Support/MySQL_accountmanager.html" target="_blank">ユーザー管理ページサンプル</a></p>
<ul>
    <li>ユーザー名、パスワード共に、user1でログインができますが、通常の利用は、利用者と別の管理者を作り、その管理者でのみログインできるようにします。</li>
</ul>

<p><a href="/INTER-Mediator/dist-docs/vm-for-trial/info.php" target="_blank">phpinfo()関数の実行</a></p>

<p>
    <a href="http://inter-mediator.com/" target="_blank">INTER-Mediator Site</a>|
    <a href="http://inter-mediator.info/" target="_blank">Manual</a>|
    <a href="http://inter-mediator.org/" target="_blank">Committee</a>
</p>


<h2>トライアル用のページファイルと定義ファイル</h2>

<p>
    以下のリンクは、Webサーバのルートに配置したファイルで、ページファイルエディタと定義ファイルエディタで開いて内容を編集し、その結果を参照することができます。いずれのリンクも、別のウインドウないしはタブを開きます。ページ更新が必要なときには手作業で行ってください。初期状態では何も表示しないようになっています。もちろん、独自に変更を加えて、自由に使ってみてください。</p>

<table style="float:left;">
<tr>
    <td><a href="page01.html" target="_blank">page01.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page01.html" target="_blank">
            page01.htmlを編集する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def01.php" target="_blank">
            def01.phpを編集する</a></td>
</tr>
<tr>
    <td><a href="page02.html" target="_blank">page02.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page02.html" target="_blank">
            page02.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def02.php" target="_blank">
            def02.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page03.html" target="_blank">page03.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page03.html" target="_blank">
            page03.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def03.php" target="_blank">
            def03.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page04.html" target="_blank">page04.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page04.html" target="_blank">
            page04.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def04.php" target="_blank">
            def04.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page05.html" target="_blank">page05.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page05.html" target="_blank">
            page05.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def05.php" target="_blank">
            def05.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page06.html" target="_blank">page06.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page06.html" target="_blank">
            page06.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def06.php" target="_blank">
            def06.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page07.html" target="_blank">page07.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page07.html" target="_blank">
            page07.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def07.php" target="_blank">
            def07.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page08.html" target="_blank">page08.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page08.html" target="_blank">
            page08.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def08.php" target="_blank">
            def08.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page09.html" target="_blank">page09.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page09.html" target="_blank">
            page09.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def09.php" target="_blank">
            def09.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page10.html" target="_blank">page10.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page10.html" target="_blank">
            page10.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def10.php" target="_blank">
            def10.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page11.html" target="_blank">page11.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page11.html" target="_blank">
            page11.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def11.php" target="_blank">
            def11.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page12.html" target="_blank">page12.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page12.html" target="_blank">
            page12.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def12.php" target="_blank">
            def12.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page13.html" target="_blank">page13.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page13.html" target="_blank">
            page13.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def13.php" target="_blank">
            def13.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page14.html" target="_blank">
        page14.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page14.html" target="_blank">
            page14.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def14.php" target="_blank">
            def14.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page15.html" target="_blank">page15.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page15.html" target="_blank">
            page15.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def15.php" target="_blank">
            def15.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page16.html" target="_blank">page16.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page16.html" target="_blank">
            page16.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def16.php" target="_blank">
            def16.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page17.html" target="_blank">page17.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page17.html" target="_blank">
            page17.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def17.php" target="_blank">
            def17.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page18.html" target="_blank">page18.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page18.html" target="_blank">
            page18.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def18.php" target="_blank">
            def18.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page19.html" target="_blank">page19.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page19.html" target="_blank">
            page19.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def19.php" target="_blank">
            def19.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page20.html" target="_blank">page20.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page20.html" target="_blank">
            page20.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def20.php" target="_blank">
            def20.phpを編集する</a>
    </td>
</tr>
</table>

<table style="float:left;">
<tr>
    <td><a href="page21.html" target="_blank">page21.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page21.html" target="_blank">
            page21.htmlを編集する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def21.php" target="_blank">
            def21.phpを編集する</a></td>
</tr>
<tr>
    <td><a href="page22.html" target="_blank">page22.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page22.html" target="_blank">
            page22.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def22.php" target="_blank">
            def22.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page23.html" target="_blank">page23.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page23.html" target="_blank">
            page23.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def23.php" target="_blank">
            def23.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page24.html" target="_blank">page24.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page24.html" target="_blank">
            page24.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def24.php" target="_blank">
            def24.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page25.html" target="_blank">page25.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page25.html" target="_blank">
            page25.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def25.php" target="_blank">
            def25.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page26.html" target="_blank">page26.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page26.html" target="_blank">
            page26.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def26.php" target="_blank">
            def26.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page27.html" target="_blank">page27.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page27.html" target="_blank">
            page27.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def27.php" target="_blank">
            def21.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page28.html" target="_blank">page28.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page28.html" target="_blank">
            page28.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def28.php" target="_blank">
            def28.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page29.html" target="_blank">page29.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page29.html" target="_blank">
            page29.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def29.php" target="_blank">
            def29.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page30.html" target="_blank">page30.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page30.html" target="_blank">
            page30.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def30.php" target="_blank">
            def30.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page33.html" target="_blank">page33.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page33.html" target="_blank">
            page33.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def33.php" target="_blank">
            def33.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page32.html" target="_blank">page32.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page32.html" target="_blank">
            page32.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def32.php" target="_blank">
            def32.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page33.html" target="_blank">page33.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page33.html" target="_blank">
            page33.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def33.php" target="_blank">
            def33.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page34.html" target="_blank">
        page34.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page34.html" target="_blank">
            page34.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def34.php" target="_blank">
            def34.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page35.html" target="_blank">page35.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page35.html" target="_blank">
            page35.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def35.php" target="_blank">
            def35.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page36.html" target="_blank">page36.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page36.html" target="_blank">
            page36.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def36.php" target="_blank">
            def36.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page37.html" target="_blank">page37.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page37.html" target="_blank">
            page37.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def37.php" target="_blank">
            def37.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page38.html" target="_blank">page38.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page38.html" target="_blank">
            page38.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def38.php" target="_blank">
            def38.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page39.html" target="_blank">page39.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page39.html" target="_blank">
            page39.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def39.php" target="_blank">
            def39.phpを編集する</a>
    </td>
</tr>
<tr>
    <td><a href="page40.html" target="_blank">page40.htmlを表示する</a></td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/pageedit.html?target=../../page40.html" target="_blank">
            page40.htmlを編集する</a>
    </td>
    <td>
        <a href="/INTER-Mediator/INTER-Mediator-Support/defedit.html?target=../../def40.php" target="_blank">
            def40.phpを編集する</a>
    </td>
</tr>
</table>

<br clear="all"/>

<h2>サーバ構築情報</h2>

<h3>このVMのアカウント</h3>

<div class="table">
    <table>
        <tr>
            <th>種類</th>
            <th>ユーザー名</th>
            <th>パスワード</th>
            <th>備考</th>
        </tr>
        <tr>
            <td>ログインアカウント</td>
            <td>developer</td>
            <td>im4135dev</td>
            <td>sudoによりルート権限取得可能</td>
        </tr>
        <tr>
            <td>MySQL</td>
            <td>root@localhost</td>
            <td>im4135dev</td>
            <td>プロセスの稼働ユーザーはmysql</td>
        </tr>
        <tr>
            <td>PostgreSQL</td>
            <td>postgres</td>
            <td>im4135dev</td>
            <td>プロセスの稼働ユーザーはpostgres</td>
        </tr>
        <tr>
            <td>Apache2</td>
            <td></td>
            <td></td>
            <td>プロセスの稼働ユーザーはwww-data</td>
        </tr>
    </table>
</div>

<h3>VMに関する情報</h3>
<ul>
    <li>OS：Ubuntu Server 14.04.2</li>
    <li>インストール言語：English/en_EN.UTF-8</li>
    <li>キーボード：Japanese</li>
    <li>タイムゾーン：Asia/Tokyo</li>
    <li>ホスト名：inter-mediator-server</li>
    <li>Webサーバルート：/var/www/html</li>
    <li>初期設定：OpenSSH Server, LAMP Server, Mail Server, PostgreSQL database</li>
    <li>アクセス方法：SSH、SFTP、HTTP、SMB</li>
    <li>作成グループ：im-developer（developerおよびwww-dataが所属）</li>
</ul>

<h3>インストール後に実行したコマンド</h3>
<p>INTER-Mediatorの中にある、<a href="https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/vm-for-trial/deploy.sh">deploy.sh</a>を実行しました。</p>

<h3>テストの実行方法</h3>
<p>VM上で下記のコマンドを実行すると、INTER-Mediatorのテストを実行できます。</p>
<ul>
    <li>phpunit /var/www/html/INTER-Mediator/INTER-Mediator-UnitTest/INTERMediator_AllTests.php</li>
    <!--
    <li>buster-server &amp;</li>
    <li>phantomjs /usr/local/lib/node_modules/buster/script/phantom.js http://localhost:1111/capture &amp;</li>
    -->
    <li>/usr/local/bin/buster-test -r specification -c /var/www/html/INTER-Mediator/buster.js</li>
</ul>

</body>
</html>
