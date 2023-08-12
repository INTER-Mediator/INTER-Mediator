<?php

/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2017/10/07
 * Time: 12:49
 */

namespace INTERMediator\Media;

use Iterator;

/**
 *
 */
class LineDivider implements Iterator
{
    /**
     * @var string
     */
    private string $data;
    /**
     * @var int
     */
    private int $pos;
    /**
     * @var int
     */
    private int $key;

    /**
     * @param $d
     */
    function __construct($d)
    {
        $this->data = $d;
        $this->pos = 0;
        $this->key = 0;
    }

    /**
     * @return array|int[]
     */
    private function getNextLinePosition(): array
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

    /**
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function current(): string
    {
        list($prevCRLF, $nextPos) = $this->getNextLinePosition();
        if ($prevCRLF > 0) {
            return substr($this->data, $this->pos, $prevCRLF - $this->pos);
        } else {
            return substr($this->data, $this->pos);
        }
    }

    /**
     * @return void
     */
    public function next(): void
    {
        $this->key++;
        list($prevCRLF, $this->pos) = $this->getNextLinePosition();
    }

    /**
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function key(): int
    {
        return $this->key;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        if ($this->pos < 0) {
            return false;
        }
        return strlen($this->data) > $this->pos;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->pos = 0;
        $this->key = 0;
    }
}
