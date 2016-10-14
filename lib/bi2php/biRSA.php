<?php
/*
The MIT License

Copyright (c)2009 Андрій Овчаренко (Andrey Ovcharenko)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
// bi2php v0.1.113.alfa from http://code.google.com/p/bi2php/

class biRSAKeyPair
{
    var $e;
    var $d;
    var $m;

    function __construct($encryptionExponent, $decryptionExponent, $modulus)
    {
        $this->e = self::biFromHex($encryptionExponent);
        $this->d = self::biFromHex($decryptionExponent);
        $this->m = self::biFromHex($modulus);
        // We can do two bytes per digit, so
        // chunkSize = 2 * (number of digits in modulus - 1).
        // Since biHighIndex returns the high index, not the number of digits, 1 has
        // already been subtracted.
        $count = 0;
        $r = $this->m;
        while ($r !== "0") {
            $r = bcdiv($r, '65536', 0);
            $count++;
        }
        $this->chunkSize = ($count - 1) * 2;
    }

    function biEncryptedString($s, $utf_encoded = FALSE)
    {
        if ($utf_encoded)
            $s = utf8_encode($s);
        $s = str_replace(chr(0), chr(255), $s);
        $s .= chr(254);
        $sl = strlen($s);
        $s = $s . self::biRandomPadding($this->chunkSize - $sl % $this->chunkSize);
        $sl = strlen($s);
        $result = '';
        $split = '';
        for ($i = 0; $i < $sl; $i += $this->chunkSize) {
            $block = "0";
            $faktor = "1";
            $j = 0;
            for ($k = $i; $k < $i + $this->chunkSize; ++$j) {
                $block = bcadd($block, bcmul(ord(substr($s, $k++, 1)), $faktor));
                $faktor = bcmul($faktor, 256);
                $block = bcadd($block, bcmul(ord(substr($s, $k++, 1)), $faktor));
                $faktor = bcmul($faktor, 256);
            }
            $text = bcpowmod($block, $this->e, $this->m);
            $result .= ($split . self::biToHex($text));
            $split = ',';
        }
        return $result; // Remove last space.
    }

    function biDecryptedString($s, $utf8_decoded = FALSE)
    {
        $blocks = explode(",", $s);
        $result = "";
        for ($i = 0; $i < count($blocks); $i++) {
            $block = bcpowmod(self::biFromHex($blocks[$i]), $this->d, $this->m);
            for ($j = 0; $block !== "0"; $j++) {
                $curchar = bcmod($block, 256);
                $result .= chr($curchar);
                $block = bcdiv($block, 256, 0);
            }
        }
        $result = str_replace(chr(255), chr(0), $result);
        $result = substr($result, 0, strpos($result, chr(254)));
        return $utf8_decoded ? utf8_decode($result) : $result;
    }

    static $hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');

    static $decimal = array('0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
        'a' => 10, 'b' => 11, 'c' => 12, 'd' => 13, 'e' => 14, 'f' => 15);

    static function biToHex($decimal)
    {
        if ($decimal === '0')
            return '0';
        if (substr($decimal, 0, 1) == '-') {
            $sign = '-';
            $decimal = substr($decimal, 1);
        } else {
            $sign = '';
        }
        $result = '';
        while ($decimal !== '0') {
            $result = self::$hex[bcmod($decimal, 16)] . $result;
            $decimal = bcdiv($decimal, 16, 0);
        }
        return $sign . $result;
    }

    static function biFromHex($hexnumber)
    {
        if ($hexnumber === '0')
            return '0';
        if (substr($hexnumber, 0, 1) == '-') {
            $sign = '-';
            $hexnumber = substr($hexnumber, 1);
        } else {
            $sign = '';
        }
        $result = '0';
        $faktor = '1';
        $hl = strlen($hexnumber);
        while ($hl--) {
            $result = bcadd(bcmul(self::$decimal[substr($hexnumber, $hl, 1)], $faktor), $result);
            $faktor = bcmul($faktor, '16');
        }
        return $sign . $result;
    }

    static function biRandomPadding($n)
    {
        $result = "";
        for ($i = 0; $i < $n; $i++)
            $result = $result . chr(rand(1, 127));
        return $result;
    }


}
