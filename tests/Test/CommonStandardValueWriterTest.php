<?php
/**
 * Contains CommonStandardValueWriterTestTest class.
 *
 * PHP version 5.4
 * LICENSE:
 * This file is part of CommonStandardValueWriter - A better PHP CSV Writer Class for PHP.
 * One of the main goals is to be more flexible then built-in function is PHP
 * Copyright (C) 2015 Stephen Gulick
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, version 2 of the License.
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see
 * <http://www.gnu.org/licenses/>.
 * You should be able to find a copy of this license in the LICENSE file.
 * @copyright 2015 Stephen Gulick
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU GPLv2
 * @author    Stephen Gulick <stephenmg12@gmail.com>
 */


namespace CommonStandardValueWriterTest\Test;

require_once dirname(dirname(__DIR__)).'/bootstrap.php';

use CommonStandardValueWriter\CommonStandardValueWriter;
use org\bovigo\vfs\vfsStream;
use FilePathNormalizer\FilePathNormalizer;

/**
 * Class CommonStandardValueWriterTest
 */
class CommonStandardValueWriterTest extends \PHPUnit_Framework_TestCase
{
    public function testAddLine()
    {
        $line=array('test1','123','test3');
        $csvw = new CommonStandardValueWriter();
        $csvw->addLine($line);
        $this->assertEquals("\"test1\",123,\"test3\"\n", $csvw->getCsvRowsAsString());
    }

    public function testCsvQuoteAllMode()
    {
        $line=array('test1','123','test3');
        $csvw = new CommonStandardValueWriter();
        $csvw->setCsvColumnQuoteMode(CommonStandardValueWriter::QUOTE_ALL);
        $csvw->addLine($line);
        $this->assertEquals("\"test1\",\"123\",\"test3\"\n", $csvw->getCsvRowsAsString());
        $line = array('test1','test2',array('test3','test4','test5'));
        $csvw->addLine($line);
        $this->assertEquals("\"test1\",\"123\",\"test3\"\n\"test1\",\"test2\",\"test3,test4,test5\"\n", $csvw->getCsvRowsAsString());
    }

    public function testCsvQuoteStringMode()
    {
        $line=array('test1','123','test3');
        $csvw = new CommonStandardValueWriter();
        $csvw->setCsvColumnQuoteMode(CommonStandardValueWriter::QUOTE_STRING);
        $csvw->addLine($line);
        $this->assertEquals("\"test1\",123,\"test3\"\n", $csvw->getCsvRowsAsString());
        $line = array('test1','test2',array('test3','test4','test5'));
        $csvw->addLine($line);
        $this->assertEquals("\"test1\",123,\"test3\"\n\"test1\",\"test2\",\"test3,test4,test5\"\n", $csvw->getCsvRowsAsString());
    }

    public function testCsvQuoteNoneMode()
    {
        $line=array('test1','123','test3');
        $csvw = new CommonStandardValueWriter();
        $csvw->setCsvColumnQuoteMode(CommonStandardValueWriter::QUOTE_NONE);
        $csvw->addLine($line);
        $this->assertEquals("test1,123,test3\n", $csvw->getCsvRowsAsString());
    }

    public function testAddLineEmpty()
    {
        $csvw = new CommonStandardValueWriter();
        $instance = $csvw->addLine(array());
        $this->assertEquals('', $csvw->getCsvRowsAsString());
        $this->assertInstanceOf('CommonStandardValueWriter\CommonStandardValueWriter', $instance );
    }

    public function testsetCsvDelimiterAndSetCsvEOL()
    {
        $line=array('test1','123','test3');
        $csvw = new CommonStandardValueWriter();
        $csvw->addLine(array())->setCsvDelimiter("\t")->setCsvEOL("\r\n")->addLine($line);
        $this->assertEquals("\"test1\"\t123\t\"test3\"\r\n", $csvw->getCsvRowsAsString());
    }

