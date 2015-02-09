<?php
/**
 * Contains CommonStandardValueWriter.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of CommonStandardValueWriter - A better PHP CSV Writer Class for PHP.
 * One of the main goals is to be more flexible then built-in function is PHP
 * Copyright (C) 2014-2015 Stephen Gulick
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
 *
 * @copyright 2014-2015 Stephen Gulick
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU GPLv2
 * @author    Stephen Gulick <stephenmg12@gmail.com>
 */
namespace CommonStandardValueWriter;

use FilePathNormalizer\FilePathNormalizer;

require_once dirname(__DIR__) . '/bootstrap.php';
/**
 * Class CommonStandardValueWriter
 */
class CommonStandardValueWriter
{
    /**
     * @param string             $filePath
     * @param FilePathNormalizer $fpn
     */
    public function __construct($filePath, FilePathNormalizer $fpn = null)
    {
        $this->setPath($filePath);
        $this->setFpn($fpn);
    }
    /**
     * @param array $newLine
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addLine(array $newLine = [])
    {
        if ($this->is_assoc($newLine)) {
            $tempLine = [];
            foreach ($newLine as $key => $value) {
                $tempLine[] = $value;
            }
            $this->csvArray[] = $tempLine;
        } else {
            $this->csvArray[] = $newLine;
        }
        return $this;
    }
    /**
     * @return $this
     */
    public function commit()
    {
        $fileHandle = $this->getFileHandle();
        if (!is_resource($fileHandle)) {
            $path = $this->getFilePath();
            $fileHandle = 'append' === $this->getCsvWriteMethod() ? fopen($path, 'ab+') : fopen($path, 'wb+');
        }
        $tempLine = $this->getCsvHeader();
        fwrite($fileHandle, $tempLine);
        $tempLine = $this->getCsvRowsAsString();
        fwrite($fileHandle, $tempLine);
        return $this;
    }
    /**
     * @return string
     */
    public function getCsvDelimiter()
    {
        return $this->csvDelimiter;
    }
    /**
     * @return string
     */
    public function getCsvEOL()
    {
        return $this->csvEOL;
    }
    /**
     * @return string
     */
    public function getCsvHeader()
    {
        if ('' === $this->headerQuoteMethod || false === $this->writeHeader) {
            return '';
        }
        if ('quote_none' === $this->headerQuoteMethod) {
            return $this->lineArrayToString($this->quoteNone($this->headerArray));
        } elseif ('quote_all' === $this->headerQuoteMethod) {
            return $this->lineArrayToString($this->quoteAll($this->headerArray));
        } elseif ('quote_string' === $this->headerQuoteMethod) {
            return $this->lineArrayToString($this->quoteString($this->headerArray));
        }
        throw new \DomainException('CommonStandardValueWriter::getCsvHeader valid options are quote_all, quote_none, or quote_string');
    }
    /**
     * @return string
     */
    public function getCsvQuote()
    {
        return $this->csvQuote;
    }
    /**
     * @return string
     */
    public function getCsvRowsAsString()
    {
        $result = '';
        foreach ($this->csvArray as $line) {
            if ('quote_none' === $this->csvRecordQuoteMethod) {
                $result .= $this->lineArrayToString($this->quoteNone($line));
            } elseif ('quote_all' === $this->csvRecordQuoteMethod) {
                $result .= $this->lineArrayToString($this->quoteAll($line));
            } elseif ('quote_string' === $this->csvRecordQuoteMethod) {
                $result .= $this->lineArrayToString($this->quoteString($line));
            } else {
                throw new \DomainException('CommonStandardValueWriter::csvQuoteNone only accepts Arrays');
            }
        }
        return $result;
    }
    /**
     * @return string
     */
    public function getCsvWriteMethod()
    {
        return $this->csvWriteMethod;
    }
    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }
    /**
     * @return FilePathNormalizer
     */
    public function getFpn()
    {
        return $this->fpn;
    }
    /**
     * @return string
     */
    public function getHeaderQuoteMethod()
    {
        return $this->headerQuoteMethod;
    }
    /**
     * @param string $csvDelimiter
     */
    public function setCsvDelimiter($csvDelimiter = '"')
    {
        $this->csvDelimiter = (string)$csvDelimiter;
    }
    /**
     * @param string $csvEOL
     */
    public function setCsvEOL($csvEOL)
    {
        $this->csvEOL = $csvEOL;
    }
    /**
     * @param string $value
     *
     * @return self
     */
    public function setCsvQuote($value)
    {
        $this->csvQuote = $value;
        return $this;
    }
    /**
     * @param string $value
     */
    public function setCsvWriteMethod($value = 'quote_string')
    {
        $this->ValidateQuoteMethod($value);
        $this->csvWriteMethod = $value;
    }
    /**
     * @param mixed $fileHandle
     */
    public function setFileHandle($fileHandle)
    {
        $this->fileHandle = $fileHandle;
    }
    /**
     * @param string $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = (string)$filePath;
    }
    /**
     * @param FilePathNormalizer $value
     *
     * @return self
     */
    public function setFpn($value = null)
    {
        if (null === $value) {
            $this->fpn = new FilePathNormalizer();
        }
        $this->fpn = $value;
        return $this;
    }
    /**
     * @param array $header
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setHeaderArray(array $header = [])
    {
        $this->headerArray = $header;
        return $this;
    }
    /**
     * @param string $value
     *
     * @return self
     */
    public function setHeaderQuoteMethod($value = 'quote_string')
    {
        $this->ValidateQuoteMethod($value);
        $this->headerQuoteMethod = $value;
        return $this;
    }
    /**
     * @param string $filePath
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setPath($filePath)
    {
        $this->filePath = $this->fpn->normalizeFile($filePath);
        return $this;
    }
    /**
     * @param $value
     */
    protected function ValidateQuoteMethod($value)
    {
        if (!in_array($value, ['quote_all', 'quote_none', 'quote_string'], true)) {
            throw new \DomainException('CommonStandardValueWriter::getCsvHeader valid options are quote_all, quote_none, or quote_string');
        }
    }
    /**
     * @return mixed
     */
    protected function getFileHandle()
    {
        return $this->fileHandle;
    }
    /**
     * @param $array
     *
     * @return bool
     */
    protected function is_assoc($array)
    {
        return (array_values($array) !== $array);
    }
    /**
     * @param array $line
     *
     * @return string
     */
    protected function lineArrayToString(array $line = [])
    {
        return implode($this->csvDelimiter, $line) . $this->csvEOL;
    }
    /**
     * @param array $line
     *
     * @return array
     */
    protected function quoteAll(array $line = [])
    {
        $tempLine = [];
        foreach ($line as $value) {
            if (is_array($value)) {
                $tempLine[] =
                    $this->csvQuote
                    . implode($this->csvQuote . $this->getCsvDelimiter() . $this->csvQuote, $value)
                    . $this->csvQuote;
            } else {
                $tempLine[] = $this->csvQuote . $value . $this->csvQuote;
            }
        }
        return $tempLine;
    }
    /**
     * @param array $line
     *
     * @return array
     */
    protected function quoteNone(array $line = [])
    {
        return $line;
    }
    /**
     * @param array $line
     *
     * @return array
     */
    protected function quoteString(array $line = [])
    {
        $tempLine = [];
        foreach ($line as $value) {
            if (is_numeric($value)) {
                $tempLine[] = $value;
            } elseif (is_array($value)) {
                $tempLine[] =
                    $this->csvQuote
                    . implode($this->csvQuote . $this->getCsvDelimiter() . $this->csvQuote, $value)
                    . $this->csvQuote;
            } else {
                $tempLine[] = $this->csvQuote . $value . $this->csvQuote;
            }
        }
        return $tempLine;
    }
    /**
     * @var array $csvArray
     */
    protected $csvArray;
    /**
     * @var string $csvDelimiter
     */
    protected $csvDelimiter = ',';
    /**
     * @var string $csvEOL
     */
    protected $csvEOL = '\n';
    /**
     * @var string $csvQuote = '"';
     */
    protected $csvQuote = '"';
    /**
     * @var int $csvRecordCount
     */
    protected $csvRecordCount = 0;
    /**
     * Set to quote_all, quote_none, or quote_string (default).
     *
     * @var string $csvRecordQuoteMethod
     */
    protected $csvRecordQuoteMethod = 'quote_string';
    /**
     * @var string $csvWriteMethod
     */
    protected $csvWriteMethod = 'truncate';
    /**
     * @var resource $fileHandle
     */
    protected $fileHandle;
    /**
     * @var string $filePath
     */
    protected $filePath;
    /**
     * @var FilePathNormalizer $fpn
     */
    protected $fpn;
    /**
     * @var array $headerArray
     */
    protected $headerArray = [];
    /**
     * @var string $headerQuoteMethod
     */
    protected $headerQuoteMethod = 'quote_string';
    /**
     * @var string $listDelimiter
     */
    protected $listDelimiter = ' ';
    /**
     * @var bool $writeHeader
     */
    protected $writeHeader = true;
}
