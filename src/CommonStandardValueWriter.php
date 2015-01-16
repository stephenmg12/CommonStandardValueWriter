<?php
/**
 * Contains CommonStandardValueWriter.
 * PHP version 5.4
 * LICENSE:
 * This file is part of CommonStandardValueWriter - A better PHP CSV Writer Class for PHP.
 * One of the main goals is to be more flexible then built-in function is PHP
 * Copyright (C) 2014 Michael Cummings
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


namespace CommonStandardValueWriter;

use FilePathNormalizer\FilePathNormalizer;

require_once "../bootstrap.php";

/**
 * Class CommonStandardValueWriter
 * @package CommonStandardValueWriter
 */
class CommonStandardValueWriter
{
    /**
     * @var
     */
    protected $fileHandle;
    /**
     * @var
     */
    protected $filePath;
    /**
     * @var
     */
    protected $csvArray;
    /**
     * @var
     */
    protected $fpn;
    /**
     * @var string
     */
    protected $csvEOL = '\n';
    /**
     * @var string
     */
    protected $csvDelimiter = ',';
    /**
     * @var string
     */
    protected $csvWriteMethod = 'truncate'; //set to append or truncate
    /**
     * @var string
     */
    protected $csvRecordQuoteMethod = 'quote_string'; //Set to quote_all, quote_none, or quote_string (default)

    /**
     * @var int
     */
    protected $csvRecordCount = 0;

    protected $listDelimiter = ' ';

    protected $writeHeader = true;

    protected $headerQuoteMethod = 'quote_string';

    protected $headerArray = [];

    public function __construct($filePath)
    {
        if (!empty($filePath)) {
            $this->setPath($filePath);
        }

    }

    /**
     * @return string
     */
    public function getCsvWriteMethod()
    {
        return $this->csvWriteMethod;
    }

    /**
     * @param string $csvWriteMethod
     */
    public function setCsvWriteMethod($csvWriteMethod)
    {
        $this->csvWriteMethod = $csvWriteMethod;
    }

    /**
     * @return mixed
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param mixed $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @param string $filePath
     *
*@return $this
     * @throws \InvalidArgumentException
     */
    public function setPath($filePath)
    {
        $this->fpn = new FilePathNormalizer();
        $this->filePath = $this->fpn->normalizeFile($filePath);
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
     * @param string $csvDelimiter
     */
    public function setCsvDelimiter($csvDelimiter)
    {
        $this->csvDelimiter = $csvDelimiter;
    }

    /**
     * @return string
     */
    public function getCsvEOL()
    {
        return $this->csvEOL;
    }

    /**
     * @param string $csvEOL
     */
    public function setCsvEOL($csvEOL)
    {
        $this->csvEOL = $csvEOL;
    }

    /**
     * @param array $header
     *
*@return $this
     * @throws \InvalidArgumentException
     */
    public function setHeaderArray($header = [])
    {
        if (!is_array($header)) {
            throw new \InvalidArgumentException("CommonStandardValueWriter::setHeader only accepts Arrays");
        }
        $this->headerArray = $header;
        return $this;
    }

    /**
     * @param array $newLine
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addLine($newLine = [])
    {
        if (is_array($newLine)) {
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
        throw new \InvalidArgumentException("CommonStandardValueWriter::addLine only accepts Arrays");
    }

    /**
     * @return $this
     */
    public function commit()
    {
        $fileHandle = $this->getFileHandle();
        if (empty($fileHandle)) {
            $path = $this->getFilePath();
            if ($this->getCsvWriteMethod() == 'append') {
                $fileHandle = fopen($path, 'a+');
            } else {
                $fileHandle = fopen($path, 'w+');
            }
        }
        if (!empty($this->headerQuoteMethod) && $this->writeHeader == true) {
            if ($this->headerQuoteMethod == 'quote_none') {
                $tempLine = $this->lineArrayToString($this->quoteNone($this->headerArray));
            } elseif ($this->headerQuoteMethod = 'quote_all') {
                $tempLine = $this->lineArrayToString($this->quoteAll($this->headerArray));
            } elseif ($this->headerQuoteMethod == 'quote_string') {
                $tempLine = $this->lineArrayToString($this->quoteString($this->headerArray));
            } else {
                throw new \DomainException("CommonStandardValueWriter::commit valid options are quote_all, quote_none, or quote_string");
            }
            fwrite($fileHandle, $tempLine);
        }

        foreach ($this->csvArray as $line) {
            if ($this->csvRecordQuoteMethod == 'quote_none') {
                $tempLine = $this->lineArrayToString($this->quoteNone($line));
            } elseif ($this->csvRecordQuoteMethod = 'quote_all') {
                $tempLine = $this->lineArrayToString($this->quoteAll($line));
            } elseif ($this->csvRecordQuoteMethod == 'quote_string') {
                $tempLine = $this->lineArrayToString($this->quoteString($line));
            } else {
                throw new \DomainException("CommonStandardValueWriter::csvQuoteNone only accepts Arrays");
            }
            fwrite($fileHandle, $tempLine);
        }
        return $this;
    }

    /**
     * @param mixed $fileHandle
     */
    public function setFileHandle($fileHandle)
    {
        $this->fileHandle = $fileHandle;
    }

    /**
     * @param array $line
     *
*@return array
     */
    protected function quoteNone($line = [])
    {
        if (!is_array($line)) {
            throw new \InvalidArgumentException("CommonStandardValueWriter::csvQuoteNone only accepts Arrays");
        }
        return $line;
    }

    /**
     * @param array $line
     *
     * @return array
     */
    protected function quoteAll($line = [])
    {
        if (!is_array($line)) {
            throw new \InvalidArgumentException("CommonStandardValueWriter::csvQuoteNone only accepts Arrays");
        }
        $tempLine = [];
        foreach ($line as $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $tempLine[] = '"' . $value . '"';
        }
        return $tempLine;
    }

    /**
     * @param array $line
     *
     * @return array
     */
    protected function quoteString($line = [])
    {
        if (!is_array($line)) {
            throw new \InvalidArgumentException("CommonStandardValueWriter::csvQuoteNone only accepts Arrays");
        }
        $tempLine = [];
        foreach ($line as $value) {
            if (is_numeric($value)) {
                $tempLine[] = $value;
            } elseif (is_array($value)) {
                $tempLine[] = '"' . implode(',', $value) . '"';
            } else {
                $tempLine[] = '"' . $value . '"';
            }
        }
        return $tempLine;

    }

    /**
     * @param array $line
     *
     * @return string
     */
    protected function lineArrayToString($line = [])
    {
        if (!is_array($line)) {
            throw new \InvalidArgumentException("CommonStandardValueWriter::csvQuoteNone only accepts Arrays");
        }
        return implode($this->csvDelimiter, $line) . $this->csvEOL;
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
*@return bool
     */
    protected function is_assoc($array)
    {
        return (array_values($array) !== $array);
    }
}
