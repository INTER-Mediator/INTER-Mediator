<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
*
*   This project started at the end of 2009.
*   INTER-Mediator is supplied under MIT License.
*/
class MessageStrings_ja
{

    function getMessages()
    {
        return $this->messages;
    }

    var $messages = array(
        1 => 'レコード番号',
        2 => '更新',
        3 => 'レコード追加',
        4 => 'レコード削除',
        5 => '追加',
        6 => '削除',
        1001 => "他のユーザによってこのフィールドの値が変更された可能性があります。\n\n初期値=@1@\n現在の値=@2@\n\nOKボタンをクリックすれば、現在の値を保存します。",
        1002 => "テーブル名を決定できません: @1@",
        1003 => "更新に必要な情報が残されていません: フィールド名=@1@",
        1005 => "db_query関数の呼び出しで、必須のプロパティ'name'が指定されていません",
        1006 => "リンクノードの設定に正しくないものがあります：@1@",
        1007 => "db_update関数の呼び出しで、必須のプロパティ'name'が指定されていません",
        1008 => "db_update関数の呼び出しで、必須のプロパティ'conditions'が指定されていません",
        1009 => "",
        1010 => "",
        1011 => "db_update関数の呼び出しで、必須のプロパティ'dataset'が指定されていません",
        1012 => "クエリーアクセス: ",
        1013 => "更新アクセス: ",
        1004 => "db_query関数での通信時のエラー=@1@/@2@",
        1014 => "db_update関数での通信時のエラー=@1@/@2@",
        1015 => "db_delete関数での通信時のエラー=@1@/@2@",
        1016 => "db_createRecord関数での通信時のエラー=@1@/@2@",
        1017 => "削除アクセス: ",
        1018 => "新規レコードアクセス: ",
        1019 => "db_delete関数の呼び出しで、必須のプロパティ'name'が指定されていません",
        1020 => "db_delete関数の呼び出しで、必須のプロパティ'conditions'が指定されていません",
        1021 => "db_createRecord関数の呼び出しで、必須のプロパティ'name'が指定されていません",
        1022 => 'ご使用のWebブラウザには対応していません。',
        1023 => '[このサイトはINTER-Mediatorを利用して構築しています。]',
    );
}

?>