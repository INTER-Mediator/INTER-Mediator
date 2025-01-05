# Sample prompt for Windsurf

2024-12-31 Masayuki Nii (nii@msyk.net)

Windsurfを利用して、INTER-Mediatorのアプリケーションを、全く何もないところから（スクラッチから）作成するためのプロンプトです。
以下の「プロンプト例」以降の文字列を与えますが、その時、プロンプト例以降の内容をそのままコピペして実行してみましょう。
マークアップのままで入力するのが良いようです。もちろん、必要ならプロンプト自体の変更もしてOKです。
現状は、SQLiteのデータベースを作成し、一覧と詳細のページを表示するユーザインタフェースを自動生成します。

# プロンプト例
住所録のWebアプリケーションをINTER-Mediatorを利用して作成してください。INTER-Mediatorについては以下の情報を参照してください。

---
# データベーススキーマの定義
- データベースはSQLiteを使用します。
- アプリケーションの目的に応じたデータベーススキーマを定義して、データベースに適用してください。
- スキーマのフィールドにはNOT NULL制約はつけないでください。

# INTER-Mediatorのインストール
- INTER-MediatorはComposerでインストール可能です。識別子は```inter-mediator/inter-mediator```です。バージョンは"*"を指定します。
- Composerでのインストールでは、以下のプラグインの許可が必要です。
  - ```mouf/nodejs-installer```
  - ```simplesamlphp/composer-module-installer```
- インストール後に、vendor/inter-mediator/inter-mediatorに移動して、```npm install```コマンドを実行します。
- その後に、```vendor/inter-mediator/inter-mediator/dist-docs/generateminifyjshere.sh``` スクリプトを実行します。

# INTER-Mediatorインストール後の作業
- プロジェクトのルートにlibディレクトリを作成してください。
- vendor/inter-mediator/inter-mediator/params.phpというファイルを、libにコピーしてください。
- コピーした lib/params.phpファイルの中身を以下のように編集します。
  - \$dbClass変数は、'PDO'を代入します。
  - \$dbUser変数は、データベースに接続する場合のユーザ名を指定します。
  - \$dbPassword変数は、データベースに接続する場合のパスワードを指定します。
  - \$dbDSN変数は、PDOでの接続に必要な接続文字列を指定します。
  - \$dbOption変数は、要素のない配列を代入します。

# 定義ファイルの作成
- 定義ファイルは、ルートにdeffile.phpという名称で、ファイルを作ってください。
- PHPのプログラムとして、```vendor/inter-mediator/inter-mediator/INTER-Mediator.php``` をrequired_once関数で読み込みます。
- その後、IM_Entry関数を呼び出します。
  - IM_Entry関数の1つ目の引数は配列です。配列の要素は、連想配列です。一覧用、詳細表示用の2つの連想配列を用意してください。それぞれ、viewキー、tableキーは、作成したデータベースの主要テーブル名と同一です。nameキーとしては、テーブル名に「_list」を繋げたものと、「_detail」を繋げたものを用意します。
  - _listがついた連想配列には、navi-controlキーで「master-hide」という文字列を指定してください。
  - _listの付いた連想配列では、pagingキーに対して値「true」を指定してください。
  - _listの付いた連想配列では、repeat-controlキーに対して文字列の値「insert-confirm delete-confirm」を指定してください。
  - _listの付いた連想配列では、keyキーに対して主要テーブルのキーフィールド名を指定してください。
  - _detailがついた連想配列には、navi-controlキーで「detail-update」という文字列を指定してください。
  - _detailがついた連想配列では、recordsキーで値は「1」を指定してください。
  - _detailの付いた連想配列では、keyキーに対して主要テーブルのキーフィールド名を指定してください。
  - IM_Entry関数の第2、第3引数は、nullにします。第4引数は2にします。

# ページファイルの作成
- ページファイルは、ルートにapp.htmlという名称で、ファイルを作ってください。
- HTMLファイルの基本構成に従って、HTML、HEAD、BODYタグを挿入してください。
- SCRIPTタグで、deffile.phpを読み込むように設定します。
- BODYタグ内に、DIVタグで、id属性が「IM_NAVIGATOR」という要素を用意します。
- BODYタグ内に、tableタグで表を用意します。この表は、「一覧のテーブル」と呼ぶことにします。
  - 一覧のテーブルには、作成したテーブルのすべてのフィールドが1行に入るようにします。
  - 一覧のテーブルの1行に対して、最初と最後に中身が空白のTDタグを挿入するものとします。
  - 一覧のテーブルでは、データの編集はできないように、フィールドの内容は、TDタグ内部のSPANタグで表示するようにします。
  - 一覧のテーブルのTDタグのセル内にあるSPANタグには、data-im属性を指定して、ターゲット指定として、定義ファイルに指定した一覧用のコンテキストのnameキーの値、続いて「@」、そしてフィールド名を繋げた文字列を指定します。
- BODYタグ内に、さらにtableタグで表を用意します。この表は、「詳細のテーブル」と呼ぶことにします。
  - 詳細のテーブルには、作成したテーブルのすべてのフィールドに対して、1フィールドを1行に表示します。
  - 詳細のテーブルの1行には、フィールド名をTHタグで、フィールドの内容をTDタグで表示しますが、TDタグ内部はINPUTタグを使って編集可能にします。
  - 詳細のテーブルにあるINPUTタグには、data-im属性を指定して、ターゲット指定として、定義ファイルに指定した一覧用のコンテキストのnameキーの値、続いて「@」、そしてフィールド名を繋げた文字列を指定します。

# JSファイルの作成
- ルートに、app.jsという名称でファイルを作ってください。
- 内容は以下の通りです。
```
INTERMediatorOnPage.doBeforeConstruct = function () {
  INTERMediatorLog.suppressDebugMessageOnPage = true
}
```
- ページファイルで、SCRIPTタグを用いて、このapp.jsファイルを読み込みます。
