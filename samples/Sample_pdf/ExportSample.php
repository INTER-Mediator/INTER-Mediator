<?php

use INTERMediator\DB\Export;

class ExportSample extends Export
{
    protected array $keysAndLabels = [
        "unitprice" => "単価",
        "name" => "商品名",
        "taxrate" => "消費税率",
        "photofile" => "画像ファイル名",
        "acknowledgement" => "画像謝辞",
        "ack_link" => "謝辞リンク",
//        "memo" => "メモ",
//        "user" => "ユーザ",
        "id" => "ID",
        "category_id" => "カテゴリID",
    ];

//    protected $fileNamePrefix = "Exported-";
//    protected $fileExtension = "csv";
    protected string $encoding = "SJIS";
//    protected $fieldSeparator = ',';
//    protected $quote = '"';
//    protected $endOfLine = "\n";

//    public function processing($contextData, $options){}
}