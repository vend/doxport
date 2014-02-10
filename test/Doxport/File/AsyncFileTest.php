<?php

namespace Doxport\File;

use Doxport\Test;

abstract class AsyncFileTest extends Test
{
    protected $root = 'build/tmp';
    protected $file;

    /**
     * @return string
     */
    abstract protected function getClassUnderTest();

    /**
     * @return void
     */
    abstract public function testWriteObject();

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->file = $this->root . \DIRECTORY_SEPARATOR . str_replace('\\', '', get_class($this));
        @unlink($this->file);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        @unlink($this->file);
    }

    /**
     * @param string $file
     * @return AsyncFile
     */
    protected function getInstance($file = null)
    {
        $class = $this->getClassUnderTest();

        if (!$file) {
            $file = $this->file;
        }

        return new $class($file);
    }
}
