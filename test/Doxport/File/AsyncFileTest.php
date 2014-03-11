<?php

namespace Doxport\File;

use Doxport\AbstractTest;

abstract class AsyncFileTest extends AbstractTest
{
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

    public function testNoPathDir()
    {
        $file = $this->getInstance(
            $this->root . DIRECTORY_SEPARATOR
            . uniqid(time(), true) . DIRECTORY_SEPARATOR
            . 'testFile'
        );

        $this->assertTrue(
            is_dir(dirname($file->getPath())),
            'Non-existent initial directory is created'
        );

        unlink($file->getPath());
        rmdir(dirname($file->getPath()));
    }
}
