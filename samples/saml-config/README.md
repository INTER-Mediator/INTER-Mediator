# SAML統合について

- INTER-Mediatorが標準的な場所にインストールされているとします。
- つまり、Webのルートにlibがあり、libの中にsrcがあり、srcの中にINTER-Mediatorのレポジトリをクローンしてあるとします。

## 作業の前提

- SAML IdPがどこかで稼働しているとします。IdPのメタデータが得られているとします。
- lib/src/にINTER-Mediatorをクローンしているとします。あるいは、INTER-Mediatorのアプリケーションテンプレート（IMApp-template）で作ったプロジェクトも想定しています。そして、composer updateを実行した後だとします。

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

- authsource.phpで、default-spキーに対する連想配列で、certificateとprivatekeyキーと値を追加してください。これらは、Webサイトの証明書と秘密鍵のファイル、あるいは独自に生成したファイル名を指定します。この指定により、メタデータに公開鍵情報が付加されます。
- 以下、作った設定ファイルをインストールすcopyconfig.shスクリプトでは、証明書のファイルは、sp.pem、sp.crtと決め打ちになっているので、この名前にしてsaml-configディレクトリにファイルを作っておくのが手軽です。また、サイトの証明書についても、SimpleSAMLphp側にインストールして使うのが良いと思われます。

```
'default-sp' => [
  'saml:SP',
  'certificate' => 'path_to_cert_file',
  'privatekey' => 'path_to_key_file',
   :
```

- copyconfig.shを実行すると、設定ファイルがSimpleSAMLphpのディレクトリ（cert, config, metadata）にコピーされます。
- ここで、SPの管理ページに以下のURLでアクセスできます。baseurlpathの値に続いて、adminというパスを追加してアクセスします。IdPのメタデータがXMLの場合、「連携」タブにある「XMLをSimpleSMLphpメタデータに変換」を使って、PHPの配列コードに直しておきます。
```
https://ドメイン名/baseurlpathの値/admin
例えば：
https://demo.inter-mediator.com/saml-trial/lib/src/INTER-Mediator/vendor/simplesamlphp/simplesamlphp/public/admin
```  
- また、SPの管理ページでは、SPのメタデータを取得しておきます。「連携」のタブにリンクがあります。これをIdPのクライアントに登録します。SimpleSAMLphpをIdPにしている場合は、saml20-sp-remote.phpファイルにメタデータの配列を追加するのが一般的です。
- saml-configフォルダにあるsaml20-idp-remote.phpを編集し、IdPのメタデータをファイルの末尾に追加します。ファイルはヘッダのコメントだけなので、その下にペーストすればいいです。
- もう一度copyconfig.shを実行して、設定ファイルをSimpleSAMLphpのディレクトリにコピーします。

その他

- 設定ファイルのコピースクリプト（gettemplates.shとcopyconfig.sh）は、INTER-MediatorとSimpleSAMLphpの典型的な場所を探してそちらを利用しようとしますが、意図しない結果になる場合は、スクリプトを修正してください。
- SimpleSAMLphp v2.0.4については、[SimpleSAMLphp Ver.2を使ってみる(1)](blog.msyk.net/?p=1566) [SimpleSAMLphp Ver.2を使ってみる(2)](blog.msyk.net/?p=1580) [SimpleSAMLphp Ver.2を使ってみる(3)](blog.msyk.net/?p=1610) も参考にしてください。