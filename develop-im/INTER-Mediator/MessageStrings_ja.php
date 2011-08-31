<?php 
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
class MessageStrings_ja	{

function getMessages()	{
	return $this->messages;
}

var $messages = array(
	1	=>	'レコード番号',
	2	=>	'更新',
	3	=>	'レコード追加',
	4	=>	'レコード削除',
	5	=>	'追加',
	6	=>	'削除',
    1001 => "他のユーザによってこのフィールドの値が変更された可能性があります。\n\n初期値=@1@\n現在の値=@2@\n\nOKボタンをクリックすれば、現在の値を保存します。",
    1002 => "テーブル名を決定できません: @1@",
	);
}
?>