<?php

/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2017/10/08
 * Time: 0:18
 */
use \PHPUnit\Framework\TestCase;
use \INTERMediator\Media\LineDivider;
use \INTERMediator\Media\FieldDivider;
//require_once(dirname(__FILE__) . '/../LineDivider.php');
//require_once(dirname(__FILE__) . '/../FieldDivider.php');

class Line_Field_Divider_Test extends TestCase
{

    private function checkLines($d)
    {
        $ar = array();
        foreach ($d as $line) {
            $ar[] = $line;
        }
        $this->assertEquals(count($ar), 3);
        $this->assertEquals($ar[0], "aaa");
        $this->assertEquals($ar[1], "bbb");
        $this->assertEquals($ar[2], "ccc");
    }

    public function testLineDivider()
    {
        $this->checkLines(new LineDivider("aaa\nbbb\nccc\n"));
        $this->checkLines(new LineDivider("aaa\rbbb\rccc\r"));
        $this->checkLines(new LineDivider("aaa\nbbb\rccc\n"));
        $this->checkLines(new LineDivider("aaa\nbbb\r\nccc\r\n"));
        $this->checkLines(new LineDivider("aaa\nbbb\nccc"));
    }

    public function testFieldDivider()
    {
        $dq = '"';
        $sq = "'";
        $tab = "\t";

        $line = new FieldDivider("aaa,bbb,ccc");
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertEquals(count($ar), 3);
        $this->assertEquals($ar[0], "aaa");
        $this->assertEquals($ar[1], "bbb");
        $this->assertEquals($ar[2], "ccc");

        $line = new FieldDivider("{$dq}aaa{$dq},{$sq}bbb{$sq},{$sq}ccc{$sq}");
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertEquals(count($ar), 3);
        $this->assertEquals($ar[0], "aaa");
        $this->assertEquals($ar[1], "bbb");
        $this->assertEquals($ar[2], "ccc");

        $line = new FieldDivider("aaa,bbb,c{$sq}c{$sq}c");
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertEquals(count($ar), 3);
        $this->assertEquals($ar[0], "aaa");
        $this->assertEquals($ar[1], "bbb");
        $this->assertEquals($ar[2], "c{$sq}c{$sq}c");

        $line = new FieldDivider("aaa,bbb,ccc");
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertEquals(count($ar), 3);
        $this->assertEquals($ar[0], "aaa");
        $this->assertEquals($ar[1], "bbb");
        $this->assertEquals($ar[2], "ccc");

        $line = new FieldDivider("aaa,bbb,{$sq}ccc");
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertEquals(count($ar), 3);
        $this->assertEquals($ar[0], "aaa");
        $this->assertEquals($ar[1], "bbb");
        $this->assertEquals($ar[2], "{$sq}ccc");

        $line = new FieldDivider("aaa,bbb,{$sq}c{$sq}cc");
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertEquals(count($ar), 3);
        $this->assertEquals($ar[0], "aaa");
        $this->assertEquals($ar[1], "bbb");
        $this->assertEquals($ar[2], "c");

        $line = new FieldDivider("aaa,{$sq}bbb,ccc{$sq}");
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertEquals(count($ar), 2);
        $this->assertEquals($ar[0], "aaa");
        $this->assertEquals($ar[1], "bbb,ccc");

        $line = new FieldDivider("a{$sq}a{$dq}a,{$sq}bbb,ccc");
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertEquals(count($ar), 2);
        $this->assertEquals($ar[0], "a{$sq}a{$dq}a");
        $this->assertEquals($ar[1], "{$sq}bbb,ccc");

        $line = new FieldDivider("a{$sq}a{$dq}a{$tab}{$sq}bbb,ccc{$tab}qpqp", $tab);
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertEquals(count($ar), 2);
        $this->assertEquals($ar[0], "a{$sq}a{$dq}a");
        $this->assertEquals($ar[1], "{$sq}bbb,ccc{$tab}qpqp");
    }
}
