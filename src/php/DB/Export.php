<?php

namespace INTERMediator\DB;

use DateTime;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\Writer;
use League\Csv\CharsetConverter;

/**
 *
 */
class Export
{
    /**
     * @var array
     */
    protected array $keysAndLabels = []; // array of field name => column name

    /**
     * @var string
     */
    protected string $fileNamePrefix = "Exported-";
    /**
     * @var string
     */
    protected string $fileExtension = "csv";
    /**
     * @var string
     */
    protected string $encoding = "UTF-8";
    /**
     * @var string
     */
    protected string $fieldSeparator = ',';
    /**
     * @var string
     */
    protected string $quote = '"';
    /**
     * @var string
     */
    protected string $endOfLine = "\n";

    /**
     * @param array $contextData
     * @param array|null $options
     * @return void
     * @throws CannotInsertRecord
     * @throws Exception
     * @throws InvalidArgument
     */
    public function processing(array $contextData, ?array $options): void
    {
        $qH = '"'; // Double quote in header
        header('Content-Type: application/octet-stream');
        $filename = $this->fileNamePrefix . (new DateTime())->format('Ymd') . ".{$this->fileExtension}";
        header("Content-Disposition: attachment; filename={$qH}{$filename}{$qH}");

        if (count($contextData) < 1) {
            exit;
        }
        $existKeysLabels = count($this->keysAndLabels) > 0;

        $writer = Writer::createFromString();
        CharsetConverter::addTo($writer, 'UTF-8', $this->encoding);
        $writer->setDelimiter($this->fieldSeparator);
        $writer->setEscape('\\');
        $writer->setEnclosure($this->quote);
        $writer->setEndOfLine($this->endOfLine);
        if ($existKeysLabels) {
            $keysArray = array_keys($this->keysAndLabels);
            $fieldArray = array_values($this->keysAndLabels);
            $result = [];
            foreach ($contextData as $record) {
                $newRecord = [];
                foreach ($keysArray as $key) {
                    $newRecord[] = $record[$key];
                }
                $result[] = $newRecord;
            }
            $contextData = $result;
        } else {
            $fieldArray = array_keys($contextData[0]);
        }
        $writer->insertOne($fieldArray);
        $writer->insertAll($contextData);
        echo $writer->toString();
    }

}