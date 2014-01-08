<?php

namespace Doxport\Action\Base;

use Doxport\Util\AsyncFile;

trait FileActionTrait
{
    /**
     * @var string
     */
    protected $path;

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
     * @return void
     */
    public function createFilePath()
    {
        mkdir($this->getFilePath(), 0775, true);
    }

    /**
     * @return string
     */
    protected function getFileActionName()
    {
        $class = $this->getClassName(__CLASS__);
        $class = preg_replace('/([a-z])([A-Z])/', '\1-\2', $class);
        return strtolower($class);
    }

    /**
     * @return string
     */
    abstract protected function getClassName($class);

    /**
     * @return string
     */
    protected function getFileMode()
    {
        return 'a';
    }

    /**
     * @param string $name
     * @return AsyncFile
     */
    public function getFileInstance($name)
    {
        return new AsyncFile(
            $this->getFilePath() . DIRECTORY_SEPARATOR . $name,
            $this->getFileMode()
        );
    }
}
