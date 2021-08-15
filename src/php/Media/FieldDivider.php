<?php

/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2017/10/07
 * Time: 12:49
 */

namespace INTERMediator\Media;

use \Iterator;

class FieldDivider implements Iterator
{
    private $data;
    private $pos;
    private $key;

    private $sep;
    private $sepCode;
    private $sqCode;
    private $dqCode;
    private $bsCode;

    function __construct($d, $sp = ",")
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

    private function getNextLinePosition()
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

    private function escapeRemovingString($str)
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

    public function next(): void
    {
        $this->key++;
        list($startPos, $endPos, $this->pos) = $this->getNextLinePosition();
    }

    public function key()
    {
        return $this->key;
    }

    public function valid(): bool
    {
        if ($this->pos < 0) {
            return false;
        }
        return strlen($this->data) > $this->pos;
    }

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
"Aa,a\"\'a",bbbb,cc\\cc
"A日本語a,a\"\'a",bb英語bb,"cc"cc

 * [result]
<table border='1'><tr><td>Aaaa,bbbb,cccc</td><td>Aaaa</td><td>bbbb</td><td>cccc</td></tr>
<tr><td>"Aaaa",'bbbb',c"cc"c</td><td>Aaaa</td><td>bbbb</td><td>c"cc"c</td></tr>
<tr><td>Aaaa,bbbb,"cccc</td><td>Aaaa</td><td>bbbb</td><td>"cccc</td></tr>
<tr><td>"Aa'aa",bbbb,'cccc</td><td>Aa'aa</td><td>bbbb</td><td>'cccc</td></tr>
<tr><td>"Aa,a\"\'a",bbbb,cc\\cc</td><td>Aa,a"'a</td><td>bbbb</td><td>cc\cc</td></tr>
<tr><td>"A日本語a,a\"\'a",bb英語bb,"cc"cc
</td><td>A日本語a,a"'a</td><td>bb英語bb</td><td>cc</td></tr>
</table>
 */