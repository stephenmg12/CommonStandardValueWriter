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
class CommonStandardValueWriter {
    /**
     * @var
     */
    protected $fileHandle;
    protected $filePath;
    protected $csvArray;
    protected $fpn;

    public function __construct($filePath)
    {
        if(!empty($filePath)) {
            $this->setPath($filePath);
        }

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

    public function setHeader($header=[])
    {
        if(is_array($header)) {
            $this->header = $header;
            return $this;
        }
        throw new \InvalidArgumentException("CommonStandardValueWriter::setHeader only accepts Arrays");
    }

    /**
     * @param array $newLine
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addLine($newLine=[])
    {
        if(is_array($newLine)) {
            if($this->is_assoc($newLine)) {
                $tempLine = [];
                foreach($newLine as $key => $value) {
                    $tempLine[]=$value;
                }
                $this->csvArray[] = $tempLine;
            } else {
                $this->csvArray[] = $newLine;
            }
            return $this;
        }
        throw new \InvalidArgumentException("CommonStandardValueWriter::addLine only accepts Arrays");
    }

    public function commit() {
     return $this;
    }

    protected function is_assoc($array){
        return (array_values($array) !== $array);
    }
}
