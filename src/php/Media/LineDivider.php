<?php

/**
 * LineDivider class for splitting string data into lines and iterating over them.
 * Implements the Iterator interface for easy traversal.
 *
 * Created by PhpStorm.
 * User: msyk
 * Date: 2017/10/07
 * Time: 12:49
 */

namespace INTERMediator\Media;

use Iterator;

/**
 * Class LineDivider
 *
 * Splits a string into lines and allows iteration over each line.
 */
class LineDivider implements Iterator
{
    /** The data string to be split into lines.
     * @var string
     */
    private string $data;
    /** The current position in the data string.
     * @var int
     */
    private int $pos;
    /** The current line key (index).
     * @var int
     */
    private int $key;

    /** Constructor.
     * @param string $d The data string to be split into lines.
     */
    function __construct($d)
    {
        $this->data = $d;
        $this->pos = 0;
        $this->key = 0;
    }

    /** Finds the start and end positions of the next line in the data string.
     * @return array Array containing start and end positions of the next line, or -1 if not found.
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
            } else if ($gotEOL) {
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

    /** Returns the current line as a string.
     * @return string The current line.
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

    /** Moves to the next line.
     * @return void
     */
    public function next(): void
    {
        $this->key++;
        list($prevCRLF, $this->pos) = $this->getNextLinePosition();
    }

    /** Returns the current line index.
     * @return int The current line index.
     */
    #[\ReturnTypeWillChange]
    public function key(): int
    {
        return $this->key;
    }

    /** Checks if the current position is valid (not at the end of the data).
     * @return bool True if valid, false otherwise.
     */
    public function valid(): bool
    {
        if ($this->pos < 0) {
            return false;
        }
        return strlen($this->data) > $this->pos;
    }

    /** Rewinds the iterator to the beginning.
     * @return void
     */
    public function rewind(): void
    {
        $this->pos = 0;
        $this->key = 0;
    }
}
