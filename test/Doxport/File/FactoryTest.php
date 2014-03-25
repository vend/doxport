<?php

namespace Doxport\File;

use Doxport\Test\AbstractTest;
use InvalidArgumentException;

class FactoryTest extends AbstractTest
{
    public function testConstructor()
    {
        $instance = new Factory();
        $this->assertInstanceOf('Doxport\File\Factory', $instance);

        $instance = new Factory();
        $instance->addFormat('something', 'stdClass');
        $instance->setFormat('something');

        $this->assertInstanceOf('Doxport\File\Factory', $instance);
        $this->assertEquals('something', $instance->getFormat());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testBadConstructorFormat()
    {
        $formats = ['something' => 'stdClass'];
        new Factory('else', $formats); // exception here
    }

    public function testAddFormat()
    {
        $instance = new Factory();
        $instance->addFormat('something', 'stdClass');
        $instance->setFormat('something');

        $this->assertArrayHasKey('something', $instance->getFormats());
    }

    public function testSetPath()
    {
        $instance = new Factory();
        $instance->setPath('something');

        $this->assertEquals('something', $instance->getPath());
        $this->assertEquals('something/file.json', $instance->getPathForFile('file'));
    }

    public function testJoin()
    {
        $instance = new Factory();
        $instance->setPath('something');
        $instance->join('else');
        $instance->join('and');
        $instance->join('stuff');

        $this->assertEquals('something/else/and/stuff', $instance->getPath());
    }

    public function testGetFile()
    {
        $instance = new Factory();
        $instance->setPath(self::$root);
        $file = $instance->getFile('foobar');

        $this->assertInstanceOf('Doxport\File\JsonWholeFile', $file);

        unlink($file->getPath());
    }

    public function testPathExists()
    {
        $instance = new Factory();
        $instance->setPath(self::$root);
        $instance->join('testPathExists' . uniqid(time(), true));

        $this->assertFalse($instance->pathExists());
        $instance->createPath();
        $this->assertTrue($instance->pathExists());

        rmdir($instance->getPath());
    }
}
