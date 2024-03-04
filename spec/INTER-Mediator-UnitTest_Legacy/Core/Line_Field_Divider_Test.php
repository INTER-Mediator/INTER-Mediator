<?php

/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2017/10/08
 * Time: 0:18
 */

use PHPUnit\Framework\TestCase;
use INTERMediator\Media\LineDivider;
use INTERMediator\Media\FieldDivider;

class Line_Field_Divider_Test extends TestCase
{

    private function checkLines($d)
    {
        $ar = array();
        foreach ($d as $line) {
            $ar[] = $line;
        }
        $this->assertCount(3, $ar);
        $this->assertEquals("aaa", $ar[0]);
        $this->assertEquals("bbb", $ar[1]);
        $this->assertEquals("ccc", $ar[2]);
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
        $this->assertCount(3, $ar);
        $this->assertEquals("aaa", $ar[0]);
        $this->assertEquals("bbb", $ar[1]);
        $this->assertEquals("ccc", $ar[2]);

        $line = new FieldDivider("{$dq}aaa{$dq},{$sq}bbb{$sq},{$sq}ccc{$sq}");
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertCount(3, $ar);
        $this->assertEquals("aaa", $ar[0]);
        $this->assertEquals("bbb", $ar[1]);
        $this->assertEquals("ccc", $ar[2]);

        $line = new FieldDivider("aaa,bbb,c{$sq}c{$sq}c");
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertCount(3, $ar);
        $this->assertEquals("aaa", $ar[0]);
        $this->assertEquals("bbb", $ar[1]);
        $this->assertEquals("c{$sq}c{$sq}c", $ar[2]);

        $line = new FieldDivider("aaa,bbb,ccc");
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertCount(3, $ar);
        $this->assertEquals("aaa", $ar[0]);
        $this->assertEquals("bbb", $ar[1]);
        $this->assertEquals("ccc", $ar[2]);

        $line = new FieldDivider("aaa,bbb,{$sq}ccc");
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertCount(3, $ar);
        $this->assertEquals("aaa", $ar[0]);
        $this->assertEquals("bbb", $ar[1]);
        $this->assertEquals("{$sq}ccc", $ar[2]);

        $line = new FieldDivider("aaa,bbb,{$sq}c{$sq}cc");
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertCount(3, $ar);
        $this->assertEquals("aaa", $ar[0]);
        $this->assertEquals("bbb", $ar[1]);
        $this->assertEquals("c", $ar[2]);

        $line = new FieldDivider("aaa,{$sq}bbb,ccc{$sq}");
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertCount(2, $ar);
        $this->assertEquals("aaa", $ar[0]);
        $this->assertEquals("bbb,ccc", $ar[1]);

        $line = new FieldDivider("a{$sq}a{$dq}a,{$sq}bbb,ccc");
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertCount(2, $ar);
        $this->assertEquals("a{$sq}a{$dq}a", $ar[0]);
        $this->assertEquals("{$sq}bbb,ccc", $ar[1]);

        $line = new FieldDivider("a{$sq}a{$dq}a{$tab}{$sq}bbb,ccc{$tab}qpqp", $tab);
        $ar = array();
        foreach ($line as $field) {
            $ar[] = $field;
        }
        $this->assertCount(2, $ar);
        $this->assertEquals("a{$sq}a{$dq}a", $ar[0]);
        $this->assertEquals("{$sq}bbb,ccc{$tab}qpqp", $ar[1]);
    }
}
