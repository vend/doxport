<?php

namespace Doxport\File;

use Doxport\Test\AbstractTest;

abstract class AbstractFileTest extends AbstractTest
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
     * @return void
     */
    abstract public function testReadObject();

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->file = self::$root . \DIRECTORY_SEPARATOR . str_replace('\\', '', get_class($this));
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
     * @return AbstractFile
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
            self::$root . DIRECTORY_SEPARATOR
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
