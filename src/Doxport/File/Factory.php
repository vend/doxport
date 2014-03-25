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
    public function __construct($format = null)
    {
        $this->addFormat('json', function ($file) {
            if (is_readable($file) && filesize($file) > 100000) {
                return 'Doxport\File\JsonStreamFile';
            }
            return 'Doxport\File\JsonWholeFile';
        });

        if ($format) {
            $this->setFormat($format);
        } else {
            $this->setFormat('json');
        }
    }

    /**
     * @return array<string>
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * @param string          $format
     * @param string|callable $classOrClosure
     * @return self
     */
    public function addFormat($format, $classOrClosure)
    {
        $this->formats[$format] = $classOrClosure;
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
     * @param string $file Path
     * @return string
     */
    protected function getClass($file)
    {
        if (is_callable($this->formats[$this->format])) {
            return $this->formats[$this->format]($file);
        } else {
            return $this->formats[$this->format];
        }
    }

    /**
     * @param string $name
     * @throws InvalidArgumentException
     * @return AbstractFile
     */
    public function getFile($name)
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Could not get file with no name');
        }

        $path  = $this->getPathForFile($name);
        $class = $this->getClass($path);

        return new $class($path);
    }
}
