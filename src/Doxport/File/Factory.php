<?php

namespace Doxport\File;

use \InvalidArgumentException;

/**
 * File factory
 */
class Factory
{
    /**
     * @var array<string => string>
     */
    protected $formats = [
        'json' => 'Doxport\File\JsonFile',
        'csv'  => 'Doxport\File\CsvFile'
    ];

    /**
     * @var string
     */
    protected $format = 'json';

    /**
     * @var string
     */
    protected $path = 'build';

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @param string $path
     */
    public function join($path)
    {
        $this->path .= \DIRECTORY_SEPARATOR . $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $format
     * @throws InvalidArgumentException
     */
    public function setFormat($format)
    {
        if (!array_key_exists($format, $this->formats)) {
            throw new InvalidArgumentException('Invalid format');
        }

        $this->format = $format;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function createPath()
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0775, true);
        }

        if (!is_writable($this->path)) {
            throw new InvalidArgumentException('Cannot write to file path: ' . $this->path);
        }
    }

    /**
     * @param string      $name The bare name of the file, without extension
     * @param string|null $ext  The extension to use, if given. If not, format used.
     * @return string
     */
    public function getPathForFile($name, $ext = null)
    {
        if ($ext == null) {
            $ext = $this->format;
        }

        $name .= '.' . $ext;

        return $this->path . \DIRECTORY_SEPARATOR . $name;
    }

    protected function getClass()
    {
        return $this->formats[$this->format];
    }

    /**
     * @param string $name
     * @param string $mode
     * @return AsyncFile
     */
    public function getFile($name, $mode = 'a')
    {
        $class = $this->getClass();
        return new $class($this->getPathForFile($name), $mode);
    }
}
