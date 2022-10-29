<?php

namespace INTERMediator\DB;

use League\Csv\Writer;
use League\Csv\CharsetConverter;

class Export
{
    protected $keysAndLabels = []; // array of field name => column name

    protected $fileNamePrefix = "Exported-";
    protected $fileExtension = "csv";
    protected $encoding = "UTF-8";
    protected $fieldSeparator = ',';
    protected $quote = '"';
    protected $endOfLine = "\n";

    public function processing($contextData, $options)
    {
        $qH = '"'; // Double quote in header
        header('Content-Type: data:application/octet-stream');
        $filename = $this->fileNamePrefix . (new \DateTime())->format('Ymd') . ".{$this->fileExtension}";
        header("Content-Disposition: attachment; filename={$qH}{$filename}{$qH}");

        if (count($contextData) < 1) {
            exit;
        }

        $writer = Writer::createFromString();
        CharsetConverter::addTo($writer, 'UTF-8',$this->encoding, );
        $writer->setDelimiter($this->fieldSeparator);
        $writer->setEscape('\\');
        $writer->setEnclosure($this->quote);
        $writer->setNewline($this->endOfLine);
        $fieldArray = array_values($this->keysAndLabels);
        if (count($fieldArray) == 0) {
            $fieldArray = array_keys($contextData[0]);
        }
        $writer->insertOne($fieldArray);
        $writer->insertAll($contextData);
        echo $writer->toString();
    }

}