<?php

namespace Doxport\Action\Base;

use Doxport\Schema;
use Doxport\Util\AsyncFile;
use Fhaculty\Graph\Walk;
use \InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class Action implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    protected $path;

    /**
     * @param Walk   $fromTargetToRoot
     * @return void
     */
    abstract public function process(Walk $fromTargetToRoot);

    /**
     * @param Walk  $fromTargetToRoot
     * @param array $associationToAndFromTarget
     * @return void
     */
    abstract public function processSelfJoin(Walk $fromTargetToRoot, array $associationToAndFromTarget);

    /**
     * @param string $class
     * @return mixed
     */
    protected function getClassName($class)
    {
        $parts = explode('\\', $class);
        return $parts[count($parts) - 1];
    }

    /**
     * @return string
     */
    protected function getFileMode()
    {
        return 'a';
    }

    /**
     * @param string $name    File name
     * @param string $subpath Optional
     * @throws InvalidArgumentException If path not writable
     * @return AsyncFile
     */
    public function getFileInstance($name, $subpath = '' , $mode = null)
    {
        $path = $this->getFilePath();

        if ($subpath) {
            $path .= \DIRECTORY_SEPARATOR . $subpath;

            if (!is_dir($path)) {
                mkdir($path, 0775, true);
            }

            if (!\is_writable($path)) {
                throw new InvalidArgumentException('Path not writable: ' . $path);
            }
        }

        $path .= \DIRECTORY_SEPARATOR . $name;

        return new AsyncFile(
            $path,
            $mode ?: $this->getFileMode()
        );
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        if (!$this->path) {
            $this->path = 'build' . DIRECTORY_SEPARATOR . $this->getFileActionName();
        }

        return $this->path;
    }

    /**
     * @param string $path
     * @return void
     */
    public function setFilePath($path)
    {
        $this->path = $path;
    }

    /**
     * @throws InvalidArgumentException If file path cannot be written
     * @return void
     */
    public function createFilePath()
    {
        if (!is_dir($this->getFilePath())) {
            mkdir($this->getFilePath(), 0775, true);
        }

        if (!is_writable($this->getFilePath())) {
            throw new InvalidArgumentException('Cannot write to file path: ' . $this->getFilePath());
        }
    }

    /**
     * @return string
     */
    protected function getFileActionName()
    {
        $class = $this->getClassName(get_class($this));
        $class = preg_replace('/([a-z])([A-Z])/', '\1-\2', $class);
        return strtolower($class);
    }
}
