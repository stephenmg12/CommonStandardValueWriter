<?php

namespace Spec\CommonStandardValueWriter;

use CommonStandardValueWriter\CommonStandardValueWriter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class CommonStandardValueWriterSpec
 *
 * @mixin \CommonStandardValueWriter\CommonStandardValueWriter
 */
class CommonStandardValueWriterSpec extends ObjectBehavior
{
    public function itIsInitializable()
    {
        $this->shouldHaveType('CommonStandardValueWriter\CommonStandardValueWriter');
    }
    public function itShouldAllowAddingComplexColumnsIfQuoted()
    {
        $this->addLine(['test1', 123, ['test2', 'test3']])
             ->__toString()
             ->shouldReturn("\"test1\",123,\"test2,test3\"");
        $this->setCsvColumnQuoteMode(CommonStandardValueWriter::QUOTE_ALL)
             ->__toString()
             ->shouldReturn("\"test1\",\"123\",\"test2,test3\"");
    }
    public function itShouldGiveEmptyStringIfNoHeaderOrRowsAdded()
    {
        $this->__toString()
             ->shouldReturn('');
    }
    public function itShouldIgnoreThemWhenEmptyLinesAreAdded()
    {
        $this->addLine(['test1', 123, 'test2'])
             ->addLine([])
             ->addLine(['test1', 123, 'test2'])
             ->__toString()
             ->shouldReturn("\"test1\",123,\"test2\"\n\"test1\",123,\"test2\"");
    }
    public function itShouldLetTheColumnQuoteModeBeChanged()
    {
        $this->setHeaderArray(['test1', 123, 'test2'])
             ->__toString()
             ->shouldReturn('"test1",123,"test2"');
        $this->addLine(['test1', 123, 'test2'])
             ->setCsvColumnQuoteMode(CommonStandardValueWriter::QUOTE_ALL)
             ->__toString()
             ->shouldReturn("\"test1\",123,\"test2\"\n\"test1\",\"123\",\"test2\"");
        $this->setCsvColumnQuoteMode(CommonStandardValueWriter::QUOTE_NONE)
             ->__toString()
             ->shouldReturn("\"test1\",123,\"test2\"\ntest1,123,test2");
    }
    public function itShouldLetTheCsvEOLBeChanged()
    {
        $this->setCsvEOL(';')
             ->addLine(['test1', 'test2'])
             ->__toString()
             ->shouldReturn('"test1","test2"');
        $this->addLine(['test3', 'test4'])
             ->__toString()
             ->shouldReturn("\"test1\",\"test2\";\"test3\",\"test4\"");
    }
    public function itShouldLetTheCsvQuoteBeChanged()
    {
        $this->setCsvQuote(';')
             ->addLine(['test1', 'test2'])
             ->__toString()
             ->shouldReturn(';test1;,;test2;');
        $this->addLine(['test3', 'test4'])
             ->__toString()
             ->shouldReturn(";test1;,;test2;\n;test3;,;test4;");
    }
    public function itShouldLetTheHeaderQuoteModeBeChanged()
    {
        $this->setHeaderArray(['test1', 123, 'test2'])
             ->__toString()
             ->shouldReturn('"test1",123,"test2"');
        $this->addLine(['test1', 123, 'test2'])
             ->setHeaderQuoteMode(CommonStandardValueWriter::QUOTE_ALL)
             ->__toString()
             ->shouldReturn("\"test1\",\"123\",\"test2\"\n\"test1\",123,\"test2\"");
        $this->setHeaderQuoteMode(CommonStandardValueWriter::QUOTE_NONE)
             ->__toString()
             ->shouldReturn("test1,123,test2\n\"test1\",123,\"test2\"");
    }
    public function itShouldNotQuoteNumericColumnsWhenModeIsQuoteString()
    {
        $this->setHeaderArray(['test1', 123, 'test2'])
             ->__toString()
             ->shouldReturn('"test1",123,"test2"');
        $this->addLine(['test1', 123, 'test2'])
             ->__toString()
             ->shouldReturn("\"test1\",123,\"test2\"\n\"test1\",123,\"test2\"");
    }
    public function itShouldOnlyReturnHeaderWhenItHasHeaderButNoRows()
    {
        $this->setHeaderArray(['test1'])
             ->__toString()
             ->shouldReturn('"test1"');
        $this->setHeaderArray(['test1', 'test2'])
             ->__toString()
             ->shouldReturn('"test1","test2"');
    }
    public function itShouldUseGivenCsvDelimiterForHeaderAsWell()
    {
        $this->setCsvDelimiter(';')
             ->setHeaderArray(['test1', 'test2'])
             ->__toString()
             ->shouldReturn('"test1";"test2"');
    }
    public function itShouldUseGivenCsvDelimiterForRows()
    {
        $this->setCsvDelimiter(';')
             ->addLine(['test1', 'test2'])
             ->__toString()
             ->shouldReturn('"test1";"test2"');
        $this->addLine(['test3', 'test4'])
             ->__toString()
             ->shouldReturn("\"test1\";\"test2\"\n\"test3\";\"test4\"");
    }
    public function itThrowExceptionIfHeaderHasMultipleRows()
    {
        $mess = 'Header can only be one row but contained more';
        $this->shouldThrow(new \DomainException($mess))
             ->duringSetHeaderArray([['test1', 'test2'], ['test3', 'test4']]);
    }
}
