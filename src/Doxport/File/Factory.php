<?php

namespace Doxport\File;

use \InvalidArgumentException;

/**
 * File factory
 */
class Factory
{
    /**
     * @var array<string>
     */
    protected $formats = [
        'json' => 'Doxport\File\JsonStreamFile',
        'csv'  => 'Doxport\File\CsvFile'
    ];

    /**
     * @var string
     */
    protected $format;

    /**
     * @var string
     */
    protected $path = 'build';

    /**
     * Constructor
     *
     * @param string        $format
     * @param array<string> $formats
     */
    public function __construct($format = 'json', array $formats = null)
    {
        if ($formats) {
            $this->formats = $formats;
        }

        $this->setFormat($format);
    }

    /**
     * @return array<string>
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * @param string $format
     * @param string $class
     * @return self
     */
    public function addFormat($format, $class)
    {
        $this->formats[$format] = $class;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     * @throws InvalidArgumentException
     * @return self
     */
    public function setFormat($format)
    {
        if (!array_key_exists($format, $this->formats)) {
            throw new InvalidArgumentException('Invalid format');
        }

        $this->format = $format;
        return $this;
    }

    /**
     * @param string $path
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param string $path
     * @return self
     */
    public function join($path)
    {
        $this->path .= \DIRECTORY_SEPARATOR . $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function createPath()
    {
        if (!$this->pathExists()) {
            mkdir($this->path, 0775, true);
        }

        if (!is_writable($this->path)) {
            throw new InvalidArgumentException('Cannot write to file path: ' . $this->path);
        }
    }

    /**
     * @return boolean
     */
    public function pathExists()
    {
        return is_dir($this->path);
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

    /**
     * @return string
     */
    protected function getClass()
    {
        return $this->formats[$this->format];
    }

    /**
     * @param string $name
     * @return AbstractFile
     */
    public function getFile($name)
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Could not get file with no name');
        }

        $class = $this->getClass();
        return new $class($this->getPathForFile($name));
    }
}
