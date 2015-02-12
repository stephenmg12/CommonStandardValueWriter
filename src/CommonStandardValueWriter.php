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
     * Used to set quote method for setCsvWriteMethod() and setHeaderQuoteMode().
     */
    const QUOTE_ALL = 'quote_all';
    const QUOTE_NONE = 'quote_none';
    const QUOTE_STRING = 'quote_string';
    /**
     * Used to set escape mode for setQuoteEscapeMode()
     */
    const ESCAPE_DOUBLE = 'double';
    const ESCAPE_BSLASH = 'back_slash';
    const ESCAPE_NONE = 'none';
    /**
     * @param FilePathNormalizer $fpn
     */
    public function __construct(FilePathNormalizer $fpn = null)
    {
        $this->setFpn($fpn);
    }
    /**
     * @return string
     */
    public function __toString()
    {
        $result = trim($this->getCsvHeader() . $this->getCsvRowsAsString(), $this->csvEOL);
        return $result;
    }
    /**
     * @param array $newLine
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addLine(array $newLine)
    {
        if (0 === count($newLine)) {
            return $this;
        }
        $this->csvArray[] = array_values($newLine);
        return $this;
    }
    /**
     * @return string
     */
    public function getCsvHeader()
    {
        if (false === $this->writeHeader || 0 === count($this->headerArray)) {
            return '';
        }
        if (self::QUOTE_ALL === $this->headerQuoteMode) {
            return implode($this->csvDelimiter, $this->quoteAll($this->headerArray)) . $this->csvEOL;
        } elseif (self::QUOTE_STRING === $this->headerQuoteMode) {
            return implode($this->csvDelimiter, $this->quoteString($this->headerArray)) . $this->csvEOL;
        }
        return implode($this->csvDelimiter, $this->headerArray) . $this->csvEOL;
    }
    /**
     * @return string
     */
    public function getCsvRowsAsString()
    {
        if (0 === count($this->csvArray)) {
            return '';
        }
        $result = '';
        foreach ($this->csvArray as $line) {
            if (self::QUOTE_ALL === $this->csvColumnQuoteMode) {
                $line = implode($this->csvDelimiter, $this->quoteAll($line));
            } elseif (self::QUOTE_STRING === $this->csvColumnQuoteMode) {
                $line = implode($this->csvDelimiter, $this->quoteString($line));
            } else {
                $line = implode($this->csvDelimiter, $line);
            }
            $result .= $line . $this->csvEOL;
        }
        return $result;
    }
    /**
     * @param string $value
     *
     * @return CommonStandardValueWriter
     * @throws \DomainException
     */
    public function setCsvColumnQuoteMode($value = self::QUOTE_STRING)
    {
        $this->validateQuoteMode($value);
        $this->csvColumnQuoteMode = $value;
        return $this;
    }
    /**
     * @param string $csvDelimiter
     *
     * @return $this
     */
    public function setCsvDelimiter($csvDelimiter = ',')
    {
        $this->csvDelimiter = (string)$csvDelimiter;
        return $this;
    }
    /**
     * @param string $csvEOL
     *
     * @return $this
     */
    public function setCsvEOL($csvEOL)
    {
        $this->csvEOL = $csvEOL;
        return $this;
    }
    /**
     * @param string $value
     *
     * @return self
     */
    public function setCsvQuote($value = '"')
    {
        $this->csvQuote = (string)$value;
        return $this;
    }
    /**
     * @param string $value
     *
     * @throws \DomainException
     */
    public function setCsvWriteMethod($value = 'append')
    {
        $this->csvWriteMethod = $value;
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
     * @throws \DomainException
     */
    public function setHeaderArray(array $header = [])
    {
        if (count($header, COUNT_RECURSIVE) !== count($header)) {
            $mess = 'Header can only be one row but contained more';
            throw new \DomainException($mess);
        }
        $this->headerArray = array_values($header);
        return $this;
    }
    /**
     * @param string $value
     *
     * @return CommonStandardValueWriter
     * @throws \DomainException
     */
    public function setHeaderQuoteMode($value = self::QUOTE_STRING)
    {
        $this->validateQuoteMode($value);
        $this->headerQuoteMode = $value;
        return $this;
    }
    /**
     * @param string $value
     *
     * @return CommonStandardValueWriter
     * @throws \InvalidArgumentException
     */
    public function setQuoteEscapeMode($value = self::ESCAPE_DOUBLE)
    {
        $value = (string)$value;
        if (!in_array($value, [self::ESCAPE_DOUBLE, self::ESCAPE_BSLASH, self::ESCAPE_NONE], true)) {
            $mess = 'Quote escape mode must be back_slash, double, or none given ' . $value;
            throw new \InvalidArgumentException($mess);
        }
        $this->quoteEscapeMode = $value;
        return $this;
    }
    /**
     * @param string $file
     *
     * @return $this
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */
    public function writeToFile($file)
    {
        $file =
            $this->getFpn()
                 ->normalizeFile((string)$file);
        $fileHandle = 'append' === $this->csvWriteMethod ? fopen($file, 'ab') : fopen($file, 'cb');
        $tries = 0;
        //Give 10 secs to try getting lock.
        $timeout = time() + 10;
        while (!flock($fileHandle, LOCK_EX | LOCK_NB)) {
            if (10 < ++$tries || $timeout < time()) {
                fclose($fileHandle);
                $mess = 'Giving up could not get flock on ' . $file;
                throw new \LengthException($mess);
            }
            // Wait 0.1 to 0.5 seconds before trying again.
            usleep(rand(100000, 500000));
        }
        $csv = $this->__toString();
        $tries = 0;
        //Give a minute to try writing file.
        $timeout = time() + 60;
        while (strlen($csv)) {
            if (10 < ++$tries || $timeout < time()) {
                if (is_resource($fileHandle)) {
                    flock($fileHandle, LOCK_UN);
                    fclose($fileHandle);
                }
                $mess = 'Giving up could not finish writing ' . $file;
                throw new \LengthException($mess);
            }
            $written = fwrite($fileHandle, $csv);
            // Decrease $tries while making progress but NEVER $tries < 1.
            if ($written > 0 && $tries > 0) {
                --$tries;
            }
            $csv = substr($csv, $written);
        }
        return $this;
    }
    /**
     * @param $string
     *
     * @return string
     */
    protected function doQuoteEscape($string)
    {
        if (self::ESCAPE_DOUBLE === $this->quoteEscapeMode) {
            return str_replace($this->csvQuote, $this->csvQuote . $this->csvQuote, (string)$string);
        }
        if (self::ESCAPE_BSLASH === $this->quoteEscapeMode) {
            return str_replace($this->csvQuote, '\\' . $this->csvQuote, (string)$string);
        }
        return $string;
    }
    /**
     * @return FilePathNormalizer
     */
    protected function getFpn()
    {
        return $this->fpn;
    }
    /**
     * @param array $line
     *
     * @return array
     */
    protected function quoteAll(array $line)
    {
        $tempLine = [];
        foreach ($line as $value) {
            if (is_array($value)) {
                $value = implode($this->csvDelimiter, $value);
            }
            $tempLine[] = $this->csvQuote . $this->doQuoteEscape($value) . $this->csvQuote;
        }
        return $tempLine;
    }
    /**
     * @param array $line
     *
     * @return array
     */
    protected function quoteString(array $line)
    {
        $tempLine = [];
        foreach ($line as $value) {
            if (is_numeric($value)) {
                $tempLine[] = $value;
            } elseif (is_array($value)) {
                $tempLine[] =
                    $this->csvQuote . $this->doQuoteEscape(implode($this->csvDelimiter, $value)) . $this->csvQuote;
            } else {
                $tempLine[] = $this->csvQuote . $this->doQuoteEscape($value) . $this->csvQuote;
            }
        }
        return $tempLine;
    }
    /**
     * @param $value
     *
     * @throws \DomainException
     */
    protected function validateQuoteMode($value)
    {
        if (!in_array($value, [self::QUOTE_ALL, self::QUOTE_STRING, self::QUOTE_NONE], true)) {
            throw new \DomainException(
                'Valid quote options are quote_all, quote_none, or quote_string'
            );
        }
    }
    /**
     * @var array $csvArray
     */
    protected $csvArray;
    /**
     * Set to quote_all, quote_none, or quote_string (default).
     *
     * @var string $csvColumnQuoteMode
     */
    protected $csvColumnQuoteMode = self::QUOTE_STRING;
    /**
     * @var string $csvDelimiter
     */
    protected $csvDelimiter = ',';
    /**
     * @var string $csvEOL
     */
    protected $csvEOL = "\n";
    /**
     * @var string $csvQuote = '"';
     */
    protected $csvQuote = '"';
    /**
     * @var string $csvWriteMethod
     */
    protected $csvWriteMethod = 'truncate';
    /**
     * @var FilePathNormalizer $fpn
     */
    protected $fpn;
    /**
     * @var array $headerArray
     */
    protected $headerArray;
    /**
     * @var string $headerQuoteMode
     */
    protected $headerQuoteMode = self::QUOTE_STRING;
    /**
     * @var string $quoteEscapeMode
     */
    protected $quoteEscapeMode = self::ESCAPE_DOUBLE;
    /**
     * @var bool $writeHeader
     */
    protected $writeHeader = true;
}
