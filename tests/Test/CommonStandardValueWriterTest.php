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

require_once dirname(dirname(__DIR__)) . '/bootstrap.php';
use CommonStandardValueWriter\CommonStandardValueWriter;
use FilePathNormalizer\FilePathNormalizer;
use org\bovigo\vfs\vfsStream;

/**
 * Class CommonStandardValueWriterTest
 */
class CommonStandardValueWriterTest extends \PHPUnit_Framework_TestCase
{
    public function testAddLine()
    {
        $line = ['test1', '123', 'test3'];
        $csvw = new CommonStandardValueWriter();
        $csvw->addLine($line);
        $this->assertEquals("\"test1\",123,\"test3\"\n", $csvw->getCsvRowsAsString());
    }
    public function testAddLineEmpty()
    {
        $csvw = new CommonStandardValueWriter();
        $instance = $csvw->addLine([]);
        $this->assertEquals('', $csvw->getCsvRowsAsString());
        $this->assertInstanceOf('CommonStandardValueWriter\CommonStandardValueWriter', $instance);
    }
    public function testCsvQuoteAllMode()
    {
        $line = ['test1', '123', 'test3'];
        $csvw = new CommonStandardValueWriter();
        $csvw->setCsvColumnQuoteMode(CommonStandardValueWriter::QUOTE_ALL);
        $csvw->addLine($line);
        $this->assertEquals("\"test1\",\"123\",\"test3\"\n", $csvw->getCsvRowsAsString());
        $line = ['test1', 'test2', ['test3', 'test4', 'test5']];
        $csvw->addLine($line);
        $this->assertEquals(
            "\"test1\",\"123\",\"test3\"\n\"test1\",\"test2\",\"test3,test4,test5\"\n",
            $csvw->getCsvRowsAsString()
        );
    }
    public function testCsvQuoteNoneMode()
    {
        $line = ['test1', '123', 'test3'];
        $csvw = new CommonStandardValueWriter();
        $csvw->setCsvColumnQuoteMode(CommonStandardValueWriter::QUOTE_NONE);
        $csvw->addLine($line);
        $this->assertEquals("test1,123,test3\n", $csvw->getCsvRowsAsString());
    }
    public function testCsvQuoteStringMode()
    {
        $line = ['test1', '123', 'test3'];
        $csvw = new CommonStandardValueWriter();
        $csvw->setCsvColumnQuoteMode(CommonStandardValueWriter::QUOTE_STRING);
        $csvw->addLine($line);
        $this->assertEquals("\"test1\",123,\"test3\"\n", $csvw->getCsvRowsAsString());
        $line = ['test1', 'test2', ['test3', 'test4', 'test5']];
        $csvw->addLine($line);
        $this->assertEquals(
            "\"test1\",123,\"test3\"\n\"test1\",\"test2\",\"test3,test4,test5\"\n",
            $csvw->getCsvRowsAsString()
        );
    }
    public function testGetCsvHeaderString()
    {
        $csvw = new CommonStandardValueWriter();
        $this->assertInternalType('string', $csvw->getCsvHeader());
    }
    public function testGetCsvRowAsString()
    {
        $csvw = new CommonStandardValueWriter();
        $this->assertInternalType('string', $csvw->getCsvRowsAsString());
    }
    public function testHeaderQuoteAll()
    {
        $csvw = new CommonStandardValueWriter();
        $csvw->setHeaderQuoteMode(CommonStandardValueWriter::QUOTE_ALL);
        $header = ['test1', '123', 'test3'];
        $csvw->setHeaderArray($header);
        $this->assertEquals("\"test1\",\"123\",\"test3\"\n", $csvw->getCsvHeader());
    }
    public function testHeaderQuoteNone()
    {
        $csvw = new CommonStandardValueWriter();
        $csvw->setHeaderQuoteMode(CommonStandardValueWriter::QUOTE_NONE);
        $header = ['test1', 'test2', 'test3'];
        $csvw->setHeaderArray($header);
        $this->assertEquals("test1,test2,test3\n", $csvw->getCsvHeader());
    }
    public function testMultipleHeaderLinesThrowsDomainException()
    {
        $this->setExpectedException('DomainException', 'Header can only be one row but contained more');
        $csvw = new CommonStandardValueWriter();
        $csvw->setHeaderQuoteMode(CommonStandardValueWriter::QUOTE_ALL);
        $header = [['test1', '123', 'test3'], ['test4', 'test5', 'test6']];
        $csvw->setHeaderArray($header);
    }
    public function testSetCsvColumnQuoteModeHasFluentInterface()
    {
        $csvw = new CommonStandardValueWriter();
        $this->assertInstanceOf(
            '\\CommonStandardValueWriter\\CommonStandardValueWriter',
            $csvw->setCsvColumnQuoteMode()
        );
    }
    public function testSetCsvDelimiterAndSetCsvEOL()
    {
        $line = ['test1', '123', 'test3'];
        $csvw = new CommonStandardValueWriter();
        $csvw->addLine([])
             ->setCsvDelimiter("\t")
             ->setCsvEOL("\r\n")
             ->addLine($line);
        $this->assertEquals("\"test1\"\t123\t\"test3\"\r\n", $csvw->getCsvRowsAsString());
    }
    public function testSetCsvDelimiterHasFluentInterface()
    {
        $csvw = new CommonStandardValueWriter();
        $this->assertInstanceOf('\\CommonStandardValueWriter\\CommonStandardValueWriter', $csvw->setCsvDelimiter());
    }
    public function testSetCsvEOLHasFluentInterface()
    {
        $csvw = new CommonStandardValueWriter();
        $this->assertInstanceOf('\\CommonStandardValueWriter\\CommonStandardValueWriter', $csvw->setCsvEOL());
    }
    public function testSetCsvQuote()
    {
        $line = ['test1', '123', 'test3'];
        $csvw = new CommonStandardValueWriter();
        $csvw->addLine([])
             ->setCsvQuote("'")
             ->addLine($line);
        $this->assertEquals("'test1',123,'test3'\n", $csvw->getCsvRowsAsString());
    }
    public function testSetCsvQuoteHasFluentInterface()
    {
        $csvw = new CommonStandardValueWriter();
        $this->assertInstanceOf('\\CommonStandardValueWriter\\CommonStandardValueWriter', $csvw->setCsvQuote());
    }
    public function testSetCsvWriteMethodException()
    {
        $this->setExpectedException(
            'DomainException',
            'csvWriteMethod must be either CommonStandardValueWriter::WRITE_APPEND or CommonStandardValueWriter::WRITE_TRUNCATE'
        );
        $csvw = new CommonStandardValueWriter();
        $csvw->setCsvWriteMethod('error');
    }
    public function testSetCsvWriteMethodHasFluentInterface()
    {
        $csvw = new CommonStandardValueWriter();
        $this->assertInstanceOf('\\CommonStandardValueWriter\\CommonStandardValueWriter', $csvw->setCsvWriteMethod());
    }
    public function testSetFpn()
    {
        $class = new \ReflectionClass('CommonStandardValueWriter\CommonStandardValueWriter');
        $getFpn = $class->getMethod('getFpn');
        $getFpn->setAccessible(true);
        $csvw = new CommonStandardValueWriter();
        $csvw->setFpn(new FilePathNormalizer());
        $this->assertInstanceOf('FilePathNormalizer\FilePathNormalizer', $getFpn->invoke($csvw));
    }
    public function testSetFpnHasFluentInterface()
    {
        $csvw = new CommonStandardValueWriter();
        $this->assertInstanceOf('\\CommonStandardValueWriter\\CommonStandardValueWriter', $csvw->setFpn());
    }
    public function testSetHeader()
    {
        $header = ['test1', 'test2', 'test3'];
        $csvw = new CommonStandardValueWriter();
        $this->assertEquals('', $csvw->getCsvHeader());
        $csvw->setHeaderArray($header);
        $this->assertEquals("\"test1\",\"test2\",\"test3\"\n", $csvw->getCsvHeader());
    }
    public function testSetHeaderArrayHasFluentInterface()
    {
        $csvw = new CommonStandardValueWriter();
        $this->assertInstanceOf('\\CommonStandardValueWriter\\CommonStandardValueWriter', $csvw->setHeaderArray());
    }
    public function testSetHeaderQuoteModeHasFluentInterface()
    {
        $csvw = new CommonStandardValueWriter();
        $this->assertInstanceOf('\\CommonStandardValueWriter\\CommonStandardValueWriter', $csvw->setHeaderQuoteMode());
    }
    public function testSetQuoteEscapeMode()
    {
        $line = ['test1', '"test two"', 'test3'];
        $csvw = new CommonStandardValueWriter();
        $csvw->setQuoteEscapeMode(CommonStandardValueWriter::ESCAPE_DOUBLE)
             ->addLine($line);
        $this->assertEquals("\"test1\",\"\"\"test two\"\"\",\"test3\"\n", $csvw->getCsvRowsAsString());
        $csvw->setQuoteEscapeMode(CommonStandardValueWriter::ESCAPE_BSLASH);
        $this->assertEquals("\"test1\",\"\\\"test two\\\"\",\"test3\"\n", $csvw->getCsvRowsAsString());
        $csvw->setQuoteEscapeMode(CommonStandardValueWriter::ESCAPE_NONE);
        $this->assertEquals("\"test1\",\"\"test two\"\",\"test3\"\n", $csvw->getCsvRowsAsString());
    }
    public function testSetQuoteEscapeModeException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Quote escape mode must be back_slash, double, or none given error'
        );
        $csvw = new CommonStandardValueWriter();
        $csvw->setQuoteEscapeMode('error');
    }
    public function testSetQuoteEscapeModeHasFluentInterface()
    {
        $csvw = new CommonStandardValueWriter();
        $this->assertInstanceOf('\\CommonStandardValueWriter\\CommonStandardValueWriter', $csvw->setQuoteEscapeMode());
    }
    public function testValidateQuoteModeException()
    {
        $this->setExpectedException(
            'DomainException',
            'Valid quote options are quote_all, quote_none, or quote_string'
        );
        $csvw = new CommonStandardValueWriter();
        $csvw->setCsvColumnQuoteMode('error');
    }
    public function testWriteToFile()
    {
        $fpn = new FilePathNormalizer();
        $csvw = new CommonStandardValueWriter();
        $header = ['header1', 'header2', 'header3'];
        $line = ['test1', '123', 'test3'];
        vfsStream::setup('test');
        $csvw->setHeaderArray($header)
             ->addLine($line)
             ->setFpn($fpn)
             ->writeToFile('vfs://test/test.csv');
        $actual = file_get_contents('vfs://test/test.csv');
        $expected = $csvw->__toString();
        $this->assertEquals($expected, $actual);
    }
    public function testsetCsvWriteMethodAppend()
    {
        $header = ['header1', 'header2', 'header3'];
        $line = ['test1', 'test2', 'test3'];
        $content = "\"header4\",\"header5\",\"header6\"\n";
        $root = vfsStream::setup('test');
        vfsStream::newFile('test.csv')
                 ->withContent($content)
                 ->at($root);
        $csvw = new CommonStandardValueWriter();
        $csvw->setCsvWriteMethod(CommonStandardValueWriter::WRITE_APPEND)
             ->setHeaderArray($header)
             ->addLine($line)
             ->writeToFile('vfs://test/test.csv');
        $actual = file_get_contents(vfsStream::url('test/test.csv'));
        $expected = $content . $csvw->getCsvRowsAsString();
        $this->assertEquals($expected, $actual);
    }
    public function testsetCsvWriteMethodTruncate()
    {
        $header = ['header1', 'header2', 'header3'];
        $line = ['test1', 'test2', 'test3'];
        $content = "\"header4\",\"header5\",\"header6\"\n";
        vfsStream::setup('test');
        vfsStream::newFile('test.csv')
                 ->withContent($content);
        $csvw = new CommonStandardValueWriter();
        $csvw->setCsvWriteMethod(CommonStandardValueWriter::WRITE_TRUNCATE)
             ->setHeaderArray($header)
             ->addLine($line)
             ->writeToFile('vfs://test/test.csv');
        $actual = file_get_contents('vfs://test/test.csv');
        $expected = $csvw->__toString();
        $this->assertEquals($expected, $actual);
    }
}
