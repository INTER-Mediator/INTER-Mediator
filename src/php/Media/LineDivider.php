<?php

/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2017/10/07
 * Time: 12:49
 */

namespace INTERMediator\Media;
use \Iterator;

class LineDivider implements Iterator
{
    private $data;
    private $pos;
    private $key;

    function __construct($d)
    {
        $this->data = $d;
        $this->pos = 0;
        $this->key = 0;
    }

    private function getNextLinePosition()
    {
        $gotEOL = false;
        $hasNextLine = false;
        $startNextLine = -1;
        for ($i = $this->pos; $i < strlen($this->data); $i++) {
            $c = ord(substr($this->data, $i, 1));
            if ($c == 10 || $c == 13) {
                $gotEOL = true;
                if ($startNextLine == -1) {
                    $startNextLine = $i;
                }
            } else if ($gotEOL && $c != 10 && $c != 13) {
                $hasNextLine = true;
                break;
            }
        }
        if ($hasNextLine) {
            return array($startNextLine, $i);
        } else if ($startNextLine > 0) {
            return array($startNextLine, -1);
        } else {
            return array(-1, -1);
        }
    }

    public function current()
    {
        list($prevCRLF, $nextPos) = $this->getNextLinePosition();
        if ($prevCRLF > 0) {
            return substr($this->data, $this->pos, $prevCRLF - $this->pos);
        } else {
            return substr($this->data, $this->pos);
        }
    }

    public function next()
    {
        $this->key++;
        list($prevCRLF, $this->pos) = $this->getNextLinePosition();
    }

    public function key()
    {
        return $this->key;
    }

    public function valid()
    {
        if ($this->pos<0)   {
            return false;
        }
        return strlen($this->data) > $this->pos;
    }

    public function rewind()
    {
        $this->pos = 0;
        $this->key = 0;
    }
}
