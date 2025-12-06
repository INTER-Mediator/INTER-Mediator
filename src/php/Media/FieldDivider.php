<?php

/**
 * FieldDivider class splits a string into fields based on a separator, supporting quoted fields and escape sequences.
 * Implements the Iterator interface for iterating over each field in the input string.
 */
namespace INTERMediator\Media;

use Iterator;

class FieldDivider implements Iterator
{
    /** The input data string to be divided into fields.
     * @var string
     */
    private string $data;
    /** The current parsing position in the data string.
     * @var int
     */
    private int $pos;
    /** The current field key (index).
     * @var int
     */
    private int $key;
    /** The separator string used to divide fields (default is comma).
     * @var string
     */
    private string $sep;
    /** ASCII code of the separator character.
     * @var int
     */
    private int $sepCode;
    /** ASCII code for the single quote (').
     * @var int
     */
    private int $sqCode;
    /** ASCII code for double quote (").
     * @var int
     */
    private int $dqCode;
    /** ASCII code for backslash (\).
     * @var int
     */
    private int $bsCode;

    /** Constructor initializes the FieldDivider with a data string and optional separator.
     * @param string $d The input data string.
     * @param string $sp The field separator (default: ',').
     */
    function __construct(string $d, string $sp = ",")
    {
        $this->sep = $sp;
        $this->sepCode = ord($this->sep);
        $this->sqCode = ord("'");
        $this->dqCode = ord('"');
        $this->bsCode = ord('\\');
        $this->data = $d;
        $this->pos = 0;
        $this->key = 0;
    }

    /** Finds the position of the next field separator, considering quotes and escapes.
     * @return array Tuple of (start position, end position, next position) for the next field.
     */
    private function getNextLinePosition(): array
    {
        $gotSep = false;
        $isInQuote = false;
        $outOfQuote = false;
        $startPos = $this->pos;
        $endPos = -1;
        $qCode = -1;
        for ($i = $this->pos; $i < strlen($this->data); $i++) {
            $c = ord(substr($this->data, $i, 1));
            if ($i == $this->pos && ($c == $this->sqCode || $c == $this->dqCode)) {
                $isInQuote = true;
                $qCode = $c;
                $startPos = $i + 1;
            } else if ($isInQuote && $c == $qCode &&
                ord(substr($this->data, $i - 1, 1)) != $this->bsCode
            ) {
                $isInQuote = false;
                $outOfQuote = true;
                $endPos = $i;
            } else if (!$isInQuote && $c == $this->sepCode) {
                $gotSep = true;
                break;
            }
        }
        if ($i == strlen($this->data)) {
            $gotSep = true;
        }
        if ($outOfQuote && $gotSep) {
            return array($startPos, $endPos, $i + 1);
        } else if ($outOfQuote) {
            return array($startPos, $endPos, -1);
        } else if ($gotSep) {
            return array($this->pos, $i, $i + 1);
        } else {
            return array($this->pos, strlen($this->data), -1);
        }
    }

    /** Removes escape characters from a string.
     * @param string $str The string to process.
     * @return string The string with escape characters removed.
     */
    private function escapeRemovingString(string $str): string
    {
        $bsCode = ord('\\');
        $result = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $c = ord(substr($str, $i, 1));
            $p = ($i > 0) ? ord(substr($str, $i - 1, 1)) : -1;
            if ($c != $bsCode || $p == $this->bsCode) {
                $result .= chr($c);
            }
        }
        return $result;
    }

    /** Returns the current field value in the iteration.
     * @return string|null The current field value, or null if none.
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        list($startPos, $endPos, $nextPos) = $this->getNextLinePosition();
        if ($nextPos > 0) {
            return $this->escapeRemovingString(substr($this->data, $startPos, $endPos - $startPos));
        } else if ((strlen($this->data) - $this->pos) > 0) {
            return $this->escapeRemovingString(substr($this->data, $startPos));
        }
        return null;
    }

    /** Moves the iterator to the next field.
     * @return void
     */
    public function next(): void
    {
        $this->key++;
        list($startPos, $endPos, $this->pos) = $this->getNextLinePosition();
    }

    /** Returns the current field index (key).
     * @return int The current field index.
     */
    #[\ReturnTypeWillChange]
    public function key(): int
    {
        return $this->key;
    }

    /** Checks if the current position is valid for iteration.
     * @return bool True if valid, false otherwise.
     */
    public function valid(): bool
    {
        if ($this->pos < 0) {
            return false;
        }
        return strlen($this->data) > $this->pos;
    }

    /** Resets the iterator to the first field.
     * @return void
     */
    public function rewind(): void
    {
        $this->pos = 0;
        $this->key = 0;
    }
}

/*
 * [Test Data]
Aaaa,bbbb,cccc
"Aaaa",'bbbb',c"cc"c
Aaaa,bbbb,"cccc
"Aa'aa",bbbb,'cccc
"Aa,a\"'a",bbbb,cc\\cc
"A日本語a,a\"'a",bb英語bb,"cc"cc

 * [result]
<table border='1'><tr><td>Aaaa,bbbb,cccc</td><td>Aaaa</td><td>bbbb</td><td>cccc</td></tr>
<tr><td>"Aaaa",'bbbb',c"cc"c</td><td>Aaaa</td><td>bbbb</td><td>c"cc"c</td></tr>
<tr><td>Aaaa,bbbb,"cccc</td><td>Aaaa</td><td>bbbb</td><td>"cccc</td></tr>
<tr><td>"Aa'aa",bbbb,'cccc</td><td>Aa'aa</td><td>bbbb</td><td>'cccc</td></tr>
<tr><td>"Aa,a\"'a",bbbb,cc\\cc</td><td>Aa,a"'a</td><td>bbbb</td><td>cc\cc</td></tr>
<tr><td>"A日本語a,a\"'a",bb英語bb,"cc"cc
</td><td>A日本語a,a"'a</td><td>bb英語bb</td><td>cc</td></tr>
</table>
 */