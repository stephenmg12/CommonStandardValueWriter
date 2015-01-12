<?php
/**
 * Created by PhpStorm.
 * User: stepheng
 * Date: 1/9/2015
 * Time: 12:03 PM
 */


namespace CommonStandardValueWriter;

use FilePathNormalizer\FilePathNormalizer;

require_once "../vendor/autoload.php";

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
    protected $csvWriteMethod = 'truncate';

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
     * @return $this
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
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setHeader($header = [])
    {
        if (!is_array($header)) {
            throw new \InvalidArgumentException("CommonStandardValueWriter::setHeader only accepts Arrays");
        }
        $this->header = $header;
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
            if ($this->getCsvWriteMethod() == 'append') {
                $path = $this->getFilePath();
                $fileHandle = fopen($path, 'a+');
            }
        }
        return $this;
    }

    /**
     * @return mixed
     */
    protected function getFileHandle()
    {
        return $this->fileHandle;
    }

    /**
     * @param mixed $fileHandle
     */
    public function setFileHandle($fileHandle)
    {
        $this->fileHandle = $fileHandle;
    }

    /**
     * @param $array
     * @return bool
     */
    protected function is_assoc($array)
    {
        return (array_values($array) !== $array);
    }
}
