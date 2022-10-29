<?php

class ExportSample extends \INTERMediator\DB\Export
{
    protected $keysAndLabels = [
        "id" => "ID",
        "category_id" => "カテゴリID",
        "unitprice" => "単価",
        "name" => "商品名",
        "taxrate" => "消費税率",
        "photofile" => "画像ファイル名",
        "acknowledgement" => "画像謝辞",
        "ack_link" => "謝辞リンク",
        "memo" => "メモ",
        "user" => "ユーザ",
    ];

//    protected $fileNamePrefix = "Exported-";
//    protected $fileExtension = "csv";
    protected $encoding = "SJIS";
//    protected $fieldSeparator = ',';
//    protected $quote = '"';
//    protected $endOfLine = "\n";

//    public function processing($contextData, $options){}
}