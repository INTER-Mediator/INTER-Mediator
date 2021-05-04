# SAML統合について

- INTER-Mediatorが標準的な場所にインストールされているとします。
- つまり、Webのルートにlibがあり、libの中にsrcがあり、srcの中にINTER-Mediatorのレポジトリをクローンしてあるとします。

## 作業の前提

- SAML IdPがどこかで稼働しているとします。IdPのメタデータが得られているとします。
- lib/src/にINTER-Mediatorをクローンしているとします。そして、composer updateを実行した後だとします。

## 作業手順

- このsaml-configフォルダをlib以下にコピーします。
- gettemplates.phpフォルダを実行すると、simplesamlphpのコードにある設定ファイルをテンプレートを、saml-configフォルダにコピーします。
- config.phpの以下のキーに対する値を変更します。

```
'baseurlpath' => 'saml-trial/lib/src/INTER-Mediator/vendor/simplesamlphp/simplesamlphp/www/',
'technicalcontact_email' => 'your_email',
'secretsalt' => 'your_salt',
'auth.adminpassword' => 'your_admin_pass',
```
  * baseurlpathは、ドメイン名を入れてもいいのですが、Webルートから、simplesamlphp/wwwまでの相対パスを記述します。lib以前は実際のサーバ側のディレクトリ構成に合わせます。本来は、Alias等でwwwを短いパスで参照できる方が望ましいと思われます。
  * secretsaltは、config.phpに生成方法のコマンドがあるので、それを参考にしてください。

- copyconfig.shを実行すると、設定ファイルがSimpleSAMLphpのディレクトリにコピーされます。
- ここで、SPの管理ページに以下のURLでアクセスできます。IdPのメタデータがXMLの場合、「連携」タブにある「XMLをSimpleSMLphpメタデータに変換」を使って、PHPの配列コードに直しておきます。
```
https://ドメイン名/baseurlpathの値
例えば：
https://demo.inter-mediator.com/saml-trial/lib/src/INTER-Mediator/vendor/simplesamlphp/simplesamlphp/www/
```  
- また、SPのメタデータを取得しておきます。「連携」のタブにリンクがあります。これをIdPのクライアントに登録します。SimpleSAMLphpをIdPにしている場合は、saml20-sp-remote.phpファイルにメタデータの配列を追加するのが一般的です。
- saml-configフォルダにあるsaml20-idp-remote.phpを編集し、IdPのメタデータをファイルの末尾に追加します。ファイルはヘッダのコメントだけなので、その下にペーストすればいいです。
- もう一度copyconfig.shを実行して、設定ファイルをSimpleSAMLphpのディレクトリにコピーします。

その他

- lib以下にINTER-Mediatorの最小化コピーを作っている場合は、copyconfig.shスクリプトを修正して、そちらのINTER-Mediator内部のsimplesamlphp側に設定ファイルをコピーしてください。
- 現状、metadataディレクトリに入れるべきファイルのうち、copyconfig.shが対応しているのは、saml20-idp-remote.phpのみです。他のファイルをコピーしたい場合は、スクリプトを修正してください。