    public function testsetCsvQuote()
    {
        $line=array('test1','123','test3');
        $csvw = new CommonStandardValueWriter();
        $csvw->addLine(array())->setCsvQuote("'")->addLine($line);
        $this->assertEquals("'test1',123,'test3'\n", $csvw->getCsvRowsAsString());
    }

    public function testMultipleHaderLinesThrowsDomainException()
    {
        $this->setExpectedException('DomainException', 'Header can only be one row but contained more');
        $csvw = new CommonStandardValueWriter();
        $csvw->setHeaderQuoteMode(CommonStandardValueWriter::QUOTE_ALL);
        $header=array(array('test1','123','test3'),array('test4','test5','test6'));
        $csvw->setHeaderArray($header);
    }

    public function testHeaderQuoteAll()
    {
        $csvw = new CommonStandardValueWriter();
        $csvw->setHeaderQuoteMode(CommonStandardValueWriter::QUOTE_ALL);
        $header=array('test1','123','test3');
        $csvw->setHeaderArray($header);
        $this->assertEquals("\"test1\",\"123\",\"test3\"\n", $csvw->getCsvHeader());
    }

    public function testHeaderQuoteNone()
    {
        $csvw = new CommonStandardValueWriter();
        $csvw->setHeaderQuoteMode(CommonStandardValueWriter::QUOTE_NONE);
        $header=array('test1','test2','test3');
        $csvw->setHeaderArray($header);
        $this->assertEquals("test1,test2,test3\n", $csvw->getCsvHeader());
    }

    public function testSetHeader()
    {
        $header=array('test1','test2','test3');
        $csvw = new CommonStandardValueWriter();
        $this->assertEquals('', $csvw->getCsvHeader());
        $csvw->setHeaderArray($header);
        $this->assertEquals("\"test1\",\"test2\",\"test3\"\n", $csvw->getCsvHeader());
    }

    public function testSetQuoteEscapeMode()
    {
        $line = array('test1','"test two"','test3');
        $csvw = new CommonStandardValueWriter();
        $csvw->setQuoteEscapeMode(CommonStandardValueWriter::ESCAPE_DOUBLE)->addLine($line);
        $this->assertEquals("\"test1\",\"\"\"test two\"\"\",\"test3\"\n", $csvw->getCsvRowsAsString());
        $csvw->setQuoteEscapeMode(CommonStandardValueWriter::ESCAPE_BSLASH);
        $this->assertEquals("\"test1\",\"\\\"test two\\\"\",\"test3\"\n", $csvw->getCsvRowsAsString());
        $csvw->setQuoteEscapeMode(CommonStandardValueWriter::ESCAPE_NONE);
        $this->assertEquals("\"test1\",\"\"test two\"\",\"test3\"\n", $csvw->getCsvRowsAsString());
    }

    public function testSetQuoteEscapeModeException()
    {
        $this->setExpectedException('InvalidArgumentException', 'Quote escape mode must be back_slash, double, or none given error');
        $csvw = new CommonStandardValueWriter();
        $csvw->setQuoteEscapeMode('error');
    }

    public function testValidateQuoteModeException()
    {
        $this->setExpectedException('DomainException', 'Valid quote options are quote_all, quote_none, or quote_string');
        $csvw = new CommonStandardValueWriter();
        $csvw->setCsvColumnQuoteMode('error');
    }

    public function testWriteToFile()
    {
        $fpn = new FilePathNormalizer();
        $csvw = new CommonStandardValueWriter();
        $header = array('header1', 'header2', 'header3');
        $line = array('test1', '123', 'test3');
        $root = vfsStream::setup('test');
        $csvw->setHeaderArray($header)->addLine($line)->setFpn($fpn)->writeToFile('vfs://test/test.csv');
        $actual = file_get_contents('vfs://test/test.csv');
        $expected = $csvw->__toString();
        $this->assertEquals($expected, $actual);
    }
}
